<!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= isset($titulo_pagina) ? $titulo_pagina : 'Control de Servicios Asignados' ?></h1>
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

            <?php if (isset($_GET['success']) && !empty($_GET['success'])): ?>
            <!-- Mostrar mensaje de éxito si existe -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-check"></i> Éxito!</h5>
                        <?= htmlspecialchars(urldecode($_GET['success'])) ?>
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
                                Control de Servicios Asignados
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
                                Aquí se muestran sus servicios asignados. Los servicios <strong class="text-success">programados</strong> pueden iniciarse, 
                                y los servicios <strong class="text-warning">en ejecución</strong> pueden continuarse para completar su finalización.
                            </p>
                            <!-- Resumen de servicios -->
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="text-success mb-1">
                                        <i class="fas fa-calendar-plus"></i>
                                        <strong><?= isset($servicios_programados) ? count($servicios_programados) : 0 ?></strong> servicio(s) programado(s)
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-warning mb-1">
                                        <i class="fas fa-play-circle"></i>
                                        <strong><?= isset($servicios_iniciados) ? count($servicios_iniciados) : 0 ?></strong> servicio(s) en ejecución
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-info mb-1">
                                        <i class="fas fa-chart-line"></i>
                                        <strong><?= isset($total_finalizados_mes) ? $total_finalizados_mes : 0 ?></strong> finalizado(s) este mes
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 1: SERVICIOS PROGRAMADOS (Cards Verdes) -->
            <?php if (isset($servicios_programados) && !empty($servicios_programados)): ?>
            <div class="row">
                <div class="col-12">
                    <h3 class="text-success">
                        <i class="fas fa-calendar-alt"></i> 
                        Servicios Programados 
                        <small class="text-muted">(<?= count($servicios_programados) ?>)</small>
                    </h3>
                    <p class="text-muted">Presione <strong>"Iniciar"</strong> para comenzar un servicio programado.</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($servicios_programados as $servicio): ?>
                <div class="col-lg-4 col-md-6 col-12">
                    <!-- Small card VERDE para servicios programados -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <!-- Información del cliente -->
                            <h4 class="text-white">
                                <i class="fas fa-building"></i>
                                <?= htmlspecialchars($servicio->name_customer) ?>
                            </h4>
                            
                            <!-- Número de Servicio -->
                            <p class="text-white mb-1">
                                <i class="fas fa-hashtag"></i>
                                <strong>Servicio No. </strong>
                                <?= htmlspecialchars($servicio->id_service) ?>
                            </p>

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
                        <a href="?c=service&a=IniciarServicio&id=<?= $servicio->id_service ?>" 
                           class="small-box-footer"
                           onclick="return confirm('¿Está seguro que desea iniciar este servicio? Se registrará automáticamente la fecha y hora de inicio.');">
                            <i class="fas fa-play"></i> Iniciar
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- SECCIÓN 2: SERVICIOS EN EJECUCIÓN (Cards Anaranjados) -->
            <?php if (isset($servicios_iniciados) && !empty($servicios_iniciados)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h3 class="text-warning">
                        <i class="fas fa-play-circle"></i> 
                        Servicios en Ejecución 
                        <small class="text-muted">(<?= count($servicios_iniciados) ?>)</small>
                    </h3>
                    <p class="text-muted">Presione <strong>"Continuar"</strong> para completar la finalización de un servicio iniciado.</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($servicios_iniciados as $servicio): ?>
                <div class="col-lg-4 col-md-6 col-12">
                    <!-- Small card ANARANJADO para servicios iniciados -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <!-- Información del cliente -->
                            <h4 class="text-white">
                                <i class="fas fa-building"></i>
                                <?= htmlspecialchars($servicio->name_customer) ?>
                            </h4>

                            <!-- Número de Servicio -->
                            <p class="text-white mb-1">
                                <i class="fas fa-hashtag"></i>
                                <strong>Servicio No. </strong>
                                <?= htmlspecialchars($servicio->id_service) ?>
                            </p>
                            
                            <!-- Fecha programada -->
                            <p class="text-white mb-1">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Programado:</strong> 
                                <?= date('d/m/Y g:i A', strtotime($servicio->preset_dt_hr)) ?>
                            </p>
                            
                            <!-- Fecha de inicio -->
                            <?php if (!empty($servicio->start_dt_hr)): ?>
                            <p class="text-white mb-1">
                                <i class="fas fa-clock"></i>
                                <strong>Iniciado:</strong> 
                                <?= date('d/m/Y g:i A', strtotime($servicio->start_dt_hr)) ?>
                            </p>
                            <?php endif; ?>
                            
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
                                <?= htmlspecialchars(substr($servicio->notes, 0, 80)) ?>
                                <?= strlen($servicio->notes) > 80 ? '...' : '' ?>
                            </p>
                            <?php endif; ?>

                            <!-- Tiempo transcurrido -->
                            <?php if (isset($servicio->minutos_transcurridos)): ?>
                            <p class="text-white-50 small mb-1">
                                <i class="fas fa-stopwatch"></i>
                                <strong>Tiempo transcurrido:</strong> 
                                <?php 
                                $horas = floor($servicio->minutos_transcurridos / 60);
                                $minutos = $servicio->minutos_transcurridos % 60;
                                echo $horas > 0 ? "{$horas}h {$minutos}min" : "{$minutos}min";
                                ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Ícono del card -->
                        <div class="icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        
                        <!-- Botón de acción para continuar/finalizar servicio -->
                        <a href="?c=service&a=ContinuarServicio&id=<?= $servicio->id_service ?>" 
                           class="small-box-footer">
                            <i class="fas fa-arrow-right"></i> Continuar
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Mensaje cuando no hay servicios -->
            <?php if ((!isset($servicios_programados) || empty($servicios_programados)) && 
                      (!isset($servicios_iniciados) || empty($servicios_iniciados))): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="empty-state py-4">
                                <div class="empty-state-icon">
                                    <i class="fas fa-clipboard-check fa-5x text-muted"></i>
                                </div>
                                <h3 class="mt-4 text-muted">No hay servicios asignados</h3>
                                <p class="text-muted">
                                    No tiene servicios programados ni en ejecución asignados como encargado en este momento.
                                    <br>
                                    Los nuevos servicios aparecerán aquí cuando sean asignados.
                                </p>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                                        <i class="fas fa-sync"></i> Actualizar
                                    </button>
                                    <?php if (isset($empleado_actual) && $empleado_actual > 0): ?>
                                    <a href="?c=service&a=ObtenerHistorialTecnico" class="btn btn-outline-secondary ml-2">
                                        <i class="fas fa-history"></i> Ver Historial
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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
                                    <h5><i class="fas fa-exclamation-triangle text-warning"></i> Servicios Programados</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Verifique la dirección antes de dirigirse al cliente</li>
                                        <li><i class="fas fa-check text-success"></i> Confirme la cita por WhatsApp o teléfono</li>
                                        <li><i class="fas fa-check text-success"></i> Lleve todo el equipo necesario para el servicio</li>
                                        <li><i class="fas fa-check text-success"></i> Presione "Iniciar" solo cuando esté en el lugar</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5><i class="fas fa-play-circle text-warning"></i> Servicios en Ejecución</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-arrow-right text-muted"></i> Complete toda la información durante el servicio</li>
                                        <li><i class="fas fa-arrow-right text-muted"></i> No olvide finalizar el servicio al terminar</li>
                                        <li><i class="fas fa-arrow-right text-muted"></i> Tome fotografías si es necesario</li>
                                        <li><i class="fas fa-arrow-right text-muted"></i> Puede continuar donde lo dejó</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard de estadísticas mejorado -->
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-calendar-plus"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Programados</span>
                            <span class="info-box-number">
                                <?= isset($total_programados) ? $total_programados : 0 ?>
                            </span>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Servicios por iniciar
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-play-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">En Ejecución</span>
                            <span class="info-box-number">
                                <?= isset($total_iniciados) ? $total_iniciados : 0 ?>
                            </span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Servicios por finalizar
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Finalizados (Mes)</span>
                            <span class="info-box-number">
                                <?= isset($total_finalizados_mes) ? $total_finalizados_mes : 0 ?>
                            </span>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Servicios completados
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary">
                            <i class="fas fa-history"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Historial</span>
                            <span class="info-box-number">
                                <?php if (isset($empleado_actual) && $empleado_actual > 0): ?>
                                <a href="?c=service&a=ObtenerHistorialTecnico" class="text-secondary">
                                    Ver Todo
                                </a>
                                <?php else: ?>
                                --
                                <?php endif; ?>
                            </span>
                            <div class="progress">
                                <div class="progress-bar bg-secondary" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Servicios anteriores
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Próximo servicio destacado -->
            <?php 
            $proximo_servicio = null;
            if (isset($servicios_programados) && !empty($servicios_programados)) {
                $ahora = time();
                foreach ($servicios_programados as $serv) {
                    $fecha_servicio = strtotime($serv->preset_dt_hr);
                    if ($fecha_servicio >= $ahora) {
                        if ($proximo_servicio === null || $fecha_servicio < strtotime($proximo_servicio->preset_dt_hr)) {
                            $proximo_servicio = $serv;
                        }
                    }
                }
            }
            ?>

            <?php if ($proximo_servicio): ?>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-star text-warning"></i>
                                Próximo Servicio Programado
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-success">
                                        <i class="fas fa-building"></i>
                                        <?= htmlspecialchars($proximo_servicio->name_customer) ?>
                                    </h5>
                                    <p class="mb-1">
                                        <i class="fas fa-calendar-alt text-muted"></i>
                                        <strong>Fecha:</strong> <?= date('d/m/Y g:i A', strtotime($proximo_servicio->preset_dt_hr)) ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                        <strong>Dirección:</strong> <?= htmlspecialchars($proximo_servicio->address_customer) ?>
                                    </p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <?php
                                    $tiempo_restante = strtotime($proximo_servicio->preset_dt_hr) - time();
                                    $dias = floor($tiempo_restante / (24 * 3600));
                                    $horas = floor(($tiempo_restante % (24 * 3600)) / 3600);
                                    ?>
                                    <p class="text-muted">Tiempo restante:</p>
                                    <h4 class="text-warning">
                                        <?php if ($dias > 0): ?>
                                            <?= $dias ?> día(s) <?= $horas ?> hora(s)
                                        <?php elseif ($horas > 0): ?>
                                            <?= $horas ?> hora(s)
                                        <?php else: ?>
                                            Menos de 1 hora
                                        <?php endif; ?>
                                    </h4>
                                    <a href="?c=service&a=IniciarServicio&id=<?= $proximo_servicio->id_service ?>" 
                                       class="btn btn-success"
                                       onclick="return confirm('¿Está seguro que desea iniciar este servicio?');">
                                        <i class="fas fa-play"></i> Iniciar Ahora
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
                
        </div><!-- /.container-fluid -->
    </section><!-- /.content -->
</div>

<!-- Script adicional para funcionalidad básica -->
<script>
// Función para actualizar automáticamente la página cada 5 minutos
setTimeout(function(){
    if (confirm('¿Desea actualizar la lista de servicios?')) {
        location.reload();
    }
}, 300000); // 5 minutos

// Función para confirmar inicio de servicio
function confirmarInicioServicio(nombreCliente, idServicio) {
    if (confirm('¿Está seguro que desea iniciar el servicio para ' + nombreCliente + '?\n\nSe registrará automáticamente la fecha y hora de inicio.')) {
        window.location.href = '?c=service&a=IniciarServicio&id=' + idServicio;
    }
    return false;
}

// Función para confirmar continuación de servicio
function confirmarContinuarServicio(nombreCliente, idServicio) {
    if (confirm('¿Desea continuar completando el servicio para ' + nombreCliente + '?')) {
        window.location.href = '?c=service&a=ContinuarServicio&id=' + idServicio;
    }
    return false;
}
</script>

<!-- AGREGAR DEBUG INFO para identificar problemas de sesión -->
<?php if (!isset($empleado_actual) || $empleado_actual <= 0): ?>
<div class="alert alert-warning">
    <h4><i class="fas fa-exclamation-triangle"></i> Problema de Identificación</h4>
    <p>No se pudo obtener su ID de empleado automáticamente. Información de debug:</p>
    <ul>
        <li><strong>Estado de sesión:</strong> <?= session_status() == PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva' ?></li>
        <li><strong>Variables de sesión disponibles:</strong> 
            <?php 
            if (isset($_SESSION) && !empty($_SESSION)) {
                echo implode(', ', array_keys($_SESSION));
            } else {
                echo 'Ninguna';
            }
            ?>
        </li>
        <li><strong>GET empleado (fallback):</strong> <?= isset($_GET['empleado']) ? $_GET['empleado'] : 'No definido' ?></li>
    </ul>
    <div class="mt-2">
        <a href="?c=auth&a=logout" class="btn btn-warning">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión e Intentar Nuevamente
        </a>
    </div>
</div>
<?php endif; ?>