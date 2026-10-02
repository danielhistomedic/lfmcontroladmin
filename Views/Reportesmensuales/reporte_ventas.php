<?php require_once('Template/header_01.php'); ?>
<?php require_once('Template/header_02.php'); ?>

<section role="main" class="content-body fondo-general">
    <header class="page-header">
        <h2><?= $data['page_form_title']; ?></h2>
        <div class="right-wrapper text-end">
            <ol class="breadcrumbs">
                <li>
                    <a href="<?= base_url(); ?>/inicio" aria-label="Inicio">
                        <i class="bx bx-home-alt" aria-hidden="true"></i>
                    </a>
                </li>
                <li><span><?= htmlspecialchars($data['page_breadcrumb'], ENT_QUOTES, 'UTF-8'); ?></span></li>
            </ol>
            <div class="sidebar-right-toggle" style="cursor: default;"></div>
        </div>
    </header>

    <?php
    $esc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    $money = static fn($value) => number_format((float)$value, 2, '.', ',');
    $filters = $data['filtros'];
    $report = $data['reporte'];
    $months = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $selectedMonths = is_array($filters['mes']) ? $filters['mes'] : [$filters['mes']];
    $selectedYears = is_array($filters['anio']) ? $filters['anio'] : [$filters['anio']];
    $yearNames = implode(', ', $selectedYears);
    $monthNames = implode(', ', array_map(static fn($month) => $months[$month], $selectedMonths));
    $periods = [];
    foreach ($selectedYears as $year) {
        foreach ($selectedMonths as $month) {
            $start = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
            $periods[] = $start->format('d/m/Y') . ' al ' . $start->modify('last day of this month')->format('d/m/Y');
        }
    }
    $periodLabel = implode(' | ', $periods);
    ?>
    <div id="ventas-mensuales">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div>
                        <h3 class="mt-0 mb-1">Reportes de Ventas</h3><span class="text-muted">Resultados de <?= $esc($monthNames); ?> <?= $esc($yearNames); ?> · Importes con IVA en USD</span>
                    </div>
                    <span class="badge bg-primary align-self-start">REPORTE MENSUAL</span>
                </div>
                <form id="filtros-ventas-mensuales" method="get" action="<?= base_url(); ?>/reportesmensuales/ventas" class="row g-3 align-items-end">
                    <div class="col-md-3"><label for="ventas-anio" class="form-label">Años</label><select id="ventas-anio" name="anio[]" class="form-control" multiple required data-plugin-selectTwo data-plugin-options='{"closeOnSelect":false}'>
                            <?php for ($y = max((int)date('Y') + 1, max($selectedYears)); $y >= 2000; $y--): ?>
                                <option value="<?= $y; ?>" <?= in_array($y, $selectedYears, true) ? 'selected' : ''; ?>><?= $y; ?></option>
                            <?php endfor; ?>
                        </select></div>
                    <div class="col-md-3"><label for="ventas-mes" class="form-label">Meses</label><select id="ventas-mes" name="mes[]" class="form-control" multiple required data-plugin-selectTwo data-plugin-options='{"closeOnSelect":false}'>
                            <?php foreach ($months as $number => $name): ?><option value="<?= $number; ?>" <?= in_array($number, $selectedMonths, true) ? 'selected' : ''; ?>><?= $name; ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-md-4"><label for="ventas-vendedor" class="form-label">Vendedor</label><select id="ventas-vendedor" name="vendedor" class="form-control" data-plugin-selectTwo>
                            <option value="">TODOS</option>
                            <?php foreach ($data['vendedores'] as $seller): ?><option value="<?= $esc($seller['id']); ?>" <?= (string)$seller['id'] === $filters['vendedor'] ? 'selected' : ''; ?>><?= $esc($seller['nombre']); ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Actualizar</button></div>
                </form>
                <small class="d-block mt-2">Selecciona uno o varios años y meses y pulsa Actualizar para aplicar la selección.</small>
                <div id="ventas-cargando" class="mt-2 text-primary" role="status" hidden>Actualizando todos los indicadores…</div>
                <?php if ((int)$data['usuario']['rol_id'] === 4): ?><small class="d-block mt-2 text-muted">TODOS incluye únicamente tus proyectos autorizados.</small><?php endif; ?>
            </div>
        </div>
        <?php if ($data['reporte_error'] !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= $esc($data['reporte_error']); ?></div>
        <?php elseif ($report !== null): ?>
            <?php if ($report['cantidades']['total_proyectos'] === 0 && $report['proyectos'] === 0): ?><div class="alert alert-info" role="status">No hay proyectos ni ventas cotizadas o colocadas para los filtros seleccionados.</div><?php endif; ?>
            <div class="ventas-encabezado-cuantitativo mb-4">
                <h3 class="mt-0 mb-1 fw-bold"><i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>Análisis cuantitativo</h3>
                <p class="mb-0">Cantidad de proyectos del período: indicadores generales, pendientes críticos y distribución por vendedor, clasificación y estatus.</p>
            </div>
            <h4 class="mt-0 mb-3">Cantidades</h4>
            <div class="row g-3 mb-4">
                <?php foreach ([['Total de Proyectos', $report['cantidades']['total_proyectos'], 'Proyectos registrados en los meses seleccionados'], ['Declinados', $report['cantidades']['declinados'], 'Proyectos de los meses seleccionados declinados'], ['Pedidos Cotizados', $report['cantidades']['cotizacion_cliente'], 'Proyectos de los meses seleccionados con cotización enviada'], ['Pedidos Colocados', $report['cantidades']['orden_compra_cliente'], 'Proyectos de los meses seleccionados con orden de compra de cliente']] as $kpi): ?>
                    <?php $colorClass = $kpi[0] === 'Declinados' ? 'ventas-kpi-declinados bg-danger text-white' : ($kpi[0] === 'Pedidos Colocados' ? 'ventas-kpi-pedidos bg-success text-white' : ''); ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card h-100 ventas-kpi shadow rounded-3 <?= $colorClass; ?><?= $kpi[0] === 'Declinados' ? ' ventas-abrir-declinados' : ''; ?>" <?php if ($kpi[0] === 'Declinados'): ?>role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#modal-declinados-ventas" aria-haspopup="dialog" aria-controls="modal-declinados-ventas" aria-label="Ver lista de proyectos declinados" <?php endif; ?>>
                            <div class="card-body">
                                <div class="text-muted mb-2"><?= $esc($kpi[0]); ?></div>
                                <div class="ventas-valor"><?= $esc($kpi[1]); ?></div><small class="text-muted"><?= $esc($kpi[2]); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr class="ventas-separador">
            <h4 class="mt-0 mb-3">Cantidades (crítico)</h4>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-3">
                    <div class="card h-100 ventas-kpi ventas-kpi-critico shadow rounded-3 ventas-abrir-declinados" role="button" tabindex="0" data-lista="interna_sin_cliente" data-bs-toggle="modal" data-bs-target="#modal-declinados-ventas" aria-haspopup="dialog" aria-controls="modal-declinados-ventas" aria-label="Ver proyectos con cotización interna sin cotización a cliente">
                        <div class="card-body">
                            <div class="text-muted mb-2">Proyectos con cotización interna sin cotización a cliente</div>
                            <div class="ventas-valor"><?= $esc($report['cantidades']['interna_sin_cliente']); ?></div>
                            <small class="text-muted">Proyectos de los meses seleccionados no declinados, con cotización interna enviada y sin cotización a cliente enviada vinculada</small>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="ventas-separador">
            <div class="ventas-paneles">
            <div class="card mb-4 ventas-grafico-clasificaciones shadow rounded-3">
                <div class="card-body">
                    <h4 class="mt-0">Proyectos por clasificación</h4>
                    <p class="text-muted">Comparativo por mes y año: una barra por clasificación dentro de cada mes, con declinados apilados en rojo. Las letras bajo las barras corresponden a la leyenda.</p>
                    <div class="ventas-cascada-scroll" tabindex="0" role="region" aria-label="Gráfica general por clasificación">
                        <div id="ventas-clasificaciones-general" class="ventas-chart" role="img" aria-label="Proyectos por clasificación con declinados apilados"></div>
                    </div>

                </div>
            </div>
            <hr class="ventas-separador">
            <div class="card mb-4 ventas-grafico-clasificaciones shadow rounded-3">
                <div class="card-body">
                    <h4 class="mt-0">Proyectos por estatus</h4>
                    <p class="text-muted">Comparativo por mes y año: una barra por estatus dentro de cada mes, con su nombre debajo y declinados apilados en rojo.</p>
                    <div class="ventas-cascada-scroll" tabindex="0" role="region" aria-label="Gráfica general por estatus del proyecto">
                        <div id="ventas-estatus-general" class="ventas-chart" role="img" aria-label="Proyectos por estatus, mes y año con declinados apilados"></div>
                    </div>
                </div>
            </div>
            <hr class="ventas-separador">
            </div>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="mt-0">Cantidades por Vendedor</h4>
                            <p class="text-muted">Proyectos registrados en los meses seleccionados, incluidos los declinados.</p>
                            <label for="ventas-vendedor-desglose" class="form-label">Selecciona una barra o un vendedor para ver su evolución mensual y sus desgloses</label>
                            <select id="ventas-vendedor-desglose" class="form-select mb-3" aria-controls="ventas-estatus-panel">
                                <option value="">Selecciona un vendedor</option>
                                <?php foreach ($report['proyectos_por_vendedor'] ?? [] as $index => $seller): ?>
                                    <option value="<?= $esc($index); ?>"><?= $esc($seller['nombre']); ?> — <?= $esc($seller['proyectos']); ?> proyectos</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="ventas-cascada-scroll" tabindex="0" role="region" aria-label="Gráfica de proyectos por vendedor; desplazamiento horizontal">
                                <div id="ventas-cantidades-vendedores" class="ventas-chart" role="img" aria-label="Cantidad de proyectos por vendedor; selecciona una barra para ver sus clasificaciones"></div>
                            </div>
                            <p id="ventas-cascada-vacio" class="text-muted" hidden>Sin proyectos para los filtros seleccionados.</p>
                            <div id="ventas-estatus-panel" class="mt-4 pt-3 border-top" hidden>
                                <h4 id="ventas-estatus-titulo" class="mt-0" aria-live="polite"></h4>
                                <p id="ventas-estatus-resumen" class="text-muted" aria-live="polite"></p>
                                <div class="ventas-cascada-scroll" tabindex="0" role="region" aria-label="Gráfica de clasificaciones del vendedor; desplazamiento horizontal">
                                    <div id="ventas-estatus-vendedor" class="ventas-chart" role="img" aria-label="Cantidad de proyectos por clasificación, con declinados apilados en rojo"></div>
                                </div>
                                <hr class="ventas-separador">
                                <h4 id="ventas-desglose-estatus-titulo" aria-live="polite"></h4>
                                <p id="ventas-desglose-estatus-resumen" class="text-muted" aria-live="polite"></p>
                                <div class="ventas-cascada-scroll" tabindex="0" role="region" aria-label="Gráfica por estatus del proyecto; desplazamiento horizontal">
                                    <div id="ventas-desglose-estatus" class="ventas-chart" role="img" aria-label="Proyectos por estatus, con declinados apilados en rojo"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="ventas-separador">
            <div class="ventas-encabezado-cuantitativo mb-4" aria-labelledby="ventas-financiero-titulo">
                <h3 id="ventas-financiero-titulo" class="mt-0 mb-1 fw-bold"><i class="fa-solid fa-dollar-sign me-2" aria-hidden="true"></i>Análisis financiero</h3>
                <p class="mb-0">Comparativo de importes cotizados y colocados del período seleccionado, con IVA en USD.</p>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="mt-0">Cotizado vs. colocado</h4>
                            <div id="ventas-comparativo" class="ventas-chart" role="img" aria-label="Comparación de montos cotizados y colocados en USD"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modal-declinados-ventas" tabindex="-1" aria-labelledby="modal-declinados-titulo" aria-hidden="true" data-url="<?= base_url(); ?>/reportesmensuales/declinados" data-anio="<?= $esc(implode(',', $selectedYears)); ?>" data-mes="<?= $esc(implode(',', $selectedMonths)); ?>" data-vendedor="<?= $esc($filters['vendedor']); ?>">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white py-3" style="border-radius: 6px 6px 0 0;">
                            <h5 class="modal-title fw-bold text-white d-flex align-items-center m-0" id="modal-declinados-titulo"><i class="fa-solid fa-chart-line-up me-2" aria-hidden="true"></i> Listado de Proyectos Declinados</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body p-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted text-3"><i class="fa-regular fa-calendar-range me-1" aria-hidden="true"></i> Periodo: <strong id="declinados-periodo" class="text-dark"><?= $esc($periodLabel); ?></strong></span>
                                <span class="badge bg-primary text-white text-3 px-3 py-2" id="declinados-total">0 Proyectos</span>
                            </div>
                            <div id="declinados-estado" class="mb-2" role="status" aria-live="polite"></div>
                            <button type="button" id="declinados-reintentar" class="btn btn-outline-primary mb-3" hidden>Reintentar</button>
                            <div class="table-responsive export-table">
                                <table class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100" id="table-declinados-ventas">
                                    <caption class="visually-hidden">Lista de proyectos del período y vendedor seleccionados</caption>
                                    <thead>
                                        <tr>
                                            <?php foreach (['No.', 'ID Proyecto', 'Fecha', 'Cliente', 'Vendedor', 'Clasificación', 'Título', 'Activo', 'Seguimientos'] as $heading): ?>
                                                <th class="border-bottom-0 fw-semibold text-center"><?= $esc($heading); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody id="declinados-proyectos"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <script type="application/json" id="ventas-mensuales-datos">
                <?= json_encode($report, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); ?>
            </script>
        <?php endif; ?>
    </div>
    <style>
        #ventas-mensuales .ventas-grafico-clasificaciones {
            border: 1px solid #cbdde7;
        }

        html.dark #ventas-mensuales .ventas-grafico-clasificaciones {
            border-color: #455563;
        }

        #ventas-mensuales .ventas-card-desglose {
            display: block;
            margin-left: 2rem;
            border-left: 3px solid #a8cbdc;
        }
        #ventas-mensuales .ventas-card-desglose[hidden] { display: none !important; }
        #ventas-mensuales .ventas-card-desglose summary { cursor: pointer; font-size: .9rem; }
        #ventas-mensuales .ventas-card-desglose p { font-size: .8rem; }
        @media (max-width: 575px) {
            #ventas-mensuales .ventas-card-desglose { margin-left: 0; }
        }
        #ventas-mensuales .ventas-encabezado-cuantitativo {
            padding: 1rem 1.25rem;
            background-color: #eaf4f8;
            border-left: 4px solid #0085a3;
            border-radius: .35rem;
        }
        html.dark #ventas-mensuales .ventas-encabezado-cuantitativo {
            background-color: #203846;
            border-left-color: #6dc2dd;
        }
        #ventas-mensuales .ventas-cascada-scroll {
            width: 100%;
            overflow-x: auto;
        }

        #ventas-mensuales .ventas-cascada-scroll:focus-visible {
            outline: 2px solid var(--primary, #0088cc);
            outline-offset: 2px;
        }

        #ventas-mensuales .ventas-separador {
            border: 0;
            border-top: 2px solid #c5d6df;
            opacity: 1;
            margin: 1.5rem 0;
        }

        html.dark #ventas-mensuales .ventas-separador {
            border-top-color: #465a68;
        }

        #ventas-mensuales {
            --ventas-texto: #243447;
            --ventas-texto-secundario: #46566a;
            --ventas-titulo: #174b66;
            color: var(--ventas-texto);
        }

        html.dark #ventas-mensuales {
            --ventas-texto: #edf2f7;
            --ventas-texto-secundario: #cbd5e1;
            --ventas-titulo: #9ddaf0;
        }

        #ventas-mensuales .card-body,
        #ventas-mensuales p,
        #ventas-mensuales .form-label {
            color: var(--ventas-texto);
        }

        #ventas-mensuales .text-muted {
            color: var(--ventas-texto-secundario) !important;
        }

        #ventas-mensuales h3,
        #ventas-mensuales h4,
        #ventas-mensuales summary {
            color: var(--ventas-titulo);
        }

        #ventas-mensuales .ventas-valor {
            color: inherit;
        }

        #modalSeguimientosVenta .text-muted {
            color: #46566a !important;
        }

        html.dark #modalSeguimientosVenta .text-muted {
            color: #cbd5e1 !important;
        }

        /* El indicador debe quedar por encima de las filas y ocultar el texto de fondo. */
        #modal-declinados-ventas .dataTables_processing,
        #modalSeguimientosVenta .dataTables_processing {
            z-index: 100;
            top: 50%;
            left: 50%;
            width: min(260px, calc(100% - 2rem));
            margin: 0;
            transform: translate(-50%, -50%);
            padding: 1.25rem;
            background: #fff !important;
            color: #212529 !important;
            opacity: 1;
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .3);
            font-weight: 600;
            text-align: center;
        }

        #modal-declinados-ventas .dataTables_processing>div>div,
        #modalSeguimientosVenta .dataTables_processing>div>div {
            background: #0085a3 !important;
        }

        #modal-declinados-ventas .declinados-buttons>.dt-buttons.btn-group {
            position: static;
            top: auto;
            left: auto;
            float: none;
            margin: 0;
            display: inline-flex;
            gap: .5rem;
        }

        #modal-declinados-ventas .declinados-buttons .buttons-excel {
            display: inline-block;
            margin-left: 0;
        }

        #modal-declinados-ventas .declinados-buttons .btn {
            margin: 0;
            white-space: nowrap;
            border-radius: .25rem;
        }

        #modal-declinados-ventas .dataTables_length,
        #modal-declinados-ventas .dataTables_filter {
            float: none;
            margin: 0;
        }

        #modal-declinados-ventas .dataTables_length label,
        #modal-declinados-ventas .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0;
            white-space: nowrap;
        }

        #modal-declinados-ventas .dataTables_length select {
            width: 80px;
        }

        #modal-declinados-ventas .dataTables_length .select2-container {
            width: 80px !important;
        }

        #modal-declinados-ventas .dataTables_filter input {
            width: 200px;
            margin-left: 0;
        }

        @media (max-width: 575px) {
            #modal-declinados-ventas .declinados-search {
                width: 100%;
            }

            #modal-declinados-ventas .dataTables_filter input {
                flex: 1;
                width: auto;
                min-width: 0;
            }
        }

        #ventas-mensuales .ventas-abrir-declinados {
            cursor: pointer;
        }

        #ventas-mensuales .ventas-abrir-declinados:focus-visible {
            outline: 3px solid var(--primary, #0088cc);
            outline-offset: 4px;
        }

        #ventas-mensuales .ventas-kpi {
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-top: 3px solid var(--primary, #0088cc);
        }

        #ventas-mensuales .ventas-kpi-critico {
            border-top-color: var(--bs-danger, #dc3545);
        }

        #ventas-mensuales .ventas-kpi-declinados {
            border-color: var(--bs-danger, #dc3545);
        }

        #ventas-mensuales .ventas-kpi-pedidos {
            border-color: var(--bs-success, #198754);
        }

        #ventas-mensuales .ventas-kpi-declinados .card-body,
        #ventas-mensuales .ventas-kpi-pedidos .card-body {
            background-color: transparent;
            color: #fff;
        }

        #ventas-mensuales .ventas-kpi-declinados .text-muted,
        #ventas-mensuales .ventas-kpi-pedidos .text-muted {
            color: #fff !important;
        }

        #ventas-mensuales .ventas-valor {
            font-size: clamp(1.35rem, 2vw, 2rem);
            font-weight: 700;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        #ventas-mensuales .ventas-chart {
            width: 100%;
            height: 340px;
        }

        #ventas-mensuales .table th {
            white-space: nowrap;
        }

        #ventas-mensuales .table td.text-end {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        #ventas-mensuales summary {
            cursor: pointer;
        }

        #ventas-mensuales .select2-container {
            width: 100% !important;
        }

        @media (max-width: 575px) {
            #ventas-mensuales .ventas-chart {
                height: 290px;
            }
        }
        /* Densidad del reporte, limitada a esta vista. */
        #ventas-mensuales { font-size: 13px; line-height: 1.4; background: #f3f5f7; padding: 10px; border-radius: 4px; }
        #ventas-mensuales .card { border: 1px solid #dbe3e8; border-radius: 5px; background-color: #fff; }
        #ventas-mensuales .shadow { box-shadow: 0 2px 7px rgba(36,52,71,.12) !important; }
        #ventas-mensuales .ventas-paneles { display: grid; grid-template-columns: minmax(0,1fr); gap: 12px; margin-bottom: 14px; }
        #ventas-mensuales .ventas-paneles > .card { min-width: 0; margin-bottom: 0 !important; }
        #ventas-mensuales .ventas-paneles > .ventas-separador { display: none; }
        html.dark #ventas-mensuales { background-color: #17232d; }
        html.dark #ventas-mensuales .card { background-color: #21313e; border-color: #455563; }
        #ventas-mensuales .ventas-kpi.bg-danger { background-color: #dc3545 !important; }
        #ventas-mensuales .ventas-kpi.bg-success { background-color: #198754 !important; }
        @media (min-width: 1600px) {
            #ventas-mensuales .ventas-paneles { grid-template-columns: repeat(2,minmax(0,1fr)); }
        }
        #ventas-mensuales h3 { font-size: 18px; line-height: 1.3; }
        #ventas-mensuales h4 { font-size: 15px; line-height: 1.3; margin-bottom: 8px; }
        #ventas-mensuales p { margin-bottom: 8px; }
        #ventas-mensuales small { font-size: 11px; line-height: 1.35; }
        #ventas-mensuales .card-body { padding: 12px 14px; }
        #ventas-mensuales .card-header { padding: 9px 12px; }
        #ventas-mensuales .mb-4 { margin-bottom: 14px !important; }
        #ventas-mensuales .mb-3 { margin-bottom: 10px !important; }
        #ventas-mensuales .mt-4 { margin-top: 14px !important; }
        #ventas-mensuales .g-3 { --bs-gutter-x: 12px; --bs-gutter-y: 10px; }
        #ventas-mensuales .form-label { font-size: 12px; margin-bottom: 4px; }
        #ventas-mensuales .form-control,
        #ventas-mensuales .form-select,
        #ventas-mensuales .btn { font-size: 12px; padding: 5px 9px; min-height: 32px; }
        #ventas-mensuales .select2-selection { min-height: 32px; font-size: 12px; }
        #ventas-mensuales .select2-selection--single { height: 32px; }
        #ventas-mensuales .select2-selection--multiple { height: auto; padding: 2px 4px; }
        #ventas-mensuales .select2-selection--multiple .select2-selection__choice { margin-top: 3px; }
        #ventas-mensuales .select2-selection--single .select2-selection__rendered { line-height: 30px; }
        #ventas-mensuales .select2-selection--single .select2-selection__arrow { height: 30px; }
        #filtros-ventas-mensuales { align-items: flex-start !important; }
        #filtros-ventas-mensuales .form-label { line-height: 16px; margin-bottom: 4px; }
        #filtros-ventas-mensuales .select2-container .select2-selection {
            box-sizing: border-box;
            min-height: 36px;
            padding: 5px 8px;
        }
        #filtros-ventas-mensuales .select2-container .select2-selection--single { height: 36px; }
        #filtros-ventas-mensuales .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
            padding: 0 20px 0 0;
        }
        #filtros-ventas-mensuales .select2-selection--single .select2-selection__arrow { height: 34px; }
        #filtros-ventas-mensuales .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px;
            min-height: 24px;
            margin: 0;
            padding: 0;
        }
        #filtros-ventas-mensuales .select2-selection--multiple .select2-selection__choice {
            margin: 0;
            padding: 1px 5px;
            line-height: 20px;
        }
        #filtros-ventas-mensuales .select2-search--inline { margin: 0; }
        #filtros-ventas-mensuales .select2-search__field {
            margin: 0 !important;
            padding: 0;
            height: 24px;
            min-height: 0;
            line-height: 24px;
        }
        #filtros-ventas-mensuales button[type="submit"] { height: 36px; margin-top: 20px; }
        @media (max-width: 767px) {
            #filtros-ventas-mensuales button[type="submit"] { margin-top: 0; }
        }
        #ventas-mensuales .ventas-kpi .card-body { padding: 10px 12px; }
        #ventas-mensuales .ventas-kpi .mb-2 { margin-bottom: 4px !important; }
        #ventas-mensuales .ventas-valor { font-size: 25px; line-height: 1.2; margin-bottom: 5px; }
        #ventas-mensuales .ventas-encabezado-cuantitativo { padding: 10px 14px; }
        #ventas-mensuales .ventas-separador { margin: 14px 0; border-top-width: 1px; }
        #ventas-mensuales .ventas-card-desglose { margin-left: 16px; }
        #ventas-mensuales .ventas-chart { height: 280px; }
        #modal-declinados-ventas .modal-body { font-size: 12px; }
        #modal-declinados-ventas .table th,
        #modal-declinados-ventas .table td { padding: 5px 7px; font-size: 12px; }
        #modal-declinados-ventas .form-control,
        #modal-declinados-ventas .btn { font-size: 12px; }
        @media (max-width: 575px) {
            #ventas-mensuales .card-body { padding: 10px; }
            #ventas-mensuales .ventas-card-desglose { margin-left: 0; }
        }

    </style>
</section>

<?php $seguimientosUrl = base_url() . '/Reportesmensuales/seguimientos';
require dirname(__DIR__, 2) . '/Template/modal_seguimientos_venta.php'; ?>

<?php require_once('Template/footer_01.php'); ?>
<script src="<?= assets(); ?>/app/js/seguimientos_proyecto.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/echarts/dist/echarts.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/echarts/i18n/langES.js?v=<?= version(); ?>"></script>
<?php require_once('Template/footer_02.php'); ?>
