<?php /* View: Seguimiento - Partidas Pendiente de Cotizar */ ?>
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
                <li><span>Partidas Pendiente de Cotizar</span></li>
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
                    <label class="form-label" for="filtro_proveedor">Proveedor:</label>
                    <select class="form-control select2 custom-select" id="filtro_proveedor" name="filtro_proveedor" style="width: 100%;">
                        <option value="">Todos los proveedores</option>
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-3 pt-0">
                    <label class="form-label" for="filtro_proyecto">Proyecto de Venta:</label>
                    <select class="form-control select2 custom-select" id="filtro_proyecto" name="filtro_proyecto" style="width: 100%;">
                        <option value="">Todos los proyectos</option>
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-2 pt-0">
                    <label class="form-label" for="filtro_solicitud">Folio Solicitud / Cotización:</label>
                    <input type="text" class="form-control" autocomplete="off" name="filtro_solicitud" id="filtro_solicitud" placeholder="Buscar por SC...">
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-2 pt-0">
                    <label class="form-label" for="filtro_antiguedad">Semáforo de Tiempo:</label>
                    <select class="form-control form-select selectpicker" name="filtro_antiguedad" id="filtro_antiguedad">
                        <option value="">Todos los Tiempos</option>
                        <option value="recientes">🟢 En tiempo (≤ 2 días)</option>
                        <option value="espera">🟡 En espera (3 a 5 días)</option>
                        <option value="demoradas">🔴 Demoradas (> 5 días)</option>
                        <option value="criticas">⚫ Críticas (> 10 días)</option>
                    </select>
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-2 pt-0">
                    <label class="form-label" for="filtro_fecha_ini">Fecha Inicio:</label>
                    <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" 
                           name="filtro_fecha_ini" id="filtro_fecha_ini" placeholder="dd/mm/aaaa" required="" maxlength="10">
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-2 pt-0">
                    <label class="form-label" for="filtro_fecha_fin">Fecha Fin:</label>
                    <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" 
                           name="filtro_fecha_fin" id="filtro_fecha_fin" placeholder="dd/mm/aaaa" required="" maxlength="10">
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-8 pt-0">
                    <label class="form-label" for="filtro_busqueda">Búsqueda Rápida (Partida, Descripción, Material, Cliente):</label>
                    <input type="text" class="form-control" autocomplete="off" name="filtro_busqueda" id="filtro_busqueda" placeholder="Buscar por código partida, descripción, cliente...">
                </div>

                <div class="form-group col-12 col-sm-6 col-lg-2 pt-0 d-flex align-items-end gap-2">
                    <button type="button" class="btn btn-primary w-100 hvr-float-shadow d-flex justify-content-center align-items-center" id="btnFiltrar" style="height: 38px;">
                        <i class="fa-regular fa-magnifying-glass me-1"></i> Buscar
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100 hvr-float-shadow d-flex justify-content-center align-items-center" id="btnLimpiar" style="height: 38px;">
                        <i class="fa-regular fa-rotate-left me-1"></i> Limpiar
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

                <!-- KPI 1: Partidas Pendientes -->
                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body border d-flex align-items-center">
                            <div class="kpi-icon bg-primary-lighten text-primary me-3">
                                <i class="fa-solid fa-clipboard-question"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-primary d-block mb-1">Partidas Pendientes</span>
                                <div class="text-amount text-dark" id="kpi_total_partidas">0</div>
                                <div class="text-muted text-2">Sin precio de proveedor</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI 2: Proveedores -->
                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body border d-flex align-items-center">
                            <div class="kpi-icon bg-info-lighten text-info me-3">
                                <i class="fa-solid fa-truck-field"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-info d-block mb-1">Proveedores en Espera</span>
                                <div class="text-amount text-dark" id="kpi_total_proveedores">0</div>
                                <div class="text-muted text-2">Con cotizaciones activas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI 3: Proyectos de Venta -->
                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body border d-flex align-items-center">
                            <div class="kpi-icon bg-success-lighten text-success me-3">
                                <i class="fa-solid fa-diagram-project"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-success d-block mb-1">Proyectos Afectados</span>
                                <div class="text-amount text-dark" id="kpi_total_proyectos">0</div>
                                <div class="text-muted text-2" id="kpi_total_solicitudes_txt">0 Solicitudes SC</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI 4: Partidas Demoradas (> 5 días) -->
                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body border d-flex align-items-center">
                            <div class="kpi-icon bg-danger-lighten text-danger me-3">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-danger d-block mb-1">Demoradas (&gt; 5 días)</span>
                                <div class="text-amount text-dark" id="kpi_total_demoradas">0</div>
                                <div class="text-muted text-2" id="kpi_promedio_dias">Promedio: 0 días</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- ========== FIN KPI CARDS ========== -->

            <!-- ========== LISTA DE PARTIDAS PENDIENTES ========== -->
            <div class="chart-container border mb-4" id="panel_lista_registros">

                <!-- Subtítulo -->
                <div class="chart-title d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <i class="fa-regular fa-bars-staggered text-primary me-2"></i> Lista de Partidas Pendientes de Cotizar
                    </div>
                </div>

                <!-- Tabla de Partidas -->
                <div class="table-responsive export-table">
                    <table id="tablePartidasPendientes"
                           class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                        <thead>
                            <tr>
                                <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
                                <th class="border-bottom-0 fw-semibold text-center">Tiempo Sin Cotizar</th>
                                <th class="border-bottom-0 fw-semibold text-center">Solicitud Cotización</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto Venta</th>
                                <th class="border-bottom-0 fw-semibold text-center">Fecha Solicitud</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proveedor</th>
                                <th class="border-bottom-0 fw-semibold text-center">Cliente</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto / Título</th>
                                <th class="border-bottom-0 fw-semibold text-center">Código Partida</th>
                                <th class="border-bottom-0 fw-semibold text-center">Descripción de Partida</th>
                                <th class="border-bottom-0 fw-semibold text-center">Cantidad</th>
                                <th class="border-bottom-0 fw-semibold text-center">Estatus Cotización</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- Fin Tabla de Partidas -->

            </div>
            <!-- ========== FIN LISTA ========== -->

        </div>

    </div>
    <!-- end: page -->

