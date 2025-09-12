<div id="formularioUserEmployee" class="card card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <?php echo isset($datos) ? 'Editar Usuario Empleado' : 'Agregar Empleado'; ?>
    </h3>
  </div>
  <form action="?c=useremployee&a=Guardar" method="POST">
    <input type="hidden" id="id_user_employee" name="id_user_employee" value="<?php echo $datos->id_user_employee ?? ''; ?>">

    <!-- Campo oculto para username -->
    <input type="hidden" name="username" value="<?php echo htmlspecialchars($datos->username ?? ''); ?>">

    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="employee_name">Nombre del Empleado</label>
            <input 
              type="text" 
              class="form-control" 
              id="employee_name" 
              value="<?php echo htmlspecialchars($datos->employee_name ?? ''); ?>" 
              disabled>
            <input type="hidden" name="employee_name" value="<?php echo htmlspecialchars($datos->employee_name ?? ''); ?>">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="username">Usuario</label>
            <input 
              type="text" 
              class="form-control" 
              id="username" 
              value="<?php echo htmlspecialchars($datos->username ?? ''); ?>" 
              disabled>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="password_hash">Nueva Contraseña<span class="text-danger">*</span></label>
            <input 
              type="password" 
              class="form-control" 
              id="password_hash" 
              name="password" 
              placeholder="Ingrese nueva contraseña"
              minlength="4"
              maxlength="64"
              pattern="^(?=(?:.*[A-Za-z]){2,})(?=.*\d)[^\s]{4,}$"
              title="Debe tener al menos 4 caracteres, incluir mínimo 2 letras y 1 número, sin espacios.">
              <small class="form-text text-muted">
              <i class="fas fa-info-circle"></i> Debe tener al menos 4 caracteres, incluir mínimo 2 letras y 1 número, sin espacios.
            </small>
            </div>
          </div>
                
        <div class="col-md-6">
          <div class="form-group">
            <label for="force_password_change">Forzar Cambio de Contraseña<span class="text-danger">*</span></label>
            <select class="form-control" id="force_password_change" name="force_password_change" required>
              <option value="1" <?php echo ($datos->force_password_change ?? null) == 1 ? 'selected' : ''; ?>>Sí</option>
              <option value="0" <?php echo ($datos->force_password_change ?? null) == 0 ? 'selected' : ''; ?>>No</option>
            </select>
          </div>
        </div>
      </div>

      <?php if (isset($datos)): ?>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="status">Estado</label>
            <select class="form-control" id="status" disabled>
              <option value="1" <?php echo $datos->status == 1 ? 'selected' : ''; ?>>Activo</option>
              <option value="0" <?php echo $datos->status == 0 ? 'selected' : ''; ?>>Inactivo</option>
            </select>
            <input type="hidden" name="status" value="<?php echo $datos->status ?? 1; ?>">
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <?php echo isset($datos) ? 'Actualizar' : 'Guardar'; ?>
      </button>
      <a href="?c=useremployee" class="btn btn-secondary ml-2">Cancelar</a>
    </div>
  </form>
</div>
</div>