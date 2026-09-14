/*==================================================================
[ JS: Almacén - Control de Pases de Salida ]
==================================================================*/

var tablePases = null;
var tableReporteCli = null;
var tableReporteVen = null;
var selectedPaseId = null;

// Signature Pad state
var canvas = null;
var ctx = null;
var isDrawing = false;
var hasSignature = false;
var lastX = 0;
var lastY = 0;

// Current loaded attachments
var currentPaseAdjuntos = [];

document.addEventListener("DOMContentLoaded", function () {
    // 1. Inicializar DataTables Principal
    initDataTablePases();

    // 2. Inicializar Signature Pad
    initSignaturePad();

    // 2.1 Inicializar Select2 para Usuario Recibe
    initSelect2UsuarioRecibe();

    // 3. Inicializar Tablas de Reporte
    initReportesTablas();

    // 4. Eventos de Filtros
    const btnAplicar = document.getElementById('btnAplicarFiltros');
    if (btnAplicar) {
        btnAplicar.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarBusquedaPases();
        });
    }

    const btnLimpiar = document.getElementById('btnLimpiarFiltros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function (e) {
            e.preventDefault();
            limpiarFiltrosPases();
        });
    }

    // Auto-búsqueda en selects
    ['selectFiltroEstatus', 'selectFiltroVendedor', 'selectFiltroMotivo', 'selectFiltroAlmacen'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                ejecutarBusquedaPases();
            });
        }
    });

    // Enter y cambio en input de filtro de cliente
    const txtCliente = document.getElementById('txtFiltroCliente');
    if (txtCliente) {
        txtCliente.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                ejecutarBusquedaPases();
            }
        });
        txtCliente.addEventListener('change', function () {
            ejecutarBusquedaPases();
        });
    }

    // Enter en input de búsqueda libre
    const txtBusqueda = document.getElementById('txtFiltroBusqueda');
    if (txtBusqueda) {
        txtBusqueda.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                ejecutarBusquedaPases();
            }
        });
    }

    // Botón borrar firma en modal
    const btnBorrarFirma = document.getElementById('btnLimpiarCanvasFirma');
    if (btnBorrarFirma) {
        btnBorrarFirma.addEventListener('click', function () {
            limpiarCanvasFirma();
        });
    }

    // Botón confirmar entrega en modal
    const btnConfirmarEntrega = document.getElementById('btnConfirmarGuardarEntrega');
    if (btnConfirmarEntrega) {
        btnConfirmarEntrega.addEventListener('click', function () {
            guardarEntregaPase();
        });
    }

    // Botón abrir visor multimedia desde panel de detalle
    const btnAbrirVisor = document.getElementById('btnAbrirModalEvidencias');
    if (btnAbrirVisor) {
        btnAbrirVisor.addEventListener('click', function () {
            if (selectedPaseId) {
                abrirModalEvidencias(selectedPaseId);
            }
        });
    }

    // Eventos de pestañas de reportes para redimensionar columnas
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // Evento al cerrar modal de evidencias: destruir reproductor de video y limpiar contenedor
    const modalVisorEl = document.getElementById('modalVisorEvidencias');
    if (modalVisorEl) {
        modalVisorEl.addEventListener('hidden.bs.modal', function () {
            if (typeof VideoPlayerEngine !== 'undefined') {
                VideoPlayerEngine.destroy();
            }
            $('#visorDisplayContainer').empty().html('<span class="text-muted">Seleccione un archivo de la lista para previsualizarlo.</span>');
            $('#visorFileInfo').empty();
        });
    }
});

/*==================================================================
[ 1. DataTables Principal de Pases de Salida ]
==================================================================*/

function initDataTablePases() {
    const tableEl = '#tablePasesSalida';

    if ($.fn.DataTable.isDataTable(tableEl)) {
        $(tableEl).DataTable().destroy();
        $(tableEl).find('tbody').empty();
    }

    tablePases = $(tableEl).DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        responsive: true,
        order: [[2, "desc"], [1, "desc"]], // Ordenar por fecha desc, luego folio desc
        iDisplayLength: 5,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Todos"]
        ],
        language: idioma_espanol,
        ajax: {
            url: base_url + '/almacen/getPasesSalida',
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, obtenerValoresFiltros());
            },
            dataSrc: function (json) {
                if (json && json.status) {
                    // Actualizar tarjetas de KPI
                    actualizarKpisPases(json.kpis);
                    // Actualizar análisis ejecutivo
                    actualizarAnalisisEjecutivo(json.analisis);
                    return json.data || [];
                }
                return [];
            }
        },
        columns: [
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function (data, type, row) {
                    const esEntregado = (row.estatus === 'ENTREGADO');
                    const esCancelado = (row.estatus === 'CANCELADO');

                    let btnEntrega = '';
                    if (!esEntregado && !esCancelado) {
                        btnEntrega = `
                            <button type="button" class="btn btn-sm btn-success py-1 px-2 me-1" 
                                    onclick="abrirModalEntrega(${row.id}); event.stopPropagation();" 
                                    title="Registrar Entrega / Firma Digital">
                                <i class="fa-solid fa-pen-nib"></i>
                            </button>
                        `;
                    }

                    const numAdjuntos = parseInt(row.total_adjuntos) || 0;
                    const badgeAdj = numAdjuntos > 0 ? `<span class="badge bg-warning text-dark position-absolute top-0 start-100 translate-middle badge-pill py-0 px-1" style="font-size: 0.65rem;">${numAdjuntos}</span>` : '';

                    return `
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-primary py-1 px-2 me-1" 
                                    onclick="cargarDetallePase(${row.id}); event.stopPropagation();" 
                                    title="Ver Detalle Completo">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            ${btnEntrega}
                            <button type="button" class="btn btn-sm btn-secondary py-1 px-2 position-relative" 
                                    onclick="abrirModalEvidencias(${row.id}); event.stopPropagation();" 
                                    title="Consultar Evidencias y Adjuntos">
                                <i class="fa-solid fa-paperclip"></i>
                                ${badgeAdj}
                            </button>
                        </div>
                    `;
                }
            },
            {
                data: 'folio',
                className: 'text-center fw-bold text-primary',
                render: function (data, type, row) {
                    return `<span class="badge bg-light text-primary border border-primary px-2 py-1">${data}</span>`;
                }
            },
            {
                data: 'fecha',
                className: 'text-center',
                render: function (data) {
                    if (!data) return '-';
                    return `<span class="fw-semibold">${data}</span>`;
                }
            },
            {
                data: 'proyecto_titulo',
                render: function (data, type, row) {
                    const pyId = row.proyecto_id ? `<span class="badge bg-dark me-1">${row.proyecto_id}</span>` : '';
                    const title = data ? htmlEncode(data) : 'Sin proyecto asignado';
                    const clFinal = row.cliente_final ? `<br><small class="text-muted"><i class="fa-solid fa-arrow-turn-down-right me-1"></i>Final: ${htmlEncode(row.cliente_final)}</small>` : '';
                    return `<div>${pyId}<strong>${title}</strong>${clFinal}</div>`;
                }
            },
            {
                data: 'nombre_cliente',
                render: function (data) {
                    return `<strong>${htmlEncode(data || 'Público General')}</strong>`;
                }
            },
            {
                data: 'nombre_vendedor',
                render: function (data) {
                    return `<small class="text-secondary fw-semibold"><i class="fa-solid fa-user-tie me-1"></i>${htmlEncode(data || 'No asignado')}</small>`;
                }
            },
            {
                data: 'calidad_salida',
                render: function (data) {
                    return `<span class="badge bg-secondary">${htmlEncode(data || 'VENTA')}</span>`;
                }
            },
            {
                data: 'cdscalmacen',
                render: function (data) {
                    return `<small class="fw-semibold">${htmlEncode(data || '-')}</small>`;
                }
            },
            {
                data: 'nombre_recibio_salida',
                render: function (data, type, row) {
                    if (data && data.trim() !== '') {
                        return `<span class="text-dark fw-bold"><i class="fa-solid fa-user-check text-success me-1"></i>${htmlEncode(data)}</span>`;
                    }
                    return `<span class="text-muted fst-italic"><i class="fa-regular fa-clock me-1"></i>Pendiente</span>`;
                }
            },
            {
                data: 'estatus',
                className: 'text-center',
                render: function (data) {
                    if (data === 'ENTREGADO') {
                        return `<span class="badge-status-entregado"><i class="fa-solid fa-check me-1"></i>ENTREGADO</span>`;
                    } else if (data === 'CANCELADO') {
                        return `<span class="badge-status-cancelado"><i class="fa-solid fa-xmark me-1"></i>CANCELADO</span>`;
                    }
                    return `<span class="badge-status-pendiente"><i class="fa-regular fa-clock me-1"></i>PENDIENTE</span>`;
                }
            },
            {
                data: 'dias_transcurridos',
                className: 'text-center',
                render: function (data, type, row) {
                    const dias = parseInt(data) || 0;
                    if (dias < 15) {
                        return `<span class="badge-semaforo-verde" title="Menos de 15 días (En tiempo)">🟢 ${dias} día${dias === 1 ? '' : 's'}</span>`;
                    } else if (dias <= 30) {
                        return `<span class="badge-semaforo-amarillo" title="15 a 30 días (Atención)">🟡 ${dias} días</span>`;
                    }
                    return `<span class="badge-semaforo-rojo" title="Más de 30 días (Crítico / Requiere atención)">🔴 ${dias} días</span>`;
                }
            }
        ],
        createdRow: function (row, data, dataIndex) {
            $(row).css('cursor', 'pointer');
            $(row).on('click', function () {
                cargarDetallePase(data.id);
            });
        }
    });
}

