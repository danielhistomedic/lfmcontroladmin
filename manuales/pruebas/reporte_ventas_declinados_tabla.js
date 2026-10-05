/* Tabla propia del modal: sin navegador, base de datos ni DataTables. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const JSZip = require('../../Assets/vendor/datatable/JSZip-3.10.1/jszip.js');
const code = fs.readFileSync('Assets/app/js/reporte_ventas_mensuales.js', 'utf8');
async function run(internal=false,quoted=false,documents=false) {
    const nodes = new Map(), events = {}, calls = []; let exported, failure = false, consolidated = false;
    function node(id) {
        if (!nodes.has(id)) nodes.set(id, {id, dataset:{}, style:{}, children:[], events:{}, value:'', textContent:'',
            addEventListener(name,cb,options={}){
                this.listeners??={};(this.listeners[name]??=[]).push({cb,once:options.once});
                this.events[name]=(...args)=>{let result;for(const entry of [...this.listeners[name]]){
                    if(entry.once)this.listeners[name]=this.listeners[name].filter(item=>item!==entry);
                    const value=entry.cb(...args);if(value!==undefined)result=value;
                }return result;};
            }, setAttribute(name,value){this[name]=value;},
            appendChild(child){child.parentElement=this;this.children.push(child);}, replaceChildren(){this.children=[];},
            prepend(child){child.parentElement=this;this.children.unshift(child);},
            click(){this.clicked=true;}, focus(){this.focused=true;}, parentElement:{clientWidth:900,setAttribute(name,value){this[name]=value;}}
        });
        return nodes.get(id);
    }
    const headers=[1,2,3,4,5,6].map((order,i)=>{const button=node('sort-'+order);button.dataset={order:String(order),label:['ID Proyecto','Fecha','Cliente','Vendedor','Clasificación','Título'][i]};return button;});
    const modal=node('modal-declinados-ventas');modal.dataset={url:'/declinados',anio:'2024,2026',mes:'9,10',vendedor:''};
    if(quoted)Object.assign(modal.dataset,{desgloseTitulo:(documents==='clasificacion_periodo'?'VÁLVULAS FLOWSERVE':'PEDIDO COTIZADO (SIN OC CLIENTE)')+' · Todo el período seleccionado',desgloseAnio:'2026',desgloseMes:'9,10',estatusId:'5',clasificacionId:'4',segmento:'no_declinados'});
    node('filtros-ventas-mensuales').querySelector=()=>node('submit');
    node('ventas-mensuales-datos').textContent=JSON.stringify({vendedores:[],productos:[],cruce:[]});
    const rows=Array.from({length:27},(_,i)=>({id:i+1,proyecto_id:'P'+String(i+1).padStart(2,'0'),fecha:'2026-09-'+String(i%28+1).padStart(2,'0'),cliente:'Cliente '+i,clasificacion:'VÁLVULAS FLOWSERVE',titulo:'<img src=x> & =SUM(1)',seller:i<23?'V1':'V2',activo:i%2?'ACTIVO':'CERRADO'}));
    rows.forEach(row=>{row.vendedor='Vendedor '+row.seller;row.vendedor_id=row.seller;});
    const context={document:{addEventListener:(name,cb)=>events[name]=cb,getElementById:node,createElement:tag=>node('created-'+Math.random()),createTextNode:text=>({textContent:text}),querySelectorAll:selector=>selector.includes('[data-order]')?headers:[]},
        window:{addEventListener(){},verSeguimientosProyecto(id,project){context.followup=[id,project];}}, jQuery:()=>({find:()=>({on(){}}),DataTable(){throw Error('No debe inicializar DataTable');}}),
        bootstrap:{Modal:{getOrCreateInstance:()=>({show(){},hide(){}})}},echarts:{init:()=>({setOption(){},resize(){},on(){}})},
        AbortController,URLSearchParams,Intl,Map,JSON,setTimeout,clearTimeout,
        JSZip:class extends JSZip{generateAsync(options){return super.generateAsync({...options,type:'nodebuffer'});}},
        URL:{createObjectURL(value){exported=value;return 'blob:test';},revokeObjectURL(){}},
        fetch:async(url)=>{
            const p=new URL(url,'http://localhost').searchParams;calls.push(p);
            if(p.has('resumen'))return {ok:true,json:async()=>({status:true,data:{total:27,vendedores:[{vendedor_id:'V1',nombre:'José',proyectos:23,porcentaje:85.19},{vendedor_id:'V2',nombre:'Ana',proyectos:4,porcentaje:14.81}]}})};
            if(failure)return {ok:false,json:async()=>({status:false,message:'Error controlado'})};
            let source=rows.filter(row=>!p.has('declinado_vendedor')||row.seller===p.get('declinado_vendedor'));
            if(consolidated)source=source.filter(row=>row.activo!=='CERRADO'&&String([1,3,6,7,8][row.id%5])===p.get('estatus_id'));
            if(quoted&&p.get('segmento')==='declinados'&&modal.dataset.estatusAgrupados)
                source=source.filter(row=>row.activo==='CERRADO'&&['sin_estatus','1','3'][row.id%3]===p.get('estatus_id'));
            const filtered=source.filter(row=>JSON.stringify(row).toLowerCase().includes((p.get('search')||'').toLowerCase()));
            const key={1:'proyecto_id',2:'fecha',3:'cliente',4:'vendedor',5:'clasificacion',6:'titulo',7:'activo'}[p.get('order_column')];
            filtered.sort((a,b)=>a[key].localeCompare(b[key])*(p.get('order_dir')==='asc'?1:-1));
            const start=Number(p.get('start')),length=Number(p.get('length'));
            return {ok:true,json:async()=>({status:true,data:{recordsTotal:source.length,recordsFiltered:filtered.length,data:filtered.slice(start,start+length)}})};
        }
    };
    const settle=()=>new Promise(resolve=>setTimeout(resolve,20));
    vm.runInNewContext(code,context);events.DOMContentLoaded();
    assert.equal(calls.length,0);
    modal.events['show.bs.modal']({relatedTarget:{dataset:quoted?{lista:['colocados_periodo','clasificacion_periodo'].includes(documents)?documents:documents?'cotizados_periodo':'estatus_periodo'}:internal?{lista:'interna_sin_cliente'}:{}}});await modal.events['shown.bs.modal']();
    if(quoted){
        assert.equal(calls.length,1);assert.equal(calls[0].get('length'),'100');assert.deepEqual(calls[0].getAll('anio[]'),['2026']);
        if(documents==='clasificacion_periodo'){
            assert.equal(calls[0].get('lista'),'clasificacion_periodo');assert.equal(calls[0].get('clasificacion_id'),'4');assert.equal(calls[0].get('segmento'),'no_declinados');
            assert.ok(!calls[0].has('estatus_id'));assert.ok(!calls[0].has('periodo_anio[]'));
        }
        else if(documents){assert.deepEqual(calls[0].getAll('periodo_anio[]'),['2024','2026']);assert.deepEqual(calls[0].getAll('periodo_mes[]'),['9','10']);assert.equal(calls[0].get('lista'),documents==='colocados_periodo'?documents:'cotizados_periodo');assert.ok(!calls[0].has('estatus_id'));assert.ok(!calls[0].has('segmento'));}
        else {assert.equal(calls[0].get('estatus_id'),'5');assert.equal(calls[0].get('segmento'),'no_declinados');}
        assert.equal(modal.dataset.tablaEstatus,'1');assert.equal(node('declinados-tabla-legado').hidden,true);
        const vendors=node('declinados-vendedores').children;assert.equal(vendors.length,2);
        assert.equal(vendors[0].dataset.vendedor,'V1');assert.equal(vendors[0].children[1].textContent,'23');assert.equal(vendors[0].children[3].textContent,'85.19 %');
        const body=node('declinados-tabla-filas');assert.equal(body.children.filter(row=>row.children.length===8).length,10);
        assert.equal(body.children[0].children[0].textContent,'Vendedor V1');assert.equal(body.children[0].children[0].colSpan,8);
        assert.equal(node('declinados-seguimiento-cabecera').hidden,false);assert.equal(node('declinados-todos')['aria-pressed'],'true');
        node('declinados-siguiente').events.click();await settle();node('declinados-siguiente').events.click();await settle();
        assert.equal(node('declinados-tabla-pagina').textContent,'21–27 de 27');
        assert.equal(body.children.filter(row=>row.children.length===1).length,2,'Mantener grupos al cambiar de página');
        vendors[1].events.click();await settle();assert.equal(body.children.length,4);assert.ok(body.children.every(row=>row.children.length===8));
        assert.equal(node('declinados-detalle-titulo').textContent,'Proyectos de: Vendedor V2');assert.equal(node('declinados-tabla-pagina').textContent,'1–4 de 4');
        assert.equal(vendors[1]['aria-pressed'],'true');assert.ok(body.children.every(row=>row.children[4].children[0].textContent==='Vendedor V2'));
        for(const projectRow of body.children){
            const button=projectRow.children[7].children[0];
            const id=Number(projectRow.children[1].children[0].textContent.slice(1));
            assert.equal(button.children[1].textContent,'Ver seguimiento');
            assert.ok(button.className.includes(rows[id-1].activo==='CERRADO'?'btn-danger':'btn-primary'));
        }
        const followupButton=body.children[0].children[7].children[0];
        followupButton.events.click({stopPropagation(){}});modal.events['hidden.bs.modal']();
        assert.deepEqual(context.followup,[27,'P27'],'Todos los estatus usan el historial existente con tb_ventas.id');
        node('modalSeguimientosVenta').events['hidden.bs.modal']();await modal.events['shown.bs.modal']();
        assert.equal(calls.length,1,'Volver del historial conserva vendedor y pagina sin recargar proyectos');
        assert.equal(node('declinados-tabla-pagina').textContent,'1–4 de 4');
        for(const header of headers){header.events.click();await settle();assert.ok(header.textContent.includes('↑')||header.textContent.includes('↓'));}
        node('declinados-buscar').value='Cliente 2';node('declinados-buscar').events.input();await new Promise(resolve=>setTimeout(resolve,330));
        vendors[0].events.click();await settle();assert.equal(node('declinados-buscar').value,'Cliente 2');assert.equal(body.children.length,4);
        node('declinados-todos').events.click();await settle();assert.equal(body.children.filter(row=>row.children.length===1).length,2);
        assert.equal(calls.length,1,'Vendedor, búsqueda, orden y páginas no repiten consultas');
        node('declinados-exportar-pagina').events.click();for(let i=0;i<100&&!exported;i++)await settle();
        const sheet=await (await JSZip.loadAsync(exported)).file('xl/worksheets/sheet1.xml').async('string');assert.ok(sheet.includes('Vendedor'));assert.ok(sheet.includes('A1:G9'));
        node('declinados-buscar').value='sin-coincidencias';node('declinados-buscar').events.input();await new Promise(resolve=>setTimeout(resolve,330));assert.equal(body.children[0].children[0].colSpan,8);
        failure=true;await modal.events['shown.bs.modal']();assert.equal(node('declinados-reintentar').hidden,false);
        failure=false;node('declinados-reintentar').events.click();await settle();assert.equal(node('declinados-total').textContent,'27 Proyectos');
        for(let i=28;i<=205;i++)rows.push({...rows[0],id:i,proyecto_id:'P'+i});
        const before=calls.length;await modal.events['shown.bs.modal']();
        assert.deepEqual(calls.slice(before).map(params=>params.get('start')),['0','100','200'],'Obtener distribución completa mediante el contrato paginado existente');
        assert.equal(node('declinados-total').textContent,'205 Proyectos');
        assert.equal(node('declinados-vendedores').children.reduce((sum,button)=>sum+Number(button.children[1].textContent),0),205);
        if(documents==='clasificacion_periodo')for(const id of ['2','3','4','5']){
            modal.dataset.clasificacionId=id;
            modal.events['show.bs.modal']({relatedTarget:{dataset:{lista:'clasificacion_periodo'}}});await modal.events['shown.bs.modal']();
            assert.equal(calls.at(-1).get('clasificacion_id'),id);
            assert.equal(calls.at(-1).get('segmento'),'no_declinados');
            assert.equal(node('declinados-seguimiento-cabecera').hidden,false);
            assert.equal(node('declinados-tabla-legado').hidden,true);
            assert.equal(node('declinados-vendedores').children.length,2);
        }
        if(!documents){
            for(const [id,name] of [['1','EN OPORTUNIDAD DE VENTA'],['3','EN COTIZACIÓN INTERNA (SIN COT. CLIENTE)'],['6','PEDIDO COLOCADO'],['7','PEDIDO COLOCADO (EN ORDEN COMPRA PROVEEDOR)'],['8','PEDIDO COLOCADO (FACTURADO)'],['sin_estatus','Sin estatus']]){
                modal.dataset.estatusId=id;modal.dataset.desgloseTitulo=name+' · Todo el período seleccionado';
                modal.events['show.bs.modal']({relatedTarget:{dataset:{lista:'estatus_periodo'}}});await modal.events['shown.bs.modal']();
                assert.equal(node('modal-declinados-titulo').textContent,modal.dataset.desgloseTitulo);
                assert.equal(calls.at(-1).get('estatus_id'),id);assert.equal(modal.dataset.tablaEstatus,'1');
                assert.equal(node('declinados-total').textContent,'205 Proyectos');assert.equal(node('declinados-seguimiento-cabecera').hidden,false);
            }
            consolidated=true;
            for(const [ids,name] of [[['1','3'],'PROCESO DE COTIZACION'],[['6','7','8'],'Pedidos Colocados']]){
                modal.dataset.estatusAgrupados=JSON.stringify(ids);modal.dataset.segmento='no_declinados';modal.dataset.desgloseTitulo=name+' · Todo el período seleccionado';
                const beforeGroup=calls.length;await modal.events['shown.bs.modal']();
                assert.deepEqual(calls.slice(beforeGroup).map(params=>params.get('estatus_id')),ids);
                const total=rows.filter(row=>row.activo!=='CERRADO'&&ids.includes(String([1,3,6,7,8][row.id%5]))).length;
                assert.equal(node('declinados-total').textContent,total+' Proyectos','Incluir todos los IDs de la categoria y excluir declinados');
                assert.equal(node('declinados-vendedores').children.reduce((sum,button)=>sum+Number(button.children[1].textContent),0),total);
            }
            consolidated=false;
            modal.dataset.estatusAgrupados=JSON.stringify(['1','3','sin_estatus']);modal.dataset.segmento='declinados';modal.dataset.desgloseTitulo='Declinados · Todo el período seleccionado';
            const beforeDeclined=calls.length;await modal.events['shown.bs.modal']();
            assert.deepEqual(calls.slice(beforeDeclined).map(params=>params.get('estatus_id')),['1','3','sin_estatus']);
            const total=rows.filter(row=>row.activo==='CERRADO').length;
            assert.equal(node('declinados-total').textContent,total+' Proyectos','Declinados de varios estatus, sin doble conteo');
            assert.equal(node('declinados-vendedores').children.reduce((sum,button)=>sum+Number(button.children[1].textContent),0),total);
        }
        console.log('OK: Pedido Cotizado, siete columnas, grupos por vendedor, TODOS, resumen, búsqueda, orden, páginas, Excel y parámetros '+(documents?'documentales':'de estatus')+'.');return;
    }
    if(internal){
        assert.equal(calls.length,1);assert.equal(calls[0].get('lista'),'interna_sin_cliente');assert.ok(!calls[0].has('resumen'));assert.ok(!calls[0].has('declinado_vendedor'));
        assert.equal(node('declinados-tabla-propia').hidden,false);assert.equal(node('declinados-tabla-legado').hidden,true);
        assert.equal(node('declinados-resumen').hidden,true);assert.equal(node('declinados-kpis').hidden,true);
        assert.equal(node('declinados-total').textContent,'27 Proyectos');assert.equal(node('declinados-tabla-pagina').textContent,'1–5 de 27');
        assert.equal(calls[0].get('length'),'5');assert.equal(node('declinados-tabla-filas').children.length,5);
        const body=node('declinados-tabla-filas');assert.equal(body.children[0].children.length,8,'Ocho columnas, sin Activo');
        assert.equal(body.children[0].children[4].children[0].textContent,'Vendedor V2');
        assert.ok(body.children[0].children[5].children[0].className.includes('clasificacion-valvulas'));
        assert.equal(body.children[0].children[6].children[0].textContent,'<img src=x> & =SUM(1)');
        const button=body.children[0].children[7].children[0];
        assert.equal(button.children.length,2,'Icono y etiqueta compacta');assert.equal(button.children[1].textContent,'Ver seguimiento');assert.equal(button.children[0].className,'fa-solid fa-list-check');
        assert.equal(button.title,'Ver seguimiento del proyecto');assert.ok(button.className.includes('btn-danger'));
        button.events.click({stopPropagation(){}});modal.events['hidden.bs.modal']();
        assert.deepEqual(context.followup,[27,'P27']);assert.equal(node('modalSeguimientosVenta').dataset.lista,'interna_sin_cliente');
        node('modalSeguimientosVenta').events['hidden.bs.modal']();await modal.events['shown.bs.modal']();assert.equal(calls.length,1);
        node('declinados-siguiente').events.click();await settle();assert.equal(node('declinados-tabla-pagina').textContent,'6–10 de 27');
        assert.equal(calls.at(-1).get('start'),'5');
        node('declinados-exportar-pagina').events.click();for(let i=0;i<100&&!exported;i++)await settle();
        const zip=await JSZip.loadAsync(exported),sheet=await zip.file('xl/worksheets/sheet1.xml').async('string');
        assert.equal((sheet.match(/<row /g)||[]).length,6);assert.ok(sheet.includes('Vendedor'));assert.ok(!sheet.includes('Activo'));assert.ok(sheet.includes('A1:G6'));
        for(const header of headers){header.events.click();await settle();assert.equal(calls.at(-1).get('order_column'),header.dataset.order);}
        node('declinados-buscar').value='Cliente 2';node('declinados-buscar').events.input();await new Promise(resolve=>setTimeout(resolve,330));assert.equal(calls.at(-1).get('search'),'Cliente 2');
        failure=true;node('declinados-reintentar').events.click();await settle();assert.equal(node('declinados-reintentar').hidden,false);
        failure=false;node('declinados-buscar').value='sin-coincidencias';node('declinados-reintentar').events.click();await settle();assert.equal(body.children[0].children[0].colSpan,8);
        modal.events['show.bs.modal']({relatedTarget:{dataset:{}}});await modal.events['shown.bs.modal']();assert.equal(calls.at(-1).get('resumen'),'1');
        node('declinados-vendedores').children[0].events.click();await settle();assert.equal(body.children[0].children.length,7,'Regresar a columnas de Declinados');
        console.log('OK: Cotización Interna, ocho columnas sin Activo, icono, filtros, orden, páginas, historial, Excel y transición a Declinados.');return;
    }
    assert.equal(node('declinados-detalle-tabla').hidden,true);
    node('declinados-vendedores').children[0].events.click();await settle();
    assert.equal(node('declinados-tabla-propia').hidden,false);assert.equal(node('declinados-tabla-legado').hidden,true);
    assert.equal(node('declinados-tabla-filas').children.length,10);
    assert.equal(node('declinados-tabla-pagina').textContent,'1–10 de 23');
    assert.equal(node('declinados-tabla-filas').children[0].children[4].children[0].className,'declinados-clasificacion declinados-clasificacion-valvulas');
    assert.equal(node('declinados-tabla-filas').children[0].children[5].children[0].textContent,'<img src=x> & =SUM(1)');
    assert.deepEqual(calls.at(-1).getAll('anio[]'),['2024','2026']);assert.deepEqual(calls.at(-1).getAll('mes[]'),['9','10']);
    node('declinados-siguiente').events.click();await settle();assert.equal(node('declinados-tabla-pagina').textContent,'11–20 de 23');
    const pageRows=node('declinados-tabla-filas').children;
    assert.equal(pageRows[0].children.length,7);
    const followButton=pageRows[0].children[6].children[0];
    assert.ok(followButton.className.includes('btn-danger'));assert.ok(pageRows[1].children[6].children[0].className.includes('btn-primary'));
    assert.equal(followButton.title,'Ver seguimiento del proyecto');
    const callsBefore=calls.length;
    followButton.events.click({stopPropagation(){}});modal.events['hidden.bs.modal']();
    assert.deepEqual(context.followup,[13,'P13'],'Usar tb_ventas.id, no folio ni índice de fila');
    assert.equal(node('modalSeguimientosVenta').dataset.lista,'declinados');
    node('modalSeguimientosVenta').events['hidden.bs.modal']();await modal.events['shown.bs.modal']();
    assert.equal(calls.length,callsBefore,'Regresar sin recargar ni perder búsqueda, orden o página');
    assert.equal(node('declinados-tabla-pagina').textContent,'11–20 de 23');assert.equal(followButton.focused,true);
    node('declinados-vendedores').children[1].events.click();await settle();
    assert.equal(node('declinados-tabla-pagina').textContent,'1–4 de 4','Ajustar página al último rango disponible');
    headers[1].events.click();await settle();assert.equal(calls.at(-1).get('order_dir'),'asc');assert.equal(headers[1].textContent,'Fecha ↑');
    headers[1].events.click();await settle();assert.equal(headers[1].textContent,'Fecha ↓');
    for(const header of headers.filter(button=>!button.parentElement.hidden)){header.events.click();await settle();assert.equal(calls.at(-1).get('order_column'),header.dataset.order);}
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
run().then(()=>run(true)).then(()=>run(false,true)).then(()=>run(false,true,true)).then(()=>run(false,true,'colocados_periodo')).then(()=>run(false,true,'clasificacion_periodo')).catch(error=>{console.error(error);process.exitCode=1;});
