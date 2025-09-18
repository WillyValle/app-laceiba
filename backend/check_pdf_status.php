<?php
/**
 * Verifica si existe el PDF físicamente en el servidor
 * Versión simplificada sin colas ni base de datos
 */

header('Content-Type: application/json');

// Verificar que se recibió el ID del servicio
if (!isset($_GET['service_id']) || empty($_GET['service_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de servicio requerido']);
    exit;
}

$serviceId = (int)$_GET['service_id'];

try {
    // Verificar si el archivo PDF existe físicamente
    $pdfDirectory = "uploads/service{$serviceId}/docs/";
    $pdfPattern = $pdfDirectory . "service{$serviceId}-*.pdf";
    
    // Buscar archivos PDF que coincidan con el patrón
    $files = glob($pdfPattern);
    
    if (!empty($files)) {
        // PDF encontrado
        $pdfFile = $files[0]; // Tomar el primer archivo encontrado
        $fileTime = file_exists($pdfFile) ? filemtime($pdfFile) : null;
        
        echo json_encode([
            'available' => true,
            'status' => 'COMPLETED',
            'processed_at' => $fileTime ? date('Y-m-d H:i:s', $fileTime) : null,
            'message' => 'PDF disponible para descarga',
            'file_path' => $pdfFile
        ]);
    } else {
        // PDF no encontrado
        echo json_encode([
            'available' => false,
            'status' => 'NOT_FOUND',
            'message' => 'PDF no encontrado. Debe finalizar el servicio para generarlo automáticamente.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error al verificar PDF físico: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'available' => false,
        'status' => 'ERROR',
        'message' => 'Error al verificar el archivo PDF'
    ]);
}
?>