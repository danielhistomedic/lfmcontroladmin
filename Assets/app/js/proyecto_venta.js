/** DataTables: diseño aprobado compartido en datatable-layout.js; contrato propio conservado. */
/**
 * proyecto_venta.js
 * Módulo: Seguimiento - Proyecto de Venta
 */

'use strict';

var controller = "Seguimiento";
var tableProyectosVenta = null;
var tablePartidasProyecto = null;
var tableElement = "#tableProyectosVenta";
var ventaIdSeleccionado = null;
var historialSeguimientoData = [];
var paginaActualSeguimiento = 1;
var registrosPorPaginaSeguimiento = 5;
var adjuntosProyectoData = [];
var paginaActualAdjuntos = 1;
var registrosPorPaginaAdjuntos = 5;
// Versiones y cancelación: ninguna respuesta anterior puede reemplazar la selección vigente.
var versionListadoProyecto = 0, versionDetalleProyecto = 0;
var peticionListadoProyecto = null, peticionHistorialProyecto = null, peticionAdjuntosProyecto = null;
var controladorDetalleProyecto = null;
// Memoria solo de la selección vigente: al actualizar/cambiar proyecto se consulta de nuevo.
var detalleProyectoListo = false, detalleProyectoEnCurso = false, scrollPendienteProyecto = false;
var estadoColeccionesProyecto = {seguimientos: 'pendiente', adjuntos: 'pendiente'};
var permisoExcelProyecto = null, consultaPermisoExcelProyecto = false;
var tablasPermisoExcelProyecto = new Set();
var filtroEjecutivoProyecto = '';
var resumenEjecutivoProyectoListo = false;

$(document).ready(function () {
    // Filtro local independiente: no reemplaza búsquedas, columnas, fechas ni permisos.
    $('#pv_panel_kpis').on('click.filtroEjecutivo', '[data-kpi-proyecto]', function () {
        if (!this.disabled) fntAplicarFiltroEjecutivoProyecto(this.dataset.kpiProyecto);
    });
    $('#pv_quitar_filtro_ejecutivo').on('click.filtroEjecutivo', function () {
        fntAplicarFiltroEjecutivoProyecto('');
    });


    // Consultar cada colección al abrir su pestaña, una sola vez por selección.
    $('#tabProyectoVenta [data-bs-toggle="tab"], #tabProyectoVenta [data-toggle="tab"]').on('shown.bs.tab', function (e) {
        fntCargarColeccionesVisiblesProyecto();
        var destino = $(e.target).attr('data-bs-target') || $(e.target).attr('data-target');
        if (destino === '#tab-partidas-proyecto' && tablePartidasProyecto) tablePartidasProyecto.columns.adjust();
    });

    // 1. Un año calendario hacia atrás, también al restablecer los filtros.
    fntRestablecerFechasProyecto();

    // 2. Inicializar DataTable
    fntInicializarTablaProyectos();

    // 3. Cargar datos del DataTable
    fntCargarTablaProyectos();

    // Actualizar aplica la búsqueda general y las fechas completas.
    $("#btnFiltrar").on("click", function (e) {
        e.preventDefault();
        fntCargarTablaProyectos();
    });

    // 6. Escuchar Enter en inputs de filtro
    $("#txtProyectoId, #filtro_fecha_ini, #filtro_fecha_fin").on("keypress", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            fntCargarTablaProyectos();
        }
    });

    // 7. Evento al hacer clic en una fila de la tabla o botón Ver Detalle
    $(tableElement + " tbody").on("click", "tr", function (e) {
        // Los botones tienen su propia acción: no ejecutar también la selección de fila.
        if ($(e.target).closest('button, a, input, select, textarea').length) return;
        const data = tableProyectosVenta ? tableProyectosVenta.row(this).data() : null;
        if (!data) return;

        // Quitar clase seleccionada a otras filas y agregársela a la actual
        $(tableElement + " tbody tr").removeClass("selected-row table-primary");
        $(this).addClass("selected-row table-primary");

        // Cargar el detalle del seguimiento para el proyecto seleccionado
        fntCargarDetalleProyecto(data.id, true);
    });

    // Evento específico en el botón "Ver Detalle" dentro de la tabla
    $(document).on("click", ".btnVerDetalleProyecto", function (e) {
        e.stopPropagation();
        const ventaId = $(this).data("id");
        if (ventaId) {
            fntCargarDetalleProyecto(ventaId, true);
        }
    });

    // Evento clic en botones de paginación del timeline de seguimiento
    $(document).on("click", ".btn-pag-timeline", function (e) {
        e.preventDefault();
        var p = parseInt($(this).attr("data-page"));
        if (!isNaN(p) && p > 0) {
            fntRenderizarTimelinePagina(p);
        }
    });

    // Evento clic en botones de paginación del timeline de adjuntos del proyecto
    $(document).on("click", ".btn-pag-adjuntos", function (e) {
        e.preventDefault();
        var p = parseInt($(this).attr("data-page"));
        if (!isNaN(p) && p > 0) {
            fntRenderizarTimelineAdjuntosPagina(p);
        }
    });
});

/** Restablece un año calendario hasta hoy; el 29 de febrero se ajusta al último día válido. */
function fntRestablecerFechasProyecto() {
    const hoy = new Date();
    const anioInicio = hoy.getFullYear() - 1;
    const diaInicio = Math.min(hoy.getDate(), new Date(anioInicio, hoy.getMonth() + 1, 0).getDate());
    const inicio = new Date(anioInicio, hoy.getMonth(), diaInicio);
    [['#filtro_fecha_ini', inicio], ['#filtro_fecha_fin', hoy]].forEach(function (item) {
        const control = $(item[0]), fecha = item[1];
        if ($.fn.datepicker && control.data('datepicker')) control.datepicker('setDate', fecha);
        control.val(String(fecha.getDate()).padStart(2, '0') + '/' + String(fecha.getMonth() + 1).padStart(2, '0') + '/' + fecha.getFullYear());
        if (control[0]) control[0].setCustomValidity('');
    });
}

/** Fecha completa sin parseo regional; vacío representa un extremo abierto. */
function fntFechaProyectoISO(valor) {
    valor = String(valor || '').trim();
    if (!valor) return '';
    var p, y, m, d;
    if ((p = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(valor))) { d = +p[1]; m = +p[2]; y = +p[3]; }
    else if ((p = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor))) { y = +p[1]; m = +p[2]; d = +p[3]; }
    else return null;
    if (y < 1000 || m < 1 || m > 12 || d < 1 || d > new Date(y, m, 0).getDate()) return null;
    return String(y) + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0');
}

/** Valida antes de alterar resultados; Proyecto admite periodos sin uno o ambos extremos. */
function fntPeriodoProyecto() {
    var ini = document.getElementById('filtro_fecha_ini'), fin = document.getElementById('filtro_fecha_fin');
    ini.setCustomValidity(''); fin.setCustomValidity('');
    var a = fntFechaProyectoISO(ini.value), b = fntFechaProyectoISO(fin.value);
    var invalido = a === null ? ini : (b === null || (a && b && a > b) ? fin : null);
    if (invalido) {
        invalido.setCustomValidity(a !== null && b !== null ? 'La fecha fin debe ser igual o posterior a la fecha inicio.' : 'Ingresa una fecha válida en formato dd/mm/aaaa.');
        invalido.reportValidity(); return null;
    }
    return {fecha_ini: a, fecha_fin: b};
}

/** No inventar USD cuando falta la moneda; conservar los IDs utilizados por el portal. */
function fntMonedaProyecto(registro) {
    if (registro && String(registro.cmoneda || '').trim()) return String(registro.cmoneda).trim();
    return Number(registro && registro.moneda_id) === 1 ? 'MXN' : (Number(registro && registro.moneda_id) === 3 ? 'USD' : 'Sin moneda');
}

