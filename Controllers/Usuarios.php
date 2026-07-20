<?php

/**
 * Controlador Usuarios 
 */
class Usuarios extends Controllers
{

    private $session;
    private $permisosMod;

    const prefijo_msj_error = "usuarios";

    /**
     * Método Constructor de Controlador Usuarios.
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
     * Carga la Vista de Usuarios. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function Usuarios(): void
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_USUARIOS];

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_USUARIOS);

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
            $data['menu'] = MOD_USUARIOS;

            //Header
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] = "panel, administracion";

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
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_USUARIOS];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Variables locales ]*/
            $data_animation = "fadeIn";

            /*-------------------------------------------
            [ Asignar variables de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');
            $usuario_id = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Obtiene el array con la lista de registros ]*/
            $class_model = new UsuariosModel;
            $class_model->setEmpresaId($empresa_id);
            $class_model->setSucursalId($sucursal_id);
            $arrData = $class_model->selectRecords($class_model);


            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            for ($i = 0; $i < count($arrData); $i++) {

                //      { "data": "usuario" },
                if ($arrData[$i]['titular'] != 0) {
                    $arrData[$i]['usuario'] = '<div><i class="fa-solid fa-user-check fs-16 text-green me-1"></i> '  . $arrData[$i]['usuario'] . '</div>';
                }

                // { "data": "nombre" },
                $arrData[$i]['nombre'] = $arrData[$i]['nombre'] . ' ' . $arrData[$i]['paterno'] . ' ' . $arrData[$i]['materno'];


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
                $btnView = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-info panel_lista_registros" data-animation="' . $data_animation . '" onclick="fntView(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Ver Detalle de Registro"><i class="fa-sharp fa-regular fa-magnifying-glass"></i> </button>';
                if ($activo == 1) {
                    $btnEdit .= '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-secondary panel_crear_editar" data-animation="' . $data_animation . '" onclick="fntEdit(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Editar Registro"><i class="fa-sharp fa-regular fa-pen-to-square"></i> </button>';
                } else {
                    $btnEdit = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-success" onclick="fntActive(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" data-id-sucursal="' . openssl_encrypt($arrData[$i]['sucursal_id'], METHODENCRIPT, KEY) . '" title= "Reactivar Registro"><i class="fa-sharp fa-regular fa-rotate-right"></i> </button>';
                }

                if ($activo == 1) {
                    $btnDelete = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-danger" onclick="fntDeleteRecord(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" data-id-sucursal="' . openssl_encrypt($arrData[$i]['sucursal_id'], METHODENCRIPT, KEY) . '" title= "Suspender Registro"><i class="fa-sharp fa-regular fa-regular fa-trash-can"></i> </button>';
                }

                $btnView = ($this->permisosMod['r']) ? $btnView : '';
                $btnEdit = ($this->permisosMod['u']) ? $btnEdit : '';
                $btnDelete = ($this->permisosMod['d']) ? $btnDelete : '';
                if ($arrData[$i]['titular'] == 0) {
                    $arrData[$i]['options'] = '<div class="d-flex ">' . $btnView . ' ' . $btnEdit  . ' ' . $btnDelete . '</div>';
                } else {
                    $arrData[$i]['options'] = '';
                }
            }

            /*-------------------------------------------
            [ Retorna respuesta json_encode ]*/
            die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Obtiene los datos del registro seleccionado por Metodo POST
     * 
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * * data (array). En caso de ser exitoso, el elemento data contiene la información solicitada.
     * * dataEspecialidad (array). En caso de ser exitoso, el elemento dataEspecialidad contiene la información solicitada.
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
            $this->permisosMod = $arrPermisos[MOD_USUARIOS];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
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
            [ Asignar variables de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            $record_id = intval(openssl_decrypt($record_id, METHODENCRIPT, KEY));
            if ($record_id == 0) {
                die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            }


            /*-------------------------------------------
            [ Instanciar Modelo y asignar datos al modelo. ]*/
            $class_model = new UsuariosModel;
            $class_model->setEmpresaId($empresa_id);
            $class_model->setSucursalId($sucursal_id);
            $class_model->setId($record_id);

            /*-------------------------------------------
            [ Obtiene array con los datos obtenidos ]*/
            $arrData = $class_model->selectRecord($class_model);
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
                $arrResponse['data']['id'] =   openssl_encrypt($record_id, METHODENCRIPT, KEY);
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Code_Error usr_1001. Error desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Guardar/Actualizar datos de Registro seleccionado
     * 
     * @return string json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
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
            $this->permisosMod = $arrPermisos[MOD_ROLES];
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
            $email = strClean($_POST['email']);
            $usuario = strClean($_POST['usuario']);
            $telefono = strClean($_POST['telefono']);
            $nombre = strClean($_POST['nombre']);
            $paterno = strClean($_POST['paterno']);
            $materno = strClean($_POST['materno']);
            $rol_id = intval($_POST['rol']);

            $password = strClean($_POST['password']);
            $password_confirm = strClean($_POST['password_confirm']);


            /*-------------------------------------------
            [ Procesos auxiliares ] 
            ------------------------------------------- */

            /*-------------------------------------------
            [ Valida Contraseñas ]*/
            if ($password !== $password_confirm) {
                die(json_encode(getResponse("Las contraseñas no coinciden"), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            $record_id = intval(strClean(openssl_decrypt($record_id, METHODENCRIPT, KEY)));

            /*-------------------------------------------
            [ Valida formulario ]*/
            (trim($nombre) == '') ? die(json_encode(getResponse("Debe indicar nombre"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($email) == '') ? die(json_encode(getResponse("Debe indicar email"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($paterno) == '') ? die(json_encode(getResponse("Debe indicar paterno"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($usuario) == '') ? die(json_encode(getResponse("Debe indicar usuario"), JSON_UNESCAPED_UNICODE)) : "";
            ($rol_id == 0) ? die(json_encode(getResponse("Debe indicar rol"), JSON_UNESCAPED_UNICODE)) : "";
            (trim($telefono) == '') ? die(json_encode(getResponse("Debe indicar telefono"), JSON_UNESCAPED_UNICODE)) : "";
            ($sucursal_id == 0) ? die(json_encode(getResponse("Debe indicar sucursal"), JSON_UNESCAPED_UNICODE)) : "";


            /*-------------------------------------------
            [ Instanciar Modelo y asignar las variables al Modelo ]*/
            $class_model = new UsuariosModel;
            $class_model->setId($record_id);
            $class_model->setUsuario($usuario);
            $class_model->setActivo(1);
            $class_model->setTheme('light-mode');
            $class_model->setEmpresaId($empresa_id);
            $class_model->setSucursalId($sucursal_id);
            $class_model->setUsuarioIdCreated($usuario_id_register);
            $class_model->setUsuarioIdUpdated($usuario_id_register);


            /*-------------------------------------------
            [ Generar Arreglo Datos Generales ]*/
            $datos_generales = array();
            $datos_generales['nombre'] = $nombre;
            $datos_generales['paterno'] = $paterno;
            $datos_generales['materno'] = $materno;
            $datos_generales['email'] = $email;
            $datos_generales['telefono'] = $telefono;
            $datos_generales['rol_id'] = $rol_id;
            $datos_generales['titular'] = 0;
            $datos_generales['sucursal_id'] = $sucursal_id;
            $datos_generales['lista_precios_id'] = 1;
            $class_model->setDatosGenerales($datos_generales);

            /*-------------------------------------------
            [ Generar Arreglo Datos Domiclio ]*/
            $datos_domicilio_envio = array();
            $datos_domicilio_envio['calle'] = '';
            $datos_domicilio_envio['num_exterior'] = '';
            $datos_domicilio_envio['num_interior'] = '';
            $datos_domicilio_envio['colonia'] = '';
            $datos_domicilio_envio['ciudad'] = '';
            $datos_domicilio_envio['estado'] = '';
            $datos_domicilio_envio['cp'] = '';
            $datos_domicilio_envio['pais'] = '';
            $datos_domicilio_envio['referencias'] = '';
            $datos_domicilio_envio['activo'] = 1;
            $datos_domicilio_envio['predeterminado'] = 1;
            $class_model->setDomicilioEnvio($datos_domicilio_envio);

            /*-------------------------------------------
            [ Generar Arreglo Datos Facturacion ]*/
            $datos_facturacion = array();
            $datos_facturacion['rfc'] = '';
            $datos_facturacion['razon_social'] = '';
            $datos_facturacion['codigo_postal'] = '';
            $datos_facturacion['regimen'] = '';
            $datos_facturacion['uso_cfdi'] = '';
            $datos_facturacion['email'] = '';
            $datos_facturacion['activo'] = 1;
            $class_model->setDatosFacturacion($datos_facturacion);


            if ($record_id == 0) {

                /*==========================================
                [ Crear Nuevo Registro ]*/

                /*-------------------------------------------
                [ Se aignan las variables especificas correspondientes al Insert ]*/
                $class_model->setActivo(1);
                $class_model->setUsuarioIdCreated($usuario_id_register);

                /*-------------------------------------------
                [ Valida Permisos. ]*/
                if (!$this->permisosMod['c']) {
                    die(json_encode(getResponse('No cuenta con privilegios suficientes para realizar esta accion.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Valida si el Registro ya existe ]*/
                $existe = $class_model->validInsertExist($class_model);
                if ($existe == true) {
                    die(json_encode(getResponse('El registro ya existe.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Hash password ]*/
                $password_val = $password === '' ? passGenerator() : $password;
                $password_encrypt = hash("SHA256", $password_val);
                $class_model->setPass($password_encrypt);

                /*-------------------------------------------
                [ Token ]*/
                $token = $email . KEY_TOKEN_USER . $password_val;
                $token = hash('SHA256', $token);
                $class_model->setToken($token);

                /*-------------------------------------------
                [ Inserta el Registro si pasa las validaciones. ]*/
                $response = $class_model->insertRecord($class_model);

                /*-------------------------------------------
                [ Evalúa respuesta  ]*/
                if (!$response) {
                    die(json_encode(getResponse('Codigo Error: ' . self::prefijo_msj_error . '_1002, Error al crear el registro, intente nuevamente.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Respuesta Exitosa  ]*/
                $arrResponse = getResponse('Registro creado exitosamente.', "ok");

                /*-------------------------------------------
                [ Enviar Correo de Bienvenida  ]*/
                $config_model = new ConfiguracionModel;
                $config_model->setEmpresaId($empresa_id);
                $config = $config_model->selectRecord($config_model);
                $telefono_contacto = $config['telefono_contacto'];
                $email_contacto = $config['email_contacto'];

                $nombre_usuario = $nombre . ' ' .  $paterno . ' ' . $materno;
                $datos_usuario = array(
                    'empresa_id' => $empresa_id,
                    'nombre_usuario' => $nombre_usuario,
                    'email_destino' => $email,
                    'email' => $email,
                    'usuario' => $usuario,
                    'password' => $password_val,
                    'attachment' => '',
                    'asunto' => 'Cuenta de Acceso al Panel de Administración',
                    'telefono_contacto' => $telefono_contacto,
                    'email_contacto' => $email_contacto
                );
                $sendEmail = sendEmailPHPMailer($datos_usuario, 'bienvenido_sistema');
                $arrResponse['email_enviado'] = 'si';
                if (!$sendEmail) {
                    $arrResponse['email_enviado'] = 'no';
                    getLoggerSystem()->error('No se envió correo de bienvenida', $datos_usuario);
                }
            } else {

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
                [ Valida si el registro ya existe ]*/
                $existe = $class_model->validUpdateExistRecord($class_model);
                if ($existe == true) {
                    die(json_encode(getResponse('El registro que desea actualizar, ya existe.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Hash password ]*/
                if ($password != '') {
                    $password_encrypt = hash("SHA256", $password);
                    $class_model->setPass($password_encrypt);
                } else {
                    $class_model->setPass('');
                }

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
                $arrResponse['email_enviado'] = 'no aplica';
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Codigo Error: ' . self::prefijo_msj_error . '_1005, Error Desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Estatus de Registro
     * Modifica el status para activar o desactivar de un registro determinado
     * 
     * 
     * @return string json_encode($arrResponse, JSON_UNESCAPED_UNICODE)
     * $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * 
     * 
     * 
     */
    public function setEstatusRecord()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_USUARIOS];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Se reciben Datos del POST, y se limpian los datos recibidos ]*/
            $record_id = strClean($_POST['record_id']);
            $estatus = intval(strClean($_POST['estatus']));
            // $sucursal_id = strClean($_POST['sucursal_id']);

            /*-------------------------------------------
            [ Valida Datos de Form ]*/
            if ($record_id == '') {
                die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Asignar varibales de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $usuario_id_register = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Se desencriptan datos ]*/
            $record_id = intval(strClean(openssl_decrypt($record_id, METHODENCRIPT, KEY)));

            /*-------------------------------------------
            [ Instanciar Modelo ]*/
            $class_model = new UsuariosModel;
            $class_model->setId($record_id);
            $class_model->setActivo($estatus);
            $class_model->setEmpresaId($empresa_id);
            $class_model->setUsuarioIdUpdated($usuario_id_register);

            /*-------------------------------------------
            [ Actualiza estatus del Registro seleccionado. ]*/
            $response = $class_model->updateEstatusRecord($class_model, $usuario_id_register);

            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if ($response) {
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
            die(json_encode(getResponse('Error al guardar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: USR-101</span>.'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }


    //*==================================================================
    // [ CAMBIAR CONSTRASEÑA ]*/

    /**
     * Carga la Vista Cambiar Contraseña. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function cambiarpassword(): void
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_CONTRASENA];

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_CONTRASENA);

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
            $data['menu'] = MOD_CONTRASENA;

            //Header
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] = "panel, administracion";

            //Form Principal
            $data['icon_form_title'] = $menu['icon_form_title'];
            $data['page_form_title'] = $menu['icon_form_title'] . $menu['form_title'];

            //Breadcrump
            $data['page_breadcrumb'] = $menu['breadcrumb'];

            //Card Principal
            $data['page_card_title'] =  $menu['card_title'];
            $data['page_card_description'] = $menu['descripcion'];

            //Datos de Usuario en Sesión.
            $data['empresa_id'] = $this->session->get('empresa_id');
            $data['sucursal_id'] = $this->session->get('sucursal_id');
            $data['theme'] = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email'] = $this->session->get('email');
            $data['usuario']['rol'] = $this->session->get('rol');

            //JS Principal
            $data['page_functions_js'] = $menu['js'];

            //Call Vista
            $this->views->getView($this, $menu['views'], $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Actualizar Contraseña
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde = '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * 
     * @return json json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     */
    public function actualizarPassword()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_ROLES];
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
            $usuario_id = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Se aignan las variables del POST y se limpian los datos recibidos ]*/
            $password = strclean($_POST['password']);
            $confirmar_password = strclean($_POST['confirmar_password']);

            /*-------------------------------------------
            [ Se valida que las contraseñas coincidan ]*/
            if ($password != $confirmar_password) {
                die(json_encode(getResponse('Las contraseñas no coinciden, Verifique.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Instanaciar Modelo y asignar las variables al Modelo ]*/
            $class_model = new UsuariosModel;
            $class_model->setId($usuario_id);
            $password_encrypt = hash("SHA256", $confirmar_password);
            $class_model->setPass($password_encrypt);

            /*-------------------------------------------
            [ Actualiza password ]*/
            $response =  $class_model->updatePassword($class_model);

            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if (!$response) {
                die(json_encode(getResponse('Error al actualizar la contraseña, intente nuevamente.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Respuesta Exitosa  ]*/
            $arrResponse = getResponse('Contraseña actualizada exitosamente', 'ok');
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Codigo Error: ' . self::prefijo_msj_error . '_1005, Error Desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
