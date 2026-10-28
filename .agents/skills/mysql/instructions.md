# MySQL — LFM Control
Aplicar la regla de acceso a datos y comprobar esquema y versión en archivos o un entorno autorizado. Las credenciales no autorizan consultas a producción.
Reutilizar Libraries/Core/Conexion.php y Mysql.php y verificar sus firmas reales. No asumir Mysqladmin ni selección de base por sesión. Parametrizar valores y usar listas permitidas para columnas y orden.
Conservar restricciones de empresa, sucursal, vendedor y almacén aunque la búsqueda esté vacía. Revisar cardinalidad antes de sumar ventas, cotizaciones, pedidos y partidas. Distinguir proyecto, venta y documento; no ocultar JOIN incorrectos con DISTINCT.
Comprobar fechas, estatus, cancelaciones, monedas y conversión. Para módulos verificar ssf_modulos, constantes y permisos antes de asignar IDs. No registrar módulos ni conceder permisos mediante GET.
Verificar índices antes de proponer cambios y medir EXPLAIN solo en un entorno autorizado. Entregar SQL separado, impacto y reversión viable; no convertir motores o charset como efecto secundario.
Documentar relaciones y filtros no evidentes y distinguir revisión estática, consultas ejecutadas y rendimiento medido.
