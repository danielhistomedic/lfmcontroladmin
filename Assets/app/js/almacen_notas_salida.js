/*==================================================================
[ JS: Almacén - Reporte de Notas de Salida ]
==================================================================*/

var tableNotas = null;
var selectedNotaId = null;

// Signature Pad state
var canvasNota = null;
var ctxNota = null;
var isDrawingNota = false;
var hasSignatureNota = false;
var lastXNota = 0;
var lastYNota = 0;

document.addEventListener("DOMContentLoaded", function () {
    // 1. Inicializar DataTables Principal
    initDataTableNotas();

    // 2. Inicializar Signature Pad para Notas
    initSignaturePadNota();

    // 3. Inicializar selector de Usuario Recibe
    initSelectUsuarioRecibeNota();

    // 4. Eventos de Filtros
    const btnAplicar = document.getElementById('btnAplicarFiltros');
    if (btnAplicar) {
        btnAplicar.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarBusquedaNotas();
        });
    }

    const btnLimpiar = document.getElementById('btnLimpiarFiltros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function (e) {
            e.preventDefault();
            limpiarFiltrosNotas();
        });
    }

    // Auto-búsqueda en selects
    ['selectFiltroEstatus', 'selectFiltroFirma', 'selectFiltroAlmacen', 'selectFiltroTipoDocto'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                ejecutarBusquedaNotas();
            });
        }
    });

    // Enter y cambio en input de cliente
    const txtCliente = document.getElementById('txtFiltroCliente');
    if (txtCliente) {
        txtCliente.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                ejecutarBusquedaNotas();
            }
        });
        txtCliente.addEventListener('change', function () {
            ejecutarBusquedaNotas();
        });
    }

    // Enter en input de búsqueda libre
    const txtBusqueda = document.getElementById('txtFiltroBusqueda');
    if (txtBusqueda) {
        txtBusqueda.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                ejecutarBusquedaNotas();
            }
        });
    }

    // Botón borrar firma en modal
    const btnBorrarFirma = document.getElementById('btnLimpiarCanvasFirmaNota');
    if (btnBorrarFirma) {
        btnBorrarFirma.addEventListener('click', function (e) {
            e.preventDefault();
            limpiarCanvasFirmaNota();
        });
        btnBorrarFirma.addEventListener('touchend', function (e) {
            e.preventDefault();
            limpiarCanvasFirmaNota();
        }, { passive: false });
    }

    // Botón confirmar firma en modal
    const btnConfirmarFirma = document.getElementById('btnConfirmarGuardarFirmaNota');
    if (btnConfirmarFirma) {
        btnConfirmarFirma.addEventListener('click', function () {
            guardarFirmaNota();
        });
    }
});

/*==================================================================
[ 1. DataTables Principal de Notas de Salida ]
==================================================================*/

