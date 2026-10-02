/* Validación sin navegador, base de datos ni dependencias nuevas: Node + ECharts del portal. */
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
// Node 24 expone navigator; ECharts 5 necesita reconocer este proceso como servidor.
Object.defineProperty(globalThis, 'navigator', { value: undefined });
const echarts = require('../../Assets/vendor/echarts/dist/echarts.js');
const code = fs.readFileSync(path.join(__dirname, '../../Assets/app/js/reporte_ventas_mensuales.js'), 'utf8');

function ejecutar(empty, width, theme, periods = false) {
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
    data.estatus_por_clasificacion = empty ? [] : [{clasificacion_id:3,estatus_id:6,estatus:'Pedido',proyectos:2,declinados:2},{clasificacion_id:3,estatus_id:3,estatus:'Cotizacion',proyectos:3,declinados:0},{clasificacion_id:5,estatus_id:6,estatus:'Pedido',proyectos:5,declinados:2}];
    data.anios_seleccionados = [2024,2026];
    data.meses_seleccionados = [2,9];
    if (periods) data.clasificaciones_por_vendedor[0].proyectos = 9;
    if (periods) data.estatus_por_clasificacion = [
        {clasificacion_id:3,anio:2026,mes:9,estatus_id:3,estatus:'Cotizacion',proyectos:4,declinados:1},
        {clasificacion_id:3,anio:2024,mes:2,estatus_id:6,estatus:'Pedido',proyectos:2,declinados:1},
        {clasificacion_id:3,anio:2026,mes:9,estatus_id:6,estatus:'Pedido',proyectos:3,declinados:0}];
    data.estatus_por_clasificacion = data.estatus_por_clasificacion.map(row => ({anio:2026,mes:9,vendedor_id:'V1',...row}));
    const context = {
        document: {
            createElement: () => ({value:'',textContent:''}),
            addEventListener: (name, cb) => { events[name] = cb; },
            getElementById: id => id === 'modal-declinados-ventas' && !nodes.has('ventas-clasificacion-card') ? null : id === 'filtros-ventas-mensuales' ? form : id === 'ventas-cargando' ? loading :
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
    assert.equal(charts.length, 3, 'Inicializar las cinco gráficas restantes');
    for (const chart of charts) {
        const svg = chart.renderToSVGString();
        assert.ok(svg.includes('<svg'), 'Renderizar con ECharts instalado');
        assert.ok(!svg.includes('NaN'), 'Sin geometría inválida en vacío ni móvil');
    }
    if (periods) {
        assert.deepEqual(charts[2].getOption().xAxis[0].data, ['Sellos','Bombas'], 'Clasificaciones de mayor a menor');
        assert.deepEqual(charts[2].getOption().series[0].data,[7,3]);
        charts[2].trigger('click',{componentType:'series',dataIndex:1});
        assert.ok(nodes.get('ventas-clasificacion-card-titulo').textContent.includes('2026'));
        assert.equal(charts[3].getOption().xAxis[0].data.length,12);
        assert.equal(charts[3].getOption().xAxis[0].data[0],'Enero');
        assert.equal(charts[3].getOption().xAxis[0].data[11],'Diciembre');
        assert.equal(charts[3].getOption().series[0].data[8],6);
        assert.equal(charts[3].getOption().series[1].data[8],1);
        assert.equal(charts[3].getOption().series[0].data[0],0);
        const tip = charts[3].getOption().tooltip[0].formatter([{dataIndex:8}]);
        assert.ok(tip.includes('Septiembre 2026') && tip.includes('Proyectos activos: 6') && tip.includes('Total: 7'));
        const year = nodes.get('ventas-clasificacion-anio');
        year.value='2024'; year.events.change();
        assert.equal(charts.length,4,'Reutilizar evolucion mensual al cambiar anio');
        assert.equal(charts[3].getOption().series[0].data[1],1);
        assert.equal(charts[3].getOption().series[1].data[1],1);
        charts[3].trigger('click',{componentType:'series',dataIndex:1,seriesIndex:0});
        assert.equal(nodes.get('ventas-clasificacion-vendedores-panel').hidden,false);
        assert.equal(charts[4].getOption().xAxis[0].data[0],'Vendedor 1');
        assert.deepEqual(charts[4].getOption().series[0].data,[1]);
        assert.deepEqual(charts[4].getOption().series[1].data,[1]);
        charts[4].trigger('click',{componentType:'series',dataIndex:0,seriesIndex:1});
        const modal=nodes.get('modal-declinados-ventas');
        assert.equal(modal.dataset.desgloseLista,'vendedor_clasificacion');
        assert.equal(modal.dataset.desgloseAnio,'2024');
        assert.equal(modal.dataset.desgloseMes,'2');
        assert.equal(modal.dataset.desgloseVendedor,'V1');
        assert.equal(modal.dataset.clasificacionId,'3');
        assert.equal(modal.dataset.segmento,'declinados');
        assert.equal(modal.shown,true);
        year.value='2026'; year.events.change();
        assert.equal(nodes.get('ventas-clasificacion-vendedores-panel').hidden,true,'Ocultar datos anteriores al cambiar anio');
        charts.forEach(chart=>chart.dispose());
        return;
    }
    if (!empty) {
        assert.deepEqual(charts[2].getOption().xAxis[0].data,['Bombas','Sellos']);
        assert.deepEqual(charts[2].getOption().series[0].data,[3,3]);
        assert.deepEqual(charts[2].getOption().series[1].data,[2,2]);
        assert.equal(charts[2].getOption().series[1].itemStyle.color,'#dc3545');
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
        assert.equal(charts.length,6,'Segunda grafica debajo sin reemplazar la primera');
        assert.deepEqual(charts[3].getOption().xAxis[0].data,['Bombas','Sellos']);
        assert.deepEqual(charts[4].getOption().xAxis[0].data,['Cotizacion','Pedido']);
        assert.deepEqual(charts[4].getOption().series[1].data,[0,2]);
        assert.equal(nodes.get('ventas-estatus-titulo').textContent,'Vendedor 1 — 8 proyectos');
        assert.equal(charts[0].getOption().series[0].data[0].itemStyle.color,'#d48825');
        dropdown.value='1'; dropdown.events.change();
        assert.equal(charts.length,6,'Reutilizar grafica secundaria');
        assert.equal(charts[5].getOption().xAxis[0].data.length,12);
        assert.ok(nodes.get('ventas-vendedor-mensual-titulo').textContent.includes('Vendedor 2'));
        assert.equal(charts[5].getOption().series[0].data.reduce((a,b)=>a+b,0),0);
        dropdown.value='0'; dropdown.events.change();
        assert.equal(charts[5].getOption().series[0].data[8],6);
        assert.equal(charts[5].getOption().series[1].data[8],4);
        const sellerYear=nodes.get('ventas-vendedor-anio');
        sellerYear.value='2024'; sellerYear.events.change();
        assert.equal(charts[5].getOption().series[0].data.reduce((a,b)=>a+b,0),0);
        dropdown.value='1'; dropdown.events.change();
        assert.deepEqual(charts[3].getOption().series[0].data,[0]);
        assert.deepEqual(charts[3].getOption().series[1].data,[2]);
        assert.equal(charts[3].getOption().series[1].itemStyle.color,'#dc3545');
        assert.equal(charts[3].getOption().series[1].stack,'proyectos');
        assert.deepEqual(charts[4].getOption().series[1].data,[2]);
        assert.equal(charts[4].getOption().series[1].itemStyle.color,'#dc3545');
    }
    if (!empty) {
        charts[2].trigger('click',{componentType:'series',dataIndex:0});
        assert.equal(nodes.get('ventas-clasificacion-card').open,true);
        assert.equal(nodes.get('ventas-clasificacion-card').hidden,false);
        assert.equal(charts[6].getOption().xAxis[0].data.length,12);
        charts[2].trigger('click',{componentType:'series',dataIndex:1});
        assert.equal(charts.length,7,'Reutilizar evolucion mensual');
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
ejecutar(false, 320, 'dark', true);
ejecutar(false, 900, 'walden');
ejecutar(false, 320, 'dark');
ejecutar(true, 900, 'walden');
ejecutar(true, 320, 'dark');
console.log('OK: comparativo y cascada, clasificaciones, estatus, declinados apilados, movil, oscuro y filtros globales.');

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
