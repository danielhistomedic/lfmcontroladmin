/**
 * Diseño DataTables aprobado: composición extraída de Órdenes de Compra Clientes.
 * Los módulos llaman create() explícitamente; recuperar una API sigue siendo nativo.
 */
(function (window, $) {
    'use strict';
    if (!$ || !$.fn.dataTable) return;

    const menu = [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Todos']];
    const filterTimers = new WeakMap();
    const actionName = /^(?:opciones|acciones|acción|accion|options|actions)$/i;

    /** Identifica columnas de acciones por contrato o encabezado, sin asumir un índice. */
    function actionColumns(table, options) {
        return Array.from(table.tHead.rows[0].cells).reduce(function (result, header, index) {
            const column = (options.columns || options.aoColumns || [])[index] || {};
            if (actionName.test(String(column.data || column.mData || '').trim()) || actionName.test(header.textContent.trim())) result.push(index);
            return result;
        }, []);
    }

    /** Completa filtros simples y conserva los eventos especializados existentes.
     * clearColumnFilters=true agrega limpieza solo para la instancia solicitante:
     * cancela debounce y borra búsquedas de columna, conservando búsqueda global y visibilidad.
     */
    function filters(table, options, actions) {
        const head = table.tHead;
        const rows = Array.from(head.rows);
        const titles = rows[0];
        // Encabezados agrupados mantienen su estructura e índices originales.
        if (Array.from(titles.cells).some(cell => cell.colSpan > 1 || cell.rowSpan > 1)) return;
        const filterRows = rows.slice(1).filter(row => row.querySelector('input, select'));
        const row = filterRows.pop() || (rows.length === 1 ? head.insertRow() : null);
        filterRows.forEach(old => old.remove());
        if (!row) return;
        row.classList.add('lfm-dt-filters');
        const timers = new Set();
        filterTimers.set(table, timers);
        const newRow = row.cells.length === 0;
        Array.from(titles.cells).forEach(function (header, index) {
            const cell = newRow ? document.createElement('th') : row.cells[index];
            if (newRow) row.appendChild(cell);
            if (!cell) return;
            const column = (options.columns || options.aoColumns || [])[index] || {};
            if (actions.includes(index)) { $(cell).empty(); return; }
            let input = cell.querySelector('input, select');
            // Una fila existente puede omitir filtros que su contrato no admite.
            if (!input && newRow && column.searchable !== false && column.bSearchable !== false && options.searching !== false) {
                input = document.createElement('input');
                input.type = 'search';
                input.className = 'form-control text-center';
                cell.appendChild(input);
                input.dataset.lfmGeneratedFilter = '1';
            }
            // Reconstruir la tabla no debe conservar closures de temporizadores anteriores.
            if (input && input.dataset.lfmGeneratedFilter === '1') {
                $(input).off('.lfmFilter');
                let timer;
                $(input).on('input.lfmFilter keyup.lfmFilter change.lfmFilter', function () {
                    clearTimeout(timer); timers.delete(timer);
                    timer = setTimeout(function () {
                        timers.delete(timer);
                        if (!$.fn.dataTable.isDataTable(table)) return;
                        const api = $(table).DataTable();
                        if (api.column(index).search() !== input.value) api.column(index).search(input.value).draw();
                    }, options.serverSide ? (options.searchDelay || 400) : 150);
                    timers.add(timer);
                });
                $(table).one('destroy.dt.lfmFilter', function () { clearTimeout(timer); timers.delete(timer); });
            }
            if (input) {
                const label = 'Filtrar ' + header.textContent.trim();
                if (!input.getAttribute('aria-label')) input.setAttribute('aria-label', label);
                if (input.tagName === 'INPUT' && !input.placeholder) input.placeholder = label;
                $(input).off('.lfmFilterFocus').on('click.lfmFilterFocus keydown.lfmFilterFocus', function (event) { event.stopPropagation(); });
            }
        });
        if (options.clearColumnFilters === true && row.cells[0]) {
            row.cells[0].querySelectorAll('.lfm-dt-clear-columns').forEach(nodo => nodo.remove());
            const boton = document.createElement('button');
            boton.type = 'button'; boton.className = 'btn btn-outline-secondary lfm-dt-clear-columns';
            boton.title = 'Limpiar filtros de columna'; boton.setAttribute('aria-label', boton.title);
            boton.innerHTML = '<i class="fa-regular fa-filter-circle-xmark" aria-hidden="true"></i>';
            $(boton).on('click.lfmClearColumns', function (event) {
                event.preventDefault(); event.stopPropagation();
                if (!$.fn.dataTable.isDataTable(table)) return;
                (filterTimers.get(table) || []).forEach(timer => clearTimeout(timer));
                if (filterTimers.has(table)) filterTimers.get(table).clear();
                const api = $(table).DataTable();
                // Se vacían también los nodos originales y la cabecera movida por FixedHeader.
                $(api.table().header()).find('.lfm-dt-filters input, .lfm-dt-filters select').val('');
                $(table).find('.lfm-dt-filters input, .lfm-dt-filters select').val('');
                api.columns().search('').draw();
            });
            $(boton).on('keydown.lfmClearColumns', event => event.stopPropagation());
            let grupo = row.cells[0].querySelector('.lfm-dt-filter-tools');
            if (!grupo) {
                grupo = document.createElement('div'); grupo.className = 'lfm-dt-filter-tools';
                const filtroPrimero = row.cells[0].querySelector('input, select');
                if (filtroPrimero) grupo.appendChild(filtroPrimero);
                row.cells[0].appendChild(grupo);
            }
            grupo.appendChild(boton);
        }
    }

    /** Conserva exportadores especiales y limita los genéricos a datos visibles. */
    function buttons(table, options, actions) {
        if (options.buttons === false) return;
        if (options.buttons === undefined) options.buttons = [{extend: 'colvis', postfixButtons: ['colvisRestore']}];
        const list = Array.isArray(options.buttons) ? options.buttons : options.buttons.buttons;
        if (!Array.isArray(list)) return;
        list.forEach(function (button) {
            if (!button || typeof button !== 'object') return;
            if (button.extend === 'colvis' && !button.postfixButtons) button.postfixButtons = ['colvisRestore'];
            if (!/^excel(?:Html5)?$/.test(button.extend || '') || button.action) return;
            button.autoFilter = true;
            const exporting = button.exportOptions = button.exportOptions || {};
            const selector = exporting.columns === undefined ? ':visible' : exporting.columns;
            if (exporting.orthogonal === undefined) exporting.orthogonal = 'export';
            exporting.columns = function (index, data, node) {
                const api = $(table).DataTable();
                if (actions.includes(index) || !api.column(index).visible()) return false;
                return api.columns(selector).indexes().toArray().includes(index);
            };
        });
    }

    /** Marca el contenedor nativo: no crea tarjetas ni modifica títulos del módulo. */
    function decorate(table) {
        const wrapper = $(table).closest('.dataTables_wrapper');
        wrapper.addClass('lfm-dt-wrapper');
        wrapper.find('.dataTables_filter input').each(function () {
            if (!this.getAttribute('aria-label')) this.setAttribute('aria-label', 'Buscar en la tabla');
        });
    }

    /**
     * Inicializa con el diseño aprobado manteniendo el contrato del módulo.
     * @param {jQuery} element Tabla por ID; no altera las recuperaciones de API.
     * @param {Object} original AJAX, columnas, renderizadores, orden y callbacks propios.
     * @returns {Object} API nativa de DataTables 1.13.
     */
    function create(element, original) {
        const table = element[0];
        if (!table || !table.tHead) return element.DataTable(original);
        const options = $.extend(true, {}, original || {});
        if ($.fn.dataTable.isDataTable(table)) {
            if (!options.destroy && !options.bDestroy) return element.DataTable(original);
            element.DataTable().destroy();
        }
        const actions = actionColumns(table, options);
        const defs = options.columnDefs || options.aoColumnDefs || [];
        options.columnDefs = actions.map(index => ({targets: index, orderable: false, searchable: false})).concat(defs);
        table.classList.add('lfm-datatable', 'table', 'table-bordered', 'table-striped', 'table-hover', 'text-nowrap', 'w-100');
        const container = table.closest('.table-responsive');
        if (container) container.classList.add('export-table', 'lfm-dt-container');
        // El ámbito aprobado debe existir antes del cálculo inicial de anchos.
        const classes = options.oClasses || options.classes || {};
        options.oClasses = $.extend({}, classes, {sWrapper: (classes.sWrapper || $.fn.dataTable.ext.classes.sWrapper) + ' lfm-dt-wrapper'});
        options.orderCellsTop = true;
        options.scrollX = '100%';
        options.responsive = false;
        if (options.fixedHeader === undefined) options.fixedHeader = true;
        if (!options.serverSide && !options.bServerSide) {
            options.pageLength = 5;
            options.iDisplayLength = 5;
            options.lengthMenu = menu;
        }
        const language = options.language || (typeof idioma_espanol !== 'undefined' ? idioma_espanol : {});
        options.language = $.extend(true, {}, language, {search: '', sSearch: '', searchPlaceholder: 'Buscar...'});
        buttons(table, options, actions);
        // Este alias mantiene la ocultación de Excel en las listas especiales.
        const specialized = String(options.dom || '').includes('declinados-buttons');
        options.dom = '<"lfm-dt-toolbar"<"lfm-dt-length' + (specialized ? ' declinados-length' : '') + '"l>'
            + '<"lfm-dt-buttons' + (specialized ? ' declinados-buttons' : '') + '"' + (options.buttons !== false ? 'B' : '') + '>'
            + '<"lfm-dt-search' + (specialized ? ' declinados-search' : '') + '"f>>rt<"lfm-dt-info"i><"lfm-dt-pagination"p>';
        filters(table, options, actions);
        element.off('init.dt.lfmLayout').on('init.dt.lfmLayout', function () { decorate(table); });
        const api = element.DataTable(options);
        decorate(table);
        return api;
    }

    // Ajustar geometría al mostrar paneles sin repetir las consultas de datos.
    $(document).on('shown.bs.modal.lfmLayout shown.bs.tab.lfmLayout', function () {
        const api = $.fn.dataTable.tables({visible: true, api: true});
        api.columns.adjust();
        if (api.fixedHeader && api.fixedHeader.adjust) api.fixedHeader.adjust();
    });
    window.LfmTables = {create: create};
})(window, window.jQuery);