/** Cancela el detalle y limpia sus datos, sin cambiar la selección usada para recargar la lista. */
function fntInvalidarDetalleProyecto() {
    versionDetalleProyecto++;
    detalleProyectoListo = false; detalleProyectoEnCurso = false;
    estadoColeccionesProyecto = {seguimientos: 'pendiente', adjuntos: 'pendiente'};
    if (controladorDetalleProyecto) controladorDetalleProyecto.abort();
    [peticionHistorialProyecto, peticionAdjuntosProyecto].forEach(function (p) { if (p && p.abort) p.abort(); });
    historialSeguimientoData = []; adjuntosProyectoData = [];
    $('#timeline_seguimientos, #timeline_adjuntos').empty();
    $('#loading_seguimientos, #loading_adjuntos').addClass('d-none');
    $('#sin_seguimientos, #sin_adjuntos, #containerCoincidencias').addClass('d-none');
    $('#badge_total_seg, #badge_total_adjuntos, #badge_total_partidas').text('—');
    $('#lblOrigenPartidasBadge').text('Sin partidas');
    if (tablePartidasProyecto) tablePartidasProyecto.clear().draw();
}

/** Estado de colección vacío/error diferenciado dentro del contenedor existente. */
function fntEstadoColeccionProyecto(tipo, estado) {
    var seguimiento = tipo === 'seguimientos';
    $('#loading_' + tipo).toggleClass('d-none', estado !== 'cargando');
    $('#sin_' + tipo).toggleClass('d-none', estado !== 'vacio' && estado !== 'error');
    var texto = estado === 'error' ? 'No fue posible consultar ' + (seguimiento ? 'los seguimientos' : 'los archivos adjuntos') + '. Intenta actualizar nuevamente.' :
        (seguimiento ? 'Este proyecto de venta no tiene seguimientos registrados.' : 'Este proyecto de venta no tiene archivos adjuntos registrados en sus etapas.');
    $('#sin_' + tipo).attr('data-estado', estado).attr('role', 'status').find('p').text(texto);
    if (estado === 'vacio' || estado === 'error') $(seguimiento ? '#badge_total_seg' : '#badge_total_adjuntos').text(estado === 'error' ? '—' : '0');
}

/** Carga diferida sin caché entre proyectos/actualizaciones ni peticiones duplicadas. */
function fntCargarColeccionesVisiblesProyecto() {
    if (!detalleProyectoListo || !ventaIdSeleccionado) return;
    if ($('#tab-detalle-seguimiento').hasClass('active') && ['pendiente', 'error'].includes(estadoColeccionesProyecto.seguimientos)) {
        fntCargarHistorialSeguimientos(ventaIdSeleccionado, versionDetalleProyecto);
    }
    if ($('#tab-adjuntos-proyecto').hasClass('active') && ['pendiente', 'error'].includes(estadoColeccionesProyecto.adjuntos)) {
        fntCargarAdjuntosProyecto(ventaIdSeleccionado, versionDetalleProyecto);
    }
}

/** Solo presentación: las variantes bg-* siguen conservando el significado del servidor.
 * La hoja propia define los tonos suaves; no heredar texto de badges del tema general.
 */
function fntHomologarPresentacionProyecto() {
    $('.proyecto-venta-page .badge').removeClass('text-dark text-white');
    $('#tableProyectosVenta .btnVerDetalleProyecto').addClass('lfm-btn-action lfm-btn-action--primary');
    $('#timeline_seguimientos a.btn, #timeline_adjuntos a.btn').removeClass('btn-outline-warning text-dark').addClass('btn-outline-secondary lfm-btn-action lfm-btn-action--quiet');
}

/** Permiso Excel una vez por página, aplicado a cada tabla al terminar su inicialización.
 * No concede permisos ni afecta botones de otros módulos; errores dejan Excel oculto.
 */
function fntPermisoExcelProyecto(api) {
    const tabla = api.table().node();
    tablasPermisoExcelProyecto.add(tabla);
    const aplicar = function () {
        tablasPermisoExcelProyecto.forEach(function (nodo) {
            if (!$.fn.dataTable.isDataTable(nodo)) return;
            $($(nodo).DataTable().buttons('.buttons-excel').nodes()).css('display', permisoExcelProyecto === true ? 'inline-flex' : 'none');
        });
    };
    aplicar();
    if (permisoExcelProyecto !== null || consultaPermisoExcelProyecto || typeof menu === 'undefined' || typeof getFunctionData !== 'function') return;
    consultaPermisoExcelProyecto = true;
    getFunctionData('Permisos', 'getPermisosMod', menu, function (respuesta) {
        permisoExcelProyecto = !!respuesta && Number(respuesta.e) === 1;
        aplicar();
    });
}

/** Refleja la selección también tras paginar/ordenar con filas diferidas. */
function fntMarcarSeleccionProyecto() {
    fntHomologarPresentacionProyecto();
    if (!tableProyectosVenta) return;
    tableProyectosVenta.rows({page: 'current'}).every(function () {
        const nodo = this.node(), dato = this.data();
        if (!nodo) return;
        const seleccionado = ventaIdSeleccionado !== null && String(dato.id) === String(ventaIdSeleccionado);
        $(nodo).toggleClass('selected-row table-primary', seleccionado).attr('aria-selected', seleccionado ? 'true' : 'false');
    });
}

/** Regla compartida lista/resumen: CERRADO = declinado; independiente de la etapa comercial. */
function fntEstadoActivoProyecto(activo) {
    const declinado = String(activo || 'ACTIVO').trim().toUpperCase() === 'CERRADO';
    return {texto: declinado ? 'Declinado' : 'Activo', clase: declinado ? 'bg-danger' : 'bg-success'};
}

/** Folios múltiples: escapar solo la vista; búsqueda, orden y Excel reciben texto plano. */
function fntRenderOrdenesProyecto(data, type) {
    const folios = Array.isArray(data) ? data.map(String) : [];
    if (type !== 'display') return folios.join(', ');
    return folios.length ? folios.map(fntEscapeHtml).join('<br>') : '—';
}

/**
 * Inicializa la configuración de DataTables para el listado de Proyectos de Venta
 */