function obtenerValoresFiltros() {
    const clienteVal = ($('#txtFiltroCliente').val() || '').trim();
    return {
        cliente: clienteVal,
        cliente_id: clienteVal,
        estatus: $('#selectFiltroEstatus').val() || '',
        vendedor: $('#selectFiltroVendedor').val() || '',
        motivo_salida: $('#selectFiltroMotivo').val() || '',
        almacen: $('#selectFiltroAlmacen').val() || '',
        fecha_inicio: $('#txtFiltroFechaInicio').val() || '',
        fecha_fin: $('#txtFiltroFechaFin').val() || '',
        busqueda: $('#txtFiltroBusqueda').val() || ''
    };
}

function ejecutarBusquedaPases() {
    if (tablePases) {
        tablePases.ajax.reload();
    }
    recargarReportesTablas();
}

function limpiarFiltrosPases() {
    $('#txtFiltroCliente').val('');
    $('#selectFiltroEstatus').val('');
    $('#selectFiltroVendedor').val('');
    $('#selectFiltroMotivo').val('');
    $('#selectFiltroAlmacen').val('');
    $('#txtFiltroFechaInicio').val('');
    $('#txtFiltroFechaFin').val('');
    $('#txtFiltroBusqueda').val('');

    ejecutarBusquedaPases();
}

function actualizarKpisPases(kpis) {
    if (!kpis) return;
    $('#kpi_total_pases').text(kpis.total_pases || 0);
    $('#kpi_pendientes').text(kpis.pendientes || 0);
    $('#kpi_entregados').text(kpis.entregados || 0);
    $('#kpi_menos_15').text(kpis.menos_15_dias || 0);
    $('#kpi_15_a_30').text(kpis.de_15_a_30_dias || 0);
    $('#kpi_mas_30').text(kpis.mas_30_dias || 0);
}

function actualizarAnalisisEjecutivo(analisis) {
    if (!analisis) return;

    // Total urgentes badge
    const totalUrg = analisis.total_urgentes || 0;
    const badgeUrg = $('#badge_total_urgentes');
    if (totalUrg > 0) {
        badgeUrg.html(`<i class="fa-solid fa-triangle-exclamation me-1"></i> ${totalUrg} Pase(s) Críticos por Devolver (&gt;30 días)`);
        badgeUrg.removeClass('bg-success-lighten text-success border-success').addClass('bg-danger-lighten text-danger border-danger');
    } else {
        badgeUrg.html(`<i class="fa-solid fa-circle-check me-1"></i> 0 Pases Críticos por Devolver`);
        badgeUrg.removeClass('bg-danger-lighten text-danger border-danger').addClass('bg-success-lighten text-success border-success');
    }

    // Top Clientes (Card A)
    const listCli = $('#list_top_clientes');
    listCli.empty();
    if (analisis.top_clientes && analisis.top_clientes.length > 0) {
        analisis.top_clientes.forEach(function (c) {
            const badgeDiasClass = (c.max_dias > 30) ? 'bg-danger text-white' : (c.max_dias >= 15 ? 'bg-warning text-dark' : 'bg-success text-white');
            listCli.append(`
                <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                    <span class="text-truncate me-2" title="${htmlEncode(c.nombre)}">• ${htmlEncode(c.nombre)}</span>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                        <span class="badge bg-secondary-lighten text-dark border px-2 py-1">${c.total} pend.</span>
                        <span class="badge ${badgeDiasClass} px-2 py-1" title="Mayor tiempo pendiente por devolver: ${c.max_dias} días">${c.max_dias} d.</span>
                    </div>
                </li>
            `);
        });
    } else {
        listCli.html('<li class="text-success small"><i class="fa-solid fa-check me-1"></i>No hay clientes con material pendiente</li>');
    }

    // Top Vendedores (Card B)
    const listVen = $('#list_top_vendedores');
    listVen.empty();
    if (analisis.top_vendedores && analisis.top_vendedores.length > 0) {
        analisis.top_vendedores.forEach(function (v) {
            const badgeDiasClass = (v.max_dias > 30) ? 'bg-danger text-white' : (v.max_dias >= 15 ? 'bg-warning text-dark' : 'bg-success text-white');
            listVen.append(`
                <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                    <span class="text-truncate me-2" title="${htmlEncode(v.nombre)}">• ${htmlEncode(v.nombre)}</span>
                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                        <span class="badge bg-secondary-lighten text-dark border px-2 py-1">${v.total} pend.</span>
                        <span class="badge ${badgeDiasClass} px-2 py-1" title="Mayor tiempo pendiente por devolver: ${v.max_dias} días">${v.max_dias} d.</span>
                    </div>
                </li>
            `);
        });
    } else {
        listVen.html('<li class="text-success small"><i class="fa-solid fa-check me-1"></i>No hay pendientes por vendedor</li>');
    }

    // Pases Críticos / Mayor Antigüedad (Card C)
    const listCrit = $('#list_pases_criticos');
    listCrit.empty();
    if (analisis.pases_criticos && analisis.pases_criticos.length > 0) {
        analisis.pases_criticos.forEach(function (p) {
            const badgeSem = (p.dias > 30) 
                ? `<span class="badge bg-danger px-2 py-1" title="Crítico: >30 días sin devolver">${p.dias} d.</span>` 
                : (p.dias >= 15 
                    ? `<span class="badge bg-warning text-dark px-2 py-1" title="Atención: 15 a 30 días sin devolver">${p.dias} d.</span>` 
                    : `<span class="badge bg-success text-white px-2 py-1" title="En tiempo: <15 días">${p.dias} d.</span>`);
            const fechaEnt = p.fecha_entrega_formateada || p.fecha_entrega || '';
            listCrit.append(`
                <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-light" style="cursor: pointer;" onclick="cargarDetallePase(${p.id})" title="Entrega: ${fechaEnt} | Motivo: ${htmlEncode(p.motivo)} | Clic para ver detalle">
                    <div class="text-truncate me-2">
                        <span class="fw-semibold ${p.dias > 30 ? 'text-danger' : 'text-dark'}">• ${p.folio}</span>
                        <span class="text-muted small">(${htmlEncode(p.cliente)})</span>
                    </div>
                    ${badgeSem}
                </li>
            `);
        });
    } else {
        listCrit.html('<li class="text-success small"><i class="fa-solid fa-check me-1"></i>Sin material pendiente por devolver</li>');
    }

    // Top Motivos (Card 4)
    const listMot = $('#list_top_motivos');
    listMot.empty();
    if (analisis.top_motivos && analisis.top_motivos.length > 0) {
        analisis.top_motivos.forEach(function (m) {
            listMot.append(`
                <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                    <span class="text-truncate me-2">• ${htmlEncode(m.motivo)}</span>
                    <span class="badge bg-primary px-2 py-1">${m.total} pases</span>
                </li>
            `);
        });
    } else {
        listMot.html('<li class="text-muted small">Sin datos disponibles</li>');
    }
}

