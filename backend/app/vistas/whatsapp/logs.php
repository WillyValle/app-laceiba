<!-- Content Wrapper. Contains page content -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fab fa-whatsapp text-success"></i> Historial de Envíos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?c=inicio">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?c=whatsapp&a=Dashboard">WhatsApp</a></li>
                        <li class="breadcrumb-item active">Historial</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="c" value="whatsapp">
                        <input type="hidden" name="a" value="Logs">
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Desde</label>
                                    <input type="date" class="form-control" name="date_from" 
                                           value="<?= $_GET['date_from'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Hasta</label>
                                    <input type="date" class="form-control" name="date_to" 
                                           value="<?= $_GET['date_to'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select class="form-control" name="status">
                                        <option value="">Todos</option>
                                        <option value="sent" <?= ($_GET['status'] ?? '') === 'sent' ? 'selected' : '' ?>>
                                            Enviado
                                        </option>
                                        <option value="failed" <?= ($_GET['status'] ?? '') === 'failed' ? 'selected' : '' ?>>
                                            Fallido
                                        </option>
                                        <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>
                                            Pendiente
                                        </option>
                                        <option value="retry" <?= ($_GET['status'] ?? '') === 'retry' ? 'selected' : '' ?>>
                                            Reintentando
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filtrar
                                        </button>
                                        <a href="?c=whatsapp&a=Logs" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Logs -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Registros de Envíos</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($data['logs'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay registros que mostrar.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Teléfono</th>
                                        <th>Servicio</th>
                                        <th>Archivo</th>
                                        <th>Estado</th>
                                        <th>Intentos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['logs'] as $log): ?>
                                        <tr>
                                            <td><?= $log['ID_WHATSAPP_LOG'] ?></td>
                                            <td>
                                                <small>
                                                    <?= date('d/m/Y H:i', strtotime($log['CREATED_AT'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($log['CUSTOMER_NAME'] ?? 'N/A') ?>
                                                <br>
                                                <small class="text-muted">ID: <?= $log['CUSTOMER_ID_CUSTOMER'] ?></small>
                                            </td>
                                            <td>
                                                <a href="https://wa.me/<?= $log['PHONE'] ?>" target="_blank">
                                                    <i class="fab fa-whatsapp text-success"></i> 
                                                    <?= $log['PHONE'] ?>
                                                </a>
                                            </td>
                                            <td>
                                                <small>
                                                    #<?= $log['SERVICE_CODE'] ?? $log['SERVICE_ID_SERVICE'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($log['FILE_NAME']) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'sent' => 'success',
                                                    'failed' => 'danger',
                                                    'pending' => 'warning',
                                                    'retry' => 'info'
                                                ];
                                                $statusText = [
                                                    'sent' => 'Enviado',
                                                    'failed' => 'Fallido',
                                                    'pending' => 'Pendiente',
                                                    'retry' => 'Reintentando'
                                                ];
                                                $class = $statusClass[$log['STATUS']] ?? 'secondary';
                                                $text = $statusText[$log['STATUS']] ?? $log['STATUS'];
                                                ?>
                                                <span class="badge badge-<?= $class ?>">
                                                    <?= $text ?>
                                                </span>
                                                <?php if ($log['STATUS'] === 'sent' && $log['SENT_AT']): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($log['SENT_AT'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">
                                                    <?= $log['ATTEMPTS'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="viewDetails(<?= $log['ID_WHATSAPP_LOG'] ?>)"
                                                        title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if ($log['STATUS'] === 'failed'): ?>
                                                    <button class="btn btn-sm btn-warning" 
                                                            onclick="retryMessage(<?= $log['ID_WHATSAPP_LOG'] ?>)"
                                                            title="Reintentar envío">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if (isset($data['pagination'])): ?>
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p class="text-muted">
                                            Mostrando 
                                            <?= (($data['pagination']['current_page'] - 1) * $data['pagination']['per_page']) + 1 ?>
                                            - 
                                            <?= min($data['pagination']['current_page'] * $data['pagination']['per_page'], $data['pagination']['total_rows']) ?>
                                            de 
                                            <?= $data['pagination']['total_rows'] ?>
                                            registros
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <nav>
                                            <ul class="pagination justify-content-end mb-0">
                                                <?php
                                                $currentPage = $data['pagination']['current_page'];
                                                $totalPages = $data['pagination']['total_pages'];
                                                
                                                // Construir URL base con filtros
                                                $baseUrl = "?c=whatsapp&a=Logs";
                                                if (!empty($_GET['date_from'])) $baseUrl .= "&date_from=" . urlencode($_GET['date_from']);
                                                if (!empty($_GET['date_to'])) $baseUrl .= "&date_to=" . urlencode($_GET['date_to']);
                                                if (!empty($_GET['status'])) $baseUrl .= "&status=" . urlencode($_GET['status']);
                                                ?>
                                                
                                                <!-- Anterior -->
                                                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="<?= $baseUrl ?>&page=<?= $currentPage - 1 ?>">
                                                        Anterior
                                                    </a>
                                                </li>
                                                
                                                <!-- Páginas -->
                                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                                        <a class="page-link" href="<?= $baseUrl ?>&page=<?= $i ?>">
                                                            <?= $i ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <!-- Siguiente -->
                                                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="<?= $baseUrl ?>&page=<?= $currentPage + 1 ?>">
                                                        Siguiente
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Modal de Detalles -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Envío</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailsContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(logId) {
    $('#detailsModal').modal('show');
    $('#detailsContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Cargando detalles...</p>
        </div>
    `);
    
    $.ajax({
        url: '?c=whatsapp&a=Ajax',
        method: 'POST',
        data: {
            action: 'get_log_details',
            log_id: logId
        },
        success: function(response) {
            if (response.success && response.data) {
                const log = response.data;
                let html = `
                    <table class="table table-bordered">
                        <tr><th>ID Log:</th><td>${log.ID_WHATSAPP_LOG}</td></tr>
                        <tr><th>Cliente:</th><td>${log.CUSTOMER_NAME || 'N/A'}</td></tr>
                        <tr><th>Teléfono:</th><td>${log.PHONE}</td></tr>
                        <tr><th>Archivo:</th><td>${log.FILE_NAME}</td></tr>
                        <tr><th>Estado:</th><td><span class="badge badge-${log.STATUS === 'sent' ? 'success' : 'danger'}">${log.STATUS}</span></td></tr>
                        <tr><th>Intentos:</th><td>${log.ATTEMPTS}</td></tr>
                        <tr><th>Creado:</th><td>${log.CREATED_AT}</td></tr>
                        ${log.SENT_AT ? `<tr><th>Enviado:</th><td>${log.SENT_AT}</td></tr>` : ''}
                        ${log.ERROR_MESSAGE ? `<tr><th>Error:</th><td class="text-danger">${log.ERROR_MESSAGE}</td></tr>` : ''}
                        ${log.MESSAGE_ID ? `<tr><th>Message ID:</th><td><code>${log.MESSAGE_ID}</code></td></tr>` : ''}
                    </table>
                `;
                $('#detailsContent').html(html);
            } else {
                $('#detailsContent').html('<div class="alert alert-warning">No se pudieron cargar los detalles.</div>');
            }
        },
        error: function() {
            $('#detailsContent').html('<div class="alert alert-danger">Error al cargar los detalles.</div>');
        }
    });
}

function retryMessage(logId) {
    if (!confirm('¿Desea reintentar el envío de este mensaje?')) {
        return;
    }
    
    $.ajax({
        url: '?c=whatsapp&a=Ajax',
        method: 'POST',
        data: {
            action: 'retry',
            log_id: logId
        },
        success: function(response) {
            if (response.success) {
                alert('Mensaje reenviado exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'No se pudo reenviar el mensaje'));
            }
        },
        error: function() {
            alert('Error de conexión al intentar reenviar el mensaje');
        }
    });
}
</script>