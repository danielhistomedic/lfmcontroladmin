<?php

/**
 * Controlador Menus 
 */
class Menus extends Controllers
{

    /**
     * Método Constructor de Controlador Menus.
     * Inicializa Controllers::__construct.
     * Inicializa y valida datos de session.
     */
    public function __construct()
    {
        parent::__construct();

        // /*-------------------------------------------
        // [ Inicializa la variable de sesión. ]*/
        // session_start();

        // /*-------------------------------------------
        // [ Valida datos de sesión. ]*/
        // if (empty($_SESSION[PREFIJO_SESSION . 'init'])) {

        //     if (empty($_SESSION[PREFIJO_SESSION . 'login'])) {

        //         /*-------------------------------------------
        //         [ Redireccionar a Login  ]*/
        //         header('Location: ' . base_url() . '/login');
        //     }
        // }
    }


    /**
     * Obtiene la lista de menus para llenar el autocomplete.
     * 
     * @param string $filtro
     * Texto recibido para filtrar la información en el query
     * 
     * @response $arrResponse, donde:
     * Array con la lista requerida para llenar el autocomplete.
     * 
     * @return json json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     */
    public function getMenus(string $filtro)
    {

        try {

            if ($filtro == "") {
                die();
            }

            /*-------------------------------------------
            [ Asignar y Limpiar parametros recibidos ]*/
            $filtro = strClean($filtro);

            /*-------------------------------------------
            [ Obtiene array con los datos de Residente ]*/
            $menus_model = new MenusModel;
            $arrData = $menus_model->selectMenus($filtro);
            $arrResponse = array();

            for ($i = 0; $i < count($arrData); $i++) {
                $arrTemp = array();
                $arrTemp['value'] =   $arrData[$i]['name'];
                // <i class="fa-regular fa-magnifying-glass-arrow-right"></i>
                $arrTemp['label'] = '<div class="w-100" style="padding: 10px;">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-0 fw-600 fs-13">
                                                <a href="' . base_url() . $arrData[$i]['url'] . '">
                                                    <i class="' . $arrData[$i]['icon'] . ' pe-2"></i> <span class="fw-semibold text-main">' . $arrData[$i]['name'] . '</span> 
                                                </a>
                                            </h5>
                                        </div>
                                        <span class="small-text" style="padding-left: 24px;">
                                            <small class="fs-11">' . $arrData[$i]['descripcion'] . '</small>
                                        <span>
                                    </div>';
                $arrTemp['id'] =  $arrData[$i]['id'];
                $arrTemp['url'] =  base_url() . $arrData[$i]['url'];
                $arrResponse[] = $arrTemp;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
