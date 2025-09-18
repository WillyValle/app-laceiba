<!-- Vista para crear nuevo servicio - app/vistas/service/serviceForm.php -->

<?php
// Variables para modo edición - AGREGADO
$es_edicion = isset($modo_edicion) && $modo_edicion === true;
$titulo_formulario = $es_edicion ? 'Reprogramar Servicio' : 'Programar Nuevo Servicio';
$accion_formulario = $es_edicion ? 'ReprogramarServicio' : 'NuevoServicio';
$id_servicio_param = $es_edicion && isset($servicio_actual) ? '&id=' . $servicio_actual->id_service : '';
?>

  <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <!-- MODIFICADO: título dinámico -->
                    <h1 class="m-0"><?= $titulo_formulario ?></h1>
                    <?php if ($es_edicion && isset($servicio_actual)): ?>
                        <small class="text-muted">Servicio #<?= $servicio_actual->id_service ?></small>
                    <?php endif; ?>
                </div>
                <!-- AGREGADO: botón volver si es edición -->
                <?php if ($es_edicion): ?>
                <div class="col-sm-6">
                    <div class="breadcrumb float-sm-right">
                        <a href="?c=service" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver a Servicios
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
  </div>

  <!-- Mostrar mensajes de éxito -->
  <?php if (!empty($mensaje_exito)): ?>
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
      <i class="fas fa-check-circle"></i>
      <?php echo htmlspecialchars($mensaje_exito); ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>
  
  <!-- Mostrar errores -->
  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
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

  <!-- MODIFICADO: action dinámico -->
  <form action="?c=service&a=<?= $accion_formulario ?><?= $id_servicio_param ?>" method="POST" enctype="multipart/form-data" id="formServicio">
    <div class="card-body">
      
      <!-- Primera fila: Solo Cliente (Estado se asigna automáticamente) -->
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="customer_id_customer">Cliente<span class="text-danger">*</span></label>
            <select class="form-control" id="customer_id_customer" name="customer_id_customer" required>
              <option value="">Seleccione...</option>
              <?php if (!empty($clientes)): ?>
                <?php foreach ($clientes as $cliente): ?>
                  <option value="<?php echo $cliente->id_customer; ?>"
                    <?php 
                    // MODIFICADO: lógica de selección para edición
                    if ($es_edicion && isset($servicio_actual) && $servicio_actual->customer_id_customer == $cliente->id_customer) {
                        echo 'selected';
                    } elseif (!$es_edicion && isset($_POST['customer_id_customer']) && $_POST['customer_id_customer'] == $cliente->id_customer) {
                        echo 'selected';
                    }
                    ?>>
                    <?php echo htmlspecialchars($cliente->name_customer); ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>
        <?php if ($es_edicion): ?>
        <!-- En edición, mostrar el estado actual como información -->
        <div class="col-md-6">
          <div class="form-group">
            <label>Estado Actual del Servicio</label>
            <div class="form-control-plaintext">
              <span class="badge badge-info">
                <?php echo isset($servicio_actual) ? htmlspecialchars($servicio_actual->name_service_status) : 'N/A'; ?>
              </span>
            </div>
            <small class="form-text text-muted">El estado se mantiene al reprogramar</small>
          </div>
        </div>
        <?php else: ?>
        <!-- En creación, mostrar información del estado automático -->
        <div class="col-md-6">
          <div class="form-group">
            <label>Estado del Servicio</label>
            <div class="form-control-plaintext">
              <span class="badge badge-warning">
                <i class="fas fa-clock"></i> Se asignará automáticamente como "Programado"
              </span>
            </div>
            <small class="form-text text-muted">El servicio se creará en estado pendiente de ejecución</small>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Segunda fila: Fecha y Empleado Encargado -->
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="preset_dt_hr">Fecha y Hora Programada<span class="text-danger">*</span></label>
            <input 
              type="datetime-local" 
              class="form-control" 
              id="preset_dt_hr" 
              name="preset_dt_hr"
              value="<?php 
              // MODIFICADO: valor dinámico para edición
              if ($es_edicion && isset($servicio_actual) && $servicio_actual->preset_dt_hr) {
                  echo date('Y-m-d\TH:i', strtotime($servicio_actual->preset_dt_hr));
              } elseif (!$es_edicion && isset($_POST['preset_dt_hr'])) {
                  echo htmlspecialchars($_POST['preset_dt_hr']);
              }
              ?>"
              required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="empleado_encargado">Empleado Encargado<span class="text-danger">*</span></label>
            <select class="form-control" id="empleado_encargado" name="empleado_encargado" required>
              <option value="">Seleccione...</option>
              <?php if (!empty($empleados)): ?>
                <?php foreach ($empleados as $empleado): ?>
                  <option value="<?php echo $empleado->id_employee; ?>"
                    <?php 
                    // MODIFICADO: lógica de selección para edición
                    if ($es_edicion && isset($empleado_encargado_actual) && $empleado_encargado_actual->id_employee == $empleado->id_employee) {
                        echo 'selected';
                    } elseif (!$es_edicion && isset($_POST['empleado_encargado']) && $_POST['empleado_encargado'] == $empleado->id_employee) {
                        echo 'selected';
                    }
                    ?>>
                    <?php echo htmlspecialchars($empleado->nombre_completo); ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Tercera fila: Empleados Asistentes -->
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label for="empleados_asistentes">Empleados Asistentes <small class="text-muted">(Opcional - Mantenga Ctrl presionado para seleccionar múltiples)</small></label>
            <select class="form-control" 
                    id="empleados_asistentes" 
                    name="empleados_asistentes[]" 
                    multiple 
                    style="height: 120px;">
              <?php if (!empty($empleados)): ?>
                <?php foreach ($empleados as $empleado): ?>
                  <option value="<?php echo $empleado->id_employee; ?>"
                    <?php 
                    // MODIFICADO: lógica de selección para edición
                    $esta_seleccionado = false;
                    if ($es_edicion && isset($asistentes_actuales)) {
                        foreach ($asistentes_actuales as $asistente) {
                            if ($asistente->id_employee == $empleado->id_employee) {
                                $esta_seleccionado = true;
                                break;
                            }
                        }
                    } elseif (!$es_edicion && isset($_POST['empleados_asistentes']) && in_array($empleado->id_employee, $_POST['empleados_asistentes'])) {
                        $esta_seleccionado = true;
                    }
                    echo $esta_seleccionado ? 'selected' : '';
                    ?>>
                    <?php echo htmlspecialchars($empleado->nombre_completo); ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Cuarta fila: Archivo PDF -->
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label for="archivo_pdf">Documento/Croquis (PDF) <small class="text-muted">(Opcional - Máximo 5MB)</small></label>
            <input 
              type="file" 
              class="form-control-file" 
              id="archivo_pdf" 
              name="archivo_pdf"
              accept=".pdf"
              onchange="validarArchivo(this)">
            <small class="form-text text-muted" id="archivo-info">
              Archivo PDF máximo 5MB. Se guardará como referencia para el servicio.
            </small>
          </div>
        </div>
      </div>

      <!-- Quinta fila: Notas -->
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label for="notes">Notas Adicionales</label>
            <textarea 
              class="form-control" 
              id="notes" 
              name="notes" 
              rows="4" 
              placeholder="Escriba aquí cualquier información adicional sobre el servicio..."
              maxlength="500"><?php 
              // MODIFICADO: valor dinámico para edición con validación de null
              if ($es_edicion && isset($servicio_actual) && !is_null($servicio_actual->notes)) {
                  echo htmlspecialchars($servicio_actual->notes);
              } elseif (!$es_edicion && isset($_POST['notes']) && !is_null($_POST['notes'])) {
                  echo htmlspecialchars($_POST['notes']);
              }
            ?></textarea>
            <small class="form-text text-muted">Máximo 500 caracteres</small>
          </div>
        </div>
      </div>

    </div>

    <div class="card-footer">
      <!-- MODIFICADO: texto del botón dinámico -->
      <button type="submit" class="btn btn-primary" id="btnGuardar">
        <i class="fas fa-save"></i> <?= $es_edicion ? 'Actualizar Servicio' : 'Programar Servicio' ?>
      </button>
      <a href="?c=service" class="btn btn-secondary ml-2">
        <i class="fas fa-times"></i> Cancelar
      </a>
      <!-- MODIFICADO: botón limpiar solo en modo creación -->
      <?php if (!$es_edicion): ?>
      <button type="reset" class="btn btn-outline-secondary ml-2" onclick="limpiarFormulario()">
        <i class="fas fa-eraser"></i> Limpiar
      </button>
      <?php endif; ?>
    </div>
  </form>
