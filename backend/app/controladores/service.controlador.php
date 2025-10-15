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
    
    // Si hay filtros aplicados, usar el método con filtros SIN LÍMITE
    if (!empty($filtros)) {
        $serviciosProgramados = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 1])); // Sin límite
        $serviciosEjecucion = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 2])); // Sin límite
        $serviciosFinalizados = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 3])); // Sin límite
    } else {
        // Sin filtros, obtener por estado normal SIN LÍMITE
        $serviciosProgramados = $this->modelo->ObtenerPorEstado(1); // Estado 1: Programado - Sin límite
        $serviciosEjecucion = $this->modelo->ObtenerPorEstado(2);   // Estado 2: En ejecución - Sin límite
        $serviciosFinalizados = $this->modelo->ObtenerPorEstado(3); // Estado 3: Finalizado - Sin límite
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
        // Validar datos requeridos (REMOVIDO: validación del estado)
        if (empty($_POST['customer_id_customer'])) {
            $errores[] = "Debe seleccionar un cliente";
        }
        
        if (empty($_POST['preset_dt_hr'])) {
            $errores[] = "Debe especificar la fecha y hora programada";
        }
        
        if (empty($_POST['empleado_encargado'])) {
            $errores[] = "Debe seleccionar un empleado encargado";
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
        
        // MODIFICADO: Obtener estado automáticamente
        $estado_automatico = $this->modelo->ObtenerEstadoProgramadoPorDefecto();
        
        // Preparar datos para crear el servicio
        $datos_servicio = [
            'notes' => !empty($_POST['notes']) ? $_POST['notes'] : null,
            'preset_dt_hr' => $_POST['preset_dt_hr'],
            'customer_id_customer' => (int)$_POST['customer_id_customer'],
            'service_status_id_service_status' => $estado_automatico // MODIFICADO: Estado automático
        ];
        
        // Crear el servicio (esto ya crea la estructura de directorios)
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
        
        // Procesar archivo PDF si se subió (ahora con el ID del servicio)
        if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            $validacion_archivo = $this->ValidarArchivoPDF($_FILES['archivo_pdf'], $id_servicio);
            if (!$validacion_archivo['success']) {
                // Si falla la subida del PDF, no fallar todo el proceso
                $resultado['mensaje'] = "Servicio creado exitosamente (ID: " . $id_servicio . "), pero hubo un problema con el archivo PDF: " . implode(', ', $validacion_archivo['errores']);
            } else {
                // Guardar referencia del archivo en base de datos
                $this->modelo->GuardarArchivoServicio($id_servicio, $validacion_archivo['ruta'], 'PDF');
            }
        }
        
        $resultado['success'] = true;
        if (empty($resultado['mensaje'])) {
            $resultado['mensaje'] = "Servicio creado exitosamente. ID: " . $id_servicio;
        }
        
    } catch (Exception $e) {
        $errores[] = "Error al procesar el formulario: " . $e->getMessage();
        $resultado['errores'] = $errores;
    }
    
    return $resultado;
}

/**
 * Validar y subir archivo PDF (usado al programar servicio)
 * @param array $archivo Datos del archivo de $_FILES
 * @param int $id_servicio ID del servicio para determinar el directorio
 * @return array Resultado de la validación
 */
