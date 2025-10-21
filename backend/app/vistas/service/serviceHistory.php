<?php
/**
 * Vista: serviceHistory.php
 * Descripción: Muestra el historial de servicios finalizados del técnico actual
 * Con tabla completa, filtros de fecha y paginación
 */

// Obtener valores de filtros actuales para mantenerlos en el formulario
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t');
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-history"></i> 
                    <?= isset($titulo_pagina) ? $titulo_pagina : 'Historial de Servicios' ?>
                </h1>
                <?php if (isset($empleado_info) && $empleado_info): ?>
                    <small class="text-muted">
                        Técnico: <strong><?= htmlspecialchars($empleado_info->nombre_completo) ?></strong>
                    </small>
                <?php endif; ?>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    <a href="?c=service&a=VistaTecnico" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Mis Servicios
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mostrar mensajes de éxito -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars(urldecode($_GET['success'])); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Mostrar mensajes de error -->
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($errores) && !empty($errores)): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <h5><i class="icon fas fa-ban"></i> Error!</h5>
        <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Sección de filtros de fecha -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filtros de Fecha
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php
                    // Obtener valores de filtros actuales
                    $filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
                    $filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t');
                    $filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
                    $filtro_numero_servicio = isset($_GET['numero_servicio']) ? $_GET['numero_servicio'] : '';
                    ?>

                    <!-- Dentro de card-body -->
                    <form method="GET" action="">
                        <input type="hidden" name="c" value="service">
                        <input type="hidden" name="a" value="ObtenerHistorialTecnico">
                        
                        <div class="row">
                            <!-- Búsqueda por número de servicio -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="numero_servicio">
                                        <i class="fas fa-search"></i> N° Servicio
                                    </label>
                                    <input type="number" 
                                        name="numero_servicio" 
                                        id="numero_servicio" 
                                        class="form-control" 
                                        placeholder="Ej: 123"
                                        value="<?= htmlspecialchars($filtro_numero_servicio) ?>">
                                </div>
                            </div>

                            <!-- Filtro por cliente -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cliente">
                                        <i class="fas fa-building"></i> Cliente
                                    </label>
                                    <select name="cliente" id="cliente" class="form-control">
                                        <option value="">Todos los clientes</option>
                                        <?php if (isset($clientes) && !empty($clientes)): ?>
                                            <?php foreach($clientes as $cliente): ?>
                                                <option value="<?= $cliente->id_customer ?>" 
                                                        <?= $filtro_cliente == $cliente->id_customer ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cliente->name_customer) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Filtro por fecha desde -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_desde">
                                        <i class="fas fa-calendar-alt"></i> Fecha Desde
                                    </label>
                                    <input type="date" 
                                        name="fecha_desde" 
                                        id="fecha_desde" 
                                        class="form-control" 
                                        value="<?= htmlspecialchars($filtro_fecha_desde) ?>">
                                </div>
                            </div>

                            <!-- Filtro por fecha hasta -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_hasta">
                                        <i class="fas fa-calendar-alt"></i> Fecha Hasta
                                    </label>
                                    <input type="date" 
                                        name="fecha_hasta" 
                                        id="fecha_hasta" 
                                        class="form-control" 
                                        value="<?= htmlspecialchars($filtro_fecha_hasta) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="?c=service&a=ObtenerHistorialTecnico" class="btn btn-secondary">
                                    <i class="fas fa-eraser"></i> Limpiar Filtros
                                </a>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</section>

