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
    async function loadTable(section, reset = false) {
        const table = tables.get(section);
        if (reset) table.start = 0;
        clearTimeout(table.timer);
        const query = params(section);
        const [column, direction] = table.order.value.split(':');
        Object.entries({draw:1,start:table.start,length:10,search:table.search.value,
            order_column:column,order_dir:direction}).forEach(([key,value])=>query.set(key,String(value)));
        table.state.textContent = 'Cargando...';
        table.state.className = 'text-muted';
        table.element.setAttribute('aria-busy', 'true');
        table.previous.disabled = table.next.disabled = true;
        table.body.replaceChildren();
        table.page.textContent = '';
        try {
            const data = await get(section, query);
            data.data.forEach(row => {
                const tr = document.createElement('tr');
                [row.nombre, row.moneda, displayAmount(row.total)].forEach((value, index) => {
                    const td = document.createElement('td');
                    td.textContent = String(value ?? '');
                    if (index === 1) td.className = 'colocados-moneda';
                    if (index === 2) td.className = 'text-end';
                    tr.appendChild(td);
                });
                table.body.appendChild(tr);
            });
            table.state.textContent = data.data.length ? '' : 'Sin resultados para esta selección.';
            table.page.textContent = data.recordsFiltered ? (table.start + 1) + '–' +
                (table.start + data.data.length) + ' de ' + data.recordsFiltered : '0 resultados';
            table.previous.disabled = table.start === 0;
            table.next.disabled = table.start + 10 >= data.recordsFiltered;
            table.element.setAttribute('aria-busy', 'false');
        } catch (error) {
            if (error.name === 'AbortError') return;
            table.state.textContent = error instanceof SyntaxError ? 'No se pudo cargar la tabla. Intente nuevamente.' : error.message;
            table.state.className = 'text-danger';
            table.element.setAttribute('aria-busy', 'false');
            retry.hidden = false;
        }
    }
    ['clientes','vendedores'].forEach(section => {
        const control = name => document.getElementById('colocados-' + section + '-' + name);
        const element = control('tabla');
        const table = {element,body:element.querySelector('tbody'),state:control('estado'),
            page:control('pagina'),previous:control('anterior'),next:control('siguiente'),
            search:control('buscar'),order:control('orden'),start:0,timer:null};
        tables.set(section, table);
        table.search.addEventListener('input', () => {
            clearTimeout(table.timer);
            // Cancel pending results as soon as the search changes.
            if (requests.has(section)) requests.get(section).abort();
            table.timer = setTimeout(() => loadTable(section, true), 350);
        });
        table.order.addEventListener('change', () => loadTable(section, true));
        table.previous.addEventListener('click', () => { table.start = Math.max(0, table.start - 10); loadTable(section); });
        table.next.addEventListener('click', () => { table.start += 10; loadTable(section); });
    });
    function load() { loadSummary(); loadTable('clientes', true); loadTable('vendedores', true); }
    modal.addEventListener('shown.bs.modal', load);
    modal.addEventListener('hidden.bs.modal', () => {
        requests.forEach(request => request.abort()); requests.clear(); summary.hidden = true;
        tables.forEach(table => clearTimeout(table.timer));
    });
    retry.addEventListener('click', load);
    document.querySelectorAll('#ventas-mensuales .ventas-abrir-colocados').forEach(card => {
        card.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); card.click(); }
        });
    });
});