function initDataTableNotas() {
    const tableEl = '#tableNotasSalida';

    if ($.fn.DataTable.isDataTable(tableEl)) {
        $(tableEl).DataTable().destroy();
        $(tableEl).find('tbody').empty();
    }

    tableNotas = $(tableEl).DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        responsive: true,
        order: [[3, "desc"], [2, "desc"]], // Ordenar por fecha desc, luego folio desc
        iDisplayLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Todos"]
        ],
        language: idioma_espanol,
        ajax: {
            url: base_url + '/almacen/getNotasSalida',
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, obtenerValoresFiltrosNotas());
            },
            dataSrc: function (json) {
                if (json && json.status) {
                    actualizarKpisNotas(json.kpis);
                    actualizarAnalisisNotas(json.analisis);
                    return json.data || [];
                }
                return [];
            }
        },
        columns: [
            // ========================================================
            // COLUMNA 1: ESTATUS (Requerimiento del usuario)
            // ========================================================
            {
                data: 'estatus',
                className: 'text-center',
                orderable: true,
                render: function (data, type, row) {
                    const est = (data || '').toUpperCase();
                    if (est === 'CONTABILIZADA') {
                        return `<span class="badge-status-contabilizada"><i class="fa-solid fa-check-double"></i> Contabilizada</span>`;
                    } else if (est === 'EN PROCESO') {
                        return `<span class="badge-status-en-proceso"><i class="fa-solid fa-hourglass-half"></i> En Proceso</span>`;
                    } else if (est === 'CANCELADA') {
                        return `<span class="badge-status-cancelada"><i class="fa-solid fa-ban"></i> Cancelada</span>`;
                    }
                    return `<span class="badge bg-secondary">${htmlEncode(data)}</span>`;
                }
            },
            // ========================================================
            // COLUMNA 2: OPCIONES (Requerimiento del usuario)
            // ========================================================
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function (data, type, row) {
                    const esCancelada = (row.estatus === 'CANCELADA');
                    const tieneFirma = (parseInt(row.tiene_firma) === 1);

                    let btnFirmar = '';
                    if (!tieneFirma && !esCancelada) {
                        btnFirmar = `
                            <button type="button" class="btn btn-sm btn-success py-1 px-2" 
                                    onclick="abrirModalFirmaNota(${row.id}); event.stopPropagation();" 
                                    title="Firmar Salida (Acuse Digital)">
                                <i class="fa-solid fa-signature"></i>
                            </button>
                        `;
                    } else if (tieneFirma) {
                        btnFirmar = `
                            <span class="badge bg-success bg-opacity-25 text-success border border-success py-1 px-2" title="Firma Registrada">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        `;
                    }

                    return `
                        <div class="btn-group align-items-center" role="group">
                            <button type="button" class="btn btn-sm btn-primary py-1 px-2 me-1" 
                                    onclick="cargarDetalleNota(${row.id}); event.stopPropagation();" 
                                    title="Ver Detalle de Partidas y Salida">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            ${btnFirmar}
                        </div>
                    `;
                }
            },
            // ========================================================
            // RESTO DE COLUMNAS DE LA SALIDA
            // ========================================================
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
                data: 'tipo_docto_almacen',
                render: function (data) {
                    return `<small class="fw-semibold text-secondary"><i class="fa-solid fa-file-lines me-1"></i>${htmlEncode(data || 'Salida')}</small>`;
                }
            },
            {
                data: 'almacen_origen',
                render: function (data, type, row) {
                    const dest = row.almacen_destino ? `<br><small class="text-muted"><i class="fa-solid fa-arrow-right me-1"></i>Dest: ${htmlEncode(row.almacen_destino)}</small>` : '';
                    return `<div><strong>${htmlEncode(data || row.ccvealmacen)}</strong>${dest}</div>`;
                }
            },
            {
                data: 'nombre_cliente',
                render: function (data, type, row) {
                    const area = row.area_afectada ? `<br><small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i>${htmlEncode(row.area_afectada)}</small>` : '';
                    return `<div><strong>${htmlEncode(data || 'Público General')}</strong>${area}</div>`;
                }
            },
            {
                data: 'num_documento_salida',
                render: function (data, type, row) {
                    if (!data || data === '') return '<span class="text-muted">-</span>';
                    const py = row.proyecto_id ? `<br><small class="text-primary fw-semibold">${htmlEncode(row.proyecto_id)}</small>` : '';
                    return `<div><span class="badge bg-dark bg-opacity-75">${htmlEncode(data)}</span>${py}</div>`;
                }
            },
            {
                data: 'nombre_solicita',
                render: function (data, type, row) {
                    const sol = data ? `<div><small class="text-muted">Sol:</small> <strong>${htmlEncode(data)}</strong></div>` : '';
                    const ent = row.usuario_entrega_nombre ? `<div><small class="text-muted">Ent:</small> ${htmlEncode(row.usuario_entrega_nombre)}</div>` : '';
                    return `<div style="font-size: 0.85rem;">${sol || '-'}${ent}</div>`;
                }
            },
            {
                data: 'persona_recibe',
                render: function (data, type, row) {
                    const tieneFirma = (parseInt(row.tiene_firma) === 1);
                    if (tieneFirma) {
                        const nombre = data || row.usuario_recibe_nombre || 'Registrado';
                        const fch = row.fch_usuario_recibe ? `<br><small class="text-muted" style="font-size:0.75rem;">${row.fch_usuario_recibe}</small>` : '';
                        return `<div><span class="badge bg-success bg-opacity-15 text-success border border-success px-2 py-1"><i class="fa-solid fa-signature me-1"></i>${htmlEncode(nombre)}</span>${fch}</div>`;
                    }
                    return `<span class="badge bg-warning bg-opacity-15 text-dark border border-warning px-2 py-1"><i class="fa-solid fa-clock me-1"></i>Pendiente</span>`;
                }
            },
            {
                data: 'total_partidas',
                className: 'text-center',
                render: function (data, type, row) {
                    const part = parseInt(data) || 0;
                    const pzs = parseFloat(row.total_piezas) || 0;
                    return `
                        <div>
                            <span class="badge bg-secondary me-1">${part} part.</span>
                            <span class="badge bg-info text-dark">${pzs} pzs.</span>
                        </div>
                    `;
                }
            }
        ],
        rowCallback: function (row, data) {
            if (selectedNotaId && data.id === selectedNotaId) {
                $(row).addClass('selected-row');
            }
            $(row).css('cursor', 'pointer');
            $(row).off('click').on('click', function () {
                cargarDetalleNota(data.id);
            });
        }
    });
}

