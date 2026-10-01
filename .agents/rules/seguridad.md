---
trigger: always_on
---
# Seguridad web

Mantener la autenticación, sesión y autorización central del portal. Revisar permisos en cada endpoint, incluida descarga de archivos y acceso a registros por ID; ocultar botones no protege el backend.
Aplicar protección CSRF existente en escrituras autenticadas mediante cookies. No ejecutar cambios por GET. Conservar gestión de sesión, regeneración tras autenticación y atributos de cookies según HTTPS y configuración real.
Parametrizar SQL. Escapar datos al renderizar HTML con htmlspecialchars y UTF-8 o equivalente existente; para JavaScript usar serialización JSON segura y para DOM preferir textContent. No concatenar contenido no confiable en innerHTML. El escape contextual de HTML no contradice conservar ñ y acentos en almacenamiento.
No copiar secretos a instrucciones, ejemplos, navegador o logs. Reutilizar configuración segura del servidor. Registrar errores sin contraseñas, tokens, documentos completos ni datos personales innecesarios.
Validar adjuntos por tamaño, extensión permitida y tipo comprobado en servidor. No confiar en MIME/nombre enviados. Usar nombres internos seguros, prevenir traversal y ejecución de subidas; preferir almacenamiento fuera del directorio público y descargas autorizadas. No sobreescribir archivos por nombre del usuario.
Para URLs externas, validar esquema y destinos permitidos; bloquear solicitudes arbitrarias a recursos internos. Aplicar timeout y validación TLS. No activar CORS abierto o deshabilitar autenticación para resolver una integración.
No conectarse a producción sin autorización explícita. Conservar auditoría y validaciones de operaciones críticas.
