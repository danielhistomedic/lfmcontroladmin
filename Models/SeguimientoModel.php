<?php

/**
 * Clase SeguimientoModel
 * Módulo: Seguimiento - Proyecto de Venta
 */
class SeguimientoModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Búsqueda de proyectos de venta por folio o número de proyecto_id
     * 
     * @param string $busqueda
     * @param string $ccveusuario_vendedor
     * @return array
     */
    public function buscarProyectosVenta(string $busqueda, string $ccveusuario_vendedor = ''): array
    {
        $arrResponse = array();

        try {
            $busqueda = trim($busqueda);
            if (empty($busqueda)) {
                return $arrResponse;
            }

            $term = '%' . $busqueda . '%';
            $arr_values = ['term' => $term];

            $sql = "SELECT 
                        v.id,
                        v.proyecto_id,
                        v.titulo,
                        v.fecha,
                        DATE_FORMAT(v.fecha, '%d/%m/%Y') AS fecha_formateada,
                        v.subtotal,
                        v.iva,
                        v.total,
                        v.moneda_id,
                        CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                        v.estatus_proyecto_id,
                        v.motivo_cancelacion,
                        COALESCE(c.nombre_comercial, c.razon_social, 'Sin Cliente') AS cliente,
                        COALESCE(CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido), 'Sin Vendedor') AS vendedor,
                        COALESCE(e.cEstatus, 'Sin Estatus') AS estatus_proyecto
                    FROM tb_ventas v
                    LEFT JOIN cat_clientes c ON c.id = v.cliente_id
                    LEFT JOIN cat_medico u ON u.ccvemedico = v.ccveusuario_vendedor
                    LEFT JOIN cat_estatus_proyecto e ON e.Id = v.estatus_proyecto_id
                    WHERE v.proyecto_id LIKE :term ";

            if (!empty($ccveusuario_vendedor)) {
                $sql .= " AND v.ccveusuario_vendedor = :ccveusuario_vendedor ";
                $arr_values['ccveusuario_vendedor'] = $ccveusuario_vendedor;
            }

            $sql .= " ORDER BY v.id DESC LIMIT 20";

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene el detalle completo de un proyecto de venta por su id (venta_id)
     * 
     * @param int $venta_id
     * @param string $ccveusuario_vendedor
     * @return array
     */
    public function getProyectoVentaById(int $venta_id, string $ccveusuario_vendedor = ''): array
    {
        $arrResponse = array();

        try {
            if ($venta_id <= 0) {
                return $arrResponse;
            }

            $arr_values = ['venta_id' => $venta_id];

            $sql = "SELECT 
                        v.id,
                        v.proyecto_id,
                        v.titulo,
                        v.fecha,
                        DATE_FORMAT(v.fecha, '%d/%m/%Y') AS fecha_formateada,
                        v.fchregistro,
                        DATE_FORMAT(v.fchregistro, '%d/%m/%Y %H:%i') AS fchregistro_formateada,
                        v.subtotal,
                        v.iva,
                        v.total,
                        v.moneda_id,
                        CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                        v.estatus_proyecto_id,
                        v.motivo_cancelacion,
                        COALESCE(c.nombre_comercial, c.razon_social, 'Sin Cliente') AS cliente,
                        COALESCE(CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido), 'Sin Vendedor') AS vendedor,
                        COALESCE(e.cEstatus, 'Sin Estatus') AS estatus_proyecto
                    FROM tb_ventas v
                    LEFT JOIN cat_clientes c ON c.id = v.cliente_id
                    LEFT JOIN cat_medico u ON u.ccvemedico = v.ccveusuario_vendedor
                    LEFT JOIN cat_estatus_proyecto e ON e.Id = v.estatus_proyecto_id
                    WHERE v.id = :venta_id ";

            if (!empty($ccveusuario_vendedor)) {
                $sql .= " AND v.ccveusuario_vendedor = :ccveusuario_vendedor ";
                $arr_values['ccveusuario_vendedor'] = $ccveusuario_vendedor;
            }

            $arrResponse = $this->selectModel($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Evalúa el checklist del proceso para los estatus ID 1 al 7
     * 
     * @param int $venta_id
     * @param int $estatus_proyecto_id
     * @return array
     */
    public function getChecklistProceso(int $venta_id, int $estatus_proyecto_id): array
    {
        $checklist = array();

        try {
            // Obtener del catálogo de estatus del 1 al 7 (excluyendo el Id 2 - Cancelado)
            $sqlCat = "SELECT Id, cEstatus FROM cat_estatus_proyecto WHERE Id IN (1, 3, 4, 5, 6, 7) ORDER BY Id ASC";
            $arrCat = $this->select($sqlCat, []);

            foreach ($arrCat as $cat) {
                $catId = (int)$cat['Id'];
                $nombreEstatus = $cat['cEstatus'];

                $item = array(
                    'id'            => $catId,
                    'nombre'        => $nombreEstatus,
                    'completado'    => false,
                    'is_cancelado'  => false,
                    'mensaje'       => '',
                    'registros'     => array()
                );

                switch ($catId) {
                    case 1:
                        // Id 1: OPORTUNIDAD DE VENTA (INICIO PROCESO) - Checar si hay registro en tb_ventas
                        $sqlVenta = "SELECT id, proyecto_id, titulo, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, total, subtotal, COALESCE(descuento, 0) AS descuento, iva, moneda_id, 
                                     CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                     FROM tb_ventas WHERE id = :vId";
                        $regVenta = $this->select($sqlVenta, ['vId' => $venta_id]);
                        if (!empty($regVenta)) {
                            $item['completado'] = true;
                            $item['mensaje']    = 'Registro de Oportunidad de Venta generado.';
                            $item['registros']  = $regVenta;
                        } else {
                            $item['mensaje']    = 'Sin registro de Oportunidad de Venta.';
                        }
                        break;

                    case 3:
                        // Id 3: EN PROCESO DE COTIZACION - Checar si hay registro en tb_compras_cotizaciones (enviado = 1)
                        $sqlCc = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, COALESCE(descuento, 0) AS descuento, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_compras_cotizaciones 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regCc = $this->select($sqlCc, ['vId' => $venta_id]);
                        if (!empty($regCc)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regCc) . ' cotización(es) de compras finalizada(s).';
                            $item['registros']  = $regCc;
                        } else {
                            $item['mensaje']    = 'Sin cotización de compras finalizada.';
                        }
                        break;

                    case 4:
                        // Id 4: COTIZACION INTERNA ELABORADA - Checar si hay registro en tb_compras_cotizacion_interna (enviado = 1)
                        $sqlCi = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, COALESCE(descuento, 0) AS descuento, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_compras_cotizacion_interna 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regCi = $this->select($sqlCi, ['vId' => $venta_id]);
                        if (!empty($regCi)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regCi) . ' cotización(es) interna(s) finalizada(s).';
                            $item['registros']  = $regCi;
                        } else {
                            $item['mensaje']    = 'Sin cotización interna finalizada.';
                        }
                        break;

                    case 5:
                        // Id 5: COTIZACION CLIENTE ELABORADA - Checar si hay registro en tb_ventas_cotizacion_cliente (enviado = 1)
                        $sqlVc = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, COALESCE(descuento, 0) AS descuento, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_ventas_cotizacion_cliente 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regVc = $this->select($sqlVc, ['vId' => $venta_id]);
                        if (!empty($regVc)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regVc) . ' cotización(es) a cliente finalizada(s).';
                            $item['registros']  = $regVc;
                        } else {
                            $item['mensaje']    = 'Sin cotización a cliente finalizada.';
                        }
                        break;

                    case 6:
                        // Id 6: ORDEN COMPRA CLIENTE (PEDIDO COLOCADO) - Checar si hay registro en tb_pedidos_cliente (enviado = 1)
                        $sqlOc = "SELECT pc.id, 
                                         COALESCE(NULLIF(pc.num_orden_compra, ''), CONCAT('Pedido #', pc.id)) AS folio_cotizacion,
                                         pc.num_orden_compra,
                                         pc.fecha_pedido AS fecha, 
                                         DATE_FORMAT(pc.fecha_pedido, '%d/%m/%Y') AS fecha_formateada, 
                                         COALESCE(pc.subtotal, 0) AS subtotal, 
                                         COALESCE(pc.descuento, 0) AS descuento, 
                                         COALESCE(pc.iva, 0) AS iva, 
                                         COALESCE(pc.total, 0) AS total, 
                                         pc.enviado, 
                                         COALESCE(v.moneda_id, 3) AS moneda_id, 
                                         CASE WHEN COALESCE(v.moneda_id, 3) = 1 THEN 'MXN' WHEN COALESCE(v.moneda_id, 3) = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_pedidos_cliente pc
                                  LEFT JOIN tb_ventas v ON v.id = pc.venta_id
                                  WHERE pc.venta_id = :vId AND pc.enviado = 1 
                                  ORDER BY pc.id DESC";
                        $regOc = $this->select($sqlOc, ['vId' => $venta_id]);
                        if (!empty($regOc)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regOc) . ' orden(es) de compra / cotización cliente confirmada(s).';
                            $item['registros']  = $regOc;
                        } else {
                            $item['mensaje']    = 'Sin orden de compra de cliente confirmada.';
                        }
                        break;

                    case 7:
                        // Id 7: ORDEN COMPRA PROVEEDOR (PEDIDO ELABORADO) - Checar si hay registro en tb_pedidos_proveedor (enviado = 1)
                        $sqlPp = "SELECT id, folio_ocp, fecha_pedido, DATE_FORMAT(fecha_pedido, '%d/%m/%Y') AS fecha_formateada, subtotal, COALESCE(descuento, 0) AS descuento, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_pedidos_proveedor 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regPp = $this->select($sqlPp, ['vId' => $venta_id]);
                        if (!empty($regPp)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regPp) . ' orden(es) de compra a proveedor finalizada(s).';
                            $item['registros']  = $regPp;
                        } else {
                            $item['mensaje']    = 'Sin orden de compra a proveedor finalizada.';
                        }
                        break;
                }

                $checklist[] = $item;
            }

        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $checklist;
    }

    /**
     * Obtiene la lista de proyectos de venta para el DataTable de Seguimiento
     * 
     * @param string $fecha_ini
     * @param string $fecha_fin
     * @param string $busqueda
     * @param string $titulo
     * @param string $cliente
     * @param string $vendedor
     * @param string $ccveusuario_vendedor
     * @return array
     */
    public function selectProyectosVentaSeguimiento(
        string $fecha_ini = '',
        string $fecha_fin = '',
        string $busqueda = '',
        string $titulo = '',
        string $cliente = '',
        string $vendedor = '',
        string $ccveusuario_vendedor = ''
    ): array {
        $arrResponse = array();

        try {
            $sql = "SELECT 
                        v.id,
                        v.proyecto_id,
                        v.titulo,
                        v.fecha,
                        DATE_FORMAT(v.fecha, '%d/%m/%Y') AS fecha_formateada,
                        v.subtotal,
                        v.iva,
                        v.total,
                        v.moneda_id,
                        CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                        v.estatus_proyecto_id,
                        v.motivo_cancelacion,
                        COALESCE(c.nombre_comercial, c.razon_social, 'Sin Cliente') AS cliente,
                        COALESCE(CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido), 'Sin Vendedor') AS vendedor,
                        COALESCE(e.cEstatus, 'Sin Estatus') AS estatus_proyecto
                    FROM tb_ventas v
                    LEFT JOIN cat_clientes c ON c.id = v.cliente_id
                    LEFT JOIN cat_medico u ON u.ccvemedico = v.ccveusuario_vendedor
                    LEFT JOIN cat_estatus_proyecto e ON e.Id = v.estatus_proyecto_id
                    WHERE 1=1 ";

            $arr_values = [];

            if (!empty($ccveusuario_vendedor)) {
                $sql .= " AND v.ccveusuario_vendedor = :ccveusuario_vendedor ";
                $arr_values['ccveusuario_vendedor'] = $ccveusuario_vendedor;
            }

            if (!empty($fecha_ini) && !empty($fecha_fin)) {
                $sql .= " AND DATE(v.fecha) BETWEEN :fecha_ini AND :fecha_fin ";
                $arr_values['fecha_ini'] = $fecha_ini;
                $arr_values['fecha_fin'] = $fecha_fin;
            } elseif (!empty($fecha_ini)) {
                $sql .= " AND DATE(v.fecha) >= :fecha_ini ";
                $arr_values['fecha_ini'] = $fecha_ini;
            } elseif (!empty($fecha_fin)) {
                $sql .= " AND DATE(v.fecha) <= :fecha_fin ";
                $arr_values['fecha_fin'] = $fecha_fin;
            }

            if (!empty($busqueda)) {
                $sql .= " AND (v.proyecto_id LIKE :term OR v.titulo LIKE :term OR c.nombre_comercial LIKE :term OR c.razon_social LIKE :term OR CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido) LIKE :term) ";
                $arr_values['term'] = '%' . $busqueda . '%';
            }

            if (!empty($titulo)) {
                $sql .= " AND v.titulo LIKE :titulo ";
                $arr_values['titulo'] = '%' . $titulo . '%';
            }

            if (!empty($cliente)) {
                $sql .= " AND (c.nombre_comercial LIKE :cliente OR c.razon_social LIKE :cliente) ";
                $arr_values['cliente'] = '%' . $cliente . '%';
            }

            if (!empty($vendedor)) {
                $sql .= " AND CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido) LIKE :vendedor ";
                $arr_values['vendedor'] = '%' . $vendedor . '%';
            }

            $sql .= " ORDER BY v.id DESC";

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene todos los archivos adjuntos vinculados a un proyecto de venta
     * ordenados por las 6 tablas especificadas:
     * 1. tb_ventas_adjuntos
     * 2. tb_compras_cotizaciones_adjuntos
     * 3. tb_compras_cotizacion_interna_adjuntos
     * 4. tb_ventas_cotizacion_cliente_adjuntos
     * 5. tb_pedidos_cliente_adjuntos
     * 6. tb_pedidos_proveedor_adjuntos
     *
     * @param int $venta_id
     * @return array
     */
    public function getAdjuntosProyectoVenta(int $venta_id): array
    {
        $arrResponse = array();
        try {
            // 1. tb_ventas_adjuntos
            $sql1 = "SELECT 
                        va.id,
                        va.venta_id,
                        va.archivo,
                        COALESCE(va.comentarios, '') AS comentarios,
                        va.fchregistro AS fecha,
                        DATE_FORMAT(va.fchregistro, '%d/%m/%Y %H:%i') AS fecha_formateada,
                        COALESCE(CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido), '') AS nombre_usuario,
                        'tb_ventas_adjuntos' AS tabla_origen,
                        'Oportunidad / Venta' AS origen_etiqueta,
                        'primary' AS badge_color
                    FROM tb_ventas_adjuntos va
                    LEFT JOIN cat_medico u ON u.ccvemedico = va.ccveusuario
                    WHERE va.venta_id = :venta_id AND va.archivo IS NOT NULL AND TRIM(va.archivo) != ''
                    ORDER BY va.id ASC";
            $res1 = $this->select($sql1, ['venta_id' => $venta_id]);
            if (!empty($res1)) {
                $arrResponse = array_merge($arrResponse, $res1);
            }

            // 2. tb_compras_cotizaciones_adjuntos
            $sql2 = "SELECT 
                        ca.id,
                        ca.venta_id,
                        ca.archivo,
                        '' AS comentarios,
                        NULL AS fecha,
                        '' AS fecha_formateada,
                        '' AS nombre_usuario,
                        'tb_compras_cotizaciones_adjuntos' AS tabla_origen,
                        'Cotización de Compra' AS origen_etiqueta,
                        'info' AS badge_color
                    FROM tb_compras_cotizaciones_adjuntos ca
                    WHERE ca.venta_id = :venta_id AND ca.archivo IS NOT NULL AND TRIM(ca.archivo) != ''
                    ORDER BY ca.id ASC";
            $res2 = $this->select($sql2, ['venta_id' => $venta_id]);
            if (!empty($res2)) {
                $arrResponse = array_merge($arrResponse, $res2);
            }

            // 3. tb_compras_cotizacion_interna_adjuntos
            $sql3 = "SELECT 
                        cia.id,
                        cia.venta_id,
                        cia.archivo,
                        '' AS comentarios,
                        NULL AS fecha,
                        '' AS fecha_formateada,
                        '' AS nombre_usuario,
                        'tb_compras_cotizacion_interna_adjuntos' AS tabla_origen,
                        'Cotización Interna' AS origen_etiqueta,
                        'secondary' AS badge_color
                    FROM tb_compras_cotizacion_interna_adjuntos cia
                    WHERE cia.venta_id = :venta_id AND cia.archivo IS NOT NULL AND TRIM(cia.archivo) != ''
                    ORDER BY cia.id ASC";
            $res3 = $this->select($sql3, ['venta_id' => $venta_id]);
            if (!empty($res3)) {
                $arrResponse = array_merge($arrResponse, $res3);
            }

            // 4. tb_ventas_cotizacion_cliente_adjuntos
            $sql4 = "SELECT 
                        vca.id,
                        vca.venta_id,
                        vca.archivo,
                        '' AS comentarios,
                        NULL AS fecha,
                        '' AS fecha_formateada,
                        '' AS nombre_usuario,
                        'tb_ventas_cotizacion_cliente_adjuntos' AS tabla_origen,
                        'Cotización a Cliente' AS origen_etiqueta,
                        'success' AS badge_color
                    FROM tb_ventas_cotizacion_cliente_adjuntos vca
                    WHERE vca.venta_id = :venta_id AND vca.archivo IS NOT NULL AND TRIM(vca.archivo) != ''
                    ORDER BY vca.id ASC";
            $res4 = $this->select($sql4, ['venta_id' => $venta_id]);
            if (!empty($res4)) {
                $arrResponse = array_merge($arrResponse, $res4);
            }

            // 5. tb_pedidos_cliente_adjuntos
            $sql5 = "SELECT 
                        pca.id,
                        pca.venta_id,
                        pca.archivo,
                        '' AS comentarios,
                        NULL AS fecha,
                        '' AS fecha_formateada,
                        '' AS nombre_usuario,
                        'tb_pedidos_cliente_adjuntos' AS tabla_origen,
                        'Pedido Cliente / Orden de Compra' AS origen_etiqueta,
                        'warning' AS badge_color
                    FROM tb_pedidos_cliente_adjuntos pca
                    WHERE pca.venta_id = :venta_id AND pca.archivo IS NOT NULL AND TRIM(pca.archivo) != ''
                    ORDER BY pca.id ASC";
            $res5 = $this->select($sql5, ['venta_id' => $venta_id]);
            if (!empty($res5)) {
                $arrResponse = array_merge($arrResponse, $res5);
            }

            // 6. tb_pedidos_proveedor_adjuntos
            $sql6 = "SELECT 
                        ppa.id,
                        ppa.venta_id,
                        ppa.archivo,
                        '' AS comentarios,
                        NULL AS fecha,
                        '' AS fecha_formateada,
                        '' AS nombre_usuario,
                        'tb_pedidos_proveedor_adjuntos' AS tabla_origen,
                        'Pedido Proveedor' AS origen_etiqueta,
                        'danger' AS badge_color
                    FROM tb_pedidos_proveedor_adjuntos ppa
                    WHERE ppa.venta_id = :venta_id AND ppa.archivo IS NOT NULL AND TRIM(ppa.archivo) != ''
                    ORDER BY ppa.id ASC";
            $res6 = $this->select($sql6, ['venta_id' => $venta_id]);
            if (!empty($res6)) {
                $arrResponse = array_merge($arrResponse, $res6);
            }

        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene la lista de partidas del proyecto de venta en orden de prioridad:
     * 1) tb_pedidos_cliente_detalle (si tb_pedidos_cliente.enviado = 1)
     * 2) tb_compras_cotizacion_interna_detalle (si tb_compras_cotizacion_interna.enviado = 1)
     * 3) tb_ventas_detalle (sin importar el estatus)
     * 
     * @param int $venta_id
     * @return array
     */
    public function getPartidasProyecto(int $venta_id): array
    {
        $arrResponse = array(
            'origen_tabla'    => '',
            'origen_etiqueta' => 'Sin partidas',
            'partidas'        => array()
        );

        if ($venta_id <= 0) {
            return $arrResponse;
        }

        try {
            // Helper interno para mapear filas de resultado
            $mapRow = function ($row, $origenTabla, $origenEtiqueta) {
                $cant   = floatval($row['cantidad'] ?? $row['cantidad_pedido'] ?? 0);
                $precio = floatval($row['precio_unitario'] ?? $row['precio'] ?? 0);
                $imp    = floatval($row['importe'] ?? ($cant * $precio));
                $cod    = !empty($row['codigo_partida']) ? $row['codigo_partida'] : (!empty($row['Clave']) ? $row['Clave'] : '');

                return array(
                    'id'                     => $row['id'] ?? 0,
                    'folio_documento'        => $row['Folio_Documento'] ?? '',
                    'codigo_partida'         => $cod,
                    'descripcion'            => !empty($row['descripcion']) ? $row['descripcion'] : 'Sin descripción',
                    'descripcion_adicional'  => $row['descripcion_adicional'] ?? '',
                    'ccveunidad'             => $row['ccveunidad'] ?? '',
                    'cantidad'               => $cant,
                    'precio_unitario'        => $precio,
                    'descuento'              => floatval($row['descuento'] ?? 0),
                    'impuesto_tasa'          => floatval($row['impuesto_tasa'] ?? 0),
                    'importe_impuesto'       => floatval($row['importe_impuesto'] ?? $row['impuesto_importe'] ?? 0),
                    'subtotal'               => $imp,
                    'importe'                => $imp,
                    'tiempo_entrega'         => $row['tiempo_entrega'] ?? '',
                    'fecha_estimada_entrega' => $row['fecha_estimada_entrega'] ?? '',
                    'clave'                  => $row['Clave'] ?? '',
                    'ccn'                    => $row['CCN'] ?? '',
                    'codigo_cliente'         => $row['Codigo_Cliente'] ?? '',
                    'tabla_origen'           => $origenTabla,
                    'origen_etiqueta'        => $origenEtiqueta
                );
            };

            // =========================================================================
            // PRIORIDAD 1: tb_pedidos_cliente_detalle (ped.enviado = 1)
            // =========================================================================
            $sql1 = "SELECT
                       pdet.id,
                       ped.num_orden_compra AS Folio_Documento,
                       vd.codigo_partida,
                       IFNULL(pdet.descripcion, vd.descripcion) AS descripcion,
                       pdet.descripcion_adicional,
                       IFNULL(pdet.ccveunidad, vd.ccveunidad) AS ccveunidad,
                       pdet.cantidad_pedido AS cantidad,
                       pdet.precio_unitario,
                       pdet.descuento,
                       pdet.impuesto_tasa,
                       pdet.importe_impuesto,
                       pdet.importe,
                       pdet.tiempo_entrega,
                       pdet.fecha_estimada_entrega,
                       pdet.ccvematerial as Clave,
                       mat.ccveMaterialAlmacen AS CCN,
                       sap.clave_cliente AS Codigo_Cliente
                       FROM tb_pedidos_cliente_detalle pdet
                       LEFT JOIN tb_pedidos_cliente ped ON ped.id = pdet.pedido_id
                       LEFT JOIN tb_ventas v ON v.id = ped.venta_id
                       LEFT JOIN tb_ventas_detalle vd ON vd.id = pdet.venta_detalle_id
                       LEFT JOIN tb_materiales mat ON mat.ccvematerial = pdet.ccvematerial
                       LEFT JOIN tb_materiales_claves_sap sap ON (sap.ccvematerial = pdet.ccvematerial AND sap.cliente_id = v.cliente_id)
                       WHERE v.id = :venta_id AND ped.enviado = 1
                       ORDER BY vd.codigo_partida";

            try {
                $res1 = $this->select($sql1, ['venta_id' => $venta_id]);
                if (!empty($res1)) {
                    $partidas1 = array();
                    foreach ($res1 as $row) {
                        $partidas1[] = $mapRow($row, 'tb_pedidos_cliente_detalle', 'Pedido de Cliente');
                    }
                    if (!empty($partidas1)) {
                        $arrResponse['origen_tabla']    = 'tb_pedidos_cliente_detalle';
                        $arrResponse['origen_etiqueta'] = 'Orden de Compra Cliente';
                        $arrResponse['partidas']        = $partidas1;
                        return $arrResponse;
                    }
                }
            } catch (\Throwable $e1) {
                getLoggerSystem()->error('Error Prioridad 1 Partidas: ' . getMensajeError($e1));
            }

            // =========================================================================
            // PRIORIDAD 2: tb_compras_cotizacion_interna_detalle (ped.enviado = 1)
            // =========================================================================
            $sql2 = "SELECT
                       pdet.id,
                       ped.folio_cotizacion AS Folio_Documento,
                       vd.codigo_partida,
                       IFNULL(pdet.descripcion_proveedor, vd.descripcion) AS descripcion,
                       pdet.descripcion_adicional,
                       IFNULL(pdet.ccveunidad, vd.ccveunidad) AS ccveunidad,
                       pdet.cantidad AS cantidad,
                       pdet.precio_unitario,
                       0 as descuento,
                       pdet.impuesto_tasa,
                       pdet.impuesto_importe,
                       pdet.importe,
                       pdet.tiempo_entrega,
                       pdet.fecha_estimada_entrega,
                       pdet.ccvematerial as Clave,
                       mat.ccveMaterialAlmacen AS CCN,
                       sap.clave_cliente AS Codigo_Cliente
                       FROM tb_compras_cotizacion_interna_detalle pdet
                       LEFT JOIN tb_compras_cotizacion_interna ped ON ped.id = pdet.cotizacion_interna_id
                       LEFT JOIN tb_ventas v ON v.id = ped.venta_id
                       LEFT JOIN tb_ventas_detalle vd ON vd.id = pdet.venta_detalle_id_partida
                       LEFT JOIN tb_materiales mat ON mat.ccvematerial = pdet.ccvematerial
                       LEFT JOIN tb_materiales_claves_sap sap ON (sap.ccvematerial = pdet.ccvematerial AND sap.cliente_id = v.cliente_id)
                       WHERE v.id = :venta_id AND ped.enviado = 1
                       ORDER BY vd.codigo_partida";

            try {
                $res2 = $this->select($sql2, ['venta_id' => $venta_id]);
                if (!empty($res2)) {
                    $partidas2 = array();
                    foreach ($res2 as $row) {
                        $partidas2[] = $mapRow($row, 'tb_compras_cotizacion_interna_detalle', 'Cotización Interna');
                    }
                    if (!empty($partidas2)) {
                        $arrResponse['origen_tabla']    = 'tb_compras_cotizacion_interna_detalle';
                        $arrResponse['origen_etiqueta'] = 'Cotización Interna';
                        $arrResponse['partidas']        = $partidas2;
                        return $arrResponse;
                    }
                }
            } catch (\Throwable $e2) {
                getLoggerSystem()->error('Error Prioridad 2 Partidas: ' . getMensajeError($e2));
            }

            // =========================================================================
            // PRIORIDAD 3: tb_ventas_detalle (sin importar el estatus)
            // =========================================================================
            $sql3 = "SELECT
                       pdet.id,
                       ped.proyecto_id AS Folio_Documento,
                       pdet.codigo_partida,
                       pdet.descripcion AS descripcion,
                       pdet.descripcion_adicional,
                       pdet.ccveunidad AS ccveunidad,
                       pdet.cantidad AS cantidad,
                       0 as precio_unitario,
                       0 as descuento,
                       0 as impuesto_tasa,
                       0 as impuesto_importe,
                       0 as importe,
                       '' as tiempo_entrega,
                       '' as fecha_estimada_entrega,
                       pdet.ccvematerial as Clave,
                       mat.ccveMaterialAlmacen AS CCN,
                       sap.clave_cliente AS Codigo_Cliente
                       FROM tb_ventas_detalle pdet
                       LEFT JOIN tb_ventas ped ON ped.id = pdet.venta_id
                       LEFT JOIN tb_materiales mat ON mat.ccvematerial = pdet.ccvematerial
                       LEFT JOIN tb_materiales_claves_sap sap ON (sap.ccvematerial = pdet.ccvematerial AND sap.cliente_id = ped.cliente_id)
                       WHERE ped.id = :venta_id
                       ORDER BY pdet.codigo_partida";

            try {
                $res3 = $this->select($sql3, ['venta_id' => $venta_id]);
                if (!empty($res3)) {
                    $partidas3 = array();
                    foreach ($res3 as $row) {
                        $partidas3[] = $mapRow($row, 'tb_ventas_detalle', 'Proyecto de Venta');
                    }
                    if (!empty($partidas3)) {
                        $arrResponse['origen_tabla']    = 'tb_ventas_detalle';
                        $arrResponse['origen_etiqueta'] = 'Proyecto de Venta';
                        $arrResponse['partidas']        = $partidas3;
                        return $arrResponse;
                    }
                }
            } catch (\Throwable $e3) {
                getLoggerSystem()->error('Error Prioridad 3 Partidas: ' . getMensajeError($e3));
            }

        } catch (\Throwable $th) {
            getLoggerSystem()->error('Error getPartidasProyecto: ' . getMensajeError($th));
        }

        return $arrResponse;
    }
}


