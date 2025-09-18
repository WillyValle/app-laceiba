<?php
// Obtener valores de filtros actuales para mantenerlos en el formulario
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$filtro_empleado = isset($_GET['empleado']) ? $_GET['empleado'] : '';
?>

<!-- CSS específico para Kanban solo en esta vista -->
<style>
/* Aplicar estilos kanban solo a esta página */
.kanban-container .card-row {
    display: inline-block;
    vertical-align: top;
    width: 30%;
    margin-right: 3%;
    margin-bottom: 0;
}
.kanban-container .card-row:last-child {
    margin-right: 0;
}
.kanban-container .card-row .card-body {
    min-height: 500px;
    max-height: 70vh;
    overflow-y: auto;
}
@media (max-width: 768px) {
    .kanban-container .card-row {
        display: block;
        width: 100%;
        margin-right: 0;
        margin-bottom: 15px;
    }
}
</style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Servicios - Kanban Board</h1>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de filtros como card -->
    <section class="content pb-3">
        <div class="container-fluid">
            <div class="card collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i> Filtros de Búsqueda
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <form method="GET" action="">
                        <input type="hidden" name="c" value="service">
                        <div class="row">
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

                            <!-- Filtro por fecha desde -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_desde">Desde</label>
                                    <input type="date" name="fecha_desde" id="fecha_desde" 
                                           class="form-control" value="<?= $filtro_fecha_desde ?>">
                                </div>
                            </div>

                            <!-- Filtro por fecha hasta -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_hasta">Hasta</label>
                                    <input type="date" name="fecha_hasta" id="fecha_hasta" 
                                           class="form-control" value="<?= $filtro_fecha_hasta ?>">
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
                        </div>

                        <!-- Botones de acción -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="?c=service" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar Filtros
                                </a>
                                <a href="?c=service&a=NuevoServicio" class="btn btn-success float-right">
                                    <i class="fas fa-plus"></i> Nuevo Servicio
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content - Kanban Board nativo AdminLTE -->
    <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper kanban">
    <section class="content pb-3">
        
        <div class="container-fluid h-100">
            
            
            <!-- Columna 1: Servicios Programados -->
            <div class="card card-row card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-check"></i> Servicios Programados
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary">
                            <?= count($serviciosProgramados) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($serviciosProgramados)): ?>
                        <?php foreach($serviciosProgramados as $servicio): ?>
                            <!-- Tarjeta individual de servicio programado -->
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <strong><?= htmlspecialchars($servicio->name_customer) ?></strong>
                                    </h5>
                                    <div class="card-tools">
                                        <a href="#" class="btn btn-tool btn-link">
                                            #<?= htmlspecialchars($servicio->id_service) ?>
                                        </a>
                                        <a href="?c=service&a=ReprogramarServicio&id=<?= $servicio->id_service ?>" 
                                        class="btn btn-tool" title="Reprogramar servicio">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Información del cliente -->
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <small><?= htmlspecialchars($servicio->address_customer) ?></small>
                                    </div>
                                    
                                    <!-- Fecha programada -->
                                    <div class="info-item">
                                        <i class="fas fa-calendar text-primary"></i>
                                        <small>
                                            <?= $servicio->preset_dt_hr ? date('d/m/Y H:i', strtotime($servicio->preset_dt_hr)) : 'No programado' ?>
                                        </small>
                                    </div>

                                    <!-- Empleados asignados -->
                                    <?php if ($servicio->empleados_asignados): ?>
                                    <div class="info-item">
                                        <i class="fas fa-user text-success"></i>
                                        <small><?= htmlspecialchars($servicio->empleados_asignados) ?></small>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Teléfono del cliente si existe -->
                                    <?php if (isset($servicio->customer_whatsapp) && $servicio->customer_whatsapp): ?>
                                    <div class="info-item">
                                        <i class="fab fa-whatsapp text-success"></i>
                                        <small><?= htmlspecialchars($servicio->customer_whatsapp) ?></small>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Notas si existen -->
                                    <?php if (isset($servicio->notes) && $servicio->notes): ?>
                                    <div class="info-item">
                                        <i class="fas fa-sticky-note text-warning"></i>
                                        <small><?= htmlspecialchars(substr($servicio->notes, 0, 100)) ?>...</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Mensaje cuando no hay servicios programados -->
                        <div class="card card-light card-outline">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay servicios programados</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna 2: Servicios en Ejecución -->
            <div class="card card-row card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog fa-spin"></i> En Ejecución
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">
                            <?= count($serviciosEjecucion) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($serviciosEjecucion)): ?>
                        <?php foreach($serviciosEjecucion as $servicio): ?>
                            <!-- Tarjeta individual de servicio en ejecución -->
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <strong><?= htmlspecialchars($servicio->name_customer) ?></strong>
                                    </h5>
                                    <div class="card-tools">
                                        <a href="#" class="btn btn-tool btn-link">
                                            #<?= htmlspecialchars($servicio->id_service) ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Información del cliente -->
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <small><?= htmlspecialchars($servicio->address_customer) ?></small>
                                    </div>
                                    
                                    <!-- Fecha de inicio -->
                                    <div class="info-item">
                                        <i class="fas fa-play text-success"></i>
                                        <small>
                                            Iniciado: <?= $servicio->start_dt_hr ? date('d/m/Y H:i', strtotime($servicio->start_dt_hr)) : 'No iniciado' ?>
                                        </small>
                                    </div>

                                    <!-- Empleados asignados -->
                                    <?php if ($servicio->empleados_asignados): ?>
                                    <div class="info-item">
                                        <i class="fas fa-user text-success"></i>
                                        <small><?= htmlspecialchars($servicio->empleados_asignados) ?></small>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Indicador de tiempo activo -->
                                    <div class="info-item">
                                        <i class="fas fa-clock text-warning"></i>
                                        <small>
                                            <span class="badge badge-warning">EN PROCESO</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Mensaje cuando no hay servicios en ejecución -->
                        <div class="card card-light card-outline">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay servicios en ejecución</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna 3: Servicios Finalizados -->
            <div class="card card-row card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle"></i> Finalizados
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success">
                            <?= count($serviciosFinalizados) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($serviciosFinalizados)): ?>
                        <?php foreach($serviciosFinalizados as $servicio): ?>
                            <!-- Tarjeta individual de servicio finalizado -->
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <strong><?= htmlspecialchars($servicio->name_customer) ?></strong>
                                    </h5>
                                    <div class="card-tools">
                                        <a href="#" class="btn btn-tool btn-link">
                                            #<?= htmlspecialchars($servicio->id_service) ?>
                                        </a>
                                        <a href="?c=service&a=VerDetalle&id=<?= $servicio->id_service ?>" 
                                           class="btn btn-tool" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Información del cliente -->
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <small><?= htmlspecialchars($servicio->address_customer) ?></small>
                                    </div>
                                    
                                    <!-- Fecha de finalización -->
                                    <div class="info-item">
                                        <i class="fas fa-check text-success"></i>
                                        <small>
                                            Finalizado: <?= $servicio->end_dt_hr ? date('d/m/Y H:i', strtotime($servicio->end_dt_hr)) : 'Sin finalizar' ?>
                                        </small>
                                    </div>

                                    <!-- Empleados asignados -->
                                    <?php if ($servicio->empleados_asignados): ?>
                                    <div class="info-item">
                                        <i class="fas fa-user text-success"></i>
                                        <small><?= htmlspecialchars($servicio->empleados_asignados) ?></small>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Botón de descarga de orden -->
                                    <div class="text-center mt-2">
                                    <button id="download-btn-<?= $servicio->id_service ?>" 
                                            onclick="downloadServiceReport(<?= $servicio->id_service ?>)" 
                                            class="btn btn-success btn-sm" 
                                            title="Descargar reporte PDF">
                                        <i class="fas fa-download"></i> Descargar Reporte
                                    </button>

                                    <!-- Indicador de estado del PDF -->
                                    <div id="pdf-status-<?= $servicio->id_service ?>" class="pdf-status-indicator"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Mensaje cuando no hay servicios finalizados -->
                        <div class="card card-light card-outline">
                            <div class="card-body text-center">
                                <i class="fas fa-check-double fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay servicios finalizados</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>