</div>
                

<!-- JavaScript para validaciones y mejoras UX -->
<script>
/**
 * Validar archivo PDF antes de enviar el formulario
 */
function validarArchivo(input) {
    const archivo = input.files[0];
    const infoElement = document.getElementById('archivo-info');
    
    if (archivo) {
        // Validar tamaño (5MB máximo)
        const tamañoMaximo = 5 * 1024 * 1024; // 5MB en bytes
        if (archivo.size > tamañoMaximo) {
            alert('El archivo es demasiado grande. El tamaño máximo permitido es 5MB.');
            input.value = '';
            infoElement.innerHTML = 'Archivo PDF máximo 5MB. Se guardará como referencia para el servicio.';
            infoElement.className = 'form-text text-muted';
            return false;
        }
        
        // Validar tipo de archivo
        const tiposPermitidos = ['application/pdf'];
        if (!tiposPermitidos.includes(archivo.type)) {
            alert('Solo se permiten archivos PDF.');
            input.value = '';
            infoElement.innerHTML = 'Archivo PDF máximo 5MB. Se guardará como referencia para el servicio.';
            infoElement.className = 'form-text text-muted';
            return false;
        }
        
        // Validar extensión
        const extension = archivo.name.split('.').pop().toLowerCase();
        if (extension !== 'pdf') {
            alert('El archivo debe tener extensión .pdf');
            input.value = '';
            infoElement.innerHTML = 'Archivo PDF máximo 5MB. Se guardará como referencia para el servicio.';
            infoElement.className = 'form-text text-muted';
            return false;
        }
        
        // Mostrar información del archivo seleccionado
        infoElement.innerHTML = `<i class="fas fa-file-pdf text-success"></i> Archivo seleccionado: ${archivo.name} (${formatearTamaño(archivo.size)})`;
        infoElement.className = 'form-text text-success';
    } else {
        infoElement.innerHTML = 'Archivo PDF máximo 5MB. Se guardará como referencia para el servicio.';
        infoElement.className = 'form-text text-muted';
    }
    return true;
}

