<?php

use Lib\phpMailer\PHPMailer;
use Lib\phpMailer\SMTP;

//==================================================================
// [ Versión del Sistema ]

/**
 * Función que devuelve el valor de la Versión Actual del sistema.
 * 
 * @return string VERSION_SYS
 * 
 */
function version()
{
    return VERSION_SYS;
}

//==================================================================
// [ Url´s ]

/**
 * Función que devuelve la Url base del proyecto.cliente_id
 * 
 * @return string BASE_URL
 * 
 */
function base_url()
{
    return BASE_URL;
}

/**
 * Función que devuelve la Url base de archivos css, js y pluggins del proyecto.
 * 
 * @return string BASE_URL . "/Assets"
 * 
 */
function base_url_assets()
{
    return BASE_URL . "/Assets";
}

/**
 * Función que devuelve la Url base de archivos css, js y pluggins del proyecto.
 * 
 * @return string BASE_URL . "/Assets"
 * 
 */
function assets()
{
    return BASE_URL . "/Assets";
}

/**
 * Función que devuelve la Url base del proyecto.cliente_id
 * 
 * @return string BASE_URL
 * 
 */
function base_url_sitio()
{
    return BASE_URL_SITIO;
}

//==================================================================
// [ Controllers Array Repsonse ]

/**
 * Funcion para obtener el arrayRepsonse de Controllers
 * Default, Error.
 * 
 */
function getResponse($mensaje, $tipo_respuesta = "error", $mostrar_mensaje = true, $tiempo = 3000, $titulo = ''): array
{

    if ($tipo_respuesta == "error") {
        getLoggerSystem()->warning($mensaje);
    }

    $arrResponse = array(
        'respuesta' => $tipo_respuesta,
        'mostrar_mensaje' => $mostrar_mensaje,
        'tiempo' => $tiempo,
        'mensaje' => $mensaje,
        'titulo' => $titulo,
    );

    /*-------------------------------------------
    [ Retorna respuesta ]*/
    return $arrResponse;
}

//==================================================================
// [Print_r ]

/**
 * Función para mostrar en pantalla un arreglo con formato amigable a la lectura
 * 
 * @param array $data array que se le dará formato.
 * 
 * @return string $format Cadena formateada.
 * 
 */
function dep($data)
{
    $format = print_r('<pre>');
    $format .= print_r($data);
    $format .= print_r('</pre>');
    return $format;
}

//==================================================================
// [ Modals ]

/**
 * Funcion para cargar con require_once el template del Modal de Formularios.
 * 
 * @param string $nameModal
 * Nombre del archivo php que contiene el template que se desea cargar
 * 
 * @param array $data
 * Array con valores personalizados que se pueden enviar al cargar el template
 * 
 */
function getModal(string $nameModal, $data)
{
    $view_modal = "Template/Modals/{$nameModal}.php";
    require_once $view_modal;
}

//==================================================================
// [ Seguridad ]


/**
 * Funcion para limpiar de inyección de codigo un valor de entrada.
 * 
 */
