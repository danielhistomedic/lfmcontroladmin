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
var tableElement = "#tableOrdenes";
var tableElementJS = "tableOrdenes";
var ventaIdActual = '';
var historialSeguimientoData = [];
var paginaActualSeguimiento = 1;
var registrosPorPaginaSeguimiento = 5;
var calendarEntregas = null;
var calendarEventosData = [];
var calendarFiltroEstatus = 'todos';
var filtroCumplimientoOcc = 'todos';
var filtroEjecutivoOcc = '';
var conteosCumplimientoOcc = {todos: 0, vencido: 0, proximo: 0, en_tiempo: 0};
var partidasClienteData = [];
var monedaPartidasCliente = 'USD';
var filtroPartidasCliente = 'todos';

/* =========================================================
 * DOCUMENT READY
 * ========================================================= */
$(document).ready(function () {

    // Incluye ventanas de fecha, encabezado fijo y menús que las bibliotecas insertan en body.
    $('body').addClass('seguimiento-ordenes-page');

    // Establece el periodo de un año atrás a hoy, en el formato del datepicker
    fntEstablecerPeriodoInicial();

    // Inicializa tabla
    fntInicializarTabla();

    // Carga tabla con filtros iniciales al arrancar
    fntCargarTabla();

    // Inicializa y carga Calendario de Entregas
    fntInicializarCalendarioEntregas();
    fntCargarCalendarioEntregas();

    // Eventos
    $('#btnRestablecerFiltros').on('click', fntRestablecerFiltrosOrdenes);
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

/**
 * Restaura el periodo inicial de un año calendario atrás a hoy, con fechas locales.
 * El 29 de febrero se ajusta al 28 cuando el año anterior no es bisiesto.
 * @returns {void}
 */
function fntEstablecerPeriodoInicial() {
    var hoy = new Date();
    var anioAnterior = hoy.getFullYear() - 1;
    var ultimoDia = new Date(anioAnterior, hoy.getMonth() + 1, 0).getDate();
    var inicio = new Date(anioAnterior, hoy.getMonth(), Math.min(hoy.getDate(), ultimoDia));
    // El datepicker y la máscara comparten dd/mm/aaaa; nunca asignar ISO al control.
    [["#filtro_fecha_ini", inicio], ["#filtro_fecha_fin", hoy]].forEach(function (item) {
        var control = $(item[0]), fecha = item[1];
        control.val(String(fecha.getDate()).padStart(2, '0') + '/' + String(fecha.getMonth() + 1).padStart(2, '0') + '/' + fecha.getFullYear());
        if ($.fn.datepicker && control.data('datepicker')) control.datepicker('setDate', fecha);
        if (control[0]) control[0].setCustomValidity('');
    });
}

/** Convierte fechas completas dd/mm/aaaa o ISO a aaaa-mm-dd, sin parseo regional.
 * @returns {string|null} ISO válido, cadena vacía o null cuando la fecha no existe.
 */
function fntFechaFiltroISO(valor) {
    valor = String(valor || '').trim();
    if (!valor) return '';
    var partes, anio, mes, dia;
    if ((partes = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(valor))) {
        dia = Number(partes[1]); mes = Number(partes[2]); anio = Number(partes[3]);
    } else if ((partes = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor))) {
        anio = Number(partes[1]); mes = Number(partes[2]); dia = Number(partes[3]);
    } else return null;
    if (anio < 1000 || mes < 1 || mes > 12 || dia < 1 || dia > new Date(anio, mes, 0).getDate()) return null;
    return String(anio) + '-' + String(mes).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
}

/** Una sola lectura del periodo para lista y calendario; buscar una orden omite las fechas.
 * @returns {Object|null} Fechas ISO listas para POST; null impide consultar con un rango inválido.
 */
function fntLeerPeriodoOrdenes() {
    var inicio = document.getElementById('filtro_fecha_ini'), fin = document.getElementById('filtro_fecha_fin');
    inicio.setCustomValidity(''); fin.setCustomValidity('');
    if (String($('#filtro_num_orden').val() || '').trim()) return {fecha_ini: '', fecha_fin: ''};
    var fechaInicio = fntFechaFiltroISO(inicio.value), fechaFin = fntFechaFiltroISO(fin.value);
    var control = !fechaInicio ? inicio : (!fechaFin || fechaInicio > fechaFin ? fin : null);
    if (control) {
        control.setCustomValidity(fechaInicio && fechaFin ? 'La fecha fin debe ser igual o posterior a la fecha inicio.' : 'Ingresa una fecha válida en formato dd/mm/aaaa.');
        control.reportValidity();
        return null;
    }
    return {fecha_ini: fechaInicio, fecha_fin: fechaFin};
}

/**
 * Limpia búsquedas de orden/tabla y selecciones, vuelve al periodo original y recarga.
 * @returns {void}
 */
function fntRestablecerFiltrosOrdenes() {
    $('#filtro_num_orden').val('');
    fntEstablecerPeriodoInicial();
    filtroEjecutivoOcc = '';
    filtroCumplimientoOcc = 'todos';
    calendarFiltroEstatus = 'todos';
    fntActualizarSeleccionEjecutiva();
    $('#filtros_cumplimiento_occ button').each(function () {
        var activo = $(this).attr('data-cumplimiento') === 'todos';
        $(this).toggleClass('active', activo).attr('aria-pressed', activo ? 'true' : 'false');
    });
    $('.btn-filter-cal').each(function () { $(this).toggleClass('active', $(this).attr('data-filter') === 'todos').attr('aria-pressed', $(this).attr('data-filter') === 'todos' ? 'true' : 'false'); });
    if (tableOrdenes) {
        tableOrdenes.search('', false, true).columns().search('', false, true);
        $('#tableOrdenes_wrapper thead input').val('');
    }
    fntCargarTabla();
    fntCargarCalendarioEntregas();
}

/**
 * Inicializa el DataTable de órdenes (sin datos aún).
 */
function fntInicializarTabla() {

    if (!document.getElementById(tableElementJS)) return;

    /* Inicializamos lo filtros de columna */
    var elemento_clone = tableElement + ' thead tr';
    var elemento_appendto = tableElement + ' thead';
    $(elemento_clone).clone(true).appendTo(elemento_appendto);

    var elemento_each = tableElement + ' thead tr:eq(1) th';
    $(elemento_each).each(function(i) {

        // Opciones contiene acciones: conserva la celda para alinear la tabla, sin filtro.
        if (i === 0) {
            $(this).empty();
            return;
        }

        //Nombre de la columna
        var title = $(this).text();

        // Filtros legibles y con nombre accesible; se conserva la búsqueda por columna.
        $(this).html('<div class="form-group mb-0"><input type="text" class="form-control form-control-sm text-center" placeholder="Filtrar ' + title + '"/></div>');

        $('input', this).attr('aria-label', 'Filtrar ' + title);

        // Evento del input creado en cada columna
        $('input', this).on('keyup change', function() {
            if (tableOrdenes && tableOrdenes.column(i).search() != this.value) {
                tableOrdenes
                    .column(i)
                    .search(this.value)
                    .draw();
            }
        });
    });

    /*-------------------------------------------
    [ DataTable Inicializa ]*/
    tableOrdenes = $(tableElement).DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        scrollX: "100%",
        destroy: true,
        select: true,
        order: [
            [6, "desc"] // Fecha Pedido conserva su orden inicial tras añadir el comparativo.
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
                sheetName: 'Órdenes de Compra',
                messageTop: "",
                title: 'Lista de Registros',
                exportOptions: {
                    // Las OCP múltiples se exportan como texto separado por |, sin HTML.
                    orthogonal: 'export',
                    // No incluir botones de Opciones en el archivo exportado.
                    columns: function (index) {
                        return index !== 0 && tableOrdenes.column(index).visible();
                    }
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
                data: 'entregado',
                defaultContent: null,
                className: 'text-center align-middle',
                render: fntRenderizarEstatusOcc
            },
            {
                data: 'cumplimiento_entregas',
                defaultContent: null,
                className: 'text-start align-middle',
                render: fntRenderizarCumplimientoOcc
            },
            {
                data: 'comparativo_entregas',
                defaultContent: null,
                className: 'text-start align-middle',
                render: fntRenderizarComparativoEntregas
            },
            {
                data: 'proyecto_id',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-semibold text-dark">' + (data || '—') + '</span>';
                }
            },
            {
                data: 'num_orden_compra',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    var valorDisplay = data || (row.pedido_id ? ('Pedido #' + row.pedido_id) : '—');
                    return '<span class="fw-semibold text-dark">' + valorDisplay + '</span>';
                }
            },
            {
                data: 'fecha_pedido_formateada',
                className: 'text-center align-middle',
                type: 'num',
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') return fntClaveOrdenFecha(row.fecha_pedido || data);
                    return type === 'display' ? '<span class="text-muted">' + escapeHtml(data || '—') + '</span>' : (data || '—');
                }
            },
            {
                data: 'ordenes_proveedor',
                defaultContent: '',
                className: 'text-center align-middle',
                render: function (data, type) {
                    return fntRenderizarOcpCliente(data, type, 'folio_ocp');
                }
            },
            {
                data: 'ordenes_proveedor',
                defaultContent: '',
                className: 'text-center align-middle',
                type: 'num',
                render: function (data, type) {
                    return fntRenderizarOcpCliente(data, type, 'fecha_ocp_formateada');
                }
            },
            {
                data: 'ordenes_proveedor',
                defaultContent: '',
                className: 'text-start align-middle',
                render: function (data, type) {
                    return fntRenderizarOcpCliente(data, type, 'proveedor');
                }
            },
            {
                data: 'cliente',
                className: 'text-start align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-bold text-primary">' + (data || '—') + '</span>';
                }
            },
            { data: 'titulo_venta', className: 'text-start align-middle' },
            {
                data: 'vendedor',
                className: 'text-start align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-semibold text-dark">' + (data || '—') + '</span>';
                }
            },
            {
                data: 'monto_formateado',
                className: 'text-end align-middle',
                render: function (data, type, row) {
                    return '<span class="fw-bold text-dark">' + (data || '$0.00') + '</span>';
                }
            },
            // Clases de presentación exclusivas; se conservan badges y valores del servidor.
            { data: 'estatus_badge', className: 'text-center align-middle col-estatus-proyecto', orderable: false },
            { data: 'seguimientos_badge', className: 'text-center align-middle col-total-seguimientos', orderable: false },
            { data: 'ultimo_seguimiento_nota_corta', className: 'text-start align-middle' },
            { data: 'ultimo_seguimiento_fecha', className: 'text-center align-middle' },
            { data: 'ultimo_seguimiento_usuario', className: 'text-start align-middle' }
        ],
        language: idioma_espanol
    });

    fntInicializarFiltrosCumplimientoOcc();

    // La inicialización es síncrona: al volver de DataTable() ya existe el botón Excel.
    // Registrar init.dt aquí perdería el evento y dejaría el botón oculto aun con permiso e.
    if (typeof menu !== 'undefined') {
        validaPermisoExportar(menu);
    }
    $('.loading-panel-showing').removeClass('loading-panel-showing');

    /*-------------------------------------------
    [ DataTable - Se ejecuta después de redibujarse la tabla ]*/
    $(tableElement).on('draw.dt', function() {
        if (tableOrdenes) {
            tableOrdenes.columns.adjust();
        }
    });
}