/*==================================================================
[ 2. Panel de Detalle de Pase ]
==================================================================*/

function cargarDetallePase(id) {
    if (!id) return;
    selectedPaseId = id;

    // Resaltar fila seleccionada
    $('#tablePasesSalida tbody tr').removeClass('selected-row');

    $.ajax({
        url: base_url + '/almacen/getPaseSalidaDetalle',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        beforeSend: function () {
            $('#panelDetallePase').slideDown(200);
            $('#det_folio_title').text('Cargando...');
        },
        success: function (res) {
            if (!res.status || !res.header) {
                alertaPersonalizada('error', 'Error', res.msg || 'No se pudo cargar el detalle del pase.');
                return;
            }

            const h = res.header;
            currentPaseAdjuntos = res.adjuntos || [];

            // Título y Badges
            $('#det_folio_title').text(h.folio);
            $('#det_folio').text(h.folio);
            $('#det_fecha').text(h.fecha || '-');
            $('#det_proyecto').html(h.proyecto_id ? `<span class="badge bg-dark me-1">${h.proyecto_id}</span> ${htmlEncode(h.proyecto_titulo)}` : 'Sin proyecto');
            $('#det_cliente').text(h.nombre_cliente || 'Público General');
            $('#det_cliente_final').text(h.cliente_final || 'No especificado');
            $('#det_vendedor').text(h.nombre_vendedor || 'No asignado');
            $('#det_almacen').text(h.cdscalmacen || '-');
            $('#det_motivo').text(h.calidad_salida + (h.calidad_otros_especifique ? ` (${h.calidad_otros_especifique})` : ''));
            $('#det_observaciones').text(h.observaciones || 'Sin observaciones registradas.');

            // Semáforo
            const dias = parseInt(h.dias_transcurridos) || 0;
            let semBadge = '';
            if (dias < 15) {
                semBadge = `<span class="badge-semaforo-verde">🟢 ${dias} día${dias === 1 ? '' : 's'} transcurrido${dias === 1 ? '' : 's'}</span>`;
            } else if (dias <= 30) {
                semBadge = `<span class="badge-semaforo-amarillo">🟡 ${dias} días transcurridos</span>`;
            } else {
                semBadge = `<span class="badge-semaforo-rojo">🔴 ${dias} días transcurridos (Crítico)</span>`;
            }
            $('#det_semaforo_badge').html(semBadge);

            // Estatus
            let estBadge = '';
            if (h.estatus === 'ENTREGADO') {
                estBadge = `<span class="badge-status-entregado"><i class="fa-solid fa-check me-1"></i>ENTREGADO</span>`;
            } else if (h.estatus === 'CANCELADO') {
                estBadge = `<span class="badge-status-cancelado"><i class="fa-solid fa-xmark me-1"></i>CANCELADO</span>`;
            } else {
                estBadge = `<span class="badge-status-pendiente"><i class="fa-regular fa-clock me-1"></i>PENDIENTE</span>`;
            }
            $('#det_estatus_badge').html(estBadge);

            // Datos de Entrega y Firma
            $('#det_persona_recibio').text(h.nombre_recibio_salida || 'Aún no entregado');
            $('#det_fecha_entrega').text(h.fch_usuario_recibe || h.fchregistroactualiza || 'Pendiente');
            $('#det_usuario_recibe').text(h.ccveusuario_recibe || '-');

            // Renderizar Firma
            const firmaBox = $('#det_firma_box');
            firmaBox.empty();
            if (h.firma_recibe && h.firma_recibe.trim() !== '') {
                firmaBox.html(`
                    <img src="${h.firma_recibe}" alt="Firma Digital Recibido" class="signature-img-preview img-fluid">
                    <div class="mt-1 small text-success fw-bold"><i class="fa-solid fa-certificate me-1"></i>Firma Digital Validada</div>
                `);
            } else {
                firmaBox.html('<span class="text-muted fst-italic small">Sin firma digital registrada</span>');
            }

            // Botón dinámico para entregar si está pendiente
            const btnCont = $('#det_btn_entregar_container');
            btnCont.empty();
            if (h.estatus === 'PENDIENTE') {
                btnCont.html(`
                    <button type="button" class="btn btn-success w-100 fw-bold shadow-sm" onclick="abrirModalEntrega(${h.id})">
                        <i class="fa-solid fa-pen-nib me-1"></i> Registrar Entrega y Firma de Recibido
                    </button>
                `);
            }

            // Partidas
            const tbodyPartidas = $('#tbodyPartidasDetalle');
            tbodyPartidas.empty();
            if (res.partidas && res.partidas.length > 0) {
                res.partidas.forEach(function (part, idx) {
                    const cant = parseFloat(part.cantidad) || 0;
                    const ccn = part.ccn ? `<span class="badge bg-secondary">${htmlEncode(part.ccn)}</span>` : '-';
                    const estPart = (part.estatus == 1) ? '<span class="badge bg-success">Entregada</span>' : '<span class="badge bg-warning text-dark">Pendiente</span>';
                    tbodyPartidas.append(`
                        <tr>
                            <td class="text-center fw-bold">${part.partida || (idx + 1)}</td>
                            <td><span class="badge bg-dark">${htmlEncode(part.ccvematerial || '-')}</span></td>
                            <td>${ccn}</td>
                            <td><strong>${htmlEncode(part.descripcion || 'Sin descripción')}</strong></td>
                            <td class="text-center fw-bold text-primary">${cant}</td>
                            <td class="text-center">${htmlEncode(part.ccveunidad || 'Pz')}</td>
                            <td class="text-muted small">${htmlEncode(part.observaciones || '-')}</td>
                            <td class="text-center">${estPart}</td>
                        </tr>
                    `);
                });
            } else {
                tbodyPartidas.html('<tr><td colspan="8" class="text-center text-muted fst-italic py-3">No hay partidas registradas para este pase.</td></tr>');
            }

            // Adjuntos / Evidencias
            const contAdj = $('#containerAdjuntosDetalle');
            contAdj.empty();
            if (currentPaseAdjuntos.length > 0) {
                currentPaseAdjuntos.forEach(function (adj, idx) {
                    const tipo = (adj.tipo_archivo || '').toLowerCase();
                    let icon = 'fa-file';
                    let badgeTipo = `<span class="badge bg-secondary">Archivo</span>`;

                    if (tipo.includes('pdf')) {
                        icon = 'fa-file-pdf text-danger';
                        badgeTipo = `<span class="badge bg-danger">PDF</span>`;
                    } else if (tipo.includes('imag') || tipo.includes('png') || tipo.includes('jpg')) {
                        icon = 'fa-file-image text-primary';
                        badgeTipo = `<span class="badge bg-primary">Imagen</span>`;
                    } else if (tipo.includes('vid') || tipo.includes('mp4')) {
                        icon = 'fa-file-video text-warning';
                        badgeTipo = `<span class="badge bg-warning text-dark">Video</span>`;
                    }

                    contAdj.append(`
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="attachment-card p-2 bg-white d-flex align-items-center justify-content-between" style="cursor: pointer;" onclick="abrirModalEvidencias(${h.id}, ${idx})">
                                <div class="d-flex align-items-center overflow-hidden me-2">
                                    <i class="fa-solid ${icon} fs-3 me-2"></i>
                                    <div class="text-truncate" style="font-size: 0.85rem;">
                                        <div class="fw-bold text-dark text-truncate">${htmlEncode(adj.archivo)}</div>
                                        <div>${badgeTipo}</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" title="Previsualizar">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </button>
                            </div>
                        </div>
                    `);
                });
            } else {
                contAdj.html('<div class="col-12 text-muted fst-italic small py-2"><i class="fa-solid fa-circle-info me-1"></i>No hay evidencias documentales adjuntas para este pase.</div>');
            }

            // Scroll suave hacia el panel de detalle
            $('html, body').animate({
                scrollTop: $("#panelDetallePase").offset().top - 80
            }, 300);
        },
        error: function () {
            alertaPersonalizada('error', 'Error', 'Ocurrió un error al contactar con el servidor.');
        }
    });
}

