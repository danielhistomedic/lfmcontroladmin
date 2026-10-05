/* Node + ECharts instalado: conteos, porcentajes y drill-down sin acceso a MySQL. */
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
Object.defineProperty(globalThis,'navigator',{value:undefined});
const echarts = require('../../Assets/vendor/echarts/dist/echarts.js');
const code = fs.readFileSync(__dirname+'/../../Assets/app/js/reporte_ventas_eficiencia.js','utf8');
function run(data,width=1200,dark=false) {
    const nodes = new Map();
    const make = () => ({children:[],events:{},textContent:'',hidden:false,
        replaceChildren(){this.children=[];},appendChild(child){this.children.push(child);},setAttribute(key,value){this[key]=value;},addEventListener(key,fn){this.events[key]=fn;}});
    const node = id => { if (!nodes.has(id)) nodes.set(id,make()); return nodes.get(id); };
    node('ventas-mensuales-datos').textContent=JSON.stringify(data);
    node('ventas-eficiencia-vendedores').hidden=true;
    let ready, resize; const charts=[];
    vm.runInNewContext(code,{
        document:{getElementById:node,createElement:make,addEventListener(key,fn){ready=fn;}},
        window:{addEventListener(key,fn){resize=fn;}},Intl,Map,JSON,Number,theme_chart:dark?'dark':null,
        echarts:{init(){const chart=echarts.init({setAttribute(){},getAttribute(){return null;}},dark?'dark':null,
            {renderer:'svg',ssr:true,width,height:220});charts.push(chart);return chart;}}
    });
    ready();
    assert.equal(charts.length,1,'Solo evolucion mensual, sin inicializar las donas retiradas');
    assert.ok(!nodes.has('ventas-eficiencia-grafica')&&!nodes.has('ventas-eficiencia-grafica-importes'));
    charts.forEach(chart=>assert.ok(!chart.renderToSVGString().includes('NaN'),'Geometria valida en escritorio, movil, oscuro y vacio'));
    resize();
    return {node,charts};
}
const fixture={
    anios_seleccionados:[2026,2025],meses_seleccionados:[10,9],
    evolucion_mensual:[{anio:2026,mes:9,cotizado:8,colocado:2,importe_cotizado:600,importe_colocado:150},
        {anio:2025,mes:10,cotizado:4,colocado:1,importe_cotizado:400,importe_colocado:100}],
    cotizado:600,colocado:250,
    cotizado_moneda_original:{USD:500,MXN:1800},
    colocado_moneda_original:{USD:150,MXN:1800},tipo_cambio_aplicado:18,fecha_tipo_cambio:'2026-09-30 12:00:00',
    cantidades:{total_proyectos:10,cotizacion_cliente:9,orden_compra_cliente:9},
    proyectos_por_vendedor:[{vendedor_id:'V1',nombre:'Ana',proyectos:6},{vendedor_id:'V2',nombre:'Beto',proyectos:4}],
    cotizados_por_periodo:[{vendedor_id:'V1',nombre:'Ana',origen:'periodo',proyectos:3},
        {vendedor_id:'V1',nombre:'Ana',origen:'anteriores',proyectos:1},
        {vendedor_id:'V2',nombre:'Beto',proyectos:3},
        {vendedor_id:'V3',nombre:'<script>',origen:'anteriores',proyectos:2}],
    colocados_por_periodo:[{vendedor_id:'V1',nombre:'Ana',proyectos:1},
        {vendedor_id:'V1',nombre:'Ana',origen:'anteriores',proyectos:1},
        {vendedor_id:'V2',nombre:'Beto',proyectos:3},
        {vendedor_id:'V3',nombre:'<script>',origen:'anteriores',proyectos:4}],
    importes_por_vendedor:[{vendedor_id:'V1',nombre:'Ana',importe_cotizado:300,importe_colocado:100},
        {vendedor_id:'V2',nombre:'Beto',importe_cotizado:200,importe_colocado:150},
        {vendedor_id:'V3',nombre:'<script>',importe_cotizado:100,importe_colocado:0}]
};
for (const [width,dark] of [[1200,false],[320,false],[320,true]]) {
    const {node,charts}=run(fixture,width,dark);
    assert.ok(!node('ventas-eficiencia-proyectado').textContent,'Proyectado retirado');
    assert.equal(node('ventas-eficiencia-cotizado').textContent,'9');
    assert.equal(node('ventas-eficiencia-colocado').textContent,'9');
    assert.equal(node('ventas-eficiencia-colocacion').textContent,'100.00 %');
    assert.ok(!node('ventas-eficiencia-eficiencia').textContent,'Eficiencia retirada');
    assert.equal(node('ventas-eficiencia-importe-cotizado').textContent,'$ 600.00');
    assert.equal(node('ventas-eficiencia-cotizado-moneda-original').textContent,'USD: $ 500.00 | MXN: $ 1,800.00');
    assert.equal(node('ventas-eficiencia-cotizado-tipo-cambio').textContent,node('ventas-eficiencia-tipo-cambio').textContent);
    assert.equal(node('ventas-eficiencia-importe-colocado').textContent,'$ 250.00');
    assert.equal(node('ventas-eficiencia-moneda-original').textContent,'USD: $ 150.00 | MXN: $ 1,800.00');
    assert.equal(node('ventas-eficiencia-tipo-cambio').textContent,'TC: $ 18.00 | Fecha TC: 30/09/2026');
    assert.equal(node('ventas-eficiencia-colocacion-monetaria').textContent,'41.67 %');
    const annual=charts[0];
    assert.deepEqual(annual.getOption().xAxis[0].data,['Septiembre 2025','Octubre 2025','Septiembre 2026','Octubre 2026']);
    assert.deepEqual(annual.getOption().series.map(series=>[series.type,series.yAxisIndex]),[['bar',0],['bar',0],['line',1],['line',1]]);
    assert.equal(annual.getOption().series[0].data[1],4,'Mantener todos los anios del filtro general');
    assert.equal(annual.getOption().series[0].data[2],8,'Septiembre conserva sus datos');
    assert.equal(annual.getOption().series[2].data[2],600);
    assert.ok(annual.getOption().tooltip[0].formatter([{dataIndex:2}]).includes('% Colocación Monetaria: 25.00 %'));
    const rows=node('ventas-eficiencia-vendedores-filas').children.map(row=>row.children.map(cell=>cell.textContent));
    assert.deepEqual(rows,[['Ana','4','2','50.00 %','$ 300.00','$ 100.00','33.33 %'],
        ['Beto','3','3','100.00 %','$ 200.00','$ 150.00','75.00 %'],
        ['<script>','2','4','200.00 %','$ 100.00','$ 0.00','0.00 %']]);
    [1,2].forEach(index=>assert.equal(rows.reduce((sum,row)=>sum+Number(row[index]),0),9,'Conciliar cantidades de vendedores y resumen'));
    assert.equal(node('ventas-eficiencia-vendedores').hidden,false);
    assert.equal(node('ventas-eficiencia-general')['aria-expanded'],'true');
    node('ventas-eficiencia-vendedores').hidden=true;
    node('ventas-eficiencia-general').events.click();
    assert.equal(node('ventas-eficiencia-vendedores').hidden,false,'Comparativo mantiene acceso al desglose');
    node('ventas-eficiencia-vendedores').hidden=true;
    let prevented=false;
    node('ventas-eficiencia-general').events.keydown({key:' ',preventDefault(){prevented=true;}});
    assert.ok(prevented && !node('ventas-eficiencia-vendedores').hidden);
    charts.forEach(chart=>chart.dispose());
}
const empty=run({cantidades:{total_proyectos:0,cotizacion_cliente:0,orden_compra_cliente:0}});
const tableSellers=Array.from({length:20},(_,i)=>({vendedor_id:String(i),nombre:'Vendedor '+String(i).padStart(2,'0')}));
const tableData={cantidades:{cotizacion_cliente:306,orden_compra_cliente:66},cotizado:7800,colocado:3300,
    proyectos_por_vendedor:tableSellers,
    cotizados_por_periodo:tableSellers.map((row,i)=>({...row,proyectos:20+i})),
    colocados_por_periodo:tableSellers.map((row,i)=>({...row,proyectos:i})),
    importes_por_vendedor:tableSellers.map((row,i)=>({...row,importe_cotizado:100*(i+1),importe_colocado:50*i}))};
