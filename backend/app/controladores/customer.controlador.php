<?php
require_once "app/modelos/customer.php";

class CustomerControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new Customer();
    }

    public function Inicio(){
        // Obtener parámetros de búsqueda y paginación
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $por_pagina = 30;
        
        // Obtener datos activos
        $clientes_activos = $this->modelo->ListarConPaginacion($busqueda, $pagina, $por_pagina, true);
        $total_activos = $this->modelo->ContarClientes($busqueda, true);
        $total_paginas_activos = ceil($total_activos / $por_pagina);
        
        // Obtener datos inactivos
        $clientes_inactivos = $this->modelo->ListarConPaginacion($busqueda, $pagina, $por_pagina, false);
        $total_inactivos = $this->modelo->ContarClientes($busqueda, false);
        $total_paginas_inactivos = ceil($total_inactivos / $por_pagina);

        require_once "app/vistas/header.php";
        require_once "app/vistas/customer/listcustomer.php";
        require_once "app/vistas/footer.php";
    }

    public function FormCrear(){
        $c = new Customer();
        require_once "app/vistas/header.php";
        require_once "app/vistas/customer/formcustomer.php";
        require_once "app/vistas/footer.php";
    }

    public function FormEditar(){
        $datos = $this->modelo->Obtener($_GET['id']);
        require_once "app/vistas/header.php";
        require_once "app/vistas/customer/formcustomer.php";
        require_once "app/vistas/footer.php";
    }

    public function Guardar(){
        $c = new Customer();
        
        // Si hay ID es una actualización, si no es nuevo
        if (isset($_POST['id_customer']) && !empty($_POST['id_customer'])) {
            $c->setIdCustomer((int)$_POST['id_customer']);
        }
        
        $c->setNameCustomer($_POST['name_customer']);
        $c->setAddressCustomer($_POST['address_customer']);
        
        // CORREGIDO: Usar el nombre correcto del campo
        $c->setTypeDoc($_POST['type_doc_id_type_doc']);
        
        $c->setDocNum($_POST['doc_num']);
        $c->setWhatsapp($_POST['whatsapp']);
        $c->setTel($_POST['tel']);
        $c->setMail($_POST['mail']);
        
        // Si es edición y viene el campo status, usar ese valor, sino usar 1 (activo)
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
        $c->setStatus($status);

        $c->getIdCustomer() > 0 ?
        $this->modelo->Actualizar($c) :
        $this->modelo->Insertar($c);
        
        header("location: ?c=customer");
    }

    private function construirUrlPaginacion($pagina){
        $params = $_GET;
        $params['pagina'] = $pagina;
        unset($params['c']);
        unset($params['a']);
        return "?c=customer&" . http_build_query($params);
    }
}