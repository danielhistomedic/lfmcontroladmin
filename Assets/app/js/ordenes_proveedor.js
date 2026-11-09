/** DataTables: diseño aprobado compartido en datatable-layout.js; contrato propio conservado. */
/**
 * ordenes_proveedor.js
 * Módulo: Seguimiento de Órdenes de Compra a Proveedores
 * 
 * Controla:
 *  - DataTable de órdenes de compra a proveedor (tb_pedidos_proveedor + tb_ventas + tb_proveedores)
 *  - Panel de filtros
 *  - Panel de detalle (datos generales, partidas tb_pedidos_proveedor_detalle, adjuntos tb_pedidos_proveedor_adjuntos)
 *  - Historial de seguimientos / bitácora (timeline)
 *  - Calendario de entregas estimadas de proveedores con FullCalendar
 */

'use strict';

/* =========================================================
 * VARIABLES GLOBALES
 * ========================================================= */
var tableOrdenes = null;
var tableElement = "#tableOrdenes";
var tableElementJS = "tableOrdenes";
var pedidoIdActual = '';
var ventaIdActual = '';
var historialSeguimientoData = [];
var paginaActualSeguimiento = 1;
var registrosPorPaginaSeguimiento = 5;
var calendarEntregas = null;
var calendarEventosData = [];
var calendarFuenteEventos = null;
var calendarFiltroEstatus = 'todos';
var calendarIntervaloVisible = null;
var ordenesProveedorRequest = null;
var calendarioProveedorRequest = null;
var calendarioProveedorSecuencia = 0;
var detalleProveedorRequest = null;

/* =========================================================
 * DOCUMENT READY
 * ========================================================= */
$(document).ready(function () {
    $('body').addClass('seguimiento-ordenes-page');

    fntEstablecerPeriodoProveedor();

    // Inicializa tabla
    fntInicializarTabla();

    // Carga tabla con filtros iniciales al arrancar
    fntCargarTabla();

    // Inicializa y carga Calendario de Entregas
    fntInicializarCalendarioEntregas();

    // Eventos
    $('#btnRestablecerFiltros').on('click', function () {
        $('#filtro_num_orden').val('');
        fntEstablecerPeriodoProveedor();
        fntCargarTabla();
        fntCargarCalendarioEntregas();
    });

    $('#btnFiltrar').on('click', function () {
        fntCargarTabla();
        fntCargarCalendarioEntregas();
    });

    $('#filtro_num_orden').on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            fntCargarTabla();
            fntCargarCalendarioEntregas();
        }
    });

    $(document).on('click', '.btnReturnList, #btnRegresar', function () {
        fntMostrarLista();
    });

    // Filtros por estatus del calendario
    $(document).on('click', '.btn-filter-cal', function () {
        $('.btn-filter-cal').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        calendarFiltroEstatus = $(this).data('filter');
        fntAplicarFiltroCalendario();
    });

    // Paginación de timeline de seguimientos
    $(document).on('click', '.btn-pag-timeline', function (e) {
        e.preventDefault();
        var page = parseInt($(this).data('page'));
        if (page && page !== paginaActualSeguimiento) {
            fntRenderizarTimelinePagina(page);
        }
    });

});


/* =========================================================
 * FUNCIONES PRINCIPALES
 * ========================================================= */

/** Periodo inicial de la lista: un año atrás hasta hoy; el calendario consulta su vista visible. */
function fntEstablecerPeriodoProveedor() {
    var hoy = new Date();
    var anioAnterior = hoy.getFullYear() - 1;
    var ultimoDia = new Date(anioAnterior, hoy.getMonth() + 1, 0).getDate();
    var inicio = new Date(anioAnterior, hoy.getMonth(), Math.min(hoy.getDate(), ultimoDia));
    [['#filtro_fecha_ini', inicio], ['#filtro_fecha_fin', hoy]].forEach(function (item) {
        var control = $(item[0]), fecha = item[1];
        control.val(String(fecha.getDate()).padStart(2, '0') + '/' + String(fecha.getMonth() + 1).padStart(2, '0') + '/' + fecha.getFullYear());
        if ($.fn.datepicker && control.data('datepicker')) control.datepicker('setDate', fecha);
        if (control[0]) control[0].setCustomValidity('');
    });
}

function fntTextoCeldaProveedor(data, type) {
    return type === 'display' ? escapeHtml(data == null ? '' : String(data)) : (data == null ? '' : data);
}

