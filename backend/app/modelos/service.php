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
            $consulta = $this->pdo->prepare("SELECT * FROM SERVICE_STATUS WHERE status = 1 ORDER BY id_service_status");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
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
                s.*,
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
        $this->pdo->commit();
        
        return $id_servicio;
    }catch(Exception $e){
        $this->pdo->rollback();
        throw new Exception("Error al crear servicio: " . $e->getMessage());
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



}