function cerrarPanelDetalle() {
    $('#panelDetallePase').slideUp(200);
    selectedPaseId = null;
    $('#tablePasesSalida tbody tr').removeClass('selected-row');
}

/*==================================================================
[ 3. Firma Digital (HTML5 Canvas Signature Pad) ]
==================================================================*/

function initSignaturePad() {
    canvas = document.getElementById('canvasFirmaDigital');
    if (!canvas) return;

    ctx = canvas.getContext('2d');
    ajustarDimensionesCanvas();

    // Mouse events
    canvas.addEventListener('mousedown', function (e) {
        isDrawing = true;
        hasSignature = true;
        const pos = getPosicionCanvas(e);
        lastX = pos.x;
        lastY = pos.y;
    });

    canvas.addEventListener('mousemove', function (e) {
        if (!isDrawing) return;
        const pos = getPosicionCanvas(e);
        trazarLinea(lastX, lastY, pos.x, pos.y);
        lastX = pos.x;
        lastY = pos.y;
    });

    canvas.addEventListener('mouseup', function () {
        isDrawing = false;
    });

    canvas.addEventListener('mouseleave', function () {
        isDrawing = false;
    });

    // Touch events para móviles / tablets
    canvas.addEventListener('touchstart', function (e) {
        e.preventDefault();
        isDrawing = true;
        hasSignature = true;
        const pos = getPosicionTouch(e);
        lastX = pos.x;
        lastY = pos.y;
    }, { passive: false });

    canvas.addEventListener('touchmove', function (e) {
        e.preventDefault();
        if (!isDrawing) return;
        const pos = getPosicionTouch(e);
        trazarLinea(lastX, lastY, pos.x, pos.y);
        lastX = pos.x;
        lastY = pos.y;
    }, { passive: false });

    canvas.addEventListener('touchend', function (e) {
        e.preventDefault();
        isDrawing = false;
    }, { passive: false });

    canvas.addEventListener('touchcancel', function (e) {
        e.preventDefault();
        isDrawing = false;
    }, { passive: false });
}

function ajustarDimensionesCanvas() {
    if (!canvas || !ctx) return;
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;

    // Ancho real en píxeles del elemento
    canvas.width = (rect.width > 0 ? rect.width : 700) * dpr;
    canvas.height = 200 * dpr;
    ctx.scale(dpr, dpr);

    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
}

function getPosicionCanvas(e) {
    const rect = canvas.getBoundingClientRect();
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
}

function getPosicionTouch(e) {
    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0] || e.changedTouches[0];
    return {
        x: touch.clientX - rect.left,
        y: touch.clientY - rect.top
    };
}

function trazarLinea(x1, y1, x2, y2) {
    if (!ctx) return;
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
    ctx.closePath();
}

function limpiarCanvasFirma() {
    if (!canvas || !ctx) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasSignature = false;
}

function abrirModalEntrega(paseId) {
    if (!paseId) return;

    // Cargar datos del pase
    $.ajax({
        url: base_url + '/almacen/getPaseSalidaDetalle',
        type: 'POST',
        data: { id: paseId },
        dataType: 'json',
        success: function (res) {
            if (!res.status || !res.header) {
                alertaPersonalizada('error', 'Error', res.msg || 'No se pudo cargar la información del pase.');
                return;
            }

            const h = res.header;

            // Validar si ya está entregado
            if (h.estatus === 'ENTREGADO' && h.firma_recibe) {
                alertaPersonalizada('warning', 'Pase ya Entregado', `El pase de salida ${h.folio} ya fue entregado y cuenta con firma digital registrada.`);
                cargarDetallePase(paseId);
                return;
            }

            if (h.estatus === 'CANCELADO') {
                alertaPersonalizada('error', 'Pase Cancelado', 'No es posible registrar entrega en un pase cancelado.');
                return;
            }

            // Llenar datos en el modal
            $('#modal_entrega_pase_id').val(h.id);
            $('#modal_entrega_folio').text(h.folio);
            $('#modal_entrega_cliente').text(h.nombre_cliente || 'Público General');
            $('#modal_entrega_proyecto').text(h.proyecto_titulo || 'Sin proyecto');
            $('#modal_entrega_motivo').text(h.calidad_salida || '-');
            $('#modal_entrega_fecha').text(h.fecha || '-');
            // Inicializar Select2 si no se ha hecho
            initSelect2UsuarioRecibe();

            // Pre-seleccionar usuario que recibe en Select2
            const prevUser = (h.ccveusuario_recibe || '').trim();
            const prevNombre = (h.nombre_recibio_salida || '').trim();

            if (prevUser && $("#selectUsuarioRecibe option[value='" + prevUser + "']").length > 0) {
                $('#selectUsuarioRecibe').val(prevUser).trigger('change');
            } else if (prevNombre) {
                let foundVal = '';
                $('#selectUsuarioRecibe option').each(function () {
                    if ($(this).data('nombre') === prevNombre || $(this).text().indexOf(prevNombre) !== -1) {
                        foundVal = $(this).val();
                        return false;
                    }
                });

                if (foundVal) {
                    $('#selectUsuarioRecibe').val(foundVal).trigger('change');
                } else {
                    const newOpt = new Option(prevNombre, prevNombre, true, true);
                    $('#selectUsuarioRecibe').append(newOpt).trigger('change');
                }
            } else {
                $('#selectUsuarioRecibe').val('').trigger('change');
            }

            // Partidas
            const tbodyModal = $('#modal_entrega_tbody_partidas');
            tbodyModal.empty();
            if (res.partidas && res.partidas.length > 0) {
                res.partidas.forEach(function (part, idx) {
                    tbodyModal.append(`
                        <tr>
                            <td class="text-center">${part.partida || (idx + 1)}</td>
                            <td><strong>${htmlEncode(part.descripcion)}</strong> <small class="text-muted">(${part.ccvematerial})</small></td>
                            <td class="text-center fw-bold text-primary">${part.cantidad}</td>
                            <td class="text-center">${part.ccveunidad || 'Pz'}</td>
                        </tr>
                    `);
                });
            } else {
                tbodyModal.html('<tr><td colspan="4" class="text-center text-muted fst-italic">Sin partidas</td></tr>');
            }

            // Limpiar canvas
            limpiarCanvasFirma();

            // Mostrar modal
            const modalEl = document.getElementById('modalRegistrarEntrega');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            // Reajustar canvas y Select2 al mostrar modal
            $(modalEl).one('shown.bs.modal', function () {
                initSelect2UsuarioRecibe();
                ajustarDimensionesCanvas();
                limpiarCanvasFirma();
            });
        }
    });
}

function initSelect2UsuarioRecibe() {
    if ($.fn.select2) {
        if ($('#selectUsuarioRecibe').hasClass("select2-hidden-accessible")) {
            return;
        }
        $('#selectUsuarioRecibe').select2({
            dropdownParent: $('#modalRegistrarEntrega'),
            placeholder: '-- Seleccionar o escribir usuario que recibe --',
            allowClear: true,
            tags: true,
            width: '100%'
        });
    }
}

