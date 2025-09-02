/**
 * Funciones para manejar formularios expandibles en tablas catálogo
 * Uso: toggleFormulario('nombreFormulario', 'textoBotonOriginal', 'colorOriginal')
 */

function toggleFormulario(nombreFormulario, textoOriginal = 'Agregar Nuevo', colorOriginal = 'btn-primary') {
  const formulario = document.getElementById(`formulario${nombreFormulario}`);
  const boton = document.getElementById(`btnToggle${nombreFormulario}`);
  
  if (!formulario || !boton) {
    console.error(`No se encontró el formulario o botón para: ${nombreFormulario}`);
    return;
  }
  
  if (formulario.style.display === 'none' || formulario.style.display === '') {
    // Mostrar formulario
    mostrarFormulario(formulario, boton, textoOriginal, colorOriginal);
  } else {
    // Ocultar formulario
    ocultarFormulario(formulario, boton, textoOriginal, colorOriginal);
  }
}

function mostrarFormulario(formulario, boton, textoOriginal, colorOriginal) {
  // Mostrar formulario con animación
  formulario.style.display = 'block';
  setTimeout(() => {
    formulario.classList.add('show');
  }, 10);
  
  // Cambiar botón a "Cancelar" con color rojo
  boton.innerHTML = '<i class="fas fa-times"></i> Cancelar';
  boton.className = boton.className.replace(colorOriginal, 'btn-danger');
  
  // Guardar estado original en data attributes
  boton.setAttribute('data-texto-original', textoOriginal);
  boton.setAttribute('data-color-original', colorOriginal);
}

function ocultarFormulario(formulario, boton, textoOriginal, colorOriginal) {
  // Animar salida
  formulario.classList.remove('show');
  
  setTimeout(() => {
    formulario.style.display = 'none';
  }, 300);
  
  // Restaurar botón original
  const textoBotonOriginal = boton.getAttribute('data-texto-original') || textoOriginal;
  const colorBotonOriginal = boton.getAttribute('data-color-original') || colorOriginal;
  
  boton.innerHTML = `<i class="fas fa-plus"></i> ${textoBotonOriginal}`;
  boton.className = boton.className.replace('btn-danger', colorBotonOriginal);
}

function cancelarFormulario(nombreFormulario, textoOriginal = 'Agregar Nuevo', colorOriginal = 'btn-primary') {
  const formulario = document.getElementById(`formulario${nombreFormulario}`);
  const boton = document.getElementById(`btnToggle${nombreFormulario}`);
  
  if (!formulario || !boton) {
    console.error(`No se encontró el formulario o botón para: ${nombreFormulario}`);
    return;
  }
  
  // Limpiar todos los campos del formulario
  limpiarCamposFormulario(formulario);
  
  // Ocultar formulario
  ocultarFormulario(formulario, boton, textoOriginal, colorOriginal);
}

function limpiarCamposFormulario(formulario) {
  // Limpiar inputs de texto
  const inputs = formulario.querySelectorAll('input[type="text"], input[type="email"], input[type="number"]');
  inputs.forEach(input => input.value = '');
  
  // Limpiar textareas
  const textareas = formulario.querySelectorAll('textarea');
  textareas.forEach(textarea => textarea.value = '');
  
  // Limpiar selects
  const selects = formulario.querySelectorAll('select');
  selects.forEach(select => select.selectedIndex = 0);
  
  // Limpiar checkboxes
  const checkboxes = formulario.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach(checkbox => checkbox.checked = false);
  
  // Limpiar radio buttons
  const radios = formulario.querySelectorAll('input[type="radio"]');
  radios.forEach(radio => radio.checked = false);
}

// Función para manejar éxito después del envío del formulario
function manejarExitoFormulario(nombreFormulario, mensaje = 'Registro agregado exitosamente') {
  const formulario = document.getElementById(`formulario${nombreFormulario}`);
  const boton = document.getElementById(`btnToggle${nombreFormulario}`);
  
  if (formulario && boton) {
    ocultarFormulario(formulario, boton, 'Agregar Nuevo', 'btn-primary');
  }
  
  // Mostrar mensaje de éxito (opcional)
  if (mensaje) {
    mostrarMensajeExito(mensaje);
  }
}

function mostrarMensajeExito(mensaje) {
  // Crear un alert temporal de Bootstrap
  const alertHtml = `
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="alertExito">
      <i class="fas fa-check-circle"></i> ${mensaje}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  `;
  
  // Insertar al inicio del content
  const content = document.querySelector('.content');
  if (content) {
    content.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
      const alert = document.getElementById('alertExito');
      if (alert) {
        alert.remove();
      }
    }, 5000);
  }
}