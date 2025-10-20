<!-- Content Wrapper. Contains page content -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fab fa-whatsapp text-success"></i> WhatsApp - Panel de Control</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?c=inicio">Inicio</a></li>
                        <li class="breadcrumb-item active">WhatsApp</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Estadísticas -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $data['stats']['data']['total'] ?? 0; ?></h3>
                            <p>Total Enviados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $data['stats']['data']['sent'] ?? 0; ?></h3>
                            <p>Exitosos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $data['stats']['data']['failed'] ?? 0; ?></h3>
                            <p>Fallidos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $data['stats']['data']['pending'] ?? 0; ?></h3>
                            <p>Pendientes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Control -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Estado de WhatsApp</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span id="connection-badge" class="badge badge-secondary" style="font-size: 1.1rem; padding: 0.5rem 1rem;">
                                    <i class="fas fa-spinner fa-spin"></i> Verificando...
                                </span>
                            </div>

                            <div id="connection-info" class="mt-3"></div>

                            <div class="text-center mt-3">
                                <button id="btn-show-qr" class="btn btn-info d-none" onclick="showQRCode()">
                                    <i class="fas fa-qrcode"></i> Mostrar QR
                                </button>
                                <button id="btn-logout" class="btn btn-danger d-none" onclick="logoutWhatsApp()">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </button>
                                <button id="btn-reset" class="btn btn-warning" onclick="resetConnection()" title="Restablecer conexión y generar nuevo QR">
                                    <i class="fas fa-sync-alt"></i> Restablecer
                                </button>
                                <button class="btn btn-secondary" onclick="checkStatus()">
                                    <i class="fas fa-refresh"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs en Tiempo Real -->
                <div class="col-md-8">
                    <div class="card card-dark">
                        <div class="card-header">
                            <h3 class="card-title">Logs del Servicio</h3>
                            <div class="card-tools">
                                <button class="btn btn-tool" onclick="refreshLogs()">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="log-viewer" style="background: #1e1e1e; color: #d4d4d4; font-family: 'Courier New', monospace; font-size: 12px; padding: 15px; height: 400px; overflow-y: auto;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando logs...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Envío de Mensaje de Prueba -->
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-paper-plane"></i> Enviar Mensaje de Prueba
                            </h3>
                        </div>
                        <div class="card-body">
                            <form id="test-message-form" onsubmit="return false;">
                                <div class="form-group">
                                    <label for="test-phone">
                                        <i class="fas fa-phone"></i> Número de Teléfono
                                        <small class="text-muted">(Incluir código de país, ej: 50212345678)</small>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="test-phone" 
                                        name="phone"
                                        placeholder="50212345678"
                                        required
                                        pattern="[0-9]{8,15}"
                                        title="Solo números, entre 8 y 15 dígitos"
                                    >
                                    <small class="form-text text-muted">
                                        Ejemplo para Guatemala: 50212345678 (502 + número)
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="test-message">
                                        <i class="fas fa-comment"></i> Mensaje de Prueba
                                    </label>
                                    <textarea 
                                        class="form-control" 
                                        id="test-message" 
                                        name="message"
                                        rows="4"
                                        placeholder="Escribe tu mensaje de prueba aquí..."
                                        required
                                        maxlength="1000"
                                    ></textarea>
                                    <small class="form-text text-muted">
                                        <span id="char-count">0</span> / 1000 caracteres
                                    </small>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-success btn-lg btn-block" id="btn-send-test" onclick="sendTestMessage()">
                                        <i class="fas fa-paper-plane"></i> Enviar Mensaje de Prueba
                                    </button>
                                </div>

                                <div id="test-result" class="mt-3" style="display: none;"></div>
                            </form>
                        </div>
                        <div class="card-footer">
                            <a href="?c=whatsapp&a=Logs" class="btn btn-sm btn-primary">
                                <i class="fas fa-history"></i> Ver Historial Completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Modal de QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode"></i> Escanear Código QR
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="qrContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Generando código QR...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar librería QRCode.js si no está cargada
if (typeof QRCode === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    document.head.appendChild(script);
}

// Inicializar al cargar la página
$(document).ready(function() {
    checkStatus();
    refreshLogs();
    
    // Contador de caracteres para el mensaje
    $('#test-message').on('input', function() {
        const length = $(this).val().length;
        $('#char-count').text(length);
    });
});

/**
 * Verifica el estado de conexión de WhatsApp
 */
