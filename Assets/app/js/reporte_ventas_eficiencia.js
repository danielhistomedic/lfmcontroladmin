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
    let charts = [];
    let sellerRows = [];
    let sellerPage = 0;
    const sellerPageSize = 15;
    function render(data, classificationLabel = 'TODOS') {
    byId('vendedores-etiqueta').textContent = 'Desglose por vendedor · ' + classificationLabel +
        (root.dataset?.periodo ? ' · ' + root.dataset.periodo : '');
    charts.forEach(chart => chart.dispose());
    charts = [];
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
    sellerRows = rows;
    sellerPage = 0;
    renderSellerTable();
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
    charts = [conversion('grafica',totals.cotizado,totals.colocado,false),
        conversion('grafica-importes',totals.importe_cotizado,totals.importe_colocado,true)];
    const evolutionElement = byId('evolucion');
    const evolution = echarts.init(evolutionElement, dark ? 'dark' : null);
    charts.push(evolution);
    const years = [...new Set((data.anios_seleccionados || []).map(Number))].sort((a,b)=>a-b);
    const months = [...new Set((data.meses_seleccionados || []).map(Number))].sort((a,b)=>a-b);
    const monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    function renderEvolution() {
        const periods = years.flatMap(year=>months.map(month=>({year,month})));
        const labels = periods.map(period=>monthNames[period.month-1]+(years.length>1?' '+period.year:''));
        const rows = periods.map(period => (data.evolucion_mensual || []).find(row =>
            Number(row.anio) === period.year && Number(row.mes) === period.month) || {});
        evolution.setOption({backgroundColor:'transparent',aria:{enabled:true},
            legend:{top:0,type:'scroll',textStyle:{color:textColor,fontSize:10}},
            grid:{left:12,right:12,top:65,bottom:35,containLabel:true},
            xAxis:{type:'category',data:labels,axisLabel:{color:textColor,fontSize:10,formatter:value=>value.slice(0,3)+(years.length>1?' '+value.slice(-4):'')}},
            yAxis:[{type:'value',name:'Proyectos',minInterval:1,axisLabel:{color:textColor},nameTextStyle:{color:textColor}},
                {type:'value',name:'USD',axisLabel:{color:textColor,formatter:value=>currency(value)},nameTextStyle:{color:textColor},splitLine:{show:false}}],
            tooltip:{trigger:'axis',renderMode:'richText',formatter:items=>{
                const index = items[0]?.dataIndex ?? 0; const row = rows[index];
                const quoted=count(row.cotizado),placed=count(row.colocado);
                const quotedAmount=money(row.importe_cotizado),placedAmount=money(row.importe_colocado);
                const period = periods[index];
                if (!period) return '';
                return monthNames[period.month-1]+' '+period.year+'\nCotizados: '+integer.format(quoted)+'\nColocados: '+integer.format(placed)+
                    '\nImporte Cotizado USD: '+currency(quotedAmount)+'\nImporte Colocado USD: '+currency(placedAmount)+
                    '\n% Colocación: '+ratio(placed,quoted)+'\n% Colocación Monetaria: '+ratio(placedAmount,quotedAmount);
            }},
            series:[{name:'Proyectos cotizados',type:'bar',yAxisIndex:0,itemStyle:{color:'#2878c8',borderRadius:[3,3,0,0]},data:rows.map(row=>count(row.cotizado))},
                {name:'Proyectos colocados',type:'bar',yAxisIndex:0,itemStyle:{color:'#198754',borderRadius:[3,3,0,0]},data:rows.map(row=>count(row.colocado))},
                {name:'Importe cotizado USD',type:'line',yAxisIndex:1,itemStyle:{color:'#2878c8'},symbolSize:6,data:rows.map(row=>money(row.importe_cotizado))},
                {name:'Importe colocado USD',type:'line',yAxisIndex:1,itemStyle:{color:'#198754'},symbolSize:6,data:rows.map(row=>money(row.importe_colocado))}]
        });
    }
    renderEvolution();
    }
    function sellerCells(seller) {
        return [seller.nombre,integer.format(seller.cotizado),integer.format(seller.colocado),ratio(seller.colocado,seller.cotizado),
            currency(seller.importe_cotizado),currency(seller.importe_colocado),ratio(seller.importe_colocado,seller.importe_cotizado)];
    }
    function renderSellerTable() {
        const search = String(byId('vendedores-buscar').value || '').trim().toLocaleLowerCase('es');
        const order = byId('vendedores-orden').value || 'importe_cotizado';
        const score = seller => order === 'colocacion' ? (seller.cotizado ? seller.colocado/seller.cotizado : null) :
            order === 'colocacion_monetaria' ? (seller.importe_cotizado ? seller.importe_colocado/seller.importe_cotizado : null) :
            (order === 'importe_colocado' ? seller.importe_colocado : order === 'importe_cotizado' ? seller.importe_cotizado : seller.colocado);
        const rows = sellerRows.filter(seller=>!search || sellerCells(seller).some(value=>String(value).toLocaleLowerCase('es').includes(search)));
        rows.sort((a,b)=>{
            const name = a.nombre.localeCompare(b.nombre,'es') || a.id.localeCompare(b.id);
            if(order === 'nombre') return name;
            const aa=score(a),bb=score(b);
            if(aa===null || bb===null) return aa===bb ? name : (aa===null ? 1 : -1);
            return bb-aa || name;
        });
        sellerPage = Math.min(sellerPage,Math.max(0,Math.ceil(rows.length/sellerPageSize)-1));
        const start=sellerPage*sellerPageSize;
        const page=rows.slice(start,start+sellerPageSize);
        const body=byId('vendedores-filas'); body.replaceChildren();
        page.forEach(seller=>{
            const tr=document.createElement('tr');
            if(seller.colocado === 0) tr.className='ventas-eficiencia-sin-colocacion';
            sellerCells(seller).forEach((value,index)=>{
                const td=document.createElement('td');td.textContent=value;if(index)td.className='text-end';tr.appendChild(td);
            });body.appendChild(tr);
        });
        if(!page.length){
            const tr=document.createElement('tr'),td=document.createElement('td');td.colSpan=7;
            td.textContent='No hay proyectos para los filtros seleccionados.';tr.appendChild(td);body.appendChild(tr);
        }
        byId('vendedores-pagina').textContent=rows.length ? (start+1)+'–'+(start+page.length)+' de '+rows.length : '0 resultados';
        byId('vendedores-anterior').disabled=sellerPage===0;
        byId('vendedores-siguiente').disabled=start+sellerPageSize>=rows.length;
    }
    byId('vendedores-buscar').addEventListener('input',()=>{sellerPage=0;renderSellerTable();});
    byId('vendedores-orden').addEventListener('change',()=>{sellerPage=0;renderSellerTable();});
    byId('vendedores-anterior').addEventListener('click',()=>{sellerPage=Math.max(0,sellerPage-1);renderSellerTable();});
    byId('vendedores-siguiente').addEventListener('click',()=>{sellerPage++;renderSellerTable();});
    function showSellers() {
        byId('vendedores').hidden = false;
        byId('general').setAttribute('aria-expanded','true');
    }
    byId('general').addEventListener('click',showSellers);
    byId('general').addEventListener('keydown',event=>{
        if(event.key==='Enter'||event.key===' '){event.preventDefault();showSellers();}
    });
    byId('evolucion').addEventListener('click',event=>event.stopPropagation());
    const filter = byId('filtro-clasificacion');
    filter.addEventListener('click',event=>event.stopPropagation());
    filter.addEventListener('keydown',event=>event.stopPropagation());
    const catalog = [...(data.clasificaciones_comparativo || [])];
    const order = ['DIVERSOS','BOMBAS FLOWSERVE','VALVULAS FLOWSERVE','SELLOS FLOWSERVE'];
    const normalized = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g,'').trim().toUpperCase();
    catalog.sort((a,b)=>{
        const ai=order.indexOf(normalized(a.clasificacion)),bi=order.indexOf(normalized(b.clasificacion));
        return (ai<0?99:ai)-(bi<0?99:bi) || String(a.clasificacion).localeCompare(String(b.clasificacion),'es');
    });
    const flowserveIds = catalog.filter(row=>order.slice(1).includes(normalized(row.clasificacion))).map(row=>row.id);
    function filtered(classIds) {
        const ids = new Set(classIds.map(String));
        const rows=(data.comparativo_clasificacion_local || []).filter(row=>ids.has(String(row.clasificacion_id)));
        const totals={cotizado:0,colocado:0,importe_cotizado:0,importe_colocado:0,cotizado_usd:0,cotizado_mxn:0,colocado_usd:0,colocado_mxn:0};
        rows.forEach(row=>Object.keys(totals).forEach(key=>totals[key]+=money(row[key])));
        const monthly = new Map();
        (data.evolucion_clasificacion || []).filter(row=>ids.has(String(row.clasificacion_id))).forEach(row=>{
            const key=String(row.anio)+'-'+String(row.mes);
            if(!monthly.has(key))monthly.set(key,{anio:row.anio,mes:row.mes,cotizado:0,colocado:0,importe_cotizado:0,importe_colocado:0});
            ['cotizado','colocado','importe_cotizado','importe_colocado'].forEach(metric=>monthly.get(key)[metric]+=money(row[metric]));
        });
        return {...data,cotizado:totals.importe_cotizado,colocado:totals.importe_colocado,
            cantidades:{cotizacion_cliente:totals.cotizado,orden_compra_cliente:totals.colocado},
            cotizado_moneda_original:{USD:totals.cotizado_usd,MXN:totals.cotizado_mxn},
            colocado_moneda_original:{USD:totals.colocado_usd,MXN:totals.colocado_mxn},
            proyectos_por_vendedor:rows,
            cotizados_por_periodo:rows.map(row=>({...row,proyectos:row.cotizado})),
            colocados_por_periodo:rows.map(row=>({...row,proyectos:row.colocado})),
            importes_por_vendedor:rows,
            evolucion_mensual:[...monthly.values()]};
    }
    const options = [{id:'',clasificacion:'TODOS'},...catalog];
    const diverseIndex = options.findIndex(row=>normalized(row.clasificacion)==='DIVERSOS');
    options.splice(diverseIndex >= 0 ? diverseIndex+1 : 1,0,{id:'flowserve',clasificacion:'FLOWSERVE'});
    options.forEach(row=>{
        const button=document.createElement('button');button.type='button';button.textContent=String(row.clasificacion).toUpperCase();
        button.setAttribute('aria-pressed',String(row.id===''));
        button.addEventListener('click',()=>{
            [...filter.children].forEach(item=>item.setAttribute('aria-pressed',String(item===button)));
            render(row.id===''?data:filtered(row.id==='flowserve'?flowserveIds:[row.id]), button.textContent);
        });filter.appendChild(button);
    });
    byId('vendedores-orden').value = 'importe_cotizado';
    showSellers();
    render(data);
    window.addEventListener('resize',()=>charts.forEach(chart=>chart.resize()));
});
