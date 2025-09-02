<?php

require_once "app/modelos/roleemployee.php";

class RoleEmployeeControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new RoleEmployee();
    }

    public function Inicio(){
        require_once "app/vistas/header.php";
        require_once "app/vistas/role_employee/listroleemployee.php";
        require_once "app/vistas/footer.php";
    }

    public function Guardar(){
        $s=new RoleEmployee();
        $s->setNAME_ROLE_EMPLOYEE($_POST['nombre_rol']);
        $s->setDESCRIPTION($_POST['descripcion_rol']);
        $s->setSTATUS(1);

        $s->getID_ROLE_EMPLOYEE() > 0 ? 
        $this->modelo->Actualizar($s) :
        $this->modelo->Insertar($s);
        header("location: ?c=roleemployee");
    }

    // Nuevo método para la edición inline
    public function ActualizarInline(){
        try {
            // Validar que se recibieron los datos necesarios
            if (!isset($_POST['id']) || !isset($_POST['nombre']) || !isset($_POST['estado'])) {
                throw new Exception('Datos incompletos');
            }

            $s = new RoleEmployee();
            $s->setID_ROLE_EMPLOYEE((int)$_POST['id']);
            $s->setNAME_ROLE_EMPLOYEE($_POST['nombre']);
            $s->setDESCRIPTION($_POST['descripcion'] ?? ''); // Descripción opcional
            $s->setSTATUS((bool)$_POST['estado']);

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