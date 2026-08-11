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

$(document).ready(function () {

    // Ajustar columnas de DataTables al cambiar de pestaña
    $('button[data-bs-toggle="tab"], a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (tablePartidasProyecto) {
            tablePartidasProyecto.columns.adjust().draw();
        }
    });

    // 1. Inicializar fechas por defecto según el periodo "este_mes"
    fntActualizarFechasPorPeriodo("este_mes");

    // 2. Inicializar DataTable
    fntInicializarTablaProyectos();

    // 3. Cargar datos del DataTable
    fntCargarTablaProyectos();

    // 4. Escuchar cambio en el select de periodo
    $("#filtro_periodo").on("change", function () {
        const periodo = $(this).val();
        fntActualizarFechasPorPeriodo(periodo);
    });

    // 5. Escuchar clic en botón Buscar
    $("#btnFiltrar").on("click", function (e) {
        e.preventDefault();
        fntCargarTablaProyectos();
    });

    // 6. Escuchar Enter en inputs de filtro
    $("#txtProyectoId, #filtro_fecha_ini, #filtro_fecha_fin, #filtro_titulo, #filtro_cliente, #filtro_vendedor").on("keypress", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            fntCargarTablaProyectos();
        }
    });

    // 7. Evento al hacer clic en una fila de la tabla o botón Ver Detalle
    $(tableElement + " tbody").on("click", "tr", function (e) {
        // Ignorar clics en botones de DataTables exportación si los hubiera dentro
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

/**
 * Actualiza los campos de fecha inicial y final según el periodo seleccionado
 */
function fntActualizarFechasPorPeriodo(periodo) {
    const hoy = new Date();
    let fechaIniStr = "";
    let fechaFinStr = "";

    const formatDDMMYYYY = (d) => {
        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yyyy = d.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
    };

    switch (periodo) {
        case "este_mes": {
            const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            fechaIniStr = formatDDMMYYYY(primerDia);
            fechaFinStr = formatDDMMYYYY(hoy);
            break;
        }
        case "mes_anterior": {
            const primerDiaMesAnt = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
            const ultimoDiaMesAnt = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
            fechaIniStr = formatDDMMYYYY(primerDiaMesAnt);
            fechaFinStr = formatDDMMYYYY(ultimoDiaMesAnt);
            break;
        }
        case "ano_actual": {
            const primerDiaAno = new Date(hoy.getFullYear(), 0, 1);
            fechaIniStr = formatDDMMYYYY(primerDiaAno);
            fechaFinStr = formatDDMMYYYY(hoy);
            break;
        }
        case "ultimos_30": {
            const hace30 = new Date();
            hace30.setDate(hoy.getDate() - 30);
            fechaIniStr = formatDDMMYYYY(hace30);
            fechaFinStr = formatDDMMYYYY(hoy);
            break;
        }
        case "todos": {
            fechaIniStr = "";
            fechaFinStr = "";
            break;
        }
        case "personalizado":
        default:
            return;
    }

    $("#filtro_fecha_ini").val(fechaIniStr);
    $("#filtro_fecha_fin").val(fechaFinStr);
}

/**
 * Inicializa la configuración de DataTables para el listado de Proyectos de Venta
 */
function fntInicializarTablaProyectos() {
    tableProyectosVenta = $(tableElement).DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        scrollX: "100%",
        destroy: true,
        order: [[1, "desc"]],
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
                data: "proyecto_id",
                className: "text-center align-middle",
                render: function (data, type, row) {
                    return '<span class="fw-bold text-dark">' + (data || "—") + '</span>';
                }
            },
            {
                data: "fecha_formateada",
                className: "text-center align-middle",
                render: function (data, type, row) {
                    return '<span class="text-muted">' + (data || "—") + '</span>';
                }
            },
            {
                data: "cliente",
                className: "text-start align-middle",
                render: function (data, type, row) {
                    return '<span class="fw-bold text-primary">' + (data || "—") + '</span>';
                }
            },
            {
                data: "titulo",
                className: "text-start align-middle",
                render: function (data, type, row) {
                    return '<span class="fw-semibold text-dark">' + (data || "Sin título") + '</span>';
                }
            },
            {
                data: "vendedor",
                className: "text-start align-middle",
                render: function (data, type, row) {
                    return '<span class="text-secondary">' + (data || "—") + '</span>';
                }
            },
            {
                data: "monto_formateado",
                className: "text-end align-middle",
                render: function (data, type, row) {
                    return '<span class="fw-bold text-success">' + (data || "$0.00") + '</span>';
                }
            },
            { data: "estatus_badge", className: "text-center align-middle", orderable: false }
        ],
        language: typeof idioma_espanol !== "undefined" ? idioma_espanol : {}
    });

    $(tableElement).on("init.dt", function () {
        if (typeof menu !== "undefined" && typeof validaPermisoExportar === "function") {
            validaPermisoExportar(menu);
        }
        $(".loading-panel-showing").removeClass("loading-panel-showing");
    });

    $(tableElement).on("draw.dt", function () {
        if (tableProyectosVenta) {
            tableProyectosVenta.columns.adjust();
        }
    });
}

