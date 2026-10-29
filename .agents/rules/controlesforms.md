---
trigger: always_on
---
# Formularios y controles web — LFM Control

## Recursos e identidad
Tomar como referencia las vistas vigentes y Template. Reutilizar layout, menú, encabezados, paleta, iconos y las versiones de Bootstrap, jQuery, Select2 y DataTables realmente cargadas.
La familia de texto se define en `Assets/app/css/font-family.css` y se carga mediante `Template/font_family.php`. Reutilizar las escalas y variables de `variables.css`, `typography.css` y `reports.css`. Limitar estilos particulares al módulo y conservar el diseño instalado; no introducir clases hm-* ni hojas de otro proyecto.
Revisar datatable-style.css y select2-style.css donde estén cargados. No asumir helpers de layout, form-standard.css, module-header.css ni tests inexistentes.

<a id="tipografia-estandar"></a>
## Fuente estándar para todos los módulos
La pila obligatoria es `Arial, Helvetica, sans-serif`. Rige todos los módulos existentes y nuevos y sus controles dinámicos: Login y recuperación de acceso, navegación, formularios, tablas, filtros, modales, tooltips, reportes, gráficas, páginas de validación, correos, comprobantes y manuales.

- **Definición única:** `Assets/app/css/font-family.css` establece `--font-family-base: Arial, Helvetica, sans-serif` y el alias compatible `--font-family-report: var(--font-family-base)`. Usar esas variables en CSS propio; no redefinirlas por módulo ni añadir Inter, Roboto, Poppins, Montserrat u otras familias de texto salvo solicitud explícita del usuario.
- **Carga compartida:** reutilizar `Template/font_family.php` en las entradas PHP, tanto dentro del portal como en vistas independientes. Comprobar que las vistas nuevas y sus componentes dinámicos reciban la hoja común. Mantener su revisión de contenido para evitar caché obsoleta y comprobar que estilos del tema o del módulo no sustituyan la familia.
- **HTML estático:** los manuales y otras entradas sin PHP deben enlazar la misma hoja con una ruta relativa válida. No duplicar la pila en hojas locales cuando el documento pueda utilizar el recurso común.
- **Correo y PDF:** cuando el cliente o motor no soporte variables CSS ni la hoja compartida, declarar literalmente `font-family: Arial, Helvetica, sans-serif;` en la plantilla compatible. No instalar fuentes ni activar descargas para cumplir este estándar; distinguir la familia configurada de la fuente disponible en cada motor.
- **Canvas y gráficas:** aplicar la familia calculada del portal a las opciones de texto de la biblioteca, incluidos títulos, ejes, leyendas y tooltips cuando corresponda. En ECharts usar `fontFamily` con la familia del documento; CSS por sí solo no modifica el texto dibujado en canvas.
- **Iconos y diseño:** preservar Font Awesome, Boxicons, Dropify y otras fuentes de iconos comprobadas, incluidos sus pseudoelementos. La unificación de familia no autoriza cambiar tamaños, pesos, interlineado, colores, espaciado, controles, permisos o lógica comercial.
- **Recursos:** no añadir Google Fonts, CDN, paquetes ni fuentes remotas para texto. No editar distribuciones de terceros para reemplazar su tipografía; corregir la herencia y las reglas de la aplicación.

Al trabajar en un módulo existente o crear uno nuevo, comprobar la familia efectiva en sus etiquetas, campos, botones, tablas, filtros, modales y estados dinámicos. Incluir escritorio/móvil y temas soportados; revisar iconos y conservar las medidas y los estados funcionales previos. Usar datos sintéticos y no conectarse a producción para validar tipografía. Este estándar se aplica al alcance solicitado; no autoriza modificaciones de módulos ajenos como efecto secundario.