/**
 * Compara fechas estimadas por cruce de partida cliente/proveedor. El resumen
 * cuenta comparaciones, no partidas únicas ni cobertura de cantidades.
 * El desplegable conserva todas las OCP; filtros y Excel reciben texto sin HTML.
 *
 * @param {Array<Object>} cruces Cruces calculados en servidor con la relación autorizada.
 * @param {string} type Uso solicitado por DataTables.
 * @returns {string} Resumen desplegable o texto para búsqueda, orden y exportación.
 */
function fntRenderizarComparativoEntregas(cruces, type) {
    if (!Array.isArray(cruces) || !cruces.length) return 'Sin partidas';
    var estados = {
        antes: ['Proveedor antes', 'cumplimiento-en-tiempo'],
        mismo_dia: ['Mismo día · Sin margen', 'cumplimiento-proximo'],
        desfase: ['Proveedor después · Desfase', 'cumplimiento-vencido'],
        sin_fecha: ['Sin fecha para comparar', 'cumplimiento-cancelado'],
        sin_ocp: ['Sin OCP activa relacionada', 'cumplimiento-cancelado'],
        sin_vinculo: ['Sin vínculo de partida', 'cumplimiento-cancelado'],
        ambiguo: ['Vínculo de partida ambiguo', 'cumplimiento-proximo'],
        cancelada: ['Partida cliente cancelada', 'cumplimiento-cancelado']
    };
    var conteos = {}, textos = [], detalles = [];
    var fecha = function (valor) {
        return /^\d{4}-\d{2}-\d{2}$/.test(valor || '') ? valor.split('-').reverse().join('/') : '—';
    };
    cruces.forEach(function (cruce) {
        var estado = Object.prototype.hasOwnProperty.call(estados, cruce.estado) ? cruce.estado : 'sin_fecha';
        var config = estados[estado];
        conteos[estado] = (conteos[estado] || 0) + 1;
        var margen = cruce.margen_dias == null ? '—' : String(cruce.margen_dias) + ' días';
        var titulo = 'Partida ' + (cruce.codigo_partida || '—') + ' · OCP ' + (cruce.folio_ocp || '—');
        var fechas = 'Cliente: ' + fecha(cruce.fecha_cliente) + ' · Proveedor: ' + fecha(cruce.fecha_proveedor) + ' · Margen: ' + margen;
        textos.push(titulo + ' · ' + (cruce.proveedor || '—') + ' · ' + fechas + ' · ' + config[0]);
        detalles.push('<div class="comparativo-cruce"><strong>' + escapeHtml(titulo) + '</strong>'
            + '<span>' + escapeHtml(cruce.proveedor || '—') + '</span><span>' + escapeHtml(fechas) + '</span>'
            + '<span class="badge ' + config[1] + '">' + config[0] + '</span></div>');
    });
    if (type !== 'display') return textos.join(' | ');
    var resumen = Object.keys(conteos).map(function (estado) {
        return '<span class="badge ' + estados[estado][1] + '">' + conteos[estado] + ' · ' + estados[estado][0] + '</span>';
    }).join(' ');
    return '<details class="comparativo-entregas"><summary>' + resumen + '<span class="font-small"> Ver fechas ('
        + cruces.length + ' comparaciones)</span></summary>' + detalles.join('') + '</details>';
}

/**
 * Predicado de DataTables limitado a la lista de OCC. Usa los conteos originales,
 * no el texto de los badges; una orden mixta puede aparecer en distintos filtros.
 * DataTables 1.13 aplica primero las búsquedas global y de columnas. Se cuentan
 * sus órdenes candidatas antes del filtro rápido; search.dt cierra cada pasada.
 *
 * @param {Object} settings Configuración de la tabla que se está filtrando.
 * @param {Array} data Valores de búsqueda de la fila (no usados).
 * @param {number} dataIndex Índice interno de la fila (no usado).
 * @param {Object} rowData Datos originales de la fila, disponibles en DataTables 1.13.
 * @returns {boolean} Si debe conservarse la fila junto con las otras búsquedas.
 */
