/* Node: modal financiero con DOM, DataTables y fetch simulados. */
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const code = fs.readFileSync(__dirname + '/../../Assets/app/js/reporte_ventas_colocados.js','utf8');
const nodes = new Map(), configurations = new Map(), outputs = new Map(), queries = [];
function node(id) {
    if (!nodes.has(id)) nodes.set(id,{id,dataset:{},events:{},children:[],hidden:false,textContent:'',
        addEventListener(name,fn){this.events[name]=fn;},replaceChildren(){this.children=[];},appendChild(child){this.children.push(child);},click(){this.clicked=true;}});
    return nodes.get(id);
}
const modal = node('modal-colocados-financiero');
modal.dataset={url:'/portal/reportesmensuales/colocadosfinanciero',anio:'2024,2026',mes:'2,9',vendedor:'V1'};
const card=node('card'); let ready, fail=false, defer=false; const pending=[];
const totals={totales:[{moneda_id:3,moneda:'USD',total:'90.00',pedidos:1},{moneda_id:1,moneda:'MXN',total:'1234.50',pedidos:2}],
    grupos:[{grupo:'Flowserve',moneda_id:1,moneda:'MXN',total:'500.00',pedidos:1},{grupo:'Diversos',moneda_id:3,moneda:'USD',total:'90.00',pedidos:1}]};
function jquery(element) {
    return {DataTable(config) {
        configurations.set(element.id,config);
        const data={draw:1,start:0,length:10,search:{value:''},order:[{column:1,dir:'asc'}]};
        const run=()=>config.ajax(data,payload=>outputs.set(element.id,payload));
        run(); return {columns:{adjust(){}},ajax:{reload:run}};
    }};
}
jquery.fn={dataTable:{render:{text:()=>({display:value=>String(value).replaceAll('<','&lt;').replaceAll('>','&gt;')})}}};
const context={document:{getElementById:node,querySelectorAll:()=>[card],createElement:tag=>({tag,children:[],appendChild(child){this.children.push(child);}}),
    addEventListener(name,fn){ready=fn;}},jQuery:jquery,Map,Intl,Number,URLSearchParams,AbortController,DOMException,SyntaxError,
    fetch:async (url,options)=>{
        const query=new URL(url,'http://test').searchParams;
        queries.push(query);
        if(defer) await new Promise(resolve=>pending.push(resolve));
        if(fail) throw new SyntaxError('invalid JSON');
        const section=query.get('seccion');
        return {ok:true,json:async()=>({status:true,data:section==='resumen'?totals:{draw:1,recordsTotal:1,recordsFiltered:1,
            data:[{nombre:'<script>alert(1)</script>',moneda:'USD',total:'90.00'}]}})};
    }};
async function tick(){await new Promise(resolve=>setImmediate(resolve));}
(async()=>{
    vm.runInNewContext(code,context); ready();
    assert.equal(queries.length,0,'No cargar datos antes de abrir la tarjeta');
    let prevented=false; card.events.keydown({key:'Enter',preventDefault(){prevented=true;}});
    assert.ok(prevented && card.clicked,'Acceso por teclado');
    modal.events['shown.bs.modal'](); await tick();
    assert.equal(queries.length,3);
    queries.forEach(query=>{assert.deepEqual(query.getAll('anio[]'),['2024','2026']);assert.deepEqual(query.getAll('mes[]'),['2','9']);assert.equal(query.get('vendedor'),'V1');});
    assert.equal(node('colocados-financiero-resumen').hidden,false);
    assert.equal(node('colocados-totales').children[0].children[1].textContent,'$ 90.00');
    assert.equal(node('colocados-totales').children[1].children[1].textContent,'$ 1,234.50');
    assert.equal(node('colocados-flowserve').children[0].children[1].textContent,'$ 0.00','No convertir MXN a USD');
    assert.equal(node('colocados-flowserve').children[1].children[1].textContent,'$ 500.00');
    const config=configurations.get('colocados-clientes-tabla');
    assert.equal(config.serverSide,true);assert.equal(config.pageLength,10);
    assert.equal(config.columns[0].render('<script>','display'),'&lt;script&gt;');
    assert.equal(config.columns[2].render('1234.5','display'),'$ 1,234.50');
    assert.equal(outputs.get('colocados-clientes-tabla').recordsTotal,1);
    fail=true; node('colocados-financiero-reintentar').events.click(); await tick();
    assert.equal(node('colocados-financiero-reintentar').hidden,false);
    assert.ok(node('colocados-financiero-estado').textContent.includes('Intente nuevamente'));
    assert.equal(outputs.get('colocados-clientes-tabla').recordsTotal,0,'Salida valida DataTables ante error');
    fail=false; defer=true; node('colocados-financiero-reintentar').events.click();
    modal.events['hidden.bs.modal'](); pending.splice(0).forEach(resolve=>resolve()); await tick();
    assert.equal(node('colocados-financiero-resumen').hidden,true,'Respuesta tardia no repinta el modal cerrado');
    defer=false; totals.totales=[];totals.grupos=[];
    modal.events['shown.bs.modal'](); await tick();
    assert.equal(node('colocados-financiero-resumen').hidden,false);
    assert.ok(node('colocados-financiero-estado').textContent.includes('No hay pedidos'));
    assert.equal(node('colocados-totales').children[0].children[1].textContent,'$ 0.00');
    console.log('OK: carga bajo demanda, monedas separadas, filtros multiples, tablas paginadas, teclado, XSS, vacio, error y cancelacion.');
})().catch(error=>{console.error(error);process.exitCode=1;});
