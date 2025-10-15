<?php
// backend/app/controllers/WhatsappController.php

class WhatsappController {
    
    private $baileys_url;
    private $api_token;
    
    public function __construct() {
        $this->baileys_url = getenv('BAILEYS_URL') ?: 'http://baileys:4000';
        $this->api_token = getenv('BAILEYS_API_TOKEN');
    }
    
    /**
     * Envía un PDF al WhatsApp del cliente después de finalizar un servicio
     * 
     * @param int $serviceId ID del servicio (ID_SERVICE)
     * @param int $customerId ID del cliente (ID_CUSTOMER)
     * @param string $phone Número de WhatsApp
     * @param string $fileName Nombre del archivo PDF
     * @param string $relativePath Ruta relativa desde /backend/uploads/
     * @return array Respuesta con success, message y data
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
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return [
            'success' => false,
            'error' => 'Cannot retrieve QR code'
        ];
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
}