function fntFiltrarCumplimientoOcc(settings, data, dataIndex, rowData) {
    if (settings.nTable.id !== tableElementJS) return true;
    var resumen = rowData && rowData.cumplimiento_entregas;
    conteosCumplimientoOcc.todos++;
    ['vencido', 'proximo', 'en_tiempo'].forEach(function (estado) {
        if (resumen && Number(resumen[estado]) > 0) conteosCumplimientoOcc[estado]++;
    });
    // Los conteos de los botones conservan el universo de las búsquedas locales.
    if (filtroEjecutivoOcc && !fntCumpleIndicadorEjecutivo(rowData, filtroEjecutivoOcc)) return false;
    if (filtroCumplimientoOcc === 'todos') return true;
    return !!resumen && Number(resumen[filtroCumplimientoOcc]) > 0;
}

/**
 * Publica conteos de órdenes, independientes de paginación y del filtro rápido.
 * Reinicia el acumulador incluso en búsquedas vacías, sin provocar otro draw.
 * Una orden con varios estados cuenta una vez en cada categoría que corresponde.
 * @returns {void}
 */
function fntActualizarConteosCumplimientoOcc() {
    $('#filtros_cumplimiento_occ [data-conteo-cumplimiento]').each(function () {
        var estado = $(this).attr('data-conteo-cumplimiento');
        $(this).text(new Intl.NumberFormat('es-MX').format(conteosCumplimientoOcc[estado] || 0));
    });
    conteosCumplimientoOcc = {todos: 0, vencido: 0, proximo: 0, en_tiempo: 0};
}

/**
 * Registra una sola búsqueda local y un manejador de botones. Conserva el filtro
 * al recargar datos o volver del detalle y lo elimina al destruir esta tabla.
 * También enlaza las tarjetas ejecutivas y su botón de limpieza sin duplicar eventos.
 * @returns {void}
 */
function fntInicializarFiltrosCumplimientoOcc() {
    var filtros = $.fn.dataTable.ext.search;
    if (filtros.indexOf(fntFiltrarCumplimientoOcc) === -1) filtros.push(fntFiltrarCumplimientoOcc);
    $(tableElement).off('search.dt.conteosCumplimientoOcc').on('search.dt.conteosCumplimientoOcc', function (event, settings) {
        if (settings.nTable.id === tableElementJS) fntActualizarConteosCumplimientoOcc();
    });
    $(tableElement).off('destroy.dt.cumplimientoOcc').on('destroy.dt.cumplimientoOcc', function () {
        var indice = filtros.indexOf(fntFiltrarCumplimientoOcc);
        if (indice !== -1) filtros.splice(indice, 1);
        $(tableElement).off('search.dt.conteosCumplimientoOcc');
        conteosCumplimientoOcc = {todos: 0, vencido: 0, proximo: 0, en_tiempo: 0};
    });
    $('#filtros_cumplimiento_occ').off('click.cumplimientoOcc').on('click.cumplimientoOcc', 'button[data-cumplimiento]', function () {
        var estado = $(this).attr('data-cumplimiento');
        if (['todos', 'vencido', 'proximo', 'en_tiempo'].indexOf(estado) === -1) return;
        // Un filtro rápido reemplaza la selección ejecutiva, evitando intersecciones ocultas.
        filtroEjecutivoOcc = '';
        fntActualizarSeleccionEjecutiva();
        filtroCumplimientoOcc = estado;
        $('#filtros_cumplimiento_occ button').each(function () {
            var activo = $(this).attr('data-cumplimiento') === estado;
            $(this).toggleClass('active', activo).attr('aria-pressed', activo ? 'true' : 'false');
        });
        if (tableOrdenes) tableOrdenes.draw();
    });
    $('#panel_kpis').off('click.filtroEjecutivo').on('click.filtroEjecutivo', 'button[data-kpi-occ]', function () {
        fntAplicarFiltroEjecutivoOcc($(this).attr('data-kpi-occ'));
    });
    $('#btn_quitar_filtro_ejecutivo').off('click.filtroEjecutivo').on('click.filtroEjecutivo', function () {
        filtroEjecutivoOcc = '';
        fntActualizarSeleccionEjecutiva();
        if (tableOrdenes) tableOrdenes.draw();
    });
}

/**
 * Regla única para contar y filtrar indicadores de órdenes abiertas (OCC 0/3).
 * El desfase excluye partidas cliente entregadas o canceladas.
 * @param {Object} row Registro original de la lista.
 * @param {string} indicador abiertas, vencidas, proximas o desfase.
 * @returns {boolean} Si la orden pertenece al indicador.
 */
function fntCumpleIndicadorEjecutivo(row, indicador) {
    if (!row) return false;
    var estado = String(row.entregado == null ? '' : row.entregado).trim();
    if (estado !== '0' && estado !== '3') return false;
    if (indicador === 'abiertas') return true;
    if (indicador === 'vencidas') return !!row.cumplimiento_entregas && Number(row.cumplimiento_entregas.vencido) > 0;
    if (indicador === 'proximas') return !!row.cumplimiento_entregas && Number(row.cumplimiento_entregas.proximo) > 0;
    if (indicador === 'desfase') return Array.isArray(row.comparativo_entregas) && row.comparativo_entregas.some(function (cruce) {
        return cruce.estado === 'desfase' && cruce.entregado_cliente != null
            && Number(cruce.entregado_cliente) !== 1 && Number(cruce.entregado_cliente) !== 2;
    });
    return false;
}

/**
 * Alterna una tarjeta y muestra su selección. Conserva búsquedas global/columnas,
 * conteos del periodo y otros controles; reemplaza el filtro rápido por Todas.
 * @param {string} indicador Indicador de la tarjeta seleccionada.
 * @returns {void}
 */
function fntAplicarFiltroEjecutivoOcc(indicador) {
    if (['abiertas', 'vencidas', 'proximas', 'desfase'].indexOf(indicador) === -1) return;
    filtroEjecutivoOcc = filtroEjecutivoOcc === indicador ? '' : indicador;
    filtroCumplimientoOcc = 'todos';
    $('#filtros_cumplimiento_occ button').each(function () {
        var activo = $(this).attr('data-cumplimiento') === 'todos';
        $(this).toggleClass('active', activo).attr('aria-pressed', activo ? 'true' : 'false');
    });
    fntActualizarSeleccionEjecutiva();
    if (tableOrdenes) tableOrdenes.draw();
}

/** Sincroniza estado visual y accesible, sin recalcular cifras ni redibujar la tabla. */
function fntActualizarSeleccionEjecutiva() {
    var nombres = {abiertas: 'Órdenes por atender', vencidas: 'Con partidas vencidas', proximas: 'Próximas (30 días)', desfase: 'Desfase del proveedor'};
    $('#panel_kpis button[data-kpi-occ]').each(function () {
        var activo = $(this).attr('data-kpi-occ') === filtroEjecutivoOcc;
        $(this).attr('aria-pressed', activo ? 'true' : 'false').closest('.executive-kpi').toggleClass('is-selected', activo);
    });
    $('#aviso_filtro_ejecutivo').toggleClass('d-none', !filtroEjecutivoOcc);
    $('#texto_filtro_ejecutivo').text(filtroEjecutivoOcc ? 'Filtro ejecutivo: ' + nombres[filtroEjecutivoOcc] : '');
}

/**
 * Resume partidas de OCC pendientes/parciales con los estados del calendario.
 * No aplica a otras OCC; sin partidas y sin fecha se distinguen de los estados en tiempo.
 *
 * @param {Object|null} resumen Conteos por estado calculados en servidor.
 * @param {string} type Uso solicitado por DataTables.
 * @returns {string} Badges para pantalla y texto para filtros, orden y Excel.
 */
