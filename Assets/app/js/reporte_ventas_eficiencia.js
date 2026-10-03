/* Cantidades del dashboard existente; no realiza consultas adicionales. */
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('ventas-financiero-eficiencia');
    const source = document.getElementById('ventas-mensuales-datos');
    if (!root || !source) return;
    const data = JSON.parse(source.textContent);
    const byId = id => document.getElementById('ventas-eficiencia-' + id);
    const integer = new Intl.NumberFormat('es-MX', {maximumFractionDigits:0});
    const decimal = new Intl.NumberFormat('es-MX', {minimumFractionDigits:2,maximumFractionDigits:2});
    const count = value => Number.isFinite(Number(value)) ? Math.max(0, Math.trunc(Number(value))) : 0;
    const ratio = (numerator, denominator) => denominator > 0 ? decimal.format(numerator / denominator * 100) + ' %' : '—';
    const quantities = data.cantidades || {};
    const totals = {
        proyectado:count(quantities.total_proyectos),
        cotizado:count(quantities.cotizacion_cliente),
        colocado:count(quantities.orden_compra_cliente)
    };
    Object.entries(totals).forEach(([key,value]) => { byId(key).textContent = integer.format(value); });
    byId('colocacion').textContent = ratio(totals.colocado, totals.cotizado);
    byId('eficiencia').textContent = ratio(totals.colocado, totals.proyectado);
    const sellers = new Map();
    function sellerFor(row) {
        const key = String(row.vendedor_id ?? '');
        if (!sellers.has(key)) sellers.set(key, {id:key,nombre:row.nombre || 'Sin vendedor',proyectado:0,cotizado:0,colocado:0});
        const seller = sellers.get(key);
        if (row.nombre) seller.nombre = row.nombre;
        return seller;
    }
    (data.proyectos_por_vendedor || []).forEach(row => { sellerFor(row).proyectado += count(row.proyectos); });
    (data.cotizados_por_periodo || []).forEach(row => { sellerFor(row).cotizado += count(row.proyectos); });
    (data.colocados_por_periodo || []).forEach(row => { sellerFor(row).colocado += count(row.proyectos); });
    const rows = [...sellers.values()].sort((a,b) => b.colocado-a.colocado || a.nombre.localeCompare(b.nombre,'es') || a.id.localeCompare(b.id));
    const body = byId('vendedores-filas');
    rows.forEach(seller => {
        const tr = document.createElement('tr');
        [seller.nombre,integer.format(seller.proyectado),integer.format(seller.cotizado),integer.format(seller.colocado),
            ratio(seller.colocado,seller.cotizado),ratio(seller.colocado,seller.proyectado)].forEach((value,index) => {
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
        td.colSpan = 6;
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
    const chart = echarts.init(byId('grafica'), dark ? 'dark' : null);
    chart.setOption({
        backgroundColor:'transparent',aria:{enabled:true},
        grid:{left:55,right:16,top:30,bottom:38},
        tooltip:{trigger:'axis',renderMode:'richText',valueFormatter:value=>integer.format(value)+' proyectos'},
        xAxis:{type:'category',data:['Proyectado','Cotizado','Colocado'],axisLabel:{color:textColor,fontSize:11}},
        yAxis:{type:'value',minInterval:1,name:'Proyectos',nameTextStyle:{color:textColor},axisLabel:{color:textColor,formatter:value=>integer.format(value)}},
        series:[{name:'Proyectos',type:'bar',barMaxWidth:48,label:{show:true,position:'top',color:textColor,formatter:params=>integer.format(params.value)},
            data:[{value:totals.proyectado,itemStyle:{color:'#78909c'}},{value:totals.cotizado,itemStyle:{color:'#2878c8'}},{value:totals.colocado,itemStyle:{color:'#198754'}}]}]
    });
    chart.on('click',showSellers);
    window.addEventListener('resize',()=>chart.resize());
});
