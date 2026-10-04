/* Validación sin navegador, base de datos ni dependencias nuevas: Node + ECharts del portal. */
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
// Node 24 expone navigator; ECharts 5 necesita reconocer este proceso como servidor.
Object.defineProperty(globalThis, 'navigator', { value: undefined });
const echarts = require('../../Assets/vendor/echarts/dist/echarts.js');
const code = fs.readFileSync(path.join(__dirname, '../../Assets/app/js/reporte_ventas_mensuales.js'), 'utf8');

function ejecutar(empty, width, theme, periods = false, quoted = false, placed = false, groupedDeclines = false) {
    const nodes = new Map();
    const charts = []; const events = {}; const formEvents = {}; let change; let requests = 0;
    const button = { disabled: false }; const loading = { hidden: true };
    const form = { addEventListener: (name, cb) => { formEvents[name] = cb; }, querySelector: () => button,
        requestSubmit: () => { requests++; formEvents.submit({ preventDefault() {} }); } };
            const data = { cotizado: empty ? 0 : 150, colocado: empty ? 0 : 120, proyectos_por_vendedor: empty ? [] : [{vendedor_id:'V1',nombre:'Vendedor 1',proyectos:8},{vendedor_id:'V2',nombre:'Vendedor 2',proyectos:2}] };
    data.clasificaciones_por_vendedor = empty ? [] : [
        {vendedor_id:'V1',clasificacion_id:5,clasificacion:'Sellos',proyectos:5,declinados:2},
        {vendedor_id:'V1',clasificacion_id:3,clasificacion:'Bombas',proyectos:3,declinados:0},
        {vendedor_id:'V2',clasificacion_id:3,clasificacion:'Bombas',proyectos:2,declinados:2}];
    data.estatus_por_vendedor = empty ? [] : [
        {vendedor_id:'V1',estatus_id:6,estatus:'Pedido',proyectos:5,declinados:2},
        {vendedor_id:'V1',estatus_id:3,estatus:'Cotizacion',proyectos:3,declinados:0},
        {vendedor_id:'V2',estatus_id:6,estatus:'Pedido',proyectos:2,declinados:2}];
    data.estatus_por_clasificacion = empty ? [] : [{vendedor_id:'V2',clasificacion_id:3,estatus_id:6,estatus:'Pedido',proyectos:2,declinados:2},{clasificacion_id:3,estatus_id:3,estatus:'Cotizacion',proyectos:3,declinados:0},{clasificacion_id:5,estatus_id:6,estatus:'Pedido',proyectos:5,declinados:2}];
    data.anios_seleccionados = [2024,2026];
    data.meses_seleccionados = [2,9];
    if (periods) data.clasificaciones_por_vendedor[0].proyectos = 9;
    if (periods) data.estatus_por_clasificacion = [
        {vendedor_id:'V2',clasificacion_id:3,anio:2026,mes:9,estatus_id:1,estatus:'Oportunidad',proyectos:2,declinados:1},
        {clasificacion_id:3,anio:2026,mes:9,estatus_id:3,estatus:'Cotizacion',proyectos:2,declinados:0},
        {clasificacion_id:3,anio:2024,mes:2,estatus_id:6,estatus:'Pedido',proyectos:2,declinados:1},
        {clasificacion_id:3,anio:2026,mes:9,estatus_id:6,estatus:'Pedido',proyectos:1,declinados:0},
        {clasificacion_id:3,anio:2026,mes:9,estatus_id:11,estatus:'Facturado',proyectos:2,declinados:1}];
    data.estatus_por_clasificacion = data.estatus_por_clasificacion.map(row => ({anio:2026,mes:9,vendedor_id:'V1',...row}));
    if (groupedDeclines) data.estatus_por_clasificacion.find(row=>row.vendedor_id==='V1' && row.clasificacion_id===3).declinados=1;
    if (quoted) {
        data.estatus_por_clasificacion.push({anio:2026,mes:9,vendedor_id:'V1',clasificacion_id:3,estatus_id:5,
            estatus:'PEDIDO COTIZADO (SIN OC CLIENTE)',proyectos:99,declinados:1});
        data.cotizados_por_periodo = [
            {anio:2026,mes:9,vendedor_id:'V1',origen:'periodo',proyectos:3},
            {anio:2026,mes:9,vendedor_id:'V1',origen:'anteriores',proyectos:2},
            {anio:2026,mes:9,vendedor_id:'V2',origen:'periodo',proyectos:4},
            {anio:2026,mes:9,vendedor_id:'V2',origen:'anteriores',proyectos:1}];
    }
    if (placed) data.colocados_por_periodo = [
        {anio:2026,mes:9,vendedor_id:'V1',origen:'periodo',proyectos:3},
        {anio:2026,mes:9,vendedor_id:'V1',origen:'anteriores',proyectos:2},
        {anio:2026,mes:9,vendedor_id:'V2',origen:'anteriores',proyectos:1}];
    // Simular los agregados del modelo a partir de proyectos disjuntos por mes.
    const classNames=new Map(data.clasificaciones_por_vendedor.map(row=>[row.clasificacion_id,row.clasificacion]));
    const classTotals=new Map(),statusTotals=new Map();
    data.estatus_por_clasificacion.forEach(row=>{
        if(!data.anios_seleccionados.includes(row.anio)||!data.meses_seleccionados.includes(row.mes))return;
        [[classTotals,'clasificacion_id','clasificacion'],[statusTotals,'estatus_id','estatus']].forEach(([totals,id,label])=>{
            const key=row.vendedor_id+':'+row[id];
            if(!totals.has(key))totals.set(key,{vendedor_id:row.vendedor_id,[id]:row[id],
                [label]:label==='clasificacion'?classNames.get(row[id]):row[label],proyectos:0,declinados:0});
            const total=totals.get(key);total.proyectos+=row.proyectos;total.declinados+=row.declinados;
        });
    });
    data.clasificaciones_por_vendedor=[...classTotals.values()];
    data.estatus_por_vendedor=[...statusTotals.values()];
    const context = {
        document: {
            createElement: () => ({value:'',textContent:''}),
            addEventListener: (name, cb) => { events[name] = cb; },
            getElementById: id => id === 'modal-declinados-ventas' && !nodes.has('ventas-clasificaciones-general') ? null : id === 'filtros-ventas-mensuales' ? form : id === 'ventas-cargando' ? loading :
                id === 'ventas-mensuales-datos' ? { textContent: JSON.stringify(data) } : (nodes.has(id)?nodes.get(id):(nodes.set(id,{ id, clientWidth: width, style: {}, dataset:{}, children:[], replaceChildren(){this.children=[];}, appendChild(child){this.children.push(child);this.value=this.children[0].value;}, events:{}, addEventListener(name,cb){this.events[name]=cb;} }),nodes.get(id))),
            querySelectorAll: () => []
        },
        window: { addEventListener: (name, cb) => { events[name] = cb; } },
        bootstrap:{Modal:{getOrCreateInstance:el=>({show(){el.shown=true;}})}},
        jQuery: () => ({ find: () => ({ on: (name, cb) => { change = cb; } }), DataTable: () => {} }),
        echarts: { init: (element, selectedTheme) => {
            // ARIA utiliza atributos del contenedor DOM incluso al renderizar SVG en servidor.
            const root = { setAttribute() {}, getAttribute() { return null; } };
            const chart = echarts.init(root, selectedTheme, { renderer: 'svg', ssr: true, width, height: 340 });
            charts.push(chart); return chart;
        } }, theme_chart: theme, Intl, Map, JSON
    };
    vm.runInNewContext(code, context, { filename: 'reporte_ventas_mensuales.js' });
    events.DOMContentLoaded();
    assert.equal(charts.length, 3, 'Inicializar las tres gráficas generales restantes');
    assert.ok(!nodes.has('ventas-comparativo'), 'No inicializar el comparativo retirado');
    if (groupedDeclines) {
        charts[0].trigger('click',{componentType:'series',dataIndex:0});
        [3,4].forEach(index=>{
            const option=charts[index].getOption();
            const slices=option.series[0].data;
            const declined=slices.filter(row=>row.name==='Declinados');
            assert.equal(declined.length,1,'Una sola seccion roja para todas las categorias');
            assert.equal(declined[0].value,3,'Sumar declinados de distintos estatus/clasificaciones');
            assert.equal(declined[0].itemStyle.color,'#dc3545');
            assert.equal(slices.reduce((sum,row)=>sum+row.value,0),8,'Sin duplicar declinados en su categoria original');
            assert.equal(slices.filter(row=>row.name!=='Declinados').reduce((sum,row)=>sum+row.value,0),5);
            assert.equal(option.title[0].text,'8','Total central conservado');
            assert.ok(option.tooltip[0].formatter({data:declined[0]}).includes('Declinados: 3 (37.5 %)'));
            assert.ok(!charts[index].renderToSVGString().includes('NaN'));
        });
        charts.forEach(chart=>chart.dispose()); return;
    }
    for (const chart of charts) {
        const svg = chart.renderToSVGString();
        assert.ok(svg.includes('<svg'), 'Renderizar con ECharts instalado');
        assert.ok(!svg.includes('NaN'), 'Sin geometría inválida en vacío ni móvil');
    }
    assert.equal(data.clasificaciones_por_vendedor.reduce((sum,row)=>sum+row.proyectos,0),
        data.estatus_por_vendedor.reduce((sum,row)=>sum+row.proyectos,0),'Ambos pasteles representan el mismo conjunto');
    [1,2].forEach(index=>{
        const option=charts[index].getOption(),slices=option.series[0].data;
        assert.equal(option.series[0].type,'pie');
        assert.equal(option.series[0].radius,'65%');
        assert.equal(option.xAxis,undefined,'Sin eje mensual');assert.equal(option.yAxis,undefined);
        const source=index===1?data.clasificaciones_por_vendedor:data.estatus_por_vendedor;
        const total=source.reduce((sum,row)=>sum+row.proyectos,0),declined=source.reduce((sum,row)=>sum+row.declinados,0);
        assert.equal(slices.reduce((sum,row)=>sum+row.value,0),total,'Conciliar proyectos registrados del periodo');
        assert.equal(slices.filter(row=>row.name==='Declinados').length,declined?1:0);
        if(declined){const slice=slices.find(row=>row.name==='Declinados');
            assert.equal(slice.value,declined);assert.equal(slice.itemStyle.color,'#dc3545');
            assert.ok(option.tooltip[0].formatter({data:slice}).includes(' | '+declined+' | '));
            charts[index].trigger('click',{componentType:'series',data:slice});
            assert.equal(nodes.get('modal-declinados-ventas').dataset.desgloseLista,'declinados');
        }
        const slice=slices.find(row=>row.name!=='Declinados');
        if(slice){charts[index].trigger('click',{componentType:'series',data:slice});
            const modal=nodes.get('modal-declinados-ventas');
            assert.equal(modal.dataset.desgloseLista,index===1?'clasificacion_periodo':'estatus_periodo');
            assert.equal(modal.dataset.desgloseAnio,'2024,2026');assert.equal(modal.dataset.desgloseMes,'2,9');
            assert.equal(modal.dataset.segmento,'no_declinados');}
    });
    if(placed||quoted||periods){
        charts[0].trigger('click',{componentType:'series',dataIndex:0});
        if(placed)assert.equal(charts[4].getOption().series[0].data.find(row=>row.name==='Pedidos Colocados').total,5);
        if(quoted)assert.equal(charts[4].getOption().series[0].data.find(row=>row.name==='PEDIDO COTIZADO (SIN OC CLIENTE)').total,99);
        if(periods){assert.deepEqual(charts[3].getOption().series[0].data.map(row=>row.value),[5,2]);
            assert.deepEqual(charts[4].getOption().series[0].data.map(row=>row.value),[2,3,2]);}
        else {const dropdown=nodes.get('ventas-vendedor-desglose');dropdown.value='1';dropdown.events.change();
            assert.deepEqual(charts[4].getOption().series[0].data.map(row=>row.value),[2]);}
        charts.forEach(chart=>{assert.ok(!chart.renderToSVGString().includes('NaN'));chart.dispose();});return;
    }
    const counts=charts[0].getOption();
    assert.equal(counts.yAxis[0].name,'Proyectos');
    assert.equal(counts.yAxis[0].minInterval,1);
    assert.equal((counts.dataZoom || []).length,0,'Sin control lateral en cantidades por vendedor');
    if (!empty) {
        assert.deepEqual(counts.series[0].data.map(row=>row.value),[8,2]);

    }
    if (!empty) {
        const dropdown=nodes.get('ventas-vendedor-desglose');
        charts[0].trigger('click',{componentType:'series',dataIndex:0});
        assert.equal(nodes.get('ventas-estatus-panel').hidden,false,'Click en barra abre el desglose');
        dropdown.value='0'; dropdown.events.change();
        assert.equal(charts.length,5,'Segunda grafica debajo sin reemplazar la primera');
        [3,4].forEach(index=>{
            const option=charts[index].getOption();
            assert.equal(option.series[0].type,'pie');
            assert.equal(option.xAxis,undefined,'Sin desglose visual por mes');
            assert.equal(option.title[0].text,'8','Total de todo el periodo en el centro');
            assert.equal(option.series[0].data.reduce((sum,row)=>sum+row.value,0),8,'Distribucion concilia con total');
            assert.ok(option.tooltip[0].formatter({data:option.series[0].data[0]}).includes('%'));
            assert.ok(!charts[index].renderToSVGString().includes('NaN'));
        });
        assert.deepEqual(charts[3].getOption().series[0].data.map(row=>row.value),[3,3,2]);
        assert.equal(charts[3].getOption().series[0].data[2].itemStyle.color,'#dc3545');
        assert.equal(nodes.get('ventas-estatus-titulo').textContent,'Vendedor 1 — 8 proyectos');
        assert.equal(charts[0].getOption().series[0].data[0].itemStyle.color,'#d48825');
        dropdown.value='1'; dropdown.events.change();
        assert.equal(charts.length,5,'Reutilizar grafica secundaria');
        assert.equal(nodes.has('ventas-vendedor-mensual'),false);
        dropdown.value='1'; dropdown.events.change();
        [3,4].forEach(index=>{
            const option=charts[index].getOption();
            assert.equal(option.title[0].text,'2');
            assert.equal(option.series[0].data.length,1);
            assert.equal(option.series[0].data[0].value,2);
            assert.equal(option.series[0].data[0].itemStyle.color,'#dc3545');
        });
    }
    if (!empty) {
        charts[1].trigger('click',{componentType:'series',dataIndex:3,seriesIndex:0});
        assert.equal(nodes.get('modal-declinados-ventas').dataset.segmento,'no_declinados');
        assert.equal(nodes.get('modal-declinados-ventas').shown,true);
        assert.equal(charts.length,5,'Modal conserva las graficas existentes sin agregar A ni B');
    }
    change.call({id:'ventas-mes'}); change.call({id:'ventas-mes'});
    change.call({id:'ventas-anio'}); change.call({id:'ventas-anio'});
    assert.equal(requests, 0, 'Elegir varios meses antes de aplicar la seleccion');
    change(); change();
    assert.equal(requests, 1, 'Un solo envío global mientras está cargando');
    assert.equal(loading.hidden, false, 'Mostrar estado de carga');
    assert.equal(button.disabled, true, 'Evitar envíos duplicados');
    events.pageshow();
    assert.equal(button.disabled, false, 'Recuperar controles al volver a la página');
    events.resize();
    charts.forEach(chart => chart.dispose());
}
ejecutar(false, 900, 'walden', true);
ejecutar(false, 900, 'walden', false, false, false, true);
ejecutar(false, 320, 'dark', false, false, false, true);
ejecutar(false, 900, 'walden', false, false, true);
ejecutar(false, 320, 'dark', false, false, true);
ejecutar(false, 900, 'walden', false, true);
ejecutar(false, 320, 'dark', false, true);
ejecutar(false, 320, 'dark', true);
ejecutar(false, 900, 'walden');
ejecutar(false, 320, 'dark');
ejecutar(true, 900, 'walden');
ejecutar(true, 320, 'dark');
console.log('OK: pasteles del periodo, estatus actuales, declinados unificados, detalle, donas por vendedor, movil, oscuro y filtros globales.');