function checkStatus() {
    $('#connection-badge').html('<i class="fas fa-spinner fa-spin"></i> Verificando...');
    $('#connection-badge').removeClass('badge-success badge-danger').addClass('badge-secondary');

    $.ajax({
        url: '?c=whatsapp&a=Ajax',
        method: 'POST',
        data: { action: 'get_status' },
        dataType: 'json',
        success: function(response) {
            console.log('Status:', response);
            
            // ✅ Verificar isReady en lugar de connected
            if (response.success && (response.connected || response.isReady)) {
                $('#connection-badge').html('<i class="fas fa-check-circle"></i> Conectado')
                    .removeClass('badge-secondary badge-danger').addClass('badge-success');
                $('#btn-show-qr').addClass('d-none');
                $('#btn-logout').removeClass('d-none');
                
                let infoHtml = '<div class="alert alert-success">';
                infoHtml += '<i class="fas fa-check-circle"></i> <strong>WhatsApp Conectado</strong><br>';
                
                if (response.phoneNumber) {
                    infoHtml += `<small>Número: ${response.phoneNumber}</small>`;
                } else if (response.status) {
                    infoHtml += `<small>Estado: ${response.status}</small>`;
                }
                
                infoHtml += '</div>';
                $('#connection-info').html(infoHtml);
            } else {
                $('#connection-badge').html('<i class="fas fa-times-circle"></i> Desconectado')
                    .removeClass('badge-secondary badge-success').addClass('badge-danger');
                $('#btn-show-qr').removeClass('d-none');
                $('#btn-logout').addClass('d-none');
                $('#connection-info').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> 
                        Necesitas escanear el código QR para conectar WhatsApp.
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error checking status:', error, xhr.responseText);
            $('#connection-badge').html('<i class="fas fa-exclamation-triangle"></i> Error')
                .removeClass('badge-secondary badge-success').addClass('badge-danger');
            $('#connection-info').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> 
                    No se pudo conectar con el servicio de WhatsApp.
                    <br><small>${error}</small>
                </div>
            `);
        }
    });
}

/**
 * Muestra el código QR
 */
function showQRCode() {
    $('#qrModal').modal('show');
    $('#qrContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Generando código QR...</p></div>');

    // Asegurar que QRCode.js esté cargado
    ensureQRCodeLoaded(function() {
        // Obtener QR del backend
        $.ajax({
            url: '?c=whatsapp&a=Ajax',
            method: 'POST',
            data: { action: 'get_qr' },
            dataType: 'json',
            success: function(response) {
                console.log('QR Response:', response);
                
                if (response.success && response.qr) {
                    // Si viene con needsGeneration, es texto y necesitamos generar el QR
                    if (response.needsGeneration && response.qrText) {
                        $('#qrContent').html(`
                            <div id="qr-code-container" style="display: flex; justify-content: center; padding: 20px;"></div>
                            <p class="mt-3"><strong>Escanea este código con WhatsApp</strong></p>
                            <small class="text-muted">WhatsApp → Configuración → Dispositivos vinculados → Vincular dispositivo</small>
                        `);
                        
                        // Generar QR con librería qrcode.js
                        try {
                            new QRCode(document.getElementById("qr-code-container"), {
                                text: response.qrText,
                                width: 300,
                                height: 300,
                                colorDark : "#000000",
                                colorLight : "#ffffff",
                                correctLevel : QRCode.CorrectLevel.H
                            });
                        } catch (e) {
                            console.error('Error generando QR:', e);
                            $('#qrContent').html(`
                                <div class="alert alert-danger">
                                    Error al generar el código QR: ${e.message}
                                </div>
                            `);
                        }
                    } else {
                        // Ya viene como imagen (base64 o data URI)
                        $('#qrContent').html(`
                            <img src="${response.qr}" class="img-fluid" style="max-width: 300px;" alt="QR Code">
                            <p class="mt-3"><strong>Escanea este código con WhatsApp</strong></p>
                            <small class="text-muted">WhatsApp → Configuración → Dispositivos vinculados → Vincular dispositivo</small>
                        `);
                    }
                } else if (response.alreadyConnected || (response.error && response.error.includes('Ya estás conectado'))) {
                    // ✅ Ya está conectado
                    $('#qrContent').html(`
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-3x mb-3 d-block"></i>
                            <h5>WhatsApp Ya Está Conectado</h5>
                            <p>No es necesario escanear el código QR.</p>
                            <small>Cerrando...</small>
                        </div>
                    `);
                    setTimeout(() => {
                        $('#qrModal').modal('hide');
                        checkStatus();
                    }, 2000);
                } else if (response.error && response.error.includes('conexión')) {
                    // ❌ Error de conexión
                    $('#qrContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Error de Conexión</strong>
                            <p>${response.error}</p>
                            <small>Verifica que el servicio de Baileys esté corriendo.</small>
                        </div>
                    `);
                } else {
                    // ❌ Otro error
                    $('#qrContent').html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>No se pudo generar el QR</strong>
                            <p>${response.error || 'Error desconocido'}</p>
                            <button class="btn btn-primary btn-sm mt-2" onclick="$('#qrModal').modal('hide'); resetConnection();">
                                <i class="fas fa-sync-alt"></i> Restablecer Conexión
                            </button>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('QR Error:', error, xhr.responseText);
                $('#qrContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i>
                        <strong>Error al obtener el código QR</strong>
                        <p>Error de comunicación con el servidor.</p>
                        <small>${error}</small>
                    </div>
                `);
            }
        });
    });
}

/**
 * Asegura que QRCode.js esté cargado
 */
function ensureQRCodeLoaded(callback) {
    if (typeof QRCode !== 'undefined') {
        callback();
        return;
    }
    
    // Cargar librería
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    script.onload = callback;
    script.onerror = function() {
        console.error('Error cargando QRCode.js');
        $('#qrContent').html(`
            <div class="alert alert-danger">
                Error al cargar la librería de QR. Intente recargar la página.
            </div>
        `);
    };
    document.head.appendChild(script);
}

/**
 * Cierra sesión de WhatsApp
 */
function logoutWhatsApp() {
    if (!confirm('¿Está seguro de desconectar WhatsApp?')) {
        return;
    }

    $.ajax({
        url: '?c=whatsapp&a=Ajax',
        method: 'POST',
        data: { action: 'logout' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('WhatsApp desconectado exitosamente');
                checkStatus();
            } else {
                alert('Error: ' + (response.error || 'No se pudo desconectar'));
            }
        },
        error: function() {
            alert('Error de conexión');
        }
    });
}

/**
 * Restablece la conexión (elimina sesión y reinicia)
 */
function resetConnection() {
    if (!confirm('¿Desea restablecer la conexión? Esto eliminará la sesión actual y generará un nuevo código QR.\n\nDeberá escanear el QR nuevamente con WhatsApp.')) {
        return;
    }

    // Mostrar loading
    $('#connection-badge').html('<i class="fas fa-spinner fa-spin"></i> Restableciendo...');
    $('#btn-reset').prop('disabled', true);

    $.ajax({
        url: '?c=whatsapp&a=Ajax',
        method: 'POST',
        data: { action: 'reset_connection' },
        dataType: 'json',
        success: function(response) {
            $('#btn-reset').prop('disabled', false);
            console.log('Reset response:', response);
            
            if (response.success) {
                alert('✅ Conexión restablecida exitosamente.\n\n' + 
                      'Archivos eliminados: ' + (response.files_deleted || 0) + '\n\n' +
                      'Espere 5-10 segundos y luego haga click en "Mostrar QR".');
                
                // Esperar 7 segundos y actualizar estado
                setTimeout(() => {
                    checkStatus();
                }, 7000);
            } else {
                // Mostrar error detallado
                let errorMsg = 'Error al restablecer:\n\n' + (response.error || 'Error desconocido');
                
                if (response.details) {
                    errorMsg += '\n\nDetalles:\n' + response.details;
                }
                
                if (response.suggestion) {
                    errorMsg += '\n\nSugerencia:\n' + response.suggestion;
                }
                
                if (response.files_deleted > 0) {
                    errorMsg += '\n\nNota: Se eliminaron ' + response.files_deleted + ' archivos de sesión, pero el contenedor no se pudo reiniciar.';
                }
                
                alert(errorMsg);
                checkStatus();
            }
        },
        error: function(xhr, status, error) {
            $('#btn-reset').prop('disabled', false);
            console.error('Reset error:', {xhr, status, error, response: xhr.responseText});
            
            // Intentar parsear la respuesta
            let errorMsg = 'Error de conexión al restablecer';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    errorMsg = response.error;
                }
            } catch (e) {
                // Si no es JSON, mostrar el texto completo
                if (xhr.responseText) {
                    errorMsg += ':\n\n' + xhr.responseText.substring(0, 500);
                }
            }
            
            alert(errorMsg);
            checkStatus();
        }
    });
}

/**
 * Refresca los logs del servicio
 */
function refreshLogs() {
    $('#log-viewer').html('<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando logs...</div>');

    $.ajax({
        url: '?c=whatsapp&a=Ajax',
        method: 'POST',
        data: { action: 'get_service_logs', lines: 50 },
        dataType: 'json',
        success: function(response) {
            console.log('Service logs:', response);
            
            if (response.success && response.logs) {
                const lines = response.logs.split('\n');
                let html = '';
                lines.forEach(line => {
                    if (line.trim()) {
                        let className = 'log-line';
                        if (line.includes('ERROR')) className += ' log-error';
                        else if (line.includes('WARN')) className += ' log-warn';
                        else if (line.includes('INFO')) className += ' log-info';
                        html += `<div class="${className}" style="margin: 2px 0; word-wrap: break-word;">${escapeHtml(line)}</div>`;
                    }
                });
                $('#log-viewer').html(html || 'No hay logs disponibles');
                $('#log-viewer').scrollTop($('#log-viewer')[0].scrollHeight);
            } else {
                $('#log-viewer').html('<div class="text-muted">No se pudieron cargar los logs</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading logs:', error);
            $('#log-viewer').html('<div class="text-danger">Error al cargar logs del servicio</div>');
        }
    });
}

/**
 * Envía un mensaje de prueba
 */
function sendTestMessage() {
    const phone = $('#test-phone').val().trim();
    const message = $('#test-message').val().trim();
    
    // Validaciones
    if (!phone || !message) {
        alert('Por favor completa todos los campos');
        return;
    }
    
    if (!/^[0-9]{8,15}$/.test(phone)) {
        alert('Número de teléfono inválido. Debe tener entre 8 y 15 dígitos y solo números.');
        return;
    }
    
    // Deshabilitar botón y mostrar loading
    const $btn = $('#btn-send-test');
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
    
    $('#test-result').hide();
    
    console.log('Enviando mensaje:', { phone, message }); // Debug
    
    $.ajax({
        url: '?c=whatsapp&a=Ajax',
        method: 'POST',
        data: {
            action: 'send_test_message',
            phone: phone,
            message: message
        },
        dataType: 'json',
        success: function(response) {
            console.log('Test message response:', response);
            
            $btn.prop('disabled', false).html(originalText);
            
            if (response.success) {
                $('#test-result')
                    .removeClass('alert-danger')
                    .addClass('alert alert-success')
                    .html(`
                        <i class="fas fa-check-circle"></i>
                        <strong>¡Mensaje enviado exitosamente!</strong>
                        <br>
                        <small>Destinatario: ${phone}</small>
                        ${response.messageId ? '<br><small>ID: ' + response.messageId + '</small>' : ''}
                    `)
                    .fadeIn();
                
                // Limpiar formulario
                $('#test-phone').val('');
                $('#test-message').val('');
                $('#char-count').text('0');
                
                // Ocultar mensaje después de 5 segundos
                setTimeout(() => {
                    $('#test-result').fadeOut();
                }, 5000);
            } else {
                $('#test-result')
                    .removeClass('alert-success')
                    .addClass('alert alert-danger')
                    .html(`
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error al enviar el mensaje</strong>
                        <br>
                        <small>${response.error || 'Error desconocido'}</small>
                    `)
                    .fadeIn();
            }
        },
        error: function(xhr, status, error) {
            console.error('Test message error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            $btn.prop('disabled', false).html(originalText);
            
            let errorMsg = 'Error de conexión';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.error || errorMsg;
            } catch (e) {
                if (xhr.responseText && xhr.responseText.length < 200) {
                    errorMsg = xhr.responseText;
                }
            }
            
            $('#test-result')
                .removeClass('alert-success')
                .addClass('alert alert-danger')
                .html(`
                    <i class="fas fa-times-circle"></i>
                    <strong>Error al enviar el mensaje</strong>
                    <br>
                    <small>${errorMsg}</small>
                `)
                .fadeIn();
        }
    });
}

/**
 * Escapa HTML
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>

<style>
.log-error { color: #f48771; }
.log-warn { color: #dcdcaa; }
.log-info { color: #4ec9b0; }
</style>