<?php
/**
 * Descarga directa de PDFs desde archivos físicos
 * Versión simplificada sin colas ni base de datos
 */

require_once 'app/modelos/database.php';

// Verificar que se recibió el ID del servicio
if (!isset($_GET['service_id']) || empty($_GET['service_id'])) {
    http_response_code(400);
    die('ID de servicio requerido');
}

$serviceId = (int)$_GET['service_id'];

try {
    // Conexión a la base de datos para verificar que el servicio esté finalizado
    $db = BasedeDatos::Conectar();
    
    if (!$db) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Verificar que el servicio esté finalizado
    $stmt = $db->prepare("
        SELECT s.ID_SERVICE, ss.NAME_SERVICE_STATUS, c.NAME_CUSTOMER
        FROM SERVICE s 
        INNER JOIN SERVICE_STATUS ss ON s.SERVICE_STATUS_ID_SERVICE_STATUS = ss.ID_SERVICE_STATUS 
        INNER JOIN CUSTOMER c ON s.CUSTOMER_ID_CUSTOMER = c.ID_CUSTOMER
        WHERE s.ID_SERVICE = :service_id
    ");
    $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
    $stmt->execute();
    $serviceInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$serviceInfo) {
        http_response_code(404);
        die('Servicio no encontrado');
    }
    
    // Verificar que esté finalizado
    if ($serviceInfo['NAME_SERVICE_STATUS'] !== 'Finalizado') {
        http_response_code(400);
        die('El servicio debe estar finalizado para descargar el reporte');
    }
    
    // Buscar archivo PDF físico
    $pdfDirectory = "uploads/service{$serviceId}/docs/";
    $pdfPattern = $pdfDirectory . "service{$serviceId}-*.pdf";
    $files = glob($pdfPattern);
    
    if (empty($files) || !file_exists($files[0])) {
        http_response_code(404);
        die('Reporte PDF no encontrado. El PDF se genera automáticamente al finalizar el servicio.');
    }
    
    $filePath = $files[0];
    $fileName = basename($filePath);
    $fileSize = filesize($filePath);
    
    // Configurar headers para descarga
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Limpiar buffer de salida
    if (ob_get_length()) {
        ob_clean();
    }
    
    // Enviar archivo
    readfile($filePath);
    exit;
    
} catch (Exception $e) {
    error_log("Error en descarga de reporte: " . $e->getMessage());
    http_response_code(500);
    die('Error interno del servidor');
}
?>