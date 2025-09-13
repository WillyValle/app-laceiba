/**
 * Función JavaScript para manejar la funcionalidad "Ver más"
 * Carga servicios adicionales vía AJAX
 */
let offsetServicios = {
    'programados': 0, // Este valor será inicializado desde PHP
    'ejecucion': 0,
    'finalizados': 0
};

function verMasServicios(estado, tipo) {
    const boton = event.target;
    const tablaId = 'tabla-' + tipo;
    
    // Deshabilitar botón mientras carga
    boton.classList.add('loading');
    boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    boton.disabled = true;
    
    // Construir URL con filtros actuales
    const params = new URLSearchParams(window.location.search);
    params.set('c', 'service');
    params.set('a', 'ObtenerMasServicios');
    params.set('estado', estado);
    params.set('offset', offsetServicios[tipo]);
    params.set('limit', 10);
    
    fetch('?' + params.toString())
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                // Agregar nuevas filas a la tabla
                const tbody = document.getElementById(tablaId);
                
                data.forEach(servicio => {
                    const fila = crearFilaServicio(servicio, tipo);
                    tbody.appendChild(fila);
                });
                
                // Actualizar offset
                offsetServicios[tipo] += data.length;
                
                // Actualizar botón o ocultarlo si no hay más
                if (data.length < 10) {
                    boton.style.display = 'none';
                } else {
                    boton.innerHTML = '<i class="fas fa-chevron-down"></i> Ver más';
                    boton.classList.remove('loading');
                    boton.disabled = false;
                }
            } else {
                // No hay más servicios
                boton.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error al cargar servicios:', error);
            boton.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al cargar';
            boton.classList.remove('loading');
            boton.disabled = false;
        });
}

/**
 * Función auxiliar para crear filas de servicio dinámicamente
 */
function crearFilaServicio(servicio, tipo) {
    const fila = document.createElement('tr');
    
    // Determinar qué fecha mostrar según el tipo
    let fecha = 'No definido';
    if (tipo === 'programados' && servicio.preset_dt_hr) {
        fecha = formatearFecha(servicio.preset_dt_hr);
    } else if (tipo === 'ejecucion' && servicio.start_dt_hr) {
        fecha = formatearFecha(servicio.start_dt_hr);
    } else if (tipo === 'finalizados' && servicio.end_dt_hr) {
        fecha = formatearFecha(servicio.end_dt_hr);
    }
    
    // Determinar clase de estado
    let claseEstado = 'status-programado';
    if (tipo === 'ejecucion') claseEstado = 'status-ejecucion';
    if (tipo === 'finalizados') claseEstado = 'status-finalizado';
    
    fila.innerHTML = `
        <td>
            <strong>${escapeHtml(servicio.name_customer)}</strong>
            <br>
            <small class="text-muted">${escapeHtml(servicio.address_customer)}</small>
        </td>
        <td>
            <div class="fecha-servicio">${fecha}</div>
        </td>
        <td>
            ${servicio.empleados_asignados ? escapeHtml(servicio.empleados_asignados) : 'Sin asignar'}
        </td>
        <td>
            <span class="status-badge ${claseEstado}">
                ${escapeHtml(servicio.name_service_status)}
            </span>
        </td>
    `;
    
    return fila;
}

/**
 * Función auxiliar para formatear fechas
 */
function formatearFecha(fechaString) {
    const fecha = new Date(fechaString);
    return fecha.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Función auxiliar para escapar HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Función para limpiar filtros rápidamente
 */
function limpiarFiltros() {
    window.location.href = '?c=service';
}

/**
 * Función para inicializar los offsets desde PHP
 * Esta función debe ser llamada después de cargar el DOM
 */
function inicializarOffsets(programados, ejecucion, finalizados) {
    offsetServicios.programados = programados;
    offsetServicios.ejecucion = ejecucion;
    offsetServicios.finalizados = finalizados;
}