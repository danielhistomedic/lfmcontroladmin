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
    if (!empty) {
        const classificationSeries = charts[1].getOption().series;
        classificationSeries.filter(series=>series.name !== 'Declinados').forEach(series=>{
            assert.equal(series.label.formatter(),series.name,'Nombre completo de clasificacion debajo de cada barra');
            assert.equal(series.label.overflow,'break');
        });
    }
    if (placed) {
        const option=charts[2].getOption();
        const index=option.series.findIndex(series=>series.name==='Pedidos Colocados');
        assert.deepEqual(option.series[index].data,[0,0,0,6],'Colocados suman ambos grupos del KPI');
        assert.deepEqual(option.series[index+1].data,[0,0,0,0],'Excluir los cerrados del conjunto colocado');
        const tip=option.tooltip[0].formatter({seriesIndex:index,dataIndex:3});
        assert.ok(tip.includes('Proyectos del período: 3') && tip.includes('Proyectos anteriores: 3') && tip.includes('Total colocado: 6'));
        charts[2].trigger('click',{componentType:'series',seriesIndex:index,dataIndex:3});
        assert.equal(nodes.get('modal-declinados-ventas').dataset.desgloseLista,'colocados_periodo');
        charts[0].trigger('click',{componentType:'series',dataIndex:0});
        assert.equal(charts[4].getOption().series[0].data.find(row=>row.name==='Pedidos Colocados').total,5);
        const dropdown=nodes.get('ventas-vendedor-desglose');dropdown.value='1';dropdown.events.change();
        assert.equal(charts[4].getOption().title[0].text,'2');
        assert.deepEqual(charts[4].getOption().series[0].data.map(row=>row.value),[2]);
        charts.forEach(chart=>{assert.ok(!chart.renderToSVGString().includes('NaN'));chart.dispose();});
        return;
    }
    if (quoted) {
        const option = charts[2].getOption();
        const quotedIndex = option.series.findIndex(series => series.name==='PEDIDO COTIZADO (SIN OC CLIENTE)');
        assert.equal(quotedIndex,2);
        assert.deepEqual(option.series[quotedIndex].data,[0,0,0,10],'La barra usa el KPI, no el conteo crudo de estatus');
        assert.deepEqual(option.series[quotedIndex+1].data,[0,0,0,0],'El conjunto cotizado excluye cerrados');
        const tip=option.tooltip[0].formatter({seriesIndex:quotedIndex,dataIndex:3});
        assert.ok(tip.includes('Proyectos del período: 7') && tip.includes('Proyectos anteriores: 3') && tip.includes('Total cotizado: 10'));
        charts[2].trigger('click',{componentType:'series',seriesIndex:quotedIndex,dataIndex:3});
        assert.equal(nodes.get('modal-declinados-ventas').dataset.desgloseLista,'cotizados_periodo');
        charts[0].trigger('click',{componentType:'series',dataIndex:0});
        assert.equal(charts[4].getOption().series[0].data.find(row=>row.name==='PEDIDO COTIZADO (SIN OC CLIENTE)').total,99,'Dona usa la distribucion de estatus registrada del vendedor');
        const dropdown=nodes.get('ventas-vendedor-desglose'); dropdown.value='1';dropdown.events.change();
        assert.ok(!charts[4].getOption().series[0].data.some(row=>row.name==='PEDIDO COTIZADO (SIN OC CLIENTE)'),'No incorporar cotizados anteriores ajenos al conjunto de proyectos del vendedor');
        charts.forEach(chart=>{assert.ok(!chart.renderToSVGString().includes('NaN'));chart.dispose();});
        return;
    }
    if (periods) {
        const statusOption = charts[2].getOption();
        assert.equal(statusOption.legend[0].show,true);
        assert.equal(statusOption.legend[0].type,'scroll');
        const statusName = 'PROCESO DE COTIZACION';
        assert.equal(statusOption.legend[0].formatter(statusName), statusName.slice(0,Math.floor(statusName.length*.66))+'...');
        assert.equal(statusOption.legend[0].formatter('Declinados'),'Declin...');
        assert.equal(statusOption.legend[0].tooltip.formatter({name:statusName}),statusName);
        assert.equal(statusOption.grid[0].top,55,'Espacio para leyenda y nombre del eje');
        assert.equal(statusOption.series[0].label.formatter(),'PROCESO DE COTIZACION');
        assert.equal(statusOption.series[0].name,'PROCESO DE COTIZACION');
        assert.equal(statusOption.series[2].name,'Pedidos Colocados');
        assert.equal(statusOption.series.length,4,'Una sola barra apilada para los estatus >= 6');
        assert.deepEqual(statusOption.series[0].data,[0,0,0,3]);
        assert.deepEqual(statusOption.series[1].data,[0,0,0,1]);
        assert.deepEqual(statusOption.series[2].data,[1,0,0,2]);
        assert.deepEqual(statusOption.series[3].data,[1,0,0,1],'Declinados de todo el grupo');
        assert.ok(statusOption.tooltip[0].formatter({seriesIndex:2,dataIndex:3}).includes('Total: 3'));
        assert.equal(statusOption.series[1].itemStyle.color,'#dc3545');
        assert.equal(statusOption.series[0].stack,statusOption.series[1].stack);
        const tip = statusOption.tooltip[0].formatter({seriesIndex:0,dataIndex:3});
        assert.ok(tip.includes('PROCESO DE COTIZACION') && tip.includes('Septiembre 2026') && tip.includes('Total: 4'));
        assert.equal(statusOption.series[0].barWidth,26);
        assert.equal(statusOption.series[0].label.fontSize,9);
        assert.equal(statusOption.xAxis[1].offset,100);
        charts[2].trigger('click',{componentType:'series',dataIndex:3,seriesIndex:1});
        const statusModal = nodes.get('modal-declinados-ventas');
        assert.equal(statusModal.dataset.desgloseLista,'estatus_periodo');
        assert.equal(statusModal.dataset.estatusId,'proceso_cotizacion');
        assert.equal(statusModal.dataset.segmento,'declinados');
        assert.equal(statusModal.dataset.desgloseAnio,'2026');
        assert.equal(statusModal.dataset.desgloseMes,'9');
        charts[2].trigger('click',{componentType:'series',dataIndex:0,seriesIndex:2});
        assert.equal(statusModal.dataset.estatusId,'colocados');
        assert.equal(statusModal.dataset.segmento,'no_declinados');
        assert.equal(statusModal.dataset.desgloseAnio,'2024');
        assert.equal(statusModal.dataset.desgloseMes,'2');

        assert.deepEqual(charts[1].getOption().xAxis[0].data, ['Febrero','Septiembre','Febrero','Septiembre']);
        assert.deepEqual(charts[1].getOption().xAxis[1].data,['2024','2024','2026','2026']);
        assert.deepEqual(charts[1].getOption().series[2].data,[1,0,0,5]);
        assert.deepEqual(charts[1].getOption().series[3].data,[1,0,0,2]);
        charts[1].trigger('click',{componentType:'series',dataIndex:3,seriesIndex:3});
        assert.equal(nodes.get('modal-declinados-ventas').dataset.desgloseLista,'clasificacion_periodo');
        assert.equal(nodes.get('modal-declinados-ventas').dataset.segmento,'declinados');
        assert.equal(nodes.get('modal-declinados-ventas').dataset.desgloseAnio,'2026');
        assert.equal(nodes.get('modal-declinados-ventas').dataset.desgloseMes,'9');
        assert.equal(charts.length,3,'Abrir modal sin crear los graficos retirados');
        charts[0].trigger('click',{componentType:'series',dataIndex:0});
        const sellerClassification = charts[3].getOption();
        const sellerStatus = charts[4].getOption();
        assert.equal(sellerClassification.xAxis,undefined);
        assert.deepEqual(sellerClassification.series[0].data.map(row=>row.value),[5,2],'Acumular meses y anios del vendedor');
        assert.equal(sellerClassification.series[0].data[0].itemStyle.color,charts[1].getOption().series[2].itemStyle.color);
        assert.equal(sellerClassification.series[0].data[0].name,'Bombas');
        assert.deepEqual(sellerStatus.series[0].data.map(row=>row.value),[2,3,2],'Distribucion registrada, sin repetir proyectos anteriores de los KPI');
        assert.equal(sellerStatus.series[0].data[1].name,'Pedidos Colocados');
        assert.ok(sellerStatus.tooltip[0].formatter({data:sellerStatus.series[0].data[0]}).includes('Total de la categoría: 2'));
        charts[3].renderToSVGString(); charts[4].renderToSVGString();
        charts.forEach(chart=>chart.dispose());
        return;
    }
    if (!empty) {
        assert.deepEqual(charts[1].getOption().xAxis[0].data,['Febrero','Septiembre','Febrero','Septiembre']);
        assert.deepEqual(charts[1].getOption().series[0].data,[0,0,0,3]);
        assert.deepEqual(charts[1].getOption().series[1].data,[0,0,0,2]);
        assert.equal(charts[1].getOption().series[1].itemStyle.color,'#dc3545');
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
console.log('OK: cascada, clasificaciones, estatus, declinados apilados, movil, oscuro y filtros globales.');

async function probarModal(critical = false) {
    const nodes = new Map(); const events = {}; const calls = []; let response; let options; let pending; let delegated;
    function node(id) {
        if (!nodes.has(id)) nodes.set(id, { id, children: [], events: {}, textContent: '', dataset: {}, style: {}, disabled: false,
            addEventListener(name, cb) { this.events[name] = cb; }, setAttribute() {},
            appendChild(child) { this.children.push(child); }, replaceChildren() { this.children = []; },
            click() { this.clicked = true; }
        });
        return nodes.get(id);
    }
    const form = node('filtros-ventas-mensuales'); form.querySelector = () => node('submit');
    const modal = node('modal-declinados-ventas');
    modal.dataset = { url: '/portal/reportesmensuales/declinados', anio: '2026', mes: '9', vendedor: 'V1' };
    const source = node('ventas-mensuales-datos');
    source.textContent = JSON.stringify({ cotizado: 0, colocado: 0, vendedores: [], productos: [], cruce: [] });
    const cards = [node('declinados-card'), node('critico-card')];
    const tableNode = node('table-declinados-ventas');
    tableNode.tHead = { rows: [{ cells: ['No.','ID Proyecto','Fecha','Cliente','Vendedor','Clasificación','Título','Activo','Seguimientos'].map(textContent => ({textContent})) }], appendChild(child) { this.filterRow=child; } };
    const requestData = { draw: 1, start: 0, length: 10, search: { value: '' }, order: [],
        columns: Array.from({ length: 8 }, () => ({ search: { value: '' } })) };
    let result;
    function draw() { pending = options.ajax(requestData, payload => { result = payload; }); return pending; }
    const dt = { columns: { adjust() {} }, ajax: { reload() { return draw(); } }, table: () => ({ container: () => node('container') }),
        row: () => ({ data: () => ({id:633, proyecto_id:'PV-2026-20035'}) }),
        column: index => ({ search(value) { requestData.columns[index].search.value = value; return { draw }; } }) };
    const jquery = element => ({ find: () => ({ on() {} }), on: (name, selector, handler) => { delegated = handler; },
        DataTable(configuration) { if (element === tableNode) { options = configuration; draw(); return dt; } } });
    jquery.fn = { dataTable: { render: { text: () => ({ display: value => value.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') }) } } };
    const context = {
        document: { addEventListener: (name, cb) => { events[name] = cb; }, getElementById: node,
            createElement: tag => node('created-'+Math.random()),
            querySelectorAll: selector => selector.includes('.ventas-abrir-declinados') ? cards : [] },
        window: { addEventListener() {}, verSeguimientosProyecto(id,project) { context.selected=[id,project]; } }, jQuery: jquery,
        bootstrap: { Modal: { getOrCreateInstance: () => ({hide() {}, show() {}}) } },
        echarts: { init: () => ({ setOption() {}, resize() {}, on() {} }) },
        fetch: async (url, settings) => { calls.push([url, settings]); return { ok: response.status, json: async () => response }; },
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
    modal.events['show.bs.modal']({relatedTarget:{dataset:critical?{lista:'interna_sin_cliente'}:{}}});
    modal.events['shown.bs.modal'](); await pending;
    assert.equal(options.serverSide, true);
    assert.ok(options.dom.includes('declinados-length"l') && options.dom.includes('declinados-buttons"B') && options.dom.includes('declinados-search ms-auto"f'), 'Separar cantidad, botones y búsqueda en la barra');
    assert.equal(options.buttons[1].extend, 'colvis');
    assert.equal(tableNode.tHead.filterRow.children.length, 9, 'Cabecera con filtros');
    const query = new URL(calls[0][0], 'http://localhost').searchParams;
    assert.deepEqual(query.getAll('anio[]'), ['2026']);
    assert.equal(query.get('vendedor'), 'V1');
    assert.deepEqual(query.getAll('mes[]'), ['9'], 'Filtrar meses y vendedor');
    modal.dataset.mes = '2,9,12';
    modal.dataset.anio = '2024,2026';
    await dt.ajax.reload();
    const multiQuery = new URL(calls.pop()[0], 'http://localhost').searchParams;
    assert.deepEqual(multiQuery.getAll('mes[]'), ['2','9','12'], 'Enviar todos los meses a los modales');
    assert.deepEqual(multiQuery.getAll('anio[]'), ['2024','2026'], 'Enviar todos los anios a los modales');
    assert.equal(node('declinados-total').textContent, '8 Proyectos');
    assert.equal(options.columns[6].render(response.data.data[0].titulo,'display'), '&lt;img src=x onerror=alert(1)&gt;', 'Salida escapada');
    assert.equal(options.columns[2].render('2026-09-30','display'), '30/09/2026');
    assert.ok(options.columns[critical ? 8 : 7].render('CERRADO','display').includes('ventas-ver-seguimientos'), 'Activo como boton de seguimiento');
    assert.ok(options.columns[7].render('CERRADO','display').includes('btn-danger'),'Declinados con boton rojo');
    assert.ok(options.columns[7].render('ACTIVO','display').includes('btn-primary'),'Activos con boton azul');
    assert.ok(options.columns[7].render('ACTIVO','display').includes('ventas-ver-seguimientos'),'Activos abren historial');
    const selectedButton = { closest: () => ({}), focus() {} };
    tableNode.events.click({ target: { closest: () => selectedButton }, stopPropagation() {} });
    modal.events['hidden.bs.modal']();
    assert.deepEqual(context.selected,[633,'PV-2026-20035'],'Historial del proyecto seleccionado');
    assert.equal(node('modalSeguimientosVenta').dataset.lista,critical?'interna_sin_cliente':'declinados');
    assert.ok(calls[0][0].includes('lista='+(critical?'interna_sin_cliente':'declinados')));
    requestData.start = 10; await dt.ajax.reload();
    assert.ok(calls[1][0].includes('start=10'), 'DataTables gestiona paginación');
    delegated.call({ dataset: { column: '3' }, value: 'Cliente' });
    await new Promise(resolve => setTimeout(resolve, 400)); await pending;
    assert.ok(calls[2][0].includes('f3=Cliente'), 'Filtro por columna delegado en scrollX');
    response = { status: false, message: 'Acceso restringido.' }; await dt.ajax.reload();
    assert.equal(node('declinados-reintentar').hidden, false);
    assert.equal(result.data.length, 0, 'Vaciar tabla ante error');
    response = { status: true, data: { draw: 1, recordsTotal: 0, recordsFiltered: 0, data: [] } };
    node('declinados-reintentar').events.click(); await pending;
    assert.equal(node('declinados-total').textContent, '0 Proyectos');
    modal.events['hidden.bs.modal']();
    console.log('OK: modal DataTables, teclado, filtros, paginación, columnas, error, reintento y salida segura.');
}
probarModal().then(() => probarModal(true)).catch(error => { console.error(error); process.exitCode = 1; });
