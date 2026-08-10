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

            <!-- ========== FILTROS DE BÚSQUEDA ========== -->
            <div class="row mb-4" id="panel_filtros">

                <div class="form-group col-12 mb-2">
                    <div class="border-bottom pb-2">
                        <p class="mb-0 fw-semibold text-primary">
                            <i class="fa-regular fa-filter text-secondary me-2"></i> Filtros de Búsqueda de Proyectos
                        </p>
                    </div>
                </div>

                <!-- Filtro por Periodo -->
                <div class="form-group col-12 col-sm-6 col-lg-3 pt-0 mb-2">
                    <label class="form-label fw-semibold text-dark mb-1" for="filtro_periodo">Período:</label>
                    <select class="form-select" id="filtro_periodo" name="filtro_periodo">
                        <option value="este_mes" selected>Este Mes</option>
                        <option value="mes_anterior">Mes Anterior</option>
                        <option value="ano_actual">Año Actual</option>
                        <option value="ultimos_30">Últimos 30 días</option>
                        <option value="personalizado">Personalizado</option>
                        <option value="todos">Todos los Periodos</option>
                    </select>
                </div>

                <!-- Fecha Inicio -->
                <div class="form-group col-12 col-sm-6 col-lg-2 pt-0 mb-2">
                    <label class="form-label fw-semibold text-dark mb-1" for="filtro_fecha_ini">Fecha Inicio:</label>
                    <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" 
                           name="filtro_fecha_ini" id="filtro_fecha_ini" placeholder="dd/mm/aaaa" maxlength="10">
                </div>

                <!-- Fecha Fin -->
                <div class="form-group col-12 col-sm-6 col-lg-2 pt-0 mb-2">
                    <label class="form-label fw-semibold text-dark mb-1" for="filtro_fecha_fin">Fecha Fin:</label>
                    <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" 
                           name="filtro_fecha_fin" id="filtro_fecha_fin" placeholder="dd/mm/aaaa" maxlength="10">
                </div>

                <!-- Búsqueda por Folio/Cliente -->
                <div class="form-group col-12 col-sm-6 col-lg-3 pt-0 mb-2">
                    <label class="form-label fw-semibold text-dark mb-1" for="txtProyectoId">Clave / Folio / Cliente:</label>
                    <input type="text" class="form-control" id="txtProyectoId" name="proyecto_id" placeholder="Ej. 18654 ó PV-2026-18654" autocomplete="off">
                </div>

                <!-- Botones Acciones -->
                <div class="form-group col-12 col-lg-2 pt-0 mb-2 d-flex align-items-end gap-2">
                    <button type="button" class="btn btn-primary flex-fill hvr-float-shadow d-flex justify-content-center align-items-center" id="btnFiltrar" style="height: 38px;">
                        <i class="fa-regular fa-magnifying-glass me-1"></i> Buscar
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-3" id="btnLimpiarFiltro" onclick="fntLimpiarFiltro()" style="height: 38px;" title="Limpiar Filtros">
                        <i class="fa-regular fa-eraser"></i>
                    </button>
                </div>

            </div>
            <!-- ========== FIN FILTROS ========== -->

            <!-- ========== PRIMERA SECCIÓN: LISTA DE PROYECTOS DE VENTA (DATATABLE) ========== -->
            <div class="chart-container border mb-4" id="panel_lista_proyectos">

                <div class="chart-title d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa-regular fa-bars-staggered text-primary me-2"></i> Lista de Proyectos de Venta
                    </div>
                    <small class="text-muted fw-normal fs-12">Haga clic en un registro de la lista para ver su detalle de seguimiento</small>
                </div>

                <!-- Tabla de Proyectos de Venta -->
                <div class="table-responsive export-table">
                    <table id="tableProyectosVenta"
                           class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                        <thead>
                            <tr>
                                <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto Venta</th>
                                <th class="border-bottom-0 fw-semibold text-center">Fecha</th>
                                <th class="border-bottom-0 fw-semibold text-center">Cliente</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto / Título</th>
                                <th class="border-bottom-0 fw-semibold text-center">Vendedor</th>
                                <th class="border-bottom-0 fw-semibold text-center">Monto Total</th>
                                <th class="border-bottom-0 fw-semibold text-center">Estatus</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
            <!-- ========== FIN PRIMERA SECCIÓN ========== -->

            <!-- ========== SEGUNDA SECCIÓN: DETALLE DE SEGUIMIENTO DEL PROYECTO SELECCIONADO ========== -->
            <div id="panel_detalle_proyecto" class="row">

                <div class="col-12 mb-3">
                    <div class="border-bottom pb-2 d-flex align-items-center justify-content-between">
                        <p class="mb-0 fw-semibold text-primary fs-16">
                            <i class="fa-sharp fa-light fa-diagram-project me-2"></i> Detalle del Seguimiento del Proyecto Seleccionado
                        </p>
                    </div>
                </div>

                <!-- SELECTOR DE COINCIDENCIAS MULTIPLES -->
                <div class="col-12 mb-4 d-none" id="containerCoincidencias">
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
                <div class="col-12 d-none" id="panelLoading">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <h5 class="text-muted mt-3 fw-semibold">Consultando información del proyecto...</h5>
                    </div>
                </div>

                <!-- RESUMEN DEL PROYECTO SELECCIONADO -->
                <div class="col-12 mb-4 d-none" id="cardResumenProyecto">
                    <section class="card shadow-sm border-0 overflow-hidden">
                        <div class="card-header bg-dark text-white p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary fs-14 px-3 py-2" id="lblProyectoId">—</span>
                                <h4 class="m-0 fw-bold text-white fs-16" id="lblTituloProyecto">—</h4>
                            </div>
                            <div>
                                <span class="fs-12 text-uppercase text-light me-1">Estatus Actual:</span>
                                <span id="lblEstatusBadge" class="badge bg-warning text-dark fs-12 px-3 py-2">—</span>
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
                            </div>
                        </div>
                    </section>
                </div>

                <!-- CHECKLIST DEL PROCESO (ESTATUS ID 1 AL 7) -->
                <div class="col-12 d-none" id="cardChecklistProceso">
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
                <div class="col-12" id="panelPlaceholder">
                    <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
                        <div class="py-4">
                            <i class="fa-sharp fa-light fa-diagram-project text-muted display-4 mb-3"></i>
                            <h4 class="fw-bold text-dark mb-2">Consulta de Proyecto de Venta</h4>
                            <p class="text-muted max-w-600 mx-auto fs-14">
                                Seleccione un proyecto de venta del listado superior o utilice los filtros para visualizar la información detallada y la evaluación en el checklist del proceso.
                            </p>
                        </div>
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
