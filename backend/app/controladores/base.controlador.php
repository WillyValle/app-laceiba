<?php

require_once "app/modelos/permission.php";

class BaseControlador {
    protected $permissionModel;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->permissionModel = new Permission();
        $this->checkLogin();
    }

    // Verificar si el usuario está logueado
    protected function checkLogin() {
        if (!isset($_SESSION['user'])) {
            header("Location: ?c=auth");
            exit();
        }

        // Si debe cambiar contraseña, redirigir
        if ($_SESSION['user']['force_password_change'] == 1) {
            // Permitir solo las acciones de cambio de contraseña
            $current_controller = $_GET['c'] ?? '';
            $current_action = $_GET['a'] ?? '';
            
            if (!($current_controller === 'auth' && in_array($current_action, ['ChangePassword', 'ProcessChangePassword', 'Logout']))) {
                header("Location: ?c=auth&a=ChangePassword");
                exit();
            }
        }
    }

    // Verificar si el usuario tiene un permiso específico
    protected function checkPermission($permission) {
        if (!isset($_SESSION['permissions']) || !in_array($permission, $_SESSION['permissions'])) {
            $this->show403();
            exit();
        }
    }

    // Mostrar página de error 403
    protected function show403() {
        http_response_code(403);
        require_once "app/vistas/header.php";
        require_once "app/vistas/errors/403.php";
        require_once "app/vistas/footer.php";
    }

    // Verificar si el usuario tiene alguno de los permisos especificados
    protected function checkAnyPermission($permissions) {
        if (!isset($_SESSION['permissions'])) {
            $this->show403();
            exit();
        }

        $has_permission = false;
        foreach ($permissions as $permission) {
            if (in_array($permission, $_SESSION['permissions'])) {
                $has_permission = true;
                break;
            }
        }

        if (!$has_permission) {
            $this->show403();
            exit();
        }
    }

    // Función helper para verificar permisos en vistas
    public static function hasPermission($permission) {
        return isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions']);
    }

    // Función helper para obtener usuario actual
    public static function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    // Función helper para obtener rol actual
    public static function getCurrentRole() {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Obtener conexión a la base de datos
     */
    protected function getConnection() {
        global $conn;
        
        require_once __DIR__ . '/../modelos/database.php';
        return BasedeDatos::Conectar();
        
        return $conn;
    }
}