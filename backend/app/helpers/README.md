# Helpers del Sistema

## ImageOptimizer.php

Clase para optimización de imágenes en el servidor.

### Uso básico:
```php
// Optimizar imagen sobrescribiendo el original
ImageOptimizer::optimizeImage(
    'ruta/imagen.jpg',    // Ruta origen
    null,                 // null = sobrescribir
    1920,                 // Ancho máximo
    1920,                 // Alto máximo
    85                    // Calidad (70-95)
);

// Optimizar específicamente para PDF (más agresivo)
ImageOptimizer::optimizeForPdf('ruta/imagen.jpg', 'ruta/destino.jpg');

// Optimizar múltiples imágenes
$resultados = ImageOptimizer::optimizeMultiple($arrayRutas);
```

### Configuración:

- **Calidad**: 70 (más compresión) a 95 (mejor calidad)
- **Dimensiones**: Ajustar según necesidad (recomendado 1920px máx)
- **Para PDFs**: Usar dimensiones más pequeñas (600-800px)

### Notas:

- Requiere extensión GD de PHP
- Convierte imágenes a JPEG por defecto (mejor compresión)
- Preserva transparencia en PNGs cuando es necesario
- Registra logs de optimización en error_log