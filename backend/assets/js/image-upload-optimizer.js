/**
 * Optimizador de subida de imágenes para AdminLTE3
 * Comprime imágenes en el cliente antes de subir y muestra progreso con Toastr
 * Compatible con el sistema existente
 */

(function($) {
    'use strict';

    // Configuración por defecto
    const defaultConfig = {
        maxWidth: 1920,
        maxHeight: 1920,
        quality: 0.85,
        maxFileSize: 50 * 1024 * 1024, // 50MB
        showPreview: true,
        useToastr: true // Usa las notificaciones de AdminLTE
    };

    /**
     * Comprimir una imagen usando Canvas
     */
    function compressImage(file, config) {
        return new Promise((resolve, reject) => {
            // Si el archivo es pequeño, no comprimirlo
            if (file.size < 500 * 1024) {
                resolve(file);
                return;
            }

            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = new Image();
                
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    // Calcular nuevas dimensiones
                    if (width > config.maxWidth || height > config.maxHeight) {
                        const ratio = Math.min(config.maxWidth / width, config.maxHeight / height);
                        width = Math.floor(width * ratio);
                        height = Math.floor(height * ratio);
                    }

                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function(blob) {
                        if (blob) {
                            const compressedFile = new File([blob], file.name, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            
                            const savedKB = ((file.size - compressedFile.size) / 1024).toFixed(1);
                            const savedPercent = (((file.size - compressedFile.size) / file.size) * 100).toFixed(1);
                            
                            console.log(`✓ ${file.name}: ${(file.size/1024).toFixed(1)}KB → ${(compressedFile.size/1024).toFixed(1)}KB (${savedPercent}% reducido)`);
                            
                            resolve(compressedFile);
                        } else {
                            reject(new Error('Error al comprimir'));
                        }
                    }, 'image/jpeg', config.quality);
                };

                img.onerror = function() {
                    reject(new Error('Error al cargar imagen'));
                };

                img.src = e.target.result;
            };

            reader.onerror = function() {
                reject(new Error('Error al leer archivo'));
            };

            reader.readAsDataURL(file);
        });
    }

    /**
     * Comprimir múltiples imágenes con progreso
     */
    async function compressMultiple(files, config, onProgress) {
        const compressedFiles = [];
        const total = files.length;

        for (let i = 0; i < files.length; i++) {
            try {
                const compressed = await compressImage(files[i], config);
                compressedFiles.push(compressed);
                
                if (onProgress) {
                    onProgress({
                        current: i + 1,
                        total: total,
                        percentage: Math.round(((i + 1) / total) * 100),
                        fileName: files[i].name
                    });
                }
            } catch (error) {
                console.error(`Error comprimiendo ${files[i].name}:`, error);
                // Si falla, usar original
                compressedFiles.push(files[i]);
            }
        }

        return compressedFiles;
    }

    /**
     * Crear preview de imágenes
     */
    function createPreview(file, container) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const col = $('<div>', {
                    class: 'col-md-3 col-sm-4 col-6 mb-3'
                });
                
                const card = $('<div>', {
                    class: 'card card-outline card-success'
                });
                
                const img = $('<img>', {
                    src: e.target.result,
                    class: 'card-img-top',
                    css: {
                        'height': '150px',
                        'object-fit': 'cover'
                    }
                });
                
                const cardBody = $('<div>', {
                    class: 'card-body p-2'
                });
                
                const fileName = $('<small>', {
                    class: 'd-block text-truncate',
                    text: file.name
                });
                
                const fileSize = $('<small>', {
                    class: 'text-muted d-block',
                    text: (file.size / 1024).toFixed(1) + ' KB'
                });
                
                cardBody.append(fileName).append(fileSize);
                card.append(img).append(cardBody);
                col.append(card);
                container.append(col);
                
                resolve();
            };
            
            reader.readAsDataURL(file);
        });
    }

    /**
     * Mostrar progreso usando AdminLTE Progress Bar
     */
    function showProgress(container, percentage, text) {
        let progressContainer = container.find('.progress-container');
        
        if (progressContainer.length === 0) {
            progressContainer = $(`
                <div class="progress-container mt-3">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             role="progressbar" 
                             style="width: 0%;">
                            <span class="progress-text">0%</span>
                        </div>
                    </div>
                    <small class="progress-info text-muted d-block mt-1"></small>
                </div>
            `);
            container.append(progressContainer);
        }
        
        const progressBar = progressContainer.find('.progress-bar');
        const progressText = progressContainer.find('.progress-text');
        const progressInfo = progressContainer.find('.progress-info');
        
        progressBar.css('width', percentage + '%');
        progressText.text(percentage + '%');
        if (text) {
            progressInfo.text(text);
        }
        
        if (percentage >= 100) {
            progressBar.removeClass('progress-bar-animated bg-success').addClass('bg-success');
        }
    }

    /**
     * Plugin jQuery principal
     */
    $.fn.imageUploadOptimizer = function(options) {
        const config = $.extend({}, defaultConfig, options);
        
        return this.each(function() {
            const $form = $(this);
            const $fileInputs = $form.find('input[type="file"][accept*="image"]');
            
            if ($fileInputs.length === 0) {
                console.warn('ImageUploadOptimizer: No se encontraron inputs de archivo de imágenes');
                return;
            }
            
            // Procesar cada input de archivo
            $fileInputs.each(function() {
                const $fileInput = $(this);
                const inputId = $fileInput.attr('id');
                
                // Crear contenedor para preview si se configuró
                if (config.showPreview) {
                    let $previewContainer = $fileInput.parent().find('.image-preview-container');
                    
                    if ($previewContainer.length === 0) {
                        $previewContainer = $(`
                            <div class="image-preview-container mt-3" style="display:none;">
                                <h5><i class="fas fa-images"></i> Imágenes seleccionadas:</h5>
                                <div class="row image-preview-row"></div>
                            </div>
                        `);
                        $fileInput.parent().append($previewContainer);
                    }
                    
                    // Evento cuando se seleccionan archivos
                    $fileInput.on('change', async function() {
                        const files = Array.from(this.files);
                        const $previewRow = $previewContainer.find('.image-preview-row');
                        
                        if (files.length === 0) {
                            $previewContainer.hide();
                            return;
                        }
                        
                        $previewRow.empty();
                        $previewContainer.show();
                        
                        // Mostrar preview de cada imagen
                        for (const file of files) {
                            if (file.type.startsWith('image/')) {
                                await createPreview(file, $previewRow);
                            }
                        }
                    });
                }
            });
            
            // Interceptar envío del formulario
            $form.on('submit', async function(e) {
                const $submitButton = $form.find('button[type="submit"], input[type="submit"]');
                const originalButtonHtml = $submitButton.html();
                const filesToCompress = [];
                const fileInputsToProcess = [];
                
                // Recolectar todos los archivos de imagen
                $fileInputs.each(function() {
                    const files = this.files;
                    if (files && files.length > 0) {
                        for (let file of files) {
                            if (file.type.startsWith('image/')) {
                                filesToCompress.push(file);
                                fileInputsToProcess.push(this);
                            }
                        }
                    }
                });
                
                // Si no hay imágenes, continuar normal
                if (filesToCompress.length === 0) {
                    return true;
                }
                
                // Prevenir envío y procesar imágenes
                e.preventDefault();
                
                // Deshabilitar botón
                $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                
                try {
                    // Mostrar notificación con Toastr si está disponible
                    if (config.useToastr && typeof toastr !== 'undefined') {
                        toastr.info('Comprimiendo imágenes...', 'Procesando', {
                            timeOut: 0,
                            extendedTimeOut: 0,
                            closeButton: false
                        });
                    }
                    
                    // Crear contenedor de progreso
                    let $progressContainer = $form.find('.compression-progress');
                    if ($progressContainer.length === 0) {
                        $progressContainer = $('<div class="compression-progress"></div>');
                        $submitButton.parent().prepend($progressContainer);
                    }
                    
                    // Comprimir imágenes
                    const compressedFiles = await compressMultiple(
                        filesToCompress,
                        config,
                        (progress) => {
                            showProgress(
                                $progressContainer,
                                progress.percentage,
                                `Comprimiendo ${progress.current} de ${progress.total}: ${progress.fileName}`
                            );
                        }
                    );
                    
                    // Limpiar notificación de compresión
                    if (config.useToastr && typeof toastr !== 'undefined') {
                        toastr.clear();
                    }
                    
                    // Crear FormData con imágenes comprimidas
                    const formData = new FormData($form[0]);
                    
                    // Eliminar archivos originales y agregar comprimidos
                    $fileInputs.each(function() {
                        const inputName = $(this).attr('name');
                        if (inputName) {
                            formData.delete(inputName);
                        }
                    });
                    
                    // Agregar archivos comprimidos
                    const inputName = $fileInputs.first().attr('name');
                    compressedFiles.forEach(file => {
                        formData.append(inputName, file);
                    });
                    
                    // Actualizar progreso para subida
                    showProgress($progressContainer, 0, 'Subiendo imágenes al servidor...');
                    $submitButton.html('<i class="fas fa-cloud-upload-alt"></i> Subiendo...');
                    
                    // Enviar con XMLHttpRequest para mostrar progreso
                    const xhr = new XMLHttpRequest();
                    
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            showProgress(
                                $progressContainer,
                                percentComplete,
                                `Subiendo... ${percentComplete}%`
                            );
                        }
                    });
                    
                    xhr.addEventListener('load', function() {
                        if (xhr.status === 200) {
                            showProgress($progressContainer, 100, '¡Completado!');
                            
                            if (config.useToastr && typeof toastr !== 'undefined') {
                                toastr.success('Imágenes subidas exitosamente', 'Éxito');
                            }
                            
                            // Redirigir o recargar
                            setTimeout(function() {
                                window.location.href = xhr.responseURL || $form.attr('action');
                            }, 1000);
                        } else {
                            throw new Error('Error en la subida');
                        }
                    });
                    
                    xhr.addEventListener('error', function() {
                        if (config.useToastr && typeof toastr !== 'undefined') {
                            toastr.error('Error al subir las imágenes', 'Error');
                        } else {
                            alert('Error al subir las imágenes. Por favor, intente nuevamente.');
                        }
                        $submitButton.prop('disabled', false).html(originalButtonHtml);
                        $progressContainer.remove();
                    });
                    
                    xhr.open($form.attr('method') || 'POST', $form.attr('action'));
                    xhr.send(formData);
                    
                } catch (error) {
                    console.error('Error:', error);
                    if (config.useToastr && typeof toastr !== 'undefined') {
                        toastr.error('Error al procesar las imágenes: ' + error.message, 'Error');
                    } else {
                        alert('Error al procesar las imágenes: ' + error.message);
                    }
                    $submitButton.prop('disabled', false).html(originalButtonHtml);
                }
                
                return false;
            });
        });
    };

})(jQuery);