<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Gestionar Permisos del Rol</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="?c=inicio">Inicio</a></li>
          <li class="breadcrumb-item"><a href="?c=permission">Permisos</a></li>
          <li class="breadcrumb-item active">Gestionar Rol</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-shield"></i> Configuración de Permisos
        </h3>
      </div>
      
      <form action="?c=permission&a=GuardarPermisosRol" method="POST">
        <input type="hidden" name="role_id" value="<?php echo $_GET['role_id']; ?>">
        
        <div class="card-body">
          <div class="row">
            <div class="col-md-12">
              <h5>Seleccione los permisos para este rol:</h5>
              <small class="text-muted">Los permisos marcados estarán disponibles para todos los usuarios con este rol.</small>
            </div>
          </div>
          
          <div class="row mt-3">
            <div class="col-md-12">
              <?php foreach($permisos as $permiso): ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" 
                         name="permissions[]" 
                         value="<?php echo $permiso->ID_PERMISSION; ?>"
                         id="perm_<?php echo $permiso->ID_PERMISSION; ?>"
                         <?php echo $permiso->ASIGNADO ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="perm_<?php echo $permiso->ID_PERMISSION; ?>">
                    <strong><?php echo htmlspecialchars($permiso->NAME_PERMISSION); ?></strong>
                    <?php if($permiso->DESCRIPTION): ?>
                      <br><small class="text-muted"><?php echo htmlspecialchars($permiso->DESCRIPTION); ?></small>
                    <?php endif; ?>
                  </label>
                </div>
                <hr>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar Cambios
          </button>
          <a href="?c=permission" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Volver
          </a>
        </div>
      </form>
    </div>

  </div>
</section>