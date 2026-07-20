<?php

/**
 * Controlador Configuracion 
 */
class Configuracion extends Controllers
{

    private $session;
    private $permisosMod;

    const prefijo_msj_error = "config";

    /**
     * Método Constructor de Controlador Configuracion.
     * Inicializa Controllers::__construct.
     * Inicializa y valida datos de session.
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        [ Validación de Sesion ]*/
        $this->session = new Session;
        if (!$this->session->getStatus()) {
            $this->session->redirect('inicio');
        }
    }

    /**
     * Carga la Vista Configuracion. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function Configuracion()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_CONFIGURACION];

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_CONFIGURACION);

            // Valida si tiene acceso a la pagina.
            if (!$this->permisosMod['r']) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            // Asigna los permisos de Módulo y SideBar
            $data['permisos'] = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;

            //Datos de Usuario en Sesión.
            $data['empresa_id'] = $this->session->get('empresa_id');
            $data['sucursal_id'] = $this->session->get('sucursal_id');
            $data['theme'] = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email'] = $this->session->get('email');
            $data['usuario']['rol'] = $this->session->get('rol');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            //Id de Menu para script de Pemisos de Boton exportar a Excel
            $data['menu'] = MOD_CONFIGURACION;

            //Header
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] =  $menu['tags'];

            //Form Principal
            $data['icon_form_title'] = $menu['icon_form_title'];
            $data['page_form_title'] = $menu['icon_form_title'] . $menu['form_title'];

            //Breadcrump
            $data['page_breadcrumb'] = $menu['breadcrumb'];

            //Card Principal
            $data['page_card_title'] =  $menu['card_title'];
            $data['page_card_description'] = $menu['descripcion'];

            //JS Principal
            $data['page_functions_js'] = $menu['js'];

            //Call Vista
            $this->views->getView($this, $menu['views'], $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Obtiene los datos del registro seleccionado por Metodo POST
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * * data (array). En caso de ser exitoso, el elemento data contiene la información solicitada.
     * 
     * @return string 
     * json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     * 
     * 
     */
    public function getRecord()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_CONFIGURACION];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Variables. ]*/
            $arrData = array();

            /*-------------------------------------------
            [ Asignar variables de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');

            /*-------------------------------------------
            [ Instanciar Modelo y asignar variables ]*/
            $class_model = new ConfiguracionModel;
            $class_model->setEmpresaId($empresa_id);

            /*-------------------------------------------
            [ Obtiene array con los datos obtenidos ]*/
            $arrData = $class_model->selectRecord($class_model);
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
                $arrResponse['data']['id'] = openssl_encrypt($arrData['id'], METHODENCRIPT, KEY);
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Codigo Error: ' . self::prefijo_msj_error . '_1001. Error Desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Guardar/Actualizar datos de Registro seleccionado
     * 
     * @return string 
     * json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     * $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * 
     */
    public function setRecord()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_CONFIGURACION];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Variables locales ]*/
            $arrResponse = array();

            /*-------------------------------------------
            [ Se aignan las variables de Sesión ]*/
            $sucursal_id = $this->session->get('sucursal_id');
            $empresa_id = $this->session->get('empresa_id');
            $usuario_id_register = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Se aignan las variables del POST y se limpian los datos recibidos ]*/
            $record_id = $_POST['id'];
            $smtp_host = strclean($_POST['smtp_host']);
            $smtp_usuario = strclean($_POST['smtp_usuario']);
            $smtp_password = strclean($_POST['smtp_password']);
            $smtp_puerto = strclean($_POST['smtp_puerto']);

            $telefono_contacto = strclean($_POST['telefono_contacto']);
            $email_contacto = strclean($_POST['email_contacto']);
            $url_tienda = strclean($_POST['url_tienda']);

            $nombre_remitente = strclean($_POST['nombre_remitente']);
            $email_remitente = strclean($_POST['email_remitente']);
            $sitio_web = strclean($_POST['sitio_web']);

            $email_destino = strclean($_POST['email_destino']);

            /*-------------------------------------------
            [ Valida formulario ]*/
            (trim($smtp_host) == '') ? die(json_encode(getResponse("Debe indicar Host"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($smtp_usuario) == '') ? die(json_encode(getResponse("Debe indicar usuario de Host"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($smtp_password) == '') ? die(json_encode(getResponse("Debe indicar password de Host"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($smtp_puerto) == '') ? die(json_encode(getResponse("Debe indicar puerto de Host"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($telefono_contacto) == '') ? die(json_encode(getResponse("Debe indicar telefono"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($email_contacto) == '') ? die(json_encode(getResponse("Debe indicar email"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($url_tienda) == '') ? die(json_encode(getResponse("Debe indicar url de tienda"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($nombre_remitente) == '') ? die(json_encode(getResponse("Debe indicar nombre de remitente de correos"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($email_remitente) == '') ? die(json_encode(getResponse("Debe indicar email de remitente de correos"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($sitio_web) == '') ? die(json_encode(getResponse("Debe indicar sitio web de la empresa"), JSON_UNESCAPED_UNICODE)) : "";

            /*-------------------------------------------
            [ Procesos auxiliares ]*/


            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            $record_id = intval(strClean(openssl_decrypt($record_id, METHODENCRIPT, KEY)));
            if ($record_id == 0) {
                die(json_encode(getResponse("Debe seleccionar un registro valido."), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $class_model = new ConfiguracionModel;
            $class_model->setId($record_id);
            $class_model->setEmpresaId($empresa_id);
            $class_model->setSmtpHost($smtp_host);
            $class_model->setSmtpUsuario($smtp_usuario);
            $class_model->setSmtpPassword($smtp_password);
            $class_model->setSmtpPuerto($smtp_puerto);
            $class_model->setTelefonoContacto($telefono_contacto);
            $class_model->setEmailContacto($email_contacto);
            $class_model->setUrlTienda($url_tienda);
            $class_model->setNombreRemitente($nombre_remitente);
            $class_model->setEmailRemitente($email_remitente);
            $class_model->setEmailDestino($email_destino);
            $class_model->setSitioWeb($sitio_web);

            /*==========================================
            [ Actualizar Registro ]*/

            /*-------------------------------------------
            [ Valida Permisos. ]*/
            if (!$this->permisosMod['u']) {
                die(json_encode(getResponse('No cuenta con privilegios suficientes para realizar esta accion.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Se aignan las variables especificas correspondientes al Update ]*/
            $class_model->setUsuarioIdUpdated($usuario_id_register);

            /*-------------------------------------------
            [ Actualizar Registro ]*/
            $response = $class_model->updateRecord($class_model);

            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if (!$response) {
                die(json_encode(getResponse('Codigo Error: ' . self::prefijo_msj_error . '_1004. Error Desconocido'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Respuesta Exitosa  ]*/
            $arrResponse = getResponse("Registro actualizado exitosamente", "ok");
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Codigo Error: ' . self::prefijo_msj_error . '_1005, Error Desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
