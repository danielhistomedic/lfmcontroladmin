<?php

/**
 * Controlador Login 
 */
class Login extends Controllers
{

    private $session;

    const prefijo_msj_error = "usuarios";

    /**
     * Método Constructor de Controlador Roles.
     * Inicializa Controllers::__construct
     * Inicializa y valida variables de sesión.
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        // [ Validación de Sesion ]*/
        $this->session = new Session;
        if ($this->session->getStatus(true)) {
            header('Location: ' . base_url() . '/inicio');
        }
    }

    /**
     * Carga la Vista Login. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function Login()
    {

        try {

            /*-------------------------------------------
            [ Crea el array $data para enviar a la vista ]*/
            $data['meta_keywords'] = "bombas, valvulas, sellos";
            $data['meta_description'] = "Sistema de Gestión LFM CONTROL";
            $data['meta_author'] = "LFM CONTROL";
            $data['title'] = "Sistema LFM CONTROL";

            $data['page_functions_js'] = "login.js";
            $data['token'] = openssl_encrypt(KEY_LOGIN, METHODENCRIPT, KEY);

            /*-------------------------------------------
            [ Ejecuta el método para generar la vista en el navegador ]*/
            $this->views->getView($this, "login", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Validar acceso al sistema
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * 
     * @return json json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     */
    public function validaAcceso()
    {

        try {

            /*-------------------------------------------
            [ Verifica que sea acceso tipo POST ]*/
            if (empty($_POST)) {
                header('Location: ' . base_url());
            }

            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $email = strtolower(strClean($_POST['email']));
            $pass = strclean($_POST['pass']);
            $token = strClean($_POST['token']);
            $recordarme_post = 0;
            if (isset($_POST['recordarme'])) {
                $recordarme_post = 1;
            }

            /*-------------------------------------------
            [ Validaciones PHP ]*/
            $token_login = openssl_encrypt(KEY_LOGIN, METHODENCRIPT, KEY);
            if ($token != $token_login) {
                $arrResponse = getResponse("Formulario No Válido");
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }
            if ($email == '') {
                $arrResponse = getResponse("Usuario o Password No Válido");
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }
            if ($pass == '') {
                $arrResponse = getResponse("Usuario o Password No Válido");
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Hash Password ]*/
            $password_hash = hash('SHA256', $pass);

            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $user_model = new UsuariosModel;
            $user_model->setUsuario($email);
            $user_model->setPass($password_hash);

            /*-------------------------------------------
            [ Se ejecuta el Método loginUser del modelo para validar al usuario ]*/
            $usuario = $user_model->validaDatosUsuario($user_model);

            if (empty($usuario)) {
                $arrResponse = getResponse("Usuario o Password No Válido");
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            //Valida si el usuario está activo NO se ocupa, se va a ver edentro validar de acuerdo el estatus de activo o inactivo los privilegios de acceso.
            if ($usuario['activo'] == 0) {
                die(json_encode(getResponse('USUARIO INACTIVO. <br> Contacte a ' . EMAIL_EMPRESA), JSON_UNESCAPED_UNICODE));
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

            /*-------------------------------------------
            [ Valida si el usuario está activo NO se ocupa, se va a ver edentro validar de acuerdo el 
              estatus de activo o inactivo los privilegios de acceso. ] */
            if ($arrEmpresas[0]['activo'] == 0) {
                die(json_encode(getResponse('USUARIO INACTIVO. <br> Contacte al Administrador de su Empresa o Sucursal.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Obtener Datos General de Perfil para session ] */
            $user_model->setId($usuario_id);
            $user_model->setEmpresaId($empresa_id);
            $user_model->setSucursalId($sucursal_id);
            $arrUsuario = $user_model->selectUsuarioLogin($user_model);
            if (count($arrUsuario) == 0) {
                $arrResponse = getResponse("Usuario o contraseña invalido, intente nuevamente");
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            };


            /*-------------------------------------------
            [ Inicializa las variables de sesión ] */
            $this->session->add('usuario', $arrUsuario['usuario']);
            $this->session->add('theme', $arrUsuario['theme']);
            $this->session->add('usuario_id', $usuario_id);
            $this->session->add('email', $arrUsuario['email']);
            $this->session->add('empresa_id', $empresa_id);
            $this->session->add('sucursal_id', $sucursal_id);
            $this->session->add('nombre', $arrUsuario['nombre'] . ' ' . $arrUsuario['paterno'] . ' ' . $arrUsuario['materno']);
            $this->session->add('nombre_solo', $arrUsuario['nombre']);
            $this->session->add('rol_id', $arrUsuario['rol_id']);
            $this->session->add('rol', $arrUsuario['rol']);
            $this->session->add('ccveusuario', $arrUsuario['ccveusuario']);
            $this->session->add('recordarme', $recordarme_post);

            /*-------------------------------------------
            [ Set Auth Cookies if 'Remember Me' checked ] */
            if ($recordarme_post == 1) {

                $usuario_encrypted = openssl_encrypt($arrUsuario['usuario'], METHODENCRIPT, KEY);
                $this->session->setCookie("usuario", $usuario_encrypted, $this->session->getCookieExpirationTime());

                $password_encrypted = openssl_encrypt($password_hash, METHODENCRIPT, KEY);
                $this->session->setCookie("password", $password_encrypted, $this->session->getCookieExpirationTime());

                $loginModel = new LoginModel;
                $loginModel->insertToken($usuario_encrypted, $password_encrypted, '', 1);
            }

            /*-------------------------------------------
            [ Respuesta exitosa ] */
            $arrResponse = getResponse("Acceso Autorizado", "ok", false);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            $arrResponse = getResponse("Codigo de Error " . self::prefijo_msj_error . "1001. Error desconocido.");
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
