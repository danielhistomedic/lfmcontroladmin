<?php

/**
 * Modelo ValidacotizacionModel
 * Consulta pública de cotizaciones de cliente
 */
class ValidacotizacionModel extends Mysql
{
    const TABLA = "tb_ventas_cotizacion_cliente";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene los datos de una cotización de cliente por su folio
     * 
     * @param string $folio
     * @return array
     */
    public function getCotizacionCliente(string $folio): array
    {
        try {
            $sql = "SELECT c.*, 
                           DATE_FORMAT(c.fecha, '%Y-%m-%d') AS fecha_db,
                           DATE_FORMAT(c.fecha, '%d/%m/%Y') AS fecha_formateada,
                           COALESCE(c.moneda_id, v.moneda_id, 3) AS id_moneda,
                           CASE 
                               WHEN COALESCE(c.moneda_id, v.moneda_id) = 1 THEN 'MXN' 
                               WHEN COALESCE(c.moneda_id, v.moneda_id) = 3 THEN 'USD' 
                               ELSE 'USD' 
                           END AS cmoneda,
                           (SELECT IFNULL(NULLIF(cl.razon_social, ''), cl.nombre_comercial) 
                            FROM cat_clientes cl 
                            WHERE cl.id = c.cliente_id) AS cliente_razon_social 
                    FROM " . self::TABLA . " c 
                    LEFT JOIN tb_ventas v ON v.id = c.venta_id
                    WHERE c.folio_cotizacion = :folio 
                    LIMIT 1";
            $arrValues = array("folio" => $folio);
            $request = $this->selectModel($sql, $arrValues);
            return $request ? $request : array();
    } catch (\Throwable $th) {
        getLoggerSystem()->error(getMensajeError($th));
        return array();
    }
}
}
