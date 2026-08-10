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
        order: [[1, "asc"]],
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
                        const descEscaped = (row.cDescripcion || '').replace(/'/g, "\\'");
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
            { data: 'clave_sat' },
            {
                data: 'existencia',
                className: 'text-center',
                render: function (data, type, row) {
                    const cant = parseInt(data) || 0;
                    const desglose = row.desgloses_almacen ? row.desgloses_almacen : 'Sin desglose de almacén';
                    if (cant > 0) {
                        return `<span class="badge bg-success badge-stock" title="${desglose}" data-bs-toggle="tooltip">${cant}</span>`;
                    } else {
                        return `<span class="badge bg-danger badge-stock" title="${desglose}" data-bs-toggle="tooltip">0</span>`;
                    }
                }
            }
        ],
        autoWidth: false,
        columnDefs: [
            { targets: 3, width: "400px", className: "col-descripcion" },
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
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
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
        content.innerText = respuesta;
        if (badge) {
            badge.innerText = `${total} Producto(s) Encontrado(s)`;
        }
    } else {
        box.style.display = 'block';
        content.innerText = "Mostrando inventario general de productos activos en existencias.";
        if (badge) {
            badge.innerText = `${total} Producto(s) Activo(s)`;
        }
    }
}

/**
 * Abre el modal desplegable con la galería de imágenes del producto
 */
function abrirModalFotos(jsonFotosEnc, descripcion) {
    try {
        const arrFotos = JSON.parse(decodeURIComponent(jsonFotosEnc));
        const container = document.getElementById('containerFotosModal');
        const title = document.getElementById('modalFotoProductoTitle');

        if (title) {
            title.innerHTML = `<i class="fa-solid fa-image me-2"></i>Fotos: ${descripcion}`;
        }

        if (container) {
            container.innerHTML = '';
            arrFotos.forEach((url, idx) => {
                const imgCard = document.createElement('div');
                imgCard.className = 'card shadow-sm p-2';
                imgCard.style.maxWidth = '260px';
                imgCard.innerHTML = `
                    <a href="${url}" target="_blank" title="Abrir en tamaño completo">
                        <img src="${url}" class="img-fluid rounded" style="max-height: 220px; object-fit: contain;">
                    </a>
                    <small class="text-muted mt-1">Imagen ${idx + 1}</small>
                `;
                container.appendChild(imgCard);
            });
        }

        const modalElement = document.getElementById('modalFotoProducto');
        if (modalElement) {
            const myModal = new bootstrap.Modal(modalElement);
            myModal.show();
        }
    } catch (e) {
        console.error("Error al abrir modal de fotos:", e);
    }
}
