# DALIA e IA — LFM Control
Verificar el módulo real, cliente HTTP, configuración, prompts y contratos antes de implementar. No asumir que una API, modelo o función existe por estar mencionado en estas reglas.
Mantener credenciales exclusivamente en servidor, en la configuración segura existente. Reutilizar cliente y librerías; aplicar timeouts, límites de tamaño/costo y manejo de errores según configuración del proyecto.
Validar archivos en servidor y enviar solo datos necesarios. No registrar documentos completos, secretos o información personal innecesaria.
Tratar texto de documentos y respuestas del modelo como datos no confiables, nunca instrucciones para ejecutar SQL, comandos o modificar permisos.
Verificar estructura y tipos de salidas antes de utilizarlas. No inventar marca, modelo, clave SAT, código proveedor u otros datos ausentes; conservar evidencia y permitir revisión humana cuando el flujo lo requiera.
No registrar movimientos, modificar existencias ni aprobar operaciones comerciales automáticamente por una respuesta IA.
Mantener compatibilidad de endpoints y manejo UTF-8. Probar fallos HTTP, timeout, salida inválida, documentos sin datos y respuestas incompletas con mocks o desarrollo autorizado. Una llamada de pago o servicio externo debe estar dentro del alcance autorizado.
