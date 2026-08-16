<?php /* View: Almacén - Productos y Existencias */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Theme Custom CSS -->
<style>
    .search-box-container {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: #fff;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
    }
    .search-input-group .form-control {
        border-radius: 8px 0 0 8px;
        font-size: 1.05rem;
        padding: 0.75rem 1.25rem;
        border: 2px solid #334155;
        background-color: #0f172a;
        color: #f8fafc;
    }
    .search-input-group .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        background-color: #1e293b;
        color: #fff;
    }
    .btn-search-smart {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 0 8px 8px 0;
        transition: all 0.2s ease;
    }
    .btn-search-smart:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        transform: translateY(-1px);
    }
    .smart-answer-card {
        border: none;
        border-left: 5px solid #3b82f6;
        background: #f8fafc;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .smart-answer-header {
        font-weight: 700;
        font-size: 1.1rem;
        color: #1e293b;
    }
    .smart-answer-body {
        white-space: pre-line;
        font-size: 0.98rem;
        color: #334155;
        line-height: 1.6;
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
    .badge-stock {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
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
    .filters-row th {
        padding: 4px 6px !important;
        background-color: #f1f5f9 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        vertical-align: middle !important;
    }
    .filters-row input {
        width: 100%;
        font-size: 0.8rem;
        padding: 3px 6px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background-color: #ffffff;
        color: #1e293b;
        font-weight: normal;
    }
    .filters-row input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }
    .filters-row input::placeholder {
        color: #94a3b8;
        font-size: 0.75rem;
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
                    <i class="fa-solid fa-sparkles text-warning fs-4 me-2"></i>
                    <h4 class="m-0 text-white fw-bold">Buscador Inteligente de Productos</h4>
                </div>
                <p class="mb-3" style="color: #cbd5e1; font-size: 0.92rem;">
                    Ingrese la consulta en lenguaje natural (ejemplo: <code>¿Tienen un sello para bomba Goulds?</code>) para consultar disponibilidades y existencias por almacén en tiempo real.
                </p>
                <form id="formBuscadorProductos" onsubmit="return false;">
                    <div class="input-group search-input-group">
                        <input type="text" id="inputBuscarProducto" class="form-control" placeholder="Escriba su consulta o el nombre del producto..." autocomplete="off">
                        <button type="submit" id="btnBuscarProducto" class="btn btn-search-smart">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
                        </button>
                        <button type="button" id="btnLimpiarBusqueda" class="btn btn-secondary" title="Limpiar búsqueda" style="border-radius: 0 8px 8px 0; display: none;">
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
            <section class="card card-featured card-featured-primary shadow-sm mb-4">
                <header class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title m-0"><?= $data['page_card_title']; ?></h2>
                </header>

                <div class="p-4 card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover width-full" id="tableProductosAlmacen">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 80px;">Foto(s)</th>
                                    <th class="text-center" style="width: 95px;">Existencias</th>
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
                                    <th class="p-1 text-center"><button type="button" id="btnResetColFilters" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Limpiar filtros de columna" style="font-size: 0.75rem;"><i class="fa-solid fa-filter-circle-xmark"></i></button></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Filtrar..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Clave..." style="min-width: 80px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="CCN..." style="min-width: 80px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Descripción..." style="min-width: 150px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Marca..." style="min-width: 80px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Submarca..." style="min-width: 80px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Línea..." style="min-width: 80px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Categoría..." style="min-width: 80px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Unidad..." style="min-width: 60px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Modelo..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Catálogo..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Parte..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Serie..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Material..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Grupo..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="SAT..." style="min-width: 70px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Cargado vía AJAX -->
                            </tbody>
                        </table>
                    </div>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <div id="carouselFotoCaption" class="fw-semibold text-secondary" style="font-size: 0.9rem;">
                    <!-- Contador de foto -->
                </div>
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
