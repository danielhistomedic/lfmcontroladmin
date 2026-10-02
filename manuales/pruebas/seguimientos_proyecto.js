/* Prueba del modal compartido, sin conexiones reales. */
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const code = fs.readFileSync(require('node:path').join(__dirname, '../../Assets/app/js/seguimientos_proyecto.js'), 'utf8');
async function probar() {
    const nodes = new Map();
    const node = id => {
        if (!nodes.has(id)) nodes.set(id, { dataset: {url:'/portal/Reportesmensuales/seguimientos'}, events:{},
            addEventListener(name, callback) { this.events[name]=callback; } });
        return nodes.get(id);
    };
    let ready, options, active=false, shown=0, mode='success', calls=[];
    const table = { columns:{adjust() {}}, destroy() { active=false; } };
    const jquery = () => ({ DataTable(config) { if(config) { options=config; active=true; } return table; } });
    jquery.fn = { DataTable: {isDataTable:()=>active}, dataTable:{ render:{text:()=>({display:v=>v.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')})} } };
    const context = { window:{}, document:{ getElementById:node, addEventListener(name, cb) {ready=cb;} },
        jQuery:jquery, bootstrap:{Modal:{getOrCreateInstance:()=>({show(){shown++;}})}},
        AbortController, FormData,
        fetch:async (url, config) => {
            calls.push([url, config]);
            if (mode==='error') return {ok:false,json:async()=>({respuesta:'error'})};
            return {ok:true,json:async()=>({respuesta:'ok',data:mode==='empty'?[]:[
                {id:768,venta_id:633,fecha:'2026-09-11 15:29:01',nombre_usuario:'Usuario',seguimiento:'<script>alert(1)</script>'}
            ]})};
        }
    };
    vm.runInNewContext(code,context); ready();
    await context.window.verSeguimientosProyecto(633,'PV-2026-20035');
    assert.equal(calls[0][0],'/portal/Reportesmensuales/seguimientos');
    assert.equal(calls[0][1].body.get('venta_id'),'633');
    assert.equal(node('lbl_modal_seguimiento_proyecto').textContent,'PV-2026-20035');
    assert.equal(node('lbl_modal_seguimiento_count').textContent,'1 Seguimiento');
    assert.ok(node('tbl_seguimiento_venta_body').innerHTML.includes('&lt;script&gt;'));
    assert.equal(options.order[0][1],'desc');
    node('modalSeguimientosVenta').dataset.lista='interna_sin_cliente';
    mode='empty'; await context.window.verSeguimientosProyecto(634,'P2');
    assert.equal(calls[1][1].body.get('lista'),'interna_sin_cliente');
    assert.equal(active,false);
    assert.equal(node('lbl_modal_seguimiento_count').textContent,'0 Seguimientos');
    assert.ok(node('tbl_seguimiento_venta_body').innerHTML.includes('No hay seguimientos'));
    mode='error'; await context.window.verSeguimientosProyecto(635,'P3');
    assert.ok(node('tbl_seguimiento_venta_body').innerHTML.includes('No se pudieron cargar'));
    assert.equal(shown,3);
    node('modalSeguimientosVenta').events['hidden.bs.modal']();
    console.log('OK: historial del ID seleccionado, datos escapados, DataTables, vacio y error.');
}
probar().catch(error=>{console.error(error);process.exitCode=1;});
