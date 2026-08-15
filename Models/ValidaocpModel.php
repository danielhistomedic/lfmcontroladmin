<?php

/**
 * Modelo ValidaocpModel
 * Consulta pública de órdenes de compra a proveedor
 */
class ValidaocpModel extends Mysql
{
    const TABLA = "tb_pedidos_proveedor";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene los datos de una orden de compra a proveedor por su folio_ocp
     * 
     * @param string $folio
     * @return array
     */
    public function getPedidoProveedor(string $folio): array
    {
        try {
            $sql = "SELECT p.*, 
                           DATE_FORMAT(p.fecha_pedido, '%Y-%m-%d') AS fecha_db,
                           DATE_FORMAT(p.fecha_pedido, '%d/%m/%Y') AS fecha_formateada,
                           CASE 
                               WHEN p.moneda_id = 1 THEN 'MXN' 
                               WHEN p.moneda_id = 3 THEN 'USD' 
                               ELSE 'MXN' 
                           END AS cmoneda 
                    FROM " . self::TABLA . " p 
                    WHERE p.folio_ocp = :folio 
                    LIMIT 1";
            $arrValues = array("folio" => $folio);
            $request = $this->selectModel($sql, $arrValues);

            if (!empty($request) && !empty($request['proveedor_id'])) {
                try {
                    $sqlProv = "SELECT * FROM tb_proveedores WHERE icveProveedor = :provId LIMIT 1";
                    $provData = $this->selectModel($sqlProv, array("provId" => $request['proveedor_id']));

                    if (!empty($provData)) {
                        $nombreProv = $provData['cRazonSocial'] 
                            ?? $provData['cNombreComercial'] 
                            ?? $provData['cNombre'] 
                            ?? $provData['razon_social'] 
                            ?? $provData['nombre_comercial'] 
                            ?? $provData['nombre'] 
                            ?? $provData['crazonsocial'] 
                            ?? $provData['cnombrecomercial'] 
                            ?? $provData['cnombre'] 
                            ?? '';

                        if (empty($nombreProv)) {
                            foreach ($provData as $k => $v) {
                                if (!empty($v) && is_string($v) && !in_array(strtolower($k), ['icveproveedor', 'id', 'fecha', 'estatus'])) {
                                    $nombreProv = $v;
                                    break;
                                }
                            }
                        }

                        if (!empty($nombreProv)) {
                            $request['proveedor'] = $nombreProv;
                        }
                    }
                } catch (\Throwable $t) {
                    getLoggerSystem()->error(getMensajeError($t));
                }
            }

            return $request ? $request : array();
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            return array();
        }
    }
}
