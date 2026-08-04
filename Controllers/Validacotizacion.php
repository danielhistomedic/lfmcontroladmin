<?php

/**
 * Controlador Validacotizacion
 * Permite la validación pública (sin sesión) de cotizaciones de cliente mediante lectura de código QR.
 */
class Validacotizacion extends Controllers
{
    public function __construct()
    {
        // Se ejecuta el constructor base sin validación de sesión para permitir el acceso libre
        parent::__construct();
    }

    /**
     * Vista principal de validación de cotización
     */
    public function Validacotizacion()
    {
        try {
            /*-------------------------------------------
            [ Obtención y sanitización de parámetros GET ]*/
            $folio = isset($_GET['folio']) ? trim(strClean($_GET['folio'])) : '';
            $fecha = isset($_GET['fecha']) ? trim(strClean($_GET['fecha'])) : '';
            $subtotal = isset($_GET['subtotal']) ? trim(strClean($_GET['subtotal'])) : '';
            
            $cotizacionBD = array();
            $statusValidacion = "NO_ENCONTRADO";
            $mensajeValidacion = "";
            $detallesCotejo = array(
                'folio_coincide' => false,
                'fecha_coincide' => null,
                'subtotal_coincide' => null
            );
            
            if (!empty($folio)) {
                /*-------------------------------------------
                [ Consulta a la base de datos ]*/
                $cotizacionBD = $this->model->getCotizacionCliente($folio);

                if (!empty($cotizacionBD)) {
                    $detallesCotejo['folio_coincide'] = true;
                    $statusValidacion = "VALIDO";
                    $mensajeValidacion = "Cotización Verificada y Auténtica";

                    // Obtener valores de BD
                    $fechaBD = $cotizacionBD['fecha'] ;
                    $subtotalBD = (float)($cotizacionBD['subtotal'] ?? 0) - (float)($cotizacionBD['descuento'] ?? 0);
                  
                    // Formatear fechas para comparación (si la fecha viene en el QR)
                    if (!empty($fecha) && !empty($fechaBD)) {
                        $fechaQRGen = date('Y-m-d', strtotime($fecha));
                        $fechaBDGen = date('Y-m-d', strtotime($fechaBD));
                        $detallesCotejo['fecha_coincide'] = ($fechaQRGen === $fechaBDGen);
                    }

                    // Comparar montos (si el subtotal o total viene en el QR)
                    if (!empty($subtotal) && $subtotalBD !== null) {
                        $numSubTotalQR = round((float)$subtotal, 2);
                        $numSubTotalBD = round((float)$subtotalBD, 2);
                        $detallesCotejo['subtotal_coincide'] = (abs($numSubTotalQR - $numSubTotalBD) < 0.01);
                    }

                    // Evaluar si existe alguna discrepancia
                    if ($detallesCotejo['fecha_coincide'] === false || $detallesCotejo['subtotal_coincide'] === false) {
                        $statusValidacion = "DISCREPANCIA";
                        $mensajeValidacion = "Atención: La cotización existe en la base de datos, pero los datos del QR difieren del registro original.";
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
            [ Preparación de datos para la vista ]*/
            $data['page_title'] = "Validación de Cotización | LFM CONTROL";
            $data['meta_keywords'] = "validación, cotización, cliente, lfm control, veracidad, comprobante";
            $data['meta_description'] = "Verificación de autenticidad de cotizaciones emitidas por LFM Control.";
            
            $data['qr_params'] = array(
                'folio' => $folio,
                'fecha' => $fecha,
                'subtotal' => $subtotal
            );

            $data['cotizacion_bd'] = $cotizacionBD;
            $data['validacion'] = array(
                'status' => $statusValidacion,
                'mensaje' => $mensajeValidacion,
                'detalles' => $detallesCotejo
            );

            /*-------------------------------------------
            [ Carga de la vista ]*/
            $this->views->getView($this, "validacotizacion", $data);

        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            echo "Ocurrió un error al procesar la solicitud de validación.";
        }
    }
}
