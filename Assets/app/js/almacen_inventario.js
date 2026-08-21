/*==================================================================
[ JS: Almacén - Inventario ]
==================================================================*/

var tableInventario = null;
var tableElement = '#tableInventarioAlmacen';

document.addEventListener("DOMContentLoaded", function () {
    // Inicializar Select2 en el filtro de producto (búsqueda dinámica AJAX)
    initSelectProducto();

    // Inicializar DataTable al cargar la página
    initDataTableInventario();

    // Evento clic en aplicar filtros
    const btnAplicar = document.getElementById('btnAplicarFiltros');
    if (btnAplicar) {
        btnAplicar.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarBusqueda();
        });
    }

    // Evento cambio en select de almacén
    const selectAlmacen = document.getElementById('selectFiltroAlmacen');
    if (selectAlmacen) {
        selectAlmacen.addEventListener('change', function () {
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
 * Inicializa el autocompletado Select2 para la selección de producto individual
 */
function initSelectProducto() {
    if ($.fn.select2) {
        $('#selectFiltroProducto').select2({
            placeholder: '-- Todos los productos --',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function () { return "No se encontraron productos"; },
                searching: function () { return "Buscando..."; },
                inputTooShort: function () { return "Ingrese clave, CCN o nombre..."; }
            },
            ajax: {
                url: base_url + '/almacen/getSelectProductos',
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return {
                        q: params.term || ''
                    };
                },
                processResults: function (data) {
                    return {
                        results: (data && data.results) ? data.results : []
                    };
                },
                cache: true
            }
        }).on('change', function () {
            ejecutarBusqueda();
        });
    }
}

/**
 * Recarga la tabla de inventario con los filtros seleccionados
 */
function ejecutarBusqueda() {
    if (tableInventario) {
        tableInventario.ajax.reload();
    } else {
        initDataTableInventario();
    }
}

/**
 * Resetea los filtros de Almacén y Producto
 */
function limpiarFiltros() {
    $('#selectFiltroAlmacen').val('');
    if ($.fn.select2) {
        $('#selectFiltroProducto').val(null).trigger('change');
    } else {
        $('#selectFiltroProducto').val('');
        ejecutarBusqueda();
    }
}

/**
 * Inicializa la tabla DataTables de Inventario
 */
function initDataTableInventario() {
    const urlGet = base_url + '/almacen/getInventario';

    if ($.fn.DataTable.isDataTable(tableElement)) {
        $(tableElement).DataTable().destroy();
        $(tableElement).find('tbody').empty();
    }

    tableInventario = $(tableElement).DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        responsive: false,
        scrollX: true,
        orderCellsTop: true,
        order: [[1, "asc"]],
        iDisplayLength: 5,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Todos"]
        ],
        ajax: {
            url: urlGet,
            type: 'POST',
            data: function (d) {
                const alm = document.getElementById('selectFiltroAlmacen');
                const prod = document.getElementById('selectFiltroProducto');
                d.almacen = alm ? alm.value : '';
                d.producto = prod ? prod.value : '';
            },
            dataSrc: function (json) {
                if (json && json.status) {
                    renderKpiCards(json.kpis || []);
                    return json.data || [];
                } else {
                    renderKpiCards([]);
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
                        const descEscaped = encodeURIComponent(row.cDescripcion || '');
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
            { data: 'almacen' },
            {
                data: 'existencia',
                className: 'text-center',
                render: function (data, type, row) {
                    const cant = parseFloat(data) || 0;
                    const res = parseFloat(row.reservadas) || 0;
                    const disp = row.disponibles !== undefined ? (parseFloat(row.disponibles) || 0) : (cant - res);
                    const alm = row.almacen ? ` (${row.almacen})` : '';
                    const tooltip = `Existencias: ${cant} | Reservadas: ${res} | Disponibles: ${disp}${alm}`;
                    if (disp > 0) {
                        return `<span class="badge bg-success badge-stock" title="${tooltip}" data-bs-toggle="tooltip">${cant}</span>`;
                    } else if (cant > 0) {
                        return `<span class="badge bg-warning text-dark badge-stock" title="${tooltip}" data-bs-toggle="tooltip">${cant}</span>`;
                    } else {
                        return `<span class="badge bg-danger badge-stock" title="${tooltip}" data-bs-toggle="tooltip">0</span>`;
                    }
                }
            },
            {
                data: 'costo_promedio',
                className: 'text-end font-mono',
                render: function (data) {
                    const val = parseFloat(data) || 0;
                    return '$' + val.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            },
            {
                data: 'moneda',
                className: 'text-center',
                render: function (data) {
                    const m = (data || 'MXN').trim();
                    return `<span class="badge bg-secondary opacity-75">${m}</span>`;
                }
            },
            { data: 'Clave', className: 'fw-bold text-dark' },
            { data: 'CCN' },
            { data: 'cDescripcion', className: 'col-descripcion' },
            { data: 'marca' },
            { data: 'submarca' },
            { data: 'linea_producto' },
            { data: 'categoria' },
            { data: 'unidad_medida', className: 'text-center' },
            { data: 'modelo' },
            { data: 'num_catalogo' },
            { data: 'num_parte' },
            { data: 'serie' },
            { data: 'material' },
            { data: 'grupo' },
            { data: 'clave_sat' }
        ],
        autoWidth: false,
        columnDefs: [
            { targets: 7, width: "400px", className: "col-descripcion" },
            { targets: "_all", defaultContent: "" }
        ],
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Inventario_Almacen',
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
            wrapper.find('#btnResetColFiltersInventario').on('click', function (e) {
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
                            <img src="${url}" class="img-fluid rounded shadow-lg" style="max-height: 400px; object-fit: contain; background-color: #ffffff; padding: 8px; border: 1px solid #334155;">
                        </a>
                        <small class="text-white-50 mt-2"><i class="fa-solid fa-up-right-from-square me-1"></i>Imagen ${idx + 1} de ${total} - Clic en la foto para ver en tamaño original</small>
                    </div>
                `;
                inner.appendChild(item);
            });

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
 * Renderiza dinámicamente las tarjetas KPI de Totales por Almacén y Moneda
 */
function renderKpiCards(kpis) {
    const panel = document.getElementById('panel_kpis');
    const container = document.getElementById('container_kpi_cards');

    if (!panel || !container) return;

    if (!kpis || kpis.length === 0) {
        panel.style.display = 'none';
        container.innerHTML = '';
        return;
    }

    panel.style.display = 'block';
    container.innerHTML = '';

    const colors = ['primary', 'success', 'info', 'purple', 'warning', 'danger'];

    // 1. Tarjeta Resumen General (Total Stock)
    let totalExistenciaGen = 0;
    kpis.forEach(item => {
        totalExistenciaGen += parseFloat(item.total_existencia) || 0;
    });

    let htmlCards = `
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card kpi-card shadow-sm border-0 h-100">
                <div class="card-body border d-flex align-items-center">
                    <div class="kpi-icon bg-primary-lighten text-primary me-3">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div class="w-100">
                        <span class="text-label text-primary d-block mb-1">Total Stock General</span>
                        <div class="text-amount text-dark">${totalExistenciaGen.toLocaleString('es-MX')}</div>
                        <div class="text-muted text-2"><i class="fa-solid fa-cubes me-1"></i>Piezas en existencias</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // 2. Tarjetas por Almacén divididas por Moneda
    kpis.forEach((item, index) => {
        const color = colors[(index + 1) % colors.length];
        const valTotal = parseFloat(item.valor_total) || 0;
        const exist = parseFloat(item.total_existencia) || 0;
        const valFormateado = '$' + valTotal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + (item.moneda || 'MXN');

        htmlCards += `
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card kpi-card shadow-sm border-0 h-100">
                    <div class="card-body border d-flex align-items-center">
                        <div class="kpi-icon bg-${color}-lighten text-${color} me-3">
                            <i class="fa-solid fa-warehouse"></i>
                        </div>
                        <div class="w-100">
                            <span class="text-label text-${color} d-block mb-1" title="${item.almacen}">${item.almacen} (${item.ccvealmacen})</span>
                            <div class="text-amount text-dark">${valFormateado}</div>
                            <div class="text-muted text-2"><i class="fa-solid fa-layer-group me-1"></i>${exist.toLocaleString('es-MX')} Piezas (${item.total_productos} Prod.)</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = htmlCards;
}

