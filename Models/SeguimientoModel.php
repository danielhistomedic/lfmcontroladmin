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
                        $sqlVenta = "SELECT id, proyecto_id, titulo, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, total, subtotal, iva, moneda_id, 
                                     CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                     FROM tb_ventas WHERE id = :vId";
                        $regVenta = $this->select($sqlVenta, ['vId' => $venta_id]);
                        if (!empty($regVenta)) {
                            $item['completado'] = true;
                            $item['mensaje']    = 'Registro de Oportunidad de Venta generado en tb_ventas.';
                            $item['registros']  = $regVenta;
                        } else {
                            $item['mensaje']    = 'Sin registro de Oportunidad de Venta.';
                        }
                        break;

                    case 3:
                        // Id 3: EN PROCESO DE COTIZACION - Checar si hay registro en tb_compras_cotizaciones (enviado = 1)
                        $sqlCc = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_compras_cotizaciones 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regCc = $this->select($sqlCc, ['vId' => $venta_id]);
                        if (!empty($regCc)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regCc) . ' cotización(es) de compras finalizada(s) (enviado = 1).';
                            $item['registros']  = $regCc;
                        } else {
                            $item['mensaje']    = 'Sin cotización de compras finalizada.';
                        }
                        break;

                    case 4:
                        // Id 4: COTIZACION INTERNA ELABORADA - Checar si hay registro en tb_compras_cotizacion_interna (enviado = 1)
                        $sqlCi = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_compras_cotizacion_interna 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regCi = $this->select($sqlCi, ['vId' => $venta_id]);
                        if (!empty($regCi)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regCi) . ' cotización(es) interna(s) finalizada(s) (enviado = 1).';
                            $item['registros']  = $regCi;
                        } else {
                            $item['mensaje']    = 'Sin cotización interna finalizada.';
                        }
                        break;

                    case 5:
                        // Id 5: COTIZACION CLIENTE ELABORADA - Checar si hay registro en tb_ventas_cotizacion_cliente (enviado = 1)
                        $sqlVc = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_ventas_cotizacion_cliente 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regVc = $this->select($sqlVc, ['vId' => $venta_id]);
                        if (!empty($regVc)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regVc) . ' cotización(es) a cliente finalizada(s) (enviado = 1).';
                            $item['registros']  = $regVc;
                        } else {
                            $item['mensaje']    = 'Sin cotización a cliente finalizada.';
                        }
                        break;

                    case 6:
                        // Id 6: ORDEN COMPRA CLIENTE (PEDIDO COLOCADO) - Checar si hay registro en tb_ventas_cotizacion_cliente (enviado = 1)
                        $sqlOc = "SELECT id, folio_cotizacion, fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_formateada, subtotal, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_ventas_cotizacion_cliente 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regOc = $this->select($sqlOc, ['vId' => $venta_id]);
                        if (!empty($regOc)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regOc) . ' orden(es) de compra / cotización cliente confirmada(s) (enviado = 1).';
                            $item['registros']  = $regOc;
                        } else {
                            $item['mensaje']    = 'Sin orden de compra de cliente confirmada.';
                        }
                        break;

                    case 7:
                        // Id 7: ORDEN COMPRA PROVEEDOR (PEDIDO ELABORADO) - Checar si hay registro en tb_pedidos_proveedor (enviado = 1)
                        $sqlPp = "SELECT id, folio_ocp, fecha_pedido, DATE_FORMAT(fecha_pedido, '%d/%m/%Y') AS fecha_formateada, subtotal, iva, total, enviado, moneda_id, 
                                  CASE WHEN moneda_id = 1 THEN 'MXN' WHEN moneda_id = 3 THEN 'USD' ELSE 'USD' END AS cmoneda 
                                  FROM tb_pedidos_proveedor 
                                  WHERE venta_id = :vId AND enviado = 1 
                                  ORDER BY id DESC";
                        $regPp = $this->select($sqlPp, ['vId' => $venta_id]);
                        if (!empty($regPp)) {
                            $item['completado'] = true;
                            $item['mensaje']    = count($regPp) . ' orden(es) de compra a proveedor finalizada(s) (enviado = 1).';
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
}


