# Arquitectura — LFM Control
Identificar objetivo, rutas, archivos, tablas, permisos y consumidores antes de diseñar. Usar el código actual; no restaurar módulos descartados por referencias del historial.
Reutilizar el MVC propio, autoloads, Conexion/Mysql, Session, helpers y plantillas existentes. No asumir conexiones administrativas ni selección de base por sesión. Conservar compatibilidad PHP 8.2 y contratos compartidos; no introducir un framework para una corrección puntual.
Verificar empresa/sucursal y alcance comercial aplicable: vendedor, cliente, proyecto, orden o almacén. Mantener los nombres reales de tablas, campos y estatus aunque tengan origen histórico.
En módulos nuevos comprobar ssf_modulos, su ID, la constante en Config/Modulos.php, navegación y permisos por rol. No usar permisos de otro módulo como reemplazo permanente ni crear registros al abrir páginas.
En reportes definir periodo, estatus, moneda, conversión y cardinalidad. Conciliar tablas, indicadores, gráficas y exportaciones con las mismas reglas. Para gráficas consultar [ECharts](../echarts/SKILL.md).
Documentar contratos y decisiones no evidentes conforme a la regla PHP; validar por alcance y entregar archivos e impactos. Aplicar los perfiles secuencialmente salvo delegación autorizada.

En módulos existentes y nuevos que generen presentación, aplicar la [fuente estándar Arial, Helvetica, sans-serif](../../rules/controlesforms.md#tipografia-estandar). Reutilizar la carga y los recursos comunes; no crear excepciones tipográficas por módulo ni modificar fuentes de iconos.

Al crear o modificar módulos con formularios, incluidos accesos independientes y Login, aplicar el [estándar común de labels](../../rules/controlesforms.md#labels-estandar). Reutilizar form-labels.css mediante Template/font_family.php; no añadir variantes por módulo ni cambiar contratos, for/id o controles por homologar etiquetas.

En módulos existentes y nuevos con captura o filtros, incluido Login, aplicar el [estándar común de campos](../../rules/controlesforms.md#campos-estandar). Reutilizar form-controls.css, sus variables y la carga compartida; no crear tamaños por módulo ni modificar contratos, tipos, valores o validaciones por un cambio visual.
Al crear o modificar vistas, incluido Login, garantizar la carga del estándar común: input, textarea y select/Select2 con fondo blanco normal, borde neutro y sin sombra; borde azul claro y sombra suave solo con foco (o Select2 abierto). Conservar texto legible en ambos temas y los estados de validación y readonly/disabled según la regla central.
