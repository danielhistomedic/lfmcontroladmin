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
        appendChild(child){this.children.push(child);},setAttribute(key,value){this[key]=value;},addEventListener(key,fn){this.events[key]=fn;}});
    const node = id => { if (!nodes.has(id)) nodes.set(id,make()); return nodes.get(id); };
    node('ventas-mensuales-datos').textContent=JSON.stringify(data);
    node('ventas-eficiencia-vendedores').hidden=true;
    let ready, resize; const charts=[];
    vm.runInNewContext(code,{
        document:{getElementById:node,createElement:make,addEventListener(key,fn){ready=fn;}},
        window:{addEventListener(key,fn){resize=fn;}},Intl,Map,JSON,Number,theme_chart:dark?'dark':null,
        echarts:{init(){const chart=echarts.init({setAttribute(){},getAttribute(){return null;}},dark?'dark':null,
            {renderer:'svg',ssr:true,width,height:240});charts.push(chart);return chart;}}
    });
    ready();
    assert.equal(charts.length,2,'Graficas independientes para cantidad e importe');
    charts.forEach(chart=>assert.ok(!chart.renderToSVGString().includes('NaN'),'Geometria valida en escritorio, movil, oscuro y vacio'));
    resize();
    return {node,charts};
}
const fixture={
    cotizado:600,colocado:250,
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
    assert.equal(node('ventas-eficiencia-importe-colocado').textContent,'$ 250.00');
    assert.equal(node('ventas-eficiencia-moneda-original').textContent,'USD: $ 150.00 | MXN: $ 1,800.00');
    assert.equal(node('ventas-eficiencia-tipo-cambio').textContent,'TC: $ 18.00 | Fecha TC: 30/09/2026');
    assert.equal(node('ventas-eficiencia-colocacion-monetaria').textContent,'41.67 %');
    assert.deepEqual(charts[0].getOption().series[0].data.map(row=>row.value),[9,9]);
    assert.deepEqual(charts[1].getOption().series[0].data.map(row=>row.value),[600,250]);
    assert.equal(charts[0].getOption().yAxis[0].name,'Proyectos');
    assert.equal(charts[1].getOption().yAxis[0].name,'USD');
    const rows=node('ventas-eficiencia-vendedores-filas').children.map(row=>row.children.map(cell=>cell.textContent));
    assert.deepEqual(rows,[['<script>','2','4','200.00 %','$ 100.00','$ 0.00','0.00 %'],
        ['Beto','3','3','100.00 %','$ 200.00','$ 150.00','75.00 %'],
        ['Ana','4','2','50.00 %','$ 300.00','$ 100.00','33.33 %']]);
    [1,2].forEach(index=>assert.equal(rows.reduce((sum,row)=>sum+Number(row[index]),0),9,'Conciliar cantidades de vendedores y resumen'));
    assert.equal(node('ventas-eficiencia-vendedores').hidden,true);
    charts[0].trigger('click',{componentType:'series',seriesIndex:0,dataIndex:1});
    assert.equal(node('ventas-eficiencia-vendedores').hidden,false);
    assert.equal(node('ventas-eficiencia-general')['aria-expanded'],'true');
    node('ventas-eficiencia-vendedores').hidden=true;
    charts[1].trigger('click',{componentType:'series',seriesIndex:0,dataIndex:1});
    assert.equal(node('ventas-eficiencia-vendedores').hidden,false,'Importes abren el mismo desglose');
    node('ventas-eficiencia-vendedores').hidden=true;
    let prevented=false;
    node('ventas-eficiencia-general').events.keydown({key:' ',preventDefault(){prevented=true;}});
    assert.ok(prevented && !node('ventas-eficiencia-vendedores').hidden);
    charts.forEach(chart=>chart.dispose());
}
const empty=run({cantidades:{total_proyectos:0,cotizacion_cliente:0,orden_compra_cliente:0}});
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
console.log('OK: cantidades e importes en escalas independientes, porcentajes, anteriores, vendedor, orden, teclado, XSS, cero, movil y oscuro.');