/*==================================================================
[ 2. Obtención y Control de Filtros ]
==================================================================*/

function obtenerValoresFiltrosNotas() {
    return {
        estatus: $('#selectFiltroEstatus').val() || '',
        estatus_firma: $('#selectFiltroFirma').val() || '',
        almacen: $('#selectFiltroAlmacen').val() || '',
        cliente: $('#txtFiltroCliente').val() || '',
        tipo_docto: $('#selectFiltroTipoDocto').val() || '',
        fecha_inicio: $('#txtFiltroFechaInicio').val() || '',
        fecha_fin: $('#txtFiltroFechaFin').val() || '',
        busqueda: $('#txtFiltroBusqueda').val() || ''
    };
}

function ejecutarBusquedaNotas() {
    if (tableNotas) {
        tableNotas.ajax.reload(null, false);
    }
}

function limpiarFiltrosNotas() {
    document.getElementById('formFiltrosNotas').reset();
    $('#selectFiltroEstatus').val('');
    $('#selectFiltroFirma').val('');
    $('#selectFiltroAlmacen').val('');
    $('#selectFiltroTipoDocto').val('');
    $('#txtFiltroCliente').val('');
    $('#txtFiltroFechaInicio').val('');
    $('#txtFiltroFechaFin').val('');
    $('#txtFiltroBusqueda').val('');

    ejecutarBusquedaNotas();
}

function aplicarPresetFecha(tipo) {
    const hoy = new Date();
    const formatoFecha = (d) => d.toISOString().split('T')[0];

    let inicio = '';
    let fin = formatoFecha(hoy);

    if (tipo === 'hoy') {
        inicio = fin;
    } else if (tipo === 'semana') {
        const diaSemana = hoy.getDay() || 7; // 1 = Lunes
        const primerDia = new Date(hoy);
        primerDia.setDate(hoy.getDate() - diaSemana + 1);
        inicio = formatoFecha(primerDia);
    } else if (tipo === 'mes') {
        const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        inicio = formatoFecha(primerDiaMes);
    } else if (tipo === '30dias') {
        const hace30 = new Date(hoy);
        hace30.setDate(hoy.getDate() - 30);
        inicio = formatoFecha(hace30);
    } else if (tipo === 'anio') {
        const primerDiaAnio = new Date(hoy.getFullYear(), 0, 1);
        inicio = formatoFecha(primerDiaAnio);
    }

    $('#txtFiltroFechaInicio').val(inicio);
    $('#txtFiltroFechaFin').val(fin);
    ejecutarBusquedaNotas();
}

