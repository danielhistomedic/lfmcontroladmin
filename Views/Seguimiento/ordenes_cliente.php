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
        font-size: 2rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .text-amount {
        font-size: var(--font-report-title);
        font-weight: var(--font-weight-bold);
        line-height: var(--line-height-tight);
    }
    .text-label {
        font-size: var(--font-small);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: var(--font-weight-semibold);
    }
    .chart-container {
        background: #ffffff;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
        margin-bottom: 2rem;
    }
    .chart-title {
        font-size: var(--font-section-title);
        font-weight: var(--font-weight-semibold);
        color: #333333;
        margin-bottom: 1.2rem;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 0.75rem;
    }
    .table-custom th {
        font-size: var(--font-table-header);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
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
                    <label class="form-label" for="filtro_num_orden">Número Orden Cliente:</label>
                    <input type="text" class="form-control" autocomplete="off" name="filtro_num_orden" id="filtro_num_orden" placeholder="Buscar por núm. orden...">
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
                                <div class="text-muted text-2">En el periodo</div>
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
                                <div class="text-muted text-2" id="kpi_monto_mxn">$0.00 MXN</div>
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
                                <div class="text-muted text-2">Órdenes con bitácora</div>
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
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto Venta</th>
                                <th class="border-bottom-0 fw-semibold text-center">Orden Compra</th>
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

            <!-- ========== CALENDARIO DE ENTREGAS A CLIENTES ========== -->
            <div class="chart-container border mb-4" id="panel_calendario_entregas">

                <!-- Encabezado de la sección -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center chart-title pb-3 mb-3">
                    <div class="mb-2 mb-md-0">
                        <span class="fs-16 fw-semibold text-dark">
                            <i class="fa-regular fa-calendar-days text-primary me-2"></i> Calendario de Fechas Límite de Entrega a Clientes
                        </span>
                        <p class="text-muted fs-12 mb-0">Estatus de cumplimiento de entregas estimado por orden y partida</p>
                    </div>

                    <!-- Leyendas y Badges Resumen -->
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-danger p-2 fs-12 d-flex align-items-center" title="Pendientes con fecha de entrega vencida">
                            <i class="fa-regular fa-circle-exclamation me-1"></i> Vencidas: <strong id="cal_count_vencidos" class="ms-1">0</strong>
                        </span>
                        <span class="badge bg-warning text-dark p-2 fs-12 d-flex align-items-center" title="Pendientes próximas a vencer en 7 días">
                            <i class="fa-regular fa-clock me-1"></i> Próximas (7d): <strong id="cal_count_proximos" class="ms-1">0</strong>
                        </span>
                        <span class="badge bg-success p-2 fs-12 d-flex align-items-center" title="Pendientes con entrega en tiempo">
                            <i class="fa-regular fa-circle-check me-1"></i> En Tiempo: <strong id="cal_count_en_tiempo" class="ms-1">0</strong>
                        </span>
                        <span class="badge bg-primary p-2 fs-12 d-flex align-items-center" title="Partidas ya entregadas al cliente (entregado = 1)">
                            <i class="fa-regular fa-box-check me-1"></i> Entregadas: <strong id="cal_count_entregados" class="ms-1">0</strong>
                        </span>
                        <span class="badge bg-secondary p-2 fs-12 d-flex align-items-center" title="Total de partidas con fecha de entrega">
                            Total: <strong id="cal_count_total" class="ms-1">0</strong>
                        </span>
                    </div>
                </div>

                <!-- Filtros rápidos por estatus -->
                <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                    <span class="fs-12 text-muted me-1 fw-semibold">Filtrar en calendario:</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary active btn-filter-cal" data-filter="todos">
                        <i class="fa-regular fa-layer-group me-1"></i> Todos
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger btn-filter-cal" data-filter="vencido">
                        <i class="fa-regular fa-circle-exclamation me-1"></i> Vencidas
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-warning text-dark btn-filter-cal" data-filter="proximo">
                        <i class="fa-regular fa-clock me-1"></i> Próximas
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-success btn-filter-cal" data-filter="en_tiempo">
                        <i class="fa-regular fa-circle-check me-1"></i> En Tiempo
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-primary btn-filter-cal" data-filter="entregado">
                        <i class="fa-regular fa-box-check me-1"></i> Entregadas
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-filter-cal" data-filter="cancelado">
                        <i class="fa-regular fa-ban me-1"></i> Canceladas
                    </button>
                </div>

                <!-- Contenedor de FullCalendar -->
                <div class="p-2 border rounded bg-light">
                    <div id="calendarEntregas" style="min-height: 550px;"></div>
                </div>

            </div>
            <!-- ========== FIN CALENDARIO DE ENTREGAS ========== -->

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
                        <div class="card-header bg-header-card border-bottom py-2.5 px-3">
                            <p class="mb-0 fw-600 text-primary fs-13">
                                <i class="fa-regular fa-receipt me-1"></i> Datos Generales de la Orden
                            </p>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">

                                <div class="form-group col-12 col-sm-6 col-lg-3 pt-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Orden de Compra:</label>
                                    <h6 class="vistadatos fs-13 text-wrap text-break fw-bold text-danger mb-0" id="det_pedido_id">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Cliente:</label>
                                    <h6 class="vistadatos fs-13 text-wrap text-break fw-bold text-dark mb-0" id="det_cliente">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Proyecto Venta:</label>
                                    <h6 class="vistadatos fs-13 text-wrap text-break fw-bold text-dark mb-0" id="det_proyecto_id">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Proyecto / Título:</label>
                                    <h6 class="vistadatos fs-13 text-wrap text-break fw-semibold text-dark mb-0" id="det_titulo">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Vendedor:</label>
                                    <h6 class="vistadatos fs-13 text-wrap text-break fw-semibold text-dark mb-0" id="det_vendedor">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Estatus:</label>
                                    <h6 class="vistadatos fs-13 text-wrap text-break mb-0" id="det_estatus">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Fecha Pedido:</label>
                                    <h6 class="vistadatos fs-13 text-wrap text-break fw-semibold text-dark mb-0" id="det_fecha_pedido">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Monto:</label>
                                    <h6 class="vistadatos fs-14 text-wrap text-break fw-bold text-success mb-0" id="det_monto">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label fs-12 fw-bold text-primary mb-1">Clasificación:</label>
                                    <h6 class="vistadatos fs-13 text-wrap text-break fw-semibold text-dark mb-0" id="det_clasificacion">—</h6>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Seguimientos -->
                <div>
                    <div class="card border p-0 shadow-sm rounded-3">
                        <div class="card-header bg-header-card border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
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

<!-- Modal Detalle Entrega -->
<div class="modal fade" id="modalDetalleEntrega" tabindex="-1" aria-labelledby="modalDetalleEntregaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom py-2.5 px-3">
                <h5 class="modal-title fw-bold text-primary fs-14" id="modalDetalleEntregaLabel">
                    <i class="fa-regular fa-calendar-lines-pen me-2"></i> Detalle de Fecha Límite de Entrega
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Orden de Compra:</label>
                        <p class="fw-bold text-danger fs-14 mb-0" id="mdl_pedido_id">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Cliente:</label>
                        <p class="fw-bold text-dark fs-14 mb-0" id="mdl_cliente">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Proyecto Venta:</label>
                        <p class="fw-bold text-dark fs-14 mb-0" id="mdl_proyecto_id">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Proyecto / Título:</label>
                        <p class="fw-semibold text-dark fs-13 mb-0" id="mdl_titulo_venta">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Código Partida:</label>
                        <p class="fw-semibold text-dark fs-13 mb-0" id="mdl_codigo_partida">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Cantidad Solicitada:</label>
                        <p class="fw-bold text-primary fs-13 mb-0" id="mdl_cantidad">—</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label fs-12 text-muted mb-0">Descripción del Producto / Servicio:</label>
                        <div class="p-0 text-dark fs-13 fw-semibold" id="mdl_descripcion" style="white-space: pre-wrap;">—</div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Fecha Pedido:</label>
                        <p class="fw-semibold text-dark fs-13 mb-0" id="mdl_fecha_pedido">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Fecha Límite de Entrega:</label>
                        <p class="fw-bold fs-14 mb-0" id="mdl_fecha_estimada">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Tiempo Restante para la Entrega:</label>
                        <p class="fw-bold fs-13 mb-0" id="mdl_tiempo_restante">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fs-12 text-muted mb-0">Estatus de Cumplimiento:</label>
                        <div id="mdl_estatus_badge">—</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src="<?= assets(); ?>/vendor/fullcalendar/index.global.min.js"></script>

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>

