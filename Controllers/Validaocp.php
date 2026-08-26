<?php

/**
 * Controlador Validaocp
 * Permite la validación pública (sin sesión) de órdenes de compra a proveedor mediante lectura de código QR.
 */
class Validaocp extends Controllers
{
    public function __construct()
    {
        // Se ejecuta el constructor base sin validación de sesión para permitir el acceso libre
        parent::__construct();
    }

    /**
     * Vista principal de validación de orden de compra a proveedor
     */
    public function Validaocp()
    {
        try {
            // Evitar almacenamiento en caché para respuestas de validación en tiempo real
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

            /*-------------------------------------------
            [ Obtención y sanitización de parámetros GET ]*/
            $folio = isset($_GET['folio']) ? trim(strClean($_GET['folio'])) : '';
            $fecha = isset($_GET['fecha']) ? trim(strClean($_GET['fecha'])) : '';
            
            // Se soporta parámetro 'total' (según QR especificado) o 'subtotal' como alternativa
            $total = isset($_GET['total']) ? trim(strClean($_GET['total'])) : (isset($_GET['subtotal']) ? trim(strClean($_GET['subtotal'])) : '');
            
            $pedidoBD = array();
            $statusValidacion = "NO_ENCONTRADO";
            $mensajeValidacion = "";
            $detallesCotejo = array(
                'folio_coincide' => false,
                'fecha_coincide' => null,
                'total_coincide' => null
            );
            
            if (!empty($folio)) {
                /*-------------------------------------------
                [ Consulta a la base de datos ]*/
                $pedidoBD = $this->model->getPedidoProveedor($folio);

                if (!empty($pedidoBD)) {
                    $detallesCotejo['folio_coincide'] = true;
                    $statusValidacion = "VALIDO";
                    $mensajeValidacion = "Compare la información mostrada contra el documento recibido para verificar su autenticidad.";

                    // Obtener valores de BD
                    $fechaBD = $pedidoBD['fecha_pedido'] ?? $pedidoBD['fecha_db'] ?? null;
                    $totalBD = (float)($pedidoBD['total'] ?? 0);
                    $subtotalBD = (float)($pedidoBD['subtotal'] ?? 0);

                    // Formatear fechas para comparación (si la fecha viene en el QR)
                    if (!empty($fecha) && !empty($fechaBD)) {
                        $fechaQRGen = date('Y-m-d', strtotime($fecha));
                        $fechaBDGen = date('Y-m-d', strtotime($fechaBD));
                        $detallesCotejo['fecha_coincide'] = ($fechaQRGen === $fechaBDGen);
                    }

                    // Comparar montos (si el total o subtotal viene en el QR)
                    if (!empty($total)) {
                        $numTotalQR = round((float)$total, 2);
                        $numTotalBD = round((float)$totalBD, 2);
                        $numSubTotalBD = round((float)$subtotalBD, 2);

                        // Coincide si el total del QR es igual al total o al subtotal registrado en BD
                        $coincideTotal = (abs($numTotalQR - $numTotalBD) < 0.01);
                        $coincideSubtotal = (abs($numTotalQR - $numSubTotalBD) < 0.01);

                        $detallesCotejo['total_coincide'] = ($coincideTotal || $coincideSubtotal);
                    }

                    // Evaluar si existe alguna discrepancia
                    if ($detallesCotejo['fecha_coincide'] === false || $detallesCotejo['total_coincide'] === false) {
                        $statusValidacion = "DISCREPANCIA";
                        $mensajeValidacion = "Atención: La orden de compra existe en la base de datos, pero los datos del QR difieren del registro original.";
                    }
                } else {
                    $statusValidacion = "NO_ENCONTRADO";
                    $mensajeValidacion = "No se encontró ningún registro correspondiente al folio especificado en la base de datos.";
                }
            } else {
                $statusValidacion = "SIN_FOLIO";
                $mensajeValidacion = "Por favor, escanee un código QR válido o proporcione un folio para validar.";
            }

            /*-------------------------------------------
            [ Obtención / Generación de Cadena Original y Sello Digital ]*/
            $selloDigital = "";
            $cadenaOriginal = "";
            if (!empty($pedidoBD)) {
                if (!empty($pedidoBD['cadena_original'])) {
                    $cadenaOriginal = $pedidoBD['cadena_original'];
                } else {
                    $montoTotalBD = (float)($pedidoBD['total'] ?? 0);
                    $cadenaOriginal = "||1.0|" . ($pedidoBD['folio_ocp'] ?? $folio) . "|" . ($pedidoBD['fecha_pedido'] ?? '') . "|" . ($pedidoBD['proveedor_id'] ?? '') . "|" . $montoTotalBD . "||";
                }

                $selloBD = $pedidoBD['sello_digital'] 
                    ?? $pedidoBD['sello'] 
                    ?? $pedidoBD['token'] 
                    ?? $pedidoBD['hash'] 
                    ?? $pedidoBD['uuid'] 
                    ?? null;

                if (!empty($selloBD)) {
                    $selloDigital = $selloBD;
                } else {
                    $selloDigital = strtoupper(hash('sha256', $cadenaOriginal . 'josue_1:8'));
                }
            }

            /*-------------------------------------------
            [ Preparación de datos para la vista ]*/
            $data['page_title'] = "Validación de Orden de Compra | LFM CONTROL";
            $data['meta_keywords'] = "validación, orden de compra, proveedor, ocp, lfm control, veracidad, comprobante";
            $data['meta_description'] = "Verificación de autenticidad de órdenes de compra a proveedor emitidas por LFM Control.";
            
            $data['qr_params'] = array(
                'folio' => $folio,
                'fecha' => $fecha,
                'total' => $total
            );

            $data['pedido_bd'] = $pedidoBD;
            $data['sello_digital'] = $selloDigital;
            $data['cadena_original'] = $cadenaOriginal;
            $data['validacion'] = array(
                'status' => $statusValidacion,
                'mensaje' => $mensajeValidacion,
                'detalles' => $detallesCotejo
            );

            /*-------------------------------------------
            [ Carga de la vista ]*/
            $this->views->getView($this, "validaocp", $data);

        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            echo "Ocurrió un error al procesar la solicitud de validación.";
        }
    }
}
