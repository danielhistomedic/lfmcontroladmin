/**
 * ordenes_cliente.js
 * Módulo: Seguimiento de Órdenes de Compra Clientes
 * 
 * Controla:
 *  - DataTable de órdenes (tb_pedidos_cliente + tb_ventas + tb_ventas_seguimiento)
 *  - Panel de filtros
 *  - Panel de detalle / historial de seguimientos (timeline)
 */

'use strict';

/* =========================================================
 * VARIABLES GLOBALES
 * ========================================================= */
var tableOrdenes = null;
var ventaIdActual = '';

/* =========================================================
 * DOCUMENT READY
 * ========================================================= */
$(document).ready(function () {

    // Establece rango de fecha por defecto (año en curso)
    var hoy = new Date();
    var primerDia = hoy.getFullYear() + '-01-01';
    var hoyStr = hoy.toISOString().split('T')[0];
    $('#filtro_fecha_ini').val(primerDia);
    $('#filtro_fecha_fin').val(hoyStr);

    // Inicializa tabla
    fntInicializarTabla();

    // Carga tabla con filtros iniciales al arrancar
    fntCargarTabla();

    // Eventos
    $('#btnFiltrar').on('click', function () {
        fntCargarTabla();
    });

    $(document).on('click', '.btnReturnList, #btnRegresar', function () {
        fntMostrarLista();
    });

});

/* =========================================================
 * FUNCIONES PRINCIPALES
 * ========================================================= */

/**
 * Inicializa el DataTable de órdenes (sin datos aún).
 */
function fntInicializarTabla() {

    if (tableOrdenes !== null) {
        tableOrdenes.destroy();
        tableOrdenes = null;
        $('#tableOrdenes').empty();

        // Re-agrega el thead
        $('#tableOrdenes').append('<thead class="table-light"><tr>' +
            '<th class="border-bottom-0 fw-semibold text-center">Opciones</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Pedido #</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Fecha Pedido</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Cliente</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Proyecto / Título</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Vendedor</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Monto</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Estatus</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Seguimientos</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Último Seguimiento</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Fecha Últ. Seg.</th>' +
            '<th class="border-bottom-0 fw-semibold text-center">Usuario Últ. Seg.</th>' +
            '</tr></thead>');
    }

    tableOrdenes = $('#tableOrdenes').DataTable({
        data: [],
        columns: [
            { data: 'options', orderable: false, className: 'text-center', width: '60px' },
            { data: 'pedido_id', className: 'text-center' },
            { data: 'fecha_pedido_formateada', className: 'text-center' },
            { data: 'cliente', className: 'text-start' },
            { data: 'titulo_venta', className: 'text-start' },
            { data: 'vendedor', className: 'text-start' },
            { data: 'monto_formateado', className: 'text-end' },
            { data: 'estatus_badge', className: 'text-center', orderable: false },
            { data: 'seguimientos_badge', className: 'text-center', orderable: false },
            { data: 'ultimo_seguimiento_nota_corta', className: 'text-start' },
            { data: 'ultimo_seguimiento_fecha', className: 'text-center' },
            { data: 'ultimo_seguimiento_usuario', className: 'text-start' }
        ],
        language: {
            url: base_url + '/Assets/vendor/datatables/es_mx.json'
        },
        order: [[2, 'desc']],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        initComplete: function () {
            $('.loading-panel-showing').removeClass('loading-panel-showing');
        }
    });
}

/**
 * Carga (o recarga) la tabla con los filtros actuales.
 */
function fntCargarTabla() {

    var fecha_ini = $('#filtro_fecha_ini').val().trim();
    var fecha_fin = $('#filtro_fecha_fin').val().trim();
    var filtro_estatus = $('#filtro_estatus').val() || '';
    var filtro_cliente = '';

    // Limpia tabla
    if (tableOrdenes !== null) {
        tableOrdenes.clear().draw();
    }

    // Muestra loading
    $('.loading-panel-showing').addClass('loading-panel-showing');

    $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getOrdenesClienteDatatable',
        data: {
            fecha_ini: fecha_ini,
            fecha_fin: fecha_fin,
            filtro_estatus: filtro_estatus,
            filtro_cliente: filtro_cliente
        },
        dataType: 'json',
        success: function (data) {

            if (tableOrdenes !== null && Array.isArray(data)) {
                tableOrdenes.clear().rows.add(data).draw();
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar órdenes:', error);
            alertify.error('Error al cargar las órdenes de clientes.', 4);
        },
        complete: function () {
            $('.loading-panel-showing').removeClass('loading-panel-showing');
        }
    });
}

/* =========================================================
 * DETALLE Y SEGUIMIENTO
 * ========================================================= */

/**
 * Muestra el panel de detalle con historial de seguimientos.
 * Llamado desde el botón de opciones en cada fila de la tabla.
 * 
 * @param {HTMLElement} btn  Botón que disparó el evento.
 */
