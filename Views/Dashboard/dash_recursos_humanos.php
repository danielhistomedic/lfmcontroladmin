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

<script>
    function cerrarPanel() {
        document.getElementById("detalle-personal").classList.add('d-none');
    }
</script>

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

            <!-- KPI Cards -->
            <div class="row mb-4">
                <div class="form-group col-12 mb-3">
                    <div class="border-bottom pb-2">
                        <p class="mb-0 fw-semibold text-primary">
                            <i class="fa-regular fa-file-chart-pie me-2 text-secondary"></i> Indicadores de Recursos Humanos (KPIs)
                        </p>
                    </div>
                </div>

                <!-- KPI Card 1: Total de Empleados -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-primary-lighten text-primary me-3">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-primary d-block mb-1">Total de Empleados</span>
                                <div class="text-amount text-dark" id="total_empleados">0</div>
                                <div class="mt-2 text-3 text-muted">
                                    <span>Registrados en el sistema</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Card 2: Empleados Activos -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-success-lighten text-success me-3">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-success d-block mb-1">Empleados Activos</span>
                                <div class="text-amount text-dark" id="total_empleados_activos">0</div>
                                <div class="mt-2 text-3 text-muted">
                                    <span>Con estatus activo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Card 3: Empleados de Baja -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card kpi-card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-danger-lighten text-danger me-3">
                                <i class="fa-solid fa-user-xmark"></i>
                            </div>
                            <div class="w-100">
                                <span class="text-label text-danger d-block mb-1">Empleados de Baja</span>
                                <div class="text-amount text-dark" id="total_empleados_baja">0</div>
                                <div class="mt-2 text-3 text-muted">
                                    <span>Con estatus de baja</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fin KPI Cards -->

            <!-- Visualizaciones Grid -->
            <div class="row">

                <!-- 1. Empleados por Departamento (Full Width) -->
                <div class="col-12">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-building text-primary me-2"></i> Total de Empleados por Departamento
                        </div>
                        <div id="chart_personal_departamentos" style="height: 400px; width: 100%;"></div>
                    </div>
                </div>

                <!-- 2. Empleados por Puesto (Full Width) -->
                <div class="col-12">
                    <div class="chart-container">
                        <div class="chart-title">
                            <i class="fa-regular fa-id-badge text-primary me-2"></i> Total de Empleados por Puesto
                        </div>
                        <div id="chart_personal_puestos" style="height: 400px; width: 100%;"></div>
                    </div>
                </div>

            </div>
            <!-- Fin Visualizaciones Grid -->

            <!-- Tabla Detalle Empleados por Departamento -->
            <div class="row d-none" id="detalle-personal">
                <div class="col-12">
                    <section class="card shadow-sm border-0 mb-5" style="border-radius: 10px; overflow: hidden;">
                        <header class="card-header border-0 bg-white pt-4 pb-2 d-flex justify-content-between align-items-center">
                            <h4 class="card-title text-primary fw-semibold mb-0">
                                <i class="fa-regular fa-users text-secondary me-2"></i>
                                <span id="titulo">Detalle de Empleados</span>
                            </h4>
                            <button class="btn btn-sm btn-outline-secondary" onclick="cerrarPanel()">
                                <i class="fa-regular fa-xmark me-1"></i> Cerrar
                            </button>
                        </header>
                        <div class="card-body pt-2">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-custom align-middle mb-0" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="40%">Nombre</th>
                                            <th width="30%">Puesto</th>
                                            <th width="25%">Teléfono</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contenido">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                                                Seleccione un departamento del gráfico para ver el detalle.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!-- Fin Tabla Detalle Empleados -->

        </div>
    </div>
    <!-- end: page -->

</section>
<!-- FIN CONTENIDO VISTA -->

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<div id="loadModalPermisos"></div>

<!-- ECHART JS -->
<script src="<?= assets(); ?>/vendor/echarts/dist/echarts.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/echarts/i18n/langES.js?v=<?= version(); ?>"></script>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>