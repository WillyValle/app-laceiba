<?php

require_once "app/modelos/permission.php";
require_once "app/modelos/rolepermission.php";
require_once "app/controladores/base.controlador.php";

class PermissionControlador extends BaseControlador {
    protected $permissionModel;
    protected $rolePermissionModel;

    public function __CONSTRUCT() {
        parent::__CONSTRUCT();
        // Solo administradores pueden gestionar permisos
        $this->checkPermission('MANAGE_CONFIGURATION');
        
        $this->permissionModel = new Permission();
        $this->rolePermissionModel = new RolePermission();
    }

    // Vista principal de gestión de permisos
    public function Inicio() {
        $roles = $this->rolePermissionModel->ListarRolesConPermisos();
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/permission/listpermissions.php";
        require_once "app/vistas/footer.php";
    }

    // Gestionar permisos de un rol específico
    public function GestionarRol() {
        $role_id = $_GET['role_id'] ?? 0;
        
        if ($role_id == 0) {
            header("Location: ?c=permission");
            exit();
        }

        $permisos = $this->rolePermissionModel->ObtenerPermisosPorRol($role_id);
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/permission/manage_role_permissions.php";
        require_once "app/vistas/footer.php";
    }

    // Guardar cambios en permisos de rol
    public function GuardarPermisosRol() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role_id = $_POST['role_id'] ?? 0;
            $permission_ids = $_POST['permissions'] ?? array();

            try {
                $this->rolePermissionModel->AsignarPermisosCompletos($role_id, $permission_ids);
                $_SESSION['success_message'] = 'Permisos actualizados correctamente';
            } catch (Exception $e) {
                $_SESSION['error_message'] = 'Error al actualizar permisos: ' . $e->getMessage();
            }

            header("Location: ?c=permission&a=GestionarRol&role_id=" . $role_id);
        } else {
            header("Location: ?c=permission");
        }
    }
}