/*==================================================================
[ 3. Actualización de Tarjetas KPI y Dashboard Ejecutivo ]
==================================================================*/

function actualizarKpisNotas(kpis) {
    if (!kpis) return;

    $('#kpi_total_notas').text(kpis.total_notas || 0);
    $('#kpi_contabilizadas').text(kpis.contabilizadas || 0);
    $('#kpi_en_proceso').text(kpis.en_proceso || 0);
    $('#kpi_canceladas').text(kpis.canceladas || 0);
    $('#kpi_con_firma').text(kpis.con_firma || 0);
    $('#kpi_pendientes_firma').text(kpis.pendientes_firma || 0);
}

function actualizarAnalisisNotas(analisis) {
    if (!analisis) return;

    // Badge alertas
    const totalAlertas = analisis.total_atencion || 0;
    const badgeAlertas = $('#badge_alertas_almacen');
    badgeAlertas.html(`<i class="fa-solid fa-bell me-1"></i> ${totalAlertas} Salidas Requieren Atención`);
    if (totalAlertas > 0) {
        badgeAlertas.removeClass('bg-light text-muted').addClass('bg-warning-lighten text-dark border-warning');
    } else {
        badgeAlertas.removeClass('bg-warning-lighten text-dark border-warning').addClass('bg-light text-muted');
    }

    // Top Clientes
    const listClientes = $('#list_top_clientes_notas');
    listClientes.empty();
    if (analisis.top_clientes && analisis.top_clientes.length > 0) {
        analisis.top_clientes.forEach(function (c) {
            listClientes.append(`
                <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="text-truncate me-2 fw-semibold" title="${htmlEncode(c.nombre)}">${htmlEncode(c.nombre)}</span>
                    <span class="badge bg-primary rounded-pill">${c.notas} salidas</span>
                </li>
            `);
        });
    } else {
        listClientes.append('<li class="text-muted fst-italic py-1">Sin movimientos registrados</li>');
    }

    // Top Almacenes
    const listAlmacenes = $('#list_top_almacenes_notas');
    listAlmacenes.empty();
    if (analisis.top_almacenes && analisis.top_almacenes.length > 0) {
        analisis.top_almacenes.forEach(function (a) {
            listAlmacenes.append(`
                <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="text-truncate me-2 fw-semibold" title="${htmlEncode(a.nombre)}">${htmlEncode(a.nombre)}</span>
                    <span class="badge bg-secondary rounded-pill">${a.notas} salidas</span>
                </li>
            `);
        });
    } else {
        listAlmacenes.append('<li class="text-muted fst-italic py-1">Sin movimientos registrados</li>');
    }

    // Salidas que requieren atención
    const listAlertas = $('#list_alertas_notas');
    listAlertas.empty();
    if (analisis.alertas_atencion && analisis.alertas_atencion.length > 0) {
        analisis.alertas_atencion.forEach(function (al) {
            const motivo = (parseInt(al.tiene_firma) === 0) ? 'Sin firma' : 'En proceso';
            listAlertas.append(`
                <li class="d-flex justify-content-between align-items-center py-1 border-bottom" style="cursor: pointer;" onclick="cargarDetalleNota(${al.id})">
                    <div>
                        <strong class="text-primary">${al.folio}</strong>
                        <small class="text-muted d-block">${htmlEncode(al.cliente)}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning text-dark">${motivo}</span>
                        <small class="text-muted d-block">${al.dias} días</small>
                    </div>
                </li>
            `);
        });
    } else {
        listAlertas.append('<li class="text-success fw-semibold py-1"><i class="fa-solid fa-circle-check me-1"></i> ¡Todas las salidas al día con firma!</li>');
    }
}

