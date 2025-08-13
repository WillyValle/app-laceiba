<?php

class InicioControlador{
    private $modelo;

    public function __CONSTRUCT(){
        //$this->modelo = new InicioModelo();
    }

    public function Inicio(){
        $bd = BasedeDatos::Conectar();
        require_once "app/vistas/header.php";
        require_once "app/vistas/inicio/principal.php";
        require_once "app/vistas/footer.php";
    }
}