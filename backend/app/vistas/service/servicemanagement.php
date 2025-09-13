<?php
// Obtener valores de filtros actuales para mantenerlos en el formulario
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$filtro_empleado = isset($_GET['empleado']) ? $_GET['empleado'] : '';
?>

<div class="dashboard-container">
    <!-- Sección de filtros -->
    <div class="filtros-container">
        <h4 style="margin-bottom: 15px; color: #495057;">
            <i class="fas fa-filter"></i> Filtros de Búsqueda
        </h4>
        
        <form method="GET" action="">
            <input type="hidden" name="c" value="service">
            <div class="filtros-row">
                <!-- Filtro por cliente -->
                <div class="filtro-grupo">
                    <label for="cliente">Cliente</label>
                    <select name="cliente" id="cliente">
                        <option value="">Todos los clientes</option>
                        <?php foreach($clientes as $cliente): ?>
                            <option value="<?= $cliente->id_customer ?>" 
                                    <?= $filtro_cliente == $cliente->id_customer ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente->name_customer) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro por fecha desde -->
                <div class="filtro-grupo">
                    <label for="fecha_desde">Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="<?= $filtro_fecha_desde ?>">
                </div>

                <!-- Filtro por fecha hasta -->
                <div class="filtro-grupo">
                    <label for="fecha_hasta">Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="<?= $filtro_fecha_hasta ?>">
                </div>

                <!-- Filtro por empleado -->
                <div class="filtro-grupo">
                    <label for="empleado">Empleado</label>
                    <select name="empleado" id="empleado">
                        <option value="">Todos los empleados</option>
                        <?php foreach($empleados as $empleado): ?>
                            <option value="<?= $empleado->id_employee ?>" 
                                    <?= $filtro_empleado == $empleado->id_employee ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empleado->nombre_completo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Botones de acción -->
                <div class="filtro-grupo">
                    <button type="submit" class="btn-filtro">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="?c=service" class="btn-filtro btn-limpiar">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Dashboard de servicios por estado -->
    <div class="cards-container">
        
        <!-- Card: Servicios Programados -->
        <div class="service-card">
            <div class="card-header programados">
                <span>
                    <i class="fas fa-calendar-check"></i> Servicios Programados
                </span>
                <span class="badge-count"><?= $totalProgramados ?></span>
            </div>
            
            <?php if (!empty($serviciosProgramados)): ?>
                <table class="service-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Fecha Programada</th>
                            <th>Empleados</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-programados">
                        <?php foreach($serviciosProgramados as $servicio): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($servicio->name_customer) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($servicio->address_customer) ?></small>
                            </td>
                            <td>
                                <div class="fecha-servicio">
                                    <?= $servicio->preset_dt_hr ? date('d/m/Y H:i', strtotime($servicio->preset_dt_hr)) : 'No programado' ?>
                                </div>
                            </td>
                            <td>
                                <?= $servicio->empleados_asignados ? htmlspecialchars($servicio->empleados_asignados) : 'Sin asignar' ?>
                            </td>
                            <td>
                                <span class="status-badge status-programado">
                                    <?= htmlspecialchars($servicio->name_service_status) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($totalProgramados > count($serviciosProgramados)): ?>
                <button class="btn-ver-mas" onclick="verMasServicios(1, 'programados')">
                    <i class="fas fa-chevron-down"></i> Ver más (<?= $totalProgramados - count($serviciosProgramados) ?> restantes)
                </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-servicios">
                    <i class="fas fa-calendar-times fa-2x"></i>
                    <p>No hay servicios programados</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card: Servicios en Ejecución -->
        <div class="service-card">
            <div class="card-header ejecucion">
                <span>
                    <i class="fas fa-cog fa-spin"></i> En Ejecución
                </span>
                <span class="badge-count"><?= $totalEjecucion ?></span>
            </div>
            
            <?php if (!empty($serviciosEjecucion)): ?>
                <table class="service-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Fecha Inicio</th>
                            <th>Empleados</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-ejecucion">
                        <?php foreach($serviciosEjecucion as $servicio): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($servicio->name_customer) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($servicio->address_customer) ?></small>
                            </td>
                            <td>
                                <div class="fecha-servicio">
                                    <?= $servicio->start_dt_hr ? date('d/m/Y H:i', strtotime($servicio->start_dt_hr)) : 'No iniciado' ?>
                                </div>
                            </td>
                            <td>
                                <?= $servicio->empleados_asignados ? htmlspecialchars($servicio->empleados_asignados) : 'Sin asignar' ?>
                            </td>
                            <td>
                                <span class="status-badge status-ejecucion">
                                    <?= htmlspecialchars($servicio->name_service_status) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($totalEjecucion > count($serviciosEjecucion)): ?>
                <button class="btn-ver-mas" onclick="verMasServicios(2, 'ejecucion')">
                    <i class="fas fa-chevron-down"></i> Ver más (<?= $totalEjecucion - count($serviciosEjecucion) ?> restantes)
                </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-servicios">
                    <i class="fas fa-clock fa-2x"></i>
                    <p>No hay servicios en ejecución</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card: Servicios Finalizados -->
        <div class="service-card">
            <div class="card-header finalizados">
                <span>
                    <i class="fas fa-check-circle"></i> Finalizados
                </span>
                <span class="badge-count"><?= $totalFinalizados ?></span>
            </div>
            
            <?php if (!empty($serviciosFinalizados)): ?>
                <table class="service-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Fecha Finalización</th>
                            <th>Empleados</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-finalizados">
                        <?php foreach($serviciosFinalizados as $servicio): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($servicio->name_customer) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($servicio->address_customer) ?></small>
                            </td>
                            <td>
                                <div class="fecha-servicio">
                                    <?= $servicio->end_dt_hr ? date('d/m/Y H:i', strtotime($servicio->end_dt_hr)) : 'Sin finalizar' ?>
                                </div>
                            </td>
                            <td>
                                <?= $servicio->empleados_asignados ? htmlspecialchars($servicio->empleados_asignados) : 'Sin asignar' ?>
                            </td>
                            <td>
                                <span class="status-badge status-finalizado">
                                    <?= htmlspecialchars($servicio->name_service_status) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($totalFinalizados > count($serviciosFinalizados)): ?>
                <button class="btn-ver-mas" onclick="verMasServicios(3, 'finalizados')">
                    <i class="fas fa-chevron-down"></i> Ver más (<?= $totalFinalizados - count($serviciosFinalizados) ?> restantes)
                </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-servicios">
                    <i class="fas fa-check-double fa-2x"></i>
                    <p>No hay servicios finalizados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información adicional -->
    <div style="text-align: center; color: #6c757d; font-size: 14px; margin-top: 20px;">
        <p>
            <i class="fas fa-info-circle"></i> 
            Mostrando hasta 10 servicios por categoría. Use los botones "Ver más" para cargar servicios adicionales.
        </p>
        <?php if (!empty($filtros)): ?>
        <p>
            <i class="fas fa-filter"></i> 
            Filtros activos aplicados a la búsqueda.
        </p>
        <?php endif; ?>
    </div>
</div>

        </div>
<script>
// Inicializar offsets desde PHP
inicializarOffsets(
    <?= count($serviciosProgramados) ?>,
    <?= count($serviciosEjecucion) ?>,
    <?= count($serviciosFinalizados) ?>
);
</script>