<?php

/**
 * Clase Core Views 
 */
class Views
{

    /**
     * Método para obtener la vista del controlador correspondiente.
     * 
     * @param mixed $controller.
     * Nombre del controlador que se llama en la ruta del navegador.
     * 
     * @param mixed $view.
     * Nombre del archivo de la vista correspondiente.
     * 
     * @param string $data.
     * Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    function getView($controller, $view, $data = "")
    {
        $controller = get_class($controller);
        $view = "Views/" . $controller . "/" . $view . ".php";
        // if ($controller == "Home") {
        //     $view = "Views/" . $view . ".php";
        // } else {
        //     $view = "Views/" . $controller . "/" . $view . ".php";
        // }
        require_once $view;
    }
}
