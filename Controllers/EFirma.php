<?php

/**
 * Controlador EFirma 
 */
class EFirma extends Controllers
{

    private $session;
    private $permisosMod;

    const prefijo_msj_error = "config";

    /**
     * Método Constructor de Controlador EFirma.
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
     * Carga la Vista EFirma. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function EFirma()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_EFIRMA];

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_EFIRMA);

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
            $data['menu'] = MOD_EFIRMA;

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
            $this->permisosMod = $arrPermisos[MOD_EFIRMA];
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
            $class_model = new EFirmaModel;
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
            $this->permisosMod = $arrPermisos[MOD_EFIRMA];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Variables locales ]*/
            $arrResponse = array();

            /*-------------------------------------------
            [ Se asignan las variables de Sesión ]*/
            $sucursal_id = $this->session->get('sucursal_id');
            $empresa_id = $this->session->get('empresa_id');
            $usuario_id_register = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Se aignan las variables del POST y se limpian los datos recibidos ]*/
            $record_id = strclean($_POST['id']);
            $rfc = strclean($_POST['rfc']);
            $password = strclean($_POST['password']);

            /*-------------------------------------------
            [ Se aignan las variables del FILES ]*/
            $files = $_FILES;

            /*-------------------------------------------
            [ Valida formulario ]*/
            (trim($rfc) == '') ? die(json_encode(getResponse("Debe indicar RFC de la empresa"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($password) == '') ? die(json_encode(getResponse("Debe indicar password"), JSON_UNESCAPED_UNICODE)) : "";

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
            $class_model = new EFirmaModel;
            $class_model->setId($record_id);
            $class_model->setRfc($rfc);
            $class_model->setPassword($password);
            $class_model->setEmpresaId($empresa_id);
            $class_model->setFiles($files);

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

    /**
     * Actualizar estatus de servivcio de descarga masiva
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
    public function setActivarDescargaMasiva()
    {

        try {


            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_EFIRMA];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Variables locales ]*/
            $arrResponse = array();

            /*-------------------------------------------
            [ Se asignan las variables de Sesión ]*/
            $sucursal_id = $this->session->get('sucursal_id');
            $empresa_id = $this->session->get('empresa_id');
            $usuario_id_register = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Se aignan las variables del POST y se limpian los datos recibidos ]*/
            $record_id = strclean($_POST['id']);
            $estatus_descarga = intval(strClean($_POST['estatus_descarga']));

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            $record_id = intval(strClean(openssl_decrypt($record_id, METHODENCRIPT, KEY)));
            if ($record_id == 0) {
                die(json_encode(getResponse("Debe seleccionar un registro valido."), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $class_model = new EFirmaModel;
            $class_model->setId($record_id);
            $class_model->setEmpresaId($empresa_id);
            $class_model->setEstatusDescarga($estatus_descarga);

            /*-------------------------------------------
            [ Verificar si los certificados está correctamente activados ]*/
            $efirma =  $class_model->selectRecord($class_model);
            $certificado = $efirma['certificado'];
            $llave = $efirma['llave'];
            if ($certificado == 0 || $llave == 0) {
                die(json_encode(getResponse("Los archivos de e-firma no están activos.<br> Actualice la Información y posteriormente <br>active el servicio de descarga masiva."), JSON_UNESCAPED_UNICODE));
            }

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
            $response = $class_model->activarDescargaMasiva($class_model);

            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if (!$response) {
                die(json_encode(getResponse('Codigo Error: ' . self::prefijo_msj_error . '_1004. Error Desconocido'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Respuesta Exitosa  ]*/
            $arrResponse = getResponse("Registro realizado exitosamente", "ok");
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Codigo Error: ' . self::prefijo_msj_error . '_1005, Error Desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
