<?php
/**
 * Modelo de WhatsApp
 * Gestiona la interacción con el servicio Baileys
 */

class WhatsappModelo {
    
    private $baileys_url;
    private $api_token;
    private $conn;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->baileys_url = getenv('BAILEYS_URL') ?: 'http://baileys:4000';
        $this->api_token = getenv('BAILEYS_API_TOKEN');
    }
    
    /**
     * Realiza una petición HTTP al servicio Baileys
     */
    private function baileysRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baileys_url . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Token: ' . $this->api_token
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("WhatsApp cURL Error: {$curlError}");
            return [
                'success' => false,
                'error' => 'Error de conexión al servicio WhatsApp',
                'details' => $curlError
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Error desconocido',
                'httpCode' => $httpCode
            ];
        }
        
        return $result;
    }
    
    /**
     * Obtiene el estado de la conexión de WhatsApp
     */
    public function getStatus() {
        return $this->baileysRequest('/health');
    }
    
    /**
     * Obtiene el código QR
     */
    public function getQRCode() {
        return $this->baileysRequest('/qr');
    }
    
    /**
     * Cierra la sesión de WhatsApp
     */
    public function logout() {
        return $this->baileysRequest('/logout', 'POST');
    }
    
    /**
     * Obtiene estadísticas de envíos
     */
    public function getStats($dateFrom = null, $dateTo = null) {
        $where = "";
        $params = [];
        $types = '';
        
        if ($dateFrom && $dateTo) {
            $where = "WHERE DATE(CREATED_AT) BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
            $types = 'ss';
        }
        
        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN STATUS = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN STATUS = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN STATUS = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN STATUS = 'retry' THEN 1 ELSE 0 END) as retry
            FROM WHATSAPP_LOGS
            $where
        ";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        
        $stats['success_rate'] = $stats['total'] > 0 
            ? round(($stats['sent'] / $stats['total']) * 100, 2) 
            : 0;
        
        return [
            'success' => true,
            'data' => $stats
        ];
    }
    
    /**
     * Obtiene logs con filtros
     */
    public function getLogs($filters = []) {
        $where = [];
        $params = [];
        $types = '';
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(wl.CREATED_AT) >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(wl.CREATED_AT) <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }
        
        if (!empty($filters['status'])) {
            $where[] = "wl.STATUS = ?";
            $params[] = $filters['status'];
            $types .= 's';
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
                c.NAME as CUSTOMER_NAME,
                s.CODE as SERVICE_CODE
            FROM WHATSAPP_LOGS wl
            LEFT JOIN CUSTOMER c ON wl.CUSTOMER_ID_CUSTOMER = c.ID_CUSTOMER
            LEFT JOIN SERVICE s ON wl.SERVICE_ID_SERVICE = s.ID_SERVICE
        ";
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        $query .= " ORDER BY wl.CREATED_AT DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        // Contar total
        $countQuery = "SELECT COUNT(*) as total FROM WHATSAPP_LOGS wl";
        if (!empty($where)) {
            $countQuery .= " WHERE " . implode(" AND ", $where);
        }
        
        $countStmt = $this->conn->prepare($countQuery);
        if (!empty($params)) {
            array_pop($params);
            array_pop($params);
            $types = substr($types, 0, -2);
            if (!empty($params)) {
                $countStmt->bind_param($types, ...$params);
            }
        }
        $countStmt->execute();
        $totalRows = $countStmt->get_result()->fetch_assoc()['total'];
        
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
    
    /**
     * Obtiene logs del servicio Baileys
     */
    public function getServiceLogs($lines = 50) {
        return $this->baileysRequest("/logs?lines=$lines");
    }
    
    /**
     * Reintenta enviar un mensaje fallido
     */
    public function retryMessage($logId) {
        return $this->baileysRequest("/retry/$logId", 'POST');
    }
    
    /**
     * Envía un PDF por WhatsApp
     */
    public function sendServicePDF($serviceId, $customerId, $phone, $fileName, $relativePath) {
        if (!$serviceId || !$customerId || !$phone || !$fileName || !$relativePath) {
            return [
                'success' => false,
                'error' => 'Parámetros requeridos faltantes'
            ];
        }
        
        $fullPath = __DIR__ . '/../../uploads/' . $relativePath;
        
        if (!file_exists($fullPath)) {
            error_log("WhatsApp Error: Archivo no encontrado - {$fullPath}");
            return [
                'success' => false,
                'error' => 'Archivo PDF no encontrado',
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
        
        return $this->baileysRequest('/send-pdf', 'POST', $postData);
    }
}