/* Resumen financiero compartido por la seccion del reporte y el modal. */
document.addEventListener('DOMContentLoaded', function () {
    ['resumen-colocados-financiero','modal-colocados-financiero'].forEach(rootId => {
    const modal = document.getElementById(rootId);
    if (!modal) return;
    const isModal = rootId === 'modal-colocados-financiero';
    const byId = id => document.getElementById(isModal ? id : 'panel-' + id);
    const state = byId('colocados-financiero-estado');
    const retry = byId('colocados-financiero-reintentar');
    const summary = byId('colocados-financiero-resumen');
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
        const target = byId(id);
        target.replaceChildren();
        const currencies = new Map([['3',{moneda:'USD',total:0,pedidos:0,proyectos:0}],['1',{moneda:'MXN',total:0,pedidos:0,proyectos:0}]]);
        rows.forEach(row => currencies.set(String(row.moneda_id), row));
        currencies.forEach(row => {
            const card = document.createElement('div');
            card.className = 'colocados-total';
            const currency = document.createElement('span');
            currency.textContent = row.moneda;
            const value = document.createElement('strong');
            value.textContent = displayAmount(row.total);
            const count = document.createElement('small');
            count.textContent = (row.proyectos ?? row.pedidos) + ' proyectos · ' + row.pedidos + ' pedidos';
            const breakdown = document.createElement('small');
            breakdown.textContent = 'Productos: ' + displayAmount(row.productos ?? 0) + ' | Servicios: ' + displayAmount(row.servicios ?? 0);

            card.appendChild(currency); card.appendChild(value); card.appendChild(count); card.appendChild(breakdown);
            if (id === 'colocados-flowserve') {
                const subcategories = document.createElement('dl');
                subcategories.className = 'colocados-flowserve-subclasificaciones';
                [1,2,3].forEach(subcategory => {
                    const label = row['subclasificacion_' + subcategory + '_nombre'];
                    const subtotal = Number(row['subclasificacion_' + subcategory] ?? 0);
                    if (typeof label !== 'string' || !label.trim() || !Number.isFinite(subtotal) || subtotal === 0) return;
                    const name = document.createElement('dt');
                    const shortNames = {'BOMBAS FLOWSERVE':'Bombas','SELLOS FLOWSERVE':'Sellos','VALVULAS FLOWSERVE':'Válvulas'};
                    name.textContent = shortNames[label.trim().toUpperCase()] || label;
                    const amount = document.createElement('dd');
                    amount.textContent = displayAmount(subtotal);
                    subcategories.appendChild(name); subcategories.appendChild(amount);
                    const types = document.createElement('dd');
                    types.className = 'colocados-subclasificacion-tipos';
                    types.textContent = 'Productos: ' + displayAmount(row['subclasificacion_' + subcategory + '_productos'] ?? 0) +
                        ' | Servicios: ' + displayAmount(row['subclasificacion_' + subcategory + '_servicios'] ?? 0);
                    subcategories.appendChild(types);
                });
                if (subcategories.children.length) card.appendChild(subcategories);
            }
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
            const counts = data.conteo || {};
            byId('colocados-proyectos-conteo').textContent = 'Proyectos colocados en el período: ' + (counts.proyectos ?? 0) +
                ' | Proyectos del período: ' + (counts.proyectos_periodo ?? 0) +
                ' | Proyectos anteriores: ' + (counts.proyectos_anteriores ?? 0);
            currencyCards('colocados-totales', data.totales);
            currencyCards('colocados-flowserve', data.grupos.filter(row => row.grupo === 'Flowserve'));
            currencyCards('colocados-diversos', data.grupos.filter(row => row.grupo === 'Diversos'));
            if (isModal) renderMonths(data.mensual || []);
            summary.hidden = false;
            state.textContent = data.totales.length ? '' : 'No hay pedidos enviados para el período y vendedor seleccionados.';
        } catch (error) {
            if (error.name === 'AbortError') return;
            state.textContent = error instanceof SyntaxError ? 'No se pudo cargar el resumen. Intente nuevamente.' : error.message;
            state.className = 'text-danger mb-2';
            retry.hidden = false;
        }
    }
    const monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const detail = {year:0,month:0,start:0,column:2,direction:'asc',timer:null};
    const detailSearch = byId('colocados-partidas-buscar');
    function appendRow(body, values) {
        const tr = document.createElement('tr');
        values.forEach(value => { const td = document.createElement('td'); td.textContent = String(value ?? ''); tr.appendChild(td); });
        body.appendChild(tr);
        return tr;
    }
    function renderMonths(rows) {
        const body = byId('colocados-mensual-filas');
        body.replaceChildren();
        if (!rows.length) { appendRow(body,['Sin pedidos para los filtros seleccionados.']); return; }
        rows.forEach(row => {
            const tr = appendRow(body,[row.anio,monthNames[Number(row.mes)-1],row.moneda,row.proyectos ?? row.pedidos,row.pedidos,
                displayAmount(row.total),displayAmount(row.productos),displayAmount(row.servicios)]);
            for (let column = 3; column <= 7; column++) tr.children[column].className = 'text-end';
            const td = document.createElement('td');
            const button = document.createElement('button');
            button.type = 'button'; button.className = 'btn btn-outline-primary btn-sm'; button.textContent = 'Ver partidas';
            button.setAttribute('aria-label', 'Ver partidas de ' + monthNames[Number(row.mes)-1] + ' ' + row.anio);
            button.addEventListener('click', () => { detail.year = row.anio; detail.month = row.mes; detail.start = 0; detailSearch.value = ''; loadDetails(); });
            td.appendChild(button); tr.appendChild(td);
        });
    }
    async function loadDetails() {
        clearTimeout(detail.timer);
        for (let column = 0; column < 12; column++) {
            byId('colocados-partidas-columna-' + column).setAttribute('aria-sort',
                column === detail.column ? (detail.direction === 'asc' ? 'ascending' : 'descending') : 'none');
        }
        const section = byId('colocados-partidas');
        const body = byId('colocados-partidas-filas');
        const state = byId('colocados-partidas-estado');
        const previous = byId('colocados-partidas-anterior');
        const next = byId('colocados-partidas-siguiente');
        const detailRetry = byId('colocados-partidas-reintentar');
        section.hidden = false;
        if (isModal) section.open = true;
        byId('colocados-partidas-titulo').textContent = isModal ? 'Partidas de ' + monthNames[Number(detail.month)-1] + ' ' + detail.year + ' (todas las monedas)' : 'Total por Productos';
        body.replaceChildren(); state.textContent = 'Cargando partidas...'; previous.disabled = next.disabled = true; detailRetry.hidden = true;
        byId('colocados-partidas-pagina').textContent = '';
        const query = params('detalle');
        modal.dataset.anio.split(',').forEach(year => query.append('periodo_anio[]',year));
        modal.dataset.mes.split(',').forEach(month => query.append('periodo_mes[]',month));
        if (isModal) {
            query.delete('anio[]'); query.delete('mes[]');
            query.append('anio[]', detail.year); query.append('mes[]', detail.month);
        }
        query.set('start', detail.start); query.set('length', 5);
        query.set('search', detailSearch.value); query.set('order_column', detail.column); query.set('order_dir', detail.direction);
        try {
            const data = await get('detalle',query);
            data.data.forEach(row => appendRow(body,[row.proyecto_id,row.num_orden_compra,row.fecha_pedido,
                row.moneda,row.tipo_partida,row.clave,row.ccn,row.codigo_cliente,row.descripcion,
                row.cantidad_pedido,displayAmount(row.precio_unitario),displayAmount(row.subtotal_partida)]));
            state.textContent = data.data.length ? '' : (isModal ? 'Sin partidas en este mes.' : 'Sin partidas para los filtros seleccionados.');
            previous.disabled = detail.start === 0; next.disabled = detail.start + 5 >= data.recordsFiltered;
            byId('colocados-partidas-pagina').textContent = data.recordsFiltered ?
                (detail.start+1) + '–' + (detail.start+data.data.length) + ' de ' + data.recordsFiltered : '0 partidas';
        } catch (error) {
            if (error.name === 'AbortError') return;
            state.textContent = error instanceof SyntaxError ? 'No se pudo cargar el detalle.' : error.message;
            detailRetry.hidden = false;
        }
    }
    byId('colocados-partidas-anterior').addEventListener('click', () => { detail.start = Math.max(0,detail.start-5); loadDetails(); });
    byId('colocados-partidas-siguiente').addEventListener('click', () => { detail.start += 5; loadDetails(); });
    byId('colocados-partidas-reintentar').addEventListener('click', loadDetails);
    detailSearch.addEventListener('input', () => {
        clearTimeout(detail.timer);
        if (requests.has('detalle')) requests.get('detalle').abort();
        detail.start = 0;
        detail.timer = setTimeout(loadDetails, 350);
    });
    for (let column = 0; column < 12; column++) {
        byId('colocados-partidas-orden-' + column).addEventListener('click', () => {
            detail.direction = column === detail.column && detail.direction === 'asc' ? 'desc' : 'asc';
            detail.column = column; detail.start = 0; loadDetails();
        });
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
        const control = name => byId('colocados-' + section + '-' + name);
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
    function load() {
        clearTimeout(detail.timer);
        if (requests.has('detalle')) requests.get('detalle').abort();
        if (isModal) {
            byId('colocados-partidas').hidden = true;
            byId('colocados-mensual-filas').replaceChildren();
        } else {
            detail.start = 0;
            byId('colocados-partidas').open = false;
            loadDetails();
        }
        loadSummary(); loadTable('clientes', true); loadTable('vendedores', true);
    }
    if (isModal) {
    modal.addEventListener('shown.bs.modal', load);
    modal.addEventListener('hidden.bs.modal', () => {
        requests.forEach(request => request.abort()); requests.clear(); summary.hidden = true;
        byId('colocados-partidas').hidden = true;
        clearTimeout(detail.timer);
        tables.forEach(table => clearTimeout(table.timer));
    });
    }
    retry.addEventListener('click', load);
    if (isModal) {
    document.querySelectorAll('#ventas-mensuales .ventas-abrir-colocados').forEach(card => {
        card.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); card.click(); }
        });
    });
    } else { load(); }
    });
});
