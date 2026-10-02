/* Modal compartido por Dashboard Ventas y Reporte Ventas. */
(function () {
    let request;
    window.verSeguimientosProyecto = async function (ventaId, proyectoId) {
        const modal = document.getElementById('modalSeguimientosVenta');
        if (!modal) return;
        const count = document.getElementById('lbl_modal_seguimiento_count');
        const body = document.getElementById('tbl_seguimiento_venta_body');
        const table = jQuery('#table_seguimiento_venta');
        const escape = jQuery.fn.dataTable.render.text().display;
        const text = value => escape(String(value == null ? '' : value));
        const destroy = () => {
            if (jQuery.fn.DataTable.isDataTable('#table_seguimiento_venta')) table.DataTable().destroy();
        };
        if (request) request.abort();
        const current = new AbortController();
        request = current;
        document.getElementById('lbl_modal_seguimiento_proyecto').textContent = proyectoId || 'N/A';
        document.getElementById('lbl_modal_seguimiento_venta_id').textContent = ventaId;
        count.textContent = 'Cargando…';
        destroy();
        body.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm text-danger me-2" role="status"></span>Cargando seguimientos…</td></tr>';
        bootstrap.Modal.getOrCreateInstance(modal).show();
        const form = new FormData();
        form.append('venta_id', ventaId);
        try {
            const response = await fetch(modal.dataset.url, { method: 'POST', body: form,
                credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: current.signal });
            const payload = await response.json();
            if (!response.ok || payload.respuesta !== 'ok' || !Array.isArray(payload.data)) {
                throw new Error('No se pudieron cargar los seguimientos. Cierre el modal e intente nuevamente.');
            }
            if (request !== current) return;
            const list = payload.data;
            count.textContent = list.length + ' Seguimiento' + (list.length === 1 ? '' : 's');
            if (!list.length) {
                body.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No hay seguimientos registrados para este proyecto.</td></tr>';
                return;
            }
            body.innerHTML = list.map(s => {
                const id = s.id || s.ID || 'N/A';
                const fecha = s.fecha_formateada || s.fecha || s.fecha_registro || s.fchregistro || s.created_at || 'N/A';
                const usuario = (s.nombre_usuario || '').trim() || s.usuario_nombre || s.usuario || s.vendedor || s.ccveusuario || 'N/A';
                const detalle = s.seguimiento || s.comentario || s.observaciones || s.observacion || s.nota || s.descripcion || s.mensaje || 'Sin observaciones';
                return '<tr><td class="text-center fw-bold text-dark text-3">' + text(id) + '</td>' +
                    '<td class="text-center text-muted text-3">' + text(fecha) + '</td>' +
                    '<td class="text-dark text-3">' + text(usuario) + '</td>' +
                    '<td class="text-3 text-wrap" style="white-space:pre-wrap">' + text(detalle) + '</td></tr>';
            }).join('');
            table.DataTable({ scrollX: '100%', order: [[0, 'desc']], iDisplayLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Todos']],
                language: typeof idioma_espanol !== 'undefined' ? idioma_espanol : {
                    search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ registros', info: '_START_ a _END_ de _TOTAL_ seguimientos',
                    infoEmpty: 'Sin seguimientos', zeroRecords: 'No hay coincidencias', infoFiltered: '(de _MAX_ seguimientos)',
                    paginate: { previous: 'Anterior', next: 'Siguiente' }
                }
            });
            table.DataTable().columns.adjust();
        } catch (error) {
            if (error.name === 'AbortError' || request !== current) return;
            count.textContent = '— Seguimientos';
            body.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">No se pudieron cargar los seguimientos. Cierre el modal e intente nuevamente.</td></tr>';
        } finally { if (request === current) request = null; }
    };
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('modalSeguimientosVenta');
        if (modal) modal.addEventListener('shown.bs.modal', () => {
            if (jQuery.fn.DataTable.isDataTable('#table_seguimiento_venta')) {
                jQuery('#table_seguimiento_venta').DataTable().columns.adjust();
            }
        });
        if (modal) modal.addEventListener('hidden.bs.modal', () => {
            if (request) request.abort();
            request = null;
        });
    });
}());
