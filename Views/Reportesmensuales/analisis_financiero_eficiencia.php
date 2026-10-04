<section id="ventas-financiero-eficiencia" class="mb-4" aria-labelledby="ventas-eficiencia-titulo">
    <div class="ventas-encabezado-cuantitativo mb-4">
        <h3 id="ventas-eficiencia-titulo" class="mt-0 mb-1 fw-bold"><i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>Análisis financiero · Comparativo de Pedidos Cotizados vs Pedidos Colocados</h3>
        <p class="mb-0">Comparativo de cantidades de proyectos e importes del período seleccionado.</p>
    </div>
    <div class="ventas-eficiencia-contenido">
        <p class="mb-1"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Período: <strong><?= $esc($monthNames); ?> <?= $esc($yearNames); ?></strong></p>
        <p class="ventas-eficiencia-nota">Cantidades: mismos proyectos únicos de los KPI, incluidos los anteriores. Importes: documentos enviados del período, sin IVA, con los filtros y la conversión a USD del dashboard. Cada porcentaje usa su propia unidad; «—» indica un denominador cero.</p>
        <div id="ventas-eficiencia-general" class="ventas-eficiencia-seccion ventas-eficiencia-abrir" role="button" tabindex="0" aria-expanded="false" aria-controls="ventas-eficiencia-vendedores" aria-label="Mostrar desglose de cantidades e importes por vendedor">
            <h6><i class="fa-solid fa-chart-simple ventas-eficiencia-icono" aria-hidden="true"></i>Comparativo General · Pedidos Cotizados vs Pedidos Colocados</h6>
            <div id="ventas-eficiencia-filtro-clasificacion" class="ventas-eficiencia-filtro-clasificacion" role="group" aria-label="Clasificación local del Comparativo General"></div>
            <div class="ventas-eficiencia-indicadores">
                <?php foreach (['cotizado' => 'Total Cotizado', 'colocado' => 'Total Colocado', 'colocacion' => '% Colocación'] as $key => $label): ?>
                    <div class="ventas-eficiencia-metrica ventas-eficiencia-<?= $key; ?>"><span><?= $esc($label); ?></span><strong id="ventas-eficiencia-<?= $key; ?>">—</strong></div>
                <?php endforeach; ?>
            </div>
            <div class="ventas-eficiencia-indicadores mt-3">
                <?php foreach (['importe-cotizado' => 'Importe Cotizado (USD)', 'importe-colocado' => 'Importe Colocado (USD)', 'colocacion-monetaria' => '% Colocación Monetaria'] as $key => $label): ?>
                    <div class="ventas-eficiencia-metrica ventas-eficiencia-<?= $key; ?>">
                        <span><?= $esc($label); ?></span><strong id="ventas-eficiencia-<?= $key; ?>">—</strong>
                        <?php if ($key === 'importe-colocado'): ?>
                            <small id="ventas-eficiencia-moneda-original" class="ventas-eficiencia-desglose">USD: — | MXN: —</small>
                            <small id="ventas-eficiencia-tipo-cambio" class="ventas-eficiencia-desglose">TC: — | Fecha TC: —</small>
                        <?php elseif ($key === 'importe-cotizado'): ?>
                            <small id="ventas-eficiencia-cotizado-moneda-original" class="ventas-eficiencia-desglose">USD: — | MXN: —</small>
                            <small id="ventas-eficiencia-cotizado-tipo-cambio" class="ventas-eficiencia-desglose">TC: — | Fecha TC: —</small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="ventas-eficiencia-graficas mt-3">
                <section aria-labelledby="ventas-eficiencia-cantidad-titulo">
                    <h6 id="ventas-eficiencia-cantidad-titulo">Colocación por cantidad</h6>
                    <div id="ventas-eficiencia-grafica" class="ventas-eficiencia-chart" role="img" aria-label="Anillo de conversión por cantidad de proyectos"></div>
                </section>
                <section aria-labelledby="ventas-eficiencia-importe-titulo">
                    <h6 id="ventas-eficiencia-importe-titulo">Colocación monetaria · USD</h6>
                    <div id="ventas-eficiencia-grafica-importes" class="ventas-eficiencia-chart" role="img" aria-label="Anillo de conversión monetaria en USD"></div>
                </section>
            </div>
            <section class="ventas-eficiencia-seccion ventas-eficiencia-evolucion mt-3" aria-labelledby="ventas-eficiencia-evolucion-titulo">
                <h6 id="ventas-eficiencia-evolucion-titulo"><i class="fa-solid fa-chart-line ventas-eficiencia-icono" aria-hidden="true"></i>Evolución mensual · Cotizado vs Colocado</h6>
                <p class="ventas-eficiencia-nota">Período y vendedor seleccionados · Proyectos e importes en USD · Mismo tipo de cambio del resumen.</p>
                <div id="ventas-eficiencia-evolucion" role="img" aria-label="Evolución mensual de proyectos e importes cotizados y colocados"></div>
            </section>
            <p class="ventas-eficiencia-nota mb-0">Selecciona el resumen o un anillo para ver el desglose por vendedor.</p>
        </div>
        <section id="ventas-eficiencia-vendedores" class="ventas-eficiencia-seccion" hidden aria-labelledby="ventas-eficiencia-vendedores-titulo">
            <h6 id="ventas-eficiencia-vendedores-titulo"><i class="fa-solid fa-user-tie ventas-eficiencia-icono" aria-hidden="true"></i><span id="ventas-eficiencia-vendedores-etiqueta">Desglose por vendedor · TODOS</span></h6>
            <p class="ventas-eficiencia-nota">% Colocación = Total Colocado / Total Cotizado × 100. % Colocación Monetaria = Importe Colocado / Importe Cotizado × 100. Importes en USD, sin IVA.</p>
            <div class="ventas-eficiencia-tabla-controles">
                <label for="ventas-eficiencia-vendedores-buscar">Buscar<input id="ventas-eficiencia-vendedores-buscar" type="search" maxlength="200" placeholder="Nombre o valor" class="form-control form-control-sm"></label>
                <label for="ventas-eficiencia-vendedores-orden">Ordenar<select id="ventas-eficiencia-vendedores-orden" class="form-select form-select-sm">
                    <option value="colocado">Mayor colocación</option>
                    <option value="importe_colocado">Mayor importe colocado</option>
                    <option value="colocacion">Mayor % colocación</option>
                    <option value="colocacion_monetaria">Mayor % colocación monetaria</option>
                    <option value="nombre">Nombre</option>
                </select></label>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Vendedor</th>
                            <th scope="col" class="text-end">Total Cotizado</th>
                            <th scope="col" class="text-end">Total Colocado</th>
                            <th scope="col" class="text-end">% Colocación</th>
                            <th scope="col" class="text-end">Importe Cotizado (USD)</th>
                            <th scope="col" class="text-end">Importe Colocado (USD)</th>
                            <th scope="col" class="text-end">% Colocación Monetaria</th>
                        </tr>
                    </thead>
                    <tbody id="ventas-eficiencia-vendedores-filas"></tbody>
                </table>
            </div>
            <div class="ventas-eficiencia-tabla-pie">
                <span id="ventas-eficiencia-vendedores-pagina" aria-live="polite"></span>
                <nav aria-label="Paginación del desglose por vendedor"><button id="ventas-eficiencia-vendedores-anterior" type="button" class="btn btn-light btn-sm" disabled>Anterior</button><button id="ventas-eficiencia-vendedores-siguiente" type="button" class="btn btn-light btn-sm" disabled>Siguiente</button></nav>
            </div>
        </section>
    </div>
</section>
