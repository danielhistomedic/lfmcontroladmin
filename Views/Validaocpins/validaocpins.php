<?php
$escape = static function ($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$numero = static function ($valor, int $decimales = 2): string {
    $partes = explode('.', (string)$valor, 2);
    $entero = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $partes[0]);
    return $entero . ($decimales > 0 ? '.' . str_pad(substr($partes[1] ?? '', 0, $decimales), $decimales, '0') : '');
};
$fechaVista = static function ($valor): string {
    $fecha = is_string($valor) ? DateTimeImmutable::createFromFormat('!Y-m-d', $valor) : false;
    return $fecha && $fecha->format('Y-m-d') === $valor ? $fecha->format('d/m/Y') : 'N/D';
};
$pedido = $data['pedido_bd'];
$qr = $data['qr_params'];
$val = $data['validacion'];
$estados = array(
    'VALIDO' => array('status-valid', 'bx bx-check-shield', 'Orden de compra localizada'),
    'DISCREPANCIA' => array('status-discrepancy', 'bx bx-error', 'Discrepancia en datos'),
    'CANCELADO' => array('status-invalid', 'bx bx-block', 'Orden de compra cancelada'),
    'SIN_FOLIO' => array('status-invalid', 'bx bx-qr-scan', 'Esperando folio de consulta'),
    'PARAMETROS_INVALIDOS' => array('status-invalid', 'bx bx-error', 'Parámetros del QR inválidos'),
    'ERROR' => array('status-invalid', 'bx bx-error', 'Consulta no disponible'),
    'NO_ENCONTRADO' => array('status-invalid', 'bx bx-x', 'Documento no encontrado')
);
$estado = $estados[$val['status']] ?? $estados['ERROR'];
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escape($data['page_title']); ?></title>
    <meta name="keywords" content="<?= $escape($data['meta_keywords']); ?>">
    <meta name="description" content="<?= $escape($data['meta_description']); ?>">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= assets(); ?>/img/favicon.ico" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/font-awesome/css/all.min.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/boxicons/css/boxicons.min.css" />

    <style>
        :root {
            --lfm-primary: #0F172A;
            --lfm-red: #D9232D;
            --lfm-red-dark: #A61B23;
            --lfm-bg: #F8FAFC;
            --lfm-card-bg: #FFFFFF;
            --lfm-text: #1E293B;
            --lfm-text-muted: #64748B;
            --lfm-border: #E2E8F0;
        }

        body {
            font-family: var(--font-family-report);
            background-color: var(--lfm-bg);
            color: var(--lfm-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .header-bar {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            border-bottom: 4px solid var(--lfm-red);
            padding: 1.25rem 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .header-logo {
            max-height: 48px;
            width: auto;
        }

        .main-container {
            max-width: 1040px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .validation-card {
            background: var(--lfm-card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            border: 1px solid var(--lfm-border);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* Status Header Badges */
        .status-header {
            padding: 2rem 1.5rem;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .status-header.status-valid {
            background: linear-gradient(135deg, #059669 0%, #10B981 100%);
        }

        .status-header.status-discrepancy {
            background: linear-gradient(135deg, #D97706 0%, #F59E0B 100%);
        }

        .status-header.status-invalid {
            background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
        }

        .status-icon-circle {
            width: 76px;
            height: 76px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            font-size: 2.5rem;
            box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.3);
        }

        .status-title {
            font-family: var(--font-family-report);
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .status-subtitle {
            font-size: 0.95rem;
            opacity: 0.95;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.4;
        }

        .card-body-custom {
            padding: 1.75rem;
        }

        .section-title {
            font-family: var(--font-family-report);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--lfm-text-muted);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .info-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 1rem 1.25rem;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--lfm-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--lfm-text);
            word-break: break-word;
        }

        .info-value.highlight-total {
            color: var(--lfm-red);
            font-size: 1.3rem;
            font-family: var(--font-family-report);
        }

        /* Check list table */
        .check-table {
            width: 100%;
            margin-bottom: 1.5rem;
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }

        .check-table tr {
            background: #F8FAFC;
            border-radius: 8px;
        }

        .check-table td {
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
        }

        .check-table td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            font-weight: 600;
        }

        .check-table td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            text-align: right;
        }

        .badge-check {
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .badge-check-success {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .badge-check-warning {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .badge-check-danger {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .security-footer {
            background-color: #F1F5F9;
            border-top: 1px solid var(--lfm-border);
            padding: 1rem 1.5rem;
            font-size: 0.8rem;
            color: var(--lfm-text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .footer-brand {
            padding: 1.5rem 0;
            text-align: center;
            font-size: 0.85rem;
            color: var(--lfm-text-muted);
        }

        @media (max-width: 576px) {
            .card-body-custom {
                padding: 1.25rem;
            }
            .status-title {
                font-size: 1.25rem;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    .partidas-table { min-width: 780px; } .partidas-table td { vertical-align: top; } .partida-descripcion { min-width: 220px; white-space: pre-wrap; overflow-wrap: anywhere; } .sello-valor { font-size: .75rem; overflow-wrap: anywhere; } .check-table td { overflow-wrap: anywhere; } </style>
    <?php require_once __DIR__ . '/../../Template/font_family.php'; ?>
</head>

<body>
    <header class="header-bar">
        <div class="container d-flex align-items-center justify-content-between px-3 flex-wrap gap-2">
            <a href="https://lfmcontrol.com.mx" target="_blank" rel="noopener noreferrer">
                <img src="<?= assets(); ?>/img/logo-light.png" alt="LFM CONTROL" class="header-logo">
            </a>
            <span class="badge bg-danger">Verificación de órdenes de compra de insumos</span>
        </div>
    </header>
    <main class="main-container">
        <div class="validation-card">
            <div class="status-header <?= $escape($estado[0]); ?>">
                <div class="status-icon-circle"><i class="<?= $escape($estado[1]); ?>" aria-hidden="true"></i></div>
                <h1 class="status-title"><?= $escape($estado[2]); ?></h1>
                <p class="status-subtitle"><?= $escape($val['mensaje']); ?></p>
            </div>
            <div class="card-body-custom">
                <?php if (!empty($pedido)): ?>
                    <h2 class="section-title">Datos registrados de la orden</h2>
                    <div class="info-grid">
                        <?php
                        $estatusDocumento = array(0 => 'En proceso', 1 => 'Finalizada', 2 => 'Cancelada');
                        $campos = array(
                            'Folio de orden de compra' => $pedido['folio_ocp'],
                            'Fecha de pedido' => $fechaVista($pedido['fecha_pedido']),
                            'Proveedor' => $pedido['proveedor'],
                            'Moneda' => $pedido['cmoneda'] !== '' ? $pedido['cmoneda'] : 'N/D',
                            'Estatus de la orden' => $estatusDocumento[(int)$pedido['enviado']] ?? 'N/D',
                            'Tipo de pedido' => $pedido['tipo_pedido'] ?? 'N/D',
                            'Número de revisión' => $pedido['num_revision'] ?? 'N/D',
                            'Fecha de revisión' => $fechaVista($pedido['fecha_revision'] ?? null),
                            'Requisición' => $pedido['num_requisicion'] ?? 'N/D',
                            'Cotización de proveedor' => $pedido['folio_cotizacion_proveedor'] ?? 'N/D'
                        );
                        foreach ($campos as $etiqueta => $valor): ?>
                            <div class="info-box">
                                <div class="info-label"><?= $escape($etiqueta); ?></div>
                                <div class="info-value"><?= $escape($valor !== '' ? $valor : 'N/D'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h2 class="section-title">Verificación de coincidencia (QR vs sistema)</h2>
                    <table class="check-table">
                        <tbody>
                            <?php foreach (array('folio' => 'Folio', 'fecha' => 'Fecha', 'total' => 'Total') as $campo => $etiqueta):
                                $coincide = $val['detalles'][$campo . '_coincide'];
                                $clase = $coincide === true ? 'badge-check-success' : 'badge-check-warning';
                                $texto = $qr[$campo] === '' ? 'No proporcionado en QR' : ($coincide === true ? 'Coincide' : 'Difiere del registro');
                            ?>
                                <tr>
                                    <th scope="row" class="p-3"><?= $escape($etiqueta); ?></th>
                                    <td>
                                        <div><?= $escape($qr[$campo]); ?></div>
                                        <span class="badge-check <?= $escape($clase); ?>"><?= $escape($texto); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h2 class="section-title">Partidas registradas</h2>
                    <?php if (!empty($data['partidas'])): ?>
                        <div class="table-responsive mb-4" tabindex="0" role="region" aria-label="Partidas de la orden, desplazamiento horizontal disponible">
                            <table class="table table-striped partidas-table">
                                <thead>
                                    <tr><th scope="col">Partida</th><th scope="col">Descripción</th><th scope="col">Unidad</th><th scope="col" class="text-end">Cantidad</th><th scope="col" class="text-end">Precio unitario</th><th scope="col" class="text-end">Descuento</th><th scope="col" class="text-end">Impuesto</th><th scope="col" class="text-end">Importe</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['partidas'] as $partida): ?>
                                        <tr>
                                            <td><?= $escape($partida['codigo_partida']); ?></td>
                                            <td class="partida-descripcion"><?= $escape($partida['descripcion']); ?><?php if (!empty($partida['descripcion_adicional'])): ?><br><span class="text-muted"><?= $escape($partida['descripcion_adicional']); ?></span><?php endif; ?></td>
                                            <td><?= $escape($partida['ccveunidad']); ?></td>
                                            <?php foreach (array('cantidad_pedido' => 4, 'precio_unitario' => 4, 'descuento' => 2, 'importe_impuesto' => 2, 'importe' => 2) as $campo => $decimales): ?>
                                                <td class="text-end"><?= $escape($numero($partida[$campo], $decimales)); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay partidas registradas en esta página.</p>
                    <?php endif; ?>
                    <?php if ($data['pagina'] > 1 || $data['hay_mas_partidas']): ?>
                        <nav class="d-flex align-items-center justify-content-between gap-2 mb-4" aria-label="Páginas de partidas">
                            <?php foreach (array($data['pagina'] - 1 => 'Anterior', $data['pagina'] + 1 => 'Siguiente') as $pagina => $etiqueta): ?>
                                <?php if (($pagina < $data['pagina'] && $pagina >= 1) || ($pagina > $data['pagina'] && $data['hay_mas_partidas'] && $pagina <= 99999)):
                                    $enlace = base_url() . '/validaocpins/?' . http_build_query(array_merge($qr, array('pagina' => $pagina)), '', '&', PHP_QUERY_RFC3986);
                                ?>
                                    <a class="btn btn-outline-secondary" href="<?= $escape($enlace); ?>"><?= $escape($etiqueta); ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <span>Página <?= (int)$data['pagina']; ?></span>
                        </nav>
                    <?php endif; ?>

                    <h2 class="section-title">Importes de la orden <?= $escape($pedido['cmoneda']); ?></h2>
                    <div class="info-grid">
                        <?php foreach (array('subtotal' => 'Subtotal', 'descuento' => 'Descuento', 'iva' => 'IVA', 'total' => 'Total') as $campo => $etiqueta): ?>
                            <div class="info-box">
                                <div class="info-label"><?= $escape($etiqueta); ?></div>
                                <div class="info-value <?= $campo === 'total' ? 'highlight-total' : ''; ?>">$<?= $escape($numero($pedido[$campo])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php foreach (array('cadena_original' => 'Cadena original registrada', 'sello_digital' => 'Sello digital registrado') as $campo => $etiqueta): ?>
                        <?php if (!empty($pedido[$campo])): ?>
                            <div class="info-box mb-3">
                                <div class="info-label"><?= $escape($etiqueta); ?></div>
                                <div class="sello-valor user-select-all"><?= $escape($pedido[$campo]); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">Consulte la orden mediante el enlace del código QR impreso en su documento.</p>
                    <?php foreach ($qr as $nombre => $valor): ?>
                        <?php if ($valor !== ''): ?>
                            <div class="info-box mb-2">
                                <div class="info-label"><?= $escape(ucfirst($nombre)); ?> recibido</div>
                                <div class="info-value"><?= $escape($valor); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="security-footer">
                <span>Consulta de orden de compra de insumos — LFM CONTROL</span>
                <span>Fecha de consulta: <?= date('d/m/Y H:i:s'); ?></span>
            </div>
        </div>
    </main>
    <footer class="footer-brand">&copy; <?= date('Y'); ?> LFM CONTROL. Todos los derechos reservados.</footer>
</body>
</html>

