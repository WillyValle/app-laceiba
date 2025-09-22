<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Dashboard Técnico</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    
    <!-- Bienvenida para técnico -->
    <div class="row">
      <div class="col-12">
        <div class="callout callout-success">
          <h5><i class="fas fa-tools"></i> ¡Hola, <?php echo htmlspecialchars(BaseControlador::getCurrentUser()['name']); ?>!</h5>
          <p>Bienvenido a tu panel de trabajo. Aquí puedes ver y gestionar los servicios que tienes asignados.</p>
        </div>
      </div>
    </div>

    <!-- Estadísticas del técnico -->
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3><?php echo $stats['mis_servicios'] ?? 0; ?></h3>
            <p>Mis Servicios</p>
          </div>
          <div class="icon">
            <i class="fas fa-tasks"></i>
          </div>
          <a href="?c=service&a=VistaTecnico" class="small-box-footer">
            Ver todos <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3><?php echo $stats['servicios_pendientes'] ?? 0; ?></h3>
            <p>Pendientes</p>
          </div>
          <div class="icon">
            <i class="fas fa-clock"></i>
          </div>
          <a href="?c=service&a=VistaTecnico" class="small-box-footer">
            Ver pendientes <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3><?php echo $stats['completados_hoy'] ?? 0; ?></h3>
            <p>Completados Hoy</p>
          </div>
          <div class="icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3><?php echo date('H:i'); ?></h3>
            <p>Hora Actual</p>
          </div>
          <div class="icon">
            <i class="fas fa-clock"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Próximos servicios -->
    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-calendar-alt mr-1"></i>
              Próximos Servicios Asignados
            </h3>
            <div class="card-tools">
              <a href="?c=service&a=VistaTecnico" class="btn btn-tool">
                <i class="fas fa-external-link-alt"></i>
              </a>
            </div>
          </div>
          <div class="card-body p-0">
            <?php if (isset($stats['proximos_servicios']) && !empty($stats['proximos_servicios'])): ?>
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Cliente</th>
                      <th>Fecha/Hora</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats['proximos_servicios'] as $servicio): ?>
                      <tr>
                        <td>
                          <strong><?php echo htmlspecialchars($servicio->NAME_CUSTOMER); ?></strong>
                        </td>
                        <td>
                          <strong><?php echo date('d/m/Y', strtotime($servicio->PRESET_DT_HR)); ?></strong><br>
                          <small class="text-muted"><?php echo date('H:i', strtotime($servicio->PRESET_DT_HR)); ?></small>
                        </td>
                        <td>
                          <span class="badge badge-warning">Programado</span>
                        </td>
                        <td>
                          <a href="?c=service&a=IniciarServicio&id=<?php echo $servicio->ID_SERVICE; ?>" 
                          class="btn btn-sm btn-primary"
                          onclick="return confirm('¿Estás seguro de que deseas iniciar este servicio? Se registrará automáticamente la fecha y hora de inicio');">
                            <i class="fas fa-tools"></i> Trabajar
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="p-3 text-center text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p>No tienes servicios programados próximamente.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Panel de información del técnico -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-id-badge mr-1"></i>
              Mi Información
            </h3>
          </div>
          <div class="card-body">
            <div class="text-center">
              <img src="<?php echo !empty(BaseControlador::getCurrentUser()['image']) ? htmlspecialchars(BaseControlador::getCurrentUser()['image']) : 'assets/dist/img/user2-160x160.jpg'; ?>" 
                   class="img-circle elevation-2" 
                   alt="Foto del técnico" 
                   style="width: 80px; height: 80px;">
              <h5 class="mt-2"><?php echo htmlspecialchars(BaseControlador::getCurrentUser()['name']); ?></h5>
              <p class="text-muted"><?php echo htmlspecialchars(BaseControlador::getCurrentRole()['name_role']); ?></p>
            </div>
            
            <hr>
            
            <div class="row">
              <div class="col-12">
                <strong>Usuario:</strong> <?php echo htmlspecialchars(BaseControlador::getCurrentUser()['username']); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
</div>