private function ValidarArchivoPDF($archivo, $id_servicio = null){
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
        
        // Determinar directorio destino
        if ($id_servicio) {
            // Si ya se creó el servicio, usar su directorio específico
            $directorio_destino = "uploads/service" . $id_servicio . "/docs/";
        } else {
            // Si aún no se crea el servicio, usar directorio temporal
            $directorio_destino = "uploads/temp/docs/";
            if (!is_dir($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
                chmod($directorio_destino, 0777);
            }
        }
        
        // Verificar que el directorio existe
        if (!is_dir($directorio_destino)) {
            $errores[] = "Directorio de destino no existe: " . $directorio_destino;
            $resultado['errores'] = $errores;
            return $resultado;
        }
        
        // Generar nombre único para el archivo
        $nombre_archivo = uniqid('doc_') . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $ruta_completa = $directorio_destino . $nombre_archivo;
        
        // Mover el archivo
        if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            chmod($ruta_completa, 0666);
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
        // NUEVO: Obtener el ID del empleado de la sesión automáticamente
        $id_empleado = $this->obtenerIdEmpleadoDesdeLogin();
        
        if ($id_empleado <= 0) {
            // Mostrar error específico si no se puede identificar al empleado
            $errores = ["No se pudo identificar al empleado. Por favor, cierre sesión e inicie sesión nuevamente."];
            $servicios_programados = [];
            $servicios_iniciados = [];
            $total_programados = 0;
            $total_iniciados = 0;
            $total_finalizados_mes = 0;
        } else {
            // Obtener servicios normalmente
            $servicios_programados = $this->modelo->ObtenerServiciosProgramadosEmpleado($id_empleado);
            $servicios_iniciados = $this->modelo->ObtenerServiciosIniciadosEmpleado($id_empleado);
            
            // Obtener estadísticas
            $total_programados = $this->modelo->ContarServiciosEmpleadoPorEstado($id_empleado, 1);
            $total_iniciados = $this->modelo->ContarServiciosEmpleadoPorEstado($id_empleado, 2);
            $total_finalizados_mes = $this->modelo->ContarServiciosEmpleadoPorEstado($id_empleado, 3);
        }
        
        // Variables para la vista
        $titulo_pagina = "Panel de Servicios - Técnico";
        $empleado_actual = $id_empleado;
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceTechView.php";
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $errores = ["Error al cargar los servicios: " . $e->getMessage()];
        $servicios_programados = [];
        $servicios_iniciados = [];
        $total_programados = 0;
        $total_iniciados = 0;
        $total_finalizados_mes = 0;
        $empleado_actual = 0;
        
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
        $id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // NUEVO: Obtener empleado de la sesión
        $id_empleado = $this->obtenerIdEmpleadoDesdeLogin();
        
        if ($id_servicio <= 0) {
            throw new Exception("ID de servicio no válido");
        }
        
        if ($id_empleado <= 0) {
            throw new Exception("No se pudo identificar al empleado");
        }
        
        // Validar permisos y procesar
        if (!$this->modelo->ValidarEncargadoServicio($id_servicio, $id_empleado)) {
            throw new Exception("No tiene permisos para iniciar este servicio");
        }
        
        $resultado = $this->modelo->IniciarServicio($id_servicio, $id_empleado);
        
        if ($resultado) {
            header("location: ?c=service&a=CompletarServicio&id=" . $id_servicio);
        } else {
            throw new Exception("No se pudo iniciar el servicio");
        }
        
    } catch (Exception $e) {
        $mensaje_error = urlencode($e->getMessage());
        header("location: ?c=service&a=VistaTecnico&error=" . $mensaje_error);
    }
}

/**
 * Mostrar formulario para completar un servicio iniciado
 * Esta vista permitirá llenar los campos faltantes del servicio
 */
public function CompletarServicio(){
    try {
        $id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // NUEVO: Obtener empleado de la sesión
        $id_empleado = $this->obtenerIdEmpleadoDesdeLogin();
        
        if ($id_servicio <= 0) {
            throw new Exception("ID de servicio no válido");
        }
        
        if ($id_empleado <= 0) {
            throw new Exception("No se pudo identificar al empleado");
        }
        
        // Obtener información completa del servicio
        $servicio = $this->modelo->ObtenerServicioParaFinalizacion($id_servicio);
        
        if (!$servicio) {
            throw new Exception("Servicio no encontrado");
        }
        
        // Debug: verificar qué propiedades tiene el objeto servicio
        // Puedes comentar esta línea después de verificar
        // error_log("Propiedades del servicio: " . print_r($servicio, true));
        
        // Validar que el empleado tenga permisos (si se especificó)
        if ($id_empleado > 0 && !$this->modelo->ValidarEncargadoServicio($id_servicio, $id_empleado)) {
            throw new Exception("No tiene permisos para completar este servicio");
        }
        
        // Validar que el servicio esté en estado "En ejecución" (estado 2)
        // Usar una verificación más segura
        $estado_servicio = isset($servicio->service_status_id_service_status) ? 
                          $servicio->service_status_id_service_status : 0;
        
        if ($estado_servicio != 2) {
            throw new Exception("El servicio no está en estado de ejecución. Estado actual: " . 
                              (isset($servicio->name_service_status) ? $servicio->name_service_status : 'Desconocido'));
        }
        
        // Obtener datos necesarios para el formulario
        $asistentes_actuales = $this->modelo->obtenerAsistentesServicio($id_servicio);
        $empleados_disponibles = $this->modelo->obtenerEmpleadosDisponibles();
        $categorias_servicio = $this->modelo->ObtenerCategoriasServicio();
        $metodos_aplicacion = $this->modelo->ObtenerMetodosAplicacion();
        
        // Variables para la vista
        $titulo_pagina = "Completar Servicio - " . $servicio->name_customer;
        $empleado_actual = $id_empleado;
        
        // Procesar formulario si se envió
        $mensaje_exito = '';
        $errores = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->ProcesarFormularioFinalizacion($id_servicio);
            if ($resultado['success']) {
                $mensaje_exito = $resultado['mensaje'];
                // Redirigir a vista de técnico después del éxito
                header("location: ?c=service&a=VistaTecnico&empleado=" . $id_empleado . "&success=" . urlencode($resultado['mensaje']));
                exit;
            } else {
                $errores = $resultado['errores'];
            }
        }
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceTechForm.php";
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $errores = [$e->getMessage()];
        $empleado_actual = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;
        
        // En lugar de redirigir, mostrar el error en la misma página
        // para evitar problemas con headers
        $titulo_pagina = "Error al cargar servicio";
        require_once "app/vistas/header.php";
        ?>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0">Error al cargar servicio</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="alert alert-danger">
                    <h4><i class="icon fas fa-ban"></i> Error!</h4>
                    <?= htmlspecialchars($e->getMessage()) ?>
                </div>
                <a href="?c=service&a=VistaTecnico&empleado=<?= $empleado_actual ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver a Mis Servicios
                </a>
            </div>
        </section>
        <?php
        require_once "app/vistas/footer.php";
    }
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

