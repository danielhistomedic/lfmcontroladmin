/* Validación sin navegador, base de datos ni dependencias nuevas: Node + ECharts del portal. */
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
// Node 24 expone navigator; ECharts 5 necesita reconocer este proceso como servidor.
Object.defineProperty(globalThis, 'navigator', { value: undefined });
const echarts = require('../../Assets/vendor/echarts/dist/echarts.js');
const code = fs.readFileSync(path.join(__dirname, '../../Assets/app/js/reporte_ventas_mensuales.js'), 'utf8');

function ejecutar(empty, width, theme) {
    const vendors = empty ? [] : [{ nombre: 'José', cotizado: 130, colocado: 80 }, { nombre: 'Ana', cotizado: 20, colocado: 40 }];
    const rows = empty ? [] : Array.from({ length: 12 }, (_, i) => ({ nombre: 'Servicio '+i, subclasificacion_id: String(i),
        vendedor_id: i % 2 ? 'V2' : 'V1', vendedor: i % 2 ? 'Ana' : 'José', cotizado: i+10, colocado: i+5 }));
    const data = { cotizado: empty ? 0 : 150, colocado: empty ? 0 : 120, vendedores: vendors, productos: rows, cruce: rows,
        diario: Array.from({ length: 29 }, (_, i) => ({ dia: i+1, cotizado: i === 0 && !empty ? 150 : 0, colocado: i === 1 && !empty ? 120 : 0 })) };
    const charts = []; const events = {}; const formEvents = {}; let change; let requests = 0;
    const button = { disabled: false }; const loading = { hidden: true };
    const form = { addEventListener: (name, cb) => { formEvents[name] = cb; }, querySelector: () => button,
        requestSubmit: () => { requests++; formEvents.submit({ preventDefault() {} }); } };
    const context = {
        document: {
            addEventListener: (name, cb) => { events[name] = cb; },
            getElementById: id => id === 'filtros-ventas-mensuales' ? form : id === 'ventas-cargando' ? loading :
                id === 'ventas-mensuales-datos' ? { textContent: JSON.stringify(data) } : { id, clientWidth: width },
            querySelectorAll: () => []
        },
        window: { addEventListener: (name, cb) => { events[name] = cb; } },
        jQuery: () => ({ find: () => ({ on: (name, cb) => { change = cb; } }), DataTable: () => {} }),
        echarts: { init: (element, selectedTheme) => {
            // ARIA utiliza atributos del contenedor DOM incluso al renderizar SVG en servidor.
            const root = { setAttribute() {}, getAttribute() { return null; } };
            const chart = echarts.init(root, selectedTheme, { renderer: 'svg', ssr: true, width, height: 340 });
            charts.push(chart); return chart;
        } }, theme_chart: theme, Intl, Map, JSON
    };
    vm.runInNewContext(code, context, { filename: 'reporte_ventas_mensuales.js' });
    events.DOMContentLoaded();
    assert.equal(charts.length, 5, 'Inicializar las cinco gráficas');
    for (const chart of charts) {
        const svg = chart.renderToSVGString();
        assert.ok(svg.includes('<svg'), 'Renderizar con ECharts instalado');
        assert.ok(!svg.includes('NaN'), 'Sin geometría inválida en vacío ni móvil');
    }
    if (!empty) {
        const comparison = charts[4].getOption();
        assert.equal(comparison.series.length, 2, 'Cruce con una serie por vendedor');
        assert.equal(comparison.series[0].data[0], 5, 'Asignar ventas a su vendedor y subclasificación');
        assert.equal(comparison.series[1].data[0], 0, 'Mantener cero para combinaciones sin ventas');
        assert.ok(comparison.dataZoom.length > 0, 'Permitir recorrer subclasificaciones numerosas');
    }
    change(); change();
    assert.equal(requests, 1, 'Un solo envío global mientras está cargando');
    assert.equal(loading.hidden, false, 'Mostrar estado de carga');
    assert.equal(button.disabled, true, 'Evitar envíos duplicados');
    events.pageshow();
    assert.equal(button.disabled, false, 'Recuperar controles al volver a la página');
    events.resize();
    charts.forEach(chart => chart.dispose());
}
ejecutar(false, 900, 'walden');
ejecutar(false, 320, 'dark');
ejecutar(true, 900, 'walden');
ejecutar(true, 320, 'dark');
console.log('OK: cinco gráficas SVG con ECharts '+echarts.version+', cruce por vendedor, categorías numerosas, móvil, oscuro, vacío y filtros globales.');
