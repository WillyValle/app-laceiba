const pino = require('pino');
const fs = require('fs');
const path = require('path');

// Asegurar que existe el directorio de logs
const logsDir = path.join(__dirname, '..', 'logs');
if (!fs.existsSync(logsDir)) {
    fs.mkdirSync(logsDir, { recursive: true });
}

const logger = pino(
    {
        level: process.env.LOG_LEVEL || 'info',
        timestamp: pino.stdTimeFunctions.isoTime,
        formatters: {
            level: (label) => {
                return { level: label };
            }
        }
    },
    pino.multistream([
        // Console con formato pretty
        {
            level: 'debug',
            stream: pino.transport({
                target: 'pino-pretty',
                options: {
                    colorize: true,
                    translateTime: 'SYS:standard',
                    ignore: 'pid,hostname'
                }
            })
        },
        // Archivo de logs
        {
            level: 'info',
            stream: fs.createWriteStream(
                path.join(logsDir, 'baileys.log'),
                { flags: 'a' }
            )
        }
    ])
);

module.exports = logger;