<?php /* View: Seguimiento - Órdenes de Compra Clientes */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Estilos exclusivos del módulo, después del tema y de la escala tipográfica común. -->
<link rel="stylesheet" href="<?= assets() ?>/app/css/seguimiento_ordenes_cliente.css?v=<?= version(); ?>">
<!-- Header Admin 02 -->
<?php require_once("Template/header_02.php"); ?>

<!-- CONTENIDO DE VISTA -->
<section role="main" class="content-body fondo-general seguimiento-ordenes-cliente">

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

            <!-- Panel compacto de consulta: conserva fechas completas y contratos de los controles. -->
            <div class="mb-4 report-filters oc-filter-shell" id="panel_filtros" role="region" aria-labelledby="filtro_ordenes_titulo">
                <div class="oc-filter-main">
                    <div class="oc-filter-brand">
                        <span class="oc-filter-icon" aria-hidden="true"><i class="fa-regular fa-cart-shopping"></i></span>
                        <div>
                            <h3 id="filtro_ordenes_titulo">Órdenes de compra</h3>
                            <p>Clientes · Seguimiento de entregas</p>
                        </div>
                    </div>
                    <div class="form-group pt-0 mb-0 oc-filter-order">
                        <label class="form-label" for="filtro_num_orden"><i class="fa-regular fa-hashtag me-1" aria-hidden="true"></i>Número de orden</label>
                        <input type="text" class="form-control" autocomplete="off" name="filtro_num_orden" id="filtro_num_orden" placeholder="Buscar por núm. orden...">
                    </div>
                    <div class="form-group pt-0 mb-0">
                        <label class="form-label" for="filtro_fecha_ini"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Fecha inicio</label>
                        <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" name="filtro_fecha_ini" id="filtro_fecha_ini" placeholder="dd/mm/aaaa" required="" maxlength="10">
                    </div>
                    <div class="form-group pt-0 mb-0">
                        <label class="form-label" for="filtro_fecha_fin"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Fecha fin</label>
                        <input type="text" class="form-control inputDateMask" autocomplete="off" data-toggle="datepicker" name="filtro_fecha_fin" id="filtro_fecha_fin" placeholder="dd/mm/aaaa" required="" maxlength="10">
                    </div>
                    <div class="oc-filter-action">
                        <button type="button" class="btn btn-primary w-100" id="btnFiltrar"><i class="fa-regular fa-arrows-rotate me-1" aria-hidden="true"></i>Actualizar</button>
                    </div>
                </div>
                <div class="oc-filter-footer">
                    <span><i class="fa-regular fa-calendar-check me-1" aria-hidden="true"></i>El número de orden se busca en todos los periodos.</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRestablecerFiltros"><i class="fa-regular fa-filter-circle-xmark me-1" aria-hidden="true"></i>Restablecer filtros</button>
                </div>
            </div>



            <!-- Resumen ejecutivo: órdenes del periodo consultado, sin sumar partidas ni monedas. -->
            <section class="row g-3 mb-4" id="panel_kpis" aria-labelledby="resumen_ejecutivo_titulo" aria-busy="false">
                <div class="col-12 d-flex flex-wrap justify-content-between align-items-end gap-2">
                    <div>
                        <p class="executive-eyebrow mb-1">Control de entregas</p>
                        <h3 class="executive-heading mb-0" id="resumen_ejecutivo_titulo">Resumen ejecutivo</h3>
                    </div>
                    <span class="executive-scope" id="kpi_occ_total" aria-live="polite">Pendiente de consulta</span>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="executive-kpi executive-kpi-open h-100" aria-labelledby="kpi_abiertas_titulo">
                        <button type="button" class="executive-kpi-filter" data-kpi-occ="abiertas" aria-labelledby="kpi_abiertas_titulo" aria-describedby="kpi_occ_abiertas" aria-pressed="false" aria-controls="tableOrdenes" disabled></button>
                        <div class="executive-kpi-top"><h4 id="kpi_abiertas_titulo">Órdenes por atender</h4><i class="fa-regular fa-cart-shopping" aria-hidden="true"></i></div>
                        <div class="executive-kpi-value" id="kpi_occ_abiertas">—</div>
                        <p class="executive-kpi-caption mb-0" id="kpi_occ_abiertas_detalle">Pendientes y entregadas parcialmente</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="executive-kpi executive-kpi-late h-100" aria-labelledby="kpi_vencidas_titulo">
                        <button type="button" class="executive-kpi-filter" data-kpi-occ="vencidas" aria-labelledby="kpi_vencidas_titulo" aria-describedby="kpi_occ_vencidas" aria-pressed="false" aria-controls="tableOrdenes" disabled></button>
                        <div class="executive-kpi-top"><h4 id="kpi_vencidas_titulo">Con partidas vencidas</h4><i class="fa-regular fa-clock" aria-hidden="true"></i></div>
                        <div class="executive-kpi-value" id="kpi_occ_vencidas">—</div>
                        <p class="executive-kpi-caption mb-0">Órdenes abiertas con entregas atrasadas</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="executive-kpi executive-kpi-soon h-100" aria-labelledby="kpi_proximas_titulo">
                        <button type="button" class="executive-kpi-filter" data-kpi-occ="proximas" aria-labelledby="kpi_proximas_titulo" aria-describedby="kpi_occ_proximas" aria-pressed="false" aria-controls="tableOrdenes" disabled></button>
                        <div class="executive-kpi-top"><h4 id="kpi_proximas_titulo">Próximas (30 días)</h4><i class="fa-regular fa-calendar-days" aria-hidden="true"></i></div>
                        <div class="executive-kpi-value" id="kpi_occ_proximas">—</div>
                        <p class="executive-kpi-caption mb-0">Órdenes abiertas con entregas de hoy a 30 días</p>
                    </article>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="executive-kpi executive-kpi-supplier h-100" aria-labelledby="kpi_desfase_titulo" title="Fecha estimada del proveedor posterior al compromiso de entrega al cliente.">
                        <button type="button" class="executive-kpi-filter" data-kpi-occ="desfase" aria-labelledby="kpi_desfase_titulo" aria-describedby="kpi_occ_desfase" aria-pressed="false" aria-controls="tableOrdenes" disabled></button>
                        <div class="executive-kpi-top"><h4 id="kpi_desfase_titulo">Desfase del proveedor</h4><i class="fa-regular fa-truck" aria-hidden="true"></i></div>
                        <div class="executive-kpi-value" id="kpi_occ_desfase">—</div>
                        <p class="executive-kpi-caption mb-0">Órdenes con partidas pendientes en desfase</p>
                    </article>
                </div>
                <div class="col-12">
                    <p class="executive-footnote mb-0" id="kpi_occ_estado" role="status">Resumen del periodo consultado. Una orden puede aparecer en varios indicadores.</p>
                </div>
            </section>



            <!-- ========== LISTA DE ÓRDENES ========== -->
            <div class="chart-container border mb-4" id="panel_lista_registros">

                <!-- Subtítulo -->
                <div class="chart-title">
                    <i class="fa-regular fa-bars-staggered text-primary me-2"></i> Lista de Órdenes de Compra
                </div>

                <!-- Filtros locales combinables con las búsquedas del DataTable. -->
                <div class="mb-3 d-flex flex-wrap gap-2 align-items-center" id="filtros_cumplimiento_occ" role="group" aria-label="Filtrar órdenes por cumplimiento de partidas">
                    <span class="font-small text-muted fw-semibold me-1">Filtrar por cumplimiento:</span>
                    <button type="button" class="btn btn-sm filtro-occ-todos active" data-cumplimiento="todos" aria-pressed="true">Todas (<span data-conteo-cumplimiento="todos">0</span>)</button>
                    <button type="button" class="btn btn-sm filtro-occ-vencido" data-cumplimiento="vencido" aria-pressed="false">Vencidas (<span data-conteo-cumplimiento="vencido">0</span>)</button>
                    <button type="button" class="btn btn-sm filtro-occ-proximo" data-cumplimiento="proximo" aria-pressed="false">Próximas (30 días) (<span data-conteo-cumplimiento="proximo">0</span>)</button>
                    <button type="button" class="btn btn-sm filtro-occ-en-tiempo" data-cumplimiento="en_tiempo" aria-pressed="false">En tiempo (<span data-conteo-cumplimiento="en_tiempo">0</span>)</button>
                </div>

                <div class="executive-filter-notice d-none mb-3" id="aviso_filtro_ejecutivo" role="status">
                    <span id="texto_filtro_ejecutivo"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn_quitar_filtro_ejecutivo">Quitar filtro ejecutivo</button>
                </div>

                <!-- Tabla de Órdenes -->
                <div class="table-responsive export-table">
                    <table id="tableOrdenes"
                           class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100 seguimiento-tabla">
                        <thead>
                            <tr>
                                <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
                                <th class="border-bottom-0 fw-semibold text-center">Estatus OCC</th>
                                <th class="border-bottom-0 fw-semibold text-center">Estatus Cumplimiento</th>
                                <th class="border-bottom-0 fw-semibold text-center" title="Fechas estimadas. Margen = fecha cliente menos fecha proveedor; un valor negativo indica desfase.">Comparativo de Entregas</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proyecto Venta</th>
                                <th class="border-bottom-0 fw-semibold text-center">Orden Compra</th>
                                <th class="border-bottom-0 fw-semibold text-center">Fecha Pedido</th>
                                <th class="border-bottom-0 fw-semibold text-center">Folio OCP</th>
                                <th class="border-bottom-0 fw-semibold text-center">Fecha OCP</th>
                                <th class="border-bottom-0 fw-semibold text-center">Proveedor</th>
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
                        <span class="font-section-title fw-semibold text-dark">
                            <i class="fa-regular fa-calendar-days text-primary me-2"></i> Calendario de Fechas Límite de Entrega a Clientes
                        </span>
                        <p class="text-muted font-small mb-0">Estatus de cumplimiento de entregas estimado por orden y partida</p>
                    </div>

                    <!-- Leyendas y Badges Resumen -->
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge oc-indicador oc-tone-vencido p-2 font-small d-flex align-items-center" title="Pendientes con fecha de entrega vencida">
                            <i class="fa-regular fa-circle-exclamation me-1"></i> Vencidas: <strong id="cal_count_vencidos" class="ms-1">0</strong>
                        </span>
                        <span class="badge oc-indicador oc-tone-proximo p-2 font-small d-flex align-items-center" title="Pendientes próximas a vencer en 30 días">
                            <i class="fa-regular fa-clock me-1"></i> Próximas (30d): <strong id="cal_count_proximos" class="ms-1">0</strong>
                        </span>
                        <span class="badge oc-indicador oc-tone-en_tiempo p-2 font-small d-flex align-items-center" title="Pendientes con entrega en tiempo">
                            <i class="fa-regular fa-circle-check me-1"></i> En Tiempo: <strong id="cal_count_en_tiempo" class="ms-1">0</strong>
                        </span>
                        <span class="badge oc-indicador oc-tone-entregado p-2 font-small d-flex align-items-center" title="Partidas ya entregadas al cliente (entregado = 1)">
                            <i class="fa-regular fa-box-check me-1"></i> Entregadas: <strong id="cal_count_entregados" class="ms-1">0</strong>
                        </span>
                        <span class="badge oc-indicador oc-tone-todos p-2 font-small d-flex align-items-center" title="Total de partidas con fecha de entrega">
                            Total: <strong id="cal_count_total" class="ms-1">0</strong>
                        </span>
                    </div>
                </div>

                <!-- Filtros rápidos por estatus -->
                <div class="oc-cal-filtros mb-3 d-flex flex-wrap gap-2 align-items-center" role="group" aria-label="Filtrar entregas por cumplimiento">
                    <span class="font-small text-muted me-1 fw-semibold">Filtrar en calendario:</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary active btn-filter-cal" data-filter="todos" aria-pressed="true">
                        <i class="fa-regular fa-layer-group me-1"></i> Todos
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger btn-filter-cal" data-filter="vencido" aria-pressed="false">
                        <i class="fa-regular fa-circle-exclamation me-1"></i> Vencidas
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-warning text-dark btn-filter-cal" data-filter="proximo" aria-pressed="false">
                        <i class="fa-regular fa-clock me-1"></i> Próximas (30 días)
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-success btn-filter-cal" data-filter="en_tiempo" aria-pressed="false">
                        <i class="fa-regular fa-circle-check me-1"></i> En Tiempo
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-primary btn-filter-cal" data-filter="entregado" aria-pressed="false">
                        <i class="fa-regular fa-box-check me-1"></i> Entregadas
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-filter-cal" data-filter="cancelado" aria-pressed="false">
                        <i class="fa-regular fa-ban me-1"></i> Canceladas
                    </button>
                </div>

                <!-- Contenedor de FullCalendar -->
                <p class="oc-cal-ayuda">Fechas de entrega por partida. Selecciona una entrega para ver su detalle; en días con varias partidas, usa «Ver más».</p>
                <div class="oc-cal-superficie">
                    <div id="calendarEntregas" aria-label="Calendario de entregas de órdenes de clientes"></div>
                    <p id="cal_resultado" class="oc-cal-ayuda mb-0 mt-3" role="status" aria-live="polite"></p>
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
                            <p class="mb-0 fw-600 text-primary font-table-body">
                                <i class="fa-regular fa-receipt me-1"></i> Datos Generales de la Orden
                            </p>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">

                                <div class="form-group col-12 col-sm-6 col-lg-3 pt-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Orden de Compra:</label>
                                    <h6 class="vistadatos font-table-body text-wrap text-break fw-bold text-danger mb-0" id="det_pedido_id">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Cliente:</label>
                                    <h6 class="vistadatos font-table-body text-wrap text-break fw-bold text-dark mb-0" id="det_cliente">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Proyecto Venta:</label>
                                    <h6 class="vistadatos font-table-body text-wrap text-break fw-bold text-dark mb-0" id="det_proyecto_id">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Proyecto / Título:</label>
                                    <h6 class="vistadatos font-table-body text-wrap text-break fw-semibold text-dark mb-0" id="det_titulo">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Vendedor:</label>
                                    <h6 class="vistadatos font-table-body text-wrap text-break fw-semibold text-dark mb-0" id="det_vendedor">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Estatus:</label>
                                    <h6 class="vistadatos font-table-body text-wrap text-break mb-0" id="det_estatus">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Estatus OCC:</label>
                                    <div class="vistadatos font-table-body text-wrap text-break mb-0" id="det_estatus_occ">—</div>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Estatus Cumplimiento:</label>
                                    <div class="vistadatos font-table-body text-wrap text-break mb-0" id="det_cumplimiento_occ">—</div>
                                </div>

                                <div class="form-group col-12">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Comparativo de Entregas:</label>
                                    <div class="font-table-body text-wrap text-break mb-0" id="det_comparativo_entregas">—</div>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Fecha Pedido:</label>
                                    <h6 class="vistadatos font-table-body text-wrap text-break fw-semibold text-dark mb-0" id="det_fecha_pedido">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Monto:</label>
                                    <h6 class="vistadatos font-body text-wrap text-break fw-bold text-success mb-0" id="det_monto">—</h6>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-lg-3">
                                    <label class="form-label font-small fw-bold text-primary mb-1">Clasificación:</label>
                                    <h6 class="vistadatos font-table-body text-wrap text-break fw-semibold text-dark mb-0" id="det_clasificacion">—</h6>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- OCP relacionadas: mismo orden y datos de la fila seleccionada en la lista. -->
                <div class="mb-4">
                    <div class="card border p-0 shadow-sm rounded-3">
                        <div class="card-header bg-header-card border-bottom py-2.5 px-3">
                            <p class="mb-0 fw-600 text-primary font-table-body">
                                <i class="fa-regular fa-file-lines me-1"></i> Órdenes de Compra a Proveedores
                            </p>
                        </div>
                        <div class="card-body p-3">
                            <div id="det_ordenes_proveedor" class="row g-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Partidas de la Orden de Compra Cliente (tb_pedidos_cliente_detalle) -->
                <div class="mb-4">
                    <div class="card border p-0 shadow-sm rounded-3">
                        <div class="card-header bg-header-card border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                            <p class="mb-0 fw-600 text-primary font-table-body">
                                <i class="fa-regular fa-boxes-stacked me-1"></i> Partidas de la Orden de Compra del Cliente (tb_pedidos_cliente_detalle)
                                <span class="badge bg-secondary ms-2" id="badge_total_partidas">0</span>
                            </p>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center" id="filtros_partidas_cliente" role="group" aria-label="Filtrar partidas por cumplimiento">
                                <span class="font-small text-muted fw-semibold me-1">Filtrar partidas:</span>
                                <button type="button" class="btn btn-sm filtro-occ-todos active" data-filtro-partidas="todos" aria-pressed="true">Todas (<span data-conteo-partidas="todos">0</span>)</button>
                                <button type="button" class="btn btn-sm filtro-occ-vencido" data-filtro-partidas="vencido" aria-pressed="false">Vencidas (<span data-conteo-partidas="vencido">0</span>)</button>
                                <button type="button" class="btn btn-sm filtro-occ-proximo" data-filtro-partidas="proximo" aria-pressed="false">Próximas (30 días) (<span data-conteo-partidas="proximo">0</span>)</button>
                                <button type="button" class="btn btn-sm filtro-occ-en-tiempo" data-filtro-partidas="en_tiempo" aria-pressed="false">En tiempo (<span data-conteo-partidas="en_tiempo">0</span>)</button>
                                <button type="button" class="btn btn-sm filtro-partidas-entregado" data-filtro-partidas="entregado" aria-pressed="false">Entregadas (<span data-conteo-partidas="entregado">0</span>)</button>
                                <button type="button" class="btn btn-sm filtro-partidas-cancelado" data-filtro-partidas="cancelado" aria-pressed="false">Canceladas (<span data-conteo-partidas="cancelado">0</span>)</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover mb-0 font-small" id="tabla_partidas_cliente">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">Partida</th>
                                            <th class="text-center" style="width: 110px;">Clave</th>
                                            <th class="text-center" style="width: 110px;">CCN</th>
                                            <th class="text-start" style="min-width: 280px;">Descripción</th>
                                            <th class="text-center" style="width: 70px;">Cant.</th>
                                            <th class="text-center" style="width: 60px;">U.M.</th>
                                            <th class="text-end" style="width: 100px;">P. Unitario</th>
                                            <th class="text-end" style="width: 95px;">Descuento</th>
                                            <th class="text-end" style="width: 110px;">Impuesto</th>
                                            <th class="text-end" style="width: 105px;">Total</th>
                                            <th class="text-center" style="width: 110px;">Tiempo Entrega</th>
                                            <th class="text-center" style="width: 110px;">Fecha Estimada</th>
                                            <th class="text-center" style="width: 150px;">Estatus Entrega</th>
                                            <th class="text-center" style="width: 180px;">Estatus Cumplimiento</th>
                                            <th class="text-center" style="min-width: 230px;">Comparativo de Entregas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_partidas_cliente">
                                        <!-- Se llena dinámicamente con JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archivos Adjuntos de la Orden de Compra Cliente (tb_pedidos_cliente_adjuntos) -->
                <div class="mb-4">
                    <div class="card border p-0 shadow-sm rounded-3">
                        <div class="card-header bg-header-card border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                            <p class="mb-0 fw-600 text-primary font-table-body">
                                <i class="fa-regular fa-paperclip me-1"></i> Archivos Adjuntos de la Orden (tb_pedidos_cliente_adjuntos)
                                <span class="badge bg-secondary ms-2" id="badge_total_adjuntos">0</span>
                            </p>
                        </div>
                        <div class="card-body p-3">
                            <div id="contenedor_adjuntos_cliente" class="d-flex flex-wrap gap-2">
                                <!-- Se llena dinámicamente con JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Seguimientos -->
                <div class="historial-seguimientos">
                    <div class="card border p-0 shadow-sm rounded-3">
                        <div class="card-header bg-header-card border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                            <p class="mb-0 fw-600 text-primary font-table-body">
                                <i class="fa-regular fa-timeline me-1"></i> Historial de Seguimientos (Bitácora de Proyecto)
                                <span class="badge bg-primary ms-2" id="badge_total_seg">0</span>
                            </p>
                        </div>
                        <div class="card-body p-0">

                            <!-- Loading seguimientos -->
                            <div id="loading_seguimientos" class="text-center p-4 d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <span class="ms-2 font-table-body text-muted">Cargando seguimientos...</span>
                            </div>

                            <!-- Sin seguimientos -->
                            <div id="sin_seguimientos" class="text-center p-4 d-none">
                                <i class="fa-light fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted font-table-body">Esta orden no tiene seguimientos registrados.</p>
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
<div class="modal fade seguimiento-ordenes-cliente" id="modalDetalleEntrega" tabindex="-1" aria-labelledby="modalDetalleEntregaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom py-2.5 px-3">
                <h5 class="modal-title fw-bold text-primary font-body" id="modalDetalleEntregaLabel">
                    <i class="fa-regular fa-calendar-lines-pen me-2"></i> Detalle de Fecha Límite de Entrega
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Orden de Compra:</label>
                        <p class="fw-bold text-danger font-body mb-0" id="mdl_pedido_id">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Cliente:</label>
                        <p class="fw-bold text-dark font-body mb-0" id="mdl_cliente">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Proyecto Venta:</label>
                        <p class="fw-bold text-dark font-body mb-0" id="mdl_proyecto_id">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Proyecto / Título:</label>
                        <p class="fw-semibold text-dark font-table-body mb-0" id="mdl_titulo_venta">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Código Partida:</label>
                        <p class="fw-semibold text-dark font-table-body mb-0" id="mdl_codigo_partida">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Cantidad Solicitada:</label>
                        <p class="fw-bold text-primary font-table-body mb-0" id="mdl_cantidad">—</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label font-small text-muted mb-0">Descripción del Producto / Servicio:</label>
                        <div class="p-0 text-dark font-table-body fw-semibold" id="mdl_descripcion" style="white-space: pre-wrap;">—</div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Fecha Pedido:</label>
                        <p class="fw-semibold text-dark font-table-body mb-0" id="mdl_fecha_pedido">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Fecha Límite de Entrega:</label>
                        <p class="fw-bold font-body mb-0" id="mdl_fecha_estimada">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Tiempo Restante para la Entrega:</label>
                        <p class="fw-bold font-table-body mb-0" id="mdl_tiempo_restante">—</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label font-small text-muted mb-0">Estatus de Cumplimiento:</label>
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

