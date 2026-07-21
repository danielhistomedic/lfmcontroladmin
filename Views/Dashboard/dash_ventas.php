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
            <div class="sidebar-right-toggle" style="cursor: default;"> </div>
        </div>
    </header>

    <!-- start: page -->
    <div class="row">
        <div class="col-12 loading-panel-showing">

            <!-- Loading Spinner -->
            <div class="loading-panel">
                <div class="bounce-loader">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </div>

            <!-- Opciones de Filtro -->
            <div class="row mb-4">
                <div class="form-group col-12">
                    <div class="border-bottom pb-2">
                        <p class="mb-0 fw-semibold text-primary">
                            <i class="fa-regular fa-filter text-secondary me-2"></i> Rango de Búsqueda Ejecutivo
                        </p>
                    </div>
                </div>

                <div id="opciones-filtro" class="form-group col-12">
                    <form name="formReporteVentas" id="formReporteVentas">
                        <div class="row align-items-end">
                            <div class="form-group col-12 col-sm-3 pt-0">
                                <label class="form-label" for="inputFechaIniPeriodo">Fecha Inicial:</label>
                                <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" name="inputFechaIniPeriodo" id="inputFechaIniPeriodo" placeholder="dd/mm/aaaa" required="" maxlength="10">
                            </div>

                            <div class="form-group col-12 col-sm-3 pt-0">
                                <label class="form-label" for="inputFechaFinPeriodo">Fecha Final:</label>
                                <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" name="inputFechaFinPeriodo" id="inputFechaFinPeriodo" placeholder="dd/mm/aaaa" required="" maxlength="10">
                            </div>

                            <div class="form-group col-12 col-sm-2 pt-0">
                                <button type="submit" class="btn btn-primary w-100 hvr-float-shadow d-flex justify-content-center align-items-center" style="height: 38px;">
                                    <i class="fa-regular fa-sync fa-fw me-1"></i>
                                    <span>Actualizar</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Fin Filtro -->

            <!-- KPI Cards -->
            <div class="row mb-4">
                <div class="form-group col-12 mb-3">
                    <div class="border-bottom pb-2">
                        <p class="mb-0 fw-semibold text-primary">
                            <i class="fa-regular fa-file-chart-pie me-2 text-secondary"></i> Indicadores de Rendimiento (KPIs)
                        </p>
                    </div>
                </div>

                <!-- KPI Card 0: Booking - Comparativo vs Meta Global (USD) -->
                <div class="col-12 mb-4">
                    <div class="card kpi-card shadow-sm border-0" style="border-left: 4px solid #CC4F4F;">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-auto mb-2 mb-md-0">
                                    <div class="kpi-icon bg-danger-lighten text-danger me-md-3" style="background:rgba(204,79,79,.12);">
                                        <i class="fa-solid fa-bullseye-arrow"></i>
                                    </div>
                                </div>
                                <div class="col-12 col-md">
                                    <span class="text-label text-danger d-block mb-2">Booking - Comparativo vs Meta Global (USD &mdash; Año Actual)</span>
                                    <div class="row g-3 text-center text-md-start">
                                        <div class="col-6 col-md-3">
                                            <div class="text-3 text-muted mb-1">Ventas Acumuladas</div>
                                            <div class="fw-bold text-dark" id="lbl_meta_ventas_usd">$0.00 USD</div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="text-3 text-muted mb-1">Meta Anual</div>
                                            <div class="fw-bold text-dark" id="lbl_meta_global_usd">$0.00 USD</div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="text-3 text-muted mb-1">Faltante</div>
                                            <div class="fw-bold" id="lbl_meta_faltante_usd">$0.00 USD</div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="text-3 text-muted mb-1">Cumplimiento</div>
                                            <div class="fw-bold" id="lbl_meta_porcentaje">0.00%</div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Progreso hacia la meta</small>
                                            <small class="fw-semibold" id="lbl_meta_porcentaje_bar">0%</small>
                                        </div>
                                        <div class="progress" style="height: 10px; border-radius: 6px;">
                                            <div class="progress-bar" id="bar_meta_progreso" role="progressbar"
                                                style="width: 0%; border-radius: 6px; background: linear-gradient(90deg, #CC4F4F, #e8836e);"
                                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Card 2: Pipeline Activo -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-primary-lighten text-primary me-3">
                                <i class="fa-solid fa-chart-line-up"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-primary d-block mb-1">Pipeline Activo (Pedidos Cotizados)</span>
                                <div class="text-amount text-dark" id="lbl_pipeline_usd_combined">$0.00 USD</div>
                                <div class="text-amount text-muted text-4 d-none" id="lbl_pipeline_mxn_combined">$0.00 MXN</div>
                                <div class="mt-2 text-3 text-muted">
                                    <span>Pedidos Cotizados: <strong class="text-dark" id="lbl_pipeline_cantidad">0</strong></span>
                                </div>
                                <div class="mt-2 text-end">
                                    <button type="button" class="btn btn-xs btn-outline-primary border-0 fw-semibold text-3 px-2 py-1" id="btn_modal_pedidos_cotizados" style="background-color: rgba(13, 110, 253, 0.08);" title="Ver listado de pedidos cotizados">
                                        <i class="fa-solid fa-list-ul me-1"></i> Ver cotizados <i class="fa-solid fa-chevron-right text-3 ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- KPI Card 1: Total de Ventas (Pedidos Colocados) / Facturadas -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-success-lighten text-success me-3">
                                <i class="fa-solid fa-circle-dollar-to-slot"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-success d-block mb-1">Total de Ventas (Pedidos Colocados)</span>
                                <div class="text-amount text-dark" id="lbl_ganadas_usd_combined">$0.00 USD</div>
                                <div class="text-amount text-muted text-4 d-none" id="lbl_ganadas_mxn_combined">$0.00 MXN</div>
                                <div class="mt-2 text-3 text-muted d-flex justify-content-between">
                                    <span>Pedidos Colocados: <strong class="text-dark" id="lbl_ganadas_cantidad">0</strong></span>
                                    <span>AOV: <strong class="text-dark" id="lbl_ganadas_aov_usd">$0.00 USD</strong></span>
                                </div>
                                <div class="mt-1 text-3 text-muted d-flex justify-content-between align-items-center border-top pt-1">
                                    <span>Efectividad:</span>
                                    <strong class="text-success" id="lbl_efectividad_porcentaje">0.00%</strong>
                                </div>
                                <div class="mt-2 text-end">
                                    <button type="button" class="btn btn-xs btn-outline-success border-0 fw-semibold text-3 px-2 py-1" id="btn_modal_pedidos_colocados" style="background-color: rgba(25, 135, 84, 0.08);" title="Ver listado de pedidos colocados">
                                        <i class="fa-solid fa-list-ul me-1"></i> Ver pedidos <i class="fa-solid fa-chevron-right text-3 ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Card 3: Clientes & Artículos -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-info-lighten text-info me-3">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-info d-block mb-1">Clientes</span>
                                <div class="text-amount text-dark" id="lbl_clientes_activos">0</div>
                                <div class="text-muted text-4">Clientes Activos</div>
                                <div class="mt-2 text-3 text-muted d-none">
                                    <span>Artículos Entregados: <strong class="text-dark" id="lbl_articulos_vendidos">0</strong> uds</span>
                                </div>
                                <div class="mt-2 text-end">
                                    <button type="button" class="btn btn-xs btn-outline-info border-0 fw-semibold text-3 px-2 py-1" id="btn_modal_clientes_activos" style="background-color: rgba(13, 202, 240, 0.08);" title="Ver listado de clientes activos">
                                        <i class="fa-solid fa-users me-1"></i> Ver clientes <i class="fa-solid fa-chevron-right text-3 ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fin KPI Cards -->

            <!-- Visualizaciones Grid -->
            <div class="row">
                
                <!-- Comparativo Ventas vs Pipeline (Full Width) -->
                <div class="col-12">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-chart-column text-primary me-2"></i> Comparativo: Total de Ventas (Colocados) vs Pipeline Activo (USD Consolidado) (Pedidos Cotizados)
                        </div>
                        <div id="chart_ventas_vs_pipeline" style="height: 350px; width: 100%;"></div>
                    </div>
                </div>

                <!-- 1. Tendencia de Ventas (Full Width) -->
                <div class="col-12">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-chart-line text-primary me-2"></i> Tendencia de Ventas del Periodo (USD Consolidado)
                        </div>
                        <div id="chart_ventas_tendencia" style="height: 350px; width: 100%;"></div>
                    </div>
                </div>

                <!-- 2. Vendedores & Clientes (2 Columns) -->
                <div class="col-12 col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-users text-primary me-2"></i> Top Vendedores por Ventas (USD)
                        </div>
                        <div id="chart_ventas_vendedor" style="height: 320px; width: 100%;"></div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-building text-primary me-2"></i> Top Clientes por Facturación (USD)
                        </div>
                        <div id="chart_ventas_cliente" style="height: 320px; width: 100%;"></div>
                    </div>
                </div>

                <!-- Tabla comparativa Ventas vs Meta por Vendedor (Full Width) -->
                <div class="col-12">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-bullseye-arrow text-danger me-2"></i> Ventas vs Meta por Vendedor (USD)
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm table-custom align-middle mb-0" id="table_vendedores_vs_meta">
                                <thead>
                                    <tr>
                                        <th>Vendedor</th>
                                        <th class="text-end">Ventas (USD)</th>
                                        <th class="text-end">Meta Anual (USD)</th>
                                        <th class="text-end">Faltante</th>
                                        <th class="text-center" style="min-width:160px;">Avance</th>
                                    </tr>
                                </thead>
                                <tbody id="tbl_vendedores_vs_meta_body">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                                            Seleccione un rango de fechas para cargar los datos.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- 3. Clasificación & Estatus (2 Columns) -->
                <div class="col-12 col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-tags text-primary me-2"></i> Ventas por Clasificación de Proyecto
                        </div>
                        <div id="chart_ventas_clasificacion" style="height: 320px; width: 100%;"></div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-diagram-project text-primary me-2"></i> Funnel de Estatus de Proyectos (Volumen)
                        </div>
                        <div id="chart_ventas_estatus" style="height: 320px; width: 100%;"></div>
                    </div>
                </div>

            </div>
            <!-- Fin Visualizaciones Grid -->

            <!-- Tabla Top Productos -->
            <div class="row">
                <div class="col-12">
                    <section class="card shadow-sm border-0 mb-5" style="border-radius: 10px; overflow: hidden;">
                        <header class="card-header border-0 bg-white pt-4 pb-2">
                            <h4 class="card-title text-primary fw-semibold mb-0">
                                <i class="fa-regular fa-box-open text-secondary me-2"></i> Top 10 Refacciones y Materiales Más Vendidos
                            </h4>
                        </header>
                        <div class="card-body pt-2">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-custom align-middle mb-0" id="table_productos_mas_vendidos">
                                    <thead>
                                        <tr>
                                            <th width="15%">Código Material</th>
                                            <th width="50%">Descripción</th>
                                            <th width="18%" class="text-center">Cantidad Vendida</th>
                                            <th width="17%" class="text-center">Número de Pedidos</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbl_productos_mas_vendidos_body">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                                                Seleccione un rango de fechas para cargar los datos.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!-- Fin Tabla Top Productos -->

        </div>
    </div>
    <!-- end: page -->

