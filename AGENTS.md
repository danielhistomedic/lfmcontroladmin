# Agentes y reglas — LFM Control / lfm_admin

## Alcance y contexto
El proyecto es el portal comercial e industrial LFM Control. La aplicación revisada está en `C:\xampp\htdocs\lfm_admin`; esta carpeta de trabajo puede contener únicamente sus instrucciones. Comprobar la raíz que contiene `index.php`, `Controllers/`, `Models/` y `Views/` antes de editar código. No modificar otros proyectos por efecto secundario.
Los módulos comprobados incluyen ventas, seguimiento de proyectos y órdenes, almacén, reportes mensuales, recursos humanos, configuración y seguridad. No aplicar reglas de pacientes, censo, cirugías o expediente clínico a este portal.
Conservar los nombres reales de tablas, clases, campos y contratos compartidos, aunque procedan de otro sistema. El cambio de identidad de las instrucciones no autoriza renombrar identificadores ni migrar datos.
Compatibilidad objetivo: PHP 8.2, MySQL y HTML/CSS/JavaScript. Verificar la versión de MySQL y las extensiones disponibles; no aplicar reglas de WinForms, VB.NET ni Visual Studio al portal PHP.

## Arquitectura comprobada
- Rutas: `.htaccess`, `index.php` y `Libraries/Core/Load.php`; conservar controlador/método/parámetros y compatibilidad con subdirectorios.
- MVC propio: `Controllers/`, `Models/`, `Views/` y autoloads en `Libraries/Core/`. `Config/Config.php` carga `vendor/autoload.php`; Composer declara `Lib\` en `Libraries/Custom/`. Conservar ambas cargas y verificar manifiestos antes de cambiar dependencias.
- Datos: `Libraries/Core/Conexion.php` y `Mysql.php`, reutilizados por los modelos. No asumir conexiones administrativas adicionales ni selección de base por sesión. Identificar el destino real sin exponer credenciales ni conectarse para una revisión documental.
- Sesión y permisos: `Libraries/Core/Session.php`, `Helpers/Helpers.php` y `Config/Modulos.php`. Verificar permiso por acción y alcance de empresa, sucursal, vendedor o almacén según el módulo real.
- Módulos: `MenusModel` consulta `ssf_modulos`; los permisos por rol se consultan mediante `PermisosModel`. Un módulo nuevo requiere un ID comprobado, constante en `Config/Modulos.php`, metadatos coherentes y autorización backend propia. No crear registros ni conceder permisos al abrir páginas.
- Interfaz: `Template/`, `Assets/app/` y `Assets/vendor/`. Reutilizar los componentes cargados por cada plantilla y la tipografía definida en `variables.css`, `typography.css` y `reports.css`. No presuponer hojas ni clases copiadas de otro portal.
- Errores y respuestas: revisar `Helpers/LogErrors.php`, helpers y consumidores JavaScript antes de cambiar contratos.

## Reglas
Leer las reglas relacionadas con el cambio:
- [Acceso a datos](.agents/rules/accesobasedatos.md).
- [Formularios y controles](.agents/rules/controlesforms.md).
- [PHP y organización](.agents/rules/php82.md).
- [Seguridad](.agents/rules/seguridad.md).
- [UTF-8](.agents/rules/utf8.md).
- [Apache ECharts](.agents/rules/echarts.md), para gráficas.
Las instrucciones explícitas del usuario tienen prioridad sobre estas convenciones.

## Procedimiento
1. Leer código, dependencias y contratos vigentes; preservar cambios locales del usuario.
2. Definir el cambio mínimo y comprobar tablas, campos, estatus, filtros y permisos. No restaurar módulos o cambios descartados por referencias del historial.
3. Reutilizar arquitectura y bibliotecas existentes; mantener lógica comercial y compatibilidad con consumidores del portal y clientes de escritorio comprobados.
4. Documentar decisiones y contratos no evidentes dentro del código afectado, conforme a la regla PHP.
5. Validar sintaxis con PHP 8.2 y comportamiento en el entorno autorizado. Separar pruebas ejecutadas, revisión estática y pendientes.
6. Entregar resumen, archivos exactos a copiar/reemplazar, comprobaciones e impactos proporcionales al cambio.

## Perfiles especializados
Leer la entrada y sus instrucciones cuando corresponda:
| Perfil | Alcance | Entrada |
| --- | --- | --- |
| architect | Arquitectura y cambios entre módulos | [.agents/skills/architect/SKILL.md](.agents/skills/architect/SKILL.md) |
| php | Implementación PHP 8.2 | [.agents/skills/php/SKILL.md](.agents/skills/php/SKILL.md) |
| php-api | Acciones AJAX, JSON y contratos HTTP | [.agents/skills/php-api/SKILL.md](.agents/skills/php-api/SKILL.md) |
| mysql | Consultas, esquema y rendimiento | [.agents/skills/mysql/SKILL.md](.agents/skills/mysql/SKILL.md) |
| ui-ux | Formularios, tablas y dashboards | [.agents/skills/ui-ux/SKILL.md](.agents/skills/ui-ux/SKILL.md) |
| echarts | Gráficas Apache ECharts | [.agents/skills/echarts/SKILL.md](.agents/skills/echarts/SKILL.md) |
| security | Sesiones, permisos y archivos | [.agents/skills/security/SKILL.md](.agents/skills/security/SKILL.md) |
| qa | Validación y regresiones | [.agents/skills/qa/SKILL.md](.agents/skills/qa/SKILL.md) |
| dalia | IA y documentos, cuando se solicite | [.agents/skills/dalia/SKILL.md](.agents/skills/dalia/SKILL.md) |
Los perfiles son instrucciones; no crean subagentes ni autorizan delegación automáticamente. Mantener las entradas breves en SKILL.md y el detalle en instructions.md.

## Entornos y datos sensibles
No conectarse a producción, incluso para SELECT o pruebas de conectividad, sin autorización explícita para la tarea. La carpeta XAMPP y las credenciales disponibles no acreditan un entorno de pruebas.
No copiar credenciales, tokens, datos personales, cotizaciones ni documentos comerciales completos a instrucciones o registros. Usar datos sintéticos o anonimizados en pruebas; verificar autorización antes de enviar documentos a servicios externos.
Estas instrucciones orientan el trabajo futuro; no certifican que la aplicación ya cumpla todas las reglas.