/** Inicializa el DataTable de órdenes de compra a proveedor. */
function fntInicializarTabla() {

    if (!document.getElementById(tableElementJS)) return;

    // La cabecera se reconstruye sin copiar eventos; FixedHeader usa los mismos índices.
    $(tableElement).off('draw.dt.lfmProveedorLayout');
    if ($.fn.dataTable.isDataTable(tableElement)) $(tableElement).DataTable().destroy();
    var cabecera = $(tableElement + ' thead');
    cabecera.find('tr.lfm-proveedor-filtros').remove();
    cabecera.find('tr').first().clone(false).addClass('lfm-proveedor-filtros').appendTo(cabecera);
    var debounceColumna = {};
    cabecera.find('tr.lfm-proveedor-filtros th').each(function (i) {
        if (i === 0) { $(this).empty(); return; }
        var title = $(this).text().trim();
        $(this).empty().append(
            $('<input>', { type: 'text', class: 'form-control form-control-sm text-center col-filter-proveedor',
                'data-column-index': i, 'aria-label': 'Filtrar ' + title, placeholder: 'Filtrar ' + title })
        );
    });

    /*-------------------------------------------
    [ DataTable Inicializa ]*/
    tableOrdenes = LfmTables.create($(tableElement), {
        orderCellsTop: true,
        fixedHeader: true,
        scrollX: "100%",
        destroy: true,
        select: true,
        order: [
            [3, "desc"]
        ],
        iDisplayLength: 5,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Todos"]
        ],
        dom: 'Blfrtip',
        buttons: [{
                extend: 'excelHtml5',
                autoFilter: true,
                sheetName: 'Órdenes Proveedor',
                messageTop: "",
                title: 'Lista de Órdenes de Compra a Proveedores',
                exportOptions: {
                    columns: function (index) { return index !== 0 && tableOrdenes.column(index).visible(); },
                    orthogonal: 'export',
                    format: { body: function (data, row, column, node) { return node ? node.textContent.trim() : String(data == null ? '' : data).trim(); } }
                }
            },
            {
                extend: 'colvis',
                postfixButtons: ['colvisRestore']
            }
        ],
        columns: [
            { data: 'options', orderable: false, className: 'text-center align-middle', width: '60px' },
            {
                data: 'folio_ocp',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    var valorDisplay = data || ('Pedido #' + row.pedido_id);
                    return type === 'display' ? '<span class="fw-bold text-danger">' + escapeHtml(valorDisplay) + '</span>' : valorDisplay;
                }
            },
            {
                data: 'proyecto_id',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    return type === 'display' ? '<span class="fw-semibold text-dark">' + escapeHtml((data || '—')) + '</span>' : (data || '');
                }
            },
            {
                data: 'fecha_pedido_formateada',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') return row.fecha_pedido || '';
                    if (type !== 'display') return data || '';
                    return '<span class="text-muted">' + escapeHtml((data || '—')) + '</span>';
                }
            },
            {
                data: 'proveedor',
                className: 'text-start align-middle',
                render: function (data, type, row) {
                    return type === 'display' ? '<span class="fw-bold text-primary">' + escapeHtml((data || '—')) + '</span>' : (data || '');
                }
            },
            {
                data: 'cliente',
                className: 'text-start align-middle',
                render: function (data, type, row) {
                    return type === 'display' ? '<span class="fw-semibold text-dark">' + escapeHtml((data || '—')) + '</span>' : (data || '');
                }
            },
            { data: 'titulo_venta', className: 'text-start align-middle', render: fntTextoCeldaProveedor },
            {
                data: 'comprador',
                className: 'text-start align-middle',
                render: function (data, type, row) {
                    return type === 'display' ? '<span class="fw-semibold text-dark">' + escapeHtml((data || '—')) + '</span>' : (data || '');
                }
            },
            {
                data: 'monto_formateado',
                className: 'text-end align-middle',
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') return Number(row.monto_pedido) || 0;
                    return type === 'display' ? '<span class="fw-bold text-dark">' + escapeHtml((data || '$0.00')) + '</span>' : (data || '');
                }
            },
            {
                data: 'estatus_orden_badge',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    // La ordenación sigue el estado real; filtros y Excel reciben el texto, sin HTML.
                    if (type === 'sort' || type === 'type') return Number(row.enviado) || 0;
                    return type === 'display' ? data : (row.estatus_orden_txt || '');
                }
            },
            {
                data: 'estatus_badge',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    // El ID refleja la secuencia comercial de estatus del proyecto.
                    if (type === 'sort' || type === 'type') return Number(row.estatus_proyecto_id) || 0;
                    return type === 'display' ? data : (row.estatus_proyecto || '');
                }
            },
            { data: 'partidas_badge', className: 'text-center align-middle', orderable: false },
            { data: 'adjuntos_badge', className: 'text-center align-middle', orderable: false },
            { data: 'seguimientos_badge', className: 'text-center align-middle', orderable: false },
            { data: 'ultimo_seguimiento_nota_corta', className: 'text-start align-middle', render: fntTextoCeldaProveedor },
            { data: 'ultimo_seguimiento_fecha', className: 'text-center align-middle', render: fntTextoCeldaProveedor },
            { data: 'ultimo_seguimiento_usuario', className: 'text-start align-middle', render: fntTextoCeldaProveedor }
        ],
        language: idioma_espanol
    });

    // El helper vacía acciones al iniciar. FixedHeader clona este botón al fijar la cabecera.
    var botonLimpiar = '<button type="button" class="btn btn-outline-secondary lfm-btn-action lfm-btn-action--quiet lfm-btn-action--icon lfm-reset-proveedor" title="Limpiar filtros de columna" aria-label="Limpiar filtros de columna"><i class="fa-regular fa-filter-circle-xmark"></i></button>';
    function asegurarBotonLimpiar() {
        var cabeceras = $(tableElement).closest('.dataTables_wrapper').find('.dataTables_scrollHead table.ocp-proveedor-table');
        cabeceras = cabeceras.add($(tableElement)).add($('table.ocp-proveedor-table.fixedHeader-floating, table.ocp-proveedor-table.fixedHeader-locked'));
        cabeceras.find('thead tr.lfm-proveedor-filtros th:first').each(function () {
            if (!$(this).find('.lfm-reset-proveedor').length) $(this).html(botonLimpiar);
        });
    }
    asegurarBotonLimpiar();
    $(document).off('.lfmProveedorFiltros');
    $(tableElement).one('destroy.dt.lfmProveedorFiltros', function () {
        $(document).off('.lfmProveedorFiltros');
        Object.keys(debounceColumna).forEach(function (indice) { clearTimeout(debounceColumna[indice]); });
    });
    if (typeof menu !== 'undefined') validaPermisoExportar(menu);

    // Delegar permite usar los inputs originales y los clones floating/locked.
    var selectorFiltros = 'table.ocp-proveedor-table .lfm-proveedor-filtros .col-filter-proveedor';
    $(document).on('click.lfmProveedorFiltros keydown.lfmProveedorFiltros', selectorFiltros, function (e) { e.stopPropagation(); });
    $(document).on('input.lfmProveedorFiltros', selectorFiltros, function () {
        var indice = Number(this.getAttribute('data-column-index'));
        var valor = this.value;
        $(selectorFiltros + '[data-column-index="' + indice + '"]').not(this).val(valor);
        clearTimeout(debounceColumna[indice]);
        debounceColumna[indice] = setTimeout(function () {
            if (tableOrdenes && tableOrdenes.column(indice).search() !== valor) tableOrdenes.column(indice).search(valor).draw();
        }, 220);
    });
    $(document).on('click.lfmProveedorFiltros', 'table.ocp-proveedor-table .lfm-reset-proveedor', function (e) {
        e.preventDefault();
        e.stopPropagation();
        Object.keys(debounceColumna).forEach(function (indice) { clearTimeout(debounceColumna[indice]); });
        $(selectorFiltros).val('');
        tableOrdenes.columns().search('').draw();
    });

    /*-------------------------------------------
    [ DataTable - Se ejecuta después de redibujarse la tabla ]*/
    $(tableElement).on('draw.dt.lfmProveedorLayout', function () {
        asegurarBotonLimpiar();
        if (tableOrdenes) {
            tableOrdenes.columns.adjust();
        }
    });
}

