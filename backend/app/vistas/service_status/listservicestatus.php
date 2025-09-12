<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      Estados de Servicio
      <span class="badge badge-primary" id="contador-activos"><?= count(array_filter($this->modelo->Listar(), function($r) { return $r->STATUS == 1; })) ?></span>
    </h3>
  </div>
  <!-- /.card-header -->
  <div class="card-body">
    <!-- Boton para mostrar/ocultar formulario -->
    <div>
      <button id="btnToggleCategoria" class="btn btn-primary btn-toggle-form" onclick="toggleFormulario('Categoria', 'Agregar Nueva Categoría', 'btn-primary')">
        <i class="fas fa-plus"></i>Agregar Estado de Servicio
      </button>
    </div>
    <br>

        <!-- Formulario expandible (inicialmente oculto) -->
    <div id="formularioCategoria" class="card card-primary formulario-expandible" style="display: none;">
      <div class="card-header">
        <h3 class="card-title">Agregar Estado de Servicio</h3>
      </div>
      <form action="?c=servicestatus&a=Guardar" method="POST">
        <div class="card-body">
          <div class="form-group">
            <input class="form-control" id="ID_SERVICE_STATUS" type="hidden">
          </div>
          <div class="form-group">
            <label for="nombre_estado">
              Nombre<span class="text-danger">*</span>
            </label>
            <input 
            type="text" 
            class="form-control" 
            id="nombre_estado" 
            name="nombre_estado" 
            placeholder="Ingrese el nombre del estado" 
            pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+(\s[a-zA-ZáéíóúÁÉÍÓÚñÑ]+)*$"
            title="Solo letras y espacios. Mínimo 2 caracteres. No números ni símbolos."
            minlength="2"
            maxlength="50"
            required>
            <small class="form-text text-muted">
              <i class="fas fa-info-circle"></i> Solo letras y espacios. Mínimo 2 caracteres. No números ni símbolos.
            </small>
          </div>
          <div class="form-group">
            <label for="descripcion_estado">Descripción</label>
            <textarea class="form-control" id="descripcion_estado" name="descripcion_estado" rows="3" placeholder="Ingrese la descripción"></textarea>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary ml-2" onclick="cancelarFormulario('Categoria', 'Agregar Nuevo Estado de Servicio', 'btn-primary')">Cancelar</button>
        </div>
      </form>
    </div>

        <!-- Tabla de registros activos -->
    <div class="table-responsive">
      <table id="tabla-activos" class="table table-bordered table-striped">
        <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $registrosActivos = array_filter($this->modelo->Listar(), function($r) { return $r->STATUS == 1; });
        foreach($registrosActivos as $r): 
        ?>
        <tr id="fila-<?=$r->ID_SERVICE_STATUS?>-activos" data-id="<?=$r->ID_SERVICE_STATUS?>">
          <td><?=$r->NAME_SERVICE_STATUS?></td>
          <td><?=$r->DESCRIPTION?></td>
          <td><span class="badge badge-success">Activo</span></td>
          <td>
            <button class="btn btn-warning btn-sm" onclick="editarFila(<?=$r->ID_SERVICE_STATUS?>, 'activos')">
              <i class="fas fa-edit"></i> Editar
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
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
          <i class="fas fa-eye"></i> Ver Registros Inactivos 
          <span class="badge badge-light" id="contador-inactivos"><?= count(array_filter($this->modelo->Listar(), function($r) { return $r->STATUS == 0; })) ?></span>
        </button>
      </div>

      <!-- Tabla de registros inactivos (inicialmente oculta) -->
      <div id="tablaInactivosContainer" class="tabla-inactivos" style="display: none;">
        <div class="card card-secondary">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-archive"></i> Registros Inactivos
            </h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tabla-inactivos" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Descripción</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php 
                $registrosInactivos = array_filter($this->modelo->Listar(), function($r) { return $r->STATUS == 0; });
                foreach($registrosInactivos as $r): 
                ?>
                <tr id="fila-<?=$r->ID_SERVICE_STATUS?>-inactivos" class="registro-inactivo" data-id="<?=$r->ID_SERVICE_STATUS?>">
                  <td><?=$r->NAME_SERVICE_STATUS?></td>
                  <td><?=$r->DESCRIPTION?></td>
                  <td><span class="badge badge-danger">Inactivo</span></td>
                  <td>
                    <button class="btn btn-warning btn-sm" onclick="editarFila(<?=$r->ID_SERVICE_STATUS?>, 'inactivos')">
                      <i class="fas fa-edit"></i> Editar
                    </button>
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