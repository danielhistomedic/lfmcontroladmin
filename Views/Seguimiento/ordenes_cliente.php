<?php /* View: Seguimiento - Órdenes de Compra Clientes */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

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

        <div class="col-12">

            <section class="card card-featured shadow-sm mb-4">

                <header class="card-header">
                    <h2 class="card-title">
                        <?= $data['page_card_title']; ?>
                        <i title="Info" style="cursor:pointer;"
                           class="text-primary fa-light fa-circle-question ms-1"
                           data-bs-toggle="collapse"
                           data-bs-target="#collapseInfo"
                           aria-expanded="false"
                           aria-controls="collapseInfo"></i>
                    </h2>
                    <div class="collapse mt-1" id="collapseInfo">
                        <span class="text-info fw-normal"><?= $data['page_card_description']; ?></span>
                    </div>
                </header>

                <div class="p-4 card-body loading-panel-showing">

                    <div class="loading-panel">
                        <div class="bounce-loader">
                            <div class="bounce1"></div>
                            <div class="bounce2"></div>
                            <div class="bounce3"></div>
                        </div>
                    </div>

                    <!-- ========== FILTROS ========== -->
                    <div class="row mb-3" id="panel_filtros">

                        <div class="col-12">
                            <div class="border-bottom border-primary subtitulos_panel mb-3">
                                <p class="mb-0 fw-600 text-primary fw-semibold">
                                    <i class="fa-regular fa-filter text-info fs-14 me-1"></i>
                                    Filtros de búsqueda
                                </p>
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-lg-3">
                            <label class="form-label fs-13" for="filtro_fecha_ini">Fecha Inicio:</label>
                            <input type="date" class="form-control btn-square fs-13" id="filtro_fecha_ini" name="filtro_fecha_ini">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-lg-3">
                            <label class="form-label fs-13" for="filtro_fecha_fin">Fecha Fin:</label>
                            <input type="date" class="form-control btn-square fs-13" id="filtro_fecha_fin" name="filtro_fecha_fin">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-lg-3">
                            <label class="form-label fs-13" for="filtro_estatus">Estatus:</label>
                            <select class="select2 custom-select fs-13" id="filtro_estatus" name="filtro_estatus" style="width: 100%;">
                                <option value="">Todos los estatus</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-lg-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100 fs-13" id="btnFiltrar">
                                <i class="fa-regular fa-magnifying-glass me-1"></i> Buscar
                            </button>
                        </div>

                    </div>
                    <!-- ========== FIN FILTROS ========== -->

                    <!-- ========== LISTA DE ÓRDENES ========== -->
                    <div class="" id="panel_lista_registros">

                        <div class="row">

                            <!-- Subtítulo -->
                            <div class="form-group col-12 mt-2">
                                <div class="border-bottom border-primary subtitulos_panel">
                                    <p class="mb-0 fw-600 text-primary fw-semibold">
                                        <i class="fa-regular fa-bars-staggered text-info text-info-shadow fs-18 me-1"></i>
                                        Lista de Órdenes de Compra
                                    </p>
                                </div>
                            </div>

                            <div class="form-group col-12 mb-0">

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

                        </div>

                    </div>
                    <!-- ========== FIN LISTA ========== -->

                    <!-- ========== PANEL DETALLE ========== -->
                    <div class="" id="panel_detalle" style="display: none;">

                        <div class="row">

                            <!-- Encabezado detalle -->
                            <div class="form-group col-12 mt-2">
                                <div class="border-bottom border-primary subtitulos_panel">
                                    <p class="mb-0 fw-600 text-primary fw-semibold">
                                        <i class="fa-regular fa-file-lines text-info text-info-shadow fs-18 me-1"></i>
                                        Detalle de Orden y Seguimientos
                                    </p>
                                </div>
                            </div>

                            <!-- Botón regresar -->
                            <div class="col-12 mb-3">
                                <button type="button" class="btn btn-outline-secondary fs-13 btnReturnList" id="btnRegresar">
                                    <i class="fa-regular fa-arrow-left me-1"></i> Regresar a la lista
                                </button>
                            </div>

                            <!-- Datos generales de la orden -->
                            <div class="col-12 mb-4">
                                <div class="card border p-0 shadow-sm">
                                    <div class="card-header bg-header-card">
                                        <p class="mb-0 fw-600 text-primary fs-13">
                                            <i class="fa-regular fa-receipt me-1"></i> Datos Generales de la Orden
                                        </p>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="form-group col-12 col-sm-6 col-lg-3">
                                                <label class="form-label fs-12">Cliente:</label>
                                                <h6 class="vistadatos fs-13 text-muted" id="det_cliente">
                                                    <i class="fa-light fa-brake-warning"></i> —
                                                </h6>
                                            </div>

                                            <div class="form-group col-12 col-sm-6 col-lg-3">
                                                <label class="form-label fs-12">Proyecto / Título:</label>
                                                <h6 class="vistadatos fs-13 text-muted" id="det_titulo">
                                                    <i class="fa-light fa-brake-warning"></i> —
                                                </h6>
                                            </div>

                                            <div class="form-group col-12 col-sm-6 col-lg-3">
                                                <label class="form-label fs-12">Vendedor:</label>
                                                <h6 class="vistadatos fs-13 text-muted" id="det_vendedor">
                                                    <i class="fa-light fa-brake-warning"></i> —
                                                </h6>
                                            </div>

                                            <div class="form-group col-12 col-sm-6 col-lg-3">
                                                <label class="form-label fs-12">Estatus:</label>
                                                <h6 class="vistadatos fs-13" id="det_estatus">—</h6>
                                            </div>

                                            <div class="form-group col-12 col-sm-6 col-lg-3">
                                                <label class="form-label fs-12">Fecha Pedido:</label>
                                                <h6 class="vistadatos fs-13 text-muted" id="det_fecha_pedido">
                                                    <i class="fa-light fa-brake-warning"></i> —
                                                </h6>
                                            </div>

                                            <div class="form-group col-12 col-sm-6 col-lg-3">
                                                <label class="form-label fs-12">Monto:</label>
                                                <h6 class="vistadatos fs-13 text-muted" id="det_monto">
                                                    <i class="fa-light fa-brake-warning"></i> —
                                                </h6>
                                            </div>

                                            <div class="form-group col-12 col-sm-6 col-lg-3">
                                                <label class="form-label fs-12">Clasificación:</label>
                                                <h6 class="vistadatos fs-13 text-muted" id="det_clasificacion">
                                                    <i class="fa-light fa-brake-warning"></i> —
                                                </h6>
                                            </div>

                                            <div class="form-group col-12 col-sm-6 col-lg-3">
                                                <label class="form-label fs-12">CLUES:</label>
                                                <h6 class="vistadatos fs-13 text-muted" id="det_clues">
                                                    <i class="fa-light fa-brake-warning"></i> —
                                                </h6>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Historial de Seguimientos -->
                            <div class="col-12">
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

                    </div>
                    <!-- ========== FIN PANEL DETALLE ========== -->

                </div>

            </section>

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
