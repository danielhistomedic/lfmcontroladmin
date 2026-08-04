/*==================================================================
[ Variables ]*/
let fecha_ini_send;
let fecha_fin_send;

const divLoading = document.querySelector('.loading-panel');
let activeRequests = 0;

function showLoader() {
    activeRequests++;
    if (divLoading) {
        divLoading.style.display = 'flex';
    }
}

function hideLoader() {
    activeRequests--;
    if (activeRequests <= 0) {
        activeRequests = 0;
        if (divLoading) {
            divLoading.style.display = 'none';
        }
    }
}

// Initialise ECharts instances
let chartVentasVsPipeline = null;
let chartVentasTendencia = null;
let chartVentasVendedor = null;
let chartVentasCliente = null;
let chartVentasClasificacion = null;
let chartVentasEstatus = null;

/*==================================================================
[ DOMContentLoaded ]*/
document.addEventListener("DOMContentLoaded", function (event) {

    // Initialize ECharts instances
    const elVsPipeline = document.getElementById('chart_ventas_vs_pipeline');
    const elTendencia = document.getElementById('chart_ventas_tendencia');
    const elVendedor = document.getElementById('chart_ventas_vendedor');
    const elCliente = document.getElementById('chart_ventas_cliente');
    const elClasificacion = document.getElementById('chart_ventas_clasificacion');
    const elEstatus = document.getElementById('chart_ventas_estatus');

    if (elVsPipeline) chartVentasVsPipeline = echarts.init(elVsPipeline);
    if (elTendencia) chartVentasTendencia = echarts.init(elTendencia);
    if (elVendedor) chartVentasVendedor = echarts.init(elVendedor);
    if (elCliente) chartVentasCliente = echarts.init(elCliente);
    if (elClasificacion) chartVentasClasificacion = echarts.init(elClasificacion);
    if (elEstatus) chartVentasEstatus = echarts.init(elEstatus);

    /*-------------------------------------------
    [ Form Submit Event ]*/
    const formReporteVentas = document.getElementById('formReporteVentas');
    if (formReporteVentas) {
        formReporteVentas.addEventListener("submit", function (event) {
            getReporteGeneralVentas(event);
        });
    }

    // Default Date Range: Jan 1st of current year to Dec 31st of current year (or today)
    const currentYear = new Date().getFullYear();
    const inputIni = document.getElementById("inputFechaIniPeriodo");
    const inputFin = document.getElementById("inputFechaFinPeriodo");

    if (inputIni && !inputIni.value) {
        inputIni.value = `01/01/${currentYear}`;
    }
    if (inputFin && !inputFin.value) {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        inputFin.value = `${dd}/${mm}/${yyyy}`;
    }

    // Event listener para el botón de pedidos colocados
    const btnModalPedidos = document.getElementById('btn_modal_pedidos_colocados');
    if (btnModalPedidos) {
        btnModalPedidos.addEventListener('click', function () {
            openModalPedidosColocados();
        });
    }

    // Event listener para el botón de pedidos cotizados
    const btnModalCotizados = document.getElementById('btn_modal_pedidos_cotizados');
    if (btnModalCotizados) {
        btnModalCotizados.addEventListener('click', function () {
            openModalPedidosCotizados();
        });
    }

    // Event listener para el botón de clientes activos
    const btnModalClientes = document.getElementById('btn_modal_clientes_activos');
    if (btnModalClientes) {
        btnModalClientes.addEventListener('click', function () {
            openModalClientesActivos();
        });
    }

    // Load initial report
    triggerLoadReport();
});

/*==================================================================
[ Window Resize Responsiveness ]*/
window.addEventListener('resize', function () {
    if (chartVentasVsPipeline) chartVentasVsPipeline.resize();
    if (chartVentasTendencia) chartVentasTendencia.resize();
    if (chartVentasVendedor) chartVentasVendedor.resize();
    if (chartVentasCliente) chartVentasCliente.resize();
    if (chartVentasClasificacion) chartVentasClasificacion.resize();
    if (chartVentasEstatus) chartVentasEstatus.resize();
});

/*==================================================================
[ Load Controller Report Data ]*/
function triggerLoadReport() {
    const inputIni = document.getElementById("inputFechaIniPeriodo");
    const inputFin = document.getElementById("inputFechaFinPeriodo");

    if (inputIni && inputFin && inputIni.value && inputFin.value) {
        fecha_ini_send = Formato_Fecha_yyyymmdd(inputIni.value);
        fecha_fin_send = Formato_Fecha_yyyymmdd(inputFin.value);

        activeRequests = 0; // reset
        loadKPIs();
        loadVentasVsPipeline();
        loadTendencia();
        loadVendedores();
        loadClientes();
        loadClasificacion();
        loadEstatus();
        loadTopProductos();
    }
}

function getReporteGeneralVentas(e) {
    e.preventDefault();
    triggerLoadReport();
}

/*-------------------------------------------
[ Formatters ]*/
function formatUSD(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value) + ' USD';
}

function formatMXN(value) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value) + ' MXN';
}

function formatNumber(value) {
    return new Intl.NumberFormat('en-US').format(value);
}

