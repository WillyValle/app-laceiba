<?php

require_once "app/modelos/permission.php";
require_once "app/modelos/rolepermission.php";

class AuthControlador {
    private $permissionModel;
    private $rolePermissionModel;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->permissionModel = new Permission();
        $this->rolePermissionModel = new RolePermission();
    }

    // Mostrar formulario de login
    public function Inicio() {
        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['user'])) {
            header("Location: ?c=inicio");
            exit();
        }
        
        require_once "app/vistas/auth/login.php";
    }

    // Procesar login
    public function Login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $error_message = '';

            // Validar campos vacíos
            if (empty($username) || empty($password)) {
                $error_message = 'Por favor ingrese usuario y contraseña';
                require_once "app/vistas/auth/login.php";
                return;
            }

            try {
                // Conectar a la base de datos
                $pdo = BasedeDatos::Conectar();
                
                // Consulta para verificar credenciales con SHA256
                $consulta = $pdo->prepare("
                    SELECT ue.ID_USER_EMPLOYEE, ue.USERNAME, ue.FORCE_PASSWORD_CHANGE,
                           e.ID_EMPLOYEE, e.NAME_EMPLOYEE, e.LASTNAME_EMPLOYEE, e.PATH_IMG_EMPLOYEE,
                           re.ID_ROLE_EMPLOYEE, re.NAME_ROLE_EMPLOYEE
                    FROM USER_EMPLOYEE ue
                    INNER JOIN EMPLOYEE e ON e.ID_EMPLOYEE = ue.EMPLOYEE_ID_EMPLOYEE
                    INNER JOIN ROLE_EMPLOYEE re ON re.ID_ROLE_EMPLOYEE = e.ROLE_EMPLOYEE_ID_ROLE_EMPLOYEE
                    WHERE ue.USERNAME = ? 
                    AND ue.PASSWORD_HASH = SHA2(?, 256)
                    AND ue.STATUS = 1 
                    AND e.STATUS = 1 
                    AND re.STATUS = 1
                ");
                
                $consulta->execute(array($username, $password));
                $user_data = $consulta->fetch(PDO::FETCH_OBJ);

                if ($user_data) {
                    // Obtener permisos del usuario
                    $permisos = $this->permissionModel->ObtenerPermisosPorUsuario($user_data->ID_USER_EMPLOYEE);
                    $permisos_array = array();
                    
                    foreach ($permisos as $permiso) {
                        $permisos_array[] = $permiso->NAME_PERMISSION;
                    }

                    // Guardar datos en sesión
                    $_SESSION['user'] = array(
                        'id_user' => $user_data->ID_USER_EMPLOYEE,
                        'id_employee' => $user_data->ID_EMPLOYEE,
                        'username' => $user_data->USERNAME,
                        'name' => $user_data->NAME_EMPLOYEE . ' ' . $user_data->LASTNAME_EMPLOYEE,
                        'image' => $user_data->PATH_IMG_EMPLOYEE,
                        'force_password_change' => $user_data->FORCE_PASSWORD_CHANGE
                    );

                    $_SESSION['role'] = array(
                        'id_role' => $user_data->ID_ROLE_EMPLOYEE,
                        'name_role' => $user_data->NAME_ROLE_EMPLOYEE
                    );

                    $_SESSION['permissions'] = $permisos_array;

                    // Redirigir según el tipo de usuario
                    if ($user_data->FORCE_PASSWORD_CHANGE == 1) {
                        header("Location: ?c=auth&a=ChangePassword");
                    } else {
                        // Redirigir al dashboard principal para ambos roles
                        header("Location: ?c=inicio");
                    }
                    exit();
                } else {
                    $error_message = 'Usuario o contraseña incorrectos';
                }

            } catch (Exception $e) {
                $error_message = 'Error del sistema: ' . $e->getMessage();
            }

            // Si llegamos aquí, hay un error
            require_once "app/vistas/auth/login.php";
        } else {
            // Si no es POST, mostrar formulario
            $this->Inicio();
        }
    }

    // Cerrar sesión
    public function Logout() {
        if (session_status() === PHP_SESSION_NONE) {
        session_start();
        }
        session_destroy();
        header("Location: ?c=auth");
        exit();
    }

    // Mostrar formulario de cambio de contraseña
    public function ChangePassword() {
        if (!isset($_SESSION['user'])) {
            header("Location: ?c=auth");
            exit();
        }

        require_once "app/vistas/auth/change_password.php";
    }

    // Procesar cambio de contraseña
    public function ProcessChangePassword() {
        if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=auth");
            exit();
        }

        $current_password = trim($_POST['current_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');
        $error_message = '';
        $success_message = '';

        // Validaciones
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'Todos los campos son obligatorios';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'Las contraseñas nuevas no coinciden';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'La contraseña debe tener al menos 6 caracteres';
        } else {
            try {
                $pdo = BasedeDatos::Conectar();
                
                // Verificar contraseña actual
                $consulta_verificar = $pdo->prepare("
                    SELECT ID_USER_EMPLOYEE 
                    FROM USER_EMPLOYEE 
                    WHERE ID_USER_EMPLOYEE = ? 
                    AND PASSWORD_HASH = SHA2(?, 256)
                ");
                $consulta_verificar->execute(array($_SESSION['user']['id_user'], $current_password));
                
                if ($consulta_verificar->fetch()) {
                    // Actualizar contraseña
                    $consulta_actualizar = $pdo->prepare("
                        UPDATE USER_EMPLOYEE 
                        SET PASSWORD_HASH = SHA2(?, 256), FORCE_PASSWORD_CHANGE = 0 
                        WHERE ID_USER_EMPLOYEE = ?
                    ");
                    $consulta_actualizar->execute(array($new_password, $_SESSION['user']['id_user']));
                    
                    // Actualizar sesión
                    $_SESSION['user']['force_password_change'] = 0;
                    
                    $success_message = 'Contraseña actualizada correctamente';
                    
                    // Redirigir después de 2 segundos
                    header("refresh:2;url=?c=inicio");
                } else {
                    $error_message = 'La contraseña actual es incorrecta';
                }
                
            } catch (Exception $e) {
                $error_message = 'Error del sistema: ' . $e->getMessage();
            }
        }

        require_once "app/vistas/auth/change_password.php";
    }
}