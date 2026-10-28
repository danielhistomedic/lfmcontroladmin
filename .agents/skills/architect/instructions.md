# Arquitectura — LFM Control
Identificar objetivo, rutas, archivos, tablas, permisos y consumidores antes de diseñar. Usar el código actual; no restaurar módulos descartados por referencias del historial.
Reutilizar el MVC propio, autoloads, Conexion/Mysql, Session, helpers y plantillas existentes. No asumir conexiones administrativas ni selección de base por sesión. Conservar compatibilidad PHP 8.2 y contratos compartidos; no introducir un framework para una corrección puntual.
Verificar empresa/sucursal y alcance comercial aplicable: vendedor, cliente, proyecto, orden o almacén. Mantener los nombres reales de tablas, campos y estatus aunque tengan origen histórico.
En módulos nuevos comprobar ssf_modulos, su ID, la constante en Config/Modulos.php, navegación y permisos por rol. No usar permisos de otro módulo como reemplazo permanente ni crear registros al abrir páginas.
En reportes definir periodo, estatus, moneda, conversión y cardinalidad. Conciliar tablas, indicadores, gráficas y exportaciones con las mismas reglas. Para gráficas consultar [ECharts](../echarts/SKILL.md).
Documentar contratos y decisiones no evidentes conforme a la regla PHP; validar por alcance y entregar archivos e impactos. Aplicar los perfiles secuencialmente salvo delegación autorizada.