/**
 * Carga (o recarga) el DataTable de Proyectos de Venta vía AJAX
 */
function fntCargarTablaProyectos() {
    const fechaIni = $("#filtro_fecha_ini").val().trim();
    const fechaFin = $("#filtro_fecha_fin").val().trim();
    const busqueda = $("#txtProyectoId").val().trim();
    const titulo   = $("#filtro_titulo").val().trim();
    const cliente  = $("#filtro_cliente").val().trim();
    const vendedor = $("#filtro_vendedor").val().trim();

    if (tableProyectosVenta !== null) {
        tableProyectosVenta.clear().draw();
    }

    $(".loading-panel-showing").addClass("loading-panel-showing");

    $.ajax({
        type: "POST",
        url: base_url + "/seguimiento/getProyectosVentaDatatable",
        data: {
            fecha_ini: fechaIni,
            fecha_fin: fechaFin,
            busqueda: busqueda,
            titulo: titulo,
            cliente: cliente,
            vendedor: vendedor
        },
        dataType: "json",
        success: function (data) {
            if (tableProyectosVenta !== null && Array.isArray(data)) {
                tableProyectosVenta.clear().rows.add(data).draw();
                tableProyectosVenta.columns.adjust().draw();

                if (data.length > 0) {
                    // Seleccionar y cargar por defecto el detalle del primer proyecto si no hay selección previa activa
                    const idACargar = ventaIdSeleccionado ? ventaIdSeleccionado : data[0].id;
                    const existeEnLista = data.some(item => item.id == idACargar);
                    const primerId = existeEnLista ? idACargar : data[0].id;

                    fntCargarDetalleProyecto(primerId, false);
                } else {
                    fntMostrarEstadoUI("placeholder");
                }
            } else {
                fntMostrarEstadoUI("placeholder");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al cargar proyectos de venta:", error);
            fntMostrarAlerta("Error de conexión al cargar la lista de proyectos.", "error");
            fntMostrarEstadoUI("placeholder");
        },
        complete: function () {
            $(".loading-panel-showing").removeClass("loading-panel-showing");
        }
    });
}

/**
 * Consulta el detalle completo y el checklist del proyecto seleccionado
 */
function fntCargarDetalleProyecto(ventaId, scrollTo = true) {
    if (!ventaId) return;

    ventaIdSeleccionado = ventaId;
    fntMostrarEstadoUI("loading");

    const formData = new FormData();
    formData.append("venta_id", ventaId);

    fetch(base_url + "/seguimiento/buscarProyectoVenta", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
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
                fntCargarHistorialSeguimientos(ventaId);
                fntCargarAdjuntosProyecto(ventaId);
                fntRenderPartidasProyecto(data.partidas);

                fntMostrarEstadoUI("resultado");

                // Desplazamiento suave a la sección de detalle si se solicitó explícitamente
                if (scrollTo) {
                    const elDetalle = document.getElementById("panel_detalle_proyecto");
                    if (elDetalle) {
                        elDetalle.scrollIntoView({ behavior: "smooth", block: "start" });
                    }
                }
            } else {
                fntMostrarEstadoUI("placeholder");
                fntMostrarAlerta(data.msg || "No se pudo cargar el detalle del proyecto.", "error");
            }
        })
        .catch(err => {
            console.error("Error al cargar detalle del proyecto:", err);
            fntMostrarEstadoUI("placeholder");
            fntMostrarAlerta("Ocurrió un error en el servidor al consultar el detalle del proyecto.", "error");
        });
}

