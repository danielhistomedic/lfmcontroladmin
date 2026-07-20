<?php

/**
 * Requiere una vez el archivo php del Modelo
 *  * Emplea la función spl_autoload_register
 *    para cargar la funcion autoload con la clase del modelo.
 */
function autoload($class)
{
    try {
        if (file_exists("Models/" . $class . ".php")) {
            require_once("Models/" . $class . ".php");
        }
    } catch (\Throwable $th) {
        getLoggerSystem()->error(getMensajeError($th));
    }
}

spl_autoload_register('autoload');
