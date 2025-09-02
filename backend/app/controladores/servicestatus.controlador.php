<?php
require_once "app/modelos/servicestatus.php";

class ServiceStatusControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new ServiceStatus();
    }

    public function Inicio(){
        require_once "app/vistas/header.php";
        require_once "app/vistas/service_status/listservicestatus.php";
        require_once "app/vistas/footer.php";
    }

    public function Guardar(){
        $s = new ServiceStatus();
        $s->setName($_POST['nombre_estado']);
        $s->setDescription($_POST['descripcion_estado']);
        $s->setStatus(1);

        $s->getIdServiceStatus() > 0 ?
        $this->modelo->Actualizar($s) :
        $this->modelo->Insertar($s);
        header("location: ?c=servicestatus");
    }

    // Metodo para la edicion inline
    public function ActualizarInline(){
        try {
            // Validar que se recibieron los datos necesarios
            if (!isset($_POST['id']) || !isset($_POST['nombre']) || !isset($_POST['estado'])) {
                throw new Exception('Datos incompletos');
            }

            $s = new ServiceStatus();
            $s->setIdServiceStatus((int)$_POST['id']);
            $s->setName($_POST['nombre']);
            $s->setDescription($_POST['descripcion'] ?? ''); // Descripción opcional
            $s->setStatus((bool)$_POST['estado']);

            // Ejecutar la actualización
            $this->modelo->Actualizar($s);

            // Devolver respuesta JSON exitosa
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Registro actualizado correctamente'
            ]);

        } catch (Exception $e) {
            // Devolver respuesta JSON con error
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }
}