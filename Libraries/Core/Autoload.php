<?php

/**
 * Requiere una vez el archivo php de las clases principales del Core
 *  * Emplea la función spl_autoload_register
 *    para cargar la funcion autoload_sys con las clases principales del Core.
 */
function autoload_sys($class)
{
    try {
        if (file_exists("Libraries/" . 'Core/' . $class . ".php")) {
            require_once("Libraries/" . 'Core/' . $class . ".php");
        }
    } catch (\Throwable $th) {
        getLoggerSystem()->error(getMensajeError($th));
    }
}

spl_autoload_register('autoload_sys');
