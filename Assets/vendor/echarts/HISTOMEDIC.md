# Apache ECharts en HistoMedic

Actualización autorizada del 27/10/2026: núcleo **6.1.0**, procedente del paquete `echarts` publicado por el proyecto Apache, enlazado desde su descarga oficial. Los archivos de terceros se conservan sin parches. Las versiones, URLs, licencias declaradas, integridad SHA-512 de cada tarball y SHA-256 de cada archivo están en `vendor-manifest.json`.

## Distribuciones y recursos

- `dist/echarts.js` / `dist/echarts.min.js`: distribución completa y autocontenida para navegador; cargar solo una.
- `dist/echarts.common.*` / `dist/echarts.simple.*`: variantes reducidas; no sustituir el núcleo completo sin comprobar componentes necesarios.
- `dist/echarts.esm.*`: distribución ESM empaquetada. Los puntos de entrada modulares de la raíz y `lib/` pertenecen al paquete original y necesitan resolver sus dependencias npm; no son scripts clásicos ni instalan un proceso de build en el portal.
- `echarts.js` y `echarts__.js`: copias de compatibilidad de `dist/echarts.js`, ambas de la versión actual. No conservan un segundo núcleo antiguo.
- `i18n/`: traducciones de la misma publicación; el portal carga `langES.js` después del núcleo.
- `theme/`: todos los temas distribuidos en el paquete, incluido `v5` para migraciones que necesiten apariencia anterior. Cargar el archivo antes de pasar su nombre a `echarts.init`; un tema disponible no cambia automáticamente el diseño de Expediente.
- `dist/extension/bmap.*` y `dataTool.*`: extensiones incluidas por Apache. BMap necesita el SDK del proveedor para renderizar mapas; no se carga automáticamente.
- `types/`, `LICENSE`, `NOTICE` y otros metadatos originales: conservar al distribuir. El catálogo no modifica Composer ni instala dependencias de Node.

## Ocho series personalizadas oficiales

En `custom-series/` están los paquetes del repositorio `apache/echarts-custom-series`:

| Carpeta | Versión | `renderItem` |
| --- | --- | --- |
| `custom-liquid-fill` | 1.0.2 | `liquidFill` |
| `custom-word-cloud` | 1.0.1 | `wordCloud` |
| `custom-violin` | 1.1.1 | `violin` |
| `custom-contour` | 1.2.1 | `contour` |
| `custom-stage` | 1.1.1 | `stage` |
| `custom-segmented-doughnut` | 1.1.1 | `segmentedDoughnut` |
| `custom-bar-range` | 1.2.1 | `barRange` |
| `custom-line-range` | 1.1.1 | `lineRange` |

Usar el README y tipos del paquete concreto: los contratos `data`, `encode` e `itemPayload` difieren. Para las vistas clásicas cargar su `dist/<nombre>.auto.js` después del núcleo: registra el renderizador sin ejecutar instalaciones ni un build. Las variantes UMD manuales y ESM requieren su método de registro propio. La serie `contour` necesita **D3 7.9.0**, disponible en `dependencies/d3/dist/d3.min.js`, antes de `contour.auto.js`.

## Extensiones JavaScript del catálogo oficial

Se descargaron las extensiones de las categorías de gráficos y herramientas. Son proyectos comunitarios enlazados por Apache, no una garantía de compatibilidad conjunta. Los enlaces a wrappers de React, Angular, Python, R, PHP u otros lenguajes no se instalan: no son extensiones del navegador necesarias para este MVC PHP/jQuery.

| Carpeta en `extensions/` | Versión | Estado/requisitos |
| --- | --- | --- |
| `echarts-gl` | 2.1.0 | Declara ECharts 5/6; renderizado 3D requiere WebGL. |
| `echarts-stat` | 1.2.0 | Herramienta estadística; registrar transformaciones solo donde se usen. |
| `echarts-extension-gmap` | 1.7.0 | Requiere Google Maps API y autorización para ese servicio; descarga local no incluye clave/SDK. |
| `echarts-wordcloud` | 2.1.0 | Declara ECharts 5; para v6 preferir la serie oficial personalizada. |
| `echarts-liquidfill` | 3.1.0 | Declara ECharts 5; para v6 preferir la serie oficial personalizada. |
| `echarts-graph-modularity` | 2.1.0 | Declara ECharts 5; no aprobada para producción con v6. |
| `echarts-leaflet` | 1.1.0 | Declara ECharts 3 y Leaflet 1; legado no activado. |
| `echartslayer` | 0.1.6 | Integración Mapbox para ECharts 3; legado no activado. |
| `arcgis-echarts3` | 0.1.0 | Integración ArcGIS para ECharts 3; legado no activado. |

Ninguna de estas extensiones se incorpora automáticamente a las pantallas existentes. No instalar sus dependencias antiguas o ejecutar scripts/builds por el solo hecho de tenerlas descargadas. No cargar SDK/cartografía externos ni enviar información clínica a esos servicios sin alcance autorizado específico.

## Consumidor y comprobaciones

El consumidor encontrado es `Views/Expediente/expediente.php`, que conserva las rutas `dist/echarts.js` e `i18n/langES.js`, ahora con revisión de contenido. Sus funciones están en `Assets/app/js/expediente.js`; el cambio de tema mantiene dataset, unidades y contexto clínico.

Ejecutar con Playwright/Chrome y las dependencias de pruebas del entorno:

```text
node tests/echarts_vendor.cjs <directorio-de-evidencias>
node tests/expediente_chart_theme.cjs <directorio-de-evidencias>
node tests/expediente_contexto.cjs
```

Las pruebas son aisladas, con datos sintéticos y sin red ni base. El informe de vendor distingue carga/registro de temas y plugins del renderizado y no certifica servicios de mapas. Las distribuciones ESM/UMD y los paquetes conservan sus licencias individuales.

## Respaldo y reversión

Antes de reemplazar se verificó una copia ZIP completa de los 52 archivos existentes, incluidos cambios locales. Nombre y hash del respaldo figuran en `vendor-manifest.json`; la ubicación y hashes originales se guardaron en el directorio de evidencias de esta conversación, en `echarts-backup-manifest.json`. El respaldo no se sirve desde la carpeta pública.

Para revertir, restaurar el conjunto completo del respaldo y retirar los archivos añadidos por esta actualización, sin mezclar versiones. Coordinar cualquier restauración de cambios de aplicación con sus pruebas; no sobrescribir modificaciones posteriores del usuario. Una nueva actualización requiere nueva autorización, respaldo y validación.

Fuentes: https://echarts.apache.org/en/download.html · https://echarts.apache.org/en/download-theme.html · https://echarts.apache.org/en/download-extension.html · https://github.com/apache/echarts-custom-series
