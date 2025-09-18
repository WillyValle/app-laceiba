<?php
require_once 'tcpdf/tcpdf.php';
// O si instalaste TCPDF manualmente:
// require_once 'tcpdf/tcpdf.php';

require_once 'app/modelos/servicePdf.php';

/**
 * Controlador para generar PDFs de servicios de fumigación
 * Utiliza TCPDF para crear documentos con formato profesional
 */
class ServicePdfController {
    private $pdfModel;
    private $database;
    
    public function __construct($database) {
        $this->database = $database;
        $this->pdfModel = new ServicePdfModel($database);
    }
    
    /**
     * Genera un PDF completo para un servicio específico
     * @param int $serviceId ID del servicio
     * @return array Resultado con éxito/error y ruta del archivo
     */
    public function generateServicePdf($serviceId) {
        try {
            // 1. Obtener datos del servicio
            $serviceData = $this->pdfModel->getServiceDataForPdf($serviceId);
            
            if (!$serviceData) {
                return [
                    'success' => false,
                    'message' => 'Servicio no encontrado.',
                    'file_path' => null
                ];
            }
            
            // 2. Crear directorio si no existe
            $directory = $this->pdfModel->getServicePdfDirectory($serviceId);
            if (!$this->pdfModel->ensureDirectoryExists($directory)) {
                return [
                    'success' => false,
                    'message' => 'No se pudo crear el directorio para guardar el PDF.',
                    'file_path' => null
                ];
            }
            
            // 3. Generar nombre del archivo
            $fileName = $this->pdfModel->generatePdfFileName($serviceData);
            $fullPath = $directory . $fileName;
            
            // 4. Crear el PDF
            $pdfCreated = $this->createPdfDocument($serviceData, $fullPath);
            
            if ($pdfCreated) {
                return [
                    'success' => true,
                    'message' => 'PDF generado exitosamente.',
                    'file_path' => $fullPath,
                    'file_name' => $fileName
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al generar el PDF.',
                    'file_path' => null
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en generateServicePdf: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al generar el PDF: ' . $e->getMessage(),
                'file_path' => null
            ];
        }
    }
    
    /**
     * Crea el documento PDF usando TCPDF
     * @param array $serviceData Datos del servicio
     * @param string $outputPath Ruta donde guardar el PDF
     * @return bool True si se creó exitosamente
     */
    private function createPdfDocument($serviceData, $outputPath) {
        try {
            // Configurar TCPDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Configuración del documento
            $pdf->SetCreator('Sistema La Ceiba');
            $pdf->SetAuthor('La Ceiba Fumigaciones');
            $pdf->SetTitle('Reporte de Servicio #' . $serviceData['ID_SERVICE']);
            $pdf->SetSubject('Reporte de Servicio de Fumigación');
            
            // Configuración de márgenes y fuente
            $pdf->SetMargins(15, 25, 15);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(10);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('helvetica', '', 10);
            
            // Añadir página
            $pdf->AddPage();
            
            // Generar contenido HTML del PDF
            $htmlContent = $this->generatePdfHtmlContent($serviceData);
            
            // Escribir HTML al PDF
            $pdf->writeHTML($htmlContent, true, false, true, false, '');
            
            // Guardar archivo
            $cleanPath = str_replace('file://', '', $outputPath);
            $absolutePath = '/var/www/html/' . $cleanPath;
            $pdf->Output($absolutePath, 'F');
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error al crear PDF con TCPDF: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera el contenido HTML para el PDF
     * @param array $serviceData Datos del servicio
     * @return string HTML formateado
     */
    private function generatePdfHtmlContent($serviceData) {
    $html = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header-container { width: 100%; margin-bottom: 20px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 10px; }
        .logo-cell { width: 25%; text-align: left; }
        .title-cell { width: 50%; text-align: center; }
        .license-cell { width: 25%; text-align: right; }
        .main-title { font-size: 18px; font-weight: bold; color: #2E7D32; }
        .license-text { font-size: 11px; }
        .license-number { font-weight: bold; }
        
        .section { margin-bottom: 15px; page-break-inside: avoid; }
        .section-title { 
            font-size: 14px; 
            font-weight: bold; 
            background-color: #E8F5E8; 
            padding: 8px; 
            margin-bottom: 10px;
            border: 1px solid #2E7D32;
        }
        
        .client-info { margin-bottom: 15px; }
        .client-row { margin-bottom: 5px; }
        .client-label { font-weight: bold; display: inline-block; width: 80px; }
        
        .service-info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .service-info-table th { 
            background-color: #f5f5f5; 
            padding: 8px; 
            border: 1px solid #ddd; 
            font-weight: bold; 
            text-align: center;
        }
        .service-info-table td { 
            padding: 8px; 
            border: 1px solid #ddd; 
            text-align: center; 
            vertical-align: top;
        }
        
        .horizontal-list { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 15px; 
            margin-bottom: 15px; 
        }
        .horizontal-item { 
            background-color: #f9f9f9; 
            padding: 5px 10px; 
            border-radius: 3px; 
            border: 1px solid #ddd; 
        }
        
        .notes-section { margin-bottom: 15px; }
        .notes-content { 
            background-color: #fafafa; 
            padding: 10px; 
            border: 1px solid #ddd; 
            min-height: 60px;
        }
        
        .image-container { 
            border: 1px solid #ddd; 
            padding: 10px; 
            margin: 10px 0; 
            text-align: center;
        }
    </style>';

    // ENCABEZADO CON LOGO, TÍTULO Y LICENCIA
    $html .= '
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="assets/images/logolaceiba.jpg" style="max-width: 100px; max-height: 60px;" alt="Logo La Ceiba">
                </td>
                <td class="title-cell">
                    <div class="main-title">HOJA DE CONTROL</div>
                </td>
                <td class="license-cell">
                    <div class="license-text">
                        No. Licencia Sanitaria:<br>
                        <span class="license-number">L-S-2917-2022</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>';

    // DATOS DEL CLIENTE
    $html .= '
    <div class="section">
        <div class="section-title">DATOS DEL CLIENTE</div>
        <div class="client-info">
            <div class="client-row">
                <span class="client-label">Nombre:</span>
                ' . htmlspecialchars($serviceData['NAME_CUSTOMER']) . '
            </div>
            <div class="client-row">
                <span class="client-label">Domicilio:</span>
                ' . htmlspecialchars($serviceData['ADDRESS_CUSTOMER']) . '
            </div>';
    
    // Teléfono y correo del cliente
    if (!empty($serviceData['CUSTOMER_WHATSAPP']) || !empty($serviceData['CUSTOMER_TEL'])) {
        $telefono = !empty($serviceData['CUSTOMER_WHATSAPP']) ? $serviceData['CUSTOMER_WHATSAPP'] : $serviceData['CUSTOMER_TEL'];
        $html .= '
            <div class="client-row">
                <span class="client-label">Teléfono:</span>
                ' . htmlspecialchars($telefono) . '
            </div>';
    }
    
    if (!empty($serviceData['CUSTOMER_MAIL'])) {
        $html .= '
            <div class="client-row">
                <span class="client-label">Correo:</span>
                ' . htmlspecialchars($serviceData['CUSTOMER_MAIL']) . '
            </div>';
    }
    
    $html .= '</div></div>';

    // INFORMACIÓN DEL SERVICIO (3 COLUMNAS)
    $html .= '
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL SERVICIO</div>
        <table class="service-info-table">
            <tr>
                <th>No. De Servicio</th>
                <th>Encargado del Servicio</th>
                <th>Horarios</th>
            </tr>
            <tr>
                <td><strong>#' . $serviceData['ID_SERVICE'] . '</strong></td>
                <td>';
    
    // Buscar encargado principal
    $encargado = 'No asignado';
    if (!empty($serviceData['employees'])) {
        foreach ($serviceData['employees'] as $employee) {
            if (stripos($employee['NAME_ROLE_IN_SERVICE'], 'encargado') !== false) {
                $encargado = $employee['NAME_EMPLOYEE'] . ' ' . $employee['LASTNAME_EMPLOYEE'];
                break;
            }
        }
    }
    
    $html .= htmlspecialchars($encargado) . '</td>
                <td>';
    
    // Horarios del servicio
    if (!empty($serviceData['PRESET_DT_HR'])) {
        $html .= '<strong>Programada:</strong><br>' . date('d/m/Y H:i', strtotime($serviceData['PRESET_DT_HR'])) . '<br>';
    }
    if (!empty($serviceData['START_DT_HR'])) {
        $html .= '<strong>Inicio:</strong><br>' . date('d/m/Y H:i', strtotime($serviceData['START_DT_HR'])) . '<br>';
    }
    if (!empty($serviceData['END_DT_HR'])) {
        $html .= '<strong>Fin:</strong><br>' . date('d/m/Y H:i', strtotime($serviceData['END_DT_HR']));
    }
    
    $html .= '</td>
            </tr>
        </table>
    </div>';

    // TIPOS DE SERVICIO (HORIZONTAL)
    // TIPOS DE SERVICIO (CON COMAS)
    if (!empty($serviceData['categories'])) {
        $html .= '
        <div class="section">
            <div class="section-title">TIPOS DE SERVICIO</div>
            <div style="padding: 8px; background-color: #fafafa; border: 1px solid #ddd;">';
        
        $categoryNames = [];
        foreach ($serviceData['categories'] as $category) {
            $categoryNames[] = htmlspecialchars($category['NAME_SERVICE_CATEGORY']);
        }
        
        $html .= implode(', ', $categoryNames);
        
        $html .= '</div>
        </div>';
    }

    // SISTEMAS DE APLICACIÓN (CON COMAS)
    if (!empty($serviceData['application_methods'])) {
        $html .= '
        <div class="section">
            <div class="section-title">SISTEMAS DE APLICACIÓN</div>
            <div style="padding: 8px; background-color: #fafafa; border: 1px solid #ddd;">';
        
        $methodNames = [];
        foreach ($serviceData['application_methods'] as $method) {
            $methodNames[] = htmlspecialchars($method['NAME_APPLICATION_METHOD']);
        }
        
        $html .= implode(', ', $methodNames);
        
        $html .= '</div>
        </div>';
    }

    // NOTAS
    if (!empty($serviceData['NOTES']) || 
        !empty($serviceData['INSPECTION_PROBLEMS']) || 
        !empty($serviceData['INSPECTION_LOCATION']) || 
        !empty($serviceData['INSPECTION_METHODS'])) {
        
        $html .= '
        <div class="section">
            <div class="section-title">NOTAS Y OBSERVACIONES</div>
            <div class="notes-content">';
        
        if (!empty($serviceData['NOTES'])) {
            $html .= '<strong>Notas generales:</strong><br>' . nl2br(htmlspecialchars($serviceData['NOTES'])) . '<br><br>';
        }
        
        if (!empty($serviceData['INSPECTION_PROBLEMS'])) {
            $html .= '<strong>Problemas detectados:</strong><br>' . nl2br(htmlspecialchars($serviceData['INSPECTION_PROBLEMS'])) . '<br><br>';
        }
        
        if (!empty($serviceData['INSPECTION_LOCATION'])) {
            $html .= '<strong>Ubicación de inspección:</strong><br>' . nl2br(htmlspecialchars($serviceData['INSPECTION_LOCATION'])) . '<br><br>';
        }
        
        if (!empty($serviceData['INSPECTION_METHODS'])) {
            $html .= '<strong>Métodos de inspección:</strong><br>' . nl2br(htmlspecialchars($serviceData['INSPECTION_METHODS']));
        }
        
        $html .= '</div>
        </div>';
    }

    // ANEXOS/ARCHIVOS ASOCIADOS (IMÁGENES)
    if (!empty($serviceData['files'])) {
        $html .= '
        <div class="section">
            <div class="section-title">ANEXOS</div>';
        
        foreach ($serviceData['files'] as $file) {
            $fileExtension = strtolower(pathinfo($file['PATH_FILE'], PATHINFO_EXTENSION));
            
            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $html .= $this->embedImageInPdf($file['PATH_FILE'], basename($file['PATH_FILE']));
            }
        }
        
        $html .= '</div>';
    }

    return $html;
}
    
    /**
     * Envía el PDF al navegador para descarga directa
     * @param int $serviceId ID del servicio
     * @return bool True si se envió correctamente
     */
    public function downloadServicePdf($serviceId) {
        try {
            $serviceData = $this->pdfModel->getServiceDataForPdf($serviceId);
            
            if (!$serviceData) {
                return false;
            }
            
            // Crear PDF en memoria y enviarlo al navegador
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            $pdf->SetCreator('Sistema La Ceiba');
            $pdf->SetAuthor('La Ceiba Fumigaciones');
            $pdf->SetTitle('Reporte de Servicio #' . $serviceData['ID_SERVICE']);
            
            $pdf->SetMargins(15, 25, 15);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->SetFont('helvetica', '', 10);
            
            $pdf->AddPage();
            
            $htmlContent = $this->generatePdfHtmlContent($serviceData);
            $pdf->writeHTML($htmlContent, true, false, true, false, '');
            
            $fileName = $this->pdfModel->generatePdfFileName($serviceData);
            
            // Enviar al navegador
            $pdf->Output($fileName, 'D'); // 'D' = download
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error en downloadServicePdf: " . $e->getMessage());
            return false;
        }
    }

    /**
 * Embeber imagen directamente en el HTML del PDF
 * @param string $imagePath Ruta de la imagen
 * @param string $fileName Nombre del archivo
 * @return string HTML con imagen embebida
 */
private function embedImageInPdf($imagePath, $fileName) {
    $html = '';
    
    try {
        // Verificar que el archivo existe
        if (!file_exists($imagePath)) {
            return '<div class="list-item">• [IMAGEN] ' . htmlspecialchars($fileName) . ' (no encontrada)</div>';
        }
        
        // Verificar que es una imagen válida
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return '<div class="list-item">• [ARCHIVO] ' . htmlspecialchars($fileName) . ' (formato no válido)</div>';
        }
        
        // Obtener dimensiones originales
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        
        // Calcular nuevas dimensiones manteniendo proporción
        $maxWidth = 400; // Ancho máximo en píxeles
        $maxHeight = 300; // Alto máximo en píxeles
        
        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = $originalWidth * $ratio;
            $newHeight = $originalHeight * $ratio;
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }
        
        // Convertir imagen a base64
        $imageData = file_get_contents($imagePath);
        $base64 = base64_encode($imageData);
        $mimeType = $imageInfo['mime'];
        
        // Generar HTML con imagen embebida
        $html .= '
        <div class="image-container" style="margin: 10px 0; page-break-inside: avoid;">
            <div style="text-align: center;">
                <img src="data:' . $mimeType . ';base64,' . $base64 . '" 
                     style="max-width: ' . $newWidth . 'px; max-height: ' . $newHeight . 'px; 
                            border: 1px solid #ddd; padding: 5px;" 
                     alt="Imagen del Servicio">
            </div>
        </div>';
        
    } catch (Exception $e) {
        error_log("Error al embeber imagen en PDF: " . $e->getMessage());
        $html = '<div class="list-item">• [IMAGEN] ' . htmlspecialchars($fileName) . ' (error al procesar)</div>';
    }
    
    return $html;
}



}