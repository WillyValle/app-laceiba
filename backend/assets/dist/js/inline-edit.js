/**
 * Funciones genericas para edicion inline de tablas
 */

let filaEnEdicion = null;
let datosOriginales = {};
let configuracionControladores = {
    'servicecategory': {
        url: '?c=servicecategory&a=ActualizarInline',
        campos: ['nombre', 'descripcion', 'estado']
    },
    'applicationmethod': {
        url: '?c=applicationmethod&a=ActualizarInline',
        campos: ['nombre', 'descripcion', 'estado']
    },
    'servicestatus': {
        url: '?c=servicestatus&a=ActualizarInline',
        campos: ['nombre', 'descripcion', 'estado']
    },
    'contacttype': {
        url: '?c=contacttype&a=ActualizarInline',
        campos: ['nombre', 'descripcion', 'estado']
    },
    'typedoc': {
        url: '?c=typedoc&a=ActualizarInline',
        campos: ['nombre', 'descripcion', 'estado']
    },
    'roleemployee': {
        url: '?c=roleemployee&a=ActualizarInline',
        campos: ['nombre', 'descripcion', 'estado']
    },
    'roleinservice': {
        url: '?c=roleinservice&a=ActualizarInline',
        campos: ['nombre', 'descripcion', 'estado']
    }
};

// Iniciar edicion de una fila
function editarFila(id, tabla = 'activos') {
    // Si ya hay una fila en edicion, cancelar primero
    if (filaEnEdicion) {
        cancelarEdicion();
    }

    const fila = document.getElementById(`fila-${id}-${tabla}`);
    if (!fila) {
        console.error(`No se encontro la fila con ID: fila-${id}-${tabla}`);
        return;
    }

    // Detectar el controlador basado en la URL actual o el contexto
    const controlador = detectarControlador();
    console.log('Controlador detectado:', controlador); // Debug
    
    filaEnEdicion = { id, tabla, elemento: fila, controlador };

    // Hacer opacas las demas filas
    aplicarOpacidadFilas(tabla, id);

    // Resaltar fila en edicion
    fila.classList.add('fila-editando');

    // Guardar datos originales
    guardarDatosOriginales(fila, id);

    // Convertir celdas a inputs editables
    convertirCeldasEditables(fila, id);

    // Cambiar botones de accion
    cambiarBotonesAccion(fila, id, tabla);
}

function detectarControlador() {
    // Detectar controlador por la URL actual
    const urlParams = new URLSearchParams(window.location.search);
    const controladorUrl = urlParams.get('c');
    
    console.log('URL params c:', controladorUrl); // Debug
    
    if (controladorUrl && configuracionControladores[controladorUrl]) {
        return controladorUrl;
    }
    
    // Fallback: detectar por elementos en la página o por la presencia de tablas específicas
    if (document.getElementById('ID_TYPE_DOC')) {
        return 'typedoc';
    } else if (document.querySelector('[id*="ID_SERVICE_CATEGORY"]')) {
        return 'servicecategory';
    } else if (document.querySelector('[id*="ID_APPLICATION_METHOD"]')) {
        return 'applicationmethod';
    } else if (document.querySelector('[id*="ID_SERVICE_STATUS"]')) {
        return 'servicestatus';
    } else if (document.querySelector('[id*="ID_CONTACT_TYPE"]')) {
        return 'contacttype';
    } else if (document.querySelector('[id*="ID_ROLE_EMPLOYEE"]')) {
        return 'roleemployee';
    } else if (document.querySelector('[id*="ID_ROLE_IN_SERVICE"]')) {
        return 'roleinservice';
    } else if (document.querySelector('#tabla-activos')) {
        // Último recurso: detectar por el contexto de la página
        const titulo = document.querySelector('.card-title');
        if (titulo && titulo.textContent.includes('Tipos de Documento')) {
            return 'typedoc';
        }
    }
    
    // Default - también revisamos la URL como último recurso
    const currentUrl = window.location.href.toLowerCase();
    if (currentUrl.includes('typedoc')) return 'typedoc';
    if (currentUrl.includes('servicecategory')) return 'servicecategory';
    if (currentUrl.includes('applicationmethod')) return 'applicationmethod';
    if (currentUrl.includes('servicestatus')) return 'servicestatus';
    if (currentUrl.includes('contacttype')) return 'contacttype';
    if (currentUrl.includes('roleemployee')) return 'roleemployee';
    
    // Default
    return 'typedoc';
}

