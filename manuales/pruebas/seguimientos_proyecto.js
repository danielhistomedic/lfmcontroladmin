/* Modal compartido: pruebas sin DataTables ni conexiones reales. */
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const code=fs.readFileSync(require('node:path').join(__dirname,'../../Assets/app/js/seguimientos_proyecto.js'),'utf8');
async function probar(){
 const nodes=new Map();
 const node=id=>{if(!nodes.has(id))nodes.set(id,{dataset:{},events:{},children:[],value:'',textContent:'',
 addEventListener(name,cb){this.events[name]=cb;},setAttribute(name,value){this[name]=value;},
 appendChild(child){child.parentElement=this;this.children.push(child);},replaceChildren(){this.children=[];}});return nodes.get(id);};
 const headers=['id','fecha','usuario','detalle'].map(key=>{const n=node('sort-'+key);n.dataset={seguimientoOrder:key,label:key};n.parentElement=node('th-'+key);return n;});
 node('modalSeguimientosVenta').dataset.url='/portal/Reportesmensuales/seguimientos';
 let ready,shown=0,mode='success',calls=[],pending;
 let data=[{id:768,fecha:'2026-09-11 15:29:01',nombre_usuario:'Usuario',seguimiento:'<script>alert(1)</script>'}];
 const context={window:{},document:{getElementById:node,createElement:tag=>node('created-'+Math.random()),querySelectorAll:()=>headers,addEventListener(name,cb){ready=cb;}},
 bootstrap:{Modal:{getOrCreateInstance:()=>({show(){shown++;}})}},AbortController,FormData,Intl,
 fetch:async(url,config)=>{calls.push([url,config]);if(mode==='delay')return new Promise(resolve=>pending=resolve);
 return {ok:mode!=='error',json:async()=>({respuesta:mode==='error'?'error':'ok',data:mode==='empty'?[]:data})};}};
 vm.runInNewContext(code,context);ready();
 await context.window.verSeguimientosProyecto(633,'PV-2026-20035');
 assert.equal(calls[0][0],'/portal/Reportesmensuales/seguimientos');assert.equal(calls[0][1].body.get('venta_id'),'633');
 assert.equal(node('lbl_modal_seguimiento_proyecto').textContent,'PV-2026-20035');assert.equal(node('lbl_modal_seguimiento_count').textContent,'1 Seguimiento');
 const body=node('tbl_seguimiento_venta_body');assert.equal(body.children[0].children[3].textContent,'<script>alert(1)</script>');
 data=Array.from({length:23},(_,i)=>({id:i+1,fecha:'2026-09-'+String(i+1).padStart(2,'0'),usuario:i%2?'Jos\u00e9':'Ana',observacion:'Nota '+i}));
 await context.window.verSeguimientosProyecto(634,'P2');assert.equal(body.children.length,10);assert.equal(body.children[0].children[0].textContent,'23');
 assert.equal(node('seguimientos-rango').textContent,'1\u201310 de 23 registros');
 node('seguimientos-siguiente').events.click();assert.equal(node('seguimientos-rango').textContent,'11\u201320 de 23 registros');
 node('seguimientos-siguiente').events.click();assert.equal(node('seguimientos-rango').textContent,'21\u201323 de 23 registros');assert.equal(node('seguimientos-siguiente').disabled,true);
 node('seguimientos-anterior').events.click();assert.equal(node('seguimientos-pagina').textContent,'P\u00e1gina 2 de 3');
 headers[0].events.click();assert.equal(body.children[0].children[0].textContent,'1');assert.equal(headers[0].textContent,'id \u2191');
 for(const h of headers){h.events.click();assert.ok(h.textContent.includes('\u2191')||h.textContent.includes('\u2193'));}
 node('seguimientos-buscar').value='jose';node('seguimientos-buscar').events.input();assert.equal(node('seguimientos-rango').textContent,'1\u201310 de 11 registros');
 assert.equal(node('lbl_modal_seguimiento_count').textContent,'23 Seguimientos');assert.equal(calls.length,2,'Buscar, ordenar y paginar localmente');
 node('seguimientos-buscar').value='ausente';node('seguimientos-buscar').events.input();assert.ok(body.children[0].children[0].textContent.includes('No hay coincidencias'));
 data=[{ID:9,fecha_formateada:'31/08/2026 09:00',usuario_nombre:'Z',comentario:'C'},{ID:10,fecha:'2026-09-01 08:00',ccveusuario:'A',nota:'N'}];
 await context.window.verSeguimientosProyecto(635,'P3');headers[1].events.click();assert.equal(body.children[0].children[0].textContent,'9','Ordenar fechas por a\u00f1o, mes, d\u00eda');headers[1].events.click();assert.equal(body.children[0].children[0].textContent,'10');
 node('modalSeguimientosVenta').dataset.lista='interna_sin_cliente';mode='empty';await context.window.verSeguimientosProyecto(636,'P4');
 assert.equal(calls.at(-1)[1].body.get('lista'),'interna_sin_cliente');assert.equal(node('lbl_modal_seguimiento_count').textContent,'0 Seguimientos');assert.ok(body.children[0].children[0].textContent.includes('No hay seguimientos'));
 mode='error';await context.window.verSeguimientosProyecto(637,'P5');assert.ok(body.children[0].children[0].textContent.includes('No se pudieron cargar'));assert.equal(node('seguimientos-buscar').disabled,true);
 mode='delay';const old=context.window.verSeguimientosProyecto(638,'P6');const signal=calls.at(-1)[1].signal;
 node('modalSeguimientosVenta').events['hidden.bs.modal']();assert.equal(signal.aborted,true);
 mode='empty';await context.window.verSeguimientosProyecto(639,'P7');pending({ok:true,json:async()=>({respuesta:'ok',data:[{id:999}]})});await old;
 assert.equal(node('lbl_modal_seguimiento_count').textContent,'0 Seguimientos','Ignorar respuesta antigua');assert.equal(shown,7);
 assert.ok(!/DataTable|jQuery/.test(code),'Sin dependencia de DataTables ni jQuery');
 console.log('OK: tabla HTML, b\u00fasqueda, fechas/ID, cuatro encabezados, paginaci\u00f3n, datos seguros, vac\u00edo/error y cancelaci\u00f3n.');
}
probar().catch(error=>{console.error(error);process.exitCode=1;});
