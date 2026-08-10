<?php /* View: Almacén - Inventario */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Theme Custom CSS -->
<style>
    .filter-box-container {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: #fff;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
    }
    .filter-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #cbd5e1;
        margin-bottom: 0.4rem;
    }
    .filter-control {
        border-radius: 8px;
        font-size: 0.95rem;
        border: 2px solid #334155;
        background-color: #0f172a;
        color: #f8fafc;
    }
    .filter-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        background-color: #1e293b;
        color: #fff;
    }
    .btn-filter-action {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.6rem 1.4rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .btn-filter-action:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        transform: translateY(-1px);
    }
    .btn-filter-clear {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.6rem 1.2rem;
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
    #tableInventarioAlmacen {
        width: 100% !important;
    }
    #tableInventarioAlmacen th, #tableInventarioAlmacen td {
        vertical-align: middle;
        white-space: nowrap;
    }
    #tableInventarioAlmacen th.col-descripcion, #tableInventarioAlmacen td.col-descripcion {
        min-width: 350px !important;
        width: 400px !important;
        white-space: normal !important;
        word-break: break-word;
    }
    /* Estilos select2 en modo oscuro en filtros */
    .select2-container--bootstrap-5 .select2-selection,
    .select2-container--default .select2-selection--single {
        border-radius: 8px !important;
        min-height: 42px !important;
        padding-top: 5px !important;
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

            <!-- Encabezado para Filtros de Información -->
            <div class="filter-box-container mb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="fa-solid fa-filter text-primary fs-4 me-2"></i>
                    <h4 class="m-0 text-white fw-bold">Filtros de Información de Inventario</h4>
                </div>
                
                <form id="formFiltrosInventario" onsubmit="return false;">
                    <div class="row g-3 align-items-end">
                        <!-- Filtro a) Por Almacén -->
                        <div class="col-md-5">
                            <label for="selectFiltroAlmacen" class="filter-label">
                                <i class="fa-solid fa-warehouse me-1 text-info"></i> Almacén:
                            </label>
                            <select id="selectFiltroAlmacen" class="form-select filter-control">
                                <option value="">-- Todos los almacenes --</option>
                                <?php if (!empty($data['almacenes'])): ?>
                                    <?php foreach ($data['almacenes'] as $alm): ?>
                                        <option value="<?= htmlspecialchars($alm['ccvealmacen']); ?>">
                                            <?= htmlspecialchars($alm['cdscalmacen']); ?> (<?= htmlspecialchars($alm['ccvealmacen']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Filtro b) Por Producto (Selección Única) -->
                        <div class="col-md-5">
                            <label for="selectFiltroProducto" class="filter-label">
                                <i class="fa-solid fa-box me-1 text-info"></i> Producto (Clave, CCN o Nombre):
                            </label>
                            <select id="selectFiltroProducto" class="form-select filter-control" style="width: 100%;">
                                <option value="">-- Todos los productos --</option>
                            </select>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="col-md-2 d-flex gap-2">
                            <button type="button" id="btnAplicarFiltros" class="btn btn-filter-action flex-grow-1">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar
                            </button>
                            <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary btn-filter-clear" title="Limpiar filtros">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabla DataTables de Inventario Completo -->
            <section class="card card-featured card-featured-primary shadow-sm mb-4">
                <header class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title m-0"><?= $data['page_card_title']; ?></h2>
                </header>

                <div class="p-4 card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover width-full" id="tableInventarioAlmacen">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 80px;">Foto(s)</th>
                                    <th>Almacén</th>
                                    <th class="text-center">Existencia</th>
                                    <th class="text-end">Costo Promedio / Identificado</th>
                                    <th class="text-center">Moneda</th>
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

<script>
    const menu = <?= $data['menu']; ?>;
</script>

<div id="loadModalPermisos"></div>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>