function aplicarOpacidadFilas(tabla, idExcluir) {
    const filas = document.querySelectorAll(`[id^="fila-"][id*="-${tabla}"]`);
    filas.forEach(fila => {
        if (!fila.id.includes(`fila-${idExcluir}-`)) {
            fila.classList.add('fila-opaca');
        }
    });
}

function guardarDatosOriginales(fila, id) {
    const celdas = fila.querySelectorAll('td');
    datosOriginales[id] = {
        nombre: celdas[0].textContent.trim(),
        descripcion: celdas[1].textContent.trim(),
        estado: celdas[2].textContent.trim() === 'Activo'
    };
    console.log('Datos originales guardados:', datosOriginales[id]); // Debug
}

function convertirCeldasEditables(fila, id) {
    const celdas = fila.querySelectorAll('td');
    
    // Celda 0: Nombre (editable)
    const nombreActual = celdas[0].textContent.trim();
    celdas[0].innerHTML = `<input type="text" class="celda-input form-control form-control-sm" id="edit-nombre-${id}" value="${nombreActual}">`;
    
    // Celda 1: Descripcion (editable)
    const descripcionActual = celdas[1].textContent.trim();
    celdas[1].innerHTML = `<textarea class="celda-textarea form-control form-control-sm" id="edit-descripcion-${id}" rows="2">${descripcionActual}</textarea>`;
    
    // Celda 2: Estado (checkbox switch)
    const estadoActual = celdas[2].textContent.trim() === 'Activo';
    celdas[2].innerHTML = `
        <label class="switch-estado">
            <input type="checkbox" id="edit-estado-${id}" ${estadoActual ? 'checked' : ''}>
            <span class="slider"></span>
        </label>
    `;
}

function cambiarBotonesAccion(fila, id, tabla) {
    const celdaAcciones = fila.querySelector('td:last-child');
    celdaAcciones.innerHTML = `
        <div class="botones-edicion">
            <button class="btn btn-success btn-sm me-1" onclick="guardarEdicion(${id}, '${tabla}')">
                <i class="fas fa-save"></i> Guardar
            </button>
            <button class="btn btn-secondary btn-sm" onclick="cancelarEdicion()">
                <i class="fas fa-times"></i> Cancelar
            </button>
        </div>
    `;
}

function cancelarEdicion() {
    if (!filaEnEdicion) return;

    const { id, tabla, elemento } = filaEnEdicion;

    // Remover clases de edicion
    elemento.classList.remove('fila-editando');

    // Restaurar opacidad de todas las filas
    const todasLasFilas = document.querySelectorAll(`[id^="fila-"][id*="-${tabla}"]`);
    todasLasFilas.forEach(fila => {
        fila.classList.remove('fila-opaca');
    });

    // Restaurar contenido original
    restaurarContenidoOriginal(elemento, id);

    // Limpiar variables
    filaEnEdicion = null;
    delete datosOriginales[id];
}

function restaurarContenidoOriginal(fila, id) {
    const datos = datosOriginales[id];
    const celdas = fila.querySelectorAll('td');
    
    // Restaurar nombre
    celdas[0].textContent = datos.nombre;
    
    // Restaurar descripcion
    celdas[1].textContent = datos.descripcion;
    
    // Restaurar estado
    celdas[2].textContent = datos.estado ? 'Activo' : 'Inactivo';
    
    // Restaurar botones originales
    celdas[3].innerHTML = `
        <button class="btn btn-warning btn-sm" onclick="editarFila(${id}, '${filaEnEdicion?.tabla || 'activos'}')">
            <i class="fas fa-edit"></i> Editar
        </button>
    `;
}

