<?php

/** Validación pública por QR de órdenes de compra a proveedor para insumos. */
class Validaocpins extends Controllers
{
    public function __construct()
    {
        // Esta consulta pública no inicializa ni exige una sesión.
        parent::__construct();
    }

    public function Validaocpins()
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

        $data = array(
            'page_title' => 'Validación de Orden de Compra de Insumos | LFM CONTROL',
            'meta_keywords' => 'validación, orden de compra, proveedor, insumos, lfm control',
            'meta_description' => 'Consulta pública de órdenes de compra a proveedor para insumos.',
            'qr_params' => array('folio' => '', 'fecha' => '', 'total' => ''),
            'pedido_bd' => array(),
            'partidas' => array(),
            'pagina' => 1,
            'hay_mas_partidas' => false,
            'validacion' => array(
                'status' => 'SIN_FOLIO',
                'mensaje' => 'Escanee el código QR impreso en la orden de compra para consultar sus datos.',
                'detalles' => array('folio_coincide' => false, 'fecha_coincide' => null, 'total_coincide' => null)
            )
        );

        try {
            /*-------------------------------------------
            [ Lectura y validación de parámetros del QR ]*/
            $parametrosValidos = true;
            foreach ($data['qr_params'] as $nombre => $valor) {
                $entrada = $_GET[$nombre] ?? '';
                if (!is_string($entrada)) {
                    $parametrosValidos = false;
                    $entrada = '';
                }
                if (str_contains($entrada, "\0")) {
                    $parametrosValidos = false;
                }
                $data['qr_params'][$nombre] = trim($entrada);
            }
            $folio = $data['qr_params']['folio'];
            $fecha = $data['qr_params']['fecha'];
            $total = $data['qr_params']['total'];
            $pagina = $_GET['pagina'] ?? '1';
            if (!is_string($pagina) || preg_match('/^[1-9][0-9]{0,4}$/D', $pagina) !== 1) {
                $parametrosValidos = false;
            } else {
                $data['pagina'] = (int)$pagina;
            }
            if ($folio !== '' && preg_match('/^.{1,24}$/us', $folio) !== 1) {
                $parametrosValidos = false;
            }
            if ($fecha !== '') {
                if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D', $fecha, $componentes) !== 1
                    || !checkdate((int)$componentes[2], (int)$componentes[3], (int)$componentes[1])) {
                    $parametrosValidos = false;
                }
            }
            $totalNormalizado = $total !== '' ? $this->normalizarImporte($total) : null;
            if ($total !== '' && $totalNormalizado === null) {
                $parametrosValidos = false;
            }

            if (!$parametrosValidos) {
                $data['validacion']['status'] = 'PARAMETROS_INVALIDOS';
                $data['validacion']['mensaje'] = 'El QR contiene parámetros inválidos. La fecha debe usar AAAA-MM-DD y el total debe ser un importe con hasta dos decimales.';
            } elseif ($folio !== '') {
                /*-------------------------------------------
                [ Consulta y cotejo con el registro original ]*/
                $pedido = $this->model->getPedidoProveedorInsumos($folio);
                if (empty($pedido)) {
                    $data['validacion']['status'] = 'NO_ENCONTRADO';
                    $data['validacion']['mensaje'] = 'No se encontró una orden de compra de insumos con el folio indicado.';
                } else {
                    $data['pedido_bd'] = $pedido;
                    $partidas = $this->model->getPartidas((int)$pedido['id'], $data['pagina']);
                    $data['hay_mas_partidas'] = count($partidas) > 50;
                    $data['partidas'] = array_slice($partidas, 0, 50);
                    $detalles = &$data['validacion']['detalles'];
                    $detalles['folio_coincide'] = $folio === $pedido['folio_ocp'];
                    if ($fecha !== '') {
                        $detalles['fecha_coincide'] = $fecha === $pedido['fecha_pedido'];
                    }
                    if ($total !== '') {
                        // Comparación decimal exacta: el parámetro total coteja únicamente el total.
                        $totalBD = $this->normalizarImporte((string)$pedido['total']);
                        $detalles['total_coincide'] = $totalBD !== null && $totalNormalizado === $totalBD;
                    }
                    $data['validacion']['status'] = 'VALIDO';
                    $data['validacion']['mensaje'] = 'Orden localizada. Compare los datos registrados y las partidas con su documento impreso.';
                    if (in_array(false, $detalles, true)) {
                        $data['validacion']['status'] = 'DISCREPANCIA';
                        $data['validacion']['mensaje'] = 'La orden existe, pero hay diferencias entre los datos del QR y el registro original.';
                    }
                    if ((int)$pedido['enviado'] === 2) {
                        $data['validacion']['status'] = 'CANCELADO';
                        $data['validacion']['mensaje'] = 'Esta orden de compra de insumos está cancelada.';
                    }
                }
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            $data['pedido_bd'] = array();
            $data['partidas'] = array();
            $data['validacion']['status'] = 'ERROR';
            $data['validacion']['mensaje'] = 'No fue posible consultar la orden. Intente nuevamente más tarde.';
        }
        $this->views->getView($this, 'validaocpins', $data);
    }

    /** Normaliza DECIMAL(18,2) sin convertir los importes a punto flotante. */
    private function normalizarImporte(string $importe): ?string
    {
        if (preg_match('/^[0-9]{1,16}(?:\.[0-9]{1,2})?$/D', $importe) !== 1) {
            return null;
        }
        $partes = explode('.', $importe, 2);
        return (ltrim($partes[0], '0') ?: '0') . '.' . str_pad($partes[1] ?? '', 2, '0');
    }
}
