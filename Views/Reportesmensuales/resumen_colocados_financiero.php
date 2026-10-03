<div id="resumen-colocados-financiero" class="mb-4"
    data-url="<?= base_url(); ?>/reportesmensuales/colocadosfinanciero" data-anio="<?= $esc(implode(',', $selectedYears)); ?>"
    data-mes="<?= $esc(implode(',', $selectedMonths)); ?>" data-vendedor="<?= $esc($filters['vendedor']); ?>">
    <div class="colocados-panel-contenido">
        <?php $colocadosPrefix = 'panel-'; require __DIR__ . '/contenido_colocados_financiero.php'; ?>
    </div>
</div>
