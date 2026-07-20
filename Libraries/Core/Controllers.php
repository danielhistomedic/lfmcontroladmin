<?php

/**
 * Clase Core Controllers 
 */
class Controllers
{

    public $views;
    public $model;

    /**
     * Método Constructor de Core Controllers
     * * Inicializa la clase de la Vista correspondiente
     * * Inicializa la clase del Modelo correspondiente
     */
    public function __construct()
    {
        $this->views = new Views();
        $this->loadModel();
    }

    /**
     * Función para cargar las Clases de los Modelos y las Vistas del sistema.
     * * $routClass = "Models/" . $model . ".php";
     */
    public function loadModel()
    {
        $model = get_class($this) . "Model";
        $routClass = "Models/" . $model . ".php";
        if (file_exists($routClass)) {
            require_once($routClass);
            $this->model = new $model();
        }
    }
}
