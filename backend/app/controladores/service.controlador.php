<?php
require_once "app/modelos/service.php";

class ServiceControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new Service();
    }

    /**
     * Método principal para cargar la vista de administración de servicios
     * Muestra el dashboard con servicios separados por estado
     */
    public function Inicio(){
        // Obtener los estados de servicio disponibles
        $estados = $this->modelo->ListarEstadosServicio();
        
        // Obtener datos para filtros
        $clientes = $this->modelo->ListarClientes();
        $empleados = $this->modelo->ListarEmpleados();
        
        // Procesar filtros si existen
        $filtros = $this->procesarFiltros();
        
        // Si hay filtros aplicados, usar el método con filtros
        if (!empty($filtros)) {
            $serviciosProgramados = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 1]), 15);
            $serviciosEjecucion = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 2]), 15);
            $serviciosFinalizados = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 3]), 15);
        } else {
            // Sin filtros, obtener por estado normal
            $serviciosProgramados = $this->modelo->ObtenerPorEstado(1, 15); // Estado 1: Programado
            $serviciosEjecucion = $this->modelo->ObtenerPorEstado(2, 15);   // Estado 2: En ejecución
            $serviciosFinalizados = $this->modelo->ObtenerPorEstado(3, 15); // Estado 3: Finalizado
        }
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/servicemanagement.php";
        require_once "app/vistas/footer.php";
    }

    /**
     * Método para obtener más servicios de un estado específico vía AJAX
     * Se llama cuando se presiona "Ver más" en alguna card
     */
    public function ObtenerMasServicios(){
        $estado = isset($_GET['estado']) ? (int)$_GET['estado'] : 0;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        if ($estado > 0) {
            // Aplicar filtros si existen
            $filtros = $this->procesarFiltros();
            
            if (!empty($filtros)) {
                $servicios = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => $estado]), $limit);
            } else {
                $servicios = $this->modelo->ObtenerPorEstado($estado, $limit, $offset); // Obtener todos
            }
            
            // Aplicar offset manualmente si es necesario
            if ($offset > 0) {
                $servicios = array_slice($servicios, $offset, $limit);
            }
            
            // Devolver JSON para AJAX
            header('Content-Type: application/json');
            echo json_encode($servicios);
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Estado no válido']);
        }
    }

    /**
     * Método para aplicar filtros desde el formulario
     */
    public function AplicarFiltros(){
        // Los filtros se procesan en el método Inicio()
        // Simplemente redirigir con los parámetros GET
        $this->Inicio();
    }

    /**
     * Método privado para procesar los filtros de la URL
     * @return array Filtros procesados
     */
    private function procesarFiltros(){
        $filtros = [];
        
        // Filtro por cliente
        if (isset($_GET['cliente']) && !empty($_GET['cliente'])) {
            $filtros['cliente'] = (int)$_GET['cliente'];
        }
        
        // Filtro por fecha desde
        if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = $_GET['fecha_desde'];
        }
        
        // Filtro por fecha hasta
        if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
        }
        
        // Filtro por empleado
        if (isset($_GET['empleado']) && !empty($_GET['empleado'])) {
            $filtros['empleado'] = (int)$_GET['empleado'];
        }
        
        return $filtros;
    }

    /**
     * Método para limpiar todos los filtros
     */
    public function LimpiarFiltros(){
        // Redirigir a la página principal sin parámetros
        header("location: ?c=service");
    }

    /**
     * Método para ver detalles de un servicio específico (opcional)
     */
    public function VerDetalle(){
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id > 0) {
            $servicio = $this->modelo->Obtener($id);
            
            if ($servicio) {
                require_once "app/vistas/header.php";
                require_once "app/vistas/service/servicedetail.php";
                require_once "app/vistas/footer.php";
            } else {
                header("location: ?c=service");
            }
        } else {
            header("location: ?c=service");
        }
    }

    /**
 * Mostrar formulario para crear nuevo servicio
 * Solo accesible para administradores
 */