/*-------------------------------------------
[ AJAX 1: KPI Cards ]*/
function loadKPIs() {
    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getTotalVentas', function (responseObj) {
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data) {
            const d = responseObj.data;

            // Won sales KPIs
            const combinedUSD = parseFloat(d.sum_ganadas_combined_usd || 0);
            const combinedMXN = parseFloat(d.sum_ganadas_combined_mxn || 0);
            const countGanadas = parseInt(d.count_ganadas || 0);
            const avgUSD = countGanadas > 0 ? (combinedUSD / countGanadas) : 0;

            document.getElementById('lbl_ganadas_usd_combined').innerHTML = formatUSD(combinedUSD);
            document.getElementById('lbl_ganadas_mxn_combined').innerHTML = formatMXN(combinedMXN);
            document.getElementById('lbl_ganadas_cantidad').innerHTML = formatNumber(countGanadas);
            document.getElementById('lbl_ganadas_aov_usd').innerHTML = formatUSD(avgUSD);

            // Pipeline KPIs
            const pipelineUSD = parseFloat(d.sum_pipeline_combined_usd || 0);
            const pipelineMXN = parseFloat(d.sum_pipeline_combined_mxn || 0);
            const countPipeline = parseInt(d.count_pipeline || 0);

            document.getElementById('lbl_pipeline_usd_combined').innerHTML = formatUSD(pipelineUSD);
            document.getElementById('lbl_pipeline_mxn_combined').innerHTML = formatMXN(pipelineMXN);
            document.getElementById('lbl_pipeline_cantidad').innerHTML = formatNumber(countPipeline);

            // Porcentaje de efectividad (Total Ventas / Pipeline Activo)
            const pctEfectividad = parseFloat(d.PorcentajeEfectividad ?? (pipelineUSD > 0 ? ((combinedUSD / pipelineUSD) * 100) : 0));
            const elEfectividad = document.getElementById('lbl_efectividad_porcentaje');
            if (elEfectividad) {
                elEfectividad.innerHTML = pctEfectividad.toFixed(2) + '%';
            }

            // Clients & Items sold
            const clientsCount = parseInt(d.count_clientes_activos || 0);
            const itemsCount = parseFloat(d.total_articulos_vendidos || 0);

            document.getElementById('lbl_clientes_activos').innerHTML = formatNumber(clientsCount);
            document.getElementById('lbl_articulos_vendidos').innerHTML = formatNumber(itemsCount);

            // --- Meta Global (USD) comparativo ---
            const metaGlobal = parseFloat(d.MetaGlobalUSD || 0);
            const pctCumplimiento = parseFloat(d.PorcentajeCumplimiento || 0);
            const faltante = parseFloat(d.FaltanteUSD || 0);

            document.getElementById('lbl_meta_ventas_usd').innerHTML = formatUSD(combinedUSD);
            document.getElementById('lbl_meta_global_usd').innerHTML = formatUSD(metaGlobal);

            // Faltante: color rojo si positivo (falta), verde si ya se superó la meta
            const faltanteEl = document.getElementById('lbl_meta_faltante_usd');
            if (faltante <= 0) {
                faltanteEl.innerHTML = '<span class="text-success">' + formatUSD(Math.abs(faltante)) + ' (superado)</span>';
            } else {
                faltanteEl.innerHTML = '<span class="text-danger">' + formatUSD(faltante) + '</span>';
            }

            // Porcentaje
            const pctDisplay = pctCumplimiento.toFixed(2) + '%';
            document.getElementById('lbl_meta_porcentaje').innerHTML = pctDisplay;
            document.getElementById('lbl_meta_porcentaje_bar').innerHTML = pctDisplay;

            // Barra de progreso (cap en 100% visual)
            const pctBar = Math.min(pctCumplimiento, 100);
            let barColor = 'linear-gradient(90deg, #CC4F4F, #e8836e)'; // rojo < 50%
            if (pctCumplimiento >= 80) {
                barColor = 'linear-gradient(90deg, #28a745, #5dd878)'; // verde >= 80%
            } else if (pctCumplimiento >= 50) {
                barColor = 'linear-gradient(90deg, #f5b800, #f7d461)'; // amarillo 50-79%
            }
            const barEl = document.getElementById('bar_meta_progreso');
            barEl.style.width = pctBar + '%';
            barEl.style.background = barColor;
            barEl.setAttribute('aria-valuenow', pctBar);

        } else {
            // Reset to zeroes
            document.getElementById('lbl_ganadas_usd_combined').innerHTML = '$0.00 USD';
            document.getElementById('lbl_ganadas_mxn_combined').innerHTML = '$0.00 MXN';
            document.getElementById('lbl_ganadas_cantidad').innerHTML = '0';
            document.getElementById('lbl_ganadas_aov_usd').innerHTML = '$0.00 USD';
            const elEfectividad = document.getElementById('lbl_efectividad_porcentaje');
            if (elEfectividad) elEfectividad.innerHTML = '0.00%';
            document.getElementById('lbl_pipeline_usd_combined').innerHTML = '$0.00 USD';
            document.getElementById('lbl_pipeline_mxn_combined').innerHTML = '$0.00 MXN';
            document.getElementById('lbl_pipeline_cantidad').innerHTML = '0';
            document.getElementById('lbl_clientes_activos').innerHTML = '0';
            document.getElementById('lbl_articulos_vendidos').innerHTML = '0';
            // Meta reset
            document.getElementById('lbl_meta_ventas_usd').innerHTML = '$0.00 USD';
            document.getElementById('lbl_meta_global_usd').innerHTML = '$0.00 USD';
            document.getElementById('lbl_meta_faltante_usd').innerHTML = '$0.00 USD';
            document.getElementById('lbl_meta_porcentaje').innerHTML = '0.00%';
            document.getElementById('lbl_meta_porcentaje_bar').innerHTML = '0%';
            document.getElementById('bar_meta_progreso').style.width = '0%';
        }
        hideLoader();
    });
}