const tableSnapshot=JSON.stringify(tableData);
const table=run(tableData);
const tableBody=table.node('ventas-eficiencia-vendedores-filas');
assert.equal(tableBody.children.length,15);
assert.equal(table.node('ventas-eficiencia-vendedores-pagina').textContent,'1–15 de 20');
assert.equal(table.node('ventas-eficiencia-vendedores-anterior').disabled,true);
table.node('ventas-eficiencia-vendedores-siguiente').events.click();
assert.equal(tableBody.children.length,5);
assert.equal(table.node('ventas-eficiencia-vendedores-pagina').textContent,'16–20 de 20');
assert.equal(table.node('ventas-eficiencia-vendedores-siguiente').disabled,true);
for(const criterion of ['colocado','importe_colocado','importe_cotizado','colocacion','colocacion_monetaria','nombre']){
    table.node('ventas-eficiencia-vendedores-orden').value=criterion;
    table.node('ventas-eficiencia-vendedores-orden').events.change();
    assert.equal(tableBody.children[0].children[0].textContent,criterion==='nombre'?'Vendedor 00':'Vendedor 19');
    assert.equal(table.node('ventas-eficiencia-vendedores-pagina').textContent,'1–15 de 20');
}
table.node('ventas-eficiencia-vendedores-buscar').value='vendedor 01';
table.node('ventas-eficiencia-vendedores-buscar').events.input();
assert.equal(tableBody.children.length,1);assert.equal(tableBody.children[0].children.length,7);
assert.equal(tableBody.children[0].children[1].textContent,'21');assert.equal(tableBody.children[0].children[5].textContent,'$ 50.00');
table.node('ventas-eficiencia-vendedores-buscar').value='sin coincidencia';
table.node('ventas-eficiencia-vendedores-buscar').events.input();
assert.equal(tableBody.children[0].children[0].colSpan,7);
assert.equal(table.node('ventas-eficiencia-vendedores-pagina').textContent,'0 resultados');
assert.equal(table.node('ventas-eficiencia-importe-cotizado').textContent,'$ 7,800.00','Busqueda no cambia KPI');
assert.equal(JSON.stringify(tableData),tableSnapshot,'Busqueda y orden no modifican datos');
table.charts.forEach(chart=>chart.dispose());
const localData={...fixture,
    clasificaciones_comparativo:[{id:34,clasificacion:'BOMBAS FLOWSERVE'},{id:91,clasificacion:'SELLOS FLOWSERVE'},
        {id:17,clasificacion:'DIVERSOS'},{id:22,clasificacion:'VÁLVULAS FLOWSERVE'}],
    comparativo_clasificacion_local:[{clasificacion_id:34,vendedor_id:'V1',nombre:'Ana',cotizado:3,colocado:1,
        importe_cotizado:200,importe_colocado:50,cotizado_usd:100,cotizado_mxn:1800,colocado_usd:50,colocado_mxn:0}],
    evolucion_clasificacion:[{clasificacion_id:34,anio:2026,mes:9,cotizado:3,colocado:1,importe_cotizado:200,importe_colocado:50}]};
