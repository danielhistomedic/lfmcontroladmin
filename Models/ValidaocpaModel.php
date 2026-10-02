<?php

/** Consulta pública de órdenes de compra a proveedor agrupadas. */
class ValidaocpaModel extends Mysql
{
    const TABLA = 'tb_pedidos_proveedor_agrupado';

    public function __construct()
    {
        parent::__construct();
    }

    public function getPedidoProveedorAgrupado(string $folio): array
    {
        try {
            $sql = 'SELECT p.folio_ocpa, p.fecha_pedido, p.cancelado,
                           p.subtotal, p.total, p.sello_digital, p.cadena_original
                    FROM ' . self::TABLA . ' p
                    WHERE p.folio_ocpa = :folio
                    LIMIT 1';
            return $this->selectModel($sql, array('folio' => $folio));
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            return array();
        }
    }
}
