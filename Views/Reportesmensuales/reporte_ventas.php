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
    ?>
    <div id="ventas-mensuales">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div>
                        <h3 class="mt-0 mb-1">Reportes de Ventas</h3><span class="text-muted">Resultados de <?= $esc($months[$filters['mes']]); ?> <?= $esc($filters['anio']); ?> · Importes con IVA en USD</span>
                    </div>
                    <span class="badge bg-primary align-self-start">REPORTE MENSUAL</span>
                </div>
                <form id="filtros-ventas-mensuales" method="get" action="<?= base_url(); ?>/reportesmensuales/ventas" class="row g-3 align-items-end">
                    <div class="col-md-3"><label for="ventas-anio" class="form-label">Año</label><select id="ventas-anio" name="anio" class="form-control" data-plugin-selectTwo>
                            <?php for ($y = max((int)date('Y') + 1, $filters['anio']); $y >= 2000; $y--): ?>
                                <option value="<?= $y; ?>" <?= $y === $filters['anio'] ? 'selected' : ''; ?>><?= $y; ?></option>
                            <?php endfor; ?>
                        </select></div>
                    <div class="col-md-3"><label for="ventas-mes" class="form-label">Mes</label><select id="ventas-mes" name="mes" class="form-control" data-plugin-selectTwo>
                            <?php foreach ($months as $number => $name): ?><option value="<?= $number; ?>" <?= $number === $filters['mes'] ? 'selected' : ''; ?>><?= $name; ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-md-4"><label for="ventas-vendedor" class="form-label">Vendedor</label><select id="ventas-vendedor" name="vendedor" class="form-control" data-plugin-selectTwo>
                            <option value="">TODOS</option>
                            <?php foreach ($data['vendedores'] as $seller): ?><option value="<?= $esc($seller['id']); ?>" <?= (string)$seller['id'] === $filters['vendedor'] ? 'selected' : ''; ?>><?= $esc($seller['nombre']); ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Actualizar</button></div>
                </form>
                <div id="ventas-cargando" class="mt-2 text-primary" role="status" hidden>Actualizando todos los indicadores…</div>
                <?php if ((int)$data['usuario']['rol_id'] === 4): ?><small class="d-block mt-2 text-muted">TODOS incluye únicamente tus proyectos autorizados.</small><?php endif; ?>
            </div>
        </div>
        <?php if ($data['reporte_error'] !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= $esc($data['reporte_error']); ?></div>
        <?php elseif ($report !== null): ?>
            <?php if ($report['cantidades']['total_proyectos'] === 0 && $report['proyectos'] === 0): ?><div class="alert alert-info" role="status">No hay proyectos ni ventas cotizadas o colocadas para los filtros seleccionados.</div><?php endif; ?>
            <h4 class="mt-0 mb-3">Cantidades</h4>
            <div class="row g-3 mb-4">
                <?php foreach ([['Total de Proyectos', $report['cantidades']['total_proyectos'], 'Proyectos registrados en el mes seleccionado'], ['Declinados', $report['cantidades']['declinados'], 'Proyectos del mes declinados'], ['Cotización Cliente', $report['cantidades']['cotizacion_cliente'], 'Proyectos del mes con cotización enviada'], ['Orden Compra Cliente', $report['cantidades']['orden_compra_cliente'], 'Proyectos del mes con orden de compra de cliente']] as $kpi): ?>
                    <?php $colorClass = $kpi[0] === 'Declinados' ? 'ventas-kpi-declinados bg-danger text-white' : ($kpi[0] === 'Orden Compra Cliente' ? 'ventas-kpi-pedidos bg-success text-white' : ''); ?>
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
                            <small class="text-muted">Proyectos del mes no declinados, con cotización interna enviada y sin cotización a cliente enviada vinculada</small>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="ventas-separador">
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="mt-0">Cantidades por Vendedor</h4>
                            <p class="text-muted">Proyectos registrados en el mes seleccionado, incluidos los declinados.</p>
                            <label for="ventas-vendedor-desglose" class="form-label">Selecciona una barra o un vendedor para ver sus clasificaciones</label>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="ventas-separador">
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="mt-0">Cotizado vs. colocado</h4>
                            <div id="ventas-comparativo" class="ventas-chart" role="img" aria-label="Comparación de montos cotizados y colocados; cifras en los totales de la tabla de ventas por vendedor"></div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="ventas-separador">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="mt-0">Ventas por vendedor</h4>
                    <div id="ventas-vendedores" class="ventas-chart" role="img" aria-label="Montos por vendedor; valores en la tabla siguiente"></div>
                    <div class="table-responsive">
                        <table class="table table-striped ventas-tabla">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th class="text-end">Cotizado USD</th>
                                    <th class="text-end">Colocado USD</th>
                                    <th class="text-end">Participación colocada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report['vendedores'] as $seller): ?><tr>
                                        <td><?= $esc($seller['nombre']); ?></td>
                                        <td class="text-end" data-order="<?= $esc($seller['cotizado']); ?>"><?= $money($seller['cotizado']); ?></td>
                                        <td class="text-end" data-order="<?= $esc($seller['colocado']); ?>"><?= $money($seller['colocado']); ?></td>
                                        <td class="text-end"><?= $money($report['colocado'] != 0 ? $seller['colocado'] / $report['colocado'] * 100 : 0); ?> %</td>
                                    </tr><?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end"><?= $money($report['cotizado']); ?></th>
                                    <th class="text-end"><?= $money($report['colocado']); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php foreach (['productos' => 'Productos / servicios por subclasificación', 'cruce' => 'Vendedores por subclasificación'] as $section => $title): ?>
                <hr class="ventas-separador">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="mt-0"><?= $title; ?></h4>
                        <div id="ventas-<?= $section; ?>" class="ventas-chart" role="img" aria-label="Comparativo por subclasificación; valores en la tabla siguiente"></div>
                        <div class="table-responsive">
                            <table class="table table-striped ventas-tabla">
                                <thead>
                                    <tr>
                                        <?php if ($section === 'cruce'): ?><th>Vendedor</th><?php endif; ?>
                                        <th>Subclasificación</th>
                                        <th class="text-end">Partidas</th>
                                        <th>Unidades cotizadas</th>
                                        <th>Unidades vendidas</th>
                                        <th class="text-end">Cotizado USD</th>
                                        <th class="text-end">Colocado USD</th>
                                        <th class="text-end">Participación colocada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report[$section] as $row): ?><tr>
                                            <?php if ($section === 'cruce'): ?><td><?= $esc($row['vendedor']); ?></td><?php endif; ?>
                                            <td><?= $esc($row['nombre']); ?></td>
                                            <td class="text-end"><?= $row['partidas']; ?></td>
                                            <td><?= $esc($row['unidades_cotizadas']); ?></td>
                                            <td><?= $esc($row['unidades_vendidas']); ?></td>
                                            <td class="text-end" data-order="<?= $esc($row['cotizado']); ?>"><?= $money($row['cotizado']); ?></td>
                                            <td class="text-end" data-order="<?= $esc($row['colocado']); ?>"><?= $money($row['colocado']); ?></td>
                                            <td class="text-end"><?= $money($row['participacion']); ?> %</td>
                                        </tr><?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr><?php if ($section === 'cruce'): ?><th></th><?php endif; ?><th>Total del período</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th class="text-end"><?= $money($report['cotizado']); ?></th>
                                        <th class="text-end"><?= $money($report['colocado']); ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <hr class="ventas-separador">
            <div class="card mb-4">
                <div class="card-body">
                    <details>
                        <summary>Criterios del reporte y conciliación</summary>
                        <p class="mt-3">Cotizado incluye únicamente cotizaciones con enviado = 1 del mes seleccionado, de proyectos con estatus de pedido 1 o 2. Los proyectos sin cotizaciones enviadas no aportan importes cotizados. Colocado incluye el total del proyecto con estatus de pedido 2 cuando tiene al menos un pedido de cliente del mes. Por indicación del usuario, los proyectos con activo = CERRADO se excluyen del conteo de Cotización Cliente y de todos los importes cotizados.</p>
                        <p>Los montos incluyen IVA. MXN se divide entre el último tipo de cambio USD registrado: <?= $esc($report['tipo_cambio'] ?: 1); ?><?= $report['fecha_tipo_cambio'] ? ' (' . $esc($report['fecha_tipo_cambio']) . ')' : ''; ?>. Las demás monedas conservan el tratamiento numérico USD de la referencia. <?= $report['tipo_cambio'] == 0 ? 'No hay una tasa válida: se utilizó el divisor 1 de la referencia.' : ''; ?></p>
                        <p class="mb-0">El detalle usa importes y cantidades de cotizaciones enviadas y pedidos del mes vinculados a tb_ventas_detalle. Las unidades se separan por clave de unidad. Las partidas son identificadores únicos de tb_ventas_detalle, aunque aparezcan en ambos conjuntos. “Sin desglose / diferencia con total de proyecto” concilia importes sin partidas valoradas y diferencias entre documentos y total del proyecto; puede ser negativo. La participación usa el total colocado de todos los filtros.</p>
                    </details>
                </div>
            </div>
            <div class="modal fade" id="modal-declinados-ventas" tabindex="-1" aria-labelledby="modal-declinados-titulo" aria-hidden="true" data-url="<?= base_url(); ?>/reportesmensuales/declinados" data-anio="<?= $esc($filters['anio']); ?>" data-mes="<?= $esc($filters['mes']); ?>" data-vendedor="<?= $esc($filters['vendedor']); ?>">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white py-3" style="border-radius: 6px 6px 0 0;">
                            <h5 class="modal-title fw-bold text-white d-flex align-items-center m-0" id="modal-declinados-titulo"><i class="fa-solid fa-chart-line-up me-2" aria-hidden="true"></i> Listado de Proyectos Declinados</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body p-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <span class="text-muted text-3"><i class="fa-regular fa-calendar-range me-1" aria-hidden="true"></i> Periodo: <strong class="text-dark"><?= sprintf('01/%02d/%04d', $filters['mes'], $filters['anio']); ?> al <?= (new DateTimeImmutable(sprintf('%04d-%02d-01', $filters['anio'], $filters['mes'])))->modify('last day of this month')->format('d/m/Y'); ?></strong></span>
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
    </style>
</section>

<?php $seguimientosUrl = base_url() . '/Reportesmensuales/seguimientos';
require dirname(__DIR__, 2) . '/Template/modal_seguimientos_venta.php'; ?>

<?php require_once('Template/footer_01.php'); ?>
<script src="<?= assets(); ?>/app/js/seguimientos_proyecto.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/echarts/dist/echarts.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/echarts/i18n/langES.js?v=<?= version(); ?>"></script>
<?php require_once('Template/footer_02.php'); ?>
