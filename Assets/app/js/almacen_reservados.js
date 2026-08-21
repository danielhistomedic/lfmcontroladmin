/*==================================================================
[ JS: Almacén - Control de Productos Reservados ]
==================================================================*/

var tableReservados = null;
var tableElement = '#tableProductosReservados';

document.addEventListener("DOMContentLoaded", function () {
    // Inicializar DataTable al cargar la página
    initDataTableReservados();

    // Evento clic en aplicar filtros
    const btnAplicar = document.getElementById('btnAplicarFiltros');
    if (btnAplicar) {
        btnAplicar.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarBusqueda();
        });
    }

    // Evento cambio en selects de filtro superior
    const selectCliente = document.getElementById('selectFiltroCliente');
    if (selectCliente) {
        selectCliente.addEventListener('change', function () {
            ejecutarBusqueda();
        });
    }

    const selectAlmacen = document.getElementById('selectFiltroAlmacen');
    if (selectAlmacen) {
        selectAlmacen.addEventListener('change', function () {
            ejecutarBusqueda();
        });
    }

    const selectAntiguedad = document.getElementById('selectFiltroAntiguedad');
    if (selectAntiguedad) {
        selectAntiguedad.addEventListener('change', function () {
            ejecutarBusqueda();
        });
    }

    // Evento clic en limpiar filtros
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function (e) {
            e.preventDefault();
            limpiarFiltros();
        });
    }
});

/**
 * Recarga la tabla de productos reservados con los filtros seleccionados
 */
function ejecutarBusqueda() {
    if (tableReservados) {
        tableReservados.ajax.reload();
    } else {
        initDataTableReservados();
    }
}

/**
 * Resetea los filtros superiores
 */
function limpiarFiltros() {
    const selCli = document.getElementById('selectFiltroCliente');
    const selAlm = document.getElementById('selectFiltroAlmacen');
    const selAnt = document.getElementById('selectFiltroAntiguedad');

    if (selCli) selCli.value = '';
    if (selAlm) selAlm.value = '';
    if (selAnt) selAnt.value = '';

    ejecutarBusqueda();
}

/**
 * Inicializa la tabla DataTables de Productos Reservados
 */
