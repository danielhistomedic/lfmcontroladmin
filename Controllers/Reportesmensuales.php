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
}
