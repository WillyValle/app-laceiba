<?php
$busqueda = isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '';
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-users"></i> Clientes Registrados</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Clientes Activos</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <a class="btn btn-primary" href="?c=customer&a=FormCrear">
                            <i class="fas fa-plus"></i> Agregar Cliente
                        </a>
                    </div>
                    <div class="col-md-8">
                        <form method="GET" action="" class="form-inline float-right">
                            <input type="hidden" name="c" value="customer">
                            <div class="input-group">
                                <input type="text" 
                                       name="busqueda" 
                                       class="form-control" 
                                       placeholder="Buscar cliente..."
                                       value="<?= $busqueda ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($busqueda)): ?>
                                    <a href="?c=customer" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!empty($busqueda)): ?>
                <div class="alert alert-info">
                    Mostrando resultados para: <strong><?= $busqueda ?></strong>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Nombre del Cliente</th>
                            <th>Dirección</th>
                            <th>Tipo Doc.</th>
                            <th>Número Doc.</th>
                            <th>WhatsApp</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($clientes_activos)): ?>
                            <?php foreach($clientes_activos as $r): ?>
                            <tr>
                                <td><?=htmlspecialchars($r->name_customer ?? '')?></td>
                                <td><?=htmlspecialchars($r->address_customer ?? '')?></td>
                                <td><?=htmlspecialchars($r->name_type_doc ?? 'Sin tipo')?></td>
                                <td><?=htmlspecialchars($r->doc_num ?? '')?></td>
                                <td><?=htmlspecialchars($r->whatsapp ?? '')?></td>
                                <td><?=htmlspecialchars($r->tel ?? '')?></td>
                                <td><?=htmlspecialchars($r->mail ?? '')?></td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td class="text-center">
                                    <a href="?c=customer&a=FormEditar&id=<?=$r->id_customer?>" 
                                       class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No se encontraron clientes activos</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación Activos -->
                <?php if ($total_paginas_activos > 1): ?>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p class="text-muted">
                            Mostrando página <?= $pagina_actual ?> de <?= $total_paginas_activos ?> 
                            (Total: <?= $total_activos ?> clientes)
                        </p>
                    </div>
                    <div class="col-md-6">
                        <nav>
                            <ul class="pagination justify-content-end">
                                <?php if ($pagina_actual > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion(1) ?>">
                                        <i class="fas fa-angle-double-left"></i> Primera
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion($pagina_actual - 1) ?>">
                                        <i class="fas fa-angle-left"></i> Anterior
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php
                                $rango = 2;
                                $inicio_pag = max(1, $pagina_actual - $rango);
                                $fin_pag = min($total_paginas_activos, $pagina_actual + $rango);
                                
                                for ($i = $inicio_pag; $i <= $fin_pag; $i++):
                                ?>
                                <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion($i) ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($pagina_actual < $total_paginas_activos): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion($pagina_actual + 1) ?>">
                                        Siguiente <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $this->construirUrlPaginacion($total_paginas_activos) ?>">
                                        Última <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Sección Inactivos -->
        <div class="card collapsed-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-archive"></i> Clientes Inactivos (<?= $total_inactivos ?>)
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Nombre del Cliente</th>
                            <th>Dirección</th>
                            <th>Tipo Doc.</th>
                            <th>Número Doc.</th>
                            <th>WhatsApp</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($clientes_inactivos)): ?>
                            <?php foreach($clientes_inactivos as $r): ?>
                            <tr>
                                <td><?=htmlspecialchars($r->name_customer ?? '')?></td>
                                <td><?=htmlspecialchars($r->address_customer ?? '')?></td>
                                <td><?=htmlspecialchars($r->name_type_doc ?? 'Sin tipo')?></td>
                                <td><?=htmlspecialchars($r->doc_num ?? '')?></td>
                                <td><?=htmlspecialchars($r->whatsapp ?? '')?></td>
                                <td><?=htmlspecialchars($r->tel ?? '')?></td>
                                <td><?=htmlspecialchars($r->mail ?? '')?></td>
                                <td><span class="badge badge-danger">Inactivo</span></td>
                                <td class="text-center">
                                    <a href="?c=customer&a=FormEditar&id=<?=$r->id_customer?>" 
                                       class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No se encontraron clientes inactivos</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>