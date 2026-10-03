/* Cantidades e importes del dashboard existente; no realiza consultas adicionales. */
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('ventas-financiero-eficiencia');
    const source = document.getElementById('ventas-mensuales-datos');
    if (!root || !source) return;
    const data = JSON.parse(source.textContent);
    const byId = id => document.getElementById('ventas-eficiencia-' + id);
    const integer = new Intl.NumberFormat('es-MX', {maximumFractionDigits:0});
    const decimal = new Intl.NumberFormat('es-MX', {minimumFractionDigits:2,maximumFractionDigits:2});
    const count = value => Number.isFinite(Number(value)) ? Math.max(0, Math.trunc(Number(value))) : 0;
    const money = value => Number.isFinite(Number(value)) ? Number(value) : 0;
    const currency = value => '$ ' + decimal.format(value);
    const ratio = (numerator, denominator) => denominator !== 0 ? decimal.format(numerator / denominator * 100) + ' %' : '—';
    const quantities = data.cantidades || {};
    const totals = {
        cotizado:count(quantities.cotizacion_cliente),
        colocado:count(quantities.orden_compra_cliente),
        importe_cotizado:money(data.cotizado),importe_colocado:money(data.colocado)
    };
    ['cotizado','colocado'].forEach(key => { byId(key).textContent = integer.format(totals[key]); });
    ['cotizado','colocado'].forEach(key => { byId('importe-'+key).textContent = currency(totals['importe_'+key]); });
    byId('colocacion').textContent = ratio(totals.colocado, totals.cotizado);
    byId('colocacion-monetaria').textContent = ratio(totals.importe_colocado, totals.importe_cotizado);
    const sellers = new Map();
    function sellerFor(row) {
        const key = String(row.vendedor_id ?? '');
        if (!sellers.has(key)) sellers.set(key, {id:key,nombre:row.nombre || 'Sin vendedor',cotizado:0,colocado:0,importe_cotizado:0,importe_colocado:0});
        const seller = sellers.get(key);
        if (row.nombre) seller.nombre = row.nombre;
        return seller;
    }
    (data.proyectos_por_vendedor || []).forEach(sellerFor);
    (data.cotizados_por_periodo || []).forEach(row => { sellerFor(row).cotizado += count(row.proyectos); });
    (data.colocados_por_periodo || []).forEach(row => { sellerFor(row).colocado += count(row.proyectos); });
    (data.importes_por_vendedor || []).forEach(row => {
        const seller = sellerFor(row);
        seller.importe_cotizado += money(row.importe_cotizado);
        seller.importe_colocado += money(row.importe_colocado);
    });
    const rows = [...sellers.values()].sort((a,b) => b.colocado-a.colocado || a.nombre.localeCompare(b.nombre,'es') || a.id.localeCompare(b.id));
    const body = byId('vendedores-filas');
    rows.forEach(seller => {
        const tr = document.createElement('tr');
        [seller.nombre,integer.format(seller.cotizado),integer.format(seller.colocado),ratio(seller.colocado,seller.cotizado),
            currency(seller.importe_cotizado),currency(seller.importe_colocado),ratio(seller.importe_colocado,seller.importe_cotizado)].forEach((value,index) => {
            const td = document.createElement('td');
            td.textContent = value;
            if (index) td.className = 'text-end';
            tr.appendChild(td);
        });
        body.appendChild(tr);
    });
    if (!rows.length) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 7;
        td.textContent = 'No hay proyectos para los filtros seleccionados.';
        tr.appendChild(td); body.appendChild(tr);
    }
    function showSellers() {
        byId('vendedores').hidden = false;
        byId('general').setAttribute('aria-expanded','true');
    }
    byId('general').addEventListener('click', showSellers);
    byId('general').addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); showSellers(); }
    });
    if (typeof echarts === 'undefined') return;
    const dark = typeof theme_chart !== 'undefined' && theme_chart === 'dark';
    const textColor = dark ? '#edf2f7' : '#243447';
    function comparison(id,quoted,placed,monetary) {
    const chart = echarts.init(byId(id), dark ? 'dark' : null);
    const format = monetary ? currency : value => integer.format(value);
    chart.setOption({
        backgroundColor:'transparent',aria:{enabled:true},
        grid:{left:monetary?90:55,right:16,top:30,bottom:38},
        tooltip:{trigger:'axis',renderMode:'richText',valueFormatter:value=>format(value)+(monetary?' USD':' proyectos')},
        xAxis:{type:'category',data:['Cotizado','Colocado'],axisLabel:{color:textColor,fontSize:11}},
        yAxis:{type:'value',minInterval:monetary?undefined:1,name:monetary?'USD':'Proyectos',nameTextStyle:{color:textColor},axisLabel:{color:textColor,formatter:value=>format(value)}},
        series:[{name:monetary?'Importe (USD)':'Proyectos',type:'bar',barMaxWidth:48,label:{show:true,position:'top',color:textColor,fontSize:10,formatter:params=>format(params.value)},
            data:[{value:quoted,itemStyle:{color:'#2878c8'}},{value:placed,itemStyle:{color:'#198754'}}]}]
    });
    chart.on('click',showSellers);
    return chart;
    }
    const charts = [comparison('grafica',totals.cotizado,totals.colocado,false),
        comparison('grafica-importes',totals.importe_cotizado,totals.importe_colocado,true)];
    window.addEventListener('resize',()=>charts.forEach(chart=>chart.resize()));
});