function strClean($strCadena)
{
    $string = preg_replace(['/\s+/', '/^\s|\s$/'], [' ', ''], $strCadena);
    $string = trim($string); //Elimina espacios en blanco al inicio y al final
    $string = stripslashes($string); // Elimina las \ invertidas
    $string = str_ireplace("<script>", "", $string);
    $string = str_ireplace("</script>", "", $string);
    $string = str_ireplace("<script src>", "", $string);
    $string = str_ireplace("<script type=>", "", $string);
    $string = str_ireplace("SELECT * FROM", "", $string);
    $string = str_ireplace("DELETE FROM", "", $string);
    $string = str_ireplace("INSERT INTO", "", $string);
    $string = str_ireplace("SELECT COUNT(*) FROM", "", $string);
    $string = str_ireplace("DROP TABLE", "", $string);
    $string = str_ireplace("OR '1'='1", "", $string);
    $string = str_ireplace('OR "1"="1"', "", $string);
    $string = str_ireplace('OR ´1´=´1´', "", $string);
    $string = str_ireplace("is NULL; --", "", $string);
    $string = str_ireplace("is NULL; --", "", $string);
    $string = str_ireplace("LIKE '", "", $string);
    $string = str_ireplace('LIKE "', "", $string);
    $string = str_ireplace("LIKE ´", "", $string);
    $string = str_ireplace("OR 'a'='a", "", $string);
    $string = str_ireplace('OR "a"="a', "", $string);
    $string = str_ireplace("OR ´a´=´a", "", $string);
    $string = str_ireplace("OR ´a´=´a", "", $string);
    $string = str_ireplace("--", "", $string);
    $string = str_ireplace("^", "", $string);
    $string = str_ireplace("[", "", $string);
    $string = str_ireplace("]", "", $string);
    $string = str_ireplace("==", "", $string);
    return $string;
}

function clear_cadena(string $cadena)
{
    //Reemplazamos la A y a
    $cadena = str_replace(
        array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
        array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
        $cadena
    );

    //Reemplazamos la E y e
    $cadena = str_replace(
        array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
        array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
        $cadena
    );

    //Reemplazamos la I y i
    $cadena = str_replace(
        array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
        array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
        $cadena
    );

    //Reemplazamos la O y o
    $cadena = str_replace(
        array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
        array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
        $cadena
    );

    //Reemplazamos la U y u
    $cadena = str_replace(
        array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
        array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
        $cadena
    );

    //Reemplazamos la N, n, C y c
    $cadena = str_replace(
        array('Ñ', 'ñ', 'Ç', 'ç', ',', '.', ';', ':'),
        array('N', 'n', 'C', 'c', '', '', '', ''),
        $cadena
    );
    return $cadena;
}

/**
 * Funcion para generar un password aleatorio.
 * Genera una contraseña de 10 caracteres
 * 
 */
function passGenerator($lenght = 7)
{
    $pass = "";
    $longitudPass = $lenght;
    $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
    $longitudCadena = strlen($cadena);
    for ($i = 0; $i < $longitudPass; $i++) {
        $pos = rand(0, $longitudCadena - 1);
        $pass .= substr($cadena, $pos, 1);
    }
    return $pass;
}

/**
 *  Genera un token. Funcion para generar un token para el reset de Password.
 * 
 */
function token()
{
    $r1 = bin2hex(random_bytes(10));
    $r2 = bin2hex(random_bytes(10));
    $r3 = bin2hex(random_bytes(10));
    $r4 = bin2hex(random_bytes(10));

    $token = $r1 . '-' . $r2 . '-' . $r3 . '-' . $r4;

    return $token;
}

const codeset = "25463211";

/**
 * Funcion para hacer un encode a un texto
 * 
 */
function encode($n)
{
    $converted = substr(md5(codeset . $n), 2, 8);
    return $converted;
}



//==================================================================
// [ Funciones de Moneda ]

/**
 * Funcion para dar formato a un valor. 
 * Formato de moneda.
 * 
 */
function formatMoney($cantidad)
{
    $cantidad =  number_format($cantidad, 2, SPD, SPM);
    return '$ ' . $cantidad;
}


//==================================================================
// [ Formatos de Fechas ]


/**
 * Dar formato a fecha-actual.
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'Y-m-d'
 * 
 */
function getFechaActual_Ymd(): string
{
    $today = getdate();
    $today_str = $today['year'] . '-' . str_pad($today['mon'], 2, "0", STR_PAD_LEFT) . '-' . $today['mday'];
    return $today_str;
}

/**
 * Obtener Fcha actual en formato d/m/Y.
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'd/m/Y'
 * 
 */
function getFechaActual_dmY(): string
{
    $today = getdate();
    $today_str = $today['mday'] . '-' . str_pad($today['mon'], 2, "0", STR_PAD_LEFT) . '-' . $today['year'];
    return $today_str;
}

