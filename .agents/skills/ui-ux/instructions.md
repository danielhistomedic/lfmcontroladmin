# UI/UX web — LFM Control
Aplicar [controlesforms.md](../../rules/controlesforms.md) y comparar con vistas reales del flujo comercial. Reutilizar Template, Assets/app y componentes cargados por el módulo.
Mantener tipografía y variables de variables.css, typography.css y reports.css. Verificar datatable-style.css y select2-style.css donde se utilicen; no imponer hojas ni clases de otro portal.
Conservar IDs, nombres, rutas, eventos, CSRF, campos ocultos, validación y metadatos. Homologar presentación sin copiar permisos ni lógica. Mantener etiquetas, foco y teclado.
En tablas conservar filtros, orden, paginación, columnas, selección y exportaciones autorizadas. Verificar alineación en paneles, modales y pestañas sin duplicar instancias ni peticiones.
En reportes identificar periodo, vendedor, moneda, unidades y alcance. Mantener coherencia entre tablas, gráficas y exportación y distinguir carga, vacío y error. Para gráficas consultar [ECharts](../echarts/SKILL.md).
Mantener navegación y submenú activo con permisos propios. Comprobar escritorio, móvil, temas, contraste, títulos largos, controles dinámicos y disabled/readonly con datos sintéticos. No cambiar SQL ni conectar a producción por una corrección visual; documentar pruebas y límites.
