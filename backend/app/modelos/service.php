<?php

class Service{
    private $pdo;

    // Propiedades de la entidad SERVICE
    private $id_service;
    private $notes;
    private $preset_dt_hr;
    private $start_dt_hr;
    private $end_dt_hr;
    private $inspection_problems;
    private $inspection_location;
    private $inspection_methods;
    private $customer_id_customer;
    private $service_status_id_service_status;

    public function __CONSTRUCT(){
        $this->pdo = BasedeDatos::Conectar();
    }

    // Métodos GET y SET
    public function getIdService(): ?int {
        return $this->id_service;
    }
    public function setIdService(int $id): void {
        $this->id_service = $id;
    }
    public function getNotes(): ?string {
        return $this->notes;
    }
    public function setNotes(?string $notes): void {
        $this->notes = $notes;
    }
    public function getPresetDtHr(): ?string {
        return $this->preset_dt_hr;
    }
    public function setPresetDtHr(?string $datetime): void {
        $this->preset_dt_hr = $datetime;
    }
    public function getStartDtHr(): ?string {
        return $this->start_dt_hr;
    }
    public function setStartDtHr(?string $datetime): void {
        $this->start_dt_hr = $datetime;
    }
    public function getEndDtHr(): ?string {
        return $this->end_dt_hr;
    }
    public function setEndDtHr(?string $datetime): void {
        $this->end_dt_hr = $datetime;
    }
    public function getInspectionProblems(): ?string {
        return $this->inspection_problems;
    }
    public function setInspectionProblems(?string $problems): void {
        $this->inspection_problems = $problems;
    }
    public function getInspectionLocation(): ?string {
        return $this->inspection_location;
    }
    public function setInspectionLocation(?string $location): void {
        $this->inspection_location = $location;
    }
    public function getInspectionMethods(): ?string {
        return $this->inspection_methods;
    }
    public function setInspectionMethods(?string $methods): void {
        $this->inspection_methods = $methods;
    }
    public function getCustomerIdCustomer(): ?int {
        return $this->customer_id_customer;
    }
    public function setCustomerIdCustomer(int $customer_id): void {
        $this->customer_id_customer = $customer_id;
    }
    public function getServiceStatusIdServiceStatus(): ?int {
        return $this->service_status_id_service_status;
    }
    public function setServiceStatusIdServiceStatus(int $status_id): void {
        $this->service_status_id_service_status = $status_id;
    }

