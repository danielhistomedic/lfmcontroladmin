/**
 * partidas_pendientes_cotizar.js
 * Módulo: Seguimiento - Partidas Pendiente de Cotizar
 * 
 * Controla:
 *  - DataTable de partidas pendientes de cotizar (tb_compras_cotizaciones_detalle + tb_ventas_detalle + tb_compras_cotizaciones + tb_proveedores)
 *  - Panel de filtros
 *  - Tarjetas de KPIs de rendimiento
 *  - Modal de detalle completo de Solicitud de Cotización
 */

'use strict';

/* =========================================================
 * VARIABLES GLOBALES
 * ========================================================= */
var tablePartidas = null;
var tableElement = "#tablePartidasPendientes";
var tableElementJS = "tablePartidasPendientes";
var xhrCargarTabla = null;
var xhrCargarKPIs = null;

/* =========================================================
 * DOCUMENT READY
 * ========================================================= */
$(document).ready(function () {

    // Establece rango de fecha por defecto (año en curso)
    var hoy = new Date();
    var primerDia = '01/01/' + hoy.getFullYear();
    var hoyDia = ('0' + hoy.getDate()).slice(-2);
    var hoyMes = ('0' + (hoy.getMonth() + 1)).slice(-2);
    var hoyStr = hoyDia + '/' + hoyMes + '/' + hoy.getFullYear();
    $('#filtro_fecha_ini').val(primerDia);
    $('#filtro_fecha_fin').val(hoyStr);

    // Inicializa datepicker si aplica
    if ($.fn.datepicker) {
        $('[data-toggle="datepicker"]').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            language: 'es',
            todayHighlight: true
        });
    }

    // Inicializa tabla
    fntInicializarTabla();

    // Carga selects dinámicos para filtros
    fntCargarSelectFiltros();

    // Carga tabla con filtros iniciales al arrancar
    fntCargarTabla();
    fntCargarKPIs();

    // Evento Filtrar
    $('#btnFiltrar').on('click', function () {
        fntCargarTabla();
        fntCargarKPIs();
    });

    // Evento Limpiar Filtros
    $('#btnLimpiar').on('click', function () {
        // Restaurar rango de fechas por defecto
        $('#filtro_fecha_ini').val(primerDia);
        $('#filtro_fecha_fin').val(hoyStr);

        // Limpiar selects sin disparar múltiples llamadas AJAX concurrentes
        $('#filtro_proveedor').val('');
        if ($.fn.select2) {
            $('#filtro_proveedor').trigger('change.select2');
        }

        $('#filtro_proyecto').val('');
        if ($.fn.select2) {
            $('#filtro_proyecto').trigger('change.select2');
        }

        $('#filtro_antiguedad').val('');
        $('#filtro_solicitud').val('');
        $('#filtro_busqueda').val('');

        // Limpiar filtros individuales de columna en DataTable
        $(tableElement + ' thead tr:eq(1) input').val('');
        if (tablePartidas) {
            tablePartidas.search('').columns().search('');
        }

        // Ejecutar una sola llamada
        fntCargarTabla();
        fntCargarKPIs();
    });

    // Eventos on change de selects
    $('#filtro_proveedor, #filtro_proyecto, #filtro_antiguedad').on('change', function () {
        fntCargarTabla();
        fntCargarKPIs();
    });

    // Evento enter en inputs de texto
    $('#filtro_solicitud, #filtro_busqueda, #filtro_fecha_ini, #filtro_fecha_fin').on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            fntCargarTabla();
            fntCargarKPIs();
        }
    });

});


/* =========================================================
 * FUNCIONES PRINCIPALES
 * ========================================================= */

/**
 * Inicializa el DataTable de partidas pendientes de cotizar.
 */
