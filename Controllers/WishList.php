<?php

/**
 * Controlador WishList 
 */
class WishList extends Controllers
{

    private $session;
    private $permisosMod;

    /**
     * Método Constructor de Controlador WishList.
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
     * Carga la Vista WishList. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function WishList()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_WISH_LIST];

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_WISH_LIST);

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
            $data['menu'] = MOD_WISH_LIST;
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
            $this->permisosMod = $arrPermisos[MOD_WISH_LIST];
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
            $class_model = new WishListModel;
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
            $this->permisosMod = $arrPermisos[MOD_WISH_LIST];
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
            $class_model = new WishListModel;
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
            $this->permisosMod = $arrPermisos[MOD_WISH_LIST];

            $arrResponse = array();

            /*-------------------------------------------
            [ Se aignan las variables de Sesión ]*/
            $sucursal_id = $this->session->get('sucursal_id');
            $empresa_id = $this->session->get('empresa_id');
            $usuario_id_register = $this->session->get('usuario_id');


            /*-------------------------------------------
            [ Se aignan las variables del POST ]*/
            $record_id = $_POST['id'];
            $name = strclean($_POST['name']);
            $descripcion = strclean($_POST['descripcion']);

            $name_lowercase = strtolower($name);
            $slug = str_replace(" ", "_", $name_lowercase);



            /*-------------------------------------------
            [ Valida formulario ]*/
            if ($name == '') {
                die(json_encode(getResponse("Debe indicar nombre"), JSON_UNESCAPED_UNICODE));
            }
            if ($descripcion == '') {
                die(json_encode(getResponse("Debe indicar Descripcion"), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            $record_id = intval(strClean(openssl_decrypt($record_id, METHODENCRIPT, KEY)));


            /*-------------------------------------------
            [ _FILES ]*/
            $files =  $_FILES;

            if ($_FILES['adjunto']['error'] == 0) {
                $arrParams = explode('.', $_FILES['adjunto']['name']);
                $index = count($arrParams) - 1;
                $file_extension = strClean($arrParams[$index]);

                $file_name = $_FILES['adjunto']['name'];
                $n = $file_name . date('YmdHis');
                $adjunto = encode($n) . '.' . trim($file_extension);
            } else {
                $adjunto = '';
            }

            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $class_model = new WishListModel;
            $class_model->setId($record_id);
            $class_model->setName($name);
            $class_model->setDescripcion($descripcion);
            $class_model->setSlug($slug);
            $class_model->setImage($adjunto);
            $class_model->setEmpresaId($empresa_id);
            $class_model->setSucursalId($sucursal_id);
            $class_model->setUsuarioIdCreated($usuario_id_register);
            $class_model->setFiles($files);

            if ($record_id == 0) {

                /*==========================================
                [ Crear Nuevo Registro ]*/

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
                $existe = $class_model->validInsertExist($name);
                if ($existe == true) {
                    die(json_encode(getResponse('El registro ya existe.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Inserta el Registro si pasa las validaciones. ]*/
                $response = $class_model->insertRecord($class_model);
                if ($response == true) {
                    $arrResponse = getResponse('Registro creado exitosamente.', "ok");
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

                /*-------------------------------------------
                [ Valida si el rol ya existe ]*/
                $existe = $class_model->validUpdateExistRecord($name, $record_id);
                if ($existe == true) {
                    die(json_encode(getResponse('El registro que desea actualizar, ya existe.'), JSON_UNESCAPED_UNICODE));
                }

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
            $this->permisosMod = $arrPermisos[MOD_WISH_LIST];
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
            $class_model = new WishListModel;
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
            $this->permisosMod = $arrPermisos[MOD_WISH_LIST];
            if (!$this->permisosMod['r']) {
                die($htmlOptions);
            }

            $htmlOptions .= '<option value="" selected="selected" disabled>Seleccione una opcion</option>';
            $rol_model = new WishListModel;
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