function initDataTableReservados() {
    const urlGet = base_url + '/almacen/getReservados';

    if ($.fn.DataTable.isDataTable(tableElement)) {
        $(tableElement).DataTable().destroy();
        $(tableElement).find('tbody').empty();
    }

    tableReservados = $(tableElement).DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        responsive: false,
        scrollX: true,
        orderCellsTop: true,
        order: [[3, "desc"]], // Ordenar por fecha de reserva descendente
        iDisplayLength: 10,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Todos"]
        ],
        ajax: {
            url: urlGet,
            type: 'POST',
            data: function (d) {
                const cli = document.getElementById('selectFiltroCliente');
                const alm = document.getElementById('selectFiltroAlmacen');
                const ant = document.getElementById('selectFiltroAntiguedad');

                d.cliente_id = cli ? cli.value : '';
                d.almacen = alm ? alm.value : '';
                d.antiguedad = ant ? ant.value : '';
            },
            dataSrc: function (json) {
                if (json && json.status) {
                    renderKpiCards(json.kpis || {});
                    return json.data || [];
                } else {
                    renderKpiCards({});
                    return [];
                }
            }
        },
        columns: [
            {
                data: 'fotos',
                className: 'text-center',
                render: function (data, type, row) {
                    if (data && data.length > 0) {
                        const firstImg = data[0];
                        const count = data.length;
                        const descEscaped = encodeURIComponent(row.material_descripcion || '');
                        const jsonFotos = encodeURIComponent(JSON.stringify(data));
                        return `
                            <div class="position-relative d-inline-block">
                                <img src="${firstImg}" class="product-img-thumb" title="Ver foto" onclick="abrirModalFotos('${jsonFotos}', '${descEscaped}')">
                                ${count > 1 ? `<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">+${count - 1}</span>` : ''}
                            </div>
                        `;
                    } else {
                        return `<span class="badge bg-secondary opacity-50"><i class="fa-solid fa-image-slash me-1"></i>Sin foto</span>`;
                    }
                }
            },
            {
                data: 'cliente_nombre',
                className: 'col-cliente fw-semibold text-dark',
                render: function (data, type, row) {
                    const nombre = data || 'Sin cliente asignado';
                    const razon = (row.cliente_razon_social && row.cliente_razon_social !== nombre) ? `<br><small class="text-muted fw-normal">${row.cliente_razon_social}</small>` : '';
                    if (row.cliente_id && row.cliente_id > 0) {
                        return `<div><i class="fa-solid fa-building me-1 text-primary"></i>${nombre}${razon}</div>`;
                    } else {
                        return `<div><span class="badge bg-light text-dark border"><i class="fa-solid fa-user-tag me-1 text-secondary"></i>${nombre}</span></div>`;
                    }
                }
            },
            {
                data: 'tiempo_texto',
                className: 'text-center',
                render: function (data, type, row) {
                    const nivel = row.nivel_antiguedad;
                    let badgeClass = 'bg-success';
                    let icon = 'fa-clock';

                    if (nivel === 'critica') {
                        badgeClass = 'bg-danger';
                        icon = 'fa-circle-exclamation';
                    } else if (nivel === 'urgente') {
                        badgeClass = 'bg-warning text-dark';
                        icon = 'fa-triangle-exclamation';
                    } else if (nivel === 'atencion') {
                        badgeClass = 'bg-info text-dark';
                        icon = 'fa-clock-rotate-left';
                    }

                    const tooltip = `Registrado el: ${row.fecha_registro} (${row.horas_transcurridas} horas transcurridas)`;
                    return `<span class="badge ${badgeClass} badge-antiguedad" title="${tooltip}" data-bs-toggle="tooltip"><i class="fa-solid ${icon} me-1"></i>${data}</span>`;
                }
            },
            {
                data: 'fecha_registro',
                className: 'text-center text-muted font-mono',
                render: function (data) {
                    return `<small class="fw-semibold">${data}</small>`;
                }
            },
            {
                data: 'cantidad',
                className: 'text-center',
                render: function (data, type, row) {
                    const cant = parseFloat(data) || 0;
                    const u = row.unidad_medida || 'pza';
                    return `<span class="badge bg-primary fs-6 px-3 py-1">${cant} <small class="fw-normal">${u}</small></span>`;
                }
            },
            { data: 'clave', className: 'fw-bold text-dark' },
            { data: 'ccn' },
            { data: 'material_descripcion', className: 'col-descripcion' },
            {
                data: 'almacen',
                render: function (data) {
                    return `<span class="badge bg-light text-dark border"><i class="fa-solid fa-warehouse me-1 text-secondary"></i>${data}</span>`;
                }
            },
            {
                data: 'orden_compra',
                className: 'text-center',
                render: function (data, type, row) {
                    if (data && data !== '' && data !== 'S/N' && data !== 'NA') {
                        return `<span class="badge bg-dark" title="Orden de Compra"><i class="fa-solid fa-file-invoice me-1 text-info"></i>${data}</span>`;
                    } else if (row.pedido_id && parseInt(row.pedido_id) > 0) {
                        return `<span class="badge bg-secondary"><i class="fa-solid fa-receipt me-1"></i>Pedido #${row.pedido_id}</span>`;
                    } else {
                        return `<span class="badge bg-light text-muted border fst-italic" title="Reserva directa sin orden de compra"><i class="fa-solid fa-asterisk me-1 text-secondary" style="font-size: 0.65rem;"></i>Reserva Directa</span>`;
                    }
                }
            },
            { data: 'marca' },
            { data: 'submarca' },
            { data: 'linea_producto' },
            { data: 'categoria' },
            {
                data: 'usuario_reserva',
                className: 'text-center',
                render: function (data) {
                    return `<span class="badge bg-secondary opacity-75">${data || 'Sistema'}</span>`;
                }
            }
        ],
        autoWidth: false,
        columnDefs: [
            { targets: 7, width: "380px", className: "col-descripcion" },
            { targets: 1, width: "220px", className: "col-cliente" },
            { targets: "_all", defaultContent: "" }
        ],
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Control_Productos_Reservados_Almacen',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'colvis',
                postfixButtons: ['colvisRestore']
            }
        ],
        language: idioma_espanol,
        initComplete: function () {
            const api = this.api();
            const wrapper = $(tableElement).closest('.dataTables_wrapper');

            // Asignar eventos de búsqueda por columna a los inputs en la cabecera
            wrapper.find('thead tr.filters-row th').each(function (colIdx) {
                const input = $(this).find('input');
                if (input.length > 0) {
                    // Evitar que el clic en el input dispare la ordenación
                    input.on('click', function (e) {
                        e.stopPropagation();
                    });

                    // Filtrado en tiempo real con debounce
                    let timeout = null;
                    input.on('keyup input change clear', function () {
                        const val = this.value;
                        clearTimeout(timeout);
                        timeout = setTimeout(function () {
                            if (api.column(colIdx).search() !== val) {
                                api.column(colIdx).search(val).draw();
                            }
                        }, 250);
                    });
                }
            });

            // Botón para resetear todos los filtros de columna
            wrapper.find('#btnResetColFiltersReservados').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                wrapper.find('thead tr.filters-row input').val('');
                api.columns().search('').draw();
            });
        },
        drawCallback: function () {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
}

