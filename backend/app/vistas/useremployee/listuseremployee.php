<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      Usuarios Empleados Activos
      <span class="badge badge-primary" id="contador-activos"><?= count(array_filter($this->modelo->Listar(), function($r) { return $r->status == 1; })) ?></span>
    </h3>
  </div>
  <!-- /.card-header -->
  <div class="card-body">
    <br>
    <!-- Tabla de registros activos -->
    <div class="table-responsive">
      <table id="tabla-activos" class="table table-bordered table-striped">
        <thead>
        <tr>
          <th>Nombre Empleado</th>
          <th>Usuario</th>
          <th>Contraseña</th>
          <th>Cambiar Contraseña</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $registrosActivos = array_filter($this->modelo->Listar(), function($r) { return $r->status == 1; });
        foreach($registrosActivos as $r): 
        ?>
        <tr id="fila-<?=$r->id_user_employee?>-activos" data-id="<?=$r->id_user_employee?>">
          <td><?=htmlspecialchars($r->employee_name ?? '')?></td>
          <td><?=htmlspecialchars($r->username ?? '')?></td>
          <td>********</td>
          <td><span class="badge <?=($r->force_password_change == 1 ? 'badge-danger' : 'badge-success')?>">
            <?=($r->force_password_change == 1 ? 'Sí' : 'No')?>
          </span></td>
          <td>
            <span class="badge badge-success">Activo</span>
          </td>
          <td>
            <a href="?c=useremployee&a=FormEditar&id=<?=$r->id_user_employee?>" 
               class="btn btn-warning btn-sm">
              <i class="fas fa-edit"></i> Editar
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
          <th>Nombre Empleado</th>
          <th>Usuario</th>
          <th>Contraseña</th>
          <th>Cambiar Contraseña</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
        </tfoot>
      </table>
    </div>

    <!-- Sección de registros inactivos -->
    <div class="tabla-inactivos-container">
      <div>
        <button id="btnToggleInactivos" class="btn btn-secondary btn-toggle-inactivos" onclick="toggleTablaInactivos()">
          <i class="fas fa-eye"></i> Ver Usuarios Inactivos 
          <span class="badge badge-light" id="contador-inactivos"><?= count(array_filter($this->modelo->Listar(), function($r) { return $r->status == 0; })) ?></span>
        </button>
      </div>

      <!-- Tabla de registros inactivos (inicialmente oculta) -->
      <div id="tablaInactivosContainer" class="tabla-inactivos" style="display: none;">
        <div class="card card-secondary">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-archive"></i> Clientes Inactivos
            </h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tabla-inactivos" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Nombre Empleado</th>
                    <th>Usuario</th>
                    <th>Contraseña</th>
                    <th>Cambiar Contraseña</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php 
                $registrosInactivos = array_filter($this->modelo->Listar(), function($r) { return $r->status == 0; });
                foreach($registrosInactivos as $r): 
                ?>
                <tr id="fila-<?=$r->id_user_employee?>-inactivos" class="registro-inactivo" data-id="<?=$r->id_user_employee?>">
                    <td><?=htmlspecialchars($r->employee_name ?? '')?></td>
                    <td><?=htmlspecialchars($r->username ?? '')?></td>
                    <td>********</td>
                    <td>
                      <span class="badge <?=($r->force_password_change == 1 ? 'badge-danger' : 'badge-success')?>">
                        <?=($r->force_password_change == 1 ? 'Sí' : 'No')?>
                      </span>
                    </td>
                  <td>
                    <span class="badge badge-danger">Inactivo</span>
                  </td>
                  <td>
                    <a href="?c=useremployee&a=FormEditar&id=<?=$r->id_user_employee?>" 
                       class="btn btn-warning btn-sm">
                      <i class="fas fa-edit"></i> Editar
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
  <!-- /.card-body -->
</div>
</div>