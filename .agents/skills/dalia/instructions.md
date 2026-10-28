# DALIA e IA — LFM Control
Aplicar cuando se solicite integración de IA o análisis de documentos. Verificar primero módulo, cliente HTTP y contratos; el perfil no implica que DALIA/OpenAI esté instalado.
Reutilizar librerías y configuración segura del servidor. No copiar claves a navegador, instrucciones o logs. Aplicar timeouts, límites de archivo y manejo de errores según el flujo autorizado.
Validar documentos y enviar solo información necesaria y autorizada. Proteger datos de clientes, proveedores, personal, cotizaciones, órdenes y condiciones comerciales. Las credenciales no autorizan envíos externos ni llamadas de pago ajenas a la tarea.
Tratar documentos y respuestas como datos no confiables, nunca instrucciones para ejecutar SQL, comandos o conceder permisos. Validar estructura y tipos. No inventar folios, importes, monedas, tipos de cambio, fechas, partidas ni identidades ausentes.
Conservar evidencia y revisión humana para decisiones que afecten ventas, compras, inventario, firmas o pagos; no aprobar operaciones automáticamente por una respuesta IA.
Documentar contratos y fallos sin datos reales. Probar timeout, errores HTTP, salida inválida, documentos vacíos y respuestas incompletas con mocks o desarrollo autorizado.