public function NuevoServicio(){
    try {
        // Obtener datos necesarios para los selects del formulario
        $clientes = $this->modelo->ListarClientes();
        $empleados = $this->modelo->ObtenerEmpleadosDisponibles();
        $estados = $this->modelo->ObtenerEstadosDisponibles();
        
        // Variables para manejo de errores y mensajes
        $errores = [];
        $mensaje_exito = '';
        
        // Si se envió el formulario por POST, procesarlo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->ProcesarFormularioServicio();
            if ($resultado['success']) {
                $mensaje_exito = $resultado['mensaje'];
                // Limpiar el formulario después del éxito
                $_POST = [];
            } else {
                $errores = $resultado['errores'];
            }
        }
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceForm.php";
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $errores[] = "Error al cargar el formulario: " . $e->getMessage();
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceForm.php";
        require_once "app/vistas/footer.php";
    }
}

/**
 * Procesar el formulario de creación de servicio
 * @return array Resultado del procesamiento con éxito/errores
 */
private function ProcesarFormularioServicio(){
    $errores = [];
    $resultado = ['success' => false, 'errores' => [], 'mensaje' => ''];
    
    try {
        // Validar datos requeridos
        if (empty($_POST['customer_id_customer'])) {
            $errores[] = "Debe seleccionar un cliente";
        }
        
        if (empty($_POST['preset_dt_hr'])) {
            $errores[] = "Debe especificar la fecha y hora programada";
        }
        
        if (empty($_POST['empleado_encargado'])) {
            $errores[] = "Debe seleccionar un empleado encargado";
        }
        
        if (empty($_POST['service_status_id_service_status'])) {
            $errores[] = "Debe seleccionar un estado de servicio";
        }
        
        // Validar archivo PDF si se subió
        $archivo_subido = false;
        $ruta_archivo = '';
        
        if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            $validacion_archivo = $this->ValidarArchivoPDF($_FILES['archivo_pdf']);
            if (!$validacion_archivo['success']) {
                $errores = array_merge($errores, $validacion_archivo['errores']);
            } else {
                $archivo_subido = true;
                $ruta_archivo = $validacion_archivo['ruta'];
            }
        }
        
        // Si hay errores de validación, retornar
        if (!empty($errores)) {
            $resultado['errores'] = $errores;
            return $resultado;
        }
        
        // Validar que los IDs existan en la base de datos
        if (!$this->modelo->ValidarCliente($_POST['customer_id_customer'])) {
            $errores[] = "El cliente seleccionado no existe o no está activo";
        }
        
        if (!$this->modelo->ValidarEmpleado($_POST['empleado_encargado'])) {
            $errores[] = "El empleado encargado seleccionado no existe o no está activo";
        }
        
        if (!$this->modelo->ValidarEstadoServicio($_POST['service_status_id_service_status'])) {
            $errores[] = "El estado de servicio seleccionado no existe o no está activo";
        }
        
        // Validar empleados asistentes si se seleccionaron
        $empleados_asistentes = [];
        if (isset($_POST['empleados_asistentes']) && is_array($_POST['empleados_asistentes'])) {
            foreach ($_POST['empleados_asistentes'] as $id_asistente) {
                if (!$this->modelo->ValidarEmpleado($id_asistente)) {
                    $errores[] = "Uno de los empleados asistentes seleccionados no existe o no está activo";
                    break;
                }
                $empleados_asistentes[] = $id_asistente;
            }
        }
        
        if (!empty($errores)) {
            $resultado['errores'] = $errores;
            return $resultado;
        }
        
        // Preparar datos para crear el servicio
        $datos_servicio = [
            'notes' => !empty($_POST['notes']) ? $_POST['notes'] : null,
            'preset_dt_hr' => $_POST['preset_dt_hr'],
            'customer_id_customer' => (int)$_POST['customer_id_customer'],
            'service_status_id_service_status' => (int)$_POST['service_status_id_service_status']
        ];
        
        // Crear el servicio
        $id_servicio = $this->modelo->CrearServicio($datos_servicio);
        
        if (!$id_servicio) {
            throw new Exception("No se pudo crear el servicio");
        }
        
        // Asignar empleados
        $this->modelo->AsignarEmpleados(
            $id_servicio, 
            (int)$_POST['empleado_encargado'], 
            $empleados_asistentes
        );
        
        // Guardar archivo si se subió
        if ($archivo_subido) {
            $this->modelo->GuardarArchivoServicio($id_servicio, $ruta_archivo, 'PDF');
        }
        
        $resultado['success'] = true;
        $resultado['mensaje'] = "Servicio creado exitosamente. ID: " . $id_servicio;
        
    } catch (Exception $e) {
        $errores[] = "Error al procesar el formulario: " . $e->getMessage();
        $resultado['errores'] = $errores;
    }
    
    return $resultado;
}

