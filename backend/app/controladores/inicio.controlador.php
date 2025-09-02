<?php

require_once "app/modelos/servicecategory.php";
require_once "app/modelos/applicationmethod.php";
require_once "app/modelos/servicestatus.php";

class InicioControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new ServiceCategory();
    }

    public function Inicio(){
        $bd = BasedeDatos::Conectar();
        require_once "app/vistas/header.php";
        require_once "app/vistas/inicio/principal.php";
        require_once "app/vistas/footer.php";
    }
}