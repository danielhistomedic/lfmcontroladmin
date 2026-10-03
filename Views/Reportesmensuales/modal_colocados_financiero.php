<div class="modal fade" id="modal-colocados-financiero" tabindex="-1" aria-labelledby="colocados-financiero-titulo" aria-hidden="true"
    data-url="<?= base_url(); ?>/reportesmensuales/colocadosfinanciero" data-anio="<?= $esc(implode(',', $selectedYears)); ?>"
    data-mes="<?= $esc(implode(',', $selectedMonths)); ?>" data-vendedor="<?= $esc($filters['vendedor']); ?>">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="colocados-financiero-titulo" class="modal-title"><i class="fa-solid fa-coins me-2" aria-hidden="true"></i>Pedidos colocados · Resumen financiero</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Período: <strong><?= $esc($monthNames); ?> <?= $esc($yearNames); ?></strong></p>
                <p class="colocados-nota">Pedidos colocados por fecha del pedido, con el filtro de vendedor aplicado. Subtotales de partidas: cantidad pedida × precio unitario, sin IVA ni conversión de moneda. Se excluyen proyectos declinados.</p>
                <div id="colocados-financiero-estado" role="status" aria-live="polite"></div>
                <button id="colocados-financiero-reintentar" type="button" class="btn btn-outline-primary btn-sm mb-3" hidden>Reintentar</button>
                <div id="colocados-financiero-resumen" hidden>
                    <section class="colocados-resumen-seccion" aria-labelledby="colocados-totales-titulo">
                        <h6 id="colocados-totales-titulo"><i class="fa-solid fa-coins colocados-icono" aria-hidden="true"></i>Total colocado por moneda</h6>
                        <div id="colocados-totales" class="colocados-monedas"></div>
                    </section>
                    <div class="colocados-grupos">
                        <section class="colocados-resumen-seccion colocados-flowserve-destacado" aria-labelledby="colocados-flowserve-titulo">
                            <h6 id="colocados-flowserve-titulo"><i class="fa-solid fa-industry colocados-icono" aria-hidden="true"></i>Total Flowserve</h6>
                            <div id="colocados-flowserve" class="colocados-monedas"></div>
                        </section>
                        <section class="colocados-resumen-seccion" aria-labelledby="colocados-diversos-titulo">
                            <h6 id="colocados-diversos-titulo"><i class="fa-solid fa-boxes-stacked colocados-icono" aria-hidden="true"></i>Total Diversos</h6>
                            <div id="colocados-diversos" class="colocados-monedas"></div>
                        </section>
                    </div>
                </div>
                <section class="colocados-resumen-seccion" aria-labelledby="colocados-mensual-titulo">
                    <h6 id="colocados-mensual-titulo"><i class="fa-solid fa-calendar colocados-icono" aria-hidden="true"></i>Pedidos por mes · Productos y servicios</h6>
                    <div class="table-responsive">
                        <table class="table colocados-tabla">
                            <thead>
                                <tr>
                                    <th>Año</th>
                                    <th>Mes</th>
                                    <th>Moneda</th>
                                    <th class="text-end">Pedidos colocados</th>
                                    <th class="text-end">Subtotal general</th>
                                    <th class="text-end">Productos</th>
                                    <th class="text-end">Servicios</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody id="colocados-mensual-filas"></tbody>
                        </table>
                    </div>
                </section>
                <details id="colocados-partidas" class="colocados-resumen-seccion" hidden open aria-labelledby="colocados-partidas-titulo">
                    <summary><h6 id="colocados-partidas-titulo">Detalle de partidas del mes</h6></summary>
                    <div class="colocados-tabla-controles"><label for="colocados-partidas-buscar">Buscar<input id="colocados-partidas-buscar" type="search" maxlength="200" placeholder="Buscar en las partidas" class="form-control form-control-sm"></label></div>
                    <div id="colocados-partidas-estado" role="status" aria-live="polite"></div>
                    <div class="table-responsive">
                        <table class="table colocados-tabla colocados-partidas-tabla">
                            <thead>
                                <tr>
                                    <?php foreach (['Proyecto','Orden de compra','Fecha','Moneda','Tipo','Clave','CCN','Código Cliente','Descripción','Cantidad','Precio unitario','Subtotal sin IVA'] as $index => $label): ?>
                                        <th id="colocados-partidas-columna-<?= $index; ?>" scope="col" aria-sort="none" class="<?= $index >= 9 ? 'text-end' : ''; ?>"><button id="colocados-partidas-orden-<?= $index; ?>" type="button" class="colocados-orden-encabezado" aria-label="Ordenar por <?= $esc($label); ?>"><?= $esc($label); ?></button></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody id="colocados-partidas-filas"></tbody>
                        </table>
                    </div>
                    <div class="colocados-tabla-pie"><span id="colocados-partidas-pagina" aria-live="polite"></span>
                        <nav aria-label="Paginación de partidas"><button id="colocados-partidas-anterior" class="btn btn-light btn-sm" type="button" disabled>Anterior</button><button id="colocados-partidas-siguiente" class="btn btn-light btn-sm" type="button" disabled>Siguiente</button><button id="colocados-partidas-reintentar" class="btn btn-light btn-sm" type="button" hidden>Reintentar</button></nav>
                    </div>
                </details>
                <div class="colocados-grupos colocados-listados">
                    <?php foreach (['clientes' => 'Total por cliente', 'vendedores' => 'Total por vendedor'] as $section => $title): ?>
                        <section class="colocados-resumen-seccion" aria-labelledby="colocados-<?= $section; ?>-titulo">
                            <h6 id="colocados-<?= $section; ?>-titulo"><i class="fa-solid <?= $section === 'clientes' ? 'fa-building' : 'fa-user-tie'; ?> colocados-icono" aria-hidden="true"></i><?= $title; ?></h6>
                            <div class="colocados-tabla-controles">
                                <label>Buscar<input id="colocados-<?= $section; ?>-buscar" type="search" maxlength="200" placeholder="Nombre o moneda" class="form-control form-control-sm"></label>
                                <label>Ordenar<select id="colocados-<?= $section; ?>-orden" class="form-select form-select-sm">
                                        <option value="2:desc">Mayor importe</option>
                                        <option value="1:asc">Moneda</option>
                                        <option value="2:asc">Menor importe</option>
                                        <option value="0:asc">Nombre A–Z</option>
                                    </select></label>
                            </div>
                            <div id="colocados-<?= $section; ?>-estado" class="text-danger" role="status" aria-live="polite"></div>
                            <div class="table-responsive">
                                <table id="colocados-<?= $section; ?>-tabla" class="table colocados-tabla w-100">
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
                                <span id="colocados-<?= $section; ?>-pagina" aria-live="polite"></span>
                                <nav aria-label="Paginación de <?= $section; ?>"><button id="colocados-<?= $section; ?>-anterior" type="button" class="btn btn-light btn-sm" disabled>Anterior</button><button id="colocados-<?= $section; ?>-siguiente" type="button" class="btn btn-light btn-sm" disabled>Siguiente</button></nav>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>
