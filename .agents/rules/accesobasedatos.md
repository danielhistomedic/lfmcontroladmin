---
trigger: always_on
---
# Acceso a datos — PHP y MySQL

## Conexión existente
Localizar el archivo de configuración y el mecanismo central de conexión real del portal. No asumir host, puerto, usuario, contraseña ni nombre de base por las instrucciones del proyecto de escritorio.
Reutilizar la conexión, repositorio o helper existente en todas las operaciones. Conservar PDO y sus consultas preparadas; si utiliza otro mecanismo, utilizar su API segura sin crear una conexión paralela ni migrar toda la capa como efecto secundario.
Proteger datos de conexión: no reemplazarlo, refactorizarlo o cambiar su contrato sin autorización específica. Usar sus métodos existentes.
No cambiar conexiones persistentes, charset o atributos globales sin evaluar impacto y autorización cuando afecte el componente protegido.

## Consultas y permisos
Parametrizar los valores de SELECT, INSERT, UPDATE y DELETE. Validar tipos, límites, NULL y cadenas vacías. Los nombres de columnas, tablas y direcciones ORDER BY requieren una lista permitida; no se parametrizan como valores.
Aplicar los permisos y el alcance del usuario autenticado en el servidor. No confiar en identificadores o perfiles enviados por el navegador. Verificar la propiedad del registro y los filtros por vendedor, zona o almacén que correspondan.
Usar campos necesarios, paginación y orden determinista. Evitar consultas N+1, JOIN que multipliquen partidas y sumas duplicadas. No usar DISTINCT para ocultar una relación incorrecta.

## Eliminar registros
Reutilizar la autorización de eliminación existente en el backend y comprobar permisos antes de DELETE. La confirmación visual no reemplaza esta comprobación. Proteger la petición con el mecanismo CSRF del portal cuando la autenticación sea por cookies.
No eliminar mediante GET. Conservar cancelación/baja lógica, auditoría y dependencias cuando sean reglas del módulo.

## Cambios de esquema
Verificar tablas, columnas, índices, motor y versión de MySQL antes de generar scripts. PHP 8.2 no implica MySQL 8.
Entregar SQL separado, impacto y reversión viable. No ejecutar scripts automáticamente contra producción. Evaluar consumidores del escritorio y portal antes de cambiar tipos o nombres compartidos.

## Producción
No conectarse a bases, APIs, FTP, recursos compartidos, servicios ni archivos de producción sin autorización explícita para la tarea, incluso para lecturas o diagnóstico. Las credenciales disponibles no autorizan su uso. Si el entorno es desconocido, no conectarse hasta aclararlo. Utilizar únicamente desarrollo/pruebas autorizados.
