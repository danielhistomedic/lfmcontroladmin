---
trigger: always_on
---
# Implementación PHP 8.2

Revisar el bootstrap, includes, rutas, configuración, `composer.json`, `composer.lock` y convenciones actuales antes de editar. Mantener compatibilidad con PHP 8.2 y las extensiones realmente disponibles. No introducir características exclusivas de PHP posterior.
Reutilizar módulos, funciones y clases. Evitar agregar dependencias o reescribir la arquitectura por un cambio puntual. Conservar el MVC propio; no introducir Laravel, una SPA o un proceso de compilación nuevo.
Validar entradas con tipos y límites; distinguir null, 0, false y cadena vacía. No confiar en isset/empty como única validación de negocio. Revisar propiedades dinámicas y otras advertencias de PHP 8.2 en código modificado.
Usar excepciones con manejo útil y registro seguro; no ocultar errores con @ ni catch vacío. No exponer SQL, rutas internas o trazas al usuario.
Conservar contratos entre PHP y JavaScript, códigos de estado, nombres de campos y comportamiento de módulos existentes.
Organizar por clases/funciones y secciones funcionales según la convención actual. Si existen comentarios region/endregion, reutilizarlos; conservar comentarios PHP válidos. Mantener manejadores de eventos JavaScript en su sección propia cuando el archivo siga esa organización. No reorganizar archivos completos sin necesidad.
Resolver rutas de archivos en servidor con el patrón existente y __DIR__ cuando corresponda. Mantener URLs compatibles con despliegue en subdirectorio; no hardcodear C:\xampp ni localhost en código portable. Verificar mayúsculas de rutas para hosting Linux.
Para rendimiento, medir consultas, llamadas HTTP y generación de documentos antes de optimizar. Evitar N+1, cargas completas innecesarias y reintentos ciegos de escrituras.
Validar los PHP modificados con php -l usando PHP 8.2; ejecutar pruebas funcionales pertinentes si se dispone del proyecto y entorno autorizado. Reportar lo no ejecutado.

Verificar la versión efectiva del ejecutable CLI antes del lint: en este equipo puede usarse `C:\xampp\php\php.exe` si existe y corresponde a PHP 8.2. Para un barrido estático puede usarse `php -n -l archivo.php`, indicando que no carga php.ini ni verifica extensiones. No ejecutar `index.php`, incluir configuración ni instanciar modelos para comprobar sintaxis: pueden iniciar sesiones o conexiones. El lint no demuestra compatibilidad funcional de dependencias ni del servidor web.
En código afectado revisar propiedades declaradas, paso de null a funciones internas que esperan cadenas y funciones obsoletas; no ocultar avisos ni agregar `AllowDynamicProperties` indiscriminadamente.

## Composer y plataforma
PHP 8.2 es la base del proyecto aunque el manifiesto no declare `require.php` o `config.platform.php`. No deducir compatibilidad funcional del mínimo PHP declarado por una dependencia. Antes de cambiar dependencias, contrastar manifiesto, lock, paquetes instalados y extensiones de la plataforma autorizada; conservar el lock en despliegues reproducibles. No ejecutar `composer update`, scripts o plugins como parte de una revisión documental.

## Documentación del código
Es obligatorio documentar el código nuevo o modificado y mantener su documentación coherente con el comportamiento final. Escribir en español claro, conservando identificadores y terminología técnica reales.
- Usar PHPDoc en clases, funciones y métodos nuevos o modificados cuando corresponda: describir propósito, reglas relevantes y contrato que la firma no exprese por sí sola. Documentar parámetros y retornos con `@param` y `@return` cuando aporten información, incluyendo estructura de arrays, valores especiales o resultados de error; usar `@throws` solo para excepciones que realmente puedan propagarse.
- Explicar con comentarios las decisiones, restricciones, permisos, efectos secundarios y reglas de negocio no evidentes del código afectado. En SQL, JavaScript, HTML y CSS usar el formato nativo; emplear JSDoc cuando corresponda a funciones o contratos JavaScript.
- Actualizar o retirar comentarios y PHPDoc desfasados al cambiar firmas, lógica, respuestas, permisos o manejo de errores. No conservar ejemplos ni descripciones que contradigan la implementación.
- Mantener la documentación breve y útil: no comentar cada línea ni repetir operaciones obvias. El código sencillo puede documentarse con una descripción corta de propósito o del cambio cuando sea suficiente. No inventar contratos, garantías, mediciones o validaciones que el código no implemente.
- Documentar dentro del alcance del cambio, sin reescribir archivos completos ni modificar dependencias de terceros. No incluir credenciales, tokens, información comercial o personal identificable ni datos reales en comentarios o ejemplos.
Antes de entregar, revisar que los comentarios y PHPDoc describan la versión final del código. El lint no verifica esta coherencia; comprobarla mediante revisión del flujo y sus consumidores.
