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
var historialSeguimientoData = [];
var paginaActualSeguimiento = 1;
var registrosPorPaginaSeguimiento = 5;

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

/**
 * Inicializa el DataTable de órdenes (sin datos aún).
 */
function fntInicializarTabla() {

    if (tableOrdenes !== null) {
        tableOrdenes.destroy();
        tableOrdenes = null;
        $('#tableOrdenes').empty();

        // Re-agrega el thead
        $('#tableOrdenes').append('<thead><tr>' +
            '<th class="text-center">Opciones</th>' +
            '<th class="text-center">Pedido #</th>' +
            '<th class="text-center">Fecha Pedido</th>' +
            '<th class="text-center">Cliente</th>' +
            '<th class="text-center">Proyecto / Título</th>' +
            '<th class="text-center">Vendedor</th>' +
            '<th class="text-center">Monto</th>' +
            '<th class="text-center">Estatus</th>' +
            '<th class="text-center">Seguimientos</th>' +
            '<th class="text-center">Último Seguimiento</th>' +
            '<th class="text-center">Fecha Últ. Seg.</th>' +
            '<th class="text-center">Usuario Últ. Seg.</th>' +
            '</tr></thead>');
    }

    // Preparar la fila de inputs de filtrado antes de la inicialización de DataTable
    const $filterRow = $('#tableOrdenes thead tr:eq(0)').clone(false);
    $filterRow.find('th').each(function (colIdx) {
        const title = $(this).text().trim();
        if (title === 'Opciones' || title === 'Seguimientos' || title === '') {
            $(this).removeAttr('style class aria-controls aria-label aria-sort tabindex')
                .addClass('text-center p-1')
                .html('');
            return;
        }
        const $input = $('<input type="text" style="max-height: 24px;" class="form-control form-control-sm text-center" placeholder="Filtrar ' + title + '"/>');

        $input.on('keyup change clear', function () {
            if (tableOrdenes && tableOrdenes.column(colIdx).search() !== this.value) {
                tableOrdenes.column(colIdx).search(this.value).draw();
            }
        });

        $(this).removeAttr('style class aria-controls aria-label aria-sort tabindex')
            .addClass('text-center p-1')
            .html($('<div class="form-group mb-0"></div>').append($input));
    });
    $('#tableOrdenes thead').append($filterRow);

    tableOrdenes = $('#tableOrdenes').DataTable({
        data: [],
        orderCellsTop: true,
        scrollX: true,
        select: true,
        order: [[2, 'desc']],
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Todos"]
        ],
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                autoFilter: true,
                sheetName: 'Órdenes de Compra',
                messageTop: "",
                title: 'Lista de Órdenes de Compra Clientes',
                exportOptions: {
                    columns: ':visible'
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
                data: 'pedido_id', 
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    return '<span class="fw-bold text-danger">' + (data || '—') + '</span>';
                }
            },
            { 
                data: 'fecha_pedido_formateada', 
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    return '<span class="text-muted">' + (data || '—') + '</span>';
                }
            },
            { 
                data: 'cliente', 
                className: 'text-start align-middle',
                render: function(data, type, row) {
                    return '<span class="fw-bold text-primary">' + (data || '—') + '</span>';
                }
            },
            { data: 'titulo_venta', className: 'text-start align-middle' },
            { 
                data: 'vendedor', 
                className: 'text-start align-middle',
                render: function(data, type, row) {
                    return '<span class="fw-semibold text-dark">' + (data || '—') + '</span>';
                }
            },
            { 
                data: 'monto_formateado', 
                className: 'text-end align-middle',
                render: function(data, type, row) {
                    return '<span class="fw-bold text-dark">' + (data || '$0.00') + '</span>';
                }
            },
            { data: 'estatus_badge', className: 'text-center align-middle', orderable: false },
            { data: 'seguimientos_badge', className: 'text-center align-middle', orderable: false },
            { data: 'ultimo_seguimiento_nota_corta', className: 'text-start align-middle' },
            { data: 'ultimo_seguimiento_fecha', className: 'text-center align-middle' },
            { data: 'ultimo_seguimiento_usuario', className: 'text-start align-middle' }
        ],
        language: {
            url: base_url + '/Assets/vendor/datatables/es_mx.json'
        },
        initComplete: function () {
            $('.loading-panel-showing').removeClass('loading-panel-showing');
        }
    });

    // Evento delegado en el contenedor para asegurar funcionamiento con scrollX
    $(tableOrdenes.table().container()).off('keyup change clear', 'thead input').on('keyup change clear', 'thead input', function () {
        const colIdx = $(this).closest('th').index();
        if (tableOrdenes && tableOrdenes.column(colIdx).search() !== this.value) {
            tableOrdenes.column(colIdx).search(this.value).draw();
        }
    });
}