/**
 * Renderiza los valores en las tarjetas KPI superiores
 */
function renderKpiCards(kpis) {
    const elPartidas = document.getElementById('kpi_total_partidas');
    const elUnidades = document.getElementById('kpi_total_unidades');
    const elClientes = document.getElementById('kpi_total_clientes');
    const elCriticas = document.getElementById('kpi_criticas');

    if (elPartidas) elPartidas.innerText = kpis.total_partidas || 0;
    if (elUnidades) elUnidades.innerText = parseFloat(kpis.total_unidades || 0).toLocaleString('es-MX');
    if (elClientes) elClientes.innerText = kpis.total_clientes || 0;
    if (elCriticas) elCriticas.innerText = kpis.criticas_mas15d || 0;
}

/**
 * Abre el modal con carrusel/slide de fotografías del producto
 */
function abrirModalFotos(jsonFotosEnc, descripcionEnc) {
    try {
        const arrFotos = JSON.parse(decodeURIComponent(jsonFotosEnc));
        let descripcion = '';
        try {
            descripcion = decodeURIComponent(descripcionEnc || '');
        } catch (e) {
            descripcion = descripcionEnc || '';
        }
        const indicators = document.getElementById('carouselIndicatorsFotos');
        const inner = document.getElementById('carouselInnerFotos');
        const caption = document.getElementById('carouselFotoCaption');
        const title = document.getElementById('modalFotoProductoTitle');

        if (title) {
            title.innerHTML = `<i class="fa-solid fa-images me-2 text-warning"></i>Fotos: <span id="modalFotoProdTitleText"></span>`;
            const titleSpan = document.getElementById('modalFotoProdTitleText');
            if (titleSpan) {
                titleSpan.textContent = descripcion;
            }
        }

        if (indicators && inner) {
            indicators.innerHTML = '';
            inner.innerHTML = '';

            const total = arrFotos.length;

            arrFotos.forEach((url, idx) => {
                // Indicador
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('data-bs-target', '#carouselFotosProducto');
                btn.setAttribute('data-bs-slide-to', idx.toString());
                if (idx === 0) {
                    btn.className = 'active';
                    btn.setAttribute('aria-current', 'true');
                }
                btn.setAttribute('aria-label', `Foto ${idx + 1}`);
                indicators.appendChild(btn);

                // Slide Item
                const item = document.createElement('div');
                item.className = `carousel-item ${idx === 0 ? 'active' : ''} h-100`;
                item.innerHTML = `
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 p-3" style="background-color: #0f172a;">
                        <a href="${url}" target="_blank" title="Haz clic para ver la imagen original en pestaña nueva">
                            <img src="${url}" data-original-src="${url}" class="img-fluid rounded shadow-lg" style="max-height: 400px; object-fit: contain; background-color: #ffffff; padding: 8px; border: 1px solid #334155;">
                        </a>
                        <small class="text-white-50 mt-2"><i class="fa-solid fa-up-right-from-square me-1"></i>Imagen ${idx + 1} de ${total} - Clic en la foto para ver en tamaño original</small>
                    </div>
                `;
                inner.appendChild(item);
            });

            // Ocultar mensaje de estado de rotación previo
            const msgEstado = document.getElementById('msgRotacionEstado');
            if (msgEstado) msgEstado.style.display = 'none';

            // Visibilidad de controles
            const prevBtn = document.querySelector('#carouselFotosProducto .carousel-control-prev');
            const nextBtn = document.querySelector('#carouselFotosProducto .carousel-control-next');
            if (total <= 1) {
                if (indicators) indicators.style.display = 'none';
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
            } else {
                if (indicators) indicators.style.display = 'flex';
                if (prevBtn) prevBtn.style.display = 'flex';
                if (nextBtn) nextBtn.style.display = 'flex';
            }

            if (caption) {
                caption.innerText = `Imagen 1 de ${total}`;
            }

            // Escuchar cambio de slide
            const carouselElem = document.getElementById('carouselFotosProducto');
            if (carouselElem) {
                carouselElem.removeEventListener('slid.bs.carousel', window._carouselSlideListener);
                window._carouselSlideListener = function (e) {
                    if (caption) {
                        caption.innerText = `Imagen ${e.to + 1} de ${total}`;
                    }
                    const msg = document.getElementById('msgRotacionEstado');
                    if (msg) msg.style.display = 'none';
                };
                carouselElem.addEventListener('slid.bs.carousel', window._carouselSlideListener);
            }
        }

        const modalElement = document.getElementById('modalFotoProducto');
        if (modalElement) {
            const myModal = bootstrap.Modal.getOrCreateInstance(modalElement);
            myModal.show();
        }
    } catch (e) {
        console.error("Error al abrir carrusel de fotos:", e);
    }
}