/**
 * Validar y subir archivo PDF
 * @param array $archivo Datos del archivo de $_FILES
 * @return array Resultado de la validación
 */
private function ValidarArchivoPDF($archivo){
    $errores = [];
    $resultado = ['success' => false, 'errores' => [], 'ruta' => ''];
    
    try {
        // Verificar que no haya errores en la subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            switch ($archivo['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errores[] = "El archivo es demasiado grande";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errores[] = "El archivo se subió parcialmente";
                    break;
                default:
                    $errores[] = "Error al subir el archivo";
                    break;
            }
            $resultado['errores'] = $errores;
            return $resultado;
        }
        
        // Verificar tipo de archivo
        $tipos_permitidos = ['application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipo_mime = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($tipo_mime, $tipos_permitidos)) {
            $errores[] = "Solo se permiten archivos PDF";
        }
        
        // Verificar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            $errores[] = "El archivo debe tener extensión .pdf";
        }
        
        // Verificar tamaño (máximo 5MB)
        $tamaño_maximo = 5 * 1024 * 1024; // 5MB en bytes
        if ($archivo['size'] > $tamaño_maximo) {
            $errores[] = "El archivo no debe superar los 5MB";
        }
        
        if (!empty($errores)) {
            $resultado['errores'] = $errores;
            return $resultado;
        }
        
        // Crear directorio si no existe
        $directorio_destino = "uploads/servicios/";
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $nombre_archivo = uniqid('servicio_') . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $ruta_completa = $directorio_destino . $nombre_archivo;
        
        // Mover el archivo
        if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            $resultado['success'] = true;
            $resultado['ruta'] = $ruta_completa;
        } else {
            $errores[] = "Error al guardar el archivo en el servidor";
            $resultado['errores'] = $errores;
        }
        
    } catch (Exception $e) {
        $errores[] = "Error al procesar el archivo: " . $e->getMessage();
        $resultado['errores'] = $errores;
    }
    
    return $resultado;
}

//Nuevos métodos para vista técnicos 

/**
 * Vista principal para técnicos - mostrar servicios programados asignados
 * Filtra servicios donde el empleado actual es el encargado
 */
public function VistaTecnico(){
    try {
        // TODO: Obtener el ID del empleado de la sesión
        // Por ahora asumo que viene por GET, pero debería venir de la sesión del usuario logueado
        $id_empleado = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
        
        if ($id_empleado <= 0) {
            // Si no hay empleado especificado, mostrar error o redirigir
            $errores = ["No se ha especificado un empleado válido"];
            $servicios_programados = [];
        } else {
            // Obtener servicios programados asignados al empleado como encargado
            $servicios_programados = $this->modelo->ObtenerServiciosProgramadosEmpleado($id_empleado);
        }
        
        // Variables para la vista
        $titulo_pagina = "Mis Servicios Programados";
        $empleado_actual = $id_empleado;
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceTechView.php";
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $errores = ["Error al cargar los servicios: " . $e->getMessage()];
        $servicios_programados = [];
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceTechView.php";
        require_once "app/vistas/footer.php";
    }
}

/**
 * Procesar la acción de iniciar un servicio
 * Actualiza el estado y registra la fecha/hora de inicio
 */