/*-------------------------------------------
[ AJAX 1.5: Ventas vs Pipeline Chart ]*/
function loadVentasVsPipeline() {
    if (!chartVentasVsPipeline) return;
    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getVentasVsPipeline', function (responseObj) {
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const data = responseObj.data;
            const dates = data.map(item => item.fecha_grupo);
            const ventasAmounts = data.map(item => parseFloat(item.sum_ventas_usd || 0));
            const pipelineAmounts = data.map(item => parseFloat(item.sum_pipeline_usd || 0));

            chartVentasVsPipeline.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'cross' },
                    formatter: function (params) {
                        let res = `<b>${params[0].name}</b><br/>`;
                        params.forEach(item => {
                            res += `${item.marker} ${item.seriesName}: <b>${formatUSD(item.value)}</b><br/>`;
                        });
                        return res;
                    }
                },
                legend: {
                    data: ['Total de Ventas (Pedidos Colocados)', 'Pipeline Activo (Pedidos Cotizados)']
                },
                grid: {
                    left: '3%', right: '4%', bottom: '3%', containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: dates,
                        axisPointer: { type: 'shadow' }
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: 'Monto (USD)',
                        axisLabel: { formatter: '${value}' }
                    }
                ],
                series: [
                    {
                        name: 'Total de Ventas (Pedidos Colocados)',
                        type: 'bar',
                        data: ventasAmounts,
                        itemStyle: { color: '#28a745' }
                    },
                    {
                        name: 'Pipeline Activo (Pedidos Cotizados)',
                        type: 'bar',
                        data: pipelineAmounts,
                        itemStyle: { color: '#00809F' }
                    }
                ]
            }, true);
        } else {
            chartVentasVsPipeline.clear();
            chartVentasVsPipeline.setOption({
                title: { text: 'No hay datos en el periodo', left: 'center', top: 'center' }
            });
        }
        hideLoader();
    });
}

/*-------------------------------------------
[ AJAX 2: Tendencia Chart ]*/
function loadTendencia() {
    if (!chartVentasTendencia) return;
    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getVentasTendencia', function (responseObj) {
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const data = responseObj.data;
            const dates = data.map(item => item.fecha_grupo);
            const facturadoAmounts = data.map(item => parseFloat(item.sum_facturado_usd || 0));
            const pagadoAmounts = data.map(item => parseFloat(item.sum_pagado_usd || 0));

            chartVentasTendencia.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'cross' },
                    formatter: function (params) {
                        let res = `<b>${params[0].name}</b><br/>`;
                        params.forEach(item => {
                            res += `${item.marker} ${item.seriesName}: <b>${formatUSD(item.value)}</b><br/>`;
                        });
                        return res;
                    }
                },
                legend: {
                    data: ['Total Facturado (USD)', 'Valor Pagado (USD)']
                },
                grid: {
                    left: '3%', right: '4%', bottom: '3%', containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: dates,
                        axisPointer: { type: 'shadow' }
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: 'Monto (USD)',
                        axisLabel: { formatter: '${value}' }
                    }
                ],
                series: [
                    {
                        name: 'Total Facturado (USD)',
                        type: 'bar',
                        data: facturadoAmounts,
                        itemStyle: { color: '#CC4F4F' }
                    },
                    {
                        name: 'Valor Pagado (USD)',
                        type: 'line',
                        data: pagadoAmounts,
                        itemStyle: { color: '#198754' },
                        lineStyle: { width: 3 },
                        symbolSize: 6
                    }
                ]
            }, true);
        } else {
            chartVentasTendencia.clear();
            chartVentasTendencia.setOption({
                title: { text: 'No hay datos en el periodo', left: 'center', top: 'center' }
            });
        }
        hideLoader();
    });
}

