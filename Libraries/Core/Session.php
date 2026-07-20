<?php

/**
 * Clase básica para adminsitrar sesiones
 */
class Session
{

    private $current_date;
    private $cookie_expiration_time;

    /**
     * Método Constructor de Controlador Inicio.
     * Inicializa Controllers::__construct.
     * Inicializa y valida datos de session.
     */
    public function __construct()
    {
        $this->init();
    }


    public static function sessionName()
    {
        $date = date('YmdHis');
        $session_name =  encode($date);
        return $session_name;
    }

    /**
     * Inicializa la sesión
     */
    public function init()
    {

        @session_start();

        // Get Current date, time
        $current_time = time();
        $this->setCurrentDate(date("Y-m-d H:i:s", $current_time));

        // Set Cookie expiration for 1 month
        $this->setCookieExpirationTime($current_time + (30 * 24 * 60 * 60));  // for 1 month

        // if ($this->getStatus() == false) {
        //     // session_set_cookie_params(60 * 60 * 12);
        //     @session_start();
        // }

    }

    /**
     * Agrega un elemento a la sesión
     * @param string $key la llave del array de sesión
     * @param string $value el valor para el elemento de la sesión
     */
    public function add($key, $value)
    {
        $key = PREFIJO_SESSION . $key;
        $_SESSION[$key] = $value;
    }

    /**
     * Retorna un elemento a la sesión
     * @param string $key la llave del array de sesión
     * @return string el valor del array de sesión si tiene valor
     */
    public function get($key, $key2 = "", $key3 = "", $key4 = "")
    {
        $key = PREFIJO_SESSION . $key;

        if ($key2 == '' && $key3 == '' && $key4 == '') {
            return !empty($_SESSION[$key]) ? $_SESSION[$key] : null;
        } else if ($key2 != '' && $key3 == '' && $key4 == '') {
            return !empty($_SESSION[$key][$key2]) ? $_SESSION[$key][$key2] : null;
        } else if ($key2 != '' && $key3 != '' && $key4 == '') {
            return !empty($_SESSION[$key][$key2][$key3]) ? $_SESSION[$key][$key2][$key3] : null;
        } else if ($key2 != '' && $key3 != '' && $key4 != '') {
            return !empty($_SESSION[$key][$key2][$key3][$key4]) ? $_SESSION[$key][$key2][$key3][$key4] : null;
        }
    }

    /**
     * Retorna todos los valores del array de sesión
     * @return el array de sesión completo
     */
    public function getAll()
    {
        return $_SESSION;
    }

    /**
     * Remueve un elemento de la sesión
     * @param string $key la llave del array de sesión
     */
    public function remove($key)
    {
        $key = PREFIJO_SESSION . $key;
        if (!empty($_SESSION[$key]))
            unset($_SESSION[$key]);
    }

    /**
     * Cierra la sesión eliminando los valores
     */
    public function close()
    {

        session_unset();
        session_destroy();

        $this->clearAuthCookie();

        $this->redirect('login');
    }

    /**
     * Retorna el estatus de la sesión
     * @return bool el estatus de la sesión
     */
    public function getStatus($inicio = false): bool
    {

        $isLoggedIn = false;
        $key = PREFIJO_SESSION . "email";

        if (!empty($_SESSION[$key])) {

            $isLoggedIn = true;
        } else {

            if (!$inicio) {
                $key_usuario = PREFIJO_SESSION . "usuario";
                $key_pass = PREFIJO_SESSION . "password";
                if (!isset($_COOKIE[$key_usuario])) {
                    return false;
                }
                $isLoggedIn = $this->reconect($_COOKIE[$key_usuario], $_COOKIE[$key_pass]);
            }
        }

        return $isLoggedIn;
    }


    /**
     * Redirecciona a la pagina especificada.
     * @param $page redirecciona a la pagina indicada
     */
    public function redirect($page)
    {
        header('Location: ' . base_url() . '/' . $page);
    }

    /**
     * Establece una cookie
     * @param string $key la llave del array de cookie
     * @param string $value el valor para el elemento de la sesión
     */
    public function setCookie($key, $value, $time)
    {
        $key = PREFIJO_SESSION . $key;
        setcookie($key, $value, $time, '/');
    }