function fntInicializarTablaProyectos() {
    if (!$.fn.dataTable.ext.search.includes(fntFiltrarIndicadorProyecto)) {
        $.fn.dataTable.ext.search.push(fntFiltrarIndicadorProyecto);
    }

    tableProyectosVenta = LfmTables.create($(tableElement), {
        clearColumnFilters: true,
        initComplete: function () { fntPermisoExcelProyecto(this.api()); },
        // Solo crear nodos de las filas visitadas; búsqueda/orden/Excel conservan todos los datos.
        deferRender: true,
        orderCellsTop: true,
        fixedHeader: true,
        scrollX: "100%",
        destroy: true,
        order: [[2, "desc"]],
        iDisplayLength: 10,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Todos"]
        ],
        dom: "Blfrtip",
        buttons: [
            {
                extend: "excelHtml5",
                autoFilter: true,
                sheetName: "Proyectos de Venta",
                extend: "excel",
                messageTop: "",
                title: "Lista de Proyectos de Venta",
                exportOptions: {
                    columns: ":visible"
                }
            },
            {
                extend: "colvis",
                postfixButtons: ["colvisRestore"]
            }
        ],
        columns: [
            { data: "options", orderable: false, className: "text-center align-middle", width: "90px" },
            {
                data: "activo", defaultContent: "ACTIVO", width: "110px",
                className: "text-center align-middle",
                render: function (data, type) {
                    const estado = fntEstadoActivoProyecto(data);
                    if (type !== 'display') return estado.texto;
                    return '<span class="badge ' + estado.clase + '">' + estado.texto + '</span>';
                }
            },
            {
                data: "proyecto_id",
                className: "text-center align-middle",
                render: function (data, type, row) {
                    return '<span class="fw-bold text-dark">' + fntEscapeHtml((data || "—")) + '</span>';
                }
            },
            {
                data: "fecha_formateada",
                type: 'num',
                className: "text-center align-middle",
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        var iso = fntFechaProyectoISO(String(row.fecha || '').slice(0, 10)) || fntFechaProyectoISO(data);
                        return iso ? Number(iso.replace(/-/g, '')) : 0;
                    }
                    if (type !== 'display') return data || '';
                    return '<span class="text-muted">' + fntEscapeHtml((data || "—")) + '</span>';
                }
            },
            {
                data: "ordenes_compra_cliente", defaultContent: "", width: "180px",
                className: "text-start align-middle",
                render: fntRenderOrdenesProyecto
            },
            {
                data: "folios_ocp_proveedor", defaultContent: "", width: "180px",
                className: "text-start align-middle",
                render: fntRenderOrdenesProyecto
            },
            {
                data: "folios_facturas", defaultContent: "", width: "180px",
                className: "text-start align-middle",
                render: fntRenderOrdenesProyecto
            },
            {
                data: "cliente",
                className: "text-start align-middle",
                render: function (data, type, row) {
                    return '<span class="fw-bold text-primary">' + fntEscapeHtml((data || "—")) + '</span>';
                }
            },
            {
                data: "titulo",
                className: "text-start align-middle",
                render: function (data, type, row) {
                    return '<span class="fw-semibold text-dark">' + fntEscapeHtml((data || "Sin título")) + '</span>';
                }
            },
            {
                data: "vendedor",
                className: "text-start align-middle",
                render: function (data, type, row) {
                    return '<span class="text-secondary">' + fntEscapeHtml((data || "—")) + '</span>';
                }
            },
            {
                data: "monto_formateado",
                type: 'num',
                className: "text-end align-middle",
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') return Number(row.total) || 0;
                    if (type !== 'display') return data || '';
                    return '<span class="fw-bold text-success">' + fntEscapeHtml((data || "$0.00")) + '</span>';
                }
            },
            // 205 px: aproximadamente 60% más que los 128 px del diseño anterior.
            { data: "estatus_badge", className: "text-center align-middle pv-col-estatus", width: "205px", orderable: false }
        ],
        language: typeof idioma_espanol !== "undefined" ? idioma_espanol : {}
    });

    $(tableElement).off('draw.dt.proyectoSeleccion').on('draw.dt.proyectoSeleccion', function () {
        fntMarcarSeleccionProyecto();
        if (tableProyectosVenta) {
            tableProyectosVenta.columns.adjust();
        }
    });
}

/** Una sola definición para tarjetas y filas: los folios ya vienen autorizados del servidor. */
function fntIndicadoresEjecutivosProyecto(proyecto) {
    const activo = fntEstadoActivoProyecto(proyecto.activo).clase !== 'bg-danger';
    const tieneFolios = valores => Array.isArray(valores) && valores.some(v => String(v == null ? '' : v).trim() !== '');
    const occ = tieneFolios(proyecto.ordenes_compra_cliente);
    // Id 5: Cotización cliente elaborada (Pedido Cotizado), sin incluir otras etapas.
    // Esta definición compartida mantiene iguales el conteo de la tarjeta y su filtro.
    return {activos: activo, por_colocar: activo && Number(proyecto.estatus_proyecto_id) === 5,
        por_facturar: activo && occ && !tieneFolios(proyecto.folios_facturas), declinados: !activo};
}

/** DataTables comparte sus predicados: limitar este filtro exclusivamente a la lista de proyectos. */
function fntFiltrarIndicadorProyecto(settings, datosBusqueda, indice, fila) {
    if (settings.nTable.id !== 'tableProyectosVenta' || !filtroEjecutivoProyecto) return true;
    const proyecto = fila || (settings.aoData[indice] && settings.aoData[indice]._aData);
    return !!proyecto && fntIndicadoresEjecutivosProyecto(proyecto)[filtroEjecutivoProyecto] === true;
}

/** Marca la tarjeta sin cambiar sus cifras; comunica el filtro vigente y permite retirarlo. */
function fntSincronizarFiltroEjecutivoProyecto() {
    const nombres = {activos: 'Proyectos activos', por_colocar: 'Por colocar', por_facturar: 'Por facturar', declinados: 'Declinados'};
    document.querySelectorAll('#pv_panel_kpis [data-kpi-proyecto]').forEach(function (boton) {
        const elegido = boton.dataset.kpiProyecto === filtroEjecutivoProyecto;
        boton.disabled = !resumenEjecutivoProyectoListo;
        boton.setAttribute('aria-pressed', elegido ? 'true' : 'false');
        boton.closest('.executive-kpi').classList.toggle('is-selected', elegido);
    });
    const aviso = document.getElementById('pv_aviso_filtro_ejecutivo');
    aviso.classList.toggle('d-none', !filtroEjecutivoProyecto);
    document.getElementById('pv_texto_filtro_ejecutivo').textContent = filtroEjecutivoProyecto
        ? 'Filtro ejecutivo: ' + nombres[filtroEjecutivoProyecto] + '. Se conservan las búsquedas de la tabla.' : '';
}

/** Solo redibuja la tabla; no consulta datos ni altera su búsqueda/visibilidad/selección. */
function fntAplicarFiltroEjecutivoProyecto(indicador) {
    if (!['', 'activos', 'por_colocar', 'por_facturar', 'declinados'].includes(indicador)) return;
    if (indicador && !resumenEjecutivoProyectoListo) return;
    filtroEjecutivoProyecto = indicador;
    fntSincronizarFiltroEjecutivoProyecto();
    if (tableProyectosVenta) tableProyectosVenta.draw();
}

/** Conteos de proyectos únicos del resultado autorizado completo, no de la página visible.
 * Por colocar y por facturar son subconjuntos de activos. No se suman importes ni monedas.
 * Las OCC/facturas del payload ya cumplen sus reglas de finalización/timbrado en servidor.
 */
function fntCalcularResumenEjecutivoProyecto(datos) {
    const proyectos = new Map();
    datos.forEach(function (p) { if (p && p.id != null) proyectos.set(String(p.id), p); });
    const conteos = {total: proyectos.size, activos: 0, por_colocar: 0, por_facturar: 0, declinados: 0};
    proyectos.forEach(function (p) {
        const indicadores = fntIndicadoresEjecutivosProyecto(p);
        Object.keys(indicadores).forEach(function (clave) { if (indicadores[clave]) conteos[clave]++; });
    });
    return conteos;
}

/** Carga/error no equivalen a ceros. Solo el listado actualiza estos indicadores. */
function fntActualizarResumenEjecutivoProyecto(estado, datos = []) {
    const panel = document.getElementById('pv_panel_kpis');
    if (!panel) return;
    panel.setAttribute('aria-busy', estado === 'cargando' ? 'true' : 'false');
    resumenEjecutivoProyectoListo = estado === 'ok';
    fntSincronizarFiltroEjecutivoProyecto();
    const conteos = estado === 'ok' ? fntCalcularResumenEjecutivoProyecto(datos) : null;
    const numero = new Intl.NumberFormat('es-MX');
    ['activos', 'por_colocar', 'por_facturar', 'declinados'].forEach(function (clave) {
        document.getElementById('pv_kpi_' + clave).textContent = conteos ? numero.format(conteos[clave]) : '—';
    });
    document.getElementById('pv_kpi_total').textContent = conteos
        ? numero.format(conteos.total) + (conteos.total === 1 ? ' proyecto consultado' : ' proyectos consultados')
        : (estado === 'cargando' ? 'Consultando proyectos…' : 'Consulta no disponible');
    document.getElementById('pv_kpi_nota').textContent = conteos
        ? (conteos.total ? 'Por colocar y por facturar son subconjuntos de activos. ' : 'No hay proyectos para los filtros consultados. ')
            + 'Pulsa una tarjeta para filtrar. Los conteos corresponden al período y buscador superior, sin reducirse por los filtros de la tabla.'
        : (estado === 'cargando' ? 'Consultando los resultados del período y la búsqueda.' : 'No fue posible calcular el resumen. Actualiza la consulta para intentar de nuevo.');
}

/**
 * Carga (o recarga) el DataTable de Proyectos de Venta vía AJAX
 */