const originalLocal=JSON.stringify(localData);
const local=run(localData);
const buttons=local.node('ventas-eficiencia-filtro-clasificacion').children;
assert.deepEqual(buttons.map(button=>button.textContent),['TODOS','DIVERSOS','FLOWSERVE','BOMBAS FLOWSERVE','VÁLVULAS FLOWSERVE','SELLOS FLOWSERVE']);
buttons[3].events.click();
assert.equal(buttons[3]['aria-pressed'],'true');assert.equal(buttons[0]['aria-pressed'],'false');
assert.equal(local.node('ventas-eficiencia-cotizado').textContent,'3');
assert.equal(local.node('ventas-eficiencia-colocado').textContent,'1');
assert.equal(local.node('ventas-eficiencia-colocacion').textContent,'33.33 %');
assert.equal(local.node('ventas-eficiencia-importe-cotizado').textContent,'$ 200.00');
assert.equal(local.node('ventas-eficiencia-cotizado-moneda-original').textContent,'USD: $ 100.00 | MXN: $ 1,800.00');
assert.equal(local.node('ventas-eficiencia-tipo-cambio').textContent,'TC: $ 18.00 | Fecha TC: 30/09/2026');
assert.deepEqual(local.charts.at(-1).getOption().series[0].data,[0,0,3,0]);
assert.equal(local.node('ventas-eficiencia-vendedores-filas').children.length,1);
buttons[1].events.click();
assert.equal(local.node('ventas-eficiencia-colocacion').textContent,'—','Clasificacion vacia sin porcentaje inventado');
buttons[0].events.click();
assert.equal(local.node('ventas-eficiencia-cotizado').textContent,'9');
assert.equal(local.node('ventas-eficiencia-importe-cotizado').textContent,'$ 600.00','TODOS restaura importes generales');
assert.equal(JSON.stringify(localData),originalLocal,'El filtro no modifica datos compartidos');
local.charts.slice(-1).forEach(chart=>chart.dispose());
const flowserveData={...localData,
    comparativo_clasificacion_local:[34,22,91,17].map(id=>({clasificacion_id:id,vendedor_id:'V1',nombre:'Ana',
        cotizado:id===17?100:3,colocado:id===17?100:1,importe_cotizado:id===17?10000:100,importe_colocado:id===17?10000:50,
        cotizado_usd:id===17?10000:100,colocado_usd:id===17?10000:50,cotizado_mxn:0,colocado_mxn:0})),
    evolucion_clasificacion:[34,22,91,17].map(id=>({clasificacion_id:id,anio:2026,mes:9,
        cotizado:id===17?100:3,colocado:id===17?100:1,importe_cotizado:id===17?10000:100,importe_colocado:id===17?10000:50}))};
