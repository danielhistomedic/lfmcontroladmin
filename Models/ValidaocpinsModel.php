<?php

/** Consulta pública de órdenes de compra a proveedor para insumos. */
class ValidaocpinsModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPedidoProveedorInsumos(string $folio): array
    {
        $sql = "SELECT p.id, p.folio_ocp, p.fecha_pedido, p.subtotal, p.descuento,
                       p.iva, p.total, p.enviado, p.tipo_pedido, p.num_revision,
                       p.fecha_revision, p.num_requisicion, p.folio_cotizacion_proveedor,
                       p.cadena_original, p.sello_digital,
                       CASE WHEN p.moneda_id = 1 THEN 'MXN'
                            WHEN p.moneda_id = 3 THEN 'USD' ELSE '' END AS cmoneda,
                       COALESCE(NULLIF(TRIM(prov.cDatGenRazonSocial), ''),
                                NULLIF(TRIM(prov.cDatGenNombreAbreviado), ''), 'N/D') AS proveedor
                FROM tb_pedidos_proveedor_ins p
                LEFT JOIN tb_proveedores prov ON prov.icveProveedor = p.proveedor_id
                WHERE p.folio_ocp = :folio
                LIMIT 1";
        $filas = $this->selectOutput($sql, array('folio' => $folio));
        if ($filas === false) {
            throw new RuntimeException('No fue posible consultar la orden de insumos.');
        }
        return $filas[0] ?? array();
    }

    public function getPartidas(int $pedidoId, int $pagina = 1): array
    {
        $desplazamiento = (max(1, min($pagina, 99999)) - 1) * 50;
        $sql = 'SELECT codigo_partida, descripcion, descripcion_adicional, ccveunidad,
                       cantidad_pedido, precio_unitario, descuento, importe_impuesto, importe
                FROM tb_pedidos_proveedor_ins_detalle
                WHERE pedido_proveedor_ins_id = :pedido_id
                ORDER BY codigo_partida, id
                LIMIT 51 OFFSET ' . $desplazamiento;
        $filas = $this->selectOutput($sql, array('pedido_id' => $pedidoId));
        if ($filas === false) {
            throw new RuntimeException('No fue posible consultar las partidas de insumos.');
        }
        return $filas;
    }
}