function fntCargarTablaProyectos() {
    const periodo = fntPeriodoProyecto();
    if (!periodo) return;
    const version = ++versionListadoProyecto;
    fntActualizarResumenEjecutivoProyecto('cargando');
    if (peticionListadoProyecto && peticionListadoProyecto.abort) peticionListadoProyecto.abort();
    fntInvalidarDetalleProyecto();
    fntMostrarEstadoUI('loading');
    if (tableProyectosVenta) tableProyectosVenta.clear().draw();
    peticionListadoProyecto = $.ajax({
        type: 'POST', url: base_url + '/seguimiento/getProyectosVentaDatatable', dataType: 'json',
        // El backend ya busca en folio, título, cliente y vendedor; conserva su alcance por usuario.
        data: {fecha_ini: periodo.fecha_ini, fecha_fin: periodo.fecha_fin, busqueda: $('#txtProyectoId').val().trim()},
        success: function (data) {
            if (version !== versionListadoProyecto) return;
            if (!Array.isArray(data)) {
                fntActualizarResumenEjecutivoProyecto('error');
                ventaIdSeleccionado = null; fntMostrarEstadoUI('error', 'La consulta de proyectos no pudo completarse.'); return;
            }
            fntActualizarResumenEjecutivoProyecto('ok', data);
            if (tableProyectosVenta) tableProyectosVenta.clear().rows.add(data).draw();
            const candidatos = filtroEjecutivoProyecto && tableProyectosVenta
                ? tableProyectosVenta.rows({search: 'applied'}).data().toArray() : data;
            if (candidatos.length) {
                const previo = candidatos.find(item => String(item.id) === String(ventaIdSeleccionado));
                fntCargarDetalleProyecto(previo ? previo.id : candidatos[0].id, false);
            } else {
                ventaIdSeleccionado = null;
                fntMostrarEstadoUI('vacio', data.length ? 'No hay proyectos para la combinación de filtros de la lista.' : 'No hay proyectos para los filtros consultados.');
                fntEstadoColeccionProyecto('seguimientos', 'vacio'); fntEstadoColeccionProyecto('adjuntos', 'vacio');
                $('#badge_total_partidas').text(0);
            }
        },
        error: function (xhr, status) {
            if (version !== versionListadoProyecto || status === 'abort') return;
            fntActualizarResumenEjecutivoProyecto('error');
            ventaIdSeleccionado = null;
            fntMostrarEstadoUI('error', (xhr.responseJSON && (xhr.responseJSON.mensaje || xhr.responseJSON.msg)) || 'No fue posible consultar los proyectos. Intenta nuevamente.');
        },
        complete: function () {
            if (version === versionListadoProyecto) $('.loading-panel-showing').removeClass('loading-panel-showing');
        }
    });
}

/**
 * Consulta cabecera/checklist/partidas; las colecciones se consultan al abrir su pestaña.
 * Una segunda pulsación del mismo proyecto pendiente reutiliza su consulta, sin caché entre actualizaciones.
 */
function fntCargarDetalleProyecto(ventaId, scrollTo = true) {
    if (!ventaId) return;
    if (detalleProyectoEnCurso && String(ventaId) === String(ventaIdSeleccionado)) {
        scrollPendienteProyecto = scrollPendienteProyecto || scrollTo;
        return; // Una segunda pulsación no vuelve a ejecutar el mismo detalle pendiente.
    }
    fntInvalidarDetalleProyecto();
    detalleProyectoEnCurso = true; scrollPendienteProyecto = scrollTo;
    const version = versionDetalleProyecto;
    controladorDetalleProyecto = new AbortController();
    ventaIdSeleccionado = ventaId;
    fntMarcarSeleccionProyecto();
    fntMostrarEstadoUI("loading");

    const formData = new FormData();
    formData.append("venta_id", ventaId);

    fetch(base_url + "/seguimiento/buscarProyectoVenta", {
        method: "POST",
        signal: controladorDetalleProyecto.signal,
        body: formData
    })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.msg || data.mensaje || 'No fue posible consultar el proyecto.');
            return data;
        })
        .then(data => {
            if (version !== versionDetalleProyecto || String(ventaId) !== String(ventaIdSeleccionado)) return;
            if (data.status) {
                if (!data.data || String(data.data.id) !== String(ventaId)) throw new Error('La respuesta no corresponde al proyecto seleccionado.');
                // Manejo de coincidencias en el select secundario si aplica
                const containerCoincidencias = document.getElementById("containerCoincidencias");
                const selectCoincidencias = document.getElementById("selectProyectosCoincidentes");

                if (data.proyectos && data.proyectos.length > 1) {
                    selectCoincidencias.innerHTML = "";
                    data.proyectos.forEach(p => {
                        const opt = document.createElement("option");
                        opt.value = p.id;
                        opt.textContent = `${p.proyecto_id} - ${p.cliente} (${p.fecha_formateada || p.fecha})`;
                        if (p.id == data.data.id) {
                            opt.selected = true;
                        }
                        selectCoincidencias.appendChild(opt);
                    });
                    containerCoincidencias.classList.remove("d-none");
                } else {
                    if (containerCoincidencias) containerCoincidencias.classList.add("d-none");
                }

                // Renderizar Resumen, Checklist de Evaluación, Seguimiento, Adjuntos y Partidas del Proyecto
                fntRenderResumenProyecto(data.data);
                fntRenderChecklistProceso(data.checklist);
                fntRenderPartidasProyecto(data.partidas);
                detalleProyectoListo = true;
                fntEstadoColeccionProyecto('seguimientos', 'pendiente');
                fntEstadoColeccionProyecto('adjuntos', 'pendiente');
                fntMostrarEstadoUI("resultado");
                fntCargarColeccionesVisiblesProyecto();

                // Desplazamiento suave a la sección de detalle si se solicitó explícitamente
                if (scrollPendienteProyecto) {
                    const elDetalle = document.getElementById("panel_detalle_proyecto");
                    if (elDetalle) {
                        elDetalle.scrollIntoView({ behavior: "smooth", block: "start" });
                    }
                }
            } else {
                fntMostrarEstadoUI("vacio", data.msg || "No se encontró el proyecto.");
                fntMostrarAlerta(data.msg || "No se pudo cargar el detalle del proyecto.", "error");
            }
        })
        .catch(err => {
            if (version !== versionDetalleProyecto || err.name === 'AbortError') return;
            fntMostrarEstadoUI('error', err.message || 'No fue posible consultar el proyecto. Intenta nuevamente.');
        }).finally(function () {
            if (version === versionDetalleProyecto) detalleProyectoEnCurso = false;
        });
}

/**
 * Renderiza la tarjeta de información general del proyecto
 */
