require('dotenv').config();
const express = require('express');
const mysql = require('mysql2/promise');
const WhatsAppService = require('./whatsappService');
const logger = require('./logger');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(express.json());

// Configuración de base de datos
const dbConfig = {
    host: process.env.DB_HOST || 'mysql',
    port: parseInt(process.env.DB_PORT) || 3306,
    user: process.env.DB_USER,
    password: process.env.DB_PASS,
    database: process.env.DB_NAME
};

// Pool de conexiones MySQL
const pool = mysql.createPool({
    ...dbConfig,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Instancia del servicio WhatsApp
const whatsappService = new WhatsAppService(dbConfig);

// Middleware de autenticación
const authMiddleware = (req, res, next) => {
    const token = req.headers['x-api-token'] || req.headers['authorization']?.replace('Bearer ', '');
    
    if (!token || token !== process.env.API_TOKEN) {
        logger.warn('Unauthorized access attempt', { ip: req.ip });
        return res.status(401).json({ 
            success: false, 
            error: 'Unauthorized' 
        });
    }
    
    next();
};

// ============================================
// ENDPOINTS
// ============================================

// Health check
app.get('/health', (req, res) => {
    const status = whatsappService.getStatus();
    res.json({
        success: true,
        service: 'baileys-whatsapp',
        status: status.isReady ? 'ready' : 'not_ready',
        ...status
    });
});

// Obtener QR Code
app.get('/qr', authMiddleware, (req, res) => {
    const status = whatsappService.getStatus();
    
    if (status.qrCode) {
        res.json({
            success: true,
            qr: status.qrCode,
            message: 'Scan this QR code with WhatsApp'
        });
    } else if (status.isReady) {
        res.json({
            success: true,
            message: 'Already connected, no QR needed'
        });
    } else {
        res.status(503).json({
            success: false,
            error: 'QR not available, service may be connecting...'
        });
    }
});

// Enviar mensaje de texto simple
app.post('/send-message', authMiddleware, async (req, res) => {
    const { phone, message } = req.body;

    // Validaciones
    if (!phone || !message) {
        return res.status(400).json({
            success: false,
            error: 'Missing required fields',
            required: ['phone', 'message']
        });
    }

    try {
        logger.info('Sending text message', { phone });

        // Enviar mensaje por WhatsApp
        const result = await whatsappService.sendMessage(phone, message);

        logger.info('Message sent successfully', { 
            messageId: result.messageId,
            phone
        });

        res.json({
            success: true,
            message: 'Message sent successfully',
            data: {
                messageId: result.messageId,
                phone
            }
        });

    } catch (error) {
        logger.error('Error sending message:', error);

        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Enviar PDF
app.post('/send-pdf', authMiddleware, async (req, res) => {
    const { serviceId, customerId, phone, fileName, relativePath } = req.body;

    // Validaciones
    if (!serviceId || !customerId || !phone || !fileName || !relativePath) {
        return res.status(400).json({
            success: false,
            error: 'Missing required fields',
            required: ['serviceId', 'customerId', 'phone', 'fileName', 'relativePath']
        });
    }

    let logId = null;

    try {
        // Construir ruta completa del archivo
        const fullPath = path.join('/app/uploads', relativePath);
        
        logger.info('Attempting to send PDF', { 
            serviceId, 
            customerId, 
            phone, 
            fileName,
            fullPath 
        });

        // Validar que existe el archivo
        if (!fs.existsSync(fullPath)) {
            throw new Error(`File not found: ${fullPath}`);
        }

        // Crear registro en WHATSAPP_LOGS
        const [logResult] = await pool.execute(
            `INSERT INTO WHATSAPP_LOGS 
            (SERVICE_ID_SERVICE, CUSTOMER_ID_CUSTOMER, PHONE, FILE_NAME, FILE_PATH, STATUS, ATTEMPTS) 
            VALUES (?, ?, ?, ?, ?, 'pending', 1)`,
            [serviceId, customerId, phone, fileName, relativePath]
        );

        logId = logResult.insertId;

        // Enviar documento por WhatsApp
        const result = await whatsappService.sendDocument(
            phone,
            fullPath,
            `📄 Documento de servicio #${serviceId}`
        );

        // Actualizar log como exitoso
        await pool.execute(
            `UPDATE WHATSAPP_LOGS 
            SET STATUS = 'sent', MESSAGE_ID = ?, SENT_AT = NOW(), UPDATED_AT = NOW() 
            WHERE ID_WHATSAPP_LOG = ?`,
            [result.messageId, logId]
        );

        logger.info('PDF sent successfully', { 
            logId, 
            messageId: result.messageId 
        });

        res.json({
            success: true,
            message: 'PDF sent successfully',
            data: {
                logId,
                messageId: result.messageId,
                phone,
                fileName
            }
        });

    } catch (error) {
        logger.error('Error sending PDF:', error);

        // Actualizar log como fallido
        if (logId) {
            await pool.execute(
                `UPDATE WHATSAPP_LOGS 
                SET STATUS = 'failed', ERROR_MESSAGE = ?, UPDATED_AT = NOW() 
                WHERE ID_WHATSAPP_LOG = ?`,
                [error.message, logId]
            );
        }

        res.status(500).json({
            success: false,
            error: error.message,
            logId
        });
    }
});

// Reintentar envío fallido
app.post('/retry/:logId', authMiddleware, async (req, res) => {
    const { logId } = req.params;

    try {
        // Obtener información del log
        const [logs] = await pool.execute(
            'SELECT * FROM WHATSAPP_LOGS WHERE ID_WHATSAPP_LOG = ? AND STATUS = "failed"',
            [logId]
        );

        if (logs.length === 0) {
            return res.status(404).json({
                success: false,
                error: 'Log not found or not in failed status'
            });
        }

        const log = logs[0];
        const fullPath = path.join('/app/uploads', log.FILE_PATH);

        // Verificar archivo
        if (!fs.existsSync(fullPath)) {
            throw new Error(`File not found: ${fullPath}`);
        }

        // Incrementar intentos
        await pool.execute(
            'UPDATE WHATSAPP_LOGS SET ATTEMPTS = ATTEMPTS + 1, STATUS = "retry" WHERE ID_WHATSAPP_LOG = ?',
            [logId]
        );

        // Reintentar envío
        const result = await whatsappService.sendDocument(
            log.PHONE,
            fullPath,
            `📄 Documento de servicio #${log.SERVICE_ID}`
        );

        // Actualizar como exitoso
        await pool.execute(
            `UPDATE WHATSAPP_LOGS 
            SET STATUS = 'sent', MESSAGE_ID = ?, SENT_AT = NOW(), ERROR_MESSAGE = NULL 
            WHERE ID_WHATSAPP_LOG = ?`,
            [result.messageId, logId]
        );

        res.json({
            success: true,
            message: 'PDF sent successfully on retry',
            data: { logId, messageId: result.messageId }
        });

    } catch (error) {
        logger.error('Error retrying send:', error);

        await pool.execute(
            'UPDATE WHATSAPP_LOGS SET STATUS = "failed", ERROR_MESSAGE = ? WHERE ID_WHATSAPP_LOG = ?',
            [error.message, logId]
        );

        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Logout de WhatsApp
app.post('/logout', authMiddleware, async (req, res) => {
    try {
        await whatsappService.logout();
        res.json({
            success: true,
            message: 'Logged out successfully'
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Obtener logs del servicio
app.get('/logs', authMiddleware, (req, res) => {
    try {
        const lines = parseInt(req.query.lines) || 50;
        const logsPath = path.join(__dirname, '..', 'logs', 'baileys.log');
        
        if (!fs.existsSync(logsPath)) {
            return res.json({
                success: true,
                logs: 'No logs available yet',
                timestamp: new Date().toISOString()
            });
        }

        // Leer últimas N líneas del archivo
        const content = fs.readFileSync(logsPath, 'utf-8');
        const allLines = content.split('\n').filter(line => line.trim());
        const lastLines = allLines.slice(-lines).join('\n');

        res.json({
            success: true,
            logs: lastLines,
            timestamp: new Date().toISOString()
        });

    } catch (error) {
        logger.error('Error reading logs:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Stream de logs en tiempo real (opcional - para dashboard avanzado)
app.get('/logs/stream', authMiddleware, (req, res) => {
    res.setHeader('Content-Type', 'text/event-stream');
    res.setHeader('Cache-Control', 'no-cache');
    res.setHeader('Connection', 'keep-alive');

    const logsPath = path.join(__dirname, '..', 'logs', 'baileys.log');
    
    // Enviar logs existentes
    if (fs.existsSync(logsPath)) {
        const content = fs.readFileSync(logsPath, 'utf-8');
        const lines = content.split('\n').slice(-50).join('\n');
        res.write(`data: ${JSON.stringify({ logs: lines })}\n\n`);
    }

    // Vigilar cambios en el archivo
    const watcher = fs.watch(logsPath, (eventType) => {
        if (eventType === 'change') {
            try {
                const content = fs.readFileSync(logsPath, 'utf-8');
                const lines = content.split('\n').slice(-50).join('\n');
                res.write(`data: ${JSON.stringify({ logs: lines })}\n\n`);
            } catch (error) {
                logger.error('Error in log stream:', error);
            }
        }
    });

    // Limpiar al cerrar conexión
    req.on('close', () => {
        watcher.close();
    });
});

// ============================================
// INICIALIZACIÓN
// ============================================

const PORT = process.env.PORT || 4000;

async function startServer() {
    try {
        // Verificar conexión a MySQL
        await pool.getConnection();
        logger.info('MySQL connection established');

        // Inicializar WhatsApp
        await whatsappService.initialize();

        // Iniciar servidor Express
        app.listen(PORT, () => {
            logger.info(`🚀 Baileys WhatsApp Service running on port ${PORT}`);
            logger.info(`Environment: ${process.env.NODE_ENV}`);
        });

    } catch (error) {
        console.error('ERROR COMPLETO:', error);
        console.error('Stack:', error.stack);
        console.error('Message:', error.message);
        logger.error('Failed to start server:', error);
        process.exit(1);
    }
}

// Manejo de señales de cierre
process.on('SIGINT', async () => {
    logger.info('SIGINT received, closing connections...');
    await pool.end();
    process.exit(0);
});

process.on('SIGTERM', async () => {
    logger.info('SIGTERM received, closing connections...');
    await pool.end();
    process.exit(0);
});

// Iniciar servidor
startServer();