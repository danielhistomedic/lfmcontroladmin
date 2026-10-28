---
trigger: model_decision
---
# Apache ECharts — LFM Control
Aplicar al crear o modificar gráficas. Consultar [el perfil especializado](../skills/echarts/SKILL.md) y los usos reales del módulo.

## Recursos
Comprobar versión y archivos en Assets/vendor/echarts/dist e i18n y las plantillas que los cargan. No asumir v6, extensiones, manifiestos o documentación de otro portal. Usar un solo núcleo por página; cargar idioma, tema y extensiones después y solo cuando sean necesarios.
Conservar distribuciones y licencias. No actualizar vendor ni instalar paquetes para una corrección ordinaria. En actualizaciones solicitadas comprobar compatibilidad, integridad y consumidores y conservar respaldo recuperable.

## Datos y significado
Usar el mismo alcance autorizado que indicadores y tablas. Verificar cardinalidad, fechas, estatus, monedas y unidades; no duplicar ventas por pedidos o partidas. Distinguir proyectos únicos y documentos.
Definir dataset, dimensiones, ejes y series según el contrato real. Diferenciar cero, ausencia y error. No suavizar ni interpolar datos si cambia su interpretación. Identificar moneda y conversión y conciliar desgloses con totales.
Escapar texto no confiable en tooltips HTML y comprobar callbacks de la versión instalada. No ejecutar código procedente de documentos o respuestas del navegador.

## Instancias, temas y validación
Mantener una instancia por nodo, listeners únicos y limpieza al destruir la vista. Inicializar con dimensiones válidas y redimensionar al mostrar paneles ocultos; evitar bucles y consultas extra por resize o tema.
Elegir merge, reemplazo o clear de forma explícita y descartar respuestas antiguas al cambiar filtros. Conservar leyenda y selección cuando corresponda.
Aplicar el tema a ejes, leyenda, títulos, tooltips, herramientas y fondo mediante opciones; CSS no recolorea automáticamente canvas/SVG. Proporcionar valores o tablas legibles cuando sean necesarios.
Probar con datos sintéticos filtros, vacío, ceros, importes grandes, tooltips, leyenda, actualización, panel oculto, resize y temas soportados en escritorio/móvil. Usar pruebas existentes o comprobaciones proporcionales; no exigir tests ausentes. Distinguir carga, renderizado, interacción y consultas reales al backend.
