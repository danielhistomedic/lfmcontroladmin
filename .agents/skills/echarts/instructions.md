# Apache ECharts — LFM Control
Aplicar [la regla de gráficas](../../rules/echarts.md) y revisar vistas y JavaScript del módulo. Verificar versión y recursos realmente presentes en Assets/vendor/echarts; no asumir manifiestos, extensiones ni APIs de otro proyecto.
Conservar un solo núcleo por página y cargar idioma y temas después. Elegir dataset, series, ejes, unidades y tooltip según el reporte. Diferenciar cotizado y colocado y conciliar desgloses con indicadores.
No añadir stack, smooth, connectNulls o escalas de una demostración si alteran el significado. Diferenciar cero, ausencia y error. Escapar texto no confiable en tooltips HTML.
Gestionar una instancia por nodo, limpieza y respuestas antiguas al cambiar filtros. Inicializar con dimensiones válidas y redimensionar al mostrar paneles sin listeners duplicados ni bucles.
Adaptar el tema en las opciones sin consultar de nuevo datos; comprobar ejes, leyenda, tooltip y herramientas. Proporcionar valores legibles además de la gráfica cuando corresponda.
Validar con datos sintéticos en escritorio/móvil y temas soportados: renderizado, interacción, actualización, vacío, resize y panel oculto. Usar pruebas existentes del módulo; una prueba de carga no acredita consultas, permisos ni servicios externos.

Usar la [fuente estándar Arial, Helvetica, sans-serif](../../rules/controlesforms.md#tipografia-estandar) en gráficas existentes y nuevas. Obtener la familia del documento y aplicarla a textStyle.fontFamily y a las opciones específicas pertinentes; no cambiar tamaños, datos o temas para unificar la familia.