public function IniciarServicio(){
    try {
        // Validar parámetros requeridos
        $id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // TODO: Obtener el ID del empleado de la sesión
        // Por ahora asumo que viene por GET, pero debería venir de la sesión
        $id_empleado = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
        
        if ($id_servicio <= 0) {
            throw new Exception("ID de servicio no válido");
        }
        
        if ($id_empleado <= 0) {
            throw new Exception("ID de empleado no válido");
        }
        
        // Validar que el empleado tenga permisos para iniciar este servicio
        if (!$this->modelo->ValidarEncargadoServicio($id_servicio, $id_empleado)) {
            throw new Exception("No tiene permisos para iniciar este servicio");
        }
        
        // Intentar iniciar el servicio
        $resultado = $this->modelo->IniciarServicio($id_servicio, $id_empleado);
        
        if ($resultado) {
            // Redirigir al formulario de completar servicio
            header("location: ?c=service&a=CompletarServicio&id=" . $id_servicio . "&empleado=" . $id_empleado);
        } else {
            throw new Exception("No se pudo iniciar el servicio");
        }
        
    } catch (Exception $e) {
        // En caso de error, redirigir de vuelta a la vista de técnico con mensaje de error
        $mensaje_error = urlencode($e->getMessage());
        $empleado = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
        header("location: ?c=service&a=VistaTecnico&empleado=" . $empleado . "&error=" . $mensaje_error);
    }
}

/**
 * Mostrar formulario para completar un servicio iniciado
 * Esta vista permitirá llenar los campos faltantes del servicio
 */
public function CompletarServicio(){
    try {
        $id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $id_empleado = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
        
        if ($id_servicio <= 0) {
            throw new Exception("ID de servicio no válido");
        }
        
        // Obtener detalles del servicio
        $servicio = $this->modelo->ObtenerDetalleServicioTecnico($id_servicio);
        
        if (!$servicio) {
            throw new Exception("Servicio no encontrado");
        }
        
        // Validar que el empleado tenga permisos
        if ($id_empleado > 0 && !$this->modelo->ValidarEncargadoServicio($id_servicio, $id_empleado)) {
            throw new Exception("No tiene permisos para acceder a este servicio");
        }
        
        // Variables para la vista
        $titulo_pagina = "Completar Servicio - " . $servicio->name_customer;
        $empleado_actual = $id_empleado;
        
        // Si se envió el formulario, procesarlo
        $mensaje_exito = '';
        $errores = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->ProcesarFormularioCompletar($id_servicio);
            if ($resultado['success']) {
                $mensaje_exito = $resultado['mensaje'];
            } else {
                $errores = $resultado['errores'];
            }
        }
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceComplete.php"; // Vista que crearías después
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $errores = [$e->getMessage()];
        $empleado_actual = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
        
        // Redirigir de vuelta a vista técnico con error
        $mensaje_error = urlencode($e->getMessage());
        header("location: ?c=service&a=VistaTecnico&empleado=" . $empleado_actual . "&error=" . $mensaje_error);
    }
}

/**
 * Procesar formulario de completar servicio
 * Actualiza los campos de inspección y finaliza el servicio
 * @param int $id_servicio ID del servicio a completar
 * @return array Resultado del procesamiento
 */
