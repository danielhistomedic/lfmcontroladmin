<?php

/**
 * Vistas de reportes mensuales.
 */
class Reportesmensuales extends Controllers
{
    private $session;

    /** Acepta enlaces antiguos escalares y filtros Select2 multiples. */
    private static function seleccionNumerica($input, int $min, int $max): array|false
    {
        $values = is_array($input) ? $input : [$input];
        if (!$values || count($values) > $max - $min + 1) return false;
        $months = [];
        foreach ($values as $value) {
            if (!is_string($value) && !is_int($value)) return false;
            $month = filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>$min,'max_range'=>$max]]);
            if ($month === false) return false;
            $months[] = $month;
        }
        $months = array_values(array_unique($months));
        sort($months, SORT_NUMERIC);
        return $months;
    }

    public function __construct()
    {
        parent::__construct();
        $this->session = new Session;
    }

    /** Historial desde los listados de proyectos; conserva el contrato del modal compartido. */
    public function seguimientos()
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                header('Allow: POST');
                http_response_code(405);
                echo json_encode(['respuesta'=>'error', 'data'=>[]]);
                return;
            }
            if (!$this->session->getStatus()) {
                http_response_code(401);
                echo json_encode(['respuesta'=>'error', 'data'=>[]]);
                return;
            }
            $permisos = getPermisosGlobal();
            if (empty($permisos[MOD_REPORTES_MENSUALES_VENTAS]['r'])) {
                http_response_code(403);
                echo json_encode(['respuesta'=>'error', 'data'=>[]]);
                return;
            }
            $ventaId = filter_var($_POST['venta_id'] ?? null, FILTER_VALIDATE_INT,
                ['options'=>['min_range'=>1]]);
            if ($ventaId === false) {
                http_response_code(400);
                echo json_encode(['respuesta'=>'error', 'data'=>[]]);
                return;
            }
            $restricted = (int)$this->session->get('rol_id') === 4;
            $seller = $restricted ? (string)$this->session->get('ccveusuario') : '';
            $lista = $_POST['lista'] ?? 'declinados';
            if (!in_array($lista, ['declinados', 'interna_sin_cliente','estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo','estatus_periodo','cotizados_periodo'], true)) {
                http_response_code(400);
                echo json_encode(['respuesta'=>'error', 'data'=>[]]);
                return;
            }
            $authorized = !($restricted && $seller === '') && ($lista === 'interna_sin_cliente'
                ? $this->model->proyectoInternaSinClienteAutorizado($ventaId, $seller)
                : ($lista === 'declinados' ? $this->model->proyectoDeclinadoAutorizado($ventaId, $seller)
                    : $this->model->proyectoReporteAutorizado($ventaId, $seller)));
            if (!$authorized) {
                http_response_code(403);
                echo json_encode(['respuesta'=>'error', 'data'=>[]]);
                return;
            }
            if (!class_exists('VentasModel')) {
                require_once dirname(__DIR__) . '/Models/VentasModel.php';
            }
            $ventas = new VentasModel;
            echo json_encode(['respuesta'=>'ok', 'data'=>$ventas->selectSeguimientoVenta($ventaId)],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        } catch (\Throwable $ex) {
            getLoggerSystem()->error('No se pudo consultar el seguimiento del proyecto del reporte.');
            http_response_code(500);
            echo json_encode(['respuesta'=>'error', 'data'=>[]]);
        }
    }

    public function ventas()
    {
        try {
            if (!$this->session->getStatus()) {
                $this->session->redirect('inicio');
                return;
            }

            // Permiso independiente del módulo de reportes mensuales de ventas.
            $permisos = getPermisosGlobal();
            if (empty($permisos[MOD_REPORTES_MENSUALES_VENTAS]['r'])) {
                http_response_code(403);
                echo '<h4>Lo sentimos, Acceso restringido</h4>';
                return;
            }

            $data['permisos'] = $permisos;
            $data['permisosMod'] = $permisos[MOD_REPORTES_MENSUALES_VENTAS];
            $data['empresa_id'] = $this->session->get('empresa_id');
            $data['sucursal_id'] = $this->session->get('sucursal_id');
            $data['theme'] = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email'] = $this->session->get('email');
            $data['usuario']['rol'] = $this->session->get('rol');
            $data['usuario']['rol_id'] = $this->session->get('rol_id');

            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $data['configuracion'] = $configuracion_model->selectRecord($configuracion_model);

            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_REPORTES_MENSUALES_VENTAS);
            $data['menu'] = MOD_REPORTES_MENSUALES_VENTAS;
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] = $menu['tags'];
            $data['page_form_title'] = $menu['icon_form_title'] . $menu['form_title'];
            $data['page_breadcrumb'] = $menu['breadcrumb'];
            $data['page_card_title'] = $menu['card_title'];
            $data['page_card_description'] = $menu['descripcion'];
            $data['page_functions_js'] = 'reporte_ventas_mensuales.js';

            $year = self::seleccionNumerica($_GET['anio'] ?? (int)date('Y'), 2000, 2100);
            $month = self::seleccionNumerica($_GET['mes'] ?? (int)date('n'), 1, 12);
            $sellerInput = $_GET['vendedor'] ?? '';
            $data['reporte_error'] = '';
            $data['reporte'] = null;
            $data['vendedores'] = [];
            $data['filtros'] = ['anio' => $year ?: [(int)date('Y')], 'mes' => $month ?: [(int)date('n')], 'vendedor' => ''];
            try {
                if ($year === false || $month === false || !is_string($sellerInput) || strlen($sellerInput) > 100) {
                    throw new InvalidArgumentException('Los filtros del reporte no son válidos.');
                }
                // La clave de usuario y el rol se obtienen de la sesión, nunca del navegador.
                $scope = (int)$this->session->get('rol_id') === 4 ? (string)$this->session->get('ccveusuario') : '';
                if ((int)$this->session->get('rol_id') === 4 && $scope === '') {
                    throw new RuntimeException('No se pudo determinar el vendedor autorizado.');
                }
                $data['vendedores'] = $this->model->vendedores($scope);
                $allowed = array_map('strval', array_column($data['vendedores'], 'id'));
                if ($sellerInput !== '' && !in_array($sellerInput, $allowed, true)) {
                    throw new InvalidArgumentException('El vendedor seleccionado no está disponible.');
                }
                $data['filtros']['vendedor'] = $sellerInput;
                $data['reporte'] = $this->model->dashboard(count($year) === 1 ? $year[0] : $year, count($month) === 1 ? $month[0] : $month, $scope !== '' ? $scope : $sellerInput);
            } catch (InvalidArgumentException $ex) {
                http_response_code(400);
                $data['reporte_error'] = $ex->getMessage();
            } catch (UnexpectedValueException $ex) {
                $data['reporte_error'] = $ex->getMessage();
            } catch (\Throwable $ex) {
                getLoggerSystem()->error('No se pudo generar el dashboard mensual de ventas.');
                $data['reporte_error'] = 'No se pudo generar el reporte. Revise la conexión y la integridad de los proyectos e intente nuevamente.';
            }

            $this->views->getView($this, $menu['views'], $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /** GET anio[], mes[], vendedor, seccion y parametros DataTables para el resumen de pedidos. */
    public function colocadosfinanciero()
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
                header('Allow: GET'); http_response_code(405);
                echo json_encode(['status'=>false,'message'=>'Método no permitido.']); return;
            }
            if (!$this->session->getStatus()) {
                http_response_code(401); echo json_encode(['status'=>false,'message'=>'La sesión ha expirado.']); return;
            }
            $permissions = getPermisosGlobal();
            if (empty($permissions[MOD_REPORTES_MENSUALES_VENTAS]['r'])) {
                http_response_code(403); echo json_encode(['status'=>false,'message'=>'Acceso restringido.']); return;
            }
            $years = self::seleccionNumerica($_GET['anio'] ?? null,2000,2100);
            $months = self::seleccionNumerica($_GET['mes'] ?? null,1,12);
            $seller = $_GET['vendedor'] ?? '';
            $section = $_GET['seccion'] ?? 'resumen';
            if ($years === false || $months === false || !is_string($seller) || strlen($seller)>100
                || !in_array($section,['resumen','clientes','vendedores','detalle'],true)) {
                http_response_code(400); echo json_encode(['status'=>false,'message'=>'Los filtros del resumen no son válidos.']); return;
            }
            if ((int)$this->session->get('rol_id') === 4) {
                $scope = (string)$this->session->get('ccveusuario');
                if ($scope === '' || ($seller !== '' && $seller !== $scope)) {
                    http_response_code(403); echo json_encode(['status'=>false,'message'=>'Vendedor no autorizado.']); return;
                }
                $seller = $scope;
            }
            if ($section === 'detalle' && (count($years) !== 1 || count($months) !== 1)) {
                http_response_code(400); echo json_encode(['status'=>false,'message'=>'Seleccione un mes y un anio.']); return;
            }
            $options = [];
            if ($section !== 'resumen') {
                $integer = static fn($key,$default,$min,$max) => filter_var($_GET[$key] ?? $default,
                    FILTER_VALIDATE_INT,['options'=>['min_range'=>$min,'max_range'=>$max]]);
                $options = ['draw'=>$integer('draw',1,0,1000000000),'start'=>$integer('start',0,0,1000000),
                    'length'=>$integer('length',$section === 'detalle' ? 5 : 10,5,100),
                    'order_column'=>$integer('order_column',2,0,$section === 'detalle' ? 11 : 2),
                    'order_dir'=>$_GET['order_dir'] ?? ($section === 'detalle' ? 'asc' : 'desc'),'search'=>$_GET['search'] ?? ''];
                if (in_array(false,[$options['draw'],$options['start'],$options['length'],$options['order_column']],true)
                    || !in_array($options['order_dir'],['asc','desc'],true) || !is_string($options['search']) || strlen($options['search'])>200) {
                    http_response_code(400); echo json_encode(['status'=>false,'message'=>'Los filtros de la tabla no son válidos.']); return;
                }
            }
            if ($section === 'detalle' && (isset($_GET['periodo_anio']) || isset($_GET['periodo_mes']))) {
                $periodYears = self::seleccionNumerica($_GET['periodo_anio'] ?? null,2000,2100);
                $periodMonths = self::seleccionNumerica($_GET['periodo_mes'] ?? null,1,12);
                if ($periodYears === false || $periodMonths === false || !in_array($years[0],$periodYears,true)
                    || !in_array($months[0],$periodMonths,true)) {
                    http_response_code(400); echo json_encode(['status'=>false,'message'=>'El mes no pertenece al período del reporte.']); return;
                }
                $options['periodo_anios'] = $periodYears;
                $options['periodo_meses'] = $periodMonths;
            }
            $result = $this->model->colocadosFinanciero($years,$months,$seller,$section,$options);
            echo json_encode(['status'=>true,'data'=>$result],JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        } catch (\Throwable $ex) {
            getLoggerSystem()->error('No se pudo consultar el resumen financiero de pedidos colocados.');
            http_response_code(500); echo json_encode(['status'=>false,'message'=>'No se pudo cargar el resumen financiero. Intente nuevamente.']);
        }
    }

    /** GET anio, mes, vendedor, pagina; acceso de lectura del módulo de ventas mensuales. */
    public function declinados()
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
                header('Allow: GET');
                http_response_code(405);
                echo json_encode(['status'=>false, 'message'=>'Método no permitido.']);
                return;
            }
            if (!$this->session->getStatus()) {
                http_response_code(401);
                echo json_encode(['status'=>false, 'message'=>'La sesión ha expirado.']);
                return;
            }
            $permissions = getPermisosGlobal();
            if (empty($permissions[MOD_REPORTES_MENSUALES_VENTAS]['r'])) {
                http_response_code(403);
                echo json_encode(['status'=>false, 'message'=>'Acceso restringido.']);
                return;
            }
            $year = self::seleccionNumerica($_GET['anio'] ?? null, 2000, 2100);
            $month = self::seleccionNumerica($_GET['mes'] ?? null, 1, 12);
            $page = filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT,
                ['options'=>['min_range'=>1, 'max_range'=>1000000]]);
            $seller = $_GET['vendedor'] ?? '';
            if ($year === false || $month === false || $page === false || !is_string($seller) || strlen($seller)>100) {
                http_response_code(400);
                echo json_encode(['status'=>false, 'message'=>'Los filtros de la lista no son válidos.']);
                return;
            }
            if ((int)$this->session->get('rol_id') === 4) {
                $scope = (string)$this->session->get('ccveusuario');
                if ($scope === '' || ($seller !== '' && $seller !== $scope)) {
                    http_response_code(403);
                    echo json_encode(['status'=>false, 'message'=>'Vendedor no autorizado.']);
                    return;
                }
                $seller = $scope;
            }
            $lista = $_GET['lista'] ?? 'declinados';
            if (!in_array($lista, ['declinados', 'interna_sin_cliente', 'estatus_clasificacion', 'vendedor_clasificacion', 'clasificacion_periodo', 'estatus_periodo','cotizados_periodo'], true)
                || ($lista !== 'declinados' && ($_GET['datatable'] ?? '') !== '1')) {
                http_response_code(400);
                echo json_encode(['status'=>false, 'message'=>'La lista solicitada no es válida.']);
                return;
            }
            if (($_GET['datatable'] ?? '') === '1') {
                $integer = static fn($key,$default,$min,$max) => filter_var($_GET[$key] ?? $default,
                    FILTER_VALIDATE_INT, ['options'=>['min_range'=>$min,'max_range'=>$max]]);
                $options = ['draw'=>$integer('draw',1,0,1000000000), 'start'=>$integer('start',0,0,1000000),
                    'length'=>$integer('length',10,5,100), 'order_column'=>$integer('order_column',2,0,7),
                    'order_dir'=>$_GET['order_dir'] ?? 'desc', 'search'=>$_GET['search'] ?? '', 'filters'=>[]];
                if (in_array($lista, ['estatus_clasificacion','estatus_periodo'], true)) {
                    $options['clasificacion_id'] = $lista === 'estatus_periodo' ? null : $integer('clasificacion_id', 0, 1, 2147483647);
                    $options['estatus_id'] = $lista === 'estatus_periodo' && in_array($_GET['estatus_id'] ?? '', ['colocados','proceso_cotizacion'], true)
                        ? $_GET['estatus_id'] : (($_GET['estatus_id'] ?? '') === 'sin_estatus'
                        ? null : $integer('estatus_id', 0, 1, 2147483647));
                    $options['segmento'] = $_GET['segmento'] ?? '';
                    if ($options['clasificacion_id'] === false || $options['estatus_id'] === false
                        || !in_array($options['segmento'], ['declinados','no_declinados'], true)) {
                        http_response_code(400);
                        echo json_encode(['status'=>false,'message'=>'El desglose solicitado no es válido.']);
                        return;
                    }
                }
                if (in_array($lista,['vendedor_clasificacion','clasificacion_periodo'],true)) {
                    $options['clasificacion_id'] = $integer('clasificacion_id',0,1,2147483647);
                    $options['vendedor_id'] = $_GET['vendedor_id'] ?? null;
                    $options['segmento'] = $_GET['segmento'] ?? '';
                    if ($options['clasificacion_id'] === false || ($lista === 'vendedor_clasificacion' && (!is_string($options['vendedor_id'])
                        || strlen($options['vendedor_id']) > 100))
                        || !in_array($options['segmento'],['declinados','no_declinados'],true)) {
                        http_response_code(400);
                        echo json_encode(['status'=>false,'message'=>'El desglose solicitado no es válido.']);
                        return;
                    }
                }
                $valid = !in_array(false, [$options['draw'],$options['start'],$options['length'],$options['order_column']],true)
                    && in_array($options['order_dir'],['asc','desc'],true)
                    && is_string($options['search']) && strlen($options['search'])<=200;
                if ($lista === 'cotizados_periodo') {
                    $quoteYears = self::seleccionNumerica($_GET['periodo_anio'] ?? $year,2000,2100);
                    $quoteMonths = self::seleccionNumerica($_GET['periodo_mes'] ?? $month,1,12);
                    if ($quoteYears === false || $quoteMonths === false || array_diff($year,$quoteYears)
                        || array_diff($month,$quoteMonths)) $valid = false;
                    else { $options['periodo_anios'] = $quoteYears; $options['periodo_meses'] = $quoteMonths; }
                }
                for ($index=1; $index<=7; $index++) {
                    $value = $_GET['f'.$index] ?? '';
                    if (!is_string($value) || strlen($value)>200) $valid = false;
                    $options['filters'][$index] = $value;
                }
                if (!$valid) {
                    http_response_code(400);
                    echo json_encode(['status'=>false,'message'=>'Los filtros de la tabla no son válidos.']);
                    return;
                }
                $result = $this->model->declinadosTabla(count($year) === 1 ? $year[0] : $year, count($month) === 1 ? $month[0] : $month,$seller,$options,$lista);
            } else {
                $result = $this->model->declinados(count($year) === 1 ? $year[0] : $year, count($month) === 1 ? $month[0] : $month, $seller, $page);
            }
            echo json_encode(['status'=>true, 'data'=>$result], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        } catch (\Throwable $ex) {
            getLoggerSystem()->error('No se pudo consultar la lista de proyectos declinados.');
            http_response_code(500);
            echo json_encode(['status'=>false, 'message'=>'No se pudo cargar la lista. Intente nuevamente.']);
        }
    }
}
