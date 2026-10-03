/* Reporte mensual: una carga global por cambio de filtros. */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filtros-ventas-mensuales');
    let submitting = false;
    form.addEventListener('submit', function (event) {
        if (submitting) { event.preventDefault(); return; }
        submitting = true;
        document.getElementById('ventas-cargando').hidden = false;
        form.querySelector('button').disabled = true;
    });
    // La plantilla inicializa Select2; change se enlaza mediante jQuery para sus eventos.
    jQuery(form).find('select').on('change', function () {
        if (this.id !== 'ventas-mes' && this.id !== 'ventas-anio' && !submitting) form.requestSubmit();
    });
    window.addEventListener('pageshow', function () {
        submitting = false;
        document.getElementById('ventas-cargando').hidden = true;
        form.querySelector('button').disabled = false;
    });
    const source = document.getElementById('ventas-mensuales-datos');
    if (!source) return;
    const modal = document.getElementById('modal-declinados-ventas');
    if (modal) {
        const periodLabel = document.getElementById('declinados-periodo');
        const globalPeriodLabel = periodLabel ? periodLabel.textContent : '';
        const status = document.getElementById('declinados-estado');
        const badge = document.getElementById('declinados-total');
        const retry = document.getElementById('declinados-reintentar');
        const tableElement = document.getElementById('table-declinados-ventas');
        let table = null;
        let request = null;
        let filterTimer;
        let restoreList = false;
        let openingFollowup = false;
        let lista = 'declinados';
        const isDrill = () => ['estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo','estatus_periodo','cotizados_periodo','colocados_periodo'].includes(lista);
        const listTitle = () => isDrill() ? modal.dataset.desgloseTitulo : lista === 'interna_sin_cliente'
            ? 'Proyectos con cotización interna sin cotización a cliente' : 'Listado de Proyectos Declinados';
        const escape = jQuery.fn.dataTable.render.text().display;
        const text = value => escape(String(value == null ? '' : value));
        function initialize() {
            if (table) { table.columns.adjust(); if (!restoreList) table.ajax.reload(null, true); restoreList = false; return; }
            const filterRow = document.createElement('tr');
            filterRow.className = 'filters';
            Array.from(tableElement.tHead.rows[0].cells).forEach((header, index) => {
                const th = document.createElement('th');
                th.className = 'text-center p-1';
                if (index > 0 && index < 8) {
                    const input = document.createElement('input');
                    input.type = 'search';
                    input.className = 'form-control form-control-sm';
                    input.placeholder = 'Filtrar ' + header.textContent.trim();
                    input.setAttribute('aria-label', input.placeholder);
                    input.dataset.column = String(index);
                    th.appendChild(input);
                }
                filterRow.appendChild(th);
            });
            tableElement.tHead.appendChild(filterRow);
            table = jQuery(tableElement).DataTable({
                serverSide: true, processing: true, searchDelay: 400,
                orderCellsTop: true, scrollX: '100%', select: true, order: [],
                iDisplayLength: 10, lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                dom: '<"declinados-toolbar d-flex flex-wrap align-items-center gap-3 mb-2"<"declinados-length"l><"declinados-buttons"B><"declinados-search ms-auto"f>>rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2"ip>',
                buttons: [
                    { extend: 'excelHtml5', text: 'Excel (página actual)', autoFilter: true,
                        sheetName: 'Proyectos', title: listTitle,
                        exportOptions: { columns: ':visible:not(.ventas-col-seguimientos)' } },
                    { extend: 'colvis', columns: ':not(.ventas-col-seguimientos)', text: '<i class="fa-solid fa-table-columns me-1"></i> Columnas' }
                ],
                language: typeof idioma_espanol !== 'undefined' ? idioma_espanol : {
                    processing: 'Cargando…', emptyTable: 'Sin proyectos para esta lista', zeroRecords: 'No hay coincidencias',
                    search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ registros', info: 'Registros del _START_ al _END_ de un total de _TOTAL_ registros',
                    infoEmpty: 'Sin registros', infoFiltered: '(filtrado de _MAX_ registros)',
                    paginate: { previous: 'Anterior', next: 'Siguiente' }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, render: (value, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'proyecto_id', render: (v, type) => type === 'display' ? '<span class="text-danger">' + text(v) + '</span>' : v },
                    { data: 'fecha', render: (v, type) => {
                        if (type !== 'display') return v;
                        const date = String(v || '').slice(0, 10);
                        return /^\d{4}-\d{2}-\d{2}$/.test(date) ? date.split('-').reverse().join('/') : '—';
                    } },
                    { data: 'cliente', render: (v, type) => type === 'display' ? '<span class="text-primary">' + text(v) + '</span>' : v },
                    { data: 'vendedor', render: (v, type) => type === 'display' ? text(v) : v },
                    { data: 'clasificacion', render: (v, type) => type === 'display' ? '<span class="badge border text-dark bg-light">' + text(v) + '</span>' : v },
                    { data: 'titulo', render: (v, type) => type === 'display' ? text(v) : v },
                    { data: 'activo', render: (v, type) => {
                        if (type !== 'display') return v;
                        const color = v === 'CERRADO' ? 'danger' : 'primary';
                        return '<button type="button" class="btn btn-' + color + ' btn-sm ventas-ver-seguimientos" title="Ver seguimientos del proyecto" aria-label="Ver seguimientos del proyecto">' +
                            text(v || 'ACTIVO') + ' <i class="fa-solid fa-list-check ms-1" aria-hidden="true"></i></button>';
                    } },
                    { data: null, orderable: false, searchable: false, visible: lista === 'interna_sin_cliente',
                        className: 'text-center ventas-col-seguimientos', render: (v, type) => type === 'display'
                            ? '<button type="button" class="btn btn-outline-primary btn-sm ventas-ver-seguimientos"><i class="fa-solid fa-list-check me-1" aria-hidden="true"></i> Ver seguimientos</button>' : '' }
                ],
                columnDefs: [{ className: 'text-center', targets: [0, 1, 2, 7] }, { className: 'text-start', targets: [3, 4, 5, 6] }],
                drawCallback: function () {
                    if (!isDrill()) return;
                    const api = this.api();
                    const nodes = api.rows({ page: 'current' }).nodes();
                    let previous = '';
                    api.rows({ page: 'current' }).data().each((row, index) => {
                        const key = JSON.stringify([row.vendedor_id]);
                        if (key === previous) return;
                        previous = key;
                        const heading = document.createElement('tr');
                        const cell = document.createElement('td');
                        cell.colSpan = api.columns(':visible').count();
                        cell.className = 'bg-light fw-bold text-primary text-wrap';
                        cell.textContent = row.vendedor;
                        heading.appendChild(cell);
                        nodes[index].parentNode.insertBefore(heading, nodes[index]);
                    });
                },
                ajax: async function (data, callback) {
                    if (request) request.abort();
                    const current = new AbortController();
                    request = current;
                    status.textContent = 'Cargando proyectos…';
                    status.className = 'mb-2 text-muted';
                    retry.hidden = true;
                    const order = data.order[0] || { column: 2, dir: 'desc' };
                    const params = new URLSearchParams({ datatable: '1',
                        vendedor: modal.dataset.vendedor, draw: String(data.draw), start: String(data.start), length: String(data.length),
                        search: data.search.value, order_column: String(order.column), order_dir: order.dir });
                    params.set('lista', lista);
                    const years = isDrill() && modal.dataset.desgloseAnio ? modal.dataset.desgloseAnio : modal.dataset.anio;
                    const months = isDrill() && modal.dataset.desgloseMes ? modal.dataset.desgloseMes : modal.dataset.mes;
                    years.split(',').forEach(year => params.append('anio[]', year));
                    months.split(',').forEach(month => params.append('mes[]', month));
                    if (isDrill()) {
                        if (!['estatus_periodo','cotizados_periodo','colocados_periodo'].includes(lista)) params.set('clasificacion_id', modal.dataset.clasificacionId);
                        if (['cotizados_periodo','colocados_periodo'].includes(lista)) {
                            modal.dataset.anio.split(',').forEach(year => params.append('periodo_anio[]',year));
                            modal.dataset.mes.split(',').forEach(month => params.append('periodo_mes[]',month));
                        }
                        if (lista === 'vendedor_clasificacion') params.set('vendedor_id', modal.dataset.desgloseVendedor);
                        else if (lista === 'estatus_clasificacion' || lista === 'estatus_periodo') params.set('estatus_id', modal.dataset.estatusId);
                        params.set('segmento', modal.dataset.segmento);
                    }
                    data.columns.forEach((column, index) => { if (index > 0 && index < 8) params.set('f' + index, column.search.value); });
                    try {
                        const response = await fetch(modal.dataset.url + '?' + params, {
                            signal: current.signal, headers: { Accept: 'application/json' }, credentials: 'same-origin'
                        });
                        const payload = await response.json();
                        if (!response.ok || !payload.status) throw new Error(payload.message || 'No se pudo cargar la lista.');
                        if (request !== current) return;
                        badge.textContent = payload.data.recordsTotal + ' Proyectos';
                        status.textContent = '';
                        callback(payload.data);
                    } catch (error) {
                        if (error.name === 'AbortError' || request !== current) return;
                        badge.textContent = '— Proyectos';
                        status.textContent = error instanceof SyntaxError ? 'No se pudo cargar la lista. Intente nuevamente.' : error.message;
                        status.className = 'mb-2 text-danger';
                        retry.hidden = false;
                        callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                    } finally { if (request === current) request = null; }
                }
            });
            // Delegación sobre el contenedor: incluye la cabecera clonada por scrollX.
            jQuery(table.table().container()).on('input', 'thead input', function () {
                const index = Number(this.dataset.column);
                const value = this.value;
                clearTimeout(filterTimer);
                filterTimer = setTimeout(() => table.column(index).search(value).draw(), 350);
            });
        }
        tableElement.addEventListener('click', event => {
            const button = event.target.closest('.ventas-ver-seguimientos');
            if (!button || !table || openingFollowup) return;
            const row = table.row(button.closest('tr')).data();
            if (!row || !row.id) return;
            event.stopPropagation();
            openingFollowup = true;
            const followup = document.getElementById('modalSeguimientosVenta');
            followup.dataset.lista = lista;
            restoreList = true;
            followup.addEventListener('hidden.bs.modal', () => {
                openingFollowup = false;
                modal.addEventListener('shown.bs.modal', () => button.focus(), { once: true });
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }, { once: true });
            modal.addEventListener('hidden.bs.modal', () => window.verSeguimientosProyecto(row.id, row.proyecto_id), { once: true });
            bootstrap.Modal.getOrCreateInstance(modal).hide();
        });
        modal.addEventListener('show.bs.modal', event => {
            if (!event.relatedTarget && modal.dataset.desglose !== '1') return;
            const next = event.relatedTarget ? (event.relatedTarget.dataset.lista || 'declinados') : (modal.dataset.desgloseLista || 'estatus_clasificacion');
            if (event.relatedTarget) modal.dataset.desglose = '';
            if ((next !== lista || ['estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo','estatus_periodo'].includes(next)) && table) {
                table.search('');
                table.columns().search('');
                jQuery(table.table().container()).find('thead input').val('');
                table.order([]);
            }
            lista = next;
            if (periodLabel) {
                const year = Number(modal.dataset.desgloseAnio);
                const month = Number(modal.dataset.desgloseMes);
                periodLabel.textContent = isDrill() && year && month
                    ? '01/' + String(month).padStart(2, '0') + '/' + year + ' al ' +
                        new Date(year, month, 0).getDate() + '/' + String(month).padStart(2, '0') + '/' + year
                    : globalPeriodLabel;
            }
            document.getElementById('modal-declinados-titulo').textContent = listTitle();
            if (table) table.column(8).visible(lista === 'interna_sin_cliente');
        });
        modal.addEventListener('shown.bs.modal', initialize);
        modal.addEventListener('hidden.bs.modal', () => {
            if (request) request.abort(); request = null; clearTimeout(filterTimer);
        });
        retry.addEventListener('click', () => { if (table) table.ajax.reload(null, false); });
        document.querySelectorAll('#ventas-mensuales .ventas-abrir-declinados').forEach(card => {
            card.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); card.click(); }
            });
        });
    }
    const data = JSON.parse(source.textContent);
    const charts = [];
    const amount = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 });
    const colors = ['#2385bd', '#27a58c', '#e5a543', '#8c6bb1', '#cf6478', '#428582', '#718bbd', '#ad7e56'];
    const projectCounts = [...(data.proyectos_por_vendedor || [])].sort((a, b) => b.proyectos - a.proyectos);
    const statusCounts = data.clasificaciones_por_vendedor || [];
    const selector = document.getElementById('ventas-vendedor-desglose');
    const statusPanel = document.getElementById('ventas-estatus-panel');
    const cascadeCharts = [];
    let selectedIndex = -1;
    let statusChart = null;
    let projectStatusChart = null;
    let quotedStatusKey = null;
    const cascadeText = typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? '#edf2f7' : '#243447';
    function cascade(id) {
        const element = document.getElementById(id);
        element.style.height = '330px';
        const instance = echarts.init(element, typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? 'dark' : null);
        const entry = { element, instance, count: 0 };
        cascadeCharts.push(entry);
        charts.push(instance);
        return entry;
    }
    function fitCascade(entry) {
        const available = entry.element.parentElement ? entry.element.parentElement.clientWidth : entry.element.clientWidth;
        const width = Math.max(available || 320, entry.count * (entry.slotWidth || 125) + 90);
        entry.element.style.width = width + 'px';
        entry.instance.resize({ width, height: entry.height || 330 });
    }
    function cascadeOption(rows, selected = -1) {
        return {
            backgroundColor: 'transparent',
            tooltip: { trigger: 'axis', renderMode: 'richText', valueFormatter: value => amount.format(value) + ' proyectos' },
            textStyle: { fontSize: 11 },
            grid: { left: 45, right: 20, top: 32, bottom: 95 },
            xAxis: { type: 'category', data: rows.map(row => row.nombre), axisLabel: {
                interval: 0, color: cascadeText, fontSize: 11, width: 110, overflow: 'break', lineHeight: 14
            } },
            yAxis: { type: 'value', name: 'Proyectos', minInterval: 1,
                nameTextStyle: { color: cascadeText }, axisLabel: { color: cascadeText } },
            series: [{ name: 'Proyectos', type: 'bar', barMaxWidth: 65,
                label: { show: true, position: 'top', fontSize: 11, color: cascadeText },
                data: rows.map((row, index) => ({ value: row.proyectos, itemStyle: {
                    color: index === selected ? '#d48825' : '#2385bd',
                    borderColor: index === selected ? '#80510f' : '#2385bd', borderWidth: index === selected ? 2 : 0
                } }))
            }], aria: { enabled: true }
        };
    }
    const sellerChart = cascade('ventas-cantidades-vendedores');
    sellerChart.count = projectCounts.length;
    sellerChart.height = 260;
    sellerChart.slotWidth = 100;
    sellerChart.element.style.height = '260px';
    function sellerOption(selected = -1) {
        const option = cascadeOption(projectCounts, selected);
        option.grid = {left:38,right:12,top:24,bottom:76};
        option.xAxis.axisLabel = {...option.xAxis.axisLabel,fontSize:9,width:92,lineHeight:12,margin:8};
        option.yAxis.axisLabel.fontSize = 10;
        option.yAxis.nameTextStyle.fontSize = 10;
        option.yAxis.splitLine = {lineStyle:{color: typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? '#354a64' : '#e7edf4'}};
        option.series[0].barMaxWidth = 42;
        option.series[0].label.fontSize = 10;
        option.series[0].itemStyle = {borderRadius:[2,2,0,0]};
        return option;
    }
    sellerChart.instance.setOption(sellerOption());
    fitCascade(sellerChart);
    document.getElementById('ventas-cascada-vacio').hidden = projectCounts.length > 0;
    function selectSeller(index) {
        selectedIndex = Number.isInteger(index) && index >= 0 && index < projectCounts.length ? index : -1;
        selector.value = selectedIndex < 0 ? '' : String(selectedIndex);
        sellerChart.instance.setOption({ series: sellerOption(selectedIndex).series });
        statusPanel.hidden = selectedIndex < 0;
        if (selectedIndex < 0) return;
        const seller = projectCounts[selectedIndex];
        document.getElementById('ventas-estatus-titulo').textContent = seller.nombre + ' — ' + seller.proyectos + ' proyectos';
        const sellerRows = (data.estatus_por_clasificacion || []).filter(row =>
            String(row.vendedor_id ?? '') === String(seller.vendedor_id ?? '') &&
            periods.some(period => Number(row.anio) === Number(period.anio) && Number(row.mes) === Number(period.mes)));
        const classifications = generalClassifications.filter(group => sellerRows.some(row => String(row.clasificacion_id) === String(group.clasificacion_id)));
        const sellerStatuses = statuses.filter(group => sellerRows.some(row => statusGroupKey(row) === String(group.id)) ||
            (kpiRows(group.id) || []).some(row => String(row.vendedor_id ?? '') === String(seller.vendedor_id ?? '')));
        const classificationSummary = document.getElementById('ventas-estatus-resumen');
        classificationSummary.hidden = classifications.length > 0;
        classificationSummary.textContent = classifications.length ? '' : 'Sin clasificaciones registradas para los meses seleccionados.';
        if (!statusChart) statusChart = cascade('ventas-estatus-vendedor');
        renderSellerMonthly(statusChart, principalOption, classifications, generalClassifications, sellerRows,
            row => String(row.clasificacion_id), group => String(group.clasificacion_id), group => group.clasificacion, classificationChart);
        document.getElementById('ventas-desglose-estatus-titulo').textContent = seller.nombre + ' — Estatus de proyectos';
        const statusSummary = document.getElementById('ventas-desglose-estatus-resumen');
        statusSummary.hidden = sellerStatuses.length > 0;
        statusSummary.textContent = sellerStatuses.length ? '' : 'Sin estatus registrados para los meses seleccionados.';
        if (!projectStatusChart) projectStatusChart = cascade('ventas-desglose-estatus');
        renderSellerMonthly(projectStatusChart, statusOption, sellerStatuses, statuses, sellerRows,
            statusGroupKey, group => String(group.id), group => group.nombre, generalStatusChart);
    }
    function renderSellerMonthly(entry, base, groups, allGroups, rows, rowKey, groupKey, groupName, reference) {
        const counts = new Map();
        rows.forEach(row => {
            const key = [rowKey(row),row.anio,row.mes].join(':');
            if (!counts.has(key)) counts.set(key, {proyectos:0,declinados:0});
            const total = counts.get(key);
            total.proyectos += Number(row.proyectos);
            total.declinados += Number(row.declinados);
        });
        if (allGroups === statuses) ['cotizados_periodo','colocados'].forEach(groupId => {
            const documentRows = kpiRows(groupId);
            if (!documentRows) return;
            periods.forEach(period => counts.set([groupId,period.anio,period.mes].join(':'),{proyectos:0,declinados:0,periodo:0,anteriores:0}));
            const seller = projectCounts[selectedIndex];
            documentRows.filter(row => String(row.vendedor_id ?? '') === String(seller.vendedor_id ?? '')).forEach(row => {
                const key = [groupId,row.anio,row.mes].join(':');
                if (counts.has(key)) {
                    counts.get(key).proyectos += Number(row.proyectos);
                    counts.get(key)[row.origen === 'periodo' ? 'periodo' : 'anteriores'] += Number(row.proyectos);
                }
            });
        });
        const totalsFor = group => periods.map(period => counts.get([groupKey(group),period.anio,period.mes].join(':')) || {proyectos:0,declinados:0});
        const option = {...base, legend:{...base.legend,data:groups.length ? [...groups.map(groupName),'Declinados'] : []},
            series:groups.flatMap(group => {
                const index = allGroups.indexOf(group) * 2;
                const totals = totalsFor(group);
                return [
                    {...base.series[index],data:totals.map(row => row.proyectos-row.declinados)},
                    {...base.series[index+1],data:totals.map(row => row.declinados),
                        label:{...base.series[index+1].label,formatter:params => String(totals[params.dataIndex].proyectos)}}
                ];
            }),
            tooltip:{trigger:'item',renderMode:'richText',formatter:params => {
                const group = groups[Math.floor(params.seriesIndex/2)];
                const period = periods[params.dataIndex];
                const total = totalsFor(group)[params.dataIndex];
                if (kpiRows(group.id)) return groupName(group)+'\n'+monthNames[Number(period.mes)-1]+' '+period.anio+
                    '\nProyectos del período: '+total.periodo+'\nProyectos anteriores: '+total.anteriores+
                    '\nTotal '+(group.id === 'colocados' ? 'colocado' : 'cotizado')+': '+total.proyectos;
                return groupName(group)+'\n'+monthNames[Number(period.mes)-1]+' '+period.anio+
                    '\nProyectos activos: '+(total.proyectos-total.declinados)+'\nDeclinados: '+total.declinados+'\nTotal: '+total.proyectos;
            }}
        };
        entry.count = periods.length;
        entry.slotWidth = Math.max(280,groups.length*88);
        entry.height = reference.height || 330;
        entry.element.style.height = entry.height + 'px';
        entry.instance.setOption(option, true);
        fitCascade(entry);
    }
    function statusGroupKey(row) {
        if (quotedStatusKey !== null && String(row.estatus_id) === quotedStatusKey) return 'cotizados_periodo';
        return Number(row.estatus_id) >= 6 ? 'colocados' :
            ([1,3].includes(Number(row.estatus_id)) ? 'proceso_cotizacion' : String(row.estatus_id));
    }
    function kpiRows(groupId) {
        const rows = groupId === 'cotizados_periodo' ? data.cotizados_por_periodo :
            (groupId === 'colocados' ? data.colocados_por_periodo : null);
        return Array.isArray(rows) ? rows : null;
    }
    sellerChart.instance.on('click', event => {
        if (event.componentType === 'series') selectSeller(event.dataIndex);
    });
    selector.addEventListener('change', () => selectSeller(selector.value === '' ? -1 : Number(selector.value)));
    // Consolida las clasificaciones del mismo conjunto filtrado, sin otra consulta.
    const classificationTotals = new Map();
    statusCounts.forEach(row => {
        const key = String(row.clasificacion_id);
        if (!classificationTotals.has(key)) classificationTotals.set(key, {
            clasificacion_id: row.clasificacion_id, clasificacion: row.clasificacion, proyectos: 0, declinados: 0
        });
        const total = classificationTotals.get(key);
        total.proyectos += Number(row.proyectos);
        total.declinados += Number(row.declinados);
    });
    const generalClassifications = [...classificationTotals.values()]
        .sort((a, b) => b.proyectos - a.proyectos || Number(a.clasificacion_id) - Number(b.clasificacion_id));
    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const selectedYears = data.anios_seleccionados || [...new Set((data.estatus_por_clasificacion || []).map(row => Number(row.anio)).filter(Boolean))];
    const selectedMonths = data.meses_seleccionados || [];
    const periods = selectedYears.slice().sort((a,b)=>a-b).flatMap(anio => selectedMonths.slice().sort((a,b)=>a-b).map(mes => ({anio,mes})));
    const periodCounts = new Map();
    (data.estatus_por_clasificacion || []).forEach(row => {
        const key = [row.clasificacion_id,row.anio,row.mes].join(':');
        if (!periodCounts.has(key)) periodCounts.set(key,{proyectos:0,declinados:0});
        const total=periodCounts.get(key);
        total.proyectos+=Number(row.proyectos);
        total.declinados+=Number(row.declinados);
    });
    const classificationColors=['#2385bd','#239c83','#d48825','#8064b0'];
    const groupedSeries=generalClassifications.flatMap((classification,index) => {
        const totals=periods.map(period=>periodCounts.get([classification.clasificacion_id,period.anio,period.mes].join(':')) || {proyectos:0,declinados:0});
        return [
            {name:classification.clasificacion,type:'bar',stack:'clase-'+classification.clasificacion_id,barWidth:26,barGap:'230%',
                itemStyle:{color:classificationColors[index%classificationColors.length]},data:totals.map(row=>row.proyectos-row.declinados),
                label:{show:true,position:'bottom',distance:5,color:cascadeText,fontSize:9,width:82,overflow:'break',lineHeight:11,formatter:()=>classification.clasificacion}},
            {name:'Declinados',type:'bar',stack:'clase-'+classification.clasificacion_id,barWidth:26,barGap:'230%',
                itemStyle:{color:'#dc3545'},data:totals.map(row=>row.declinados),
                label:{show:true,position:'top',color:cascadeText,formatter:params=>String(totals[params.dataIndex].proyectos)}}
        ];
    });
    const classificationChart = cascade('ventas-clasificaciones-general');
    classificationChart.count = periods.length;
    classificationChart.slotWidth = Math.max(280,generalClassifications.length*88);
    const principalOption = cascadeOption([]);
    principalOption.legend={top:0,type:'scroll',data:generalClassifications.length?[...generalClassifications.map(row=>row.clasificacion),'Declinados']:[],textStyle:{color:cascadeText,fontSize:10}};
    principalOption.grid.bottom=115;
    principalOption.xAxis.data=periods.map(period=>monthNames[Number(period.mes)-1]);
    principalOption.xAxis.axisLabel.margin=46;
    principalOption.xAxis.splitLine={show:true,lineStyle:{color:"#dce5ed"}};
    const yearAxis={type:'category',data:periods.map(period=>String(period.anio)),position:'bottom',offset:75,
        axisLine:{show:false},axisTick:{show:false},axisLabel:{interval:0,color:cascadeText,formatter:(value,index)=>{
            const first=periods.findIndex(period=>String(period.anio)===value);
            const last=periods.length-1-periods.slice().reverse().findIndex(period=>String(period.anio)===value);
            return index===Math.floor((first+last)/2)?value:'';
        }}};
    principalOption.xAxis=[principalOption.xAxis,yearAxis];
    principalOption.series=groupedSeries;
    principalOption.tooltip={trigger:'item',renderMode:'richText',formatter:params=>{
        const classification=generalClassifications[Math.floor(params.seriesIndex/2)];
        const period=periods[params.dataIndex];
        const total=periodCounts.get([classification.clasificacion_id,period.anio,period.mes].join(':')) || {proyectos:0,declinados:0};
        return classification.clasificacion+'\n'+monthNames[Number(period.mes)-1]+' '+period.anio+
            '\nProyectos activos: '+(total.proyectos-total.declinados)+'\nDeclinados: '+total.declinados+'\nTotal: '+total.proyectos;
    }};
    classificationChart.instance.setOption(principalOption);
    fitCascade(classificationChart);
    classificationChart.instance.on('click', event => {
        if (event.componentType !== 'series') return;
        const row = generalClassifications[Math.floor(event.seriesIndex / 2)];
        const period = periods[event.dataIndex];
        if (!row || !period) return;
        const modal = document.getElementById('modal-declinados-ventas');
        modal.dataset.desglose = '1';
        modal.dataset.desgloseLista = 'clasificacion_periodo';
        modal.dataset.clasificacionId = String(row.clasificacion_id);
        modal.dataset.desgloseAnio = String(period.anio);
        modal.dataset.desgloseMes = String(period.mes);
        modal.dataset.segmento = event.seriesIndex % 2 === 1 ? 'declinados' : 'no_declinados';
        modal.dataset.desgloseTitulo = row.clasificacion + ' · ' + monthNames[Number(period.mes)-1] + ' ' + period.anio +
            (event.seriesIndex % 2 === 1 ? ' · Declinados' : ' · No declinados');
        bootstrap.Modal.getOrCreateInstance(modal).show();
    });
    // El mismo conjunto autorizado se consolida por estatus, sin consultas adicionales.
    const statusGroups = new Map();
    const statusPeriodCounts = new Map();
    (data.estatus_por_clasificacion || []).forEach(row => {
        const placed = Number(row.estatus_id) >= 6;
        const quotation = [1,3].includes(Number(row.estatus_id));
        const key = statusGroupKey(row);
        if (!statusGroups.has(key)) statusGroups.set(key, { id: placed || quotation ? key : row.estatus_id,
            orden: placed ? 6 : (quotation ? 1 : Number(row.estatus_id)),
            nombre: placed ? 'Pedidos Colocados' : (quotation ? 'PROCESO DE COTIZACION' : (row.estatus || 'Sin estatus')) });
        const periodKey = [key,row.anio,row.mes].join(':');
        if (!statusPeriodCounts.has(periodKey)) statusPeriodCounts.set(periodKey,{proyectos:0,declinados:0});
        const total = statusPeriodCounts.get(periodKey);
        total.proyectos += Number(row.proyectos);
        total.declinados += Number(row.declinados);
    });
    if (Array.isArray(data.cotizados_por_periodo)) {
        const quoted = [...statusGroups.entries()].find(([,group]) => group.nombre.toUpperCase().startsWith('PEDIDO COTIZADO'));
        if (quoted) { quotedStatusKey = String(quoted[1].id); statusGroups.delete(quoted[0]); }
        statusGroups.set('cotizados_periodo',{id:'cotizados_periodo',orden:quoted ? quoted[1].orden : 5,
            nombre:quoted ? quoted[1].nombre : 'PEDIDO COTIZADO (SIN OC CLIENTE)'});
        periods.forEach(period => statusPeriodCounts.set(['cotizados_periodo',period.anio,period.mes].join(':'),
            {proyectos:0,declinados:0,periodo:0,anteriores:0}));
        data.cotizados_por_periodo.forEach(row => {
            const total = statusPeriodCounts.get(['cotizados_periodo',row.anio,row.mes].join(':'));
            if (!total) return;
            total.proyectos += Number(row.proyectos);
            total[row.origen === 'periodo' ? 'periodo' : 'anteriores'] += Number(row.proyectos);
        });
    }
    if (Array.isArray(data.colocados_por_periodo)) {
        statusGroups.set('colocados',{id:'colocados',orden:6,nombre:'Pedidos Colocados'});
        periods.forEach(period => statusPeriodCounts.set(['colocados',period.anio,period.mes].join(':'),
            {proyectos:0,declinados:0,periodo:0,anteriores:0}));
        data.colocados_por_periodo.forEach(row => {
            const total = statusPeriodCounts.get(['colocados',row.anio,row.mes].join(':'));
            if (!total) return;
            total.proyectos += Number(row.proyectos);
            total[row.origen === 'periodo' ? 'periodo' : 'anteriores'] += Number(row.proyectos);
        });
    }
    const statuses = [...statusGroups.values()].sort((a,b) => a.orden - b.orden);
    const statusColors = ['#2385bd','#239c83','#d48825','#8064b0','#56748c','#9b713a','#458f96','#b66489'];
    const statusOption = { ...principalOption, grid: { ...principalOption.grid },
        legend: {
            show:true, type:'scroll', top:0, left:0, right:0, itemWidth:12, itemHeight:8, itemGap:12,
            textStyle:{color:cascadeText,fontSize:10},
            formatter:name=>{
                const characters = Array.from(String(name));
                return characters.slice(0, Math.max(1, Math.floor(characters.length * .66))).join('') + '...';
            },
            tooltip:{show:true,renderMode:'richText',formatter:params=>params.name}
        },
        series: statuses.flatMap((status,index) => {
            const totals = periods.map(period=>statusPeriodCounts.get([String(status.id),period.anio,period.mes].join(':')) || {proyectos:0,declinados:0});
            return [
                {name:status.nombre,type:'bar',stack:'estatus-'+status.id,barWidth:26,barGap:'230%',
                    itemStyle:{color:statusColors[index%statusColors.length]},data:totals.map(row=>row.proyectos-row.declinados),
                    label:{show:true,position:'bottom',distance:5,color:cascadeText,fontSize:9,width:82,overflow:'break',lineHeight:11,formatter:()=>status.nombre}},
                {name:'Declinados',type:'bar',stack:'estatus-'+status.id,barWidth:26,barGap:'230%',
                    itemStyle:{color:'#dc3545'},data:totals.map(row=>row.declinados),
                    label:{show:true,position:'top',color:cascadeText,formatter:params=>String(totals[params.dataIndex].proyectos)}}
            ];
        }),
        tooltip:{trigger:'item',renderMode:'richText',formatter:params=>{
            const status = statuses[Math.floor(params.seriesIndex/2)];
            const period = periods[params.dataIndex];
            const total = statusPeriodCounts.get([String(status.id),period.anio,period.mes].join(':')) || {proyectos:0,declinados:0};
            if (kpiRows(status.id)) return status.nombre+'\n'+monthNames[Number(period.mes)-1]+' '+period.anio+
                '\nProyectos del período: '+total.periodo+'\nProyectos anteriores: '+total.anteriores+
                '\nTotal '+(status.id === 'colocados' ? 'colocado' : 'cotizado')+': '+total.proyectos;
            return status.nombre+'\n'+monthNames[Number(period.mes)-1]+' '+period.anio+
                '\nProyectos activos: '+(total.proyectos-total.declinados)+'\nDeclinados: '+total.declinados+'\nTotal: '+total.proyectos;
        }}
    };
    const generalStatusChart = cascade('ventas-estatus-general');
    generalStatusChart.height = 370;
    generalStatusChart.element.style.height = '370px';
    generalStatusChart.count = periods.length;
    generalStatusChart.slotWidth = Math.max(280,statuses.length*88);
    statusOption.grid.top = 55;
    statusOption.grid.bottom = 125;
    statusOption.xAxis = principalOption.xAxis.map(axis => ({...axis,axisLabel:{...axis.axisLabel}}));
    statusOption.xAxis[0].axisLabel.margin = 83;
    statusOption.xAxis[0].axisLabel.fontSize = 10;
    statusOption.xAxis[1].offset = 100;
    statusOption.xAxis[1].axisLabel.fontSize = 10;
    generalStatusChart.instance.setOption(statusOption);
    fitCascade(generalStatusChart);
    generalStatusChart.instance.on('click', event => {
        if (event.componentType !== 'series') return;
        const status = statuses[Math.floor(event.seriesIndex / 2)];
        const period = periods[event.dataIndex];
        const modal = document.getElementById('modal-declinados-ventas');
        if (!status || !period || !modal) return;
        modal.dataset.desglose = '1';
        if (kpiRows(status.id) && event.seriesIndex % 2 === 1) return;
        modal.dataset.desgloseLista = status.id === 'cotizados_periodo' ? 'cotizados_periodo' :
            (status.id === 'colocados' && kpiRows(status.id) ? 'colocados_periodo' : 'estatus_periodo');
        modal.dataset.estatusId = status.id == null ? 'sin_estatus' : String(status.id);
        modal.dataset.desgloseAnio = String(period.anio);
        modal.dataset.desgloseMes = String(period.mes);
        modal.dataset.segmento = event.seriesIndex % 2 === 1 ? 'declinados' : 'no_declinados';
        modal.dataset.desgloseTitulo = status.nombre + ' · ' + monthNames[Number(period.mes)-1] + ' ' + period.anio +
            (event.seriesIndex % 2 === 1 ? ' · Declinados' : ' · No declinados');
        bootstrap.Modal.getOrCreateInstance(modal).show();
    });
    window.addEventListener('resize', function () { charts.forEach(c => c.resize()); cascadeCharts.forEach(fitCascade); });

});