</section>

<!-- =========================================================
     MODAL: DETALLE DE SOLICITUD DE COTIZACIÓN Y PARTIDAS
========================================================= -->
<div class="modal fade" id="modalDetalleSolicitud" tabindex="-1" aria-labelledby="modalDetalleSolicitudLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-regular fa-file-invoice-dollar fs-18"></i>
                    <h5 class="modal-title fw-bold mb-0 text-white" id="modalDetalleSolicitudLabel">
                        Detalle de Solicitud de Cotización
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">

                <!-- Bloque Cabecera -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Folio Solicitud</span>
                                <span class="fw-bold fs-14 text-primary" id="mdl_folio_solicitud">—</span>
                            </div>
                            <div class="col-12 col-md-3">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Fecha Envío</span>
                                <span class="fw-semibold fs-13 text-dark" id="mdl_fecha_solicitud">—</span>
                            </div>
                            <div class="col-12 col-md-3">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Tiempo Transcurrido</span>
                                <div id="mdl_tiempo_transcurrido">—</div>
                            </div>
                            <div class="col-12 col-md-3">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Proyecto de Venta</span>
                                <span class="fw-bold fs-13 text-dark" id="mdl_proyecto_id">—</span>
                            </div>

                            <div class="col-12 col-md-4 border-top pt-2">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Proveedor</span>
                                <span class="fw-bold fs-13 text-dark" id="mdl_proveedor_nombre">—</span>
                            </div>
                            <div class="col-12 col-md-4 border-top pt-2">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Contacto / Correo</span>
                                <span class="fs-12 text-dark" id="mdl_proveedor_contacto">—</span>
                            </div>
                            <div class="col-12 col-md-4 border-top pt-2">
                                <span class="text-muted fs-11 text-uppercase fw-semibold d-block">Cliente del Proyecto</span>
                                <span class="fw-semibold fs-12 text-dark" id="mdl_cliente_nombre">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bloque Resumen de Partidas -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="fw-bold text-primary fs-13">
                            <i class="fa-regular fa-list-check me-1"></i> Desglose de Partidas de la Solicitud
                        </span>
                        <div class="d-flex gap-2" id="mdl_partidas_resumen_badge">
                            <!-- Badges generados por JS -->
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0 align-middle fs-12" id="tableMdlPartidas">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th style="width: 120px;">Código Partida</th>
                                        <th>Descripción</th>
                                        <th class="text-center" style="width: 90px;">Cantidad</th>
                                        <th class="text-end" style="width: 120px;">Precio Cotizado</th>
                                        <th class="text-end" style="width: 120px;">Importe</th>
                                        <th class="text-center" style="width: 120px;">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyMdlPartidas">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Bloque Adjuntos de Cotización si existen -->
                <div class="card border-0 shadow-sm d-none" id="cardMdlAdjuntos">
                    <div class="card-header bg-white py-2 border-bottom">
                        <span class="fw-bold text-primary fs-13">
                            <i class="fa-regular fa-paperclip me-1"></i> Archivos Adjuntos
                        </span>
                    </div>
                    <div class="card-body p-3" id="containerMdlAdjuntos">
                    </div>
                </div>

            </div>

            <div class="modal-footer bg-white py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                    <i class="fa-regular fa-xmark me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>