function guardarEntregaPase() {
    const paseId = $('#modal_entrega_pase_id').val();
    const selectEl = $('#selectUsuarioRecibe');
    const usuarioVal = (selectEl.val() || '').trim();
    const selectedOpt = selectEl.find('option:selected');
    
    let nombrePersona = selectedOpt.data('nombre') || '';
    if (!nombrePersona) {
        nombrePersona = usuarioVal;
    }

    if (!paseId) {
        mensajeAlertaModal({
            icon: 'error',
            title: iconMensajeError + ' ¡Identificador Inválido!',
            text: 'No se pudo identificar el pase de salida a procesar.',
            textButton: 'Cerrar',
            timer: 3500
        });
        return;
    }

    if (!usuarioVal) {
        mensajeAlertaModal({
            icon: 'info',
            title: iconMensajeInfo + ' ¡Campo Requerido!',
            text: 'Por favor seleccione o escriba el usuario o persona que recibe la mercancía.',
            textButton: 'Aceptar',
            timer: 3500
        });
        if ($.fn.select2) {
            selectEl.select2('open');
        }
        return;
    }

    if (!hasSignature || isCanvasEmpty(canvas)) {
        mensajeAlertaModal({
            icon: 'info',
            title: iconMensajeInfo + ' ¡Firma Obligatoria!',
            text: 'Debe dibujar la firma digital en el recuadro correspondiente antes de guardar la entrega.',
            textButton: 'Aceptar',
            timer: 3500
        });
        return;
    }

    // Obtener imagen en formato BASE64
    const firmaBase64 = canvas.toDataURL('image/png');

    // Confirmación previa usando mensajeAlertaModal de alertas.js
    mensajeAlertaModal({
        icon: 'warning',
        title: iconMensajeWarning + ' ¿Confirmar Entrega?',
        text: `¿Desea registrar formalmente la entrega del pase a <strong>${htmlEncode(nombrePersona)}</strong>?`,
        textButton: 'Sí, Confirmar',
        textCancelButton: 'No, Cancelar'
    }).then(function (result) {
        if (result && result.si) {
            enviarRegistroEntregaAjax(paseId, usuarioVal, nombrePersona, firmaBase64);
        }
    });
}

function isCanvasEmpty(cnv) {
    if (!cnv) return true;
    const blank = document.createElement('canvas');
    blank.width = cnv.width;
    blank.height = cnv.height;
    return cnv.toDataURL() === blank.toDataURL();
}

function enviarRegistroEntregaAjax(paseId, usuarioRecibe, nombreRecibe, firmaBase64) {
    $.ajax({
        url: base_url + '/almacen/registrarEntregaPase',
        type: 'POST',
        data: {
            pase_id: paseId,
            usuario_recibe: usuarioRecibe,
            nombre_recibe: nombreRecibe,
            firma_base64: firmaBase64
        },
        dataType: 'json',
        beforeSend: function () {
            $('#btnConfirmarGuardarEntrega').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...');
        },
        success: function (res) {
            $('#btnConfirmarGuardarEntrega').prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Confirmar y Guardar Entrega');

            if (res.status) {
                // 1. Cerrar modal
                const modalEl = document.getElementById('modalRegistrarEntrega');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }

                // 2. Si el filtro de estatus estaba en PENDIENTE, cambiar a todos para ver el cambio
                if ($('#selectFiltroEstatus').val() === 'PENDIENTE') {
                    $('#selectFiltroEstatus').val('');
                }

                // 3. Recargar inmediatamente DataTables principal
                if (tablePases) {
                    tablePases.ajax.reload(function (json) {
                        if (json && json.status) {
                            actualizarKpisPases(json.kpis);
                            actualizarAnalisisEjecutivo(json.analisis);
                        }
                    }, false);
                } else if ($.fn.DataTable.isDataTable('#tablePasesSalida')) {
                    $('#tablePasesSalida').DataTable().ajax.reload(null, false);
                }

                // 4. Recargar tablas de reportes
                recargarReportesTablas();

                // 5. Recargar panel de detalle
                if (paseId) {
                    cargarDetallePase(paseId);
                }

                // 6. Notificar al usuario con alerta_success de alertas.js
                alerta_success({
                    mostrar_mensaje: true,
                    tiempo: 3500,
                    mensaje: res.msg || 'Entrega y firma digital registradas correctamente.'
                }, "");
            } else {
                alerta_error({
                    mostrar_mensaje: true,
                    tiempo: 4000,
                    mensaje: res.msg || 'No se pudo guardar la entrega.'
                }, "");
            }
        },
        error: function () {
            $('#btnConfirmarGuardarEntrega').prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Confirmar y Guardar Entrega');
            alerta_error({
                mostrar_mensaje: true,
                tiempo: 4000,
                mensaje: 'Error de red: no se pudo conectar con el servidor.'
            }, "");
        }
    });
}

/*==================================================================
[ 4. Motor Modular de Video HTML5: Video.js (Principal) + Plyr ]
==================================================================*/

