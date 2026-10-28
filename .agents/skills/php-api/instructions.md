# PHP/API — LFM Control
Leer rutas, helpers, autenticación y consumidores existentes. El router del Core despacha métodos de controladores; no asumir middleware central que ya verifique método HTTP, CSRF o permisos. Comprobar cada acción y que una redirección por acceso denegado detenga su ejecución. Documentar método, parámetros, respuesta, errores y permisos del contrato afectado.
Mantener nombres y formatos compatibles. No cambiar de form-urlencoded a JSON, token o envoltorio de respuesta como efecto secundario.
Validar entradas y alcance del usuario en servidor. Aplicar CSRF a autenticación por cookies, parametrizar SQL y no usar GET para escrituras.
Emitir JSON UTF-8 válido con Content-Type y código HTTP coherentes con el contrato. No mezclar HTML, avisos, BOM o trazas en respuestas. Verificar errores de serialización con el mecanismo existente.
Gestionar timeouts, errores externos y reenvíos; no reintentar automáticamente operaciones no idempotentes. No desactivar TLS ni abrir CORS globalmente.
Verificar éxito, datos inválidos, acceso denegado, recurso ajeno, error controlado y compatibilidad del cliente. No llamar APIs de producción sin autorización.

Las acciones se enrutan mediante `index.php?url=controlador/metodo/parametros`; no asumir un router REST independiente. Revisar `getResponse` y el JavaScript consumidor antes de modificar claves como `respuesta`, `mensaje`, `mostrar_mensaje`, `tiempo` o `data`. Conservar POST/formularios e identificadores cifrados cuando sean parte del contrato; el cifrado no sustituye permisos.

Documentar obligatoriamente acciones nuevas o modificadas mediante PHPDoc cuando corresponda: método HTTP, entradas, permisos, respuesta y errores reales. Mantener comentarios y ejemplos del contrato coherentes con los consumidores, retirando los desfasados conforme a la regla PHP 8.2.
