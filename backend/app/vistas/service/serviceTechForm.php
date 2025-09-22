<!-- Vista para finalizar servicio por técnicos - app/vistas/service/serviceTechForm.php -->

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= isset($titulo_pagina) ? $titulo_pagina : 'Finalizar Servicio' ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="?c=service&a=VistaTecnico&empleado=<?= isset($empleado_actual) ? $empleado_actual : '' ?>">
                            <i class="fas fa-arrow-left"></i> Volver a Mis Servicios
                        </a>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Mostrar mensajes de éxito -->
        <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($mensaje_exito); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>
        
        <!-- Mostrar errores -->
        <?php if (!empty($errores)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Por favor corrija los siguientes errores:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errores as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>

        <!-- Formulario de finalización -->
        <form action="?c=service&a=CompletarServicio&id=<?= $servicio->id_service ?>&empleado=<?= isset($empleado_actual) ? $empleado_actual : '' ?>" 
              method="POST" enctype="multipart/form-data" id="formFinalizarServicio">
            
            <div class="row">
                <!-- Columna izquierda -->
                <div class="col-md-6">
                    
                    <!-- Card: Información del Cliente (Solo lectura) -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building"></i> Información del Cliente
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Cliente:</label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($servicio->name_customer) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Dirección:</label>
                                <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($servicio->address_customer) ?></textarea>
                            </div>
                            <div class="row">
                                <?php if (!empty($servicio->customer_whatsapp)): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>WhatsApp:</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars($servicio->customer_whatsapp) ?>" readonly>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($servicio->customer_tel)): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Teléfono:</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars($servicio->customer_tel) ?>" readonly>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Información del Servicio (Solo lectura) -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt"></i> Información del Servicio
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label># De Servicio:</label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($servicio->id_service) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Técnico Encargado:</label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($servicio->encargado_nombre) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Fecha Programada:</label>
                                <input type="text" class="form-control" 
                                       value="<?= date('d/m/Y g:i A', strtotime($servicio->preset_dt_hr)) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Fecha de Inicio:</label>
                                <input type="text" class="form-control" 
                                       value="<?= !empty($servicio->start_dt_hr) ? date('d/m/Y g:i A', strtotime($servicio->start_dt_hr)) : 'No iniciado' ?>" readonly>
                            </div>
                            <?php if (!empty($servicio->notes)): ?>
                            <div class="form-group">
                                <label>Notas del Servicio:</label>
                                <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($servicio->notes) ?></textarea>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card: Técnicos Asistentes (Editable) -->
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Técnicos Asistentes
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="asistentes">Seleccionar Asistentes:</label>
                                <small class="form-text text-muted">Mantenga Ctrl presionado para seleccionar múltiples técnicos</small>
                                <select class="form-control" id="asistentes" name="asistentes[]" multiple style="height: 120px;">
                                    <?php if (!empty($empleados_disponibles)): ?>
                                        <?php foreach ($empleados_disponibles as $empleado): ?>
                                            <?php 
                                            // Verificar si este empleado está actualmente asignado como asistente
                                            $es_asistente_actual = false;
                                            if (!empty($asistentes_actuales)) {
                                                foreach ($asistentes_actuales as $asistente) {
                                                    if ($asistente->id_employee == $empleado->id_employee) {
                                                        $es_asistente_actual = true;
                                                        break;
                                                    }
                                                }
                                            }
                                            
                                            // No mostrar al encargado en la lista de asistentes
                                            if ($empleado->id_employee != $servicio->encargado_id): ?>
                                            <option value="<?= $empleado->id_employee ?>" 
                                                    <?= $es_asistente_actual ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($empleado->nombre_completo) ?>
                                            </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <!-- Mostrar asistentes actuales -->
                            <?php if (!empty($asistentes_actuales)): ?>
                            <div class="alert alert-light">
                                <small class="text-muted">
                                    <strong>Asistentes actuales:</strong><br>
                                    <?php foreach ($asistentes_actuales as $asistente): ?>
                                        <span class="badge badge-secondary mr-1"><?= htmlspecialchars($asistente->nombre_completo) ?></span>
                                    <?php endforeach; ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Columna derecha -->
                <div class="col-md-6">
                    
                    <!-- Card: Categorías de Servicio -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tags"></i> Categorías de Servicio
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Seleccione las categorías aplicadas:</label>
                                <small class="form-text text-muted">Marque todas las categorías que aplicaron en este servicio</small>
                                
                                <?php if (!empty($categorias_servicio)): ?>
                                    <?php foreach ($categorias_servicio as $categoria): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="categorias_servicio[]" 
                                               value="<?= $categoria->id_service_category ?>"
                                               id="categoria_<?= $categoria->id_service_category ?>">
                                        <label class="form-check-label" for="categoria_<?= $categoria->id_service_category ?>">
                                            <strong><?= htmlspecialchars($categoria->name_service_category) ?></strong>
                                            <?php if (!empty($categoria->description)): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($categoria->description) ?></small>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No hay categorías disponibles</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Métodos de Aplicación -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tools"></i> Métodos de Aplicación
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Seleccione los métodos utilizados:</label>
                                <small class="form-text text-muted">Marque todos los métodos que se utilizaron</small>
                                
                                <?php if (!empty($metodos_aplicacion)): ?>
                                    <?php foreach ($metodos_aplicacion as $metodo): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="metodos_aplicacion[]" 
                                               value="<?= $metodo->id_application_method ?>"
                                               id="metodo_<?= $metodo->id_application_method ?>">
                                        <label class="form-check-label" for="metodo_<?= $metodo->id_application_method ?>">
                                            <strong><?= htmlspecialchars($metodo->name_application_method) ?></strong>
                                            <?php if (!empty($metodo->description)): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($metodo->description) ?></small>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No hay métodos disponibles</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Imágenes del Servicio -->
                    <div class="card card-dark">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-camera"></i> Imágenes del Servicio
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="imagenes_servicio">Subir Imágenes:</label>
                                <small class="form-text text-muted">Puede seleccionar múltiples imágenes. Máximo 5MB por imagen. Formatos: JPG, PNG, GIF</small>
                                <input type="file" class="form-control-file" 
                                       id="imagenes_servicio" name="imagenes_servicio[]" 
                                       multiple accept="image/*">
                                <small id="imagen-info" class="form-text text-muted">
                                    Las imágenes se guardarán como evidencia del servicio realizado.
                                </small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Información de Inspección -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-check"></i> Información de Inspección
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="inspection_problems">Problemas Encontrados:</label>
                                        <textarea class="form-control" id="inspection_problems" 
                                                  name="inspection_problems" rows="4"
                                                  placeholder="Describa los problemas o plagas encontradas..."><?= isset($_POST['inspection_problems']) ? htmlspecialchars($_POST['inspection_problems']) : '' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="inspection_location">Ubicaciones Tratadas:</label>
                                        <textarea class="form-control" id="inspection_location" 
                                                  name="inspection_location" rows="4"
                                                  placeholder="Describa las áreas o ubicaciones donde se aplicó el tratamiento..."><?= isset($_POST['inspection_location']) ? htmlspecialchars($_POST['inspection_location']) : '' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="inspection_methods">Métodos Específicos:</label>
                                        <textarea class="form-control" id="inspection_methods" 
                                                  name="inspection_methods" rows="4"
                                                  placeholder="Detalle los métodos específicos utilizados, productos aplicados, etc..."><?= isset($_POST['inspection_methods']) ? htmlspecialchars($_POST['inspection_methods']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notas Finales -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-light">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sticky-note"></i> Notas de Finalización
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="notes_finalizacion">Observaciones Adicionales:</label>
                                <textarea class="form-control" id="notes_finalizacion" 
                                          name="notes_finalizacion" rows="3"
                                          placeholder="Agregue cualquier observación adicional, recomendaciones para el cliente, etc..."><?= isset($_POST['notes_finalizacion']) ? htmlspecialchars($_POST['notes_finalizacion']) : '' ?></textarea>
                                <small class="form-text text-muted">Estas notas se agregarán a las notas existentes del servicio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-footer text-center">
                            <button type="submit" class="btn btn-success btn-lg" id="btnFinalizar"
                                    onclick="return confirm('¿Está seguro que desea finalizar este servicio?\n\nEsta acción no se puede deshacer y marcará el servicio como completado.');">
                                <i class="fas fa-check-circle"></i> Finalizar Servicio
                            </button>
                            <a href="?c=service&a=VistaTecnico&empleado=<?= isset($empleado_actual) ? $empleado_actual : '' ?>" 
                               class="btn btn-secondary btn-lg ml-3">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </form>
        
        <!-- Información adicional -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-light">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> Instrucciones Importantes
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-exclamation-triangle text-warning"></i> Antes de Finalizar</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Complete toda la información de inspección</li>
                                    <li><i class="fas fa-check text-success"></i> Seleccione las categorías y métodos aplicados</li>
                                    <li><i class="fas fa-check text-success"></i> Suba las fotos del trabajo realizado</li>
                                    <li><i class="fas fa-check text-success"></i> Verifique que todos los datos sean correctos</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-clock text-info"></i> Después de Finalizar</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-arrow-right text-muted"></i> El servicio se marcará como completado</li>
                                    <li><i class="fas fa-arrow-right text-muted"></i> Se registrará automáticamente la fecha y hora de finalización</li>
                                    <li><i class="fas fa-arrow-right text-muted"></i> Las imágenes quedarán guardadas permanentemente</li>
                                    <li><i class="fas fa-arrow-right text-muted"></i> No se podrá modificar la información posteriormente</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.container-fluid -->
</section><!-- /.content -->
                                </div>

<!-- Estilos adicionales para mantener consistencia -->
<style>
.form-control[multiple] {
    height: auto;
    min-height: 120px;
}

.form-control[multiple] option {
    padding: 8px 12px;
}

.form-control[multiple] option:checked {
    background-color: #007bff;
    color: white;
}

.form-check {
    margin-bottom: 0.75rem;
}

.form-check-label {
    margin-left: 0.25rem;
}

.form-control-file {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control-file:focus {
    color: #495057;
    background-color: #fff;
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.badge {
    font-size: 0.75em;
}

.card-header .card-title {
    margin: 0;
}
</style>