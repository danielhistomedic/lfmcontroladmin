<?php /* View: Almacén - Reporte de Notas de Salida */ ?>
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
    .btn-preset-date {
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
        color: #cbd5e1;
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.15s ease;
    }
    .btn-preset-date:hover {
        background: #3b82f6;
        color: #fff;
        border-color: #3b82f6;
    }

    /* Estilos Tarjetas KPI */
    .kpi-card {
        border-radius: 10px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0,0,0,0.06);
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

    /* Badges de Estatus */
    .badge-status-contabilizada {
        background-color: #10b981;
        color: #ffffff;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
        border-radius: 20px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
    }
    .badge-status-en-proceso {
        background-color: #f59e0b;
        color: #1e1b4b;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
        border-radius: 20px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.25);
    }
    .badge-status-cancelada {
        background-color: #ef4444;
        color: #ffffff;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
        border-radius: 20px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.25);
    }

    /* Tabla y Detalle */
    #tableNotasSalida th, #tableNotasSalida td {
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
                    <a href="<?= base_url(); ?>/inicio">
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
            <!-- 1. ENCABEZADO CON FILTROS DE INFORMACIÓN PARA EL JEFE    -->
            <!-- ======================================================== -->
            <div class="filter-box-container mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-filter text-primary fs-4 me-2"></i>
                        <h4 class="m-0 text-white fw-bold">Filtros de Notas de Salida de Almacén</h4>
                    </div>
                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-2" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-boxes-packing me-1"></i> Control de salidas que afectan inventarios y firmas de conformidad
                    </span>
                </div>

                <form id="formFiltrosNotas" onsubmit="return false;">
                    <div class="row g-2 align-items-end">

                        <!-- Filtro Estatus -->
                        <div class="col-md-2 col-sm-6">
                            <label for="selectFiltroEstatus" class="filter-label">
                                <i class="fa-solid fa-traffic-light me-1 text-warning"></i> Estatus:
                            </label>
                            <select id="selectFiltroEstatus" class="form-select filter-control">
                                <option value="">-- Todos --</option>
                                <option value="CONTABILIZADA">🟢 Contabilizada</option>
                                <option value="EN PROCESO">🟡 En Proceso</option>
                                <option value="CANCELADA">🔴 Cancelada</option>
                            </select>
                        </div>

                        <!-- Filtro Firma Digital -->
                        <div class="col-md-2 col-sm-6">
                            <label for="selectFiltroFirma" class="filter-label">
                                <i class="fa-solid fa-signature me-1 text-info"></i> Firma Digital:
                            </label>
                            <select id="selectFiltroFirma" class="form-select filter-control">
                                <option value="">-- Todas --</option>
                                <option value="CON_FIRMA">✅ Con Firma Registrada</option>
                                <option value="SIN_FIRMA">⏳ Pendientes de Firma</option>
                            </select>
                        </div>

                        <!-- Filtro Almacén Origen -->
                        <div class="col-md-3 col-sm-6">
                            <label for="selectFiltroAlmacen" class="filter-label">
                                <i class="fa-solid fa-warehouse me-1 text-primary"></i> Almacén Origen:
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

                        <!-- Filtro Cliente / Destino -->
                        <div class="col-md-3 col-sm-6">
                            <label for="txtFiltroCliente" class="filter-label">
                                <i class="fa-solid fa-building me-1 text-info"></i> Cliente / Destino:
                            </label>
                            <input type="text" 
                                   id="txtFiltroCliente" 
                                   class="form-control filter-control" 
                                   placeholder="Escriba cliente o razón social..." 
                                   list="datalistClientesNotas" 
                                   autocomplete="off">
                            <datalist id="datalistClientesNotas">
                                <?php if (!empty($data['clientes'])): ?>
                                    <?php foreach ($data['clientes'] as $cli): ?>
                                        <option value="<?= htmlspecialchars($cli['nombre_cliente']); ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </datalist>
                        </div>

                        <!-- Filtro Tipo de Documento -->
                        <div class="col-md-2 col-sm-6">
                            <label for="selectFiltroTipoDocto" class="filter-label">
                                <i class="fa-solid fa-file-lines me-1 text-secondary"></i> Tipo Documento:
                            </label>
                            <select id="selectFiltroTipoDocto" class="form-select filter-control">
                                <option value="">-- Todos --</option>
                                <?php if (!empty($data['tipos_docto'])): ?>
                                    <?php foreach ($data['tipos_docto'] as $td): ?>
                                        <option value="<?= htmlspecialchars($td['id']); ?>">
                                            <?= htmlspecialchars($td['nombre']); ?>
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

                        <!-- Búsqueda General -->
                        <div class="col-md-4 col-sm-8">
                            <label for="txtFiltroBusqueda" class="filter-label">
                                <i class="fa-solid fa-magnifying-glass me-1 text-info"></i> Folio, Documento Salida / PO, Persona:
                            </label>
                            <input type="text" id="txtFiltroBusqueda" class="form-control filter-control" placeholder="Ej. NS-0001-00004, PO450..., Armando...">
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

                        <!-- Presets rápidos de fechas -->
                        <div class="col-12 mt-2 pt-1 border-top border-secondary border-opacity-25 d-flex align-items-center gap-1 flex-wrap">
                            <small class="text-white-50 me-2"><i class="fa-regular fa-clock me-1"></i> Accesos rápidos de fecha:</small>
                            <button type="button" class="btn btn-preset-date" onclick="aplicarPresetFecha('hoy')">Hoy</button>
                            <button type="button" class="btn btn-preset-date" onclick="aplicarPresetFecha('semana')">Esta Semana</button>
                            <button type="button" class="btn btn-preset-date" onclick="aplicarPresetFecha('mes')">Este Mes</button>
                            <button type="button" class="btn btn-preset-date" onclick="aplicarPresetFecha('30dias')">Últimos 30 días</button>
                            <button type="button" class="btn btn-preset-date" onclick="aplicarPresetFecha('anio')">Todo el Año</button>
                        </div>

                    </div>
                </form>
            </div>

            <!-- ======================================================== -->
            <!-- 2. DASHBOARD KPI: INDICADORES CLAVE DE DESEMPEÑO         -->
            <!-- ======================================================== -->
            <div class="row mb-4" id="panel_kpis">
                <div class="form-group col-12 mb-3">
                    <div class="border-bottom pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <p class="mb-0 fw-semibold text-primary" style="font-size: 1.05rem;">
                            <i class="fa-regular fa-chart-simple me-2 text-secondary"></i> Indicadores de Control de Salidas de Almacén
                        </p>
                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.82rem;">
                            <i class="fa-solid fa-boxes-stacked text-primary me-1"></i> Afectación de Inventarios y Trazabilidad
                        </span>
                    </div>
                </div>

                <div class="row g-3" id="container_kpi_cards">
                    <!-- KPI 1: Total Notas -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-card shadow-sm border-0 bg-primary-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-primary text-white me-3">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div>
                                    <div class="text-label text-primary">Total Salidas</div>
                                    <div class="text-amount text-dark" id="kpi_total_notas">0</div>
                                    <small class="text-muted">Notas de salida</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 2: Contabilizadas (Afectan Inventario) -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card kpi-card shadow-sm border-0 bg-success-lighten h-100 border-start border-4 border-success">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-success text-white me-3" style="background-color: #10b981 !important;">
                                    <i class="fa-solid fa-check-double"></i>
                                </div>
                                <div>
                                    <div class="text-label text-success">Contabilizadas</div>
                                    <div class="text-amount text-dark" id="kpi_contabilizadas">0</div>
                                    <small class="text-success fw-bold">🟢 Afectaron Inventario</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 3: En Proceso -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-warning-lighten h-100 border-start border-4 border-warning">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-warning text-dark me-2" style="background-color: #f59e0b !important; color: #fff !important;">
                                    <i class="fa-solid fa-hourglass-half"></i>
                                </div>
                                <div>
                                    <div class="text-label text-dark">En Proceso</div>
                                    <div class="text-amount text-dark" id="kpi_en_proceso">0</div>
                                    <small class="text-warning fw-bold" style="color: #b45309 !important;">🟡 Pendientes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 4: Canceladas -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-danger-lighten h-100 border-start border-4 border-danger">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-danger text-white me-2" style="background-color: #ef4444 !important;">
                                    <i class="fa-solid fa-ban"></i>
                                </div>
                                <div>
                                    <div class="text-label text-danger">Canceladas</div>
                                    <div class="text-amount text-danger" id="kpi_canceladas">0</div>
                                    <small class="text-danger fw-bold">🔴 Anuladas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 5: Firmadas vs Pendientes de Firma -->
                    <div class="col-sm-6 col-xl-2">
                        <div class="card kpi-card shadow-sm border-0 bg-info-lighten h-100">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="kpi-icon bg-info text-white me-2">
                                    <i class="fa-solid fa-signature"></i>
                                </div>
                                <div>
                                    <div class="text-label text-info">Con Firma</div>
                                    <div class="text-amount text-dark" id="kpi_con_firma">0</div>
                                    <small class="text-muted"><span id="kpi_pendientes_firma" class="text-warning fw-bold">0</span> sin firmar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- 3. RESUMEN Y ANÁLISIS EJECUTIVO PARA JEFE DE ALMACÉN    -->
            <!-- ======================================================== -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-chart-line text-primary me-2 fs-5"></i>
                        <h5 class="m-0 fw-bold text-dark">Monitoreo Ejecutivo para el Jefe de Almacén</h5>
                    </div>
                    <div>
                        <span class="badge bg-warning-lighten text-dark border border-warning px-3 py-1 fw-bold" id="badge_alertas_almacen">
                            <i class="fa-solid fa-bell me-1 text-warning"></i> 0 Salidas Requieren Atención
                        </span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <!-- Clientes / Destinos con Más Salidas -->
                        <div class="col-md-4 col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold text-primary mb-2 border-bottom pb-1" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-building me-1 text-secondary"></i> Clientes / Destinos con Más Movimiento
                                </h6>
                                <ul class="list-unstyled mb-0" id="list_top_clientes_notas" style="font-size: 0.85rem;">
                                    <li class="text-muted fst-italic">Cargando datos...</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Almacenes Emisores -->
                        <div class="col-md-4 col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold text-primary mb-2 border-bottom pb-1" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-warehouse me-1 text-secondary"></i> Almacenes con Mayor Emisión
                                </h6>
                                <ul class="list-unstyled mb-0" id="list_top_almacenes_notas" style="font-size: 0.85rem;">
                                    <li class="text-muted fst-italic">Cargando datos...</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Salidas Pendientes de Atención (Sin firma o en proceso) -->
                        <div class="col-md-4 col-sm-12">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold text-danger mb-2 border-bottom pb-1" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-triangle-exclamation me-1 text-danger"></i> Salidas Pendientes de Firma o Proceso
                                </h6>
                                <ul class="list-unstyled mb-0" id="list_alertas_notas" style="font-size: 0.85rem;">
                                    <li class="text-muted fst-italic">Cargando datos...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- 4. LISTADO PRINCIPAL DE NOTAS DE SALIDA (DATATABLES)    -->
            <!-- ======================================================== -->
            <section class="card card-featured card-featured-primary shadow-sm mb-4">
                <header class="card-header d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
                    <h2 class="card-title m-0">
                        <i class="fa-solid fa-boxes-stacked me-2 text-primary"></i> <?= $data['page_card_title']; ?>
                    </h2>
                    <span class="badge bg-light text-muted border">
                        <i class="fa-solid fa-hand-pointer me-1"></i> Haga clic en cualquier fila para ver el desglose inferior
                    </span>
                </header>

                <div class="p-4 card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover width-full" id="tableNotasSalida">
                            <thead class="table-dark">
                                <tr>
                                    <!-- Requerimiento 2: Dos columnas al inicio -->
                                    <th class="text-center" style="width: 135px;">Estatus</th>
                                    <th class="text-center" style="width: 120px;">Opciones</th>

                                    <!-- Datos de la Salida -->
                                    <th class="text-center" style="width: 110px;">Folio</th>
                                    <th class="text-center" style="width: 95px;">Fecha Salida</th>
                                    <th>Tipo Salida</th>
                                    <th>Almacén Origen</th>
                                    <th>Cliente / Destino</th>
                                    <th>Documento Salida</th>
                                    <th>Solicitó / Entregó</th>
                                    <th>Receptor / Firma</th>
                                    <th class="text-center" style="width: 90px;">Partidas / Piezas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Cargado vía AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 5. PANEL DE DETALLE INFERIOR DE LA NOTA SELECCIONADA    -->
            <!-- ======================================================== -->
            <div id="panelDetalleNota" class="card shadow-sm border-0 mb-4" style="display: none;">
                <div class="detail-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-file-invoice text-warning fs-5 me-2"></i>
                        <h5 class="m-0 text-white fw-bold">
                            Detalle de la Nota de Salida: <span id="det_folio_title" class="badge bg-primary fs-6 ms-1">-</span>
                        </h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span id="det_estatus_badge"></span>
                        <span id="det_firma_badge"></span>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cerrarPanelDetalleNota()" title="Cerrar panel de detalle">
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
                                    <i class="fa-solid fa-circle-info me-1"></i> Información General de la Salida de Almacén
                                </h6>
                                <div class="row g-2" style="font-size: 0.9rem;">
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Folio Nota:</span>
                                        <div id="det_folio" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Fecha de Salida:</span>
                                        <div id="det_fecha" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Almacén Origen:</span>
                                        <div id="det_almacen_origen" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Almacén Destino:</span>
                                        <div id="det_almacen_destino" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Cliente:</span>
                                        <div id="det_cliente" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Proyecto / Venta:</span>
                                        <div id="det_proyecto" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Tipo Documento:</span>
                                        <div id="det_tipo_docto" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Documento de Salida (PO/Ref):</span>
                                        <div id="det_num_docto" class="fw-bold text-primary">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Solicitado por:</span>
                                        <div id="det_solicita" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Entregado por:</span>
                                        <div id="det_entrega" class="fw-bold text-dark">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Control de Recepción y Firma -->
                        <div class="col-lg-5">
                            <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                                        <i class="fa-solid fa-signature me-1"></i> Estado de la Firma Digital de Recibido
                                    </h6>
                                    <div class="mb-2" style="font-size: 0.9rem;">
                                        <span class="text-muted fw-semibold">Persona / Receptor:</span>
                                        <div id="det_persona_recibe" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="mb-3" style="font-size: 0.9rem;">
                                        <span class="text-muted fw-semibold">Fecha y Hora de Firma:</span>
                                        <div id="det_fecha_firma" class="fw-bold text-dark">-</div>
                                    </div>
                                    <div class="text-center p-2 bg-white rounded border" id="container_firma_preview">
                                        <span class="text-muted fst-italic">Sin firma registrada</span>
                                    </div>
                                </div>
                                <div class="mt-3 text-end" id="container_btn_firmar_detalle">
                                    <!-- Botón dinámico para firmar si aún no está firmada -->
                                </div>
                            </div>
                        </div>

                        <!-- Partidas y Materiales Afectados en Inventario -->
                        <div class="col-12">
                            <div class="card shadow-none border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="m-0 fw-bold text-dark">
                                        <i class="fa-solid fa-boxes-stacked me-1 text-primary"></i> Partidas y Materiales Afectados en Inventario
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped table-bordered mb-0" id="tableDetallePartidasNota">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th class="text-center" style="width: 40px;">#</th>
                                                    <th class="text-center" style="width: 100px;">Clave</th>
                                                    <th class="text-center" style="width: 110px;">CCN</th>
                                                    <th>Descripción del Material</th>
                                                    <th class="text-center" style="width: 80px;">Cantidad</th>
                                                    <th class="text-center" style="width: 70px;">Unidad</th>
                                                    <th class="text-center" style="width: 100px;">Lote</th>
                                                    <th class="text-center" style="width: 100px;">Serie</th>
                                                    <th class="text-end" style="width: 110px;">Costo Unit.</th>
                                                    <th class="text-end" style="width: 110px;">Importe</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyPartidasNota">
                                                <!-- Cargado dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ======================================================== -->
