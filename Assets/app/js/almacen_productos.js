/*==================================================================
[ JS: Almacén - Productos y Existencias ]
==================================================================*/

var tableProductos = null;
var tableElement = '#tableProductosAlmacen';

document.addEventListener("DOMContentLoaded", function () {
    // Inicializar DataTable al cargar la página
    initDataTableProductos();

    // Evento submit en formulario de búsqueda
    const formBuscador = document.getElementById('formBuscadorProductos');
    if (formBuscador) {
        formBuscador.addEventListener('submit', function (e) {
            e.preventDefault();
            ejecutarBusqueda();
        });
    }

    // Evento clic en botón buscar
    const btnBuscar = document.getElementById('btnBuscarProducto');
    if (btnBuscar) {
        btnBuscar.addEventListener('click', function (e) {
            e.preventDefault();
            ejecutarBusqueda();
        });
    }

    // Evento clic en botón limpiar
    const btnLimpiar = document.getElementById('btnLimpiarBusqueda');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            document.getElementById('inputBuscarProducto').value = '';
            btnLimpiar.style.display = 'none';
            ejecutarBusqueda();
        });
    }

    // Mostrar / ocultar botón de limpiar según texto
    const inputBuscar = document.getElementById('inputBuscarProducto');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', function () {
            if (this.value.trim().length > 0) {
                if (btnLimpiar) btnLimpiar.style.display = 'inline-block';
            } else {
                if (btnLimpiar) btnLimpiar.style.display = 'none';
            }
        });
    }
});

/**
 * Recarga la tabla con los parámetros de búsqueda actuales
 */
function ejecutarBusqueda() {
    if (tableProductos) {
        tableProductos.ajax.reload();
    } else {
        initDataTableProductos();
    }
}

/**
 * Inicializa el DataTable de Productos
 */
function initDataTableProductos() {
    const urlGet = base_url + '/almacen/getProductos';

    if ($.fn.DataTable.isDataTable(tableElement)) {
        $(tableElement).DataTable().destroy();
        $(tableElement).find('tbody').empty();
    }

    tableProductos = $(tableElement).DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        responsive: false,
        scrollX: true,
        orderCellsTop: true,
        order: [[2, "asc"]],
        iDisplayLength: 5,
        lengthMenu: [
            [3, 5, 10, 25, 50, 100, -1],
            [3, 5, 10, 25, 50, 100, "Todos"]
        ],
        ajax: {
            url: urlGet,
            type: 'POST',
            data: function (d) {
                const input = document.getElementById('inputBuscarProducto');
                d.busqueda = input ? input.value.trim() : '';
            },
            dataSrc: function (json) {
                if (json && json.status) {
                    mostrarRespuestaInteligente(json.busqueda, json.respuesta_inteligente, json.total_registros);
                    return json.data || [];
                } else {
                    mostrarRespuestaInteligente('', 'No fue posible realizar la consulta.', 0);
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
            {
                data: 'disponibles',
                className: 'text-center',
                render: function (data, type, row) {
                    const cant = parseInt(row.existencia) || 0;
                    const res = parseInt(row.reservadas) || 0;
                    const disp = (data !== undefined && data !== null) ? (parseInt(data) || 0) : (cant - res);
                    const desglose = row.desgloses_almacen ? ` (${row.desgloses_almacen})` : '';
                    const tooltip = `Existencias: ${cant} | Reservadas: ${res} | Disponibles: ${disp}${desglose}`;
                    if (disp > 0) {
                        return `<span class="badge bg-success badge-stock" title="${tooltip}" data-bs-toggle="tooltip">${disp}</span>`;
                    } else if (cant > 0) {
                        return `<span class="badge bg-warning text-dark badge-stock" title="${tooltip}" data-bs-toggle="tooltip">${disp}</span>`;
                    } else {
                        return `<span class="badge bg-danger badge-stock" title="${tooltip}" data-bs-toggle="tooltip">0</span>`;
                    }
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
            { targets: 4, width: "400px", className: "col-descripcion" },
            { targets: "_all", defaultContent: "" }
        ],
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Productos_y_Existencias_Almacen',
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
            wrapper.find('#btnResetColFilters').on('click', function (e) {
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
 * Renderiza la respuesta sintética inteligente del sistema
 */
function mostrarRespuestaInteligente(busqueda, respuesta, total) {
    const box = document.getElementById('boxRespuestaInteligente');
    const content = document.getElementById('contentRespuestaInteligente');
    const badge = document.getElementById('badgeTotalResultados');

    if (!box || !content) return;

    if (busqueda && busqueda.trim().length > 0) {
        box.style.display = 'block';
        content.innerHTML = respuesta;
        if (badge) {
            badge.innerText = `${total} Producto(s) Encontrado(s)`;
        }
    } else {
        box.style.display = 'block';
        content.innerHTML = respuesta || "Mostrando inventario general de productos activos en existencias.";
        if (badge) {
            badge.innerText = `${total} Producto(s) Activo(s)`;
        }
    }
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
