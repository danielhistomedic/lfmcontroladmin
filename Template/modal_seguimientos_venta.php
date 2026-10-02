<!-- Modal Seguimiento de Pedido / Proyecto -->
<div class="modal fade" id="modalSeguimientosVenta" data-url="<?= htmlspecialchars($seguimientosUrl, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" aria-labelledby="modalSeguimientosVentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3" style="border-radius: 6px 6px 0 0;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center m-0" id="modalSeguimientosVentaLabel">
                    <i class="fa-solid fa-list-check me-2"></i> Seguimientos del Proyecto: <span id="lbl_modal_seguimiento_proyecto" class="ms-1 font-monospace"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted text-3"><i class="fa-regular fa-hashtag me-1"></i> ID Venta: <strong id="lbl_modal_seguimiento_venta_id" class="text-dark">--</strong></span>
                    <span class="badge bg-danger text-white text-3 px-3 py-2" id="lbl_modal_seguimiento_count">0 Seguimientos</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100" id="table_seguimiento_venta">
                        <thead>
                            <tr>
                                <th class="border-bottom-0 fw-semibold text-center" width="5%">ID</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="15%">Fecha</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="25%">Usuario</th>
                                <th class="border-bottom-0 fw-semibold text-center">Seguimiento / Observación</th>
                            </tr>
                        </thead>
                        <tbody id="tbl_seguimiento_venta_body">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                                    Cargando seguimientos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