<!-- MODAL: REGISTRAR FIRMA DIGITAL DE SALIDA DE ALMACÉN      -->
<!-- ======================================================== -->
<div class="modal fade" id="modalRegistrarFirmaNota" tabindex="-1" aria-labelledby="modalFirmaNotaTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalFirmaNotaTitle">
                    <i class="fa-solid fa-signature me-2 text-warning"></i> Registrar Firma de Recibido / Entrega de Salida
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form id="formRegistrarFirmaNota" onsubmit="return false;">
                    <input type="hidden" id="modal_nota_id" value="">

                    <!-- Resumen de la Nota -->
                    <div class="p-3 bg-white rounded border mb-3">
                        <div class="row g-2" style="font-size: 0.88rem;">
                            <div class="col-sm-4">
                                <span class="text-muted fw-semibold">Folio:</span>
                                <div id="modal_folio_nota" class="fw-bold text-primary">-</div>
                            </div>
                            <div class="col-sm-8">
                                <span class="text-muted fw-semibold">Cliente / Destino:</span>
                                <div id="modal_cliente_nota" class="fw-bold text-dark">-</div>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted fw-semibold">Fecha Salida:</span>
                                <div id="modal_fecha_nota" class="fw-bold text-dark">-</div>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted fw-semibold">Almacén Origen:</span>
                                <div id="modal_almacen_nota" class="fw-bold text-dark">-</div>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted fw-semibold">Documento Salida:</span>
                                <div id="modal_num_docto_nota" class="fw-bold text-primary">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Materiales / Partidas a Entregar -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.9rem;">
                            <i class="fa-solid fa-boxes-stacked text-secondary me-1"></i> Partidas Incluidas en esta Salida:
                        </label>
                        <div class="table-responsive bg-white rounded border" style="max-height: 150px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 0.82rem;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">#</th>
                                        <th>Material</th>
                                        <th class="text-center" style="width: 80px;">Cantidad</th>
                                        <th class="text-center" style="width: 70px;">Unidad</th>
                                    </tr>
                                </thead>
                                <tbody id="modal_tbody_partidas_nota">
                                    <!-- Dinámico -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Captura de Usuario / Persona que Recibe -->
                    <div class="mb-3">
                        <label for="selectUsuarioRecibeNota" class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-user-check text-success me-1"></i> Usuario / Persona que Recibe la Mercancía: <span class="text-danger">*</span>
                        </label>
                        <select id="selectUsuarioRecibeNota" class="form-select form-select-md shadow-sm" style="width: 100%; font-size: 0.95rem;" required>
                            <option value="">-- Seleccionar usuario del sistema que recibe --</option>
                            <option value="__OTRO__">✍️ [OTRO] Escribir nombre de receptor externo / cliente...</option>
                            <?php if (!empty($data['usuarios_sistema'])): ?>
                                <?php foreach ($data['usuarios_sistema'] as $usr): ?>
                                    <option value="<?= htmlspecialchars($usr['ccvemedico'] ?? $usr['ccveusuario']); ?>"
                                            data-clave="<?= htmlspecialchars($usr['ccvemedico'] ?? $usr['ccveusuario']); ?>"
                                            data-usuario="<?= htmlspecialchars($usr['usuario'] ?? ''); ?>"
                                            data-nombre="<?= htmlspecialchars($usr['nombre_completo'] ?? ''); ?>"
                                            data-email="<?= htmlspecialchars($usr['email'] ?? ''); ?>">
                                        <?= htmlspecialchars($usr['nombre_completo']); ?> (<?= htmlspecialchars($usr['usuario'] ?? $usr['ccvemedico']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>

                        <!-- Entrada para especificar receptor externo cuando se elija [OTRO] -->
                        <div id="containerNombreRecibeOtroNota" class="mt-2" style="display: none;">
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary border-end-0">
                                    <i class="fa-solid fa-user-pen"></i>
                                </span>
                                <input type="text" id="inputNombreRecibeOtroNota" class="form-control border-start-0" placeholder="Escriba el nombre completo y/o cargo del receptor externo...">
                            </div>
                            <small class="text-muted fst-italic">Ingrese el nombre completo del receptor que firmará de conformidad.</small>
                        </div>
                    </div>

                    <!-- Cuadro de Firma Digital -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-dark m-0">
                                <i class="fa-solid fa-pen-nib text-primary me-1"></i> Firma Digital de Conformidad: <span class="text-danger">*</span>
                            </label>
                            <button type="button" id="btnLimpiarCanvasFirmaNota" class="btn btn-sm btn-outline-danger py-1 px-3 d-inline-flex align-items-center gap-1 shadow-sm" onclick="limpiarCanvasFirmaNota();" title="Limpiar trazo para volver a firmar">
                                <i class="fa-solid fa-eraser"></i> <span>Borrar Firma</span>
                            </button>
                        </div>

                        <div class="signature-container p-1 shadow-sm">
                            <canvas id="canvasFirmaDigitalNota" width="700" height="200" style="width: 100%; height: 200px; cursor: crosshair; display: block;"></canvas>
                        </div>
                        <small class="text-muted fst-italic">
                            <i class="fa-solid fa-hand-pointer me-1"></i> Dibuje su firma con el cursor del mouse, touchpad o directamente con el dedo en pantallas táctiles.
                        </small>
                    </div>

                </form>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between align-items-center py-2 px-4">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarGuardarFirmaNota" class="btn btn-success px-4 fw-bold">
                    <i class="fa-solid fa-check me-1"></i> Confirmar y Guardar Firma
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<div id="loadModalPermisos"></div>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>