function fntInicializarTabla() {

    if (!document.getElementById(tableElementJS)) return;

    /* Inicializamos los filtros de columna */
    var elemento_clone = tableElement + ' thead tr';
    var elemento_appendto = tableElement + ' thead';
    $(elemento_clone).clone(true).appendTo(elemento_appendto);

    var elemento_each = tableElement + ' thead tr:eq(1) th';
    $(elemento_each).each(function (i) {

        // Nombre de la columna
        var title = $(this).text();

        // Crear el elemento input en cada columna
        $(this).html('<div class="form-group mb-0"><input type="text" style="max-height: 12px;" class="form-control form-control-sm text-center" placeholder="Filtrar ' + title + '"/></div>');

        // Evento del input creado en cada columna
        $('input', this).on('keyup change', function () {
            if (tablePartidas && tablePartidas.column(i).search() != this.value) {
                tablePartidas
                    .column(i)
                    .search(this.value)
                    .draw();
            }
        });
    });

    /*-------------------------------------------
    [ DataTable Inicializa ]*/
    tablePartidas = $(tableElement).DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        scrollX: "100%",
        destroy: true,
        select: true,
        order: [
            [1, "desc"]
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
                sheetName: 'Partidas Pendientes',
                extend: 'excel',
                messageTop: "",
                title: 'Lista de Partidas Pendientes de Cotizar',
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
                data: 'tiempo_transcurrido_badge', 
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        return row.dias_transcurridos !== undefined ? parseInt(row.dias_transcurridos) : 0;
                    }
                    return data;
                }
            },
            {
                data: 'folio_solicitud',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    var valorDisplay = data || ('SC-' + row.cotizacion_id);
                    return '<span class="fw-bold text-danger">' + valorDisplay + '</span>';
                }
            },
            {
                data: 'proyecto_id',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-semibold text-dark">' + (data || '—') + '</span>';
                }
            },
            {
                data: 'fecha_solicitud_formateada',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    return '<span class="text-muted">' + (data || '—') + '</span>';
                }
            },
            {
                data: 'proveedor_nombre',
                className: 'text-start align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-bold text-primary">' + (data || '—') + '</span>';
                }
            },
            {
                data: 'cliente_nombre',
                className: 'text-start align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-semibold text-dark">' + (data || '—') + '</span>';
                }
            },
            { data: 'proyecto_titulo', className: 'text-start align-middle' },
            {
                data: 'codigo_partida',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-semibold text-dark">' + (data || '—') + '</span>';
                }
            },
            { data: 'descripcion_partida', className: 'text-start align-middle' },
            {
                data: 'cantidad_formateada',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-bold text-dark">' + (data || '1 PZA') + '</span>';
                }
            },
            { data: 'estatus_cotizacion_badge', className: 'text-center align-middle', orderable: false }
        ],
        language: typeof idioma_espanol !== 'undefined' ? idioma_espanol : {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    });

    /*-------------------------------------------
    [ DataTable - Se ejecuta después de inicializar la tabla ]*/
    $(tableElement).on('init.dt', function () {
        if (typeof menu !== 'undefined' && typeof validaPermisoExportar === 'function') {
            validaPermisoExportar(menu);
        }
        $('.loading-panel-showing').removeClass('loading-panel-showing');
    });

    /*-------------------------------------------
    [ DataTable - Se ejecuta después de redibujarse la tabla ]*/
    $(tableElement).on('draw.dt', function () {
        if (tablePartidas) {
            tablePartidas.columns.adjust();
        }
    });
}

/**
 * Carga (o recarga) la tabla con los filtros actuales.
 */
