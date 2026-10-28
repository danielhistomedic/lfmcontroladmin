---
trigger: always_on
---
# Formularios y controles web — LFM Control

## Recursos e identidad
Tomar como referencia las vistas vigentes y Template. Reutilizar layout, menú, encabezados, paleta, iconos y las versiones de Bootstrap, jQuery, Select2 y DataTables realmente cargadas.
La tipografía de reportes está centralizada en Assets/app/css/variables.css, typography.css y reports.css; --font-family-report usa Inter y alternativas. Reutilizar sus variables de tamaños, pesos, espaciados y tablas. No imponer Arial global, clases hm-* ni hojas de otro proyecto. Limitar estilos particulares al módulo; no descargar fuentes ni añadir frameworks por una homologación.
Revisar datatable-style.css y select2-style.css donde estén cargados. No asumir helpers de layout, form-standard.css, module-header.css ni tests inexistentes.

## Formularios y acciones
Conservar IDs, nombres, métodos, rutas, eventos, campos ocultos, CSRF, validaciones y contratos PHP/JavaScript. Mantener etiquetas visibles, foco, acceso por teclado y valores tras errores. Verificar el control real antes de cambiar for/id.
Distinguir la acción principal y reutilizar avisos/modales existentes. No asumir nombres de helpers de otros sistemas. Mostrar acciones según permisos y comprobarlos en backend. Confirmar operaciones destructivas; cerrar un modal no equivale a cancelar un documento.
Agrupar por flujo comercial; no trasladar controles de escritorio ni reglas clínicas al portal.

## Tablas, filtros y reportes
Conservar filtros, selección, orden, paginación y exportaciones autorizadas. Comprobar columnas y alineación al mostrar pestañas, modales o paneles ocultos; ajustar cuando el contenedor sea visible usando la API instalada, sin duplicar instancias, listeners ni peticiones.
No exportar HTML de acciones ni datos fuera del alcance autorizado. Mantener estados vacío, carga y error; no ocultar fallos de consulta con ceros.
Alinear importes y cantidades e indicar moneda y unidad. Separar monedas o usar la conversión comercial verificada; no sumar unidades incompatibles ni inventar tipos de cambio. Ordenar fechas por su valor real.
Distinguir cotizado, colocado, pedido, facturado y cancelado según campos y estatus reales. Usar alcance coherente de fechas y vendedor en indicadores, gráficas, tablas y exportaciones y explicar diferencias intencionales. Evitar duplicar proyectos o partidas por JOIN.
Mostrar progreso real, deshabilitar temporalmente acciones en proceso y restituirlas ante error. No sustituir prevención backend de duplicados por un botón deshabilitado.

## Menú y módulos
Reutilizar Template/aside.php y Config/Modulos.php. Menú y página deben verificar el permiso de lectura propio; conservar expansión del padre y submenú activo.
Cuando corresponda y el entorno esté autorizado, comprobar nombre, descripción, etiquetas, breadcrumb, vista y recursos de ssf_modulos. No registrar módulos ni conceder permisos al renderizar vistas.

## Accesibilidad y adaptación
Comprobar escritorio, tablet y móvil, con scroll local en tablas anchas y acciones esenciales accesibles. Mantener nombres accesibles, foco y gestión de foco de modales; no depender solo del color para estatus o errores.
Respetar temas soportados y contraste de al menos 4.5:1 para texto normal sobre su fondo efectivo. Cubrir hover, foco, selección, validación, disabled/readonly, menús y componentes dinámicos. No aplicar filtros globales de brillo ni recolorear logos; conservar filtros y valores al alternar tema.
Para ECharts aplicar [la regla de gráficas](echarts.md); el CSS exterior no garantiza que el canvas cambie de tema.

## Validación
Usar datos sintéticos para revisar disposición, filtros, controles, acentos, estados, temas y exportaciones. Ejecutar pruebas disponibles del módulo y separar validación estática de funcional. Una corrección visual no autoriza conectar a producción ni cambiar SQL o lógica comercial.
