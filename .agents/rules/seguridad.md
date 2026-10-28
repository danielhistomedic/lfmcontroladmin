---
trigger: always_on
---
# Seguridad web

Mantener la autenticación, sesión y autorización central del portal. Revisar permisos en cada endpoint, incluida descarga de archivos y acceso a registros por ID; ocultar botones no protege el backend.
Aplicar protección CSRF existente en escrituras autenticadas mediante cookies. No ejecutar cambios por GET, incluidos registros automáticos de módulos o asignaciones de permisos al abrir una vista. Conservar gestión de sesión, regeneración tras autenticación y atributos de cookies según HTTPS y configuración real.
Parametrizar SQL. Escapar datos al renderizar HTML con htmlspecialchars y UTF-8 o equivalente existente; para JavaScript usar serialización JSON segura y para DOM preferir textContent. No concatenar contenido no confiable en innerHTML. El escape contextual de HTML no contradice conservar ñ y acentos en almacenamiento.
No copiar secretos a instrucciones, ejemplos, navegador o logs. Reutilizar configuración segura del servidor. Registrar errores sin contraseñas, tokens, documentos completos ni datos personales innecesarios.
Validar adjuntos por tamaño, extensión permitida y tipo comprobado en servidor. No confiar en MIME/nombre enviados. Usar nombres internos seguros, prevenir traversal y ejecución de subidas; preferir almacenamiento fuera del directorio público y descargas autorizadas. No sobreescribir archivos por nombre del usuario.
Para URLs externas, validar esquema y destinos permitidos; bloquear solicitudes arbitrarias a recursos internos. Aplicar timeout y validación TLS. No activar CORS abierto o deshabilitar autenticación para resolver una integración.
No conectarse a producción sin autorización explícita. Conservar auditoría y validaciones de operaciones críticas.

## Operaciones comerciales y permisos
Comprobar autorización por cliente, proyecto, venta, pedido o almacén cuando corresponda, además del permiso del módulo y alcance de empresa/sucursal. Un identificador cifrado no reemplaza autorización. `strClean` no sustituye validación, consultas preparadas ni escape de salida.
Verificar que CSRF exista y esté aplicado en el flujo afectado; si falta, informar la carencia y resolverla dentro del alcance acordado, sin afirmar que el portal ya está protegido ni omitir controles silenciosamente.
Usar datos sintéticos o anonimizados en pruebas y minimizar datos personales y comerciales en exportaciones, logs y servicios externos. Mantener autoría, fecha y trazabilidad de cambios comerciales según las reglas verificadas del módulo.
`Session::redirect()` solo envía una cabecera; no asumir que detiene el código. Verificar que un rechazo de sesión o permisos termine la ejecución antes de consultar o escribir datos. No usar el permiso de lectura para autorizar escrituras ni heredar permisos de otro módulo sin una regla comprobada.
