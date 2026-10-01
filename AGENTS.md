# Agentes y reglas — LFM Control / lfm_admin

## Alcance y contexto
Trabajar en el portal PHP de LFM Control ubicado en `C:\xampp\htdocs\lfm_admin`. El proyecto de escritorio indicado por el usuario es únicamente la referencia de origen de estas instrucciones; no modificarlo como efecto secundario.
El portal corresponde al sistema comercial e industrial LFM Control. Mantener los nombres reales de tablas, clases y contratos compartidos aunque contengan referencias históricas a HistoMedic.
Compatibilidad objetivo: PHP 8.2, MySQL y HTML/CSS/JavaScript. No imponer reglas de Visual Studio, WinForms, DotNetBar ni sintaxis VB.NET al portal.

## Lectura obligatoria
Leer y aplicar las reglas antes de editar:
- [Acceso a datos](.agents/rules/accesobasedatos.md).
- [Formularios y controles web](.agents/rules/controlesforms.md).
- [PHP y organización](.agents/rules/php82.md).
- [Seguridad](.agents/rules/seguridad.md).
- [UTF-8](.agents/rules/utf8.md).
Las reglas se aplican en todo el repositorio según la operación. Las instrucciones explícitas del usuario tienen prioridad sobre estas convenciones.

## Procedimiento
1. Leer el código afectado y sus dependencias. Localizar rutas, configuración, conexión, autenticación, permisos, plantillas y componentes reales.
2. Definir el cambio mínimo y verificar tablas, campos, estatus y contratos en el código o esquema disponible. No inventar recursos.
3. Reutilizar la arquitectura y bibliotecas instaladas. No introducir un framework, capa de datos o dependencia para una corrección puntual.
4. Implementar manteniendo la lógica comercial, filtros por usuario y compatibilidad de APIs con clientes existentes.
5. Validar sintaxis y comportamiento en un entorno de desarrollo autorizado. Distinguir pruebas ejecutadas de revisiones estáticas o pruebas pendientes.
6. Entregar un resumen del cambio, archivos exactos a copiar/reemplazar, comprobaciones realizadas e impactos relevantes. Ajustar el detalle al alcance.

## Perfiles especializados
Leer el SKILL.md y sus instrucciones cuando corresponda:
| Perfil | Alcance | Entrada |
| --- | --- | --- |
| architect | Arquitectura, dependencias y cambios entre módulos | [.agents/skills/architect/SKILL.md](.agents/skills/architect/SKILL.md) |
| php | Implementación y rendimiento PHP 8.2 | [.agents/skills/php/SKILL.md](.agents/skills/php/SKILL.md) |
| php-api | Endpoints, JSON y contratos REST | [.agents/skills/php-api/SKILL.md](.agents/skills/php-api/SKILL.md) |
| mysql | SQL, índices y scripts de esquema | [.agents/skills/mysql/SKILL.md](.agents/skills/mysql/SKILL.md) |
| ui-ux | Formularios, tablas y dashboards web | [.agents/skills/ui-ux/SKILL.md](.agents/skills/ui-ux/SKILL.md) |
| security | Sesiones, permisos y archivos | [.agents/skills/security/SKILL.md](.agents/skills/security/SKILL.md) |
| qa | Pruebas y regresiones | [.agents/skills/qa/SKILL.md](.agents/skills/qa/SKILL.md) |
| dalia | Integración de IA y análisis de documentos | [.agents/skills/dalia/SKILL.md](.agents/skills/dalia/SKILL.md) |
Los perfiles son instrucciones de especialidad; no crean agentes automáticamente. Aplicar las fases secuencialmente si no hay delegación autorizada. Mantener instrucciones detalladas en instructions.md y entradas de descubrimiento en SKILL.md.

## Entornos y datos sensibles
No conectarse a producción, ni siquiera para SELECT o pruebas de conectividad, sin autorización explícita para esa tarea. No copiar credenciales a reglas, ejemplos, respuestas ni registros. La carpeta XAMPP no garantiza que su conexión sea local.