    /**
     * Obtener servicios por estado con límite opcional
     * @param int $status_id ID del estado del servicio
     * @param int|null $limit Límite de registros (null para todos)
     * @return array
     */
    public function ObtenerPorEstado($status_id, $limit = null, $offset = null){
    try {
        $sql = "
        SELECT
            s.id_service,
            s.notes,
            s.preset_dt_hr,
            s.start_dt_hr,
            s.end_dt_hr,
            s.inspection_problems,
            s.inspection_location,
            s.inspection_methods,
            s.customer_id_customer,
            c.name_customer,
            c.address_customer,
            c.whatsapp as customer_whatsapp,
            s.service_status_id_service_status,
            ss.name_service_status,
            GROUP_CONCAT(DISTINCT CONCAT(e.name_employee, ' ', e.lastname_employee) SEPARATOR ', ') as empleados_asignados
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        LEFT JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
        LEFT JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
        WHERE s.service_status_id_service_status = :status_id
        ";

        // Rango del mes actual
        $inicioMes = date('Y-m-01 00:00:00');
        $finMes = date('Y-m-t 23:59:59');

        // Filtro por fecha según estado
        switch ($status_id) {
            case 1:
                $sql .= " AND s.preset_dt_hr BETWEEN :inicio AND :fin ";
                $ordenCampo = "s.preset_dt_hr";
                break;
            case 2:
                $sql .= " AND s.start_dt_hr BETWEEN :inicio AND :fin ";
                $ordenCampo = "s.start_dt_hr";
                break;
            case 3:
                $sql .= " AND s.end_dt_hr BETWEEN :inicio AND :fin ";
                $ordenCampo = "s.end_dt_hr";
                break;
            default:
                $ordenCampo = "s.preset_dt_hr";
                break;
        }

        // Agrupar antes de ordenar
        $sql .= " GROUP BY s.id_service ";
        $sql .= " ORDER BY $ordenCampo ASC ";

        // Límite y offset
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':status_id', $status_id, PDO::PARAM_INT);

        if (in_array($status_id, [1, 2, 3])) {
            $consulta->bindParam(':inicio', $inicioMes, PDO::PARAM_STR);
            $consulta->bindParam(':fin', $finMes, PDO::PARAM_STR);
        }

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

    /**
     * Obtener servicios con filtros aplicados
     * @param array $filtros Array asociativo con filtros
     * @param int|null $limit Límite de registros
     * @return array
     */
    public function ObtenerConFiltros($filtros = [], $limit = null){
        try{
            $sql = "
            SELECT
                s.id_service,
                s.notes,
                s.preset_dt_hr,
                s.start_dt_hr,
                s.end_dt_hr,
                s.inspection_problems,
                s.inspection_location,
                s.inspection_methods,
                s.customer_id_customer,
                c.name_customer,
                c.address_customer,
                c.whatsapp as customer_whatsapp,
                s.service_status_id_service_status,
                ss.name_service_status,
                GROUP_CONCAT(DISTINCT CONCAT(e.name_employee, ' ', e.lastname_employee) SEPARATOR ', ') as empleados_asignados
            FROM SERVICE s
            INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
            INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
            LEFT JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
            LEFT JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
            ";
            
            $condiciones = [];
            $parametros = [];
            
            // Filtro por cliente
            if (!empty($filtros['cliente'])) {
                $condiciones[] = "s.customer_id_customer = ?";
                $parametros[] = $filtros['cliente'];
            }
            
            // Filtro por fecha desde
            if (!empty($filtros['fecha_desde'])) {
                $condiciones[] = "DATE(s.preset_dt_hr) >= ?";
                $parametros[] = $filtros['fecha_desde'];
            }
            
            // Filtro por fecha hasta
            if (!empty($filtros['fecha_hasta'])) {
                $condiciones[] = "DATE(s.preset_dt_hr) <= ?";
                $parametros[] = $filtros['fecha_hasta'];
            }
            
            // Filtro por empleado
            if (!empty($filtros['empleado'])) {
                $condiciones[] = "se.employee_id_employee = ?";
                $parametros[] = $filtros['empleado'];
            }
            
            // Filtro por estado
            if (!empty($filtros['estado'])) {
                $condiciones[] = "s.service_status_id_service_status = ?";
                $parametros[] = $filtros['estado'];
            }
            
            if (!empty($condiciones)) {
                $sql .= " WHERE " . implode(" AND ", $condiciones);
            }
            
            $sql .= " GROUP BY s.id_service ORDER BY s.preset_dt_hr ASC";
            
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            $consulta = $this->pdo->prepare($sql);
            $consulta->execute($parametros);
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    /**
     * Contar servicios por estado
     * @param int $status_id ID del estado del servicio
     * @return int
     */
    public function ContarPorEstado($status_id){
        try{
            $consulta = $this->pdo->prepare("SELECT COUNT(*) as total FROM SERVICE WHERE service_status_id_service_status = ?");
            $consulta->execute(array($status_id));
            $resultado = $consulta->fetch(PDO::FETCH_OBJ);
            return $resultado->total;
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    /**
     * Obtener todos los clientes activos para filtros
     * @return array
     */
    public function ListarClientes(){
        try{
            $consulta = $this->pdo->prepare("SELECT id_customer, name_customer FROM CUSTOMER WHERE status = 1 ORDER BY name_customer");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    /**
     * Obtener todos los empleados activos para filtros
     * @return array
     */
    public function ListarEmpleados(){
        try{
            $consulta = $this->pdo->prepare("
                SELECT id_employee, CONCAT(name_employee, ' ', lastname_employee) as nombre_completo 
                FROM EMPLOYEE 
                WHERE status = 1 
                ORDER BY name_employee
            ");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    /**
     * Obtener todos los estados de servicio activos
     * @return array
     */
    public function ListarEstadosServicio(){
    try{
        $consulta = $this->pdo->prepare("
            SELECT 
                id_service_status, 
                name_service_status, 
                description,
                status
            FROM SERVICE_STATUS 
            WHERE status = 1 
            ORDER BY id_service_status
        ");
        $consulta->execute();
        $resultado = $consulta->fetchAll(PDO::FETCH_OBJ);
        
        // Debug: verificar que la consulta devuelva datos válidos
        if (empty($resultado)) {
            error_log("ListarEstadosServicio: No se encontraron estados de servicio");
        }
        
        return $resultado;
    }catch(Exception $e){
        error_log("Error en ListarEstadosServicio: " . $e->getMessage());
        return []; // Devolver array vacío en caso de error
    }
}

    /**
     * Obtener un servicio específico por ID
     * @param int $id ID del servicio
     * @return object|false
     */
    public function Obtener($id){
        try{
        $consulta = $this->pdo->prepare("
        SELECT
            s.id_service,
            s.notes,
            s.preset_dt_hr,
            s.start_dt_hr,
            s.end_dt_hr,
            s.inspection_problems,
            s.inspection_location,
            s.inspection_methods,
            s.customer_id_customer,
            s.service_status_id_service_status,
            c.name_customer,
            c.address_customer,
            ss.name_service_status
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        WHERE s.id_service = ?
        ");
        $consulta->execute(array($id));
        return $consulta->fetch(PDO::FETCH_OBJ);
    }catch(Exception $e){
        die($e->getMessage());
    }
    }

    //Crear Nuevo Servicio
    /**
 * Crear nuevo servicio y estructura de directorios
 * @param array $datos Datos del servicio
 * @return int ID del servicio creado
 */

    public function CrearServicio($datos){
    try{
        $this->pdo->beginTransaction();
        
        $sql = "INSERT INTO SERVICE (
            notes, 
            preset_dt_hr, 
            customer_id_customer, 
            service_status_id_service_status
        ) VALUES (?, ?, ?, ?)";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(array(
            $datos['notes'],
            $datos['preset_dt_hr'],
            $datos['customer_id_customer'],
            $datos['service_status_id_service_status']
        ));
        
        $id_servicio = $this->pdo->lastInsertId();
        
        // Crear estructura de directorios para el servicio
        $this->CrearEstructuraDirectorios($id_servicio);
        
        $this->pdo->commit();
        
        return $id_servicio;
    }catch(Exception $e){
        $this->pdo->rollback();
        throw new Exception("Error al crear servicio: " . $e->getMessage());
    }
}

/**
 * Crear estructura de directorios para un servicio
 * @param int $id_servicio ID del servicio
 * @return bool
 */
private function CrearEstructuraDirectorios($id_servicio){
    try{
        // Directorio base del servicio
        $directorio_servicio = "uploads/service" . $id_servicio . "/";
        $directorio_docs = $directorio_servicio . "docs/";
        $directorio_img = $directorio_servicio . "img/";
        
        // Crear directorio base del servicio
        if (!is_dir($directorio_servicio)) {
            if (!mkdir($directorio_servicio, 0777, true)) {
                throw new Exception("No se pudo crear directorio base del servicio");
            }
            chmod($directorio_servicio, 0777);
        }
        
        // Crear subdirectorio para documentos
        if (!is_dir($directorio_docs)) {
            if (!mkdir($directorio_docs, 0777, true)) {
                throw new Exception("No se pudo crear directorio de documentos");
            }
            chmod($directorio_docs, 0777);
        }
        
        // Crear subdirectorio para imágenes
        if (!is_dir($directorio_img)) {
            if (!mkdir($directorio_img, 0777, true)) {
                throw new Exception("No se pudo crear directorio de imágenes");
            }
            chmod($directorio_img, 0777);
        }
        
        return true;
    }catch(Exception $e){
        throw new Exception("Error al crear estructura de directorios: " . $e->getMessage());
    }
}

/**
 * Asignar empleados a un servicio
 * @param int $id_servicio ID del servicio
 * @param int $empleado_encargado ID del empleado encargado
 * @param array $empleados_asistentes Array de IDs de empleados asistentes
 * @return bool
 */
public function AsignarEmpleados($id_servicio, $empleado_encargado, $empleados_asistentes = []){
    try{
        $this->pdo->beginTransaction();
        
        // Obtener ID del rol "Encargado" (asumiendo que existe)
        $sql_rol_encargado = "SELECT id_role_in_service FROM ROLE_IN_SERVICE 
                             WHERE LOWER(name_role_in_service) LIKE '%encargado%' 
                             AND status = 1 LIMIT 1";
        $consulta = $this->pdo->prepare($sql_rol_encargado);
        $consulta->execute();
        $rol_encargado = $consulta->fetch(PDO::FETCH_OBJ);
        
        if (!$rol_encargado) {
            throw new Exception("No se encontró el rol 'Encargado'");
        }
        
        // Asignar empleado encargado
        $sql_encargado = "INSERT INTO SRVIC_EMPLOYEE (
            service_id_service, 
            employee_id_employee, 
            role_in_service_id_role_in_service
        ) VALUES (?, ?, ?)";
        
        $consulta = $this->pdo->prepare($sql_encargado);
        $consulta->execute(array(
            $id_servicio,
            $empleado_encargado,
            $rol_encargado->id_role_in_service
        ));
        
        // Si hay asistentes, asignarlos
        if (!empty($empleados_asistentes)) {
            // Obtener ID del rol "Asistente"
            $sql_rol_asistente = "SELECT id_role_in_service FROM ROLE_IN_SERVICE 
                                 WHERE LOWER(name_role_in_service) LIKE '%asistente%' 
                                 AND status = 1 LIMIT 1";
            $consulta = $this->pdo->prepare($sql_rol_asistente);
            $consulta->execute();
            $rol_asistente = $consulta->fetch(PDO::FETCH_OBJ);
            
            if (!$rol_asistente) {
                throw new Exception("No se encontró el rol 'Asistente'");
            }
            
            // Insertar cada asistente
            $sql_asistente = "INSERT INTO SRVIC_EMPLOYEE (
                service_id_service, 
                employee_id_employee, 
                role_in_service_id_role_in_service
            ) VALUES (?, ?, ?)";
            
            $consulta = $this->pdo->prepare($sql_asistente);
            
            foreach ($empleados_asistentes as $id_asistente) {
                $consulta->execute(array(
                    $id_servicio,
                    $id_asistente,
                    $rol_asistente->id_role_in_service
                ));
            }
        }
        
        $this->pdo->commit();
        return true;
    }catch(Exception $e){
        $this->pdo->rollback();
        throw new Exception("Error al asignar empleados: " . $e->getMessage());
    }
}

/**
 * Guardar archivo del servicio
 * @param int $id_servicio ID del servicio
 * @param string $ruta_archivo Ruta del archivo guardado
 * @param string $tipo_archivo Tipo de archivo (PDF, IMG, etc.)
 * @return bool
 */
public function GuardarArchivoServicio($id_servicio, $ruta_archivo, $tipo_archivo = 'PDF'){
    try{
        $sql = "INSERT INTO SERVICE_FILE (
            service_id_service, 
            path_file, 
            file_type
        ) VALUES (?, ?, ?)";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(array(
            $id_servicio,
            $ruta_archivo,
            $tipo_archivo
        ));
        
        return true;
    }catch(Exception $e){
        throw new Exception("Error al guardar archivo: " . $e->getMessage());
    }
}

/**
 * Obtener empleados disponibles para asignar (solo activos)
 * @return array
 */
public function ObtenerEmpleadosDisponibles(){
    try{
        $sql = "SELECT 
            id_employee, 
            CONCAT(name_employee, ' ', lastname_employee) as nombre_completo,
            name_employee,
            lastname_employee
        FROM EMPLOYEE 
        WHERE status = 1 
        ORDER BY name_employee, lastname_employee";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener empleados: " . $e->getMessage());
    }
}

/**
 * Obtener estados de servicio disponibles (solo activos)
 * @return array
 */
public function ObtenerEstadosDisponibles(){
    try{
        $sql = "SELECT 
            id_service_status, 
            name_service_status, 
            description 
        FROM SERVICE_STATUS 
        WHERE status = 1 
        ORDER BY id_service_status";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener estados: " . $e->getMessage());
    }
}

/**
 * Validar si un cliente existe y está activo
 * @param int $id_cliente
 * @return bool
 */
public function ValidarCliente($id_cliente){
    try{
        $sql = "SELECT COUNT(*) as existe FROM CUSTOMER 
                WHERE id_customer = ? AND status = 1";
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(array($id_cliente));
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        return $resultado->existe > 0;
    }catch(Exception $e){
        return false;
    }
}

/**
 * Validar si un empleado existe y está activo
 * @param int $id_empleado
 * @return bool
 */
public function ValidarEmpleado($id_empleado){
    try{
        $sql = "SELECT COUNT(*) as existe FROM EMPLOYEE 
                WHERE id_employee = ? AND status = 1";
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(array($id_empleado));
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        return $resultado->existe > 0;
    }catch(Exception $e){
        return false;
    }
}

/**
 * Validar si un estado de servicio existe y está activo
 * @param int $id_estado
 * @return bool
 */
public function ValidarEstadoServicio($id_estado){
    try{
        $sql = "SELECT COUNT(*) as existe FROM SERVICE_STATUS 
                WHERE id_service_status = ? AND status = 1";
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(array($id_estado));
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        return $resultado->existe > 0;
    }catch(Exception $e){
        return false;
    }
}



//Nuevos métodos para vista de técnicos

// NUEVOS MÉTODOS AGREGADOS AL MODELO service.php

/**
 * Obtener servicios programados asignados a un empleado como encargado
 * @param int $id_empleado ID del empleado encargado
 * @return array Servicios programados del empleado
 */
public function ObtenerServiciosProgramadosEmpleado($id_empleado){
    try{
        $sql = "
        SELECT DISTINCT
            s.id_service,
            s.notes,
            s.preset_dt_hr,
            s.start_dt_hr,
            s.end_dt_hr,
            s.customer_id_customer,
            c.name_customer,
            c.address_customer,
            c.whatsapp as customer_whatsapp,
            c.tel as customer_tel,
            s.service_status_id_service_status,
            ss.name_service_status,
            CONCAT(e_enc.name_employee, ' ', e_enc.lastname_employee) as empleado_encargado
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        INNER JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
        INNER JOIN EMPLOYEE e_enc ON se.employee_id_employee = e_enc.id_employee
        INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE se.employee_id_employee = :id_empleado
        AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
        AND s.service_status_id_service_status = 1
        AND s.preset_dt_hr >= CURDATE()
        ORDER BY s.preset_dt_hr ASC
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener servicios programados del empleado: " . $e->getMessage());
    }
}

/**
 * Iniciar un servicio - actualizar estado a "Iniciado" y registrar fecha/hora de inicio
 * @param int $id_servicio ID del servicio a iniciar
 * @param int $id_empleado ID del empleado que inicia (para validación)
 * @return bool Resultado de la operación
 */
public function IniciarServicio($id_servicio, $id_empleado){
    try{
        $this->pdo->beginTransaction();
        
        // Primero validar que el empleado sea el encargado del servicio
        $sql_validar = "
        SELECT COUNT(*) as es_encargado
        FROM SERVICE s
        INNER JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
        INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE s.id_service = :id_servicio
        AND se.employee_id_employee = :id_empleado
        AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
        AND s.service_status_id_service_status = 1
        ";
        
        $consulta_validar = $this->pdo->prepare($sql_validar);
        $consulta_validar->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $consulta_validar->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta_validar->execute();
        
        $validacion = $consulta_validar->fetch(PDO::FETCH_OBJ);
        
        if ($validacion->es_encargado == 0) {
            throw new Exception("El empleado no está autorizado para iniciar este servicio o el servicio no está en estado programado");
        }
        
        // Obtener ID del estado "En ejecución" o "Iniciado"
        $sql_estado = "SELECT id_service_status FROM SERVICE_STATUS 
                      WHERE (LOWER(name_service_status) LIKE '%ejecuci%' 
                      OR LOWER(name_service_status) LIKE '%iniciado%'
                      OR LOWER(name_service_status) LIKE '%proceso%')
                      AND status = 1 LIMIT 1";
        $consulta_estado = $this->pdo->prepare($sql_estado);
        $consulta_estado->execute();
        $estado = $consulta_estado->fetch(PDO::FETCH_OBJ);
        
        if (!$estado) {
            // Si no existe, usar el estado 2 por defecto (según tu estructura actual)
            $id_estado_iniciado = 2;
        } else {
            $id_estado_iniciado = $estado->id_service_status;
        }
        
        // Actualizar el servicio con la fecha/hora de inicio y nuevo estado
        $sql_actualizar = "
        UPDATE SERVICE 
        SET start_dt_hr = NOW(),
            service_status_id_service_status = :nuevo_estado
        WHERE id_service = :id_servicio
        ";
        
        $consulta_actualizar = $this->pdo->prepare($sql_actualizar);
        $consulta_actualizar->bindParam(':nuevo_estado', $id_estado_iniciado, PDO::PARAM_INT);
        $consulta_actualizar->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $consulta_actualizar->execute();
        
        if ($consulta_actualizar->rowCount() == 0) {
            throw new Exception("No se pudo actualizar el servicio");
        }
        
        $this->pdo->commit();
        return true;
    }catch(Exception $e){
        $this->pdo->rollback();
        throw new Exception("Error al iniciar servicio: " . $e->getMessage());
    }
}

/**
 * Validar si un empleado es encargado de un servicio específico
 * @param int $id_servicio ID del servicio
 * @param int $id_empleado ID del empleado
 * @return bool True si es encargado, False en caso contrario
 */
public function ValidarEncargadoServicio($id_servicio, $id_empleado){
    try{
        $sql = "
        SELECT COUNT(*) as es_encargado
        FROM SERVICE s
        INNER JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
        INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE s.id_service = :id_servicio
        AND se.employee_id_employee = :id_empleado
        AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $consulta->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->execute();
        
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        return $resultado->es_encargado > 0;
    }catch(Exception $e){
        return false;
    }
}

/**
 * Obtener detalles de un servicio específico para técnicos
 * Incluye información completa del cliente y estado
 * @param int $id_servicio ID del servicio
 * @return object|false Datos del servicio o false si no existe
 */
public function ObtenerDetalleServicioTecnico($id_servicio){
    try{
        $sql = "
        SELECT 
            s.*,
            c.name_customer,
            c.address_customer,
            c.whatsapp as customer_whatsapp,
            c.tel as customer_tel,
            c.mail as customer_mail,
            ss.name_service_status,
            GROUP_CONCAT(
                CONCAT(e.name_employee, ' ', e.lastname_employee, ' (', ris.name_role_in_service, ')')
                SEPARATOR ', '
            ) as empleados_asignados
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        LEFT JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
        LEFT JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
        LEFT JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE s.id_service = :id_servicio
        GROUP BY s.id_service
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener detalles del servicio: " . $e->getMessage());
    }
}


//NUEVOS MÉTODOS PARA FORMULARIO TECNICOS
/**
 * Obtener información completa del servicio para el formulario de finalización
 * Incluye cliente, encargado, asistentes, fecha programada
 * @param int $id_servicio ID del servicio
 * @return object|false Información completa del servicio
 */
public function ObtenerServicioParaFinalizacion($id_servicio){
    try{
        $sql = "
        SELECT 
            s.id_service,
            s.notes,
            s.preset_dt_hr,
            s.start_dt_hr,
            s.end_dt_hr,
            s.inspection_problems,
            s.inspection_location,
            s.inspection_methods,
            s.customer_id_customer,
            s.service_status_id_service_status,
            c.name_customer,
            c.address_customer,
            c.whatsapp as customer_whatsapp,
            c.tel as customer_tel,
            c.mail as customer_mail,
            ss.name_service_status,
            -- Obtener encargado
            (SELECT CONCAT(e.name_employee, ' ', e.lastname_employee) 
             FROM SRVIC_EMPLOYEE se 
             INNER JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
             INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
             WHERE se.service_id_service = s.id_service 
             AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
             LIMIT 1) as encargado_nombre,
            -- Obtener ID del encargado
            (SELECT e.id_employee 
             FROM SRVIC_EMPLOYEE se 
             INNER JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
             INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
             WHERE se.service_id_service = s.id_service 
             AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
             LIMIT 1) as encargado_id
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        WHERE s.id_service = :id_servicio
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        if (!$resultado) {
            return false;
        }
        
        return $resultado;
    }catch(Exception $e){
        throw new Exception("Error al obtener servicio para finalización: " . $e->getMessage());
    }
}

/**
 * Obtener lista de técnicos asistentes asignados al servicio
 * @param int $id_servicio ID del servicio
 * @return array Lista de asistentes
 */
public function ObtenerAsistentesServicio($id_servicio){
    try{
        $sql = "
        SELECT 
            e.id_employee,
            e.name_employee,
            e.lastname_employee,
            CONCAT(e.name_employee, ' ', e.lastname_employee) as nombre_completo
        FROM SRVIC_EMPLOYEE se
        INNER JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
        INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE se.service_id_service = :id_servicio
        AND LOWER(ris.name_role_in_service) LIKE '%asistente%'
        GROUP BY e.id_employee, e.name_employee, e.lastname_employee
        ORDER BY e.name_employee, e.lastname_employee
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener asistentes del servicio: " . $e->getMessage());
    }
}

/**
 * Obtener categorías de un servicio
 */
private function obtenerCategoriasDeServicio($id_servicio){
    try {
        $sql = "SELECT GROUP_CONCAT(sc.name_service_category SEPARATOR ', ') as categorias
                FROM SRVIC_TYPE st
                INNER JOIN SERVICE_CATEGORY sc ON st.service_category_id_service_category = sc.id_service_category
                WHERE st.service_id_service = ?";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute([$id_servicio]);
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        return $resultado ? $resultado->categorias : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Obtener métodos de aplicación de un servicio
 */
private function obtenerMetodosDeAplicacion($id_servicio){
    try {
        $sql = "SELECT GROUP_CONCAT(am.name_application_method SEPARATOR ', ') as metodos
                FROM SRVIC_SYSTEM ss_method
                INNER JOIN APPLICATION_METHOD am ON ss_method.application_method_id_application_method = am.id_application_method
                WHERE ss_method.service_id_service = ?";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute([$id_servicio]);
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        return $resultado ? $resultado->metodos : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Obtener todas las categorías de servicio disponibles (público)
 * Para usar en formularios
 */
public function ObtenerCategoriasServicio(){
    try{
        $sql = "SELECT 
            id_service_category, 
            name_service_category, 
            description 
        FROM SERVICE_CATEGORY 
        WHERE status = 1 
        ORDER BY name_service_category";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener categorías de servicio: " . $e->getMessage());
    }
}

/**
 * Obtener todos los métodos de aplicación disponibles (público)
 * Para usar en formularios
 */
public function ObtenerMetodosAplicacion(){
    try{
        $sql = "SELECT 
            id_application_method, 
            name_application_method, 
            description 
        FROM APPLICATION_METHOD 
        WHERE status = 1 
        ORDER BY name_application_method";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener métodos de aplicación: " . $e->getMessage());
    }
}

/**
 * Finalizar servicio y actualizar toda la información
 * @param int $id_servicio ID del servicio
 * @param array $datos_finalizacion Datos del formulario
 * @return bool Resultado de la operación
 */
public function FinalizarServicio($id_servicio, $datos_finalizacion){
    try{
        $this->pdo->beginTransaction();
        
        // 1. Obtener ID del estado "Finalizado"
        $sql_estado = "SELECT id_service_status FROM SERVICE_STATUS 
                      WHERE (LOWER(name_service_status) LIKE '%finalizado%' 
                      OR LOWER(name_service_status) LIKE '%completado%'
                      OR LOWER(name_service_status) LIKE '%terminado%')
                      AND status = 1 LIMIT 1";
        $consulta_estado = $this->pdo->prepare($sql_estado);
        $consulta_estado->execute();
        $estado = $consulta_estado->fetch(PDO::FETCH_OBJ);
        
        if (!$estado) {
            // Si no existe, usar el estado 3 por defecto
            $id_estado_finalizado = 3;
        } else {
            $id_estado_finalizado = $estado->id_service_status;
        }
        
        // 2. Actualizar el servicio con fecha de finalización y estado
        $sql_service = "UPDATE SERVICE SET 
                       end_dt_hr = NOW(),
                       inspection_problems = ?,
                       inspection_location = ?,
                       inspection_methods = ?,
                       notes = ?,
                       service_status_id_service_status = ?
                       WHERE id_service = ?";
        
        $consulta_service = $this->pdo->prepare($sql_service);
        $consulta_service->execute(array(
            $datos_finalizacion['inspection_problems'],
            $datos_finalizacion['inspection_location'], 
            $datos_finalizacion['inspection_methods'],
            $datos_finalizacion['notes'],
            $id_estado_finalizado,
            $id_servicio
        ));
        
        // 3. Actualizar técnicos asistentes si se proporcionaron
        if (isset($datos_finalizacion['asistentes']) && is_array($datos_finalizacion['asistentes'])) {
            $this->ActualizarAsistentesServicio($id_servicio, $datos_finalizacion['asistentes']);
        }
        
        // 4. Insertar categorías de servicio seleccionadas
        if (isset($datos_finalizacion['categorias']) && is_array($datos_finalizacion['categorias'])) {
            $this->InsertarCategoriasServicio($id_servicio, $datos_finalizacion['categorias']);
        }
        
        // 5. Insertar métodos de aplicación seleccionados
        if (isset($datos_finalizacion['metodos']) && is_array($datos_finalizacion['metodos'])) {
            $this->InsertarMetodosAplicacion($id_servicio, $datos_finalizacion['metodos']);
        }
        
        $this->pdo->commit();
        return true;
    }catch(Exception $e){
        $this->pdo->rollback();
        throw new Exception("Error al finalizar servicio: " . $e->getMessage());
    }
}

/**
 * Actualizar lista de técnicos asistentes del servicio
 * @param int $id_servicio ID del servicio
 * @param array $nuevos_asistentes Array de IDs de empleados asistentes
 * @return bool
 */
private function ActualizarAsistentesServicio($id_servicio, $nuevos_asistentes){
    try{
        // Obtener ID del rol "Asistente"
        $sql_rol = "SELECT id_role_in_service FROM ROLE_IN_SERVICE 
                   WHERE LOWER(name_role_in_service) LIKE '%asistente%' 
                   AND status = 1 LIMIT 1";
        $consulta_rol = $this->pdo->prepare($sql_rol);
        $consulta_rol->execute();
        $rol_asistente = $consulta_rol->fetch(PDO::FETCH_OBJ);
        
        if (!$rol_asistente) {
            throw new Exception("No se encontró el rol 'Asistente'");
        }
        
        // Eliminar asistentes actuales
        $sql_delete = "DELETE FROM SRVIC_EMPLOYEE 
                      WHERE service_id_service = ? 
                      AND role_in_service_id_role_in_service = ?";
        $consulta_delete = $this->pdo->prepare($sql_delete);
        $consulta_delete->execute(array($id_servicio, $rol_asistente->id_role_in_service));
        
        // Insertar nuevos asistentes
        if (!empty($nuevos_asistentes)) {
            $sql_insert = "INSERT INTO SRVIC_EMPLOYEE 
                          (service_id_service, employee_id_employee, role_in_service_id_role_in_service) 
                          VALUES (?, ?, ?)";
            $consulta_insert = $this->pdo->prepare($sql_insert);
            
            foreach ($nuevos_asistentes as $id_asistente) {
                if (!empty($id_asistente)) {
                    $consulta_insert->execute(array(
                        $id_servicio, 
                        $id_asistente, 
                        $rol_asistente->id_role_in_service
                    ));
                }
            }
        }
        
        return true;
    }catch(Exception $e){
        throw new Exception("Error al actualizar asistentes: " . $e->getMessage());
    }
}

/**
 * Insertar categorías de servicio seleccionadas
 * @param int $id_servicio ID del servicio
 * @param array $categorias Array de IDs de categorías
 * @return bool
 */
private function InsertarCategoriasServicio($id_servicio, $categorias){
    try{
        // Eliminar categorías existentes
        $sql_delete = "DELETE FROM SRVIC_TYPE WHERE service_id_service = ?";
        $consulta_delete = $this->pdo->prepare($sql_delete);
        $consulta_delete->execute(array($id_servicio));
        
        // Insertar nuevas categorías
        if (!empty($categorias)) {
            $sql_insert = "INSERT INTO SRVIC_TYPE 
                          (service_id_service, service_category_id_service_category) 
                          VALUES (?, ?)";
            $consulta_insert = $this->pdo->prepare($sql_insert);
            
            foreach ($categorias as $id_categoria) {
                if (!empty($id_categoria)) {
                    $consulta_insert->execute(array($id_servicio, $id_categoria));
                }
            }
        }
        
        return true;
    }catch(Exception $e){
        throw new Exception("Error al insertar categorías: " . $e->getMessage());
    }
}

/**
 * Insertar métodos de aplicación seleccionados
 * @param int $id_servicio ID del servicio
 * @param array $metodos Array de IDs de métodos
 * @return bool
 */
private function InsertarMetodosAplicacion($id_servicio, $metodos){
    try{
        // Eliminar métodos existentes
        $sql_delete = "DELETE FROM SRVIC_SYSTEM WHERE service_id_service = ?";
        $consulta_delete = $this->pdo->prepare($sql_delete);
        $consulta_delete->execute(array($id_servicio));
        
        // Insertar nuevos métodos
        if (!empty($metodos)) {
            $sql_insert = "INSERT INTO SRVIC_SYSTEM 
                          (service_id_service, application_method_id_application_method) 
                          VALUES (?, ?)";
            $consulta_insert = $this->pdo->prepare($sql_insert);
            
            foreach ($metodos as $id_metodo) {
                if (!empty($id_metodo)) {
                    $consulta_insert->execute(array($id_servicio, $id_metodo));
                }
            }
        }
        
        return true;
    }catch(Exception $e){
        throw new Exception("Error al insertar métodos de aplicación: " . $e->getMessage());
    }
}

/**
 * Guardar múltiples imágenes del servicio
 * @param int $id_servicio ID del servicio
 * @param array $rutas_imagenes Array de rutas de imágenes guardadas
 * @return bool
 */
public function GuardarImagenesServicio($id_servicio, $rutas_imagenes){
    try{
        if (empty($rutas_imagenes)) {
            return true; // No hay imágenes que guardar
        }
        
        $sql = "INSERT INTO SERVICE_FILE 
               (service_id_service, path_file, file_type) 
               VALUES (?, ?, 'IMG')";
        
        $consulta = $this->pdo->prepare($sql);
        
        foreach ($rutas_imagenes as $ruta) {
            $consulta->execute(array($id_servicio, $ruta));
        }
        
        return true;
    }catch(Exception $e){
        throw new Exception("Error al guardar imágenes: " . $e->getMessage());
    }
}

/**
 * Obtener servicios en estado "Iniciado/En Ejecución" asignados a un empleado como encargado
 * Estos son servicios que ya fueron iniciados pero aún no finalizados
 * @param int $id_empleado ID del empleado encargado
 * @return array Servicios en ejecución del empleado
 */
public function ObtenerServiciosIniciadosEmpleado($id_empleado){
    try{
        $sql = "
        SELECT DISTINCT
            s.id_service,
            s.notes,
            s.preset_dt_hr,
            s.start_dt_hr,
            s.end_dt_hr,
            s.customer_id_customer,
            c.name_customer,
            c.address_customer,
            c.whatsapp as customer_whatsapp,
            c.tel as customer_tel,
            s.service_status_id_service_status,
            ss.name_service_status,
            CONCAT(e_enc.name_employee, ' ', e_enc.lastname_employee) as empleado_encargado,
            -- Calcular tiempo transcurrido desde el inicio
            TIMESTAMPDIFF(MINUTE, s.start_dt_hr, NOW()) as minutos_transcurridos
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        INNER JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
        INNER JOIN EMPLOYEE e_enc ON se.employee_id_employee = e_enc.id_employee
        INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE se.employee_id_employee = :id_empleado
        AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
        AND s.service_status_id_service_status = 2
        AND s.start_dt_hr IS NOT NULL
        AND s.end_dt_hr IS NULL
        ORDER BY s.start_dt_hr DESC
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener servicios iniciados del empleado: " . $e->getMessage());
    }
}

/**
 * Contar servicios por estado para un empleado específico
 * Útil para mostrar estadísticas en el dashboard del técnico
 * @param int $id_empleado ID del empleado
 * @param int $estado_servicio ID del estado a contar (1=Programado, 2=Iniciado, 3=Finalizado)
 * @return int Cantidad de servicios
 */
public function ContarServiciosEmpleadoPorEstado($id_empleado, $estado_servicio){
    try{
        $sql = "
        SELECT COUNT(DISTINCT s.id_service) as total
        FROM SERVICE s
        INNER JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
        INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE se.employee_id_employee = :id_empleado
        AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
        AND s.service_status_id_service_status = :estado_servicio
        ";
        
        // Agregar filtros adicionales según el estado
        switch ($estado_servicio) {
            case 1: // Programados - solo futuros
                $sql .= " AND s.preset_dt_hr >= CURDATE() ";
                break;
            case 2: // Iniciados - que no estén finalizados
                $sql .= " AND s.start_dt_hr IS NOT NULL AND s.end_dt_hr IS NULL ";
                break;
            case 3: // Finalizados - del mes actual
                $sql .= " AND s.end_dt_hr >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ";
                break;
        }
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->bindParam(':estado_servicio', $estado_servicio, PDO::PARAM_INT);
        $consulta->execute();
        
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        return (int)$resultado->total;
    }catch(Exception $e){
        return 0; // En caso de error, retornar 0
    }
}

/**
 * Obtener información completa de un servicio para edición/reprogramación
 * @param int $id_servicio ID del servicio
 * @return object|false Información completa del servicio
 */
public function ObtenerServicioCompleto($id_servicio){
    try{
        $sql = "
        SELECT 
            s.id_service,
            s.notes,
            s.preset_dt_hr,
            s.start_dt_hr,
            s.end_dt_hr,
            s.inspection_problems,
            s.inspection_location,
            s.inspection_methods,
            s.customer_id_customer,
            s.service_status_id_service_status,
            c.name_customer,
            c.address_customer,
            c.whatsapp as customer_whatsapp,
            c.tel as customer_tel,
            ss.name_service_status
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        WHERE s.id_service = :id_servicio
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener servicio completo: " . $e->getMessage());
    }
}

/**
 * Obtener el empleado encargado de un servicio específico
 * @param int $id_servicio ID del servicio
 * @return object|false Información del empleado encargado
 */
public function ObtenerEncargadoServicio($id_servicio){
    try{
        $sql = "
        SELECT 
            e.id_employee,
            e.name_employee,
            e.lastname_employee,
            CONCAT(e.name_employee, ' ', e.lastname_employee) as nombre_completo
        FROM SRVIC_EMPLOYEE se
        INNER JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
        INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE se.service_id_service = :id_servicio
        AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
        LIMIT 1
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener encargado del servicio: " . $e->getMessage());
    }
}

/**
 * Actualizar información básica de un servicio
 * @param int $id_servicio ID del servicio a actualizar
 * @param array $datos_servicio Array con los datos a actualizar
 * @return bool Resultado de la operación
 */
public function ActualizarServicio($id_servicio, $datos_servicio){
    try{
        $this->pdo->beginTransaction();
        
        $sql = "UPDATE SERVICE SET 
                notes = ?, 
                preset_dt_hr = ?, 
                customer_id_customer = ?, 
                service_status_id_service_status = ?
                WHERE id_service = ?";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(array(
            $datos_servicio['notes'],
            $datos_servicio['preset_dt_hr'],
            $datos_servicio['customer_id_customer'],
            $datos_servicio['service_status_id_service_status'],
            $id_servicio
        ));
        
        if ($consulta->rowCount() == 0) {
            throw new Exception("No se pudo actualizar el servicio o no se encontró");
        }
        
        $this->pdo->commit();
        return true;
    }catch(Exception $e){
        $this->pdo->rollback();
        throw new Exception("Error al actualizar servicio: " . $e->getMessage());
    }
}

/**
 * Actualizar los empleados asignados a un servicio (encargado y asistentes)
 * @param int $id_servicio ID del servicio
 * @param int $nuevo_encargado ID del nuevo empleado encargado
 * @param array $nuevos_asistentes Array de IDs de nuevos empleados asistentes
 * @return bool Resultado de la operación
 */
public function ActualizarEmpleadosServicio($id_servicio, $nuevo_encargado, $nuevos_asistentes = []){
    try{
        $this->pdo->beginTransaction();
        
        // Obtener IDs de roles
        $sql_rol_encargado = "SELECT id_role_in_service FROM ROLE_IN_SERVICE 
                             WHERE LOWER(name_role_in_service) LIKE '%encargado%' 
                             AND status = 1 LIMIT 1";
        $consulta = $this->pdo->prepare($sql_rol_encargado);
        $consulta->execute();
        $rol_encargado = $consulta->fetch(PDO::FETCH_OBJ);
        
        $sql_rol_asistente = "SELECT id_role_in_service FROM ROLE_IN_SERVICE 
                             WHERE LOWER(name_role_in_service) LIKE '%asistente%' 
                             AND status = 1 LIMIT 1";
        $consulta = $this->pdo->prepare($sql_rol_asistente);
        $consulta->execute();
        $rol_asistente = $consulta->fetch(PDO::FETCH_OBJ);
        
        if (!$rol_encargado) {
            throw new Exception("No se encontró el rol 'Encargado'");
        }
        
        // Eliminar todas las asignaciones actuales del servicio
        $sql_delete = "DELETE FROM SRVIC_EMPLOYEE WHERE service_id_service = ?";
        $consulta_delete = $this->pdo->prepare($sql_delete);
        $consulta_delete->execute(array($id_servicio));
        
        // Insertar nuevo empleado encargado
        $sql_encargado = "INSERT INTO SRVIC_EMPLOYEE (
            service_id_service, 
            employee_id_employee, 
            role_in_service_id_role_in_service
        ) VALUES (?, ?, ?)";
        
        $consulta_encargado = $this->pdo->prepare($sql_encargado);
        $consulta_encargado->execute(array(
            $id_servicio,
            $nuevo_encargado,
            $rol_encargado->id_role_in_service
        ));
        
        // Insertar nuevos asistentes si existen y hay rol definido
        if (!empty($nuevos_asistentes) && $rol_asistente) {
            $sql_asistente = "INSERT INTO SRVIC_EMPLOYEE (
                service_id_service, 
                employee_id_employee, 
                role_in_service_id_role_in_service
            ) VALUES (?, ?, ?)";
            
            $consulta_asistente = $this->pdo->prepare($sql_asistente);
            
            foreach ($nuevos_asistentes as $id_asistente) {
                if (!empty($id_asistente)) {
                    $consulta_asistente->execute(array(
                        $id_servicio,
                        $id_asistente,
                        $rol_asistente->id_role_in_service
                    ));
                }
            }
        }
        
        $this->pdo->commit();
        return true;
    }catch(Exception $e){
        $this->pdo->rollback();
        throw new Exception("Error al actualizar empleados del servicio: " . $e->getMessage());
    }
}

/**
 * Obtener el ID del estado "Programado" o "Pendiente" para asignación automática
 * @return int ID del estado por defecto para nuevos servicios
 */
public function ObtenerEstadoProgramadoPorDefecto(){
    try{
        $sql = "SELECT id_service_status FROM SERVICE_STATUS 
                WHERE (LOWER(name_service_status) LIKE '%programado%' 
                OR LOWER(name_service_status) LIKE '%pendiente%')
                AND status = 1 
                ORDER BY id_service_status ASC 
                LIMIT 1";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        if ($resultado) {
            return (int)$resultado->id_service_status;
        } else {
            // Si no encuentra estado específico, devolver 1 como fallback
            return 1;
        }
    }catch(Exception $e){
        // En caso de error, devolver 1 como fallback
        return 1;
    }
}