<a id="labels-estandar"></a>
## Etiquetas de campos — estándar común
Este estándar es obligatorio para todos los módulos actuales y nuevos, incluido Login y sus etiquetas Usuario, Contraseña y Recordarme. Usar el estilo de la referencia de Órdenes de Compra Clientes: `Arial, Helvetica, sans-serif`, tamaño `13px`, peso `600`, interlineado `1.5` y color claro `#008DAF`. En modo oscuro usar `#58C6DE` para mantener legibilidad sobre las superficies del tema.
Reutilizar `Assets/app/css/form-labels.css`, cargado desde `Template/font_family.php`, y las variables `--lfm-label-font-size`, `--lfm-label-font-weight` y `--lfm-label-color`. La hoja cubre elementos label y las clases form-label, control-label, filter-label y col-form-label, incluidos controles y modales dinámicos. Las entradas HTML estáticas deben enlazar la misma hoja con una ruta relativa válida. Mantener la capa `lfm-form-labels` y la revisión del recurso compartido para que las utilidades o estilos locales no sustituyan el estándar ni permanezcan versiones antiguas en caché. No duplicar reglas por módulo ni introducir excepciones inline.
Conservar for/id, campos, validaciones, estados, clic de checkboxes/radios y fuentes de iconos. Mantener los colores semánticos de etiquetas que actúan como botones y avisos de error, así como los indicadores de obligatoriedad. No copiar iconos de la referencia a campos donde no correspondan ni cambiar el tamaño de los controles por homologar labels.
Validar la familia, tamaño, peso, interlineado y color calculados en navegador, con escritorio/móvil y temas soportados, incluyendo etiquetas sin clase, utilidades del tema, estilos locales, DataTables, modales y Login. No editar distribuciones de terceros ni conectarse a bases para comprobar presentación.

<a id="campos-estandar"></a>
## Campos input, select y textarea — estándar común
Este tamaño y diseño son obligatorios para todos los módulos actuales y nuevos, incluido Login (Usuario y Contraseña), filtros, tablas, modales y controles dinámicos. Reutilizar `Assets/app/css/form-controls.css`, cargado por `Template/font_family.php`, y sus variables `--lfm-control-*` y `--lfm-text-control-*`; no duplicar reglas por módulo ni crear excepciones inline salvo instrucción explícita del usuario.

- **Estado normal:** input de texto, textarea y select, incluido Select2, usan fondo blanco `#FFFFFF`, texto `#1E293B`, placeholder `#64748B`, borde neutro de `1px` y ninguna sombra. El borde normal es `#CBD5E1` en claro y `#60768C` en oscuro. El fondo blanco y el texto oscuro se conservan en ambos temas.
- **Con foco:** aplicar borde azul claro `#80BDFF` y sombra suave `0 0 6px rgb(0 123 255 / 20%)` en claro o `0 0 6px rgb(128 189 255 / 24%)` en oscuro. Usar el mismo acabado en input, textarea, select nativo y selección visible de Select2; en Select2 también mientras está abierto. El borde y halo deben ofrecer una indicación visible al navegar con teclado.
- **Al perder el foco:** recuperar el borde neutro y eliminar completamente la sombra cuando Select2 tampoco esté abierto. Hover por sí solo no activa este acabado.
- **Medidas:** Arial, Helvetica, sans-serif; 14 px; peso 400; interlineado 20 px; altura de una línea 38 px; padding 8 px 12 px y radio 5 px. Mantener las excepciones de grupos y controles multilínea descritas más adelante.
- **Estados especiales:** conservar los bordes semánticos de válido/inválido y las superficies y colores diferenciados de readonly/disabled, con texto legible. El fondo blanco normal no sustituye esos estados ni muestra auxiliares ocultos o redimensiona controles nativos especiales.

