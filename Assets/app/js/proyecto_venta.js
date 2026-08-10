/**
 * proyecto_venta.js
 * Módulo: Seguimiento - Proyecto de Venta
 */

'use strict';

var controller = "Seguimiento";
var tableProyectosVenta = null;
var tableElement = "#tableProyectosVenta";
var ventaIdSeleccionado = null;

$(document).ready(function () {

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

                // Renderizar Resumen y Checklist de Evaluación
                fntRenderResumenProyecto(data.data);
                fntRenderChecklistProceso(data.checklist);

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
            registrosHtml = `
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered align-middle bg-white mb-0 fs-12">
                        <thead class="table-light">
                            <tr>
                                <th>Folio / Documento</th>
                                <th>Fecha</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">IVA</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Moneda</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            step.registros.forEach(r => {
                const folioDoc = r.folio_cotizacion || r.folio_ocp || r.proyecto_id || `ID #${r.id}`;
                const fechaDoc = r.fecha_formateada || r.fecha_pedido || r.fecha || "—";
                const sub = parseFloat(r.subtotal || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const iva = parseFloat(r.iva || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const tot = parseFloat(r.total || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const monedaDoc = r.cmoneda || (parseInt(r.moneda_id || 0) === 1 ? "MXN" : (parseInt(r.moneda_id || 0) === 3 ? "USD" : "USD"));

                registrosHtml += `
                    <tr>
                        <td class="fw-bold text-primary">${folioDoc}</td>
                        <td>${fechaDoc}</td>
                        <td class="text-end">$${sub}</td>
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
