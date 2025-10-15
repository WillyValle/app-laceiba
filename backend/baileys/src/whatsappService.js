const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    makeInMemoryStore
} = require('@whiskeysockets/baileys');
const crypto = require('crypto');
global.crypto = crypto;
const qrcode = require('qrcode-terminal');
const logger = require('./logger');
const MySQLAuthAdapter = require('./authAdapter');
const fs = require('fs');
const path = require('path');
const mime = require('mime-types');

class WhatsAppService {
    constructor(dbConfig) {
        this.sock = null;
        this.qrCode = null;
        this.isReady = false;
        this.dbConfig = dbConfig;
        this.authAdapter = new MySQLAuthAdapter(dbConfig);
        this.store = makeInMemoryStore({ logger });
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
    }

    async initialize() {
        try {
            logger.info('Initializing WhatsApp service...');

            // Crear directorio de autenticación
            const authDir = path.join(__dirname, '..', 'auth');
            if (!fs.existsSync(authDir)) {
                fs.mkdirSync(authDir, { recursive: true });
            }

            // Configurar autenticación con archivos (Baileys v7 usa archivos por defecto)
            const { state, saveCreds } = await useMultiFileAuthState(authDir);

            // Obtener versión más reciente de Baileys
            const { version } = await fetchLatestBaileysVersion();

            // Crear socket de WhatsApp
            this.sock = makeWASocket({
                version,
                logger,
                printQRInTerminal: true,
                auth: {
                    creds: state.creds,
                    keys: makeCacheableSignalKeyStore(state.keys, logger)
                },
                generateHighQualityLinkPreview: true,
                syncFullHistory: false,
                markOnlineOnConnect: false
            });

            // Vincular store con socket
            this.store.bind(this.sock.ev);

            // Event: Actualización de credenciales
            this.sock.ev.on('creds.update', saveCreds);

            // Event: Actualización de conexión
            this.sock.ev.on('connection.update', async (update) => {
                const { connection, lastDisconnect, qr } = update;

                if (qr) {
                    this.qrCode = qr;
                    logger.info('QR Code received, scan it with WhatsApp');
                    qrcode.generate(qr, { small: true });
                }

                if (connection === 'close') {
                    const shouldReconnect = 
                        lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;

                    logger.warn('Connection closed', {
                        reason: lastDisconnect?.error?.output?.statusCode,
                        shouldReconnect
                    });

                    if (shouldReconnect && this.reconnectAttempts < this.maxReconnectAttempts) {
                        this.reconnectAttempts++;
                        logger.info(`Reconnecting... Attempt ${this.reconnectAttempts}`);
                        setTimeout(() => this.initialize(), 5000);
                    } else if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                        logger.error('Max reconnection attempts reached');
                        this.isReady = false;
                    }
                } else if (connection === 'open') {
                    this.isReady = true;
                    this.reconnectAttempts = 0;
                    this.qrCode = null;
                    logger.info('WhatsApp connection established successfully! ✅');
                }
            });

            // Event: Mensajes recibidos (opcional para respuestas automáticas)
            this.sock.ev.on('messages.upsert', async ({ messages }) => {
                // Aquí puedes manejar mensajes entrantes si lo necesitas
                logger.debug('Message received:', messages[0]?.key);
            });

        } catch (error) {
            logger.error('Error initializing WhatsApp service:', error);
            throw error;
        }
    }

    async sendDocument(phone, filePath, caption = '') {
        try {
            if (!this.isReady) {
                throw new Error('WhatsApp service is not ready');
            }

            // Validar archivo
            if (!fs.existsSync(filePath)) {
                throw new Error(`File not found: ${filePath}`);
            }

            // Formatear número de teléfono
            const formattedPhone = phone.includes('@s.whatsapp.net') 
                ? phone 
                : `${phone}@s.whatsapp.net`;

            // Obtener información del archivo
            const fileName = path.basename(filePath);
            const mimeType = mime.lookup(filePath) || 'application/pdf';
            const fileBuffer = fs.readFileSync(filePath);

            logger.info(`Sending document to ${phone}`, { fileName, mimeType });

            // Enviar documento
            const result = await this.sock.sendMessage(formattedPhone, {
                document: fileBuffer,
                mimetype: mimeType,
                fileName: fileName,
                caption: caption
            });

            logger.info('Document sent successfully', { 
                messageId: result.key.id,
                phone 
            });

            return {
                success: true,
                messageId: result.key.id,
                timestamp: result.messageTimestamp
            };

        } catch (error) {
            logger.error('Error sending document:', error);
            throw error;
        }
    }

    getStatus() {
        return {
            isReady: this.isReady,
            hasQR: !!this.qrCode,
            qrCode: this.qrCode,
            reconnectAttempts: this.reconnectAttempts
        };
    }

    async logout() {
        try {
            if (this.sock) {
                await this.sock.logout();
                this.isReady = false;
                logger.info('Logged out from WhatsApp');
            }
        } catch (error) {
            logger.error('Error during logout:', error);
            throw error;
        }
    }
}

module.exports = WhatsAppService;