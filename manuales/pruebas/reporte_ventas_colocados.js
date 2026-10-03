/* Node: modal financiero con DOM y fetch simulados. */
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const code = fs.readFileSync(__dirname + '/../../Assets/app/js/reporte_ventas_colocados.js','utf8');
const nodes = new Map(), queries = [];
function node(id) {
    if (!nodes.has(id)) nodes.set(id,{id,dataset:{},events:{},children:[],hidden:false,textContent:'',value:id.endsWith('-orden')?'2:desc':'',disabled:false,
        setAttribute(name,value){this[name]=value;},querySelector(){return node(this.id+'-body');},
        addEventListener(name,fn){this.events[name]=fn;},replaceChildren(){this.children=[];},appendChild(child){this.children.push(child);},click(){this.clicked=true;}});
    return nodes.get(id);
}
const modal = node('modal-colocados-financiero');
modal.dataset={url:'/portal/reportesmensuales/colocadosfinanciero',anio:'2024,2026',mes:'2,9',vendedor:'V1'};
const card=node('card'); let ready, fail=false, defer=false; const pending=[];
const totals={totales:[{moneda_id:3,moneda:'USD',total:'90.00',pedidos:1},{moneda_id:1,moneda:'MXN',total:'1234.50',pedidos:2}],
    mensual:[{anio:2026,mes:9,moneda:'USD',pedidos:1,total:'90.00',productos:'60.00',servicios:'30.00'}],
    grupos:[{grupo:'Flowserve',moneda_id:1,moneda:'MXN',total:'500.00',pedidos:1},{grupo:'Diversos',moneda_id:3,moneda:'USD',total:'90.00',pedidos:1}]};
const context={document:{getElementById:node,querySelectorAll:()=>[card],createElement:tag=>({tag,events:{},children:[],setAttribute(name,value){this[name]=value;},addEventListener(name,fn){this.events[name]=fn;},appendChild(child){this.children.push(child);}}),
    addEventListener(name,fn){ready=fn;}},setTimeout,clearTimeout,Map,Intl,Number,URLSearchParams,AbortController,DOMException,SyntaxError,
    fetch:async (url,options)=>{
        const query=new URL(url,'http://test').searchParams;
        queries.push(query);
        if(defer) await new Promise(resolve=>pending.push(resolve));
        if(fail) throw new SyntaxError('invalid JSON');
        const section=query.get('seccion');
        return {ok:true,json:async()=>({status:true,data:section==='resumen'?totals:section==='detalle'?{recordsFiltered:1,data:[{pedido_id:4,venta_id:3,proyecto_id:'PV-2026-4',clave:'CLAVE1',ccn:'CCN1',codigo_cliente:'CLIENTE1',num_orden_compra:'OC1',fecha_pedido:'2026-09-01',moneda:'USD',tipo_partida:'SERVICIO',codigo_partida:'P1',clave_material:'MAT',descripcion:'<script>',cantidad_pedido:2,precio_unitario:15,subtotal_partida:30}]}:{draw:1,recordsTotal:1,recordsFiltered:1,
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
    const body=node('colocados-clientes-tabla-body');
    assert.equal(body.children[0].children[0].textContent,'<script>alert(1)</script>','Nombres como texto, sin HTML');
    assert.equal(body.children[0].children[2].textContent,'$ 90.00');
    assert.equal(node('colocados-clientes-siguiente').disabled,true);
    assert.equal(node('colocados-clientes-anterior').disabled,true);
    assert.ok(queries.some(query=>query.get('length')==='10'));
    node('colocados-clientes-orden').value='2:desc';
    node('colocados-clientes-orden').events.change(); await tick();
    assert.equal(queries.at(-1).get('order_column'),'2');
    assert.equal(queries.at(-1).get('order_dir'),'desc');
    const monthly=node('colocados-mensual-filas');
    assert.equal(monthly.children[0].children[5].textContent,'$ 60.00');
    assert.equal(monthly.children[0].children[6].textContent,'$ 30.00');
    monthly.children[0].children[7].children[0].events.click(); await tick();
    assert.deepEqual(queries.at(-1).getAll('anio[]'),['2026']);
    assert.deepEqual(queries.at(-1).getAll('mes[]'),['9']);
    assert.equal(queries.at(-1).get('seccion'),'detalle');
    assert.equal(node('colocados-partidas').open,true,'Ver partidas abre el desplegable');
    const detailRow=node('colocados-partidas-filas').children[0];
    assert.equal(detailRow.children[0].textContent,'PV-2026-4');
    assert.equal(detailRow.children[5].textContent,'CLAVE1');
    assert.equal(detailRow.children[6].textContent,'CCN1');
    assert.equal(detailRow.children[7].textContent,'CLIENTE1');
    assert.equal(detailRow.children[8].textContent,'<script>');
    assert.equal(node('colocados-partidas-filas').children[0].children[11].textContent,'$ 30.00');
    assert.equal(queries.at(-1).get('length'),'5','Detalle muestra cinco filas');
    node('colocados-partidas-orden-11').events.click(); await tick();
    assert.equal(queries.at(-1).get('order_column'),'11');
    assert.equal(queries.at(-1).get('order_dir'),'asc');
    node('colocados-partidas-orden-11').events.click(); await tick();
    assert.equal(queries.at(-1).get('order_dir'),'desc');
    assert.equal(node('colocados-partidas-columna-11')['aria-sort'],'descending');
    node('colocados-partidas-buscar').value='material';
    node('colocados-partidas-buscar').events.input();
    await new Promise(resolve=>setTimeout(resolve,400));
    assert.equal(queries.at(-1).get('search'),'material');
    assert.equal(queries.at(-1).get('start'),'0');
    fail=true; node('colocados-financiero-reintentar').events.click(); await tick();
    assert.equal(node('colocados-financiero-reintentar').hidden,false);
    assert.ok(node('colocados-financiero-estado').textContent.includes('Intente nuevamente'));
    assert.equal(body.children.length,0,'Tabla vacia ante error');
    assert.ok(node('colocados-clientes-estado').textContent.includes('Intente nuevamente'));
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