/**
 * Dar formato a fecha.
 * 
 * @param string $fecha
 * Fecha en fromato 'Y-m-d'
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'd/m/Y'
 * 
 */
function formatDate($fecha)
{
    $formato = 'Y-m-d';
    $fecha_format = \DateTime::createFromFormat($formato, $fecha);
    $fecha_formatted = $fecha_format->format('d/m/Y');
    return $fecha_formatted;
}

/**
 * Dar formato a fecha 
 * 
 * @param string $fecha
 * Fecha en fromato 'd/m/Y'
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'Y-m-d'
 * 
 */
function formatDate_DataBase($fecha)
{
    $formato = 'd/m/Y';
    $fecha_format = \DateTime::createFromFormat($formato, $fecha);
    $fecha_formatted = $fecha_format->format('Y-m-d');
    return $fecha_formatted;
}

/**
 * Dar formato a fecha.
 * 
 * @param string $fecha
 * Fecha en fromato 'Y-m-d H:i:s'
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'd/m/Y H:i'
 * 
 */
function formatDateHms($fecha)
{

    $formato = 'Y-m-d H:i:s';
    $fecha_format = \DateTime::createFromFormat($formato, $fecha);
    $fecha_formatted = $fecha_format->format('d/m/Y H:i');
    return $fecha_formatted;
}

/**
 * Dar formato a fecha hora.
 * 
 * @param string $fecha
 * Fecha en fromato 'Y-m-d H:i:s'
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'd/m/Y H:i'
 * 
 */
function formatDateTime($fecha)
{
    $formato = 'Y-m-d H:i:s';
    $fecha_format = \DateTime::createFromFormat($formato, $fecha);
    $fecha_formatted = $fecha_format->format('d/m/Y H:i');
    return $fecha_formatted;
}

/**
 * Dar formato a hora.
 * 
 * @param string $fecha
 * Fecha en fromato 'Y-m-d H:i:s'
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'H:i'
 * 
 */
function formatTime($fecha)
{
    $formato = 'Y-m-d H:i:s';
    $fecha_format = \DateTime::createFromFormat($formato, $fecha);
    $fecha_formatted = $fecha_format->format('H:i');
    return $fecha_formatted;
}

/**
 * Dar formato a fecha-día.
 * 
 * @param string $fecha
 * Fecha en fromato 'Y-m-d H:i:s'
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'd'
 * 
 */
function formatDate_Dia($fecha)
{
    $formato = 'Y-m-d H:i:s';
    $fecha_format = \DateTime::createFromFormat($formato, $fecha);
    $fecha_formatted = $fecha_format->format('d');
    return $fecha_formatted;
}

/**
 * Dar formato a fecha-mes.
 * 
 * @param string $fecha
 * Fecha en fromato 'Y-m-d H:i:s'
 * 
 * @return string $fecha_formatted
 * Fecha en Formato '%B'
 * 
 */
function formatDate_Mes($fecha)
{
    setlocale(LC_TIME, "spanish");
    $mi_fecha = $fecha;
    $nueva_Fecha = date("Y-m-d H:i:s", strtotime($mi_fecha));
    $mes = strftime("%B", strtotime($nueva_Fecha));
    return strtoupper($mes);
}

/**
 * Obtener nombre del mes.
 * 
 * @param int $mes
 * Mes en valor numerico
 * 
 * @return string $fecha_formatted
 * Fecha en Formato '%B'
 * 
 */
