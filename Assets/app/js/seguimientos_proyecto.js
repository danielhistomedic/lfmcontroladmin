/* Modal compartido por Dashboard Ventas y Reporte Ventas. */
(function () {
    let request, rows = [], page = 0, order = 'id', direction = 'desc';
    const size = 10;
    const collator = new Intl.Collator('es', {numeric:true,sensitivity:'base'});
    const element = id => document.getElementById(id);
    const headers = () => [...document.querySelectorAll('#table_seguimiento_venta [data-seguimiento-order]')];
    function controls(disabled) {
        element('seguimientos-buscar').disabled = disabled;
        headers().forEach(button=>{button.disabled=disabled;});
        element('seguimientos-anterior').disabled = true;
        element('seguimientos-siguiente').disabled = true;
    }
    function message(text) {
        const body=element('tbl_seguimiento_venta_body');body.replaceChildren();
        const tr=document.createElement('tr'),td=document.createElement('td');
        td.colSpan=4;td.className='text-center py-4';td.textContent=text;tr.appendChild(td);body.appendChild(tr);
    }
    const normalize = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
    function filteredRows() {
        const search=normalize(element('seguimientos-buscar').value.trim());
        return rows.filter(row=>Object.values(row).some(value=>normalize(value).includes(search)));
    }
    function dateKey(value) {
        // Ordenar fechas ISO y fechas formateadas día/mes/año por su valor cronológico.
        const date=String(value).trim(),match=date.match(/^(\d{2})\/(\d{2})\/(\d{4})(.*)$/);
        return match ? match[3]+'-'+match[2]+'-'+match[1]+match[4] : date;
    }
    function render() {
        const list=filteredRows();
        list.sort((a,b)=>{
            const left=order==='fecha'?dateKey(a[order]):a[order],right=order==='fecha'?dateKey(b[order]):b[order];
            return collator.compare(String(left),String(right))*(direction==='asc'?1:-1);
        });
        page=Math.min(page,Math.max(0,Math.ceil(list.length/size)-1));
        headers().forEach(button=>{
            const active=button.dataset.seguimientoOrder===order;
            button.textContent=button.dataset.label+(active?(direction==='asc'?' ↑':' ↓'):'');
            button.parentElement.setAttribute('aria-sort',active?(direction==='asc'?'ascending':'descending'):'none');
        });
        const body=element('tbl_seguimiento_venta_body');body.replaceChildren();
        list.slice(page*size,(page+1)*size).forEach(row=>{
            const tr=document.createElement('tr');
            ['id','fecha','usuario','detalle'].forEach(key=>{
                const td=document.createElement('td');td.textContent=String(row[key]);tr.appendChild(td);
            });body.appendChild(tr);
        });
        if(!list.length)message(rows.length?'No hay coincidencias para la búsqueda.':'No hay seguimientos registrados para este proyecto.');
        element('seguimientos-rango').textContent=list.length?(page*size+1)+'–'+Math.min((page+1)*size,list.length)+' de '+list.length+' registros':'0 registros';
        element('seguimientos-pagina').textContent='Página '+(page+1)+' de '+Math.max(1,Math.ceil(list.length/size));
        element('seguimientos-anterior').disabled=page===0;
        element('seguimientos-siguiente').disabled=(page+1)*size>=list.length;
    }
    window.verSeguimientosProyecto = async function (ventaId, proyectoId) {
        const modal=element('modalSeguimientosVenta');if(!modal)return;
        if(request)request.abort();
        const current=new AbortController();request=current;
        rows=[];page=0;order='id';direction='desc';element('seguimientos-buscar').value='';controls(true);
        element('lbl_modal_seguimiento_proyecto').textContent=proyectoId || 'N/A';
        element('lbl_modal_seguimiento_venta_id').textContent=ventaId;
        element('lbl_modal_seguimiento_count').textContent='Cargando…';
        element('seguimientos-rango').textContent='Cargando…';element('seguimientos-pagina').textContent='Página 1 de 1';
        message('Cargando seguimientos…');
        bootstrap.Modal.getOrCreateInstance(modal).show();
        const form=new FormData();form.append('venta_id',ventaId);
        if(modal.dataset.lista)form.append('lista',modal.dataset.lista);
        try {
            const response=await fetch(modal.dataset.url,{method:'POST',body:form,credentials:'same-origin',headers:{Accept:'application/json'},signal:current.signal});
            const payload=await response.json();
            if(!response.ok||payload.respuesta!=='ok'||!Array.isArray(payload.data))throw new Error('No se pudieron cargar los seguimientos.');
            if(request!==current)return;
            rows=payload.data.map(s=>({
                id:s.id || s.ID || 'N/A',
                fecha:s.fecha_formateada || s.fecha || s.fecha_registro || s.fchregistro || s.created_at || 'N/A',
                usuario:(s.nombre_usuario || '').trim() || s.usuario_nombre || s.usuario || s.vendedor || s.ccveusuario || 'N/A',
                detalle:s.seguimiento || s.comentario || s.observaciones || s.observacion || s.nota || s.descripcion || s.mensaje || 'Sin observaciones'
            }));
            element('lbl_modal_seguimiento_count').textContent=rows.length+' Seguimiento'+(rows.length===1?'':'s');
            controls(false);render();
        } catch(error) {
            if(error.name==='AbortError'||request!==current)return;
            element('lbl_modal_seguimiento_count').textContent='— Seguimientos';
            element('seguimientos-rango').textContent='0 registros';
            message('No se pudieron cargar los seguimientos. Cierre el modal e intente nuevamente.');
        } finally {if(request===current)request=null;}
    };
    document.addEventListener('DOMContentLoaded',()=>{
        const modal=element('modalSeguimientosVenta');if(!modal)return;
        element('seguimientos-buscar').addEventListener('input',()=>{page=0;render();});
        element('seguimientos-anterior').addEventListener('click',()=>{if(page>0){page--;render();}});
        element('seguimientos-siguiente').addEventListener('click',()=>{if((page+1)*size<filteredRows().length){page++;render();}});
        headers().forEach(button=>button.addEventListener('click',()=>{
            const selected=button.dataset.seguimientoOrder;
            direction=order===selected&&direction==='asc'?'desc':'asc';order=selected;page=0;render();
        }));
        modal.addEventListener('hidden.bs.modal',()=>{if(request)request.abort();request=null;});
    });
}());
