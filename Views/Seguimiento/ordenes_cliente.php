<?php /* View: Seguimiento - Órdenes de Compra Clientes */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Theme Custom CSS -->
<style>
    .kpi-card {
        border-radius: 10px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
    }
    .kpi-icon {
        font-size: 2.2rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .text-amount {
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .text-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .chart-container {
        background: #ffffff;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
        margin-bottom: 2rem;
    }
    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333333;
        margin-bottom: 1.2rem;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 0.75rem;
    }
    .table-custom th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        background-color: #fcfcfc;
    }
    .bg-primary-lighten { background-color: rgba(13, 110, 253, 0.08) !important; }
    .bg-success-lighten { background-color: rgba(25, 135, 84, 0.08) !important; }
    .bg-info-lighten { background-color: rgba(13, 202, 240, 0.08) !important; }
    .bg-warning-lighten { background-color: rgba(255, 193, 7, 0.08) !important; }
    .bg-danger-lighten { background-color: rgba(220, 53, 69, 0.08) !important; }
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
                <li><span>Seguimiento</span></li>
                <li><span>Órdenes de Compra Clientes</span></li>
            </ol>
            <div class="sidebar-right-toggle" style="cursor: default;"> </div>
        </div>
    </header>

    <!-- start: page -->
    <div class="row">

        <div class="col-12 loading-panel-showing">

            <div class="loading-panel">
                <div class="bounce-loader">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </div>

            <!-- Cabecera de Tarjeta e Información -->
            <div class="mb-4">
                <h4 class="text-primary fw-semibold mb-1 d-flex align-items-center">
                    <?= $data['page_card_title']; ?>
                    <i title="Info" style="cursor:pointer;"
                       class="text-primary fa-light fa-circle-question ms-2 fs-18"
                       data-bs-toggle="collapse"
                       data-bs-target="#collapseInfo"
                       aria-expanded="false"
                       aria-controls="collapseInfo"></i>
                </h4>
                <div class="collapse mt-1" id="collapseInfo">
                    <span class="text-info fw-normal"><?= $data['page_card_description']; ?></span>
                </div>
            </div>

            <!-- ========== FILTROS ========== -->
            <div class="row mb-4" id="panel_filtros">

                <div class="col-12">
                    <div class="border-bottom pb-2 mb-3">
                        <p class="mb-0 fw-semibold text-primary">
                            <i class="fa-regular fa-filter text-secondary me-2"></i> Filtros de Búsqueda
                        </p>
                    </div>
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-3">
                    <label class="control-label" for="filtro_fecha_ini">Fecha Inicio:</label>
                    <input type="date" class="form-control" id="filtro_fecha_ini" name="filtro_fecha_ini">
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-3">
                    <label class="control-label" for="filtro_fecha_fin">Fecha Fin:</label>
                    <input type="date" class="form-control" id="filtro_fecha_fin" name="filtro_fecha_fin">
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-3">
                    <label class="control-label" for="filtro_estatus">Estatus:</label>
                    <select class="select2 custom-select" id="filtro_estatus" name="filtro_estatus" style="width: 100%;">
                        <option value="">Todos los estatus</option>
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100 hvr-float-shadow d-flex justify-content-center align-items-center" id="btnFiltrar" style="height: 38px;">
                        <i class="fa-regular fa-magnifying-glass me-1"></i> Buscar
                    </button>
                </div>

            </div>
            <!-- ========== FIN FILTROS ========== -->

            <!-- ========== KPI CARDS ========== -->
            <div class="row mb-4" id="panel_kpis">

                <!-- KPI Card 1: Total Órdenes -->
                <div class="col-12 col-md-4 mb-3 mb-md-0">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body border d-flex align-items-center">
                            <div class="kpi-icon bg-primary-lighten text-primary me-3">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-primary d-block mb-1">Total Órdenes</span>
                                <div class="text-amount text-dark" id="kpi_total_ordenes">0</div>
                                <div class="text-muted text-4">En el periodo</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Card 2: Monto Total Acumulado -->
                <div class="col-12 col-md-4 mb-3 mb-md-0">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body border d-flex align-items-center">
                            <div class="kpi-icon bg-success-lighten text-success me-3">
                                <i class="fa-solid fa-circle-dollar-to-slot"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-success d-block mb-1">Monto Acumulado (USD)</span>
                                <div class="text-amount text-dark" id="kpi_monto_usd">$0.00 USD</div>
                                <div class="text-muted text-4" id="kpi_monto_mxn">$0.00 MXN</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Card 3: Órdenes con Seguimiento -->
                <div class="col-12 col-md-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body border d-flex align-items-center">
                            <div class="kpi-icon bg-info-lighten text-info me-3">
                                <i class="fa-solid fa-timeline"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-info d-block mb-1">Con Seguimiento</span>
                                <div class="text-amount text-dark" id="kpi_con_seguimiento">0</div>
                                <div class="text-muted text-4">Órdenes con bitácora</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ========== FIN KPI CARDS ========== -->

            <!-- ========== LISTA DE ÓRDENES ========== -->
            <div class="chart-container border mb-4" id="panel_lista_registros">

                <!-- Subtítulo -->
                <div class="border-bottom pb-2 mb-3">
                    <p class="mb-0 fw-semibold text-primary">
                        <i class="fa-regular fa-bars-staggered text-secondary me-2"></i> Lista de Órdenes de Compra
                    </p>
                </div>

                <!-- Tabla de Órdenes -->
                <div class="table-responsive export-table">
                    <table id="tableOrdenes"
                           class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                        <thead class="table-light">
                            <tr>
                                <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
                                <th class="border-bottom-0 fw-semibold text-center">Pedido #</th>
                                <th class="border-bottom-0 fw-semibold text-center">Fecha Pedido</th>
                                <th class="border-bottom-0 fw-semibold text-center">Cliente</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto / Título</th>
                                <th class="border-bottom-0 fw-semibold text-center">Vendedor</th>
                                <th class="border-bottom-0 fw-semibold text-center">Monto</th>
                                <th class="border-bottom-0 fw-semibold text-center">Estatus</th>
                                <th class="border-bottom-0 fw-semibold text-center">Seguimientos</th>
                                <th class="border-bottom-0 fw-semibold text-center">Último Seguimiento</th>
                                <th class="border-bottom-0 fw-semibold text-center">Fecha Últ. Seg.</th>
                                <th class="border-bottom-0 fw-semibold text-center">Usuario Últ. Seg.</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- Fin Tabla de Órdenes -->

            </div>
            <!-- ========== FIN LISTA ========== -->

            <!-- ========== PANEL DETALLE ========== -->
            <div class="chart-container border mb-4" id="panel_detalle" style="display: none;">

                <!-- Encabezado detalle -->
                <div class="border-bottom pb-2 mb-3">
                    <p class="mb-0 fw-semibold text-primary">
                        <i class="fa-regular fa-file-lines text-secondary me-2"></i> Detalle de Orden y Seguimientos
                    </p>
                </div>

                <!-- Botón regresar -->
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-secondary btnReturnList" id="btnRegresar">
                        <i class="fa-regular fa-arrow-left me-1"></i> Regresar a la lista
                    </button>
                </div>

                <!-- Datos generales de la orden -->
                <div class="mb-4">
                    <div class="card border p-0 shadow-sm">
                        <div class="card-header bg-header-card">
                            <p class="mb-0 fw-600 text-primary fs-13">
                                <i class="fa-regular fa-receipt me-1"></i> Datos Generales de la Orden
                            </p>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="control-label fs-12">Cliente:</label>
                                    <h6 class="vistadatos fs-13 text-muted" id="det_cliente">
                                        <i class="fa-light fa-brake-warning"></i> —
                                    </h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="control-label fs-12">Proyecto / Título:</label>
                                    <h6 class="vistadatos fs-13 text-muted" id="det_titulo">
                                        <i class="fa-light fa-brake-warning"></i> —
                                    </h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="control-label fs-12">Vendedor:</label>
                                    <h6 class="vistadatos fs-13 text-muted" id="det_vendedor">
                                        <i class="fa-light fa-brake-warning"></i> —
                                    </h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="control-label fs-12">Estatus:</label>
                                    <h6 class="vistadatos fs-13" id="det_estatus">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="control-label fs-12">Fecha Pedido:</label>
                                    <h6 class="vistadatos fs-13 text-muted" id="det_fecha_pedido">
                                        <i class="fa-light fa-brake-warning"></i> —
                                    </h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="control-label fs-12">Monto:</label>
                                    <h6 class="vistadatos fs-13 text-muted" id="det_monto">
                                        <i class="fa-light fa-brake-warning"></i> —
                                    </h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="control-label fs-12">Clasificación:</label>
                                    <h6 class="vistadatos fs-13 text-muted" id="det_clasificacion">
                                        <i class="fa-light fa-brake-warning"></i> —
                                    </h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="control-label fs-12">CLUES:</label>
                                    <h6 class="vistadatos fs-13 text-muted" id="det_clues">
                                        <i class="fa-light fa-brake-warning"></i> —
                                    </h6>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Seguimientos -->
                <div>
                    <div class="card border p-0 shadow-sm">
                        <div class="card-header bg-header-card d-flex justify-content-between align-items-center">
                            <p class="mb-0 fw-600 text-primary fs-13">
                                <i class="fa-regular fa-timeline me-1"></i> Historial de Seguimientos
                                <span class="badge bg-primary ms-2" id="badge_total_seg">0</span>
                            </p>
                        </div>
                        <div class="card-body p-0">

                            <!-- Loading seguimientos -->
                            <div id="loading_seguimientos" class="text-center p-4 d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <span class="ms-2 fs-13 text-muted">Cargando seguimientos...</span>
                            </div>

                            <!-- Sin seguimientos -->
                            <div id="sin_seguimientos" class="text-center p-4 d-none">
                                <i class="fa-light fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted fs-13">Esta orden no tiene seguimientos registrados.</p>
                            </div>

                            <!-- Timeline de seguimientos -->
                            <div id="timeline_seguimientos" class="p-3">
                                <!-- Se llena dinámicamente con JS -->
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <!-- ========== FIN PANEL DETALLE ========== -->

        </div>

    </div>
    <!-- end: page -->

</section>
<!-- FIN CONTENIDO VISTA -->


<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<!-- Modals -->
<div id="loadModalPermisos"></div>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>