function guardarEdicion(id, tabla) {
    if (!filaEnEdicion) {
        console.error('No hay fila en edición');
        return;
    }

    // Obtener valores actuales
    const nombre = document.getElementById(`edit-nombre-${id}`).value.trim();
    const descripcion = document.getElementById(`edit-descripcion-${id}`).value.trim();
    const estado = document.getElementById(`edit-estado-${id}`).checked;

    console.log('Datos a enviar:', { id, nombre, descripcion, estado }); // Debug

    // Validaciones basicas
    if (!nombre) {
        mostrarMensajeError('Por favor complete el campo Nombre.');
        return;
    }

    // Obtener URL del controlador
    const controlador = filaEnEdicion.controlador;
    const config = configuracionControladores[controlador];
    
    console.log('Controlador:', controlador, 'Config:', config); // Debug
    
    if (!config) {
        mostrarMensajeError('Error: Controlador no configurado.');
        return;
    }

    // Mostrar indicador de carga
    mostrarCargando(true);

    // Preparar datos para envio
    const datosFormulario = new FormData();
    datosFormulario.append('id', id);
    datosFormulario.append('nombre', nombre);
    datosFormulario.append('descripcion', descripcion);
    datosFormulario.append('estado', estado ? 1 : 0);

    console.log('URL a enviar:', config.url); // Debug
    
    // Enviar datos via AJAX
    fetch(config.url, {
        method: 'POST',
        body: datosFormulario
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // Debug
        mostrarCargando(false);
        
        if (data.success) {
            manejarActualizacionExitosa(id, tabla, estado, nombre, descripcion);
        } else {
            mostrarMensajeError('Error al actualizar: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        mostrarCargando(false);
        mostrarMensajeError('Error de conexión al guardar los cambios.');
    });
}

function mostrarCargando(mostrar) {
    const botonGuardar = document.querySelector('.botones-edicion .btn-success');
    if (botonGuardar) {
        if (mostrar) {
            botonGuardar.disabled = true;
            botonGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        } else {
            botonGuardar.disabled = false;
            botonGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar';
        }
    }
}

function manejarActualizacionExitosa(id, tabla, nuevoEstado, nombre, descripcion) {
    // Mostrar mensaje de exito
    mostrarMensajeExito('¡Registro actualizado exitosamente!');
    
    // Cancelar edición para restaurar la vista normal
    cancelarEdicion();
    
    // Actualizar los datos en la fila sin recargar la página
    actualizarFilaVisual(id, tabla, nuevoEstado, nombre, descripcion);
    
    // Actualizar contadores
    actualizarContadores();
}

function actualizarFilaVisual(id, tabla, nuevoEstado, nombre, descripcion) {
    const estadoAnterior = datosOriginales[id]?.estado;
    const estadoTexto = nuevoEstado ? 'Activo' : 'Inactivo';
    
    // Si el estado cambió, mover entre tablas
    if (estadoAnterior !== nuevoEstado) {
        moverRegistroEntreTablas(id, tabla, nuevoEstado, nombre, descripcion);
    } else {
        // Si el estado no cambió, solo actualizar la fila actual
        const fila = document.getElementById(`fila-${id}-${tabla}`);
        if (fila) {
            const celdas = fila.querySelectorAll('td');
            celdas[0].textContent = nombre;
            celdas[1].textContent = descripcion;
            celdas[2].textContent = estadoTexto;
        }
    }
}

function moverRegistroEntreTablas(id, tablaOrigen, nuevoEstado, nombre, descripcion) {
    const filaOrigen = document.getElementById(`fila-${id}-${tablaOrigen}`);
    const tablaDestino = nuevoEstado ? 'activos' : 'inactivos';
    
    if (filaOrigen) {
        // Animar salida de la tabla origen
        filaOrigen.classList.add('fila-desapareciendo');
        
        setTimeout(() => {
            // Remover de tabla origen
            filaOrigen.remove();
            
            // Agregar a tabla destino
            agregarFilaATabla(id, nombre, descripcion, nuevoEstado, tablaDestino);
            
            // Actualizar contadores
            actualizarContadores();
        }, 500);
    }
}

function agregarFilaATabla(id, nombre, descripcion, estado, tabla) {
    const tbody = document.querySelector(`#tabla-${tabla} tbody`);
    if (!tbody) return;

    const estadoTexto = estado ? 'Activo' : 'Inactivo';
    const nuevaFila = document.createElement('tr');
    nuevaFila.id = `fila-${id}-${tabla}`;
    nuevaFila.classList.add('fila-apareciendo');
    
    nuevaFila.innerHTML = `
        <td>${nombre}</td>
        <td>${descripcion}</td>
        <td>${estadoTexto}</td>
        <td>
            <button class="btn btn-warning btn-sm" onclick="editarFila(${id}, '${tabla}')">
                <i class="fas fa-edit"></i> Editar
            </button>
        </td>
    `;
    
    tbody.appendChild(nuevaFila);
}

function actualizarContadores() {
    // Actualizar contador de registros activos
    const filasActivas = document.querySelectorAll('#tabla-activos tbody tr').length;
    const contadorActivos = document.getElementById('contador-activos');
    if (contadorActivos) {
        contadorActivos.textContent = filasActivas;
    }

    // Actualizar contador de registros inactivos
    const filasInactivas = document.querySelectorAll('#tabla-inactivos tbody tr').length;
    const contadorInactivos = document.getElementById('contador-inactivos');
    if (contadorInactivos) {
        contadorInactivos.textContent = filasInactivas;
    }
}

// Funcion para mostrar/ocultar tabla de inactivos
function toggleTablaInactivos() {
    const container = document.getElementById('tablaInactivosContainer');
    const boton = document.getElementById('btnToggleInactivos');
    
    if (!container || !boton) return;
    
    if (container.style.display === 'none' || container.style.display === '') {
        // Mostrar tabla inactivos
        container.style.display = 'block';
        setTimeout(() => {
            container.classList.add('show');
        }, 10);
        
        boton.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar Registros Inactivos';
        boton.classList.remove('btn-secondary');
        boton.classList.add('btn-outline-secondary');
    } else {
        // Ocultar tabla inactivos
        container.classList.remove('show');
        setTimeout(() => {
            container.style.display = 'none';
        }, 300);
        
        boton.innerHTML = '<i class="fas fa-eye"></i> Ver Registros Inactivos';
        boton.classList.remove('btn-outline-secondary');
        boton.classList.add('btn-secondary');
    }
}

// Funciones auxiliares para mostrar mensajes
function mostrarMensajeExito(mensaje) {
    mostrarMensaje(mensaje, 'success', 'check-circle');
}

function mostrarMensajeError(mensaje) {
    mostrarMensaje(mensaje, 'danger', 'exclamation-circle');
}

function mostrarMensaje(mensaje, tipo, icono) {
    // Remover mensaje anterior si existe
    const mensajeAnterior = document.getElementById('mensajeInline');
    if (mensajeAnterior) {
        mensajeAnterior.remove();
    }

    const alertHtml = `
        <div class="alert alert-${tipo} alert-dismissible fade show" role="alert" id="mensajeInline" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-${icono}"></i> ${mensaje}
            <button type="button" class="close" onclick="document.getElementById('mensajeInline').remove()">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        const alert = document.getElementById('mensajeInline');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }
    }, 5000);
}

// Manejar escape key para cancelar edicion
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && filaEnEdicion) {
        cancelarEdicion();
    }
});

// Debug: Agregar evento para verificar cuando se carga el script
document.addEventListener('DOMContentLoaded', function() {
    console.log('inline-edit.js cargado correctamente');
    console.log('Controlador detectado al cargar:', detectarControlador());
});