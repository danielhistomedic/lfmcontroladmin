<?php

require_once('vendor/autoload.php');

//==================================================================
// [ Zona Horaria ]
date_default_timezone_set('America/Mexico_City');

//==================================================================
// [ Entorno y Detección Automática ]
$_host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
// Normalizar: quitar puerto si lo hay (ej. localhost:8080)
$_hostClean = explode(':', $_host)[0];

$isLocalhost = in_array($_hostClean, ['localhost', '127.0.0.1']);
$isProduccion = !$isLocalhost && (strpos($_hostClean, 'lfmcontrol.com.mx') !== false || strpos($_hostClean, 'sistema.lfmcontrol.com.mx') !== false);

// Protocolo real del request
$_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
// URL base dinámica = protocolo + host exacto que usó el navegador
$_baseUrlDynamic = $_scheme . '://' . $_host;

if ($isLocalhost) {
    // -------------------------------------------------------
    // Entorno: DESARROLLO (Local)
    // -------------------------------------------------------
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    define('BASE_URL_SITIO', 'http://localhost/lfm_sitioweb');
    define('BASE_URL', 'http://localhost/lfm_admin');
    define('DB_HOST', 'lfmcontrol.com.mx');
} else {
    // -------------------------------------------------------
    // Entorno: PRODUCCIÓN
    // -------------------------------------------------------
    define('BASE_URL_SITIO', 'https://lfmcontrol.com.mx');
    define('BASE_URL', $_baseUrlDynamic); // URL base dinámica para evitar CORS
    define('DB_HOST', 'localhost');
}

define('DB_NAME', 'lfmcontr_sistema');
define('DB_PORT', '3306');
define('DB_USER', 'lfmcontr_admin');
define('DB_PASSWORD', 'fkSp_EkB6dX_');

define('URL_SITIO', 'https://sistema.lfmcontrol.com.mx');

const DB_CHARSET = "charset=utf8";

//==================================================================
// [ Delimitadores decimal y millar Ej. 24,1989.00 ]
const SPD = ".";
const SPM = ",";

//==================================================================
// [ LOGGER ]
const LOG_CHANNEL = "lfmcontrol";
const LOG_PATH = "Log";

//==================================================================
// [ Datos envio de correo ]
define('WEB_LOGIN', BASE_URL . "/login");
const EMAIL_EMPRESA = "ventas@lfmcontrol.com";

//==================================================================
// [ Otras Constantes ]
define('PREFIJO_SESSION', 'LFM_141836');

//==================================================================
// [ ssl ]
const KEY_TOKEN_USER = "&14/Gistro*18)==";
const KEY_TOKEN = "&14/Tistro*18)==";
const KEY_LOGIN = "&25/Nistro*46)==";
const KEY_LOGIN_REGISTRO = "&25/Tistro*46)==";
const KEY = "&25/Bistro*46)==";
const METHODENCRIPT = "AES-128-ECB";

//==================================================================
// [ STRIPE ]