function format_Mes($mes): string
{

    $mes_nombre = '';

    switch ($mes) {

        case 1:
            $mes_nombre = 'Enero';
            break;

        case 2:
            $mes_nombre = 'Febrero';
            break;

        case 3:
            $mes_nombre = 'Marzo';
            break;

        case 4:
            $mes_nombre = 'Abril';
            break;

        case 5:
            $mes_nombre = 'Mayo';
            break;

        case 6:
            $mes_nombre = 'Junio';
            break;

        case 7:
            $mes_nombre = 'Julio';
            break;

        case 8:
            $mes_nombre = 'Agosto';
            break;

        case 9:
            $mes_nombre = 'Septiembre';
            break;

        case 10:
            $mes_nombre = 'Octubre';
            break;

        case 11:
            $mes_nombre = 'Noviembre';
            break;

        case 12:
            $mes_nombre = 'Diciembre';
            break;
    }

    $mes_nombre = strtoupper($mes_nombre);
    return $mes_nombre;
}

/**
 * Dar formato a fecha para DataBase.
 * 
 * @param string $fecha
 * Fecha en formato 'd/m/Y'
 * 
 * @return mixvalue 
 * $fecha_formatted Fecha en Formato 'Y-m-d'
 * or false en caso de falla.
 * 
 */
function formatDate_DB($fecha)
{

    try {
        if ($fecha == '') {
            return false;
        }
        $formato = 'd/m/Y';
        $fecha_format = \DateTime::createFromFormat($formato, $fecha);
        $fecha_formatted = $fecha_format->format('Y-m-d');
        return $fecha_formatted;
    } catch (\Throwable $th) {
        return false;
    }
}

/**
 * Dar formato a fecha para DataBase.
 * 
 * @param string $fecha
 * Fecha en formato 'd/m/Y'
 * 
 * @return mixvalue 
 * $fecha_formatted Fecha en Formato 'Y-m-d'
 * or false en caso de falla.
 * 
 */
function formatDateTime_DB_Int($fecha)
{

    try {
        $formato = 'm/d/Y H:i';
        $fecha_format = \DateTime::createFromFormat($formato, $fecha);
        $fecha_formatted = $fecha_format->format('Y-m-d');
        return $fecha_formatted;
    } catch (\Throwable $th) {
        return false;
    }
}

/**
 * Dar formato a fecha para DataBase.
 * 
 * @param string $fecha
 * Fecha en formato 'd/m/Y H:i:s'
 * 
 * @return mixvalue 
 * $fecha_formatted Fecha en Formato 'Y-m-d H:i:s'
 * or false en caso de falla.
 * 
 */
function formatDateTime_DB($fecha)
{

    try {
        $formato = 'm/d/Y H:i';
        $fecha_format = \DateTime::createFromFormat($formato, $fecha);
        $fecha_formatted = $fecha_format->format('Y-m-d H:i:s');
        return $fecha_formatted;
    } catch (\Throwable $th) {
        return false;
    }
}


/**
 * Dar formato a fecha-año.
 * 
 * @param string $fecha
 * Fecha en fromato 'Y-m-d H:i:s'
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'Y'
 * 
 */
function formatDate_Anio($fecha)
{

    $formato = 'Y-m-d H:i:s';
    $fecha_format = \DateTime::createFromFormat($formato, $fecha);
    $fecha_formatted = $fecha_format->format('Y');
    return $fecha_formatted;
}

function obtenerEdadAños($fecha_nacimiento)
{
    $nacimiento = new DateTime($fecha_nacimiento);
    $ahora = new DateTime(date("Y-m-d"));
    $diferencia = $ahora->diff($nacimiento);
    return $diferencia->format("%y");
}

/**
 * Ceros a la izquierda.
 * 
 * @return string $fecha_formatted
 * Fecha en Formato 'Y-m-d'
 * 
 */
function formatCerosIzquierda($value, $cantidad): string
{
    $valor = str_pad($value, $cantidad, "0", STR_PAD_LEFT);
    return $valor;
}

/**
 * Dar formato a fecha para DataBase.
 * 
 * @param string $fecha
 * Fecha en formato 'm/d/Y'
 * 
 * @return mixvalue 
 * $fecha_formatted Fecha en Formato 'Y-m-d'
 * or false en caso de falla.
 * 
 */