/*==================================================================
[ 4. Detalle Completo de la Nota de Salida ]
==================================================================*/

function cargarDetalleNota(id) {
    selectedNotaId = id;

    // Resaltar fila seleccionada en tabla
    $('#tableNotasSalida tbody tr').removeClass('selected-row');

    $.ajax({
        url: base_url + '/almacen/getNotaSalidaDetalle',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function (res) {
            if (!res.status || !res.header) {
                Swal.fire('Atención', res.msg || 'No se pudo cargar el detalle.', 'warning');
                return;
            }

            const h = res.header;
            const partidas = res.partidas || [];

            // Título y badges
            $('#det_folio_title').text(h.folio || '-');
            $('#det_folio').text(h.folio || '-');
            $('#det_fecha').text(h.fecha || '-');
            $('#det_almacen_origen').text(h.almacen_origen || h.ccvealmacen || '-');
            $('#det_almacen_destino').text(h.almacen_destino || h.ccvealmacenDestino || '-');
            $('#det_cliente').text(h.nombre_cliente || 'PÚBLICO GENERAL');
            $('#det_proyecto').text(h.proyecto_titulo ? `${h.proyecto_id} - ${h.proyecto_titulo}` : (h.proyecto_id || '-'));
            $('#det_tipo_docto').text(h.tipo_docto_almacen || 'Salida');
            $('#det_num_docto').text(h.num_documento_salida || 'Sin referencia');
            $('#det_solicita').text(h.nombre_solicita || '-');
            $('#det_entrega').text(h.usuario_entrega_nombre || '-');

            // Badge estatus
            const est = (h.estatus || '').toUpperCase();
            let estHtml = '';
            if (est === 'CONTABILIZADA') {
                estHtml = `<span class="badge-status-contabilizada"><i class="fa-solid fa-check-double"></i> Contabilizada</span>`;
            } else if (est === 'EN PROCESO') {
                estHtml = `<span class="badge-status-en-proceso"><i class="fa-solid fa-hourglass-half"></i> En Proceso</span>`;
            } else if (est === 'CANCELADA') {
                estHtml = `<span class="badge-status-cancelada"><i class="fa-solid fa-ban"></i> Cancelada</span>`;
            }
            $('#det_estatus_badge').html(estHtml);

            // Badge y sección de firma
            const tieneFirma = (parseInt(h.tiene_firma) === 1);
            let firmaBadge = '';
            let btnFirmarDetalle = '';

            if (tieneFirma) {
                firmaBadge = `<span class="badge bg-success bg-opacity-25 text-success border border-success"><i class="fa-solid fa-signature me-1"></i> Firmado</span>`;
                $('#det_persona_recibe').text(h.persona_recibe || h.usuario_recibe_nombre || 'Receptor registrado');
                $('#det_fecha_firma').text(h.fch_usuario_recibe || '-');

                if (h.firma_recibe && h.firma_recibe.length > 50) {
                    $('#container_firma_preview').html(`
                        <img src="${h.firma_recibe}" alt="Firma Digital" class="signature-img-preview img-fluid" style="max-height: 120px;">
                    `);
                } else {
                    $('#container_firma_preview').html('<span class="text-success fw-bold"><i class="fa-solid fa-check-circle me-1"></i> Firma estampada en sistema</span>');
                }
            } else {
                firmaBadge = `<span class="badge bg-warning bg-opacity-25 text-dark border border-warning"><i class="fa-solid fa-clock me-1"></i> Pendiente de Firma</span>`;
                $('#det_persona_recibe').text('Pendiente');
                $('#det_fecha_firma').text('No registrada');
                $('#container_firma_preview').html('<span class="text-muted fst-italic"><i class="fa-solid fa-pen-nib me-1"></i> Sin firma digital aún</span>');

                if (est !== 'CANCELADA') {
                    btnFirmarDetalle = `
                        <button type="button" class="btn btn-sm btn-success fw-bold px-3 shadow-sm" onclick="abrirModalFirmaNota(${h.id});">
                            <i class="fa-solid fa-signature me-1"></i> Registrar Firma de Entrega
                        </button>
                    `;
                }
            }
            $('#det_firma_badge').html(firmaBadge);
            $('#container_btn_firmar_detalle').html(btnFirmarDetalle);

            // Llenar tabla de partidas
            const tbody = $('#tbodyPartidasNota');
            tbody.empty();
            if (partidas && partidas.length > 0) {
                let idx = 1;
                partidas.forEach(function (p) {
                    const cant = parseFloat(p.cantidad) || 0;
                    const costo = parseFloat(p.costo_unitario) || 0;
                    const imp = parseFloat(p.importe) || 0;

                    tbody.append(`
                        <tr>
                            <td class="text-center fw-bold">${idx++}</td>
                            <td class="text-center font-monospace">${htmlEncode(p.clave || '-')}</td>
                            <td class="text-center font-monospace text-primary fw-bold">${htmlEncode(p.ccn || '-')}</td>
                            <td>${htmlEncode(p.descripcion || '-')}</td>
                            <td class="text-center fw-bold text-dark">${cant}</td>
                            <td class="text-center text-muted">${htmlEncode(p.unidad || 'pza')}</td>
                            <td class="text-center">${htmlEncode(p.lote || '-')}</td>
                            <td class="text-center">${htmlEncode(p.serie || '-')}</td>
                            <td class="text-end font-monospace">$${costo.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
                            <td class="text-end font-monospace fw-bold">$${imp.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="10" class="text-center text-muted py-3">No hay partidas registradas en el detalle de esta salida.</td></tr>');
            }

            // Mostrar panel y scroll suave
            $('#panelDetalleNota').slideDown(250);
            $('html, body').animate({
                scrollTop: $('#panelDetalleNota').offset().top - 80
            }, 300);
        },
        error: function () {
            Swal.fire('Error', 'No fue posible comunicarse con el servidor.', 'error');
        }
    });
}

function cerrarPanelDetalleNota() {
    $('#panelDetalleNota').slideUp(200);
    $('#tableNotasSalida tbody tr').removeClass('selected-row');
    selectedNotaId = null;
}

/*==================================================================
[ 5. Signature Pad y Modal de Firma Digital ]
==================================================================*/

function initSignaturePadNota() {
    canvasNota = document.getElementById('canvasFirmaDigitalNota');
    if (!canvasNota) return;

    ctxNota = canvasNota.getContext('2d');
    ctxNota.lineWidth = 2.5;
    ctxNota.lineCap = 'round';
    ctxNota.lineJoin = 'round';
    ctxNota.strokeStyle = '#0f172a';

    function getCoords(e) {
        const rect = canvasNota.getBoundingClientRect();
        const scaleX = canvasNota.width / rect.width;
        const scaleY = canvasNota.height / rect.height;

        let clientX = e.clientX;
        let clientY = e.clientY;

        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }

        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    // Mouse events
    canvasNota.addEventListener('mousedown', function (e) {
        isDrawingNota = true;
        hasSignatureNota = true;
        const coords = getCoords(e);
        lastXNota = coords.x;
        lastYNota = coords.y;
    });

    canvasNota.addEventListener('mousemove', function (e) {
        if (!isDrawingNota) return;
        const coords = getCoords(e);
        ctxNota.beginPath();
        ctxNota.moveTo(lastXNota, lastYNota);
        ctxNota.lineTo(coords.x, coords.y);
        ctxNota.stroke();
        lastXNota = coords.x;
        lastYNota = coords.y;
    });

    canvasNota.addEventListener('mouseup', function () {
        isDrawingNota = false;
    });

    canvasNota.addEventListener('mouseleave', function () {
        isDrawingNota = false;
    });

    // Touch events
    canvasNota.addEventListener('touchstart', function (e) {
        e.preventDefault();
        isDrawingNota = true;
        hasSignatureNota = true;
        const coords = getCoords(e);
        lastXNota = coords.x;
        lastYNota = coords.y;
    }, { passive: false });

    canvasNota.addEventListener('touchmove', function (e) {
        e.preventDefault();
        if (!isDrawingNota) return;
        const coords = getCoords(e);
        ctxNota.beginPath();
        ctxNota.moveTo(lastXNota, lastYNota);
        ctxNota.lineTo(coords.x, coords.y);
        ctxNota.stroke();
        lastXNota = coords.x;
        lastYNota = coords.y;
    }, { passive: false });

    canvasNota.addEventListener('touchend', function (e) {
        e.preventDefault();
        isDrawingNota = false;
    }, { passive: false });
}

function limpiarCanvasFirmaNota() {
    if (ctxNota && canvasNota) {
        ctxNota.clearRect(0, 0, canvasNota.width, canvasNota.height);
        hasSignatureNota = false;
    }
}

function initSelectUsuarioRecibeNota() {
    const sel = document.getElementById('selectUsuarioRecibeNota');
    const containerOtro = document.getElementById('containerNombreRecibeOtroNota');
    const inputOtro = document.getElementById('inputNombreRecibeOtroNota');

    if (!sel) return;

    sel.addEventListener('change', function () {
        if (sel.value === '__OTRO__') {
            if (containerOtro) containerOtro.style.display = 'block';
            if (inputOtro) inputOtro.focus();
        } else {
            if (containerOtro) containerOtro.style.display = 'none';
            if (inputOtro) inputOtro.value = '';
        }
    });
}

function abrirModalFirmaNota(notaId) {
    limpiarCanvasFirmaNota();

    $('#modal_nota_id').val(notaId);
    $('#selectUsuarioRecibeNota').val('').trigger('change');
    $('#inputNombreRecibeOtroNota').val('');

    // Consultar datos de la nota para resumen en modal
    $.ajax({
        url: base_url + '/almacen/getNotaSalidaDetalle',
        type: 'POST',
        data: { id: notaId },
        dataType: 'json',
        success: function (res) {
            if (!res.status || !res.header) {
                Swal.fire('Error', res.msg || 'No se pudieron consultar los datos de la nota.', 'error');
                return;
            }

            const h = res.header;
            const partidas = res.partidas || [];

            $('#modal_folio_nota').text(h.folio || '-');
            $('#modal_cliente_nota').text(h.nombre_cliente || 'PÚBLICO GENERAL');
            $('#modal_fecha_nota').text(h.fecha || '-');
            $('#modal_almacen_nota').text(h.almacen_origen || h.ccvealmacen || '-');
            $('#modal_num_docto_nota').text(h.num_documento_salida || 'Sin referencia');

            // Partidas resumen
            const tbody = $('#modal_tbody_partidas_nota');
            tbody.empty();
            if (partidas.length > 0) {
                let idx = 1;
                partidas.forEach(function (p) {
                    tbody.append(`
                        <tr>
                            <td class="text-center">${idx++}</td>
                            <td><strong>${htmlEncode(p.descripcion || p.clave)}</strong></td>
                            <td class="text-center fw-bold">${parseFloat(p.cantidad) || 0}</td>
                            <td class="text-center text-muted">${htmlEncode(p.unidad || 'pza')}</td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="4" class="text-center text-muted py-2">Sin partidas</td></tr>');
            }

            // Si la nota ya tenía un receptor sugerido en `cRecibeNotaExterna` o `ccveusuarioRecibe`
            if (h.ccveusuarioRecibe && $(`#selectUsuarioRecibeNota option[value="${h.ccveusuarioRecibe}"]`).length > 0) {
                $('#selectUsuarioRecibeNota').val(h.ccveusuarioRecibe).trigger('change');
            } else if (h.persona_recibe) {
                $('#selectUsuarioRecibeNota').val('__OTRO__').trigger('change');
                $('#inputNombreRecibeOtroNota').val(h.persona_recibe);
            }

            const modal = new bootstrap.Modal(document.getElementById('modalRegistrarFirmaNota'));
            modal.show();
        },
        error: function () {
            Swal.fire('Error', 'No fue posible consultar la información de la nota.', 'error');
        }
    });
}

function guardarFirmaNota() {
    const notaId = $('#modal_nota_id').val();
    const selVal = $('#selectUsuarioRecibeNota').val();
    let nombreRecibe = '';
    let usuarioRecibe = '';

    if (!selVal) {
        Swal.fire('Atención', 'Debe seleccionar o especificar la persona que recibe la salida.', 'warning');
        return;
    }

    if (selVal === '__OTRO__') {
        nombreRecibe = $.trim($('#inputNombreRecibeOtroNota').val());
        usuarioRecibe = nombreRecibe;
        if (!nombreRecibe) {
            Swal.fire('Atención', 'Debe ingresar el nombre del receptor externo.', 'warning');
            return;
        }
    } else {
        usuarioRecibe = selVal;
        const opt = $(`#selectUsuarioRecibeNota option[value="${selVal}"]`);
        nombreRecibe = opt.data('nombre') || opt.text();
    }

    if (!hasSignatureNota || !canvasNota) {
        Swal.fire('Atención', 'Debe dibujar la firma digital de conformidad en el recuadro antes de confirmar.', 'warning');
        return;
    }

    const firmaBase64 = canvasNota.toDataURL('image/png');
    if (!firmaBase64 || firmaBase64.length < 150) {
        Swal.fire('Atención', 'El trazo de la firma es demasiado corto. Por favor firme adecuadamente.', 'warning');
        return;
    }

    // Preparar FormData con soporte Blob multipart
    const formData = new FormData();
    formData.append('nota_id', notaId);
    formData.append('nombre_recibe', nombreRecibe);
    formData.append('usuario_recibe', usuarioRecibe);
    formData.append('firma_base64', firmaBase64);

    // Convertir canvas a Blob para mayor resiliencia contra WAF/ModSecurity
    try {
        canvasNota.toBlob(function (blob) {
            if (blob) {
                formData.append('firma_file', blob, 'firma_recibe.png');
            }
            enviarRegistroFirma(formData);
        }, 'image/png');
    } catch (e) {
        enviarRegistroFirma(formData);
    }
}

function enviarRegistroFirma(formData) {
    const btnConfirmar = $('#btnConfirmarGuardarFirmaNota');
    btnConfirmar.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...');

    $.ajax({
        url: base_url + '/almacen/guardarFirmaNotaSalida',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (res) {
            btnConfirmar.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Confirmar y Guardar Firma');

            if (res.status) {
                const modalEl = document.getElementById('modalRegistrarFirmaNota');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: '¡Firma Registrada!',
                    text: res.msg,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Recargar tabla principal
                if (tableNotas) {
                    tableNotas.ajax.reload(null, false);
                }

                // Si el panel de detalle estaba abierto en esta nota, recargarlo
                const currentId = parseInt($('#modal_nota_id').val());
                if (selectedNotaId && selectedNotaId === currentId) {
                    cargarDetalleNota(currentId);
                }
            } else {
                Swal.fire('Atención', res.msg || 'No se pudo guardar la firma.', 'error');
            }
        },
        error: function () {
            btnConfirmar.prop('disabled', false).html('<i class="fa-solid fa-check me-1"></i> Confirmar y Guardar Firma');
            Swal.fire('Error', 'Error de comunicación con el servidor al registrar la firma.', 'error');
        }
    });
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