const flowserveSnapshot=JSON.stringify(flowserveData);
const flowserve=run(flowserveData);
const flowserveButtons=flowserve.node('ventas-eficiencia-filtro-clasificacion').children;
flowserveButtons[2].events.click();
assert.equal(flowserveButtons[2]['aria-pressed'],'true');
assert.equal(flowserve.node('ventas-eficiencia-cotizado').textContent,'9');
assert.equal(flowserve.node('ventas-eficiencia-colocado').textContent,'3');
assert.equal(flowserve.node('ventas-eficiencia-colocacion').textContent,'33.33 %');
assert.equal(flowserve.node('ventas-eficiencia-importe-cotizado').textContent,'$ 300.00');
assert.equal(flowserve.node('ventas-eficiencia-importe-colocado').textContent,'$ 150.00');
assert.equal(flowserve.node('ventas-eficiencia-colocacion-monetaria').textContent,'50.00 %');
assert.deepEqual(flowserve.charts.at(-1).getOption().series[0].data,[0,0,9,0],'Consolidar clasificaciones del mismo mes');
assert.deepEqual(flowserve.charts.at(-1).getOption().series[2].data,[0,0,300,0]);
assert.equal(flowserve.node('ventas-eficiencia-vendedores-filas').children.length,1,'Consolidar el mismo vendedor entre clasificaciones');
flowserveButtons[3].events.click();
assert.equal(flowserve.node('ventas-eficiencia-cotizado').textContent,'3','Mantener filtro individual');
flowserveButtons[0].events.click();
assert.equal(flowserve.node('ventas-eficiencia-importe-cotizado').textContent,'$ 600.00','TODOS restaura el general');
assert.equal(JSON.stringify(flowserveData),flowserveSnapshot,'No modificar datos del dashboard');
flowserve.charts.slice(-1).forEach(chart=>chart.dispose());
assert.equal(empty.node('ventas-eficiencia-colocacion').textContent,'—');
assert.equal(empty.node('ventas-eficiencia-colocacion-monetaria').textContent,'—');
assert.equal(empty.node('ventas-eficiencia-tipo-cambio').textContent,'TC: — | Fecha TC: —','Datos ausentes sin tipo de cambio inventado');
assert.equal(empty.node('ventas-eficiencia-vendedores-filas').children[0].children[0].colSpan,7);
empty.charts.forEach(chart=>chart.dispose());
const scoped=run({cantidades:{total_proyectos:6,cotizacion_cliente:4,orden_compra_cliente:2},
    proyectos_por_vendedor:[fixture.proyectos_por_vendedor[0]],
    cotizados_por_periodo:fixture.cotizados_por_periodo.filter(row=>row.vendedor_id==='V1'),
    colocados_por_periodo:fixture.colocados_por_periodo.filter(row=>row.vendedor_id==='V1'),
    cotizado:300,colocado:100,importes_por_vendedor:[fixture.importes_por_vendedor[0]]});
