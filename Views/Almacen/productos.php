<?php /* View: Almacén - Productos y Existencias */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Theme Custom CSS -->
<style>
    .search-box-container {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 1.5rem;
        color: #1e293b;
        font-family: var(--font-family-report);
        font-size: var(--font-body);
        line-height: var(--line-height-normal);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }
    html.dark .search-box-container { background: #1e293b; border-color: #53657d; color: #edf2f7; }
    html.dark .search-box-container .search-description { color: #cbd5e1; }
    /* El grupo conserva un extremo redondeado con y sin la acción de limpiar. */
    #formBuscadorProductos #btnBuscarProducto { border-radius: 0 5px 5px 0; }
    #formBuscadorProductos:has(#btnLimpiarBusqueda:not([style*="display: none"])) #btnBuscarProducto { border-radius: 0; }
    #formBuscadorProductos #btnLimpiarBusqueda { border-radius: 0 5px 5px 0; }
    .search-description { font-size: var(--font-body); line-height: var(--line-height-normal); }
    .smart-answer-card {
        border: none;
        border-left: 5px solid #3b82f6;
        background: #f8fafc;
        font-family: var(--font-family-report);
        font-size: var(--font-body);
        line-height: var(--line-height-normal);
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .smart-answer-header {
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-section-title);
        line-height: var(--line-height-heading);
        color: #1e293b;
    }
    .smart-answer-body {
        white-space: pre-line;
        font-size: var(--font-body);
        color: #334155;
        line-height: var(--line-height-relaxed);
    }
    .product-img-thumb {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    .product-img-thumb:hover {
        transform: scale(1.15);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    /* Clave y CCN son datos; las cantidades son estados, no botones. */
    #contentRespuestaInteligente .product-code-chip {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        padding: 3px 8px;
        border: 1px solid #a9d7e2;
        border-radius: 5px;
        background: #e9f5f8;
        color: #075985;
        font: var(--font-weight-semibold) var(--font-small)/18px var(--font-family-report);
        vertical-align: middle;
    }
    #contentRespuestaInteligente .product-code-chip--ccn {
        border-color: #cbd5e1;
        background: #f1f5f9;
        color: #334155;
    }
    #tableProductosAlmacen .badge-stock {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        min-height: 26px;
        padding: 4px 8px;
        border: 1px solid transparent;
        border-radius: 5px;
        font: var(--font-weight-bold) var(--font-table-body)/18px var(--font-family-report);
        font-variant-numeric: tabular-nums;
    }
    #tableProductosAlmacen .badge-stock.bg-info { background: #d9f3f9 !important; border-color: #8ccbdc; color: #075985 !important; }
    #tableProductosAlmacen .badge-stock.bg-warning { background: #fff3cd !important; border-color: #e9c46a; color: #854d0e !important; }
    #tableProductosAlmacen .badge-stock.bg-success { background: #e3f4e9 !important; border-color: #91cda8; color: #146c43 !important; }
    #tableProductosAlmacen .badge-stock.bg-danger { background: #fde8e8 !important; border-color: #e7aaaa; color: #a3212b !important; }
    #tableProductosAlmacen .badge-stock.bg-secondary { background: #f1f5f9 !important; border-color: #cbd5e1; color: #475569 !important; }
    /* Escala común para la respuesta dinámica, la foto y el modal. */
    .product-result-intro { font-size: var(--font-section-title); font-weight: var(--font-weight-semibold); color: #1e293b; }
    .product-result-name { font-size: var(--font-highlight); font-weight: var(--font-weight-bold); line-height: var(--line-height-heading); color: #1e293b; }
    .product-result-details { font-size: var(--font-body); font-weight: var(--font-weight-normal); line-height: var(--line-height-normal); color: #334155; }
    .product-result-marker { font-size: var(--font-small); vertical-align: middle; margin-right: 3px; }
    .product-result-value { font-weight: var(--font-weight-bold); font-variant-numeric: tabular-nums; }
    .product-result-value--existencia { color: #334155; }
    .product-result-value--reservadas { color: #854d0e; }
    .product-result-value--available { color: #146c43; }
    .product-result-value--unavailable { color: #a3212b; }
    .product-result-value--empty { color: #334155; }
    .product-result-warehouse { font-size: var(--font-small); color: #475569; }
    .product-result-more { font-size: var(--font-small); }
    .product-photo-count { font-size: var(--font-small); }
    .photo-carousel-caption { font-size: var(--font-body); }
    .btn-reset-col-filters { font-size: var(--font-small); }
    /* Panel de lista con la misma composición aprobada en Órdenes de Compra Clientes. */
    #panel_productos_almacen {
        background: #fff;
        border: 1px solid #b2c4d7 !important;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(15, 40, 70, .10), 0 8px 20px rgba(15, 40, 70, .10);
        padding: 1.25rem;
        min-width: 0;
    }
    #panel_productos_almacen > .chart-title {
        background: linear-gradient(90deg, #e9f5f8, #f4f8fc);
        border-bottom: 1px solid #cbd5e1;
        border-left: 3px solid #008daf;
        border-radius: 6px;
        color: #075985;
        font-family: var(--font-family-report);
        font-size: var(--font-highlight);
        font-weight: var(--font-weight-semibold);
        line-height: var(--line-height-heading);
        margin-bottom: 1rem;
        padding: .875rem 1rem;
    }
    html.dark #panel_productos_almacen { background: #1d2735; border-color: #60768c !important; }
    html.dark #panel_productos_almacen > .chart-title {
        background: linear-gradient(90deg, #243e4a, #263749);
        border-color: #53657d;
        border-left-color: #58c6de !important;
        color: #edf2f7;
    }
    /* El indicador de carga conserva contraste sin el bloque turquesa del tema. */
    #panel_productos_almacen .lfm-dt-wrapper .dataTables_processing {
        background: #fff !important;
        color: #1e293b !important;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(15, 40, 70, .15);
        padding: 8px 12px;
        z-index: 10;
    }
    html.dark #panel_productos_almacen .lfm-dt-wrapper .dataTables_processing {
        background: #253346 !important;
        color: #edf2f7 !important;
        border-color: #53657d;
    }
    #tableProductosAlmacen {
        width: 100% !important;
    }
    #tableProductosAlmacen th, #tableProductosAlmacen td {
        vertical-align: middle;
        white-space: nowrap;
    }
    #tableProductosAlmacen th.col-descripcion, #tableProductosAlmacen td.col-descripcion {
        min-width: 350px !important;
        width: 400px !important;
        white-space: normal !important;
        word-break: break-word;
    }
</style>

<!-- Header Admin 02 -->
<?php require_once("Template/header_02.php"); ?>

<!-- CONTENIDO DE VISTA -->
<section role="main" class="content-body fondo-general">

    <header class="page-header">
        <h2><?= $data['page_form_title']; ?></h2>

        <div class="right-wrapper text-end">
            <ol class="breadcrumbs">
                <li>
                    <a href="<?= base_url() ?>/inicio">
                        <i class="bx bx-home-alt"></i>
                    </a>
                </li>
                <li><span>Inicio</span></li>
                <li><span><?= $data['page_breadcrumb']; ?></span></li>
            </ol>
            <div class="sidebar-right-toggle" style="cursor: default;"></div>
        </div>
    </header>

    <!-- start: page -->
    <div class="row">
        <div class="col-12">
            
            <!-- Buscador Inteligente -->
            <div class="search-box-container mb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="fa-solid fa-sparkles text-primary fs-4 me-2"></i>
                    <h4 class="m-0">Buscador Inteligente de Productos</h4>
                </div>
                <p class="mb-3 search-description">
                    Ingrese la consulta en lenguaje natural (ejemplo: <code>¿Tienen un sello para bomba Goulds?</code>) para consultar disponibilidades y existencias por almacén en tiempo real.
                </p>
                <form id="formBuscadorProductos" onsubmit="return false;">
                    <label for="inputBuscarProducto" class="form-label">Producto, clave o descripción</label>
                    <div class="input-group search-input-group">
                        <input type="text" id="inputBuscarProducto" class="form-control" placeholder="Escriba su consulta o el nombre del producto..." autocomplete="off">
                        <button type="submit" id="btnBuscarProducto" class="btn lfm-btn-action lfm-btn-action--primary">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
                        </button>
                        <button type="button" id="btnLimpiarBusqueda" class="btn lfm-btn-action lfm-btn-action--quiet lfm-btn-action--icon" title="Limpiar búsqueda" aria-label="Limpiar búsqueda" style="display: none;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Respuesta Inteligente del Sistema -->
            <div id="boxRespuestaInteligente" class="smart-answer-card p-4 mb-4" style="display: none;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="smart-answer-header text-primary">
                        <i class="fa-solid fa-robot me-2"></i>Respuesta del Sistema
                    </div>
                    <span id="badgeTotalResultados" class="badge bg-primary"></span>
                </div>
                <div id="contentRespuestaInteligente" class="smart-answer-body"></div>
            </div>

            <!-- Tabla de Productos -->
            <section class="chart-container border mb-4" id="panel_productos_almacen">
                <h2 class="chart-title"><i class="fa-regular fa-bars-staggered text-primary me-2" aria-hidden="true"></i><?= $data['page_card_title']; ?></h2>

                <div class="table-responsive export-table">
                    <table class="table table-bordered table-striped table-hover width-full" id="tableProductosAlmacen">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">Foto(s)</th>
                                <th class="text-center" style="width: 95px;">Existencia</th>
                                <th class="text-center" style="width: 95px;">Reservadas</th>
                                <th class="text-center" style="width: 95px;">Disponibles</th>
                                <th>Clave</th>
                                <th>CCN</th>
                                <th class="col-descripcion" style="min-width: 350px;">Descripción</th>
                                <th>Marca</th>
                                <th>Submarca</th>
                                <th>Línea Producto</th>
                                <th>Categoría</th>
                                <th>Unidad</th>
                                <th>Modelo</th>
                                <th>N° Catálogo</th>
                                <th>N° Parte</th>
                                <th>Serie</th>
                                <th>Material</th>
                                <th>Grupo</th>
                                <th>Clave SAT</th>
                            </tr>
                            <tr class="filters-row bg-light">
                                <th class="p-1 text-center"><button type="button" id="btnResetColFilters" class="btn btn-sm btn-outline-secondary py-0 px-1 btn-reset-col-filters" title="Limpiar filtros de columna" aria-label="Limpiar filtros de columna"><i class="fa-solid fa-filter-circle-xmark"></i></button></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="1" placeholder="Filtrar Existencia" style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="2" placeholder="Filtrar Reservadas" style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="3" placeholder="Filtrar Disponibles" style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="4" placeholder="Clave..." style="min-width: 80px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="5" placeholder="CCN..." style="min-width: 80px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="6" placeholder="Descripción..." style="min-width: 150px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="7" placeholder="Marca..." style="min-width: 80px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="8" placeholder="Submarca..." style="min-width: 80px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="9" placeholder="Línea..." style="min-width: 80px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="10" placeholder="Categoría..." style="min-width: 80px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="11" placeholder="Unidad..." style="min-width: 60px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="12" placeholder="Modelo..." style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="13" placeholder="Catálogo..." style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="14" placeholder="Parte..." style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="15" placeholder="Serie..." style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="16" placeholder="Material..." style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="17" placeholder="Grupo..." style="min-width: 70px;"></th>
                                <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" data-column-index="18" placeholder="SAT..." style="min-width: 70px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Cargado vía AJAX -->
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

</section>

<!-- Modal de Visualización de Fotografías en Carrusel / Slide -->
<div class="modal fade" id="modalFotoProducto" tabindex="-1" aria-labelledby="modalFotoProductoTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalFotoProductoTitle"><i class="fa-solid fa-images me-2 text-warning"></i>Fotografías del Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0 bg-dark position-relative overflow-hidden">
                <div id="carouselFotosProducto" class="carousel slide carousel-fade" data-bs-ride="false" data-bs-interval="false">
                    <!-- Indicadores -->
                    <div class="carousel-indicators" id="carouselIndicatorsFotos"></div>
                    
                    <!-- Diapositivas -->
                    <div class="carousel-inner" id="carouselInnerFotos" style="height: 460px;">
                        <!-- Insertadas dinámicamente -->
                    </div>
                    
                    <!-- Control Anterior -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselFotosProducto" data-bs-slide="prev" style="width: 12%;">
                        <span class="carousel-control-prev-icon p-3 bg-dark rounded-circle bg-opacity-75" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>

                    <!-- Control Siguiente -->
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselFotosProducto" data-bs-slide="next" style="width: 12%;">
                        <span class="carousel-control-next-icon p-3 bg-dark rounded-circle bg-opacity-75" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between align-items-center py-2 px-3">
                <div id="carouselFotoCaption" class="fw-semibold text-secondary photo-carousel-caption">
                    <!-- Contador de foto -->
                </div>
                <?php if (!empty($data['puede_rotar_fotos'])): ?>
                <div class="d-flex align-items-center gap-2" id="boxRotarFotos">
                    <input type="hidden" id="csrfRotarFoto" value="<?= htmlspecialchars($data['csrf_rotar_foto'], ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="button" class="btn btn-outline-dark btn-sm px-3 shadow-sm" id="btnGirarFotoIzq" onclick="rotarFotoModal('izq')" title="Girar 90° a la izquierda">
                        <i class="fa-solid fa-rotate-left me-1 text-primary"></i> Girar Izquierda
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm px-3 shadow-sm" id="btnGirarFotoDer" onclick="rotarFotoModal('der')" title="Girar 90° a la derecha">
                        <i class="fa-solid fa-rotate-right me-1 text-primary"></i> Girar Derecha
                    </button>
                    <span id="msgRotacionEstado" class="text-success fw-bold small ms-1" style="display: none;">
                        <i class="fa-solid fa-circle-check me-1"></i>Guardado
                    </span>
                </div>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- FIN CONTENIDO VISTA -->

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<div id="loadModalPermisos"></div>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>
