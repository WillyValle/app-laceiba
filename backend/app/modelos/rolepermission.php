<?php

class RolePermission {
    private $pdo;
    private $role_id;
    private $permission_id;

    public function __CONSTRUCT() {
        try {
            $this->pdo = BasedeDatos::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Getters y Setters
    public function getRoleId() { return $this->role_id; }
    public function setRoleId($role_id) { $this->role_id = $role_id; }
    
    public function getPermissionId() { return $this->permission_id; }
    public function setPermissionId($permission_id) { $this->permission_id = $permission_id; }

    // Obtener todos los permisos asignados a un rol
    public function ObtenerPermisosPorRol($role_id) {
        try {
            $consulta = $this->pdo->prepare("
                SELECT p.ID_PERMISSION, p.NAME_PERMISSION, p.DESCRIPTION,
                       CASE WHEN rp.ROLE_ID IS NOT NULL THEN 1 ELSE 0 END as ASIGNADO
                FROM PERMISSION p
                LEFT JOIN ROLE_PERMISSION rp ON rp.PERMISSION_ID = p.ID_PERMISSION AND rp.ROLE_ID = ?
                WHERE p.STATUS = 1
                ORDER BY p.NAME_PERMISSION
            ");
            $consulta->execute(array($role_id));
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Asignar permiso a un rol
    public function AsignarPermiso($role_id, $permission_id) {
        try {
            // Verificar si ya existe la asignación
            $consulta_existe = $this->pdo->prepare("SELECT COUNT(*) as existe FROM ROLE_PERMISSION WHERE ROLE_ID = ? AND PERMISSION_ID = ?");
            $consulta_existe->execute(array($role_id, $permission_id));
            $existe = $consulta_existe->fetch(PDO::FETCH_OBJ);

            if ($existe->existe == 0) {
                $consulta = $this->pdo->prepare("INSERT INTO ROLE_PERMISSION (ROLE_ID, PERMISSION_ID) VALUES (?, ?)");
                $consulta->execute(array($role_id, $permission_id));
                return true;
            }
            return false; // Ya existía
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Revocar permiso de un rol
    public function RevocarPermiso($role_id, $permission_id) {
        try {
            $consulta = $this->pdo->prepare("DELETE FROM ROLE_PERMISSION WHERE ROLE_ID = ? AND PERMISSION_ID = ?");
            $consulta->execute(array($role_id, $permission_id));
            return true;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Asignar múltiples permisos a un rol (reemplaza los existentes)
    public function AsignarPermisosCompletos($role_id, $permission_ids) {
        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            // Eliminar todos los permisos actuales del rol
            $consulta_eliminar = $this->pdo->prepare("DELETE FROM ROLE_PERMISSION WHERE ROLE_ID = ?");
            $consulta_eliminar->execute(array($role_id));

            // Insertar los nuevos permisos
            if (!empty($permission_ids)) {
                $consulta_insertar = $this->pdo->prepare("INSERT INTO ROLE_PERMISSION (ROLE_ID, PERMISSION_ID) VALUES (?, ?)");
                foreach ($permission_ids as $permission_id) {
                    $consulta_insertar->execute(array($role_id, $permission_id));
                }
            }

            // Confirmar transacción
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Rollback en caso de error
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }

    // Configurar permisos por defecto para roles específicos
    public function ConfigurarPermisosDefecto() {
        try {
            // Obtener IDs de roles (asumiendo nombres estándar)
            $consulta_roles = $this->pdo->prepare("SELECT ID_ROLE_EMPLOYEE, NAME_ROLE_EMPLOYEE FROM ROLE_EMPLOYEE WHERE STATUS = 1");
            $consulta_roles->execute();
            $roles = $consulta_roles->fetchAll(PDO::FETCH_OBJ);

            // Obtener IDs de permisos
            $consulta_permisos = $this->pdo->prepare("SELECT ID_PERMISSION, NAME_PERMISSION FROM PERMISSION WHERE STATUS = 1");
            $consulta_permisos->execute();
            $permisos = $consulta_permisos->fetchAll(PDO::FETCH_OBJ);

            // Crear arrays asociativos para facilitar el mapeo
            $roles_map = [];
            foreach ($roles as $rol) {
                $roles_map[strtolower($rol->NAME_ROLE_EMPLOYEE)] = $rol->ID_ROLE_EMPLOYEE;
            }

            $permisos_map = [];
            foreach ($permisos as $permiso) {
                $permisos_map[$permiso->NAME_PERMISSION] = $permiso->ID_PERMISSION;
            }

            // Configurar permisos para Administrador
            if (isset($roles_map['administrador'])) {
                $permisos_admin = [
                    'VIEW_ADMIN_PANEL',
                    'MANAGE_SERVICES',
                    'MANAGE_CUSTOMERS',
                    'MANAGE_EMPLOYEES',
                    'MANAGE_CONFIGURATION',
                    'VIEW_REPORTS'
                ];
                
                $permission_ids_admin = [];
                foreach ($permisos_admin as $permiso_name) {
                    if (isset($permisos_map[$permiso_name])) {
                        $permission_ids_admin[] = $permisos_map[$permiso_name];
                    }
                }
                
                $this->AsignarPermisosCompletos($roles_map['administrador'], $permission_ids_admin);
            }

            // Configurar permisos para Técnico
            if (isset($roles_map['tecnico']) || isset($roles_map['técnico'])) {
                $role_id_tecnico = $roles_map['tecnico'] ?? $roles_map['técnico'];
                $permisos_tecnico = [
                    'VIEW_TECHNICIAN_PANEL'
                ];
                
                $permission_ids_tecnico = [];
                foreach ($permisos_tecnico as $permiso_name) {
                    if (isset($permisos_map[$permiso_name])) {
                        $permission_ids_tecnico[] = $permisos_map[$permiso_name];
                    }
                }
                
                $this->AsignarPermisosCompletos($role_id_tecnico, $permission_ids_tecnico);
            }

            return true;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Listar todos los roles con sus permisos
    public function ListarRolesConPermisos() {
        try {
            $consulta = $this->pdo->prepare("
                SELECT re.ID_ROLE_EMPLOYEE, re.NAME_ROLE_EMPLOYEE, re.DESCRIPTION,
                       COUNT(rp.PERMISSION_ID) as TOTAL_PERMISOS
                FROM ROLE_EMPLOYEE re
                LEFT JOIN ROLE_PERMISSION rp ON rp.ROLE_ID = re.ID_ROLE_EMPLOYEE
                WHERE re.STATUS = 1
                GROUP BY re.ID_ROLE_EMPLOYEE, re.NAME_ROLE_EMPLOYEE, re.DESCRIPTION
                ORDER BY re.NAME_ROLE_EMPLOYEE
            ");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}