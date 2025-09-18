<?php
/**
 * Modelo para obtener datos completos de un servicio para generar PDF
 * Incluye información del cliente, empleados, categorías, métodos y archivos
 */
class ServicePdfModel {
    private $conn;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }
    
    /**
     * Obtiene todos los datos necesarios para generar el PDF de un servicio
     * @param int $serviceId ID del servicio
     * @return array|false Datos del servicio o false si no existe
     */
    public function getServiceDataForPdf($serviceId) {
        try {
            // Consulta principal: datos del servicio y cliente
            $query = "
                SELECT 
                    s.ID_SERVICE,
                    s.NOTES,
                    s.PRESET_DT_HR,
                    s.START_DT_HR,
                    s.END_DT_HR,
                    s.INSPECTION_PROBLEMS,
                    s.INSPECTION_LOCATION,
                    s.INSPECTION_METHODS,
                    -- Datos del cliente
                    c.ID_CUSTOMER,
                    c.NAME_CUSTOMER,
                    c.ADDRESS_CUSTOMER,
                    c.DOC_NUM as CUSTOMER_DOC,
                    c.WHATSAPP as CUSTOMER_WHATSAPP,
                    c.TEL as CUSTOMER_TEL,
                    c.MAIL as CUSTOMER_MAIL,
                    td_customer.NAME_TYPE_DOC as CUSTOMER_DOC_TYPE,
                    -- Estado del servicio
                    ss.NAME_SERVICE_STATUS,
                    ss.DESCRIPTION as STATUS_DESCRIPTION
                FROM SERVICE s
                INNER JOIN CUSTOMER c ON s.CUSTOMER_ID_CUSTOMER = c.ID_CUSTOMER
                INNER JOIN TYPE_DOC td_customer ON c.TYPE_DOC_ID_TYPE_DOC = td_customer.ID_TYPE_DOC
                INNER JOIN SERVICE_STATUS ss ON s.SERVICE_STATUS_ID_SERVICE_STATUS = ss.ID_SERVICE_STATUS
                WHERE s.ID_SERVICE = :service_id
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
            $stmt->execute();
            
            $serviceData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$serviceData) {
                return false; // Servicio no encontrado
            }
            
            // Obtener empleados asignados al servicio
            $serviceData['employees'] = $this->getServiceEmployees($serviceId);
            
            // Obtener categorías del servicio
            $serviceData['categories'] = $this->getServiceCategories($serviceId);
            
            // Obtener métodos de aplicación
            $serviceData['application_methods'] = $this->getServiceApplicationMethods($serviceId);
            
            // Obtener archivos asociados
            $serviceData['files'] = $this->getServiceFiles($serviceId);
            
            return $serviceData;
            
        } catch (PDOException $e) {
            error_log("Error al obtener datos del servicio para PDF: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene los empleados asignados al servicio con sus roles
     * @param int $serviceId
     * @return array
     */
    private function getServiceEmployees($serviceId) {
        $query = "
            SELECT 
                e.ID_EMPLOYEE,
                e.NAME_EMPLOYEE,
                e.LASTNAME_EMPLOYEE,
                e.DOC_NUM as EMPLOYEE_DOC,
                e.WHATSAPP as EMPLOYEE_WHATSAPP,
                e.TEL as EMPLOYEE_TEL,
                e.MAIL as EMPLOYEE_MAIL,
                -- Rol del empleado en la empresa
                re.NAME_ROLE_EMPLOYEE,
                re.DESCRIPTION as ROLE_DESCRIPTION,
                -- Rol específico en este servicio
                ris.NAME_ROLE_IN_SERVICE,
                ris.DESCRIPTION as SERVICE_ROLE_DESCRIPTION
            FROM SRVIC_EMPLOYEE se
            INNER JOIN EMPLOYEE e ON se.EMPLOYEE_ID_EMPLOYEE = e.ID_EMPLOYEE
            INNER JOIN ROLE_EMPLOYEE re ON e.ROLE_EMPLOYEE_ID_ROLE_EMPLOYEE = re.ID_ROLE_EMPLOYEE
            INNER JOIN ROLE_IN_SERVICE ris ON se.ROLE_IN_SERVICE_ID_ROLE_IN_SERVICE = ris.ID_ROLE_IN_SERVICE
            WHERE se.SERVICE_ID_SERVICE = :service_id
            AND e.STATUS = 1
            ORDER BY ris.NAME_ROLE_IN_SERVICE, e.NAME_EMPLOYEE
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene las categorías de servicio
     * @param int $serviceId
     * @return array
     */
    private function getServiceCategories($serviceId) {
        $query = "
            SELECT 
                sc.ID_SERVICE_CATEGORY,
                sc.NAME_SERVICE_CATEGORY,
                sc.DESCRIPTION
            FROM SRVIC_TYPE st
            INNER JOIN SERVICE_CATEGORY sc ON st.SERVICE_CATEGORY_ID_SERVICE_CATEGORY = sc.ID_SERVICE_CATEGORY
            WHERE st.SERVICE_ID_SERVICE = :service_id
            AND sc.STATUS = 1
            ORDER BY sc.NAME_SERVICE_CATEGORY
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene los métodos de aplicación del servicio
     * @param int $serviceId
     * @return array
     */
    private function getServiceApplicationMethods($serviceId) {
        $query = "
            SELECT 
                am.ID_APPLICATION_METHOD,
                am.NAME_APPLICATION_METHOD,
                am.DESCRIPTION
            FROM SRVIC_SYSTEM ss
            INNER JOIN APPLICATION_METHOD am ON ss.APPLICATION_METHOD_ID_APPLICATION_METHOD = am.ID_APPLICATION_METHOD
            WHERE ss.SERVICE_ID_SERVICE = :service_id
            AND am.STATUS = 1
            ORDER BY am.NAME_APPLICATION_METHOD
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene los archivos asociados al servicio
     * @param int $serviceId
     * @return array
     */
    private function getServiceFiles($serviceId) {
        $query = "
            SELECT 
                ID_SERVICE_FILE,
                FILE_TYPE,
                PATH_FILE
            FROM SERVICE_FILE
            WHERE SERVICE_ID_SERVICE = :service_id
            ORDER BY FILE_TYPE, PATH_FILE
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Genera el nombre del archivo PDF para el servicio
     * @param array $serviceData Datos del servicio
     * @return string Nombre del archivo
     */
    public function generatePdfFileName($serviceData) {
        $serviceId = $serviceData['ID_SERVICE'];
        $customerName = $serviceData['NAME_CUSTOMER'];
        
        // Limpiar el nombre del cliente para usar en el nombre del archivo
        $cleanCustomerName = $this->sanitizeFileName($customerName);
        
        return "service{$serviceId}-{$cleanCustomerName}.pdf";
    }
    
    /**
     * Sanitiza un string para usar como nombre de archivo
     * @param string $filename
     * @return string
     */
    private function sanitizeFileName($filename) {
        // Remover caracteres especiales y espacios
        $clean = preg_replace('/[^a-zA-Z0-9\-_]/', '', $filename);
        // Limitar longitud
        return substr($clean, 0, 50);
    }
    
    /**
     * Genera la ruta de directorio donde se guardará el PDF
     * @param int $serviceId
     * @return string
     */
    public function getServicePdfDirectory($serviceId) {
        return "uploads/service{$serviceId}/docs/";
    }
    
    /**
     * Crea el directorio si no existe
     * @param string $directory
     * @return bool
     */
    public function ensureDirectoryExists($directory) {
        if (!is_dir($directory)) {
            return mkdir($directory, 0755, true);
        }
        return true;
    }
}