function fntRenderizarCumplimientoOcc(resumen, type) {
    if (!resumen) return type === 'display' ? '—' : '';
    if (!resumen.total) return 'Sin partidas';
    var estados = [
        ['en_tiempo', 'En tiempo', 'cumplimiento-en-tiempo'],
        ['proximo', 'Próximas', 'cumplimiento-proximo'],
        ['vencido', 'Vencidas', 'cumplimiento-vencido'],
        ['cancelado', 'Canceladas', 'cumplimiento-cancelado'],
        ['entregado', 'Entregadas', 'cumplimiento-entregado'],
        ['sin_fecha', 'Sin fecha de entrega', 'cumplimiento-cancelado']
    ];
    var lineas = [];
    estados.forEach(function (estado) {
        var cantidad = Number(resumen[estado[0]]) || 0;
        if (cantidad <= 0) return;
        var texto = cantidad + (cantidad === 1 ? ' Partida (' : ' Partidas (') + estado[1] + ')';
        lineas.push(type === 'display'
            ? '<span class="badge ' + estado[2] + '">' + texto + '</span>' : texto);
    });
    return type === 'display' ? '<div class="occ-cumplimiento">' + lineas.join('') + '</div>' : lineas.join(' | ');
}

/**
 * Representa el estado de entrega del pedido de cliente, independiente del proyecto.
 * Los valores no definidos no se convierten en pendientes. Filtros y Excel usan texto.
 *
 * @param {number|string|null} entregado Valor original de tb_pedidos_cliente.entregado.
 * @param {string} type Tipo solicitado por DataTables.
 * @returns {string} Badge para pantalla o nombre del estado para otros usos.
 */
function fntRenderizarEstatusOcc(entregado, type) {
    var estados = {
        '0': {texto: 'Pendiente', clase: 'occ-pendiente'},
        '1': {texto: 'Entregado', clase: 'occ-entregado'},
        '3': {texto: 'Entrega parcial', clase: 'occ-parcial'}
    };
    var clave = entregado == null ? '' : String(entregado).trim();
    var estado = Object.prototype.hasOwnProperty.call(estados, clave)
        ? estados[clave] : {texto: 'Sin definir', clase: 'bg-secondary'};
    return type === 'display'
        ? '<span class="badge ' + estado.clase + '">' + estado.texto + '</span>'
        : estado.texto;
}

/**
 * Convierte una fecha ISO (con hora opcional) o dd/mm/aaaa en clave numérica AAAAMMDD.
 * Valida día/mes para no ordenar fechas imposibles. Vacíos e inválidos usan cero:
 * primero en ascendente y al final en descendente, sin inventar una fecha.
 * @param {string|null} valor Fecha original o presentación de respaldo.
 * @returns {number} Clave cronológica o cero.
 */
function fntClaveOrdenFecha(valor) {
    var texto = String(valor == null ? '' : valor).trim();
    var match = /^(\d{4})-(\d{2})-(\d{2})(?:[ T].*)?$/.exec(texto);
    var anio, mes, dia;
    if (match) {
        anio = Number(match[1]); mes = Number(match[2]); dia = Number(match[3]);
    } else {
        match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(texto);
        if (!match) return 0;
        anio = Number(match[3]); mes = Number(match[2]); dia = Number(match[1]);
    }
    if (anio < 1000 || mes < 1 || mes > 12 || dia < 1) return 0;
    var fecha = new Date(Date.UTC(anio, mes - 1, dia));
    if (fecha.getUTCFullYear() !== anio || fecha.getUTCMonth() !== mes - 1 || fecha.getUTCDate() !== dia) return 0;
    return anio * 10000 + mes * 100 + dia;
}

/**
 * Representa las OCP del pedido en el mismo orden en las tres columnas.
 * Devuelve texto sin HTML para búsquedas/exportación. Fecha OCP ordena por la
 * fecha válida más antigua del conjunto, aunque falten fechas en otras OCP.
 *
 * @param {Array<Object>} ordenes OCP relacionadas, ordenadas por fecha e ID en servidor.
 * @param {string} type Tipo solicitado por DataTables.
 * @param {string} campo Campo visible de la OCP.
 * @returns {string} Valores escapados para mostrar o texto para los demás usos.
 */
function fntRenderizarOcpCliente(ordenes, type, campo) {
    if ((type === 'sort' || type === 'type') && campo === 'fecha_ocp_formateada') {
        var fechas = Array.isArray(ordenes) ? ordenes.map(function (ocp) {
            return fntClaveOrdenFecha(ocp.fecha_ocp || ocp.fecha_ocp_formateada);
        }).filter(function (fecha) { return fecha > 0; }) : [];
        return fechas.length ? Math.min.apply(null, fechas) : 0;
    }
    if (!Array.isArray(ordenes) || ordenes.length === 0) return type === 'display' ? '—' : '';
    var valores = ordenes.map(function (ocp) {
        var clave = (type === 'sort' || type === 'type') && campo === 'fecha_ocp_formateada' ? 'fecha_ocp' : campo;
        return String(ocp[clave] == null || ocp[clave] === '' ? '—' : ocp[clave]);
    });
    if (type !== 'display') return valores.join(' | ');
    return valores.map(function (valor) {
        return '<div class="text-nowrap">' + escapeHtml(valor) + '</div>';
    }).join('');
}

/**
 * Carga (o recarga) la tabla con los filtros actuales.
 */
function fntCargarTabla() {

    var periodo = fntLeerPeriodoOrdenes();
    if (!periodo) return;
    fntEstadoResumenEjecutivo('cargando');

    var fecha_ini = periodo.fecha_ini;
    var fecha_fin = periodo.fecha_fin;
    var filtro_num_orden = $('#filtro_num_orden').val() ? $('#filtro_num_orden').val().trim() : '';
    var filtro_estatus   = '';
    var filtro_cliente   = '';

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
            filtro_num_orden: filtro_num_orden,
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
            } else {
                fntEstadoResumenEjecutivo('error');
            }
        },
        error: function (xhr, status, error) {
            fntEstadoResumenEjecutivo('error');
            console.error('Error al cargar órdenes:', error);
            alertify.error(xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'Error al cargar las órdenes de clientes.', 4);
        },
        complete: function () {
            $('.loading-panel-showing').removeClass('loading-panel-showing');
        }
    });
}

/**
 * Calcula cuatro indicadores de órdenes del periodo recibido del servidor.
 * Una OCC se cuenta una vez por indicador, aunque tenga varias partidas u OCP.
 * Abiertas = entregado 0/3; las categorías pueden solaparse. El desfase se basa
 * en fechas estimadas del comparativo, sin afirmar cobertura de cantidades.
 * No depende de paginación ni de filtros locales del DataTable.
 *
 * @param {Array<Object>} data Órdenes de la consulta vigente.
 * @returns {void}
 */
function fntCalcularKPIs(data) {
    if (!Array.isArray(data)) { fntEstadoResumenEjecutivo('error'); return; }
    var unicas = new Map();
    data.forEach(function (row, index) { unicas.set(row.pedido_id == null ? 'fila_' + index : String(row.pedido_id), row); });
    var pendientes = 0, parciales = 0, vencidas = 0, proximas = 0, desfases = 0;
    unicas.forEach(function (row) {
        var estado = String(row.entregado == null ? '' : row.entregado).trim();
        if (!fntCumpleIndicadorEjecutivo(row, 'abiertas')) return;
        if (estado === '0') pendientes++; else parciales++;
        if (fntCumpleIndicadorEjecutivo(row, 'vencidas')) vencidas++;
        if (fntCumpleIndicadorEjecutivo(row, 'proximas')) proximas++;
        if (fntCumpleIndicadorEjecutivo(row, 'desfase')) desfases++;
    });
    var formato = new Intl.NumberFormat('es-MX');
    $('#kpi_occ_abiertas').text(formato.format(pendientes + parciales));
    $('#kpi_occ_vencidas').text(formato.format(vencidas));
    $('#kpi_occ_proximas').text(formato.format(proximas));
    $('#kpi_occ_desfase').text(formato.format(desfases));
    $('#kpi_occ_abiertas_detalle').text(formato.format(pendientes) + ' pendientes · ' + formato.format(parciales) + ' parciales');
    $('#kpi_occ_total').text(formato.format(unicas.size) + ' órdenes consultadas');
    $('#kpi_occ_estado').text(unicas.size ? 'Resumen del periodo consultado. Una orden puede aparecer en varios indicadores.' : 'Sin órdenes para los filtros consultados.');
    $('#panel_kpis').attr('aria-busy', 'false');
    $('#panel_kpis button[data-kpi-occ]').prop('disabled', false);
}

