<?php /* View: Almacén - Pases de Salida */ ?>
<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Theme Custom CSS -->
<style>
    .filter-box-container {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: #fff;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
    }
    .filter-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #cbd5e1;
        margin-bottom: 0.35rem;
    }
    .filter-control {
        border-radius: 8px;
        font-size: 0.9rem;
        border: 2px solid #334155;
        background-color: #0f172a;
        color: #f8fafc;
    }
    .filter-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        background-color: #1e293b;
        color: #fff;
    }
    .btn-filter-action {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .btn-filter-action:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        transform: translateY(-1px);
    }
    .btn-filter-clear {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.6rem 1.1rem;
    }
    /* Estilos Tarjetas KPI */
    .kpi-card {
        border-radius: 10px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
    }
    .kpi-icon {
        font-size: 1.6rem;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .text-amount {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .text-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    .bg-primary-lighten { background-color: rgba(13, 110, 253, 0.08) !important; }
    .bg-success-lighten { background-color: rgba(25, 135, 84, 0.08) !important; }
    .bg-info-lighten { background-color: rgba(13, 202, 240, 0.08) !important; }
    .bg-warning-lighten { background-color: rgba(255, 193, 7, 0.12) !important; }
    .bg-danger-lighten { background-color: rgba(220, 53, 69, 0.08) !important; }
    .bg-purple-lighten { background-color: rgba(111, 66, 193, 0.08) !important; }

    /* Semáforos y Badges */
    .badge-semaforo-verde {
        background-color: #10b981;
        color: #ffffff;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-semaforo-amarillo {
        background-color: #f59e0b;
        color: #1e1b4b;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-semaforo-rojo {
        background-color: #ef4444;
        color: #ffffff;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        animation: pulse-danger 2s infinite;
    }
    @keyframes pulse-danger {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.85; }
    }
    .badge-status-pendiente {
        background: rgba(245, 158, 11, 0.15);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.4);
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
    }
    .badge-status-entregado {
        background: rgba(16, 185, 129, 0.15);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.4);
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
    }
    .badge-status-cancelado {
        background: rgba(239, 68, 68, 0.15);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.4);
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        border-radius: 20px;
    }

    /* Tabla y Detalle */
    #tablePasesSalida th, #tablePasesSalida td {
        vertical-align: middle;
    }
    .selected-row {
        background-color: rgba(59, 130, 246, 0.12) !important;
        font-weight: 500;
    }
    .detail-card-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 0.9rem 1.25rem;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    .signature-container {
        border: 2px dashed #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
        position: relative;
        touch-action: none;
    }
    .signature-img-preview {
        max-height: 120px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #ffffff;
        padding: 4px;
    }
    .attachment-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .attachment-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .nav-tabs .nav-link {
        font-weight: 600;
        color: #475569;
    }
    .nav-tabs .nav-link.active {
        color: #1d4ed8;
        border-bottom: 3px solid #1d4ed8;
    }
    /* Video Player Moderno y Responsive para Modal de Evidencias */
    .visor-modal-body {
        height: 72vh;
        min-height: 480px;
        max-height: 590px;
    }
    .visor-sidebar-col {
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .visor-sidebar-list {
        overflow-y: auto;
        flex-grow: 1;
    }
    .visor-display-col {
        height: 100%;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .video-player-wrapper {
        width: 100%;
        max-width: 820px;
        height: 460px;
        max-height: calc(72vh - 100px);
        margin: 0 auto;
        position: relative;
        background-color: #0b1120;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
    }
    .video-js {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        font-family: 'Inter', system-ui, sans-serif !important;
        border-radius: 12px;
        overflow: hidden;
        background-color: #0b1120 !important;
    }
    .video-js .vjs-tech {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
    }
    .video-js .vjs-big-play-button {
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: 70px !important;
        height: 70px !important;
        line-height: 70px !important;
        border-radius: 50% !important;
        background: rgba(37, 99, 235, 0.85) !important;
        border: 2px solid rgba(255, 255, 255, 0.8) !important;
        box-shadow: 0 0 20px rgba(37, 99, 235, 0.6) !important;
        transition: all 0.25s ease !important;
    }
    .video-js:hover .vjs-big-play-button {
        background: rgba(29, 78, 216, 0.95) !important;
        transform: translate(-50%, -50%) scale(1.1) !important;
    }
    .video-js .vjs-control-bar {
        background: linear-gradient(180deg, transparent 0%, rgba(15, 23, 42, 0.9) 100%) !important;
        height: 48px !important;
        padding: 0 8px !important;
    }
    .video-js .vjs-play-progress {
        background-color: #3b82f6 !important;
    }
    .video-js .vjs-volume-level {
        background-color: #3b82f6 !important;
    }
    /* Plyr custom container */
    .plyr {
        border-radius: 12px;
        overflow: hidden;
        width: 100% !important;
        height: 100% !important;
        max-height: 100% !important;
        background-color: #0b1120 !important;
    }
    .plyr video {
        width: 100% !important;
        height: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
    }
    .plyr--video {
        height: 100% !important;
    }
    @media (max-width: 767.98px) {
        .visor-modal-body {
            height: auto;
            max-height: 85vh;
            overflow-y: auto;
        }
        .visor-sidebar-col {
            height: 180px;
            max-height: 180px;
        }
        .visor-display-col {
            height: auto;
            min-height: 360px;
        }
        .video-player-wrapper {
            height: 280px;
        }
    }
    /* Video Error and Loading Overlays */
    .video-error-card {
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(239, 68, 68, 0.4);
        border-radius: 12px;
        padding: 2.5rem 1.5rem;
        color: #f8fafc;
        max-width: 600px;
        margin: 1.5rem auto;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
        text-align: center;
    }
    .video-error-icon {
        font-size: 3.5rem;
        color: #ef4444;
        margin-bottom: 1rem;
        animation: pulseError 2s infinite ease-in-out;
    }
    @keyframes pulseError {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.06); opacity: 0.85; }
    }
    .video-loading-badge {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(59, 130, 246, 0.4);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        color: #93c5fd;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }
</style>

<!-- Vendor CSS Reproductor de Video -->
<link rel="stylesheet" href="<?= assets(); ?>/vendor/video-js/video-js.min.css">
<link rel="stylesheet" href="<?= assets(); ?>/vendor/plyr/plyr.css">

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

    <!-- start: page -->
    <div class="row">
        <div class="col-12">

            <!-- ======================================================== -->
            <!-- 1. ENCABEZADO PARA FILTROS DE INFORMACIÓN               -->
            <!-- ======================================================== -->
            <div class="filter-box-container mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-filter text-primary fs-4 me-2"></i>
                        <h4 class="m-0 text-white fw-bold">Filtros de Pases de Salida</h4>
                    </div>
                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-2" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-circle-info me-1"></i> Filtre por cliente, estatus, fechas o vendedor para actualizar KPIs y reportes
                    </span>
                </div>
                
                <form id="formFiltrosPases" onsubmit="return false;">
                    <div class="row g-2 align-items-end">
                        <!-- Filtro Cliente (Input) -->
                        <div class="col-md-3 col-sm-6">
                            <label for="txtFiltroCliente" class="filter-label">
                                <i class="fa-solid fa-user-tie me-1 text-info"></i> Cliente:
                            </label>
                            <input type="text" 
                                   id="txtFiltroCliente" 
                                   class="form-control filter-control" 
                                   placeholder="Escriba cliente o razón social..." 
                                   list="datalistClientes" 
                                   autocomplete="off">
                            <datalist id="datalistClientes">
                                <?php if (!empty($data['clientes'])): ?>
                                    <?php foreach ($data['clientes'] as $cli): ?>
                                        <option value="<?= htmlspecialchars($cli['nombre_cliente']); ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </datalist>
                        </div>

                        <!-- Filtro Estatus -->
                        <div class="col-md-2 col-sm-6">
                            <label for="selectFiltroEstatus" class="filter-label">
                                <i class="fa-solid fa-tags me-1 text-warning"></i> Estatus:
                            </label>
                            <select id="selectFiltroEstatus" class="form-select filter-control">
                                <option value="">-- Todos --</option>
                                <option value="PENDIENTE">⏳ Pendientes de Entrega</option>
                                <option value="ENTREGADO">✅ Entregados</option>
                                <option value="CANCELADO">❌ Cancelados</option>
                            </select>
                        </div>

                        <!-- Filtro Vendedor -->
                        <div class="col-md-2 col-sm-6">
                            <label for="selectFiltroVendedor" class="filter-label">
                                <i class="fa-solid fa-id-badge me-1 text-info"></i> Vendedor:
                            </label>
                            <select id="selectFiltroVendedor" class="form-select filter-control">
                                <option value="">-- Todos los Vendedores --</option>
                                <?php if (!empty($data['vendedores'])): ?>
                                    <?php foreach ($data['vendedores'] as $ven): ?>
                                        <option value="<?= htmlspecialchars($ven['ccvemedico']); ?>">
                                            <?= htmlspecialchars($ven['nombre_vendedor']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Filtro Motivo de Salida -->
                        <div class="col-md-2 col-sm-6">
                            <label for="selectFiltroMotivo" class="filter-label">
                                <i class="fa-solid fa-list-check me-1 text-info"></i> Motivo Salida:
                            </label>
                            <select id="selectFiltroMotivo" class="form-select filter-control">
                                <option value="">-- Todos los Motivos --</option>
                                <?php if (!empty($data['motivos'])): ?>
                                    <?php foreach ($data['motivos'] as $mot): ?>
                                        <option value="<?= htmlspecialchars($mot); ?>">
                                            <?= htmlspecialchars($mot); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Filtro Almacén -->
                        <div class="col-md-3 col-sm-6">
                            <label for="selectFiltroAlmacen" class="filter-label">
                                <i class="fa-solid fa-warehouse me-1 text-info"></i> Almacén:
                            </label>
                            <select id="selectFiltroAlmacen" class="form-select filter-control">
                                <option value="">-- Todos los Almacenes --</option>
                                <?php if (!empty($data['almacenes'])): ?>
                                    <?php foreach ($data['almacenes'] as $alm): ?>
                                        <option value="<?= htmlspecialchars($alm['ccvealmacen']); ?>">
                                            <?= htmlspecialchars($alm['cdscalmacen']); ?> (<?= htmlspecialchars($alm['ccvealmacen']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Fecha Desde -->
                        <div class="col-md-3 col-sm-6">
                            <label for="txtFiltroFechaInicio" class="filter-label">
                                <i class="fa-regular fa-calendar me-1 text-info"></i> Fecha Salida Desde:
                            </label>
                            <input type="date" id="txtFiltroFechaInicio" class="form-control filter-control">
                        </div>

                        <!-- Fecha Hasta -->
                        <div class="col-md-3 col-sm-6">
                            <label for="txtFiltroFechaFin" class="filter-label">
                                <i class="fa-regular fa-calendar-check me-1 text-info"></i> Fecha Salida Hasta:
                            </label>
                            <input type="date" id="txtFiltroFechaFin" class="form-control filter-control">
                        </div>

                        <!-- Proyecto / Folio -->
                        <div class="col-md-4 col-sm-8">
                            <label for="txtFiltroBusqueda" class="filter-label">
                                <i class="fa-solid fa-magnifying-glass me-1 text-info"></i> Proyecto, Folio o Persona Recibe:
                            </label>
                            <input type="text" id="txtFiltroBusqueda" class="form-control filter-control" placeholder="Ej. PS-2026-0001, PV-2026, Roberto...">
                        </div>

                        <!-- Botones de Acción -->
                        <div class="col-md-2 col-sm-4 d-flex gap-2">
                            <button type="button" id="btnAplicarFiltros" class="btn btn-filter-action flex-grow-1" title="Aplicar filtros">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar
                            </button>
                            <button type="button" id="btnLimpiarFiltros" class="btn btn-secondary btn-filter-clear" title="Limpiar todos los filtros">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ======================================================== -->
            <!-- 2. TARJETAS KPI (INDICADORES CLAVE)                     -->
            <!-- ======================================================== -->
            <div class="row mb-4" id="panel_kpis">
                <div class="form-group col-12 mb-3">
                    <div class="border-bottom pb-2 d-flex align-items-center justify-content-between">
                        <p class="mb-0 fw-semibold text-primary" style="font-size: 1.05rem;">
                            <i class="fa-regular fa-chart-simple me-2 text-secondary"></i> Indicadores de Control de Pases de Salida
                        </p>
                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.82rem;">
                            <i class="fa-solid fa-traffic-light text-warning me-1"></i> Semáforo Operativo por Días
                        </span>
                    </div>
                </div>

                <div class="row g-3" id="container_kpi_cards">
                    <!-- KPI 1: Total Pases -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-primary-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-primary text-white me-2">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div>
                                    <div class="text-label text-primary">Total Pases</div>
                                    <div class="text-amount text-dark" id="kpi_total_pases">0</div>
                                    <small class="text-muted">Movimientos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 2: Pendientes -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-warning-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-warning text-dark me-2">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div>
                                    <div class="text-label text-dark">Pendientes</div>
                                    <div class="text-amount text-dark" id="kpi_pendientes">0</div>
                                    <small class="text-muted">Por devolver</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 3: Entregados -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-success-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-success text-white me-2">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </div>
                                <div>
                                    <div class="text-label text-success">Entregados</div>
                                    <div class="text-amount text-success" id="kpi_entregados">0</div>
                                    <small class="text-muted">Con firma digital</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 4: Menos de 15 Días (Verde) -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-success-lighten h-100 border-start border-4 border-success">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-success text-white me-2" style="background-color: #10b981 !important;">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div>
                                    <div class="text-label text-success">&lt; 15 Días</div>
                                    <div class="text-amount text-dark" id="kpi_menos_15">0</div>
                                    <small class="text-success fw-bold">🟢 En Tiempo</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 5: 15 a 30 Días (Amarillo) -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-warning-lighten h-100 border-start border-4 border-warning">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-warning text-dark me-2" style="background-color: #f59e0b !important; color: #fff !important;">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <div class="text-label text-dark">15 a 30 Días</div>
                                    <div class="text-amount text-dark" id="kpi_15_a_30">0</div>
                                    <small class="text-warning fw-bold" style="color: #b45309 !important;">🟡 Atención</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 6: Más de 30 Días (Rojo) -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-danger-lighten h-100 border-start border-4 border-danger">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-danger text-white me-2" style="background-color: #ef4444 !important;">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                </div>
                                <div>
                                    <div class="text-label text-danger">&gt; 30 Días</div>
                                    <div class="text-amount text-danger" id="kpi_mas_30">0</div>
                                    <small class="text-danger fw-bold">🔴 Crítico</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- 3. RESUMEN Y ANÁLISIS EJECUTIVO                         -->
            <!-- ======================================================== -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-chart-line text-primary me-2 fs-5"></i>
                        <h5 class="m-0 fw-bold text-dark">Análisis Ejecutivo de Pases de Salida</h5>
                    </div>
                    <div>
                        <span class="badge bg-danger-lighten text-danger border border-danger px-3 py-1 fw-bold" id="badge_total_urgentes">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> 0 Pases Requieren Atención Inmediata
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <!-- Clientes con Mayor Cantidad de Pendientes -->
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold text-primary mb-2 border-bottom pb-1" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-building me-1 text-secondary"></i> Clientes con Más Pendientes
                                </h6>
                                <ul class="list-unstyled mb-0" id="list_top_clientes" style="font-size: 0.85rem;">
                                    <li class="text-muted fst-italic">Cargando datos...</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Vendedores con Mayor Cantidad de Pendientes -->
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold text-primary mb-2 border-bottom pb-1" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-user-tie me-1 text-secondary"></i> Vendedores con Más Pendientes
                                </h6>
                                <ul class="list-unstyled mb-0" id="list_top_vendedores" style="font-size: 0.85rem;">
                                    <li class="text-muted fst-italic">Cargando datos...</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Pases con Mayor Antigüedad (Atención Inmediata) -->
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold text-danger mb-2 border-bottom pb-1" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-fire me-1 text-danger"></i> Mayor Antigüedad (&gt;30 días)
                                </h6>
                                <ul class="list-unstyled mb-0" id="list_pases_criticos" style="font-size: 0.85rem;">
                                    <li class="text-muted fst-italic">Cargando datos...</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Motivos de Salida más Frecuentes -->
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold text-primary mb-2 border-bottom pb-1" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-chart-pie me-1 text-secondary"></i> Motivos más Frecuentes
                                </h6>
                                <ul class="list-unstyled mb-0" id="list_top_motivos" style="font-size: 0.85rem;">
                                    <li class="text-muted fst-italic">Cargando datos...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- 4. LISTADO PRINCIPAL DE PASES DE SALIDA (DATATABLES)    -->
            <!-- ======================================================== -->
            <section class="card card-featured card-featured-primary shadow-sm mb-4">
                <header class="card-header d-flex justify-content-between align-items-center py-3">
                    <h2 class="card-title m-0">
                        <i class="fa-solid fa-list-check me-2 text-primary"></i> <?= $data['page_card_title']; ?>
                    </h2>
                    <span class="badge bg-light text-muted border">Haga clic en cualquier fila para ver el detalle inferior</span>
                </header>

                <div class="p-4 card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover width-full" id="tablePasesSalida">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 140px;">Opciones</th>
                                    <th class="text-center" style="width: 105px;">Folio</th>
                                    <th class="text-center" style="width: 95px;">Fecha Salida</th>
                                    <th>Proyecto</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Motivo Salida</th>
                                    <th>Almacén</th>
                                    <th>Persona Recibe</th>
                                    <th class="text-center" style="width: 110px;">Estatus</th>
                                    <th class="text-center" style="width: 125px;">Semáforo / Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se carga dinámicamente vía AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 5. PANEL DE DETALLE DEL PASE SELECCIONADO              -->
            <!-- ======================================================== -->
            <div id="panelDetallePase" class="card shadow-sm border-0 mb-4" style="display: none;">
                <div class="detail-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-file-lines me-2 fs-5 text-warning"></i>
                        <h5 class="m-0 text-white fw-bold">
                            Detalle del Pase de Salida: <span id="det_folio_title" class="badge bg-primary fs-6 ms-1">-</span>
                        </h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span id="det_semaforo_badge"></span>
                        <span id="det_estatus_badge"></span>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cerrarPanelDetalle()" title="Cerrar panel de detalle">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="row g-4">
                        <!-- Columna Izquierda: Datos Generales -->
                        <div class="col-lg-7">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                                    <i class="fa-solid fa-circle-info me-1"></i> Datos Generales de la Salida
                                </h6>
                                <div class="row g-2" style="font-size: 0.9rem;">
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Folio:</span>
                                        <div id="det_folio" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Fecha Salida:</span>
                                        <div id="det_fecha" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Proyecto:</span>
                                        <div id="det_proyecto" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Cliente:</span>
                                        <div id="det_cliente" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Cliente Final:</span>
                                        <div id="det_cliente_final" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Vendedor Asignado:</span>
                                        <div id="det_vendedor" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Almacén:</span>
                                        <div id="det_almacen" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Motivo de Salida:</span>
                                        <div id="det_motivo" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-12">
                                        <span class="text-muted fw-semibold">Observaciones:</span>
                                        <div id="det_observaciones" class="p-2 bg-white rounded border small text-secondary">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Control de Entrega y Firma -->
                        <div class="col-lg-5">
                            <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                                        <i class="fa-solid fa-signature me-1"></i> Control de Entrega y Recepción
                                    </h6>
                                    <div class="mb-2" style="font-size: 0.9rem;">
                                        <span class="text-muted fw-semibold">Persona que Recibe:</span>
                                        <div id="det_persona_recibio" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="mb-2" style="font-size: 0.9rem;">
                                        <span class="text-muted fw-semibold">Fecha y Hora de Entrega:</span>
                                        <div id="det_fecha_entrega" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="mb-2" style="font-size: 0.9rem;">
                                        <span class="text-muted fw-semibold">Usuario que Registró Entrega:</span>
                                        <div id="det_usuario_recibe" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="text-muted fw-semibold d-block mb-1" style="font-size: 0.9rem;">Firma Digital Recibida:</span>
                                        <div id="det_firma_box" class="p-2 bg-white rounded border text-center">
                                            <span class="text-muted fst-italic small">Sin firma registrada</span>
                                        </div>
                                    </div>
                                </div>

                                <div id="det_btn_entregar_container" class="mt-2 text-center">
                                    <!-- Botón dinámico para registrar entrega si está pendiente -->
                                </div>
                            </div>
                        </div>

                        <!-- Partidas / Materiales del Pase -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="fa-solid fa-boxes-stacked me-1"></i> Materiales y Partidas de Salida
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle" id="tablePartidasDetalle">
                                    <thead class="table-secondary" style="font-size: 0.85rem;">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">#</th>
                                            <th style="width: 120px;">Clave</th>
                                            <th style="width: 120px;">CCN</th>
                                            <th>Descripción del Material</th>
                                            <th class="text-center" style="width: 90px;">Cantidad</th>
                                            <th class="text-center" style="width: 80px;">Unidad</th>
                                            <th>Observaciones de Partida</th>
                                            <th class="text-center" style="width: 110px;">Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyPartidasDetalle" style="font-size: 0.88rem;">
                                        <!-- Se carga dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Evidencias y Archivos Adjuntos -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-primary m-0">
                                    <i class="fa-solid fa-paperclip me-1"></i> Evidencias Documentales y Multimedia (Todas las Partidas)
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAbrirModalEvidencias">
                                    <i class="fa-solid fa-images me-1"></i> Ver en Visor Multimedia
                                </button>
                            </div>
                            <div class="row g-2" id="containerAdjuntosDetalle">
                                <div class="col-12 text-muted fst-italic small">No hay evidencias adjuntas para este pase.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- 6. PESTAÑAS DE REPORTE GENERAL (POR CLIENTE Y VENDEDOR) -->
            <!-- ======================================================== -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs px-3 pt-2" id="reportesTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-cliente" data-bs-toggle="tab" data-bs-target="#panel-reporte-cliente" type="button" role="tab" aria-selected="true">
                                <i class="fa-solid fa-users me-1 text-primary"></i> PESTAÑA 1 - REPORTE POR CLIENTE
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-vendedor" data-bs-toggle="tab" data-bs-target="#panel-reporte-vendedor" type="button" role="tab" aria-selected="false">
                                <i class="fa-solid fa-user-tie me-1 text-primary"></i> PESTAÑA 2 - REPORTE POR VENDEDOR
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="tab-content" id="reportesTabsContent">
                        <!-- Pestaña 1: Por Cliente -->
                        <div class="tab-pane fade show active" id="panel-reporte-cliente" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <p class="text-muted m-0 small">
                                    <i class="fa-solid fa-circle-info me-1 text-info"></i> Identifique clientes con materiales pendientes y mayor antigüedad de préstamo/salida.
                                </p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle width-full" id="tableReporteCliente">
                                    <thead class="table-dark" style="font-size: 0.85rem;">
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Motivo de Salida</th>
                                            <th class="text-center">Total Pases</th>
                                            <th class="text-center">Pendientes</th>
                                            <th class="text-center">Entregados</th>
                                            <th class="text-center">Días Acumulados</th>
                                            <th class="text-center">Promedio Días</th>
                                            <th class="text-center">Pases &gt; 30 Días</th>
                                        </tr>
                                    </thead>
                                    <tbody style="font-size: 0.88rem;">
                                        <!-- Cargado vía AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pestaña 2: Por Vendedor -->
                        <div class="tab-pane fade" id="panel-reporte-vendedor" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <p class="text-muted m-0 small">
                                    <i class="fa-solid fa-circle-info me-1 text-info"></i> Control y seguimiento de materiales entregados o prestados por cada asesor comercial.
                                </p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle width-full" id="tableReporteVendedor">
                                    <thead class="table-dark" style="font-size: 0.85rem;">
                                        <tr>
                                            <th>Vendedor</th>
                                            <th>Motivo de Salida</th>
                                            <th class="text-center">Total Pases</th>
                                            <th class="text-center">Pendientes</th>
                                            <th class="text-center">Entregados</th>
                                            <th class="text-center">Días Acumulados</th>
                                            <th class="text-center">Promedio Días</th>
                                            <th class="text-center">Pases &gt; 30 Días</th>
                                        </tr>
                                    </thead>
                                    <tbody style="font-size: 0.88rem;">
                                        <!-- Cargado vía AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</section>

<!-- ======================================================== -->
<!-- MODAL 1: REGISTRAR ENTREGA Y FIRMA DIGITAL              -->
<!-- ======================================================== -->
<div class="modal fade" id="modalRegistrarEntrega" tabindex="-1" aria-labelledby="modalEntregaTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalEntregaTitle">
                    <i class="fa-solid fa-signature me-2 text-warning"></i> Registrar Entrega / Firma de Recibido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form id="formRegistrarEntrega" onsubmit="return false;">
                    <input type="hidden" id="modal_entrega_pase_id" value="">
                    
                    <!-- Resumen del Pase -->
                    <div class="p-3 bg-white rounded border mb-3">
                        <div class="row g-2" style="font-size: 0.88rem;">
                            <div class="col-sm-3">
                                <span class="text-muted fw-semibold">Folio:</span>
                                <div id="modal_entrega_folio" class="fw-bold text-primary">-</div>
                            </div>
                            <div class="col-sm-5">
                                <span class="text-muted fw-semibold">Cliente:</span>
                                <div id="modal_entrega_cliente" class="fw-bold text-dark">-</div>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted fw-semibold">Proyecto:</span>
                                <div id="modal_entrega_proyecto" class="fw-bold text-dark">-</div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted fw-semibold">Motivo de Salida:</span>
                                <div id="modal_entrega_motivo" class="fw-bold text-dark">-</div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted fw-semibold">Fecha de Salida:</span>
                                <div id="modal_entrega_fecha" class="fw-bold text-dark">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Materiales / Partidas a Entregar -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.9rem;">
                            <i class="fa-solid fa-boxes-stacked text-secondary me-1"></i> Partidas / Materiales Incluidos en esta Entrega:
                        </label>
                        <div class="table-responsive bg-white rounded border" style="max-height: 160px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 0.82rem;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">#</th>
                                        <th>Material</th>
                                        <th class="text-center" style="width: 80px;">Cantidad</th>
                                        <th class="text-center" style="width: 70px;">Unidad</th>
                                    </tr>
                                </thead>
                                <tbody id="modal_entrega_tbody_partidas">
                                    <!-- Dinámico -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Captura de Usuario / Persona que Recibe con Select2 -->
                    <div class="mb-3">
                        <label for="selectUsuarioRecibe" class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-user-check text-success me-1"></i> Usuario / Persona que Recibe Física y Formalmente: <span class="text-danger">*</span>
                        </label>
                        <select id="selectUsuarioRecibe" class="form-select select2" style="width: 100%;" required>
                            <option value="">-- Seleccionar o escribir usuario que recibe --</option>
                            <?php if (!empty($data['usuarios_sistema'])): ?>
                                <?php foreach ($data['usuarios_sistema'] as $usr): ?>
                                    <option value="<?= htmlspecialchars($usr['usuario']); ?>"
                                            data-nombre="<?= htmlspecialchars($usr['nombre_completo']); ?>"
                                            data-clave="<?= htmlspecialchars($usr['ccveusuario']); ?>">
                                        <?= htmlspecialchars($usr['nombre_completo']); ?> (<?= htmlspecialchars($usr['usuario']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i> Seleccione un usuario de la lista o escriba el nombre/cargo del receptor.
                        </small>
                    </div>

                    <!-- Cuadro de Firma Digital -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-dark m-0">
                                <i class="fa-solid fa-pen-nib text-primary me-1"></i> Firma Digital de Recibido de Conformidad: <span class="text-danger">*</span>
                            </label>
                            <button type="button" id="btnLimpiarCanvasFirma" class="btn btn-sm btn-outline-danger py-0 px-2" title="Limpiar trazo para volver a firmar">
                                <i class="fa-solid fa-eraser me-1"></i> Borrar Firma
                            </button>
                        </div>
                        
                        <div class="signature-container p-1 shadow-sm">
                            <canvas id="canvasFirmaDigital" width="700" height="200" style="width: 100%; height: 200px; cursor: crosshair; display: block;"></canvas>
                        </div>
                        <small class="text-muted fst-italic">
                            <i class="fa-solid fa-hand-pointer me-1"></i> Dibuje su firma con el cursor del mouse, touchpad o directamente con el dedo en pantallas táctiles.
                        </small>
                    </div>


                </form>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between align-items-center py-2 px-4">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarGuardarEntrega" class="btn btn-success px-4 fw-bold">
                    <i class="fa-solid fa-check me-1"></i> Confirmar y Guardar Entrega
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: VISOR DE EVIDENCIAS Y ADJUNTOS MULTIMEDIA       -->
<!-- ======================================================== -->
<div class="modal fade" id="modalVisorEvidencias" tabindex="-1" aria-labelledby="modalVisorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalVisorTitle">
                    <i class="fa-solid fa-photo-film me-2 text-warning"></i> Visor de Evidencias de Entrega y Salida
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-dark position-relative visor-modal-body">
                <div class="row g-0 h-100 align-items-stretch">
                    <!-- Lista lateral de archivos -->
                    <div class="col-md-3 bg-light border-end p-3 visor-sidebar-col">
                        <h6 class="fw-bold text-primary mb-2 flex-shrink-0" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-folder-open me-1"></i> Archivos del Pase
                        </h6>
                        <div class="list-group list-group-flush visor-sidebar-list" id="visorListaArchivos">
                            <!-- Dinámico -->
                        </div>
                    </div>

                    <!-- Área de visualización interactiva -->
                    <div class="col-md-9 bg-dark text-white p-3 visor-display-col">
                        <div id="visorDisplayContainer" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center">
                            <span class="text-muted">Seleccione un archivo de la lista para previsualizarlo.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between">
                <div id="visorFileInfo" class="small text-muted fw-semibold"></div>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- FIN CONTENIDO VISTA -->

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<div id="loadModalPermisos"></div>

<!-- Scripts Reproductor Multimedia HTML5 (Video.js + Plyr Fallback) -->
<script src="<?= assets(); ?>/vendor/video-js/video.min.js"></script>
<script src="<?= assets(); ?>/vendor/video-js/es.js"></script>
<script src="<?= assets(); ?>/vendor/plyr/plyr.polyfilled.min.js"></script>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>
