---
trigger: always_on
---
# Implementación PHP 8.2

Revisar el bootstrap, includes, rutas, configuración, Composer si existe y convenciones actuales antes de editar. Mantener compatibilidad con PHP 8.2 y las extensiones realmente disponibles. No introducir características exclusivas de PHP posterior.
Reutilizar módulos, funciones y clases. Evitar agregar dependencias o reescribir la arquitectura por un cambio puntual. No imponer MVC, Laravel, una SPA o un proceso de compilación nuevo.
Validar entradas con tipos y límites; distinguir null, 0, false y cadena vacía. No confiar en isset/empty como única validación de negocio. Revisar propiedades dinámicas y otras advertencias de PHP 8.2 en código modificado.
Usar excepciones con manejo útil y registro seguro; no ocultar errores con @ ni catch vacío. No exponer SQL, rutas internas o trazas al usuario.
Conservar contratos entre PHP y JavaScript, códigos de estado, nombres de campos y comportamiento de módulos existentes.
Organizar por clases/funciones y secciones funcionales según la convención actual. Si existen comentarios region/endregion, reutilizarlos; no insertar #Region/#End Region VB.NET. Mantener manejadores de eventos JavaScript en su sección propia cuando el archivo siga esa organización. No reorganizar archivos completos sin necesidad.
Resolver rutas de archivos en servidor con el patrón existente y __DIR__ cuando corresponda. Mantener URLs compatibles con despliegue en subdirectorio; no hardcodear C:\xampp ni localhost en código portable. Verificar mayúsculas de rutas para hosting Linux.
Para rendimiento, medir consultas, llamadas HTTP y generación de documentos antes de optimizar. Evitar N+1, cargas completas innecesarias y reintentos ciegos de escrituras.
Validar los PHP modificados con php -l usando PHP 8.2; ejecutar pruebas funcionales pertinentes si se dispone del proyecto y entorno autorizado. Reportar lo no ejecutado.