function formatDate_DB_Int($fecha)
{

    try {
        $formato = 'm/d/Y';
        $fecha_format = \DateTime::createFromFormat($formato, $fecha);
        $fecha_formatted = $fecha_format->format('Y-m-d');
        return $fecha_formatted;
    } catch (\Throwable $th) {
        return false;
    }
}


//==================================================================
// [ Log de Errores ]

/**
 * Funcion para obtener los datos completos de un mensaje de error:
 * * getMessage
 * * getFile
 * * getLine
 * * getCode
 * 
 */
function getMensajeError($th): string
{

    $error_mensaje = "mensaje: " . $th->getMessage();
    $error_mensaje .= ", archivo: " . $th->getFile();
    $error_mensaje .= ", linea: " . $th->getLine();
    $error_mensaje .= ", codigo: " . $th->getCode();

    return $error_mensaje;
}


//==================================================================
// [ Funciones Varias ]

function getFile(string $url, $data)
{
    ob_start();
    require_once("Template/Pdf/{$url}.php");
    $file = ob_get_clean();
    return $file;
}


//==================================================================
// [ Mails ]

function sendEmailPHPMailer($data, $template): bool
{

    try {

        $response = false;

        /*-------------------------------------------
        [ Varobales Generales ]*/
        $asunto = $data['asunto'];
        $emailDestino = $data['email_destino'];

        /*-------------------------------------------
        [ Cargar Template de Email ]*/
        ob_start();
        require("Template/Email/" . $template . ".php");
        $message = ob_get_clean();

        /*-------------------------------------------
        [ Instanciar la Clase PHPMailer ]*/
        $mail = new PHPMailer();

        /*-------------------------------------------
        [ Se obtienen los datos de configuración de smtp ]*/
        $configuracion_model = new ConfiguracionModel;
        $configuracion_model->setEmpresaId($data['empresa_id']);
        $configuracion = $configuracion_model->selectRecord($configuracion_model);
        $empresa =  $configuracion['nombre_remitente'];
        $remitente = $configuracion['email_remitente'];

        /*-------------------------------------------
        [ Agregar Parámteros de Configuración SMTP a la Clase PHPMailer ]*/
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                   //Enable verbose debug output
        $mail->isSMTP();                                      //Send using SMTP
        $mail->Host = $configuracion['smtp_host'];            //Set the SMTP server to send through
        $mail->SMTPAuth = true;                               //Enable SMTP authentication
        $mail->Username = $configuracion['smtp_usuario'];     //SMTP username
        $mail->Password = $configuracion['smtp_password'];    //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port = $configuracion['smtp_puerto'];          //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        if ($data['attachment'] !== '') {
            $mail->addAttachment($data['attachment']);
        }

        /*-------------------------------------------
        [ Agregar Parámteros de Configuración Recipients a la Clase PHPMailer ]*/

        // (Remitente) Establecer de quién proviene el correo electrónico 
        $mail->setFrom($remitente, $empresa);

        // (Destinatario) Establecer a quién se envía el correo electrónico
        $mail->addAddress($emailDestino, $data['nombre_usuario']);


        /*-------------------------------------------
        [ Agregar Parámteros de Configuración Content a la Clase PHPMailer ]*/
        $mail->isHTML(true);                                                    //Set email format to HTML
        // $mail->Subject =  utf8_decode($asunto);                                 //Set the subject
        $mail->Subject = mb_convert_encoding($asunto, 'ISO-8859-1', 'UTF-8');

        // $mail->MsgHTML(utf8_decode($message));                                  //Set the message
        $mail->MsgHTML(mb_convert_encoding($message, 'ISO-8859-1', 'UTF-8'));

        // $mail->Body = utf8_decode(emailBody($cliente_id_post,  $nombre_cliente_post, $numero_departamento_post, $mensaje, "all"));
        // $mail->AltBody = strip_tags(utf8_decode($message));
        $mail->AltBody = strip_tags(mb_convert_encoding($message, 'ISO-8859-1', 'UTF-8'));

        /*-------------------------------------------
        [ Agregar Parámteros de CCon Copia para ]*/
        // $mail->addCC($data['email_gerente'], $data['nombre_gerente']);

        /*-------------------------------------------
        [ Send the email ]*/
        $response = $mail->send();

        /*-------------------------------------------
        [ Evaluar respuesta ]*/
        if (!$response) {
            getLoggerSystem()->error("Mailer Error: " . $mail->ErrorInfo);
        }
    } catch (\Throwable $th) {
        getLoggerSystem()->error(getMensajeError($th));
    }

    /*-------------------------------------------
    [ Retorna respuesta ]*/
    return $response;
}


