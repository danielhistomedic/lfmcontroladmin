<?php /* View: Seguimiento - Proyecto de Venta */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Custom CSS for Checklist & Timeline -->
<style>
    .timeline-checklist {
        position: relative;
        padding-left: 2rem;
        border-left: 3px solid #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.75rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-badge {
        position: absolute;
        left: -2.85rem;
        top: 0.2rem;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        color: #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        z-index: 2;
    }
    .timeline-badge.badge-completed {
        background: linear-gradient(135deg, #198754, #20c997);
    }
    .timeline-badge.badge-pending {
        background: linear-gradient(135deg, #6c757d, #adb5bd);
    }
    .timeline-badge.badge-canceled {
        background: linear-gradient(135deg, #dc3545, #f87171);
    }
    .timeline-badge.badge-active {
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
    }
    .step-card {
        border-radius: 10px;
        transition: all 0.25s ease-in-out;
        border: 1px solid rgba(0,0,0,0.08);
    }
    .step-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.06) !important;
    }
    .step-card.completed {
        border-left: 4px solid #198754;
        background-color: #f8fffb;
    }
    .step-card.pending {
        border-left: 4px solid #adb5bd;
        background-color: #ffffff;
    }
    .step-card.canceled {
        border-left: 4px solid #dc3545;
        background-color: #fff5f5;
    }
    .chart-container {
        background: #ffffff;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
        margin-bottom: 2rem;
    }
    .chart-title {
        font-size: var(--font-section-title, 1.1rem);
        font-weight: var(--font-weight-semibold, 600);
        color: #333333;
        margin-bottom: 1.2rem;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 0.75rem;
    }
    .table-custom th {
        font-size: var(--font-table-header, 0.85rem);
        font-weight: var(--font-weight-semibold, 600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #fcfcfc;
    }
    tr.selected-row {
        background-color: rgba(13, 110, 253, 0.08) !important;
    }
    .nav-tabs-proyecto {
        border-bottom: 2px solid #e9ecef;
    }
    .nav-tabs-proyecto .nav-link {
        font-weight: 600;
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.75rem 1.25rem;
        font-size: 0.95rem;
        transition: all 0.2s ease-in-out;
        background: transparent;
    }
    .nav-tabs-proyecto .nav-link:hover {
        color: #0d6efd;
        border-bottom-color: rgba(13, 110, 253, 0.3);
    }
    .nav-tabs-proyecto .nav-link.active {
        color: #0d6efd;
        background-color: transparent;
        border-bottom-color: #0d6efd;
        font-weight: 700;
    }

    /* Correcciones de legibilidad aisladas: mismas tarjetas, tamaños y distribución. */
    .proyecto-venta-page { --pv-surface: #fff; --pv-soft: #f8fafc; --pv-text: #1e293b; --pv-muted: #475569; --pv-border: #cbd5e1; --pv-select: #e8f2fa; }
    .proyecto-venta-page .text-muted:not(:where(.badge, .badge *, .btn, .btn *)) { color: var(--pv-muted) !important; }
    .proyecto-venta-page .text-secondary:not(:where(.badge, .badge *, .btn, .btn *, i)) { color: var(--pv-muted) !important; }
    .proyecto-venta-page .text-primary:not(:where(.badge, .badge *, .btn, .btn *, i)) { color: #075985 !important; }
    html.dark .proyecto-venta-page .text-primary:not(:where(.badge, .badge *, .btn, .btn *, i)) { color: #8bd5ff !important; }
    .proyecto-venta-page #tableProyectosVenta tbody tr.selected-row > td { background-color: var(--pv-select) !important; }
    .proyecto-venta-page .nav-tabs-proyecto .nav-link:focus-visible { outline: 2px solid #80bdff; outline-offset: 2px; }
    html.dark .proyecto-venta-page { --pv-surface: #1d2735; --pv-soft: #253346; --pv-text: #edf2f7; --pv-muted: #cbd5e1; --pv-border: #53657d; --pv-select: #304860; background-color: var(--pv-surface) !important; background-image: none !important; }
    html.dark .proyecto-venta-page :is(#cardResumenProyecto .card-body, #cardChecklistProceso .card-body, #panelPlaceholder .card, .step-card, #timeline_seguimientos .card, #timeline_seguimientos .card-body, #timeline_adjuntos .card, #timeline_adjuntos .card-body) { background-color: var(--pv-surface) !important; color: var(--pv-text); border-color: var(--pv-border) !important; }
    html.dark .proyecto-venta-page :is(#cardChecklistProceso .card-header, #timeline_seguimientos .card-header, #timeline_adjuntos .card-header, #cardResumenProyecto .bg-light, #timeline_adjuntos .bg-light) { background-color: var(--pv-soft) !important; border-color: var(--pv-border) !important; }
    html.dark .proyecto-venta-page :is(#cardResumenProyecto, #cardChecklistProceso, #panelPlaceholder, #timeline_seguimientos, #timeline_adjuntos) .text-muted { color: var(--pv-muted) !important; }
    html.dark .proyecto-venta-page :is(#cardResumenProyecto, #cardChecklistProceso, #panelPlaceholder, #timeline_seguimientos, #timeline_adjuntos) :is(.text-dark, .text-secondary):not(:where(.badge, .btn, .badge *, .btn *)) { color: var(--pv-text) !important; }
    html.dark .proyecto-venta-page :is(.step-card, #timeline_seguimientos, #timeline_adjuntos) .text-primary:not(:where(.badge, .btn, .badge *, .btn *)) { color: #8bd5ff !important; }
    html.dark .proyecto-venta-page #lblMontoTotal { color: #75d3a3 !important; }
    html.dark .proyecto-venta-page .step-card .text-danger:not(:where(.badge, .badge *)) { color: #ff8792 !important; }
    html.dark .proyecto-venta-page .step-card :is(table, thead, tbody, tr, th, td) { background-color: var(--pv-surface) !important; color: var(--pv-text); border-color: var(--pv-border) !important; }
    html.dark .proyecto-venta-page .step-card thead th { background-color: var(--pv-soft) !important; }
    html.dark .proyecto-venta-page #containerChecklistItems .step-card.completed { border-left-color: #198754 !important; }
    html.dark .proyecto-venta-page #containerChecklistItems .step-card.canceled { border-left-color: #dc3545 !important; }
    html.dark .proyecto-venta-page #containerChecklistItems .step-card.pending { border-left-color: #adb5bd !important; }
    html.dark .proyecto-venta-page .timeline-checklist { border-color: var(--pv-border); }
    html.dark .proyecto-venta-page .badge:is(.bg-light, .bg-white, .bg-warning, .bg-info) { color: #1e293b !important; }
    html.dark .proyecto-venta-page .nav-tabs-proyecto .nav-item { background: transparent !important; }
    html.dark .proyecto-venta-page .nav-tabs-proyecto { border-color: var(--pv-border); }
    html.dark .proyecto-venta-page .nav-tabs-proyecto .nav-link { background: transparent !important; color: var(--pv-muted) !important; }
    html.dark .proyecto-venta-page .nav-tabs-proyecto .nav-link.active { color: #8bd5ff !important; border-bottom-color: #8bd5ff; }
    html.dark .proyecto-venta-page #timeline_adjuntos .btn-outline-warning { color: var(--pv-text) !important; }
    html.dark .proyecto-venta-page #timeline_adjuntos .btn-outline-warning:is(:hover, :active) { color: #1e293b !important; }
    @media (prefers-reduced-motion: reduce) {
        .proyecto-venta-page :is(.step-card, .nav-tabs-proyecto .nav-link) { transition: none; }
    }
</style>
<!-- Presentación propia: esquema visual compartido con Órdenes de Compra Clientes. -->
<link rel="stylesheet" href="<?= assets(); ?>/app/css/seguimiento_proyecto_venta.css?v=<?= filemtime(__DIR__ . '/../../Assets/app/css/seguimiento_proyecto_venta.css'); ?>">


<!-- Header Admin 02 -->
<?php require_once("Template/header_02.php"); ?>

<!-- CONTENIDO DE VISTA -->
<section role="main" class="content-body fondo-general proyecto-venta-page">

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
                <li><span>Proyecto de Venta</span></li>
            </ol>
            <div class="sidebar-right-toggle" style="cursor: default;"></div>
        </div>
    </header>

    <!-- START: PAGE CONTENT -->
    <div class="row">

        <div class="col-12 loading-panel-showing">

            <div class="loading-panel">
                <div class="bounce-loader">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </div>

            <!-- Búsqueda general y fechas completas; se combinan sin alterar el alcance autorizado. -->
            <section class="mb-4 report-filters pv-filter-shell" id="panel_filtros" aria-labelledby="filtro_proyectos_titulo">
                <div class="pv-filter-main">
                    <div class="pv-filter-brand">
                        <span class="pv-filter-icon" aria-hidden="true"><i class="fa-sharp fa-light fa-diagram-project"></i></span>
                        <div><h3 id="filtro_proyectos_titulo">Proyectos de venta</h3><p>Seguimiento · Evaluación del proceso</p></div>
                    </div>                    <div class="form-group pt-0 mb-0 pv-filter-search">
                        <label class="form-label" for="txtProyectoId">Buscar proyecto</label>
                        <input type="text" class="form-control" id="txtProyectoId" name="proyecto_id" placeholder="Proyecto, cliente, vendedor, orden o factura" aria-describedby="ayuda_buscar_proyecto" autocomplete="off">
                    </div>
                    <div class="form-group pt-0 mb-0 "><label class="form-label" for="filtro_fecha_ini">Fecha inicio</label><input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" 
                           name="filtro_fecha_ini" id="filtro_fecha_ini" placeholder="dd/mm/aaaa" maxlength="10"></div><div class="form-group pt-0 mb-0 "><label class="form-label" for="filtro_fecha_fin">Fecha fin</label><input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" 
                           name="filtro_fecha_fin" id="filtro_fecha_fin" placeholder="dd/mm/aaaa" maxlength="10"></div>
                    <div class="pv-filter-action"><button type="button" class="btn btn-primary w-100 lfm-btn-action lfm-btn-action--primary" id="btnFiltrar"><i class="fa-regular fa-arrows-rotate me-1" aria-hidden="true"></i>Actualizar</button></div>
                </div>
                <div class="pv-filter-footer">
                    <span><i class="fa-regular fa-calendar-check me-1" aria-hidden="true"></i><span id="ayuda_buscar_proyecto">Incluye órdenes de compra finalizadas y folios de facturas activas y timbradas, con o sin serie. La búsqueda y las fechas se aplican en conjunto.</span></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary lfm-btn-action lfm-btn-action--quiet" id="btnLimpiarFiltro" onclick="fntLimpiarFiltro()" title="Restablecer filtros"><i class="fa-regular fa-filter-circle-xmark me-1" aria-hidden="true"></i>Restablecer filtros</button>
                </div>
            </section>
            <!-- ========== FIN FILTROS ========== -->

            <!-- Resumen del conjunto autorizado: no suma monedas ni repite consultas. -->
            <section class="row g-3 mb-4" id="pv_panel_kpis" aria-labelledby="pv_resumen_titulo" aria-busy="true">
                <div class="col-12 d-flex flex-wrap justify-content-between align-items-end gap-2">
                    <div>
                        <p class="executive-eyebrow mb-1">Control comercial</p>
                        <h3 class="executive-heading mb-0" id="pv_resumen_titulo">Resumen ejecutivo</h3>
                    </div>
                    <span class="executive-scope" id="pv_kpi_total" role="status" aria-live="polite">Consultando proyectos…</span>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="executive-kpi executive-kpi-open h-100" aria-labelledby="pv_kpi_activos_titulo">
                        <button type="button" class="executive-kpi-filter" data-kpi-proyecto="activos" aria-labelledby="pv_kpi_activos_titulo" aria-describedby="pv_kpi_activos" aria-controls="tableProyectosVenta" aria-pressed="false" disabled></button>
                        <div class="executive-kpi-top"><h4 id="pv_kpi_activos_titulo">Proyectos activos</h4><i class="fa-regular fa-diagram-project" aria-hidden="true"></i></div>
                        <div class="executive-kpi-value" id="pv_kpi_activos">—</div>
                        <p class="executive-kpi-caption mb-0">Cartera activa, excluye proyectos declinados</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="executive-kpi executive-kpi-soon h-100" aria-labelledby="pv_kpi_por_colocar_titulo">
                        <button type="button" class="executive-kpi-filter" data-kpi-proyecto="por_colocar" aria-labelledby="pv_kpi_por_colocar_titulo" aria-describedby="pv_kpi_por_colocar" aria-controls="tableProyectosVenta" aria-pressed="false" disabled></button>
                        <div class="executive-kpi-top"><h4 id="pv_kpi_por_colocar_titulo">Por colocar</h4><i class="fa-regular fa-cart-shopping" aria-hidden="true"></i></div>
                        <div class="executive-kpi-value" id="pv_kpi_por_colocar">—</div>
                        <p class="executive-kpi-caption mb-0">Activos en Pedido Cotizado (cotización cliente elaborada)</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="executive-kpi executive-kpi-supplier h-100" aria-labelledby="pv_kpi_por_facturar_titulo">
                        <button type="button" class="executive-kpi-filter" data-kpi-proyecto="por_facturar" aria-labelledby="pv_kpi_por_facturar_titulo" aria-describedby="pv_kpi_por_facturar" aria-controls="tableProyectosVenta" aria-pressed="false" disabled></button>
                        <div class="executive-kpi-top"><h4 id="pv_kpi_por_facturar_titulo">Por facturar</h4><i class="fa-regular fa-file-invoice-dollar" aria-hidden="true"></i></div>
                        <div class="executive-kpi-value" id="pv_kpi_por_facturar">—</div>
                        <p class="executive-kpi-caption mb-0">Activos con OCC y sin factura activa y timbrada</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="executive-kpi executive-kpi-late h-100" aria-labelledby="pv_kpi_declinados_titulo">
                        <button type="button" class="executive-kpi-filter" data-kpi-proyecto="declinados" aria-labelledby="pv_kpi_declinados_titulo" aria-describedby="pv_kpi_declinados" aria-controls="tableProyectosVenta" aria-pressed="false" disabled></button>
                        <div class="executive-kpi-top"><h4 id="pv_kpi_declinados_titulo">Declinados</h4><i class="fa-regular fa-circle-xmark" aria-hidden="true"></i></div>
                        <div class="executive-kpi-value" id="pv_kpi_declinados">—</div>
                        <p class="executive-kpi-caption mb-0">Proyectos marcados como declinados</p>
                    </article>
                </div>
                <div class="col-12"><p class="executive-footnote mb-0" id="pv_kpi_nota">Consultando los resultados del período y la búsqueda.</p></div>
            </section>

            <!-- ========== PRIMERA SECCIÓN: LISTA DE PROYECTOS DE VENTA (DATATABLE) ========== -->
            <div class="chart-container border mb-4" id="panel_lista_proyectos">

                <div class="chart-title d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa-regular fa-bars-staggered text-primary me-2"></i> Lista de Proyectos de Venta
                    </div>
                    <small class="text-muted fw-normal fs-12">Haga clic en un registro de la lista para ver su detalle de seguimiento</small>
                </div>

                <div class="pv-executive-filter-notice d-none mb-3" id="pv_aviso_filtro_ejecutivo" role="status">
                    <span id="pv_texto_filtro_ejecutivo"></span>
                    <button type="button" class="btn btn-outline-secondary lfm-btn-action lfm-btn-action--quiet" id="pv_quitar_filtro_ejecutivo">Quitar filtro ejecutivo</button>
                </div>

                <!-- Tabla de Proyectos de Venta -->
                <div class="table-responsive export-table">
                    <table id="tableProyectosVenta"
                           class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                        <thead>
                            <tr>
                                <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
                                <th class="border-bottom-0 fw-semibold text-center">Estatus</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto Venta</th>
                                <th class="border-bottom-0 fw-semibold text-center">Fecha</th>
                                <th class="border-bottom-0 fw-semibold text-center">Número OCC</th>
                                <th class="border-bottom-0 fw-semibold text-center">Folio OCP</th>
                                <th class="border-bottom-0 fw-semibold text-center">Folios de facturas</th>
                                <th class="border-bottom-0 fw-semibold text-center">Cliente</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto / Título</th>
                                <th class="border-bottom-0 fw-semibold text-center">Vendedor</th>
                                <th class="border-bottom-0 fw-semibold text-center">Monto Total</th>
                                <th class="border-bottom-0 fw-semibold text-center">Etapa del proyecto</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
            <!-- ========== FIN PRIMERA SECCIÓN ========== -->

            <!-- ========== SEGUNDA SECCIÓN: TABS DE DETALLE DEL PROYECTO SELECCIONADO ========== -->
            <div id="panel_detalle_proyecto" class="row">

                <!-- NAVEGACIÓN DE TABS -->
                <div class="col-12 mb-3">
                    <ul class="nav nav-tabs nav-tabs-proyecto border-bottom pb-0" id="tabProyectoVenta" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="evaluacion-calidad-tab" data-bs-toggle="tab" data-bs-target="#tab-evaluacion-calidad" data-toggle="tab" data-target="#tab-evaluacion-calidad" type="button" role="tab" aria-controls="tab-evaluacion-calidad" aria-selected="true">
                                <i class="fa-sharp fa-light fa-diagram-project me-2 text-primary"></i>Evaluación de Calidad
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="detalle-seguimiento-tab" data-bs-toggle="tab" data-bs-target="#tab-detalle-seguimiento" data-toggle="tab" data-target="#tab-detalle-seguimiento" type="button" role="tab" aria-controls="tab-detalle-seguimiento" aria-selected="false">
                                <i class="fa-regular fa-list-timeline me-2 text-info"></i>Detalle de Seguimiento
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="adjuntos-proyecto-tab" data-bs-toggle="tab" data-bs-target="#tab-adjuntos-proyecto" data-toggle="tab" data-target="#tab-adjuntos-proyecto" type="button" role="tab" aria-controls="tab-adjuntos-proyecto" aria-selected="false">
                                <i class="fa-regular fa-paperclip me-2 text-warning"></i>Adjuntos del Proyecto
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="partidas-proyecto-tab" data-bs-toggle="tab" data-bs-target="#tab-partidas-proyecto" data-toggle="tab" data-target="#tab-partidas-proyecto" type="button" role="tab" aria-controls="tab-partidas-proyecto" aria-selected="false">
                                <i class="fa-regular fa-boxes-stacked me-2 text-success"></i>Partidas
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- CONTENIDO DE LOS TABS -->
                <div class="col-12">
                    <div class="tab-content border-0 p-0 bg-transparent" id="tabContentProyectoVenta">

                        <!-- TAB 1: Evaluación de Calidad (Contenido actual) -->
                        <div class="tab-pane fade show active" id="tab-evaluacion-calidad" role="tabpanel" aria-labelledby="evaluacion-calidad-tab">

                            <!-- SELECTOR DE COINCIDENCIAS MULTIPLES -->
                            <div class="mb-4 d-none" id="containerCoincidencias">
                                <div class="alert alert-info shadow-sm border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 rounded-3">
                                    <div class="mb-2 mb-md-0">
                                        <i class="fa-regular fa-list-check me-2 fs-5"></i>
                                        <strong>Se encontraron múltiples proyectos coincidentes.</strong> Por favor seleccione uno:
                                    </div>
                                    <div class="col-md-5">
                                        <select id="selectProyectosCoincidentes" class="form-select form-select-sm fw-semibold" onchange="fntSeleccionarProyecto(this.value)">
                                            <!-- Opciones dinámicas -->
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- STATE: CARGANDO -->
                            <div class="d-none" id="panelLoading">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <h5 class="text-muted mt-3 fw-semibold">Consultando información del proyecto...</h5>
                                </div>
                            </div>

                            <!-- RESUMEN DEL PROYECTO SELECCIONADO -->
                            <div class="mb-4 d-none" id="cardResumenProyecto">
                                <section class="card shadow-sm border-0 overflow-hidden">
                                    <div class="card-header bg-dark text-white p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary fs-14 px-3 py-2" id="lblProyectoId">—</span>
                                            <h4 class="m-0 fw-bold text-white fs-16" id="lblTituloProyecto">—</h4>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-3">
                                            <div>
                                                <span class="form-label mb-0 me-1">Estatus:</span>
                                                <span id="lblEstadoActivoProyecto" class="badge bg-secondary">—</span>
                                            </div>
                                            <div>
                                                <span class="form-label mb-0 me-1">Etapa actual:</span>
                                                <span id="lblEstatusBadge" class="badge bg-warning text-dark fs-12 px-3 py-2">—</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-4 bg-light-subtle">
                                        <div class="row g-3">
                                            <div class="col-md-6 col-lg-3">
                                                <div class="p-3 bg-white rounded border h-100 shadow-2xs">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Cliente</small>
                                                    <span class="fw-bold text-dark fs-14 d-block text-truncate" id="lblCliente" title="">—</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <div class="p-3 bg-white rounded border h-100 shadow-2xs">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Vendedor Asignado</small>
                                                    <span class="fw-semibold text-dark fs-14 d-block text-truncate" id="lblVendedor" title="">—</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <div class="p-3 bg-white rounded border h-100 shadow-2xs">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Fecha de Proyecto</small>
                                                    <span class="fw-semibold text-dark fs-14 d-block" id="lblFecha">—</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <div class="p-3 bg-white rounded border h-100 shadow-2xs">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Monto Oportunidad / Venta</small>
                                                    <span class="fw-bold text-success fs-15 d-block" id="lblMontoTotal">—</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="p-3 bg-white rounded border h-100 shadow-2xs">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Números OCC</small>
                                                    <div class="fw-semibold text-dark fs-14 text-break" id="lblOrdenesCliente">—</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="p-3 bg-white rounded border h-100 shadow-2xs">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Folios OCP</small>
                                                    <div class="fw-semibold text-dark fs-14 text-break" id="lblFoliosProveedor">—</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="p-3 bg-white rounded border h-100 shadow-2xs">
                                                    <small class="text-muted d-block text-uppercase fw-semibold fs-11">Facturas activas y timbradas</small>
                                                    <div class="fw-semibold text-dark fs-14 text-break" id="lblFoliosFacturas">—</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- CHECKLIST DEL PROCESO (ESTATUS ID 1 AL 7) -->
                            <div class="d-none" id="cardChecklistProceso">
                                <section class="card card-featured shadow-sm">
                                    <header class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div>
                                            <h3 class="card-title m-0 text-dark fw-bold fs-16">
                                                <i class="fa-sharp fa-regular fa-list-check text-primary me-2"></i>Checklist de Evaluación del Proceso
                                            </h3>
                                            <small class="text-muted">Evaluación de las etapas principales del proyecto de venta</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary fs-12 px-3 py-2" id="lblProgresoPorcentaje">0% Completado</span>
                                        </div>
                                    </header>

                                    <div class="card-body p-4">
                                        <!-- BARRA DE PROGRESO -->
                                        <div class="progress mb-4" style="height: 12px; border-radius: 6px;">
                                            <div id="barProgresoChecklist" 
                                                 class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                                 role="progressbar" 
                                                 style="width: 0%;" 
                                                 aria-valuenow="0" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>

                                        <!-- TIMELINE CONTENEDOR DEL CHECKLIST -->
                                        <div class="timeline-checklist mt-4" id="containerChecklistItems">
                                            <!-- Generado dinámicamente mediante JS -->
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- PLACEHOLDER / SIN SELECCIÓN -->
                            <div id="panelPlaceholder">
                                <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
                                    <div class="py-4">
                                        <i class="fa-sharp fa-light fa-diagram-project text-muted display-4 mb-3"></i>
                                        <h4 class="fw-bold text-dark mb-2">Consulta de Proyecto de Venta</h4>
                                        <p class="text-muted max-w-600 mx-auto fs-14 mb-0">
                                            Seleccione un proyecto de venta del listado superior o utilice los filtros para visualizar la información detallada y la evaluación en el checklist del proceso.
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- FIN TAB 1 -->

                        <!-- TAB 2: Detalle de Seguimiento (Historial de Seguimientos) -->
                        <div class="tab-pane fade" id="tab-detalle-seguimiento" role="tabpanel" aria-labelledby="detalle-seguimiento-tab">
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
                                        <p class="text-muted fs-13 mb-0">Este proyecto de venta no tiene seguimientos registrados.</p>
                                    </div>

                                    <!-- Timeline de seguimientos -->
                                    <div id="timeline_seguimientos" class="p-3">
                                        <!-- Se llena dinámicamente con JS -->
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB 2 -->

                        <!-- TAB 3: Adjuntos del Proyecto -->
                        <div class="tab-pane fade" id="tab-adjuntos-proyecto" role="tabpanel" aria-labelledby="adjuntos-proyecto-tab">
                            <div class="card border p-0 shadow-sm rounded-3">
                                <div class="card-header bg-header-card border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                                    <p class="mb-0 fw-600 text-warning-emphasis fs-13">
                                        <i class="fa-regular fa-paperclip me-1 text-warning"></i> Archivos Adjuntos del Proyecto
                                        <span class="badge bg-warning text-dark ms-2" id="badge_total_adjuntos">0</span>
                                    </p>
                                </div>
                                <div class="card-body p-0">

                                    <!-- Loading adjuntos -->
                                    <div id="loading_adjuntos" class="text-center p-4 d-none">
                                        <div class="spinner-border spinner-border-sm text-warning" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <span class="ms-2 fs-13 text-muted">Cargando archivos adjuntos...</span>
                                    </div>

                                    <!-- Sin adjuntos -->
                                    <div id="sin_adjuntos" class="text-center p-4 d-none">
                                        <i class="fa-light fa-paperclip fa-2x text-muted mb-2"></i>
                                        <p class="text-muted fs-13 mb-0">Este proyecto de venta no tiene archivos adjuntos registrados en sus etapas.</p>
                                    </div>

                                    <!-- Timeline de adjuntos -->
                                    <div id="timeline_adjuntos" class="p-3">
                                        <!-- Se llena dinámicamente con JS -->
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB 3 -->

                        <!-- TAB 4: Partidas del Proyecto -->
                        <div class="tab-pane fade" id="tab-partidas-proyecto" role="tabpanel" aria-labelledby="partidas-proyecto-tab">
                            <div class="card border p-0 shadow-sm rounded-3">
                                <div class="card-header bg-header-card border-bottom py-2.5 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <p class="mb-0 fw-600 text-success fs-13">
                                            <i class="fa-regular fa-boxes-stacked me-1 text-success"></i> Partidas del Proyecto
                                            <span class="badge bg-success ms-2" id="badge_total_partidas">0</span>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="fs-12 text-muted me-1">Origen de Datos:</span>
                                        <span id="lblOrigenPartidasBadge" class="badge bg-light text-dark border fs-12 px-2.5 py-1">Sin partidas</span>
                                    </div>
                                </div>
                                <div class="card-body p-3">

                                    <!-- Tabla de Partidas -->
                                    <div class="table-responsive export-table">
                                        <table id="tablePartidasProyecto" class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                                            <thead>
                                                <tr>
                                                    <th class="border-bottom-0 fw-semibold text-center" style="width: 50px;">#</th>
                                                    <th class="border-bottom-0 fw-semibold text-center">Código / Partida</th>
                                                    <th class="border-bottom-0 fw-semibold text-center">Clave</th>
                                                    <th class="border-bottom-0 fw-semibold text-center">CCN</th>
                                                    <th class="border-bottom-0 fw-semibold text-center">Código Cliente</th>
                                                    <th class="border-bottom-0 fw-semibold text-start text-wrap" style="min-width: 350px;">Descripción</th>
                                                    <th class="border-bottom-0 fw-semibold text-start text-wrap" style="min-width: 450px;">Descripción Adicional</th>
                                                    <th class="border-bottom-0 fw-semibold text-end">Cantidad</th>
                                                    <th class="border-bottom-0 fw-semibold text-end">Precio Unitario</th>
                                                    <th class="border-bottom-0 fw-semibold text-end">Descuento</th>
                                                    <th class="border-bottom-0 fw-semibold text-end">Importe Impuesto</th>
                                                    <th class="border-bottom-0 fw-semibold text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Se llena dinámicamente con JS / DataTables -->
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB 4 -->

                    </div>
                </div>

            </div>
            <!-- ========== FIN SEGUNDA SECCIÓN ========== -->

        </div>

    </div>
    <!-- END: PAGE CONTENT -->

</section>
<!-- FIN CONTENIDO VISTA -->

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<div id="loadModalPermisos"></div>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>
