<?php

require_once "app/modelos/servicecategory.php";
require_once "app/modelos/applicationmethod.php";
require_once "app/modelos/servicestatus.php";
require_once "app/controladores/base.controlador.php";

class InicioControlador extends BaseControlador {
    private $modelo;

    public function __CONSTRUCT() {
        parent::__CONSTRUCT();
        $this->modelo = new ServiceCategory();
    }

    public function Inicio() {
        // Verificar que tenga al menos uno de estos permisos para acceder al dashboard
        $this->checkAnyPermission(['VIEW_ADMIN_PANEL', 'VIEW_TECHNICIAN_PANEL']);
        
        $bd = BasedeDatos::Conectar();
        
        // Obtener estadísticas según el tipo de usuario
        $stats = $this->getStatistics();
        
        require_once "app/vistas/header.php";
        
        // Mostrar dashboard según permisos
        if (BaseControlador::hasPermission('VIEW_ADMIN_PANEL')) {
            require_once "app/vistas/inicio/dashboard_admin.php";
        } else {
            require_once "app/vistas/inicio/dashboard_tecnico.php";
        }
        
        require_once "app/vistas/footer.php";
    }

    // Obtener estadísticas del sistema
    private function getStatistics() {
        try {
            $bd = BasedeDatos::Conectar();
            $current_user = BaseControlador::getCurrentUser();
            
            $stats = array();
            
            if (BaseControlador::hasPermission('VIEW_ADMIN_PANEL')) {
                // Estadísticas para administradores
                
                // Total de servicios
                $query = $bd->query("SELECT COUNT(*) as total FROM SERVICE");
                $stats['total_servicios'] = $query->fetch(PDO::FETCH_OBJ)->total;
                
                // Servicios por estado
                $query = $bd->query("
                    SELECT ss.NAME_SERVICE_STATUS, COUNT(*) as cantidad 
                    FROM SERVICE s 
                    INNER JOIN SERVICE_STATUS ss ON ss.ID_SERVICE_STATUS = s.SERVICE_STATUS_ID_SERVICE_STATUS 
                    GROUP BY s.SERVICE_STATUS_ID_SERVICE_STATUS, ss.NAME_SERVICE_STATUS
                ");
                $stats['servicios_por_estado'] = $query->fetchAll(PDO::FETCH_OBJ);
                
                // Total de clientes
                $query = $bd->query("SELECT COUNT(*) as total FROM CUSTOMER WHERE STATUS = 1");
                $stats['total_clientes'] = $query->fetch(PDO::FETCH_OBJ)->total;
                
                // Total de empleados
                $query = $bd->query("SELECT COUNT(*) as total FROM EMPLOYEE WHERE STATUS = 1");
                $stats['total_empleados'] = $query->fetch(PDO::FETCH_OBJ)->total;
                
                // Servicios de hoy
                $query = $bd->query("
                    SELECT COUNT(*) as total 
                    FROM SERVICE 
                    WHERE DATE(PRESET_DT_HR) = CURDATE()
                ");
                $stats['servicios_hoy'] = $query->fetch(PDO::FETCH_OBJ)->total;
                
            } else {
                // Estadísticas para técnicos (solo sus servicios)
                
                // Servicios asignados al técnico
                $query = $bd->prepare("
                    SELECT COUNT(*) as total 
                    FROM SERVICE s 
                    INNER JOIN SRVIC_EMPLOYEE se ON se.SERVICE_ID_SERVICE = s.ID_SERVICE 
                    WHERE se.EMPLOYEE_ID_EMPLOYEE = ?
                ");
                $query->execute(array($current_user['id_employee']));
                $stats['mis_servicios'] = $query->fetch(PDO::FETCH_OBJ)->total;
                
                // Servicios pendientes del técnico
                $query = $bd->prepare("
                    SELECT COUNT(*) as total 
                    FROM SERVICE s 
                    INNER JOIN SRVIC_EMPLOYEE se ON se.SERVICE_ID_SERVICE = s.ID_SERVICE 
                    WHERE se.EMPLOYEE_ID_EMPLOYEE = ? 
                    AND s.SERVICE_STATUS_ID_SERVICE_STATUS = 1
                ");
                $query->execute(array($current_user['id_employee']));
                $stats['servicios_pendientes'] = $query->fetch(PDO::FETCH_OBJ)->total;
                
                // Servicios completados hoy
                $query = $bd->prepare("
                    SELECT COUNT(*) as total 
                    FROM SERVICE s 
                    INNER JOIN SRVIC_EMPLOYEE se ON se.SERVICE_ID_SERVICE = s.ID_SERVICE 
                    WHERE se.EMPLOYEE_ID_EMPLOYEE = ? 
                    AND s.SERVICE_STATUS_ID_SERVICE_STATUS = 3 
                    AND DATE(s.END_DT_HR) = CURDATE()
                ");
                $query->execute(array($current_user['id_employee']));
                $stats['completados_hoy'] = $query->fetch(PDO::FETCH_OBJ)->total;
                
                // Próximos servicios
                $query = $bd->prepare("
                    SELECT s.*, c.NAME_CUSTOMER 
                    FROM SERVICE s 
                    INNER JOIN SRVIC_EMPLOYEE se ON se.SERVICE_ID_SERVICE = s.ID_SERVICE 
                    INNER JOIN CUSTOMER c ON c.ID_CUSTOMER = s.CUSTOMER_ID_CUSTOMER 
                    WHERE se.EMPLOYEE_ID_EMPLOYEE = ? 
                    AND s.SERVICE_STATUS_ID_SERVICE_STATUS = 1 
                    AND s.PRESET_DT_HR >= NOW() 
                    ORDER BY s.PRESET_DT_HR ASC 
                    LIMIT 5
                ");
                $query->execute(array($current_user['id_employee']));
                $stats['proximos_servicios'] = $query->fetchAll(PDO::FETCH_OBJ);
            }
            
            return $stats;
            
        } catch (Exception $e) {
            return array(); // Retornar array vacío en caso de error
        }
    }
}