/**
 * Obtener servicios con información completa para la vista de tabla administrativa
 * Versión corregida con LIMIT y OFFSET
 */
public function ObtenerServiciosTablaAdmin($filtros = [], $limit = 30, $offset = 0){
    try{
        $sql = "
        SELECT DISTINCT
            s.id_service,
            s.notes,
            s.preset_dt_hr,
            s.start_dt_hr,
            s.end_dt_hr,
            s.customer_id_customer,
            s.service_status_id_service_status,
            c.name_customer,
            c.address_customer,
            c.whatsapp as customer_whatsapp,
            ss.name_service_status
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        ";
        
        // Si hay filtro de empleado, necesitamos hacer JOIN con las tablas de empleados
        if (!empty($filtros['empleado'])) {
            $sql .= " INNER JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service ";
        }
        
        $condiciones = [];
        $parametros = [];
        
        // Aplicar filtros
        if (!empty($filtros['cliente'])) {
            $condiciones[] = "s.customer_id_customer = ?";
            $parametros[] = $filtros['cliente'];
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $condiciones[] = "DATE(s.preset_dt_hr) >= ?";
            $parametros[] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $condiciones[] = "DATE(s.preset_dt_hr) <= ?";
            $parametros[] = $filtros['fecha_hasta'];
        }
        
        if (!empty($filtros['estado'])) {
            $condiciones[] = "s.service_status_id_service_status = ?";
            $parametros[] = $filtros['estado'];
        }
        
        if (!empty($filtros['numero_servicio'])) {
            $condiciones[] = "s.id_service = ?";
            $parametros[] = $filtros['numero_servicio'];
        }
        
        // CORREGIDO: Filtro por empleado
        if (!empty($filtros['empleado'])) {
            $condiciones[] = "se.employee_id_employee = ?";
            $parametros[] = $filtros['empleado'];
        }
        
        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY s.id_service DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($parametros);
        $servicios = $consulta->fetchAll(PDO::FETCH_OBJ);
        
        // Obtener información adicional para cada servicio
        foreach ($servicios as $servicio) {
            $servicio->tecnico_encargado = $this->obtenerTecnicoEncargado($servicio->id_service);
            $servicio->tecnicos_asistentes = $this->obtenerTecnicosAsistentes($servicio->id_service);
            $servicio->categorias_servicio = $this->obtenerCategoriasDeServicio($servicio->id_service);
            $servicio->metodos_aplicacion = $this->ObtenerMetodosDeAplicacion($servicio->id_service);
            
            $croquis_info = $this->verificarCroquis($servicio->id_service);
            $servicio->tiene_croquis = $croquis_info['tiene_croquis'];
            $servicio->ruta_croquis = $croquis_info['ruta_croquis'];
        }
        
        return $servicios;
        
    }catch(Exception $e){
        error_log("Error en ObtenerServiciosTablaAdmin: " . $e->getMessage());
        return [];
    }
}

