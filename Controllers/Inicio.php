<?php

/**
 * Controlador Inicio 
 */
class Inicio extends Controllers
{

    private $session;


    /**
     * Método Constructor de Controlador Inicio.
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
            $this->session->redirect('login');
        }
    }

    /**
     * Carga la Vista Inicio. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function Inicio()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('login');
                return;
            }

            //Datos de Usuario en Sesión.
            $data['empresa_id'] = $this->session->get('empresa_id');
            $data['sucursal_id'] = $this->session->get('sucursal_id');
            $data['theme'] = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email'] = $this->session->get('email');
            $data['usuario']['rol'] = $this->session->get('rol');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            // Asigna los permisos de Módulo y Menu de Sistema
            $data['permisos'] = $arrPermisos;

            //Id de Menu para script de Pemisos de Boton exportar a Excel
            $data['menu'] = 0;

            //Header
            $data['page_title'] = 'LFM CONTROL';
            $data['meta_description'] = 'Panel de Administración LFM CONTROL';
            $data['meta_keywords'] = "panel, administracion";

            //Form Principal
            $data['page_form_title'] = "Inicio";

            //Breadcrump
            $data['page_breadcrumb'] = "Panel de Administración";


            //Card Principal
            $data['page_card_title'] = 'Inicio';
            $data['page_card_description'] = "<i class='fa-regular fa-circle-info me-1'></i> Inicio";

            //JS Principal
            $data['page_functions_js'] = "inicio.js";

            /*-------------------------------------------
            [ Ejecuta el método para generar la vista en el navegador ]*/
            $this->views->getView($this, "inicio", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }
}
