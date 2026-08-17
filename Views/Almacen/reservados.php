<?php /* View: Almacén - Control de Productos Reservados */ ?>
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
    .badge-antiguedad {
        font-size: 0.85rem;
        padding: 0.4em 0.7em;
        font-weight: 600;
    }
    #tableProductosReservados {
        width: 100% !important;
    }
    #tableProductosReservados th, #tableProductosReservados td {
        vertical-align: middle;
        white-space: nowrap;
    }
    #tableProductosReservados th.col-descripcion, #tableProductosReservados td.col-descripcion {
        min-width: 320px !important;
        width: 380px !important;
        white-space: normal !important;
        word-break: break-word;
    }
    #tableProductosReservados th.col-cliente, #tableProductosReservados td.col-cliente {
        min-width: 220px !important;
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
    /* Estilos Tarjetas KPI */
    .kpi-card {
        border-radius: 10px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
    }
    .kpi-icon {
        font-size: 1.8rem;
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .text-amount {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .text-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .bg-primary-lighten { background-color: rgba(13, 110, 253, 0.08) !important; }
    .bg-success-lighten { background-color: rgba(25, 135, 84, 0.08) !important; }
    .bg-info-lighten { background-color: rgba(13, 202, 240, 0.08) !important; }
    .bg-warning-lighten { background-color: rgba(255, 193, 7, 0.12) !important; }
    .bg-danger-lighten { background-color: rgba(220, 53, 69, 0.08) !important; }
    .bg-purple-lighten { background-color: rgba(111, 66, 193, 0.08) !important; }
    .text-purple { color: #6f42c1 !important; }
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
                    <h4 class="m-0 text-white fw-bold">Filtros de Productos Reservados</h4>
                </div>
                
                <form id="formFiltrosReservados" onsubmit="return false;">
                    <div class="row g-3 align-items-end">
                        <!-- Filtro a) Por Cliente -->
                        <div class="col-md-4">
                            <label for="selectFiltroCliente" class="filter-label">
                                <i class="fa-solid fa-user-tag me-1 text-info"></i> Cliente:
                            </label>
                            <select id="selectFiltroCliente" class="form-select filter-control">
                                <option value="">-- Todos los clientes con reservas --</option>
                                <?php if (!empty($data['clientes'])): ?>
                                    <?php foreach ($data['clientes'] as $cli): ?>
                                        <option value="<?= htmlspecialchars($cli['id']); ?>">
                                            <?= htmlspecialchars($cli['nombre_cliente']); ?> (<?= $cli['total_reservas']; ?> reservas)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Filtro b) Por Almacén -->
                        <div class="col-md-3">
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

                        <!-- Filtro c) Por Tiempo / Antigüedad -->
                        <div class="col-md-3">
                            <label for="selectFiltroAntiguedad" class="filter-label">
                                <i class="fa-solid fa-clock-rotate-left me-1 text-warning"></i> Antigüedad de Reserva:
                            </label>
                            <select id="selectFiltroAntiguedad" class="form-select filter-control">
                                <option value="">-- Cualquier tiempo --</option>
                                <option value="menos7">Recientes (&lt; 7 días)</option>
                                <option value="7a15">Atención (7 a 15 días)</option>
                                <option value="15a30">Urgentes (15 a 30 días)</option>
                                <option value="mas30">Críticas (&gt; 30 días)</option>
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

            <!-- ========== TARJETAS KPI ========== -->
            <div class="row mb-4" id="panel_kpis">
                <div class="form-group col-12 mb-3">
                    <div class="border-bottom pb-2 d-flex align-items-center justify-content-between">
                        <p class="mb-0 fw-semibold text-primary" style="font-size: 1.05rem;">
                            <i class="fa-regular fa-chart-simple me-2 text-secondary"></i> Indicadores de Productos Reservados
                        </p>
                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.82rem;">
                            <i class="fa-solid fa-shield-halved text-info me-1"></i> Retención y Disponibilidad
                        </span>
                    </div>
                </div>

                <div class="row g-3" id="container_kpi_cards">
                    <!-- Tarjeta 1: Total Partidas -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-card shadow-sm border-0 bg-primary-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-primary text-white me-3">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                                <div>
                                    <div class="text-label text-primary">Partidas Reservadas</div>
                                    <div class="text-amount text-dark" id="kpi_total_partidas">0</div>
                                    <small class="text-muted" id="kpi_sub_partidas">Líneas de productos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Total Unidades Retenidas -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-card shadow-sm border-0 bg-warning-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-warning text-dark me-3">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                                <div>
                                    <div class="text-label text-dark">Piezas Retenidas</div>
                                    <div class="text-amount text-dark" id="kpi_total_unidades">0</div>
                                    <small class="text-muted">Unidades apartadas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 3: Clientes con Reservas -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-card shadow-sm border-0 bg-info-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-info text-white me-3">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <div class="text-label text-info">Clientes con Reserva</div>
                                    <div class="text-amount text-dark" id="kpi_total_clientes">0</div>
                                    <small class="text-muted">Clientes únicos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 4: Reservas Críticas (> 15 días) -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-card shadow-sm border-0 bg-danger-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-danger text-white me-3">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div>
                                    <div class="text-label text-danger">Antigüedad &gt; 15 Días</div>
                                    <div class="text-amount text-danger" id="kpi_criticas">0</div>
                                    <small class="text-muted">Requieren seguimiento</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ========== FIN TARJETAS KPI ========== -->

            <!-- Tabla DataTables de Productos Reservados -->
            <section class="card card-featured card-featured-primary shadow-sm mb-4">
                <header class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title m-0"><?= $data['page_card_title']; ?></h2>
                </header>

                <div class="p-4 card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover width-full" id="tableProductosReservados">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 75px;">Foto(s)</th>
                                    <th class="col-cliente" style="min-width: 220px;">Cliente</th>
                                    <th class="text-center" style="width: 150px;">Tiempo Reservado</th>
                                    <th class="text-center">Fecha Reserva</th>
                                    <th class="text-center">Cant. Reservada</th>
                                    <th>Clave</th>
                                    <th>CCN</th>
                                    <th class="col-descripcion" style="min-width: 320px;">Descripción</th>
                                    <th>Almacén</th>
                                    <th>Orden Compra / Pedido</th>
                                    <th>Marca</th>
                                    <th>Submarca</th>
                                    <th>Línea Producto</th>
                                    <th>Categoría</th>
                                    <th>Usuario que Reservó</th>
                                </tr>
                                <tr class="filters-row bg-light">
                                    <th class="p-1 text-center"><button type="button" id="btnResetColFiltersReservados" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Limpiar filtros de columna" style="font-size: 0.75rem;"><i class="fa-solid fa-filter-circle-xmark"></i></button></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Cliente..." style="min-width: 120px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Tiempo..." style="min-width: 90px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Fecha..." style="min-width: 80px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Cant..." style="min-width: 60px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Clave..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="CCN..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Descripción..." style="min-width: 140px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Almacén..." style="min-width: 80px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="OC / Pedido..." style="min-width: 90px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Marca..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Submarca..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Línea..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Categoría..." style="min-width: 70px;"></th>
                                    <th class="p-1"><input type="text" class="form-control form-control-sm col-filter-input" placeholder="Usuario..." style="min-width: 70px;"></th>
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
