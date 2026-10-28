<?php

/**
 * Clase VentasModel
 */
class VentasModel extends Mysql
{

    private $id;
    private $cliente_id;
    private $ccveusuario_vendedor;
    private $titulo;
    private $proyecto_id;
    private $fchregistro;
    private $ccveusuario;
    private $fecha;
    private $estatus_proyecto_id;
    private $clues;
    private $clasificacion_proyecto_id;
    private $enviada;
    private $correos;
    private $moneda_id;
    private $subtotal;
    private $iva;
    private $total;

    private $fecha_filtro_ini;
    private $fecha_filtro_fin;


    const TABLA = "tb_ventas";
    const PREFIJO_TABLA = "";

    /**
     * Método Constructor de VentasModel.
     * Inicializa Mysql::__construct
     * 
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene la lista de registros para llenar DataTable o Selects
     * 
     * @return array $arrResponse
     * * Retorna array de tipo:
     *   fetchAll(PDO::FETCH_ASSOC): returns an array containing all of the remaining rows in the result set. 
     * 
     */
    public function selectRecords(VentasModel $modelo): array
    {

        $arrResponse = array();

        try {

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.* as id, ";
            $sql .= "cat_clientes.nombre_comercial, ";
            $sql .= "CONCAT_WS(' ', usr_vendedor.cnombre, usr_vendedor.cpriapellido, usr_vendedor.csegapellido) AS vendedor, ";
            $sql .= "cat_estatus_proyecto.cEstatus as estatus_proyecto, ";
            $sql .= "cat_clasificacion_proyectos.clasificacion as clasificacion_proyecto, ";
            $sql .= "CONCAT_WS(' ', usr_reg.cnombre, usr_reg.cpriapellido, usr_reg.csegapellido) AS registro ";
            $sql .= "FROM tb_ventas t ";
            $sql .= "INNER JOIN cat_clientes ON (cat_clientes.id = t.cliente_id) ";
            $sql .= "LEFT JOIN cat_medico usr_vendedor ON (usr_vendedor.ccvemedico = t.ccveusuario_vendedor) ";
            $sql .= "LEFT JOIN cat_estatus_proyecto ON (cat_estatus_proyecto.Id = t.estatus_proyecto_id) ";
            $sql .= "LEFT JOIN cat_clasificacion_proyectos ON (cat_clasificacion_proyectos.id = t.clasificacion_proyecto_id) ";
            $sql .= "LEFT JOIN cat_medico usr_reg ON (usr_reg.ccvemedico = cat_medico.ccveusuario) ";
            $sql .= "ORDER BY order by t.fecha DESC ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene datos de un Registro determinado.
     * 
     * @return array $arrResponse
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectRecord(VentasModel $modelo): array
    {

        $arrResponse = array();

        try {

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.* as id, ";
            $sql .= "cat_clientes.nombre_comercial, ";
            $sql .= "CONCAT_WS(' ', usr_vendedor.cnombre, usr_vendedor.cpriapellido, usr_vendedor.csegapellido) AS vendedor, ";
            $sql .= "cat_estatus_proyecto.cEstatus as estatus_proyecto, ";
            $sql .= "cat_clasificacion_proyectos.clasificacion as clasificacion_proyecto, ";
            $sql .= "CONCAT_WS(' ', usr_reg.cnombre, usr_reg.cpriapellido, usr_reg.csegapellido) AS registro ";
            $sql .= "FROM tb_ventas t ";
            $sql .= "INNER JOIN cat_clientes ON (cat_clientes.id = t.cliente_id) ";
            $sql .= "LEFT JOIN cat_medico usr_vendedor ON (usr_vendedor.ccvemedico = t.ccveusuario_vendedor) ";
            $sql .= "LEFT JOIN cat_estatus_proyecto ON (cat_estatus_proyecto.Id = t.estatus_proyecto_id) ";
            $sql .= "LEFT JOIN cat_clasificacion_proyectos ON (cat_clasificacion_proyectos.id = t.clasificacion_proyecto_id) ";
            $sql .= "LEFT JOIN cat_medico usr_reg ON (usr_reg.ccvemedico = cat_medico.ccveusuario) ";
            $sql .= "ORDER BY order by t.fecha DESC ";

            $sql .= "where cat_medico.control_rh = 1 AND cat_medico.icvemedico = :registro_id ";


            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'registro_id' => $modelo->getId()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $arrResponse = $this->selectModel($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array asociativo con los datos del registro o empty en caso de error ]*/
        return $arrResponse;
    }
        
    //*==================================================================
    // [ Dashboard ]*/

    /**
     * Obtiene resumen de ventas general
     *
     * @return array $arrResponse
     * * Retorna array de tipo:
     *   fetchAll(PDO::FETCH_ASSOC): returns an array containing all of the remaining rows in the result set. 
     * 
     */
    public function selectResumenGeneralPedidosColocados(string $filtro): array
    {

        $arrResponse = array();

        try {

            //   $sql .= "WHERE v.estatus_proyecto_id >= 6 and (v.clasificacion_proyecto_id = 5 or v.clasificacion_proyecto_id = 2)";

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";


            $sql .= "COUNT(*) AS CantidadVentas, ";

            $sql .= "ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS VentaMXN, ";

            $sql .= "ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS VentaUSD, ";

            $sql .= "ROUND( ";
            $sql .= "    SUM( ";
            $sql .= "        CASE ";
            $sql .= "            WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ";
            $sql .= "            ELSE 0 ";
            $sql .= "        END ";
            $sql .= "    ), 2 ";
            $sql .= ") AS MXN_ConvertidoUSD, ";

            $sql .= "ROUND( ";
            $sql .= "    SUM( ";
            $sql .= "        CASE ";
            $sql .= "            WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ";
            $sql .= "            WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ";
            $sql .= "            ELSE 0 ";
            $sql .= "        END ";
            $sql .= "    ), 2 ";
            $sql .= ") AS TotalUSD, ";

            $sql .= "metas.MetaGlobalUSD, ";

            $sql .= "ROUND( ";
            $sql .= "    ( ";
            $sql .= "        SUM( ";
            $sql .= "            CASE ";
            $sql .= "                WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ";
            $sql .= "                WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ";
            $sql .= "                ELSE 0 ";
            $sql .= "            END ";
            $sql .= "        ) / metas.MetaGlobalUSD ";
            $sql .= "    ) * 100, ";
            $sql .= "    2 ";
            $sql .= ") AS PorcentajeCumplimiento, ";

            $sql .= "ROUND( ";
            $sql .= "    metas.MetaGlobalUSD - ";
            $sql .= "    SUM( ";
            $sql .= "        CASE ";
            $sql .= "            WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ";
            $sql .= "            WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ";
            $sql .= "            ELSE 0 ";
            $sql .= "        END ";
            $sql .= "    ), ";
            $sql .= "    2 ";
            $sql .= ") AS FaltanteUSD ";

            $sql .= "FROM tb_ventas v ";

            $sql .= "CROSS JOIN ( ";
            $sql .= "    SELECT valor ";
            $sql .= "    FROM tb_historial_tipos_cambio ";
            $sql .= "    WHERE idMoneda = 3 ";
            $sql .= "    ORDER BY fecha DESC, id DESC ";
            $sql .= "    LIMIT 1 ";
            $sql .= ") tc ";

            $sql .= "CROSS JOIN ( ";
            $sql .= "    SELECT ";
            $sql .= "        SUM(meta) AS MetaGlobalUSD ";
            $sql .= "    FROM tb_metas ";
            $sql .= "    WHERE anio = YEAR(CURDATE()) ";
            $sql .= ") metas ";

            $sql .= "WHERE YEAR(v.fecha) = YEAR(CURDATE()) ";
            $sql .= "  AND v.estatus_proyecto_id >= 6 ";
            $sql .= "  AND v.clasificacion_proyecto_id IN (2,3,4,5) ";

            $sql .= $filtro;
            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene resumen de ventas general por vendedores
     *
     * @return array $arrResponse
     * * Retorna array de tipo:
     *   fetchAll(PDO::FETCH_ASSOC): returns an array containing all of the remaining rows in the result set. 
     * 
     */
    public function selectResumenGeneralPorVendedorPedidosColocados(string $filtro): array
    {

        $arrResponse = array();

        try {

            //   $sql .= "WHERE v.estatus_proyecto_id >= 6 and (v.clasificacion_proyecto_id = 5 or v.clasificacion_proyecto_id = 2)";

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";

            $sql .= "COUNT(*) AS CantidadVentas, ";

            $sql .= "CONCAT_WS(' ', vd.cNombre, vd.cPriApellido, vd.cSegApellido) AS Vendedor, ";

            $sql .= "ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS VentaMXN, ";

            $sql .= "ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS VentaUSD, ";

            $sql .= "ROUND( ";
            $sql .= "    SUM( ";
            $sql .= "        CASE ";
            $sql .= "            WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ";
            $sql .= "            ELSE 0 ";
            $sql .= "        END ";
            $sql .= "    ), 2 ";
            $sql .= ") AS MXN_ConvertidoUSD, ";

            $sql .= "ROUND( ";
            $sql .= "    SUM( ";
            $sql .= "        CASE ";
            $sql .= "            WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ";
            $sql .= "            WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ";
            $sql .= "            ELSE 0 ";
            $sql .= "        END ";
            $sql .= "    ), 2 ";
            $sql .= ") AS TotalUSD, ";

            $sql .= "COALESCE(m.meta, 0) AS MetaAnualUSD, ";

            $sql .= "ROUND( ";
            $sql .= "    ( ";
            $sql .= "        SUM( ";
            $sql .= "            CASE ";
            $sql .= "                WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ";
            $sql .= "                WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ";
            $sql .= "                ELSE 0 ";
            $sql .= "            END ";
            $sql .= "        ) / NULLIF(m.meta, 0) ";
            $sql .= "    ) * 100, ";
            $sql .= "    2 ";
            $sql .= ") AS PorcentajeCumplimiento, ";

            $sql .= "ROUND( ";
            $sql .= "    COALESCE(m.meta,0) - ";
            $sql .= "    SUM( ";
            $sql .= "        CASE ";
            $sql .= "            WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ";
            $sql .= "            WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ";
            $sql .= "            ELSE 0 ";
            $sql .= "        END ";
            $sql .= "    ), ";
            $sql .= "    2 ";
            $sql .= ") AS FaltanteUSD ";

            $sql .= "FROM tb_ventas v ";

            $sql .= "INNER JOIN cat_medico vd ";
            $sql .= "    ON vd.ccvemedico = v.ccveusuario_vendedor ";

            $sql .= "CROSS JOIN ( ";
            $sql .= "    SELECT valor ";
            $sql .= "    FROM tb_historial_tipos_cambio ";
            $sql .= "    WHERE idMoneda = 3 ";
            $sql .= "    ORDER BY fecha DESC, id DESC ";
            $sql .= "    LIMIT 1 ";
            $sql .= ") tc ";

            $sql .= "LEFT JOIN tb_metas_vendedor m ";
            $sql .= "    ON m.ccvevendedor = vd.ccvemedico ";
            $sql .= "    AND m.anio = YEAR(CURDATE()) ";

            $sql .= "WHERE ";
            $sql .= "    v.estatus_proyecto_id >= 6 ";
            $sql .= "    AND v.clasificacion_proyecto_id IN (2,3,4,5) ";
            $sql .= "    AND YEAR(v.fecha) = YEAR(CURDATE()) ";

            $sql .= "GROUP BY ";
            $sql .= "    vd.ccvemedico ";

            $sql .= "ORDER BY ";
            $sql .= "    TotalUSD DESC;";

            $sql .= $filtro;
            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene el resumen de KPIs para el Dashboard de Ventas
     */
    public function selectResumenDashboardVentas(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            /* -------------------------------------------------------
             * Consulta principal: pipeline (p1, p2), clientes activos,
             * artículos vendidos y meta global.
             * "ganadas" se ejecuta en consulta separada para evitar
             * el error MAX_JOIN_SIZE al combinar múltiples CROSS JOINs.
             * ------------------------------------------------------- */
            $sql = "SELECT
                (p1.p1_count + p2.p2_count) AS count_pipeline,
                ROUND(p1.p1_mxn_only + p2.p2_mxn_only, 2) AS sum_pipeline_mxn,
                ROUND(p1.p1_usd_only + p2.p2_usd_only, 2) AS sum_pipeline_usd,
                ROUND(p1.p1_usd + p2.p2_usd, 2) AS sum_pipeline_combined_usd,
                ROUND(p1.p1_mxn + p2.p2_mxn, 2) AS sum_pipeline_combined_mxn,

                COUNT(DISTINCT v.cliente_id) AS count_clientes_activos,

                /* Artículos vendidos: se calcula en subconsulta independiente para evitar
                   que el JOIN con detalle multiplique las filas de tb_ventas y distorsione
                   todos los SUM() de montos. */
                COALESCE((
                    SELECT SUM(d.cantidad)
                    FROM tb_ventas_detalle d
                    INNER JOIN tb_ventas vd ON vd.id = d.venta_id
                    WHERE vd.estatus_proyecto_id >= 6
                      AND d.cancelado = 0
                      AND DATE(vd.fecha) BETWEEN :fecha_ini_sub AND :fecha_fin_sub
                ), 0) AS total_articulos_vendidos,

                metas.MetaGlobalUSD
            FROM tb_ventas v
            CROSS JOIN (
                SELECT 
                    COALESCE(SUM(cc.subtotal - cc.descuento), 0) AS p1_raw,
                    COALESCE(SUM(
                        CASE WHEN COALESCE(v1.moneda_id, 3) = 1 THEN (cc.subtotal - cc.descuento) / tc1.valor 
                             ELSE (cc.subtotal - cc.descuento) 
                        END
                    ), 0) AS p1_usd,
                    COALESCE(SUM(
                        CASE WHEN COALESCE(v1.moneda_id, 3) = 3 THEN (cc.subtotal - cc.descuento) * tc1.valor 
                             ELSE (cc.subtotal - cc.descuento) 
                        END
                    ), 0) AS p1_mxn,
                    COALESCE(SUM(CASE WHEN COALESCE(v1.moneda_id, 3) = 1 THEN (cc.subtotal - cc.descuento) ELSE 0 END), 0) AS p1_mxn_only,
                    COALESCE(SUM(CASE WHEN COALESCE(v1.moneda_id, 3) = 3 THEN (cc.subtotal - cc.descuento) ELSE 0 END), 0) AS p1_usd_only,
                    COUNT(DISTINCT cc.venta_id) AS p1_count
                FROM tb_ventas_cotizacion_cliente cc
                LEFT JOIN tb_ventas v1 ON v1.id = cc.venta_id
                CROSS JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc1
                WHERE cc.enviado = 1
                  AND DATE(COALESCE(cc.fecha, v1.fecha)) BETWEEN :fecha_ini_p1 AND :fecha_fin_p1
            ) p1
            CROSS JOIN (
                SELECT 
                    COALESCE(SUM(v2.subtotal - v2.descuento), 0) AS p2_raw,
                    COALESCE(SUM(
                        CASE WHEN v2.moneda_id = 1 THEN (v2.subtotal - v2.descuento) / tc2.valor 
                             ELSE (v2.subtotal - v2.descuento) 
                        END
                    ), 0) AS p2_usd,
                    COALESCE(SUM(
                        CASE WHEN v2.moneda_id = 3 THEN (v2.subtotal - v2.descuento) * tc2.valor 
                             ELSE (v2.subtotal - v2.descuento) 
                        END
                    ), 0) AS p2_mxn,
                    COALESCE(SUM(CASE WHEN v2.moneda_id = 1 THEN (v2.subtotal - v2.descuento) ELSE 0 END), 0) AS p2_mxn_only,
                    COALESCE(SUM(CASE WHEN v2.moneda_id = 3 THEN (v2.subtotal - v2.descuento) ELSE 0 END), 0) AS p2_usd_only,
                    COUNT(DISTINCT v2.id) AS p2_count
                FROM tb_ventas v2
                CROSS JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc2
                WHERE COALESCE(v2.activo, 'ACTIVO') = 'ACTIVO'
                  AND v2.estatus_proyecto_id >= 5
                  AND DATE(v2.fecha) BETWEEN :fecha_ini_p2 AND :fecha_fin_p2
                  AND NOT EXISTS (
                      SELECT 1
                      FROM tb_ventas_cotizacion_cliente cc2
                      WHERE cc2.venta_id = v2.id
                  )
            ) p2
            CROSS JOIN (
                SELECT COALESCE(SUM(meta), 0) AS MetaGlobalUSD
                FROM tb_metas
                WHERE anio = YEAR(CURDATE())
            ) metas
            WHERE DATE(v.fecha) BETWEEN :fecha_ini AND :fecha_fin";

            $arr_values = [
                'fecha_ini'     => $fecha_ini,
                'fecha_fin'     => $fecha_fin,
                'fecha_ini_sub' => $fecha_ini,
                'fecha_fin_sub' => $fecha_fin,
                'fecha_ini_p1'  => $fecha_ini,
                'fecha_fin_p1'  => $fecha_fin,
                'fecha_ini_p2'  => $fecha_ini,
                'fecha_fin_p2'  => $fecha_fin,
            ];

            $resumen = $this->select($sql, $arr_values)[0] ?? [];

            /* -------------------------------------------------------
             * Consulta separada: métricas de ventas "ganadas".
             * Se ejecuta en 2 partes (tb_pedidos_cliente + tb_ventas excluidas)
             * y se suman los totales.
             * ------------------------------------------------------- */
            $sql_ganadas = "SELECT
                (COALESCE(g1.g1_count, 0) + COALESCE(g2.g2_count, 0)) AS count_ganadas,
                ROUND(COALESCE(g1.g1_mxn_only, 0) + COALESCE(g2.g2_mxn_only, 0), 2) AS sum_ganadas_mxn,
                ROUND(COALESCE(g1.g1_usd_only, 0) + COALESCE(g2.g2_usd_only, 0), 2) AS sum_ganadas_usd,
                ROUND(COALESCE(g1.g1_usd, 0) + COALESCE(g2.g2_usd, 0), 2) AS sum_ganadas_combined_usd,
                ROUND(COALESCE(g1.g1_mxn, 0) + COALESCE(g2.g2_mxn, 0), 2) AS sum_ganadas_combined_mxn
            FROM (
                SELECT 
                    COALESCE(SUM(pc.subtotal - pc.descuento), 0) AS g1_raw,
                    COALESCE(SUM(
                        CASE WHEN COALESCE(v1.moneda_id, 3) = 1 THEN (pc.subtotal - pc.descuento) / tc1.valor 
                             ELSE (pc.subtotal - pc.descuento) 
                        END
                    ), 0) AS g1_usd,
                    COALESCE(SUM(
                        CASE WHEN COALESCE(v1.moneda_id, 3) = 3 THEN (pc.subtotal - pc.descuento) * tc1.valor 
                             ELSE (pc.subtotal - pc.descuento) 
                        END
                    ), 0) AS g1_mxn,
                    COALESCE(SUM(CASE WHEN COALESCE(v1.moneda_id, 3) = 1 THEN (pc.subtotal - pc.descuento) ELSE 0 END), 0) AS g1_mxn_only,
                    COALESCE(SUM(CASE WHEN COALESCE(v1.moneda_id, 3) = 3 THEN (pc.subtotal - pc.descuento) ELSE 0 END), 0) AS g1_usd_only,
                    COUNT(DISTINCT pc.venta_id) AS g1_count
                FROM tb_pedidos_cliente pc
                LEFT JOIN tb_ventas v1 ON v1.id = pc.venta_id
                CROSS JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc1
                WHERE pc.enviado = 1
                  AND DATE(pc.fecha_pedido) BETWEEN :fecha_ini_g1 AND :fecha_fin_g1
            ) g1
            CROSS JOIN (
                SELECT 
                    COALESCE(SUM(v2.subtotal - v2.descuento), 0) AS g2_raw,
                    COALESCE(SUM(
                        CASE WHEN v2.moneda_id = 1 THEN (v2.subtotal - v2.descuento) / tc2.valor 
                             ELSE (v2.subtotal - v2.descuento) 
                        END
                    ), 0) AS g2_usd,
                    COALESCE(SUM(
                        CASE WHEN v2.moneda_id = 3 THEN (v2.subtotal - v2.descuento) * tc2.valor 
                             ELSE (v2.subtotal - v2.descuento) 
                        END
                    ), 0) AS g2_mxn,
                    COALESCE(SUM(CASE WHEN v2.moneda_id = 1 THEN (v2.subtotal - v2.descuento) ELSE 0 END), 0) AS g2_mxn_only,
                    COALESCE(SUM(CASE WHEN v2.moneda_id = 3 THEN (v2.subtotal - v2.descuento) ELSE 0 END), 0) AS g2_usd_only,
                    COUNT(DISTINCT v2.id) AS g2_count
                FROM tb_ventas v2
                CROSS JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc2
                WHERE COALESCE(v2.activo, 'ACTIVO') = 'ACTIVO'
                  AND v2.estatus_proyecto_id >= 6
                  AND DATE(v2.fecha) BETWEEN :fecha_ini_g2 AND :fecha_fin_g2
                  AND NOT EXISTS (
                      SELECT 1
                      FROM tb_pedidos_cliente pc2
                      WHERE pc2.venta_id = v2.id
                  )
            ) g2";

            $arr_values_ganadas = [
                'fecha_ini_g1' => $fecha_ini,
                'fecha_fin_g1' => $fecha_fin,
                'fecha_ini_g2' => $fecha_ini,
                'fecha_fin_g2' => $fecha_fin,
            ];

            $ganadas = $this->select($sql_ganadas, $arr_values_ganadas)[0] ?? [];
            if (empty($ganadas)) {
                $ganadas = [
                    'count_ganadas'            => 0,
                    'sum_ganadas_mxn'          => 0,
                    'sum_ganadas_usd'          => 0,
                    'sum_ganadas_combined_usd' => 0,
                    'sum_ganadas_combined_mxn' => 0,
                ];
            }

            /* -------------------------------------------------------
             * Combinación PHP: merge de ambos resultados + KPIs
             * calculados a partir de los valores ya obtenidos.
             * ------------------------------------------------------- */
            if (!empty($resumen)) {
                $sumGanadasUSD  = (float)($ganadas['sum_ganadas_combined_usd'] ?? 0);
                $metaGlobalUSD  = (float)($resumen['MetaGlobalUSD'] ?? 0);
                $sumPipelineUSD = (float)($resumen['sum_pipeline_combined_usd'] ?? 0);

                $arrResponse = array_merge($resumen, $ganadas, [
                    'PorcentajeCumplimiento' => $metaGlobalUSD > 0
                        ? round(($sumGanadasUSD / $metaGlobalUSD) * 100, 2)
                        : 0,
                    'FaltanteUSD' => round($metaGlobalUSD - $sumGanadasUSD, 2),
                    'PorcentajeEfectividad'  => $sumPipelineUSD > 0
                        ? round(($sumGanadasUSD / $sumPipelineUSD) * 100, 2)
                        : 0,
                ]);
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene ventas consolidadas por vendedor
     */
    public function selectVentasPorVendedor(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS vendedor,
                COUNT(*) AS count_ventas,
                ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS sum_mxn,
                ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS sum_usd,
                ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_usd,
                ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) * tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_mxn,
                COALESCE(m.meta, 0) AS MetaAnualUSD,
                ROUND(
                    (
                        SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END)
                        / NULLIF(m.meta, 0)
                    ) * 100, 2
                ) AS PorcentajeCumplimiento,
                ROUND(
                    COALESCE(m.meta, 0) -
                    SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END),
                    2
                ) AS FaltanteUSD
            FROM tb_ventas v
            INNER JOIN cat_medico vd ON vd.ccvemedico = v.ccveusuario_vendedor
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            LEFT JOIN tb_metas_vendedor m
                ON m.ccvevendedor = vd.ccvemedico
                AND m.anio = YEAR(CURDATE())
            WHERE v.estatus_proyecto_id >= 6
              AND v.fecha BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY vd.ccvemedico, vd.cnombre, vd.cpriapellido, vd.csegapellido, m.meta
            ORDER BY sum_combined_usd DESC
            LIMIT 10";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene ventas consolidadas por cliente
     */
    public function selectVentasPorCliente(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                c.nombre_comercial AS cliente,
                COUNT(*) AS count_ventas,
                ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS sum_mxn,
                ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS sum_usd,
                ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_usd,
                ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) * tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_mxn
            FROM tb_ventas v
            INNER JOIN cat_clientes c ON c.id = v.cliente_id
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            WHERE v.estatus_proyecto_id >= 6
              AND v.fecha BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY c.id, c.nombre_comercial
            ORDER BY sum_combined_usd DESC
            LIMIT 10";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene ventas consolidadas por clasificación de proyecto
     */
    public function selectVentasPorClasificacion(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                cl.clasificacion AS clasificacion,
                COUNT(*) AS count_ventas,
                ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_usd,
                ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) * tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_mxn
            FROM tb_ventas v
            INNER JOIN cat_clasificacion_proyectos cl ON cl.id = v.clasificacion_proyecto_id
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            WHERE v.estatus_proyecto_id >= 6
              AND v.fecha BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY cl.id, cl.clasificacion
            ORDER BY sum_combined_usd DESC";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene ventas consolidadas por estatus
     */
    public function selectVentasPorEstatus(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                s.cEstatus AS estatus,
                v.estatus_proyecto_id AS estatus_id,
                COUNT(*) AS count_ventas,
                ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_usd,
                ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) * tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_mxn
            FROM tb_ventas v
            LEFT JOIN cat_estatus_proyecto s ON s.Id = v.estatus_proyecto_id
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            WHERE v.fecha BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY v.estatus_proyecto_id, s.cEstatus
            ORDER BY v.estatus_proyecto_id ASC";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene comparativo de ventas (pedidos colocados) vs pipeline activo (Pedidos Cotizados) por fecha
     */
    public function selectVentasVsPipeline(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                fecha_grupo,
                ROUND(SUM(ventas_usd), 2) AS sum_ventas_usd,
                ROUND(SUM(pipeline_usd), 2) AS sum_pipeline_usd,
                ROUND(
                    CASE 
                        WHEN SUM(pipeline_usd) > 0 
                        THEN (SUM(ventas_usd) / SUM(pipeline_usd)) * 100 
                        ELSE 0 
                    END
                , 2) AS PorcentajeEfectividad
            FROM (
                /* Pipeline p1: Cotizaciones enviadas */
                SELECT 
                    DATE_FORMAT(COALESCE(cc.fecha, v1.fecha), '%Y-%m') AS fecha_grupo,
                    0 AS ventas_usd,
                    COALESCE(
                        CASE WHEN COALESCE(v1.moneda_id, 3) = 1 THEN (cc.subtotal - cc.descuento) / tc1.valor 
                             ELSE (cc.subtotal - cc.descuento) 
                        END, 0
                    ) AS pipeline_usd
                FROM tb_ventas_cotizacion_cliente cc
                LEFT JOIN tb_ventas v1 ON v1.id = cc.venta_id
                CROSS JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc1
                WHERE cc.enviado = 1
                  AND DATE(COALESCE(cc.fecha, v1.fecha)) BETWEEN :fecha_ini_p1 AND :fecha_fin_p1

                UNION ALL

                /* Pipeline p2: Ventas sin cotización cliente y estatus >= 5 */
                SELECT 
                    DATE_FORMAT(v2.fecha, '%Y-%m') AS fecha_grupo,
                    0 AS ventas_usd,
                    COALESCE(
                        CASE WHEN v2.moneda_id = 1 THEN (v2.subtotal - v2.descuento) / tc2.valor 
                             ELSE (v2.subtotal - v2.descuento) 
                        END, 0
                    ) AS pipeline_usd
                FROM tb_ventas v2
                CROSS JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc2
                WHERE COALESCE(v2.activo, 'ACTIVO') = 'ACTIVO'
                  AND v2.estatus_proyecto_id >= 5
                  AND DATE(v2.fecha) BETWEEN :fecha_ini_p2 AND :fecha_fin_p2
                  AND NOT EXISTS (
                      SELECT 1
                      FROM tb_ventas_cotizacion_cliente cc2
                      WHERE cc2.venta_id = v2.id
                  )

                UNION ALL

                /* Ventas Ganadas g1: Pedidos cliente enviados */
                SELECT 
                    DATE_FORMAT(pc.fecha_pedido, '%Y-%m') AS fecha_grupo,
                    COALESCE(
                        CASE WHEN COALESCE(v1.moneda_id, 3) = 1 THEN (pc.subtotal - pc.descuento) / tc3.valor 
                             ELSE (pc.subtotal - pc.descuento) 
                        END, 0
                    ) AS ventas_usd,
                    0 AS pipeline_usd
                FROM tb_pedidos_cliente pc
                LEFT JOIN tb_ventas v1 ON v1.id = pc.venta_id
                CROSS JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc3
                WHERE pc.enviado = 1
                  AND DATE(pc.fecha_pedido) BETWEEN :fecha_ini_g1 AND :fecha_fin_g1

                UNION ALL

                /* Ventas Ganadas g2: Ventas sin pedido cliente y estatus >= 6 */
                SELECT 
                    DATE_FORMAT(v2.fecha, '%Y-%m') AS fecha_grupo,
                    COALESCE(
                        CASE WHEN v2.moneda_id = 1 THEN (v2.subtotal - v2.descuento) / tc4.valor 
                             ELSE (v2.subtotal - v2.descuento) 
                        END, 0
                    ) AS ventas_usd,
                    0 AS pipeline_usd
                FROM tb_ventas v2
                CROSS JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc4
                WHERE COALESCE(v2.activo, 'ACTIVO') = 'ACTIVO'
                  AND v2.estatus_proyecto_id >= 6
                  AND DATE(v2.fecha) BETWEEN :fecha_ini_g2 AND :fecha_fin_g2
                  AND NOT EXISTS (
                      SELECT 1
                      FROM tb_pedidos_cliente pc2
                      WHERE pc2.venta_id = v2.id
                  )
            ) t
            WHERE fecha_grupo IS NOT NULL
            GROUP BY fecha_grupo
            ORDER BY fecha_grupo ASC";

            $arr_values = [
                'fecha_ini_p1' => $fecha_ini,
                'fecha_fin_p1' => $fecha_fin,
                'fecha_ini_p2' => $fecha_ini,
                'fecha_fin_p2' => $fecha_fin,
                'fecha_ini_g1' => $fecha_ini,
                'fecha_fin_g1' => $fecha_fin,
                'fecha_ini_g2' => $fecha_ini,
                'fecha_fin_g2' => $fecha_fin,
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene ventas consolidadas en tendencia mensual
     */
    public function selectVentasTendencia(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                DATE_FORMAT(v.fecha, '%Y-%m') AS fecha_grupo,
                COALESCE(ROUND(SUM(CASE WHEN v.estatus_proyecto_id IN (11, 12) THEN (CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END) ELSE 0 END), 2), 0) AS sum_facturado_usd,
                COALESCE(ROUND(SUM(CASE WHEN v.estatus_proyecto_id = 12 THEN (CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END) ELSE 0 END), 2), 0) AS sum_pagado_usd
            FROM tb_ventas v
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            WHERE v.estatus_proyecto_id >= 6
              AND v.estatus_proyecto_id IN (11, 12)
              AND v.fecha BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY DATE_FORMAT(v.fecha, '%Y-%m')
            ORDER BY fecha_grupo ASC";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene los productos más vendidos (Top 10)
     */
    public function selectProductosMasVendidos(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                d.ccvematerial AS codigo,
                d.descripcion AS descripcion,
                SUM(d.cantidad) AS cantidad_vendida,
                COUNT(DISTINCT v.id) AS count_pedidos
            FROM tb_ventas_detalle d
            INNER JOIN tb_ventas v ON v.id = d.venta_id
            WHERE v.estatus_proyecto_id >= 6
              AND d.cancelado = 0
              AND v.fecha BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY d.ccvematerial, d.descripcion
            ORDER BY cantidad_vendida DESC
            LIMIT 10";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene el Top 10 de Refacciones y Materiales Más Vendidos por cantidad.
     * Función independiente para el informe del Dashboard de Ventas,
     * separada de selectResumenDashboardVentas para no interferir con los KPIs.
     */
    public function selectTopRefaccionesMasVendidas(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                d.ccvematerial       AS codigo,
                d.descripcion        AS descripcion,
                SUM(d.cantidad)      AS cantidad_vendida,
                COUNT(DISTINCT v.id) AS count_pedidos
            FROM tb_ventas_detalle d
            INNER JOIN tb_ventas v ON v.id = d.venta_id
            WHERE v.estatus_proyecto_id >= 6
              AND d.cancelado = 0
              AND v.fecha BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY d.ccvematerial, d.descripcion
            ORDER BY cantidad_vendida DESC
            LIMIT 10";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin,
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene la lista detallada de pedidos colocados (estatus_proyecto_id >= 6) en un rango de fechas.
     */
    public function selectPedidosColocados(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                v.id,
                v.titulo,
                v.proyecto_id,
                v.clues,
                v.fecha,
                DATE_FORMAT(v.fecha, '%d/%m/%Y') AS fecha_formateada,
                COALESCE(pc.monto, (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0))) AS total,
                v.moneda_id,
                CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                ROUND(CASE WHEN v.moneda_id = 1 THEN COALESCE(pc.monto, (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0))) / COALESCE(NULLIF(tc.valor, 0), 1) ELSE COALESCE(pc.monto, (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0))) END, 2) AS total_usd,
                COALESCE(c.nombre_comercial, 'Sin Cliente') AS cliente,
                CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS vendedor,
                COALESCE(cl.clasificacion, 'Sin Clasificación') AS clasificacion_proyecto,
                COALESCE(e.cEstatus, 'Sin Estatus') AS estatus_proyecto
            FROM tb_ventas v
            LEFT JOIN (
                SELECT 
                    venta_id,
                    SUM(COALESCE(subtotal, 0) - COALESCE(descuento, 0)) AS monto
                FROM tb_pedidos_cliente
                WHERE enviado = 1
                GROUP BY venta_id
            ) pc ON pc.venta_id = v.id
            LEFT JOIN cat_clientes c ON c.id = v.cliente_id
            LEFT JOIN cat_medico vd ON vd.ccvemedico = v.ccveusuario_vendedor
            LEFT JOIN cat_clasificacion_proyectos cl ON cl.id = v.clasificacion_proyecto_id
            LEFT JOIN cat_estatus_proyecto e ON e.Id = v.estatus_proyecto_id
            LEFT JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc ON 1=1
            WHERE v.estatus_proyecto_id >= 6
              AND DATE(v.fecha) BETWEEN :fecha_ini AND :fecha_fin
            ORDER BY total_usd DESC";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene la lista detallada de pedidos cotizados para el modal del KPI "Pipeline Activo",
     * combinando Parte 1 (tb_ventas_cotizacion_cliente enviado=1) y Parte 2 (tb_ventas estatus >= 5 y activo).
     */
    public function selectPedidosCotizados(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "(
                SELECT 
                    v.id,
                    v.titulo,
                    v.proyecto_id,
                    v.clues,
                    COALESCE(MAX(cc.fecha), v.fecha) AS fecha,
                    DATE_FORMAT(COALESCE(MAX(cc.fecha), v.fecha), '%d/%m/%Y') AS fecha_formateada,
                    SUM(COALESCE(cc.subtotal, 0) - COALESCE(cc.descuento, 0)) AS total,
                    v.moneda_id,
                    CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                    ROUND(
                        CASE 
                            WHEN v.moneda_id = 1 THEN SUM(COALESCE(cc.subtotal, 0) - COALESCE(cc.descuento, 0)) / COALESCE(NULLIF(tc.valor, 0), 1) 
                            ELSE SUM(COALESCE(cc.subtotal, 0) - COALESCE(cc.descuento, 0)) 
                        END, 
                        2
                    ) AS total_usd,
                    COALESCE(c.nombre_comercial, 'Sin Cliente') AS cliente,
                    CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS vendedor,
                    COALESCE(cl.clasificacion, 'Sin Clasificación') AS clasificacion_proyecto,
                    COALESCE(e.cEstatus, 'Sin Estatus') AS estatus_proyecto,
                    v.estatus_proyecto_id AS valida_colocados,
                    COALESCE(v.activo, 'ACTIVO') AS activo
                FROM tb_ventas_cotizacion_cliente cc
                INNER JOIN tb_ventas v ON v.id = cc.venta_id
                LEFT JOIN cat_clientes c ON c.id = v.cliente_id
                LEFT JOIN cat_medico vd ON vd.ccvemedico = v.ccveusuario_vendedor
                LEFT JOIN cat_clasificacion_proyectos cl ON cl.id = v.clasificacion_proyecto_id
                LEFT JOIN cat_estatus_proyecto e ON e.Id = v.estatus_proyecto_id
                LEFT JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc ON 1=1
                WHERE cc.enviado = 1
                  AND DATE(COALESCE(cc.fecha, v.fecha)) BETWEEN :fecha_ini_1 AND :fecha_fin_1
                GROUP BY cc.venta_id
            )
            UNION ALL
            (
                SELECT 
                    v.id,
                    v.titulo,
                    v.proyecto_id,
                    v.clues,
                    v.fecha,
                    DATE_FORMAT(v.fecha, '%d/%m/%Y') AS fecha_formateada,
                    (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0)) AS total,
                    v.moneda_id,
                    CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                    ROUND(
                        CASE 
                            WHEN v.moneda_id = 1 THEN (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0)) / COALESCE(NULLIF(tc.valor, 0), 1) 
                            ELSE (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0)) 
                        END, 
                        2
                    ) AS total_usd,
                    COALESCE(c.nombre_comercial, 'Sin Cliente') AS cliente,
                    CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS vendedor,
                    COALESCE(cl.clasificacion, 'Sin Clasificación') AS clasificacion_proyecto,
                    COALESCE(e.cEstatus, 'Sin Estatus') AS estatus_proyecto,
                    v.estatus_proyecto_id AS valida_colocados,
                    COALESCE(v.activo, 'ACTIVO') AS activo
                FROM tb_ventas v
                LEFT JOIN cat_clientes c ON c.id = v.cliente_id
                LEFT JOIN cat_medico vd ON vd.ccvemedico = v.ccveusuario_vendedor
                LEFT JOIN cat_clasificacion_proyectos cl ON cl.id = v.clasificacion_proyecto_id
                LEFT JOIN cat_estatus_proyecto e ON e.Id = v.estatus_proyecto_id
                LEFT JOIN (
                    SELECT valor
                    FROM tb_historial_tipos_cambio
                    WHERE idMoneda = 3
                    ORDER BY fecha DESC, id DESC
                    LIMIT 1
                ) tc ON 1=1
                WHERE COALESCE(v.activo, 'ACTIVO') = 'ACTIVO'
                  AND v.estatus_proyecto_id >= 5
                  AND DATE(v.fecha) BETWEEN :fecha_ini_2 AND :fecha_fin_2
                  AND NOT EXISTS (
                      SELECT 1
                      FROM tb_ventas_cotizacion_cliente cc2
                      WHERE cc2.venta_id = v.id
                  )
            )
            ORDER BY total_usd DESC";

            $arr_values = [
                'fecha_ini_1' => $fecha_ini,
                'fecha_fin_1' => $fecha_fin,
                'fecha_ini_2' => $fecha_ini,
                'fecha_fin_2' => $fecha_fin,
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }


    /**
     * Obtiene la lista detallada de clientes activos (con ventas colocadas) en un rango de fechas.
     */
    public function selectListaClientesActivos(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                v.cliente_id,
                COALESCE(c.nombre_comercial, 'Sin Cliente') AS cliente,
                COALESCE(c.razon_social, '') AS razon_social,
                COALESCE(c.rfc, '') AS rfc,
                COUNT(v.id) AS count_pedidos,
                ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS sum_mxn,
                ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) ELSE 0 END), 2) AS sum_usd,
                ROUND(SUM(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_usd,
                ROUND(SUM(CASE WHEN v.moneda_id = 3 THEN (v.subtotal - v.descuento) * tc.valor ELSE (v.subtotal - v.descuento) END), 2) AS sum_combined_mxn
            FROM tb_ventas v
            LEFT JOIN cat_clientes c ON c.id = v.cliente_id
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            WHERE v.estatus_proyecto_id >= 6
              AND DATE(v.fecha) BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY v.cliente_id, c.nombre_comercial, c.razon_social, c.rfc
            ORDER BY sum_combined_usd DESC";

            $arr_values = [
                'fecha_ini' => $fecha_ini,
                'fecha_fin' => $fecha_fin
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    //*==================================================================
    // [ GETTERS & SETTERS ]*/



    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of cliente_id
     */
    public function getClienteId()
    {
        return $this->cliente_id;
    }

    /**
     * Set the value of cliente_id
     */
    public function setClienteId($cliente_id): self
    {
        $this->cliente_id = $cliente_id;

        return $this;
    }

    /**
     * Get the value of ccveusuario_vendedor
     */
    public function getCcveusuarioVendedor()
    {
        return $this->ccveusuario_vendedor;
    }

    /**
     * Set the value of ccveusuario_vendedor
     */
    public function setCcveusuarioVendedor($ccveusuario_vendedor): self
    {
        $this->ccveusuario_vendedor = $ccveusuario_vendedor;

        return $this;
    }

    /**
     * Get the value of titulo
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set the value of titulo
     */
    public function setTitulo($titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get the value of proyecto_id
     */
    public function getProyectoId()
    {
        return $this->proyecto_id;
    }

    /**
     * Set the value of proyecto_id
     */
    public function setProyectoId($proyecto_id): self
    {
        $this->proyecto_id = $proyecto_id;

        return $this;
    }

    /**
     * Get the value of fchregistro
     */
    public function getFchregistro()
    {
        return $this->fchregistro;
    }

    /**
     * Set the value of fchregistro
     */
    public function setFchregistro($fchregistro): self
    {
        $this->fchregistro = $fchregistro;

        return $this;
    }

    /**
     * Get the value of ccveusuario
     */
    public function getCcveusuario()
    {
        return $this->ccveusuario;
    }

    /**
     * Set the value of ccveusuario
     */
    public function setCcveusuario($ccveusuario): self
    {
        $this->ccveusuario = $ccveusuario;

        return $this;
    }

    /**
     * Get the value of fecha
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set the value of fecha
     */
    public function setFecha($fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get the value of estatus_proyecto_id
     */
    public function getEstatusProyectoId()
    {
        return $this->estatus_proyecto_id;
    }

    /**
     * Set the value of estatus_proyecto_id
     */
    public function setEstatusProyectoId($estatus_proyecto_id): self
    {
        $this->estatus_proyecto_id = $estatus_proyecto_id;

        return $this;
    }

    /**
     * Get the value of clues
     */
    public function getClues()
    {
        return $this->clues;
    }

    /**
     * Set the value of clues
     */
    public function setClues($clues): self
    {
        $this->clues = $clues;

        return $this;
    }

    /**
     * Get the value of clasificacion_proyecto_id
     */
    public function getClasificacionProyectoId()
    {
        return $this->clasificacion_proyecto_id;
    }

    /**
     * Set the value of clasificacion_proyecto_id
     */
    public function setClasificacionProyectoId($clasificacion_proyecto_id): self
    {
        $this->clasificacion_proyecto_id = $clasificacion_proyecto_id;

        return $this;
    }

    /**
     * Get the value of enviada
     */
    public function getEnviada()
    {
        return $this->enviada;
    }

    /**
     * Set the value of enviada
     */
    public function setEnviada($enviada): self
    {
        $this->enviada = $enviada;

        return $this;
    }

    /**
     * Get the value of correos
     */
    public function getCorreos()
    {
        return $this->correos;
    }

    /**
     * Set the value of correos
     */
    public function setCorreos($correos): self
    {
        $this->correos = $correos;

        return $this;
    }

    /**
     * Get the value of moneda_id
     */
    public function getMonedaId()
    {
        return $this->moneda_id;
    }

    /**
     * Set the value of moneda_id
     */
    public function setMonedaId($moneda_id): self
    {
        $this->moneda_id = $moneda_id;

        return $this;
    }

    /**
     * Get the value of subtotal
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * Set the value of subtotal
     */
    public function setSubtotal($subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * Get the value of iva
     */
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * Set the value of iva
     */
    public function setIva($iva): self
    {
        $this->iva = $iva;

        return $this;
    }

    /**
     * Get the value of total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set the value of total
     */
    public function setTotal($total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get the value of fecha_filtro_ini
     */
    public function getFechaFiltroIni()
    {
        return $this->fecha_filtro_ini;
    }

    /**
     * Set the value of fecha_filtro_ini
     */
    public function setFechaFiltroIni($fecha_filtro_ini): self
    {
        $this->fecha_filtro_ini = $fecha_filtro_ini;

        return $this;
    }

    /**
     * Get the value of fecha_filtro_fin
     */
    public function getFechaFiltroFin()
    {
        return $this->fecha_filtro_fin;
    }

    /**
     * Set the value of fecha_filtro_fin
     */
    public function setFechaFiltroFin($fecha_filtro_fin): self
    {
        $this->fecha_filtro_fin = $fecha_filtro_fin;

        return $this;
    }

    /**
     * Obtiene la lista de seguimientos de una venta específica desde tb_ventas_seguimiento.
     */
    public function selectSeguimientoVenta(int $venta_id): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT s.*,
                           COALESCE(CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido), '') AS nombre_usuario
                    FROM tb_ventas_seguimiento s
                    LEFT JOIN cat_medico u ON u.ccvemedico = s.ccveusuario
                    WHERE s.venta_id = :venta_id
                    ORDER BY s.id DESC";
            $arr_values = ['venta_id' => $venta_id];
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            try {
                $sql = "SELECT s.* FROM tb_ventas_seguimiento s WHERE s.venta_id = :venta_id ORDER BY s.id DESC";
                $arr_values = ['venta_id' => $venta_id];
                $arrResponse = $this->select($sql, $arr_values);
            } catch (\Throwable $th2) {
                getLoggerSystem()->error(getMensajeError($th2));
            }
        }
        return $arrResponse;
    }

    /**
     * Obtiene la lista completa de órdenes de clientes con su seguimiento para la vista de seguimiento.
     * Une tb_pedidos_cliente con tb_ventas y tb_ventas_seguimiento.
     * Añade ordenes_proveedor como lista de folio_ocp, fecha_ocp,
     * fecha_ocp_formateada y proveedor por cada pedido. Incluye todas sus OCP,
     * ordenadas por fecha e ID, sin multiplicar pedidos ni sus importes.
     * Expone entregado del pedido de cliente para el Estatus OCC, separado del estatus del proyecto.
     * Añade cumplimiento_entregas sólo para OCC 0/3; null indica que no aplica.
     * comparativo_entregas contiene cruces por OCC, venta_detalle_id y codigo_partida.
     * Un número de orden tiene prioridad sobre el periodo, manteniendo finalización y alcance.
     *
     * @return array $arrResponse
     */
    public function selectOrdenesClienteSeguimiento(string $fecha_ini = '', string $fecha_fin = '', string $filtro_estatus = '', string $filtro_cliente = '', string $ccveusuario_vendedor = '', string $filtro_num_orden = ''): array
    {
        $arrResponse = array();

        try {

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT
                pc.id                                                       AS pedido_id,
                pc.num_orden_compra,
                pc.entregado,
                pc.venta_id,
                v.titulo                                                    AS titulo_venta,
                v.proyecto_id,
                v.clues,
                COALESCE(c.nombre_comercial, 'Sin Cliente')                 AS cliente,
                CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS vendedor,
                COALESCE(e.cEstatus, 'Sin Estatus')                         AS estatus_proyecto,
                v.estatus_proyecto_id,
                COALESCE(cl.clasificacion, 'Sin Clasificación')             AS clasificacion_proyecto,
                pc.fecha_pedido,
                DATE_FORMAT(pc.fecha_pedido, '%d/%m/%Y')                    AS fecha_pedido_formateada,
                CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                v.moneda_id,
                COALESCE(pc.subtotal, 0) - COALESCE(pc.descuento, 0)       AS monto_pedido,
                ROUND(
                    CASE
                        WHEN v.moneda_id = 1 THEN (COALESCE(pc.subtotal, 0) - COALESCE(pc.descuento, 0)) / COALESCE(NULLIF(tc.valor, 0), 1)
                        ELSE (COALESCE(pc.subtotal, 0) - COALESCE(pc.descuento, 0))
                    END, 2
                )                                                           AS monto_pedido_usd,
                pc.enviado,
                pc.fchregistro                                              AS fecha_registro_pedido,
                DATE_FORMAT(pc.fchregistro, '%d/%m/%Y')                     AS fecha_registro_formateada,
                /* Último seguimiento */
                (SELECT vs.seguimiento
                 FROM tb_ventas_seguimiento vs
                 WHERE vs.venta_id = pc.venta_id
                 ORDER BY vs.id DESC LIMIT 1)                               AS ultimo_seguimiento_nota,
                (SELECT DATE_FORMAT(vs2.fchregistro, '%d/%m/%Y')
                 FROM tb_ventas_seguimiento vs2
                 WHERE vs2.venta_id = pc.venta_id
                 ORDER BY vs2.id DESC LIMIT 1)                              AS ultimo_seguimiento_fecha,
                (SELECT COALESCE(CONCAT_WS(' ', u2.cnombre, u2.cpriapellido, u2.csegapellido), '')
                 FROM tb_ventas_seguimiento vs3
                 LEFT JOIN cat_medico u2 ON u2.ccvemedico = vs3.ccveusuario
                 WHERE vs3.venta_id = pc.venta_id
                 ORDER BY vs3.id DESC LIMIT 1)                              AS ultimo_seguimiento_usuario,
                (SELECT COUNT(*) FROM tb_ventas_seguimiento vs4
                 WHERE vs4.venta_id = pc.venta_id)                         AS total_seguimientos
            FROM tb_pedidos_cliente pc
            INNER JOIN tb_ventas v     ON v.id   = pc.venta_id
            LEFT  JOIN cat_clientes c  ON c.id   = v.cliente_id
            LEFT  JOIN cat_medico vd   ON vd.ccvemedico = v.ccveusuario_vendedor
            LEFT  JOIN cat_estatus_proyecto e   ON e.Id  = v.estatus_proyecto_id
            LEFT  JOIN cat_clasificacion_proyectos cl ON cl.id = v.clasificacion_proyecto_id
            LEFT  JOIN (
                SELECT valor FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc ON 1=1
            WHERE pc.enviado = 1 ";

            /*-------------------------------------------
            [ Filtros opcionales ]*/
            $arr_values = [];

            if (!empty($ccveusuario_vendedor)) {
                $sql .= " AND v.ccveusuario_vendedor = :ccveusuario_vendedor ";
                $arr_values['ccveusuario_vendedor'] = $ccveusuario_vendedor;
            }

            if (trim($filtro_num_orden) !== '') {
                $sql .= " AND (pc.num_orden_compra LIKE :num_orden_compra OR pc.id LIKE :num_orden_compra_id) ";
                $arr_values['num_orden_compra']    = '%' . trim($filtro_num_orden) . '%';
                $arr_values['num_orden_compra_id'] = '%' . trim($filtro_num_orden) . '%';
            }

            if (trim($filtro_num_orden) === '' && !empty($fecha_ini) && !empty($fecha_fin)) {
                $sql .= " AND DATE(pc.fecha_pedido) BETWEEN :fecha_ini AND :fecha_fin ";
                $arr_values['fecha_ini'] = $fecha_ini;
                $arr_values['fecha_fin'] = $fecha_fin;
            }

            if (!empty($filtro_estatus)) {
                $sql .= " AND v.estatus_proyecto_id = :estatus_id ";
                $arr_values['estatus_id'] = $filtro_estatus;
            }

            if (!empty($filtro_cliente)) {
                $sql .= " AND v.cliente_id = :cliente_id ";
                $arr_values['cliente_id'] = $filtro_cliente;
            }

            $sql .= " ORDER BY pc.fecha_pedido DESC, pc.id DESC ";

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);

            // Sólo se consultan OCP de los pedidos que ya pasaron los filtros de acceso.
            // El lote evita N+1 y la truncación que podría introducir GROUP_CONCAT.
            if (!empty($arrResponse)) {
                $pedidoIds = array_values(array_unique(array_column($arrResponse, 'pedido_id')));
                $ocpValues = [];
                $ocpParams = [];
                foreach ($pedidoIds as $index => $pedidoId) {
                    $param = 'pedido_cliente_' . $index;
                    $ocpParams[] = ':' . $param;
                    $ocpValues[$param] = (int)$pedidoId;
                }

                $sqlOcp = "SELECT pp.pedido_cliente_id,
                                  pp.folio_ocp,
                                  pp.fecha_pedido AS fecha_ocp,
                                  DATE_FORMAT(pp.fecha_pedido, '%d/%m/%Y') AS fecha_ocp_formateada,
                                  COALESCE(NULLIF(prov.cDatGenRazonSocial, ''),
                                           NULLIF(prov.cDatGenNombreAbreviado, ''), 'Sin Proveedor') AS proveedor
                           FROM tb_pedidos_proveedor pp
                           INNER JOIN tb_pedidos_cliente pc ON pc.id = pp.pedido_cliente_id AND pc.enviado = 1
                           LEFT JOIN tb_proveedores prov ON prov.icveProveedor = pp.proveedor_id
                           WHERE pp.enviado = 1 AND pp.pedido_cliente_id IN (" . implode(', ', $ocpParams) . ")
                           ORDER BY pp.fecha_pedido ASC, pp.id ASC";
                $ocpPorPedido = [];
                foreach ($this->select($sqlOcp, $ocpValues) as $ocp) {
                    $ocpPorPedido[(int)$ocp['pedido_cliente_id']][] = [
                        'folio_ocp' => $ocp['folio_ocp'],
                        'fecha_ocp' => $ocp['fecha_ocp'],
                        'fecha_ocp_formateada' => $ocp['fecha_ocp_formateada'],
                        'proveedor' => $ocp['proveedor'],
                    ];
                }
                foreach ($arrResponse as &$pedido) {
                    $pedido['ordenes_proveedor'] = $ocpPorPedido[(int)$pedido['pedido_id']] ?? [];
                }
                unset($pedido);

                // El resumen cuenta partidas, no cantidades ni OCP. Sólo se aplica a OCC 0/3.
                $pedidosPendientes = [];
                foreach ($arrResponse as $pedido) {
                    if (in_array((string)($pedido['entregado'] ?? ''), ['0', '3'], true)) {
                        $pedidosPendientes[] = (int)$pedido['pedido_id'];
                    }
                }
                $resumenes = $this->selectResumenCumplimientoPedidosCliente($pedidosPendientes);
                foreach ($arrResponse as &$pedido) {
                    $pedido['cumplimiento_entregas'] = $resumenes[(int)$pedido['pedido_id']] ?? null;
                }
                unset($pedido);
                $comparativos = $this->selectComparativoEntregasClienteProveedor($pedidoIds);
                foreach ($arrResponse as &$pedido) {
                    $pedido['comparativo_entregas'] = $comparativos[(int)$pedido['pedido_id']] ?? [];
                }
                unset($pedido);
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna el array de la consulta ]*/
        return $arrResponse;
    }

    /**
     * Consulta por lote las fechas comprometidas de partidas cliente/proveedor.
     * Sólo recibe OCC ya filtradas por la lista; las OCP deben estar finalizadas
     * (enviado = 1), igual que el calendario de proveedor. También valida la OCC.
     * No une recibos, para
     * evitar duplicación por recepciones parciales. No compara cantidades.
     *
     * @param array $pedidoIds Identificadores internos de OCC autorizadas por la lista.
     * @return array Cruces agrupados por OCC; mantiene cada OCP relacionada individualmente.
     */
    public function selectComparativoEntregasClienteProveedor(array $pedidoIds): array
    {
        if (empty($pedidoIds)) return [];
        $params = []; $marcadores = [];
        foreach (array_values(array_unique($pedidoIds)) as $index => $id) {
            $clave = 'comparativo_' . $index;
            $params[$clave] = (int)$id;
            $marcadores[] = ':' . $clave;
        }
        $lista = implode(', ', $marcadores);
        $clientes = $this->select("SELECT pcd.id, pcd.pedido_id, pcd.venta_detalle_id, pcd.codigo_partida,
                                         pcd.fecha_estimada_entrega, COALESCE(pcd.entregado, 0) AS entregado
                                  FROM tb_pedidos_cliente_detalle pcd
                                  INNER JOIN tb_pedidos_cliente pc ON pc.id = pcd.pedido_id AND pc.enviado = 1
                                  WHERE pcd.pedido_id IN ($lista)
                                  ORDER BY pcd.pedido_id, pcd.codigo_partida, pcd.id", $params);
        if (empty($clientes)) return [];
        $proveedores = $this->select("SELECT ppd.id, pp.pedido_cliente_id, ppd.venta_detalle_id,
                                            ppd.codigo_partida, ppd.fecha_estimada_entrega,
                                            COALESCE(ppd.entregado, 0) AS entregado, pp.folio_ocp,
                                            COALESCE(NULLIF(prov.cDatGenRazonSocial, ''),
                                                     NULLIF(prov.cDatGenNombreAbreviado, ''), 'Sin Proveedor') AS proveedor
                                     FROM tb_pedidos_proveedor_detalle ppd
                                     INNER JOIN tb_pedidos_proveedor pp ON pp.id = ppd.pedido_proveedor_id
                                     INNER JOIN tb_pedidos_cliente pc ON pc.id = pp.pedido_cliente_id AND pc.enviado = 1
                                     LEFT JOIN tb_proveedores prov ON prov.icveProveedor = pp.proveedor_id
                                     WHERE pp.pedido_cliente_id IN ($lista) AND pp.enviado = 1
                                     ORDER BY pp.pedido_cliente_id, ppd.codigo_partida, pp.id, ppd.id", $params);
        return self::compararFechasPartidasClienteProveedor($clientes, $proveedores);
    }

    /**
     * Relaciona partidas exclusivamente por OCC, venta_detalle_id positivo y código
     * exacto. El código sólo se recorta en los extremos; no se usa material.
     * Duplicados de la clave del cliente son ambiguos y no se asignan automáticamente.
     * Partidas canceladas del proveedor no se toman como compromisos activos.
     * Expone entregado_cliente en cada cruce para excluir partidas completadas del riesgo ejecutivo.
     * Margen = fecha cliente menos fecha proveedor: negativo indica desfase.
     *
     * @param array $clientes Partidas originales de las OCC filtradas.
     * @param array $proveedores Partidas de OCP relacionadas con esas OCC.
     * @return array Cruces por OCC, sin multiplicar filas de la lista ni sus importes.
     */
    public static function compararFechasPartidasClienteProveedor(array $clientes, array $proveedores): array
    {
        $clave = static function (array $partida, string $campoPedido): ?string {
            $ventaId = (int)($partida['venta_detalle_id'] ?? 0);
            $codigo = trim((string)($partida['codigo_partida'] ?? ''));
            return $ventaId > 0 && $codigo !== ''
                ? json_encode([(int)$partida[$campoPedido], $ventaId, $codigo]) : null;
        };
        $fecha = static function ($valor): ?string {
            $texto = (string)($valor ?? '');
            $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $texto);
            return $dt && $dt->format('Y-m-d') === $texto ? $texto : null;
        };
        $repeticiones = []; $porClave = []; $resultado = [];
        foreach ($clientes as $cliente) {
            $key = $clave($cliente, 'pedido_id');
            if ($key !== null) $repeticiones[$key] = ($repeticiones[$key] ?? 0) + 1;
        }
        foreach ($proveedores as $proveedor) {
            if ((int)($proveedor['entregado'] ?? 0) === 2) continue;
            $key = $clave($proveedor, 'pedido_cliente_id');
            if ($key !== null) $porClave[$key][] = $proveedor;
        }
        foreach ($clientes as $cliente) {
            $key = $clave($cliente, 'pedido_id');
            $base = [
                'partida_cliente_id' => $cliente['id'], 'codigo_partida' => $cliente['codigo_partida'],
                // Permite distinguir desfases de partidas aún pendientes en el resumen ejecutivo.
                'entregado_cliente' => (int)($cliente['entregado'] ?? 0),
                'fecha_cliente' => $fecha($cliente['fecha_estimada_entrega'] ?? null),
                'folio_ocp' => null, 'proveedor' => null, 'fecha_proveedor' => null, 'margen_dias' => null,
            ];
            $estado = (int)($cliente['entregado'] ?? 0) === 2 ? 'cancelada'
                : ($key === null ? 'sin_vinculo' : (($repeticiones[$key] ?? 0) > 1 ? 'ambiguo' : null));
            $coincidencias = $key !== null ? ($porClave[$key] ?? []) : [];
            if ($estado !== null || empty($coincidencias)) {
                $base['estado'] = $estado ?? 'sin_ocp';
                $resultado[(int)$cliente['pedido_id']][] = $base;
                continue;
            }
            foreach ($coincidencias as $proveedor) {
                $cruce = $base;
                $cruce['folio_ocp'] = $proveedor['folio_ocp'];
                $cruce['proveedor'] = $proveedor['proveedor'];
                $cruce['fecha_proveedor'] = $fecha($proveedor['fecha_estimada_entrega'] ?? null);
                $cruce['estado'] = 'sin_fecha';
                if ($cruce['fecha_cliente'] !== null && $cruce['fecha_proveedor'] !== null) {
                    $cruce['margen_dias'] = (int)(new DateTimeImmutable($cruce['fecha_proveedor']))
                        ->diff(new DateTimeImmutable($cruce['fecha_cliente']))->format('%r%a');
                    $cruce['estado'] = $cruce['margen_dias'] < 0 ? 'desfase'
                        : ($cruce['margen_dias'] === 0 ? 'mismo_dia' : 'antes');
                }
                $resultado[(int)$cliente['pedido_id']][] = $cruce;
            }
        }
        return $resultado;
    }

    /**
     * Clasifica una partida con las reglas compartidas del calendario de clientes.
     * El estado 1/2 de la partida prevalece sobre el vencimiento. Hoy y +30 días
     * son próximos; sin fecha válida para calendario se devuelve sin_fecha.
     * La fecha de referencia usa la zona horaria configurada por el portal.
     *
     * @param array $partida fecha_estimada_entrega y entregado de la partida (no de la OCC).
     * @param string $hoy Fecha de referencia Y-m-d.
     * @return array Metadatos de estado, colores y plazo usados por calendario y resumen.
     */
    public static function clasificarCumplimientoEntregaCliente(array $partida, string $hoy): array
    {
        $fecha = (string)($partida['fecha_estimada_entrega'] ?? '');
        if ($fecha === '' || str_starts_with($fecha, '0000-00-00')) {
            return ['estatus_codigo' => 'sin_fecha'];
        }
        $dias = (int)(new DateTime($hoy))->diff(new DateTime($fecha))->format('%r%a');
        $entregado = (int)($partida['entregado'] ?? 0);
        if ($entregado === 1) {
            $codigo = 'entregado'; $label = 'Entregado'; $badge = 'bg-primary';
            $color = '#0d6efd'; $texto = '#ffffff'; $plazo = 'Entregado a cliente';
        } elseif ($entregado === 2) {
            $codigo = 'cancelado'; $label = 'Entrega Cancelada'; $badge = 'bg-secondary';
            $color = '#6c757d'; $texto = '#ffffff'; $plazo = 'Entrega cancelada';
        } else {
            $plazo = $dias > 0 ? $dias . ' día' . ($dias > 1 ? 's' : '')
                : ($dias === 0 ? '0 días (Entrega hoy)' : abs($dias) . ' día' . (abs($dias) > 1 ? 's' : '') . ' de atraso');
            if ($fecha < $hoy) {
                $codigo = 'vencido'; $label = 'Vencido (Pendiente)'; $badge = 'bg-danger';
                $color = '#dc3545'; $texto = '#ffffff';
            } elseif ($fecha <= date('Y-m-d', strtotime($hoy . ' +30 days'))) {
                $codigo = 'proximo'; $label = 'Próximo a Vencer'; $badge = 'bg-warning text-dark';
                $color = '#ffc107'; $texto = '#212529';
            } else {
                $codigo = 'en_tiempo'; $label = 'En Tiempo'; $badge = 'bg-success';
                $color = '#198754'; $texto = '#ffffff';
            }
        }
        return [
            'dias_restantes' => $dias, 'tiempo_restante_str' => $plazo,
            'estatus_codigo' => $codigo, 'estatus_label' => $label, 'badge_class' => $badge,
            'event_color' => $color, 'event_text_color' => $texto,
        ];
    }

    /**
     * Cuenta por lote las partidas de OCC pendientes/parciales ya autorizadas por la lista.
     * No filtra por fecha de entrega: el periodo de la lista corresponde al pedido padre.
     * Sin fecha se informa aparte para no confundir ausencia de plazo con entrega en tiempo.
     *
     * @param array $pedidoIds Identificadores internos de las OCC 0/3 filtradas.
     * @param string $hoy Referencia Y-m-d opcional para pruebas; vacío usa la fecha del portal.
     * @return array Resúmenes por pedido con cinco estados, sin_fecha y total.
     */
    public function selectResumenCumplimientoPedidosCliente(array $pedidoIds, string $hoy = ''): array
    {
        if (empty($pedidoIds)) return [];
        $hoy = $hoy !== '' ? $hoy : date('Y-m-d');
        $resumenes = []; $params = []; $marcadores = [];
        foreach (array_values(array_unique($pedidoIds)) as $index => $id) {
            $resumenes[(int)$id] = array_fill_keys(['en_tiempo', 'proximo', 'vencido', 'cancelado', 'entregado', 'sin_fecha', 'total'], 0);
            $clave = 'cumplimiento_' . $index;
            $params[$clave] = (int)$id;
            $marcadores[] = ':' . $clave;
        }
        $sql = "SELECT pcd.pedido_id, pcd.fecha_estimada_entrega, COALESCE(pcd.entregado, 0) AS entregado
                FROM tb_pedidos_cliente_detalle pcd
                INNER JOIN tb_pedidos_cliente pc ON pc.id = pcd.pedido_id AND pc.enviado = 1
                WHERE pcd.pedido_id IN (" . implode(', ', $marcadores) . ")";
        foreach ($this->select($sql, $params) as $partida) {
            $id = (int)$partida['pedido_id'];
            if (!isset($resumenes[$id])) continue;
            $estado = self::clasificarCumplimientoEntregaCliente($partida, $hoy)['estatus_codigo'];
            $resumenes[$id][$estado]++;
            $resumenes[$id]['total']++;
        }
        return $resumenes;
    }

    /**
     * Obtiene todos los seguimientos de una venta con historial completo.
     */
    public function selectHistorialSeguimientoVenta(int $venta_id): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                        vs.id,
                        vs.venta_id,
                        vs.seguimiento AS notas,
                        vs.ccveusuario,
                        vs.fchregistro AS fecha,
                        vs.archivo,
                        DATE_FORMAT(vs.fchregistro, '%d/%m/%Y %H:%i') AS fecha_formateada,
                        COALESCE(CONCAT_WS(' ', u.cnombre, u.cpriapellido, u.csegapellido), 'Sistema') AS nombre_usuario
                    FROM tb_ventas_seguimiento vs
                    LEFT JOIN cat_medico u ON u.ccvemedico = vs.ccveusuario
                    WHERE vs.venta_id = :venta_id
                    ORDER BY vs.id DESC";
            $arr_values = ['venta_id' => $venta_id];
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse;
    }

    /**
     * Obtiene los eventos de entregas estimadas a clientes para el calendario
     * Si se indica número de orden, omite el periodo y usa la misma búsqueda que la lista.
     * 
     * @param string $fecha_ini
     * @param string $fecha_fin
     * @param string $ccveusuario_vendedor
     * @return array
     */
    public function selectFechasEntregaClientes(string $fecha_ini = '', string $fecha_fin = '', string $ccveusuario_vendedor = '', string $filtro_num_orden = ''): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT
                pcd.id                                                      AS detalle_id,
                pcd.pedido_id,
                pc.venta_id,
                pc.num_orden_compra,
                COALESCE(c.nombre_comercial, 'Sin Cliente')                 AS cliente,
                v.titulo                                                    AS titulo_venta,
                v.proyecto_id,
                pcd.codigo_partida,
                pcd.descripcion,
                pcd.cantidad_pedido,
                pcd.precio_unitario,
                pcd.tiempo_entrega,
                pcd.fecha_estimada_entrega,
                DATE_FORMAT(pcd.fecha_estimada_entrega, '%d/%m/%Y')         AS fecha_estimada_formateada,
                pc.fecha_pedido,
                DATE_FORMAT(pc.fecha_pedido, '%d/%m/%Y')                    AS fecha_pedido_formateada,
                COALESCE(pcd.entregado, 0)                                  AS entregado
            FROM tb_pedidos_cliente_detalle pcd
            INNER JOIN tb_pedidos_cliente pc ON pc.id = pcd.pedido_id
            INNER JOIN tb_ventas v           ON v.id  = pc.venta_id
            LEFT  JOIN cat_clientes c        ON c.id  = v.cliente_id
            WHERE pc.enviado = 1
              AND pcd.fecha_estimada_entrega IS NOT NULL
              AND pcd.fecha_estimada_entrega != '0000-00-00' ";

            $arr_values = [];

            if (!empty($ccveusuario_vendedor)) {
                $sql .= " AND v.ccveusuario_vendedor = :ccveusuario_vendedor ";
                $arr_values['ccveusuario_vendedor'] = $ccveusuario_vendedor;
            }

            if (trim($filtro_num_orden) === '' && !empty($fecha_ini) && !empty($fecha_fin)) {
                $sql .= " AND DATE(pc.fecha_pedido) BETWEEN :fecha_ini AND :fecha_fin ";
                $arr_values['fecha_ini'] = $fecha_ini;
                $arr_values['fecha_fin'] = $fecha_fin;
            }

            if (trim($filtro_num_orden) !== '') {
                $sql .= " AND (pc.num_orden_compra LIKE :num_orden_compra OR pc.id LIKE :num_orden_compra_id) ";
                $arr_values['num_orden_compra'] = '%' . trim($filtro_num_orden) . '%';
                $arr_values['num_orden_compra_id'] = '%' . trim($filtro_num_orden) . '%';
            }
            $sql .= " ORDER BY pcd.fecha_estimada_entrega ASC, pcd.id ASC ";

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $arrResponse;
    }
}