/**
 * Rota la fotografía activa en el modal y actualiza el archivo en el servidor.
 * @param {string} direccion 'izq' o 'der'
 */
function rotarFotoModal(direccion) {
    const activeItem = document.querySelector('#carouselFotosProducto .carousel-item.active');
    if (!activeItem) return;

    const img = activeItem.querySelector('img');
    if (!img) return;

    const btnIzq = document.getElementById('btnGirarFotoIzq');
    const btnDer = document.getElementById('btnGirarFotoDer');
    const btnTarget = (direccion === 'izq' || direccion === 'left') ? btnIzq : btnDer;
    const iconBtn = btnTarget ? btnTarget.querySelector('i') : null;
    const originalIconClass = iconBtn ? iconBtn.className : '';

    if (btnIzq) btnIzq.disabled = true;
    if (btnDer) btnDer.disabled = true;
    if (iconBtn) iconBtn.className = 'fa-solid fa-spinner fa-spin me-1';

    const msgEstado = document.getElementById('msgRotacionEstado');
    if (msgEstado) msgEstado.style.display = 'none';

    const fotoSrc = img.getAttribute('data-original-src') || img.src;

    const formData = new FormData();
    formData.append('foto', fotoSrc);
    formData.append('direccion', direccion);

    fetch(base_url + '/almacen/rotarFoto', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.status) {
            const timestamp = data.timestamp || Date.now();
            const cleanUrl = fotoSrc.split('?')[0];
            const updatedUrl = cleanUrl + '?v=' + timestamp;

            // Actualizar imagen y enlace en el modal
            img.src = updatedUrl;
            img.setAttribute('data-original-src', cleanUrl);
            const parentLink = img.closest('a');
            if (parentLink) parentLink.href = updatedUrl;

            // Actualizar cualquier miniatura en la tabla con este archivo
            if (data.filename) {
                document.querySelectorAll(`img[src*="${data.filename}"]`).forEach(thumb => {
                    const thumbClean = thumb.src.split('?')[0];
                    thumb.src = thumbClean + '?v=' + timestamp;
                });
            }

            // Notificación visual de éxito en el modal
            if (msgEstado) {
                msgEstado.style.display = 'inline-block';
                setTimeout(() => {
                    if (typeof $ !== 'undefined' && $(msgEstado).length) {
                        $(msgEstado).fadeOut();
                    } else {
                        msgEstado.style.display = 'none';
                    }
                }, 2500);
            }
        } else {
            alert(data.msg || 'No fue posible rotar la fotografía.');
        }
    })
    .catch(err => {
        console.error('Error al solicitar rotación:', err);
        alert('Ocurrió un error de conexión al rotar la fotografía.');
    })
    .finally(() => {
        if (btnIzq) btnIzq.disabled = false;
        if (btnDer) btnDer.disabled = false;
        if (iconBtn) iconBtn.className = originalIconClass;
    });
}

