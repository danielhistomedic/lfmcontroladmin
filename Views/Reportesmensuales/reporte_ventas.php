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
    $months = [1=>'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    ?>
    <div id="ventas-mensuales">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div><h3 class="mt-0 mb-1">Seguimiento comercial</h3><span class="text-muted">Resultados de <?= $esc($months[$filters['mes']]); ?> <?= $esc($filters['anio']); ?> · Importes con IVA en USD</span></div>
                    <span class="badge bg-primary align-self-start">REPORTE MENSUAL</span>
                </div>
                <form id="filtros-ventas-mensuales" method="get" action="<?= base_url(); ?>/reportesmensuales/ventas" class="row g-3 align-items-end">
                    <div class="col-md-3"><label for="ventas-anio" class="form-label">Año</label><select id="ventas-anio" name="anio" class="form-control" data-plugin-selectTwo>
                        <?php for ($y=max((int)date('Y')+1,$filters['anio']); $y>=2000; $y--): ?>
                            <option value="<?= $y; ?>" <?= $y===$filters['anio'] ? 'selected' : ''; ?>><?= $y; ?></option>
                        <?php endfor; ?>
                    </select></div>
                    <div class="col-md-3"><label for="ventas-mes" class="form-label">Mes</label><select id="ventas-mes" name="mes" class="form-control" data-plugin-selectTwo>
                        <?php foreach ($months as $number=>$name): ?><option value="<?= $number; ?>" <?= $number===$filters['mes'] ? 'selected' : ''; ?>><?= $name; ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="col-md-4"><label for="ventas-vendedor" class="form-label">Vendedor</label><select id="ventas-vendedor" name="vendedor" class="form-control" data-plugin-selectTwo>
                        <option value="">TODOS</option>
                        <?php foreach ($data['vendedores'] as $seller): ?><option value="<?= $esc($seller['id']); ?>" <?= (string)$seller['id']===$filters['vendedor'] ? 'selected' : ''; ?>><?= $esc($seller['nombre']); ?></option><?php endforeach; ?>
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
            <?php if ($report['proyectos'] === 0): ?><div class="alert alert-info" role="status">No hay ventas cotizadas ni colocadas para los filtros seleccionados.</div><?php endif; ?>
            <div class="row g-3 mb-4">
                <?php foreach ([['Total cotizado', '$ '.$money($report['cotizado']), $report['proyectos_cotizados'].' proyectos cotizados'], ['Total colocado', '$ '.$money($report['colocado']), $report['proyectos_colocados'].' proyectos colocados'], ['Proyectos únicos', (string)$report['proyectos'], $report['cotizaciones_enviadas'].' cotizaciones enviadas en el mes'], ['Colocado / cotizado', $report['cotizado'] != 0 ? $money($report['colocado']/$report['cotizado']*100).' %' : '—', 'Relación de montos del mes; períodos independientes']] as $kpi): ?>
                    <div class="col-sm-6 col-xl-3"><div class="card h-100 ventas-kpi"><div class="card-body"><div class="text-muted mb-2"><?= $esc($kpi[0]); ?></div><div class="ventas-valor"><?= $esc($kpi[1]); ?></div><small class="text-muted"><?= $esc($kpi[2]); ?></small></div></div></div>
                <?php endforeach; ?>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-lg-4"><div class="card h-100"><div class="card-body"><h4 class="mt-0">Cotizado vs. colocado</h4><div id="ventas-comparativo" class="ventas-chart" role="img" aria-label="Comparación de montos cotizados y colocados; cifras en los indicadores superiores"></div></div></div></div>
                <div class="col-lg-8"><div class="card h-100"><div class="card-body"><h4 class="mt-0">Evolución durante el mes</h4><div id="ventas-evolucion" class="ventas-chart" role="img" aria-label="Distribución diaria; cifras disponibles en la tabla diaria"></div><small class="text-muted">Cotizado: última fecha de cotización enviada del proyecto en el mes. Colocado: primer pedido del proyecto en el mes. Cada proyecto se contabiliza una vez por concepto.</small>
                <details class="mt-2"><summary>Ver distribución diaria</summary><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Día</th><th class="text-end">Cotizado USD</th><th class="text-end">Colocado USD</th></tr></thead><tbody><?php foreach ($report['diario'] as $day): ?><tr><td><?= $day['dia']; ?></td><td class="text-end"><?= $money($day['cotizado']); ?></td><td class="text-end"><?= $money($day['colocado']); ?></td></tr><?php endforeach; ?></tbody></table></div></details>
                </div></div></div>
            </div>
            <div class="card mb-4"><div class="card-body"><h4 class="mt-0">Ventas por vendedor</h4><div id="ventas-vendedores" class="ventas-chart" role="img" aria-label="Montos por vendedor; valores en la tabla siguiente"></div>
                <div class="table-responsive"><table class="table table-striped ventas-tabla"><thead><tr><th>Vendedor</th><th class="text-end">Cotizado USD</th><th class="text-end">Colocado USD</th><th class="text-end">Participación colocada</th></tr></thead><tbody>
                    <?php foreach ($report['vendedores'] as $seller): ?><tr><td><?= $esc($seller['nombre']); ?></td><td class="text-end" data-order="<?= $esc($seller['cotizado']); ?>"><?= $money($seller['cotizado']); ?></td><td class="text-end" data-order="<?= $esc($seller['colocado']); ?>"><?= $money($seller['colocado']); ?></td><td class="text-end"><?= $money($report['colocado'] != 0 ? $seller['colocado']/$report['colocado']*100 : 0); ?> %</td></tr><?php endforeach; ?>
                </tbody><tfoot><tr><th>Total</th><th class="text-end"><?= $money($report['cotizado']); ?></th><th class="text-end"><?= $money($report['colocado']); ?></th><th></th></tr></tfoot></table></div>
            </div></div>
            <?php foreach (['productos'=>'Productos / servicios por subclasificación', 'cruce'=>'Vendedores por subclasificación'] as $section=>$title): ?>
                <div class="card mb-4"><div class="card-body"><h4 class="mt-0"><?= $title; ?></h4><div id="ventas-<?= $section; ?>" class="ventas-chart" role="img" aria-label="Comparativo por subclasificación; valores en la tabla siguiente"></div>
                    <div class="table-responsive"><table class="table table-striped ventas-tabla"><thead><tr>
                        <?php if ($section==='cruce'): ?><th>Vendedor</th><?php endif; ?>
                        <th>Subclasificación</th><th class="text-end">Partidas</th><th>Unidades cotizadas</th><th>Unidades vendidas</th><th class="text-end">Cotizado USD</th><th class="text-end">Colocado USD</th><th class="text-end">Participación colocada</th>
                    </tr></thead><tbody>
                        <?php foreach ($report[$section] as $row): ?><tr>
                            <?php if ($section==='cruce'): ?><td><?= $esc($row['vendedor']); ?></td><?php endif; ?>
                            <td><?= $esc($row['nombre']); ?></td><td class="text-end"><?= $row['partidas']; ?></td><td><?= $esc($row['unidades_cotizadas']); ?></td><td><?= $esc($row['unidades_vendidas']); ?></td><td class="text-end" data-order="<?= $esc($row['cotizado']); ?>"><?= $money($row['cotizado']); ?></td><td class="text-end" data-order="<?= $esc($row['colocado']); ?>"><?= $money($row['colocado']); ?></td><td class="text-end"><?= $money($row['participacion']); ?> %</td>
                        </tr><?php endforeach; ?>
                    </tbody><tfoot><tr><?php if ($section==='cruce'): ?><th></th><?php endif; ?><th>Total del período</th><th></th><th></th><th></th><th class="text-end"><?= $money($report['cotizado']); ?></th><th class="text-end"><?= $money($report['colocado']); ?></th><th></th></tr></tfoot></table></div>
                </div></div>
            <?php endforeach; ?>
            <div class="card mb-4"><div class="card-body"><details><summary>Criterios del reporte y conciliación</summary>
                <p class="mt-3">Cotizado incluye cotizaciones enviadas del mes de proyectos con estatus de pedido 1 o 2; si no existe ninguna cotización enviada en todo el historial, incluye el total del proyecto ACTIVO según su fecha de cotización o fecha de proyecto. Colocado incluye el total del proyecto con estatus de pedido 2 cuando tiene al menos un pedido de cliente del mes. No se agregan exclusiones de cancelado o declinado que no existen en esas ramas del listado de referencia.</p>
                <p>Los montos incluyen IVA. MXN se divide entre el último tipo de cambio USD registrado: <?= $esc($report['tipo_cambio'] ?: 1); ?><?= $report['fecha_tipo_cambio'] ? ' ('.$esc($report['fecha_tipo_cambio']).')' : ''; ?>. Las demás monedas conservan el tratamiento numérico USD de la referencia. <?= $report['tipo_cambio'] == 0 ? 'No hay una tasa válida: se utilizó el divisor 1 de la referencia.' : ''; ?></p>
                <p class="mb-0">El detalle usa importes y cantidades de cotizaciones enviadas y pedidos del mes vinculados a tb_ventas_detalle. Las unidades se separan por clave de unidad. Las partidas son identificadores únicos de tb_ventas_detalle, aunque aparezcan en ambos conjuntos. “Sin desglose / diferencia con total de proyecto” concilia importes sin partidas valoradas y diferencias entre documentos y total del proyecto; puede ser negativo. La participación usa el total colocado de todos los filtros.</p>
            </details></div></div>
            <script type="application/json" id="ventas-mensuales-datos"><?= json_encode($report, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); ?></script>
        <?php endif; ?>
    </div>
    <style>
        #ventas-mensuales .ventas-kpi { border-top: 3px solid var(--primary, #0088cc); }
        #ventas-mensuales .ventas-valor { font-size: clamp(1.35rem, 2vw, 2rem); font-weight: 700; line-height: 1.4; overflow-wrap: anywhere; }
        #ventas-mensuales .ventas-chart { width: 100%; height: 340px; }
        #ventas-mensuales .table th { white-space: nowrap; }
        #ventas-mensuales .table td.text-end { font-variant-numeric: tabular-nums; white-space: nowrap; }
        #ventas-mensuales summary { cursor: pointer; }
        #ventas-mensuales .select2-container { width: 100% !important; }
        @media (max-width: 575px) { #ventas-mensuales .ventas-chart { height: 290px; } }
    </style>
</section>

<?php require_once('Template/footer_01.php'); ?>
<script src="<?= assets(); ?>/vendor/echarts/dist/echarts.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/echarts/i18n/langES.js?v=<?= version(); ?>"></script>
<?php require_once('Template/footer_02.php'); ?>
