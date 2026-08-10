/**
 * proyecto_venta.js
 * Módulo: Seguimiento - Proyecto de Venta
 */

var controller = "Seguimiento";

document.addEventListener("DOMContentLoaded", function () {
    // Escuchar Enter en el input de filtro
    const inputProyecto = document.getElementById("txtProyectoId");
    if (inputProyecto) {
        inputProyecto.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                fntBuscarProyecto(e);
            }
        });
    }
});

/**
 * Realiza la búsqueda del proyecto de venta por clave o número de folio
 */
function fntBuscarProyecto(e) {
    if (e) e.preventDefault();

    const inputProyecto = document.getElementById("txtProyectoId");
    const valProyecto = inputProyecto ? inputProyecto.value.trim() : "";

    if (!valProyecto) {
        if (typeof swal === "function") {
            swal("Atención", "Por favor ingrese la clave o número del proyecto de venta.", "warning");
        } else {
            alert("Por favor ingrese la clave o número del proyecto de venta.");
        }
        return;
    }

    // Mostrar panel de carga
    fntMostrarEstadoUI("loading");

    const formData = new FormData();
    formData.append("proyecto_id", valProyecto);

    const requestUrl = base_url + "/seguimiento/buscarProyectoVenta";

    fetch(requestUrl, {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (!data.status) {
                fntMostrarEstadoUI("placeholder");
                if (typeof swal === "function") {
                    swal("Sin resultados", data.msg || "No se encontró el proyecto indicado.", "info");
                } else {
                    alert(data.msg || "No se encontró el proyecto indicado.");
                }
                return;
            }

            // Manejo de múltiples coincidencias
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
                containerCoincidencias.classList.add("d-none");
            }

            // Renderizar resumen y checklist
            fntRenderResumenProyecto(data.data);
            fntRenderChecklistProceso(data.checklist);

            fntMostrarEstadoUI("resultado");
        })
        .catch(err => {
            console.error("Error al buscar proyecto:", err);
            fntMostrarEstadoUI("placeholder");
            if (typeof swal === "function") {
                swal("Error", "Ocurrió un error en el servidor al consultar el proyecto.", "error");
            } else {
                alert("Ocurrió un error en el servidor al consultar el proyecto.");
            }
        });
}

/**
 * Selecciona un proyecto cuando existen múltiples coincidencias en el select
 */
function fntSeleccionarProyecto(ventaId) {
    if (!ventaId) return;

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
                fntRenderResumenProyecto(data.data);
                fntRenderChecklistProceso(data.checklist);
                fntMostrarEstadoUI("resultado");
            } else {
                fntMostrarEstadoUI("placeholder");
                alert(data.msg || "Error al cargar proyecto.");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            fntMostrarEstadoUI("placeholder");
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
        const numPaso = step.id;
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

                registrosHtml += `
                    <tr>
                        <td class="fw-bold text-primary">${folioDoc}</td>
                        <td>${fechaDoc}</td>
                        <td class="text-end">$${sub}</td>
                        <td class="text-end">$${iva}</td>
                        <td class="text-end fw-bold text-dark">$${tot}</td>
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
 * Limpia el filtro y reinicia la vista
 */
function fntLimpiarFiltro() {
    const inputProyecto = document.getElementById("txtProyectoId");
    if (inputProyecto) inputProyecto.value = "";

    const containerCoincidencias = document.getElementById("containerCoincidencias");
    if (containerCoincidencias) containerCoincidencias.classList.add("d-none");

    fntMostrarEstadoUI("placeholder");
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

