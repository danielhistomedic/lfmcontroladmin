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
            $data['page_functions_js'] = $menu['js'];

            $this->views->getView($this, $menu['views'], $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }
}