/*-------------------------------------------
[ AJAX 3: Vendedores Chart ]*/
function loadVendedores() {
    if (!chartVentasVendedor) return;
    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getVentasPorVendedor', function (responseObj) {
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const data = responseObj.data;
            const dataReversed = [...data].reverse(); // Reverse for horizontal bar ranking
            const names = dataReversed.map(item => item.vendedor || 'Sin Nombre');
            const totals = dataReversed.map(item => parseFloat(item.sum_combined_usd || 0));

            chartVentasVendedor.setOption({
                tooltip: {
                    trigger: 'axis',
                    formatter: function (params) {
                        const item = params[0];
                        return `${item.name}<br/>Monto: <b>${formatUSD(item.value)}</b>`;
                    }
                },
                grid: {
                    left: '3%', right: '4%', bottom: '3%', containLabel: true
                },
                xAxis: {
                    type: 'value',
                    axisLabel: { formatter: '${value}' }
                },
                yAxis: {
                    type: 'category',
                    data: names,
                    axisLabel: { interval: 0 }
                },
                series: [
                    {
                        name: 'Ventas (USD)',
                        type: 'bar',
                        data: totals,
                        itemStyle: { color: '#CC4F4F' },
                        label: {
                            show: true,
                            position: 'insideRight',
                            formatter: function (p) {
                                return formatUSD(p.value);
                            },
                            textStyle: { fontSize: 9 }
                        }
                    }
                ]
            }, true);

            // --- Tabla comparativa Ventas vs Meta por Vendedor ---
            const tbody = document.getElementById('tbl_vendedores_vs_meta_body');
            if (tbody) {
                let html = '';
                data.forEach(function (item) {
                    const vendedor = item.vendedor || 'Sin Nombre';
                    const ventas = parseFloat(item.sum_combined_usd || 0);
                    const meta = parseFloat(item.MetaAnualUSD || 0);
                    const faltante = parseFloat(item.FaltanteUSD || 0);
                    const pct = parseFloat(item.PorcentajeCumplimiento || 0);
                    const pctBar = Math.min(pct, 100);

                    // Bar color based on progress
                    let barColor = '#CC4F4F';  // red < 50%
                    if (pct >= 80) {
                        barColor = '#28a745';  // green >= 80%
                    } else if (pct >= 50) {
                        barColor = '#f5b800';  // yellow 50-79%
                    }

                    // Faltante display
                    let faltanteHtml;
                    if (meta <= 0) {
                        faltanteHtml = '<span class="text-muted">Sin meta</span>';
                    } else if (faltante <= 0) {
                        faltanteHtml = '<span class="text-success fw-semibold">' + formatUSD(Math.abs(faltante)) + '<br><small>(superado)</small></span>';
                    } else {
                        faltanteHtml = '<span class="text-danger">' + formatUSD(faltante) + '</span>';
                    }

                    const metaHtml = meta > 0 ? formatUSD(meta) : '<span class="text-muted">—</span>';
                    const pctLabel = meta > 0 ? pct.toFixed(1) + '%' : '—';

                    html += `<tr>
                        <td class="fw-semibold text-dark text-3">${vendedor}</td>
                        <td class="text-end font-monospace text-3 fw-bold text-success">${formatUSD(ventas)}</td>
                        <td class="text-end font-monospace text-3">${metaHtml}</td>
                        <td class="text-end text-3">${faltanteHtml}</td>
                        <td class="text-center text-3">
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress flex-grow-1" style="height:8px; border-radius:4px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width:${pctBar}%; background:${barColor}; border-radius:4px;"
                                        aria-valuenow="${pctBar}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="fw-semibold" style="font-size:0.72rem; min-width:36px;">${pctLabel}</span>
                            </div>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            }
        } else {
            chartVentasVendedor.setOption({
                title: { text: 'Sin datos', left: 'center', top: 'center' }
            }, true);

            const tbody = document.getElementById('tbl_vendedores_vs_meta_body');
            if (tbody) {
                tbody.innerHTML = `<tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                        No se encontraron ventas en este periodo.
                    </td>
                </tr>`;
            }
        }
        hideLoader();
    });
}


/*-------------------------------------------
[ AJAX 4: Clientes Chart ]*/
function loadClientes() {
    if (!chartVentasCliente) return;
    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getVentasPorCliente', function (responseObj) {
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const data = responseObj.data.reverse();
            const names = data.map(item => item.cliente || 'Sin Nombre');
            const totals = data.map(item => parseFloat(item.sum_combined_usd || 0));

            chartVentasCliente.setOption({
                tooltip: {
                    trigger: 'axis',
                    formatter: function (params) {
                        const item = params[0];
                        return `${item.name}<br/>Compras: <b>${formatUSD(item.value)}</b>`;
                    }
                },
                grid: {
                    left: '3%', right: '4%', bottom: '3%', containLabel: true
                },
                xAxis: {
                    type: 'value',
                    axisLabel: { formatter: '${value}' }
                },
                yAxis: {
                    type: 'category',
                    data: names,
                    axisLabel: { interval: 0 }
                },
                series: [
                    {
                        name: 'Compras (USD)',
                        type: 'bar',
                        data: totals,
                        itemStyle: { color: '#00809F' },
                        label: {
                            show: true,
                            position: 'insideRight',
                            formatter: function (p) {
                                return formatUSD(p.value);
                            },
                            textStyle: { fontSize: 9 }
                        }
                    }
                ]
            }, true);
        } else {
            chartVentasCliente.setOption({
                title: { text: 'Sin datos', left: 'center', top: 'center' }
            }, true);
        }
        hideLoader();
    });
}

/*-------------------------------------------
[ AJAX 5: Clasificación Chart ]*/
function loadClasificacion() {
    if (!chartVentasClasificacion) return;
    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getVentasPorClasificacion', function (responseObj) {
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const data = responseObj.data.map(item => {
                return {
                    name: item.clasificacion || 'Otro',
                    value: parseFloat(item.sum_combined_usd || 0)
                };
            });

            chartVentasClasificacion.setOption({
                tooltip: {
                    trigger: 'item',
                    formatter: function (p) {
                        return `${p.marker}${p.name}: <b>${formatUSD(p.value)}</b> (${p.percent}%)`;
                    }
                },
                legend: {
                    orient: 'horizontal',
                    bottom: '0%'
                },
                series: [
                    {
                        name: 'Clasificación',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '45%'],
                        data: data,
                        roseType: false,
                        itemStyle: {
                            borderRadius: 4
                        },
                        color: ['#CC4F4F', '#00809F', '#F5B041', '#5DADE2', '#48C9B0', '#AF7AC5']
                    }
                ]
            }, true);
        } else {
            chartVentasClasificacion.setOption({
                title: { text: 'Sin datos', left: 'center', top: 'center' }
            }, true);
        }
        hideLoader();
    });
}

/*-------------------------------------------
[ AJAX 6: Estatus Chart ]*/
function loadEstatus() {
    if (!chartVentasEstatus) return;
    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getVentasPorEstatus', function (responseObj) {
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const data = responseObj.data.map(item => {
                return {
                    name: item.estatus || 'Sin Estatus',
                    value: parseInt(item.count_ventas || 0),
                    monto: parseFloat(item.sum_combined_usd || 0)
                };
            });

            chartVentasEstatus.setOption({
                tooltip: {
                    trigger: 'item',
                    formatter: function (p) {
                        return `${p.marker}${p.name}<br/>Cantidad: <b>${formatNumber(p.value)} proyectos</b> (${p.percent}%)<br/>Monto: <b>${formatUSD(p.data.monto)}</b>`;
                    }
                },
                legend: {
                    orient: 'horizontal',
                    bottom: '0%'
                },
                series: [
                    {
                        name: 'Estatus',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        center: ['50%', '45%'],
                        avoidLabelOverlap: true,
                        itemStyle: {
                            borderRadius: 5,
                            borderColor: '#fff',
                            borderWidth: 2
                        },
                        label: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: '12',
                                fontWeight: 'bold',
                                formatter: '{b}\n{c} uds'
                            }
                        },
                        data: data,
                        color: ['#AEB6BF', '#D98880', '#F9E79F', '#A9DFBF', '#85C1E9', '#A2D9CE', '#45B39D', '#F4D03F', '#EB984E', '#EC7063', '#AF7AC5', '#52BE80']
                    }
                ]
            }, true);
        } else {
            chartVentasEstatus.setOption({
                title: { text: 'Sin datos', left: 'center', top: 'center' }
            }, true);
        }
        hideLoader();
    });
}

/*-------------------------------------------
[ AJAX 7: Top Productos Table ]*/
function loadTopProductos() {
    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getTopRefaccionesMasVendidas', function (responseObj) {
        let html = '';
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            responseObj.data.forEach(p => {
                const desc = p.descripcion || 'Sin descripción';
                html += `<tr>
                    <td class="fw-semibold text-primary text-3">${p.codigo}</td>
                    <td class="text-dark text-3">${desc}</td>
                    <td class="text-center font-monospace fw-bold text-3">${formatNumber(p.cantidad_vendida)}</td>
                    <td class="text-center text-muted text-3">${formatNumber(p.count_pedidos)}</td>
                </tr>`;
            });
        } else {
            html = `<tr>
                <td colspan="4" class="text-center text-muted py-4">
                    <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                    No se encontraron productos vendidos en este periodo.
                </td>
            </tr>`;
        }
        const tbody = document.getElementById('tbl_productos_mas_vendidos_body');
        if (tbody) {
            tbody.innerHTML = html;
        }
        hideLoader();
    });
}

/*-------------------------------------------
[ AJAX 8: Modal Pedidos Colocados ]*/
function openModalPedidosColocados() {
    const inputIni = document.getElementById("inputFechaIniPeriodo");
    const inputFin = document.getElementById("inputFechaFinPeriodo");

    if (inputIni && inputFin && inputIni.value && inputFin.value) {
        fecha_ini_send = Formato_Fecha_yyyymmdd(inputIni.value);
        fecha_fin_send = Formato_Fecha_yyyymmdd(inputFin.value);
    }

    const textIni = inputIni ? inputIni.value : '';
    const textFin = inputFin ? inputFin.value : '';

    const lblRango = document.getElementById('lbl_modal_pedidos_rango');
    if (lblRango) {
        lblRango.innerHTML = `${textIni} al ${textFin}`;
    }

    const tbody = document.getElementById('tbl_pedidos_colocados_body');
    const lblCount = document.getElementById('lbl_modal_pedidos_count');

    if (tbody) {
        tbody.innerHTML = `<tr>
            <td colspan="8" class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                Cargando pedidos colocados...
            </td>
        </tr>`;
    }

    // Abrir Modal
    const modalEl = document.getElementById('modalPedidosColocados');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#modalPedidosColocados').modal('show');
        }
    }

    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getPedidosColocados', function (responseObj) {
        let html = '';
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const list = responseObj.data;
            if (lblCount) lblCount.innerHTML = `${list.length} Pedidos`;

            list.forEach(p => {
                const id = p.id || 'N/A';
                const proyectoId = p.proyecto_id || 'N/A';
                const fecha = p.fecha_formateada || p.fecha || 'N/A';
                const cliente = p.cliente || 'Sin Cliente';
                const vendedor = p.vendedor || 'Sin Vendedor';
                const clasificacion = p.clasificacion_proyecto || 'Sin Clasificación';
                const totalMoneda = (p.cmoneda === 'MXN') ? formatMXN(p.total) : formatUSD(p.total);
                const totalUSD = formatUSD(p.total_usd);

                html += `<tr>
                    <td class="text-center fw-bold text-dark text-3">${id}</td>
                    <td class="text-center fw-semibold text-secondary text-3">${proyectoId}</td>
                    <td class="text-muted text-3">${fecha}</td>
                    <td class="fw-semibold text-primary text-3">${cliente}</td>
                    <td class="text-dark text-3">${vendedor}</td>
                    <td class="text-3"><span class="badge bg-light text-dark border">${clasificacion}</span></td>
                    <td class="text-end font-monospace fw-semibold text-3">${totalMoneda}</td>
                    <td class="text-end font-monospace fw-bold text-success text-3">${totalUSD}</td>
                </tr>`;
            });
        } else {
            if (lblCount) lblCount.innerHTML = `0 Pedidos`;
            html = `<tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                    No se encontraron pedidos colocados en este periodo.
                </td>
            </tr>`;
        }

        if (tbody) {
            tbody.innerHTML = html;
        }
        hideLoader();
    });
}

/*-------------------------------------------
[ AJAX 9: Modal Pedidos Cotizados ]*/
function openModalPedidosCotizados() {
    const inputIni = document.getElementById("inputFechaIniPeriodo");
    const inputFin = document.getElementById("inputFechaFinPeriodo");

    if (inputIni && inputFin && inputIni.value && inputFin.value) {
        fecha_ini_send = Formato_Fecha_yyyymmdd(inputIni.value);
        fecha_fin_send = Formato_Fecha_yyyymmdd(inputFin.value);
    }

    const textIni = inputIni ? inputIni.value : '';
    const textFin = inputFin ? inputFin.value : '';

    const lblRango = document.getElementById('lbl_modal_cotizados_rango');
    if (lblRango) {
        lblRango.innerHTML = `${textIni} al ${textFin}`;
    }

    const tbody = document.getElementById('tbl_pedidos_cotizados_body');
    const lblCount = document.getElementById('lbl_modal_cotizados_count');

    if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#table_pedidos_cotizados')) {
        $('#table_pedidos_cotizados').DataTable().destroy();
    }
    if (typeof $ !== 'undefined') {
        $('#table_pedidos_cotizados thead').html(`
            <tr>
                <th class="border-bottom-0 fw-semibold text-center" width="5%">ID</th>
                <th class="border-bottom-0 fw-semibold text-center" width="9%">ID Proyecto</th>
                <th class="border-bottom-0 fw-semibold text-center" width="10%">Fecha</th>
                <th class="border-bottom-0 fw-semibold text-center" width="20%">Cliente</th>
                <th class="border-bottom-0 fw-semibold text-center" width="16%">Vendedor</th>
                <th class="border-bottom-0 fw-semibold text-center" width="10%">Clasificación</th>
                <th class="border-bottom-0 fw-semibold text-center" width="8%">Activo</th>
                <th class="border-bottom-0 fw-semibold text-center" width="8%">Colocado</th>
                <th class="border-bottom-0 fw-semibold text-center" width="7%">Total</th>
                <th class="border-bottom-0 fw-semibold text-center" width="7%">Total (USD)</th>
            </tr>
        `);
    }

    if (tbody) {
        tbody.innerHTML = `<tr>
            <td colspan="10" class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Cargando pedidos cotizados...
            </td>
        </tr>`;
    }

    // Abrir Modal
    const modalEl = document.getElementById('modalPedidosCotizados');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#modalPedidosCotizados').modal('show');
        }
    }

    if (typeof $ !== 'undefined') {
        $('#modalPedidosCotizados').off('shown.bs.modal').on('shown.bs.modal', function () {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#table_pedidos_cotizados')) {
                $('#table_pedidos_cotizados').DataTable().columns.adjust().draw();
            }
        });
    }

    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getPedidosCotizados', function (responseObj) {
        if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#table_pedidos_cotizados')) {
            $('#table_pedidos_cotizados').DataTable().destroy();
        }
        if (typeof $ !== 'undefined') {
            $('#table_pedidos_cotizados thead').html(`
                <tr>
                    <th class="border-bottom-0 fw-semibold text-center" width="5%">ID</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="9%">ID Proyecto</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="10%">Fecha</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="20%">Cliente</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="16%">Vendedor</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="10%">Clasificación</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="8%">Activo</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="8%">Colocado</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="7%">Total</th>
                    <th class="border-bottom-0 fw-semibold text-center" width="7%">Total (USD)</th>
                </tr>
            `);
        }

        let html = '';
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const list = responseObj.data;
            if (lblCount) lblCount.innerHTML = `${list.length} Pedidos`;

            list.forEach(p => {
                const id = p.id || 'N/A';
                const proyectoId = p.proyecto_id || 'N/A';
                const fecha = p.fecha_formateada || p.fecha || 'N/A';
                const cliente = p.cliente || 'Sin Cliente';
                const vendedor = p.vendedor || 'Sin Vendedor';
                const clasificacion = p.clasificacion_proyecto || 'Sin Clasificación';
                const valTotal = parseFloat(p.total || 0);
                const valTotalUSD = parseFloat(p.total_usd || 0);
                const totalMoneda = (p.cmoneda === 'MXN') ? formatMXN(valTotal) : formatUSD(valTotal);
                const totalUSD = formatUSD(valTotalUSD);

                const activoVal = (p.activo !== null && p.activo !== undefined && p.activo !== '') ? String(p.activo).trim() : 'ACTIVO';
                const isCerrado = (activoVal.toUpperCase() === 'CERRADO');
                const cellActivoStyle = isCerrado
                    ? 'class="text-center text-3 bg-danger text-white fw-bold"'
                    : 'class="text-center text-3"';
                const activoHtml = isCerrado
                    ? `<span class="badge bg-white text-danger fw-bold px-2 py-1 shadow-sm" style="cursor:pointer;" onclick="verSeguimientosProyecto(${id}, '${proyectoId}')" title="Haz clic para ver seguimientos">CERRADO <i class="fa-solid fa-eye ms-1"></i></span>`
                    : `<span class="badge bg-light text-dark border px-2 py-1">${activoVal}</span>`;


                const isColocado = parseInt(p.valida_colocados || 0, 10) >= 6;
                const colocadoHtml = isColocado
                    ? `<span class="badge bg-success text-white px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Sí</span>`
                    : `<span class="badge bg-secondary text-white px-2 py-1">No</span>`;

                html += `<tr>
                    <td class="text-center fw-bold text-dark text-3">${id}</td>
                    <td class="text-center fw-semibold text-secondary text-3">${proyectoId}</td>
                    <td class="text-muted text-3">${fecha}</td>
                    <td class="fw-semibold text-primary text-3">${cliente}</td>
                    <td class="text-dark text-3">${vendedor}</td>
                    <td class="text-3"><span class="badge bg-light text-dark border">${clasificacion}</span></td>
                    <td ${cellActivoStyle}>${activoHtml}</td>
                    <td class="text-center text-3">${colocadoHtml}</td>
                    <td class="text-end font-monospace fw-semibold text-3">${totalMoneda}</td>
                    <td class="text-end font-monospace fw-bold text-primary text-3">${totalUSD}</td>
                </tr>`;
            });

            if (tbody) {
                tbody.innerHTML = html;
            }

            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                let tableCotizados;

                // Preparar la fila de inputs de filtrado antes de la inicialización de DataTable (clone sin data sucia)
                const $filterRow = $('#table_pedidos_cotizados thead tr:eq(0)').clone(false);
                $filterRow.find('th').each(function (colIdx) {
                    const title = $(this).text().trim();
                    const $input = $('<input type="text" style="max-height: 24px;" class="form-control form-control-sm text-center" placeholder="Filtrar ' + title + '"/>');

                    $input.on('keyup change clear', function () {
                        if (tableCotizados && tableCotizados.column(colIdx).search() !== this.value) {
                            tableCotizados.column(colIdx).search(this.value).draw();
                        }
                    });

                    $(this).removeAttr('style class aria-controls aria-label aria-sort tabindex')
                           .addClass('text-center p-1')
                           .html($('<div class="form-group mb-0"></div>').append($input));
                });
                $('#table_pedidos_cotizados thead').append($filterRow);

                tableCotizados = $('#table_pedidos_cotizados').DataTable({
                    orderCellsTop: true,
                    scrollX: "100%",
                    destroy: true,
                    select: true,
                    order: [[0, "desc"]],
                    iDisplayLength: 10,
                    lengthMenu: [
                        [5, 10, 25, 50, 100, -1],
                        [5, 10, 25, 50, 100, "Todos"]
                    ],
                    dom: 'Blfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            autoFilter: true,
                            sheetName: 'Pipeline Activo',
                            messageTop: "",
                            title: 'Listado de Pedidos Cotizados (Pipeline Activo)',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'colvis',
                            postfixButtons: ['colvisRestore']
                        }
                    ],
                    columnDefs: [
                        { className: "text-center", targets: [0, 1, 2, 6, 7] },
                        { className: "text-start", targets: [3, 4, 5] },
                        { className: "text-end", targets: [8, 9] }
                    ],
                    language: (typeof idioma_espanol !== 'undefined') ? idioma_espanol : {}
                });

                tableCotizados.columns.adjust().draw();

                setTimeout(function () {
                    if (tableCotizados && $.fn.DataTable.isDataTable('#table_pedidos_cotizados')) {
                        tableCotizados.columns.adjust().draw();
                    }
                }, 150);

                setTimeout(function () {
                    if (tableCotizados && $.fn.DataTable.isDataTable('#table_pedidos_cotizados')) {
                        tableCotizados.columns.adjust().draw();
                    }
                }, 350);

                // Evento delegado en el contenedor para asegurar funcionamiento con scrollX (.dataTables_scrollHead)
                $(tableCotizados.table().container()).off('keyup change clear', 'thead input').on('keyup change clear', 'thead input', function () {
                    const colIdx = $(this).closest('th').index();
                    if (tableCotizados && tableCotizados.column(colIdx).search() !== this.value) {
                        tableCotizados.column(colIdx).search(this.value).draw();
                    }
                });
            }
        } else {
            if (lblCount) lblCount.innerHTML = `0 Pedidos`;
            html = `<tr>
                <td colspan="10" class="text-center text-muted py-4">
                    <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                    No se encontraron pedidos cotizados en este periodo.
                </td>
            </tr>`;

            if (tbody) {
                tbody.innerHTML = html;
            }
        }

        hideLoader();
    });
}


