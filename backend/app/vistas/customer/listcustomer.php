<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      Clientes Registrados
      <span class="badge badge-primary" id="contador-activos"><?= count(array_filter($this->modelo->Listar(), function($r) { return $r->status == 1; })) ?></span>
    </h3>
  </div>
  <!-- /.card-header -->
  <div class="card-body">
    <!-- Botón para agregar cliente -->
    <div>
      <a class="btn btn-primary btn-flat" href="?c=customer&a=FormCrear">
        <i class="fas fa-plus"></i> Agregar Cliente
      </a>
    </div>
    <br>
    <!-- Tabla de registros activos -->
    <div class="table-responsive">
      <table id="tabla-activos" class="table table-bordered table-striped">
        <thead>
        <tr>
          <th>Nombre del Cliente</th>
          <th>Dirección</th>
          <th>Tipo Doc.</th>
          <th>Número Doc.</th>
          <th>WhatsApp</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $registrosActivos = array_filter($this->modelo->Listar(), function($r) { return $r->status == 1; });
        foreach($registrosActivos as $r): 
        ?>
        <tr id="fila-<?=$r->id_customer?>-activos" data-id="<?=$r->id_customer?>">
          <td><?=htmlspecialchars($r->name_customer ?? '')?></td>
          <td><?=htmlspecialchars($r->address_customer ?? '')?></td>
          <td><?=htmlspecialchars($r->name_type_doc ?? 'Sin tipo')?></td>
          <td><?=htmlspecialchars($r->doc_num ?? '')?></td>
          <td><?=htmlspecialchars($r->whatsapp ?? '')?></td>
          <td><?=htmlspecialchars($r->tel ?? '')?></td>
          <td><?=htmlspecialchars($r->mail ?? '')?></td>
          <td>
            <span class="badge badge-success">Activo</span>
          </td>
          <td>
            <a href="?c=customer&a=FormEditar&id=<?=$r->id_customer?>" 
               class="btn btn-warning btn-sm">
              <i class="fas fa-edit"></i> Editar
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
          <th>Nombre del Cliente</th>
          <th>Dirección</th>
          <th>Tipo Doc.</th>
          <th>Número Doc.</th>
          <th>WhatsApp</th>
          <th>Teléfono</th>
          <th>Email</th>
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
          <i class="fas fa-eye"></i> Ver Clientes Inactivos 
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
                  <th>Nombre del Cliente</th>
                  <th>Dirección</th>
                  <th>Tipo Doc.</th>
                  <th>Número Doc.</th>
                  <th>WhatsApp</th>
                  <th>Teléfono</th>
                  <th>Email</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php 
                $registrosInactivos = array_filter($this->modelo->Listar(), function($r) { return $r->status == 0; });
                foreach($registrosInactivos as $r): 
                ?>
                <tr id="fila-<?=$r->id_customer?>-inactivos" class="registro-inactivo" data-id="<?=$r->id_customer?>">
                  <td><?=htmlspecialchars($r->name_customer ?? '')?></td>
                  <td><?=htmlspecialchars($r->address_customer ?? '')?></td>
                  <td><?=htmlspecialchars($r->name_type_doc ?? 'Sin tipo')?></td>
                  <td><?=htmlspecialchars($r->doc_num ?? '')?></td>
                  <td><?= $r->whatsapp ?? '' ?></td>
                  <td><?=htmlspecialchars($r->tel ?? '')?></td>
                  <td><?=htmlspecialchars($r->mail ?? '')?></td>
                  <td>
                    <span class="badge badge-danger">Inactivo</span>
                  </td>
                  <td>
                    <a href="?c=customer&a=FormEditar&id=<?=$r->id_customer?>" 
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