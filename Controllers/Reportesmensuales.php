<?php

/**
 * Vistas de reportes mensuales.
 */
class Reportesmensuales extends Controllers
{
    private $session;

    public function __construct()
    {
        parent::__construct();
        $this->session = new Session;
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

            $year = filter_var($_GET['anio'] ?? date('Y'), FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 2000, 'max_range' => 2100]]);
            $month = filter_var($_GET['mes'] ?? date('n'), FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 12]]);
            $sellerInput = $_GET['vendedor'] ?? '';
            $data['reporte_error'] = '';
            $data['reporte'] = null;
            $data['vendedores'] = [];
            $data['filtros'] = ['anio' => $year ?: (int)date('Y'), 'mes' => $month ?: (int)date('n'), 'vendedor' => ''];
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
                $data['reporte'] = $this->model->dashboard($year, $month, $scope !== '' ? $scope : $sellerInput);
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
            $year = filter_var($_GET['anio'] ?? null, FILTER_VALIDATE_INT,
                ['options'=>['min_range'=>2000, 'max_range'=>2100]]);
            $month = filter_var($_GET['mes'] ?? null, FILTER_VALIDATE_INT,
                ['options'=>['min_range'=>1, 'max_range'=>12]]);
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
            $result = $this->model->declinados($year, $month, $seller, $page);
            echo json_encode(['status'=>true, 'data'=>$result], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        } catch (\Throwable $ex) {
            getLoggerSystem()->error('No se pudo consultar la lista de proyectos declinados.');
            http_response_code(500);
            echo json_encode(['status'=>false, 'message'=>'No se pudo cargar la lista. Intente nuevamente.']);
        }
    }
}