/*-------------------------------------------
[ AJAX 9: Modal Clientes Activos ]*/
function openModalClientesActivos() {
    const inputIni = document.getElementById("inputFechaIniPeriodo");
    const inputFin = document.getElementById("inputFechaFinPeriodo");

    if (inputIni && inputFin && inputIni.value && inputFin.value) {
        fecha_ini_send = Formato_Fecha_yyyymmdd(inputIni.value);
        fecha_fin_send = Formato_Fecha_yyyymmdd(inputFin.value);
    }

    const textIni = inputIni ? inputIni.value : '';
    const textFin = inputFin ? inputFin.value : '';

    const lblRango = document.getElementById('lbl_modal_clientes_rango');
    if (lblRango) {
        lblRango.innerHTML = `${textIni} al ${textFin}`;
    }

    const tbody = document.getElementById('tbl_clientes_activos_body');
    const lblCount = document.getElementById('lbl_modal_clientes_count');

    if (tbody) {
        tbody.innerHTML = `<tr>
            <td colspan="6" class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm text-info me-2" role="status"></div>
                Cargando lista de clientes...
            </td>
        </tr>`;
    }

    // Abrir Modal
    const modalEl = document.getElementById('modalClientesActivos');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#modalClientesActivos').modal('show');
        }
    }

    showLoader();
    const formData = new FormData();
    formData.append('fecha_ini', fecha_ini_send);
    formData.append('fecha_fin', fecha_fin_send);

    postFunctionData(formData, 'Ventas', 'getListaClientesActivos', function (responseObj) {
        let html = '';
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const list = responseObj.data;
            if (lblCount) lblCount.innerHTML = `${list.length} Clientes`;

            list.forEach(c => {
                const id = c.cliente_id || 'N/A';
                const cliente = c.cliente || 'Sin Cliente';
                const razonSocial = c.razon_social ? `<br><small class="text-muted">${c.razon_social}</small>` : '';
                const rfc = c.rfc || 'N/A';
                const countPedidos = formatNumber(c.count_pedidos);
                const totalMXN = formatMXN(c.sum_combined_mxn);
                const totalUSD = formatUSD(c.sum_combined_usd);

                html += `<tr>
                    <td class="text-center fw-bold text-dark text-3">${id}</td>
                    <td class="fw-semibold text-primary text-3">${cliente}${razonSocial}</td>
                    <td class="text-center text-muted font-monospace text-3">${rfc}</td>
                    <td class="text-center fw-semibold text-dark text-3">${countPedidos}</td>
                    <td class="text-end font-monospace fw-semibold text-3">${totalMXN}</td>
                    <td class="text-end font-monospace fw-bold text-info text-3">${totalUSD}</td>
                </tr>`;
            });
        } else {
            if (lblCount) lblCount.innerHTML = `0 Clientes`;
            html = `<tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                    No se encontraron clientes activos en este periodo.
                </td>
            </tr>`;
        }

        if (tbody) {
            tbody.innerHTML = html;
        }
        hideLoader();
    });
}

