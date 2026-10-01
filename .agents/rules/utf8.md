---
trigger: always_on
---
# Codificación UTF-8

Guardar archivos nuevos o modificados en UTF-8, preferentemente sin BOM en PHP para no emitir salida antes de headers. Conservar ñ, Ñ, acentos y signos españoles; revisar que no aparezcan caracteres corruptos.
Declarar UTF-8 en HTML y respuestas HTTP/JSON por el mecanismo existente. No emitir avisos PHP, espacios o HTML antes de una respuesta JSON o binaria.
Verificar la cadena completa: archivo, entrada HTTP, conexión MySQL, columnas, salida y navegador. Preferir utf8mb4 para recursos nuevos cuando el servidor/esquema lo soporten; no modificar globalmente la conexión protegida ni convertir tablas legacy sin análisis y autorización.
No aplicar utf8_encode/utf8_decode como solución automática; están obsoletas en PHP 8.2. Convertir con origen conocido y herramientas disponibles únicamente cuando exista un desajuste comprobado. No realizar conversiones dobles.
Escapar HTML según contexto en la salida, manteniendo datos originales en almacenamiento. Revisar JSON, CSV, XML, correos y PDF cuando estén dentro del alcance. No alterar archivos firmados fiscalmente mediante recodificación sin evaluar su integridad.
