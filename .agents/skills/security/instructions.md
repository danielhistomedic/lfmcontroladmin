# Seguridad — LFM Control
Leer seguridad.md y revisar el flujo real de autenticación y autorización. Reutilizar helpers existentes.
Verificar permisos backend por acción y por registro en páginas, endpoints, descargas y adjuntos. Revisar manipulación de ID y perfil.
Comprobar SQL parametrizado, salida escapada por contexto y CSRF donde aplique. No debilitar seguridad para resolver fallos funcionales.
Revisar sesiones y cookies según despliegue real sin cambios globales innecesarios.
Comprobar límites y validación de archivos, rutas seguras y prevención de ejecución/traversal. Examinar URLs externas y exposición de secretos en logs/respuestas.
Proponer la corrección mínima y validar en desarrollo con datos de prueba. No ejecutar ataques, escaneos o pruebas contra producción sin autorización explícita.

Revisar `Libraries/Core/Session.php`, los helpers de permisos y `Config/Modulos.php`. Comprobar límites de empresa/sucursal, vendedor, cliente, proyecto, pedido o almacén según el flujo. Identificadores cifrados y `strClean` no reemplazan autorización. Proteger información comercial y personal y verificar si existe protección CSRF real. Revisar que el permiso corresponda al módulo y a la acción; no copiar autorizaciones comentadas ni usar lectura para escrituras. Verificar el fin de ejecución tras rechazar acceso.
En reemplazo de adjuntos, guardar y validar el archivo nuevo y confirmar la actualización antes de eliminar el anterior; prever limpieza del nuevo si falla la operación para evitar pérdida de datos.

Documentar obligatoriamente los cambios de seguridad en código nuevo o modificado: explicar controles y restricciones no evidentes sin exponer secretos ni datos de clientes, proveedores y personal. Usar PHPDoc cuando corresponda y retirar o actualizar comentarios que atribuyan al código protecciones que no implemente, conforme a la regla PHP 8.2.
