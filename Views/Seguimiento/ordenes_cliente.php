<?php /* View: Seguimiento - Órdenes de Compra Clientes */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Theme Custom CSS -->
<link rel="stylesheet" type="text/css" href="<?= assets() ?>/app/css/chart.css?v=<?= version(); ?>">
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

    /* Estilos personalizados DataTable idénticos a la imagen */
    .export-table table.dataTable {
        border-collapse: separate !important;
        border-spacing: 0;
        border: 1px solid #ced4da !important;
        border-radius: 4px;
        overflow: hidden;
    }
    .export-table table.dataTable thead tr:first-child {
        background-color: #00809F !important;
    }
    .export-table table.dataTable thead tr:first-child th {
        color: #ffffff !important;
        background-color: #00809F !important;
        font-weight: 700 !important;
        font-size: 12.5px !important;
        vertical-align: middle !important;
        border-right: 1px solid rgba(255, 255, 255, 0.25) !important;
        border-bottom: 1px solid #006882 !important;
        padding: 9px 12px !important;
        text-transform: none !important;
    }
    .export-table table.dataTable thead tr:first-child th:last-child {
        border-right: none !important;
    }
    .export-table table.dataTable thead tr:first-child th.sorting:before,
    .export-table table.dataTable thead tr:first-child th.sorting:after,
    .export-table table.dataTable thead tr:first-child th.sorting_asc:before,
    .export-table table.dataTable thead tr:first-child th.sorting_asc:after,
    .export-table table.dataTable thead tr:first-child th.sorting_desc:before,
    .export-table table.dataTable thead tr:first-child th.sorting_desc:after {
        color: #ffffff !important;
        opacity: 0.85 !important;
    }
    .export-table table.dataTable thead tr:nth-child(2) {
        background-color: #f8f9fa !important;
    }
    .export-table table.dataTable thead tr:nth-child(2) th {
        background-color: #f8f9fa !important;
        padding: 4px 6px !important;
        border-bottom: 1px solid #ced4da !important;
        border-right: 1px solid #e9ecef !important;
    }
    .export-table table.dataTable thead tr:nth-child(2) input {
        height: 25px !important;
        max-height: 25px !important;
        font-size: 11px !important;
        border: 1px solid #ced4da !important;
        border-radius: 3px !important;
        background-color: #ffffff !important;
        color: #495057 !important;
        text-align: center;
        padding: 2px 4px;
        box-shadow: none !important;
    }
    .export-table table.dataTable thead tr:nth-child(2) input::placeholder {
        color: #adb5bd !important;
        font-weight: 300;
    }
    .export-table table.dataTable tbody tr td {
        font-size: 12px !important;
        vertical-align: middle !important;
        padding: 8px 10px !important;
        border-color: #e9ecef !important;
    }
    .export-table table.dataTable.table-striped tbody tr:nth-of-type(odd) {
        background-color: #ffffff !important;
    }
    .export-table table.dataTable.table-striped tbody tr:nth-of-type(even) {
        background-color: #f8f9fa !important;
    }
    .export-table table.dataTable.table-hover tbody tr:hover > * {
        background-color: #e8f4f8 !important;
    }
    .export-table table.dataTable tbody tr.selected > * {
        background-color: #00809f26 !important;
        color: #1d2127 !important;
    }
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #00809F !important;
        border-color: #00809F !important;
        color: #ffffff !important;
    }
    .dataTables_wrapper .dataTables_paginate .page-link {
        color: #00809F;
        border-radius: 4px;
        margin: 0 2px;
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

            <!-- ========== FILTROS ========== -->
            <div class="row mb-4" id="panel_filtros">

              

               <div class="form-group col-12 mb-2">
                    <div class="border-bottom pb-2">
                        <p class="mb-0 fw-semibold text-primary">
                            <i class="fa-regular fa-filter text-secondary me-2"></i> Filtros de Búsqueda
                        </p>
                    </div>
                </div>

               
                <div class="form-group col-12 col-sm-6 col-lg-3 pt-0">
                    <label class="form-label" for="filtro_fecha_ini">Fecha Inicio:</label>
                    <!-- <input type="date" class="form-control" id="filtro_fecha_ini" name="filtro_fecha_ini"> -->
                    <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" 
                    name="filtro_fecha_ini" id="filtro_fecha_ini" placeholder="dd/mm/aaaa" required="" maxlength="10">

                </div>

                <div class="form-group col-12 col-sm-6 col-lg-3 pt-0">
                    <label class="form-label" for="filtro_fecha_fin">Fecha Fin:</label>
                     <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" 
                    name="filtro_fecha_fin" id="filtro_fecha_fin" placeholder="dd/mm/aaaa" required="" maxlength="10">
                    <!-- <input type="date" class="form-control" id="filtro_fecha_fin" name="filtro_fecha_fin"> -->
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-3 pt-0 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100 hvr-float-shadow d-flex justify-content-center align-items-center" id="btnFiltrar" style="height: 38px;">
                        <i class="fa-regular fa-magnifying-glass me-1"></i> Buscar
                    </button>
                </div>

            </div>
            <!-- ========== FIN FILTROS ========== -->

            <!-- ========== KPI CARDS ========== -->
            <div class="row mb-4" id="panel_kpis">

                <div class="form-group col-12 mb-3">
                    <div class="border-bottom pb-2">
                        <p class="mb-0 fw-semibold text-primary">
                            <i class="fa-regular fa-file-chart-pie me-2 text-secondary"></i> Indicadores de Rendimiento (KPIs)
                        </p>
                    </div>
                </div>

                <!-- KPI Card 1: Total Órdenes -->
                <div class="col-12 col-lg-4 mb-4">
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
                <div class="col-12 col-lg-4 mb-4">
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
                <div class="col-12 col-lg-4 mb-4">
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
                <div class="chart-title">
                    <i class="fa-regular fa-bars-staggered text-primary me-2"></i> Lista de Órdenes de Compra
                </div>

                <!-- Tabla de Órdenes -->
                <div class="table-responsive export-table">
                    <table id="tableOrdenes"
                           class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                        <thead>
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
                <div class="chart-title">
                    <i class="fa-regular fa-file-lines text-primary me-2"></i> Detalle de Orden y Seguimientos
                </div>

                <!-- Botón regresar -->
                <div class="mb-3">
                    <button type="button" class="btn btn-primary hvr-float-shadow d-flex justify-content-center align-items-center btnReturnList" id="btnRegresar">
                        <i class="fa-regular fa-arrow-left me-1"></i> Regresar a la lista
                    </button>
                </div>

                <!-- Datos generales de la orden -->
                <div class="mb-4">
                    <div class="card border p-0 shadow-sm rounded-3">
                        <div class="card-header bg-light border-bottom py-2.5 px-3">
                            <span class="fw-bold fs-13 text-primary text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fa-regular fa-receipt me-1 text-primary"></i> Datos Generales de la Orden
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="p-2.5 bg-light rounded border h-100">
                                        <label class="form-label fs-11 text-uppercase fw-bold text-primary mb-1 d-block">
                                            <i class="fa-regular fa-building me-1"></i> Cliente:
                                        </label>
                                        <div class="vistadatos fs-13 fw-bold text-dark" id="det_cliente">—</div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="p-2.5 bg-light rounded border h-100">
                                        <label class="form-label fs-11 text-uppercase fw-bold text-primary mb-1 d-block">
                                            <i class="fa-regular fa-folder-open me-1"></i> Proyecto / Título:
                                        </label>
                                        <div class="vistadatos fs-13 fw-semibold text-dark" id="det_titulo">—</div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="p-2.5 bg-light rounded border h-100">
                                        <label class="form-label fs-11 text-uppercase fw-bold text-primary mb-1 d-block">
                                            <i class="fa-regular fa-user me-1"></i> Vendedor:
                                        </label>
                                        <div class="vistadatos fs-13 fw-semibold text-dark" id="det_vendedor">—</div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="p-2.5 bg-light rounded border h-100">
                                        <label class="form-label fs-11 text-uppercase fw-bold text-primary mb-1 d-block">
                                            <i class="fa-regular fa-tag me-1"></i> Estatus:
                                        </label>
                                        <div class="vistadatos fs-13 fw-semibold" id="det_estatus">—</div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="p-2.5 bg-light rounded border h-100">
                                        <label class="form-label fs-11 text-uppercase fw-bold text-primary mb-1 d-block">
                                            <i class="fa-regular fa-calendar-days me-1"></i> Fecha Pedido:
                                        </label>
                                        <div class="vistadatos fs-13 fw-semibold text-dark" id="det_fecha_pedido">—</div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="p-2.5 bg-light rounded border h-100">
                                        <label class="form-label fs-11 text-uppercase fw-bold text-primary mb-1 d-block">
                                            <i class="fa-regular fa-dollar-sign me-1"></i> Monto:
                                        </label>
                                        <div class="vistadatos fs-14 fw-bold text-success" id="det_monto">—</div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="p-2.5 bg-light rounded border h-100">
                                        <label class="form-label fs-11 text-uppercase fw-bold text-primary mb-1 d-block">
                                            <i class="fa-regular fa-layer-group me-1"></i> Clasificación:
                                        </label>
                                        <div class="vistadatos fs-13 fw-semibold text-dark" id="det_clasificacion">—</div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="p-2.5 bg-light rounded border h-100">
                                        <label class="form-label fs-11 text-uppercase fw-bold text-primary mb-1 d-block">
                                            <i class="fa-regular fa-hashtag me-1"></i> CLUES:
                                        </label>
                                        <div class="vistadatos fs-13 fw-semibold text-dark" id="det_clues">—</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Seguimientos -->
                <div>
                    <div class="card border p-0 shadow-sm rounded-3">
                        <div class="card-header bg-light border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-13 text-primary text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="fa-regular fa-timeline me-1 text-primary"></i> Historial de Seguimientos
                                <span class="badge bg-primary rounded-pill ms-2" id="badge_total_seg">0</span>
                            </span>
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


<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>
