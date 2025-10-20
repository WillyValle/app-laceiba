<?php
/**
 * Controlador de WhatsApp
 * Versión adaptada para PDO
 */

require_once "app/controladores/base.controlador.php";
require_once "app/modelos/database.php";

class WhatsappControlador extends BaseControlador {
    
    private $baileys_url;
    private $api_token;
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        $this->baileys_url = getenv('BAILEYS_URL') ?: 'http://baileys:4000';
        $this->api_token = getenv('BAILEYS_API_TOKEN');
        
        // Inicializar conexión a base de datos
        try {
            $this->db = BasedeDatos::Conectar();
        } catch (Exception $e) {
            error_log("Error connecting to database: " . $e->getMessage());
            $this->db = null;
        }
        
        // Verificar autenticación
        if (!isset($_SESSION['user'])) {
            header("Location: ?c=auth");
            exit();
        }
    }
    
    // ============================================
    // MÉTODOS PÚBLICOS PARA RUTAS MVC
    // ============================================
    
    /**
     * Acción: Dashboard
     * Ruta: ?c=whatsapp&a=Dashboard
     */
    public function Dashboard() {
        // Verificar permisos
        if (!$this->hasPermission('MANAGE_WHATSAPP')) {
            header("Location: ?c=inicio");
            exit();
        }
        
        // Obtener datos
        $status = $this->checkStatus();
        $stats = $this->getStats();
        
        // Cargar vista
        $data = [
            'title' => 'WhatsApp - Panel de Control',
            'status' => $status,
            'stats' => $stats
        ];
        
        require_once __DIR__ . '/../vistas/header.php';
        require_once __DIR__ . '/../vistas/whatsapp/dashboard.php';
        require_once __DIR__ . '/../vistas/footer.php';
    }
    
    /**
     * Acción: Logs
     * Ruta: ?c=whatsapp&a=Logs
     */
    public function Logs() {
        // Verificar permisos
        if (!$this->hasPermission('MANAGE_WHATSAPP')) {
            header("Location: ?c=inicio");
            exit();
        }
        
        // Obtener filtros
        $filters = [
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'status' => $_GET['status'] ?? null,
            'page' => $_GET['page'] ?? 1
        ];
        
        // Obtener logs
        $result = $this->getLogs($filters);
        
        // Cargar vista
        $data = [
            'title' => 'WhatsApp - Historial de Envíos',
            'logs' => $result['data'] ?? [],
            'pagination' => $result['pagination'] ?? []
        ];
        
        require_once __DIR__ . '/../vistas/header.php';
        require_once __DIR__ . '/../vistas/whatsapp/logs.php';
        require_once __DIR__ . '/../vistas/footer.php';
    }
    
    /**
     * Acción: Ajax
     * Ruta: ?c=whatsapp&a=Ajax
     */
    public function Ajax() {
        // Verificar permisos
        if (!$this->hasPermission('MANAGE_WHATSAPP')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Sin permisos']);
            exit();
        }
        
        require_once __DIR__ . '/../vistas/whatsapp/whatsapp_ajax.php';
        exit();
    }
    
    // ============================================
    // MÉTODOS EXISTENTES
    // ============================================
    
    /**
     * Envía un PDF al WhatsApp del cliente después de finalizar un servicio
     */
    public function sendServicePDF($serviceId, $customerId, $phone, $fileName, $relativePath) {
        
        if (empty($this->api_token)) {
            return [
                'success' => false,
                'error' => 'API Token not configured'
            ];
        }
        
        if (!$serviceId || !$customerId || !$phone || !$fileName || !$relativePath) {
            return [
                'success' => false,
                'error' => 'Missing required parameters'
            ];
        }
        
        $fullPath = __DIR__ . '/../../uploads/' . $relativePath;
        
        if (!file_exists($fullPath)) {
            error_log("WhatsApp Error: File not found - {$fullPath}");
            return [
                'success' => false,
                'error' => 'PDF file not found',
                'path' => $relativePath
            ];
        }
        
        $postData = [
            'serviceId' => (int)$serviceId,
            'customerId' => (int)$customerId,
            'phone' => $phone,
            'fileName' => $fileName,
            'relativePath' => $relativePath
        ];
        
        $ch = curl_init($this->baileys_url . '/send-pdf');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Token: ' . $this->api_token
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("WhatsApp cURL Error: {$curlError}");
            return [
                'success' => false,
                'error' => 'Connection error to WhatsApp service',
                'details' => $curlError
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['success']) && $result['success']) {
            error_log("WhatsApp Success: PDF sent to {$phone} for service #{$serviceId}");
            return [
                'success' => true,
                'message' => 'PDF sent successfully to WhatsApp',
                'data' => $result['data'] ?? []
            ];
        } else {
            $errorMsg = $result['error'] ?? 'Unknown error';
            error_log("WhatsApp Error: {$errorMsg} (HTTP {$httpCode})");
            return [
                'success' => false,
                'error' => $errorMsg,
                'httpCode' => $httpCode
            ];
        }
    }
    
    public function checkStatus() {
        $ch = curl_init($this->baileys_url . '/health');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return [
            'success' => false,
            'error' => 'Service unavailable'
        ];
    }
    
    public function getQRCode() {
        $ch = curl_init($this->baileys_url . '/qr');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Token: ' . $this->api_token
            ],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Si hay error de conexión
        if ($error) {
            return [
                'success' => false,
                'error' => 'Error de conexión con Baileys: ' . $error
            ];
        }
        
        // Decodificar respuesta
        $data = json_decode($response, true);
        
        // Si el código es 200
        if ($httpCode === 200) {
            if ($data && isset($data['qr'])) {
                $qrData = $data['qr'];
                
                // Verificar si ya es una data URI (imagen)
                if (strpos($qrData, 'data:image') === 0) {
                    return [
                        'success' => true,
                        'qr' => $qrData
                    ];
                }
                
                // Si empieza con "2@" es el texto del QR de WhatsApp
                if (strpos($qrData, '2@') === 0 || strpos($qrData, '1@') === 0) {
                    return [
                        'success' => true,
                        'qr' => $qrData,
                        'qrText' => $qrData,
                        'needsGeneration' => true
                    ];
                }
                
                // Si es base64 puro (solo caracteres válidos de base64)
                if (preg_match('/^[A-Za-z0-9+\/=]+$/', $qrData) && strlen($qrData) > 100) {
                    return [
                        'success' => true,
                        'qr' => 'data:image/png;base64,' . $qrData
                    ];
                }
                
                // Por defecto, asumir que es texto y necesita generación
                return [
                    'success' => true,
                    'qr' => $qrData,
                    'qrText' => $qrData,
                    'needsGeneration' => true
                ];
            }
            
            return $data ?: ['success' => false, 'error' => 'Respuesta vacía'];
        }
        
        // Si el código es 400 y dice "already connected"
        if ($httpCode === 400 && $data && isset($data['message'])) {
            if (stripos($data['message'], 'already connected') !== false) {
                return [
                    'success' => false,
                    'error' => 'Ya estás conectado a WhatsApp',
                    'alreadyConnected' => true
                ];
            }
        }
        
        // Otros errores
        return [
            'success' => false,
            'error' => $data['error'] ?? $data['message'] ?? 'No se pudo obtener el código QR',
            'httpCode' => $httpCode,
            'response' => $data
        ];
    }
    
    public function logout() {
        $ch = curl_init($this->baileys_url . '/logout');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Token: ' . $this->api_token
            ],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return [
            'success' => false,
            'error' => 'Logout failed'
        ];
    }
    
    /**
     * Registra un mensaje enviado en la base de datos
     */
    private function logMessage($serviceId, $phone, $pdfPath, $status, $messageId = null, $remarks = null, $customerName = null) {
        try {
            // Validar conexión a base de datos
            if (!$this->db) {
                error_log("WhatsApp Log: Database connection not available");
                return null;
            }
            
            $sql = "INSERT INTO WHATSAPP_LOGS 
                    (SERVICE_ID_SERVICE, PHONE, FILE_PATH, STATUS, MESSAGE_ID, REMARKS, CUSTOMER_NAME, CREATED_AT, UPDATED_AT) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $serviceId,
                $phone,
                $pdfPath,
                $status,
                $messageId,
                $remarks,
                $customerName
            ]);
            
            $logId = $this->db->lastInsertId();
            error_log("WhatsApp Log: Saved with ID $logId");
            
            return $logId;
        } catch (PDOException $e) {
            error_log("Error logging WhatsApp message: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Envía un mensaje de prueba
     */
    public function sendTestMessage($phone, $message) {
        try {
            // Validar que WhatsApp esté conectado
            $status = $this->checkStatus();
            if (!$status['success'] || !($status['connected'] ?? $status['isReady'] ?? false)) {
                return [
                    'success' => false,
                    'error' => 'WhatsApp no está conectado. Por favor conecta primero.'
                ];
            }
            
            // Preparar datos para enviar al endpoint correcto
            $data = [
                'phone' => $phone,
                'message' => $message
            ];
            
            error_log("WhatsApp Test Message: Sending to $phone");
            
            // Usar endpoint /send-message (NO /send-pdf)
            $ch = curl_init($this->baileys_url . '/send-message');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-API-Token: ' . $this->api_token
                ],
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            error_log("WhatsApp Test Message: HTTP Code $httpCode, Response: $response");
            
            if ($error) {
                error_log("WhatsApp Test Message: Connection error - $error");
                return [
                    'success' => false,
                    'error' => 'Error de conexión con Baileys: ' . $error
                ];
            }
            
            $result = json_decode($response, true);
            
            if ($httpCode === 200 && $result && ($result['success'] ?? false)) {
                // Registrar en logs (opcional para mensajes de prueba)
                try {
                    $this->logMessage(
                        null,
                        $phone,
                        null,
                        'sent',
                        $result['data']['messageId'] ?? $result['messageId'] ?? null,
                        'Mensaje de prueba',
                        null
                    );
                } catch (Exception $e) {
                    error_log("WhatsApp Test Message: Log error - " . $e->getMessage());
                }
                
                return [
                    'success' => true,
                    'message' => 'Mensaje enviado exitosamente',
                    'messageId' => $result['data']['messageId'] ?? $result['messageId'] ?? null
                ];
            }
            
            error_log("WhatsApp Test Message: Failed - " . json_encode($result));
            return [
                'success' => false,
                'error' => $result['error'] ?? $result['message'] ?? 'No se pudo enviar el mensaje',
                'details' => $result,
                'httpCode' => $httpCode
            ];
            
        } catch (Exception $e) {
            error_log("WhatsApp Test Message Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    public function resetConnection() {
        try {
            // Ruta a la carpeta de autenticación
            $authPath = __DIR__ . '/../../baileys/auth';
            
            // Validar que la ruta existe y es la correcta (prevenir path traversal)
            $realPath = realpath($authPath);
            if (!$realPath || !is_dir($realPath)) {
                error_log("WhatsApp Reset: Invalid auth path: $authPath");
                return [
                    'success' => false,
                    'error' => 'Auth directory not found'
                ];
            }
            
            // Contar archivos antes de eliminar
            $filesBefore = glob($realPath . '/*');
            $fileCount = count($filesBefore);
            
            error_log("WhatsApp Reset: Starting reset. Files to delete: $fileCount");
            
            // Intentar ejecutar script con sudo (método seguro)
            $output = [];
            $return_code = 0;
            
            // Usar wrapper script dentro del contenedor
            $command = 'sudo /usr/local/bin/reset-baileys-wrapper.sh 2>&1';
            @exec($command, $output, $return_code);
            
            error_log("WhatsApp Reset: Script return code: $return_code");
            error_log("WhatsApp Reset: Script output: " . implode("\n", $output));
            
            if ($return_code === 0) {
                // Verificar que los archivos fueron eliminados
                sleep(1);
                $filesAfter = glob($realPath . '/*');
                $filesDeleted = $fileCount - count($filesAfter);
                
                return [
                    'success' => true,
                    'message' => 'Connection reset successfully. Wait 5-10 seconds for Baileys to restart.',
                    'files_deleted' => $filesDeleted
                ];
            }
            
            // Si el script falló, intentar método manual pero seguro
            error_log("WhatsApp Reset: Script failed, trying manual method");
            
            $deletedFiles = [];
            $errors = [];
            
            foreach ($filesBefore as $file) {
                if (is_file($file)) {
                    // Validar que el archivo está dentro del directorio permitido
                    $realFile = realpath($file);
                    if ($realFile && strpos($realFile, $realPath) === 0) {
                        if (@unlink($file)) {
                            $deletedFiles[] = basename($file);
                        } else {
                            $errors[] = basename($file);
                        }
                    }
                }
            }
            
            $success = count($deletedFiles) > 0;
            
            return [
                'success' => $success,
                'message' => $success 
                    ? 'Session files deleted. Please restart Baileys manually: docker restart laceiba_baileys'
                    : 'Could not delete session files. Check permissions.',
                'files_deleted' => count($deletedFiles),
                'errors' => $errors,
                'suggestion' => 'Run from host: sudo /usr/local/bin/reset-baileys.sh'
            ];
            
        } catch (Exception $e) {
            error_log("WhatsApp Reset Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Internal error: ' . $e->getMessage()
            ];
        }
    }
    
    public function retryFailedSend($logId) {
        $ch = curl_init($this->baileys_url . '/retry/' . $logId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Token: ' . $this->api_token
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return [
            'success' => false,
            'error' => 'Retry failed'
        ];
    }

    public function getLogs($filters = []) {
        // ✅ CONVERTIDO A PDO
        $conn = $this->getConnection();
        
        $where = [];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(wl.CREATED_AT) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(wl.CREATED_AT) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "wl.STATUS = :status";
            $params['status'] = $filters['status'];
        }
        
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $query = "
            SELECT 
                wl.ID_WHATSAPP_LOG,
                wl.SERVICE_ID_SERVICE,
                wl.CUSTOMER_ID_CUSTOMER,
                wl.PHONE,
                wl.FILE_NAME,
                wl.FILE_PATH,
                wl.STATUS,
                wl.MESSAGE_ID,
                wl.ERROR_MESSAGE,
                wl.ATTEMPTS,
                wl.SENT_AT,
                wl.CREATED_AT,
                wl.UPDATED_AT,
                c.NAME_CUSTOMER as CUSTOMER_NAME,
                s.ID_SERVICE as SERVICE_CODE
            FROM WHATSAPP_LOGS wl
            LEFT JOIN CUSTOMER c ON wl.CUSTOMER_ID_CUSTOMER = c.ID_CUSTOMER
            LEFT JOIN SERVICE s ON wl.SERVICE_ID_SERVICE = s.ID_SERVICE
        ";
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        $query .= " ORDER BY wl.CREATED_AT DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $stmt = $conn->prepare($query);
        
        // Bind de parámetros con tipos específicos para LIMIT y OFFSET
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Contar total
        $countQuery = "SELECT COUNT(*) as total FROM WHATSAPP_LOGS wl";
        if (!empty($where)) {
            $whereClause = str_replace([':limit', ':offset'], ['', ''], implode(" AND ", $where));
            $countQuery .= " WHERE " . $whereClause;
        }
        
        $countStmt = $conn->prepare($countQuery);
        // Bind solo parámetros de filtro (no limit/offset)
        foreach ($params as $key => $value) {
            if ($key !== 'limit' && $key !== 'offset') {
                $countStmt->bindValue(":$key", $value);
            }
        }
        $countStmt->execute();
        $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'success' => true,
            'data' => $logs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_rows' => $totalRows,
                'total_pages' => ceil($totalRows / $perPage)
            ]
        ];
    }

    public function getStats($dateFrom = null, $dateTo = null) {
        // ✅ CONVERTIDO A PDO
        $conn = $this->getConnection();
        
        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN STATUS = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN STATUS = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN STATUS = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN STATUS = 'retry' THEN 1 ELSE 0 END) as retry
            FROM WHATSAPP_LOGS
        ";
        
        $params = [];
        
        if ($dateFrom && $dateTo) {
            $query .= " WHERE DATE(CREATED_AT) BETWEEN :date_from AND :date_to";
            $params['date_from'] = $dateFrom;
            $params['date_to'] = $dateTo;
        }
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['success_rate'] = $stats['total'] > 0 
            ? round(($stats['sent'] / $stats['total']) * 100, 2) 
            : 0;
        
        return [
            'success' => true,
            'data' => $stats
        ];
    }

    public function getServiceLogs($lines = 50) {
        $ch = curl_init($this->baileys_url . "/logs?lines=$lines");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Token: ' . $this->api_token
            ],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return [
            'success' => false,
            'logs' => 'Error loading logs',
            'error' => 'HTTP ' . $httpCode
        ];
    }
}