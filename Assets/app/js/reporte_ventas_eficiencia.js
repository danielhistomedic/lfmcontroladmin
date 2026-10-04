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
    const originalAmount = value => value != null && Number.isFinite(Number(value)) ? currency(Number(value)) : '—';
    const rate = data.tipo_cambio_aplicado;
    const rateText = rate != null && Number.isFinite(Number(rate)) ? '$ ' +
        new Intl.NumberFormat('es-MX',{minimumFractionDigits:2,maximumFractionDigits:8}).format(Number(rate)) : '—';
    const rateDate = String(data.fecha_tipo_cambio || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
    const exchangeText = 'TC: ' + rateText + ' | Fecha TC: ' +
        (rateDate ? rateDate[3]+'/'+rateDate[2]+'/'+rateDate[1] : '—');
    ['cotizado','colocado'].forEach(key => {
        const original = data[key + '_moneda_original'] || {};
        const prefix = key === 'cotizado' ? 'cotizado-' : '';
        byId(prefix + 'moneda-original').textContent = 'USD: ' + originalAmount(original.USD) + ' | MXN: ' + originalAmount(original.MXN);
        byId(prefix + 'tipo-cambio').textContent = exchangeText;
    });
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
    function conversion(id,quoted,placed,monetary) {
        const element = byId(id);
        const chart = echarts.init(element, dark ? 'dark' : null);
        const format = monetary ? value => currency(value)+' USD' : value => integer.format(value);
        const pending = Math.max(0,quoted-placed);
        const validRing = quoted > 0 && placed >= 0;
        const placedName = monetary ? 'Importe colocado' : 'Colocado';
        const pendingName = monetary ? 'Importe pendiente de colocar' : 'Pendiente de colocar';
        const percentage = ratio(placed,quoted);
        const pendingColor = dark ? '#3d5669' : '#dce7f0';
        element.setAttribute('aria-label',(monetary?'Colocación monetaria: ':'Colocación por cantidad: ')+percentage);
        chart.setOption({
            backgroundColor:'transparent',aria:{enabled:true},
            title:[{text:percentage,left:'center',top:'34%',textStyle:{color:textColor,fontSize:22,fontWeight:600}},
                {text:monetary?'Colocación monetaria':'Colocación',left:'center',top:'47%',textStyle:{color:dark?'#cbd5e1':'#62758e',fontSize:10,fontWeight:400}}],
            legend:{bottom:0,left:'center',type:'scroll',itemWidth:10,itemHeight:10,
                data:[placedName,pendingName],textStyle:{color:textColor,fontSize:10}},
            tooltip:{trigger:'item',renderMode:'richText',formatter:() => monetary ?
                'Importe cotizado: '+format(quoted)+'\nImporte colocado: '+format(placed)+
                    '\nImporte pendiente de colocar: '+format(pending) :
                'Cotizados: '+format(quoted)+'\nColocados: '+format(placed)+'\nPendientes: '+format(pending)},
            series:[{name:monetary?'Colocación monetaria':'Colocación por cantidad',type:'pie',
                radius:['52%','70%'],center:['50%','43%'],label:{show:false},labelLine:{show:false},
                emphasis:{scale:false},itemStyle:{borderRadius:4,borderWidth:2,borderColor:dark?'#21313e':'#fff'},
                // El anillo alcanza 100%; el centro y el tooltip conservan la conversión real.
                data:[{name:placedName,value:validRing?Math.min(placed,quoted):0,itemStyle:{color:'#198754'}},
                    {name:pendingName,value:validRing?pending:1,itemStyle:{color:pendingColor}}]}]
        });
        chart.on('click',showSellers);
        return chart;
    }
    const charts = [conversion('grafica',totals.cotizado,totals.colocado,false),
        conversion('grafica-importes',totals.importe_cotizado,totals.importe_colocado,true)];
    window.addEventListener('resize',()=>charts.forEach(chart=>chart.resize()));
});
