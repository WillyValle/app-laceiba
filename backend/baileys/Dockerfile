# backend/baileys/Dockerfile
FROM node:18-alpine

# Instalar dependencias del sistema para Baileys
RUN apk add --no-cache \
    python3 \
    make \
    g++ \
    cairo-dev \
    jpeg-dev \
    pango-dev \
    giflib-dev \
    curl \
    git

# Crear directorio de trabajo
WORKDIR /app

# Copiar package.json y package-lock.json
COPY package*.json ./

# Instalar dependencias
RUN npm install --omit=dev && npm cache clean --force

# Copiar código fuente
COPY src/ ./src/

# Crear directorios necesarios
RUN mkdir -p /app/auth /app/logs /app/uploads

# Dar permisos
RUN chown -R node:node /app

# Usuario no-root
USER node

# Exponer puerto
EXPOSE 4000

# Comando de inicio
CMD ["node", "src/index.js"]