</section>
<!-- FIN CONTENIDO VISTA -->

<!-- Modal Pedidos Colocados -->
<div class="modal fade" id="modalPedidosColocados" tabindex="-1" aria-labelledby="modalPedidosColocadosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-3" style="border-radius: 6px 6px 0 0;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center m-0" id="modalPedidosColocadosLabel">
                    <i class="fa-solid fa-boxes-packing me-2"></i> Listado de Pedidos Colocados
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted text-3"><i class="fa-regular fa-calendar-range me-1"></i> Periodo: <strong id="lbl_modal_pedidos_rango" class="text-dark">--/--/---- al --/--/----</strong></span>
                    <span class="badge bg-success text-white text-3 px-3 py-2" id="lbl_modal_pedidos_count">0 Pedidos</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-custom align-middle w-100 mb-0" id="table_pedidos_colocados">
                        <thead>
                            <tr>
                                <th class="text-center" width="6%">ID</th>
                                <th class="text-center" width="10%">ID Proyecto</th>
                                <th width="11%">Fecha</th>
                                <th width="26%">Cliente</th>
                                <th width="20%">Vendedor</th>
                                <th width="11%">Clasificación</th>
                                <th class="text-end" width="8%">Total</th>
                                <th class="text-end" width="8%">Total (USD)</th>
                            </tr>
                        </thead>
                        <tbody id="tbl_pedidos_colocados_body">
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                                    Cargando pedidos colocados...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pedidos Cotizados -->
<div class="modal fade" id="modalPedidosCotizados" tabindex="-1" aria-labelledby="modalPedidosCotizadosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3" style="border-radius: 6px 6px 0 0;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center m-0" id="modalPedidosCotizadosLabel">
                    <i class="fa-solid fa-chart-line-up me-2"></i> Listado de Pedidos Cotizados (Pipeline Activo)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted text-3"><i class="fa-regular fa-calendar-range me-1"></i> Periodo: <strong id="lbl_modal_cotizados_rango" class="text-dark">--/--/---- al --/--/----</strong></span>
                    <span class="badge bg-primary text-white text-3 px-3 py-2" id="lbl_modal_cotizados_count">0 Pedidos</span>
                </div>
                <div class="table-responsive export-table">
                    <table class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100" id="table_pedidos_cotizados">
                        <thead>
                            <tr>
                                <th class="border-bottom-0 fw-semibold text-center" width="5%">ID</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="9%">ID Proyecto</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="10%">Fecha</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="20%">Cliente</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="16%">Vendedor</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="10%">Clasificación</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="8%">Activo</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="8%">Colocado</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="7%">Total</th>
                                <th class="border-bottom-0 fw-semibold text-center" width="7%">Total (USD)</th>
                            </tr>
                        </thead>
                        <tbody id="tbl_pedidos_cotizados_body">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                                    Cargando pedidos cotizados...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Clientes Activos -->
