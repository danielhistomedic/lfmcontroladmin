<?php

/**
 * Controlador UsuarioSesion 
 */
class UsuarioSesion extends Controllers
{

    private $session;

    /**
     * Método Constructor de Controlador UsuarioSesion.
     * Inicializa Controllers::__construct.
     * Inicializa y valida datos de session.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Verifica el estatus de la sesión
     */
    public function getEstatusSesion()
    {
        /*-------------------------------------------
        [ Validación de Sesion ]*/
        $this->session = new Session();
        if (!$this->session->getStatus()) {
            $arrResponse = getResponse('Sesión terminada', 'error', false);
            $arrResponse['data']['respuesta'] = 'Cerrar Sesion';
        } else {
            $arrResponse = getResponse('Sesión activa', 'ok', false);
            $arrResponse['data']['respuesta'] = 'Sesion Activa';
        }

        /*-------------------------------------------
            [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
