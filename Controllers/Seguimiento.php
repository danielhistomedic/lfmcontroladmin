<?php

/**
 * Controlador Seguimiento 
 * Módulo: Seguimiento de Órdenes de Compra Clientes
 */
class Seguimiento extends Controllers
{

    private $session;
    private $permisosMod;

    const prefijo_msj_error = "seguimiento";

    /**
     * Método Constructor de Controlador Seguimiento.
     * Inicializa Controllers::__construct.
     * Inicializa y valida datos de session.
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        // [ Validación de Sesion ]*/
        $this->session = new Session;
        if (!$this->session->getStatus()) {
            $this->session->redirect('inicio');
        }
    }

    /**
     * Carga la Vista Ordenes Cliente. 
     * URL: /seguimiento/ordenesCliente
     */
    public function ordenesCliente()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_CLIENTE] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];

            // Valida si tiene acceso a la pagina.
            if (empty($this->permisosMod['r'])) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_SEGUIMIENTO_ORDENES_CLIENTE);

            // Asigna los permisos de Módulo y SideBar
            $data['permisos']    = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;

            //Datos de Usuario en Sesión.
            $data['empresa_id']            = $this->session->get('empresa_id');
            $data['sucursal_id']           = $this->session->get('sucursal_id');
            $data['theme']                 = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email']      = $this->session->get('email');
            $data['usuario']['rol']        = $this->session->get('rol');
            $data['usuario']['rol_id']     = $this->session->get('rol_id');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            //Id de Menu para script de Permisos de Boton exportar a Excel
            $data['menu'] = MOD_SEGUIMIENTO_ORDENES_CLIENTE;

            //Header
            $data['page_title']        = !empty($menu['name'])        ? $menu['name']        : 'Seguimiento Órdenes Cliente';
            $data['meta_description']  = !empty($menu['descripcion']) ? $menu['descripcion'] : 'Seguimiento de órdenes de compra de clientes';
            $data['meta_keywords']     = !empty($menu['tags'])        ? $menu['tags']        : 'seguimiento, ordenes, clientes, ventas';

            //Form Principal
            $data['icon_form_title']  = !empty($menu['icon_form_title']) ? $menu['icon_form_title'] : '<i class="fa-regular fa-cart-shopping-fast fa-fw text-primary"></i>';
            $data['page_form_title']  = $data['icon_form_title'] . (!empty($menu['form_title']) ? $menu['form_title'] : ' Órdenes de Compra Clientes');

            //Breadcrumb
            $data['page_breadcrumb']     = 'Seguimiento / Órdenes de Compra Clientes';
            $data['page_card_title']     = !empty($menu['card_title']) ? $menu['card_title'] : 'Órdenes de Compra Clientes';
            $data['page_card_description'] = $data['meta_description'];

            //JS Principal
            $data['page_functions_js'] = !empty($menu['js']) ? $menu['js'] : 'ordenes_cliente.js';

            //Call Vista
            $this->views->getView($this, 'ordenes_cliente', $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Obtiene la lista de órdenes de clientes para llenar el DataTable.
     *
     * @return json
     */
    public function getOrdenesClienteDatatable()
    {
        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_CLIENTE] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];
            if (empty($this->permisosMod['r'])) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Recibe y limpia parámetros de filtro ]*/
            $fecha_ini      = strClean($_POST['fecha_ini']      ?? '');
            $fecha_fin      = strClean($_POST['fecha_fin']      ?? '');
            $filtro_estatus = strClean($_POST['filtro_estatus'] ?? '');
            $filtro_cliente = strClean($_POST['filtro_cliente'] ?? '');

            /*-------------------------------------------
            [ Obtiene el array de registros ]*/
            $class_model = new VentasModel;
            $arrData = $class_model->selectOrdenesClienteSeguimiento($fecha_ini, $fecha_fin, $filtro_estatus, $filtro_cliente);

            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            $data_animation = "fadeIn";

            for ($i = 0; $i < count($arrData); $i++) {

                // Badge estatus
                $estatus_id = $arrData[$i]['estatus_proyecto_id'];
                $estatus_txt = $arrData[$i]['estatus_proyecto'];

                if ($estatus_id >= 11) {
                    $badge_class = 'bg-success';
                } elseif ($estatus_id >= 8) {
                    $badge_class = 'bg-primary';
                } elseif ($estatus_id >= 6) {
                    $badge_class = 'bg-info';
                } else {
                    $badge_class = 'bg-warning';
                }
                $arrData[$i]['estatus_badge'] = '<span class="badge ' . $badge_class . ' fs-11">' . htmlspecialchars($estatus_txt) . '</span>';

                // Monto formateado
                $moneda = $arrData[$i]['cmoneda'] ?? 'USD';
                $monto  = number_format((float)($arrData[$i]['monto_pedido'] ?? 0), 2, '.', ',');
                $arrData[$i]['monto_formateado'] = $moneda . ' $' . $monto;

                // Total seguimientos badge
                $total_seg = (int)($arrData[$i]['total_seguimientos'] ?? 0);
                if ($total_seg > 0) {
                    $arrData[$i]['seguimientos_badge'] = '<span class="badge bg-primary-gradient">' . $total_seg . '</span>';
                } else {
                    $arrData[$i]['seguimientos_badge'] = '<span class="badge bg-secondary">0</span>';
                }

                // Notas de último seguimiento (truncar si es muy largo)
                $nota = $arrData[$i]['ultimo_seguimiento_nota'] ?? '';
                if (strlen($nota) > 60) {
                    $arrData[$i]['ultimo_seguimiento_nota_corta'] = substr($nota, 0, 60) . '...';
                } else {
                    $arrData[$i]['ultimo_seguimiento_nota_corta'] = $nota;
                }

                // Botón Ver Detalle
                $venta_id_enc = openssl_encrypt($arrData[$i]['venta_id'], METHODENCRIPT, KEY);
                $pedido_id_enc = openssl_encrypt($arrData[$i]['pedido_id'], METHODENCRIPT, KEY);

                $btnDetalle = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-info" '
                    . 'data-animation="' . $data_animation . '" '
                    . 'onclick="fntVerDetalle(this)" '
                    . 'data-venta-id="' . $venta_id_enc . '" '
                    . 'data-pedido-id="' . $pedido_id_enc . '" '
                    . 'title="Ver Detalle y Seguimiento">'
                    . '<i class="fa-sharp fa-regular fa-magnifying-glass"></i></button>';

                $btnDetalle = (!empty($this->permisosMod['r'])) ? $btnDetalle : '';
                $arrData[$i]['options'] = $btnDetalle;

                // Encriptar IDs
                $arrData[$i]['venta_id']  = $venta_id_enc;
                $arrData[$i]['pedido_id'] = $pedido_id_enc;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene el historial completo de seguimiento de una venta específica.
     *
     * @return json
     */
    public function getHistorialSeguimiento()
    {
        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                die(json_encode(getResponse('Acceso restringido', 'error'), JSON_UNESCAPED_UNICODE));
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_CLIENTE] ?? ['r' => 0];
            if (empty($this->permisosMod['r'])) {
                die(json_encode(getResponse('Acceso restringido', 'error'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Recibe y desencripta venta_id ]*/
            $venta_id_enc = strClean($_POST['venta_id'] ?? '');
            if (empty($venta_id_enc)) {
                die(json_encode(getResponse('Debe seleccionar una orden', 'error'), JSON_UNESCAPED_UNICODE));
            }

            $venta_id = intval(openssl_decrypt($venta_id_enc, METHODENCRIPT, KEY));
            if ($venta_id <= 0) {
                die(json_encode(getResponse('ID de orden no válido', 'error'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Obtiene historial ]*/
            $class_model = new VentasModel;
            $arrData     = $class_model->selectHistorialSeguimientoVenta($venta_id);

            $arrResponse = getResponse('Datos encontrados', 'ok', false);
            $arrResponse['data'] = $arrData;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Código Error: ' . self::prefijo_msj_error . '_1002. Error Desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
