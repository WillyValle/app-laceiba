<?php
require_once "app/controladores/base.controlador.php";

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user'])) {
    header("Location: ?c=auth");
    exit();
}

$current_user = BaseControlador::getCurrentUser();
$current_role = BaseControlador::getCurrentRole();
?>
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
  <!-- QRCode.js para WhatsApp -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
        <a href="?c=inicio" class="nav-link">Inicio</a>
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

      <!-- User Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" title="<?php echo htmlspecialchars($current_user['name']); ?>">
          <i class="fas fa-user"></i>
          <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['name']); ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item-text">
            <strong><?php echo htmlspecialchars($current_user['name']); ?></strong><br>
            <small class="text-muted"><?php echo htmlspecialchars($current_role['name_role']); ?></small>
          </span>
          <div class="dropdown-divider"></div>
          <a href="?c=auth&a=ChangePassword" class="dropdown-item">
            <i class="fas fa-key mr-2"></i> Cambiar Contraseña
          </a>
          <div class="dropdown-divider"></div>
          <a href="?c=auth&a=Logout" class="dropdown-item">
            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
          </a>
        </div>
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
    <a href="?c=inicio" class="brand-link">
      <img src="assets/dist/img/logolaceiba.png" alt="Logo La Ceiba" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">APP La Ceiba</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?php echo !empty($current_user['image']) ? htmlspecialchars($current_user['image']) : 'assets/dist/img/user2-160x160.jpg'; ?>" 
               class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo htmlspecialchars($current_user['name']); ?></a>
          <small class="text-muted"><?php echo htmlspecialchars($current_role['name_role']); ?></small>
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
          
          <?php if (BaseControlador::hasPermission('VIEW_ADMIN_PANEL')): ?>
            <!-- Menú Servicios - Solo Administradores -->
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

            <!-- Menú Clientes - Solo Administradores -->
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

            <!-- Menú Empleados - Solo Administradores -->
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

            <!-- Menú WhatsApp - Solo Administradores -->
            <?php if (BaseControlador::hasPermission('MANAGE_WHATSAPP')): ?>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fab fa-whatsapp text-success"></i>
                <p>
                  WhatsApp
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="?c=whatsapp&a=Dashboard" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Panel de Control</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="?c=whatsapp&a=Logs" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Historial de Envíos</p>
                  </a>
                </li>
              </ul>
            </li>
            <?php endif; ?>

            <!-- Configuración - Solo Administradores -->
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
                    <p>Tipos de Documentos</p>
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
                <li class="nav-item">
                  <a href="?c=permission" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Gestión de Permisos</p>
                  </a>
                </li>
              </ul>
            </li>
          <?php endif; ?>

          <?php if (BaseControlador::hasPermission('VIEW_TECHNICIAN_PANEL')): ?>
            <!-- Menú para Técnicos -->
            <li class="nav-item">
              <a href="?c=service&a=VistaTecnico" class="nav-link">
                <i class="nav-icon fas fa-tools"></i>
                <p>Mis Servicios Asignados</p>
              </a>
            </li>
          <?php endif; ?>

          <!-- Separador -->
          <li class="nav-header">SISTEMA</li>
          
          <!-- Enlace de Ayuda -->
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-question-circle"></i>
              <p>Ayuda</p>
            </a>
          </li>

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