private function ProcesarFormularioCompletar($id_servicio){
    $resultado = ['success' => false, 'errores' => [], 'mensaje' => ''];
    
    try {
        $this->pdo->beginTransaction();
        
        // Validar y procesar datos del formulario
        $inspection_problems = isset($_POST['inspection_problems']) ? trim($_POST['inspection_problems']) : '';
        $inspection_location = isset($_POST['inspection_location']) ? trim($_POST['inspection_location']) : '';
        $inspection_methods = isset($_POST['inspection_methods']) ? trim($_POST['inspection_methods']) : '';
        $notes_adicionales = isset($_POST['notes_adicionales']) ? trim($_POST['notes_adicionales']) : '';
        
        // Obtener notas actuales si existen
        $servicio_actual = $this->modelo->Obtener($id_servicio);
        $notas_finales = $servicio_actual->notes;
        
        if (!empty($notes_adicionales)) {
            $notas_finales .= (!empty($notas_finales) ? "\n\n" : "") . "Notas de finalización: " . $notes_adicionales;
        }
        
        // Obtener ID del estado "Finalizado" o "Completado"
        $sql_estado = "SELECT id_service_status FROM SERVICE_STATUS 
                      WHERE (LOWER(name_service_status) LIKE '%finalizado%' 
                      OR LOWER(name_service_status) LIKE '%completado%'
                      OR LOWER(name_service_status) LIKE '%terminado%')
                      AND status = 1 LIMIT 1";
        $consulta_estado = $this->pdo->prepare($sql_estado);
        $consulta_estado->execute();
        $estado = $consulta_estado->fetch(PDO::FETCH_OBJ);
        
        if (!$estado) {
            // Si no existe, usar el estado 3 por defecto (según tu estructura actual)
            $id_estado_finalizado = 3;
        } else {
            $id_estado_finalizado = $estado->id_service_status;
        }
        
        // Actualizar el servicio con toda la información
        $sql = "UPDATE SERVICE SET 
                end_dt_hr = NOW(),
                inspection_problems = ?,
                inspection_location = ?,
                inspection_methods = ?,
                notes = ?,
                service_status_id_service_status = ?
                WHERE id_service = ?";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(array(
            $inspection_problems,
            $inspection_location,
            $inspection_methods,
            $notas_finales,
            $id_estado_finalizado,
            $id_servicio
        ));
        
        if ($consulta->rowCount() > 0) {
            $this->pdo->commit();
            $resultado['success'] = true;
            $resultado['mensaje'] = "Servicio completado exitosamente";
        } else {
            throw new Exception("No se pudo actualizar el servicio");
        }
        
    } catch (Exception $e) {
        $this->pdo->rollback();
        $resultado['errores'][] = "Error al completar servicio: " . $e->getMessage();
    }
    
    return $resultado;
}

/**
 * Método auxiliar para obtener servicios con filtros específicos para técnicos
 * Útil para futuras funcionalidades como historial, búsquedas, etc.
 */
public function ObtenerHistorialTecnico(){
    try {
        $id_empleado = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
        $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t');
        
        if ($id_empleado <= 0) {
            throw new Exception("ID de empleado no válido");
        }
        
        // Obtener servicios históricos del empleado
        $sql = "
        SELECT DISTINCT
            s.*,
            c.name_customer,
            c.address_customer,
            ss.name_service_status,
            CONCAT(e.name_employee, ' ', e.lastname_employee) as empleado_encargado
        FROM SERVICE s
        INNER JOIN CUSTOMER c ON s.customer_id_customer = c.id_customer
        INNER JOIN SERVICE_STATUS ss ON s.service_status_id_service_status = ss.id_service_status
        INNER JOIN SRVIC_EMPLOYEE se ON s.id_service = se.service_id_service
        INNER JOIN EMPLOYEE e ON se.employee_id_employee = e.id_employee
        INNER JOIN ROLE_IN_SERVICE ris ON se.role_in_service_id_role_in_service = ris.id_role_in_service
        WHERE se.employee_id_employee = :id_empleado
        AND LOWER(ris.name_role_in_service) LIKE '%encargado%'
        AND DATE(s.preset_dt_hr) BETWEEN :fecha_desde AND :fecha_hasta
        ORDER BY s.preset_dt_hr DESC
        ";
        
        $consulta = $this->pdo->prepare($sql);
        $consulta->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $consulta->bindParam(':fecha_desde', $fecha_desde, PDO::PARAM_STR);
        $consulta->bindParam(':fecha_hasta', $fecha_hasta, PDO::PARAM_STR);
        $consulta->execute();
        
        $historial_servicios = $consulta->fetchAll(PDO::FETCH_OBJ);
        
        // Variables para la vista
        $titulo_pagina = "Historial de Servicios";
        $empleado_actual = $id_empleado;
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceHistory.php"; // Vista que crearías después
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $errores = [$e->getMessage()];
        $historial_servicios = [];
        $empleado_actual = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceHistory.php";
        require_once "app/vistas/footer.php";
    }
}

}