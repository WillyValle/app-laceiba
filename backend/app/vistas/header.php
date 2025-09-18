<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>APP La Ceiba</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
  <!-- Estilos para formularios expandibles -->
  <link rel="stylesheet" href="assets/dist/css/form-toggle.css">
  <!-- Estilo para edicion registros -->
  <link rel="stylesheet" href="assets/dist/css/inline-edit.css">
  <!-- Estilos para el theme switcher -->
  <link rel="stylesheet" href="assets/dist/css/theme-switcher.css">
</head>
<body class="hold-transition sidebar-mini theme-light">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index.php" class="nav-link">Inicio</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Buscar" aria-label="Buscar">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Theme Toggle Button -->
      <li class="nav-item">
        <button class="nav-link theme-toggle-btn" id="theme-toggle-btn" title="Cambiar a tema oscuro">
          <i class="fas fa-moon" id="theme-icon"></i>
        </button>
      </li>

      <!-- Login Button (placeholder for future implementation) -->
      <li class="nav-item">
        <a class="nav-link" href="#" title="Iniciar Sesión">
          <i class="fas fa-sign-in-alt"></i>
        </a>
      </li>

      <!-- Fullscreen Toggle -->
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
      <img src="assets/dist/img/logolaceiba.png" alt="Logo La Ceiba" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">APP La Ceiba</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="assets/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">Administrador</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Buscar" aria-label="Buscar">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
<nav class="mt-2">
  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    
    <!-- Menú Servicios -->
    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-cogs"></i>
        <p>
          Servicios
          <i class="right fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="?c=service&a=NuevoServicio" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Programar Servicio</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="?c=service" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Gestionar Por Estados</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="?c=service&a=VistaTablaAdmin" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Tabla de Gestión</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="?c=service&a=VistaTecnico" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Vista Técnicos</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="?c=servicecategory" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Categoría de Servicios</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="?c=applicationmethod" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Métodos de Aplicación</p>
          </a>
        </li>
      </ul>
    </li>

    <!-- Menú Clientes -->
    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>
          Clientes
          <i class="right fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="?c=customer" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Gestionar Clientes</p>
          </a>
        </li>
      </ul>
    </li>

        <!-- Menú Empleados -->
    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-user-tie"></i>
        <p>
          Empleados
          <i class="right fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="?c=employee" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Gestionar Empleados</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="?c=useremployee" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Usuarios Empleados</p>
          </a>
        </li>
      </ul>
    </li>

    <!-- Configuración -->
    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-cog"></i>
        <p>
          Configuración
          <i class="right fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="?c=servicestatus" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Estados de los Servicios</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="?c=typedoc" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Tipos de Documentos Aceptados</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="?c=roleemployee" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Tipos de Empleados</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="?c=roleinservice" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Empleados en Servicio</p>
          </a>
        </li>
      </ul>

  </ul>
</nav>
<!-- /.sidebar-menu -->

    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
    
    </div>
    <!-- /.content-header -->