function fntCargarTabla() {

    var fecha_ini         = $('#filtro_fecha_ini').val().trim();
    var fecha_fin         = $('#filtro_fecha_fin').val().trim();
    var filtro_proveedor  = $('#filtro_proveedor').val() ? $('#filtro_proveedor').val().trim() : '';
    var filtro_proyecto   = $('#filtro_proyecto').val() ? $('#filtro_proyecto').val().trim() : '';
    var filtro_antiguedad = $('#filtro_antiguedad').val() ? $('#filtro_antiguedad').val().trim() : '';
    var filtro_solicitud  = $('#filtro_solicitud').val() ? $('#filtro_solicitud').val().trim() : '';
    var filtro_busqueda   = $('#filtro_busqueda').val() ? $('#filtro_busqueda').val().trim() : '';

    // Abortar llamada previa si aún está en curso
    if (xhrCargarTabla && xhrCargarTabla.readyState !== 4) {
        xhrCargarTabla.abort();
    }

    // Muestra loading
    $('.loading-panel-showing').addClass('loading-panel-showing');

    xhrCargarTabla = $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getPartidasPendientesCotizarDatatable',
        data: {
            fecha_ini: fecha_ini,
            fecha_fin: fecha_fin,
            filtro_proveedor: filtro_proveedor,
            filtro_proyecto: filtro_proyecto,
            filtro_antiguedad: filtro_antiguedad,
            filtro_solicitud: filtro_solicitud,
            filtro_busqueda: filtro_busqueda
        },
        dataType: 'json',
        success: function (data) {
            if (tablePartidas !== null && Array.isArray(data)) {
                // Limpia e inserta inmediatamente antes de dibujar
                tablePartidas.clear().rows.add(data).draw();
                tablePartidas.columns.adjust().draw();
                setTimeout(function () {
                    if (tablePartidas) {
                        tablePartidas.columns.adjust().draw();
                    }
                }, 150);
            }
        },
        error: function (xhr, status, error) {
            if (status !== 'abort') {
                console.error('Error al cargar tabla de partidas pendientes:', error);
            }
        },
        complete: function () {
            $('.loading-panel-showing').removeClass('loading-panel-showing');
        }
    });
}

/**
 * Carga los KPIs superiores mediante AJAX
 */
function fntCargarKPIs() {

    var fecha_ini         = $('#filtro_fecha_ini').val().trim();
    var fecha_fin         = $('#filtro_fecha_fin').val().trim();
    var filtro_proveedor  = $('#filtro_proveedor').val() ? $('#filtro_proveedor').val().trim() : '';
    var filtro_proyecto   = $('#filtro_proyecto').val() ? $('#filtro_proyecto').val().trim() : '';
    var filtro_antiguedad = $('#filtro_antiguedad').val() ? $('#filtro_antiguedad').val().trim() : '';
    var filtro_solicitud  = $('#filtro_solicitud').val() ? $('#filtro_solicitud').val().trim() : '';
    var filtro_busqueda   = $('#filtro_busqueda').val() ? $('#filtro_busqueda').val().trim() : '';

    // Abortar llamada previa si aún está en curso
    if (xhrCargarKPIs && xhrCargarKPIs.readyState !== 4) {
        xhrCargarKPIs.abort();
    }

    xhrCargarKPIs = $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getPartidasPendientesKPIs',
        data: {
            fecha_ini: fecha_ini,
            fecha_fin: fecha_fin,
            filtro_proveedor: filtro_proveedor,
            filtro_proyecto: filtro_proyecto,
            filtro_antiguedad: filtro_antiguedad,
            filtro_solicitud: filtro_solicitud,
            filtro_busqueda: filtro_busqueda
        },
        dataType: 'json',
        success: function (res) {
            if (res && res.respuesta === 'ok' && res.data) {
                var k = res.data;
                $('#kpi_total_partidas').text(k.total_partidas_pendientes || 0);
                $('#kpi_total_proveedores').text(k.total_proveedores || 0);
                $('#kpi_total_proyectos').text(k.total_proyectos || 0);
                $('#kpi_total_solicitudes_txt').text((k.total_solicitudes || 0) + ' Solicitudes SC');
                $('#kpi_total_demoradas').text(k.total_demoradas || 0);

                var prom = parseFloat(k.promedio_dias_espera || 0);
                $('#kpi_promedio_dias').text('Promedio: ' + prom.toFixed(1) + ' días');
            }
        },
        error: function (xhr, status, error) {
            if (status !== 'abort') {
                console.error('Error al cargar KPIs:', error);
            }
        }
    });
}

/**
 * Carga los catálogos para los selects de filtro (Proveedores y Proyectos) e inicializa Select2
 */
