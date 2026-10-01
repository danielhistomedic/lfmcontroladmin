# Seguridad — LFM Control
Leer seguridad.md y revisar el flujo real de autenticación y autorización. Reutilizar helpers existentes.
Verificar permisos backend por acción y por registro en páginas, endpoints, descargas y adjuntos. Revisar manipulación de ID y perfil.
Comprobar SQL parametrizado, salida escapada por contexto y CSRF donde aplique. No debilitar seguridad para resolver fallos funcionales.
Revisar sesiones y cookies según despliegue real sin cambios globales innecesarios.
Comprobar límites y validación de archivos, rutas seguras y prevención de ejecución/traversal. Examinar URLs externas y exposición de secretos en logs/respuestas.
Proponer la corrección mínima y validar en desarrollo con datos de prueba. No ejecutar ataques, escaneos o pruebas contra producción sin autorización explícita.