<div class="modal fade" id="modalClientesActivos" tabindex="-1" aria-labelledby="modalClientesActivosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white py-3" style="border-radius: 6px 6px 0 0;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center m-0" id="modalClientesActivosLabel">
                    <i class="fa-solid fa-users me-2"></i> Listado de Clientes Activos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted text-3"><i class="fa-regular fa-calendar-range me-1"></i> Periodo: <strong id="lbl_modal_clientes_rango" class="text-dark">--/--/---- al --/--/----</strong></span>
                    <span class="badge bg-info text-white text-3 px-3 py-2" id="lbl_modal_clientes_count">0 Clientes</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-custom align-middle w-100 mb-0" id="table_clientes_activos">
                        <thead>
                            <tr>
                                <th class="text-center" width="6%">ID</th>
                                <th width="34%">Cliente / Razón Social</th>
                                <th class="text-center" width="15%">RFC</th>
                                <th class="text-center" width="15%">Pedidos Colocados</th>
                                <th class="text-end" width="15%">Monto Total (MXN)</th>
                                <th class="text-end" width="15%">Monto Total (USD)</th>
                            </tr>
                        </thead>
                        <tbody id="tbl_clientes_activos_body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                                    Cargando lista de clientes...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<div id="loadModalPermisos"></div>

<!-- ECHART JS -->
<script src="<?= assets(); ?>/vendor/echarts/dist/echarts.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/echarts/i18n/langES.js?v=<?= version(); ?>"></script>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>