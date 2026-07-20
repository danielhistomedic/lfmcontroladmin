<?php

/**
 * Controlador Clientes 
 */
class Clientes extends Controllers
{

    private $session;
    private $permisosMod;

    /**
     * Método Constructor de Controlador Clientes.
     * Inicializa Controllers::__construct.
     * Inicializa y valida datos de session.
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        // [ Validación de Sesion ]*/
        $this->session = new Session;
        if (!$this->session->getStatus()) {
            $this->session->redirect('inicio');
        }
    }

    /**
     * Carga la Vista Clientes. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function Clientes()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_CAT_CLIENTES];

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_CAT_CLIENTES);

            // Valida si tiene acceso a la pagina.
            if (!$this->permisosMod['r']) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion = $configuracion_model->selectConfiguracion();
            $data['configuracion'] = $configuracion;

            // Asigna los permisos de Módulo y SideBar
            $data['permisos'] = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;

            //Id de Menu para script de Pemisos de Boton exportar a Excel
            $data['menu'] = MOD_CAT_CLIENTES;
            $data['sucursal_id'] = $this->session->get('sucursal_id');
            $data['theme'] = $this->session->get('theme');

            //Header
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] = "panel, administracion, hdsolutions";

            //Form Principal
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
     * Obtiene la lista de registros para llenar la tabla en DataTable.net
     * 
     * @return string $arrData
     * json_encode($arrData, JSON_UNESCAPED_UNICODE)
     * 
     */
    public function getListaRecords()
    {

        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_CAT_CLIENTES];
            $modal_permisos = $arrPermisos[MOD_PERMISOS];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeIn";

            /*-------------------------------------------
            [ Obtiene el array con la lista de registros ]*/
            $class_model = new UsuariosModel;
            $arrData = $class_model->selectRecords();

            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            for ($i = 0; $i < count($arrData); $i++) {

                // { "data": "name" },

                // { "data": "descripcion" },

                // { "data": "activo" },
                $activo = $arrData[$i]['activo'];
                if ($activo == 1) {
                    $arrData[$i]['activo'] = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-success"><i class="fa-sharp fa-regular fa-circle-check"></i> Activo</button>';
                } else {
                    $arrData[$i]['activo'] = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-danger"><i class="fa-sharp fa-regular fa-circle-xmark"></i> Eliminado</button>';
                }

                // { "data": "options" }
                $arrData[$i]['options'] = '';

                $btnView = '';
                $btnEdit = '';
                $btnDelete = '';
                $btnView = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-info panel_lista_registros" data-animation="' . $data_animation . '" onclick="fntView(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Ver Detalle de Registro"><i class="fa-sharp fa-regular fa-magnifying-glass "></i> </button>';

                if ($activo == 1) {
                    $btnEdit .= '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-secondary panel_crear_editar" data-animation="' . $data_animation . '" onclick="fntEdit(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Editar Registro"><i class="fa-sharp fa-regular fa-pen-to-square "></i> </button>';
                } else {
                    $btnEdit = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-success" onclick="fntActive(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Reactivar Registro"><i class="fa-sharp fa-regular fa-rotate-right "></i> </button>';
                }

                if ($activo == 1) {
                    $btnDelete = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-danger" onclick="fntDeleteRecord(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Suspender Registro"><i class="fa-sharp fa-regular fa-regular fa-trash-can "></i> </button>';
                }

                $btnView = ($this->permisosMod['r']) ? $btnView : '';
                $btnEdit = ($this->permisosMod['u']) ? $btnEdit : '';
                $btnDelete = ($this->permisosMod['d']) ? $btnDelete : '';
                $arrData[$i]['options'] = '' . $btnView . ' ' . $btnEdit  . ' ' . $btnDelete . '';
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
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
            $this->permisosMod = $arrPermisos[MOD_CAT_CLIENTES];
            if (!$this->permisosMod['r']) {
                die(json_encode(getResponse('Acceso restringido'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Se reciben Datos del POST, y se limpian los datos recibidos ]*/
            $record_id = strClean($_POST['record_id']);

            /*-------------------------------------------
            [ Valida Datos de Form ]*/
            if ($record_id == '') {
                die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Asignar varibales de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            $record_id = intval(openssl_decrypt($record_id, METHODENCRIPT, KEY));

            /*-------------------------------------------
            [ Obtiene array con los datos del Modelo ]*/
            $class_model = new UsuariosModel;
            $arrData = $class_model->selectRecord($record_id);
            if (empty($arrData)) {

                die(json_encode(getResponse('Lo sentimos, Datos no encontrados'), JSON_UNESCAPED_UNICODE));
            } else {

                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
                $arrResponse['dataId'] = openssl_encrypt($record_id, METHODENCRIPT, KEY);
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Code rol_1001. Error Desconocido'), JSON_UNESCAPED_UNICODE));
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
            $this->permisosMod = $arrPermisos[MOD_CAT_CLIENTES];

            $arrResponse = array();

            /*-------------------------------------------
            [ Se aignan las variables de Sesión ]*/
            $sucursal_id = $this->session->get('sucursal_id');
            $empresa_id = $this->session->get('empresa_id');
            $usuario_id_register = $this->session->get('usuario_id');


            /*-------------------------------------------
            [ Se aignan las variables del POST ]*/

            $post = $_POST;

            $record_id = $_POST['id'];
            $usuario = strclean($_POST['usuario']);
            $password = strclean($_POST['password']);
            $password_confirm = strclean($_POST['password_confirm']);
            $nombre = strclean($_POST['nombre']);
            $paterno = strclean($_POST['paterno']);
            $materno = strclean($_POST['materno']);
            $telefono = strclean($_POST['telefono']);
            $email = strclean($_POST['email']);
            $calle = strclean($_POST['calle']);
            $num_exterior = strclean($_POST['num_exterior']);
            $num_interior = strclean($_POST['num_interior']);
            $colonia = strclean($_POST['colonia']);
            $ciudad = strclean($_POST['ciudad']);
            $estado = strclean($_POST['estado']);
            $cp = strclean($_POST['cp']);
            $pais = strclean($_POST['pais']);
            $referencias = strclean($_POST['referencias']);
            $lista_precios_id = intval(strclean($_POST['lista_precios_id']));

            $nombre = strtoupper($nombre);
            $paterno = strtoupper($paterno);
            $materno = strtoupper($materno);
            // $slug = str_replace(" ", "_", $name_lowercase);


            $rfc = strclean($_POST['rfc']);
            $razon_social = strclean($_POST['razon_social']);
            $codigo_postal = strclean($_POST['codigo_postal']);
            $regimen = strclean($_POST['regimen']);
            $uso_cfdi = strclean($_POST['uso_cfdi']);
            $email_fact = strclean($_POST['email_fact']);


            /*-------------------------------------------
            [ Valida formulario ]*/
            if ($password !== $password_confirm) {
                die(json_encode(getResponse("Las contraseñas no coinciden"), JSON_UNESCAPED_UNICODE));
            }

            ($usuario == '') ? die(json_encode(getResponse("Debe indicar usuario"), JSON_UNESCAPED_UNICODE)) : "";
            // ($password == '') ? die(json_encode(getResponse("Debe indicar password"), JSON_UNESCAPED_UNICODE)) : "";
            ($nombre == '') ? die(json_encode(getResponse("Debe indicar nombre"), JSON_UNESCAPED_UNICODE)) : "";
            ($paterno == '') ? die(json_encode(getResponse("Debe indicar paterno"), JSON_UNESCAPED_UNICODE)) : "";
            ($materno == '') ? die(json_encode(getResponse("Debe indicar materno"), JSON_UNESCAPED_UNICODE)) : "";
            ($telefono == '') ? die(json_encode(getResponse("Debe indicar telefono"), JSON_UNESCAPED_UNICODE)) : "";
            ($email == '') ? die(json_encode(getResponse("Debe indicar email"), JSON_UNESCAPED_UNICODE)) : "";
            ($calle == '') ? die(json_encode(getResponse("Debe indicar calle"), JSON_UNESCAPED_UNICODE)) : "";
            ($num_exterior == '') ? die(json_encode(getResponse("Debe indicar numero exterior"), JSON_UNESCAPED_UNICODE)) : "";
            ($colonia == '') ? die(json_encode(getResponse("Debe indicar colonia"), JSON_UNESCAPED_UNICODE)) : "";
            ($ciudad == '') ? die(json_encode(getResponse("Debe indicar ciudad"), JSON_UNESCAPED_UNICODE)) : "";
            ($estado == '') ? die(json_encode(getResponse("Debe indicar estado"), JSON_UNESCAPED_UNICODE)) : "";
            ($cp == '') ? die(json_encode(getResponse("Debe indicar cp"), JSON_UNESCAPED_UNICODE)) : "";
            ($pais == '') ? die(json_encode(getResponse("Debe indicar pais"), JSON_UNESCAPED_UNICODE)) : "";
            ($lista_precios_id == 0) ? die(json_encode(getResponse("Debe indicar lista de ´precios"), JSON_UNESCAPED_UNICODE)) : "";

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            $record_id = intval(strClean(openssl_decrypt($record_id, METHODENCRIPT, KEY)));


            /*-------------------------------------------
            [ _FILES ]*/
            // $files =  $_FILES;

            // if ($_FILES['adjunto']['error'] == 0) {
            //     $arrParams = explode('.', $_FILES['adjunto']['name']);
            //     $index = count($arrParams) - 1;
            //     $file_extension = strClean($arrParams[$index]);

            //     $file_name = $_FILES['adjunto']['name'];
            //     $n = $file_name . date('YmdHis');
            //     $adjunto = encode($n) . '.' . trim($file_extension);
            // } else {
            //     $adjunto = '';
            // }

            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $class_model = new UsuariosModel;
            $class_model->setId($record_id);
            $class_model->setUsuario($usuario);
            $class_model->setSucursal_id($sucursal_id);
            $class_model->setEmpresa_id($empresa_id);
            $class_model->setUsuario_id_created($usuario_id_register);
            $class_model->setIsCliente(true);

            // -- Datos Generales --
            $datos_generales = array();
            $datos_generales['nombre'] = $nombre;
            $datos_generales['paterno'] = $paterno;
            $datos_generales['materno'] = $materno;
            $datos_generales['email'] = $email;
            $datos_generales['telefono'] = $telefono;
            $datos_generales['titular'] = 0;
            $datos_generales['rol_id'] = 2;
            $datos_generales['lista_precios_id'] = $lista_precios_id;
            $class_model->setDatos_generales($datos_generales);

            // -- Domiclio --
            $datos_domicilio_envio = array();
            $datos_domicilio_envio['calle'] = $calle;
            $datos_domicilio_envio['num_exterior'] = $num_exterior;
            $datos_domicilio_envio['num_interior'] = $num_interior;
            $datos_domicilio_envio['colonia'] = $colonia;
            $datos_domicilio_envio['ciudad'] = $ciudad;
            $datos_domicilio_envio['estado'] = $estado;
            $datos_domicilio_envio['cp'] = $cp;
            $datos_domicilio_envio['pais'] = $pais;
            $datos_domicilio_envio['referencias'] = $referencias;
            $datos_domicilio_envio['activo'] = 1;
            $datos_domicilio_envio['predeterminado'] = 1;
            $class_model->setDomicilioEnvio($datos_domicilio_envio);

            // -- Facturacion --
            $datos_facturacion = array();
            $datos_facturacion['rfc'] = $rfc;
            $datos_facturacion['razon_social'] = $razon_social;
            $datos_facturacion['codigo_postal'] = $codigo_postal;
            $datos_facturacion['regimen'] = $regimen;
            $datos_facturacion['uso_cfdi'] = $uso_cfdi;
            $datos_facturacion['email'] = $email_fact;
            $datos_facturacion['activo'] = 1;
            $class_model->setDatosFacturacion($datos_facturacion);

            // $class_model->setFiles($files);

            if ($record_id == 0) {

                /*==========================================
                [ Crear Nuevo Registro ]*/

                $password_val = $password === '' ? passGenerator() : $password;
                $password_encrypt = hash("SHA256", $password_val);
                $class_model->setPass($password_encrypt);

                /*-------------------------------------------
                [ Se aignan las variables al Modelo antes de Insert ]*/
                $class_model->setActivo(1);

                /*-------------------------------------------
                [ Valida Permisos. ]*/
                if (!$this->permisosMod['c']) {
                    die(json_encode(getResponse('No cuenta con privilegios suficientes para realizar esta accion.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Valida si el rol ya existe ]*/
                /*-------------------------------------------
                [ Valida si el Registro ya existe ]*/
                $existe = $class_model->validInsertExist($usuario);
                if ($existe == true) {
                    die(json_encode(getResponse('El registro ya existe.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Inserta el Registro si pasa las validaciones. ]*/
                $response = $class_model->insertRecord($class_model);
                if ($response == true) {
                    $arrResponse = getResponse('Registro creado exitosamente.', "ok");

                    /*-------------------------------------------
                    [ Enviar Correo de Bienvenida  ]*/

                    $config_model = new ConfiguracionModel;
                    $config = $config_model->selectConfiguracion();
                    $telefono_contacto = $config['telefono_contacto'];
                    $email_contacto = $config['email_contacto'];

                    $nombre_usuario = $nombre . ' ' .  $paterno . ' ' . $materno;
                    $datos_usuario = array(
                        'nombre_usuario' => $nombre_usuario,
                        'email_destino' => $email,
                        'email' => $email,
                        'usuario' => $usuario,
                        'password' => $password_val,
                        'attachment' => '',
                        'asunto' => 'Cuenta de Acceso a Tienda en Linea',
                        'telefono_contacto' => $telefono_contacto,
                        'email_contacto' => $email_contacto
                    );
                    $sendEmail = sendEmailPHPMailer($datos_usuario, 'welcome_clientes');
                    $arrResponse['email_enviado'] = 'si';
                    if (!$sendEmail) {
                        $arrResponse['email_enviado'] = 'no';
                        getLoggerSystem()->error('No se envió correo de bienvenida', $datos_usuario);
                    }
                } else {
                    die(json_encode(getResponse("Code: 1002,  Error al crear el registro, intente nuevamente"), JSON_UNESCAPED_UNICODE));
                }
            } else {

                /*==========================================
                [ Actualizar Registro ]*/

                /*-------------------------------------------
                [ Valida Permisos. ]*/
                if (!$this->permisosMod['u']) {
                    die(json_encode(getResponse('No cuenta con privilegios suficientes para realizar esta accion.'), JSON_UNESCAPED_UNICODE));
                }

                //Hash password
                if ($password != '') {
                    $password_encrypt = hash("SHA256", $password);
                    $class_model->setPass($password_encrypt);
                } else {
                    $class_model->setPass('');
                }

                /*-------------------------------------------
                [ Valida si el rol ya existe ]*/
                $existe = $class_model->validUpdateExistRecord($usuario, $record_id);
                if ($existe == true) {
                    die(json_encode(getResponse('El registro que desea actualizar, ya existe.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Verifica rol del usuario y asigna]*/
                $usuario_model = new UsuariosModel;
                $rol_usuario = $usuario_model->selectRecord($record_id);
                $datos_generales['rol_id'] = $rol_usuario['rol_id'];

                /*-------------------------------------------
                [ Actualizar Registro ]*/
                $response = $class_model->updateRecord($class_model);
                if ($response == true) {
                    $arrResponse = getResponse("Registro actualizado exitosamente", "ok", true);
                } else {
                    die(json_encode(getResponse("Code: 1003, Error al actualizar el registro, intente nuevamente"), JSON_UNESCAPED_UNICODE));
                }
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse("Try Error al crear el registro, intente nuevamente"), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Actualizar Estatus de Registro
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * 
     * @return json json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     */
    public function setEstatusRecord()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_CAT_CLIENTES];
            if (!$this->permisosMod['u']) {
                die(json_encode(getResponse('Acceso Restringido'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Se reciben Datos del POST, y se limpian los datos recibidos ]*/
            $record_id = strClean($_POST['record_id']);
            $estatus = intval(strClean($_POST['estatus']));

            /*-------------------------------------------
            [ Valida Datos de Form ]*/
            if ($record_id == '') {
                die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Asignar varibales de sesión ]*/
            $usuario_id_register = $this->session->get('usuario_id');
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');


            /*-------------------------------------------
            [ Se desencriptan datos ]*/
            $record_id = intval(strClean(openssl_decrypt($record_id, METHODENCRIPT, KEY)));


            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $class_model = new ClientesModel;
            $class_model->setId($record_id);
            $class_model->setActivo($estatus);
            $class_model->setUsuarioIdCreated($usuario_id_register);

            /*-------------------------------------------
            [ Elimina el Registro seleccionado. ]*/
            $response = $class_model->updateEstatusRecord($class_model);

            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if ($response == true) {
                if ($estatus == 0) {
                    $arrResponse = getResponse('Registro Eliminado exitosamente', 'ok', true);
                } else {
                    $arrResponse = getResponse('Registro Reactivado exitosamente', 'ok', true);
                }
            } else {
                if ($estatus == 0) {
                    die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente'), JSON_UNESCAPED_UNICODE));
                } else {
                    die(json_encode(getResponse('Error al reactivar el registro, intente nuevamente'), JSON_UNESCAPED_UNICODE));
                }
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Code rol_1001. Error desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene la lista de registros, para crear el html con los options que llenaran el Select correspondiente.
     * 
     * @return string $htmlOptions
     * 
     */
    public function getSelectRecords()
    {

        try {

            $htmlOptions = '';

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_CAT_CLIENTES];
            if (!$this->permisosMod['r']) {
                die($htmlOptions);
            }

            $htmlOptions .= '<option value="" selected="selected" disabled>Seleccione una opcion</option>';
            $rol_model = new ClientesModel;
            $arrData = $rol_model->selectRecords();
            if (count($arrData) > 0) {
                for ($i = 0; $i < count($arrData); $i++) {
                    if ($arrData[$i]['activo'] == 1) {
                        $htmlOptions .= '<option value="' . $arrData[$i]['id'] . '">' . $arrData[$i]['name'] . '</option>';
                    }
                }
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta string html ]*/
        die($htmlOptions);
    }
}
