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
            $serviciosProgramados = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 1]), 5);
            $serviciosEjecucion = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 2]), 5);
            $serviciosFinalizados = $this->modelo->ObtenerConFiltros(array_merge($filtros, ['estado' => 3]), 5);
        } else {
            // Sin filtros, obtener por estado normal
            $serviciosProgramados = $this->modelo->ObtenerPorEstado(1, 5); // Estado 1: Programado
            $serviciosEjecucion = $this->modelo->ObtenerPorEstado(2, 5);   // Estado 2: En ejecución
            $serviciosFinalizados = $this->modelo->ObtenerPorEstado(3, 5); // Estado 3: Finalizado
        }
        
        // Contar totales por estado
        $totalProgramados = $this->modelo->ContarPorEstado(1);
        $totalEjecucion = $this->modelo->ContarPorEstado(2);
        $totalFinalizados = $this->modelo->ContarPorEstado(3);
        
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
                $servicios = $this->modelo->ObtenerPorEstado($estado, null); // Obtener todos
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


}