/**
 * Carga (o recarga) la tabla con los filtros actuales.
 */
function fntCargarTabla() {

    var fecha_ini            = $('#filtro_fecha_ini').val().trim();
    var fecha_fin            = $('#filtro_fecha_fin').val().trim();
    var filtro_num_orden     = $('#filtro_num_orden').val() ? $('#filtro_num_orden').val().trim() : '';
    var filtro_estatus       = '';

    if (ordenesProveedorRequest) ordenesProveedorRequest.abort();

    // Muestra loading
    $('.loading-panel-showing').addClass('loading-panel-showing');

    ordenesProveedorRequest = $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getOrdenesProveedorDatatable',
        data: {
            fecha_ini: fecha_ini,
            fecha_fin: fecha_fin,
            filtro_num_orden: filtro_num_orden,
            filtro_estatus: filtro_estatus
        },
        dataType: 'json',
        success: function (data) {

            if (tableOrdenes !== null && Array.isArray(data)) {
                tableOrdenes.clear().rows.add(data).draw();
                fntCalcularKPIs(data);

                tableOrdenes.columns.adjust();
            } else {
                alertify.error((data && data.msg) || 'Respuesta inválida al cargar órdenes.', 4);
            }
        },
        error: function (xhr, status, error) {
            if (status === 'abort') return;
            console.error('Error al cargar órdenes de proveedor:', error);
            alertify.error('Error al cargar las órdenes de compra a proveedores.', 4);
        },
        complete: function () {
            ordenesProveedorRequest = null;
            $('.loading-panel-showing').removeClass('loading-panel-showing');
        }
    });
}

/**
 * Resume exclusivamente órdenes finalizadas (enviado = 1) del conjunto consultado.
 * Los conteos de partidas ya llegan en la respuesta de la lista; no se agregan consultas.
 * Una recepción parcial también está pendiente de recibir.
 * @param {Array<Object>} data Órdenes devueltas por el filtro externo.
 */
function fntCalcularKPIs(data) {
    var resumen = {activas: 0, porRecibir: 0, parciales: 0, completas: 0};
    data.forEach(function (row) {
        if (Number(row.enviado) !== 1) return;
        resumen.activas++;
        var total = Number(row.total_partidas) || 0;
        var pendientes = Number(row.total_partidas_pendientes) || 0;
        var recibidas = Number(row.total_partidas_entregadas) || 0;
        if (pendientes > 0) {
            resumen.porRecibir++;
            if (recibidas > 0) resumen.parciales++;
        }
        if (total > 0 && recibidas === total) resumen.completas++;
    });

    var formato = new Intl.NumberFormat('es-MX');
    $('#kpi_total_ordenes').text(formato.format(resumen.activas));
    $('#kpi_proveedor_alcance').text(formato.format(resumen.activas) + ' órdenes activas');
    $('#kpi_por_recibir').text(formato.format(resumen.porRecibir));
    $('#kpi_recepcion_parcial').text(formato.format(resumen.parciales));
    $('#kpi_recepcion_completa').text(formato.format(resumen.completas));
}

/* =========================================================
 * DETALLE Y SEGUIMIENTO
 * ========================================================= */

/**
 * Muestra el panel de detalle con datos generales, partidas, adjuntos e historial de seguimiento.
 * Llamado desde el botón de opciones en cada fila de la tabla.
 * 
 * @param {HTMLElement} btn Botón que disparó el evento.
 */
function fntVerDetalle(btn) {

    var pedidoIdEnc = $(btn).data('pedido-id');
    var ventaIdEnc = $(btn).data('venta-id');

    if (!pedidoIdEnc) {
        alertify.error('No se pudo obtener el ID de la orden de proveedor.', 3);
        return;
    }

    // Recupera datos de la fila del DataTable para llenar vista inicial rápida
    var rowData = tableOrdenes.row($(btn).closest('tr')).data();
    if (rowData) {
        $('#det_folio_ocp').text(rowData.folio_ocp || ('Pedido #' + rowData.pedido_id));
        $('#det_proveedor').text(rowData.proveedor || '—');
        $('#det_proveedor_rfc').text(rowData.proveedor_rfc || '—');
        $('#det_proveedor_contacto').text(rowData.proveedor_contacto || '—');
        $('#det_proyecto_id').text(rowData.proyecto_id || '—');
        $('#det_cliente').text(rowData.cliente || '—');
        $('#det_titulo').text(rowData.titulo_venta || '—');
        $('#det_comprador').text(rowData.comprador || '—');
        $('#det_estatus_orden').html(rowData.estatus_orden_badge || '—').addClass('text-wrap');
        $('#det_estatus_orden').find('span, .badge').addClass('text-wrap');
        $('#det_estatus_proyecto').html(rowData.estatus_badge || '—').addClass('text-wrap');
        $('#det_estatus_proyecto').find('span, .badge').addClass('text-wrap');
        $('#det_fecha_pedido').text(rowData.fecha_pedido_formateada || '—');
        $('#det_tipo_pedido').text(rowData.tipo_pedido || '—');
        $('#det_incoterm').text(rowData.incoterm || '—');
        if (rowData.montos_ocultos) {
            $('#det_subtotal, #det_iva, #det_monto').text('- - -');
        } else {
            $('#det_subtotal').text((rowData.cmoneda || 'USD') + ' $' + (parseFloat(rowData.subtotal_pedido || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })));
            $('#det_iva').text((rowData.cmoneda || 'USD') + ' $' + (parseFloat(rowData.iva_pedido || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })));
            $('#det_monto').text(rowData.monto_formateado || '—');
        }
    }

    pedidoIdActual = pedidoIdEnc;
    ventaIdActual = ventaIdEnc;

    // Carga detalle completo desde backend (partidas, adjuntos, seguimiento)
    fntCargarDetalleCompleto(pedidoIdEnc);

    // Muestra el panel de detalle
    fntMostrarDetalle();
}