const VideoPlayerEngine = {
    currentEngine: 'videojs', // 'videojs' (preferente) | 'plyr' | 'native'
    activePlayer: null,
    watchdogTimer: null,
    currentOptions: null,

    /**
     * Permite alternar el motor de reproducción (Video.js, Plyr, HTML5 Nativo)
     */
    setEngine: function (engineName) {
        if (['videojs', 'plyr', 'native'].includes(engineName)) {
            this.currentEngine = engineName;
            if (this.currentOptions && this.currentOptions.container) {
                this.init(this.currentOptions.container, this.currentOptions);
            }
        }
    },

    getEngine: function () {
        return this.currentEngine;
    },

    /**
     * Destruye de forma segura cualquier instancia activa del reproductor
     * Previene fugas de memoria, audio residual y errores de colisión en el DOM.
     */
    destroy: function () {
        if (this.watchdogTimer) {
            clearTimeout(this.watchdogTimer);
            this.watchdogTimer = null;
        }

        if (this.activePlayer) {
            try {
                if (this.currentEngine === 'videojs' && typeof this.activePlayer.dispose === 'function') {
                    if (!this.activePlayer.isDisposed()) {
                        this.activePlayer.pause();
                        this.activePlayer.dispose();
                    }
                } else if (this.currentEngine === 'plyr' && typeof this.activePlayer.destroy === 'function') {
                    this.activePlayer.destroy();
                } else if (this.activePlayer.pause) {
                    this.activePlayer.pause();
                    this.activePlayer.removeAttribute('src');
                    if (typeof this.activePlayer.load === 'function') {
                        this.activePlayer.load();
                    }
                }
            } catch (err) {
                console.warn('[VideoPlayerEngine] Error al destruir reproductor anterior:', err);
            }
            this.activePlayer = null;
        }
    },

    /**
     * Inicializa el reproductor sobre el contenedor indicado
     */
    init: function (containerSelector, options) {
        const self = this;
        self.destroy();

        const $container = $(containerSelector);
        if (!$container.length) return;

        self.currentOptions = Object.assign({
            container: containerSelector,
            url: '',
            fileName: 'video.mp4',
            type: 'video',
            autoplay: true,
            mimeType: 'video/mp4'
        }, options || {});

        const url = self.currentOptions.url || '';
        const fileName = self.currentOptions.fileName || 'video.mp4';

        // Detección automática del tipo MIME según extensión
        let mime = 'video/mp4';
        const lowerUrl = url.toLowerCase();
        const lowerName = fileName.toLowerCase();
        if (lowerUrl.includes('.webm') || lowerName.endsWith('.webm')) mime = 'video/webm';
        else if (lowerUrl.includes('.ogg') || lowerName.endsWith('.ogg') || lowerUrl.includes('.ogv')) mime = 'video/ogg';
        else if (lowerUrl.includes('.mov') || lowerName.endsWith('.mov')) mime = 'video/mp4';

        $container.empty();

        // 1. Alternativa modular: Plyr
        if (self.currentEngine === 'plyr' && typeof Plyr !== 'undefined') {
            self.initPlyr($container, url, mime, fileName);
            return;
        }

        // 2. Opción preferente: Video.js
        if (typeof videojs !== 'undefined' && self.currentEngine !== 'native') {
            self.initVideoJs($container, url, mime, fileName);
            return;
        }

        // 3. Respaldo: HTML5 Nativo
        self.initNative($container, url, mime, fileName);
    },

    /**
     * Inicialización moderna basada en Video.js v8+
     */
    initVideoJs: function ($container, url, mime, fileName) {
        const self = this;
        const playerId = 'visorVideoJsPlayer_' + Date.now();

        const html = `
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="video-player-wrapper">
                    <video-js id="${playerId}" 
                              class="video-js vjs-default-skin vjs-big-play-centered" 
                              controls 
                              preload="auto" 
                              playsinline>
                        <source src="${url}" type="${mime}">
                        <p class="vjs-no-js">
                            Para ver este video, por favor habilite JavaScript y considere actualizar su navegador.
                        </p>
                    </video-js>
                    <div id="${playerId}_loadingWatchdog" class="video-loading-badge d-none">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>El video está tardando más de lo habitual en responder. Verificando conexión...</span>
                    </div>
                </div>
                <div class="mt-2 flex-shrink-0 d-flex justify-content-center align-items-center gap-2 flex-wrap">
                    <a href="${url}" target="_blank" download="${htmlEncode(fileName)}" class="btn btn-sm btn-outline-light">
                        <i class="fa-solid fa-download me-1"></i> Descargar Video
                    </a>
                    <a href="${url}" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir en Pestaña
                    </a>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Motor del reproductor">
                            <i class="fa-solid fa-sliders me-1"></i> Motor: <strong>Video.js</strong>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">Alternar Motor de Reproducción</h6></li>
                            <li><a class="dropdown-item active" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('videojs')"><i class="fa-solid fa-check me-2"></i>Video.js (Preferente)</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('plyr')"><i class="fa-solid fa-play me-2"></i>Plyr (Alternativa)</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('native')"><i class="fa-brands fa-html5 me-2"></i>HTML5 Nativo</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        `;

        $container.html(html);

        try {
            const player = videojs(playerId, {
                controls: true,
                autoplay: self.currentOptions.autoplay !== false,
                preload: 'auto',
                fluid: false,
                responsive: false,
                language: 'es',
                playbackRates: [0.5, 0.75, 1, 1.25, 1.5, 2],
                controlBar: {
                    children: [
                        'playToggle',
                        'volumePanel',
                        'currentTimeDisplay',
                        'timeDivider',
                        'durationDisplay',
                        'progressControl',
                        'playbackRateMenuButton',
                        'fullscreenToggle'
                    ]
                }
            });

            self.activePlayer = player;

            // Watchdog para detectar demoras de red (10s)
            self.watchdogTimer = setTimeout(function () {
                const badge = document.getElementById(`${playerId}_loadingWatchdog`);
                if (badge && !player.hasStarted() && !player.error()) {
                    $(badge).removeClass('d-none');
                }
            }, 10000);

            player.on('canplay', function () {
                if (self.watchdogTimer) clearTimeout(self.watchdogTimer);
                $(`#${playerId}_loadingWatchdog`).addClass('d-none');
            });

            player.on('playing', function () {
                if (self.watchdogTimer) clearTimeout(self.watchdogTimer);
                $(`#${playerId}_loadingWatchdog`).addClass('d-none');
            });

            // Manejador centralizado de errores de reproducción
            player.on('error', function () {
                if (self.watchdogTimer) clearTimeout(self.watchdogTimer);
                const mediaErr = player.error();
                let errorTitle = 'No se pudo reproducir el video';
                let errorMsg = 'Ocurrió un error inesperado al cargar o procesar el archivo de video.';
                let errorCode = mediaErr ? mediaErr.code : 0;

                if (errorCode === 1) { // MEDIA_ERR_ABORTED
                    errorTitle = 'Reproducción Cancelada';
                    errorMsg = 'La reproducción del video fue cancelada por el usuario o por el navegador.';
                } else if (errorCode === 2) { // MEDIA_ERR_NETWORK
                    errorTitle = 'Error de Conexión / Red';
                    errorMsg = 'No fue posible descargar el archivo de video. Verifique su conexión de red o la disponibilidad del servidor.';
                } else if (errorCode === 3) { // MEDIA_ERR_DECODE
                    errorTitle = 'Archivo Dañado o Códec No Compatible';
                    errorMsg = 'El archivo de video parece estar incompleto o codificado en un formato incompatible con este navegador.';
                } else if (errorCode === 4) { // MEDIA_ERR_SRC_NOT_SUPPORTED
                    errorTitle = 'Video No Encontrado o Formato No Soportado';
                    errorMsg = 'El archivo no fue encontrado en la ruta indicada (Error 404) o el formato no es admitido por el motor de reproducción.';
                }

                self.renderErrorCard($container, errorTitle, errorMsg, url, fileName, errorCode);
            });

        } catch (e) {
            console.error('[VideoPlayerEngine] Error al inicializar Video.js:', e);
            self.renderErrorCard($container, 'Error de Inicialización', 'No fue posible inicializar Video.js en este navegador.', url, fileName, 0);
        }
    },

    /**
     * Inicialización alternativa modular basada en Plyr
     */
    initPlyr: function ($container, url, mime, fileName) {
        const self = this;
        const videoElId = 'visorPlyrVideo_' + Date.now();

        const html = `
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="video-player-wrapper">
                    <video id="${videoElId}" playsinline controls class="w-100 h-100" style="object-fit: contain; background: #000;">
                        <source src="${url}" type="${mime}">
                        <p>Su navegador no soporta reproducción directa de video HTML5.</p>
                    </video>
                    <div id="${videoElId}_loadingWatchdog" class="video-loading-badge d-none">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>El video está tardando más de lo habitual en responder. Verificando conexión...</span>
                    </div>
                </div>
                <div class="mt-2 flex-shrink-0 d-flex justify-content-center align-items-center gap-2 flex-wrap">
                    <a href="${url}" target="_blank" download="${htmlEncode(fileName)}" class="btn btn-sm btn-outline-light">
                        <i class="fa-solid fa-download me-1"></i> Descargar Video
                    </a>
                    <a href="${url}" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir en Pestaña
                    </a>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Motor del reproductor">
                            <i class="fa-solid fa-sliders me-1"></i> Motor: <strong>Plyr</strong>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">Alternar Motor de Reproducción</h6></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('videojs')"><i class="fa-solid fa-play me-2"></i>Video.js (Preferente)</a></li>
                            <li><a class="dropdown-item active" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('plyr')"><i class="fa-solid fa-check me-2"></i>Plyr (Alternativa)</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('native')"><i class="fa-brands fa-html5 me-2"></i>HTML5 Nativo</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        `;

        $container.html(html);

        try {
            const plyrInstance = new Plyr(`#${videoElId}`, {
                autoplay: self.currentOptions.autoplay !== false,
                controls: [
                    'play-large',
                    'play',
                    'progress',
                    'current-time',
                    'duration',
                    'mute',
                    'volume',
                    'settings',
                    'pip',
                    'airplay',
                    'fullscreen'
                ],
                i18n: {
                    restart: 'Reiniciar',
                    rewind: 'Retroceder {seektime}s',
                    play: 'Reproducir',
                    pause: 'Pausar',
                    fastForward: 'Adelantar {seektime}s',
                    seek: 'Buscar',
                    seekLabel: '{currentTime} de {duration}',
                    played: 'Reproducido',
                    buffered: 'Cargado',
                    currentTime: 'Tiempo actual',
                    duration: 'Duración',
                    volume: 'Volumen',
                    mute: 'Silenciar',
                    unmute: 'Activar sonido',
                    enableCaptions: 'Activar subtítulos',
                    disableCaptions: 'Desactivar subtítulos',
                    download: 'Descargar',
                    enterFullscreen: 'Pantalla completa',
                    exitFullscreen: 'Salir de pantalla completa',
                    frameTitle: 'Reproductor para {title}',
                    captions: 'Subtítulos',
                    settings: 'Ajustes',
                    speed: 'Velocidad',
                    normal: 'Normal',
                    quality: 'Calidad',
                    loop: 'Repetir'
                }
            });

            self.activePlayer = plyrInstance;

            const videoElem = document.getElementById(videoElId);
            if (videoElem) {
                videoElem.addEventListener('error', function () {
                    const mediaErr = videoElem.error;
                    let errorTitle = 'No se pudo reproducir el video';
                    let errorMsg = 'Ocurrió un error inesperado al cargar el video en Plyr.';
                    let code = mediaErr ? mediaErr.code : 0;
                    if (code === 2) {
                        errorTitle = 'Error de Red / Conexión';
                        errorMsg = 'No fue posible descargar el video desde el servidor.';
                    } else if (code === 3) {
                        errorTitle = 'Archivo Dañado / No Decodificable';
                        errorMsg = 'El archivo de video está dañado o su codec no es compatible.';
                    } else if (code === 4) {
                        errorTitle = 'Video No Encontrado o Formato Incompatible';
                        errorMsg = 'El archivo no existe en el servidor (404) o el formato no es compatible.';
                    }
                    self.renderErrorCard($container, errorTitle, errorMsg, url, fileName, code);
                });
            }
        } catch (e) {
            console.error('[VideoPlayerEngine] Error en Plyr:', e);
            self.renderErrorCard($container, 'Fallo de Inicialización Plyr', 'No fue posible iniciar el reproductor Plyr.', url, fileName, 0);
        }
    },

    /**
     * Respaldo HTML5 estándar
     */
    initNative: function ($container, url, mime, fileName) {
        const self = this;
        const nativeId = 'visorNativeVideo_' + Date.now();
        const html = `
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="video-player-wrapper text-center">
                    <video id="${nativeId}" controls autoplay class="rounded shadow-lg w-100 h-100" style="max-height: 100%; object-fit: contain; background: #000;">
                        <source src="${url}" type="${mime}">
                        Su navegador no soporta reproducción directa de video HTML5.
                    </video>
                </div>
                <div class="mt-2 flex-shrink-0 d-flex justify-content-center align-items-center gap-2 flex-wrap">
                    <a href="${url}" target="_blank" download="${htmlEncode(fileName)}" class="btn btn-sm btn-outline-light">
                        <i class="fa-solid fa-download me-1"></i> Descargar Video
                    </a>
                    <a href="${url}" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir en Pestaña
                    </a>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Motor del reproductor">
                            <i class="fa-solid fa-sliders me-1"></i> Motor: <strong>HTML5 Nativo</strong>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header">Alternar Motor de Reproducción</h6></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('videojs')"><i class="fa-solid fa-play me-2"></i>Video.js (Preferente)</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('plyr')"><i class="fa-solid fa-play me-2"></i>Plyr (Alternativa)</a></li>
                            <li><a class="dropdown-item active" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('native')"><i class="fa-solid fa-check me-2"></i>HTML5 Nativo</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        `;
        $container.html(html);

        const videoElem = document.getElementById(nativeId);
        self.activePlayer = videoElem;
        if (videoElem) {
            videoElem.addEventListener('error', function () {
                const mediaErr = videoElem.error;
                let errorTitle = 'No se pudo reproducir el video';
                let errorMsg = 'Ocurrió un error al cargar el video nativo.';
                let code = mediaErr ? mediaErr.code : 0;
                if (code === 4) {
                    errorTitle = 'Video No Encontrado o No Compatible';
                    errorMsg = 'El archivo no existe (404) o el formato no es compatible.';
                }
                self.renderErrorCard($container, errorTitle, errorMsg, url, fileName, code);
            });
        }
    },

    /**
     * Renderiza tarjeta amigable e interactiva cuando ocurre un error de reproducción
     */
    renderErrorCard: function ($container, title, message, url, fileName, code) {
        const self = this;
        self.destroy();

        const cardHtml = `
            <div class="video-error-card">
                <div class="video-error-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h4 class="fw-bold mb-2 text-white">${htmlEncode(title)}</h4>
                <p class="text-white-50 mb-3" style="font-size: 0.92rem; line-height: 1.5;">
                    ${htmlEncode(message)}
                </p>
                <div class="p-3 mb-3 rounded bg-black bg-opacity-50 border border-secondary border-opacity-50 text-start" style="font-size: 0.8rem;">
                    <div class="mb-1"><strong class="text-secondary">Archivo:</strong> <span class="text-light">${htmlEncode(fileName)}</span></div>
                    <div class="text-truncate mb-1"><strong class="text-secondary">Ruta / URL:</strong> <span class="text-light">${htmlEncode(url)}</span></div>
                    ${code ? `<div><strong class="text-secondary">Código HTML5:</strong> <span class="badge bg-danger text-uppercase">Error de Reproducción ${code}</span></div>` : ''}
                </div>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary btn-sm px-3" onclick="VideoPlayerEngine.init('${self.currentOptions ? self.currentOptions.container : '#visorDisplayContainer'}', ${JSON.stringify(self.currentOptions || {}).replace(/"/g, '&quot;')})">
                        <i class="fa-solid fa-rotate-right me-1"></i> Reintentar Carga
                    </button>
                    <a href="${url}" target="_blank" download="${htmlEncode(fileName)}" class="btn btn-outline-light btn-sm px-3">
                        <i class="fa-solid fa-download me-1"></i> Descargar Archivo
                    </a>
                    <a href="${url}" target="_blank" class="btn btn-outline-info btn-sm px-3">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir Enlace
                    </a>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-sliders me-1"></i> Probar otro motor
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                            <li><a class="dropdown-item ${self.currentEngine === 'videojs' ? 'active' : ''}" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('videojs')">Video.js</a></li>
                            <li><a class="dropdown-item ${self.currentEngine === 'plyr' ? 'active' : ''}" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('plyr')">Plyr</a></li>
                            <li><a class="dropdown-item ${self.currentEngine === 'native' ? 'active' : ''}" href="javascript:void(0);" onclick="VideoPlayerEngine.setEngine('native')">HTML5 Nativo</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        `;
        $container.html(cardHtml);
    }
};