async function probarModal(critical = false, unscoped = false) {
    const nodes = new Map(); const events = {}; const calls = []; let response; let options; let pending; let delegated;
    function node(id) {
        if (!nodes.has(id)) nodes.set(id, { id, children: [], events: {}, textContent: '', dataset: {}, style: {}, disabled: false,
            addEventListener(name, cb) { this.events[name] = cb; }, setAttribute(key,value) { this[key]=value; },
            appendChild(child) { this.children.push(child); }, replaceChildren() { this.children = []; },
            click() { this.clicked = true; }
        });
        return nodes.get(id);
    }
    const form = node('filtros-ventas-mensuales'); form.querySelector = () => node('submit');
    const modal = node('modal-declinados-ventas');
    modal.dataset = { url: '/portal/reportesmensuales/declinados', anio: '2026', mes: '9', vendedor: unscoped ? '' : 'V1' };
    const source = node('ventas-mensuales-datos');
    source.textContent = JSON.stringify({ cotizado: 0, colocado: 0, vendedores: [], productos: [], cruce: [] });
    const cards = [node('declinados-card'), node('critico-card')];
    const tableNode = node('table-declinados-ventas');
    tableNode.tHead = { rows: [{ cells: ['No.','ID Proyecto','Fecha','Cliente','Vendedor','Clasificación','Título','Activo','Seguimientos'].map(textContent => ({textContent})) }], appendChild(child) { this.filterRow=child; } };
    const requestData = { draw: 1, start: 0, length: 10, search: { value: '' }, order: [],
        columns: Array.from({ length: 8 }, () => ({ search: { value: '' } })) };
    let result;
    function draw() { pending = options.ajax(requestData, payload => { result = payload; }); return pending; }
    const columns = () => ({search(){requestData.columns.forEach(column=>column.search.value='');}}); columns.adjust=()=>{};
    const dt = { columns, search(value){requestData.search.value=value;}, order(value){requestData.order=value;}, ajax: { reload() { requestData.start=0; return draw(); } }, table: () => ({ container: () => node('container') }),
        row: () => ({ data: () => ({id:633, proyecto_id:'PV-2026-20035'}) }),
        column: index => ({visible(){}, search(value) { requestData.columns[index].search.value = value; return { draw }; } }) };
    const jquery = element => ({ find: () => ({ on() {},val(){} }), on: (name, selector, handler) => { delegated = handler; },
        DataTable(configuration) { if (element === tableNode) { options = configuration; draw(); return dt; } } });
    jquery.fn = { dataTable: { render: { text: () => ({ display: value => value.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') }) } } };
    const summaryCalls=[]; let exported;
    jquery.fn.dataTable.ext={buttons:{excelHtml5:{action(event,table,button,configuration){
        exported={header:[],body:[],footer:[]};configuration.customizeData(exported);
    }}}};
    const context = {
        document: { addEventListener: (name, cb) => { events[name] = cb; }, getElementById: node,
            createElement: tag => node('created-'+Math.random()),
            querySelectorAll: selector => selector.includes('.ventas-abrir-declinados') ? cards : [] },
        window: { addEventListener() {}, verSeguimientosProyecto(id,project) { context.selected=[id,project]; } }, jQuery: jquery,
        bootstrap: { Modal: { getOrCreateInstance: () => ({hide() {}, show() {}}) } },
        echarts: { init: () => ({ setOption() {}, resize() {}, on() {} }) },
        fetch: async (url, settings) => {
            if(new URL(url,'http://localhost').searchParams.get('resumen')==='1'){
                summaryCalls.push(url);return {ok:true,json:async()=>({status:true,data:{total:8,
                    vendedores:unscoped ? [{vendedor_id:'V1',nombre:'José',proyectos:5,porcentaje:62.5},{vendedor_id:'V2',nombre:'Ana',proyectos:3,porcentaje:37.5}]
                        : [{vendedor_id:'V1',nombre:'José',proyectos:8,porcentaje:100}]}})};
            }
            calls.push([url, settings]); return { ok: response.status, json: async () => response };
        },
        AbortController, URLSearchParams, Intl, Map, JSON, setTimeout, clearTimeout
    };
    vm.runInNewContext(code, context); events.DOMContentLoaded();
    assert.equal(calls.length, 0, 'No consultar antes de abrir');
    for (const card of cards) {
        card.events.keydown({ key: 'Enter', preventDefault() {} });
        assert.ok(card.clicked, 'Ambas tarjetas accesibles con teclado');
    }
    response = { status: true, data: { draw: 1, recordsTotal: 8, recordsFiltered: 8,
        data: [{ proyecto_id: 'P1', fecha: '2026-09-30', titulo: '<img src=x onerror=alert(1)>', cliente: 'Cliente', vendedor: 'José', clasificacion: 'Diversos', activo: 'CERRADO' }] } };
    modal.events['show.bs.modal']({relatedTarget:{dataset:critical?{lista:'cotizados_periodo'}:{}}});
    await modal.events['shown.bs.modal']();
    if(!critical){
        assert.equal(summaryCalls.length,1);assert.equal(options,undefined,'Resumen antes de cargar tabla plana');
        assert.equal(node('declinados-detalle').hidden,false);
        assert.equal(node('declinados-detalle-tabla').hidden,true,'Detalle derecho con mensaje inicial');
        assert.equal(node('declinados-resumen-total').textContent,'8');
        assert.equal(node('declinados-resumen-lider').textContent,'José');
        assert.equal(node('declinados-resumen-lider-detalle').textContent,unscoped ? '5 proyectos (62.5 %)' : '8 proyectos (100 %)');
        node('declinados-vendedores').children[0].events.click();
        assert.equal(node('declinados-vendedores').children[0]['aria-pressed'],'true');
        assert.equal(node('declinados-detalle').hidden,false);
    }
    await pending;
    assert.equal(options.serverSide, true);
    assert.ok(options.dom.includes('declinados-length"l') && options.dom.includes('declinados-buttons"B') && options.dom.includes('declinados-search ms-auto"f'), 'Separar cantidad, botones y búsqueda en la barra');
    assert.equal(options.buttons[1].extend, 'colvis');
    assert.equal(tableNode.tHead.filterRow.children.length, 9, 'Cabecera con filtros');
    const query = new URL(calls[0][0], 'http://localhost').searchParams;
    assert.deepEqual(query.getAll('anio[]'), ['2026']);
    assert.equal(query.get('vendedor'), unscoped ? '' : 'V1');
    assert.deepEqual(query.getAll('mes[]'), ['9'], 'Filtrar meses y vendedor');
    if(!critical)assert.equal(query.get('declinado_vendedor'),'V1','Seleccion local separada del vendedor global');
    if(unscoped){
        node('declinados-vendedores').children[1].events.click();await pending;
        assert.equal(new URL(calls.pop()[0],'http://localhost').searchParams.get('declinado_vendedor'),'V2');
        assert.equal(node('declinados-vendedores').children[0]['aria-pressed'],'false');
        assert.equal(node('declinados-vendedores').children[1]['aria-pressed'],'true');
        assert.equal(node('declinados-detalle-titulo').textContent,'Proyectos de: Ana');
        assert.equal(node('declinados-detalle-cantidad').textContent,'8 proyectos declinados');
        node('declinados-vendedores').children[0].events.click();await pending;calls.pop();
        assert.equal(summaryCalls.length,1,'Cambiar vendedor actualiza solo detalle');
    }
    modal.dataset.mes = '2,9,12';
    modal.dataset.anio = '2024,2026';
    await dt.ajax.reload();
    const multiQuery = new URL(calls.pop()[0], 'http://localhost').searchParams;
    assert.deepEqual(multiQuery.getAll('mes[]'), ['2','9','12'], 'Enviar todos los meses a los modales');
    assert.deepEqual(multiQuery.getAll('anio[]'), ['2024','2026'], 'Enviar todos los anios a los modales');
    assert.equal(node('declinados-total').textContent, '8 Proyectos');
    const titleHtml=options.columns[6].render(response.data.data[0].titulo,'display');
    assert.ok(titleHtml.includes('&lt;img src=x onerror=alert(1)&gt;') && !titleHtml.includes('<img'),'Salida escapada');
    if(!critical){
        assert.ok(titleHtml.includes('title="'),'Texto completo disponible en tooltip');
        assert.ok(options.columns[6].render('" onmouseover="alert(1)','display').includes('&quot;'),'Escapar comillas en tooltip');
        assert.ok(options.columns[5].render('VÁLVULAS FLOWSERVE','display').includes('declinados-clasificacion-valvulas'));
        assert.ok(options.columns[5].render('BOMBAS FLOWSERVE','display').includes('declinados-clasificacion-bombas'));
        assert.ok(options.columns[5].render('SELLOS FLOWSERVE','display').includes('declinados-clasificacion-sellos'));
        assert.equal(node('declinados-vendedores').children[0].children[2].children[0].style.width,unscoped?'62.5%':'100%');
    }
    assert.equal(options.columns[2].render('2026-09-30','display'), '30/09/2026');
    assert.ok(options.columns[critical ? 8 : 7].render('CERRADO','display').includes('ventas-ver-seguimientos'), 'Activo como boton de seguimiento');
    assert.ok(options.columns[7].render('CERRADO','display').includes('btn-danger'),'Declinados con boton rojo');
    assert.ok(options.columns[7].render('ACTIVO','display').includes('btn-primary'),'Activos con boton azul');
    assert.ok(options.columns[7].render('ACTIVO','display').includes('ventas-ver-seguimientos'),'Activos abren historial');
    const selectedButton = { closest: () => ({}), focus() {} };
    tableNode.events.click({ target: { closest: () => selectedButton }, stopPropagation() {} });
    modal.events['hidden.bs.modal']();
    assert.deepEqual(context.selected,[633,'PV-2026-20035'],'Historial del proyecto seleccionado');
    assert.equal(node('modalSeguimientosVenta').dataset.lista,critical?'cotizados_periodo':'declinados');
    assert.ok(calls[0][0].includes('lista='+(critical?'cotizados_periodo':'declinados')));
    requestData.start = 10; await draw();
    assert.ok(calls[1][0].includes('start=10'), 'DataTables gestiona paginación');
    delegated.call({ dataset: { column: '3' }, value: 'Cliente' });
    await new Promise(resolve => setTimeout(resolve, 400)); await pending;
    assert.ok(calls[2][0].includes('f3=Cliente'), 'Filtro por columna delegado en scrollX');
    response = { status: false, message: 'Acceso restringido.' }; await dt.ajax.reload();
    assert.equal(node('declinados-reintentar').hidden, false);
    assert.equal(result.data.length, 0, 'Vaciar tabla ante error');
    response = { status: true, data: { draw: 1, recordsTotal: 0, recordsFiltered: 0, data: [] } };
    node('declinados-reintentar').events.click(); await pending;
    assert.equal(node('declinados-total').textContent, critical ? '0 Proyectos' : '8 Proyectos','El resumen global no cambia al filtrar el detalle');
    if(!critical){
        await modal.events['shown.bs.modal'](); // Regresar del historial conserva el detalle.
        node('declinados-todos').events.click();await pending;
        assert.equal(new URL(calls.at(-1)[0],'http://localhost').searchParams.has('declinado_vendedor'),false);
        assert.equal(node('declinados-todos')['aria-pressed'],'true');
        assert.equal(summaryCalls.length,1,'Seleccionar TODOS no recarga resumen ni modal');
        // Exportar 105 proyectos en dos paginas; nunca solo la pagina visible.
        context.fetch=async(url,settings)=>{
            calls.push([url,settings]);const start=Number(new URL(url,'http://localhost').searchParams.get('start'));
            return {ok:true,json:async()=>({status:true,data:{recordsTotal:105,
                data:Array.from({length:start?5:100},(_,i)=>({id:start+i,proyecto_id:'P'+(start+i),fecha:'2026-09-01',cliente:'Cliente',clasificacion:'Diversos',titulo:'Título'}))}})};
        };
        await options.buttons[0].action.call({processing(){}},{},dt,{},options.buttons[0]);
        assert.equal(exported.body.length,105);assert.deepEqual(Array.from(exported.header),['ID Proyecto','Fecha','Cliente','Clasificación','Título']);
        node('declinados-vendedores').children[0].events.click();await pending;
        await options.buttons[0].action.call({processing(){}},{},dt,{},options.buttons[0]);
        assert.equal(new URL(calls.at(-1)[0],'http://localhost').searchParams.get('declinado_vendedor'),'V1');
        assert.equal(exported.body.length,105,'Exportar completo el vendedor elegido');
    }
    modal.events['hidden.bs.modal']();
    console.log('OK: modal DataTables, teclado, filtros, paginación, columnas, error, reintento y salida segura.');
}
probarModal(true).catch(error => { console.error(error); process.exitCode = 1; });