/**
 * Renderiza la tarjeta de información general del proyecto
 */
function fntRenderResumenProyecto(p) {
    if (!p) return;

    document.getElementById("lblProyectoId").textContent = p.proyecto_id || "PV-N/A";
    document.getElementById("lblTituloProyecto").textContent = p.titulo || "Sin título especificado";

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

    const moneda = p.cmoneda || "USD";
    const total = parseFloat(p.total || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById("lblMontoTotal").textContent = `${moneda} $${total}`;
}

/**
 * Renderiza los 7 ítems del checklist de evaluación del proceso
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
                const monedaDoc = r.cmoneda || (parseInt(r.moneda_id || 0) === 1 ? "MXN" : (parseInt(r.moneda_id || 0) === 3 ? "USD" : "USD"));

                registrosHtml += `
                    <tr>
                        <td class="fw-bold text-primary">${folioDoc}</td>
                        <td>${fechaDoc}</td>
                        <td class="text-end">$${sub}</td>
                        ${showDescuento ? `<td class="text-end text-danger">$${desc}</td>` : ''}
                        <td class="text-end">$${iva}</td>
                        <td class="text-end fw-bold text-dark">$${tot}</td>
                        <td class="text-center"><span class="badge bg-secondary px-2 py-1">${monedaDoc}</span></td>
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
                            <span class="text-secondary me-1">Paso ${numPaso}:</span> ${step.nombre}
                        </h5>
                        <div>${statusBadgeHtml}</div>
                    </div>
                    <p class="text-muted mb-0 fs-13 mt-2">
                        <i class="fa-regular fa-circle-info me-1"></i>${step.mensaje || "Sin detalles disponibles."}
                    </p>
                    ${registrosHtml}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Calcular progreso
    const totalSteps = checklist.length;
    const porcentaje = Math.round((completadosCount / totalSteps) * 100);

    const bar = document.getElementById("barProgresoChecklist");
    const lblPorcentaje = document.getElementById("lblProgresoPorcentaje");

    if (bar) {
        bar.style.width = `${porcentaje}%`;
        bar.setAttribute("aria-valuenow", porcentaje);
    }
    if (lblPorcentaje) {
        lblPorcentaje.textContent = `${completadosCount} de ${totalSteps} etapas completadas (${porcentaje}%)`;
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
    $("#filtro_periodo").val("este_mes");
    fntActualizarFechasPorPeriodo("este_mes");
    $("#txtProyectoId").val("");
    $("#filtro_titulo").val("");
    $("#filtro_cliente").val("");
    $("#filtro_vendedor").val("");

    ventaIdSeleccionado = null;
    fntCargarTablaProyectos();
}

/**
 * Controla la visibilidad de los páneles de la interfaz
 */
function fntMostrarEstadoUI(estado) {
    const pPlaceholder = document.getElementById("panelPlaceholder");
    const pLoading = document.getElementById("panelLoading");
    const cResumen = document.getElementById("cardResumenProyecto");
    const cChecklist = document.getElementById("cardChecklistProceso");

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
function fntCargarHistorialSeguimientos(ventaId) {
    $("#timeline_seguimientos").empty();
    $("#sin_seguimientos").addClass("d-none");
    $("#loading_seguimientos").removeClass("d-none");

    $.ajax({
        type: "POST",
        url: base_url + "/seguimiento/getHistorialSeguimiento",
        data: { venta_id: ventaId },
        dataType: "json",
        success: function (resp) {
            $("#loading_seguimientos").addClass("d-none");

            if (resp.respuesta !== "ok" || !Array.isArray(resp.data) || resp.data.length === 0) {
                historialSeguimientoData = [];
                $("#badge_total_seg").text(0);
                $("#sin_seguimientos").removeClass("d-none");
                return;
            }

            historialSeguimientoData = resp.data;
            $("#badge_total_seg").text(historialSeguimientoData.length);
            fntRenderizarTimelinePagina(1);
        },
        error: function (xhr, status, error) {
            historialSeguimientoData = [];
            $("#badge_total_seg").text(0);
            $("#loading_seguimientos").addClass("d-none");
            $("#sin_seguimientos").removeClass("d-none");
            console.error("Error al cargar historial de seguimiento:", error);
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
            var fileUrl = base_url + '/Assets/files/ventas/' + encodeURIComponent(fileName);
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
function fntCargarAdjuntosProyecto(ventaId) {
    $("#timeline_adjuntos").empty();
    $("#sin_adjuntos").addClass("d-none");
    $("#loading_adjuntos").removeClass("d-none");

    $.ajax({
        type: "POST",
        url: base_url + "/seguimiento/getAdjuntosProyecto",
        data: { venta_id: ventaId },
        dataType: "json",
        success: function (resp) {
            $("#loading_adjuntos").addClass("d-none");

            if (resp.respuesta !== "ok" || !Array.isArray(resp.data) || resp.data.length === 0) {
                adjuntosProyectoData = [];
                $("#badge_total_adjuntos").text(0);
                $("#sin_adjuntos").removeClass("d-none");
                return;
            }

            adjuntosProyectoData = resp.data;
            $("#badge_total_adjuntos").text(adjuntosProyectoData.length);
            fntRenderizarTimelineAdjuntosPagina(1);
        },
        error: function (xhr, status, error) {
            adjuntosProyectoData = [];
            $("#badge_total_adjuntos").text(0);
            $("#loading_adjuntos").addClass("d-none");
            $("#sin_adjuntos").removeClass("d-none");
            console.error("Error al cargar adjuntos del proyecto:", error);
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
        var fileUrl = base_url + '/Assets/files/ventas/' + encodeURIComponent(fileName);
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
    $("#lblOrigenPartidasBadge").text(origenEtiqueta);

    // Destruir DataTable previo si existe
    if (tablePartidasProyecto !== null) {
        tablePartidasProyecto.destroy();
        tablePartidasProyecto = null;
    }

    // Llenar filas en el tbody
    var tbodyHtml = "";
    if (partidas.length > 0) {
        partidas.forEach(function (item, index) {
            var num = index + 1;
            var cod = item.codigo_partida || "—";
            var clave = item.clave || "—";
            var ccn = item.ccn || "—";
            var codCliente = item.codigo_cliente || "—";
            var desc = item.descripcion ? String(item.descripcion).replace(/\r?\n/g, "<br>") : "Sin descripción";
            var descAdic = item.descripcion_adicional ? String(item.descripcion_adicional).replace(/\r?\n/g, "<br>") : "—";
            var cant = parseFloat(item.cantidad || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var prec = parseFloat(item.precio_unitario || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var descMonto = parseFloat(item.descuento || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var impImpuesto = parseFloat(item.importe_impuesto || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var sub = parseFloat(item.subtotal || item.importe || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            tbodyHtml += '<tr>' +
                '<td class="text-center fw-bold text-muted">' + num + '</td>' +
                '<td class="text-center fw-bold text-primary">' + cod + '</td>' +
                '<td class="text-center">' + clave + '</td>' +
                '<td class="text-center">' + ccn + '</td>' +
                '<td class="text-center">' + codCliente + '</td>' +
                '<td class="text-wrap" style="min-width: 350px; white-space: pre-wrap;">' + desc + '</td>' +
                '<td class="text-wrap" style="min-width: 450px; white-space: pre-wrap;">' + descAdic + '</td>' +
                '<td class="text-end fw-semibold">' + cant + '</td>' +
                '<td class="text-end">$ ' + prec + '</td>' +
                '<td class="text-end">$ ' + descMonto + '</td>' +
                '<td class="text-end">$ ' + impImpuesto + '</td>' +
                '<td class="text-end fw-bold text-dark">$ ' + sub + '</td>' +
                '</tr>';
        });
    }

    $("#tablePartidasProyecto tbody").html(tbodyHtml);

    // Re-inicializar DataTables
    tablePartidasProyecto = $("#tablePartidasProyecto").DataTable({
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

    setTimeout(function () {
        if (tablePartidasProyecto) {
            tablePartidasProyecto.columns.adjust().draw();
        }
    }, 150);
}

