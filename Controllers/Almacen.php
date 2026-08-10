<?php

/**
 * Controlador Almacén
 * Módulo: Productos e Inventario de Almacén
 */
class Almacen extends Controllers
{

    private $session;
    private $permisosMod;

    const prefijo_msj_error = "almacen";

    /**
     * Método Constructor de Controlador Almacen.
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
     * Carga la Vista Productos. 
     * URL: /almacen/productos
     */
    public function productos()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_ALMACEN_PRODUCTOS] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];

            // Valida si tiene acceso a la pagina.
            if (empty($this->permisosMod['r'])) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_ALMACEN_PRODUCTOS);

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

            //Id de Menu para script de Permisos
            $data['menu'] = MOD_ALMACEN_PRODUCTOS;

            //Header
            $data['page_title']        = !empty($menu['name'])        ? $menu['name']        : 'Productos y Existencias';
            $data['meta_description']  = !empty($menu['descripcion']) ? $menu['descripcion'] : 'Consultar los productos y existencias de almacén';
            $data['meta_keywords']     = !empty($menu['tags'])        ? $menu['tags']        : 'almacen, productos, existencias, stock';

            //Form Principal
            $data['icon_form_title']  = !empty($menu['icon_form_title']) ? $menu['icon_form_title'] : '<i class="fa-sharp fa-light fa-boxes-stacked text-primary me-2"></i>';
            $data['page_form_title']  = $data['icon_form_title'] . (!empty($menu['form_title']) ? $menu['form_title'] : ' Productos y Existencias');

            //Breadcrumb
            $data['page_breadcrumb']       = 'Almacén / Productos';
            $data['page_card_title']       = !empty($menu['card_title']) ? $menu['card_title'] : 'Productos y Existencias de Almacén';
            $data['page_card_description'] = $data['meta_description'];

            //Call Vista
            $this->views->getView($this, "productos", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
        }
    }

    /**
     * Carga la Vista Inventario. 
     * URL: /almacen/inventario
     */
    public function inventario()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_ALMACEN_INVENTARIO] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];

            // Valida si tiene acceso a la pagina.
            if (empty($this->permisosMod['r'])) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_ALMACEN_INVENTARIO);

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

            //Id de Menu para script de Permisos
            $data['menu'] = MOD_ALMACEN_INVENTARIO;

            //Header
            $data['page_title']        = !empty($menu['name'])        ? $menu['name']        : 'Inventario';
            $data['meta_description']  = !empty($menu['descripcion']) ? $menu['descripcion'] : 'Consultar el inventario de almacenes y subalmacenes de la empresa';
            $data['meta_keywords']     = !empty($menu['tags'])        ? $menu['tags']        : 'almacen, inventario, subalmacenes, stock';

            //Form Principal
            $data['icon_form_title']  = !empty($menu['icon_form_title']) ? $menu['icon_form_title'] : '<i class="fa-sharp fa-light fa-warehouse text-primary me-2"></i>';
            $data['page_form_title']  = $data['icon_form_title'] . (!empty($menu['form_title']) ? $menu['form_title'] : ' Inventario');

            //Breadcrumb
            $data['page_breadcrumb']       = 'Almacén / Inventario';
            $data['page_card_title']       = !empty($menu['card_title']) ? $menu['card_title'] : 'Inventario de Almacenes y Subalmacenes';
            $data['page_card_description'] = $data['meta_description'];

            //Call Vista
            $this->views->getView($this, "inventario", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
        }
    }
}
