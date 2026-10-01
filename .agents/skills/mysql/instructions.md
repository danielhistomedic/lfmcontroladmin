# MySQL — LFM Control
Leer accesobasedatos.md y verificar esquema/versión en archivos o desarrollo autorizado. No conectarse a producción para descubrirlos.
Analizar WHERE, JOIN, GROUP BY, ORDER BY, cardinalidad, NULL e índices. Evitar N+1, SELECT * innecesario y funciones que impidan índices.
Comprobar cardinalidad antes de agregar DISTINCT o sumar; preservar partidas y relaciones correctas. Revisar EXPLAIN solo en desarrollo autorizado y si aporta evidencia.
Parametrizar valores desde la capa PHP existente y permitir identificadores dinámicos únicamente mediante lista cerrada.
Verificar índices existentes antes de proponer nuevos. No sustituir LIKE por FULLTEXT sin evaluar semántica y compatibilidad.
Para esquema entregar script separado, impacto en PHP y consumidores del escritorio y reversión posible. No convertir motores ni tipos/charset automáticamente.
Reportar evidencia real y límites; no afirmar rendimiento medido si solo hubo análisis estático.
