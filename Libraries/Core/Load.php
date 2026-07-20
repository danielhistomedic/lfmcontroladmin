<?php

//==================================================================
// [ Proceso de carga de controladores de sistema ]

$controller = ucwords($controller);
$controllerfile = "Controllers/" . $controller . ".php";

if (file_exists($controllerfile)) {
    require_once($controllerfile);
    $controller = new $controller();
    if (method_exists($controller, $method)) {
        $controller->{$method}($params);
    } else {
        require_once("Controllers/Errors.php");
    }
} else {
    require_once("Controllers/Errors.php");
}
