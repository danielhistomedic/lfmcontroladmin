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
                           (SELECT IFNULL(NULLIF(cl.razon_social, ''), cl.nombre_comercial) 
                            FROM cat_clientes cl 
                        WHERE cl.id = c.cliente_id) AS cliente_razon_social 
                FROM " . self::TABLA . " c 
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