/*==================================================================
[ 4.1 Modal Visor de Evidencias Multimedia (PDF, Imágenes, Video) ]
==================================================================*/

function abrirModalEvidencias(paseId, activeIndex = 0) {
    if (!paseId) return;

    // Asegurar limpieza de reproductor previo
    VideoPlayerEngine.destroy();

    $.ajax({
        url: base_url + '/almacen/getAdjuntosPase',
        type: 'POST',
        data: { pase_id: paseId },
        dataType: 'json',
        success: function (res) {
            if (!res.status || !res.adjuntos || res.adjuntos.length === 0) {
                alertaPersonalizada('info', 'Sin Archivos', 'Este pase de salida no cuenta con archivos adjuntos o evidencias registradas.');
                return;
            }

            const adjuntos = res.adjuntos;
            const listaEl = $('#visorListaArchivos');
            listaEl.empty();

            adjuntos.forEach(function (adj, idx) {
                const tipo = (adj.tipo_archivo || '').toLowerCase();
                let icon = 'fa-file';
                if (tipo.includes('pdf')) icon = 'fa-file-pdf text-danger';
                else if (tipo.includes('imag') || tipo.includes('png') || tipo.includes('jpg')) icon = 'fa-file-image text-primary';
                else if (tipo.includes('vid') || tipo.includes('mp4')) icon = 'fa-file-video text-warning';

                const activeClass = (idx === activeIndex) ? 'active' : '';
                listaEl.append(`
                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center ${activeClass}" 
                            onclick="mostrarArchivoEnVisor(${JSON.stringify(adj).replace(/"/g, '&quot;')}, this)">
                        <i class="fa-solid ${icon} fs-4 me-2"></i>
                        <div class="text-truncate">
                            <div class="fw-bold text-truncate" style="font-size: 0.85rem;">${htmlEncode(adj.archivo)}</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.72rem;">${tipo}</small>
                        </div>
                    </button>
                `);
            });

            // Mostrar modal
            const modalEl = document.getElementById('modalVisorEvidencias');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            // Mostrar el primer archivo
            mostrarArchivoEnVisor(adjuntos[activeIndex] || adjuntos[0]);
        }
    });
}