/**
 * Carga vía AJAX los datos detallados, partidas, adjuntos y bitácora de seguimiento.
 * 
 * @param {string} pedidoIdEnc
 */
function fntCargarDetalleCompleto(pedidoIdEnc) {

    $('#tbody_partidas_proveedor').html('<tr><td colspan="12" class="text-center p-3 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando partidas...</td></tr>');
    $('#contenedor_adjuntos_proveedor').html('<div class="text-muted fs-12 p-2"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando adjuntos...</div>');
    $('#timeline_seguimientos').empty();
    $('#sin_seguimientos').addClass('d-none');
    $('#loading_seguimientos').removeClass('d-none');

    if (detalleProveedorRequest) detalleProveedorRequest.abort();
    detalleProveedorRequest = $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getDetallePedidoProveedor',
        data: { pedido_id: pedidoIdEnc },
        dataType: 'json',
        success: function (resp) {
            if (pedidoIdActual !== pedidoIdEnc) return;

            $('#loading_seguimientos').addClass('d-none');

            if (!resp || resp.respuesta !== 'ok' || !resp.data) {
                alertify.error(resp.mensaje || 'Error al obtener detalle de la orden.', 3);
                return;
            }

            var detalle = resp.data.pedido || {};
            var partidas = resp.data.partidas || [];
            var adjuntos = resp.data.adjuntos || [];
            var historial = resp.data.historial || [];

            // Actualiza campos de encabezado con los datos más recientes
            if (detalle.folio_ocp) $('#det_folio_ocp').text(detalle.folio_ocp);
            if (detalle.proveedor_nombre) $('#det_proveedor').text(detalle.proveedor_nombre);
            if (detalle.proveedor_rfc) $('#det_proveedor_rfc').text(detalle.proveedor_rfc);
            if (detalle.proveedor_contacto) $('#det_proveedor_contacto').text(detalle.proveedor_contacto);
            if (detalle.proyecto_id) $('#det_proyecto_id').text(detalle.proyecto_id);
            if (detalle.cliente_nombre) $('#det_cliente').text(detalle.cliente_nombre);
            if (detalle.titulo_venta) $('#det_titulo').text(detalle.titulo_venta);
            if (detalle.comprador_nombre) $('#det_comprador').text(detalle.comprador_nombre);
            if (detalle.fecha_pedido_formateada) $('#det_fecha_pedido').text(detalle.fecha_pedido_formateada);
            if (detalle.tipo_pedido) $('#det_tipo_pedido').text(detalle.tipo_pedido);
            if (detalle.incoterm) $('#det_incoterm').text(detalle.incoterm);

            // Estatus de la orden a proveedor (1 = Orden Activa, 2 = Orden Cancelada)
            var enviadoVal = parseInt(detalle.enviado || 0);
            var badgeOrden = '';
            if (enviadoVal === 1) {
                badgeOrden = '<span class="badge bg-success fs-11"><i class="fa-regular fa-circle-check me-1"></i>Orden Activa</span>';
            } else if (enviadoVal === 2) {
                badgeOrden = '<span class="badge bg-danger fs-11"><i class="fa-regular fa-ban me-1"></i>Orden Cancelada</span>';
            } else {
                badgeOrden = '<span class="badge bg-secondary fs-11">Borrador</span>';
            }
            $('#det_estatus_orden').html(badgeOrden);

            // Estatus del proyecto
            if (detalle.estatus_proyecto) {
                var estId = parseInt(detalle.estatus_proyecto_id || 0);
                var badgeProyClass = (estId >= 11) ? 'bg-success' : (estId >= 8 ? 'bg-primary' : (estId >= 6 ? 'bg-info' : 'bg-warning text-dark'));
                $('#det_estatus_proyecto').html('<span class="badge ' + badgeProyClass + ' fs-11">' + escapeHtml(detalle.estatus_proyecto) + '</span>');
            }

            var moneda = detalle.cmoneda || 'USD';
            var montosOcultos = resp.data.montos_ocultos === true;
            if (montosOcultos) {
                $('#det_subtotal, #det_iva, #det_monto').text('- - -');
            } else {
                var subtotalNum = parseFloat(detalle.subtotal || 0);
                var ivaNum = parseFloat(detalle.iva || 0);
                var totalNum = parseFloat(detalle.total || 0);
                $('#det_subtotal').text(moneda + ' $' + subtotalNum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#det_iva').text(moneda + ' $' + ivaNum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#det_monto').text(moneda + ' $' + totalNum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }

            // Renderizar Partidas
            fntRenderizarPartidas(partidas, moneda, montosOcultos);

            // Renderizar Adjuntos
            fntRenderizarAdjuntos(adjuntos);

            // Renderizar Historial de Seguimientos
            historialSeguimientoData = historial;
            $('#badge_total_seg').text(historial.length);
            if (historial.length === 0) {
                $('#sin_seguimientos').removeClass('d-none');
            } else {
                fntRenderizarTimelinePagina(1);
            }
        },
        error: function (xhr, status, error) {
            if (status === 'abort') return;
            console.error('Error al cargar detalle:', error);
            $('#loading_seguimientos').addClass('d-none');
            $('#tbody_partidas_proveedor').html('<tr><td colspan="12" class="text-center p-3 text-danger"><i class="fa-regular fa-circle-exclamation me-1"></i>Error al cargar partidas</td></tr>');
            $('#contenedor_adjuntos_proveedor').html('<div class="text-muted fs-12 p-2">Sin adjuntos</div>');
            $('#sin_seguimientos').removeClass('d-none');
        }
    });
}

/**
 * Renderiza la tabla de partidas de la orden a proveedor.
 * 
 * @param {Array} partidas
 * @param {string} moneda
 * @param {boolean} montosOcultos Restricción del perfil vendedor informada por el backend.
 */
