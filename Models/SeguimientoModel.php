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
     * @return array
     */
    public function buscarProyectosVenta(string $busqueda): array
    {
        $arrResponse = array();

        try {
            $busqueda = trim($busqueda);
            if (empty($busqueda)) {
                return $arrResponse;
            }

            $term = '%' . $busqueda . '%';

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
                    WHERE v.proyecto_id LIKE :term
                    ORDER BY v.id DESC
                    LIMIT 20";

            $arrResponse = $this->select($sql, ['term' => $term]);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }

    /**
     * Obtiene el detalle completo de un proyecto de venta por su id (venta_id)
     * 
     * @param int $venta_id
     * @return array
     */
    public function getProyectoVentaById(int $venta_id): array
    {
        $arrResponse = array();

        try {
            if ($venta_id <= 0) {
                return $arrResponse;
            }

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
                    WHERE v.id = :venta_id";

            $arrResponse = $this->selectModel($sql, ['venta_id' => $venta_id]);
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
     * @return array
     */
    public function selectProyectosVentaSeguimiento(string $fecha_ini = '', string $fecha_fin = '', string $busqueda = ''): array
    {
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
                $sql .= " AND (v.proyecto_id LIKE :term OR v.titulo LIKE :term OR c.nombre_comercial LIKE :term OR c.razon_social LIKE :term) ";
                $arr_values['term'] = '%' . $busqueda . '%';
            }

            $sql .= " ORDER BY v.id DESC";

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }
}

