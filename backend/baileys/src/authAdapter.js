const mysql = require('mysql2/promise');
const logger = require('./logger');

class MySQLAuthAdapter {
    constructor(dbConfig, sessionId = 'main_session') {
        this.dbConfig = dbConfig;
        this.sessionId = sessionId;
        this.pool = null;
    }

    async initPool() {
        if (!this.pool) {
            this.pool = mysql.createPool({
                host: this.dbConfig.host,
                port: this.dbConfig.port,
                user: this.dbConfig.user,
                password: this.dbConfig.password,
                database: this.dbConfig.database,
                waitForConnections: true,
                connectionLimit: 10,
                queueLimit: 0
            });
            logger.info('MySQL pool initialized for auth');
        }
        return this.pool;
    }

    async readData(key) {
        try {
            const pool = await this.initPool();
            const [rows] = await pool.execute(
                'SELECT AUTH_DATA FROM WHATSAPP_AUTH WHERE SESSION_KEY = ?',
                [this.sessionId]
            );

            if (rows.length === 0) return null;

            const authData = JSON.parse(rows[0].AUTH_DATA);
            return authData[key] || null;
        } catch (error) {
            logger.error(`Error reading auth data for key ${key}:`, error);
            return null;
        }
    }

    async writeData(key, value) {
        try {
            const pool = await this.initPool();
            
            // Obtener datos actuales
            const [rows] = await pool.execute(
                'SELECT AUTH_DATA FROM WHATSAPP_AUTH WHERE SESSION_KEY = ?',
                [this.sessionId]
            );

            let authData = {};
            if (rows.length > 0) {
                authData = JSON.parse(rows[0].AUTH_DATA);
            }

            // Actualizar o agregar la clave
            authData[key] = value;

            // Guardar en DB
            if (rows.length > 0) {
                await pool.execute(
                    'UPDATE WHATSAPP_AUTH SET AUTH_DATA = ?, UPDATED_AT = NOW() WHERE SESSION_KEY = ?',
                    [JSON.stringify(authData), this.sessionId]
                );
            } else {
                await pool.execute(
                    'INSERT INTO WHATSAPP_AUTH (SESSION_KEY, AUTH_DATA) VALUES (?, ?)',
                    [this.sessionId, JSON.stringify(authData)]
                );
            }

            logger.debug(`Auth data written for key: ${key}`);
        } catch (error) {
            logger.error(`Error writing auth data for key ${key}:`, error);
            throw error;
        }
    }

    async removeData(key) {
        try {
            const pool = await this.initPool();
            const [rows] = await pool.execute(
                'SELECT AUTH_DATA FROM WHATSAPP_AUTH WHERE SESSION_KEY = ?',
                [this.sessionId]
            );

            if (rows.length > 0) {
                const authData = JSON.parse(rows[0].AUTH_DATA);
                delete authData[key];

                await pool.execute(
                    'UPDATE WHATSAPP_AUTH SET AUTH_DATA = ?, UPDATED_AT = NOW() WHERE SESSION_KEY = ?',
                    [JSON.stringify(authData), this.sessionId]
                );
            }

            logger.debug(`Auth data removed for key: ${key}`);
        } catch (error) {
            logger.error(`Error removing auth data for key ${key}:`, error);
            throw error;
        }
    }

    async clearAll() {
        try {
            const pool = await this.initPool();
            await pool.execute(
                'DELETE FROM WHATSAPP_AUTH WHERE SESSION_KEY = ?',
                [this.sessionId]
            );
            logger.info('All auth data cleared');
        } catch (error) {
            logger.error('Error clearing auth data:', error);
            throw error;
        }
    }
}

module.exports = MySQLAuthAdapter;