/**
     * Muestra el modal con el listado de seguimientos asociados a un proyecto / venta
     */
function verSeguimientosProyecto(ventaId, proyectoId) {
    const lblProyecto = document.getElementById('lbl_modal_seguimiento_proyecto');
    const lblVentaId = document.getElementById('lbl_modal_seguimiento_venta_id');
    const lblCount = document.getElementById('lbl_modal_seguimiento_count');
    const tbody = document.getElementById('tbl_seguimiento_venta_body');

    if (lblProyecto) lblProyecto.textContent = proyectoId || 'N/A';
    if (lblVentaId) lblVentaId.textContent = ventaId || '--';
    if (lblCount) lblCount.textContent = '0 Seguimientos';

    if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#table_seguimiento_venta')) {
        $('#table_seguimiento_venta').DataTable().destroy();
    }

    if (tbody) {
        tbody.innerHTML = `<tr>
            <td colspan="4" class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                Cargando seguimientos...
            </td>
        </tr>`;
    }

    const modalEl = document.getElementById('modalSeguimientosVenta');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#modalSeguimientosVenta').modal('show');
        }
    }

    const formData = new FormData();
    formData.append('venta_id', ventaId);

    postFunctionData(formData, 'Ventas', 'getSeguimientoVenta', function (responseObj) {
        if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#table_seguimiento_venta')) {
            $('#table_seguimiento_venta').DataTable().destroy();
        }

        let html = '';
        if (responseObj && responseObj.respuesta === "ok" && responseObj.data && responseObj.data.length > 0) {
            const list = responseObj.data;
            if (lblCount) lblCount.textContent = `${list.length} Seguimiento${list.length > 1 ? 's' : ''}`;

            list.forEach(s => {
                const id = s.id || s.ID || 'N/A';
                const fecha = s.fecha_formateada || s.fecha || s.fecha_registro || s.fchregistro || s.created_at || 'N/A';
                const usuario = (s.nombre_usuario && s.nombre_usuario.trim() !== '') 
                    ? s.nombre_usuario 
                    : (s.usuario_nombre || s.usuario || s.vendedor || s.ccveusuario || 'N/A');

                let detalle = s.seguimiento || s.comentario || s.observaciones || s.observacion || s.nota || s.descripcion || s.mensaje || '';
                if (!detalle) {
                    const extra = [];
                    Object.keys(s).forEach(k => {
                        if (!['id', 'venta_id', 'ccveusuario', 'nombre_usuario', 'fecha', 'fchregistro', 'created_at'].includes(k.toLowerCase()) && s[k]) {
                            extra.push(`<strong>${k}:</strong> ${s[k]}`);
                        }
                    });
                    detalle = extra.length > 0 ? extra.join(' | ') : 'Sin observaciones';
                }

                html += `<tr>
                    <td class="text-center fw-bold text-dark text-3">${id}</td>
                    <td class="text-center text-muted text-3">${fecha}</td>
                    <td class="text-dark text-3">${usuario}</td>
                    <td class="text-3 text-wrap" style="white-space: normal;">${detalle}</td>
                </tr>`;
            });

            if (tbody) tbody.innerHTML = html;

            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#table_seguimiento_venta').DataTable({
                    scrollX: "100%",
                    destroy: true,
                    order: [[0, "desc"]],
                    iDisplayLength: 10,
                    lengthMenu: [
                        [5, 10, 25, 50, -1],
                        [5, 10, 25, 50, "Todos"]
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                    }
                });
            }
        } else {
            if (lblCount) lblCount.textContent = '0 Seguimientos';
            if (tbody) {
                tbody.innerHTML = `<tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fa-regular fa-folder-open fa-2x d-block mb-2"></i>
                        No hay seguimientos registrados para este proyecto.
                    </td>
                </tr>`;
            }
        }
    });
}

window.verSeguimientosProyecto = verSeguimientosProyecto;



