<?php
require_once "app/modelos/employee.php";

class EmployeeControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new Employee();
    }

    public function Inicio(){
        require_once "app/vistas/header.php";
        require_once "app/vistas/employee/listemployee.php";
        require_once "app/vistas/footer.php";
    }

    public function FormCrear(){
        $e = new Employee();
        require_once "app/vistas/header.php";
        require_once "app/vistas/employee/formemployee.php";
        require_once "app/vistas/footer.php";
    }

    public function FormEditar(){
        $datos = $this->modelo->Obtener($_GET['id']);
        require_once "app/vistas/header.php";
        require_once "app/vistas/employee/formemployee.php";
        require_once "app/vistas/footer.php";
    }

    public function Guardar(){
        $e = new Employee();
        
        // Si hay ID es una actualización, si no es nuevo
        if (isset($_POST['id_employee']) && !empty($_POST['id_employee'])) {
            $e->setIdEmployee((int)$_POST['id_employee']);
        }
        
        $e->setNameEmployee($_POST['name_employee']);
        $e->setLastnameEmployee($_POST['lastname_employee']);
        $e->setAddressEmployee($_POST['address_employee']);
        
        // CORREGIDO: Usar el nombre correcto del campo
        $e->setTypeDoc($_POST['type_doc_id_type_doc']);
        
        $e->setDocNum($_POST['doc_num']);
        $e->setPathImg($_POST['image_employee']);
        
        // CORREGIDO: Usar el nombre correcto del campo
        $e->setRoleEmployee($_POST['role_employee_id_role_employee']);
        
        $e->setWhatsapp($_POST['whatsapp']);
        $e->setTel($_POST['tel']);
        $e->setMail($_POST['mail']);
        
        // Si es edición y viene el campo status, usar ese valor, sino usar 1 (activo)
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
        $e->setStatus($status);

        $e->getIdEmployee() > 0 ?
        $this->modelo->Actualizar($e) :
        $this->modelo->Insertar($e);
        
        header("location: ?c=employee");
    }


}