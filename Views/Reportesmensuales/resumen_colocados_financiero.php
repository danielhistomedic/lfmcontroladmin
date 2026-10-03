<section id="resumen-colocados-financiero" class="colocados-panel mb-4" aria-labelledby="panel-colocados-financiero-titulo"
    data-url="<?= base_url(); ?>/reportesmensuales/colocadosfinanciero" data-anio="<?= $esc(implode(',', $selectedYears)); ?>"
    data-mes="<?= $esc(implode(',', $selectedMonths)); ?>" data-vendedor="<?= $esc($filters['vendedor']); ?>">
    <div class="colocados-panel-encabezado"><h4 id="panel-colocados-financiero-titulo"><i class="fa-solid fa-coins me-2" aria-hidden="true"></i>Pedidos colocados · Resumen financiero</h4></div>
    <div class="colocados-panel-contenido">
        <?php $colocadosPrefix = 'panel-'; require __DIR__ . '/contenido_colocados_financiero.php'; ?>
    </div>
</section>
