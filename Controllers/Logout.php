<?php

/**
 * Controlador Logout 
 */
class Logout
{

    private $session;

    /**
     * Método Constructor de Controlador Roles.
     * Inicializa Controllers::__construct
     */
    public function __construct()
    {

        /*-------------------------------------------
        [ Validación de Sesion ]*/

        // $key = PREFIJO_SESSION . "email";
        // unset($_SESSION[$key]);

        $this->session = new Session();
        $this->session->close();
    }
}