/**
 * Distingue carga/error de un resultado válido con cero órdenes.
 * @param {string} estado cargando o error.
 * @returns {void}
 */
function fntEstadoResumenEjecutivo(estado) {
    $('#panel_kpis button[data-kpi-occ]').prop('disabled', true);
    $('#kpi_occ_abiertas, #kpi_occ_vencidas, #kpi_occ_proximas, #kpi_occ_desfase').text('—');
    $('#kpi_occ_abiertas_detalle').text('Pendientes y entregadas parcialmente');
    $('#kpi_occ_total').text(estado === 'cargando' ? 'Consultando órdenes…' : 'Información no disponible');
    $('#kpi_occ_estado').text(estado === 'cargando' ? 'Actualizando el resumen del periodo consultado…' : 'No se pudo actualizar el resumen. Vuelve a consultar.');
    $('#panel_kpis').attr('aria-busy', estado === 'cargando' ? 'true' : 'false');
}

/* =========================================================
 * DETALLE Y SEGUIMIENTO
 * ========================================================= */

/**
 * Muestra el panel de detalle con datos generales, OCP, partidas, adjuntos e historial de seguimientos.
 * Llamado desde el botón de opciones en cada fila de la tabla.
 * 
 * @param {HTMLElement} btn  Botón que disparó el evento.
 */
function fntVerDetalle(btn) {

    var ventaId = $(btn).data('venta-id');
    var pedidoId = $(btn).data('pedido-id');

    if (!ventaId && !pedidoId) {
        alertify.error('No se pudo obtener el ID de la orden.', 3);
        return;
    }

    // Recupera datos de la fila del DataTable
    var rowData = tableOrdenes.row($(btn).closest('tr')).data();
    // Comparte el mismo estado, colores y tipografía de la lista; limpia el estado anterior.
    $('#det_estatus_occ').html(fntRenderizarEstatusOcc(rowData ? rowData.entregado : null, 'display'));
    // Mismo resumen y representación de la lista; sustituye cualquier dato de la orden anterior.
    $('#det_cumplimiento_occ').html(fntRenderizarCumplimientoOcc(rowData ? rowData.cumplimiento_entregas : null, 'display'));
    // Reutiliza fechas, márgenes y estilos de la lista; reemplaza el comparativo anterior.
    $('#det_comparativo_entregas').html(fntRenderizarComparativoEntregas(rowData ? rowData.comparativo_entregas : [], 'display'));
    // Reutiliza las OCP ya autorizadas de la lista, sin nuevas consultas ni datos de otra orden.
    fntRenderizarOcpDetalle(rowData ? rowData.ordenes_proveedor : []);
    if (rowData) {
        // Llena el panel de detalle con los datos generales
        $('#det_pedido_id').text(rowData.num_orden_compra || (rowData.pedido_id ? ('Pedido #' + rowData.pedido_id) : '—'));
        $('#det_cliente').text(rowData.cliente || '—');
        $('#det_proyecto_id').text(rowData.proyecto_id || '—');
        $('#det_titulo').text(rowData.titulo_venta || '—');
        $('#det_vendedor').text(rowData.vendedor || '—');
        $('#det_estatus').html(rowData.estatus_badge || '—').addClass('text-wrap');
        $('#det_estatus').find('span, .badge').addClass('text-wrap');
        $('#det_fecha_pedido').text(rowData.fecha_pedido_formateada || '—');
        $('#det_monto').text(rowData.monto_formateado || '—');
        $('#det_clasificacion').text(rowData.clasificacion_proyecto || '—');
        $('#badge_total_seg').text(rowData.total_seguimientos || 0);
    }

    // Guarda venta_id actual
    ventaIdActual = ventaId;

    // Carga detalle completo (partidas, adjuntos y bitácora de seguimiento)
    var moneda = (rowData && rowData.cmoneda) ? rowData.cmoneda : 'USD';
    fntCargarDetalleCompleto(pedidoId, ventaId, moneda, rowData ? {
        pedido_id: rowData.pedido_id,
        cruces: rowData.comparativo_entregas
    } : null);

    // Muestra el panel de detalle
    fntMostrarDetalle();
}

/**
 * Presenta las OCP de la orden seleccionada con los controles tipográficos del detalle.
 * Cada OCP conserva juntos folio, fecha y proveedor; sin registros muestra los tres guiones.
 * Usa textContent para admitir texto libre sin interpretarlo como HTML.
 *
 * @param {Array<Object>} ordenes OCP del registro seleccionado en el DataTable.
 * @returns {void}
 */
function fntRenderizarOcpDetalle(ordenes) {
    var contenedor = document.getElementById('det_ordenes_proveedor');
    if (!contenedor) return;
    contenedor.replaceChildren();
    var registros = Array.isArray(ordenes) && ordenes.length ? ordenes : [{}];
    registros.forEach(function (ocp, indice) {
        var grupo = document.createElement('div');
        grupo.className = 'col-12' + (indice ? ' border-top pt-3' : '');
        var fila = document.createElement('div');
        fila.className = 'row g-3';
        [['folio_ocp', 'Folio OCP:'], ['fecha_ocp_formateada', 'Fecha OCP:'], ['proveedor', 'Proveedor:']].forEach(function (campo) {
            var columna = document.createElement('div');
            columna.className = 'form-group col-12 col-md-4 pt-0';
            var etiqueta = document.createElement('label');
            etiqueta.className = 'form-label font-small fw-bold text-primary mb-1';
            etiqueta.textContent = campo[1];
            var valor = document.createElement('h6');
            valor.className = 'vistadatos font-table-body text-wrap text-break fw-semibold text-dark mb-0';
            valor.textContent = ocp[campo[0]] == null || ocp[campo[0]] === '' ? '—' : String(ocp[campo[0]]);
            columna.append(etiqueta, valor);
            fila.appendChild(columna);
        });
        grupo.appendChild(fila);
        contenedor.appendChild(grupo);
    });
}

/**
 * Carga vía AJAX las partidas de la orden de compra cliente, archivos adjuntos y bitácora de seguimiento.
 * 
 * @param {string} pedidoId ID o hash de pedido_id
 * @param {string} ventaId  ID o hash de venta_id
 * @param {string} moneda   Moneda de la orden (USD / MXN)
 * @param {Object|null} comparativoOrden Pedido interno y cruces de la fila seleccionada, sin consultas adicionales.
 */
