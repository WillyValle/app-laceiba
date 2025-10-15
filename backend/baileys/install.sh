#!/bin/bash
# backend/baileys/install.sh

echo "🚀 Installing Baileys WhatsApp Service..."

# Crear directorios necesarios
mkdir -p auth logs

# Instalar dependencias
echo "📦 Installing Node.js dependencies..."
npm install

# Verificar instalación
echo "✅ Verifying installation..."
npm list @whiskeysockets/baileys

echo "✨ Installation complete!"
echo ""
echo "Next steps:"
echo "1. Configure .env file with your database credentials"
echo "2. Run 'docker-compose up -d baileys' to start the service"
echo "3. Check logs with 'docker logs -f laceiba_baileys'"
echo "4. Scan QR code to authenticate WhatsApp"
