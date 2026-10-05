/* Reporte mensual: una carga global por cambio de filtros. */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filtros-ventas-mensuales');
    let submitting = false;
    form.addEventListener('submit', function (event) {
        if (submitting) { event.preventDefault(); return; }
        submitting = true;
        document.getElementById('ventas-cargando').hidden = false;
        form.querySelector('button').disabled = true;
    });
    // La plantilla inicializa Select2; change se enlaza mediante jQuery para sus eventos.
    jQuery(form).find('select').on('change', function () {
        if (this.id !== 'ventas-mes' && this.id !== 'ventas-anio' && !submitting) form.requestSubmit();
    });
    window.addEventListener('pageshow', function () {
        submitting = false;
        document.getElementById('ventas-cargando').hidden = true;
        form.querySelector('button').disabled = false;
    });
    const source = document.getElementById('ventas-mensuales-datos');
    if (!source) return;
    const modal = document.getElementById('modal-declinados-ventas');
    if (modal) {
        const periodLabel = document.getElementById('declinados-periodo');
        const globalPeriodLabel = periodLabel ? periodLabel.textContent : '';
        const status = document.getElementById('declinados-estado');
        const badge = document.getElementById('declinados-total');
        const retry = document.getElementById('declinados-reintentar');
        const tableElement = document.getElementById('table-declinados-ventas');
        const summaryPanel = document.getElementById('declinados-resumen');
        const detailPanel = document.getElementById('declinados-detalle');
        const detailTitle = document.getElementById('declinados-detalle-titulo');
        const sellerButtons = document.getElementById('declinados-vendedores');
        const allSellers = document.getElementById('declinados-todos');
        const kpiPanel = document.getElementById('declinados-kpis');
        const detailCount = document.getElementById('declinados-detalle-cantidad');
        const detailEmpty = document.getElementById('declinados-detalle-vacio');
        const detailTable = document.getElementById('declinados-detalle-tabla');
        const exportButton = document.getElementById('declinados-exportar');
        let selectedDeclinedSeller = null;
        let declinedSummary = null;
        let summaryRequest = null;
        let exportRequest = null;
        let declinedPage = 0, declinedOrder = 2, declinedDirection = 'desc', declinedTotal = 0;
        const declinedSearch = document.getElementById('declinados-buscar');
        const customPanel = document.getElementById('declinados-tabla-propia');
        const legacyPanel = document.getElementById('declinados-tabla-legado');
        const sortButtons = [...document.querySelectorAll('#declinados-tabla-propia [data-order]')];
        let table = null;
        let request = null;
        let filterTimer;
        let restoreList = false;
        let openingFollowup = false;
        let lista = 'declinados';
        const isStatusTable = () => ['cotizados_periodo','colocados_periodo','estatus_periodo','clasificacion_periodo'].includes(lista);
        const isLightTable = () => ['declinados','interna_sin_cliente'].includes(lista)||isStatusTable();
        const hasSellerColumn = () => lista==='interna_sin_cliente'||isStatusTable();
        const projectColumnCount = () => hasSellerColumn()?8:7;
        let statusRows = null;
        const projectPageSize = () => lista==='interna_sin_cliente'?5:10;
        let currentPageRows = [];
        const isDrill = () => ['estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo','estatus_periodo','cotizados_periodo','colocados_periodo'].includes(lista);
        const listTitle = () => isDrill() ? modal.dataset.desgloseTitulo : lista === 'interna_sin_cliente'
            ? 'Proyectos con cotización interna sin cotización a cliente' : 'Listado de Proyectos Declinados';
        const escape = value => value.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        const text = value => escape(String(value == null ? '' : value));
        const attribute = value => text(value).replace(/"/g,'&quot;').replace(/'/g,'&#39;');
        const cell = (value,className='') => '<span class="declinados-celda '+className+'" title="'+attribute(value)+'">'+text(value)+'</span>';
        function detailHeading(name,total) {
            detailTitle.textContent = 'Proyectos de: '+name;
            detailTitle.title = name;
            detailCount.textContent = total+' proyectos'+(lista==='declinados'?' declinados':'');
        }
        function declinedParams() {
            const params = new URLSearchParams({lista:'declinados',vendedor:modal.dataset.vendedor});
            modal.dataset.anio.split(',').forEach(value=>params.append('anio[]',value));
            modal.dataset.mes.split(',').forEach(value=>params.append('mes[]',value));
            return params;
        }
        function selectDeclinedSeller(seller) {
            if (!declinedSummary || !(lista==='declinados'||isStatusTable())) return;
            if (exportRequest) exportRequest.abort();
            selectedDeclinedSeller = seller;
            allSellers.setAttribute('aria-pressed',String(seller === null));
            [...sellerButtons.children].forEach(button=>button.setAttribute('aria-pressed',String(seller !== null && button.dataset.vendedor === String(seller.vendedor_id))));
            detailHeading(seller ? seller.nombre : 'TODOS',seller ? seller.proyectos : declinedSummary.total);
            detailPanel.hidden = false;
            detailEmpty.hidden = true; detailTable.hidden = false;
            initializeTable();
        }
        allSellers.addEventListener('click',()=>selectDeclinedSeller(null));
        document.getElementById('declinados-exportar-pagina').addEventListener('click',async()=>{
            if(!(lista==='interna_sin_cliente'||isStatusTable())||exportRequest||request)return;
            const current=new AbortController();exportRequest=current;
            const button=document.getElementById('declinados-exportar-pagina');button.disabled=true;
            try{await downloadDeclinedExcel(currentPageRows,current.signal,declinedPage*projectPageSize());}
            catch(error){status.textContent='No se pudo exportar: '+error.message;status.className='mb-2 text-danger';}
            finally{if(exportRequest===current)exportRequest=null;button.disabled=false;}
        });
        exportButton.addEventListener('click',()=>{
            if (lista !== 'declinados' || !declinedSummary || exportRequest) return;
            if (detailTable.hidden) selectDeclinedSeller(null);
            exportDeclined.call({processing:busy=>{exportButton.disabled=busy;}});
        });
        declinedSearch.addEventListener('input',()=>{
            clearTimeout(filterTimer);declinedPage=0;
            filterTimer=setTimeout(loadDeclinedTable,300);
        });
        document.getElementById('declinados-anterior').addEventListener('click',()=>{if(declinedPage>0){declinedPage--;loadDeclinedTable();}});
        document.getElementById('declinados-siguiente').addEventListener('click',()=>{if((declinedPage+1)*projectPageSize()<declinedTotal){declinedPage++;loadDeclinedTable();}});
        sortButtons.forEach(button=>button.addEventListener('click',()=>{
            const order=Number(button.dataset.order);
            declinedDirection=declinedOrder===order&&declinedDirection==='asc'?'desc':'asc';
            declinedOrder=order;declinedPage=0;loadDeclinedTable();
        }));
        async function loadDeclinedTable() {
            if(!isLightTable()||(lista==='declinados'&&!declinedSummary)||detailTable.hidden)return;
            if(isStatusTable()){renderStatusTable();return;}
            if(request)request.abort();
            const current=new AbortController();request=current;
            const body=document.getElementById('declinados-tabla-filas');body.replaceChildren();
            currentPageRows=[];document.getElementById('declinados-exportar-pagina').disabled=true;
            document.getElementById('declinados-anterior').disabled=true;document.getElementById('declinados-siguiente').disabled=true;
            customPanel.setAttribute('aria-busy','true');status.textContent='Cargando proyectos…';status.className='mb-2 text-muted';retry.hidden=true;
            sortButtons.forEach(button=>{
                button.parentElement.hidden=Number(button.dataset.order)===4&&!hasSellerColumn();
                const active=Number(button.dataset.order)===declinedOrder;
                button.textContent=button.dataset.label+(active?(declinedDirection==='asc'?' ↑':' ↓'):'');
                button.parentElement.setAttribute('aria-sort',active?(declinedDirection==='asc'?'ascending':'descending'):'none');
            });
            document.getElementById('declinados-seguimiento-cabecera').textContent='Seguimiento';
            document.getElementById('declinados-seguimiento-cabecera').hidden=false;
            const size=projectPageSize();
            const params=declinedParams();params.set('lista',lista);params.set('datatable','1');params.set('start',String(declinedPage*size));params.set('length',String(size));
            params.set('search',declinedSearch.value || '');params.set('order_column',String(declinedOrder));params.set('order_dir',declinedDirection);
            if(lista==='declinados'&&selectedDeclinedSeller)params.set('declinado_vendedor',selectedDeclinedSeller.vendedor_id);
            try{
                const response=await fetch(modal.dataset.url+'?'+params,{signal:current.signal,credentials:'same-origin',headers:{Accept:'application/json'}});
                const payload=await response.json();if(!response.ok||!payload.status)throw new Error(payload.message || 'No se pudo cargar el detalle.');
                if(request!==current)return;
                declinedTotal=payload.data.recordsFiltered;
                const last=Math.max(0,Math.ceil(declinedTotal/size)-1);
                if(declinedPage>last){declinedPage=last;return loadDeclinedTable();}
                if(lista==='declinados')detailHeading(selectedDeclinedSeller?selectedDeclinedSeller.nombre:'TODOS',payload.data.recordsTotal);
                else badge.textContent=payload.data.recordsTotal+' Proyectos';
                currentPageRows=payload.data.data;
                appendProjectRows(payload.data.data,declinedPage*size);
                if(!payload.data.data.length){const tr=document.createElement('tr'),td=document.createElement('td');td.colSpan=projectColumnCount();td.textContent='No hay proyectos para la búsqueda seleccionada.';tr.appendChild(td);body.appendChild(tr);}
                const start=declinedPage*size;
                document.getElementById('declinados-tabla-pagina').textContent=declinedTotal?(start+1)+'–'+(start+payload.data.data.length)+' de '+declinedTotal:'0 resultados';
                document.getElementById('declinados-anterior').disabled=declinedPage===0;document.getElementById('declinados-siguiente').disabled=start+size>=declinedTotal;
                status.textContent='';
                document.getElementById('declinados-exportar-pagina').disabled=!currentPageRows.length;
            }catch(error){if(error.name==='AbortError'||request!==current)return;
                document.getElementById('declinados-tabla-pagina').textContent='—';
                status.textContent=error instanceof SyntaxError?'No se pudo cargar el detalle. Intente nuevamente.':error.message;
                status.className='mb-2 text-danger';retry.hidden=false;
            }finally{if(request===current){request=null;customPanel.setAttribute('aria-busy','false');}}
        }
        function appendProjectRows(projectRows,start,grouped=false) {
            const body=document.getElementById('declinados-tabla-filas');let previous=null;
                projectRows.forEach((row,index)=>{
                    if(grouped){const key=String(row.vendedor_id??'');if(key!==previous){
                        previous=key;const heading=document.createElement('tr'),td=document.createElement('td');td.colSpan=projectColumnCount();td.className='declinados-grupo-vendedor';td.textContent=row.vendedor||'Sin vendedor';heading.appendChild(td);body.appendChild(heading);
                    }}
                    const tr=document.createElement('tr');const date=String(row.fecha || '').slice(0,10);
                    const internal=hasSellerColumn();
                    const values=[start+index+1,row.proyecto_id,/^\d{4}-\d{2}-\d{2}$/.test(date)?date.split('-').reverse().join('/'):'—',row.cliente,...(internal?[row.vendedor]:[]),row.clasificacion,row.titulo];
                    values.forEach((value,column)=>{
                        const td=document.createElement('td'),span=document.createElement('span');span.textContent=String(value ?? '');span.title=String(value ?? '');
                        span.className='declinados-celda'+(column===1?' text-danger':'');
                        if(column===(internal?5:4)){const name=String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toUpperCase();
                            span.className='declinados-clasificacion declinados-clasificacion-'+(name.includes('VALVULAS')?'valvulas':name.includes('BOMBAS')?'bombas':name.includes('SELLOS')?'sellos':name.includes('DIVERSOS')?'diversos':'neutral');}
                        td.appendChild(span);tr.appendChild(td);
                    });
                    const actionCell=document.createElement('td'),button=followupButton(row);
                    actionCell.className='declinados-col-seguimiento';
                    actionCell.appendChild(button);tr.appendChild(actionCell);
                    body.appendChild(tr);
                });
        }
        async function loadStatusSellers() {
            if(summaryRequest)summaryRequest.abort();
            const current=new AbortController();summaryRequest=current;
            statusRows=null;declinedSummary=null;selectedDeclinedSeller=null;
            declinedPage=0;declinedOrder=2;declinedDirection='desc';declinedSearch.value='';
            summaryPanel.hidden=false;kpiPanel.hidden=true;exportButton.hidden=true;detailPanel.hidden=false;
            document.getElementById('declinados-detalle-cabecera').hidden=false;
            detailTable.hidden=true;detailEmpty.hidden=false;detailTitle.textContent='Proyectos del vendedor';detailCount.textContent='';
            sellerButtons.replaceChildren();allSellers.disabled=true;retry.hidden=true;
            status.textContent='Cargando proyectos y vendedores…';status.className='mb-2 text-muted';
            const params=new URLSearchParams({lista,datatable:'1',vendedor:modal.dataset.desgloseVendedor??modal.dataset.vendedor,length:'100',order_column:'2',order_dir:'desc'});
            const years=modal.dataset.desgloseAnio||modal.dataset.anio,months=modal.dataset.desgloseMes||modal.dataset.mes;
            years.split(',').forEach(value=>params.append('anio[]',value));months.split(',').forEach(value=>params.append('mes[]',value));
            if(lista==='estatus_periodo'){params.set('estatus_id',modal.dataset.estatusId);params.set('segmento',modal.dataset.segmento);}
            else if(lista==='clasificacion_periodo'){params.set('clasificacion_id',modal.dataset.clasificacionId);params.set('segmento',modal.dataset.segmento);}
            else {
                modal.dataset.anio.split(',').forEach(value=>params.append('periodo_anio[]',value));
                modal.dataset.mes.split(',').forEach(value=>params.append('periodo_mes[]',value));
            }
            const statusIds=lista==='estatus_periodo'&&modal.dataset.estatusAgrupados
                ? JSON.parse(modal.dataset.estatusAgrupados) : [modal.dataset.estatusId];
            const projects=new Map();
            try {
                for(const statusId of statusIds){
                if(lista==='estatus_periodo')params.set('estatus_id',statusId);
                let expected=null;
                for(let start=0;expected===null||start<expected;start+=100){
                    params.set('start',String(start));
                    const response=await fetch(modal.dataset.url+'?'+params,{signal:current.signal,credentials:'same-origin',headers:{Accept:'application/json'}});
                    const payload=await response.json();
                    if(!response.ok||!payload.status)throw new Error(payload.message||'No se pudo cargar el listado.');
                    if(summaryRequest!==current||current.signal.aborted)return;
                    if(expected===null)expected=payload.data.recordsTotal;
                    if(expected!==payload.data.recordsTotal||(start<expected&&!payload.data.data.length))throw new Error('La lista cambió durante la carga. Intente nuevamente.');
                    payload.data.data.forEach(row=>projects.set(String(row.id),row));
                }
                }
                statusRows=[...projects.values()];
                if(modal.dataset.desgloseVendedor!==undefined)statusRows=statusRows.filter(row=>String(row.vendedor_id??'')===modal.dataset.desgloseVendedor);
                const sellers=new Map();
                statusRows.forEach(row=>{
                    const key=String(row.vendedor_id??'');
                    if(!sellers.has(key))sellers.set(key,{vendedor_id:key,nombre:row.vendedor||'Sin vendedor',proyectos:0});
                    sellers.get(key).proyectos++;
                });
                const distribution=[...sellers.values()].sort((a,b)=>b.proyectos-a.proyectos||a.nombre.localeCompare(b.nombre,'es')||a.vendedor_id.localeCompare(b.vendedor_id));
                distribution.forEach(seller=>{seller.porcentaje=statusRows.length?seller.proyectos/statusRows.length*100:0;});
                declinedSummary={total:statusRows.length,vendedores:distribution};
                badge.textContent=declinedSummary.total+' Proyectos';renderSellerButtons(distribution);allSellers.disabled=false;status.textContent='';
                selectDeclinedSeller(modal.dataset.desgloseVendedor!==undefined
                    ? distribution.find(seller=>seller.vendedor_id===modal.dataset.desgloseVendedor)||null : null);
            }catch(error){
                if(error.name==='AbortError'||summaryRequest!==current)return;
                statusRows=null;status.textContent=error instanceof SyntaxError?'No se pudo cargar el listado. Intente nuevamente.':error.message;
                status.className='mb-2 text-danger';retry.hidden=false;badge.textContent='— Proyectos';
            }finally{if(summaryRequest===current)summaryRequest=null;}
        }
        function renderStatusTable() {
            if(statusRows===null)return;
            const collator=new Intl.Collator('es',{numeric:true,sensitivity:'base'});
            const normalize=value=>String(value??'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
            const search=normalize(declinedSearch.value.trim());
            const field={1:'proyecto_id',2:'fecha',3:'cliente',4:'vendedor',5:'clasificacion',6:'titulo'}[declinedOrder]||'fecha';
            let rows=statusRows.filter(row=>!selectedDeclinedSeller||String(row.vendedor_id??'')===selectedDeclinedSeller.vendedor_id);
            const sellerTotal=rows.length;
            rows=rows.filter(row=>[row.proyecto_id,row.fecha,String(row.fecha||'').slice(0,10).split('-').reverse().join('/'),row.cliente,row.vendedor,row.clasificacion,row.titulo,row.activo].some(value=>normalize(value).includes(search)));
            rows.sort((a,b)=>{
                const group=collator.compare(a.vendedor||'Sin vendedor',b.vendedor||'Sin vendedor')||collator.compare(String(a.vendedor_id??''),String(b.vendedor_id??''));
                if(!selectedDeclinedSeller&&group)return group*(declinedOrder===4&&declinedDirection==='desc'?-1:1);
                return collator.compare(String(a[field]??''),String(b[field]??''))*(declinedDirection==='asc'?1:-1)||Number(b.id)-Number(a.id);
            });
            declinedTotal=rows.length;const size=projectPageSize();declinedPage=Math.min(declinedPage,Math.max(0,Math.ceil(rows.length/size)-1));
            const start=declinedPage*size;currentPageRows=rows.slice(start,start+size);
            sortButtons.forEach(button=>{
                button.parentElement.hidden=false;const active=Number(button.dataset.order)===declinedOrder;
                button.textContent=button.dataset.label+(active?(declinedDirection==='asc'?' ↑':' ↓'):'');
                button.parentElement.setAttribute('aria-sort',active?(declinedDirection==='asc'?'ascending':'descending'):'none');
            });
            document.getElementById('declinados-seguimiento-cabecera').textContent='Seguimiento';
            document.getElementById('declinados-seguimiento-cabecera').hidden=false;
            const body=document.getElementById('declinados-tabla-filas');body.replaceChildren();
            appendProjectRows(currentPageRows,start,selectedDeclinedSeller===null);
            if(!currentPageRows.length){const tr=document.createElement('tr'),td=document.createElement('td');td.colSpan=projectColumnCount();td.textContent='No hay proyectos para la búsqueda seleccionada.';tr.appendChild(td);body.appendChild(tr);}
            detailHeading(selectedDeclinedSeller?selectedDeclinedSeller.nombre:'TODOS',sellerTotal);
            document.getElementById('declinados-tabla-pagina').textContent=rows.length?(start+1)+'–'+(start+currentPageRows.length)+' de '+rows.length:'0 resultados';
            document.getElementById('declinados-anterior').disabled=declinedPage===0;
            document.getElementById('declinados-siguiente').disabled=start+size>=rows.length;
            document.getElementById('declinados-exportar-pagina').disabled=!currentPageRows.length;
            status.textContent='';retry.hidden=true;customPanel.setAttribute('aria-busy','false');
        }
        async function loadDeclinedSummary() {
            if (summaryRequest) summaryRequest.abort();
            const current = new AbortController(); summaryRequest = current;
            declinedSummary = null; selectedDeclinedSeller = null;
            declinedPage=0;declinedOrder=2;declinedDirection='desc';declinedSearch.value='';
            summaryPanel.hidden = false; kpiPanel.hidden = false; detailPanel.hidden = false;
            document.getElementById('declinados-detalle-cabecera').hidden = false;
            detailEmpty.hidden = false; detailTable.hidden = true;
            detailTitle.textContent = 'Proyectos del vendedor'; detailCount.textContent = '';
            exportButton.hidden = false; exportButton.disabled = true;
            sellerButtons.replaceChildren(); allSellers.disabled = true;
            allSellers.setAttribute('aria-pressed','false');
            ['total','vendedores','lider'].forEach(key=>document.getElementById('declinados-resumen-'+key).textContent='—');
            document.getElementById('declinados-resumen-lider-detalle').textContent='';
            status.textContent = 'Cargando resumen por vendedor…'; status.className = 'mb-2 text-muted'; retry.hidden = true;
            const params = declinedParams(); params.set('resumen','1');
            try {
                const response = await fetch(modal.dataset.url+'?'+params,{signal:current.signal,credentials:'same-origin',headers:{Accept:'application/json'}});
                const payload = await response.json();
                if (!response.ok || !payload.status) throw new Error(payload.message || 'No se pudo cargar el resumen.');
                if (summaryRequest !== current) return;
                declinedSummary = payload.data;
                const sellers = declinedSummary.vendedores;
                badge.textContent = declinedSummary.total+' Proyectos';
                document.getElementById('declinados-resumen-total').textContent = String(declinedSummary.total);
                document.getElementById('declinados-resumen-vendedores').textContent = String(sellers.length);
                const percent = new Intl.NumberFormat('es-MX',{maximumFractionDigits:2});
                document.getElementById('declinados-resumen-lider').textContent = sellers.length ? sellers[0].nombre : 'Sin proyectos';
                document.getElementById('declinados-resumen-lider-detalle').textContent = sellers.length ? sellers[0].proyectos+' proyectos ('+percent.format(sellers[0].porcentaje)+' %)' : '';
                renderSellerButtons(sellers);
                allSellers.disabled = false;
                exportButton.disabled = false;
                status.textContent = sellers.length ? '' : 'No hay proyectos declinados para el período seleccionado.';
            } catch(error) {
                if (error.name === 'AbortError' || summaryRequest !== current) return;
                badge.textContent = '— Proyectos'; status.textContent = error instanceof SyntaxError ? 'No se pudo cargar el resumen. Intente nuevamente.' : error.message;
                status.className = 'mb-2 text-danger'; retry.hidden = false;
            } finally { if (summaryRequest === current) summaryRequest = null; }
        }
        function renderSellerButtons(sellers) {
            sellerButtons.replaceChildren();
            const percent=new Intl.NumberFormat('es-MX',{maximumFractionDigits:2});
                sellers.forEach(seller=>{
                    const button = document.createElement('button'); button.type = 'button';
                    button.className = 'declinados-vendedor'; button.dataset.vendedor = seller.vendedor_id;
                    button.setAttribute('aria-pressed','false');
                    const name = document.createElement('span'); name.textContent = seller.nombre; name.title = seller.nombre;
                    const quantity = document.createElement('span'); quantity.textContent = String(seller.proyectos); quantity.className = 'declinados-vendedor-cantidad';
                    const percentage = document.createElement('span'); percentage.textContent = percent.format(seller.porcentaje)+' %'; percentage.className = 'declinados-vendedor-porcentaje';
                    const track = document.createElement('span'); track.className = 'declinados-vendedor-barra'; track.setAttribute('aria-hidden','true');
                    const fill = document.createElement('span'); fill.style.width = seller.porcentaje+'%';
                    track.appendChild(fill); button.appendChild(name); button.appendChild(quantity); button.appendChild(track); button.appendChild(percentage);
                    button.addEventListener('click',()=>selectDeclinedSeller(seller)); sellerButtons.appendChild(button);
                });
        }
        function initialize() {
            if(isStatusTable()){
                if(restoreList){restoreList=false;return;}
                return loadStatusSellers();
            }
            if (lista === 'declinados') {
                if (restoreList) { restoreList = false; if (table) table.columns.adjust(); return; }
                return loadDeclinedSummary();
            }
            summaryPanel.hidden = true; kpiPanel.hidden = true; exportButton.hidden = true;
            document.getElementById('declinados-detalle-cabecera').hidden = true;
            detailPanel.hidden = false; detailTable.hidden = false; detailEmpty.hidden = true;
            detailTitle.textContent = ''; detailCount.textContent = '';
            if(lista==='interna_sin_cliente'){
                if(restoreList){restoreList=false;return;}
                declinedPage=0;declinedOrder=2;declinedDirection='desc';declinedSearch.value='';
            }
            return initializeTable();
        }
        async function exportDeclined(event,dt,node,config) {
            if (lista !== 'declinados') return jQuery.fn.dataTable.ext.buttons.excelHtml5.action.call(this,event,dt,node,config);
            if (exportRequest) return;
            const current = new AbortController(); exportRequest = current;
            exportButton.disabled = true;
            this.processing(true);
            const params = declinedParams(); params.set('datatable','1'); params.set('length','100'); params.set('order_column','2'); params.set('order_dir','desc');
            if (selectedDeclinedSeller) params.set('declinado_vendedor',selectedDeclinedSeller.vendedor_id);
            const rows = new Map(); let expected = null;
            try {
                for (let start=0; expected === null || start<expected; start+=100) {
                    params.set('start',String(start));
                    const response = await fetch(modal.dataset.url+'?'+params,{signal:current.signal,credentials:'same-origin',headers:{Accept:'application/json'}});
                    const payload = await response.json();
                    if (!response.ok || !payload.status) throw new Error(payload.message || 'No se pudo exportar.');
                    if (current.signal.aborted) return;
                    if (expected === null) expected = payload.data.recordsTotal;
                    if (expected !== payload.data.recordsTotal || (start<expected && !payload.data.data.length)) throw new Error('La lista cambió durante la exportación. Intente nuevamente.');
                    payload.data.data.forEach(row=>rows.set(String(row.id),row));
                }
                if (rows.size !== expected) throw new Error('La lista cambió durante la exportación. Intente nuevamente.');
                await downloadDeclinedExcel([...rows.values()],current.signal);
            } catch(error) {
                if (error.name === 'AbortError') return;
                status.textContent = 'No se pudo exportar: '+(error instanceof SyntaxError ? 'respuesta inválida.' : error.message);
                status.className = 'mb-2 text-danger';
            } finally { if (exportRequest === current) { exportRequest = null; exportButton.disabled = false; this.processing(false); } }
        }
        async function downloadDeclinedExcel(rows,signal,offset=0) {
            const sourceList=lista,internal=hasSellerColumn(),quoted=isStatusTable();
            if(typeof JSZip==='undefined')throw new Error('La biblioteca de Excel no está disponible.');
            const xml=value=>String(value ?? '').replace(/[\x00-\x08\x0B\x0C\x0E-\x1F]/g,'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&apos;');
            const values=[['No.','ID Proyecto','Fecha','Cliente',...(internal?['Vendedor']:[]),'Clasificación','Título'],...rows.map((row,index)=>{
                const date=String(row.fecha || '').slice(0,10);
                return [offset+index+1,row.proyecto_id,/^\d{4}-\d{2}-\d{2}$/.test(date)?date.split('-').reverse().join('/'):'',row.cliente,...(internal?[row.vendedor]:[]),row.clasificacion,row.titulo];
            })];
            const sheet=values.map((row,index)=>'<row r="'+(index+1)+'">'+row.map((value,column)=>'<c r="'+String.fromCharCode(65+column)+(index+1)+'" t="inlineStr"><is><t xml:space="preserve">'+xml(value)+'</t></is></c>').join('')+'</row>').join('');
            const zip=new JSZip();const declaration='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
            zip.file('[Content_Types].xml',declaration+'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
            zip.file('_rels/.rels',declaration+'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            zip.file('xl/workbook.xml',declaration+'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Proyectos" sheetId="1" r:id="rId1"/></sheets></workbook>');
            zip.file('xl/_rels/workbook.xml.rels',declaration+'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
            zip.file('xl/worksheets/sheet1.xml',declaration+'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols><col min="1" max="1" width="6" customWidth="1"/><col min="2" max="3" width="19" customWidth="1"/><col min="4" max="5" width="30" customWidth="1"/><col min="6" max="6" width="60" customWidth="1"/></cols><sheetData>'+sheet+'</sheetData><autoFilter ref="A1:'+String.fromCharCode(64+values[0].length)+values.length+'"/></worksheet>');
            const blob=await zip.generateAsync({type:'blob',mimeType:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',compression:'DEFLATE'});
            if(lista!==sourceList||signal.aborted)return;
            const url=URL.createObjectURL(blob),link=document.createElement('a');link.href=url;link.download=quoted?'Proyectos_por_estatus.xlsx':internal?'Proyectos_cotizacion_interna.xlsx':'Proyectos_declinados.xlsx';link.click();
            setTimeout(()=>URL.revokeObjectURL(url),1000);
        }
        function detailColumns() {
            [4,7].forEach(index=>table.column(index).visible(lista !== 'declinados'));
            table.column(8).visible(lista === 'interna_sin_cliente');
        }
        function initializeTable() {
            customPanel.hidden=!isLightTable();legacyPanel.hidden=isLightTable();
            document.getElementById('declinados-exportar-pagina').hidden=!(lista==='interna_sin_cliente'||isStatusTable());
            if(isLightTable())return loadDeclinedTable();
            if (table) { detailColumns(); table.columns.adjust(); if (!restoreList) table.ajax.reload(null, true); restoreList = false; return; }
            const filterRow = document.createElement('tr');
            filterRow.className = 'filters';
            Array.from(tableElement.tHead.rows[0].cells).forEach((header, index) => {
                const th = document.createElement('th');
                th.className = 'text-center p-1';
                if (index > 0 && index < 8) {
                    const input = document.createElement('input');
                    input.type = 'search';
                    input.className = 'form-control form-control-sm';
                    input.placeholder = 'Filtrar ' + header.textContent.trim();
                    input.setAttribute('aria-label', input.placeholder);
                    input.dataset.column = String(index);
                    th.appendChild(input);
                }
                filterRow.appendChild(th);
            });
            tableElement.tHead.appendChild(filterRow);
            table = jQuery(tableElement).DataTable({
                serverSide: true, processing: true, searchDelay: 400,
                orderCellsTop: true, scrollX: '100%', select: true, order: [],
                iDisplayLength: 10, lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                dom: '<"declinados-toolbar d-flex flex-wrap align-items-center gap-3 mb-2"<"declinados-length"l><"declinados-buttons"B><"declinados-search ms-auto"f>>rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2"ip>',
                buttons: [
                    { extend: 'excelHtml5', text: () => lista === 'declinados' ? 'Excel (detalle completo)' : 'Excel (página actual)', autoFilter: true, action:exportDeclined,
                        sheetName: 'Proyectos', title: listTitle,
                        exportOptions: { columns: ':visible:not(.ventas-col-seguimientos)' } },
                    { extend: 'colvis', columns: ':not(.ventas-col-seguimientos)', text: '<i class="fa-solid fa-table-columns me-1"></i> Columnas' }
                ],
                language: typeof idioma_espanol !== 'undefined' ? idioma_espanol : {
                    processing: 'Cargando…', emptyTable: 'Sin proyectos para esta lista', zeroRecords: 'No hay coincidencias',
                    search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ registros', info: 'Registros del _START_ al _END_ de un total de _TOTAL_ registros',
                    infoEmpty: 'Sin registros', infoFiltered: '(filtrado de _MAX_ registros)',
                    paginate: { previous: 'Anterior', next: 'Siguiente' }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, render: (value, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'proyecto_id', render: (v, type) => type === 'display' ? (lista === 'declinados' ? cell(v,'text-danger') : '<span class="text-danger">' + text(v) + '</span>') : v },
                    { data: 'fecha', render: (v, type) => {
                        if (type !== 'display') return v;
                        const date = String(v || '').slice(0, 10);
                        return /^\d{4}-\d{2}-\d{2}$/.test(date) ? date.split('-').reverse().join('/') : '—';
                    } },
                    { data: 'cliente', render: (v, type) => type === 'display' ? (lista === 'declinados' ? cell(v) : '<span class="text-primary">' + text(v) + '</span>') : v },
                    { data: 'vendedor', render: (v, type) => type === 'display' ? text(v) : v },
                    { data: 'clasificacion', render: (v, type) => {
                        if (type !== 'display') return v;
                        if (lista !== 'declinados') return '<span class="badge border text-dark bg-light">'+text(v)+'</span>';
                        const name=String(v || '').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toUpperCase();
                        const color=name.includes('VALVULAS')?'valvulas':name.includes('BOMBAS')?'bombas':name.includes('SELLOS')?'sellos':name.includes('DIVERSOS')?'diversos':'neutral';
                        return '<span class="declinados-clasificacion declinados-clasificacion-'+color+'" title="'+attribute(v)+'">'+text(v)+'</span>';
                    } },
                    { data: 'titulo', render: (v, type) => type === 'display' ? (lista === 'declinados' ? cell(v) : text(v)) : v },
                    { data: 'activo', render: (v, type) => {
                        if (type !== 'display') return v;
                        const color = v === 'CERRADO' ? 'danger' : 'primary';
                        return '<button type="button" class="btn btn-' + color + ' btn-sm ventas-ver-seguimientos" title="Ver seguimientos del proyecto" aria-label="Ver seguimientos del proyecto">' +
                            text(v || 'ACTIVO') + ' <i class="fa-solid fa-list-check ms-1" aria-hidden="true"></i></button>';
                    } },
                    { data: null, orderable: false, searchable: false, visible: lista === 'interna_sin_cliente',
                        className: 'text-center ventas-col-seguimientos', render: (v, type) => type === 'display'
                            ? '<button type="button" class="btn btn-outline-primary btn-sm ventas-ver-seguimientos"><i class="fa-solid fa-list-check me-1" aria-hidden="true"></i> Ver seguimientos</button>' : '' }
                ],
                columnDefs: [{ className: 'text-center', targets: [0, 1, 2, 7] }, { className: 'text-start', targets: [3, 4, 5, 6] }],
                drawCallback: function () {
                    if (!isDrill()) return;
                    const api = this.api();
                    const nodes = api.rows({ page: 'current' }).nodes();
                    let previous = '';
                    api.rows({ page: 'current' }).data().each((row, index) => {
                        const key = JSON.stringify([row.vendedor_id]);
                        if (key === previous) return;
                        previous = key;
                        const heading = document.createElement('tr');
                        const cell = document.createElement('td');
                        cell.colSpan = api.columns(':visible').count();
                        cell.className = 'bg-light fw-bold text-primary text-wrap';
                        cell.textContent = row.vendedor;
                        heading.appendChild(cell);
                        nodes[index].parentNode.insertBefore(heading, nodes[index]);
                    });
                },
                ajax: async function (data, callback) {
                    if (request) request.abort();
                    const current = new AbortController();
                    request = current;
                    status.textContent = 'Cargando proyectos…';
                    status.className = 'mb-2 text-muted';
                    retry.hidden = true;
                    const order = data.order[0] || { column: 2, dir: 'desc' };
                    const params = new URLSearchParams({ datatable: '1',
                        vendedor: modal.dataset.vendedor, draw: String(data.draw), start: String(data.start), length: String(data.length),
                        search: data.search.value, order_column: String(order.column), order_dir: order.dir });
                    params.set('lista', lista);
                    if (lista === 'declinados' && selectedDeclinedSeller) params.set('declinado_vendedor',selectedDeclinedSeller.vendedor_id);
                    const years = isDrill() && modal.dataset.desgloseAnio ? modal.dataset.desgloseAnio : modal.dataset.anio;
                    const months = isDrill() && modal.dataset.desgloseMes ? modal.dataset.desgloseMes : modal.dataset.mes;
                    years.split(',').forEach(year => params.append('anio[]', year));
                    months.split(',').forEach(month => params.append('mes[]', month));
                    if (isDrill()) {
                        if (!['estatus_periodo','cotizados_periodo','colocados_periodo'].includes(lista)) params.set('clasificacion_id', modal.dataset.clasificacionId);
                        if (['cotizados_periodo','colocados_periodo'].includes(lista)) {
                            modal.dataset.anio.split(',').forEach(year => params.append('periodo_anio[]',year));
                            modal.dataset.mes.split(',').forEach(month => params.append('periodo_mes[]',month));
                        }
                        if (lista === 'vendedor_clasificacion') params.set('vendedor_id', modal.dataset.desgloseVendedor);
                        else if (lista === 'estatus_clasificacion' || lista === 'estatus_periodo') params.set('estatus_id', modal.dataset.estatusId);
                        params.set('segmento', modal.dataset.segmento);
                    }
                    data.columns.forEach((column, index) => { if (index > 0 && index < 8) params.set('f' + index, column.search.value); });
                    try {
                        const response = await fetch(modal.dataset.url + '?' + params, {
                            signal: current.signal, headers: { Accept: 'application/json' }, credentials: 'same-origin'
                        });
                        const payload = await response.json();
                        if (!response.ok || !payload.status) throw new Error(payload.message || 'No se pudo cargar la lista.');
                        if (request !== current) return;
                        badge.textContent = (lista === 'declinados' && declinedSummary ? declinedSummary.total : payload.data.recordsTotal) + ' Proyectos';
                        if (lista === 'declinados') detailHeading(selectedDeclinedSeller ? selectedDeclinedSeller.nombre : 'TODOS',payload.data.recordsTotal);
                        status.textContent = '';
                        callback(payload.data);
                    } catch (error) {
                        if (error.name === 'AbortError' || request !== current) return;
                        badge.textContent = lista === 'declinados' && declinedSummary ? declinedSummary.total+' Proyectos' : '— Proyectos';
                        status.textContent = error instanceof SyntaxError ? 'No se pudo cargar la lista. Intente nuevamente.' : error.message;
                        status.className = 'mb-2 text-danger';
                        retry.hidden = false;
                        callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                    } finally { if (request === current) request = null; }
                }
            });
            detailColumns();
            // Delegación sobre el contenedor: incluye la cabecera clonada por scrollX.
            jQuery(table.table().container()).on('input', 'thead input', function () {
                const index = Number(this.dataset.column);
                const value = this.value;
                clearTimeout(filterTimer);
                filterTimer = setTimeout(() => table.column(index).search(value).draw(), 350);
            });
        }
        tableElement.addEventListener('click', event => {
            const button = event.target.closest('.ventas-ver-seguimientos');
            if (!button || !table || openingFollowup) return;
            const row = table.row(button.closest('tr')).data();
            openProjectFollowup(row,button,event);
        });
        function followupButton(row) {
            const button=document.createElement('button');button.type='button';
            button.className='btn btn-'+(row.activo==='CERRADO'?'danger':'primary')+' btn-sm ventas-ver-seguimientos';
            button.title='Ver seguimiento del proyecto';button.setAttribute('aria-label','Ver seguimiento del proyecto '+row.proyecto_id);
            const icon=document.createElement('i');icon.className='fa-solid fa-list-check';icon.setAttribute('aria-hidden','true');button.appendChild(icon);
            const label=document.createElement('span');label.textContent='Ver seguimiento';button.appendChild(label);
            button.addEventListener('click',event=>openProjectFollowup(row,button,event));
            return button;
        }
        function openProjectFollowup(row,button,event) {
            if (!row || !row.id || openingFollowup) return;
            event.stopPropagation();
            openingFollowup = true;
            const followup = document.getElementById('modalSeguimientosVenta');
            followup.dataset.lista = lista;
            restoreList = true;
            followup.addEventListener('hidden.bs.modal', () => {
                openingFollowup = false;
                modal.addEventListener('shown.bs.modal', () => button.focus(), { once: true });
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }, { once: true });
            modal.addEventListener('hidden.bs.modal', () => window.verSeguimientosProyecto(row.id, row.proyecto_id), { once: true });
            bootstrap.Modal.getOrCreateInstance(modal).hide();
        }
        modal.addEventListener('show.bs.modal', event => {
            if (!event.relatedTarget && modal.dataset.desglose !== '1') return;
            const next = event.relatedTarget ? (event.relatedTarget.dataset.lista || 'declinados') : (modal.dataset.desgloseLista || 'estatus_clasificacion');
            if (event.relatedTarget) { modal.dataset.desglose = ''; delete modal.dataset.desgloseVendedor; }
            if ((next !== lista || ['estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo','estatus_periodo'].includes(next)) && table) {
                table.search('');
                table.columns().search('');
                jQuery(table.table().container()).find('thead input').val('');
                table.order([]);
            }
            if(next!==lista){if(request)request.abort();request=null;clearTimeout(filterTimer);}
            lista = next;
            modal.dataset.lista = lista;
            modal.dataset.tablaEstatus=isStatusTable()?'1':'';
            if (summaryRequest) summaryRequest.abort(); summaryRequest = null;
            if (exportRequest) exportRequest.abort();
            if (periodLabel) {
                const year = Number(modal.dataset.desgloseAnio);
                const month = Number(modal.dataset.desgloseMes);
                periodLabel.textContent = isDrill() && year && month
                    ? '01/' + String(month).padStart(2, '0') + '/' + year + ' al ' +
                        new Date(year, month, 0).getDate() + '/' + String(month).padStart(2, '0') + '/' + year
                    : globalPeriodLabel;
            }
            document.getElementById('modal-declinados-titulo').textContent = listTitle();
            if (table) table.column(8).visible(lista === 'interna_sin_cliente');
        });
        modal.addEventListener('shown.bs.modal', initialize);
        modal.addEventListener('hidden.bs.modal', () => {
            if (request) request.abort(); request = null; clearTimeout(filterTimer);
            if (summaryRequest) summaryRequest.abort(); summaryRequest = null;
            if (exportRequest) exportRequest.abort();
        });
        retry.addEventListener('click', () => { if(isLightTable()){if(isStatusTable()&&statusRows===null)loadStatusSellers();else if(lista==='declinados'&&!declinedSummary)loadDeclinedSummary();else loadDeclinedTable();}else if(table)table.ajax.reload(null,false); });
        document.querySelectorAll('#ventas-mensuales .ventas-abrir-declinados').forEach(card => {
            card.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); card.click(); }
            });
        });
    }
    const data = JSON.parse(source.textContent);
    const charts = [];
    const amount = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 });
    const colors = ['#2385bd', '#27a58c', '#e5a543', '#8c6bb1', '#cf6478', '#428582', '#718bbd', '#ad7e56'];
    const projectCounts = [...(data.proyectos_por_vendedor || [])].sort((a, b) => b.proyectos - a.proyectos);
    const statusCounts = data.clasificaciones_por_vendedor || [];
    const declinedBySeller = new Map();
    statusCounts.forEach(row=>{
        const key=String(row.vendedor_id??'');
        declinedBySeller.set(key,(declinedBySeller.get(key)||0)+Number(row.declinados));
    });
    const selector = document.getElementById('ventas-vendedor-desglose');
    const statusPanel = document.getElementById('ventas-estatus-panel');
    const cascadeCharts = [];
    let selectedIndex = -1;
    let statusChart = null;
    let projectStatusChart = null;
    let quotedStatusKey = null;
    const cascadeText = typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? '#edf2f7' : '#243447';
    function cascade(id) {
        const element = document.getElementById(id);
        element.style.height = '330px';
        const instance = echarts.init(element, typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? 'dark' : null);
        const entry = { element, instance, count: 0 };
        cascadeCharts.push(entry);
        charts.push(instance);
        return entry;
    }
    function fitCascade(entry) {
        const available = entry.element.parentElement ? entry.element.parentElement.clientWidth : entry.element.clientWidth;
        const width = entry.donut ? (available || 320) : Math.max(available || 320, entry.count * (entry.slotWidth || 125) + 90);
        entry.element.style.width = width + 'px';
        entry.instance.resize({ width, height: entry.height || 330 });
    }
    function cascadeOption(rows, selected = -1) {
        return {
            backgroundColor: 'transparent',
            tooltip: { trigger: 'axis', renderMode: 'richText', valueFormatter: value => amount.format(value) + ' proyectos' },
            textStyle: { fontSize: 11 },
            grid: { left: 45, right: 20, top: 32, bottom: 95 },
            xAxis: { type: 'category', data: rows.map(row => row.nombre), axisLabel: {
                interval: 0, color: cascadeText, fontSize: 11, width: 110, overflow: 'break', lineHeight: 14
            } },
            yAxis: { type: 'value', name: 'Proyectos', minInterval: 1,
                nameTextStyle: { color: cascadeText }, axisLabel: { color: cascadeText } },
            series: [{ name: 'Proyectos', type: 'bar', barMaxWidth: 65,
                label: { show: true, position: 'top', fontSize: 11, color: cascadeText },
                data: rows.map((row, index) => ({ value: row.proyectos, itemStyle: {
                    color: index === selected ? '#d48825' : '#2385bd',
                    borderColor: index === selected ? '#80510f' : '#2385bd', borderWidth: index === selected ? 2 : 0
                } }))
            }], aria: { enabled: true }
        };
    }
    const sellerChart = cascade('ventas-cantidades-vendedores');
    sellerChart.count = projectCounts.length;
    sellerChart.height = 260;
    sellerChart.slotWidth = 100;
    sellerChart.element.style.height = '260px';
    function sellerOption(selected = -1) {
        const option = cascadeOption(projectCounts, selected);
        option.grid = {left:38,right:12,top:44,bottom:76};
        option.legend = {data:['Proyectos','Declinados'],top:2,right:12,itemWidth:12,itemHeight:9,
            selectedMode:false,textStyle:{color:cascadeText,fontSize:10}};
        option.tooltip = {trigger:'axis',renderMode:'richText',formatter:items=>{
            const row=items[0]?.data;
            return row ? projectCounts[items[0].dataIndex].nombre+'\nTotal: '+row.total+
                '\nNo declinados: '+(row.total-row.declined)+'\nDeclinados: '+row.declined : '';
        }};
        option.xAxis.axisLabel = {...option.xAxis.axisLabel,fontSize:9,width:92,lineHeight:12,margin:8};
        option.yAxis.axisLabel.fontSize = 10;
        option.yAxis.nameTextStyle.fontSize = 10;
        option.yAxis.splitLine = {lineStyle:{color: typeof theme_chart !== 'undefined' && theme_chart === 'dark' ? '#354a64' : '#e7edf4'}};
        option.series = ['Proyectos','Declinados'].map((name,segment)=>({
            name,type:'bar',stack:'proyectos-vendedor',barMaxWidth:42,
            label:{show:true,position:'top',fontSize:10,color:cascadeText,
                formatter:params=>(segment===1?params.data.declined>0:params.data.declined===0)?String(params.data.total):''},
            data:projectCounts.map((row,index)=>{
                const total=Number(row.proyectos),declined=declinedBySeller.get(String(row.vendedor_id??''))||0;
                return {value:segment===1?declined:total-declined,total,declined,itemStyle:{
                    color:segment===1?'#dc3545':'#2385bd',
                    borderColor:segment===1?'#9f2431':'#80510f',borderWidth:index===selected?2:0,
                    borderRadius:segment===1||declined===0?[2,2,0,0]:0}};
            })
        }));
        return option;
    }
    sellerChart.instance.setOption(sellerOption());
    fitCascade(sellerChart);
    document.getElementById('ventas-cascada-vacio').hidden = projectCounts.length > 0;
    function selectSeller(index) {
        selectedIndex = Number.isInteger(index) && index >= 0 && index < projectCounts.length ? index : -1;
        selector.value = selectedIndex < 0 ? '' : String(selectedIndex);
        sellerChart.instance.setOption({ series: sellerOption(selectedIndex).series });
        statusPanel.hidden = selectedIndex < 0;
        if (selectedIndex < 0) return;
        const seller = projectCounts[selectedIndex];
        document.getElementById('ventas-estatus-titulo').textContent = seller.nombre + ' — ' + seller.proyectos + ' proyectos';
        const sellerRows = (data.estatus_por_clasificacion || []).filter(row =>
            String(row.vendedor_id ?? '') === String(seller.vendedor_id ?? '') &&
            periods.some(period => Number(row.anio) === Number(period.anio) && Number(row.mes) === Number(period.mes)));
        const classifications = generalClassifications.filter(group => sellerRows.some(row => String(row.clasificacion_id) === String(group.clasificacion_id)));
        const sellerStatuses = statuses.filter(group => sellerRows.some(row => statusGroupKey(row) === String(group.id)));
        const classificationSummary = document.getElementById('ventas-estatus-resumen');
        classificationSummary.hidden = classifications.length > 0;
        classificationSummary.textContent = classifications.length ? '' : 'Sin clasificaciones registradas para los meses seleccionados.';
        if (!statusChart) statusChart = cascade('ventas-estatus-vendedor');
        renderSellerDonut(statusChart, principalOption, classifications, generalClassifications, sellerRows,
            row => String(row.clasificacion_id), group => String(group.clasificacion_id), group => group.clasificacion, 'clasificacion_periodo');
        document.getElementById('ventas-desglose-estatus-titulo').textContent = seller.nombre + ' — Estatus de proyectos';
        const statusSummary = document.getElementById('ventas-desglose-estatus-resumen');
        statusSummary.hidden = sellerStatuses.length > 0;
        statusSummary.textContent = sellerStatuses.length ? '' : 'Sin estatus registrados para los meses seleccionados.';
        if (!projectStatusChart) projectStatusChart = cascade('ventas-desglose-estatus');
        renderSellerDonut(projectStatusChart, statusOption, sellerStatuses, statuses, sellerRows,
            statusGroupKey, group => String(group.id), group => group.nombre, 'estatus_periodo');
    }
    function renderSellerDonut(entry, base, groups, allGroups, rows, rowKey, groupKey, groupName, kind) {
        const totalProjects = Number(projectCounts[selectedIndex].proyectos);
        const declinedTotal = rows.reduce((sum,row)=>sum+Number(row.declinados),0);
        const distribution = groups.flatMap(group => {
            const selected = rows.filter(row => rowKey(row) === groupKey(group));
            const total = selected.reduce((sum,row)=>sum+Number(row.proyectos),0);
            const declined = selected.reduce((sum,row)=>sum+Number(row.declinados),0);
            const index = allGroups.indexOf(group)*2;
            const common = {group:groupName(group),total,declined:false,id:groupKey(group),
                estatusIds:[...new Set(selected.map(row=>row.estatus_id==null?'sin_estatus':String(row.estatus_id)))]};
            const slices = [];
            if (total > declined) slices.push({...common,name:groupName(group),value:total-declined,itemStyle:base.series[index].itemStyle});
            return slices;
        });
        if (declinedTotal > 0) distribution.push({group:'Declinados',name:'Declinados',value:declinedTotal,
            total:declinedTotal,declined:true,
            estatusIds:[...new Set(rows.filter(row=>Number(row.declinados)>0).map(row=>row.estatus_id==null?'sin_estatus':String(row.estatus_id)))],itemStyle:{color:'#dc3545'}});
        entry.donut = true; entry.height = 360; entry.element.style.height = '360px';
        entry.instance.setOption({backgroundColor:'transparent',aria:{enabled:true},
            title:[{text:String(totalProjects),left:'center',top:'37%',textStyle:{color:cascadeText,fontSize:25,fontWeight:600}},
                {text:'Proyectos del vendedor',left:'center',top:'48%',textStyle:{color:cascadeText,fontSize:10,fontWeight:400}}],
            legend:{bottom:0,type:'scroll',textStyle:{color:cascadeText,fontSize:10},data:[...new Set(distribution.map(row=>row.name))]},
            tooltip:{trigger:'item',renderMode:'richText',formatter:params=>{
                const row=params.data; const percentage=totalProjects ? row.value/totalProjects*100 : 0;
                if (row.name === 'Declinados') return 'Declinados: '+row.value+' ('+amount.format(percentage)+' %)'+
                    '\nTotal del vendedor: '+totalProjects;
                return row.group+'\n'+row.name+': '+row.value+' ('+amount.format(percentage)+' %)'+
                    '\nTotal de la categoría: '+row.value+' ('+amount.format(percentage)+' %)'+
                    '\nTotal del vendedor: '+totalProjects;
            }},
            series:[{type:'pie',radius:['47%','68%'],center:['50%','44%'],
                label:{show:true,formatter:'{b}\n{c} ({d} %)',color:cascadeText,fontSize:10,overflow:'break',width:110},
                labelLine:{length:8,length2:6},avoidLabelOverlap:true,
                data:distribution.length ? distribution : [{name:'Sin proyectos',value:1,itemStyle:{color:'#dce7f0'},label:{show:false},tooltip:{show:false}}]}]
        },true);
        fitCascade(entry);
        if(!entry.drillBound){
            entry.drillBound=true;
            entry.instance.on('click',event=>{
                if(selectedIndex>=0)openPeriodSlice(event,kind,projectCounts[selectedIndex]);
            });
        }
    }
    function statusGroupKey(row) {
        if (quotedStatusKey !== null && String(row.estatus_id) === quotedStatusKey) return 'cotizados_periodo';
        return Number(row.estatus_id) >= 6 ? 'colocados' :
            ([1,3].includes(Number(row.estatus_id)) ? 'proceso_cotizacion' : String(row.estatus_id));
    }
    sellerChart.instance.on('click', event => {
        if (event.componentType !== 'series')return;
        selectSeller(event.dataIndex);
        if(selectedIndex<0)return;
        const seller=projectCounts[selectedIndex];
        const ids=[...new Set((data.estatus_por_vendedor||[]).filter(row=>String(row.vendedor_id??'')===String(seller.vendedor_id??''))
            .map(row=>row.estatus_id==null?'sin_estatus':String(row.estatus_id)))];
        openPeriodSlice({componentType:'series',data:{name:'Todos los proyectos',estatusIds:ids,segmento:'todos'}},'estatus_periodo',seller);
    });
    selector.addEventListener('change', () => selectSeller(selector.value === '' ? -1 : Number(selector.value)));
    // Consolida las clasificaciones del mismo conjunto filtrado, sin otra consulta.
    const classificationTotals = new Map();
    statusCounts.forEach(row => {
        const key = String(row.clasificacion_id);
        if (!classificationTotals.has(key)) classificationTotals.set(key, {
            clasificacion_id: row.clasificacion_id, clasificacion: row.clasificacion, proyectos: 0, declinados: 0
        });
        const total = classificationTotals.get(key);
        total.proyectos += Number(row.proyectos);
        total.declinados += Number(row.declinados);
    });
    const generalClassifications = [...classificationTotals.values()]
        .sort((a, b) => b.proyectos - a.proyectos || Number(a.clasificacion_id) - Number(b.clasificacion_id));
    const selectedYears = data.anios_seleccionados || [...new Set((data.estatus_por_clasificacion || []).map(row => Number(row.anio)).filter(Boolean))];
    const selectedMonths = data.meses_seleccionados || [];
    const periods = selectedYears.slice().sort((a,b)=>a-b).flatMap(anio => selectedMonths.slice().sort((a,b)=>a-b).map(mes => ({anio,mes})));
    const classificationColors=['#2385bd','#239c83','#d48825','#8064b0'];
    // Las bases conservan los colores de las donas por vendedor.
    const principalOption={series:generalClassifications.flatMap((group,index)=>[
        {itemStyle:{color:classificationColors[index%classificationColors.length]}},{itemStyle:{color:'#dc3545'}}])};
    const statusGroups = new Map();
    (data.estatus_por_clasificacion || []).forEach(row=>{
        const placed=Number(row.estatus_id)>=6, quotation=[1,3].includes(Number(row.estatus_id));
        const key=statusGroupKey(row);
        if(!statusGroups.has(key)) statusGroups.set(key,{id:placed||quotation?key:row.estatus_id,
            orden:placed?6:(quotation?1:Number(row.estatus_id)),
            nombre:placed?'Pedidos Colocados':(quotation?'PROCESO DE COTIZACION':(row.estatus||'Sin estatus'))});
    });
    if(Array.isArray(data.cotizados_por_periodo)) {
        const quoted=[...statusGroups.entries()].find(([,group])=>group.nombre.toUpperCase().startsWith('PEDIDO COTIZADO'));
        if(quoted){quotedStatusKey=String(quoted[1].id);statusGroups.delete(quoted[0]);}
        statusGroups.set('cotizados_periodo',{id:'cotizados_periodo',orden:quoted?quoted[1].orden:5,
            nombre:quoted?quoted[1].nombre:'PEDIDO COTIZADO (SIN OC CLIENTE)'});
    }
    if(Array.isArray(data.colocados_por_periodo)) statusGroups.set('colocados',{id:'colocados',orden:6,nombre:'Pedidos Colocados'});
    const statuses=[...statusGroups.values()].sort((a,b)=>a.orden-b.orden);
    const statusColors=['#2385bd','#239c83','#d48825','#8064b0','#56748c','#9b713a','#458f96','#b66489'];
    const statusOption={series:statuses.flatMap((group,index)=>[
        {itemStyle:{color:statusColors[index%statusColors.length]}},{itemStyle:{color:'#dc3545'}}])};
    function periodPie(entry,groups) {
        const declined=groups.reduce((sum,row)=>sum+Number(row.declinados),0);
        const sliceValue=row=>row.valorSegmento??(row.proyectos-row.declinados);
        const total=groups.reduce((sum,row)=>sum+sliceValue(row),declined);
        const slices=groups.filter(row=>sliceValue(row)>0).map(row=>({
            name:row.nombre,value:sliceValue(row),id:row.id,lista:row.lista,estatusIds:row.estatusIds,itemStyle:{color:row.color}}));
        if(declined>0) slices.push({name:'Declinados',value:declined,declined:true,itemStyle:{color:'#dc3545'}});
        entry.donut=true;entry.height=370;entry.element.style.height='370px';
        entry.instance.setOption({backgroundColor:'transparent',aria:{enabled:true},
            legend:{bottom:0,type:'scroll',textStyle:{color:cascadeText,fontSize:10}},
            title:total?[]:[{text:'Sin proyectos',left:'center',top:'43%',textStyle:{color:cascadeText,fontSize:12}}],
            tooltip:{trigger:'item',renderMode:'richText',formatter:params=>params.data.name+' | '+params.data.value+' | '+
                amount.format(total?params.data.value/total*100:0)+' %'},
            series:[{type:'pie',radius:'65%',center:['50%','45%'],
                label:{show:true,color:cascadeText,fontSize:10,width:110,overflow:'break',
                    formatter:params=>params.data.name+'\n'+params.data.value+' ('+amount.format(total?params.data.value/total*100:0)+' %)'},
                labelLine:{length:8,length2:6},avoidLabelOverlap:true,data:slices}]
        },true);
        fitCascade(entry);
    }
    function openPeriodSlice(event,kind,seller=null) {
        if(event.componentType!=='series'||!event.data)return;
        const row=event.data,modal=document.getElementById('modal-declinados-ventas');
        if(!modal||row.name==='Sin proyectos')return;
        if(seller)modal.dataset.desgloseVendedor=String(seller.vendedor_id??'');
        else delete modal.dataset.desgloseVendedor;
        // El segmento rojo comparte el detalle agrupado por estatus del mismo periodo.
        if(kind==='clasificacion_periodo'&&row.declined)kind='estatus_periodo';
        modal.dataset.desglose='1';
        modal.dataset.desgloseLista=kind==='estatus_periodo'?kind:row.declined?'declinados':kind;
        modal.dataset.estatusAgrupados=kind==='estatus_periodo'
            ? JSON.stringify(row.estatusIds|| (row.declined
                ? [...new Set((data.estatus_por_vendedor||[]).filter(project=>Number(project.declinados)>0).map(project=>project.estatus_id==null?'sin_estatus':String(project.estatus_id)))]
                : [])) : '';
        modal.dataset.desgloseAnio=selectedYears.join(',');
        modal.dataset.desgloseMes=selectedMonths.join(',');
        if(kind==='clasificacion_periodo')modal.dataset.clasificacionId=String(row.id);
        else modal.dataset.estatusId=row.id==null?'sin_estatus':String(row.id);
        modal.dataset.segmento=row.segmento||(row.declined?'declinados':'no_declinados');
        modal.dataset.desgloseTitulo=row.name+(seller?' · '+seller.nombre:'')+' · Todo el período seleccionado';
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }
    const classificationChart=cascade('ventas-clasificaciones-general');
    periodPie(classificationChart,generalClassifications.map((row,index)=>({id:row.clasificacion_id,nombre:row.clasificacion,
        proyectos:row.proyectos,declinados:row.declinados,color:classificationColors[index%classificationColors.length]})));
    classificationChart.instance.on('click',event=>openPeriodSlice(event,'clasificacion_periodo'));
    // Estatus actuales del mismo conjunto de proyectos registrados en el periodo.
    // Solo Cotizados y Colocados reutilizan abajo los conjuntos de los KPI.
    const periodStatuses=new Map();
    (data.estatus_por_vendedor || []).forEach(row=>{
        const key=statusGroupKey(row);
        if(!periodStatuses.has(key)) {
            const index=statuses.findIndex(group=>String(group.id)===key);
            const status=statuses[index];
            periodStatuses.set(key,{id:status?status.id:row.estatus_id,nombre:status?status.nombre:(row.estatus||'Sin estatus'),
                orden:status?status.orden:Number(row.estatus_id),estatusIds:[],proyectos:0,declinados:0,
                color:statusColors[Math.max(0,index)%statusColors.length]});
        }
        const group=periodStatuses.get(key);
        const statusId=row.estatus_id==null?'sin_estatus':String(row.estatus_id);
        if(!group.estatusIds.includes(statusId))group.estatusIds.push(statusId);
        group.proyectos+=Number(row.proyectos);group.declinados+=Number(row.declinados);
    });
    // Igual que en las barras originales: periodo + anteriores del conjunto documental.
    // Conservar los declinados originales y todos los otros estatus sin cambios.
    [['cotizados_periodo',data.cotizados_por_periodo],['colocados',data.colocados_por_periodo]].forEach(([key,rows])=>{
        if(!Array.isArray(rows))return;
        const status=statusGroups.get(key);
        if(!status)return;
        if(!periodStatuses.has(key)){
            const index=statuses.findIndex(group=>String(group.id)===key);
            periodStatuses.set(key,{...status,proyectos:0,declinados:0,color:statusColors[Math.max(0,index)%statusColors.length]});
        }
        const group=periodStatuses.get(key);
        group.valorSegmento=rows.reduce((sum,row)=>sum+Number(row.proyectos),0);
        group.lista=key==='colocados'?'colocados_periodo':'cotizados_periodo';
    });
    const generalStatusChart=cascade('ventas-estatus-general');
    periodPie(generalStatusChart,[...periodStatuses.values()].sort((a,b)=>a.orden-b.orden));
    generalStatusChart.instance.on('click',event=>openPeriodSlice(event,event.data?.lista||'estatus_periodo'));
    window.addEventListener('resize', function () { charts.forEach(c => c.resize()); cascadeCharts.forEach(fitCascade); });

});