    /**
     * 
     */
    public function clearAuthCookie()
    {

        $key_usuario = PREFIJO_SESSION . "usuario";
        setcookie($key_usuario, "", time() - 60, '/');

        $key_pass = PREFIJO_SESSION . "password";
        setcookie($key_pass, "", time() - 60, '/');
    }



    /**
     * Validar acceso al sistema para reconectar
     * 
     * @response bool
     */
    public function reconect(string $usuario_encrypted, string $password_encrypted): bool
    {

        try {

            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $usuario_encrypted = strClean($usuario_encrypted);
            $password_encrypted = strclean($password_encrypted);

            /*-------------------------------------------
            [ Validaciones PHP ]*/
            if ($usuario_encrypted == '') {
                return false;
            }
            if ($password_encrypted == '') {
                return false;
            }

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            $usuario = openssl_decrypt($usuario_encrypted, METHODENCRIPT, KEY);
            $password = openssl_decrypt($password_encrypted, METHODENCRIPT, KEY);

            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $user_model = new UsuariosModel;
            $user_model->setUsuario($usuario);
            $user_model->setPass($password);

            /*-------------------------------------------
            [ Se ejecuta el Método loginUser del modelo para validar al usuario ]*/
            $usuario = $user_model->validaDatosUsuario($user_model);

            if (empty($usuario)) {
                return false;
            }

            //Valida si el usuario está activo NO se ocupa, se va a ver edentro validar de acuerdo el estatus de activo o inactivo los privilegios de acceso.
            if ($usuario['activo'] == 0) {
                return false;
            }

            /*-------------------------------------------
            [ Asigan valores de usuario ] */
            $usuario_id = intval($usuario['id']);

            /*-------------------------------------------
            [ Valida si solo pertecece a una Unidad Médica ] */
            $empresas_model = new EmpresasModel;
            $empresas_model->setUsuarioId($usuario_id);
            $arrEmpresas = $empresas_model->getEmpresasUsuarios($empresas_model);
            $total_empresas = count($arrEmpresas);

            if ($total_empresas == 1) {
                $empresa_id = intval($arrEmpresas[0]['empresa_id']);
                $sucursal_id = intval($arrEmpresas[0]['sucursal_id']);
            } else {
                // Se debe validar y
                // buscar un modo de enviar un modal para que seleccione la empresa y sucursal donde va a laborar
            }

            //Valida si el usuario está activo NO se ocupa, se va a ver edentro validar de acuerdo el estatus de activo o inactivo los privilegios de acceso.
            if ($arrEmpresas[0]['activo'] == 0) {
                return false;
            }

            /*-------------------------------------------
            [ Obtener Datos General de Perfil para session ] */
            $user_model->setId($usuario_id);
            $user_model->setEmpresaId($empresa_id);
            $user_model->setSucursalId($sucursal_id);
            $arrUsuario = $user_model->selectUsuarioLogin($user_model);
            if (count($arrUsuario) == 0) {
                return false;
            };


            /*-------------------------------------------
            [ Inicializa las variables de sesión ] */
            $this->add('usuario', $arrUsuario['usuario']);
            $this->add('theme', $arrUsuario['theme']);
            $this->add('usuario_id', $usuario_id);
            $this->add('email', $arrUsuario['email']);
            $this->add('empresa_id', $empresa_id);
            $this->add('sucursal_id', $sucursal_id);
            $this->add('nombre', $arrUsuario['nombre'] . ' ' . $arrUsuario['paterno'] . ' ' . $arrUsuario['materno']);
            $this->add('nombre_solo', $arrUsuario['nombre']);
            $this->add('rol_id', $arrUsuario['rol_id']);
            $this->add('rol', $arrUsuario['rol']);
            $this->add('recordarme', 1);

            return true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        return false;
    }


    /**
     * Get the value of current_date
     */
    public function getCurrentDate()
    {
        return $this->current_date;
    }

    /**
     * Set the value of current_date
     */
    public function setCurrentDate($current_date): self
    {
        $this->current_date = $current_date;

        return $this;
    }

    /**
     * Get the value of cookie_expiration_time
     */
    public function getCookieExpirationTime()
    {
        return $this->cookie_expiration_time;
    }

    /**
     * Set the value of cookie_expiration_time
     */
    public function setCookieExpirationTime($cookie_expiration_time): self
    {
        $this->cookie_expiration_time = $cookie_expiration_time;

        return $this;
    }
}