function fntCargarSelectFiltros() {

    $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getFiltrosPendientesCotizar',
        dataType: 'json',
        success: function (res) {
            if (res && res.respuesta === 'ok') {

                // Llenar select de proveedores
                var htmlProv = '<option value="">Todos los proveedores</option>';
                if (res.proveedores && Array.isArray(res.proveedores)) {
                    res.proveedores.forEach(function (p) {
                        htmlProv += '<option value="' + p.id + '">' + p.nombre + ' (' + p.total_pendientes + ' pendientes)</option>';
                    });
                }
                $('#filtro_proveedor').html(htmlProv);

                // Llenar select de proyectos
                var htmlProy = '<option value="">Todos los proyectos</option>';
                if (res.proyectos && Array.isArray(res.proyectos)) {
                    res.proyectos.forEach(function (pr) {
                        var titCorto = pr.titulo && pr.titulo.length > 30 ? pr.titulo.substring(0, 30) + '...' : (pr.titulo || '');
                        htmlProy += '<option value="' + pr.id + '">' + pr.proyecto_id + ' - ' + titCorto + ' (' + pr.total_pendientes + ' pendientes)</option>';
                    });
                }
                $('#filtro_proyecto').html(htmlProy);

                // Reinicializar select2
                if ($.fn.select2) {
                    $('#filtro_proveedor, #filtro_proyecto').select2({
                        width: '100%',
                        placeholder: 'Seleccione una opción',
                        allowClear: true
                    });
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar catálogos de filtro:', error);
        }
    });
}

/* =========================================================
 * MODAL DE DETALLE DE SOLICITUD
 * ========================================================= */

/**
 * Abre el modal con el detalle completo de una Solicitud de Cotización
 * @param {number} cotizacionId
 */
