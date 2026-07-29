<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['page_title']; ?></title>
    <meta name="keywords" content="<?= $data['meta_keywords']; ?>">
    <meta name="description" content="<?= $data['meta_description']; ?>">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= assets(); ?>/img/favicon.ico" />

    <!-- Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">

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
            font-family: 'Poppins', sans-serif;
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
            max-width: 680px;
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
            font-family: 'Montserrat', sans-serif;
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
            font-family: 'Montserrat', sans-serif;
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
            font-family: 'Montserrat', sans-serif;
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
    </style>
</head>

<body>

    <!-- Header institucional -->
    <header class="header-bar">
        <div class="container d-flex align-items-center justify-content-between px-3">
            <a href="https://lfmcontrol.com.mx" target="_blank" class="d-flex align-items-center text-white text-decoration-none">
                <img src="<?= assets(); ?>/img/logo-light.png" alt="LFM CONTROL" class="header-logo" onerror="this.src='<?= assets(); ?>/img/logo.png';">
            </a>
            <span class="badge bg-danger text-uppercase px-2 py-1 font-weight-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                Módulo de Verificación
            </span>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-container">
        <div class="validation-card">

            <?php
            $val = $data['validacion'];
            $status = $val['status'];
            $cotizacion = $data['cotizacion_bd'];
            $qr = $data['qr_params'];

            // Configuración del Header según el estado
            $headerClass = "status-invalid";
            $iconClass = "bx bx-x";
            $titleText = "Documento No Encontrado";

            if ($status === "VALIDO") {
                $headerClass = "status-valid";
                $iconClass = "bx bx-check-shield";
                $titleText = "Cotización Válida y Auténtica";
            } elseif ($status === "DISCREPANCIA") {
                $headerClass = "status-discrepancy";
                $iconClass = "bx bx-error";
                $titleText = "Discrepancia en Datos";
            } elseif ($status === "SIN_FOLIO") {
                $headerClass = "status-invalid";
                $iconClass = "bx bx-qr-scan";
                $titleText = "Esperando Folio de Consulta";
            }
            ?>

            <!-- Banner de Estado -->
            <div class="status-header <?= $headerClass; ?>">
                <div class="status-icon-circle">
                    <i class="<?= $iconClass; ?>"></i>
                </div>
                <h1 class="status-title"><?= $titleText; ?></h1>
                <p class="status-subtitle"><?= $val['mensaje']; ?></p>
            </div>

            <!-- Cuerpo de la tarjeta -->
            <div class="card-body-custom">

                <?php if (!empty($cotizacion)): ?>
                    
                    <!-- Sección Datos del Registro en Base de Datos -->
                    <div class="section-title">
                        <i class="bx bx-data text-primary"></i> Datos Registrados en Base de Datos
                    </div>

                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">Folio de Cotización</div>
                            <div class="info-value">
                                <?= !empty($cotizacion['folio_cotizacion']) ? htmlspecialchars($cotizacion['folio_cotizacion']) : (!empty($cotizacion['folio']) ? htmlspecialchars($cotizacion['folio']) : htmlspecialchars($qr['folio'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($cotizacion['cliente_razon_social'])): ?>
                            <div class="info-box">
                                <div class="info-label">Cliente / Razón Social</div>
                                <div class="info-value">
                                    <?= htmlspecialchars($cotizacion['cliente_razon_social']); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="info-box">
                            <div class="info-label">Fecha Cotización</div>
                            <div class="info-value">
                                <?php 
                                $fechaBD = $cotizacion['fecha'];
                                echo !empty($fechaBD) ? date('d/m/Y', strtotime($fechaBD)) : 'N/A';
                                ?>
                            </div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Total SIN IVA</div>
                            <div class="info-value highlight-total">
                                <?php 
                                $montoBD = $cotizacion['subtotal'] ?? $cotizacion['total'] ?? $cotizacion['monto_total'] ?? $cotizacion['gran_total'] ?? $qr['subtotal'];
                                echo !empty($montoBD) ? '$' . number_format((float)$montoBD, 2, '.', ',') . ' MXN' : 'N/A';
                                ?>
                            </div>
                        </div>

                      
                    </div>

                    <!-- Sección Cotejo de Integridad -->
                    <div class="section-title">
                        <i class="bx bx-check-double text-success"></i> Verificación de Coincidencia (QR vs Sistema)
                    </div>

                    <table class="check-table">
                        <tbody>
                            <tr>
                                <td><i class="bx bx-barcode-reader me-1 text-muted"></i> Folio impreso (QR)</td>
                                <td>
                                    <?php if ($val['detalles']['folio_coincide']): ?>
                                        <span class="badge-check badge-check-success"><i class="bx bx-check"></i> Coincide (<?= htmlspecialchars($qr['folio']); ?>)</span>
                                    <?php else: ?>
                                        <span class="badge-check badge-check-danger"><i class="bx bx-x"></i> No coincide</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <?php if (!empty($qr['fecha'])): ?>
                            <tr>
                                <td><i class="bx bx-calendar me-1 text-muted"></i> Fecha impresa (QR)</td>
                                <td>
                                    <?php if ($val['detalles']['fecha_coincide'] === true): ?>
                                        <span class="badge-check badge-check-success"><i class="bx bx-check"></i> Coincide (<?= date('d/m/Y', strtotime($qr['fecha'])); ?>)</span>
                                    <?php elseif ($val['detalles']['fecha_coincide'] === false): ?>
                                        <span class="badge-check badge-check-warning"><i class="bx bx-error"></i> Difiere de BD (<?= date('d/m/Y', strtotime($qr['fecha'])); ?>)</span>
                                    <?php else: ?>
                                        <span class="badge-check badge-check-warning">No proporcionada en BD</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <?php if (!empty($qr['subtotal'])): ?>
                            <tr>
                                <td><i class="bx bx-dollar-circle me-1 text-muted"></i> Subtotal / Monto impreso (QR)</td>
                                <td>
                                    <?php 
                                    $subtotalCoincide = $val['detalles']['subtotal_coincide'] ?? $val['detalles']['total_coincide'] ?? null;
                                    if ($subtotalCoincide === true): 
                                    ?>
                                        <span class="badge-check badge-check-success"><i class="bx bx-check"></i> Coincide ($<?= number_format((float)$qr['subtotal'], 2, '.', ','); ?>)</span>
                                    <?php elseif ($subtotalCoincide === false): ?>
                                        <span class="badge-check badge-check-warning"><i class="bx bx-error"></i> Difiere de BD ($<?= number_format((float)$qr['subtotal'], 2, '.', ','); ?>)</span>
                                    <?php else: ?>
                                        <span class="badge-check badge-check-warning">No proporcionado en BD</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php else: ?>

                    <!-- Vista vacía / Folio No Encontrado -->
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="bx bx-search-alt text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Consulta de Cotización por Código QR</h5>
                        <p class="text-muted mb-4" style="max-width: 450px; margin: 0 auto; font-size: 0.9rem;">
                            <?= !empty($qr['folio']) ? 'El folio <strong>' . htmlspecialchars($qr['folio']) . '</strong> no fue localizado en nuestro sistema. Reportelo en <a href="https://lfmcontrol.com.mx/contactanos">https://lfmcontrol.com.mx/contactanos</a>.' : 'Ingrese o escanee un código QR válido impreso en su documento de cotización.'; ?>
                        </p>

                        <?php if (!empty($qr['folio'])): ?>
                            <div class="info-box text-start max-width-400 mx-auto" style="max-width: 400px;">
                                <div class="info-label">Folio Escaneado:</div>
                                <div class="info-value text-muted"><?= htmlspecialchars($qr['folio']); ?></div>
                                <?php if (!empty($qr['fecha'])): ?>
                                    <div class="info-label mt-2">Fecha Escaneada:</div>
                                    <div class="info-value text-muted"><?= htmlspecialchars($qr['fecha']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($qr['subtotal'])): ?>
                                    <div class="info-label mt-2">Subtotal Escaneado:</div>
                                    <div class="info-value text-muted">$<?= number_format((float)$qr['subtotal'], 2, '.', ','); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

            </div>

            <!-- Footer de Seguridad -->
            <div class="security-footer">
                <div>
                    <i class="bx bx-lock-alt text-success me-1"></i>
                    <span>Verificación de Integridad LFM CONTROL</span>
                </div>
                <div>
                    <span>Fecha de consulta: <?= date('d/m/Y H:i:s'); ?></span>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer institucional -->
    <footer class="footer-brand">
        <div class="container">
            <p class="mb-1">&copy; <?= date('Y'); ?> LFM CONTROL. Todos los derechos reservados.</p>
            <p class="mb-0 text-muted" style="font-size: 0.8rem;">Sistema de Validación de Documentos y Cotizaciones de Cliente</p>
        </div>
    </footer>

</body>

</html>
