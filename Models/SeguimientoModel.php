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
                        // Id 3: EN PROCESO DE COTIZACION - Checar si hay registro en tb_compras_cotizaciones
                        $sqlCc = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, iva, total, enviado, moneda_id, 
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
                        // Id 4: COTIZACION INTERNA ELABORADA - Checar si hay registro en tb_compras_cotizacion_interna
                        $sqlCi = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, iva, total, enviado, moneda_id, 
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
                        // Id 5: COTIZACION CLIENTE ELABORADA - Checar si hay registro en tb_ventas_cotizacion_cliente
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
                        // Id 6: ORDEN COMPRA CLIENTE (PEDIDO COLOCADO) - Checar si hay registro en tb_pedidos_cliente
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
                        // Id 7: ORDEN COMPRA PROVEEDOR (PEDIDO ELABORADO) - Checar si hay registro en tb_pedidos_proveedor
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

    /**
     * Obtiene la lista completa de órdenes de compra a proveedores con su seguimiento para la vista de seguimiento.
     * Une tb_pedidos_proveedor con tb_ventas, tb_proveedores, cat_clientes y tb_ventas_seguimiento.
     *
     * @param string $fecha_ini
     * @param string $fecha_fin
     * @param string $filtro_estatus
     * @param string $filtro_proveedor
     * @param string $ccveusuario
     * @param string $filtro_num_orden
     * @param string $filtro_estatus_orden
     * @return array
     */
    public function selectOrdenesProveedorSeguimiento(
        string $fecha_ini = '',
        string $fecha_fin = '',
        string $filtro_estatus = '',
        string $filtro_proveedor = '',
        string $ccveusuario = '',
        string $filtro_num_orden = '',
        string $filtro_estatus_orden = ''
    ): array {
        $arrResponse = array();

        try {
            $sql = "SELECT
                pp.id                                                       AS pedido_id,
                pp.folio_ocp,
                pp.oa_proveedor,
                pp.oe_proveedor,
                pp.num_revision,
                pp.tipo_pedido,
                pp.incoterm,
                pp.venta_id,
                v.titulo                                                    AS titulo_venta,
                v.proyecto_id,
                v.clues,
                COALESCE(c.nombre_comercial, 'Sin Cliente')                 AS cliente,
                COALESCE(prov.cDatGenRazonSocial, prov.cDatGenNombreAbreviado, 'Sin Proveedor') AS proveedor,
                prov.cDatGenRFC                                             AS proveedor_rfc,
                prov.cContactoNombre                                        AS proveedor_contacto,
                prov.cContactoeMail                                         AS proveedor_email,
                prov.cContactoTel                                           AS proveedor_telefono,
                CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS comprador,
                COALESCE(e.cEstatus, 'Sin Estatus')                         AS estatus_proyecto,
                COALESCE(v.estatus_proyecto_id, 0)                          AS estatus_proyecto_id,
                COALESCE(cl.clasificacion, 'Sin Clasificación')             AS clasificacion_proyecto,
                pp.fecha_pedido,
                DATE_FORMAT(pp.fecha_pedido, '%d/%m/%Y')                    AS fecha_pedido_formateada,
                CASE WHEN pp.moneda_id = 1 THEN 'MXN' WHEN pp.moneda_id = 3 THEN 'USD' ELSE 'MXN' END AS cmoneda,
                pp.moneda_id,
                COALESCE(pp.subtotal, 0)                                    AS subtotal_pedido,
                COALESCE(pp.iva, 0)                                         AS iva_pedido,
                COALESCE(pp.total, 0)                                       AS monto_pedido,
                ROUND(
                    CASE
                        WHEN pp.moneda_id = 1 THEN COALESCE(pp.total, 0) / COALESCE(NULLIF(tc.valor, 0), 1)
                        ELSE COALESCE(pp.total, 0)
                    END, 2
                )                                                           AS monto_pedido_usd,
                pp.enviado,
                pp.fchregistro                                              AS fecha_registro_pedido,
                DATE_FORMAT(pp.fchregistro, '%d/%m/%Y')                     AS fecha_registro_formateada,
                /* Último seguimiento */
                (SELECT vs.seguimiento
                 FROM tb_ventas_seguimiento vs
                 WHERE vs.venta_id = pp.venta_id
                 ORDER BY vs.id DESC LIMIT 1)                               AS ultimo_seguimiento_nota,
                (SELECT DATE_FORMAT(vs2.fchregistro, '%d/%m/%Y')
                 FROM tb_ventas_seguimiento vs2
                 WHERE vs2.venta_id = pp.venta_id
                 ORDER BY vs2.id DESC LIMIT 1)                              AS ultimo_seguimiento_fecha,
                (SELECT COALESCE(CONCAT_WS(' ', u2.cnombre, u2.cpriapellido, u2.csegapellido), '')
                 FROM tb_ventas_seguimiento vs3
                 LEFT JOIN cat_medico u2 ON u2.ccvemedico = vs3.ccveusuario
                 WHERE vs3.venta_id = pp.venta_id
                 ORDER BY vs3.id DESC LIMIT 1)                              AS ultimo_seguimiento_usuario,
                (SELECT COUNT(*) FROM tb_ventas_seguimiento vs4
                 WHERE vs4.venta_id = pp.venta_id)                         AS total_seguimientos,
                /* Total partidas */
                (SELECT COUNT(*) FROM tb_pedidos_proveedor_detalle ppd
                 WHERE ppd.pedido_proveedor_id = pp.id)                     AS total_partidas,
                (SELECT COUNT(*) FROM tb_pedidos_proveedor_detalle ppd
                 WHERE ppd.pedido_proveedor_id = pp.id AND ppd.entregado = 1) AS total_partidas_entregadas,
                (SELECT COUNT(*) FROM tb_pedidos_proveedor_detalle ppd
                 WHERE ppd.pedido_proveedor_id = pp.id AND (ppd.entregado = 0 OR ppd.entregado IS NULL)) AS total_partidas_pendientes,
                (SELECT COUNT(*) FROM tb_pedidos_proveedor_detalle ppd
                 WHERE ppd.pedido_proveedor_id = pp.id AND ppd.entregado = 2) AS total_partidas_canceladas,
                /* Total adjuntos */
                (SELECT COUNT(*) FROM tb_pedidos_proveedor_adjuntos ppa
                 WHERE ppa.pedido_proveedor_id = pp.id)                     AS total_adjuntos
            FROM tb_pedidos_proveedor pp
            LEFT JOIN tb_ventas v                  ON v.id = pp.venta_id
            LEFT JOIN cat_clientes c               ON (c.id = pp.cliente_id OR c.id = v.cliente_id)
            LEFT JOIN tb_proveedores prov          ON prov.icveProveedor = pp.proveedor_id
            LEFT JOIN cat_medico vd                ON vd.ccvemedico = pp.ccveusuario
            LEFT JOIN cat_estatus_proyecto e       ON e.Id = v.estatus_proyecto_id
            LEFT JOIN cat_clasificacion_proyectos cl ON cl.id = v.clasificacion_proyecto_id
            LEFT JOIN (
                SELECT valor FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc ON 1=1
            WHERE pp.enviado > 0 ";

            $arr_values = [];

            if (!empty($filtro_estatus_orden)) {
                $sql .= " AND pp.enviado = :filtro_estatus_orden ";
                $arr_values['filtro_estatus_orden'] = intval($filtro_estatus_orden);
            }

            if (!empty($ccveusuario)) {
                $sql .= " AND (pp.ccveusuario = :ccveusuario OR v.ccveusuario_vendedor = :ccveusuario_vendedor) ";
                $arr_values['ccveusuario'] = $ccveusuario;
                $arr_values['ccveusuario_vendedor'] = $ccveusuario;
            }

            if (!empty($filtro_num_orden)) {
                $sql .= " AND (pp.folio_ocp LIKE :filtro_num_orden OR pp.id LIKE :filtro_num_orden_id OR pp.oa_proveedor LIKE :filtro_oa) ";
                $arr_values['filtro_num_orden']    = '%' . $filtro_num_orden . '%';
                $arr_values['filtro_num_orden_id'] = '%' . $filtro_num_orden . '%';
                $arr_values['filtro_oa']           = '%' . $filtro_num_orden . '%';
            }

            if (!empty($filtro_proveedor)) {
                $sql .= " AND (prov.cDatGenRazonSocial LIKE :filtro_proveedor OR prov.cDatGenNombreAbreviado LIKE :filtro_proveedor_abr) ";
                $arr_values['filtro_proveedor']     = '%' . $filtro_proveedor . '%';
                $arr_values['filtro_proveedor_abr'] = '%' . $filtro_proveedor . '%';
            }

            if (!empty($fecha_ini) && !empty($fecha_fin)) {
                $sql .= " AND DATE(pp.fecha_pedido) BETWEEN :fecha_ini AND :fecha_fin ";
                $arr_values['fecha_ini'] = $fecha_ini;
                $arr_values['fecha_fin'] = $fecha_fin;
            }

            if (!empty($filtro_estatus)) {
                $sql .= " AND v.estatus_proyecto_id = :estatus_id ";
                $arr_values['estatus_id'] = $filtro_estatus;
            }

            $sql .= " ORDER BY pp.fecha_pedido DESC, pp.id DESC ";

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene los eventos de entregas estimadas de proveedores para el calendario.
     * Consulta tb_pedidos_proveedor_detalle unida a tb_pedidos_proveedor.
     *
     * @param string $fecha_ini
     * @param string $fecha_fin
     * @param string $ccveusuario
     * @return array
     */
    public function selectFechasEntregaProveedores(string $fecha_ini = '', string $fecha_fin = '', string $ccveusuario = ''): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                ppd.id                                                      AS detalle_id,
                ppd.pedido_proveedor_id                                     AS pedido_id,
                pp.venta_id,
                pp.folio_ocp                                                AS num_orden_compra,
                COALESCE(prov.cDatGenRazonSocial, prov.cDatGenNombreAbreviado, 'Sin Proveedor') AS proveedor,
                COALESCE(c.nombre_comercial, 'Sin Cliente')                 AS cliente,
                COALESCE(v.titulo, 'Sin Título')                           AS titulo_venta,
                COALESCE(v.proyecto_id, '')                                 AS proyecto_id,
                ppd.codigo_partida,
                ppd.descripcion,
                ppd.descripcion_adicional,
                ppd.cantidad_pedido,
                ppd.precio_unitario,
                ppd.tiempo_entrega,
                ppd.fecha_estimada_entrega,
                DATE_FORMAT(ppd.fecha_estimada_entrega, '%d/%m/%Y')         AS fecha_estimada_formateada,
                pp.fecha_pedido,
                DATE_FORMAT(pp.fecha_pedido, '%d/%m/%Y')                    AS fecha_pedido_formateada,
                COALESCE(ppd.entregado, 0)                                  AS entregado,
                pp.enviado                                                  AS pedido_enviado,
                /* Datos de Entrada a Almacén y Facturación */
                COALESCE(r.cNumRecibo, '')                                  AS num_recibo,
                r.fchRecibo                                                 AS fecha_recibo,
                DATE_FORMAT(r.fchRecibo, '%d/%m/%Y')                        AS fecha_recibo_formateada,
                COALESCE(r.cNumFactura, '')                                 AS factura_serie_folio,
                COALESCE(r.FolioFiscal, '')                                 AS factura_folio_fiscal
            FROM tb_pedidos_proveedor_detalle ppd
            INNER JOIN tb_pedidos_proveedor pp ON pp.id = ppd.pedido_proveedor_id
            LEFT  JOIN tb_ventas v             ON v.id  = pp.venta_id
            LEFT  JOIN cat_clientes c          ON (c.id = pp.cliente_id OR c.id = v.cliente_id)
            LEFT  JOIN tb_proveedores prov     ON prov.icveProveedor = pp.proveedor_id
            LEFT  JOIN tb_recibos_detalle rd   ON rd.pedido_proveedor_detalle_id = ppd.id
            LEFT  JOIN tb_recibos r            ON r.cNumRecibo = rd.cNumRecibo
            WHERE ppd.fecha_estimada_entrega IS NOT NULL
              AND ppd.fecha_estimada_entrega != '0000-00-00'
              AND pp.enviado > 0 ";

            $arr_values = [];

            if (!empty($ccveusuario)) {
                $sql .= " AND (pp.ccveusuario = :ccveusuario OR v.ccveusuario_vendedor = :ccveusuario_vendedor) ";
                $arr_values['ccveusuario'] = $ccveusuario;
                $arr_values['ccveusuario_vendedor'] = $ccveusuario;
            }

            if (!empty($fecha_ini) && !empty($fecha_fin)) {
                $sql .= " AND (
                    DATE(ppd.fecha_estimada_entrega) BETWEEN :fecha_ini AND :fecha_fin
                    OR DATE(pp.fecha_pedido) BETWEEN :fecha_ini2 AND :fecha_fin2
                ) ";
                $arr_values['fecha_ini']  = $fecha_ini;
                $arr_values['fecha_fin']  = $fecha_fin;
                $arr_values['fecha_ini2'] = $fecha_ini;
                $arr_values['fecha_fin2'] = $fecha_fin;
            }

            $sql .= " ORDER BY ppd.fecha_estimada_entrega ASC ";

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene el detalle completo del encabezado de una orden de compra a proveedor.
     *
     * @param int $pedido_proveedor_id
     * @return array
     */
    public function selectDetallePedidoProveedorCompleto(int $pedido_proveedor_id): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                pp.*,
                DATE_FORMAT(pp.fecha_pedido, '%d/%m/%Y')                    AS fecha_pedido_formateada,
                DATE_FORMAT(pp.fecha_revision, '%d/%m/%Y')                  AS fecha_revision_formateada,
                DATE_FORMAT(pp.fchregistro, '%d/%m/%Y %H:%i')               AS fecha_registro_formateada,
                CASE WHEN pp.moneda_id = 1 THEN 'MXN' WHEN pp.moneda_id = 3 THEN 'USD' ELSE 'MXN' END AS cmoneda,
                COALESCE(prov.cDatGenRazonSocial, prov.cDatGenNombreAbreviado, 'Sin Proveedor') AS proveedor_nombre,
                prov.cDatGenRFC                                             AS proveedor_rfc,
                prov.cContactoNombre                                        AS proveedor_contacto,
                prov.cContactoeMail                                         AS proveedor_email,
                prov.cContactoTel                                           AS proveedor_telefono,
                prov.cDatGenCalleNumero                                     AS proveedor_direccion,
                prov.cDatGenColonia                                         AS proveedor_colonia,
                prov.cDatGenMunicipio                                       AS proveedor_municipio,
                prov.cDatEntidad                                            AS proveedor_estado,
                prov.cDatGenCP                                              AS proveedor_cp,
                COALESCE(c.nombre_comercial, 'Sin Cliente')                 AS cliente_nombre,
                v.proyecto_id                                               AS proyecto_id,
                v.titulo                                                    AS titulo_venta,
                COALESCE(e.cEstatus, 'Sin Estatus')                         AS estatus_proyecto,
                v.estatus_proyecto_id,
                CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS comprador_nombre
            FROM tb_pedidos_proveedor pp
            LEFT JOIN tb_proveedores prov    ON prov.icveProveedor = pp.proveedor_id
            LEFT JOIN cat_clientes c         ON (c.id = pp.cliente_id)
            LEFT JOIN tb_ventas v            ON v.id = pp.venta_id
            LEFT JOIN cat_medico vd          ON vd.ccvemedico = pp.ccveusuario
            LEFT JOIN cat_estatus_proyecto e ON e.Id = v.estatus_proyecto_id
            WHERE pp.id = :pedido_id
            LIMIT 1";

            $arrResponse = $this->selectModel($sql, ['pedido_id' => $pedido_proveedor_id]);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene las partidas de una orden de compra a proveedor (tb_pedidos_proveedor_detalle).
     *
     * @param int $pedido_proveedor_id
     * @return array
     */
    public function selectPartidasPedidoProveedor(int $pedido_proveedor_id): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                ppd.*,
                COALESCE(ppd.entregado, 0) AS entregado,
                DATE_FORMAT(ppd.fecha_estimada_entrega, '%d/%m/%Y') AS fecha_estimada_formateada,
                UPPER(COALESCE(NULLIF(TRIM(ppd.ccveunidad), ''), 'PZA')) AS unidad_medida,
                /* Datos de Entrada a Almacén y Facturación */
                COALESCE(r.cNumRecibo, '') AS num_recibo,
                r.fchRecibo AS fecha_recibo,
                DATE_FORMAT(r.fchRecibo, '%d/%m/%Y') AS fecha_recibo_formateada,
                COALESCE(r.cNumFactura, '') AS factura_serie_folio,
                COALESCE(r.FolioFiscal, '') AS factura_folio_fiscal
            FROM tb_pedidos_proveedor_detalle ppd
            LEFT JOIN tb_recibos_detalle rd ON rd.pedido_proveedor_detalle_id = ppd.id
            LEFT JOIN tb_recibos r ON r.cNumRecibo = rd.cNumRecibo
            WHERE ppd.pedido_proveedor_id = :pedido_id
            ORDER BY ppd.codigo_partida ASC, ppd.id ASC";

            $arrResponse = $this->select($sql, ['pedido_id' => $pedido_proveedor_id]);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene los archivos adjuntos de una orden de compra a proveedor (tb_pedidos_proveedor_adjuntos).
     *
     * @param int $pedido_proveedor_id
     * @return array
     */
    public function selectAdjuntosPedidoProveedor(int $pedido_proveedor_id): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                ppa.id,
                ppa.venta_id,
                ppa.pedido_proveedor_id,
                ppa.archivo,
                'tb_pedidos_proveedor_adjuntos' AS tabla_origen,
                'Pedido Proveedor' AS origen_etiqueta
            FROM tb_pedidos_proveedor_adjuntos ppa
            WHERE ppa.pedido_proveedor_id = :pedido_id
              AND ppa.archivo IS NOT NULL
              AND TRIM(ppa.archivo) != ''
            ORDER BY ppa.id ASC";

            $arrResponse = $this->select($sql, ['pedido_id' => $pedido_proveedor_id]);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene las partidas de una orden de compra de cliente (tb_pedidos_cliente_detalle).
     *
     * @param int $pedido_cliente_id
     * @param int $venta_id
     * @return array
     */
    public function selectPartidasPedidoCliente(int $pedido_cliente_id, int $venta_id = 0): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                pcd.*,
                COALESCE(pcd.entregado, 0) AS entregado,
                DATE_FORMAT(pcd.fecha_estimada_entrega, '%d/%m/%Y') AS fecha_estimada_formateada,
                UPPER(COALESCE(NULLIF(TRIM(pcd.ccveunidad), ''), 'PZA')) AS unidad_medida,
                pcd.ccvematerial AS clave,
                mat.ccveMaterialAlmacen AS ccn,
                sap.clave_cliente AS codigo_cliente,
                ped.num_orden_compra
            FROM tb_pedidos_cliente_detalle pcd
            LEFT JOIN tb_pedidos_cliente ped ON ped.id = pcd.pedido_id
            LEFT JOIN tb_ventas v ON v.id = ped.venta_id
            LEFT JOIN tb_materiales mat ON mat.ccvematerial = pcd.ccvematerial
            LEFT JOIN tb_materiales_claves_sap sap ON (sap.ccvematerial = pcd.ccvematerial AND sap.cliente_id = ped.cliente_id)
            WHERE (pcd.pedido_id = :pedido_id OR (:pedido_id = 0 AND ped.venta_id = :venta_id))
            ORDER BY pcd.codigo_partida ASC, pcd.id ASC";

            $arrResponse = $this->select($sql, [
                'pedido_id' => $pedido_cliente_id,
                'venta_id'  => $venta_id
            ]);

            // Si no se encontraron partidas por pedido_id, buscar por venta_id usando getPartidasProyecto
            if (empty($arrResponse) && $venta_id > 0) {
                $partidasProy = $this->getPartidasProyecto($venta_id);
                if (!empty($partidasProy['partidas'])) {
                    return $partidasProy['partidas'];
                }
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene los archivos adjuntos de una orden de compra de cliente (tb_pedidos_cliente_adjuntos).
     *
     * @param int $pedido_cliente_id
     * @param int $venta_id
     * @return array
     */
    public function selectAdjuntosPedidoCliente(int $pedido_cliente_id, int $venta_id = 0): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                pca.id,
                pca.venta_id,
                pca.pedidos_cliente_id AS pedido_cliente_id,
                pca.archivo,
                'tb_pedidos_cliente_adjuntos' AS tabla_origen,
                'Pedido Cliente' AS origen_etiqueta
            FROM tb_pedidos_cliente_adjuntos pca
            WHERE (pca.pedidos_cliente_id = :pedido_id OR (:pedido_id = 0 AND pca.venta_id = :venta_id))
              AND pca.archivo IS NOT NULL
              AND TRIM(pca.archivo) != ''
            ORDER BY pca.id ASC";

            $arrResponse = $this->select($sql, [
                'pedido_id' => $pedido_cliente_id,
                'venta_id'  => $venta_id
            ]);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * =========================================================================
     * MÓDULO: PARTIDAS PENDIENTES DE COTIZAR
     * =========================================================================
     */

    /**
     * Obtiene la lista de partidas pendientes de cotizar (precio_unitario <= 0 o NULL)
     * vinculando tb_ventas_detalle y tb_compras_cotizaciones_detalle.
     *
     * @param string $fecha_ini
     * @param string $fecha_fin
     * @param string $filtro_proveedor
     * @param string $filtro_proyecto
     * @param string $filtro_solicitud
     * @param string $filtro_antiguedad
     * @param string $ccveusuario_vendedor
     * @param string $filtro_busqueda
     * @return array
     */
    public function selectPartidasPendientesCotizar(
        string $fecha_ini = '',
        string $fecha_fin = '',
        string $filtro_proveedor = '',
        string $filtro_proyecto = '',
        string $filtro_solicitud = '',
        string $filtro_antiguedad = '',
        string $ccveusuario_vendedor = '',
        string $filtro_busqueda = ''
    ): array {
        $arrResponse = array();

        try {
            $arr_values = array();

            $sql = "SELECT 
                        cd.id AS cotizacion_detalle_id,
                        cd.cotizacion_id,
                        cd.venta_id,
                        cd.venta_detalle_id_partida,
                        cd.cantidad,
                        COALESCE(cd.precio_unitario, 0) AS precio_unitario,
                        COALESCE(cd.importe, 0) AS importe,
                        COALESCE(cd.codigo_proveedor, '') AS codigo_proveedor,
                        COALESCE(cd.tiempo_entrega, 'NO REGISTRADO') AS tiempo_entrega,
                        COALESCE(NULLIF(TRIM(cd.descripcion_proveedor), ''), cd.descripcion_adicional, vd.descripcion, 'Sin descripción') AS descripcion_partida,
                        COALESCE(NULLIF(TRIM(cd.ccveunidad), ''), vd.ccveunidad, 'PZA') AS unidad_medida,
                        COALESCE(NULLIF(TRIM(cd.ccvematerial), ''), vd.ccvematerial, '') AS clave_material,
                        
                        -- Datos de la solicitud de cotización
                        COALESCE(cc.folio_solicitud, CONCAT('SC-', cc.id)) AS folio_solicitud,
                        COALESCE(cc.folio_cotizacion, '') AS folio_cotizacion,
                        cc.fecha AS fecha_solicitud,
                        DATE_FORMAT(cc.fecha, '%d/%m/%Y') AS fecha_solicitud_formateada,
                        cc.fchregistro AS fecha_solicitud_registro,
                        DATE_FORMAT(cc.fchregistro, '%d/%m/%Y %H:%i') AS fecha_registro_formateada,
                        COALESCE(cc.enviado, 0) AS solicitud_enviada,
                        
                        -- Proveedor
                        p.icveProveedor AS proveedor_id,
                        COALESCE(NULLIF(TRIM(p.cDatGenNombreAbreviado), ''), p.cDatGenRazonSocial, 'Sin Proveedor') AS proveedor_nombre,
                        COALESCE(p.cDatGenRazonSocial, '') AS proveedor_razon_social,
                        COALESCE(p.cContactoNombre, '') AS proveedor_contacto,
                        COALESCE(p.cContactoeMail, '') AS proveedor_email,
                        COALESCE(p.cDatGenTelefono, '') AS proveedor_telefono,
                        
                        -- Proyecto de Venta
                        v.proyecto_id,
                        COALESCE(v.titulo, 'Sin título') AS proyecto_titulo,
                        v.fecha AS proyecto_fecha,
                        DATE_FORMAT(v.fecha, '%d/%m/%Y') AS proyecto_fecha_formateada,
                        v.estatus_proyecto_id,
                        COALESCE(ep.cEstatus, 'Sin Estatus') AS estatus_proyecto,
                        v.ccveusuario_vendedor,
                        COALESCE(CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido), 'Sin Vendedor') AS vendedor_nombre,
                        
                        -- Cliente
                        COALESCE(cl.nombre_comercial, cl.razon_social, 'Sin Cliente') AS cliente_nombre,
                        
                        -- Partida de Venta
                        COALESCE(vd.codigo_partida, '') AS codigo_partida,
                        COALESCE(vd.clave_sap, '') AS clave_sap,
                        
                        -- Cálculo de tiempo transcurrido (en días y horas)
                        DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) AS dias_transcurridos,
                        TIMESTAMPDIFF(HOUR, COALESCE(cc.fchregistro, cc.fecha), NOW()) AS horas_transcurridas

                    FROM tb_compras_cotizaciones_detalle cd
                    INNER JOIN tb_compras_cotizaciones cc ON cc.id = cd.cotizacion_id
                    LEFT JOIN tb_proveedores p ON p.icveProveedor = cc.proveedor_id
                    LEFT JOIN tb_ventas_detalle vd ON vd.id = cd.venta_detalle_id_partida
                    LEFT JOIN tb_ventas v ON v.id = cd.venta_id
                    LEFT JOIN cat_clientes cl ON cl.id = v.cliente_id
                    LEFT JOIN cat_medico u ON u.ccvemedico = v.ccveusuario_vendedor
                    LEFT JOIN cat_estatus_proyecto ep ON ep.Id = v.estatus_proyecto_id
                    WHERE (cd.precio_unitario <= 0 OR cd.precio_unitario IS NULL) ";

            // Filtro Rango de Fechas
            if (!empty($fecha_ini)) {
                $sql .= " AND DATE(COALESCE(cc.fchregistro, cc.fecha)) >= :fecha_ini ";
                $arr_values['fecha_ini'] = $fecha_ini;
            }
            if (!empty($fecha_fin)) {
                $sql .= " AND DATE(COALESCE(cc.fchregistro, cc.fecha)) <= :fecha_fin ";
                $arr_values['fecha_fin'] = $fecha_fin;
            }

            // Filtro Proveedor
            if (!empty($filtro_proveedor)) {
                if (is_numeric($filtro_proveedor)) {
                    $sql .= " AND cc.proveedor_id = :proveedor_id ";
                    $arr_values['proveedor_id'] = intval($filtro_proveedor);
                } else {
                    $sql .= " AND (p.cDatGenRazonSocial LIKE :prov_str OR p.cDatGenNombreAbreviado LIKE :prov_str) ";
                    $arr_values['prov_str'] = '%' . trim($filtro_proveedor) . '%';
                }
            }

            // Filtro Proyecto
            if (!empty($filtro_proyecto)) {
                if (is_numeric($filtro_proyecto)) {
                    $sql .= " AND (v.id = :proy_id OR v.proyecto_id LIKE :proy_term) ";
                    $arr_values['proy_id'] = intval($filtro_proyecto);
                    $arr_values['proy_term'] = '%' . trim($filtro_proyecto) . '%';
                } else {
                    $sql .= " AND (v.proyecto_id LIKE :proy_term OR v.titulo LIKE :proy_term) ";
                    $arr_values['proy_term'] = '%' . trim($filtro_proyecto) . '%';
                }
            }

            // Filtro Solicitud
            if (!empty($filtro_solicitud)) {
                $sql .= " AND (cc.folio_solicitud LIKE :sol_term OR cc.folio_cotizacion LIKE :sol_term) ";
                $arr_values['sol_term'] = '%' . trim($filtro_solicitud) . '%';
            }

            // Filtro Semáforo de Antigüedad
            if (!empty($filtro_antiguedad)) {
                switch ($filtro_antiguedad) {
                    case 'recientes': // <= 2 días
                        $sql .= " AND DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) <= 2 ";
                        break;
                    case 'espera': // 3 a 5 días
                        $sql .= " AND DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) BETWEEN 3 AND 5 ";
                        break;
                    case 'demoradas': // > 5 días
                        $sql .= " AND DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) > 5 ";
                        break;
                    case 'criticas': // > 10 días
                        $sql .= " AND DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) > 10 ";
                        break;
                }
            }

            // Filtro Vendedor (rol_id = 4)
            if (!empty($ccveusuario_vendedor)) {
                $sql .= " AND v.ccveusuario_vendedor = :ccveusuario_vendedor ";
                $arr_values['ccveusuario_vendedor'] = $ccveusuario_vendedor;
            }

            // Filtro Búsqueda General
            if (!empty($filtro_busqueda)) {
                $sql .= " AND (
                            vd.codigo_partida LIKE :busq_term OR 
                            cd.codigo_proveedor LIKE :busq_term OR 
                            cd.descripcion_proveedor LIKE :busq_term OR 
                            vd.descripcion LIKE :busq_term OR 
                            cd.descripcion_adicional LIKE :busq_term OR 
                            vd.clave_sap LIKE :busq_term OR 
                            cd.ccvematerial LIKE :busq_term OR 
                            cl.nombre_comercial LIKE :busq_term OR 
                            cl.razon_social LIKE :busq_term OR
                            v.titulo LIKE :busq_term OR
                            v.proyecto_id LIKE :busq_term
                        ) ";
                $arr_values['busq_term'] = '%' . trim($filtro_busqueda) . '%';
            }

            $sql .= " ORDER BY dias_transcurridos DESC, cc.id DESC, cd.id ASC";

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene el resumen de KPIs para las partidas pendientes de cotizar
     *
     * @param string $fecha_ini
     * @param string $fecha_fin
     * @param string $filtro_proveedor
     * @param string $filtro_proyecto
     * @param string $filtro_solicitud
     * @param string $filtro_antiguedad
     * @param string $ccveusuario_vendedor
     * @param string $filtro_busqueda
     * @return array
     */
    public function getKPIsPartidasPendientesCotizar(
        string $fecha_ini = '',
        string $fecha_fin = '',
        string $filtro_proveedor = '',
        string $filtro_proyecto = '',
        string $filtro_solicitud = '',
        string $filtro_antiguedad = '',
        string $ccveusuario_vendedor = '',
        string $filtro_busqueda = ''
    ): array {
        $arrDefault = [
            'total_partidas_pendientes' => 0,
            'total_proveedores'         => 0,
            'total_solicitudes'         => 0,
            'total_proyectos'           => 0,
            'total_recientes'           => 0,
            'total_en_espera'           => 0,
            'total_demoradas'           => 0,
            'total_criticas'            => 0,
            'promedio_dias_espera'      => 0
        ];

        try {
            $arr_values = array();

            $sql = "SELECT 
                        COUNT(cd.id) AS total_partidas_pendientes,
                        COUNT(DISTINCT cc.proveedor_id) AS total_proveedores,
                        COUNT(DISTINCT cc.id) AS total_solicitudes,
                        COUNT(DISTINCT cd.venta_id) AS total_proyectos,
                        SUM(CASE WHEN DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) <= 2 THEN 1 ELSE 0 END) AS total_recientes,
                        SUM(CASE WHEN DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) BETWEEN 3 AND 5 THEN 1 ELSE 0 END) AS total_en_espera,
                        SUM(CASE WHEN DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) > 5 THEN 1 ELSE 0 END) AS total_demoradas,
                        SUM(CASE WHEN DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) > 10 THEN 1 ELSE 0 END) AS total_criticas,
                        COALESCE(ROUND(AVG(DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha))), 1), 0) AS promedio_dias_espera
                    FROM tb_compras_cotizaciones_detalle cd
                    INNER JOIN tb_compras_cotizaciones cc ON cc.id = cd.cotizacion_id
                    LEFT JOIN tb_proveedores p ON p.icveProveedor = cc.proveedor_id
                    LEFT JOIN tb_ventas_detalle vd ON vd.id = cd.venta_detalle_id_partida
                    LEFT JOIN tb_ventas v ON v.id = cd.venta_id
                    LEFT JOIN cat_clientes cl ON cl.id = v.cliente_id
                    WHERE (cd.precio_unitario <= 0 OR cd.precio_unitario IS NULL) ";

            if (!empty($fecha_ini)) {
                $sql .= " AND DATE(COALESCE(cc.fchregistro, cc.fecha)) >= :fecha_ini ";
                $arr_values['fecha_ini'] = $fecha_ini;
            }
            if (!empty($fecha_fin)) {
                $sql .= " AND DATE(COALESCE(cc.fchregistro, cc.fecha)) <= :fecha_fin ";
                $arr_values['fecha_fin'] = $fecha_fin;
            }
            if (!empty($filtro_proveedor)) {
                if (is_numeric($filtro_proveedor)) {
                    $sql .= " AND cc.proveedor_id = :proveedor_id ";
                    $arr_values['proveedor_id'] = intval($filtro_proveedor);
                } else {
                    $sql .= " AND (p.cDatGenRazonSocial LIKE :prov_str OR p.cDatGenNombreAbreviado LIKE :prov_str) ";
                    $arr_values['prov_str'] = '%' . trim($filtro_proveedor) . '%';
                }
            }
            if (!empty($filtro_proyecto)) {
                if (is_numeric($filtro_proyecto)) {
                    $sql .= " AND (v.id = :proy_id OR v.proyecto_id LIKE :proy_term) ";
                    $arr_values['proy_id'] = intval($filtro_proyecto);
                    $arr_values['proy_term'] = '%' . trim($filtro_proyecto) . '%';
                } else {
                    $sql .= " AND (v.proyecto_id LIKE :proy_term OR v.titulo LIKE :proy_term) ";
                    $arr_values['proy_term'] = '%' . trim($filtro_proyecto) . '%';
                }
            }
            if (!empty($filtro_solicitud)) {
                $sql .= " AND (cc.folio_solicitud LIKE :sol_term OR cc.folio_cotizacion LIKE :sol_term) ";
                $arr_values['sol_term'] = '%' . trim($filtro_solicitud) . '%';
            }
            if (!empty($filtro_antiguedad)) {
                switch ($filtro_antiguedad) {
                    case 'recientes':
                        $sql .= " AND DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) <= 2 ";
                        break;
                    case 'espera':
                        $sql .= " AND DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) BETWEEN 3 AND 5 ";
                        break;
                    case 'demoradas':
                        $sql .= " AND DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) > 5 ";
                        break;
                    case 'criticas':
                        $sql .= " AND DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) > 10 ";
                        break;
                }
            }
            if (!empty($ccveusuario_vendedor)) {
                $sql .= " AND v.ccveusuario_vendedor = :ccveusuario_vendedor ";
                $arr_values['ccveusuario_vendedor'] = $ccveusuario_vendedor;
            }
            if (!empty($filtro_busqueda)) {
                $sql .= " AND (
                            vd.codigo_partida LIKE :busq_term OR 
                            cd.codigo_proveedor LIKE :busq_term OR 
                            cd.descripcion_proveedor LIKE :busq_term OR 
                            vd.descripcion LIKE :busq_term OR 
                            cd.descripcion_adicional LIKE :busq_term OR 
                            vd.clave_sap LIKE :busq_term OR 
                            cd.ccvematerial LIKE :busq_term OR 
                            cl.nombre_comercial LIKE :busq_term OR 
                            cl.razon_social LIKE :busq_term OR
                            v.titulo LIKE :busq_term OR
                            v.proyecto_id LIKE :busq_term
                        ) ";
                $arr_values['busq_term'] = '%' . trim($filtro_busqueda) . '%';
            }

            $res = $this->select($sql, $arr_values);
            if (!empty($res) && isset($res[0])) {
                return $res[0];
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrDefault;
    }

    /**
     * Obtiene el catálogo de proveedores que tienen solicitudes con partidas pendientes de cotizar.
     *
     * @return array
     */
    public function getProveedoresConPendientesCotizar(): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT DISTINCT 
                        p.icveProveedor AS id,
                        COALESCE(NULLIF(TRIM(p.cDatGenNombreAbreviado), ''), p.cDatGenRazonSocial, 'Sin Nombre') AS nombre,
                        COALESCE(p.cDatGenRazonSocial, '') AS razon_social,
                        COUNT(cd.id) AS total_pendientes
                    FROM tb_compras_cotizaciones_detalle cd
                    INNER JOIN tb_compras_cotizaciones cc ON cc.id = cd.cotizacion_id
                    INNER JOIN tb_proveedores p ON p.icveProveedor = cc.proveedor_id
                    WHERE (cd.precio_unitario <= 0 OR cd.precio_unitario IS NULL)
                    GROUP BY p.icveProveedor, p.cDatGenNombreAbreviado, p.cDatGenRazonSocial
                    ORDER BY total_pendientes DESC, nombre ASC";

            $arrResponse = $this->select($sql, []);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene el catálogo de proyectos de venta que tienen partidas pendientes de cotizar.
     *
     * @param string $ccveusuario_vendedor
     * @return array
     */
    public function getProyectosConPendientesCotizar(string $ccveusuario_vendedor = ''): array
    {
        $arrResponse = array();
        try {
            $arr_values = [];
            $sql = "SELECT DISTINCT 
                        v.id,
                        v.proyecto_id,
                        COALESCE(v.titulo, 'Sin título') AS titulo,
                        COUNT(cd.id) AS total_pendientes
                    FROM tb_compras_cotizaciones_detalle cd
                    INNER JOIN tb_compras_cotizaciones cc ON cc.id = cd.cotizacion_id
                    INNER JOIN tb_ventas v ON v.id = cd.venta_id
                    WHERE (cd.precio_unitario <= 0 OR cd.precio_unitario IS NULL) ";

            if (!empty($ccveusuario_vendedor)) {
                $sql .= " AND v.ccveusuario_vendedor = :ccveusuario_vendedor ";
                $arr_values['ccveusuario_vendedor'] = $ccveusuario_vendedor;
            }

            $sql .= " GROUP BY v.id, v.proyecto_id, v.titulo
                      ORDER BY v.id DESC";

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene el detalle completo de una Solicitud de Cotización,
     * incluyendo todas sus partidas (cotizadas y pendientes) y adjuntos.
     *
     * @param int $cotizacion_id
     * @return array
     */
    public function getDetalleSolicitudCotizacionCompleta(int $cotizacion_id): array
    {
        $arrResponse = [
            'cabecera'  => null,
            'partidas'  => [],
            'adjuntos'  => []
        ];

        try {
            if ($cotizacion_id <= 0) {
                return $arrResponse;
            }

            // 1. Cabecera
            $sqlCab = "SELECT 
                        cc.id AS cotizacion_id,
                        COALESCE(cc.folio_solicitud, CONCAT('SC-', cc.id)) AS folio_solicitud,
                        COALESCE(cc.folio_cotizacion, '') AS folio_cotizacion,
                        cc.fecha AS fecha_solicitud,
                        DATE_FORMAT(cc.fecha, '%d/%m/%Y') AS fecha_solicitud_formateada,
                        cc.fchregistro AS fecha_solicitud_registro,
                        DATE_FORMAT(cc.fchregistro, '%d/%m/%Y %H:%i') AS fecha_registro_formateada,
                        COALESCE(cc.enviado, 0) AS solicitud_enviada,
                        cc.subtotal,
                        cc.iva,
                        cc.total,
                        cc.moneda_id,
                        CASE WHEN cc.moneda_id = 1 THEN 'MXN' WHEN cc.moneda_id = 3 THEN 'USD' ELSE 'USD' END AS moneda,
                        cc.texto_personalizado_compras,
                        
                        -- Proveedor
                        p.icveProveedor AS proveedor_id,
                        COALESCE(NULLIF(TRIM(p.cDatGenNombreAbreviado), ''), p.cDatGenRazonSocial, 'Sin Proveedor') AS proveedor_nombre,
                        COALESCE(p.cDatGenRazonSocial, '') AS proveedor_razon_social,
                        COALESCE(p.cContactoNombre, '') AS proveedor_contacto,
                        COALESCE(p.cContactoeMail, '') AS proveedor_email,
                        COALESCE(p.cDatGenTelefono, '') AS proveedor_telefono,
                        
                        -- Proyecto y Cliente
                        v.id AS venta_id,
                        v.proyecto_id,
                        COALESCE(v.titulo, 'Sin título') AS proyecto_titulo,
                        DATE_FORMAT(v.fecha, '%d/%m/%Y') AS proyecto_fecha_formateada,
                        COALESCE(cl.nombre_comercial, cl.razon_social, 'Sin Cliente') AS cliente_nombre,
                        COALESCE(CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido), 'Sin Vendedor') AS vendedor_nombre,
                        
                        -- Días transcurridos
                        DATEDIFF(NOW(), COALESCE(cc.fchregistro, cc.fecha)) AS dias_transcurridos,
                        TIMESTAMPDIFF(HOUR, COALESCE(cc.fchregistro, cc.fecha), NOW()) AS horas_transcurridas

                    FROM tb_compras_cotizaciones cc
                    LEFT JOIN tb_proveedores p ON p.icveProveedor = cc.proveedor_id
                    LEFT JOIN tb_ventas v ON v.id = cc.venta_id
                    LEFT JOIN cat_clientes cl ON cl.id = v.cliente_id
                    LEFT JOIN cat_medico u ON u.ccvemedico = v.ccveusuario_vendedor
                    WHERE cc.id = :cotizacion_id";

            $arrCab = $this->select($sqlCab, ['cotizacion_id' => $cotizacion_id]);
            if (!empty($arrCab) && isset($arrCab[0])) {
                $arrResponse['cabecera'] = $arrCab[0];
            }

            // 2. Partidas
            $sqlPartidas = "SELECT 
                                cd.id AS cotizacion_detalle_id,
                                cd.cotizacion_id,
                                cd.venta_id,
                                cd.venta_detalle_id_partida,
                                cd.cantidad,
                                COALESCE(cd.precio_unitario, 0) AS precio_unitario,
                                COALESCE(cd.importe, 0) AS importe,
                                COALESCE(cd.codigo_proveedor, '') AS codigo_proveedor,
                                COALESCE(cd.tiempo_entrega, 'NO REGISTRADO') AS tiempo_entrega,
                                COALESCE(NULLIF(TRIM(cd.descripcion_proveedor), ''), cd.descripcion_adicional, vd.descripcion, 'Sin descripción') AS descripcion_partida,
                                COALESCE(NULLIF(TRIM(cd.ccveunidad), ''), vd.ccveunidad, 'PZA') AS unidad_medida,
                                COALESCE(NULLIF(TRIM(cd.ccvematerial), ''), vd.ccvematerial, '') AS clave_material,
                                COALESCE(vd.codigo_partida, '') AS codigo_partida,
                                COALESCE(vd.clave_sap, '') AS clave_sap,
                                CASE WHEN cd.precio_unitario > 0 THEN 1 ELSE 0 END AS esta_cotizada
                            FROM tb_compras_cotizaciones_detalle cd
                            LEFT JOIN tb_ventas_detalle vd ON vd.id = cd.venta_detalle_id_partida
                            WHERE cd.cotizacion_id = :cotizacion_id
                            ORDER BY cd.id ASC";

            $arrResponse['partidas'] = $this->select($sqlPartidas, ['cotizacion_id' => $cotizacion_id]);

            // 3. Adjuntos
            $sqlAdjuntos = "SELECT 
                                ca.id,
                                ca.archivo,
                                'tb_compras_cotizaciones_adjuntos' AS tabla_origen
                            FROM tb_compras_cotizaciones_adjuntos ca
                            WHERE ca.cotizacion_id = :cotizacion_id
                              AND ca.archivo IS NOT NULL
                              AND TRIM(ca.archivo) != ''
                            ORDER BY ca.id ASC";

            $arrResponse['adjuntos'] = $this->select($sqlAdjuntos, ['cotizacion_id' => $cotizacion_id]);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }
}

