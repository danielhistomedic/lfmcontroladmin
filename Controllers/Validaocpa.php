<?php

/** Validación pública por QR de órdenes de compra a proveedor agrupadas. */
class Validaocpa extends Controllers
{
    public function __construct()
    {
        parent::__construct();
    }

    public function Validaocpa()
    {
        try {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

            /*-------------------------------------------
            [ Parámetros del QR ]*/
            $parametrosInvalidos = false;
            $parametros = array();
            foreach (array('folio', 'fecha', 'total', 'subtotal') as $nombre) {
                $valor = $_GET[$nombre] ?? '';
                if (!is_string($valor)) {
                    $parametrosInvalidos = true;
                    $valor = '';
                }
                $parametros[$nombre] = trim($valor);
            }
            $folio = $parametros['folio'];
            $fecha = $parametros['fecha'];
            $total = isset($_GET['total']) ? $parametros['total'] : $parametros['subtotal'];
            if (($folio !== '' && preg_match('/^.{1,45}$/us', $folio) !== 1)
                || strlen($fecha) > 40 || strlen($total) > 40) {
                $parametrosInvalidos = true;
            }
            $fechaQR = $fecha !== '' ? strtotime($fecha) : false;
            $fechaAnalizada = $fecha !== '' ? date_parse($fecha) : array('warning_count' => 0, 'error_count' => 0);
            if (($fecha !== '' && $fechaQR === false)
                || $fechaAnalizada['warning_count'] > 0 || $fechaAnalizada['error_count'] > 0
                || ($total !== '' && (!is_numeric($total) || !is_finite((float)$total) || (float)$total < 0))) {
                $parametrosInvalidos = true;
            }

            $pedidoBD = array();
            $statusValidacion = 'SIN_FOLIO';
            $mensajeValidacion = 'Por favor, escanee un código QR válido o proporcione un folio para validar.';
            $detallesCotejo = array(
                'folio_coincide' => false,
                'fecha_coincide' => null,
                'total_coincide' => null
            );

            /*-------------------------------------------
            [ Consulta y cotejo de la orden agrupada ]*/
            if ($parametrosInvalidos) {
                $statusValidacion = 'PARAMETROS_INVALIDOS';
                $mensajeValidacion = 'Los parámetros del QR no son válidos. Revise el folio, la fecha y el importe.';
            } elseif ($folio !== '') {
                $pedidoBD = $this->model->getPedidoProveedorAgrupado($folio);
                if (empty($pedidoBD)) {
                    $statusValidacion = 'NO_ENCONTRADO';
                    $mensajeValidacion = 'No se encontró ningún registro correspondiente al folio especificado en la base de datos.';
                } else {
                    $detallesCotejo['folio_coincide'] = true;
                    $statusValidacion = 'VALIDO';
                    $mensajeValidacion = 'Compare la información mostrada contra el documento recibido para verificar su autenticidad.';

                    $fechaBD = $pedidoBD['fecha_pedido'] ?? null;
                    if ($fecha !== '' && !empty($fechaBD)) {
                        $fechaRegistro = strtotime($fechaBD);
                        $detallesCotejo['fecha_coincide'] = $fechaRegistro !== false
                            && date('Y-m-d', $fechaQR) === date('Y-m-d', $fechaRegistro);
                    }
                    // Igual que Validaocp, se admite el total o el subtotal registrado.
                    if ($total !== '') {
                        $importeQR = round((float)$total, 2);
                        $coincideTotal = isset($pedidoBD['total'])
                            && abs($importeQR - round((float)$pedidoBD['total'], 2)) < 0.01;
                        $coincideSubtotal = isset($pedidoBD['subtotal'])
                            && abs($importeQR - round((float)$pedidoBD['subtotal'], 2)) < 0.01;
                        if (isset($pedidoBD['total']) || isset($pedidoBD['subtotal'])) {
                            $detallesCotejo['total_coincide'] = $coincideTotal || $coincideSubtotal;
                        }
                    }
                    if ($detallesCotejo['fecha_coincide'] === false || $detallesCotejo['total_coincide'] === false) {
                        $statusValidacion = 'DISCREPANCIA';
                        $mensajeValidacion = 'Atención: La orden de compra agrupada existe, pero los datos del QR difieren del registro original.';
                    }
                    if ((int)($pedidoBD['cancelado'] ?? 0) !== 0) {
                        $statusValidacion = 'CANCELADO';
                        $mensajeValidacion = 'Atención: Esta orden de compra agrupada está cancelada.';
                    }
                }
            }

            /*-------------------------------------------
            [ Datos públicos y sello almacenado del documento ]*/
            $data = array(
                'page_title' => 'Validación de Orden de Compra Agrupada | LFM CONTROL',
                'meta_keywords' => 'validación, orden de compra agrupada, proveedor, ocpa, lfm control',
                'meta_description' => 'Verificación de órdenes de compra a proveedor agrupadas emitidas por LFM Control.',
                'qr_params' => array('folio' => $folio, 'fecha' => $fecha, 'total' => $total),
                'pedido_bd' => $pedidoBD,
                'sello_digital' => $pedidoBD['sello_digital'] ?? '',
                'cadena_original' => $pedidoBD['cadena_original'] ?? '',
                'validacion' => array(
                    'status' => $statusValidacion,
                    'mensaje' => $mensajeValidacion,
                    'detalles' => $detallesCotejo
                )
            );
            $this->views->getView($this, 'validaocpa', $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            echo 'Ocurrió un error al procesar la solicitud de validación.';
        }
    }
}