/**
 * Verificar si un servicio tiene croquis
 */
private function verificarCroquis($id_servicio){
    try {
        $sql = "SELECT COUNT(*) as tiene_croquis, 
                       (SELECT path_file FROM SERVICE_FILE 
                        WHERE service_id_service = ? AND file_type = 'PDF' 
                        LIMIT 1) as ruta_croquis
                FROM SERVICE_FILE 
                WHERE service_id_service = ? AND file_type = 'PDF'";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute([$id_servicio, $id_servicio]);
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        return [
            'tiene_croquis' => $resultado ? (int)$resultado->tiene_croquis : 0,
            'ruta_croquis' => $resultado ? $resultado->ruta_croquis : null
        ];
    } catch (Exception $e) {
        return ['tiene_croquis' => 0, 'ruta_croquis' => null];
    }
}

/**
 * Obtener técnico encargado de un servicio
 */
private function obtenerTecnicoEncargado($id_servicio){
    try {
        $sql = "SELECT CONCAT(e.name_employee, ' ', e.lastname_employee) as nombre
                FROM SRVIC_EMPLOYEE se 
                INNER JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
                INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
                WHERE se.service_id_service = ? 
                AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
                LIMIT 1";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute([$id_servicio]);
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        return $resultado ? $resultado->nombre : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Obtener técnicos asistentes de un servicio
 */
private function obtenerTecnicosAsistentes($id_servicio){
    try {
        $sql = "SELECT GROUP_CONCAT(CONCAT(e.name_employee, ' ', e.lastname_employee) SEPARATOR ', ') as nombres
                FROM SRVIC_EMPLOYEE se 
                INNER JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
                INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
                WHERE se.service_id_service = ? 
                AND LOWER(ris.name_role_in_service) LIKE '%asistente%'";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute([$id_servicio]);
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        return $resultado ? $resultado->nombres : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Contar total de servicios para paginación
 */
public function ContarServiciosTablaAdmin($filtros = []){
    try{
        $sql = "
        SELECT COUNT(DISTINCT s.id_service) as total
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        ";
        
        // Si hay filtro de empleado, agregar JOIN
        if (!empty($filtros['empleado'])) {
            $sql .= " INNER JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service ";
        }
        
        $condiciones = [];
        $parametros = [];
        
        // Aplicar los mismos filtros
        if (!empty($filtros['cliente'])) {
            $condiciones[] = "s.customer_id_customer = ?";
            $parametros[] = $filtros['cliente'];
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $condiciones[] = "DATE(s.preset_dt_hr) >= ?";
            $parametros[] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $condiciones[] = "DATE(s.preset_dt_hr) <= ?";
            $parametros[] = $filtros['fecha_hasta'];
        }
        
        if (!empty($filtros['estado'])) {
            $condiciones[] = "s.service_status_id_service_status = ?";
            $parametros[] = $filtros['estado'];
        }
        
        if (!empty($filtros['numero_servicio'])) {
            $condiciones[] = "s.id_service = ?";
            $parametros[] = $filtros['numero_servicio'];
        }
        
        // CORREGIDO: Incluir filtro por empleado en el conteo
        if (!empty($filtros['empleado'])) {
            $condiciones[] = "se.employee_id_employee = ?";
            $parametros[] = $filtros['empleado'];
        }
        
        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($parametros);
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        return (int)$resultado->total;
        
    }catch(Exception $e){
        error_log("Error al contar servicios: " . $e->getMessage());
        return 0;
    }
}

/**
 * Verificar si un servicio tiene archivos PDF (croquis) asociados
 * @param int $id_servicio ID del servicio
 * @return array Información sobre los archivos PDF del servicio
 */
public function ObtenerArchivosPDFServicio($id_servicio){
    try{
        $sql = "SELECT * FROM SERVICE_FILE 
                WHERE service_id_service = ? 
                AND file_type = 'PDF' 
                ORDER BY id_service_file DESC";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(array($id_servicio));
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }catch(Exception $e){
        throw new Exception("Error al obtener archivos PDF del servicio: " . $e->getMessage());
    }
}


}