Los textarea conservan sus filas, valores y capacidad multilínea, con altura mínima 96 px; los selects múltiples o de lista también admiten altura automática desde 96 px. No convertirlos en controles de una sola línea. Mantener las dimensiones nativas de checkbox, radio, archivo, rango y color y ocultos los campos hidden y auxiliares accesibles de Select2.
Homologar la selección visible de Select2, búsqueda desplegable, selección múltiple y estados; no redimensionar sus búsquedas inline ni mostrar el select original. Conservar flechas, limpieza, chips y eventos. Alinear campos con adornos y acciones dentro de input-group, incluido Mostrar/Ocultar de Login, reservando espacio para texto e indicadores de validación.
Preservar IDs, for/id, nombres, tipos, valores, restricciones, rows, máscaras, calendarios, CSRF y controladores. Mantener foco visible, validación válida/inválida, readonly y disabled. El estándar visual no autoriza reescribir la lógica ni actualizar bibliotecas; usar la capa común lfm-form-controls para la precedencia y conservar las excepciones funcionales.
Comprobar fondo blanco y texto legible en reposo; borde neutro y ausencia de sombra; borde azul y halo al enfocar mediante clic y teclado; retorno al estado normal al desenfocar y cerrar Select2. Cubrir input, textarea y select nativo/Select2, incluido Login, sin foco inicial ni hover que simulen el estado enfocado. Comprobar además tamaños y estilos calculados, escritura, selección/limpieza, multilinea, mensajes, visibilidad de auxiliares y asociaciones de labels con datos sintéticos, en escritorio/móvil y temas soportados. No conectarse a bases para validar presentación. Las entradas HTML estáticas deben enlazar la misma hoja con una ruta relativa válida.

## Formularios y acciones
Conservar IDs, nombres, métodos, rutas, eventos, campos ocultos, CSRF, validaciones y contratos PHP/JavaScript. Mantener etiquetas visibles, foco, acceso por teclado y valores tras errores. Verificar el control real antes de cambiar for/id.
Distinguir la acción principal y reutilizar avisos/modales existentes. No asumir nombres de helpers de otros sistemas. Mostrar acciones según permisos y comprobarlos en backend. Confirmar operaciones destructivas; cerrar un modal no equivale a cancelar un documento.
Agrupar por flujo comercial; no trasladar controles de escritorio ni reglas clínicas al portal.

## Tablas, filtros y reportes
Conservar filtros, selección, orden, paginación y exportaciones autorizadas. Comprobar columnas y alineación al mostrar pestañas, modales o paneles ocultos; ajustar cuando el contenedor sea visible usando la API instalada, sin duplicar instancias, listeners ni peticiones.
No exportar HTML de acciones ni datos fuera del alcance autorizado. Mantener estados vacío, carga y error; no ocultar fallos de consulta con ceros.
Alinear importes y cantidades e indicar moneda y unidad. Separar monedas o usar la conversión comercial verificada; no sumar unidades incompatibles ni inventar tipos de cambio. Ordenar fechas por su valor real.
Distinguir cotizado, colocado, pedido, facturado y cancelado según campos y estatus reales. Usar alcance coherente de fechas y vendedor en indicadores, gráficas, tablas y exportaciones y explicar diferencias intencionales. Evitar duplicar proyectos o partidas por JOIN.
Mostrar progreso real, deshabilitar temporalmente acciones en proceso y restituirlas ante error. No sustituir prevención backend de duplicados por un botón deshabilitado.

## Menú y módulos
Reutilizar Template/aside.php y Config/Modulos.php. Menú y página deben verificar el permiso de lectura propio; conservar expansión del padre y submenú activo.
Cuando corresponda y el entorno esté autorizado, comprobar nombre, descripción, etiquetas, breadcrumb, vista y recursos de ssf_modulos. No registrar módulos ni conceder permisos al renderizar vistas.

## Accesibilidad y adaptación
Comprobar escritorio, tablet y móvil, con scroll local en tablas anchas y acciones esenciales accesibles. Mantener nombres accesibles, foco y gestión de foco de modales; no depender solo del color para estatus o errores.
Respetar temas soportados y contraste de al menos 4.5:1 para texto normal sobre su fondo efectivo. Cubrir hover, foco, selección, validación, disabled/readonly, menús y componentes dinámicos. No aplicar filtros globales de brillo ni recolorear logos; conservar filtros y valores al alternar tema.
Para ECharts aplicar [la regla de gráficas](echarts.md); el CSS exterior no garantiza que el canvas cambie de tema.

## Validación
Usar datos sintéticos para revisar disposición, filtros, controles, acentos, estados, temas y exportaciones. Ejecutar pruebas disponibles del módulo y separar validación estática de funcional. Una corrección visual no autoriza conectar a producción ni cambiar SQL o lógica comercial.
