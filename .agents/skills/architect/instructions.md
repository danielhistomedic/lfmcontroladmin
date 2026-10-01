# Arquitectura — LFM Control
Leer código y dependencias antes de diseñar. Identificar objetivo, archivos, tablas, endpoints, permisos y consumidores compartidos.
Diseñar la solución mínima compatible con PHP 8.2 y la arquitectura instalada. No imponer un framework ni cambiar contratos existentes sin evaluar clientes del portal y del escritorio.
Verificar nombres reales; si falta un dato crítico, avanzar en lo independiente y pedir únicamente lo indispensable.
Para varias capas, definir contrato y reglas de datos antes de implementación; revisar permisos, estados parciales y concurrencia.
Aplicar perfiles de PHP, API, MySQL, UI, seguridad y QA según alcance. Los perfiles no obligan a crear subagentes; usar trabajo secuencial si no hay delegación autorizada.
Priorizar estabilidad, seguridad, compatibilidad, rendimiento y mantenibilidad. No eliminar funciones ni cambiar componentes centrales protegidos sin autorización.
Entregar archivos afectados, solución y evidencia de validación, con extensión proporcional al cambio.
