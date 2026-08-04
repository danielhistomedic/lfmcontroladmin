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
            $sql .= "  AND v.clasificacion_proyecto_id IN (2,3,5) ";

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
            $sql .= "    AND v.clasificacion_proyecto_id IN (2,3,5) ";
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
            $sql = "SELECT
                SUM(CASE WHEN v.estatus_proyecto_id >= 6 THEN 1 ELSE 0 END) AS count_ganadas,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 6 AND v.moneda_id = 1 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) ELSE 0 END), 2) AS sum_ganadas_mxn,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 6 AND v.moneda_id = 3 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) ELSE 0 END), 2) AS sum_ganadas_usd,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 6 THEN
                    CASE WHEN v.moneda_id = 1 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(pc.monto, (v.subtotal - v.descuento)) END
                ELSE 0 END), 2) AS sum_ganadas_combined_usd,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 6 THEN
                    CASE WHEN v.moneda_id = 3 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) * tc.valor ELSE COALESCE(pc.monto, (v.subtotal - v.descuento)) END
                ELSE 0 END), 2) AS sum_ganadas_combined_mxn,

                SUM(CASE WHEN v.estatus_proyecto_id >= 5 THEN 1 ELSE 0 END) AS count_pipeline,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 5 AND v.moneda_id = 1 THEN COALESCE(cc.monto, (v.subtotal - v.descuento)) ELSE 0 END), 2) AS sum_pipeline_mxn,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 5 AND v.moneda_id = 3 THEN COALESCE(cc.monto, (v.subtotal - v.descuento)) ELSE 0 END), 2) AS sum_pipeline_usd,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 5 THEN
                    CASE WHEN v.moneda_id = 1 THEN COALESCE(cc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(cc.monto, (v.subtotal - v.descuento)) END
                ELSE 0 END), 2) AS sum_pipeline_combined_usd,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 5 THEN
                    CASE WHEN v.moneda_id = 3 THEN COALESCE(cc.monto, (v.subtotal - v.descuento)) * tc.valor ELSE COALESCE(cc.monto, (v.subtotal - v.descuento)) END
                ELSE 0 END), 2) AS sum_pipeline_combined_mxn,

                COUNT(DISTINCT CASE WHEN v.estatus_proyecto_id >= 6 THEN v.cliente_id ELSE NULL END) AS count_clientes_activos,

                /* Artículos vendidos: se calcula en subconsulta independiente para evitar
                   que el JOIN con detalle multiplique las filas de tb_ventas y distorsione
                   todos los SUM() de montos. */
                COALESCE((
                    SELECT SUM(d.cantidad)
                    FROM tb_ventas_detalle d
                    INNER JOIN tb_ventas vd ON vd.id = d.venta_id
                    WHERE vd.estatus_proyecto_id >= 6
                      AND d.cancelado = 0
                      AND vd.fecha BETWEEN :fecha_ini_sub AND :fecha_fin_sub
                ), 0) AS total_articulos_vendidos,

                metas.MetaGlobalUSD,
                ROUND(
                    (
                        SUM(CASE WHEN v.estatus_proyecto_id >= 6 THEN
                            CASE WHEN v.moneda_id = 1 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(pc.monto, (v.subtotal - v.descuento)) END
                        ELSE 0 END)
                        / NULLIF(metas.MetaGlobalUSD, 0)
                    ) * 100,
                    2
                ) AS PorcentajeCumplimiento,
                ROUND(
                    metas.MetaGlobalUSD -
                    SUM(CASE WHEN v.estatus_proyecto_id >= 6 THEN
                        CASE WHEN v.moneda_id = 1 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(pc.monto, (v.subtotal - v.descuento)) END
                    ELSE 0 END),
                    2
                ) AS FaltanteUSD,
                ROUND(
                    COALESCE(
                        (
                            SUM(CASE WHEN v.estatus_proyecto_id >= 6 THEN
                                CASE WHEN v.moneda_id = 1 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(pc.monto, (v.subtotal - v.descuento)) END
                            ELSE 0 END)
                            / NULLIF(SUM(CASE WHEN v.estatus_proyecto_id >= 5 THEN
                                CASE WHEN v.moneda_id = 1 THEN COALESCE(cc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(cc.monto, (v.subtotal - v.descuento)) END
                            ELSE 0 END), 0)
                        ) * 100,
                        0
                    ),
                    2
                ) AS PorcentajeEfectividad
            FROM tb_ventas v
            LEFT JOIN (
                SELECT 
                    venta_id,
                    SUM(subtotal - descuento) AS monto
                FROM tb_pedidos_cliente
                WHERE enviado = 1
                GROUP BY venta_id
            ) pc ON pc.venta_id = v.id
            LEFT JOIN (
                SELECT 
                    venta_id,
                    SUM(subtotal - descuento) AS monto
                FROM tb_ventas_cotizacion_cliente
                WHERE enviado = 1
                GROUP BY venta_id
            ) cc ON cc.venta_id = v.id
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            CROSS JOIN (
                SELECT COALESCE(SUM(meta), 0) AS MetaGlobalUSD
                FROM tb_metas
                WHERE anio = YEAR(CURDATE())
            ) metas
            WHERE v.fecha BETWEEN :fecha_ini AND :fecha_fin";

            $arr_values = [
                'fecha_ini'     => $fecha_ini,
                'fecha_fin'     => $fecha_fin,
                'fecha_ini_sub' => $fecha_ini,
                'fecha_fin_sub' => $fecha_fin,
            ];

            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $arrResponse[0] ?? [];
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
                DATE_FORMAT(v.fecha, '%Y-%m') AS fecha_grupo,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 6 THEN (CASE WHEN v.moneda_id = 1 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(pc.monto, (v.subtotal - v.descuento)) END) ELSE 0 END), 2) AS sum_ventas_usd,
                ROUND(SUM(CASE WHEN v.estatus_proyecto_id >= 5 THEN (CASE WHEN v.moneda_id = 1 THEN COALESCE(cc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(cc.monto, (v.subtotal - v.descuento)) END) ELSE 0 END), 2) AS sum_pipeline_usd,
                ROUND(
                    CASE 
                        WHEN SUM(CASE WHEN v.estatus_proyecto_id >= 5 THEN (CASE WHEN v.moneda_id = 1 THEN COALESCE(cc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(cc.monto, (v.subtotal - v.descuento)) END) ELSE 0 END) > 0 
                        THEN (
                            SUM(CASE WHEN v.estatus_proyecto_id >= 6 THEN (CASE WHEN v.moneda_id = 1 THEN COALESCE(pc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(pc.monto, (v.subtotal - v.descuento)) END) ELSE 0 END)
                            /
                            SUM(CASE WHEN v.estatus_proyecto_id >= 5 THEN (CASE WHEN v.moneda_id = 1 THEN COALESCE(cc.monto, (v.subtotal - v.descuento)) / tc.valor ELSE COALESCE(cc.monto, (v.subtotal - v.descuento)) END) ELSE 0 END)
                        ) * 100
                        ELSE 0 
                    END
                , 2) AS PorcentajeEfectividad
            FROM tb_ventas v
            LEFT JOIN (
                SELECT 
                    venta_id,
                    SUM(subtotal - descuento) AS monto
                FROM tb_pedidos_cliente
                WHERE enviado = 1
                GROUP BY venta_id
            ) pc ON pc.venta_id = v.id
            LEFT JOIN (
                SELECT 
                    venta_id,
                    SUM(subtotal - descuento) AS monto
                FROM tb_ventas_cotizacion_cliente
                WHERE enviado = 1
                GROUP BY venta_id
            ) cc ON cc.venta_id = v.id
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            WHERE (v.estatus_proyecto_id >= 6 OR v.estatus_proyecto_id >= 5)
              AND v.fecha BETWEEN :fecha_ini AND :fecha_fin
            GROUP BY DATE_FORMAT(v.fecha, '%Y-%m')
            ORDER BY DATE_FORMAT(v.fecha, '%Y-%m') ASC";

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
     * Obtiene ventas consolidadas en tendencia diaria/mensual
     */
    public function selectVentasTendencia(string $fecha_ini, string $fecha_fin): array
    {
        $arrResponse = array();
        try {
            $sql = "SELECT 
                v.fecha AS fecha_grupo,
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
            GROUP BY v.fecha
            ORDER BY v.fecha ASC";

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
                (v.subtotal - v.descuento) AS total,
                v.moneda_id,
                CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                ROUND(CASE WHEN v.moneda_id = 1 THEN (v.subtotal - v.descuento) / tc.valor ELSE (v.subtotal - v.descuento) END, 2) AS total_usd,
                COALESCE(c.nombre_comercial, 'Sin Cliente') AS cliente,
                CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS vendedor,
                COALESCE(cl.clasificacion, 'Sin Clasificación') AS clasificacion_proyecto,
                COALESCE(e.cEstatus, 'Sin Estatus') AS estatus_proyecto
            FROM tb_ventas v
            LEFT JOIN cat_clientes c ON c.id = v.cliente_id
            LEFT JOIN cat_medico vd ON vd.ccvemedico = v.ccveusuario_vendedor
            LEFT JOIN cat_clasificacion_proyectos cl ON cl.id = v.clasificacion_proyecto_id
            LEFT JOIN cat_estatus_proyecto e ON e.Id = v.estatus_proyecto_id
            CROSS JOIN (
                SELECT valor
                FROM tb_historial_tipos_cambio
                WHERE idMoneda = 3
                ORDER BY fecha DESC, id DESC
                LIMIT 1
            ) tc
            WHERE v.estatus_proyecto_id >= 6
              AND DATE(v.fecha) BETWEEN :fecha_ini AND :fecha_fin
            ORDER BY clasificacion_proyecto, v.proyecto_id, v.id";

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
     * Obtiene la lista detallada de pedidos cotizados (estatus_proyecto_id >= 5) en un rango de fechas.
     */
    public function selectPedidosCotizados(string $fecha_ini, string $fecha_fin): array
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
                COALESCE(cc.monto, (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0))) AS total,
                v.moneda_id,
                CASE WHEN v.moneda_id = 1 THEN 'MXN' WHEN v.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                ROUND(CASE WHEN v.moneda_id = 1 THEN COALESCE(cc.monto, (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0))) / COALESCE(NULLIF(tc.valor, 0), 1) ELSE COALESCE(cc.monto, (COALESCE(v.subtotal, 0) - COALESCE(v.descuento, 0))) END, 2) AS total_usd,
                COALESCE(c.nombre_comercial, 'Sin Cliente') AS cliente,
                CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido) AS vendedor,
                COALESCE(cl.clasificacion, 'Sin Clasificación') AS clasificacion_proyecto,
                COALESCE(e.cEstatus, 'Sin Estatus') AS estatus_proyecto,
                (SELECT vc.estatus_proyecto_id FROM tb_ventas vc WHERE vc.id = v.id) as valida_colocados,
                COALESCE(v.activo, 'ACTIVO') AS activo
            FROM tb_ventas v
            LEFT JOIN (
                SELECT 
                    venta_id,
                    SUM(COALESCE(subtotal, 0) - COALESCE(descuento, 0)) AS monto
                FROM tb_ventas_cotizacion_cliente
                WHERE enviado = 1
                GROUP BY venta_id
            ) cc ON cc.venta_id = v.id
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
            WHERE v.estatus_proyecto_id >= 5
              AND DATE(v.fecha) BETWEEN :fecha_ini AND :fecha_fin
            ORDER BY clasificacion_proyecto, v.proyecto_id, v.id";

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
}

