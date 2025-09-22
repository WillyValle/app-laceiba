<?php

class Permission {
    private $pdo;
    private $id_permission;
    private $name_permission;
    private $description;
    private $status;

    public function __CONSTRUCT() {
        try {
            $this->pdo = BasedeDatos::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Getters y Setters
    public function getIdPermission() { return $this->id_permission; }
    public function setIdPermission($id_permission) { $this->id_permission = $id_permission; }
    
    public function getNamePermission() { return $this->name_permission; }
    public function setNamePermission($name_permission) { $this->name_permission = $name_permission; }
    
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }
    
    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }

    // Listar todos los permisos activos
    public function Listar() {
        try {
            $consulta = $this->pdo->prepare("SELECT * FROM PERMISSION WHERE STATUS = 1 ORDER BY NAME_PERMISSION");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Obtener permisos de un usuario específico (JOIN completo)
    public function ObtenerPermisosPorUsuario($user_id) {
        try {
            $consulta = $this->pdo->prepare("
                SELECT DISTINCT p.NAME_PERMISSION, p.DESCRIPTION
                FROM USER_EMPLOYEE ue
                INNER JOIN EMPLOYEE e ON e.ID_EMPLOYEE = ue.EMPLOYEE_ID_EMPLOYEE
                INNER JOIN ROLE_EMPLOYEE re ON re.ID_ROLE_EMPLOYEE = e.ROLE_EMPLOYEE_ID_ROLE_EMPLOYEE
                INNER JOIN ROLE_PERMISSION rp ON rp.ROLE_ID = re.ID_ROLE_EMPLOYEE
                INNER JOIN PERMISSION p ON p.ID_PERMISSION = rp.PERMISSION_ID
                WHERE ue.ID_USER_EMPLOYEE = ? 
                AND ue.STATUS = 1 
                AND e.STATUS = 1 
                AND re.STATUS = 1 
                AND p.STATUS = 1
            ");
            $consulta->execute(array($user_id));
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Verificar si un usuario tiene un permiso específico
    public function UsuarioTienePermiso($user_id, $permission_name) {
        try {
            $consulta = $this->pdo->prepare("
                SELECT COUNT(*) as tiene_permiso
                FROM USER_EMPLOYEE ue
                INNER JOIN EMPLOYEE e ON e.ID_EMPLOYEE = ue.EMPLOYEE_ID_EMPLOYEE
                INNER JOIN ROLE_EMPLOYEE re ON re.ID_ROLE_EMPLOYEE = e.ROLE_EMPLOYEE_ID_ROLE_EMPLOYEE
                INNER JOIN ROLE_PERMISSION rp ON rp.ROLE_ID = re.ID_ROLE_EMPLOYEE
                INNER JOIN PERMISSION p ON p.ID_PERMISSION = rp.PERMISSION_ID
                WHERE ue.ID_USER_EMPLOYEE = ? 
                AND p.NAME_PERMISSION = ?
                AND ue.STATUS = 1 
                AND e.STATUS = 1 
                AND re.STATUS = 1 
                AND p.STATUS = 1
            ");
            $consulta->execute(array($user_id, $permission_name));
            $resultado = $consulta->fetch(PDO::FETCH_OBJ);
            return $resultado->tiene_permiso > 0;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Insertar nuevo permiso
    public function Insertar(Permission $p) {
        try {
            $consulta = "INSERT INTO PERMISSION (NAME_PERMISSION, DESCRIPTION, STATUS) VALUES (?, ?, ?)";
            $this->pdo->prepare($consulta)->execute(array(
                $p->getNamePermission(),
                $p->getDescription(),
                $p->getStatus()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Obtener un permiso por ID
    public function Obtener($id) {
        try {
            $consulta = $this->pdo->prepare("SELECT * FROM PERMISSION WHERE ID_PERMISSION = ?");
            $consulta->execute(array($id));
            return $consulta->fetch(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Actualizar permiso
    public function Actualizar(Permission $p) {
        try {
            $consulta = "UPDATE PERMISSION SET NAME_PERMISSION = ?, DESCRIPTION = ?, STATUS = ? WHERE ID_PERMISSION = ?";
            $this->pdo->prepare($consulta)->execute(array(
                $p->getNamePermission(),
                $p->getDescription(),
                $p->getStatus(),
                $p->getIdPermission()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // Inicializar permisos básicos del sistema
    public function InicializarPermisosBasicos() {
        try {
            $permisos_basicos = [
                ['VIEW_ADMIN_PANEL', 'Acceso al panel de administración completo'],
                ['VIEW_TECHNICIAN_PANEL', 'Acceso al panel de técnicos'],
                ['MANAGE_SERVICES', 'Crear y gestionar servicios'],
                ['MANAGE_CUSTOMERS', 'Gestionar clientes'],
                ['MANAGE_EMPLOYEES', 'Gestionar empleados'],
                ['MANAGE_CONFIGURATION', 'Acceso a configuración del sistema'],
                ['VIEW_REPORTS', 'Ver reportes del sistema']
            ];

            foreach ($permisos_basicos as $permiso) {
                // Verificar si ya existe
                $consulta_existe = $this->pdo->prepare("SELECT COUNT(*) as existe FROM PERMISSION WHERE NAME_PERMISSION = ?");
                $consulta_existe->execute(array($permiso[0]));
                $existe = $consulta_existe->fetch(PDO::FETCH_OBJ);

                if ($existe->existe == 0) {
                    $consulta_insertar = $this->pdo->prepare("INSERT INTO PERMISSION (NAME_PERMISSION, DESCRIPTION, STATUS) VALUES (?, ?, 1)");
                    $consulta_insertar->execute(array($permiso[0], $permiso[1]));
                }
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}