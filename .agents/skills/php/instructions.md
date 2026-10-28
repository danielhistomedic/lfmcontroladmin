# PHP 8.2 — LFM Control
Localizar bootstrap, configuración, autenticación, conexión y helpers. Leer el flujo completo antes de modificar.
Aplicar las reglas PHP, datos, seguridad y UTF-8. Reutilizar la arquitectura real, consultas preparadas y validadores existentes.
Separar validación, lógica comercial y renderizado siguiendo el patrón del módulo. No agregar strict_types globalmente ni alterar firmas sin revisar consumidores.
Manejar null, entradas inválidas, duplicados y errores. No exponer excepciones al navegador.
Conservar permisos, filtros, estatus y compatibilidad. Evitar reintentos de escrituras sin idempotencia; deshabilitar un botón no garantiza una única escritura.
Para optimizar, medir y eliminar trabajo repetitivo antes de cambiar conexiones o infraestructura.
Comprobar php -l en PHP 8.2 y casos funcionales relevantes; informar pruebas pendientes y archivos a reemplazar.

El portal usa MVC propio y autoload del Core más `vendor/autoload.php`, cargado por `Config/Config.php`. Revisar `composer.json` y `composer.lock` de la raíz y conservar el mapeo `Lib\` hacia `Libraries/Custom/` y su classmap. Contrastar el lock con `vendor/composer/`; no ejecutar actualizaciones ni reconstrucciones de vendor automáticamente. Conservar los modelos que extienden `Mysql` mediante la conexión central y la sesión existente; no crear conexiones directas desde vistas o controladores. Revisar propiedades declaradas en las clases modificadas para PHP 8.2.

Documentar obligatoriamente el código nuevo o modificado conforme a la regla PHP 8.2. Usar PHPDoc cuando corresponda para propósito, parámetros, retornos y excepciones propagadas; explicar reglas no evidentes y actualizar comentarios o PHPDoc desfasados en el mismo cambio.

Al homologar vistas, reutilizar los estilos realmente cargados y conservar bloques PHP, metadatos y atributos funcionales de controles. Cambiar asociaciones for/id solo después de verificar el control real; no modificar controladores ni modelos por un ajuste de presentación.