function fntCargarDetalleCompleto(pedidoId, ventaId, moneda, comparativoOrden) {

    // Descarta las partidas de la orden anterior y deshabilita filtros durante la carga.
    fntRenderizarPartidas([], moneda);
    $('#filtros_partidas_cliente button').prop('disabled', true);

    $('#tbody_partidas_cliente').html('<tr><td colspan="15" class="text-center p-3 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando partidas...</td></tr>');
    $('#contenedor_adjuntos_cliente').html('<div class="text-muted font-small p-2"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando adjuntos...</div>');
    $('#timeline_seguimientos').empty();
    $('#sin_seguimientos').addClass('d-none');
    $('#loading_seguimientos').removeClass('d-none');

    $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getDetallePedidoCliente',
        data: {
            pedido_id: pedidoId,
            venta_id: ventaId
        },
        dataType: 'json',
        success: function (resp) {

            $('#loading_seguimientos').addClass('d-none');

            if (!resp || resp.respuesta !== 'ok' || !resp.data) {
                fntRenderizarPartidas([], moneda);
                historialSeguimientoData = [];
                $('#badge_total_partidas').text(0);
                $('#badge_total_adjuntos').text(0);
                $('#badge_total_seg').text(0);
                $('#tbody_partidas_cliente').html('<tr><td colspan="15" class="text-center text-muted p-3">No se encontraron partidas para esta orden de compra.</td></tr>');
                $('#contenedor_adjuntos_cliente').html('<div class="text-muted font-small p-2">No hay archivos adjuntos para esta orden de compra.</div>');
                $('#sin_seguimientos').removeClass('d-none');
                return;
            }

            var partidas = fntRelacionarComparativoPartidas(resp.data.partidas || [], comparativoOrden);
            var adjuntos = resp.data.adjuntos || [];
            var historial = resp.data.historial || [];

            // 1. Renderiza Partidas
            fntRenderizarPartidas(partidas, moneda);

            // 2. Renderiza Adjuntos
            fntRenderizarAdjuntos(adjuntos);

            // 3. Renderiza Seguimiento
            historialSeguimientoData = historial;
            $('#badge_total_seg').text(historialSeguimientoData.length);
            if (historialSeguimientoData.length > 0) {
                fntRenderizarTimelinePagina(1);
            } else {
                $('#sin_seguimientos').removeClass('d-none');
            }
        },
        error: function (xhr, status, error) {
            fntRenderizarPartidas([], moneda);
            historialSeguimientoData = [];
            $('#badge_total_partidas').text(0);
            $('#badge_total_adjuntos').text(0);
            $('#badge_total_seg').text(0);
            $('#loading_seguimientos').addClass('d-none');
            $('#sin_seguimientos').removeClass('d-none');
            $('#tbody_partidas_cliente').html('<tr><td colspan="15" class="text-center text-danger p-3">Error al cargar partidas.</td></tr>');
            $('#contenedor_adjuntos_cliente').html('<div class="text-danger font-small p-2">Error al cargar adjuntos.</div>');
            console.error('Error al cargar detalle completo:', error);
        }
    });
}

/**
 * Distribuye los cruces ya calculados por la lista a las partidas reales del pedido.
 * No vuelve a relacionar cliente/proveedor: los cruces ya usan venta_detalle_id y código.
 * Exige el pedido, ID y código de la partida cliente; los resultados alternativos
 * de cotizaciones/proyecto sin pedido_id no se confunden con partidas de la OCC.
 *
 * @param {Array<Object>} partidas Partidas devueltas por el detalle de la OCC.
 * @param {Object|null} comparativoOrden pedido_id y cruces de la fila seleccionada.
 * @returns {Array<Object>} Copias con comparativo_entregas propio o null si falta información.
 */
function fntRelacionarComparativoPartidas(partidas, comparativoOrden) {
    if (!Array.isArray(partidas)) return [];
    var pedidoId = Number(comparativoOrden && comparativoOrden.pedido_id);
    var porPartida = new Map();
    var cruces = comparativoOrden && Array.isArray(comparativoOrden.cruces) ? comparativoOrden.cruces : [];
    cruces.forEach(function (cruce) {
        var clave = JSON.stringify([Number(cruce.partida_cliente_id), String(cruce.codigo_partida || '').trim()]);
        if (!porPartida.has(clave)) porPartida.set(clave, []);
        porPartida.get(clave).push(cruce);
    });
    return partidas.map(function (partida) {
        var clave = JSON.stringify([Number(partida.id), String(partida.codigo_partida || '').trim()]);
        var crucesPartida = pedidoId > 0 && Number(partida.pedido_id) === pedidoId && Number(partida.id) > 0
            ? porPartida.get(clave) : null;
        return Object.assign({}, partida, {comparativo_entregas: crucesPartida || null});
    });
}

/**
 * Renderiza la lista de partidas de la orden de compra cliente en la tabla de detalle.
 * 
 * @param {Array} partidas
 * @param {string} moneda
 */
function fntRenderizarPartidas(partidas, moneda) {
    // Cada carga reemplaza la caché completa de la orden, sin consultas al filtrar.
    partidasClienteData = Array.isArray(partidas) ? partidas.slice() : [];
    monedaPartidasCliente = moneda;
    $('#badge_total_partidas').text(partidasClienteData.length);
    var conteos = {todos: partidasClienteData.length, vencido: 0, proximo: 0, en_tiempo: 0, entregado: 0, cancelado: 0};
    partidasClienteData.forEach(function (partida) {
        var estado = partida.cumplimiento_entrega && partida.cumplimiento_entrega.estatus_codigo;
        if (estado !== 'todos' && Object.prototype.hasOwnProperty.call(conteos, estado)) conteos[estado]++;
    });
    $('#filtros_partidas_cliente [data-conteo-partidas]').each(function () {
        $(this).text(new Intl.NumberFormat('es-MX').format(conteos[$(this).attr('data-conteo-partidas')] || 0));
    });
    $('#filtros_partidas_cliente button').prop('disabled', false);
    $('#filtros_partidas_cliente').off('click.filtrosPartidas').on('click.filtrosPartidas', 'button[data-filtro-partidas]', function () {
        fntAplicarFiltroPartidasCliente($(this).attr('data-filtro-partidas'));
    });
    fntAplicarFiltroPartidasCliente('todos');
}

/**
 * Filtra sólo las partidas cargadas de la orden seleccionada, sin modificar sus conteos.
 * Todas incluye partidas sin fecha o sin información, aunque no pertenezcan a una categoría.
 * @param {string} estado Código de cumplimiento o todos.
 * @returns {void}
 */
function fntAplicarFiltroPartidasCliente(estado) {
    if (['todos', 'vencido', 'proximo', 'en_tiempo', 'entregado', 'cancelado'].indexOf(estado) === -1) return;
    filtroPartidasCliente = estado;
    $('#filtros_partidas_cliente button').each(function () {
        var activo = $(this).attr('data-filtro-partidas') === estado;
        $(this).toggleClass('active', activo).attr('aria-pressed', activo ? 'true' : 'false');
    });
    var filtradas = estado === 'todos' ? partidasClienteData : partidasClienteData.filter(function (partida) {
        return partida.cumplimiento_entrega && partida.cumplimiento_entrega.estatus_codigo === estado;
    });
    fntRenderizarFilasPartidas(filtradas, monedaPartidasCliente);
}

/**
 * Dibuja las filas visibles conservando importes, moneda, descripción y los dos estados.
 * @param {Array} partidas Subconjunto visible de partidas.
 * @param {string} moneda Moneda de la orden.
 * @returns {void}
 */
