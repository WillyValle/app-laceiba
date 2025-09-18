<!-- Vista para subir croquis - app/vistas/service/subirCroquis.php -->

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Subir Croquis</h1>
                <small class="text-muted">Servicio #<?= isset($servicio) ? $servicio->id_service : $_GET['id'] ?></small>
            </div>
            <div class="col-sm-6">
                <div class="breadcrumb float-sm-right">
                    <a href="?c=service&a=VistaTablaAdmin" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver a la Tabla
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mostrar errores -->
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Error al subir el archivo:</strong>
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

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-upload"></i> Subir Croquis PDF
                        </h3>
                    </div>
                    
                    <?php if (isset($servicio)): ?>
                    <!-- Información del servicio -->
                    <div class="card-body bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Cliente:</strong> <?= htmlspecialchars($servicio->name_customer) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Estado:</strong> 
                                <span class="badge badge-info"><?= htmlspecialchars($servicio->name_service_status) ?></span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <strong>Dirección:</strong> <?= htmlspecialchars($servicio->address_customer) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Formulario de subida -->
                    <form action="?c=service&a=SubirCroquis&id=<?= $_GET['id'] ?>" method="POST" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="croquis_pdf">
                                    <i class="fas fa-file-pdf"></i> Seleccionar Archivo PDF
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" 
                                               class="custom-file-input" 
                                               id="croquis_pdf" 
                                               name="croquis_pdf"
                                               accept=".pdf"
                                               required>
                                        <label class="custom-file-label" for="croquis_pdf">Seleccionar archivo...</label>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Archivo PDF máximo 10MB. Se guardará como croquis de referencia para el servicio.
                                </small>
                            </div>

                            <!-- Instrucciones -->
                            <div class="alert alert-info">
                                <h5><i class="fas fa-lightbulb"></i> Recomendaciones:</h5>
                                <ul class="mb-0">
                                    <li>Asegúrese de que el croquis sea legible y de buena calidad</li>
                                    <li>Incluya toda la información relevante del área a tratar</li>
                                    <li>El archivo debe ser un PDF válido y no superar los 5MB</li>
                                    <li>Una vez subido, podrá ser visualizado desde la tabla administrativa</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload"></i> Subir Croquis
                            </button>
                            <a href="?c=service&a=VistaTablaAdmin" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
                    </div>