<!-- Tabla de historial de servicios -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table"></i> Servicios Finalizados
                    <?php if (isset($total_servicios)): ?>
                        <span class="badge badge-success"><?= $total_servicios ?> servicio(s) en total</span>
                    <?php endif; ?>
                </h3>
                <div class="card-tools">
                    <?php if (isset($pagina_actual) && isset($total_paginas)): ?>
                    <span class="text-muted">
                        Página <?= $pagina_actual ?> de <?= $total_paginas ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 600px;">
                <?php if (!empty($historial_servicios)): ?>
                    <table class="table table-head-fixed text-nowrap table-hover">
                        <thead">
                            <tr>
                                <th>N° Servicio</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th>Fecha Programada</th>
                                <th>Fecha de Inicio</th>
                                <th>Fecha de Finalización</th>
                                <th>Técnicos Asistentes</th>
                                <th>Categorías de Servicio</th>
                                <th>Métodos de Aplicación</th>
                                <th>Croquis</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($historial_servicios as $servicio): ?>
                                <tr>
                                    <!-- Número de servicio -->
                                    <td>
                                        <strong class="text-primary">
                                            #<?= htmlspecialchars($servicio->id_service) ?>
                                        </strong>
                                    </td>

                                    <!-- Estado del servicio -->
                                    <td>
                                        <span class="badge badge-success">
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
                                            <span class="text-muted">
                                                <i class="fas fa-times"></i> N/A
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Acciones (Descargar Reporte PDF) -->
                                    <td>
                                        <button id="download-btn-table-<?= $servicio->id_service ?>" 
                                                onclick="downloadServiceReport(<?= $servicio->id_service ?>)" 
                                                class="btn btn-sm btn-success mb-1" 
                                                title="Descargar reporte PDF">
                                            <i class="fas fa-download"></i> Descargar Reporte
                                        </button>
                                        <div id="pdf-status-table-<?= $servicio->id_service ?>" class="pdf-status-indicator"></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info m-3">
                        <h5><i class="icon fas fa-info"></i> Sin resultados</h5>
                        No se encontraron servicios finalizados en el período seleccionado.
                        <?php if (isset($filtro_fecha_desde) && isset($filtro_fecha_hasta)): ?>
                            <br>
                            <small class="text-muted">
                                Período consultado: <?= date('d/m/Y', strtotime($filtro_fecha_desde)) ?> 
                                al <?= date('d/m/Y', strtotime($filtro_fecha_hasta)) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Paginación -->
            <?php if (isset($total_paginas) && $total_paginas > 1): ?>
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                    <!-- Primera página -->
                    <?php if (isset($pagina_actual) && $pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= construirUrlPaginacionHistorial(1) ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Página anterior -->
                    <?php if (isset($pagina_actual) && $pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= construirUrlPaginacionHistorial($pagina_actual - 1) ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Páginas numeradas -->
                    <?php
                    if (isset($pagina_actual) && isset($total_paginas)) {
                        $rango_inicio = max(1, $pagina_actual - 2);
                        $rango_fin = min($total_paginas, $pagina_actual + 2);
                        
                        for ($i = $rango_inicio; $i <= $rango_fin; $i++):
                    ?>
                        <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                            <a class="page-link" href="<?= construirUrlPaginacionHistorial($i) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php 
                        endfor;
                    }
                    ?>

                    <!-- Página siguiente -->
                    <?php if (isset($pagina_actual) && isset($total_paginas) && $pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= construirUrlPaginacionHistorial($pagina_actual + 1) ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Última página -->
                    <?php if (isset($pagina_actual) && isset($total_paginas) && $pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= construirUrlPaginacionHistorial($total_paginas) ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Mostrar información de paginación -->
                <div class="float-left">
                    <?php if (isset($pagina_actual) && isset($total_servicios)): ?>
                        <small class="text-muted">
                            Mostrando página <?= $pagina_actual ?> de <?= $total_paginas ?> 
                            (Total: <?= $total_servicios ?> servicios)
                        </small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
                    </div>

<!-- Script para descargar reportes (reutiliza la función de serviceTableAdmin.php) -->
<script>
// Función simplificada para descargar reporte PDF
function downloadServiceReport(serviceId) {
    const button = document.getElementById('download-btn-table-' + serviceId);
    const statusDiv = document.getElementById('pdf-status-table-' + serviceId);
    
    if (button) {
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Descargando...';
        button.disabled = true;
        
        // Verificar si el PDF existe antes de intentar descarga
        fetch('check_pdf_status.php?service_id=' + serviceId)
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    // PDF disponible, iniciar descarga directa
                    window.location.href = 'download_report.php?service_id=' + serviceId;
                    showPdfMessage('Descarga iniciada correctamente', 'success');
                    
                    if (statusDiv) {
                        statusDiv.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> PDF listo</small>';
                    }
                } else {
                    // PDF no disponible
                    showPdfMessage('El PDF no está disponible. Debe finalizar el servicio primero.', 'warning');
                    
                    if (statusDiv) {
                        statusDiv.innerHTML = '<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> PDF no generado</small>';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showPdfMessage('Error al verificar el PDF', 'error');
            })
            .finally(() => {
                // Restaurar botón
                button.innerHTML = originalHtml;
                button.disabled = false;
            });
    }
}

// Función auxiliar para mostrar mensajes
function showPdfMessage(message, type) {
    let alertClass = 'alert-info';
    switch(type) {
        case 'success': alertClass = 'alert-success'; break;
        case 'warning': alertClass = 'alert-warning'; break;
        case 'error': alertClass = 'alert-danger'; break;
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show mx-3 mt-3`;
    alertDiv.innerHTML = `
        <i class="fas fa-info-circle"></i> ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    const contentHeader = document.querySelector('.content-header');
    if (contentHeader) {
        contentHeader.parentNode.insertBefore(alertDiv, contentHeader.nextSibling);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
}
</script>

<?php
/**
 * Función auxiliar para construir URL de paginación
 * Debe estar definida en el controlador, pero si no está disponible aquí está el fallback
 */
if (!function_exists('construirUrlPaginacionHistorial')) {
    function construirUrlPaginacionHistorial($pagina) {
        $params = [
            'c' => 'service',
            'a' => 'ObtenerHistorialTecnico',
            'pagina' => $pagina
        ];
        
        if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
            $params['fecha_desde'] = $_GET['fecha_desde'];
        }
        if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
            $params['fecha_hasta'] = $_GET['fecha_hasta'];
        }
        if (isset($_GET['cliente']) && !empty($_GET['cliente'])) {
            $params['cliente'] = $_GET['cliente'];
        }
        if (isset($_GET['numero_servicio']) && !empty($_GET['numero_servicio'])) {
            $params['numero_servicio'] = $_GET['numero_servicio'];
        }
        
        return '?' . http_build_query($params);
    }
}
?>