//NUEVOS METODOS PARA FORMULARIO TECNICOS

/**
 * Procesar formulario de finalización de servicio
 * Guarda toda la información y finaliza el servicio
 * @param int $id_servicio ID del servicio a finalizar
 * @return array Resultado del procesamiento
 */
private function ProcesarFormularioFinalizacion($id_servicio){
    $resultado = ['success' => false, 'errores' => [], 'mensaje' => ''];
    
    try {
        // Validar campos requeridos mínimos
        $inspection_problems = isset($_POST['inspection_problems']) ? trim($_POST['inspection_problems']) : '';
        $inspection_location = isset($_POST['inspection_location']) ? trim($_POST['inspection_location']) : '';
        $inspection_methods = isset($_POST['inspection_methods']) ? trim($_POST['inspection_methods']) : '';
        $notes_finalizacion = isset($_POST['notes_finalizacion']) ? trim($_POST['notes_finalizacion']) : '';
        
        // Obtener notas actuales del servicio de forma segura
        $servicio_actual = $this->modelo->ObtenerServicioParaFinalizacion($id_servicio);
        $notas_finales = '';
        
        // Verificar si existen notas actuales
        if ($servicio_actual && isset($servicio_actual->notes) && !empty($servicio_actual->notes)) {
            $notas_finales = $servicio_actual->notes;
        }
        
        // Agregar notas de finalización si existen
        if (!empty($notes_finalizacion)) {
            $notas_finales .= (!empty($notas_finales) ? "\n\n" : "") . "Notas de finalización: " . $notes_finalizacion;
        }
        
        // Procesar imágenes si se subieron
        $rutas_imagenes = [];
        if (isset($_FILES['imagenes_servicio']) && !empty($_FILES['imagenes_servicio']['name'][0])) {
            $resultado_imagenes = $this->ProcesarImagenesServicio($id_servicio, $_FILES['imagenes_servicio']);
            if (!$resultado_imagenes['success']) {
                $resultado['errores'] = array_merge($resultado['errores'], $resultado_imagenes['errores']);
                return $resultado;
            }
            $rutas_imagenes = $resultado_imagenes['rutas'];
        }
        
        // Validar formulario
        $errores_validacion = $this->ValidarFormularioFinalizacion($_POST);
        if (!empty($errores_validacion)) {
            $resultado['errores'] = $errores_validacion;
            return $resultado;
        }
        
        // Preparar datos para finalización
        $datos_finalizacion = [
            'inspection_problems' => $inspection_problems,
            'inspection_location' => $inspection_location,
            'inspection_methods' => $inspection_methods,
            'notes' => $notas_finales,
            'asistentes' => isset($_POST['asistentes']) ? $_POST['asistentes'] : [],
            'categorias' => isset($_POST['categorias_servicio']) ? $_POST['categorias_servicio'] : [],
            'metodos' => isset($_POST['metodos_aplicacion']) ? $_POST['metodos_aplicacion'] : []
        ];
        
        // Finalizar servicio en base de datos
        $finalizado = $this->modelo->FinalizarServicio($id_servicio, $datos_finalizacion);
        
        if ($finalizado) {
            // Guardar imágenes si se procesaron correctamente
            if (!empty($rutas_imagenes)) {
                $this->modelo->GuardarImagenesServicio($id_servicio, $rutas_imagenes);
            }
            
            $resultado['success'] = true;
            $resultado['mensaje'] = "Servicio finalizado exitosamente. ID: " . $id_servicio;
            
            // Generar PDF automáticamente al finalizar servicio
            try {
                require_once 'servicePdf.controlador.php';
                
                // Usar tu método estático de conexión
                $conexion = BasedeDatos::Conectar();
                
                if ($conexion) {
                    $pdfController = new ServicePdfController($conexion);
                    $resultadoPdf = $pdfController->generateServicePdf($id_servicio);
                    
                    if ($resultadoPdf['success']) {
                        require_once __DIR__ . '/WhatsappController.php';
                        
                        $stmt = $conexion->prepare("
                            SELECT c.WHATSAPP, c.ID_CUSTOMER 
                            FROM CUSTOMER c 
                            INNER JOIN SERVICE s ON s.CUSTOMER_ID_CUSTOMER = c.ID_CUSTOMER 
                            WHERE s.ID_SERVICE = ?
                        ");
                        $stmt->execute([$id_servicio]);
                        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($customer && !empty($customer['WHATSAPP'])) {
                            $fullPath = $resultadoPdf['file_path'];
                            $relativePath = str_replace(__DIR__ . '/../../uploads/', '', $fullPath);
                            $relativePath = str_replace('uploads/', '', $relativePath);

                            $whatsappController = new WhatsappController();
                            $whatsappResult = $whatsappController->sendServicePDF(
                                $id_servicio,
                                $customer['ID_CUSTOMER'],
                                $customer['WHATSAPP'],
                                $resultadoPdf['file_name'],
                                $relativePath
                            );
                            
                            if ($whatsappResult['success']) {
                                error_log("✅ WhatsApp enviado a " . $customer['WHATSAPP']);
                            }
                        }
                    }

                    if (!$resultadoPdf['success']) {
                        error_log("Error al generar PDF automático para servicio $id_servicio: " . $resultadoPdf['message']);
                    }
                }
                
            } catch (Exception $e) {
                error_log("Excepción al generar PDF automático: " . $e->getMessage());
            }
            
        } else {
            throw new Exception("No se pudo finalizar el servicio");
        }
        
    } catch (Exception $e) {
        $resultado['errores'][] = "Error al finalizar servicio: " . $e->getMessage();
    }
    
    return $resultado;
}

/**
 * Procesar y guardar imágenes del servicio
 * @param int $id_servicio ID del servicio
 * @param array $archivos_imagenes Array de archivos de $_FILES
 * @return array Resultado del procesamiento con rutas de imágenes
 */
private function ProcesarImagenesServicio($id_servicio, $archivos_imagenes){
    $resultado = ['success' => false, 'errores' => [], 'rutas' => []];
    
    try {
        // El directorio ya debe existir desde la creación del servicio
        $directorio_imagenes = "uploads/service" . $id_servicio . "/img/";
        
        // Verificar que el directorio existe
        if (!is_dir($directorio_imagenes)) {
            throw new Exception("El directorio de imágenes no existe. Contacte al administrador.");
        }
        
        // Verificar permisos de escritura
        if (!is_writable($directorio_imagenes)) {
            throw new Exception("El directorio de imágenes no tiene permisos de escritura.");
        }
        
        // Tipos de imagen permitidos
        $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Tamaño máximo por imagen (10MB)
        $tamaño_maximo = 10 * 1024 * 1024;
        
        $rutas_guardadas = [];
        $total_archivos = count($archivos_imagenes['name']);
        
        // Procesar cada imagen
        for ($i = 0; $i < $total_archivos; $i++) {
            // Verificar si se subió archivo
            if ($archivos_imagenes['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue; // Saltar archivos vacíos
            }
            
            // Verificar errores de subida
            if ($archivos_imagenes['error'][$i] !== UPLOAD_ERR_OK) {
                switch ($archivos_imagenes['error'][$i]) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $resultado['errores'][] = "Imagen " . ($i + 1) . " es demasiado grande. Límite: " . ini_get('upload_max_filesize');
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $resultado['errores'][] = "Imagen " . ($i + 1) . " se subió parcialmente";
                        break;
                    default:
                        $resultado['errores'][] = "Error al subir imagen " . ($i + 1);
                        break;
                }
                continue;
            }
            
            // Verificar que el archivo temporal existe
            if (!file_exists($archivos_imagenes['tmp_name'][$i])) {
                $resultado['errores'][] = "Archivo temporal no encontrado para imagen " . ($i + 1);
                continue;
            }
            
            // Verificar tipo de archivo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $tipo_mime = finfo_file($finfo, $archivos_imagenes['tmp_name'][$i]);
            finfo_close($finfo);
            
            if (!in_array($tipo_mime, $tipos_permitidos)) {
                $resultado['errores'][] = "Imagen " . ($i + 1) . " no es un tipo válido. Solo JPG, PNG y GIF";
                continue;
            }
            
            // Verificar extensión
            $extension = strtolower(pathinfo($archivos_imagenes['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($extension, $extensiones_permitidas)) {
                $resultado['errores'][] = "Imagen " . ($i + 1) . " no tiene una extensión válida";
                continue;
            }
            
            // Verificar tamaño
            if ($archivos_imagenes['size'][$i] > $tamaño_maximo) {
                $tamaño_mb = round($tamaño_maximo / (1024 * 1024), 1);
                $resultado['errores'][] = "Imagen " . ($i + 1) . " supera el tamaño máximo de {$tamaño_mb}MB";
                continue;
            }
            
            // Generar nombre único para la imagen
            $nombre_archivo = uniqid('img_') . '_' . date('Y-m-d_H-i-s') . '_' . $i . '.' . $extension;
            $ruta_completa = $directorio_imagenes . $nombre_archivo;
            
            // Mover archivo
            if (move_uploaded_file($archivos_imagenes['tmp_name'][$i], $ruta_completa)) {
                chmod($ruta_completa, 0666);
                $rutas_guardadas[] = $ruta_completa;
            } else {
                $resultado['errores'][] = "Error al guardar imagen " . ($i + 1);
            }
        }
        
        // Marcar como éxito si se procesó al menos una imagen o no hubo errores
        if (!empty($rutas_guardadas) || empty($resultado['errores'])) {
            $resultado['success'] = true;
            $resultado['rutas'] = $rutas_guardadas;
        }
        
        // Información de debug
        if (!empty($rutas_guardadas)) {
            $resultado['mensaje'] = "Se guardaron " . count($rutas_guardadas) . " imagen(es) en: " . $directorio_imagenes;
        }
        
    } catch (Exception $e) {
        $resultado['errores'][] = "Error al procesar imágenes: " . $e->getMessage();
    }
    
    return $resultado;
}

/**
 * Método auxiliar para validar formulario de finalización
 * @param array $datos Datos del formulario POST
 * @return array Array de errores encontrados
 */
private function ValidarFormularioFinalizacion($datos){
    $errores = [];
    
    // Validar categorías (al menos una)
    if (!isset($datos['categorias_servicio']) || empty($datos['categorias_servicio'])) {
        $errores[] = "Debe seleccionar al menos una categoría de servicio";
    }
    
    // Validar métodos (al menos uno)
    if (!isset($datos['metodos_aplicacion']) || empty($datos['metodos_aplicacion'])) {
        $errores[] = "Debe seleccionar al menos un método de aplicación";
    }
    
    // Validar asistentes (verificar que existan en base de datos si se seleccionaron)
    if (isset($datos['asistentes']) && is_array($datos['asistentes'])) {
        foreach ($datos['asistentes'] as $id_asistente) {
            if (!empty($id_asistente) && !$this->modelo->ValidarEmpleado($id_asistente)) {
                $errores[] = "Uno de los asistentes seleccionados no es válido";
                break;
            }
        }
    }
    
    // Validar que se haya llenado al menos algún campo de inspección O notas de finalización
    $tiene_contenido = false;
    if (!empty($datos['inspection_problems']) || 
        !empty($datos['inspection_location']) || 
        !empty($datos['inspection_methods']) ||
        !empty($datos['notes_finalizacion'])) {
        $tiene_contenido = true;
    }
    
    if (!$tiene_contenido) {
        $errores[] = "Debe completar al menos un campo de información (inspección o notas de finalización)";
    }
    
    return $errores;
}

/**
 * Método auxiliar para continuar un servicio iniciado
 * Redirige directamente al formulario de finalización
 */
public function ContinuarServicio(){
    try {
        $id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // NUEVO: Obtener empleado de la sesión
        $id_empleado = $this->obtenerIdEmpleadoDesdeLogin();
        
        if ($id_servicio <= 0) {
            throw new Exception("ID de servicio no válido");
        }
        
        if ($id_empleado <= 0) {
            throw new Exception("No se pudo identificar al empleado");
        }
        
        // Validar que el empleado tenga permisos para continuar este servicio
        if (!$this->modelo->ValidarEncargadoServicio($id_servicio, $id_empleado)) {
            throw new Exception("No tiene permisos para continuar este servicio");
        }
        
        // IMPORTANTE: Validar que el servicio esté en estado "Iniciado" (estado 2)
        $servicio = $this->modelo->ObtenerServicioParaFinalizacion($id_servicio);
        if (!$servicio) {
            throw new Exception("Servicio no encontrado");
        }
        
        if ($servicio->service_status_id_service_status != 2) {
            throw new Exception("El servicio no está en estado de ejecución. Estado actual: " . 
                              (isset($servicio->name_service_status) ? $servicio->name_service_status : 'Desconocido'));
        }
        
        // Si todas las validaciones pasan, redirigir al formulario de completar servicio
        header("location: ?c=service&a=CompletarServicio&id=" . $id_servicio);
        
    } catch (Exception $e) {
        $mensaje_error = urlencode($e->getMessage());
        header("location: ?c=service&a=VistaTecnico&error=" . $mensaje_error);
    }
}


/**
 * Mostrar formulario para reprogramar un servicio existente
 * Carga los datos actuales del servicio en el formulario
 */
public function ReprogramarServicio(){
    try {
        $id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id_servicio <= 0) {
            throw new Exception("ID de servicio no válido");
        }
        
        // Obtener información completa del servicio
        $servicio_actual = $this->modelo->ObtenerServicioCompleto($id_servicio);
        
        if (!$servicio_actual) {
            throw new Exception("Servicio no encontrado");
        }
        
        // Verificar que el servicio esté en estado "Programado" (puede ser reprogramado)
        if ($servicio_actual->service_status_id_service_status != 1) {
            throw new Exception("Solo se pueden reprogramar servicios en estado 'Programado'");
        }
        
        // Obtener datos necesarios para los selects del formulario
        $clientes = $this->modelo->ListarClientes();
        $empleados = $this->modelo->ObtenerEmpleadosDisponibles();
        $estados = $this->modelo->ObtenerEstadosDisponibles();
        
        // Obtener empleados asignados actualmente
        $empleado_encargado_actual = $this->modelo->ObtenerEncargadoServicio($id_servicio);
        $asistentes_actuales = $this->modelo->ObtenerAsistentesServicio($id_servicio);
        
        // Variables para manejo de errores y mensajes
        $errores = [];
        $mensaje_exito = '';
        $modo_edicion = true; // Indicador para el formulario
        
        // Si se envió el formulario por POST, procesarlo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->ProcesarFormularioReprogramacion($id_servicio);
            if ($resultado['success']) {
                $mensaje_exito = $resultado['mensaje'];
                // Actualizar datos del servicio después de la modificación
                $servicio_actual = $this->modelo->ObtenerServicioCompleto($id_servicio);
                $empleado_encargado_actual = $this->modelo->ObtenerEncargadoServicio($id_servicio);
                $asistentes_actuales = $this->modelo->ObtenerAsistentesServicio($id_servicio);
            } else {
                $errores = $resultado['errores'];
            }
        }
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceForm.php";
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $errores[] = "Error al cargar el servicio: " . $e->getMessage();
        // Redirigir a la lista de servicios con mensaje de error
        $mensaje_error = urlencode($e->getMessage());
        header("location: ?c=service&error=" . $mensaje_error);
        exit;
    }
}

/**
 * Procesar el formulario de reprogramación de servicio
 * @param int $id_servicio ID del servicio a reprogramar
 * @return array Resultado del procesamiento con éxito/errores
 */
private function ProcesarFormularioReprogramacion($id_servicio){
    $errores = [];
    $resultado = ['success' => false, 'errores' => [], 'mensaje' => ''];
    
    try {
        // Validar datos requeridos (REMOVIDO: validación del estado)
        if (empty($_POST['customer_id_customer'])) {
            $errores[] = "Debe seleccionar un cliente";
        }
        
        if (empty($_POST['preset_dt_hr'])) {
            $errores[] = "Debe especificar la fecha y hora programada";
        }
        
        if (empty($_POST['empleado_encargado'])) {
            $errores[] = "Debe seleccionar un empleado encargado";
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
        
        // MODIFICADO: En reprogramación, mantener el estado actual
        $servicio_actual = $this->modelo->ObtenerServicioCompleto($id_servicio);
        
        // Preparar datos para actualizar el servicio
        $datos_servicio = [
            'notes' => !empty($_POST['notes']) ? $_POST['notes'] : null,
            'preset_dt_hr' => $_POST['preset_dt_hr'],
            'customer_id_customer' => (int)$_POST['customer_id_customer'],
            'service_status_id_service_status' => $servicio_actual->service_status_id_service_status // MODIFICADO: Mantener estado actual
        ];
        
        // Actualizar el servicio
        $actualizado = $this->modelo->ActualizarServicio($id_servicio, $datos_servicio);
        
        if (!$actualizado) {
            throw new Exception("No se pudo actualizar el servicio");
        }
        
        // Actualizar asignación de empleados
        $this->modelo->ActualizarEmpleadosServicio(
            $id_servicio, 
            (int)$_POST['empleado_encargado'], 
            $empleados_asistentes
        );
        
        // Procesar archivo PDF si se subió (opcional en reprogramación)
        if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            $validacion_archivo = $this->ValidarArchivoPDF($_FILES['archivo_pdf'], $id_servicio);
            if (!$validacion_archivo['success']) {
                // Si falla la subida del PDF, no fallar todo el proceso
                $resultado['mensaje'] = "Servicio reprogramado exitosamente (ID: " . $id_servicio . "), pero hubo un problema con el archivo PDF: " . implode(', ', $validacion_archivo['errores']);
            } else {
                // Guardar referencia del archivo en base de datos
                $this->modelo->GuardarArchivoServicio($id_servicio, $validacion_archivo['ruta'], 'PDF');
            }
        }
        
        $resultado['success'] = true;
        if (empty($resultado['mensaje'])) {
            $resultado['mensaje'] = "Servicio reprogramado exitosamente. ID: " . $id_servicio;
        }
        
    } catch (Exception $e) {
        $errores[] = "Error al procesar la reprogramación: " . $e->getMessage();
        $resultado['errores'] = $errores;
    }
    
    return $resultado;
}

/**
 * Vista de tabla administrativa con todos los servicios
 * Muestra información completa en formato tabular con filtros avanzados
 */
public function VistaTablaAdmin(){
    try {
        // Obtener datos para filtros
        $clientes = $this->modelo->ListarClientes();
        $empleados = $this->modelo->ListarEmpleados();
        $estados = $this->modelo->ListarEstadosServicio();
        
        if (empty($estados)) {
            $estados = [];
        }
        
        // Procesar filtros y búsqueda
        $filtros = $this->procesarFiltrosTabla();
        
        // Manejar paginación
        $pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $registros_por_pagina = 30;
        $offset = ($pagina_actual - 1) * $registros_por_pagina;
        
        // Obtener servicios
        $servicios = $this->modelo->ObtenerServiciosTablaAdmin($filtros, $registros_por_pagina, $offset);
        
        // Obtener total de registros
        $total_servicios = $this->modelo->ContarServiciosTablaAdmin($filtros);
        $total_paginas = ceil($total_servicios / $registros_por_pagina);
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceTableAdmin.php";
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $errores = ["Error: " . $e->getMessage()];
        $servicios = [];
        $clientes = [];
        $empleados = [];
        $estados = [];
        $total_servicios = 0;
        $total_paginas = 0;
        $pagina_actual = 1;
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/serviceTableAdmin.php";
        require_once "app/vistas/footer.php";
    }
}

/**
 * Construir URL para paginación manteniendo filtros actuales
 * @param int $pagina Número de página
 * @return string URL completa con parámetros
 */
private function construirUrlPaginacion($pagina){
    $params = [];
    $params['c'] = 'service';
    $params['a'] = 'VistaTablaAdmin';
    $params['pagina'] = $pagina;
    
    // Mantener filtros actuales
    if (isset($_GET['cliente']) && !empty($_GET['cliente'])) {
        $params['cliente'] = $_GET['cliente'];
    }
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $params['fecha_desde'] = $_GET['fecha_desde'];
    }
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $params['fecha_hasta'] = $_GET['fecha_hasta'];
    }
    if (isset($_GET['empleado']) && !empty($_GET['empleado'])) {
        $params['empleado'] = $_GET['empleado'];
    }
    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $params['estado'] = $_GET['estado'];
    }
    if (isset($_GET['buscar_servicio']) && !empty($_GET['buscar_servicio'])) {
        $params['buscar_servicio'] = $_GET['buscar_servicio'];
    }
    
    return '?' . http_build_query($params);
}

