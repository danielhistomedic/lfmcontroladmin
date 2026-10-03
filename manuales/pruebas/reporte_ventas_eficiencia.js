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
    let ready, chart, resize;
    vm.runInNewContext(code,{
        document:{getElementById:node,createElement:make,addEventListener(key,fn){ready=fn;}},
        window:{addEventListener(key,fn){resize=fn;}},Intl,Map,JSON,Number,theme_chart:dark?'dark':null,
        echarts:{init(){chart=echarts.init({setAttribute(){},getAttribute(){return null;}},dark?'dark':null,
            {renderer:'svg',ssr:true,width,height:240});return chart;}}
    });
    ready();
    assert.ok(!chart.renderToSVGString().includes('NaN'),'Geometria valida en escritorio, movil, oscuro y vacio');
    resize();
    return {node,chart};
}
const fixture={
    // Importes deliberadamente diferentes: nunca deben usarse para los porcentajes.
    cotizado:999999,colocado:1,
    cantidades:{total_proyectos:10,cotizacion_cliente:9,orden_compra_cliente:9},
    proyectos_por_vendedor:[{vendedor_id:'V1',nombre:'Ana',proyectos:6},{vendedor_id:'V2',nombre:'Beto',proyectos:4}],
    cotizados_por_periodo:[{vendedor_id:'V1',nombre:'Ana',origen:'periodo',proyectos:3},
        {vendedor_id:'V1',nombre:'Ana',origen:'anteriores',proyectos:1},
        {vendedor_id:'V2',nombre:'Beto',proyectos:3},
        {vendedor_id:'V3',nombre:'<script>',origen:'anteriores',proyectos:2}],
    colocados_por_periodo:[{vendedor_id:'V1',nombre:'Ana',proyectos:1},
        {vendedor_id:'V1',nombre:'Ana',origen:'anteriores',proyectos:1},
        {vendedor_id:'V2',nombre:'Beto',proyectos:3},
        {vendedor_id:'V3',nombre:'<script>',origen:'anteriores',proyectos:4}]
};
for (const [width,dark] of [[1200,false],[320,false],[320,true]]) {
    const {node,chart}=run(fixture,width,dark);
    assert.equal(node('ventas-eficiencia-proyectado').textContent,'10');
    assert.equal(node('ventas-eficiencia-cotizado').textContent,'9');
    assert.equal(node('ventas-eficiencia-colocado').textContent,'9');
    assert.equal(node('ventas-eficiencia-colocacion').textContent,'100.00 %');
    assert.equal(node('ventas-eficiencia-eficiencia').textContent,'90.00 %');
    assert.deepEqual(chart.getOption().series[0].data.map(row=>row.value),[10,9,9]);
    const rows=node('ventas-eficiencia-vendedores-filas').children.map(row=>row.children.map(cell=>cell.textContent));
    assert.deepEqual(rows,[['<script>','0','2','4','200.00 %','—'],['Beto','4','3','3','100.00 %','75.00 %'],['Ana','6','4','2','50.00 %','33.33 %']]);
    [1,2,3].forEach(index=>assert.equal(rows.reduce((sum,row)=>sum+Number(row[index]),0),[0,10,9,9][index],'Conciliar vendedores y resumen'));
    assert.equal(node('ventas-eficiencia-vendedores').hidden,true);
    chart.trigger('click',{componentType:'series',seriesIndex:0,dataIndex:1});
    assert.equal(node('ventas-eficiencia-vendedores').hidden,false);
    assert.equal(node('ventas-eficiencia-general')['aria-expanded'],'true');
    node('ventas-eficiencia-vendedores').hidden=true;
    let prevented=false;
    node('ventas-eficiencia-general').events.keydown({key:' ',preventDefault(){prevented=true;}});
    assert.ok(prevented && !node('ventas-eficiencia-vendedores').hidden);
    chart.dispose();
}
const empty=run({cantidades:{total_proyectos:0,cotizacion_cliente:0,orden_compra_cliente:0}});
assert.equal(empty.node('ventas-eficiencia-colocacion').textContent,'—');
assert.equal(empty.node('ventas-eficiencia-eficiencia').textContent,'—');
assert.equal(empty.node('ventas-eficiencia-vendedores-filas').children[0].children[0].colSpan,6);
empty.chart.dispose();
const scoped=run({cantidades:{total_proyectos:6,cotizacion_cliente:4,orden_compra_cliente:2},
    proyectos_por_vendedor:[fixture.proyectos_por_vendedor[0]],
    cotizados_por_periodo:fixture.cotizados_por_periodo.filter(row=>row.vendedor_id==='V1'),
    colocados_por_periodo:fixture.colocados_por_periodo.filter(row=>row.vendedor_id==='V1')});
assert.equal(scoped.node('ventas-eficiencia-vendedores-filas').children.length,1,'Reutilizar alcance ya filtrado por vendedor');
assert.equal(scoped.node('ventas-eficiencia-colocacion').textContent,'50.00 %');
scoped.chart.dispose();
console.log('OK: cantidades comparables, porcentajes, anteriores, vendedor, orden, conciliacion, teclado, XSS, cero, movil y oscuro.');