function fntRenderizarFilasPartidas(partidas, moneda) {

    if (!Array.isArray(partidas) || partidas.length === 0) {
        var mensaje = partidasClienteData.length > 0 && filtroPartidasCliente !== 'todos'
            ? 'No hay partidas con el estatus seleccionado.' : 'No hay partidas registradas para esta orden de compra.';
        $('#tbody_partidas_cliente').html('<tr><td colspan="15" class="text-center text-muted p-3"><i class="fa-light fa-inbox me-1"></i>' + mensaje + '</td></tr>');
        return;
    }

    var html = '';
    partidas.forEach(function (item) {
        var cant = parseFloat(item.cantidad || item.cantidad_pedido || 0);
        var pu = parseFloat(item.precio_unitario || 0);
        var descVal = parseFloat(item.descuento || 0);
        var impImpuesto = parseFloat(item.importe_impuesto || 0);
        var imp = parseFloat(item.importe || (cant * pu - descVal + impImpuesto) || 0);
        var claveMat = item.clave || item.ccvematerial || '—';
        var ccnMat = item.ccn || item.codigo_cliente || '—';

        var descPrincipal = escapeHtml(item.descripcion || 'Sin descripción');
        var descAdicional = (item.descripcion_adicional && item.descripcion_adicional.trim() !== '') ? escapeHtml(item.descripcion_adicional) : '';

        var descHtml = '<div class="text-wrap" style="white-space: pre-wrap; word-break: break-word; line-height: 1.4;">' + descPrincipal;
        if (descAdicional !== '') {
            descHtml += '<div class="text-muted mt-1 text-wrap" style="white-space: pre-wrap; word-break: break-word; font-size: var(--font-small); line-height: 1.35;">' + descAdicional + '</div>';
        }
        descHtml += '</div>';

        // Tasa de impuesto para badge informativo opcional
        var tasaImp = parseFloat(item.impuesto_tasa || 0);
        var tasaPct = (tasaImp > 0 && tasaImp <= 1) ? Math.round(tasaImp * 100) : (tasaImp > 1 ? Math.round(tasaImp) : 0);
        var impImpuestoHtml = moneda + ' $' + impImpuesto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (tasaPct > 0) {
            impImpuestoHtml += '<br><small class="text-muted fs-10">(' + tasaPct + '% IVA)</small>';
        }

        // Estatus de entrega
        var entregadoVal = parseInt(item.entregado !== undefined ? item.entregado : 0);
        var badgeEntregado = '';
        if (entregadoVal === 1) {
            badgeEntregado = '<span class="badge bg-primary fs-11"><i class="fa-regular fa-box-check me-1"></i>Entregado</span>';
        } else if (entregadoVal === 2) {
            badgeEntregado = '<span class="badge bg-secondary fs-11"><i class="fa-regular fa-ban me-1"></i>Cancelada</span>';
        } else {
            badgeEntregado = '<span class="badge bg-warning text-dark fs-11"><i class="fa-regular fa-clock me-1"></i>Pendiente de Entrega</span>';
        }

        var fechaEstFmt = item.fecha_estimada_formateada || item.fecha_estimada_entrega || '—';

        html += '<tr>';
        html += '<td class="text-center align-middle fw-bold text-dark">' + escapeHtml(item.codigo_partida || '—') + '</td>';
        html += '<td class="text-center align-middle"><span class="badge bg-light text-dark border">' + escapeHtml(claveMat) + '</span></td>';
        html += '<td class="text-center align-middle"><span class="badge bg-light text-secondary border">' + escapeHtml(ccnMat) + '</span></td>';
        html += '<td class="align-middle text-start text-wrap" style="white-space: pre-wrap; word-break: break-word; min-width: 280px;">' + descHtml + '</td>';
        html += '<td class="text-center align-middle fw-bold text-primary">' + cant.toLocaleString('en-US') + '</td>';
        html += '<td class="text-center align-middle">' + escapeHtml(item.unidad_medida || item.ccveunidad || 'PZA') + '</td>';
        html += '<td class="text-end align-middle">' + moneda + ' $' + pu.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>';
        html += '<td class="text-end align-middle text-muted">' + moneda + ' $' + descVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>';
        html += '<td class="text-end align-middle">' + impImpuestoHtml + '</td>';
        html += '<td class="text-end align-middle fw-bold text-dark">' + moneda + ' $' + imp.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>';
        html += '<td class="text-center align-middle"><span class="badge bg-light text-dark border">' + escapeHtml(item.tiempo_entrega || '—') + '</span></td>';
        html += '<td class="text-center align-middle fw-semibold text-primary">' + escapeHtml(fechaEstFmt) + '</td>';
        // La clase limita el estilo suave a Estatus Entrega sin alterar su significado.
        html += '<td class="text-center align-middle col-estatus-entrega">' + badgeEntregado + '</td>';
        html += '<td class="text-center align-middle">' + fntRenderizarCumplimientoPartida(item.cumplimiento_entrega) + '</td>';
        html += '<td class="text-start align-middle">' + (Array.isArray(item.comparativo_entregas) && item.comparativo_entregas.length
            ? fntRenderizarComparativoEntregas(item.comparativo_entregas, 'display')
            : '<span class="badge cumplimiento-cancelado">Sin información</span>') + '</td>';
        html += '</tr>';
    });

    $('#tbody_partidas_cliente').html(html);
}

/**
 * Muestra el cumplimiento de una partida calculado por la regla compartida del servidor.
 * Usa las clases del resumen de OCC; no recalcula fechas con la zona del navegador.
 *
 * @param {Object|null} cumplimiento Metadatos de cumplimiento_entrega de la partida.
 * @returns {string} Badge de estado sin incorporar HTML recibido del servidor.
 */
function fntRenderizarCumplimientoPartida(cumplimiento) {
    var estados = {
        en_tiempo: ['En tiempo', 'cumplimiento-en-tiempo'],
        proximo: ['Próxima a vencer (30 días)', 'cumplimiento-proximo'],
        vencido: ['Vencida', 'cumplimiento-vencido'],
        cancelado: ['Cancelada', 'cumplimiento-cancelado'],
        entregado: ['Entregada', 'cumplimiento-entregado'],
        sin_fecha: ['Sin fecha de entrega', 'cumplimiento-cancelado']
    };
    var codigo = cumplimiento && cumplimiento.estatus_codigo;
    var estado = Object.prototype.hasOwnProperty.call(estados, codigo)
        ? estados[codigo] : ['Sin información', 'cumplimiento-cancelado'];
    return '<span class="badge ' + estado[1] + '">' + estado[0] + '</span>';
}

/**
 * Renderiza la lista de archivos adjuntos de la orden de compra cliente.
 * 
 * @param {Array} adjuntos
 */
function fntRenderizarAdjuntos(adjuntos) {
    $('#badge_total_adjuntos').text(adjuntos.length);

    if (!Array.isArray(adjuntos) || adjuntos.length === 0) {
        $('#contenedor_adjuntos_cliente').html('<div class="text-muted font-small p-2"><i class="fa-light fa-file me-1"></i>No hay archivos adjuntos para esta orden de compra.</div>');
        return;
    }

    var html = '';
    adjuntos.forEach(function (adj) {
        var fileName = (adj.archivo || '').trim();
        if (!fileName) return;

        var fileUrl = base_url + '/Assets/files/ventas/' + encodeURIComponent(fileName);
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

        html += '<a href="' + fileUrl + '" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 py-2 px-3 shadow-sm" style="border-radius: 8px; max-width: 320px;" title="Abrir ' + escapeHtml(fileName) + '">';
        html += '<i class="fa-regular ' + iconClass + ' font-section-title"></i>';
        html += '<span class="text-truncate font-small fw-semibold" style="max-width: 230px;">' + escapeHtml(fileName) + '</span>';
        html += '<i class="fa-solid fa-arrow-up-right-from-square fs-10 text-muted"></i>';
        html += '</a>';
    });

    $('#contenedor_adjuntos_cliente').html(html);
}

