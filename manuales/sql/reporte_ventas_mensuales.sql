-- Registro creado en producción el 2026-10-01; ID utilizado por Config/Modulos.php.
-- No volver a ejecutar en producción: conservar como referencia de despliegue.
-- En otro entorno, comprobar antes que el ID 139 y la ruta estén libres.
-- No modifica permisos de roles; habilitar lectura desde Seguridad / Permisos.
-- En producción se asignaron al rol ADMINISTRADOR (ID 1) todos los permisos
-- del módulo 139: r, c, u, d, e, p = 1. Se conservaron los demás módulos.
INSERT INTO ssf_modulos (
    id, name, descripcion, activo, created_at, usuario_id_created,
    menu_base, reservado_sys, orden, icon, url, menu_search, tags,
    card_title, breadcrumb, form_title, js, views, icon_form_title
)
SELECT
    139,
    'Reportes Mensuales - Reporte Ventas',
    'Reporte mensual de ventas para consulta de resultados comerciales por periodo. Vista inicial en blanco para integrar el análisis de ventas mensuales.',
    1, NOW(), 1, 0, 0, 900,
    'fa-sharp fa-light fa-chart-column',
    '/reportesmensuales/ventas', 1,
    'reportes mensuales, reporte ventas, ventas mensuales, resultados comerciales, periodo, mes',
    'Reporte de Ventas Mensuales',
    'Reportes Mensuales / Reporte Ventas',
    'Reporte Ventas', '', 'reporte_ventas',
    '<i class="fa-sharp fa-light fa-chart-column text-primary me-2"></i>'
WHERE NOT EXISTS (
    SELECT 1 FROM ssf_modulos
    WHERE id = 139 OR url IN ('/reportesmensuales/ventas', 'reportesmensuales/ventas')
);

-- Comprobación: si el ID estaba ocupado, verificar que corresponda a este módulo.
SELECT id, name, url, activo, views
FROM ssf_modulos
WHERE id = 139;

-- Reversión lógica opcional (no ejecutada): deshabilitar también sus permisos
-- en Seguridad / Permisos y retirar el bloque del menú al revertir los archivos.
-- UPDATE ssf_modulos SET activo = 0, updated_at = NOW()
-- WHERE id = 139 AND url = '/reportesmensuales/ventas';
