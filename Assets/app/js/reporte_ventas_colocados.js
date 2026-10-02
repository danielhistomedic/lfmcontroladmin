/* Resumen financiero de pedidos. Solo carga al abrir la tarjeta Pedidos Colocados. */
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modal-colocados-financiero');
    if (!modal) return;
    const state = document.getElementById('colocados-financiero-estado');
    const retry = document.getElementById('colocados-financiero-reintentar');
    const summary = document.getElementById('colocados-financiero-resumen');
    const requests = new Map();
    const tables = new Map();
    const amount = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const displayAmount = value => Number.isFinite(Number(value)) ? '$ ' + amount.format(Number(value)) : '—';
    const text = jQuery.fn.dataTable.render.text().display;
    function params(section) {
        const result = new URLSearchParams({ seccion: section, vendedor: modal.dataset.vendedor });
        modal.dataset.anio.split(',').forEach(year => result.append('anio[]', year));
        modal.dataset.mes.split(',').forEach(month => result.append('mes[]', month));
        return result;
    }
    async function get(section, query) {
        if (requests.has(section)) requests.get(section).abort();
        const current = new AbortController();
        requests.set(section, current);
        try {
            const response = await fetch(modal.dataset.url + '?' + query, {
                signal: current.signal, credentials: 'same-origin', headers: { Accept: 'application/json' }
            });
            const payload = await response.json();
            if (requests.get(section) !== current || current.signal.aborted) throw new DOMException('Cancelado', 'AbortError');
            if (!response.ok || !payload.status) throw new Error(payload.message || 'No se pudo cargar el resumen.');
            return payload.data;
        } catch (error) {
            if (current.signal.aborted || requests.get(section) !== current) throw new DOMException('Cancelado', 'AbortError');
            throw error;
        } finally { if (requests.get(section) === current) requests.delete(section); }
    }
    function currencyCards(id, rows) {
        const target = document.getElementById(id);
        target.replaceChildren();
        const currencies = new Map([['3',{moneda:'USD',total:0,pedidos:0}],['1',{moneda:'MXN',total:0,pedidos:0}]]);
        rows.forEach(row => currencies.set(String(row.moneda_id), row));
        currencies.forEach(row => {
            const card = document.createElement('div');
            card.className = 'colocados-total';
            const currency = document.createElement('span');
            currency.textContent = row.moneda;
            const value = document.createElement('strong');
            value.textContent = displayAmount(row.total);
            const count = document.createElement('small');
            count.textContent = row.pedidos + ' pedidos';
            card.appendChild(currency); card.appendChild(value); card.appendChild(count);
            target.appendChild(card);
        });
    }
    async function loadSummary() {
        summary.hidden = true;
        state.textContent = 'Cargando resumen financiero…';
        state.className = 'text-muted mb-2';
        retry.hidden = true;
        try {
            const data = await get('resumen', params('resumen'));
            currencyCards('colocados-totales', data.totales);
            currencyCards('colocados-flowserve', data.grupos.filter(row => row.grupo === 'Flowserve'));
            currencyCards('colocados-diversos', data.grupos.filter(row => row.grupo === 'Diversos'));
            summary.hidden = false;
            state.textContent = data.totales.length ? '' : 'No hay pedidos enviados para el período y vendedor seleccionados.';
        } catch (error) {
            if (error.name === 'AbortError') return;
            state.textContent = error instanceof SyntaxError ? 'No se pudo cargar el resumen. Intente nuevamente.' : error.message;
            state.className = 'text-danger mb-2';
            retry.hidden = false;
        }
    }
    function loadTable(section) {
        if (tables.has(section)) { tables.get(section).columns.adjust(); tables.get(section).ajax.reload(null, true); return; }
        const element = document.getElementById('colocados-' + section + '-tabla');
        const tableState = document.getElementById('colocados-' + section + '-estado');
        const table = jQuery(element).DataTable({
            serverSide: true, processing: true, searchDelay: 400, pageLength: 10,
            lengthMenu: [5,10,25,50,100], order: [[1,'asc']],
            language: typeof idioma_espanol !== 'undefined' ? idioma_espanol : {
                processing:'Cargando…',emptyTable:'Sin pedidos',zeroRecords:'Sin coincidencias',search:'Buscar:',
                lengthMenu:'Mostrar _MENU_',info:'_START_ a _END_ de _TOTAL_',infoEmpty:'Sin registros',infoFiltered:'(de _MAX_)',
                paginate:{previous:'Anterior',next:'Siguiente'}
            },
            columns: [
                {data:'nombre',render:(value,type)=>type === 'display' ? text(String(value ?? '')) : value},
                {data:'moneda',render:(value,type)=>type === 'display' ? text(String(value ?? '')) : value},
                {data:'total',className:'text-end',render:(value,type)=>type === 'display' ? displayAmount(value) : value}
            ],
            ajax: async function (data, callback) {
                const query = params(section);
                const order = data.order[0] || {column:1,dir:'asc'};
                Object.entries({draw:data.draw,start:data.start,length:data.length,search:data.search.value,
                    order_column:order.column,order_dir:order.dir}).forEach(([key,value])=>query.set(key,String(value)));
                tableState.textContent = '';
                try { callback(await get(section, query)); }
                catch (error) {
                    if (error.name === 'AbortError') return;
                    tableState.textContent = error instanceof SyntaxError ? 'No se pudo cargar la tabla. Intente nuevamente.' : error.message;
                    retry.hidden = false;
                    callback({draw:data.draw,recordsTotal:0,recordsFiltered:0,data:[]});
                }
            }
        });
        tables.set(section, table);
    }
    function load() { loadSummary(); loadTable('clientes'); loadTable('vendedores'); }
    modal.addEventListener('shown.bs.modal', load);
    modal.addEventListener('hidden.bs.modal', () => {
        requests.forEach(request => request.abort()); requests.clear(); summary.hidden = true;
    });
    retry.addEventListener('click', load);
    document.querySelectorAll('#ventas-mensuales .ventas-abrir-colocados').forEach(card => {
        card.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); card.click(); }
        });
    });
});