/**
 * Escapa caracteres HTML para evitar XSS
 * 
 * @param {string} str
 * @return {string}
 */
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
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
        html += '<i class="fa-regular ' + iconClass + ' font-small"></i>';
        html += '</div>';
        if (indexInPage < itemsPagina.length - 1) {
            html += '<div style="flex:1;width:2px;background:#dee2e6;margin:4px auto;"></div>';
        }
        html += '</div>';

        // Contenido
        html += '<div class="card border p-0 flex-grow-1 mb-0 shadow-sm">';
        html += '<div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" >';
        html += '<span class="fw-semibold font-small text-primary">';
        html += '<i class="fa-regular fa-user-circle me-1"></i>';
        html += escapeHtml(seg.nombre_usuario || 'Sistema');
        html += '</span>';
        html += '<span class="fs-11 text-muted">';
        html += '<i class="fa-regular fa-calendar me-1"></i>';
        html += escapeHtml(seg.fecha_formateada || seg.fecha || '—');
        html += '</span>';
        html += '</div>';
        html += '<div class="card-body py-2 px-3">';
        html += '<p class="mb-0 font-table-body text-muted" style="white-space:pre-wrap;">';
        html += escapeHtml(seg.notas || 'Sin nota registrada.');
        html += '</p>';

        if (seg.archivo && seg.archivo.trim() !== '') {
            var fileName = seg.archivo.trim();
            var fileUrl = base_url + '/Assets/files/ventas/' + encodeURIComponent(fileName);
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
            html += '<span class="font-small fw-semibold text-muted d-inline-flex align-items-center">';
            html += '<i class="fa-regular fa-paperclip me-1 text-primary"></i>Adjunto:';
            html += '</span>';
            html += '<a href="' + fileUrl + '" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 py-1 px-2 text-truncate" style="max-width: 100%; font-size: var(--font-small); border-radius: 6px;" title="Abrir ' + escapeHtml(fileName) + '">';
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
        html += '<span class="font-small text-muted">';
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
        html += '<span class="font-small text-muted">Mostrando los ' + totalRegistros + ' seguimientos.</span>';
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
    $('#panel_calendario_entregas').show();
    $('#panel_detalle').hide();
    ventaIdActual = '';

    if (tableOrdenes !== null) {
        tableOrdenes.columns.adjust().draw();
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
 * CALENDARIO DE ENTREGAS A CLIENTES
 * ========================================================= */

/** Tonos permitidos; el estado y sus fechas siguen siendo calculados por el servidor. */
function fntTonoCalendario(estado) {
    return ['vencido', 'proximo', 'en_tiempo', 'entregado', 'cancelado'].indexOf(estado) >= 0 ? estado : 'todos';
}

/**
 * Inicializa FullCalendar para mostrar las fechas estimadas de entrega.
 */
function fntInicializarCalendarioEntregas() {
    var calendarEl = document.getElementById('calendarEntregas');
    if (!calendarEl || typeof FullCalendar === 'undefined') return;

    calendarEntregas = new FullCalendar.Calendar(calendarEl, {
        // Abre el mes en curso según la fecha local del usuario.
        initialView: 'dayGridMonth',
        initialDate: new Date(),
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
        // Limita la densidad sin ocultar partidas: FullCalendar abre el resto en un popover.
        dayMaxEvents: 3,
        moreLinkText: function (n) { return 'Ver ' + n + ' más'; },
        noEventsText: 'No hay entregas para este mes y filtro.',
        allDayText: 'Todo el día',
        eventClassNames: function (info) {
            var estado = info.event.extendedProps.estatus_codigo;
            return ['oc-cal-evento', 'oc-tone-' + fntTonoCalendario(estado)];
        },
        eventDidMount: function (info) {
            // Texto nativo seguro: conserva el título completo y explica el color.
            var texto = info.event.title + ' · ' + (info.event.extendedProps.estatus_label || 'Sin estatus');
            info.el.title = texto;
            info.el.setAttribute('aria-label', texto);
            info.el.setAttribute('tabindex', '0');
            info.el.setAttribute('role', 'button');
            info.el.addEventListener('keydown', function (e) {
                // Captura ambas teclas para abrir una sola vez en mes, semana y lista.
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    fntMostrarDetalleEntregaModal(info.event.extendedProps);
                }
            }, true);
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
 * Consulta las fechas de entrega a clientes via AJAX y actualiza el calendario.
 */
function fntCargarCalendarioEntregas() {
    if (!document.getElementById('calendarEntregas')) return;

    var periodo = fntLeerPeriodoOrdenes();
    if (!periodo) return;
    var fecha_ini = periodo.fecha_ini;
    var fecha_fin = periodo.fecha_fin;

    $.ajax({
        type: 'POST',
        url: base_url + '/seguimiento/getCalendarioEntregasData',
        data: {
            fecha_ini: fecha_ini,
            fecha_fin: fecha_fin,
            filtro_num_orden: $('#filtro_num_orden').val().trim()
        },
        dataType: 'json',
        success: function (resp) {
            if (resp && resp.respuesta === 'ok') {
                var resumen = resp.resumen || {};
                $('#cal_count_total').text(new Intl.NumberFormat('en-US').format(resumen.total || 0));
                $('#cal_count_entregados').text(new Intl.NumberFormat('en-US').format(resumen.entregados || 0));
                $('#cal_count_vencidos').text(new Intl.NumberFormat('en-US').format(resumen.vencidos || 0));
                $('#cal_count_proximos').text(new Intl.NumberFormat('en-US').format(resumen.proximos || 0));
                $('#cal_count_en_tiempo').text(new Intl.NumberFormat('en-US').format(resumen.en_tiempo || 0));

                calendarEventosData = resp.eventos || [];
                fntAplicarFiltroCalendario();

                // La carga de entregas conserva el mes elegido; no salta al primer evento.
            }
        },
        error: function (xhr, status, error) {
            $('#cal_resultado').text(xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible cargar las entregas. Intenta actualizar de nuevo.');
            console.error('Error al cargar calendario de entregas:', error);
        }
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

    // Retira también las fuentes anteriores para no acumularlas al alternar filtros.
    calendarEntregas.batchRendering(function () {
        calendarEntregas.getEventSources().forEach(function (source) { source.remove(); });
        calendarEntregas.removeAllEvents();
        calendarEntregas.addEventSource(eventosFiltrados);
    });
    $('#cal_resultado').text(eventosFiltrados.length + ' partidas con fecha en la consulta' +
        (calendarFiltroEstatus === 'todos' ? '.' : ' para el estatus seleccionado.'));
}

/**
 * Muestra el modal con los detalles completos de la fecha límite de entrega seleccionada.
 * 
 * @param {Object} props Propiedades extendidas del evento de FullCalendar
 */
function fntMostrarDetalleEntregaModal(props) {
    if (!props) return;

    var ordenText = props.num_orden_compra ? props.num_orden_compra : ('Pedido #' + props.pedido_id);

    $('#mdl_pedido_id').text(ordenText);
    $('#mdl_cliente').text(props.cliente || '—');
    $('#mdl_proyecto_id').text(props.proyecto_id || '—');
    $('#mdl_titulo_venta').text(props.titulo_venta || '—');
    $('#mdl_codigo_partida').text(props.codigo_partida || '—');
    $('#mdl_cantidad').text(props.cantidad_pedido || '0');
    $('#mdl_descripcion').text(props.descripcion || 'Sin descripción');
    $('#mdl_fecha_pedido').text(props.fecha_pedido_formateada || '—');
    $('#mdl_fecha_estimada').text(props.fecha_estimada_formateada || '—');

    var tiempoRestante = props.tiempo_restante_str || '—';
    var colorClass = 'text-success';
    if (props.dias_restantes < 0) {
        colorClass = 'text-danger';
    } else if (props.dias_restantes <= 30) {
        colorClass = 'text-warning text-dark';
    }
    $('#mdl_tiempo_restante').html('<span class="' + colorClass + ' fw-bold"><i class="fa-regular fa-clock me-1"></i>' + escapeHtml(tiempoRestante) + '</span>');

    var badgeHtml = '<span class="badge oc-indicador oc-tone-' + fntTonoCalendario(props.estatus_codigo) + ' font-small p-2">' + escapeHtml(props.estatus_label || '—') + '</span>';
    $('#mdl_estatus_badge').html(badgeHtml);

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

