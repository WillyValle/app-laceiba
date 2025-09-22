<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Panel de Administración</h1>
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
    
    <!-- Bienvenida -->
    <div class="row">
      <div class="col-12">
        <div class="callout callout-info">
          <h5><i class="fas fa-user-shield"></i> Bienvenido, <?php echo htmlspecialchars(BaseControlador::getCurrentUser()['name']); ?></h5>
          <p>Panel de control administrativo del sistema de gestión de servicios de fumigación La Ceiba.</p>
        </div>
      </div>
    </div>

    <!-- Estadísticas principales -->
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3><?php echo $stats['total_servicios'] ?? 0; ?></h3>
            <p>Total Servicios</p>
          </div>
          <div class="icon">
            <i class="fas fa-cogs"></i>
          </div>
          <a href="?c=service" class="small-box-footer">
            Ver más <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3><?php echo $stats['total_clientes'] ?? 0; ?></h3>
            <p>Clientes Activos</p>
          </div>
          <div class="icon">
            <i class="fas fa-users"></i>
          </div>
          <a href="?c=customer" class="small-box-footer">
            Ver más <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3><?php echo $stats['total_empleados'] ?? 0; ?></h3>
            <p>Empleados</p>
          </div>
          <div class="icon">
            <i class="fas fa-user-tie"></i>
          </div>
          <a href="?c=employee" class="small-box-footer">
            Ver más <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3><?php echo $stats['servicios_hoy'] ?? 0; ?></h3>
            <p>Servicios Hoy</p>
          </div>
          <div class="icon">
            <i class="fas fa-calendar-day"></i>
          </div>
          <a href="?c=service&a=VistaTablaAdmin" class="small-box-footer">
            Ver más <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Gráficos y estadísticas -->
    <div class="row">
      <!-- Servicios por estado -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-chart-pie mr-1"></i>
              Servicios por Estado
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-responsive">
              <?php if (isset($stats['servicios_por_estado']) && !empty($stats['servicios_por_estado'])): ?>
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Estado</th>
                      <th>Cantidad</th>
                      <th>%</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                    $total_servicios = $stats['total_servicios'];
                    foreach ($stats['servicios_por_estado'] as $estado): 
                      $porcentaje = $total_servicios > 0 ? round(($estado->cantidad / $total_servicios) * 100, 1) : 0;
                    ?>
                      <tr>
                        <td><?php echo htmlspecialchars($estado->NAME_SERVICE_STATUS); ?></td>
                        <td>
                          <span class="badge badge-primary"><?php echo $estado->cantidad; ?></span>
                        </td>
                        <td><?php echo $porcentaje; ?>%</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p class="text-center text-muted">No hay datos de servicios disponibles</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Acciones rápidas -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-bolt mr-1"></i>
              Acciones Rápidas
            </h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-6 text-center">
                <a href="?c=service&a=NuevoServicio" class="btn btn-app bg-success">
                  <i class="fas fa-plus"></i>
                  Nuevo Servicio
                </a>
              </div>
              <div class="col-6 text-center">
                <a href="?c=customer&a=FormCrear" class="btn btn-app bg-info">
                  <i class="fas fa-user-plus"></i>
                  Nuevo Cliente
                </a>
              </div>
              <div class="col-6 text-center">
                <a href="?c=employee&a=FormCrear" class="btn btn-app bg-warning">
                  <i class="fas fa-user-tie"></i>
                  Nuevo Empleado
                </a>
              </div>
              <div class="col-6 text-center">
                <a href="?c=service&a=VistaTablaAdmin" class="btn btn-app bg-primary">
                  <i class="fas fa-table"></i>
                  Ver Servicios
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
</div>