//==================================================================
// [ Permisos ]


/**
 * Funcion para obtener los permisos de acceso a modulos para un usuario determinado
 *
 */
function getPermisos(int $modulo_id): array
{

    try {


        $arrResponse = array();

        /*-------------------------------------------
        [ Obtener valores de Sessión ]*/
        $session = new Session();
        $rol_id = $session->get('rol_id');

        /*-------------------------------------------
        [ Obtener json con los permisos de acuerdo al rol de usuario ]*/
        $permisos_model = new PermisosModel();
        $permisos = $permisos_model->selectPermisosRol($rol_id);
        $arrPermisosRol = json_decode($permisos['permisos'], true);

        /*-------------------------------------------
        [ Prepara array de respuesta de permisos por modulo ]*/
        if (count($arrPermisosRol) > 0) {
            $arrPermisosMod = isset($arrPermisosRol[$modulo_id]) ? $arrPermisosRol[$modulo_id] : "";
        }
        $arrResponse['permisosMod'] = $arrPermisosMod;
    } catch (\Throwable $th) {
        getLoggerSystem()->error(getMensajeError($th));
    }

    /*-------------------------------------------
        [ Retorna respuesta ]*/
    return $arrResponse;
}

/**
 * Funcion para obtener los permisos de acceso a modulos para un usuario determinado
 *
 */
function getPermisosMod(int $modulo_id): array
{

    try {


        $arrResponse = array();

        /*-------------------------------------------
        [ Obtener valores de Sessión ]*/
        $session = new Session();
        $rol_id = $session->get('rol_id');

        /*-------------------------------------------
        [ Obtener json con los permisos de acuerdo al rol de usuario ]*/
        $permisos_model = new PermisosModel();
        $permisos = $permisos_model->selectPermisosRol($rol_id);
        $arrPermisosRol = json_decode($permisos['permisos'], true);

        /*-------------------------------------------
        [ Prepara array de respuesta de permisos por modulo ]*/
        if (count($arrPermisosRol) > 0) {
            $arrPermisosMod = isset($arrPermisosRol[$modulo_id]) ? $arrPermisosRol[$modulo_id] : "";
        }
        $arrResponse = $arrPermisosMod;
    } catch (\Throwable $th) {
        getLoggerSystem()->error(getMensajeError($th));
    }

    /*-------------------------------------------
        [ Retorna respuesta ]*/
    return $arrResponse;
}


/**
 * Funcion para obtener los permisos de acceso a a todos los modulos por Rol, para un usuario determinado
 * 
 */
function getPermisosGlobal(): array
{

    try {

        $arrResponse = array();

        /*-------------------------------------------
        [ Obtener valores de Sessión ]*/
        $session = new Session();
        $rol_id = $session->get('rol_id');

        /*-------------------------------------------
        [ Obtener json con los permisos de acuerdo al rol de usuario ]*/
        if (!empty($rol_id)) {
            $permisos_model = new PermisosModel();
            $permisos = $permisos_model->selectPermisosRol($rol_id);
            if (!empty($permisos)) {
                $arrResponse = json_decode($permisos['permisos'], true);
            }
        }
    } catch (\Throwable $th) {
        getLoggerSystem()->error(getMensajeError($th));
    }

    /*-------------------------------------------
        [ Retorna respuesta ]*/
    return $arrResponse;
}
