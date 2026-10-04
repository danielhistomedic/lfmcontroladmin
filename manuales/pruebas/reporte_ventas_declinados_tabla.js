/* Tabla propia del modal: sin navegador, base de datos ni DataTables. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const JSZip = require('../../Assets/vendor/datatable/JSZip-3.10.1/jszip.js');
const code = fs.readFileSync('Assets/app/js/reporte_ventas_mensuales.js', 'utf8');
async function run() {
    const nodes = new Map(), events = {}, calls = []; let exported, failure = false;
    function node(id) {
        if (!nodes.has(id)) nodes.set(id, {id, dataset:{}, style:{}, children:[], events:{}, value:'', textContent:'',
            addEventListener(name,cb){this.events[name]=cb;}, setAttribute(name,value){this[name]=value;},
            appendChild(child){child.parentElement=this;this.children.push(child);}, replaceChildren(){this.children=[];},
            click(){this.clicked=true;}, parentElement:{clientWidth:900,setAttribute(name,value){this[name]=value;}}
        });
        return nodes.get(id);
    }
    const headers=[1,2,3,5,6].map((order,i)=>{const button=node('sort-'+order);button.dataset={order:String(order),label:['ID Proyecto','Fecha','Cliente','Clasificación','Título'][i]};return button;});
    const modal=node('modal-declinados-ventas');modal.dataset={url:'/declinados',anio:'2024,2026',mes:'9,10',vendedor:''};
    node('filtros-ventas-mensuales').querySelector=()=>node('submit');
    node('ventas-mensuales-datos').textContent=JSON.stringify({vendedores:[],productos:[],cruce:[]});
    const rows=Array.from({length:27},(_,i)=>({id:i+1,proyecto_id:'P'+String(i+1).padStart(2,'0'),fecha:'2026-09-'+String(i%28+1).padStart(2,'0'),cliente:'Cliente '+i,clasificacion:'VÁLVULAS FLOWSERVE',titulo:'<img src=x> & =SUM(1)',seller:i<23?'V1':'V2'}));
    const context={document:{addEventListener:(name,cb)=>events[name]=cb,getElementById:node,createElement:tag=>node('created-'+Math.random()),querySelectorAll:selector=>selector.includes('[data-order]')?headers:[]},
        window:{addEventListener(){}}, jQuery:()=>({find:()=>({on(){}}),DataTable(){throw Error('No debe inicializar DataTable');}}),
        bootstrap:{Modal:{getOrCreateInstance:()=>({show(){},hide(){}})}},echarts:{init:()=>({setOption(){},resize(){},on(){}})},
        AbortController,URLSearchParams,Intl,Map,JSON,setTimeout,clearTimeout,
        JSZip:class extends JSZip{generateAsync(options){return super.generateAsync({...options,type:'nodebuffer'});}},
        URL:{createObjectURL(value){exported=value;return 'blob:test';},revokeObjectURL(){}},
        fetch:async(url)=>{
            const p=new URL(url,'http://localhost').searchParams;calls.push(p);
            if(p.has('resumen'))return {ok:true,json:async()=>({status:true,data:{total:27,vendedores:[{vendedor_id:'V1',nombre:'José',proyectos:23,porcentaje:85.19},{vendedor_id:'V2',nombre:'Ana',proyectos:4,porcentaje:14.81}]}})};
            if(failure)return {ok:false,json:async()=>({status:false,message:'Error controlado'})};
            const source=rows.filter(row=>!p.has('declinado_vendedor')||row.seller===p.get('declinado_vendedor'));
            const filtered=source.filter(row=>JSON.stringify(row).toLowerCase().includes((p.get('search')||'').toLowerCase()));
            const key={1:'proyecto_id',2:'fecha',3:'cliente',5:'clasificacion',6:'titulo'}[p.get('order_column')];
            filtered.sort((a,b)=>a[key].localeCompare(b[key])*(p.get('order_dir')==='asc'?1:-1));
            const start=Number(p.get('start')),length=Number(p.get('length'));
            return {ok:true,json:async()=>({status:true,data:{recordsTotal:source.length,recordsFiltered:filtered.length,data:filtered.slice(start,start+length)}})};
        }
    };
    const settle=()=>new Promise(resolve=>setTimeout(resolve,20));
    vm.runInNewContext(code,context);events.DOMContentLoaded();
    assert.equal(calls.length,0);
    modal.events['show.bs.modal']({relatedTarget:{dataset:{}}});await modal.events['shown.bs.modal']();
    assert.equal(node('declinados-detalle-tabla').hidden,true);
    node('declinados-vendedores').children[0].events.click();await settle();
    assert.equal(node('declinados-tabla-propia').hidden,false);assert.equal(node('declinados-tabla-legado').hidden,true);
    assert.equal(node('declinados-tabla-filas').children.length,10);
    assert.equal(node('declinados-tabla-pagina').textContent,'1–10 de 23');
    assert.equal(node('declinados-tabla-filas').children[0].children[4].children[0].className,'declinados-clasificacion declinados-clasificacion-valvulas');
    assert.equal(node('declinados-tabla-filas').children[0].children[5].children[0].textContent,'<img src=x> & =SUM(1)');
    assert.deepEqual(calls.at(-1).getAll('anio[]'),['2024','2026']);assert.deepEqual(calls.at(-1).getAll('mes[]'),['9','10']);
    node('declinados-siguiente').events.click();await settle();assert.equal(node('declinados-tabla-pagina').textContent,'11–20 de 23');
    node('declinados-vendedores').children[1].events.click();await settle();
    assert.equal(node('declinados-tabla-pagina').textContent,'1–4 de 4','Ajustar página al último rango disponible');
    headers[1].events.click();await settle();assert.equal(calls.at(-1).get('order_dir'),'asc');assert.equal(headers[1].textContent,'Fecha ↑');
    headers[1].events.click();await settle();assert.equal(headers[1].textContent,'Fecha ↓');
    for(const header of headers){header.events.click();await settle();assert.equal(calls.at(-1).get('order_column'),header.dataset.order);}
    node('declinados-buscar').value='Cliente 2';node('declinados-buscar').events.input();await new Promise(resolve=>setTimeout(resolve,330));
    node('declinados-vendedores').children[0].events.click();await settle();assert.equal(calls.at(-1).get('search'),'Cliente 2');assert.equal(calls.at(-1).get('order_column'),'6');
    node('declinados-todos').events.click();await settle();assert.equal(calls.at(-1).has('declinado_vendedor'),false);
    failure=true;node('declinados-reintentar').events.click();await settle();assert.equal(node('declinados-reintentar').hidden,false);assert.equal(node('declinados-estado').textContent,'Error controlado');
    failure=false;node('declinados-buscar').value='no-existe';node('declinados-reintentar').events.click();await settle();assert.equal(node('declinados-tabla-pagina').textContent,'0 resultados');
    // Exporta todo el detalle seleccionado, sin limitarse a la búsqueda ni la página.
    for(let i=28;i<=109;i++)rows.push({...rows[0],id:i,proyecto_id:'P'+i,seller:'V1'});
    node('declinados-vendedores').children[0].events.click();await settle();node('declinados-exportar').events.click();
    for(let i=0;i<100&&!exported;i++)await settle();assert.ok(exported,'Generar XLSX sin DataTable');
    const zip=await JSZip.loadAsync(exported),sheet=await zip.file('xl/worksheets/sheet1.xml').async('string');
    assert.equal((sheet.match(/<row /g)||[]).length,106);assert.ok(sheet.includes('&lt;img src=x&gt; &amp; =SUM(1)'));assert.ok(!sheet.includes('<f>'));
    assert.equal(calls.at(-1).get('start'),'100','Exportar también las páginas posteriores');
    assert.equal(calls.at(-1).get('declinado_vendedor'),'V1');assert.equal(calls.at(-1).get('length'),'100');
    exported=null;node('declinados-todos').events.click();await settle();node('declinados-exportar').events.click();
    for(let i=0;i<100&&!exported;i++)await settle();
    assert.equal((await (await JSZip.loadAsync(exported)).file('xl/worksheets/sheet1.xml').async('string')).match(/<row /g).length,110);
    modal.events['hidden.bs.modal']();
    console.log('OK: tabla sin DataTables, búsqueda, cinco encabezados, paginación, vendedores, error/reintento, salida segura y Excel real.');
}
run().catch(error=>{console.error(error);process.exitCode=1;});
