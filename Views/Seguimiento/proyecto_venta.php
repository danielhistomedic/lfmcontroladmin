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

    <!-- START: PAGE CONTENT -->
    <div class="row">


        <!-- 1) ENCABEZADO DE FILTRO -->
        <div class="col-12 mb-4">
    

            <div class="form-group col-12 mb-2">
                    <div class="border-bottom pb-2">
                        <p class="mb-0 fw-semibold text-primary">
                            <i class="fa-regular fa-filter text-secondary me-2"></i> Filtro de Proyecto de Venta
                        </p>
                    </div>
            </div>

           <div class="form-group col-12 mb-2">
                    <form id="formFiltroProyecto" onsubmit="fntBuscarProyecto(event)">
                        <div class="row align-items-end g-3">
                            <div class="col-md-7 col-lg-8">
                                <label for="txtProyectoId" class="form-label fw-semibold text-dark">
                                    Clave / Folio o Número de Proyecto <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-end-0 text-primary">
                                        <i class="fa-sharp fa-light fa-diagram-project"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-start-0 fs-15 fw-bold" 
                                           id="txtProyectoId" 
                                           name="proyecto_id" 
                                           placeholder="Ej. 18654  ó  PV-2026-18654" 
                                           autocomplete="off" 
                                           required>
                                </div>
                                <div class="form-text mt-1 text-muted fs-12">
                                    <i class="fa-regular fa-lightbulb text-warning me-1"></i>Tip: Puede digitar sólo la parte numérica final (ej. <strong>18654</strong>).
                                </div>
                            </div>
                            <div class="col-md-5 col-lg-4 d-flex gap-2">
                                <button type="submit" id="btnBuscarProyecto" class="btn btn-primary btn-lg flex-fill shadow-sm">
                                    <i class="fa-regular fa-magnifying-glass me-2"></i>Buscar
                                </button>
                                <button type="button" id="btnLimpiarFiltro" onclick="fntLimpiarFiltro()" class="btn btn-outline-secondary btn-lg px-3">
                                    <i class="fa-regular fa-eraser me-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
        </div>

        <!-- 1.5) SELECTOR DE COINCIDENCIAS MULTIPLES (Si existen varias coincidencias parciales) -->
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

        <!-- STATE: CARGANDO / NO ENCONTRADO / INICIAL -->
        <div class="col-12 d-none" id="panelLoading">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <h5 class="text-muted mt-3 fw-semibold">Consultando información del proyecto...</h5>
            </div>
        </div>

        <!-- 2) RESUMEN DEL PROYECTO SELECCIONADO -->
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
                                <small class="text-muted d-block text-uppercase fw-semibold fs-11">Fecha de Registro</small>
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

        <!-- 3) CHECKLIST DEL PROCESO (ESTATUS ID 1 AL 7) -->
        <div class="col-12 d-none" id="cardChecklistProceso">
            <section class="card card-featured shadow-sm">
                <header class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h3 class="card-title m-0 text-dark fw-bold fs-16">
                            <i class="fa-sharp fa-regular fa-list-check text-primary me-2"></i>Checklist de Evaluación del Proceso
                        </h3>
                        <small class="text-muted">Evaluación de las etapas principales (cat_estatus_proyecto del ID 1 al 7)</small>
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

        <!-- INICIAL / SIN SELECCIÓN PLACEHOLDER -->
        <div class="col-12" id="panelPlaceholder">
            <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
                <div class="py-4">
                    <i class="fa-sharp fa-light fa-diagram-project text-muted display-4 mb-3"></i>
                    <h4 class="fw-bold text-dark mb-2">Consulta de Proyecto de Venta</h4>
                    <p class="text-muted max-w-600 mx-auto fs-14">
                        Ingrese la clave o el folio numérico en el encabezado de filtro superior para visualizar la información detallada y la evaluación paso a paso en el checklist del proceso.
                    </p>
                </div>
            </div>
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

