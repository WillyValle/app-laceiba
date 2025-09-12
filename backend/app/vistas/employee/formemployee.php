<div id="formularioEmployee" class="card card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <?php echo isset($datos) ? 'Editar Empleado' : 'Agregar Empleado'; ?>
    </h3>
  </div>
  <form action="?c=employee&a=Guardar" method="POST">
    <input type="hidden" id="id_employee" name="id_employee" value="<?php echo isset($datos) ? $datos->id_employee : ''; ?>">
    
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="name_employee">Nombre del Empleado<span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              id="name_employee" 
              name="name_employee" 
              placeholder="Ingrese el nombre del empleado"
              pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9]+(\s[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9]+)*$"
              title="Solo letras, números y espacios. Mínimo 2 caracteres."
              minlength="2"
              maxlength="50"
              value="<?php echo isset($datos) ? htmlspecialchars($datos->name_employee) : ''; ?>"
              required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="lastname_employee">Apellido del Empleado<span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              id="lastname_employee" 
              name="lastname_employee" 
              placeholder="Ingrese el apellido del empleado"
              pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9]+(\s[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9]+)*$"
              title="Solo letras, números y espacios. Mínimo 2 caracteres."
              minlength="2"
              maxlength="50"
              value="<?php echo isset($datos) ? htmlspecialchars($datos->lastname_employee) : ''; ?>"
              required>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label for="address_employee">Dirección <span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              id="address_employee" 
              name="address_employee" 
              placeholder="Ingrese la dirección"
              maxlength="150"
              value="<?php echo isset($datos) ? htmlspecialchars($datos->address_employee) : ''; ?>"
              required>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="type_doc_id_type_doc">Tipo de Documento<span class="text-danger">*</span></label>
            <select class="form-control" id="type_doc_id_type_doc" name="type_doc_id_type_doc" required>
              <option value="">Seleccione...</option>
              <?php foreach($this->modelo->ListarTipoDoc() as $tipo): ?>
                <option value="<?= $tipo->ID_TYPE_DOC ?>" 
                        <?php echo (isset($datos) && $datos->type_doc_id_type_doc == $tipo->ID_TYPE_DOC) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($tipo->NAME_TYPE_DOC) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="doc_num">Número de Documento<span class="text-danger">*</span></label>
            <input 
              type="text" 
              class="form-control" 
              id="doc_num" 
              name="doc_num" 
              placeholder="Ingrese el número de documento"
              maxlength="25"
              value="<?php echo isset($datos) ? htmlspecialchars($datos->doc_num) : ''; ?>"
              required>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="image_employee">Imagen del Empleado</label>
            <input 
              type="text" 
              class="form-control" 
              id="image_employee" 
              name="image_employee" 
              placeholder="Ingrese la imagen del empleado"
              maxlength="200"
              value="<?php echo htmlspecialchars($datos->image_employee ?? ''); ?>">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="role_employee_id_role_employee">Rol del Empleado<span class="text-danger">*</span></label>
            <select class="form-control" id="role_employee_id_role_employee" name="role_employee_id_role_employee" required>
              <option value="">Seleccione...</option>
              <?php foreach($this->modelo->ListarRoles() as $rol): ?>
                <option value="<?= $rol->ID_ROLE_EMPLOYEE ?>"
                        <?php echo (isset($datos) && $datos->role_employee_id_role_employee == $rol->ID_ROLE_EMPLOYEE) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($rol->NAME_ROLE_EMPLOYEE) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="whatsapp">WhatsApp</label>
            <input 
              type="tel" 
              class="form-control" 
              id="whatsapp" 
              name="whatsapp" 
              placeholder="Ej: +502 5500-0000"
              value="<?php echo htmlspecialchars($datos->whatsapp ?? ''); ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="tel">Teléfono</label>
            <input 
              type="tel" 
              class="form-control" 
              id="tel" 
              name="tel" 
              placeholder="Ej: +502 7800-0000"
              value="<?php echo htmlspecialchars($datos->tel ?? ''); ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="mail">Email</label>
            <input 
              type="email" 
              class="form-control" 
              id="mail" 
              name="mail" 
              placeholder="ejemplo@correo.com"
              maxlength="50"
              value="<?php echo htmlspecialchars($datos->mail ?? ''); ?>">
          </div>
        </div>
      </div>

      <?php if (isset($datos)): ?>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="status">Estado</label>
            <select class="form-control" id="status" name="status">
              <option value="1" <?php echo ($datos->status == 1) ? 'selected' : ''; ?>>Activo</option>
              <option value="0" <?php echo ($datos->status == 0) ? 'selected' : ''; ?>>Inactivo</option>
            </select>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <?php echo isset($datos) ? 'Actualizar' : 'Guardar'; ?>
      </button>
      <a href="?c=employee" class="btn btn-secondary ml-2">Cancelar</a>
    </div>
  </form>
</div>
      </div>