assert.equal(scoped.node('ventas-eficiencia-vendedores-filas').children.length,1,'Reutilizar alcance ya filtrado por vendedor');
assert.equal(scoped.node('ventas-eficiencia-colocacion').textContent,'50.00 %');
assert.equal(scoped.node('ventas-eficiencia-colocacion-monetaria').textContent,'33.33 %');
scoped.charts.forEach(chart=>chart.dispose());
const moneyOnly=run({cantidades:{},cotizado:0,colocado:20,
    importes_por_vendedor:[{vendedor_id:'V4',nombre:'Solo importes',importe_cotizado:0,importe_colocado:20}]});
assert.equal(moneyOnly.node('ventas-eficiencia-vendedores-filas').children.length,1,'Incluir vendedores presentes solo en importes');
assert.equal(moneyOnly.node('ventas-eficiencia-colocacion-monetaria').textContent,'—');
assert.equal(moneyOnly.node('ventas-eficiencia-vendedores-filas').children[0].children[6].textContent,'—');
moneyOnly.charts.forEach(chart=>chart.dispose());
const negative=run({cantidades:{},cotizado:-100,colocado:20});
assert.equal(negative.node('ventas-eficiencia-colocacion-monetaria').textContent,'-20.00 %','Conservar importes con signo; solo cero invalida el denominador');
negative.charts.forEach(chart=>chart.dispose());
const fallback=run({cantidades:{},tipo_cambio:0,tipo_cambio_aplicado:1,fecha_tipo_cambio:null});
assert.equal(fallback.node('ventas-eficiencia-tipo-cambio').textContent,'TC: $ 1.00 | Fecha TC: —','Mostrar divisor efectivo del backend sin reconvertir importes');
fallback.charts.forEach(chart=>chart.dispose());
const excess=run({cantidades:{cotizacion_cliente:5,orden_compra_cliente:8},cotizado:100,colocado:150});
assert.equal(excess.node('ventas-eficiencia-colocacion').textContent,'160.00 %','Conservar conversion real en KPI');
assert.equal(excess.node('ventas-eficiencia-colocacion-monetaria').textContent,'150.00 %');
excess.charts.forEach(chart=>chart.dispose());
console.log('OK: donas retiradas, evolucion mensual, filtros, KPI, vendedor visible, orden, paginas de 15, teclado, XSS, cero, movil y oscuro.');
