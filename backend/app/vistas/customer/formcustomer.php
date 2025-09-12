<div id="formularioCustomer" class="card card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <?php echo isset($datos) ? 'Editar Cliente' : 'Agregar Cliente'; ?>
    </h3>
  </div>
  <form action="?c=customer&a=Guardar" method="POST">
    <div class="card-body">
      <div class="form-group">
        <input class="form-control" id="id_customer" name="id_customer" type="hidden" 
               value="<?php echo isset($datos) ? $datos->id_customer : ''; ?>">
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="name_customer">
              Nombre del Cliente<span class="text-danger">*</span>
            </label>
            <input 
            type="text" 
            class="form-control" 
            id="name_customer" 
            name="name_customer" 
            placeholder="Ingrese el nombre del cliente"
            pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ-1-9]+(\s[a-zA-ZáéíóúÁÉÍÓÚñÑ]+)+*$"
            title="Solo letras y espacios. Mínimo 2 caracteres."
            minlength="2"
            maxlength="100"
            value="<?php echo isset($datos) ? htmlspecialchars($datos->name_customer) : ''; ?>"
            required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="address_customer">
              Dirección<span class="text-danger">*</span>
            </label>
            <input 
            type="text" 
            class="form-control" 
            id="address_customer" 
            name="address_customer" 
            placeholder="Ingrese la dirección"
            maxlength="150"
            value="<?php echo isset($datos) ? htmlspecialchars($datos->address_customer) : ''; ?>"
            required>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="type_doc_id_type_doc">
              Tipo de Documento<span class="text-danger">*</span>
            </label>
            <select class="form-control" id="type_doc_id_type_doc" name="type_doc_id_type_doc" required>
              <option value="">Seleccione...</option>
              <?php 
              foreach($this->modelo->ListarTipoDoc() as $tipo): 
              ?>
                <option value="<?= $tipo->ID_TYPE_DOC ?>" 
                        <?php echo (isset($datos) && $datos->type_doc_id_type_doc == $tipo->ID_TYPE_DOC) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($tipo->NAME_TYPE_DOC) ?>
                </option>
              <?php 
                endforeach;
              ?>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="doc_num">
              Número de Documento<span class="text-danger">*</span>
            </label>
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
        <div class="col-md-4">
          <div class="form-group">
            <label for="whatsapp">WhatsApp</label>
            <input 
            type="tel" 
            class="form-control" 
            id="whatsapp" 
            name="whatsapp" 
            placeholder="Ej: +502 5500-0000"
            maxlength="11"
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
            maxlength="11"
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
              <option value="1" <?php echo (isset($datos) && $datos->status == 1) ? 'selected' : ''; ?>>Activo</option>
              <option value="0" <?php echo (isset($datos) && $datos->status == 0) ? 'selected' : ''; ?>>Inactivo</option>
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
      <a href="?c=customer" class="btn btn-secondary ml-2">Cancelar</a>
    </div>
  </form>
</div>
</div>