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
                <?php $colocadosPrefix = ''; require __DIR__ . '/contenido_colocados_financiero.php'; ?>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>