/**
 * Procesar filtros específicos para la vista de tabla administrativa
 * @return array Filtros procesados incluyendo búsqueda por número de servicio
 */
private function procesarFiltrosTabla(){
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
    
    // Filtro por estado
    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $filtros['estado'] = (int)$_GET['estado'];
    }
    
    // Búsqueda por número de servicio
    if (isset($_GET['buscar_servicio']) && !empty($_GET['buscar_servicio'])) {
        $filtros['numero_servicio'] = (int)$_GET['buscar_servicio'];
    }
    
    return $filtros;
}

/**
 * Subir croquis para un servicio específico
 * Para servicios que no tienen croquis subido
 */
public function SubirCroquis(){
    try {
        $id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id_servicio <= 0) {
            throw new Exception("ID de servicio no válido");
        }
        
        // Verificar que el servicio existe
        $servicio = $this->modelo->Obtener($id_servicio);
        if (!$servicio) {
            throw new Exception("Servicio no encontrado");
        }
        
        $errores = [];
        $mensaje_exito = '';
        
        // Procesar archivo si se envió
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['croquis_pdf'])) {
            $validacion_archivo = $this->ValidarArchivoPDF($_FILES['croquis_pdf'], $id_servicio);
            if ($validacion_archivo['success']) {
                // Guardar referencia del archivo en base de datos
                $this->modelo->GuardarArchivoServicio($id_servicio, $validacion_archivo['ruta'], 'PDF');
                $mensaje_exito = "Croquis subido exitosamente para el servicio #" . $id_servicio;
                
                // Redirigir de vuelta a la vista de tabla con mensaje
                $mensaje_encoded = urlencode($mensaje_exito);
                header("location: ?c=service&a=VistaTablaAdmin&success=" . $mensaje_encoded);
                exit;
            } else {
                $errores = $validacion_archivo['errores'];
            }
        }
        
        require_once "app/vistas/header.php";
        require_once "app/vistas/service/subirCroquis.php";
        require_once "app/vistas/footer.php";
        
    } catch (Exception $e) {
        $mensaje_error = urlencode($e->getMessage());
        header("location: ?c=service&a=VistaTablaAdmin&error=" . $mensaje_error);
        exit;
    }
}

