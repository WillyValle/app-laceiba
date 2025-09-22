<?php

session_start();

require_once "app/modelos/database.php";

// Si no hay controlador especificado, manejar según estado de autenticación
if(!isset($_GET['c'])) {
    // Si no está logueado, ir al login
    if (!isset($_SESSION['user'])) {
        require_once "app/controladores/auth.controlador.php";
        $controlador = new AuthControlador();
        call_user_func(array($controlador, "Inicio"));
    } else {
        // Si está logueado, ir al dashboard principal
        require_once "app/controladores/inicio.controlador.php";
        $controlador = new InicioControlador();
        call_user_func(array($controlador, "Inicio"));
    }
} else {
    $controlador = $_GET['c'];
    
    // Manejar rutas especiales
    if ($controlador === 'auth') {
        require_once "app/controladores/auth.controlador.php";
        $controlador = new AuthControlador();
        $accion = isset($_GET['a']) ? $_GET['a'] : 'Inicio';
        call_user_func(array($controlador, $accion));
    } else {
        // Para otros controladores, verificar autenticación
        if (!isset($_SESSION['user'])) {
            header("Location: ?c=auth");
            exit();
        }
        
        require_once "app/controladores/$controlador.controlador.php";
        $controlador = ucwords($controlador)."Controlador";
        $controlador = new $controlador();
        $accion = isset($_GET['a']) ? $_GET['a'] : 'Inicio';
        call_user_func(array($controlador, $accion));
    }
}