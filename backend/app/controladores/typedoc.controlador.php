<?php
require_once "app/modelos/typedoc.php";

class TypeDocControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new TypeDoc();
    }

    public function Inicio(){
        require_once "app/vistas/header.php";
        require_once "app/vistas/type_doc/listtypedoc.php";
        require_once "app/vistas/footer.php";
    }

    public function Guardar(){
        $s = new TypeDoc();
        $s->setNameTypeDoc($_POST['nombre_tipo']);
        $s->setDescription($_POST['descripcion_tipo']);
        $s->setStatus(1);

        $s->getIdTypeDoc() > 0 ?
        $this->modelo->Actualizar($s) :
        $this->modelo->Insertar($s);
        header("location: ?c=typedoc");
    }

    // Nuevo método para la edición inline
    public function ActualizarInline(){
        try {
            // Validar que se recibieron los datos necesarios
            if (!isset($_POST['id']) || !isset($_POST['nombre']) || !isset($_POST['estado'])) {
                throw new Exception('Datos incompletos');
            }

            $s = new TypeDoc();
            $s->setIdTypeDoc((int)$_POST['id']);
            $s->setNameTypeDoc($_POST['nombre']);
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