</div>
</div>


<script>
// Función simplificada para descargar reporte PDF
function downloadServiceReport(serviceId) {
    const button = document.getElementById('download-btn-table-' + serviceId) || 
                   document.getElementById('download-btn-' + serviceId);
    const statusDiv = document.getElementById('pdf-status-table-' + serviceId) || 
                      document.getElementById('pdf-status-' + serviceId);
    
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

// Función auxiliar para mostrar mensajes (nombre único para evitar conflictos)
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

// Verificar estado inicial de PDFs al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const downloadButtons = document.querySelectorAll('[id^="download-btn-table-"], [id^="download-btn-"]');
    
    downloadButtons.forEach(button => {
        const serviceId = button.id.replace('download-btn-table-', '').replace('download-btn-', '');
        const statusDiv = document.getElementById('pdf-status-table-' + serviceId) || 
                          document.getElementById('pdf-status-' + serviceId);
        
        // Verificar estado inicial del PDF
        fetch('check_pdf_status.php?service_id=' + serviceId)
            .then(response => response.json())
            .then(data => {
                if (statusDiv) {
                    if (data.available) {
                        statusDiv.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> PDF listo</small>';
                    } else {
                        statusDiv.innerHTML = '<small class="text-muted"><i class="fas fa-info-circle"></i> PDF se genera al finalizar</small>';
                    }
                }
            })
            .catch(error => {
                console.error('Error al verificar estado inicial:', error);
            });
    });
});
</script>

<style>
/* Estilos para indicadores de estado del PDF */
.pdf-status-indicator {
    margin-top: 2px;
    font-size: 0.7rem;
}

.pdf-status-indicator .text-success {
    color: #28a745 !important;
}

.pdf-status-indicator .text-warning {
    color: #ffc107 !important;
}

.pdf-status-indicator .text-muted {
    color: #6c757d !important;
}
</style>
