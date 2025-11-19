<?php
/**
 * Clase para optimizar imágenes antes de guardar y antes de usar en PDFs
 * Reduce el tamaño de archivos y evita problemas de memoria con TCPDF
 */
class ImageOptimizer {
    
    /**
     * Optimiza una imagen: redimensiona y comprime
     * @param string $sourcePath Ruta de la imagen original
     * @param string $destinationPath Ruta donde guardar la imagen optimizada (opcional)
     * @param int $maxWidth Ancho máximo en píxeles
     * @param int $maxHeight Alto máximo en píxeles
     * @param int $quality Calidad de compresión (1-100)
     * @return bool|string False si falla, ruta del archivo optimizado si tiene éxito
     */
    public static function optimizeImage($sourcePath, $destinationPath = null, $maxWidth = 1920, $maxHeight = 1920, $quality = 85) {
        try {
            // Si no se especifica destino, sobrescribir el original
            if ($destinationPath === null) {
                $destinationPath = $sourcePath;
            }
            
            // Verificar que el archivo existe
            if (!file_exists($sourcePath)) {
                error_log("ImageOptimizer: Archivo no encontrado: $sourcePath");
                return false;
            }
            
            // Obtener información de la imagen
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                error_log("ImageOptimizer: No es una imagen válida: $sourcePath");
                return false;
            }
            
            list($originalWidth, $originalHeight, $imageType) = $imageInfo;
            
            // Si la imagen es muy pequeña, no optimizar (menor a 500KB)
            $fileSize = filesize($sourcePath);
            if ($fileSize < 500 * 1024 && $originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                // Si el destino es diferente, copiar
                if ($sourcePath !== $destinationPath) {
                    copy($sourcePath, $destinationPath);
                }
                return $destinationPath;
            }
            
            // Crear recurso de imagen según el tipo
            $sourceImage = self::createImageResource($sourcePath, $imageType);
            if (!$sourceImage) {
                error_log("ImageOptimizer: No se pudo crear recurso de imagen");
                return false;
            }
            
            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            
            // Solo redimensionar si es necesario
            if ($ratio < 1) {
                $newWidth = (int)($originalWidth * $ratio);
                $newHeight = (int)($originalHeight * $ratio);
            } else {
                $newWidth = $originalWidth;
                $newHeight = $originalHeight;
            }
            
            // Crear nueva imagen
            $optimizedImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparencia para PNG y GIF
            if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
                imagealphablending($optimizedImage, false);
                imagesavealpha($optimizedImage, true);
                $transparent = imagecolorallocatealpha($optimizedImage, 255, 255, 255, 127);
                imagefilledrectangle($optimizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Redimensionar
            imagecopyresampled(
                $optimizedImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $originalWidth, $originalHeight
            );
            
            // Guardar según el tipo
            $saved = self::saveOptimizedImage($optimizedImage, $destinationPath, $imageType, $quality);
            
            // Liberar memoria
            imagedestroy($sourceImage);
            imagedestroy($optimizedImage);
            
            if ($saved) {
                // Log del ahorro
                $newSize = filesize($destinationPath);
                $savedBytes = $fileSize - $newSize;
                $savedPercent = round(($savedBytes / $fileSize) * 100, 1);
                error_log("ImageOptimizer: Optimizada $sourcePath - Ahorrado: " . self::formatBytes($savedBytes) . " ($savedPercent%)");
                
                return $destinationPath;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("ImageOptimizer: Error al optimizar imagen: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Optimiza una imagen específicamente para PDF (más agresivo)
     * @param string $sourcePath Ruta de la imagen original
     * @param string $destinationPath Ruta donde guardar la imagen optimizada
     * @return bool|string False si falla, ruta del archivo optimizado si tiene éxito
     */
    public static function optimizeForPdf($sourcePath, $destinationPath = null) {
        // Parámetros más agresivos para PDFs
        return self::optimizeImage($sourcePath, $destinationPath, 800, 800, 75);
    }
    
    /**
     * Optimiza múltiples imágenes
     * @param array $imagePaths Array de rutas de imágenes
     * @param int $maxWidth Ancho máximo
     * @param int $maxHeight Alto máximo
     * @param int $quality Calidad
     * @return array Array con resultados [ruta => éxito]
     */
    public static function optimizeMultiple($imagePaths, $maxWidth = 1920, $maxHeight = 1920, $quality = 85) {
        $results = [];
        
        foreach ($imagePaths as $imagePath) {
            $result = self::optimizeImage($imagePath, null, $maxWidth, $maxHeight, $quality);
            $results[$imagePath] = $result !== false;
        }
        
        return $results;
    }
    
    /**
     * Crea un recurso de imagen desde un archivo
     * @param string $path Ruta del archivo
     * @param int $imageType Tipo de imagen (IMAGETYPE_*)
     * @return resource|false Recurso de imagen o false si falla
     */
    private static function createImageResource($path, $imageType) {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return @imagecreatefromgif($path);
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    return @imagecreatefromwebp($path);
                }
                return false;
            default:
                return false;
        }
    }
    
    /**
     * Guarda una imagen optimizada
     * @param resource $image Recurso de imagen
     * @param string $path Ruta donde guardar
     * @param int $imageType Tipo de imagen original
     * @param int $quality Calidad de compresión
     * @return bool True si se guardó correctamente
     */
    private static function saveOptimizedImage($image, $path, $imageType, $quality) {
        // Siempre guardar como JPEG para PDFs (mejor compresión)
        // Excepto si es PNG con transparencia
        if ($imageType === IMAGETYPE_PNG) {
            // Verificar si tiene transparencia
            $hasTransparency = false;
            $width = imagesx($image);
            $height = imagesy($image);
            
            for ($x = 0; $x < $width && !$hasTransparency; $x += 10) {
                for ($y = 0; $y < $height && !$hasTransparency; $y += 10) {
                    $rgba = imagecolorat($image, $x, $y);
                    $alpha = ($rgba & 0x7F000000) >> 24;
                    if ($alpha > 0) {
                        $hasTransparency = true;
                    }
                }
            }
            
            if ($hasTransparency) {
                // Guardar como PNG
                $pngQuality = (int)(9 - ($quality / 100 * 9)); // Convertir 0-100 a 9-0
                return imagepng($image, $path, $pngQuality);
            }
        }
        
        // Para todo lo demás, guardar como JPEG
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'jpg' && strtolower($extension) !== 'jpeg') {
            $path = preg_replace('/\.[^.]+$/', '.jpg', $path);
        }
        
        return imagejpeg($image, $path, $quality);
    }
    
    /**
     * Formatea bytes a formato legible
     * @param int $bytes Número de bytes
     * @return string Tamaño formateado
     */
    private static function formatBytes($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
    
    /**
     * Verifica si una imagen necesita optimización
     * @param string $imagePath Ruta de la imagen
     * @param int $maxWidth Ancho máximo deseado
     * @param int $maxHeight Alto máximo deseado
     * @param int $maxSizeKb Tamaño máximo en KB
     * @return bool True si necesita optimización
     */
    public static function needsOptimization($imagePath, $maxWidth = 1920, $maxHeight = 1920, $maxSizeKb = 500) {
        if (!file_exists($imagePath)) {
            return false;
        }
        
        $fileSize = filesize($imagePath);
        if ($fileSize > $maxSizeKb * 1024) {
            return true;
        }
        
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo) {
            list($width, $height) = $imageInfo;
            if ($width > $maxWidth || $height > $maxHeight) {
                return true;
            }
        }
        
        return false;
    }
}