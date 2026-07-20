<?php

/**
 * Controlador Servicios 
 */
class Servicios extends Controllers
{

    private $session;
    private $permisosMod;

    /**
     * Método Constructor de Controlador Servicios.
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
     * Obtiene la lista de registros, para crear el html con los options que llenaran el Select correspondiente.
     * 
     * @return string $htmlOptions
     * 
     */
    public function getSelectRecords()
    {

        try {

            $htmlOptions = '';

            // /*-------------------------------------------
            // [ Validación de Permisos ]*/
            // $arrPermisos = getPermisosGlobal();
            // $this->permisosMod = $arrPermisos[MOD_SERVICIOS];
            // if (!$this->permisosMod['r']) {
            //     die($htmlOptions);
            // }

            $htmlOptions .= '<option value="" selected="selected" disabled>Seleccione una opcion</option>';
            $rol_model = new ServiciosModel;
            $arrData = $rol_model->selectRecords();
            if (count($arrData) > 0) {
                for ($i = 0; $i < count($arrData); $i++) {
                    if ($arrData[$i]['activo'] == 1) {
                        $htmlOptions .= '<option value="' . $arrData[$i]['id'] . '">' . $arrData[$i]['name'] . '</option>';
                    }
                }
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta string html ]*/
        die($htmlOptions);
    }
}