function fntVerDetalle(btn) {

    var ventaId = $(btn).data('venta-id');
    var pedidoId = $(btn).data('pedido-id');

    if (!ventaId) {
        alertify.error('No se pudo obtener el ID de la orden.', 3);
        return;
    }

    // Recupera datos de la fila del DataTable
    var rowData = tableOrdenes.row($(btn).closest('tr')).data();
    if (!rowData) {
        alertify.error('No se pudieron obtener los datos de la orden.', 3);
        return;
    }

    // Llena el panel de detalle con los datos generales
    $('#det_cliente').text(rowData.cliente || '—');
    $('#det_titulo').text(rowData.titulo_venta || '—');
    $('#det_vendedor').text(rowData.vendedor || '—');
    $('#det_estatus').html(rowData.estatus_badge || '—');
    $('#det_fecha_pedido').text(rowData.fecha_pedido_formateada || '—');
    $('#det_monto').text(rowData.monto_formateado || '—');
    $('#det_clasificacion').text(rowData.clasificacion_proyecto || '—');
    $('#det_clues').text(rowData.clues || '—');
    $('#badge_total_seg').text(rowData.total_seguimientos || 0);

    // Guarda venta_id actual
    ventaIdActual = ventaId;

    // Carga historial
    fntCargarHistorial(ventaId);

    // Muestra el panel de detalle
    fntMostrarDetalle();
}

/**
 * Carga el historial de seguimientos de la venta seleccionada.
 * 
 * @param {string} ventaId  ID de venta encriptado.
 */
function fntCargarHistorial(ventaId) {

    $('#timeline_seguimientos').empty();
    $('#sin_seguimientos').addClass('d-none');
    $('#loading_seguimientos').removeClass('d-none');

    $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getHistorialSeguimiento',
        data: { venta_id: ventaId },
        dataType: 'json',
        success: function (resp) {

            $('#loading_seguimientos').addClass('d-none');

            if (resp.respuesta !== 'ok' || !Array.isArray(resp.data) || resp.data.length === 0) {
                $('#sin_seguimientos').removeClass('d-none');
                return;
            }

            var html = '<div class="timeline-seguimiento">';

            resp.data.forEach(function (seg, index) {

                var colorClass = (index === 0) ? 'border-primary text-primary' : 'border-secondary text-secondary';
                var iconClass = (index === 0) ? 'fa-comment-check text-primary' : 'fa-comment text-secondary';

                html += '<div class="d-flex mb-3 seg-item" style="gap: 12px;">';

                // Icono lateral
                html += '<div class="d-flex flex-column align-items-center" style="min-width:32px;">';
                html += '<div class="rounded-circle d-flex align-items-center justify-content-center border ' + colorClass + '" style="width:32px;height:32px;">';
                html += '<i class="fa-regular ' + iconClass + ' fs-12"></i>';
                html += '</div>';
                if (index < resp.data.length - 1) {
                    html += '<div style="flex:1;width:2px;background:#dee2e6;margin:4px auto;"></div>';
                }
                html += '</div>';

                // Contenido
                html += '<div class="card border p-0 flex-grow-1 mb-0 shadow-sm">';
                html += '<div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background:#f8f9fa;">';
                html += '<span class="fw-semibold fs-12 text-primary">';
                html += '<i class="fa-regular fa-user-circle me-1"></i>';
                html += escapeHtml(seg.nombre_usuario || 'Sistema');
                html += '</span>';
                html += '<span class="fs-11 text-muted">';
                html += '<i class="fa-regular fa-calendar me-1"></i>';
                html += escapeHtml(seg.fecha_formateada || seg.fecha || '—');
                html += '</span>';
                html += '</div>';
                html += '<div class="card-body py-2 px-3">';
                html += '<p class="mb-0 fs-13 text-muted" style="white-space:pre-wrap;">';
                html += escapeHtml(seg.notas || 'Sin nota registrada.');
                html += '</p>';
                html += '</div>';
                html += '</div>';

                html += '</div>'; // .d-flex
            });

            html += '</div>'; // .timeline-seguimiento

            $('#timeline_seguimientos').html(html);
            $('#badge_total_seg').text(resp.data.length);
        },
        error: function (xhr, status, error) {
            $('#loading_seguimientos').addClass('d-none');
            $('#sin_seguimientos').removeClass('d-none');
            console.error('Error al cargar historial de seguimiento:', error);
        }
    });
}

/* =========================================================
 * NAVEGACIÓN ENTRE PANELES
 * ========================================================= */

/**
 * Muestra el panel de lista y oculta el detalle.
 */
function fntMostrarLista() {
    $('#panel_lista_registros').show();
    $('#panel_filtros').show();
    $('#panel_detalle').hide();
    ventaIdActual = '';
}

/**
 * Muestra el panel de detalle y oculta la lista.
 */
function fntMostrarDetalle() {
    $('#panel_lista_registros').hide();
    $('#panel_filtros').hide();
    $('#panel_detalle').show();
}

/* =========================================================
 * UTILIDADES
 * ========================================================= */

/**
 * Escapa caracteres HTML para evitar XSS.
 * 
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