function fntRenderizarPartidas(partidas, moneda, montosOcultos) {
    $('#badge_total_partidas').text(partidas.length);

    if (!Array.isArray(partidas) || partidas.length === 0) {
        $('#tbody_partidas_proveedor').html('<tr><td colspan="12" class="text-center p-3 text-muted"><i class="fa-light fa-inbox me-1"></i>Esta orden no tiene partidas registradas.</td></tr>');
        return;
    }

    var html = '';
    partidas.forEach(function (item) {
        var cantOcCliente = parseFloat(item.cantidad_orden_compra_cliente !== null && item.cantidad_orden_compra_cliente !== undefined ? item.cantidad_orden_compra_cliente : (item.cantidad_pedido || 0));
        var cantPedidoProv = parseFloat(item.cantidad_pedido !== null && item.cantidad_pedido !== undefined ? item.cantidad_pedido : 0);
        var cantPedidoAlm = parseFloat(item.cantidad_pedido_almacen !== null && item.cantidad_pedido_almacen !== undefined ? item.cantidad_pedido_almacen : 0);
        var pu = parseFloat(item.precio_unitario || 0);
        var imp = parseFloat(item.importe || 0);
        var claveMat = item.clave_sap || item.ccvematerial || '—';
        
        var descPrincipal = escapeHtml(item.descripcion || 'Sin descripción');
        var descAdicional = (item.descripcion_adicional && item.descripcion_adicional.trim() !== '') ? escapeHtml(item.descripcion_adicional) : '';

        var descHtml = '<div class="text-wrap" style="white-space: pre-wrap; word-break: break-word; line-height: 1.4;">' + descPrincipal;
        if (descAdicional !== '') {
            descHtml += '<div class="text-muted mt-1 text-wrap" style="white-space: pre-wrap; word-break: break-word; font-size: 11.5px; line-height: 1.35;">' + descAdicional + '</div>';
        }
        descHtml += '</div>';

        // Estatus de entrega (tb_pedidos_proveedor_detalle.entregado)
        var entregadoVal = parseInt(item.entregado !== undefined ? item.entregado : 0);
        var badgeEntregado = '';
        if (entregadoVal === 1) {
            badgeEntregado = '<span class="badge bg-primary fs-11"><i class="fa-regular fa-box-check me-1"></i>Entregado</span>';
        } else if (entregadoVal === 2) {
            badgeEntregado = '<span class="badge bg-secondary fs-11"><i class="fa-regular fa-ban me-1"></i>Entrega Cancelada</span>';
        } else {
            badgeEntregado = '<span class="badge bg-warning text-dark fs-11"><i class="fa-regular fa-clock me-1"></i>Pendiente de Entrega</span>';
        }

        html += '<tr>';
        html += '<td class="text-center align-middle fw-bold text-dark">' + escapeHtml(item.codigo_partida || '—') + '</td>';
        html += '<td class="text-center align-middle"><span class="badge bg-light text-dark border">' + escapeHtml(claveMat) + '</span></td>';
        html += '<td class="align-middle text-start text-wrap" style="white-space: pre-wrap; word-break: break-word; min-width: 280px;">' + descHtml + '</td>';
        html += '<td class="text-center align-middle fw-bold text-dark">' + cantOcCliente.toLocaleString('en-US') + '</td>';
        html += '<td class="text-center align-middle fw-bold text-primary">' + cantPedidoProv.toLocaleString('en-US') + '</td>';
        html += '<td class="text-center align-middle fw-bold text-info">' + cantPedidoAlm.toLocaleString('en-US') + '</td>';
        html += '<td class="text-center align-middle">' + escapeHtml(item.unidad_medida || 'PZA') + '</td>';
        html += '<td class="text-end align-middle">' + (montosOcultos ? '- - -' : moneda + ' $' + pu.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) + '</td>';
        html += '<td class="text-end align-middle fw-bold text-dark">' + (montosOcultos ? '- - -' : moneda + ' $' + imp.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) + '</td>';
        html += '<td class="text-center align-middle"><span class="badge bg-light text-dark border">' + escapeHtml(item.tiempo_entrega || '—') + '</span></td>';
        html += '<td class="text-center align-middle fw-semibold text-primary">' + escapeHtml(item.fecha_estimada_formateada || '—') + '</td>';
        html += '<td class="text-center align-middle">' + badgeEntregado + '</td>';
        html += '</tr>';
    });

    $('#tbody_partidas_proveedor').html(html);
}

/**
 * Renderiza la lista de archivos adjuntos de la orden a proveedor.
 * 
 * @param {Array} adjuntos
 */
function fntRenderizarAdjuntos(adjuntos) {
    $('#badge_total_adjuntos').text(adjuntos.length);

    if (!Array.isArray(adjuntos) || adjuntos.length === 0) {
        $('#contenedor_adjuntos_proveedor').html('<div class="text-muted fs-12 p-2"><i class="fa-light fa-file me-1"></i>No hay archivos adjuntos para esta orden de compra a proveedor.</div>');
        return;
    }

    var html = '';
    adjuntos.forEach(function (adj) {
        var fileName = (adj.archivo || '').trim();
        if (!fileName) return;

        var fileUrl = base_url + '/seguimiento/getArchivoProveedor?pedido_id=' + encodeURIComponent(pedidoIdActual) + '&archivo=' + encodeURIComponent(fileName);
        var fileExt = fileName.split('.').pop().toLowerCase();

        var iconClass = 'fa-file-lines text-secondary';
        if (['pdf'].includes(fileExt)) {
            iconClass = 'fa-file-pdf text-danger';
        } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExt)) {
            iconClass = 'fa-file-image text-info';
        } else if (['doc', 'docx'].includes(fileExt)) {
            iconClass = 'fa-file-word text-primary';
        } else if (['xls', 'xlsx', 'csv'].includes(fileExt)) {
            iconClass = 'fa-file-excel text-success';
        } else if (['zip', 'rar', '7z'].includes(fileExt)) {
            iconClass = 'fa-file-archive text-warning';
        }

        html += '<a href="' + fileUrl + '" target="_blank" class="btn btn-outline-secondary lfm-btn-action lfm-btn-action--quiet adjunto-orden-proveedor" title="Abrir ' + escapeHtml(fileName) + '">';
        html += '<i class="fa-regular ' + iconClass + ' fs-16"></i>';
        html += '<span class="text-truncate fs-12 fw-semibold" style="max-width: 230px;">' + escapeHtml(fileName) + '</span>';
        html += '<i class="fa-solid fa-arrow-up-right-from-square fs-10 text-muted"></i>';
        html += '</a>';
    });

    $('#contenedor_adjuntos_proveedor').html(html);
}

