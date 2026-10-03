<section id="ventas-financiero-eficiencia" class="mb-4" aria-labelledby="ventas-eficiencia-titulo">
    <div class="ventas-encabezado-cuantitativo mb-4">
        <h3 id="ventas-eficiencia-titulo" class="mt-0 mb-1 fw-bold"><i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>Análisis financiero · Colocación e importes</h3>
        <p class="mb-0">Comparativo de cantidades de proyectos e importes del período seleccionado.</p>
    </div>
    <div class="ventas-eficiencia-contenido">
        <p class="mb-1"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Período: <strong><?= $esc($monthNames); ?> <?= $esc($yearNames); ?></strong></p>
        <p class="ventas-eficiencia-nota">Cantidades: mismos proyectos únicos de los KPI, incluidos los anteriores. Importes: documentos enviados del período, sin IVA, con los filtros y la conversión a USD del dashboard. Cada porcentaje usa su propia unidad; «—» indica un denominador cero.</p>
        <div id="ventas-eficiencia-general" class="ventas-eficiencia-seccion ventas-eficiencia-abrir" role="button" tabindex="0" aria-expanded="false" aria-controls="ventas-eficiencia-vendedores" aria-label="Mostrar desglose de cantidades e importes por vendedor">
            <h6><i class="fa-solid fa-chart-simple ventas-eficiencia-icono" aria-hidden="true"></i>Resumen general · Proyectos</h6>
            <div class="ventas-eficiencia-indicadores">
                <?php foreach (['cotizado'=>'Total Cotizado','colocado'=>'Total Colocado','colocacion'=>'% Colocación'] as $key=>$label): ?>
                    <div class="ventas-eficiencia-metrica ventas-eficiencia-<?= $key; ?>"><span><?= $esc($label); ?></span><strong id="ventas-eficiencia-<?= $key; ?>">—</strong></div>
                <?php endforeach; ?>
            </div>
            <div class="ventas-eficiencia-indicadores mt-3">
                <?php foreach (['importe-cotizado'=>'Importe Cotizado (USD)','importe-colocado'=>'Importe Colocado (USD)','colocacion-monetaria'=>'% Colocación Monetaria'] as $key=>$label): ?>
                    <div class="ventas-eficiencia-metrica ventas-eficiencia-<?= $key; ?>"><span><?= $esc($label); ?></span><strong id="ventas-eficiencia-<?= $key; ?>">—</strong></div>
                <?php endforeach; ?>
            </div>
            <div class="ventas-eficiencia-graficas mt-3">
                <section aria-labelledby="ventas-eficiencia-cantidad-titulo"><h6 id="ventas-eficiencia-cantidad-titulo">Cantidad · Proyectos</h6><div id="ventas-eficiencia-grafica" class="ventas-eficiencia-chart" role="img" aria-label="Cantidad de proyectos cotizados y colocados"></div></section>
                <section aria-labelledby="ventas-eficiencia-importe-titulo"><h6 id="ventas-eficiencia-importe-titulo">Importe · USD</h6><div id="ventas-eficiencia-grafica-importes" class="ventas-eficiencia-chart" role="img" aria-label="Importes cotizados y colocados en USD, escala independiente"></div></section>
            </div>
            <p class="ventas-eficiencia-nota mb-0">Selecciona el resumen o una barra para ver el desglose por vendedor.</p>
        </div>
        <section id="ventas-eficiencia-vendedores" class="ventas-eficiencia-seccion" hidden aria-labelledby="ventas-eficiencia-vendedores-titulo">
            <h6 id="ventas-eficiencia-vendedores-titulo"><i class="fa-solid fa-user-tie ventas-eficiencia-icono" aria-hidden="true"></i>Desglose por vendedor</h6>
            <p class="ventas-eficiencia-nota">Ordenado de mayor a menor por proyectos colocados. % Colocación = Total Colocado / Total Cotizado × 100. % Colocación Monetaria = Importe Colocado / Importe Cotizado × 100. Importes en USD, sin IVA.</p>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th scope="col">Vendedor</th><th scope="col" class="text-end">Total Cotizado</th><th scope="col" class="text-end">Total Colocado</th><th scope="col" class="text-end">% Colocación</th><th scope="col" class="text-end">Importe Cotizado (USD)</th><th scope="col" class="text-end">Importe Colocado (USD)</th><th scope="col" class="text-end">% Colocación Monetaria</th></tr></thead>
                    <tbody id="ventas-eficiencia-vendedores-filas"></tbody>
                </table>
            </div>
        </section>
    </div>
</section>