/**
 * Obtener ID de empleado desde el sistema de login
 */
private function obtenerIdEmpleadoDesdeLogin(){
    // Iniciar sesión si no está iniciada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // CORREGIDO: Basado en tu estructura real de sesión
    if (isset($_SESSION['user']['id_employee']) && !empty($_SESSION['user']['id_employee'])) {
        return (int)$_SESSION['user']['id_employee'];
    }
    
    // Fallback adicionales para mayor compatibilidad
    if (isset($_SESSION['user']['id_user']) && !empty($_SESSION['user']['id_user'])) {
        // Si necesitas buscar por ID de usuario (pero con tu estructura actual no es necesario)
        return $this->buscarEmpleadoPorUsuarioId($_SESSION['user']['id_user']);
    }
    
    // Como fallback, si viene por GET (mantener compatibilidad)
    if (isset($_GET['empleado']) && !empty($_GET['empleado'])) {
        return (int)$_GET['empleado'];
    }
    
    return 0;
}

/**
 * Buscar empleado por ID de usuario del sistema de login
 */
private function buscarEmpleadoPorUsuarioId($id_usuario){
    try {
        // Basándome en tu estructura de USER_EMPLOYEE
        $sql = "SELECT employee_id_employee 
                FROM USER_EMPLOYEE 
                WHERE id_user_employee = ? 
                AND status = 1
                LIMIT 1";
        
        $consulta = $this->modelo->pdo->prepare($sql);
        $consulta->execute([$id_usuario]);
        $resultado = $consulta->fetch(PDO::FETCH_OBJ);
        
        return $resultado ? (int)$resultado->employee_id_employee : 0;
        
    } catch (Exception $e) {
        error_log("Error al buscar empleado por usuario: " . $e->getMessage());
        return 0;
    }
}



}