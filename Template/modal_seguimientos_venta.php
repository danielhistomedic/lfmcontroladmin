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
                <label class="seguimientos-buscar" for="seguimientos-buscar">Buscar
                    <input type="search" id="seguimientos-buscar" class="form-control form-control-sm" placeholder="ID, fecha, usuario o seguimiento" disabled>
                </label>
                <div class="table-responsive">
                    <table class="seguimientos-tabla w-100" id="table_seguimiento_venta">
                        <caption class="visually-hidden">Historial de seguimiento del proyecto seleccionado</caption>
                        <thead>
                            <tr>
                                <?php foreach (['id'=>'ID','fecha'=>'Fecha','usuario'=>'Usuario','detalle'=>'Seguimiento / Observación'] as $key=>$label): ?>
                                    <th scope="col" aria-sort="<?= $key === 'id' ? 'descending' : 'none'; ?>"><button type="button" data-seguimiento-order="<?= $key; ?>" data-label="<?= $label; ?>" disabled><?= $label; ?><?= $key === 'id' ? ' ↓' : ''; ?></button></th>
                                <?php endforeach; ?>
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
                <div class="seguimientos-pie">
                    <span id="seguimientos-rango" aria-live="polite">0 registros</span>
                    <nav aria-label="Páginas del historial de seguimiento">
                        <button type="button" id="seguimientos-anterior" class="btn btn-sm" disabled>Anterior</button>
                        <span id="seguimientos-pagina">Página 1 de 1</span>
                        <button type="button" id="seguimientos-siguiente" class="btn btn-sm" disabled>Siguiente</button>
                    </nav>
                </div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<style>
    #modalSeguimientosVenta .seguimientos-buscar { display:block; margin-bottom:12px; color:#62758e; font-size:11px; }
    #modalSeguimientosVenta .seguimientos-buscar input { margin-top:4px; border-color:#dfe8f2; }
    #modalSeguimientosVenta .seguimientos-tabla { border-collapse:collapse; table-layout:fixed; color:#243b53; font-size:12px; }
    #modalSeguimientosVenta .seguimientos-tabla th { padding:10px 8px; background:#e8f4f7; color:#087e98; text-align:left; }
    #modalSeguimientosVenta .seguimientos-tabla th:nth-child(1) { width:9%; }
    #modalSeguimientosVenta .seguimientos-tabla th:nth-child(2) { width:23%; }
    #modalSeguimientosVenta .seguimientos-tabla th:nth-child(3) { width:25%; }
    #modalSeguimientosVenta .seguimientos-tabla th:nth-child(4) { width:43%; }
    #modalSeguimientosVenta .seguimientos-tabla th button { border:0; padding:0; background:transparent; color:inherit; font:inherit; font-weight:600; text-align:inherit; cursor:pointer; }
    #modalSeguimientosVenta .seguimientos-tabla th button:focus-visible { outline:2px solid #2385bd; outline-offset:3px; }
    #modalSeguimientosVenta .seguimientos-tabla td { padding:10px 8px; vertical-align:top; border-bottom:1px solid #dfe8f2; overflow-wrap:anywhere; }
    #modalSeguimientosVenta .seguimientos-tabla td:last-child { white-space:pre-wrap; }
    #modalSeguimientosVenta .seguimientos-tabla tbody tr:hover { background:#f4f8fc; }
    #modalSeguimientosVenta .seguimientos-pie { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-top:12px; color:#62758e; font-size:11px; }
    #modalSeguimientosVenta .seguimientos-pie nav { display:flex; align-items:center; gap:6px; }
    #modalSeguimientosVenta .seguimientos-pie .btn { border:1px solid #dfe8f2; border-radius:5px; padding:6px 10px; color:#243b53; font-size:11px; }
    #modalSeguimientosVenta .seguimientos-pie .btn:hover { background:#f4f8fc; }
    html.dark #modalSeguimientosVenta .seguimientos-tabla { color:#edf2f7; }
    html.dark #modalSeguimientosVenta .seguimientos-tabla th { background:#2a3b4e; color:#edf2f7; }
    html.dark #modalSeguimientosVenta .seguimientos-tabla td { border-color:#455563; }
    html.dark #modalSeguimientosVenta .seguimientos-tabla tbody tr:hover { background:#2a3b4e; }
    html.dark #modalSeguimientosVenta :is(.seguimientos-buscar,.seguimientos-pie) { color:#cbd5e1; }
    html.dark #modalSeguimientosVenta .seguimientos-pie .btn { color:#edf2f7; border-color:#455563; }
    html.dark #modalSeguimientosVenta .seguimientos-pie .btn:hover { background:#2a3b4e; }
    @media(max-width:575px) {
        #modalSeguimientosVenta .seguimientos-tabla { font-size:11px; }
        #modalSeguimientosVenta .seguimientos-tabla :is(th,td) { padding:8px 4px; }
    }
</style>