/**
 * Renderiza una página específica del historial de seguimientos en el timeline.
 * 
 * @param {number} pagina Número de página a renderizar (1-indexed).
 */
function fntRenderizarTimelinePagina(pagina) {
    if (!historialSeguimientoData || historialSeguimientoData.length === 0) {
        $('#sin_seguimientos').removeClass('d-none');
        $('#timeline_seguimientos').empty();
        return;
    }

    var totalRegistros = historialSeguimientoData.length;
    var totalPaginas = Math.ceil(totalRegistros / registrosPorPaginaSeguimiento);

    if (pagina < 1) pagina = 1;
    if (pagina > totalPaginas) pagina = totalPaginas;

    paginaActualSeguimiento = pagina;

    var inicio = (pagina - 1) * registrosPorPaginaSeguimiento;
    var fin = Math.min(inicio + registrosPorPaginaSeguimiento, totalRegistros);
    var itemsPagina = historialSeguimientoData.slice(inicio, fin);

    var html = '<div class="timeline-seguimiento">';

    itemsPagina.forEach(function (seg, indexInPage) {
        var globalIndex = inicio + indexInPage;
        var colorClass = (globalIndex === 0) ? 'border-primary text-primary' : 'border-secondary text-secondary';
        var iconClass = (globalIndex === 0) ? 'fa-comment-check text-primary' : 'fa-comment text-secondary';

        html += '<div class="d-flex mb-3 seg-item" style="gap: 12px;">';

        // Icono lateral
        html += '<div class="d-flex flex-column align-items-center" style="min-width:32px;">';
        html += '<div class="rounded-circle d-flex align-items-center justify-content-center border ' + colorClass + '" style="width:32px;height:32px;">';
        html += '<i class="fa-regular ' + iconClass + ' fs-12"></i>';
        html += '</div>';
        if (indexInPage < itemsPagina.length - 1) {
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

        if (seg.archivo && seg.archivo.trim() !== '') {
            var fileName = seg.archivo.trim();
            var fileUrl = base_url + '/seguimiento/getArchivoProveedor?pedido_id=' + encodeURIComponent(pedidoIdActual) + '&archivo=' + encodeURIComponent(fileName);
            var fileExt = fileName.split('.').pop().toLowerCase();

            var iconClass = 'fa-file-lines text-secondary';
            if (['pdf'].includes(fileExt)) {
                iconClass = 'fa-file-pdf text-danger';
            } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExt)) {
                iconClass = 'fa-file-image text-info';
            } else if (['doc', 'docx'].includes(fileExt)) {
                iconClass = 'fa-file-word text-primary';
            } else if (['xls', 'xlsx', 'csv'].includes(fileExt)) {
                iconClass = 'fa-file-excel text-success';
            } else if (['zip', 'rar', '7z'].includes(fileExt)) {
                iconClass = 'fa-file-archive text-warning';
            }

            html += '<div class="mt-2 pt-2 border-top d-flex align-items-center flex-wrap gap-2">';
            html += '<span class="fs-12 fw-semibold text-muted d-inline-flex align-items-center">';
            html += '<i class="fa-regular fa-paperclip me-1 text-primary"></i>Adjunto:';
            html += '</span>';
            html += '<a href="' + fileUrl + '" target="_blank" class="btn btn-outline-secondary lfm-btn-action lfm-btn-action--quiet text-truncate adjunto-seguimiento-proveedor" title="Abrir ' + escapeHtml(fileName) + '">';
            html += '<i class="fa-regular ' + iconClass + ' me-1"></i>';
            html += '<span class="text-truncate" style="max-width: 280px;">' + escapeHtml(fileName) + '</span>';
            html += '<i class="fa-solid fa-arrow-up-right-from-square fs-10 ms-1 text-muted"></i>';
            html += '</a>';
            html += '</div>';
        }

        html += '</div>';
        html += '</div>';

        html += '</div>'; // .d-flex
    });

    html += '</div>'; // .timeline-seguimiento

    // Paginación si hay más de 1 página
    if (totalPaginas > 1) {
        html += '<div class="d-flex flex-column flex-sm-row justify-content-between align-items-center pt-3 mt-2 border-top gap-2">';
        html += '<span class="fs-12 text-muted">';
        html += 'Mostrando <strong>' + (inicio + 1) + '</strong> a <strong>' + fin + '</strong> de <strong>' + totalRegistros + '</strong> seguimientos';
        html += '</span>';

        html += '<nav aria-label="Navegación del timeline">';
        html += '<ul class="pagination pagination-sm mb-0">';

        // Botón Anterior
        var prevDisabled = (pagina === 1) ? ' disabled' : '';
        html += '<li class="page-item' + prevDisabled + '">';
        html += '<a class="page-link btn-pag-timeline" href="javascript:void(0);" data-page="' + (pagina - 1) + '" aria-label="Anterior">';
        html += '<i class="fa-solid fa-chevron-left"></i>';
        html += '</a>';
        html += '</li>';

        // Rango de páginas con elipsis
        var range = fntObtenerRangoPaginas(pagina, totalPaginas);
        range.forEach(function (p) {
            if (p === '...') {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            } else {
                var activeClass = (p === pagina) ? ' active' : '';
                html += '<li class="page-item' + activeClass + '">';
                html += '<a class="page-link btn-pag-timeline" href="javascript:void(0);" data-page="' + p + '">' + p + '</a>';
                html += '</li>';
            }
        });

        // Botón Siguiente
        var nextDisabled = (pagina === totalPaginas) ? ' disabled' : '';
        html += '<li class="page-item' + nextDisabled + '">';
        html += '<a class="page-link btn-pag-timeline" href="javascript:void(0);" data-page="' + (pagina + 1) + '" aria-label="Siguiente">';
        html += '<i class="fa-solid fa-chevron-right"></i>';
        html += '</a>';
        html += '</li>';

        html += '</ul>';
        html += '</nav>';
        html += '</div>';
    } else if (totalRegistros > 0) {
        html += '<div class="pt-2 mt-2 border-top text-center">';
        html += '<span class="fs-12 text-muted">Mostrando los ' + totalRegistros + ' seguimientos.</span>';
        html += '</div>';
    }

    $('#timeline_seguimientos').html(html);
}

