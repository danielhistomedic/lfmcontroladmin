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
    jQuery(form).find('select').on('change', function () { if (!submitting) form.requestSubmit(); });
    window.addEventListener('pageshow', function () {
        submitting = false;
        document.getElementById('ventas-cargando').hidden = true;
        form.querySelector('button').disabled = false;
    });
    const source = document.getElementById('ventas-mensuales-datos');
    if (!source) return;
    const modal = document.getElementById('modal-declinados-ventas');
    if (modal) {
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
        const listTitle = () => lista === 'interna_sin_cliente'
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
                        return lista === 'declinados'
                            ? '<button type="button" class="badge border text-danger bg-light ventas-ver-seguimientos" title="Ver seguimientos del proyecto" aria-label="Ver seguimientos del proyecto">' + text(v) + ' <i class="fa-solid fa-eye ms-1" aria-hidden="true"></i></button>'
                            : '<span class="badge border text-dark bg-light">' + text(v || 'ACTIVO') + '</span>';
                    } },
                    { data: null, orderable: false, searchable: false, visible: lista === 'interna_sin_cliente',
                        className: 'text-center ventas-col-seguimientos', render: (v, type) => type === 'display'
                            ? '<button type="button" class="btn btn-outline-primary btn-sm ventas-ver-seguimientos"><i class="fa-solid fa-list-check me-1" aria-hidden="true"></i> Ver seguimientos</button>' : '' }
                ],
                columnDefs: [{ className: 'text-center', targets: [0, 1, 2, 7] }, { className: 'text-start', targets: [3, 4, 5, 6] }],
                ajax: async function (data, callback) {
                    if (request) request.abort();
                    const current = new AbortController();
                    request = current;
                    status.textContent = 'Cargando proyectos…';
                    status.className = 'mb-2 text-muted';
                    retry.hidden = true;
                    const order = data.order[0] || { column: 2, dir: 'desc' };
                    const params = new URLSearchParams({ datatable: '1', anio: modal.dataset.anio, mes: modal.dataset.mes,
                        vendedor: modal.dataset.vendedor, draw: String(data.draw), start: String(data.start), length: String(data.length),
                        search: data.search.value, order_column: String(order.column), order_dir: order.dir });
                    params.set('lista', lista);
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
            if (!event.relatedTarget) return;
            const next = event.relatedTarget.dataset.lista || 'declinados';
            if (next !== lista && table) {
                table.search('');
                table.columns().search('');
                jQuery(table.table().container()).find('thead input').val('');
                table.order([]);
            }
            lista = next;
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
    function chart(id, labels, series, horizontal, unit = 'USD') {
        const el = document.getElementById(id);
        const instance = echarts.init(el, typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? 'dark' : null);
        const compact = el.clientWidth < 500;
        const chartText = typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? '#edf2f7' : '#243447';
        const category = { type: 'category', data: labels, axisLabel: { color: chartText, width: compact ? 95 : 150, overflow: 'truncate' } };
        const value = { type: 'value', name: unit, nameTextStyle: { color: chartText }, axisLabel: { color: chartText, formatter: v => amount.format(v) } };
        if (unit === 'Proyectos') value.minInterval = 1;
        const zoom = labels.length > 10 ? [{ type: 'slider', orient: horizontal ? 'vertical' : 'horizontal',
            [horizontal ? 'yAxisIndex' : 'xAxisIndex']: 0, start: 0, end: Math.min(100, 1000 / labels.length) }] : [];
        instance.setOption({ color: colors, backgroundColor: 'transparent',
            tooltip: { trigger: 'axis', renderMode: 'richText', valueFormatter: v => amount.format(v) + ' ' + unit },
            legend: { top: 0, type: 'scroll', textStyle: { color: chartText } }, grid: { left: horizontal ? (compact ? 110 : 180) : 70, right: labels.length > 10 && horizontal ? 60 : 25, top: 50, bottom: 60 },
            xAxis: horizontal ? value : category, yAxis: horizontal ? category : value,
            dataZoom: zoom, series: series, aria: { enabled: true } });
        charts.push(instance);
    }
    const bars = rows => ['cotizado', 'colocado'].map((key, i) => ({ name: i === 0 ? 'Cotizado' : 'Colocado', type: 'bar', data: rows.map(r => r[key]) }));
    const projectCounts = [...(data.proyectos_por_vendedor || [])].sort((a, b) => b.proyectos - a.proyectos);
    const statusCounts = data.clasificaciones_por_vendedor || [];
    const selector = document.getElementById('ventas-vendedor-desglose');
    const statusPanel = document.getElementById('ventas-estatus-panel');
    const cascadeCharts = [];
    let selectedIndex = -1;
    let statusChart = null;
    let projectStatusChart = null;
    const cascadeText = typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? '#edf2f7' : '#243447';
    function cascade(id) {
        const element = document.getElementById(id);
        element.style.height = '460px';
        const instance = echarts.init(element, typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? 'dark' : null);
        const entry = { element, instance, count: 0 };
        cascadeCharts.push(entry);
        charts.push(instance);
        return entry;
    }
    function fitCascade(entry) {
        const available = entry.element.parentElement ? entry.element.parentElement.clientWidth : entry.element.clientWidth;
        const width = Math.max(available || 320, entry.count * (entry.slotWidth || 160) + 90);
        entry.element.style.width = width + 'px';
        entry.instance.resize({ width, height: entry.height || 460 });
    }
    function cascadeOption(rows, selected = -1) {
        return {
            backgroundColor: 'transparent',
            tooltip: { trigger: 'axis', renderMode: 'richText', valueFormatter: value => amount.format(value) + ' proyectos' },
            grid: { left: 60, right: 30, top: 45, bottom: 130 },
            xAxis: { type: 'category', data: rows.map(row => row.nombre), axisLabel: {
                interval: 0, color: cascadeText, width: 140, overflow: 'break', lineHeight: 17
            } },
            yAxis: { type: 'value', name: 'Proyectos', minInterval: 1,
                nameTextStyle: { color: cascadeText }, axisLabel: { color: cascadeText } },
            series: [{ name: 'Proyectos', type: 'bar', barMaxWidth: 65,
                label: { show: true, position: 'top', color: cascadeText },
                data: rows.map((row, index) => ({ value: row.proyectos, itemStyle: {
                    color: index === selected ? '#d48825' : '#2385bd',
                    borderColor: index === selected ? '#80510f' : '#2385bd', borderWidth: index === selected ? 2 : 0
                } }))
            }], aria: { enabled: true }
        };
    }
    const sellerChart = cascade('ventas-cantidades-vendedores');
    sellerChart.count = projectCounts.length;
    sellerChart.instance.setOption(cascadeOption(projectCounts));
    fitCascade(sellerChart);
    document.getElementById('ventas-cascada-vacio').hidden = projectCounts.length > 0;
    function selectSeller(index) {
        selectedIndex = Number.isInteger(index) && index >= 0 && index < projectCounts.length ? index : -1;
        selector.value = selectedIndex < 0 ? '' : String(selectedIndex);
        sellerChart.instance.setOption({ series: cascadeOption(projectCounts, selectedIndex).series });
        statusPanel.hidden = selectedIndex < 0;
        if (selectedIndex < 0) return;
        const seller = projectCounts[selectedIndex];
        const statuses = statusCounts.filter(row => String(row.vendedor_id ?? '') === String(seller.vendedor_id ?? ''))
            .sort((a, b) => Number(a.clasificacion_id) - Number(b.clasificacion_id));
        document.getElementById('ventas-estatus-titulo').textContent = seller.nombre + ' — ' + seller.proyectos + ' proyectos';
        document.getElementById('ventas-estatus-resumen').textContent = statuses.length
            ? statuses.map(row => row.clasificacion + ': ' + row.proyectos + ' (' + row.declinados + ' declinados)').join(' | ') : 'Sin clasificaciones registradas.';
        if (!statusChart) statusChart = cascade('ventas-estatus-vendedor');
        statusChart.count = statuses.length;
        statusChart.instance.setOption(stackedOption(statuses, 'clasificacion'), true);
        fitCascade(statusChart);
        const projectStatuses = (data.estatus_por_vendedor || [])
            .filter(row => String(row.vendedor_id ?? '') === String(seller.vendedor_id ?? ''))
            .sort((a, b) => Number(a.estatus_id) - Number(b.estatus_id));
        document.getElementById('ventas-desglose-estatus-titulo').textContent = seller.nombre + ' — Estatus de proyectos';
        document.getElementById('ventas-desglose-estatus-resumen').textContent = projectStatuses.length
            ? projectStatuses.map(row => row.estatus + ': ' + row.proyectos + ' (' + row.declinados + ' declinados)').join(' | ')
            : 'Sin estatus registrados.';
        if (!projectStatusChart) projectStatusChart = cascade('ventas-desglose-estatus');
        projectStatusChart.count = projectStatuses.length;
        projectStatusChart.instance.setOption(stackedOption(projectStatuses, 'estatus'), true);
        fitCascade(projectStatusChart);
    }
    function stackedOption(statuses, nameField) {
        const option = cascadeOption(statuses.map(row => ({ nombre: row[nameField], proyectos: row.proyectos })));
        option.legend = { top: 0, textStyle: { color: cascadeText } };
        option.series = [
            { name: 'No declinados', type: 'bar', stack: 'proyectos', barMaxWidth: 65,
                itemStyle: { color: '#2385bd' }, data: statuses.map(row => row.proyectos - row.declinados) },
            { name: 'Declinados', type: 'bar', stack: 'proyectos', barMaxWidth: 65,
                itemStyle: { color: '#dc3545' }, data: statuses.map(row => row.declinados),
                label: { show: true, position: 'top', color: cascadeText,
                    formatter: params => String(statuses[params.dataIndex].proyectos) } }
        ];
        return option;
    }
    sellerChart.instance.on('click', event => {
        if (event.componentType === 'series') selectSeller(event.dataIndex);
    });
    selector.addEventListener('change', () => selectSeller(selector.value === '' ? -1 : Number(selector.value)));
    chart('ventas-comparativo', ['Mes seleccionado'], bars([data]), false);
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
        .sort((a, b) => Number(a.clasificacion_id) - Number(b.clasificacion_id));
    const classificationChart = cascade('ventas-clasificaciones-general');
    classificationChart.count = generalClassifications.length;
    classificationChart.instance.setOption(stackedOption(generalClassifications, 'clasificacion'));
    fitCascade(classificationChart);
    const classificationCard = document.getElementById('ventas-clasificacion-card');
    const classificationSelect = document.getElementById('ventas-clasificacion-desglose');
    let classificationStatusChart = null;
    function selectClassification(id) {
        const classification = generalClassifications.find(row => String(row.clasificacion_id) === String(id));
        classificationCard.hidden = !classification;
        classificationSelect.value = classification ? String(classification.clasificacion_id) : '';
        if (!classification) return;
        classificationCard.open = true;
        const statuses = (data.estatus_por_clasificacion || [])
            .filter(row => String(row.clasificacion_id) === String(classification.clasificacion_id))
            .sort((a, b) => Number(a.estatus_id) - Number(b.estatus_id));
        document.getElementById('ventas-clasificacion-card-titulo').textContent = classification.clasificacion +
            ' — ' + classification.proyectos + ' proyectos · Desglose por estatus';
        document.getElementById('ventas-clasificacion-card-resumen').textContent = statuses.length
            ? statuses.map(row => row.estatus + ': ' + row.proyectos + ' (' + row.declinados + ' declinados)').join(' | ')
            : 'Sin estatus registrados.';
        if (!classificationStatusChart) {
            classificationStatusChart = cascade('ventas-clasificacion-estatus');
            classificationStatusChart.height = 380;
            classificationStatusChart.slotWidth = 145;
            classificationStatusChart.element.style.height = '380px';
        }
        classificationStatusChart.count = statuses.length;
        const option = stackedOption(statuses, 'estatus');
        option.xAxis.axisLabel.fontSize = 10;
        option.xAxis.axisLabel.lineHeight = 14;
        option.yAxis.axisLabel.fontSize = 10;
        option.yAxis.nameTextStyle.fontSize = 10;
        option.legend.textStyle.fontSize = 10;
        option.series[1].label.fontSize = 10;
        option.grid.bottom = 110;
        classificationStatusChart.instance.setOption(option, true);
        fitCascade(classificationStatusChart);
    }
    classificationChart.instance.on('click', event => {
        if (event.componentType !== 'series') return;
        const row = generalClassifications[event.dataIndex];
        if (row) selectClassification(row.clasificacion_id);
    });
    classificationSelect.addEventListener('change', () => selectClassification(classificationSelect.value));
    classificationCard.addEventListener('toggle', () => {
        if (classificationCard.open && classificationStatusChart) fitCascade(classificationStatusChart);
    });
    window.addEventListener('resize', function () { charts.forEach(c => c.resize()); cascadeCharts.forEach(fitCascade); });

});
