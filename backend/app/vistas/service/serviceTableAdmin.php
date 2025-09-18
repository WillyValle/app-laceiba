<?php
// Obtener valores de filtros actuales para mantenerlos en el formulario
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$filtro_empleado = isset($_GET['empleado']) ? $_GET['empleado'] : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$buscar_servicio = isset($_GET['buscar_servicio']) ? $_GET['buscar_servicio'] : '';
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Administración de Servicios</h1>
                <small class="text-muted">Vista completa en formato tabla</small>
            </div>
        </div>
    </div>
</section>

<!-- Mostrar mensajes de éxito -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($_GET['success']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Mostrar mensajes de error -->
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Sección de filtros y búsqueda -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filtros y Búsqueda
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="c" value="service">
                    <input type="hidden" name="a" value="VistaTablaAdmin">
                    
                    <div class="row">
                        <!-- Búsqueda por número de servicio -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="buscar_servicio">
                                    <i class="fas fa-search"></i> Número de Servicio
                                </label>
                                <input type="number" 
                                       name="buscar_servicio" 
                                       id="buscar_servicio" 
                                       class="form-control" 
                                       placeholder="Ej: 123"
                                       value="<?= htmlspecialchars($buscar_servicio) ?>">
                            </div>
                        </div>

                        <!-- Filtro por cliente -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cliente">Cliente</label>
                                <select name="cliente" id="cliente" class="form-control">
                                    <option value="">Todos los clientes</option>
                                    <?php foreach($clientes as $cliente): ?>
                                        <option value="<?= $cliente->id_customer ?>" 
                                                <?= $filtro_cliente == $cliente->id_customer ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cliente->name_customer) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Filtro por empleado -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="empleado">Empleado</label>
                                <select name="empleado" id="empleado" class="form-control">
                                    <option value="">Todos los empleados</option>
                                    <?php foreach($empleados as $empleado): ?>
                                        <option value="<?= $empleado->id_employee ?>" 
                                                <?= $filtro_empleado == $empleado->id_employee ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($empleado->nombre_completo) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Filtro por estado -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select name="estado" id="estado" class="form-control">
                                    <option value="">Todos los estados</option>
                                    <?php if (!empty($estados)): ?>
                                        <?php foreach($estados as $estado_item): ?>
                                            <option value="<?= isset($estado_item->id_service_status) ? $estado_item->id_service_status : '' ?>" 
                                                    <?= $filtro_estado == (isset($estado_item->id_service_status) ? $estado_item->id_service_status : '') ? 'selected' : '' ?>>
                                                <?= isset($estado_item->name_service_status) ? htmlspecialchars($estado_item->name_service_status) : 'Estado sin nombre' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Filtro por fecha desde -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_desde">Fecha Desde</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" 
                                       class="form-control" value="<?= htmlspecialchars($filtro_fecha_desde) ?>">
                            </div>
                        </div>

                        <!-- Filtro por fecha hasta -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_hasta">Fecha Hasta</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" 
                                       class="form-control" value="<?= htmlspecialchars($filtro_fecha_hasta) ?>">
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="?c=service&a=VistaTablaAdmin" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                                <a href="?c=service" class="btn btn-info">
                                    <i class="fas fa-th-large"></i> Vista Kanban
                                </a>
                                <a href="?c=service&a=NuevoServicio" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Nuevo Servicio
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Tabla de servicios -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table"></i> Servicios 
                    <span class="badge badge-info"><?= count($servicios) ?> de <?= $total_servicios ?> registros</span>
                </h3>
                <div class="card-tools">
                    <span class="text-muted">
                        Página <?= $pagina_actual ?> de <?= $total_paginas ?>
                    </span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <?php if (!empty($servicios)): ?>
                    <table class="table table-head-fixed text-nowrap">
                        <thead>
                            <tr>
                                <th>N° Servicio</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th>Fecha Programada</th>
                                <th>Fecha de Inicio</th>
                                <th>Fecha de Finalización</th>
                                <th>Técnico Encargado</th>
                                <th>Técnicos Asistentes</th>
                                <th>Categorías de Servicio</th>
                                <th>Métodos de Aplicación</th>
                                <th>Croquis</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($servicios as $servicio): ?>
                                <tr>
                                    <!-- Número de servicio -->
                                    <td>
                                        <strong class="text-primary">#<?= htmlspecialchars($servicio->id_service) ?></strong>
                                    </td>

                                    <!-- Estado del servicio -->
                                    <td>
                                        <?php
                                        $badge_class = 'secondary';
                                        switch($servicio->service_status_id_service_status) {
                                            case 1: $badge_class = 'warning'; break;  // Programado
                                            case 2: $badge_class = 'info'; break;     // En ejecución
                                            case 3: $badge_class = 'success'; break;  // Finalizado
                                            default: $badge_class = 'secondary';
                                        }
                                        ?>
                                        <span class="badge badge-<?= $badge_class ?>">
                                            <?= htmlspecialchars($servicio->name_service_status) ?>
                                        </span>
                                    </td>

                                    <!-- Cliente -->
                                    <td>
                                        <strong><?= htmlspecialchars($servicio->name_customer) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($servicio->address_customer) ?>
                                        </small>
                                    </td>

                                    <!-- Fecha Programada -->
                                    <td>
                                        <?php if ($servicio->preset_dt_hr): ?>
                                            <i class="fas fa-calendar text-primary"></i>
                                            <?= date('d/m/Y', strtotime($servicio->preset_dt_hr)) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($servicio->preset_dt_hr)) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted"><i>Sin programar</i></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Fecha de Inicio -->
                                    <td>
                                        <?php if ($servicio->start_dt_hr): ?>
                                            <i class="fas fa-play text-success"></i>
                                            <?= date('d/m/Y', strtotime($servicio->start_dt_hr)) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($servicio->start_dt_hr)) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted"><i>Sin iniciar</i></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Fecha de Finalización -->
                                    <td>
                                        <?php if ($servicio->end_dt_hr): ?>
                                            <i class="fas fa-check text-info"></i>
                                            <?= date('d/m/Y', strtotime($servicio->end_dt_hr)) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($servicio->end_dt_hr)) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted"><i>Sin finalizar</i></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Técnico Encargado -->
                                    <td>
                                        <?php if (isset($servicio->tecnico_encargado) && $servicio->tecnico_encargado): ?>
                                            <i class="fas fa-user-tie text-primary"></i>
                                            <?= htmlspecialchars($servicio->tecnico_encargado) ?>
                                        <?php else: ?>
                                            <span class="text-muted"><i>Sin asignar</i></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Técnicos Asistentes -->
                                    <td>
                                        <?php if (isset($servicio->tecnicos_asistentes) && $servicio->tecnicos_asistentes): ?>
                                            <i class="fas fa-users text-success"></i>
                                            <?= htmlspecialchars($servicio->tecnicos_asistentes) ?>
                                        <?php else: ?>
                                            <span class="text-muted"><i>Sin asistentes</i></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Categorías de Servicio -->
                                    <td>
                                        <?php if (isset($servicio->categorias_servicio) && $servicio->categorias_servicio): ?>
                                            <i class="fas fa-tags text-info"></i>
                                            <?= htmlspecialchars($servicio->categorias_servicio) ?>
                                        <?php else: ?>
                                            <span class="text-muted"><i>Sin categorías</i></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Métodos de Aplicación -->
                                    <td>
                                        <?php if (isset($servicio->metodos_aplicacion) && $servicio->metodos_aplicacion): ?>
                                            <i class="fas fa-cogs text-warning"></i>
                                            <?= htmlspecialchars($servicio->metodos_aplicacion) ?>
                                        <?php else: ?>
                                            <span class="text-muted"><i>Sin métodos</i></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Croquis -->
                                    <td>
                                        <?php if (isset($servicio->tiene_croquis) && $servicio->tiene_croquis > 0): ?>
                                            <a href="<?= htmlspecialchars($servicio->ruta_croquis) ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Ver croquis">
                                                <i class="fas fa-file-pdf"></i> Ver
                                            </a>
                                        <?php else: ?>
                                            <a href="?c=service&a=SubirCroquis&id=<?= $servicio->id_service ?>" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Subir croquis">
                                                <i class="fas fa-upload"></i> Subir
                                            </a>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Acciones -->
                                    <td>
                                        <!-- Botón ver reporte (placeholder) -->
                                        <a href="#" class="btn btn-sm btn-outline-primary mb-1" title="Ver reporte">
                                            <i class="fas fa-file-alt"></i> Reporte
                                        </a>
                                        <br>
                                        
                                        <!-- Botón editar si está programado -->
                                        <?php if ($servicio->service_status_id_service_status == 1): ?>
                                            <a href="?c=service&a=ReprogramarServicio&id=<?= $servicio->id_service ?>" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Reprogramar">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center p-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron servicios</h5>
                        <p class="text-muted">Intenta ajustar los filtros de búsqueda o crear un nuevo servicio.</p>
                        <a href="?c=service&a=NuevoServicio" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Crear Nuevo Servicio
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Paginación con elementos nativos de AdminLTE v3 -->
            <?php if ($total_paginas > 1): ?>
            <div class="card-footer clearfix">
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted">
                            Mostrando <?= count($servicios) ?> de <?= $total_servicios ?> servicios
                            (Página <?= $pagina_actual ?> de <?= $total_paginas ?>)
                        </p>
                    </div>
                    <div class="col-md-6">
                        <ul class="pagination pagination-sm m-0 float-right">
                            
                            <!-- Botón Anterior -->
                            <?php if ($pagina_actual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion($pagina_actual - 1) ?>">
                                        <i class="fas fa-angle-left"></i> Anterior
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-angle-left"></i> Anterior
                                    </span>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Páginas numeradas -->
                            <?php
                            // Calcular rango de páginas a mostrar
                            $inicio_pag = max(1, $pagina_actual - 2);
                            $fin_pag = min($total_paginas, $pagina_actual + 2);
                            
                            // Mostrar primera página si no está en el rango
                            if ($inicio_pag > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion(1) ?>">1</a>
                                </li>
                                <?php if ($inicio_pag > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Páginas del rango actual -->
                            <?php for ($i = $inicio_pag; $i <= $fin_pag; $i++): ?>
                                <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                                    <?php if ($i == $pagina_actual): ?>
                                        <span class="page-link"><?= $i ?></span>
                                    <?php else: ?>
                                        <a class="page-link" href="<?= $this->construirUrlPaginacion($i) ?>"><?= $i ?></a>
                                    <?php endif; ?>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Mostrar última página si no está en el rango -->
                            <?php if ($fin_pag < $total_paginas): ?>
                                <?php if ($fin_pag < $total_paginas - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion($total_paginas) ?>"><?= $total_paginas ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Botón Siguiente -->
                            <?php if ($pagina_actual < $total_paginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion($pagina_actual + 1) ?>">
                                        Siguiente <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        Siguiente <i class="fas fa-angle-right"></i>
                                    </span>
                                </li>
                            <?php endif; ?>
                            
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
                            </div>