/**
 * Formatear tamaño de archivo para mostrar en formato legible
 */
function formatearTamaño(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Limpiar formulario completamente
 */
function limpiarFormulario() {
    document.getElementById('formServicio').reset();
    // Limpiar mensaje de archivo seleccionado
    const infoElement = document.getElementById('archivo-info');
    infoElement.innerHTML = 'Archivo PDF máximo 5MB. Se guardará como referencia para el servicio.';
    infoElement.className = 'form-text text-muted';
}

/**
 * Validaciones adicionales antes del envío del formulario
 */
document.getElementById('formServicio').addEventListener('submit', function(e) {
    const encargado = document.getElementById('empleado_encargado').value;
    const asistentes = Array.from(document.getElementById('empleados_asistentes').selectedOptions).map(option => option.value);
    
    // Verificar que el encargado no esté también como asistente
    if (encargado && asistentes.includes(encargado)) {
        e.preventDefault();
        alert('El empleado encargado no puede ser también un asistente. Por favor, corrija la selección.');
        return false;
    }
    
    // Validar fecha mínima (no puede ser en el pasado)
    const fechaSeleccionada = new Date(document.getElementById('preset_dt_hr').value);
    const fechaActual = new Date();
    
    if (fechaSeleccionada < fechaActual) {
        e.preventDefault();
        alert('La fecha y hora programada no puede ser en el pasado.');
        return false;
    }
    
    // Mostrar indicador de carga
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    
    return true;
});

/**
 * Configuraciones iniciales cuando carga la página
 */
document.addEventListener('DOMContentLoaded', function() {
    // Establecer fecha mínima como ahora
    const fechaInput = document.getElementById('preset_dt_hr');
    const ahora = new Date();
    ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
    fechaInput.min = ahora.toISOString().slice(0, 16);
    
    // Mejorar la experiencia del select múltiple
    const selectAsistentes = document.getElementById('empleados_asistentes');
    selectAsistentes.addEventListener('change', function() {
        const seleccionados = Array.from(this.selectedOptions).map(option => option.text);
        const label = document.querySelector('label[for="empleados_asistentes"]');
        
        if (seleccionados.length > 0) {
            label.innerHTML = `Empleados Asistentes <span class="badge badge-primary">${seleccionados.length}</span> <small class="text-muted">(Opcional)</small>`;
        } else {
            label.innerHTML = 'Empleados Asistentes <small class="text-muted">(Opcional - Mantenga Ctrl presionado para seleccionar múltiples)</small>';
        }
    });
});

/**
 * Función para confirmar antes de salir con cambios sin guardar
 */
let formModificado = false;

// Detectar cambios en el formulario
document.getElementById('formServicio').addEventListener('input', function() {
    formModificado = true;
});

// Advertir antes de salir
window.addEventListener('beforeunload', function(e) {
    if (formModificado) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// No mostrar advertencia si se envía el formulario
document.getElementById('formServicio').addEventListener('submit', function() {
    formModificado = false;
});
</script>

<style>
/* Estilos adicionales para mantener consistencia con el diseño */
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

.alert ul {
    padding-left: 1.2rem;
}

/* Mejorar apariencia del input de archivo para que coincida con AdminLTE */
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

/* Indicador visual para campos requeridos */
.form-group label span.text-danger {
    font-weight: bold;
}

/* Badge para contador de asistentes */
.badge {
    font-size: 0.75em;
    vertical-align: middle;
}
</style>