/**
 * Carga (o recarga) la tabla con los filtros actuales.
 */
function fntCargarTabla() {

    var fecha_ini = $('#filtro_fecha_ini').val().trim();
    var fecha_fin = $('#filtro_fecha_fin').val().trim();
    var filtro_estatus = '';
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
                fntCalcularKPIs(data);

                tableOrdenes.columns.adjust().draw();
                setTimeout(function () {
                    if (tableOrdenes) {
                        tableOrdenes.columns.adjust().draw();
                    }
                }, 150);
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

/**
 * Calcula los indicadores clave de rendimiento (KPIs) en tiempo real
 * a partir de los datos cargados en la tabla de órdenes de compra.
 * 
 * @param {Array} data Lista de registros de órdenes
 */
function fntCalcularKPIs(data) {
    var totalOrdenes = data.length;
    var totalUSD = 0;
    var totalMXN = 0;
    var conSeguimiento = 0;

    data.forEach(function (row) {
        var monto = parseFloat(row.monto_pedido) || 0;
        var moneda = (row.cmoneda || '').toUpperCase();
        if (moneda === 'USD') {
            totalUSD += monto;
        } else {
            totalMXN += monto;
        }

        var totalSeg = parseInt(row.total_seguimientos) || 0;
        if (totalSeg > 0) {
            conSeguimiento++;
        }
    });

    $('#kpi_total_ordenes').text(new Intl.NumberFormat('en-US').format(totalOrdenes));
    $('#kpi_monto_usd').text(new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(totalUSD) + ' USD');
    $('#kpi_monto_mxn').text(new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(totalMXN) + ' MXN');
    $('#kpi_con_seguimiento').text(new Intl.NumberFormat('en-US').format(conSeguimiento));
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
    $('#det_pedido_id').text(rowData.pedido_id || '—');
    $('#det_cliente').text(rowData.cliente || '—');
    $('#det_titulo').text(rowData.titulo_venta || '—');
    $('#det_vendedor').text(rowData.vendedor || '—');
    $('#det_estatus').html(rowData.estatus_badge || '—');
    $('#det_fecha_pedido').text(rowData.fecha_pedido_formateada || '—');
    $('#det_monto').text(rowData.monto_formateado || '—');
    $('#det_clasificacion').text(rowData.clasificacion_proyecto || '—');
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
                historialSeguimientoData = [];
                $('#badge_total_seg').text(0);
                $('#sin_seguimientos').removeClass('d-none');
                return;
            }

            historialSeguimientoData = resp.data;
            $('#badge_total_seg').text(historialSeguimientoData.length);
            fntRenderizarTimelinePagina(1);
        },
        error: function (xhr, status, error) {
            historialSeguimientoData = [];
            $('#badge_total_seg').text(0);
            $('#loading_seguimientos').addClass('d-none');
            $('#sin_seguimientos').removeClass('d-none');
            console.error('Error al cargar historial de seguimiento:', error);
        }
    });
}

/**
 * Renderiza una página específica del historial de seguimientos en el timeline.
 * 
 * @param {number} pagina  Número de página a renderizar (1-indexed).
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
 * @returns {Array} Array con números de página y elipsis.
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
    $('#panel_detalle').hide();
    ventaIdActual = '';

    if (tableOrdenes !== null) {
        tableOrdenes.columns.adjust().draw();
    }
}

/**
 * Muestra el panel de detalle y oculta la lista.
 */
function fntMostrarDetalle() {
    $('#panel_lista_registros').hide();
    $('#panel_filtros').hide();
    $('#panel_kpis').hide();
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
