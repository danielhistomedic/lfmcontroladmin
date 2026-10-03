<p class="mb-1"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Período: <strong><?= $esc($monthNames); ?> <?= $esc($yearNames); ?></strong></p>
<p class="colocados-nota">Mismo conjunto de proyectos que el KPI: proyectos registrados en el período con pedido enviado y proyectos anteriores con pedido enviado dentro del período. Subtotales de partidas: cantidad pedida × precio unitario, sin IVA ni conversión de moneda. Se excluyen proyectos cerrados y de estatus cancelado. Un proyecto puede tener varios pedidos o monedas.</p>
<div id="<?= $colocadosPrefix; ?>colocados-financiero-estado" role="status" aria-live="polite"></div>
<button id="<?= $colocadosPrefix; ?>colocados-financiero-reintentar" type="button" class="btn btn-outline-primary btn-sm mb-3" hidden>Reintentar</button>
<div id="<?= $colocadosPrefix; ?>colocados-financiero-resumen" hidden>
    <section class="colocados-resumen-seccion colocados-moneda-destacado" aria-labelledby="<?= $colocadosPrefix; ?>colocados-totales-titulo">
        <h6 id="<?= $colocadosPrefix; ?>colocados-totales-titulo"><i class="fa-solid fa-coins colocados-icono" aria-hidden="true"></i>Total colocado por moneda</h6>
        <p id="<?= $colocadosPrefix; ?>colocados-proyectos-conteo" class="colocados-nota" aria-live="polite"></p>
        <div id="<?= $colocadosPrefix; ?>colocados-totales" class="colocados-monedas"></div>
    </section>
    <div class="colocados-grupos">
        <section class="colocados-resumen-seccion colocados-flowserve-destacado" aria-labelledby="<?= $colocadosPrefix; ?>colocados-flowserve-titulo">
            <h6 id="<?= $colocadosPrefix; ?>colocados-flowserve-titulo"><i class="fa-solid fa-industry colocados-icono" aria-hidden="true"></i>Total Flowserve</h6>
            <div id="<?= $colocadosPrefix; ?>colocados-flowserve" class="colocados-monedas"></div>
        </section>
        <section class="colocados-resumen-seccion colocados-diversos-destacado" aria-labelledby="<?= $colocadosPrefix; ?>colocados-diversos-titulo">
            <h6 id="<?= $colocadosPrefix; ?>colocados-diversos-titulo"><i class="fa-solid fa-boxes-stacked colocados-icono" aria-hidden="true"></i>Total Diversos</h6>
            <div id="<?= $colocadosPrefix; ?>colocados-diversos" class="colocados-monedas"></div>
        </section>
    </div>
