# PHP/API — LFM Control
Leer rutas, middleware/helpers, autenticación y consumidores existentes. Documentar método, parámetros, respuesta, errores y permisos del contrato afectado.
Mantener nombres y formatos compatibles. No cambiar de form-urlencoded a JSON, token o envoltorio de respuesta como efecto secundario.
Validar entradas y alcance del usuario en servidor. Aplicar CSRF a autenticación por cookies, parametrizar SQL y no usar GET para escrituras.
Emitir JSON UTF-8 válido con Content-Type y código HTTP coherentes con el contrato. No mezclar HTML, avisos, BOM o trazas en respuestas. Verificar errores de serialización con el mecanismo existente.
Gestionar timeouts, errores externos y reenvíos; no reintentar automáticamente operaciones no idempotentes. No desactivar TLS ni abrir CORS globalmente.
Verificar éxito, datos inválidos, acceso denegado, recurso ajeno, error controlado y compatibilidad del cliente. No llamar APIs de producción sin autorización.