/**
 * Genera el rango de números de página con elipsis (...) para la paginación.
 * 
 * @param {number} paginaActual 
 * @param {number} totalPaginas 
 * @returns {Array}
 */
function fntObtenerRangoPaginas(paginaActual, totalPaginas) {
    var delta = 1;
    var range = [];
    var rangeWithDots = [];
    var l;

    for (var i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - delta && i <= paginaActual + delta)) {
            range.push(i);
        }
    }

    for (var j = 0; j < range.length; j++) {
        var i = range[j];
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    }

    return rangeWithDots;
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
    $('#panel_kpis').show();
    $('#panel_calendario_entregas').show();
    $('#panel_detalle').hide();
    pedidoIdActual = '';
    ventaIdActual = '';

    if (tableOrdenes !== null) {
        tableOrdenes.columns.adjust();
        if (tableOrdenes.fixedHeader) tableOrdenes.fixedHeader.adjust();
    }

    if (calendarEntregas !== null) {
        setTimeout(function () {
            calendarEntregas.updateSize();
        }, 150);
    }
}

/**
 * Muestra el panel de detalle y oculta la lista.
 */
function fntMostrarDetalle() {
    $('#panel_lista_registros').hide();
    $('#panel_filtros').hide();
    $('#panel_kpis').hide();
    $('#panel_calendario_entregas').hide();
    $('#panel_detalle').show();
}

/* =========================================================
 * CALENDARIO DE ENTREGAS DE PROVEEDORES
 * ========================================================= */

/** Formatea una fecha local sin convertirla a UTC para conservar los límites del calendario. */
function fntFechaLocalCalendario(fecha) {
    return fecha.getFullYear() + '-' + String(fecha.getMonth() + 1).padStart(2, '0') + '-' + String(fecha.getDate()).padStart(2, '0');
}

/**
 * Inicializa FullCalendar para mostrar las fechas estimadas de entrega de proveedores.
 */
function fntInicializarCalendarioEntregas() {
    var calendarEl = document.getElementById('calendarEntregas');
    if (!calendarEl || typeof FullCalendar === 'undefined') return;

    calendarEntregas = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: new Date(), // Abrir en el mes actual; los eventos no cambian la vista.
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            list: 'Lista'
        },
        locale: 'es',
        height: 'auto',
        navLinks: true,
        editable: false,
        // Mostrar tres partidas por día y abrir las restantes en el popover compartido con Órdenes Cliente.
        dayMaxEvents: 3,
        moreLinkText: function (n) { return 'Ver ' + n + ' más'; },
        datesSet: function (info) {
            // FullCalendar entrega un fin exclusivo; consultar hasta el último día visible.
            var ultimoDiaVisible = new Date(info.end.getFullYear(), info.end.getMonth(), info.end.getDate() - 1);
            calendarIntervaloVisible = {
                fecha_ini: fntFechaLocalCalendario(info.start),
                fecha_fin: fntFechaLocalCalendario(ultimoDiaVisible)
            };
            fntCargarCalendarioEntregas();
        },
        eventClassNames: function (arg) {
            var estatus = arg.event.extendedProps.estatus_codigo || 'todos';
            return ['oc-cal-evento', 'oc-tone-' + (['vencido', 'proximo', 'en_tiempo', 'entregado', 'cancelado'].includes(estatus) ? estatus : 'todos')];
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            if (info.event && info.event.extendedProps) {
                fntMostrarDetalleEntregaModal(info.event.extendedProps);
            }
        }
    });

    calendarEntregas.render();
}

/**
 * Consulta las fechas de entrega del periodo visible y conserva la búsqueda y el alcance del usuario.
 */
function fntCargarCalendarioEntregas() {
    if (!document.getElementById('calendarEntregas')) return;

    if (!calendarIntervaloVisible) return;

    var fecha_ini = calendarIntervaloVisible.fecha_ini;
    var fecha_fin = calendarIntervaloVisible.fecha_fin;
    var filtro_num_orden = $('#filtro_num_orden').val().trim();
    // Ignorar respuestas antiguas cuando el usuario cambia de mes antes de que termine la consulta.
    var solicitudId = ++calendarioProveedorSecuencia;
    if (calendarioProveedorRequest) calendarioProveedorRequest.abort();

    calendarioProveedorRequest = $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getCalendarioEntregasProveedorData',
        data: {
            fecha_ini: fecha_ini,
            fecha_fin: fecha_fin,
            filtro_num_orden: filtro_num_orden
        },
        dataType: 'json',
        success: function (resp) {
            if (solicitudId !== calendarioProveedorSecuencia) return;
            if (resp && resp.respuesta === 'ok') {
                var resumen = resp.resumen || {};
                $('#cal_count_total').text(new Intl.NumberFormat('en-US').format(resumen.total || 0));
                $('#cal_count_entregados').text(new Intl.NumberFormat('en-US').format(resumen.entregados || 0));
                $('#cal_count_vencidos').text(new Intl.NumberFormat('en-US').format(resumen.vencidos || 0));
                $('#cal_count_proximos').text(new Intl.NumberFormat('en-US').format(resumen.proximos || 0));
                $('#cal_count_en_tiempo').text(new Intl.NumberFormat('en-US').format(resumen.en_tiempo || 0));

                calendarEventosData = resp.eventos || [];
                fntAplicarFiltroCalendario();

            } else {
                alertify.error((resp && resp.msg) || 'No fue posible consultar el calendario.', 4);
            }
        },
        error: function (xhr, status, error) {
            if (solicitudId !== calendarioProveedorSecuencia || status === 'abort') return;
            console.error('Error al cargar calendario de entregas de proveedor:', error);
            alertify.error('Error al cargar el calendario de entregas.', 4);
        },
        complete: function () { if (solicitudId === calendarioProveedorSecuencia) calendarioProveedorRequest = null; }
    });
}

/**
 * Aplica el filtro por estatus seleccionado en los eventos del calendario.
 */