function mostrarArchivoEnVisor(adj, btnEl) {
    // 1. Destruir cualquier reproductor de video previo inmediatamente
    VideoPlayerEngine.destroy();

    if (btnEl) {
        $('#visorListaArchivos .list-group-item').removeClass('active');
        $(btnEl).addClass('active');
    }

    const container = $('#visorDisplayContainer');
    const infoEl = $('#visorFileInfo');
    container.empty();

    if (!adj || !adj.url) {
        container.html('<span class="text-muted">Archivo no disponible</span>');
        return;
    }

    const tipo = (adj.tipo_archivo || '').toLowerCase();
    const url = adj.url;
    const fileName = adj.archivo || 'Archivo';

    infoEl.html(`<strong>Archivo:</strong> ${htmlEncode(fileName)} | <strong>Tipo:</strong> ${tipo.toUpperCase()}`);

    if (tipo.includes('pdf') || fileName.match(/\.pdf$/i)) {
        // Visor de PDF embebido
        container.html(`
            <div class="w-100 h-100 d-flex flex-column" style="height: 100%;">
                <div class="mb-2 text-end flex-shrink-0">
                    <a href="${url}" target="_blank" class="btn btn-sm btn-outline-light">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir en Nueva Pestaña
                    </a>
                </div>
                <iframe src="${url}" class="w-100 flex-grow-1 rounded border-0" style="min-height: 380px; height: 100%;"></iframe>
            </div>
        `);
    } else if (tipo.includes('vid') || tipo.includes('mp4') || fileName.match(/\.(mp4|webm|ogg|mov|m4v)$/i)) {
        // Reproductor de Video HTML5 con VideoPlayerEngine (Video.js preferente / Plyr)
        VideoPlayerEngine.init('#visorDisplayContainer', {
            url: url,
            fileName: fileName,
            type: tipo,
            autoplay: true
        });
    } else {
        // Visor de Imágenes con zoom ligero
        container.html(`
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center">
                <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="min-height: 0; max-height: calc(72vh - 90px);">
                    <img src="${url}" alt="${htmlEncode(fileName)}" class="img-fluid rounded shadow-lg" style="max-height: 460px; max-width: 100%; object-fit: contain;">
                </div>
                <div class="mt-2 flex-shrink-0">
                    <a href="${url}" target="_blank" class="btn btn-sm btn-outline-light">
                        <i class="fa-solid fa-expand me-1"></i> Ver Imagen Completa
                    </a>
                </div>
            </div>
        `);
    }
}

/*==================================================================
[ 5. Reportes por Cliente y por Vendedor ]
==================================================================*/

function initReportesTablas() {
    // Tabla Reporte por Cliente
    tableReporteCli = $('#tableReporteCliente').DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        responsive: true,
        order: [[3, "desc"], [6, "desc"]], // Pendientes desc, promedio desc
        pageLength: 10,
        language: { url: assets + "/vendor/datatable/es-ES.json" },
        ajax: {
            url: base_url + '/almacen/getReportePases',
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, obtenerValoresFiltros());
            },
            dataSrc: function (json) {
                return json.por_cliente || [];
            }
        },
        columns: [
            {
                data: 'cliente',
                render: function (data) {
                    return `<strong>${htmlEncode(data)}</strong>`;
                }
            },
            {
                data: 'motivo_salida',
                render: function (data) {
                    return `<span class="badge bg-secondary">${htmlEncode(data)}</span>`;
                }
            },
            {
                data: 'total_pases',
                className: 'text-center fw-bold text-primary'
            },
            {
                data: 'pendientes',
                className: 'text-center fw-bold',
                render: function (data) {
                    const cnt = parseInt(data) || 0;
                    return cnt > 0 ? `<span class="badge bg-warning text-dark">${cnt}</span>` : '<span class="text-muted">0</span>';
                }
            },
            {
                data: 'entregados',
                className: 'text-center fw-bold text-success'
            },
            {
                data: 'suma_dias',
                className: 'text-center'
            },
            {
                data: 'promedio_dias',
                className: 'text-center fw-bold',
                render: function (data) {
                    const avg = parseFloat(data) || 0;
                    let color = 'text-success';
                    if (avg > 30) color = 'text-danger';
                    else if (avg >= 15) color = 'text-warning';
                    return `<span class="${color}">${avg} d.</span>`;
                }
            },
            {
                data: 'pases_mas_30',
                className: 'text-center fw-bold',
                render: function (data) {
                    const cnt = parseInt(data) || 0;
                    return cnt > 0 ? `<span class="badge bg-danger">${cnt}</span>` : '<span class="text-muted">0</span>';
                }
            }
        ]
    });

    // Tabla Reporte por Vendedor
    tableReporteVen = $('#tableReporteVendedor').DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        responsive: true,
        order: [[3, "desc"], [6, "desc"]], // Pendientes desc, promedio desc
        pageLength: 10,
        language: { url: assets + "/vendor/datatable/es-ES.json" },
        ajax: {
            url: base_url + '/almacen/getReportePases',
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, obtenerValoresFiltros());
            },
            dataSrc: function (json) {
                return json.por_vendedor || [];
            }
        },
        columns: [
            {
                data: 'vendedor',
                render: function (data) {
                    return `<strong>${htmlEncode(data)}</strong>`;
                }
            },
            {
                data: 'motivo_salida',
                render: function (data) {
                    return `<span class="badge bg-secondary">${htmlEncode(data)}</span>`;
                }
            },
            {
                data: 'total_pases',
                className: 'text-center fw-bold text-primary'
            },
            {
                data: 'pendientes',
                className: 'text-center fw-bold',
                render: function (data) {
                    const cnt = parseInt(data) || 0;
                    return cnt > 0 ? `<span class="badge bg-warning text-dark">${cnt}</span>` : '<span class="text-muted">0</span>';
                }
            },
            {
                data: 'entregados',
                className: 'text-center fw-bold text-success'
            },
            {
                data: 'suma_dias',
                className: 'text-center'
            },
            {
                data: 'promedio_dias',
                className: 'text-center fw-bold',
                render: function (data) {
                    const avg = parseFloat(data) || 0;
                    let color = 'text-success';
                    if (avg > 30) color = 'text-danger';
                    else if (avg >= 15) color = 'text-warning';
                    return `<span class="${color}">${avg} d.</span>`;
                }
            },
            {
                data: 'pases_mas_30',
                className: 'text-center fw-bold',
                render: function (data) {
                    const cnt = parseInt(data) || 0;
                    return cnt > 0 ? `<span class="badge bg-danger">${cnt}</span>` : '<span class="text-muted">0</span>';
                }
            }
        ]
    });
}

function recargarReportesTablas() {
    if (tableReporteCli) tableReporteCli.ajax.reload();
    if (tableReporteVen) tableReporteVen.ajax.reload();
}

/*==================================================================
[ 6. Utilidades Generales ]
==================================================================*/

function htmlEncode(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function alertaPersonalizada(tipo, titulo, mensaje) {
    if (typeof mensajeAlertaModal === 'function') {
        let iconType = 'info';
        let iconHtml = typeof iconMensajeInfo !== 'undefined' ? iconMensajeInfo : '';

        if (tipo === 'error' || tipo === 'danger') {
            iconType = 'error';
            iconHtml = typeof iconMensajeError !== 'undefined' ? iconMensajeError : '';
        } else if (tipo === 'warning') {
            iconType = 'warning';
            iconHtml = typeof iconMensajeWarning !== 'undefined' ? iconMensajeWarning : '';
        } else if (tipo === 'success') {
            iconType = 'success';
            iconHtml = typeof iconMensajeSuccess !== 'undefined' ? iconMensajeSuccess : '';
        }

        mensajeAlertaModal({
            icon: iconType,
            title: iconHtml + ' ' + (titulo || '¡Atención!'),
            text: mensaje,
            textButton: 'Cerrar',
            timer: 3500
        });
    } else {
        alert((titulo ? titulo + ': ' : '') + mensaje);
    }
}
