<?php
require_once "app/modelos/applicationmethod.php";

class ApplicationMethodControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new ApplicationMethod();
    }

    public function Inicio(){
        require_once "app/vistas/header.php";
        require_once "app/vistas/application_method/listapplicationmethod.php";
        require_once "app/vistas/footer.php";
    }

    public function Guardar(){
        $s = new ApplicationMethod();
        $s->setNameApplicationMethod($_POST['nombre_metodo']);
        $s->setDescription($_POST['descripcion_metodo']);
        $s->setStatus(1);

        $s->getIdApplicationMethod() > 0 ?
        $this->modelo->Actualizar($s) :
        $this->modelo->Insertar($s);
        header("location: ?c=applicationmethod");
    }

    // Nuevo método para la edición inline
    public function ActualizarInline(){
        try {
            // Validar que se recibieron los datos necesarios
            if (!isset($_POST['id']) || !isset($_POST['nombre']) || !isset($_POST['estado'])) {
                throw new Exception('Datos incompletos');
            }

            $s = new ApplicationMethod();
            $s->setIdApplicationMethod((int)$_POST['id']);
            $s->setNameApplicationMethod($_POST['nombre']);
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
        exit; // Importante: detener la ejecución después de enviar JSON

    }

}