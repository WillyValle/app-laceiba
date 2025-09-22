<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Gestión de Permisos</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="?c=inicio">Inicio</a></li>
          <li class="breadcrumb-item">Configuración</li>
          <li class="breadcrumb-item active">Permisos</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
      </div>
    <?php endif; ?>

    <!-- Información del sistema -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-shield-alt"></i> Sistema de Permisos
            </h3>
          </div>
          <div class="card-body">
            <p>Gestione los permisos asignados a cada rol del sistema. Los cambios se aplican inmediatamente a todos los usuarios con el rol correspondiente.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de roles -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Roles y Permisos</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Rol</th>
                    <th>Descripción</th>
                    <th>Permisos Asignados</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($roles as $rol): ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($rol->NAME_ROLE_EMPLOYEE); ?></strong>
                      </td>
                      <td>
                        <?php echo htmlspecialchars($rol->DESCRIPTION ?? 'Sin descripción'); ?>
                      </td>
                      <td>
                        <span class="badge badge-primary"><?php echo $rol->TOTAL_PERMISOS; ?> permisos</span>
                      </td>
                      <td>
                        <a href="?c=permission&a=GestionarRol&role_id=<?php echo $rol->ID_ROLE_EMPLOYEE; ?>" 
                           class="btn btn-primary btn-sm">
                          <i class="fas fa-cog"></i> Gestionar Permisos
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
                  </div>