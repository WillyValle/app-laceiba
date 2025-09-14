
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= isset($titulo_pagina) ? $titulo_pagina : 'Mis Servicios Programados' ?></h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <?php if (isset($_GET['error']) && !empty($_GET['error'])): ?>
            <!-- Mostrar mensaje de error si existe -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <?= htmlspecialchars(urldecode($_GET['error'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($errores) && !empty($errores)): ?>
            <!-- Mostrar errores si existen -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <ul class="mb-0">
                            <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Header de información -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-list"></i>
                                Panel de Servicios Técnico
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="location.reload()">
                                    <i class="fas fa-sync"></i> Actualizar
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Aquí se muestran los servicios programados donde usted está asignado como encargado. 
                                Presione <strong>"Iniciar"</strong> para comenzar un servicio programado.
                            </p>
                            <?php if (isset($servicios_programados)): ?>
                            <p class="text-info">
                                <i class="fas fa-tasks"></i>
                                <strong><?= count($servicios_programados) ?></strong> servicio(s) programado(s) encontrado(s)
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servicios Programados -->
            <div class="row">
                <?php if (isset($servicios_programados) && !empty($servicios_programados)): ?>
                    <?php foreach ($servicios_programados as $servicio): ?>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Small card para cada servicio -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <!-- Información del cliente -->
                                <h4 class="text-white">
                                    <i class="fas fa-building"></i>
                                    <?= htmlspecialchars($servicio->name_customer) ?>
                                </h4>
                                
                                <!-- Fecha programada -->
                                <p class="text-white mb-1">
                                    <i class="fas fa-calendar-alt"></i>
                                    <strong>Programado:</strong> 
                                    <?= date('d/m/Y g:i A', strtotime($servicio->preset_dt_hr)) ?>
                                </p>
                                
                                <!-- Dirección -->
                                <p class="text-white mb-1">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>Dirección:</strong> 
                                    <?= htmlspecialchars($servicio->address_customer) ?>
                                </p>
                                
                                <!-- Encargado -->
                                <p class="text-white mb-1">
                                    <i class="fas fa-user-tie"></i>
                                    <strong>Encargado:</strong> 
                                    <?= htmlspecialchars($servicio->empleado_encargado) ?>
                                </p>
                                
                                <!-- Información adicional -->
                                <?php if (!empty($servicio->customer_whatsapp)): ?>
                                <p class="text-white mb-1">
                                    <i class="fab fa-whatsapp"></i>
                                    <strong>WhatsApp:</strong> 
                                    <?= htmlspecialchars($servicio->customer_whatsapp) ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($servicio->notes)): ?>
                                <p class="text-white-50 small mb-0">
                                    <i class="fas fa-sticky-note"></i>
                                    <strong>Notas:</strong> 
                                    <?= htmlspecialchars(substr($servicio->notes, 0, 100)) ?>
                                    <?= strlen($servicio->notes) > 100 ? '...' : '' ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Ícono del card -->
                            <div class="icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            
                            <!-- Botón de acción para iniciar servicio -->
                            <a href="?c=service&a=IniciarServicio&id=<?= $servicio->id_service ?>&empleado=<?= isset($empleado_actual) ? $empleado_actual : '' ?>" 
                               class="small-box-footer"
                               onclick="return confirm('¿Está seguro que desea iniciar este servicio? Se registrará automáticamente la fecha y hora de inicio.');">
                                <i class="fas fa-play"></i> Iniciar
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                <?php else: ?>
                    <!-- Mensaje cuando no hay servicios programados -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="empty-state py-4">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-clipboard-check fa-5x text-muted"></i>
                                    </div>
                                    <h3 class="mt-4 text-muted">No hay servicios programados</h3>
                                    <p class="text-muted">
                                        No tiene servicios programados asignados como encargado en este momento.
                                        <br>
                                        Los nuevos servicios aparecerán aquí cuando sean asignados.
                                    </p>
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-primary" onclick="location.reload()">
                                            <i class="fas fa-sync"></i> Actualizar
                                        </button>
                                        <?php if (isset($empleado_actual) && $empleado_actual > 0): ?>
                                        <a href="?c=service&a=ObtenerHistorialTecnico&empleado=<?= $empleado_actual ?>" class="btn btn-outline-secondary ml-2">
                                            <i class="fas fa-history"></i> Ver Historial
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Información adicional y acciones -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i>
                                Información Importante
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-exclamation-triangle text-warning"></i> Instrucciones</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Verifique la dirección antes de dirigirse al cliente</li>
                                        <li><i class="fas fa-check text-success"></i> Confirme la cita por WhatsApp o teléfono</li>
                                        <li><i class="fas fa-check text-success"></i> Lleve todo el equipo necesario para el servicio</li>
                                        <li><i class="fas fa-check text-success"></i> Presione "Iniciar" solo cuando esté en el lugar</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5><i class="fas fa-clock text-info"></i> Recordatorios</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-arrow-right text-muted"></i> La fecha de inicio se registra automáticamente</li>
                                        <li><i class="fas fa-arrow-right text-muted"></i> Complete toda la información durante el servicio</li>
                                        <li><i class="fas fa-arrow-right text-muted"></i> No olvide finalizar el servicio al terminar</li>
                                        <li><i class="fas fa-arrow-right text-muted"></i> Tome fotografías si es necesario</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enlaces rápidos -->
             


            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Próximo Servicio</span>
                            <span class="info-box-number">
                                <?php if (isset($servicios_programados) && !empty($servicios_programados)): ?>
                                    <?php
                                    // Encontrar el próximo servicio por fecha
                                    $proximo_servicio = null;
                                    $ahora = time();
                                    foreach ($servicios_programados as $serv) {
                                        $fecha_servicio = strtotime($serv->preset_dt_hr);
                                        if ($fecha_servicio >= $ahora) {
                                            if ($proximo_servicio === null || $fecha_servicio < strtotime($proximo_servicio->preset_dt_hr)) {
                                                $proximo_servicio = $serv;
                                            }
                                        }
                                    }
                                    
                                    if ($proximo_servicio) {
                                        echo date('d/m/Y g:i A', strtotime($proximo_servicio->preset_dt_hr));
                                    } else {
                                        echo '--:--';
                                    }
                                    ?>
                                <?php else: ?>
                                --:--
                                <?php endif; ?>
                            </span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                <?php if (isset($proximo_servicio)): ?>
                                    <?= htmlspecialchars($proximo_servicio->name_customer) ?>
                                <?php else: ?>
                                    No hay servicios próximos
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Programados Hoy</span>
                            <span class="info-box-number">
                                <?php
                                $hoy = date('Y-m-d');
                                $servicios_hoy = 0;
                                if (isset($servicios_programados)) {
                                    foreach ($servicios_programados as $serv) {
                                        if (date('Y-m-d', strtotime($serv->preset_dt_hr)) === $hoy) {
                                            $servicios_hoy++;
                                        }
                                    }
                                }
                                echo $servicios_hoy;
                                ?>
                            </span>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Servicios programados para hoy
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-history"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Historial</span>
                            <span class="info-box-number">Servicios Anteriores</span>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                <?php if (isset($empleado_actual) && $empleado_actual > 0): ?>
                                <a href="?c=service&a=ObtenerHistorialTecnico&empleado=<?= $empleado_actual ?>" class="text-info">
                                    Ver historial completo
                                </a>
                                <?php else: ?>
                                <span class="text-muted">Historial no disponible</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
                
                

        </div><!-- /.container-fluid -->
    </section><!-- /.content -->
</div>

<!-- Script adicional para funcionalidad básica (opcional) -->
<script>
// Función para actualizar automáticamente la página cada 5 minutos
setTimeout(function(){
    if (confirm('¿Desea actualizar la lista de servicios?')) {
        location.reload();
    }
}, 300000); // 5 minutos

// Función para confirmar inicio de servicio
function confirmarInicioServicio(nombreCliente, idServicio, empleadoId) {
    if (confirm('¿Está seguro que desea iniciar el servicio para ' + nombreCliente + '?\n\nSe registrará automáticamente la fecha y hora de inicio.')) {
        window.location.href = '?c=service&a=IniciarServicio&id=' + idServicio + '&empleado=' + empleadoId;
    }
    return false;
}
</script>