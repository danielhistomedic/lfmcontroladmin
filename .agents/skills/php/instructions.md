# PHP 8.2 — LFM Control
Localizar bootstrap, configuración, autenticación, conexión y helpers. Leer el flujo completo antes de modificar.
Aplicar las reglas PHP, datos, seguridad y UTF-8. Reutilizar la arquitectura real, consultas preparadas y validadores existentes.
Separar validación, lógica comercial y renderizado siguiendo el patrón del módulo. No agregar strict_types globalmente ni alterar firmas sin revisar consumidores.
Manejar null, entradas inválidas, duplicados y errores. No exponer excepciones al navegador.
Conservar permisos, filtros, estatus y compatibilidad. Evitar reintentos de escrituras sin idempotencia; deshabilitar un botón no garantiza una única escritura.
Para optimizar, medir y eliminar trabajo repetitivo antes de cambiar conexiones o infraestructura.
Comprobar php -l en PHP 8.2 y casos funcionales relevantes; informar pruebas pendientes y archivos a reemplazar.
