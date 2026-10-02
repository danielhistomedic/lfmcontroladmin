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
    jQuery('#ventas-mensuales .ventas-tabla').DataTable({
        pageLength: 10, order: [], autoWidth: false,
        language: { emptyTable: 'Sin resultados para este período', search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ filas', info: '_START_ a _END_ de _TOTAL_ filas', infoEmpty: 'Sin filas',
            infoFiltered: '(de _MAX_ filas)', zeroRecords: 'No hay coincidencias',
            paginate: { first: 'Primera', last: 'Última', next: 'Siguiente', previous: 'Anterior' } }
    });
    const source = document.getElementById('ventas-mensuales-datos');
    if (!source) return;
    const data = JSON.parse(source.textContent);
    const charts = [];
    const amount = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 });
    const colors = ['#2385bd', '#27a58c', '#e5a543', '#8c6bb1', '#cf6478', '#428582', '#718bbd', '#ad7e56'];
    function chart(id, labels, series, horizontal) {
        const el = document.getElementById(id);
        const instance = echarts.init(el, typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? 'dark' : null);
        const compact = el.clientWidth < 500;
        const category = { type: 'category', data: labels, axisLabel: { width: compact ? 95 : 150, overflow: 'truncate' } };
        const value = { type: 'value', name: 'USD', axisLabel: { formatter: v => amount.format(v) } };
        const zoom = labels.length > 10 ? [{ type: 'slider', orient: horizontal ? 'vertical' : 'horizontal',
            [horizontal ? 'yAxisIndex' : 'xAxisIndex']: 0, start: 0, end: Math.min(100, 1000 / labels.length) }] : [];
        instance.setOption({ color: colors, backgroundColor: 'transparent',
            tooltip: { trigger: 'axis', renderMode: 'richText', valueFormatter: v => amount.format(v) + ' USD' },
            legend: { top: 0, type: 'scroll' }, grid: { left: horizontal ? (compact ? 110 : 180) : 70, right: labels.length > 10 && horizontal ? 60 : 25, top: 50, bottom: 60 },
            xAxis: horizontal ? value : category, yAxis: horizontal ? category : value,
            dataZoom: zoom, series: series, aria: { enabled: true } });
        charts.push(instance);
    }
    const bars = rows => ['cotizado', 'colocado'].map((key, i) => ({ name: i === 0 ? 'Cotizado' : 'Colocado', type: 'bar', data: rows.map(r => r[key]) }));
    chart('ventas-comparativo', ['Mes seleccionado'], bars([data]), false);
    chart('ventas-vendedores', data.vendedores.map(r => r.nombre), bars(data.vendedores), true);
    chart('ventas-productos', data.productos.map(r => r.nombre), bars(data.productos), true);
    // Series por vendedor, categorías por subclasificación: comparación del mismo producto entre vendedores.
    const products = [...new Map(data.cruce.map(r => [r.subclasificacion_id, r.nombre])).entries()];
    const sellers = [...new Map(data.cruce.map(r => [r.vendedor_id, r.vendedor])).entries()];
    const matrix = new Map(data.cruce.map(r => [JSON.stringify([r.vendedor_id, r.subclasificacion_id]), r.colocado]));
    chart('ventas-cruce', products.map(r => r[1]), sellers.map(([id, name]) => ({ name, type: 'bar',
        data: products.map(([sub]) => matrix.get(JSON.stringify([id, sub])) || 0) })), true);
    window.addEventListener('resize', function () { charts.forEach(c => c.resize()); });
    document.querySelectorAll('#ventas-mensuales details').forEach(el => el.addEventListener('toggle', () => charts.forEach(c => c.resize())));
});
