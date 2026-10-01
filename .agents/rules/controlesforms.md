---
trigger: always_on
---
# Formularios y controles web

## Identidad visual
Tomar como referencia las páginas y componentes reales de lfm_admin. Conservar layout, menú, encabezados, espaciado, paleta, iconos y sistema de diseño instalado. No copiar controles de WinForms ni tamaños de fuentes de escritorio a CSS.
Reutilizar fuentes y variables CSS existentes. Si se requiere un nuevo estilo, integrarlo al mecanismo común y limitar su alcance. No descargar fuentes, añadir CDN o instalar un framework solo para igualar un formulario.

## Captura y acciones
Mantener etiquetas visibles asociadas a campos, obligatoriedad y formato esperado. Validar en cliente para comodidad y en servidor para seguridad. Mostrar mensajes contextuales y conservar valores tras errores.
Diferenciar la acción principal. Utilizar el sistema de avisos y confirmaciones existente; no asumir que Msj_Adv o Msj_AdvOnly son funciones JavaScript.
Confirmar acciones destructivas y volver a verificar permisos/estatus en backend. Una acción de cerrar un modal no equivale a cancelar un documento.
Agrupar secciones por flujo de trabajo. Usar tooltips existentes para acciones ambiguas, sin sustituir etiquetas esenciales.

## Tablas, filtros y totales
Conservar biblioteca de tablas instalada, filtros, selección y ordenamiento. Selección múltiple solo cuando la operación la requiera, con casillas y estado de selección claro; no seleccionarla obligatoriamente por herencia de DataGridViewX.
Alinear importes/cantidades, indicar moneda y unidad y separar totales por moneda o convertir según reglas existentes. Ordenar fechas por su valor real, no por el texto formateado.
Mostrar estados con texto y color. Mantener paginación, vacío, carga y error. No cargar miles de registros por defecto ni modificar reglas de filtro existentes para simplificar el diseño.

## Responsividad y accesibilidad
Usar CSS responsive y componentes disponibles, evitando posiciones rígidas. Comprobar escritorio, tablet y móvil; permitir desplazamiento local en tablas anchas sin ocultar acciones.
Mantener foco visible, navegación por teclado, contraste y nombres accesibles para botones con iconos. Al abrir/cerrar modales, gestionar el foco según el componente instalado.

## Procesos, errores e impresión
Indicar carga real, deshabilitar temporalmente la acción mientras se procesa y restituir controles ante error. Prevenir duplicados también en servidor; no confiar solo en el botón deshabilitado.
Mostrar porcentaje solo si se conoce el avance. Mantener las librerías de gráficos/PDF y plantillas de impresión existentes; no imponer Crystal Reports al navegador.
Los ajustes visuales no deben alterar SQL, lógica comercial o contratos. Verificar español, ñ, acentos, filtros y estados tras el cambio.