</div>
<div class="colocados-grupos colocados-listados">
    <?php foreach (['clientes' => 'Total por cliente', 'vendedores' => 'Total por vendedor'] as $section => $title): ?>
        <section class="colocados-resumen-seccion colocados-entidad-destacada colocados-<?= $section; ?>-destacado" aria-labelledby="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-titulo">
            <h6 id="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-titulo"><i class="fa-solid <?= $section === 'clientes' ? 'fa-building' : 'fa-user-tie'; ?> colocados-icono" aria-hidden="true"></i><?= $title; ?></h6>
            <div class="colocados-tabla-controles">
                <label>Buscar<input id="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-buscar" type="search" maxlength="200" placeholder="Nombre o moneda" class="form-control form-control-sm"></label>
                <label>Ordenar<select id="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-orden" class="form-select form-select-sm">
                        <option value="2:desc">Mayor importe</option>
                        <option value="1:asc">Moneda</option>
                        <option value="2:asc">Menor importe</option>
                        <option value="0:asc">Nombre A–Z</option>
                    </select></label>
            </div>
            <div id="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-estado" class="text-danger" role="status" aria-live="polite"></div>
            <div class="table-responsive">
                <table id="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-tabla" class="table colocados-tabla w-100">
                    <thead>
                        <tr>
                            <th><?= $section === 'clientes' ? 'Cliente' : 'Vendedor'; ?></th>
                            <th>Moneda</th>
                            <th class="text-end">Total colocado</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="colocados-tabla-pie">
                <span id="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-pagina" aria-live="polite"></span>
                <nav aria-label="Paginación de <?= $section; ?>"><button id="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-anterior" type="button" class="btn btn-light btn-sm" disabled>Anterior</button><button id="<?= $colocadosPrefix; ?>colocados-<?= $section; ?>-siguiente" type="button" class="btn btn-light btn-sm" disabled>Siguiente</button></nav>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<section class="colocados-resumen-seccion colocados-mensual-destacado" aria-labelledby="<?= $colocadosPrefix; ?>colocados-mensual-titulo">
    <h6 id="<?= $colocadosPrefix; ?>colocados-mensual-titulo"><i class="fa-solid fa-calendar colocados-icono" aria-hidden="true"></i>Pedidos por mes · Productos y servicios</h6>
    <p class="colocados-nota">Los proyectos del período se muestran en su mes de registro; los anteriores, en el mes del pedido. Los conteos por moneda pueden compartir proyectos.</p>
    <div class="table-responsive">
        <table class="table colocados-tabla">
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Mes</th>
                    <th>Moneda</th>
                    <th class="text-end">Proyectos colocados</th>
                    <th class="text-end">Pedidos enviados</th>
                    <th class="text-end">Subtotal general</th>
                    <th class="text-end">Productos</th>
                    <th class="text-end">Servicios</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody id="<?= $colocadosPrefix; ?>colocados-mensual-filas"></tbody>
        </table>
    </div>
</section>
<details id="<?= $colocadosPrefix; ?>colocados-partidas" class="colocados-resumen-seccion colocados-partidas-destacado" hidden open aria-labelledby="<?= $colocadosPrefix; ?>colocados-partidas-titulo">
    <summary>
        <h6 id="<?= $colocadosPrefix; ?>colocados-partidas-titulo">Detalle de partidas del mes</h6>
    </summary>
    <div class="colocados-tabla-controles"><label for="<?= $colocadosPrefix; ?>colocados-partidas-buscar">Buscar<input id="<?= $colocadosPrefix; ?>colocados-partidas-buscar" type="search" maxlength="200" placeholder="Buscar en las partidas" class="form-control form-control-sm"></label></div>
    <div id="<?= $colocadosPrefix; ?>colocados-partidas-estado" role="status" aria-live="polite"></div>
    <div class="table-responsive">
        <table class="table colocados-tabla colocados-partidas-tabla">
            <thead>
                <tr>
                    <?php foreach (['Proyecto', 'Orden de compra', 'Fecha', 'Moneda', 'Tipo', 'Clave', 'CCN', 'Código Cliente', 'Descripción', 'Cantidad', 'Precio unitario', 'Subtotal sin IVA'] as $index => $label): ?>
                        <th id="<?= $colocadosPrefix; ?>colocados-partidas-columna-<?= $index; ?>" scope="col" aria-sort="none" class="<?= $index >= 9 ? 'text-end' : ''; ?>"><button id="<?= $colocadosPrefix; ?>colocados-partidas-orden-<?= $index; ?>" type="button" class="colocados-orden-encabezado" aria-label="Ordenar por <?= $esc($label); ?>"><?= $esc($label); ?></button></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody id="<?= $colocadosPrefix; ?>colocados-partidas-filas"></tbody>
        </table>
    </div>
    <div class="colocados-tabla-pie"><span id="<?= $colocadosPrefix; ?>colocados-partidas-pagina" aria-live="polite"></span>
        <nav aria-label="Paginación de partidas"><button id="<?= $colocadosPrefix; ?>colocados-partidas-anterior" class="btn btn-light btn-sm" type="button" disabled>Anterior</button><button id="<?= $colocadosPrefix; ?>colocados-partidas-siguiente" class="btn btn-light btn-sm" type="button" disabled>Siguiente</button><button id="<?= $colocadosPrefix; ?>colocados-partidas-reintentar" class="btn btn-light btn-sm" type="button" hidden>Reintentar</button></nav>
    </div>
</details>