function fntVerDetalleSolicitud(cotizacionId) {

    if (!cotizacionId || cotizacionId <= 0) return;

    $('.loading-panel-showing').addClass('loading-panel-showing');

    $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getDetalleSolicitudModal',
        data: { cotizacion_id: cotizacionId },
        dataType: 'json',
        success: function (res) {
            $('.loading-panel-showing').removeClass('loading-panel-showing');

            if (res && res.respuesta === 'ok' && res.data) {
                var cab = res.data.cabecera;
                var partidas = res.data.partidas || [];
                var adjuntos = res.data.adjuntos || [];

                // Llenar cabecera
                $('#mdl_folio_solicitud').text(cab.folio_solicitud || ('SC-' + cab.cotizacion_id));
                $('#mdl_fecha_solicitud').text((cab.fecha_solicitud_formateada || '—') + (cab.fecha_registro_formateada ? ' (' + cab.fecha_registro_formateada + ')' : ''));
                $('#mdl_proyecto_id').text((cab.proyecto_id || 'S/P') + (cab.proyecto_titulo ? ' - ' + cab.proyecto_titulo : ''));
                $('#mdl_proveedor_nombre').text(cab.proveedor_nombre || 'Sin Proveedor');
                $('#mdl_proveedor_contacto').text(
                    (cab.proveedor_contacto || '—') + 
                    (cab.proveedor_email ? ' | ' + cab.proveedor_email : '') +
                    (cab.proveedor_telefono ? ' | Tel: ' + cab.proveedor_telefono : '')
                );
                $('#mdl_cliente_nombre').text(cab.cliente_nombre || 'Sin Cliente');

                // Tiempo transcurrido en modal
                var dias = parseInt(cab.dias_transcurridos || 0);
                var horas = parseInt(cab.horas_transcurridas || 0);
                var badgeMdl = '';
                if (dias === 0) {
                    badgeMdl = '<span class="badge bg-success text-white fs-12 px-2 py-1"><i class="fa-regular fa-clock me-1"></i> Hoy (' + horas + ' hrs)</span>';
                } else if (dias <= 2) {
                    badgeMdl = '<span class="badge bg-success text-white fs-12 px-2 py-1"><i class="fa-regular fa-circle-check me-1"></i> ' + dias + ' días</span>';
                } else if (dias <= 5) {
                    badgeMdl = '<span class="badge bg-warning text-dark fs-12 px-2 py-1"><i class="fa-regular fa-hourglass-half me-1"></i> ' + dias + ' días</span>';
                } else {
                    badgeMdl = '<span class="badge bg-danger text-white fs-12 px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> ' + dias + ' días (Demorado)</span>';
                }
                $('#mdl_tiempo_transcurrido').html(badgeMdl);

                // Conteo de partidas cotizadas vs pendientes
                var countCotizadas = 0;
                var countPendientes = 0;
                var htmlPartidas = '';

                partidas.forEach(function (part, idx) {
                    var isCotizada = parseInt(part.esta_cotizada) === 1;
                    if (isCotizada) {
                        countCotizadas++;
                    } else {
                        countPendientes++;
                    }

                    var pu = parseFloat(part.precio_unitario || 0);
                    var imp = parseFloat(part.importe || 0);
                    var mon = cab.moneda || 'USD';

                    var badgePartida = isCotizada
                        ? '<span class="badge bg-success text-white fs-11 px-2 py-1"><i class="fa-regular fa-check me-1"></i> Cotizada</span>'
                        : '<span class="badge bg-warning text-dark fs-11 px-2 py-1 fw-bold"><i class="fa-regular fa-clock me-1"></i> Pendiente</span>';

                    var rowBg = isCotizada ? 'table-success bg-opacity-10' : '';

                    htmlPartidas += '<tr class="' + rowBg + '">';
                    htmlPartidas += '<td class="text-center fw-bold text-muted">' + (idx + 1) + '</td>';
                    htmlPartidas += '<td><span class="fw-bold text-primary">' + (part.codigo_partida || part.codigo_proveedor || '—') + '</span></td>';
                    htmlPartidas += '<td><div class="fs-12">' + (part.descripcion_partida || '—') + '</div></td>';
                    htmlPartidas += '<td class="text-center fw-semibold">' + (part.cantidad || 0) + ' ' + (part.unidad_medida || 'PZA') + '</td>';
                    htmlPartidas += '<td class="text-end fw-bold">' + (pu > 0 ? (mon + ' $' + pu.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : '<span class="text-muted fst-italic">$0.00</span>') + '</td>';
                    htmlPartidas += '<td class="text-end fw-bold">' + (imp > 0 ? (mon + ' $' + imp.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : '<span class="text-muted fst-italic">$0.00</span>') + '</td>';
                    htmlPartidas += '<td class="text-center">' + badgePartida + '</td>';
                    htmlPartidas += '</tr>';
                });

                if (partidas.length === 0) {
                    htmlPartidas = '<tr><td colspan="7" class="text-center text-muted py-3">No hay partidas registradas en esta solicitud</td></tr>';
                }

                $('#tbodyMdlPartidas').html(htmlPartidas);

                // Badges de resumen en modal
                var badgeResumen = '<span class="badge bg-primary fs-11">' + partidas.length + ' Total Partidas</span> ' +
                    '<span class="badge bg-success fs-11">' + countCotizadas + ' Cotizadas</span> ' +
                    '<span class="badge bg-warning text-dark fs-11">' + countPendientes + ' Pendientes</span>';
                $('#mdl_partidas_resumen_badge').html(badgeResumen);

                // Adjuntos si existen
                if (adjuntos && adjuntos.length > 0) {
                    var htmlAdj = '<div class="d-flex flex-wrap gap-2">';
                    adjuntos.forEach(function (adj) {
                        var fileName = adj.archivo ? adj.archivo.split('/').pop() : 'Archivo Adjunto';
                        htmlAdj += '<a href="' + base_url + '/uploads/cotizaciones_adjuntos/' + adj.archivo + '" target="_blank" class="btn btn-xs btn-outline-info">';
                        htmlAdj += '<i class="fa-regular fa-paperclip me-1"></i> ' + fileName + '</a>';
                    });
                    htmlAdj += '</div>';
                    $('#containerMdlAdjuntos').html(htmlAdj);
                    $('#cardMdlAdjuntos').removeClass('d-none');
                } else {
                    $('#cardMdlAdjuntos').addClass('d-none');
                }

                // Mostrar Modal
                var modalInstance = new bootstrap.Modal(document.getElementById('modalDetalleSolicitud'));
                modalInstance.show();
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Atención', res.mensaje || 'No se encontró la información de la solicitud', 'warning');
                } else {
                    alert(res.mensaje || 'No se encontró la información de la solicitud');
                }
            }
        },
        error: function (xhr, status, error) {
            $('.loading-panel-showing').removeClass('loading-panel-showing');
            console.error('Error al consultar detalle de solicitud:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'No fue posible consultar el detalle de la solicitud', 'error');
            } else {
                alert('No fue posible consultar el detalle de la solicitud');
            }
        }
    });
}
