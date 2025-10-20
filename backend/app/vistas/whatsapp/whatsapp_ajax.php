<?php
/**
 * AJAX Handler para el Dashboard de WhatsApp
 */

// Header JSON PRIMERO
header('Content-Type: application/json');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Incluir archivos
require_once __DIR__ . '/../../modelos/database.php';
require_once __DIR__ . '/../../controladores/base.controlador.php';
require_once __DIR__ . '/../../controladores/whatsapp.controlador.php';

// Verificar permisos
if (!BaseControlador::hasPermission('MANAGE_WHATSAPP')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Sin permisos']);
    exit;
}

// Instanciar controlador
$controller = new WhatsappControlador();

// Obtener acción
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        
        case 'get_status':
            $response = $controller->checkStatus();
            echo json_encode($response);
            break;
        
        case 'get_qr':
            $response = $controller->getQRCode();
            echo json_encode($response);
            break;
        
        case 'logout':
            $response = $controller->logout();
            echo json_encode($response);
            break;
        
        case 'reset_connection':
            $response = $controller->resetConnection();
            echo json_encode($response);
            break;
        
        case 'send_test_message':
            // Debug: Ver qué llega
            error_log("send_test_message called");
            error_log("POST data: " . print_r($_POST, true));
            
            $phone = $_POST['phone'] ?? null;
            $message = $_POST['message'] ?? null;
            
            error_log("Phone: $phone, Message: $message");
            
            if (!$phone || !$message) {
                error_log("Missing fields - phone: " . ($phone ? 'OK' : 'MISSING') . ", message: " . ($message ? 'OK' : 'MISSING'));
                echo json_encode([
                    'success' => false, 
                    'error' => 'Teléfono y mensaje son requeridos',
                    'debug' => [
                        'phone' => $phone,
                        'message' => $message,
                        'post' => $_POST
                    ]
                ]);
                break;
            }
            
            // Validar formato de teléfono (solo números, 8-15 dígitos)
            if (!preg_match('/^[0-9]{8,15}$/', $phone)) {
                echo json_encode(['success' => false, 'error' => 'Formato de teléfono inválido']);
                break;
            }
            
            $response = $controller->sendTestMessage($phone, $message);
            echo json_encode($response);
            break;
        
        case 'get_logs':
            $filters = [
                'date_from' => $_POST['date_from'] ?? null,
                'date_to' => $_POST['date_to'] ?? null,
                'status' => $_POST['status'] ?? null,
                'page' => $_POST['page'] ?? 1
            ];
            
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            $response = $controller->getLogs($filters);
            echo json_encode($response);
            break;
        
        case 'get_log_details':
            $logId = $_POST['log_id'] ?? null;
            
            if (!$logId) {
                echo json_encode(['success' => false, 'error' => 'log_id requerido']);
                break;
            }
            
            // Obtener detalles del log específico
            $filters = ['page' => 1];
            $allLogs = $controller->getLogs($filters);
            
            $logDetails = null;
            foreach ($allLogs['data'] as $log) {
                if ($log['ID_WHATSAPP_LOG'] == $logId) {
                    $logDetails = $log;
                    break;
                }
            }
            
            if ($logDetails) {
                echo json_encode(['success' => true, 'data' => $logDetails]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Log no encontrado']);
            }
            break;
        
        case 'retry':
            $logId = $_POST['log_id'] ?? null;
            
            if (!$logId) {
                echo json_encode(['success' => false, 'error' => 'log_id requerido']);
                break;
            }
            
            $response = $controller->retryFailedSend($logId);
            echo json_encode($response);
            break;
        
        case 'get_stats':
            $dateFrom = $_POST['date_from'] ?? null;
            $dateTo = $_POST['date_to'] ?? null;
            
            $response = $controller->getStats($dateFrom, $dateTo);
            echo json_encode($response);
            break;
        
        case 'get_service_logs':
            $lines = $_POST['lines'] ?? 50;
            $response = $controller->getServiceLogs($lines);
            echo json_encode($response);
            break;
        
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida: ' . $action
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    
    error_log("Error en whatsapp_ajax.php: " . $e->getMessage());
}