function fntRenderResumenProyecto(p) {
    if (!p) return;

    document.getElementById("lblProyectoId").textContent = p.proyecto_id || "PV-N/A";
    document.getElementById("lblTituloProyecto").textContent = p.titulo || "Sin título especificado";
    const estadoActivo = fntEstadoActivoProyecto(p.activo);
    const badgeActivo = document.getElementById('lblEstadoActivoProyecto');
    badgeActivo.className = 'badge ' + estadoActivo.clase;
    badgeActivo.textContent = estadoActivo.texto;


    // Badge de estatus actual
    const badgeEstatus = document.getElementById("lblEstatusBadge");
    badgeEstatus.textContent = p.estatus_proyecto || "Desconocido";

    const stId = parseInt(p.estatus_proyecto_id || 0);
    if (stId === 2) {
        badgeEstatus.className = "badge bg-danger fs-12 px-3 py-2";
    } else if (stId >= 7) {
        badgeEstatus.className = "badge bg-success fs-12 px-3 py-2";
    } else if (stId >= 5) {
        badgeEstatus.className = "badge bg-info text-dark fs-12 px-3 py-2";
    } else {
        badgeEstatus.className = "badge bg-warning text-dark fs-12 px-3 py-2";
    }

    document.getElementById("lblCliente").textContent = p.cliente || "Sin cliente asignado";
    document.getElementById("lblCliente").title = p.cliente || "";

    document.getElementById("lblVendedor").textContent = p.vendedor || "Sin vendedor asignado";
    document.getElementById("lblVendedor").title = p.vendedor || "";

    document.getElementById("lblFecha").textContent = p.fecha_formateada || p.fecha || "—";

    const moneda = fntMonedaProyecto(p);
    const total = parseFloat(p.total || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById("lblMontoTotal").textContent = `${moneda} $${total}`;
    // Las filas se crean como texto: conservar folios múltiples sin interpretar HTML.
    [['lblOrdenesCliente', p.ordenes_compra_cliente], ['lblFoliosProveedor', p.folios_ocp_proveedor], ['lblFoliosFacturas', p.folios_facturas]].forEach(function (item) {
        const destino = document.getElementById(item[0]);
        destino.replaceChildren();
        const folios = Array.isArray(item[1]) ? item[1] : [];
        if (!folios.length) { destino.textContent = '—'; return; }
        folios.forEach(function (folio) {
            const linea = document.createElement('div');
            linea.textContent = String(folio);
            destino.appendChild(linea);
        });
    });

}

/**
 * Renderiza las etapas configuradas y distingue cancelación de avance histórico.
 */
function fntRenderChecklistProceso(checklist) {
    const container = document.getElementById("containerChecklistItems");
    if (!container || !Array.isArray(checklist)) return;

    let completadosCount = 0;
    let html = "";

    checklist.forEach((step, index) => {
        const numPaso = index + 1;
        const isCompletado = step.completado === true;
        const isCancelado = step.is_cancelado === true;

        if (isCompletado) {
            completadosCount++;
        }

        let badgeClass = "badge-pending";
        let cardClass = "pending";
        let statusBadgeHtml = '<span class="badge bg-light text-dark border px-2 py-1"><i class="fa-regular fa-clock me-1 text-warning"></i>PENDIENTE</span>';

        if (isCancelado) {
            badgeClass = "badge-canceled";
            cardClass = "canceled";
            statusBadgeHtml = '<span class="badge bg-danger px-2 py-1"><i class="fa-regular fa-circle-xmark me-1"></i>PROYECTO CANCELADO</span>';
        } else if (isCompletado) {
            badgeClass = "badge-completed";
            cardClass = "completed";
            statusBadgeHtml = '<span class="badge bg-success px-2 py-1"><i class="fa-regular fa-circle-check me-1"></i>COMPLETADO</span>';
        }

        // Generar HTML de registros vinculados si existen
        let registrosHtml = "";
        if (step.registros && step.registros.length > 0) {
            const showDescuento = !(numPaso === 2 || numPaso === 3 || step.id === 3 || step.id === 4);

            registrosHtml = `
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered align-middle bg-white mb-0 fs-12">
                        <thead class="table-light">
                            <tr>
                                <th>Folio / Documento</th>
                                <th>Fecha</th>
                                <th class="text-end">Subtotal</th>
                                ${showDescuento ? '<th class="text-end">Descuento</th>' : ''}
                                <th class="text-end">IVA</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Moneda</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            step.registros.forEach(r => {
                const folioDoc = r.num_orden_compra || r.folio_cotizacion || r.folio_ocp || r.proyecto_id || `ID #${r.id}`;
                const fechaDoc = r.fecha_formateada || r.fecha_pedido || r.fecha || "—";
                const sub = parseFloat(r.subtotal || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const desc = parseFloat(r.descuento || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const iva = parseFloat(r.iva || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const tot = parseFloat(r.total || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const monedaDoc = fntMonedaProyecto(r);
                const restringido = step.importes_restringidos === true || r.importes_restringidos === true;

                registrosHtml += `
                    <tr>
                        <td class="fw-bold text-primary">${fntEscapeHtml(folioDoc)}</td>
                        <td>${fntEscapeHtml(fechaDoc)}</td>
                        <td class="text-end">${restringido ? '- - -' : '$' + sub}</td>
                        ${showDescuento ? `<td class="text-end text-danger">${restringido ? '- - -' : '$' + desc}</td>` : ''}
                        <td class="text-end">${restringido ? '- - -' : '$' + iva}</td>
                        <td class="text-end fw-bold text-dark">${restringido ? '- - -' : '$' + tot}</td>
                        <td class="text-center"><span class="badge bg-secondary px-2 py-1">${fntEscapeHtml(monedaDoc)}</span></td>
                    </tr>
                `;
            });

            registrosHtml += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        html += `
            <div class="timeline-item">
                <div class="timeline-badge ${badgeClass}">${numPaso}</div>
                <div class="step-card ${cardClass} card p-3 shadow-2xs">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="fw-bold text-dark mb-0 fs-15">
                            <span class="text-secondary me-1">Paso ${numPaso}:</span> ${fntEscapeHtml(step.nombre)}
                        </h5>
                        <div>${statusBadgeHtml}</div>
                    </div>
                    <p class="text-muted mb-0 fs-13 mt-2">
                        <i class="fa-regular fa-circle-info me-1"></i>${fntEscapeHtml(step.mensaje || "Sin detalles disponibles.")}
                    </p>
                    ${registrosHtml}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Calcular progreso
    const totalSteps = checklist.length;
    const porcentaje = totalSteps ? Math.round((completadosCount / totalSteps) * 100) : 0;
    const cancelado = checklist.some(step => step.is_cancelado === true);

    const bar = document.getElementById("barProgresoChecklist");
    const lblPorcentaje = document.getElementById("lblProgresoPorcentaje");

    if (bar) {
        bar.style.width = `${porcentaje}%`;
        bar.setAttribute("aria-valuenow", porcentaje);
    }
    if (lblPorcentaje) {
        lblPorcentaje.textContent = cancelado ? `Proyecto cancelado · ${completadosCount} de ${totalSteps} etapas con documentos (${porcentaje}% histórico)` : `${completadosCount} de ${totalSteps} etapas completadas (${porcentaje}%)`;
    }
}

/**
 * Selecciona un proyecto cuando existen múltiples coincidencias en el select secundario
 */
function fntSeleccionarProyecto(ventaId) {
    if (!ventaId) return;
    fntCargarDetalleProyecto(ventaId, false);
}

/**
 * Limpia los filtros y restablece al estado por defecto
 */
function fntLimpiarFiltro() {
    filtroEjecutivoProyecto = '';
    fntSincronizarFiltroEjecutivoProyecto();
    fntRestablecerFechasProyecto();
    $("#txtProyectoId").val("");

    ventaIdSeleccionado = null;
    fntCargarTablaProyectos();
}

/**
 * Controla la visibilidad de los páneles de la interfaz
 */
function fntMostrarEstadoUI(estado, mensaje) {
    fntHomologarPresentacionProyecto();
    const pPlaceholder = document.getElementById("panelPlaceholder");
    const pLoading = document.getElementById("panelLoading");
    if (pPlaceholder) {
        pPlaceholder.setAttribute('data-estado', estado);
        pPlaceholder.setAttribute('role', 'status');
        const titulo = pPlaceholder.querySelector('h4'), texto = pPlaceholder.querySelector('p');
        if (titulo) titulo.textContent = estado === 'error' ? 'No fue posible completar la consulta' : (estado === 'vacio' ? 'Sin resultados' : 'Consulta de Proyecto de Venta');
        if (texto) texto.textContent = mensaje || 'Seleccione un proyecto del listado o utilice los filtros para consultar su detalle.';
    }
    const cResumen = document.getElementById("cardResumenProyecto");
    const cChecklist = document.getElementById("cardChecklistProceso");
    // El estado también debe ser legible si el usuario permanece en otra pestaña.
    if (estado === 'loading' || estado === 'error' || estado === 'vacio') {
        const estadoColeccion = estado === 'loading' ? 'cargando' : estado;
        fntEstadoColeccionProyecto('seguimientos', estadoColeccion);
        fntEstadoColeccionProyecto('adjuntos', estadoColeccion);
        $('#lblOrigenPartidasBadge').text(estado === 'loading' ? 'Consultando partidas…' : (estado === 'error' ? 'Consulta no disponible' : 'Sin partidas'));
        $('#badge_total_partidas').text(estado === 'vacio' ? '0' : '—');
    }

    if (pPlaceholder) pPlaceholder.classList.add("d-none");
    if (pLoading) pLoading.classList.add("d-none");
    if (cResumen) cResumen.classList.add("d-none");
    if (cChecklist) cChecklist.classList.add("d-none");

    switch (estado) {
        case "loading":
            if (pLoading) pLoading.classList.remove("d-none");
            break;
        case "resultado":
            if (cResumen) cResumen.classList.remove("d-none");
            if (cChecklist) cChecklist.classList.remove("d-none");
            break;
        case "placeholder":
        default:
            if (pPlaceholder) pPlaceholder.classList.remove("d-none");
            break;
    }
}

/**
 * Muestra mensaje de alerta modal del sistema
 */
function fntMostrarAlerta(mensaje, tipo = "error") {
    var responseObj = {
        mostrar_mensaje: true,
        tiempo: 4000,
        mensaje: mensaje
    };

    if (typeof alerta_error === "function" && tipo === "error") {
        alerta_error(responseObj, "");
    } else if (typeof alerta_warning_only === "function" && (tipo === "warning" || tipo === "info")) {
        alerta_warning_only(responseObj, "");
    } else if (typeof alerta_warning === "function" && (tipo === "warning" || tipo === "info")) {
        alerta_warning(responseObj, "");
    } else if (typeof mensajeAlertaModal === "function") {
        mensajeAlertaModal({
            icon: tipo,
            timer: 4000,
            title: "¡Atención!",
            text: mensaje,
            textButton: "Cerrar"
        });
    } else if (typeof swal === "function") {
        swal("¡Atención!", mensaje, tipo);
    } else {
        alert(mensaje);
    }
}

/**
 * Carga el historial de seguimientos (bitácora) para el proyecto seleccionado
 */
function fntCargarHistorialSeguimientos(ventaId, version = versionDetalleProyecto) {
    if (estadoColeccionesProyecto.seguimientos === 'cargando') return;
    estadoColeccionesProyecto.seguimientos = 'cargando';
    historialSeguimientoData = [];
    $('#timeline_seguimientos').empty();
    fntEstadoColeccionProyecto('seguimientos', 'cargando');
    peticionHistorialProyecto = $.ajax({
        type: 'POST', url: base_url + '/seguimiento/getHistorialSeguimiento', data: {venta_id: ventaId}, dataType: 'json',
        success: function (resp) {
            if (version !== versionDetalleProyecto || String(ventaId) !== String(ventaIdSeleccionado)) return;
            if (!resp || resp.respuesta !== 'ok' || !Array.isArray(resp.data)) { estadoColeccionesProyecto.seguimientos = 'error'; fntEstadoColeccionProyecto('seguimientos', 'error'); return; }
            estadoColeccionesProyecto.seguimientos = 'listo';
            historialSeguimientoData = resp.data;
            fntEstadoColeccionProyecto('seguimientos', resp.data.length ? 'datos' : 'vacio');
            $('#badge_total_seg').text(resp.data.length);
            if (resp.data.length) fntRenderizarTimelinePagina(1);
        },
        error: function (xhr, status) {
            if (version !== versionDetalleProyecto || String(ventaId) !== String(ventaIdSeleccionado) || status === 'abort') return;
            estadoColeccionesProyecto.seguimientos = 'error';
            fntEstadoColeccionProyecto('seguimientos', 'error');
        }
    });
}

/**
 * Renderiza una página específica del historial de seguimientos en la pestaña Detalle de Seguimiento
 */
function fntRenderizarTimelinePagina(pagina) {
    if (!historialSeguimientoData || historialSeguimientoData.length === 0) {
        $("#sin_seguimientos").removeClass("d-none");
        $("#timeline_seguimientos").empty();
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

        // Contenido de la nota
        html += '<div class="card border p-0 flex-grow-1 mb-0 shadow-sm">';
        html += '<div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background:#f8f9fa;">';
        html += '<span class="fw-semibold fs-12 text-primary">';
        html += '<i class="fa-regular fa-user-circle me-1"></i>';
        html += fntEscapeHtml(seg.nombre_usuario || 'Sistema');
        html += '</span>';
        html += '<span class="fs-11 text-muted">';
        html += '<i class="fa-regular fa-calendar me-1"></i>';
        html += fntEscapeHtml(seg.fecha_formateada || seg.fecha || '—');
        html += '</span>';
        html += '</div>';
        html += '<div class="card-body py-2 px-3">';
        html += '<p class="mb-0 fs-13 text-muted" style="white-space:pre-wrap;">';
        html += fntEscapeHtml(seg.notas || 'Sin nota registrada.');
        html += '</p>';

        if (seg.archivo && seg.archivo.trim() !== '') {
            var fileName = seg.archivo.trim();
            var fileUrl = fntEscapeHtml(base_url + '/seguimiento/getArchivoProyecto?venta_id=' + encodeURIComponent(ventaIdSeleccionado) + '&archivo=' + encodeURIComponent(fileName));
            var fileExt = fileName.split('.').pop().toLowerCase();

            var fileIconClass = 'fa-file-lines text-secondary';
            if (['pdf'].includes(fileExt)) {
                fileIconClass = 'fa-file-pdf text-danger';
            } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExt)) {
                fileIconClass = 'fa-file-image text-info';
            } else if (['doc', 'docx'].includes(fileExt)) {
                fileIconClass = 'fa-file-word text-primary';
            } else if (['xls', 'xlsx', 'csv'].includes(fileExt)) {
                fileIconClass = 'fa-file-excel text-success';
            } else if (['zip', 'rar', '7z'].includes(fileExt)) {
                fileIconClass = 'fa-file-archive text-warning';
            }

            html += '<div class="mt-2 pt-2 border-top d-flex align-items-center flex-wrap gap-2">';
            html += '<span class="fs-12 fw-semibold text-muted d-inline-flex align-items-center">';
            html += '<i class="fa-regular fa-paperclip me-1 text-primary"></i>Adjunto:';
            html += '</span>';
            html += '<a href="' + fileUrl + '" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 py-1 px-2 text-truncate" style="max-width: 100%; font-size: 12px; border-radius: 6px;" title="Abrir ' + fntEscapeHtml(fileName) + '">';
            html += '<i class="fa-regular ' + fileIconClass + ' me-1"></i>';
            html += '<span class="text-truncate" style="max-width: 280px;">' + fntEscapeHtml(fileName) + '</span>';
            html += '<i class="fa-solid fa-arrow-up-right-from-square fs-10 ms-1 text-muted"></i>';
            html += '</a>';
            html += '</div>';
        }

        html += '</div>';
        html += '</div>';
        html += '</div>';
    });

    html += '</div>';

    // Paginación si hay más de 1 página
    if (totalPaginas > 1) {
        html += '<div class="d-flex flex-column flex-sm-row justify-content-between align-items-center pt-3 mt-2 border-top gap-2">';
        html += '<span class="fs-12 text-muted">';
        html += 'Mostrando <strong>' + (inicio + 1) + '</strong> a <strong>' + fin + '</strong> de <strong>' + totalRegistros + '</strong> seguimientos';
        html += '</span>';

        html += '<nav aria-label="Navegación del timeline">';
        html += '<ul class="pagination pagination-sm mb-0">';

        var prevDisabled = (pagina === 1) ? ' disabled' : '';
        html += '<li class="page-item' + prevDisabled + '">';
        html += '<a class="page-link btn-pag-timeline" href="javascript:void(0);" data-page="' + (pagina - 1) + '" aria-label="Anterior">';
        html += '<i class="fa-solid fa-chevron-left"></i>';
        html += '</a>';
        html += '</li>';

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
    fntHomologarPresentacionProyecto();
}

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

function fntEscapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Carga los archivos adjuntos vinculados al proyecto de venta de las 6 tablas
 */
function fntCargarAdjuntosProyecto(ventaId, version = versionDetalleProyecto) {
    if (estadoColeccionesProyecto.adjuntos === 'cargando') return;
    estadoColeccionesProyecto.adjuntos = 'cargando';
    adjuntosProyectoData = [];
    $('#timeline_adjuntos').empty();
    fntEstadoColeccionProyecto('adjuntos', 'cargando');
    peticionAdjuntosProyecto = $.ajax({
        type: 'POST', url: base_url + '/seguimiento/getAdjuntosProyecto', data: {venta_id: ventaId}, dataType: 'json',
        success: function (resp) {
            if (version !== versionDetalleProyecto || String(ventaId) !== String(ventaIdSeleccionado)) return;
            if (!resp || resp.respuesta !== 'ok' || !Array.isArray(resp.data)) { estadoColeccionesProyecto.adjuntos = 'error'; fntEstadoColeccionProyecto('adjuntos', 'error'); return; }
            estadoColeccionesProyecto.adjuntos = 'listo';
            adjuntosProyectoData = resp.data;
            fntEstadoColeccionProyecto('adjuntos', resp.data.length ? 'datos' : 'vacio');
            $('#badge_total_adjuntos').text(resp.data.length);
            if (resp.data.length) fntRenderizarTimelineAdjuntosPagina(1);
        },
        error: function (xhr, status) {
            if (version !== versionDetalleProyecto || String(ventaId) !== String(ventaIdSeleccionado) || status === 'abort') return;
            estadoColeccionesProyecto.adjuntos = 'error';
            fntEstadoColeccionProyecto('adjuntos', 'error');
        }
    });
}

/**
 * Renderiza la línea del tiempo de archivos adjuntos del proyecto en Tab 3
 */
function fntRenderizarTimelineAdjuntosPagina(pagina) {
    if (!adjuntosProyectoData || adjuntosProyectoData.length === 0) {
        $("#sin_adjuntos").removeClass("d-none");
        $("#timeline_adjuntos").empty();
        return;
    }

    var totalRegistros = adjuntosProyectoData.length;
    var totalPaginas = Math.ceil(totalRegistros / registrosPorPaginaAdjuntos);

    if (pagina < 1) pagina = 1;
    if (pagina > totalPaginas) pagina = totalPaginas;

    paginaActualAdjuntos = pagina;

    var inicio = (pagina - 1) * registrosPorPaginaAdjuntos;
    var fin = Math.min(inicio + registrosPorPaginaAdjuntos, totalRegistros);
    var itemsPagina = adjuntosProyectoData.slice(inicio, fin);

    var html = '<div class="timeline-adjuntos">';

    itemsPagina.forEach(function (adj, indexInPage) {
        var fileName = (adj.archivo || '').trim();
        var fileUrl = fntEscapeHtml(base_url + '/seguimiento/getArchivoProyecto?venta_id=' + encodeURIComponent(ventaIdSeleccionado) + '&archivo=' + encodeURIComponent(fileName));
        var fileExt = fileName.split('.').pop().toLowerCase();

        var fileIconClass = 'fa-file-lines text-warning';
        if (['pdf'].includes(fileExt)) {
            fileIconClass = 'fa-file-pdf text-danger';
        } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExt)) {
            fileIconClass = 'fa-file-image text-info';
        } else if (['doc', 'docx'].includes(fileExt)) {
            fileIconClass = 'fa-file-word text-primary';
        } else if (['xls', 'xlsx', 'csv'].includes(fileExt)) {
            fileIconClass = 'fa-file-excel text-success';
        } else if (['zip', 'rar', '7z'].includes(fileExt)) {
            fileIconClass = 'fa-file-archive text-warning';
        }

        var badgeBg = adj.badge_color || 'warning';

        html += '<div class="d-flex mb-3 adj-item" style="gap: 12px;">';

        // Icono lateral (círculo ámbar/warning con icono del tipo de archivo)
        html += '<div class="d-flex flex-column align-items-center" style="min-width:32px;">';
        html += '<div class="rounded-circle d-flex align-items-center justify-content-center border border-warning bg-warning-subtle text-warning shadow-2xs" style="width:34px;height:34px;">';
        html += '<i class="fa-regular ' + fileIconClass + ' fs-13"></i>';
        html += '</div>';
        if (indexInPage < itemsPagina.length - 1) {
            html += '<div style="flex:1;width:2px;background:#e9ecef;margin:4px auto;"></div>';
        }
        html += '</div>';

        // Tarjeta con diseño diferenciado
        html += '<div class="card border border-warning-subtle p-0 flex-grow-1 mb-0 shadow-sm rounded-3 overflow-hidden">';
        
        // Header de la tarjeta con Badge del origen de la tabla y datos de registro
        html += '<div class="card-header py-2 px-3 d-flex justify-content-between align-items-center bg-light-subtle" style="border-bottom: 1px solid #f1f3f5;">';
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<span class="badge bg-' + badgeBg + ' fs-11 px-2 py-1"><i class="fa-regular fa-paperclip me-1"></i>' + fntEscapeHtml(adj.origen_etiqueta) + '</span>';
        if (adj.nombre_usuario && adj.nombre_usuario.trim() !== '') {
            html += '<span class="fw-semibold fs-12 text-muted ms-1"><i class="fa-regular fa-user me-1"></i>' + fntEscapeHtml(adj.nombre_usuario) + '</span>';
        }
        html += '</div>';

        if (adj.fecha_formateada && adj.fecha_formateada.trim() !== '') {
            html += '<span class="fs-11 text-muted"><i class="fa-regular fa-calendar me-1"></i>' + fntEscapeHtml(adj.fecha_formateada) + '</span>';
        } else {
            html += '<span class="fs-11 text-muted-subtle"><i class="fa-regular fa-folder me-1"></i>Etapa del Proyecto</span>';
        }
        html += '</div>';

        // Cuerpo con Comentarios (si existen) y Botón del Archivo Adjunto
        html += '<div class="card-body py-3 px-3 bg-white">';

        if (adj.comentarios && adj.comentarios.trim() !== '') {
            html += '<div class="mb-2 p-2 bg-light rounded border-start border-3 border-warning fs-13 text-dark">';
            html += '<small class="text-muted d-block fw-semibold mb-1 fs-11"><i class="fa-regular fa-comment-dots me-1"></i>Comentarios / Notas:</small>';
            html += '<p class="mb-0 text-secondary" style="white-space:pre-wrap;">' + fntEscapeHtml(adj.comentarios) + '</p>';
            html += '</div>';
        }

        html += '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1">';
        html += '<div class="d-flex align-items-center gap-2 overflow-hidden" style="max-width: 75%;">';
        html += '<i class="fa-regular ' + fileIconClass + ' fs-5"></i>';
        html += '<span class="fw-bold text-dark fs-13 text-truncate" title="' + fntEscapeHtml(fileName) + '">' + fntEscapeHtml(fileName) + '</span>';
        html += '</div>';

        html += '<a href="' + fileUrl + '" target="_blank" class="btn btn-sm btn-outline-warning text-dark font-semibold d-inline-flex align-items-center gap-1.5 py-1 px-3 rounded-2 shadow-2xs" title="Ver / Descargar ' + fntEscapeHtml(fileName) + '">';
        html += '<i class="fa-solid fa-arrow-down-to-bracket text-warning"></i>';
        html += '<span>Descargar Archivo</span>';
        html += '</a>';
        html += '</div>';

        html += '</div>'; // .card-body
        html += '</div>'; // .card
        html += '</div>'; // .adj-item
    });

    html += '</div>'; // .timeline-adjuntos

    // Paginación si hay más de 1 página
    if (totalPaginas > 1) {
        html += '<div class="d-flex flex-column flex-sm-row justify-content-between align-items-center pt-3 mt-2 border-top gap-2">';
        html += '<span class="fs-12 text-muted">';
        html += 'Mostrando <strong>' + (inicio + 1) + '</strong> a <strong>' + fin + '</strong> de <strong>' + totalRegistros + '</strong> archivos adjuntos';
        html += '</span>';

        html += '<nav aria-label="Navegación de adjuntos">';
        html += '<ul class="pagination pagination-sm mb-0">';

        var prevDisabled = (pagina === 1) ? ' disabled' : '';
        html += '<li class="page-item' + prevDisabled + '">';
        html += '<a class="page-link btn-pag-adjuntos" href="javascript:void(0);" data-page="' + (pagina - 1) + '" aria-label="Anterior">';
        html += '<i class="fa-solid fa-chevron-left"></i>';
        html += '</a>';
        html += '</li>';

        var range = fntObtenerRangoPaginas(pagina, totalPaginas);
        range.forEach(function (p) {
            if (p === '...') {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            } else {
                var activeClass = (p === pagina) ? ' active' : '';
                html += '<li class="page-item' + activeClass + '">';
                html += '<a class="page-link btn-pag-adjuntos" href="javascript:void(0);" data-page="' + p + '">' + p + '</a>';
                html += '</li>';
            }
        });

        var nextDisabled = (pagina === totalPaginas) ? ' disabled' : '';
        html += '<li class="page-item' + nextDisabled + '">';
        html += '<a class="page-link btn-pag-adjuntos" href="javascript:void(0);" data-page="' + (pagina + 1) + '" aria-label="Siguiente">';
        html += '<i class="fa-solid fa-chevron-right"></i>';
        html += '</a>';
        html += '</li>';

        html += '</ul>';
        html += '</nav>';
        html += '</div>';
    } else if (totalRegistros > 0) {
        html += '<div class="pt-2 mt-2 border-top text-center">';
        html += '<span class="fs-12 text-muted">Mostrando los ' + totalRegistros + ' archivos adjuntos del proyecto.</span>';
        html += '</div>';
    }

    $('#timeline_adjuntos').html(html);
    fntHomologarPresentacionProyecto();
}

/**
 * Renderiza las partidas del proyecto en el DataTable del Tab Partidas
 */
function fntRenderPartidasProyecto(partidasObj) {
    var partidas = [];
    var origenEtiqueta = "Sin partidas";

    if (partidasObj) {
        if (Array.isArray(partidasObj)) {
            partidas = partidasObj;
        } else if (typeof partidasObj === "object") {
            partidas = partidasObj.partidas || [];
            origenEtiqueta = partidasObj.origen_etiqueta || "Sin partidas";
        }
    }

    $("#badge_total_partidas").text(partidas.length);
    var badgeClass = "badge bg-light text-dark border fs-12 px-2.5 py-1";
    if (origenEtiqueta.indexOf("Orden de Compra") !== -1 || origenEtiqueta.indexOf("Pedido") !== -1) {
        badgeClass = "badge bg-success fs-12 px-2.5 py-1";
    } else if (origenEtiqueta.indexOf("Cotización de Cliente") !== -1 || origenEtiqueta.indexOf("Cotización Cliente") !== -1) {
        badgeClass = "badge bg-info text-dark fs-12 px-2.5 py-1";
    } else if (origenEtiqueta.indexOf("Cotización Interna") !== -1) {
        badgeClass = "badge bg-warning text-dark fs-12 px-2.5 py-1";
    } else if (origenEtiqueta.indexOf("Proyecto") !== -1) {
        badgeClass = "badge bg-secondary fs-12 px-2.5 py-1";
    }
    var documento = partidasObj && partidasObj.documento ? partidasObj.documento : (partidas[0] || {});
    var monedaPartidas = fntMonedaProyecto(documento);
    var folioDocumento = documento.folio || documento.folio_documento || documento.documento_id || '';
    var detalleOrigen = origenEtiqueta + (folioDocumento ? ' · ' + (origenEtiqueta === 'Proyecto de Venta' ? 'Documento: ' : 'Último documento: ') + folioDocumento : '') + (partidas.length ? ' · ' + monedaPartidas : '');
    $('#lblOrigenPartidasBadge').attr('class', badgeClass).attr('title', detalleOrigen).addClass('text-wrap').text(detalleOrigen);

    // Destruir DataTable previo si existe
    if (tablePartidasProyecto !== null) {
        tablePartidasProyecto.destroy();
        tablePartidasProyecto = null;
    }

    // Los datos comerciales son texto: escapar antes de admitir solo saltos de línea.
    // Llenar filas en el tbody
    var tbodyHtml = "";
    if (partidas.length > 0) {
        partidas.forEach(function (item, index) {
            var num = index + 1;
            var restringido = partidasObj.importes_restringidos === true || item.importes_restringidos === true;
            var cod = item.codigo_partida || "—";
            var clave = item.clave || "—";
            var ccn = item.ccn || "—";
            var codCliente = item.codigo_cliente || "—";
            var desc = item.descripcion ? fntEscapeHtml(item.descripcion).replace(/\r?\n/g, "<br>") : "Sin descripción";
            var descAdic = item.descripcion_adicional ? fntEscapeHtml(item.descripcion_adicional).replace(/\r?\n/g, "<br>") : "—";
            var cant = parseFloat(item.cantidad || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var prec = parseFloat(item.precio_unitario || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var descMonto = parseFloat(item.descuento || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var impImpuesto = parseFloat(item.importe_impuesto || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var sub = parseFloat(item.subtotal || item.importe || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            tbodyHtml += '<tr>' +
                '<td class="text-center fw-bold text-muted">' + num + '</td>' +
                '<td class="text-center fw-bold text-primary">' + fntEscapeHtml(cod) + '</td>' +
                '<td class="text-center">' + fntEscapeHtml(clave) + '</td>' +
                '<td class="text-center">' + fntEscapeHtml(ccn) + '</td>' +
                '<td class="text-center">' + fntEscapeHtml(codCliente) + '</td>' +
                '<td class="text-wrap" style="min-width: 350px; white-space: pre-wrap;">' + desc + '</td>' +
                '<td class="text-wrap" style="min-width: 450px; white-space: pre-wrap;">' + descAdic + '</td>' +
                '<td class="text-end fw-semibold">' + cant + '</td>' +
                '<td class="text-end">' + (restringido ? '- - -' : fntEscapeHtml(monedaPartidas) + ' $ ' + prec) + '</td>' +
                '<td class="text-end">' + (restringido ? '- - -' : fntEscapeHtml(monedaPartidas) + ' $ ' + descMonto) + '</td>' +
                '<td class="text-end">' + (restringido ? '- - -' : fntEscapeHtml(monedaPartidas) + ' $ ' + impImpuesto) + '</td>' +
                '<td class="text-end fw-bold text-dark">' + (restringido ? '- - -' : fntEscapeHtml(monedaPartidas) + ' $ ' + sub) + '</td>' +
                '</tr>';
        });
    }

    $("#tablePartidasProyecto tbody").html(tbodyHtml);

    // Re-inicializar DataTables
    tablePartidasProyecto = LfmTables.create($("#tablePartidasProyecto"), {
        clearColumnFilters: true,
        // # es un consecutivo: sin búsqueda ni filtro; conserva orden y exportación.
        // El helper compartido mantiene el botón de limpiar en esta primera celda.
        columns: Array.from(document.querySelector('#tablePartidasProyecto thead tr').cells, function (_, indice) {
            return indice === 0 ? {searchable: false} : {};
        }),
        initComplete: function () { fntPermisoExcelProyecto(this.api()); },
        orderCellsTop: true,
        fixedHeader: true,
        scrollX: "100%",
        destroy: true,
        iDisplayLength: 10,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Todos"]
        ],
        dom: "Blfrtip",
        buttons: [
            {
                extend: "excelHtml5",
                autoFilter: true,
                sheetName: "Partidas del Proyecto",
                extend: "excel",
                messageTop: "",
                title: "Partidas del Proyecto",
                exportOptions: {
                    columns: ":visible"
                }
            },
            {
                extend: "colvis",
                postfixButtons: ["colvisRestore"]
            }
        ],
        language: typeof idioma_espanol !== "undefined" ? idioma_espanol : {}
    });

    fntHomologarPresentacionProyecto();

    setTimeout(function () {
        if (tablePartidasProyecto) {
            tablePartidasProyecto.columns.adjust().draw();
        }
    }, 150);
}

