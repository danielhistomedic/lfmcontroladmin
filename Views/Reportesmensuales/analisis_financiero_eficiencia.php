<section id="ventas-financiero-eficiencia" class="mb-4" aria-labelledby="ventas-eficiencia-titulo">
    <div class="ventas-encabezado-cuantitativo mb-4">
        <h3 id="ventas-eficiencia-titulo" class="mt-0 mb-1 fw-bold"><i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>Análisis financiero · Colocación y eficiencia</h3>
        <p class="mb-0">Comparativo de cantidades de proyectos del período seleccionado.</p>
    </div>
    <div class="ventas-eficiencia-contenido">
        <p class="mb-1"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Período: <strong><?= $esc($monthNames); ?> <?= $esc($yearNames); ?></strong></p>
        <p class="ventas-eficiencia-nota">Proyectado: proyectos registrados en el período. Cotizado y colocado: mismos proyectos únicos de los KPI, incluidos los anteriores. Los porcentajes usan cantidades de proyectos; «—» indica un denominador cero. Al incluir proyectos anteriores, la eficiencia puede superar el 100 %.</p>
        <div id="ventas-eficiencia-general" class="ventas-eficiencia-seccion ventas-eficiencia-abrir" role="button" tabindex="0" aria-expanded="false" aria-controls="ventas-eficiencia-vendedores" aria-label="Mostrar desglose de colocación y eficiencia por vendedor">
            <h6><i class="fa-solid fa-chart-simple ventas-eficiencia-icono" aria-hidden="true"></i>Resumen general · Proyectos</h6>
            <div class="ventas-eficiencia-indicadores">
                <?php foreach (['proyectado'=>'Total Proyectado','cotizado'=>'Total Cotizado','colocado'=>'Total Colocado','colocacion'=>'% Colocación','eficiencia'=>'% Eficiencia'] as $key=>$label): ?>
                    <div class="ventas-eficiencia-metrica ventas-eficiencia-<?= $key; ?>"><span><?= $esc($label); ?></span><strong id="ventas-eficiencia-<?= $key; ?>">—</strong></div>
                <?php endforeach; ?>
            </div>
            <div id="ventas-eficiencia-grafica" role="img" aria-label="Comparativo de proyectos proyectados, cotizados y colocados"></div>
            <p class="ventas-eficiencia-nota mb-0">Selecciona el resumen o una barra para ver el desglose por vendedor.</p>
        </div>
        <section id="ventas-eficiencia-vendedores" class="ventas-eficiencia-seccion" hidden aria-labelledby="ventas-eficiencia-vendedores-titulo">
            <h6 id="ventas-eficiencia-vendedores-titulo"><i class="fa-solid fa-user-tie ventas-eficiencia-icono" aria-hidden="true"></i>Desglose por vendedor</h6>
            <p class="ventas-eficiencia-nota">Ordenado de mayor a menor por proyectos colocados. % Colocación = Colocado / Cotizado × 100. % Eficiencia = Colocado / Proyectado × 100.</p>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th scope="col">Vendedor</th><th scope="col" class="text-end">Proyectado</th><th scope="col" class="text-end">Cotizado</th><th scope="col" class="text-end">Colocado</th><th scope="col" class="text-end">% Colocación</th><th scope="col" class="text-end">% Eficiencia</th></tr></thead>
                    <tbody id="ventas-eficiencia-vendedores-filas"></tbody>
                </table>
            </div>
        </section>
    </div>
</section>
