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
            $data['usuario']['ccveusuario'] = $this->session->get('ccveusuario');

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
     * Carga la Vista Proyecto de Venta. 
     * URL: /seguimiento/proyectoVenta
     */
    public function proyectoVenta()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_PROYECTO_VENTA] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];

            // Valida si tiene acceso a la pagina.
            if (empty($this->permisosMod['r'])) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_SEGUIMIENTO_PROYECTO_VENTA);

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
            $data['usuario']['ccveusuario'] = $this->session->get('ccveusuario');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            //Id de Menu para script de Permisos
            $data['menu'] = MOD_SEGUIMIENTO_PROYECTO_VENTA;

            //Header
            $data['page_title']        = !empty($menu['name'])        ? $menu['name']        : 'Proyecto de Venta';
            $data['meta_description']  = !empty($menu['descripcion']) ? $menu['descripcion'] : 'Seguimiento por proyecto de venta';
            $data['meta_keywords']     = !empty($menu['tags'])        ? $menu['tags']        : 'seguimiento, proyecto, venta, proyectos';

            //Form Principal
            $data['icon_form_title']  = !empty($menu['icon_form_title']) ? $menu['icon_form_title'] : '<i class="fa-sharp fa-light fa-diagram-project text-primary me-2"></i>';
            $data['page_form_title']  = $data['icon_form_title'] . (!empty($menu['form_title']) ? $menu['form_title'] : ' Proyecto de Venta');

            //Breadcrumb
            $data['page_breadcrumb']       = 'Seguimiento / Proyecto de Venta';
            $data['page_card_title']       = !empty($menu['card_title']) ? $menu['card_title'] : 'Proyecto de Venta';
            $data['page_card_description'] = $data['meta_description'];

            //JS Principal
            $data['page_functions_js'] = !empty($menu['js']) ? $menu['js'] : 'proyecto_venta.js';

            //Call Vista
            $this->views->getView($this, 'proyecto_venta', $data);
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
            $fecha_ini        = strClean($_POST['fecha_ini']        ?? '');
            $fecha_fin        = strClean($_POST['fecha_fin']        ?? '');
            $filtro_num_orden = strClean($_POST['filtro_num_orden']   ?? '');
            $filtro_estatus   = strClean($_POST['filtro_estatus']   ?? '');
            $filtro_cliente   = strClean($_POST['filtro_cliente']   ?? '');

            /*-------------------------------------------
            [ Filtro Vendedor (rol_id = 4) ]*/
            $rol_id = intval($this->session->get('rol_id') ?? 0);
            $ccveusuario_vendedor = '';
            if ($rol_id === 4) {
                $ccveusuario_vendedor = strClean($this->session->get('ccveusuario') ?? '');
            }

            /*-------------------------------------------
            [ Obtiene el array de registros ]*/
            $class_model = new VentasModel;
            $arrData = $class_model->selectOrdenesClienteSeguimiento($fecha_ini, $fecha_fin, $filtro_estatus, $filtro_cliente, $ccveusuario_vendedor, $filtro_num_orden);

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
                    $arrData[$i]['seguimientos_badge'] = '<span class="badge bg-primary">' . $total_seg . '</span>';
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

                // Encriptar IDs para llamadas AJAX
                $arrData[$i]['venta_id_enc']  = $venta_id_enc;
                $arrData[$i]['pedido_id_enc'] = $pedido_id_enc;
                $arrData[$i]['venta_id']      = $venta_id_enc;
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
            $permisoOrdenes  = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_CLIENTE]['r'] ?? 0;
            $permisoProyecto = $arrPermisos[MOD_SEGUIMIENTO_PROYECTO_VENTA]['r'] ?? 0;
            if (empty($permisoOrdenes) && empty($permisoProyecto)) {
                die(json_encode(getResponse('Acceso restringido', 'error'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Recibe y desencripta / obtiene venta_id ]*/
            $venta_id_input = strClean($_POST['venta_id'] ?? '');
            if (empty($venta_id_input)) {
                die(json_encode(getResponse('Debe seleccionar una orden o proyecto', 'error'), JSON_UNESCAPED_UNICODE));
            }

            if (is_numeric($venta_id_input)) {
                $venta_id = intval($venta_id_input);
            } else {
                $venta_id = intval(openssl_decrypt($venta_id_input, METHODENCRIPT, KEY));
            }

            if ($venta_id <= 0) {
                die(json_encode(getResponse('ID de proyecto no válido', 'error'), JSON_UNESCAPED_UNICODE));
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

    /**
     * Obtiene los archivos adjuntos del proyecto de venta de las 6 tablas especificadas.
     *
     * @return json
     */
    public function getAdjuntosProyecto()
    {
        try {
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                die(json_encode(getResponse('Acceso restringido', 'error'), JSON_UNESCAPED_UNICODE));
            }
            $permisoProyecto = $arrPermisos[MOD_SEGUIMIENTO_PROYECTO_VENTA]['r'] ?? 0;
            $permisoOrdenes  = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_CLIENTE]['r'] ?? 0;
            if (empty($permisoProyecto) && empty($permisoOrdenes)) {
                die(json_encode(getResponse('Acceso restringido', 'error'), JSON_UNESCAPED_UNICODE));
            }

            $venta_id_input = strClean($_POST['venta_id'] ?? '');
            if (empty($venta_id_input)) {
                die(json_encode(getResponse('Debe seleccionar un proyecto', 'error'), JSON_UNESCAPED_UNICODE));
            }

            if (is_numeric($venta_id_input)) {
                $venta_id = intval($venta_id_input);
            } else {
                $venta_id = intval(openssl_decrypt($venta_id_input, METHODENCRIPT, KEY));
            }

            if ($venta_id <= 0) {
                die(json_encode(getResponse('ID de proyecto no válido', 'error'), JSON_UNESCAPED_UNICODE));
            }

            $arrData = $this->model->getAdjuntosProyectoVenta($venta_id);

            $arrResponse = getResponse('Datos encontrados', 'ok', false);
            $arrResponse['data'] = $arrData;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Código Error: ' . self::prefijo_msj_error . '_1003. Error Desconocido'), JSON_UNESCAPED_UNICODE));
        }

        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene los eventos de fechas estimadas de entrega a clientes para el calendario.
     *
     * @return json
     */
    public function getCalendarioEntregasData()
    {
        try {
            $arrResponse = array(
                'respuesta' => 'ok',
                'resumen'   => array('total' => 0, 'vencidos' => 0, 'proximos' => 0, 'en_tiempo' => 0),
                'eventos'   => array(),
                'raw_data'  => array()
            );

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_CLIENTE] ?? ['r' => 0];
            if (empty($this->permisosMod['r'])) {
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Recibe parámetros de fecha opcionales ]*/
            $fecha_ini = strClean($_POST['fecha_ini'] ?? '');
            $fecha_fin = strClean($_POST['fecha_fin'] ?? '');

            /*-------------------------------------------
            [ Filtro Vendedor (rol_id = 4) ]*/
            $rol_id = intval($this->session->get('rol_id') ?? 0);
            $ccveusuario_vendedor = '';
            if ($rol_id === 4) {
                $ccveusuario_vendedor = strClean($this->session->get('ccveusuario') ?? '');
            }

            /*-------------------------------------------
            [ Consulta el Modelo ]*/
            $class_model = new VentasModel;
            $arrData = $class_model->selectFechasEntregaClientes($fecha_ini, $fecha_fin, $ccveusuario_vendedor);

            $hoy = date('Y-m-d');
            $limite_proximo = date('Y-m-d', strtotime('+7 days'));

            $eventos = array();
            $raw_data = array();
            $count_entregados = 0;
            $count_vencidos   = 0;
            $count_proximos   = 0;
            $count_en_tiempo  = 0;
            $count_cancelados = 0;

            $hoy_dt = new DateTime($hoy);

            foreach ($arrData as $item) {
                $fecha_estimada = $item['fecha_estimada_entrega'];
                $fecha_est_dt   = new DateTime($fecha_estimada);
                $diff           = $hoy_dt->diff($fecha_est_dt);
                $dias_restantes = (int)$diff->format('%r%a');

                $entregado_val  = intval($item['entregado'] ?? 0);

                if ($entregado_val === 1) {
                    // 1 = Entregado
                    $estatus_codigo      = 'entregado';
                    $estatus_label       = 'Entregado';
                    $badge_class         = 'bg-primary';
                    $event_color         = '#0d6efd';
                    $event_text_color    = '#ffffff';
                    $tiempo_restante_str = 'Entregado a cliente';
                    $count_entregados++;
                } elseif ($entregado_val === 2) {
                    // 2 = Entrega Cancelada
                    $estatus_codigo      = 'cancelado';
                    $estatus_label       = 'Entrega Cancelada';
                    $badge_class         = 'bg-secondary';
                    $event_color         = '#6c757d';
                    $event_text_color    = '#ffffff';
                    $tiempo_restante_str = 'Entrega cancelada';
                    $count_cancelados++;
                } else {
                    // 0 = Pendiente de Entrega
                    if ($dias_restantes > 0) {
                        $tiempo_restante_str = $dias_restantes . ' día' . ($dias_restantes > 1 ? 's' : '');
                    } elseif ($dias_restantes === 0) {
                        $tiempo_restante_str = '0 días (Entrega hoy)';
                    } else {
                        $tiempo_restante_str = abs($dias_restantes) . ' día' . (abs($dias_restantes) > 1 ? 's' : '') . ' de atraso';
                    }

                    if ($fecha_estimada < $hoy) {
                        $estatus_codigo   = 'vencido';
                        $estatus_label    = 'Vencido (Pendiente)';
                        $badge_class      = 'bg-danger';
                        $event_color      = '#dc3545';
                        $event_text_color = '#ffffff';
                        $count_vencidos++;
                    } elseif ($fecha_estimada <= $limite_proximo) {
                        $estatus_codigo   = 'proximo';
                        $estatus_label    = 'Próximo a Vencer';
                        $badge_class      = 'bg-warning text-dark';
                        $event_color      = '#ffc107';
                        $event_text_color = '#212529';
                        $count_proximos++;
                    } else {
                        $estatus_codigo   = 'en_tiempo';
                        $estatus_label    = 'En Tiempo';
                        $badge_class      = 'bg-success';
                        $event_color      = '#198754';
                        $event_text_color = '#ffffff';
                        $count_en_tiempo++;
                    }
                }

                $item['dias_restantes']      = $dias_restantes;
                $item['tiempo_restante_str'] = $tiempo_restante_str;
                $item['estatus_codigo']      = $estatus_codigo;
                $item['estatus_label']       = $estatus_label;
                $item['badge_class']         = $badge_class;

                $oc_str = !empty($item['num_orden_compra']) ? $item['num_orden_compra'] : ('Pedido #' . $item['pedido_id']);
                $partida_str = !empty($item['codigo_partida']) ? $item['codigo_partida'] : '';
                
                $desc_limpia = trim(preg_replace('/\s+/', ' ', $item['descripcion'] ?? ''));
                if (mb_strlen($desc_limpia) > 60) {
                    $desc_corta = mb_substr($desc_limpia, 0, 60) . '...';
                } else {
                    $desc_corta = $desc_limpia;
                }

                $title_parts = array_filter([$oc_str, $partida_str, $desc_corta]);
                $event_title = implode(' - ', $title_parts);

                $eventos[] = array(
                    'id'              => $item['detalle_id'],
                    'title'           => $event_title,
                    'start'           => $fecha_estimada,
                    'backgroundColor' => $event_color,
                    'borderColor'     => $event_color,
                    'textColor'       => $event_text_color,
                    'extendedProps'   => $item
                );

                $raw_data[] = $item;
            }

            $arrResponse['resumen'] = array(
                'total'      => count($arrData),
                'entregados' => $count_entregados,
                'vencidos'   => $count_vencidos,
                'proximos'   => $count_proximos,
                'en_tiempo'  => $count_en_tiempo,
                'cancelados' => $count_cancelados
            );
            $arrResponse['eventos']  = $eventos;
            $arrResponse['raw_data'] = $raw_data;

        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Busca proyecto(s) de venta por id/folio y devuelve la información general y checklist del proceso.
     * URL / AJAX: /seguimiento/buscarProyectoVenta
     * 
     * @return json
     */
    public function buscarProyectoVenta()
    {
        try {
            $arrResponse = array(
                'status'    => false,
                'msg'       => '',
                'data'      => array(),
                'proyectos' => array(),
                'checklist' => array(),
                'partidas'  => array()
            );

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $arrResponse['msg'] = 'Acceso restringido';
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_PROYECTO_VENTA] ?? ['r' => 0];
            if (empty($this->permisosMod['r'])) {
                $arrResponse['msg'] = 'Acceso restringido';
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Parámetros de entrada ]*/
            $proyecto_id_input = strClean($_POST['proyecto_id'] ?? '');
            $venta_id_select   = intval($_POST['venta_id'] ?? 0);

            if (empty($proyecto_id_input) && $venta_id_select <= 0) {
                $arrResponse['msg'] = 'Debe ingresar la clave o número de folio del proyecto de venta.';
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Filtro Vendedor (rol_id = 4) ]*/
            $rol_id = intval($this->session->get('rol_id') ?? 0);
            $ccveusuario_vendedor = '';
            if ($rol_id === 4) {
                $ccveusuario_vendedor = strClean($this->session->get('ccveusuario') ?? '');
            }

            $model = new SeguimientoModel();

            // Si se especificó un venta_id directamente
            if ($venta_id_select > 0) {
                $proyectoData = $model->getProyectoVentaById($venta_id_select, $ccveusuario_vendedor);
                if (!empty($proyectoData)) {
                    $checklist    = $model->getChecklistProceso($proyectoData['id'], $proyectoData['estatus_proyecto_id']);
                    $partidasData = $model->getPartidasProyecto($proyectoData['id']);
                    $arrResponse['status']    = true;
                    $arrResponse['data']      = $proyectoData;
                    $arrResponse['checklist'] = $checklist;
                    $arrResponse['partidas']  = $partidasData;
                    $arrResponse['msg']       = 'Proyecto localizado correctamente';
                } else {
                    $arrResponse['msg'] = 'No se encontró el proyecto seleccionado.';
                }
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            // Buscar proyectos coincidentes
            $listaProyectos = $model->buscarProyectosVenta($proyecto_id_input, $ccveusuario_vendedor);

            if (empty($listaProyectos)) {
                $arrResponse['msg'] = 'No se encontraron proyectos de venta con la clave ingresada: "' . htmlspecialchars($proyecto_id_input) . '"';
            } elseif (count($listaProyectos) === 1) {
                // Exactamente 1 coincidencia
                $proyectoData = $model->getProyectoVentaById($listaProyectos[0]['id'], $ccveusuario_vendedor);
                $checklist    = $model->getChecklistProceso($proyectoData['id'], $proyectoData['estatus_proyecto_id']);
                $partidasData = $model->getPartidasProyecto($proyectoData['id']);
                $arrResponse['status']    = true;
                $arrResponse['data']      = $proyectoData;
                $arrResponse['proyectos'] = $listaProyectos;
                $arrResponse['checklist'] = $checklist;
                $arrResponse['partidas']  = $partidasData;
                $arrResponse['msg']       = 'Proyecto localizado correctamente';
            } else {
                // Múltiples coincidencias -> Devolver lista para que el usuario elija y cargar por defecto el primero
                $proyectoData = $model->getProyectoVentaById($listaProyectos[0]['id'], $ccveusuario_vendedor);
                $checklist    = $model->getChecklistProceso($proyectoData['id'], $proyectoData['estatus_proyecto_id']);
                $partidasData = $model->getPartidasProyecto($proyectoData['id']);
                $arrResponse['status']    = true;
                $arrResponse['data']      = $proyectoData;
                $arrResponse['proyectos'] = $listaProyectos;
                $arrResponse['checklist'] = $checklist;
                $arrResponse['partidas']  = $partidasData;
                $arrResponse['msg']       = 'Se encontraron ' . count($listaProyectos) . ' proyectos coincidentes.';
            }

        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            $arrResponse['msg'] = 'Error al procesar la solicitud.';
        }

        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene las partidas del proyecto seleccionado vía AJAX.
     * URL / AJAX: /seguimiento/getPartidasProyecto
     */
    public function getPartidasProyecto()
    {
        try {
            $arrResponse = array('status' => false, 'data' => array());
            $venta_id = intval($_POST['venta_id'] ?? 0);
            if ($venta_id > 0) {
                $model = new SeguimientoModel();
                $arrData = $model->getPartidasProyecto($venta_id);
                $arrResponse['status'] = true;
                $arrResponse['data']   = $arrData;
            }
            die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(array('status' => false, 'data' => array()), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Obtiene la lista de proyectos de venta para llenar el DataTable.
     * URL / AJAX: /seguimiento/getProyectosVentaDatatable
     * 
     * @return json
     */
    public function getProyectosVentaDatatable()
    {
        try {
            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_PROYECTO_VENTA] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];
            if (empty($this->permisosMod['r'])) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Recibe y limpia parámetros de filtro ]*/
            $fecha_ini = strClean($_POST['fecha_ini'] ?? '');
            $fecha_fin = strClean($_POST['fecha_fin'] ?? '');
            $busqueda  = strClean($_POST['busqueda']  ?? '');
            $titulo    = strClean($_POST['titulo']    ?? '');
            $cliente   = strClean($_POST['cliente']   ?? '');
            $vendedor  = strClean($_POST['vendedor']  ?? '');

            /*-------------------------------------------
            [ Filtro Vendedor (rol_id = 4) ]*/
            $rol_id = intval($this->session->get('rol_id') ?? 0);
            $ccveusuario_vendedor = '';
            if ($rol_id === 4) {
                $ccveusuario_vendedor = strClean($this->session->get('ccveusuario') ?? '');
            }

            if (!empty($fecha_ini) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha_ini)) {
                $parts = explode('/', $fecha_ini);
                $fecha_ini = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }

            if (!empty($fecha_fin) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha_fin)) {
                $parts = explode('/', $fecha_fin);
                $fecha_fin = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }

            /*-------------------------------------------
            [ Obtiene el array de registros ]*/
            $model = new SeguimientoModel();
            $arrData = $model->selectProyectosVentaSeguimiento($fecha_ini, $fecha_fin, $busqueda, $titulo, $cliente, $vendedor, $ccveusuario_vendedor);

            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            for ($i = 0; $i < count($arrData); $i++) {
                $venta_id = $arrData[$i]['id'];
                $estatus_id = (int)$arrData[$i]['estatus_proyecto_id'];
                $estatus_txt = $arrData[$i]['estatus_proyecto'];

                if ($estatus_id === 2) {
                    $badge_class = 'bg-danger';
                } elseif ($estatus_id >= 7) {
                    $badge_class = 'bg-success';
                } elseif ($estatus_id >= 5) {
                    $badge_class = 'bg-info text-dark';
                } else {
                    $badge_class = 'bg-warning text-dark';
                }
                $arrData[$i]['estatus_badge'] = '<span class="badge ' . $badge_class . ' fs-11">' . htmlspecialchars($estatus_txt) . '</span>';

                $moneda = !empty($arrData[$i]['cmoneda']) ? $arrData[$i]['cmoneda'] : 'USD';
                $monto  = (float)($arrData[$i]['total'] ?? 0);
                $arrData[$i]['monto_formateado'] = $moneda . ' $' . number_format($monto, 2, '.', ',');

                $btnDetalle = '<button type="button" class="btn btn-sm btn-primary px-2 py-1 btnVerDetalleProyecto" data-id="' . $venta_id . '" title="Ver Detalle de Seguimiento"><i class="fa-regular fa-eye me-1"></i>Ver Detalle</button>';
                $arrData[$i]['options'] = $btnDetalle;
            }

            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);
            die();
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            echo json_encode(array(), JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    /**
     * Carga la Vista Ordenes de Compra Proveedores.
     * URL: /seguimiento/ordenesProveedor
     */
    public function ordenesProveedor()
    {
        try {
            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_PROVEEDOR] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];

            // Valida si tiene acceso a la pagina.
            if (empty($this->permisosMod['r'])) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_SEGUIMIENTO_ORDENES_PROVEEDOR);

            // Asigna los permisos de Módulo y SideBar
            $data['permisos']    = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;

            // Datos de Usuario en Sesión.
            $data['empresa_id']             = $this->session->get('empresa_id');
            $data['sucursal_id']            = $this->session->get('sucursal_id');
            $data['theme']                  = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email']       = $this->session->get('email');
            $data['usuario']['rol']         = $this->session->get('rol');
            $data['usuario']['rol_id']      = $this->session->get('rol_id');
            $data['usuario']['ccveusuario']  = $this->session->get('ccveusuario');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            // Id de Menu para script de Permisos de Boton exportar a Excel
            $data['menu'] = MOD_SEGUIMIENTO_ORDENES_PROVEEDOR;

            // Header
            $data['page_title']        = !empty($menu['name'])        ? $menu['name']        : 'Seguimiento Órdenes Proveedor';
            $data['meta_description']  = !empty($menu['descripcion']) ? $menu['descripcion'] : 'Seguimiento de órdenes de compra a proveedores';
            $data['meta_keywords']     = !empty($menu['tags'])        ? $menu['tags']        : 'seguimiento, ordenes, proveedores, compras, pedidos';

            // Form Principal
            $data['icon_form_title']  = !empty($menu['icon_form_title']) ? $menu['icon_form_title'] : '<i class="fa-regular fa-truck-ramp-box fa-fw text-primary"></i>';
            $data['page_form_title']  = $data['icon_form_title'] . (!empty($menu['form_title']) ? (' ' . $menu['form_title']) : ' Órdenes de Compra Proveedores');

            // Breadcrumb
            $data['page_breadcrumb']       = 'Seguimiento / Órdenes de Compra Proveedores';
            $data['page_card_title']       = !empty($menu['card_title']) ? $menu['card_title'] : 'Órdenes de Compra Proveedores';
            $data['page_card_description'] = $data['meta_description'];

            // JS Principal
            $data['page_functions_js'] = !empty($menu['js']) ? $menu['js'] : 'ordenes_proveedor.js';

            // Call Vista
            $this->views->getView($this, 'ordenes_proveedor', $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Obtiene la lista de órdenes a proveedores para llenar el DataTable.
     * URL / AJAX: /seguimiento/getOrdenesProveedorDatatable
     *
     * @return json
     */
    public function getOrdenesProveedorDatatable()
    {
        try {
            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_PROVEEDOR] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];
            if (empty($this->permisosMod['r'])) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Recibe y limpia parámetros de filtro ]*/
            $fecha_ini        = strClean($_POST['fecha_ini']        ?? '');
            $fecha_fin        = strClean($_POST['fecha_fin']        ?? '');
            $filtro_num_orden = strClean($_POST['filtro_num_orden'] ?? '');
            $filtro_estatus   = strClean($_POST['filtro_estatus']   ?? '');
            $filtro_proveedor = strClean($_POST['filtro_proveedor'] ?? '');

            /*-------------------------------------------
            [ Filtro Vendedor (rol_id = 4) ]*/
            $rol_id = intval($this->session->get('rol_id') ?? 0);
            $ccveusuario_filtro = '';
            if ($rol_id === 4) {
                $ccveusuario_filtro = strClean($this->session->get('ccveusuario') ?? '');
            }

            /*-------------------------------------------
            [ Obtiene el array de registros ]*/
            $model = new SeguimientoModel();
            $arrData = $model->selectOrdenesProveedorSeguimiento(
                $fecha_ini,
                $fecha_fin,
                $filtro_estatus,
                $filtro_proveedor,
                $ccveusuario_filtro,
                $filtro_num_orden
            );

            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            $data_animation = "fadeIn";

            for ($i = 0; $i < count($arrData); $i++) {
                // Badge estatus
                $estatus_id  = intval($arrData[$i]['estatus_proyecto_id']);
                $estatus_txt = $arrData[$i]['estatus_proyecto'];

                if ($estatus_id >= 11) {
                    $badge_class = 'bg-success';
                } elseif ($estatus_id >= 8) {
                    $badge_class = 'bg-primary';
                } elseif ($estatus_id >= 6) {
                    $badge_class = 'bg-info';
                } else {
                    $badge_class = 'bg-warning text-dark';
                }
                $arrData[$i]['estatus_badge'] = '<span class="badge ' . $badge_class . ' fs-11">' . htmlspecialchars($estatus_txt) . '</span>';

                // Monto formateado
                $moneda = $arrData[$i]['cmoneda'] ?? 'USD';
                $monto  = number_format((float)($arrData[$i]['monto_pedido'] ?? 0), 2, '.', ',');
                $arrData[$i]['monto_formateado'] = $moneda . ' $' . $monto;

                // Total seguimientos badge
                $total_seg = intval($arrData[$i]['total_seguimientos'] ?? 0);
                if ($total_seg > 0) {
                    $arrData[$i]['seguimientos_badge'] = '<span class="badge bg-primary">' . $total_seg . '</span>';
                } else {
                    $arrData[$i]['seguimientos_badge'] = '<span class="badge bg-secondary">0</span>';
                }

                // Total partidas badge con desglose de entregado
                $total_partidas            = intval($arrData[$i]['total_partidas'] ?? 0);
                $total_partidas_entregadas = intval($arrData[$i]['total_partidas_entregadas'] ?? 0);
                $total_partidas_canceladas = intval($arrData[$i]['total_partidas_canceladas'] ?? 0);
                $total_partidas_pendientes = intval($arrData[$i]['total_partidas_pendientes'] ?? 0);

                if ($total_partidas === 0) {
                    $arrData[$i]['partidas_badge'] = '<span class="badge bg-secondary">0</span>';
                } elseif ($total_partidas_entregadas === $total_partidas) {
                    $arrData[$i]['partidas_badge'] = '<span class="badge bg-primary" title="Todas las partidas entregadas (' . $total_partidas_entregadas . '/' . $total_partidas . ')"><i class="fa-regular fa-box-check me-1"></i>' . $total_partidas_entregadas . '/' . $total_partidas . '</span>';
                } elseif ($total_partidas_entregadas > 0) {
                    $arrData[$i]['partidas_badge'] = '<span class="badge bg-info text-dark" title="Partidas entregadas: ' . $total_partidas_entregadas . ' de ' . $total_partidas . '"><i class="fa-regular fa-boxes-stacked me-1"></i>' . $total_partidas_entregadas . '/' . $total_partidas . '</span>';
                } else {
                    $arrData[$i]['partidas_badge'] = '<span class="badge bg-secondary" title="' . $total_partidas . ' partidas pendientes"><i class="fa-regular fa-clock me-1"></i>' . $total_partidas . '</span>';
                }

                // Total adjuntos badge
                $total_adjuntos = intval($arrData[$i]['total_adjuntos'] ?? 0);
                $arrData[$i]['adjuntos_badge'] = '<span class="badge bg-secondary">' . $total_adjuntos . '</span>';

                // Notas de último seguimiento (truncar si es muy largo)
                $nota = $arrData[$i]['ultimo_seguimiento_nota'] ?? '';
                if (mb_strlen($nota) > 60) {
                    $arrData[$i]['ultimo_seguimiento_nota_corta'] = mb_substr($nota, 0, 60) . '...';
                } else {
                    $arrData[$i]['ultimo_seguimiento_nota_corta'] = $nota;
                }

                // Encriptar IDs para llamadas AJAX
                $pedido_id_enc = openssl_encrypt($arrData[$i]['pedido_id'], METHODENCRIPT, KEY);
                $venta_id_enc  = !empty($arrData[$i]['venta_id']) ? openssl_encrypt($arrData[$i]['venta_id'], METHODENCRIPT, KEY) : '';

                // Botón Ver Detalle
                $btnDetalle = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-info" '
                    . 'data-animation="' . $data_animation . '" '
                    . 'onclick="fntVerDetalle(this)" '
                    . 'data-pedido-id="' . $pedido_id_enc . '" '
                    . 'data-venta-id="' . $venta_id_enc . '" '
                    . 'title="Ver Detalle y Seguimiento">'
                    . '<i class="fa-sharp fa-regular fa-magnifying-glass"></i></button>';

                $btnDetalle = (!empty($this->permisosMod['r'])) ? $btnDetalle : '';
                $arrData[$i]['options'] = $btnDetalle;

                $arrData[$i]['pedido_id_enc'] = $pedido_id_enc;
                $arrData[$i]['venta_id_enc']  = $venta_id_enc;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene los eventos de fechas estimadas de entrega de proveedores para el calendario.
     * URL / AJAX: /seguimiento/getCalendarioEntregasProveedorData
     *
     * @return json
     */
    public function getCalendarioEntregasProveedorData()
    {
        try {
            $arrResponse = array(
                'respuesta' => 'ok',
                'resumen'   => array('total' => 0, 'vencidos' => 0, 'proximos' => 0, 'en_tiempo' => 0),
                'eventos'   => array(),
                'raw_data'  => array()
            );

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_PROVEEDOR] ?? ['r' => 0];
            if (empty($this->permisosMod['r'])) {
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Recibe parámetros de fecha opcionales ]*/
            $fecha_ini = strClean($_POST['fecha_ini'] ?? '');
            $fecha_fin = strClean($_POST['fecha_fin'] ?? '');

            /*-------------------------------------------
            [ Filtro Vendedor (rol_id = 4) ]*/
            $rol_id = intval($this->session->get('rol_id') ?? 0);
            $ccveusuario_filtro = '';
            if ($rol_id === 4) {
                $ccveusuario_filtro = strClean($this->session->get('ccveusuario') ?? '');
            }

            /*-------------------------------------------
            [ Consulta el Modelo ]*/
            $model = new SeguimientoModel();
            $arrData = $model->selectFechasEntregaProveedores($fecha_ini, $fecha_fin, $ccveusuario_filtro);

            $hoy = date('Y-m-d');
            $limite_proximo = date('Y-m-d', strtotime('+7 days'));

            $eventos = array();
            $raw_data = array();
            $count_entregados = 0;
            $count_vencidos   = 0;
            $count_proximos   = 0;
            $count_en_tiempo  = 0;
            $count_cancelados = 0;

            $hoy_dt = new DateTime($hoy);

            foreach ($arrData as $item) {
                $fecha_estimada = $item['fecha_estimada_entrega'];
                $fecha_est_dt   = new DateTime($fecha_estimada);
                $diff           = $hoy_dt->diff($fecha_est_dt);
                $dias_restantes = (int)$diff->format('%r%a');

                $entregado_val  = intval($item['entregado'] ?? 0);

                // Cálculo de retraso real respecto a la fecha de entrega en almacén (tb_recibos.fchRecibo)
                $dias_retraso_entrega = 0;
                if (!empty($item['fecha_recibo']) && $item['fecha_recibo'] != '0000-00-00' && $item['fecha_recibo'] != '0000-00-00 00:00:00') {
                    $fecha_recibo_str = substr($item['fecha_recibo'], 0, 10);
                    $fecha_recibo_dt  = new DateTime($fecha_recibo_str);
                    $diff_recibo      = $fecha_est_dt->diff($fecha_recibo_dt);
                    $dias_diff_recibo = (int)$diff_recibo->format('%r%a'); // > 0 indica que fchRecibo es posterior a fecha_estimada

                    if ($dias_diff_recibo > 0) {
                        $dias_retraso_entrega = $dias_diff_recibo;
                    }
                }

                if ($entregado_val === 1) {
                    // 1 = Entregado
                    $estatus_codigo      = 'entregado';
                    $estatus_label       = 'Entregado';
                    $badge_class         = 'bg-primary';
                    $event_color         = '#0d6efd';
                    $event_text_color    = '#ffffff';

                    if ($dias_retraso_entrega > 0) {
                        $tiempo_restante_str = 'Entregado con ' . $dias_retraso_entrega . ' día' . ($dias_retraso_entrega > 1 ? 's' : '') . ' de retraso';
                    } elseif (!empty($item['fecha_recibo']) && $item['fecha_recibo'] != '0000-00-00') {
                        $tiempo_restante_str = 'Entregado a tiempo en almacén';
                    } else {
                        $tiempo_restante_str = 'Entregado por proveedor';
                    }
                    $count_entregados++;
                } elseif ($entregado_val === 2) {
                    // 2 = Entrega Cancelada
                    $estatus_codigo      = 'cancelado';
                    $estatus_label       = 'Entrega Cancelada';
                    $badge_class         = 'bg-secondary';
                    $event_color         = '#6c757d';
                    $event_text_color    = '#ffffff';
                    $tiempo_restante_str = 'Entrega cancelada';
                    $count_cancelados++;
                } else {
                    // 0 = Pendiente de Entrega
                    if ($dias_retraso_entrega > 0) {
                        $tiempo_restante_str = 'Entregado con ' . $dias_retraso_entrega . ' día' . ($dias_retraso_entrega > 1 ? 's' : '') . ' de retraso';
                    } else {
                        if ($dias_restantes > 0) {
                            $tiempo_restante_str = $dias_restantes . ' día' . ($dias_restantes > 1 ? 's' : '');
                        } elseif ($dias_restantes === 0) {
                            $tiempo_restante_str = '0 días (Entrega hoy)';
                        } else {
                            $tiempo_restante_str = abs($dias_restantes) . ' día' . (abs($dias_restantes) > 1 ? 's' : '') . ' de atraso';
                        }
                    }

                    if ($fecha_estimada < $hoy) {
                        $estatus_codigo   = 'vencido';
                        $estatus_label    = 'Vencido (Pendiente)';
                        $badge_class      = 'bg-danger';
                        $event_color      = '#dc3545';
                        $event_text_color = '#ffffff';
                        $count_vencidos++;
                    } elseif ($fecha_estimada <= $limite_proximo) {
                        $estatus_codigo   = 'proximo';
                        $estatus_label    = 'Próximo a Vencer (<= 7 días)';
                        $badge_class      = 'bg-warning text-dark';
                        $event_color      = '#ffc107';
                        $event_text_color = '#212529';
                        $count_proximos++;
                    } else {
                        $estatus_codigo   = 'en_tiempo';
                        $estatus_label    = 'En Tiempo';
                        $badge_class      = 'bg-success';
                        $event_color      = '#198754';
                        $event_text_color = '#ffffff';
                        $count_en_tiempo++;
                    }
                }

                $item['dias_retraso_entrega'] = $dias_retraso_entrega;
                $item['dias_restantes']      = $dias_restantes;
                $item['tiempo_restante_str'] = $tiempo_restante_str;
                $item['estatus_codigo']      = $estatus_codigo;
                $item['estatus_label']       = $estatus_label;
                $item['badge_class']         = $badge_class;

                $oc_str      = !empty($item['num_orden_compra']) ? $item['num_orden_compra'] : ('Pedido Prov. #' . $item['pedido_id']);
                $prov_str    = !empty($item['proveedor']) ? $item['proveedor'] : '';
                $partida_str = !empty($item['codigo_partida']) ? $item['codigo_partida'] : '';

                $desc_limpia = trim(preg_replace('/\s+/', ' ', $item['descripcion'] ?? ''));
                if (mb_strlen($desc_limpia) > 50) {
                    $desc_corta = mb_substr($desc_limpia, 0, 50) . '...';
                } else {
                    $desc_corta = $desc_limpia;
                }

                $title_parts = array_filter([$oc_str, $prov_str, $partida_str, $desc_corta]);
                $event_title = implode(' - ', $title_parts);

                $eventos[] = array(
                    'id'              => $item['detalle_id'],
                    'title'           => $event_title,
                    'start'           => $fecha_estimada,
                    'backgroundColor' => $event_color,
                    'borderColor'     => $event_color,
                    'textColor'       => $event_text_color,
                    'extendedProps'   => $item
                );

                $raw_data[] = $item;
            }

            $arrResponse['resumen'] = array(
                'total'      => count($arrData),
                'entregados' => $count_entregados,
                'vencidos'   => $count_vencidos,
                'proximos'   => $count_proximos,
                'en_tiempo'  => $count_en_tiempo,
                'cancelados' => $count_cancelados
            );
            $arrResponse['eventos']  = $eventos;
            $arrResponse['raw_data'] = $raw_data;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene los datos detallados de una orden de compra a proveedor,
     * incluyendo datos generales, partidas (tb_pedidos_proveedor_detalle),
     * adjuntos (tb_pedidos_proveedor_adjuntos) y la bitácora de seguimiento.
     * URL / AJAX: /seguimiento/getDetallePedidoProveedor
     *
     * @return json
     */
    public function getDetallePedidoProveedor()
    {
        try {
            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                die(json_encode(getResponse('Acceso restringido', 'error'), JSON_UNESCAPED_UNICODE));
            }
            $this->permisosMod = $arrPermisos[MOD_SEGUIMIENTO_ORDENES_PROVEEDOR] ?? ['r' => 0];
            if (empty($this->permisosMod['r'])) {
                die(json_encode(getResponse('Acceso restringido', 'error'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Recibe y desencripta pedido_id ]*/
            $pedido_id_input = strClean($_POST['pedido_id'] ?? '');
            if (empty($pedido_id_input)) {
                die(json_encode(getResponse('Debe especificar el ID de la orden a proveedor', 'error'), JSON_UNESCAPED_UNICODE));
            }

            if (is_numeric($pedido_id_input)) {
                $pedido_id = intval($pedido_id_input);
            } else {
                $pedido_id = intval(openssl_decrypt($pedido_id_input, METHODENCRIPT, KEY));
            }

            if ($pedido_id <= 0) {
                die(json_encode(getResponse('ID de orden a proveedor no válido', 'error'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Consultas a través del Modelo ]*/
            $model = new SeguimientoModel();
            $detallePedido = $model->selectDetallePedidoProveedorCompleto($pedido_id);
            if (empty($detallePedido)) {
                die(json_encode(getResponse('No se encontró la orden de compra a proveedor especificada.', 'error'), JSON_UNESCAPED_UNICODE));
            }

            $partidas = $model->selectPartidasPedidoProveedor($pedido_id);
            $adjuntos = $model->selectAdjuntosPedidoProveedor($pedido_id);

            // Obtener venta_id para consultar la bitácora de seguimientos
            $venta_id = intval($detallePedido['venta_id'] ?? 0);
            $historial = [];
            if ($venta_id > 0) {
                $ventasModel = new VentasModel();
                $historial = $ventasModel->selectHistorialSeguimientoVenta($venta_id);
            }

            $arrResponse = getResponse('Datos encontrados', 'ok', false);
            $arrResponse['data'] = array(
                'pedido'    => $detallePedido,
                'partidas'  => $partidas,
                'adjuntos'  => $adjuntos,
                'historial' => $historial
            );
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Código Error: ' . self::prefijo_msj_error . '_2001. Error Desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}