function fntAplicarFiltroCalendario() {
    if (!calendarEntregas) return;

    var eventosFiltrados = calendarEventosData.filter(function (evt) {
        if (calendarFiltroEstatus === 'todos') return true;
        return evt.extendedProps && evt.extendedProps.estatus_codigo === calendarFiltroEstatus;
    });

    // Mantener una sola fuente; FullCalendar puede recargar las fuentes al cambiar de mes.
    if (calendarFuenteEventos) calendarFuenteEventos.remove();
    calendarFuenteEventos = calendarEntregas.addEventSource(eventosFiltrados);
    $('#cal_resultado').text(eventosFiltrados.length + ' partidas con fecha en el periodo visible' + (calendarFiltroEstatus === 'todos' ? '.' : ' para el estatus seleccionado.'));
}

/**
 * Muestra el modal con los detalles completos de la fecha límite de entrega seleccionada.
 * 
 * @param {Object} props Propiedades extendidas del evento de FullCalendar
 */
function fntMostrarDetalleEntregaModal(props) {
    if (!props) return;

    var ordenText = props.num_orden_compra ? props.num_orden_compra : ('Pedido Prov. #' + props.pedido_id);

    $('#mdl_pedido_id').text(ordenText);
    $('#mdl_proveedor').text(props.proveedor || '—');
    $('#mdl_proyecto_id').text(props.proyecto_id || '—');
    $('#mdl_cliente').text(props.cliente || '—');
    $('#mdl_titulo_venta').text(props.titulo_venta || '—');
    $('#mdl_codigo_partida').text(props.codigo_partida || '—');
    $('#mdl_cantidad').text(props.cantidad_pedido || '0');
    $('#mdl_tiempo_entrega').text(props.tiempo_entrega || '—');
    var descCompleta = props.descripcion || 'Sin descripción';
    if (props.descripcion_adicional && props.descripcion_adicional.trim() !== '') {
        descCompleta += '\n' + props.descripcion_adicional.trim();
    }
    $('#mdl_descripcion').text(descCompleta);
    $('#mdl_fecha_pedido').text(props.fecha_pedido_formateada || '—');
    $('#mdl_fecha_estimada').text(props.fecha_estimada_formateada || '—');

    var tiempoRestante = props.tiempo_restante_str || '—';
    var colorClass = 'text-success';
    var diasRetraso = parseInt(props.dias_retraso_entrega || 0);

    // Fallback de cálculo en frontend si fecha_recibo y fecha_estimada_entrega están disponibles
    if (!diasRetraso && props.fecha_recibo && props.fecha_estimada_entrega && props.fecha_recibo !== '0000-00-00') {
        var fReciboStr = String(props.fecha_recibo).substring(0, 10);
        var fEstStr    = String(props.fecha_estimada_entrega).substring(0, 10);
        var fReciboDt  = new Date(fReciboStr + 'T00:00:00');
        var fEstDt     = new Date(fEstStr + 'T00:00:00');
        var diffMs     = fReciboDt.getTime() - fEstDt.getTime();
        var diffDays   = Math.round(diffMs / (1000 * 60 * 60 * 24));
        if (diffDays > 0) {
            diasRetraso = diffDays;
            tiempoRestante = 'Entregado con ' + diasRetraso + ' día' + (diasRetraso > 1 ? 's' : '') + ' de retraso';
        }
    }

    if (diasRetraso > 0) {
        colorClass = 'text-danger';
    } else if (props.entregado == 1) {
        colorClass = 'text-primary';
    } else if (props.dias_restantes < 0) {
        colorClass = 'text-danger';
    } else if (props.dias_restantes <= 7) {
        colorClass = 'text-warning text-dark';
    }
    $('#mdl_tiempo_restante').html('<span class="' + colorClass + ' fw-bold"><i class="fa-regular fa-clock me-1"></i>' + escapeHtml(tiempoRestante) + '</span>');

    var tono = ['vencido', 'proximo', 'en_tiempo', 'entregado', 'cancelado'].includes(props.estatus_codigo) ? props.estatus_codigo : 'todos';
    var badgeHtml = '<span class="badge oc-indicador oc-tone-' + tono + ' font-small p-2">' + escapeHtml(props.estatus_label || '—') + '</span>';
    $('#mdl_estatus_badge').html(badgeHtml);

    // Estatus de entrega (tb_pedidos_proveedor_detalle.entregado)
    var entregadoVal = parseInt(props.entregado !== undefined ? props.entregado : 0);
    var entregadoBadge = '';
    if (entregadoVal === 1) {
        entregadoBadge = '<span class="badge bg-primary fs-12 p-2"><i class="fa-regular fa-box-check me-1"></i> Entregado</span>';
    } else if (entregadoVal === 2) {
        entregadoBadge = '<span class="badge bg-secondary fs-12 p-2"><i class="fa-regular fa-ban me-1"></i> Entrega Cancelada</span>';
    } else {
        entregadoBadge = '<span class="badge bg-warning text-dark fs-12 p-2"><i class="fa-regular fa-clock me-1"></i> Pendiente de Entrega</span>';
    }
    $('#mdl_entregado_badge').html(entregadoBadge);

    // Información de Entrada a Almacén y Facturación (tb_recibos)
    var numRecibo   = (props.num_recibo && String(props.num_recibo).trim() !== '') ? props.num_recibo : '—';
    var fchRecibo   = (props.fecha_recibo_formateada && String(props.fecha_recibo_formateada).trim() !== '') ? props.fecha_recibo_formateada : '—';
    var factura     = (props.factura_serie_folio && String(props.factura_serie_folio).trim() !== '') ? props.factura_serie_folio : '—';
    var folioFiscal = (props.factura_folio_fiscal && String(props.factura_folio_fiscal).trim() !== '') ? props.factura_folio_fiscal : '—';

    $('#mdl_num_recibo').text(numRecibo);
    $('#mdl_fecha_entrega_almacen').text(fchRecibo);
    $('#mdl_factura_serie_folio').text(factura);
    $('#mdl_factura_folio_fiscal').text(folioFiscal);

    var modalElement = document.getElementById('modalDetalleEntrega');
    if (modalElement) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            $('#modalDetalleEntrega').modal('show');
        }
    }
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
