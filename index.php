<?php

//******************************** */
// [ Versión del Sistema ]
const VERSION_SYS = "1.0.36";
const DIR = __DIR__;

//******************************** */
// [ Desplegar Errores PHP en el navegador ]

// Desactivar toda las notificaciónes del PHP
error_reporting(0);


//******************************** */
// [ Se cargan las constantes y otras funciones del proyecto. ]
require_once('Config/Config.php');
require_once('Config/Modulos.php');
require_once('Helpers/Helpers.php');
require_once('Helpers/LogErrors.php');
include_once 'Libraries/Core/Session.php';


//******************************** */
// [ Se obtienen las variables de las url desde el .htacces ]

$url = !empty($_GET['url']) ? $_GET['url'] : 'login';

$arrUrl = explode("/", $url);
$controller = $arrUrl[0];
$method = $arrUrl[0];
$params = "";

if (!empty($arrUrl[1])) {
    if ($arrUrl[1] != "") {
        $method = $arrUrl[1];
    }
}

if (!empty($arrUrl[2])) {
    if ($arrUrl[2] != "") {
        for ($i = 2; $i < count($arrUrl); $i++) {
            $params .= $arrUrl[$i] . ',';
        }
        $params = trim($params, ',');
    }
}


//******************************** */
// [ Se registran las clases obtenidas desde la url. ]
require_once('Libraries/Core/Autoload.php');
require_once('Libraries/Core/AutoloadModel.php');


//******************************** */
// [ Se hace la carga inicial de la página obtenida de los controladores. ]
require_once('Libraries/Core/Load.php');
