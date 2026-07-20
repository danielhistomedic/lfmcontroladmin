<?php

/**
 * Controlador Tutoriales 
 */
class Tutoriales extends Controllers
{

    private $session;
    private $permisosMod;

    /**
     * Método Constructor de Controlador Tutoriales.
     * Inicializa Controllers::__construct.
     * Inicializa y valida datos de session.
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        [ Validación de Sesion ]*/
        $this->session = new Session();
        if ($this->session->getStatus() === false || empty($this->session->get('email'))) {
            $this->session->redirect('login');
        }
    }

    // =====================================================
    // [ Registro de Tutoriales ]
    // =====================================================

    /**
     * Carga la Vista.
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function TutorialesAlta()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_ALTA];

            // Valida si tiene acceso a la pagina.
            if (!$this->permisosMod['r']) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_TUTORIALES_ALTA);

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion = $configuracion_model->selectConfiguracion();
            $data['configuracion'] = $configuracion;

            // Asigna los permisos de Módulo y SideBar
            $data['permisos'] = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;


            //Id de Menu para script de Pemisos de Boton exportar a Excel
            $data['menu'] = MOD_TUTORIALES_ALTA;

            //Header
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] = "panel, administracion, hdsolutions";

            //Form Principal
            $data['page_form_title'] = "<i class='fa-brands fa-youtube fa-fw text-primary text-shadow-info'></i> Registro de Tutoriales";

            //Breadcrump
            $data['page_breadcrumb'] = "Registro";

            //Card Principal
            $data['page_card_title'] =  $menu['card_title'];
            $data['page_card_description'] = $menu['descripcion'];

            //JS Principal
            $data['page_functions_js'] = "tutoriales_alta.js";

            //Call Vista
            $this->views->getView($this, "tutoriales_alta", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Obtiene la lista de Registros para llenar la tabla en DataTable.net
     * 
     * @return string $arrData
     * json_encode($arrData, JSON_UNESCAPED_UNICODE)
     * 
     */
    public function getAllDatatable()
    {

        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_ALTA];
            if (!$this->permisosMod['r']) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeInLeft";

            /*-------------------------------------------
            [ Obtiene el array con la lista de catálogo de roles ]*/
            $class_model = new TutorialesModel;
            $arrData = $class_model->selectAll();

            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            for ($i = 0; $i < count($arrData); $i++) {

                // { "data": "titulo" },
                $arrData[$i]['titulo'] = '<a href="' . assets() . '/files/videos/' . $arrData[$i]['archivo'] . '" target="_blank">' . $arrData[$i]['titulo'] . '</a>';


                // { "data": "activo" },
                $activo = $arrData[$i]['activo'];
                if ($activo == 1) {
                    $arrData[$i]['activo'] = '<div><span class="badge bg-success-gradient  me-1 mb-1 mt-1">Activo</span></div>';
                } else {
                    $arrData[$i]['activo'] = '<div><span class="badge bg-danger-gradient  me-1 mb-1 mt-1">Inactivo</span></div>';
                }

                // { "data": "options" }
                $btnView = '';
                $btnEdit = '';
                $btnDelete = '';

                $btnView = '<button style="box-shadow: none!important; width: 40px;" class="btn btn-sm btn-outline-info d-flex justify-content-center align-items-center view" data-animation="' . $data_animation . '" onclick="fntView(this)" data-id="' . $arrData[$i]['id'] . '" title= "Ver Detalle de Registro">
                                <i class="fa-regular fa-eye fs-12"></i>
                            </button>';

                if ($activo == 1) {
                    $btnEdit = ' <button style="margin-left: 3px; box-shadow: none!important; width: 40px;" class="btn btn-sm btn-outline-secondary d-flex justify-content-center align-items-center create_edit" data-animation="' . $data_animation . '" onclick="fntEdit(this)" data-id="' . $arrData[$i]['id'] . '" title= "Editar Registro">
                                      <i class="fa-regular fa-pencil-alt fs-12"></i>
                                  </button>';
                } else {
                    $btnEdit = ' <button style="margin-left: 3px; box-shadow: none!important; width: 40px;" class="btn btn-sm btn-outline-success d-flex justify-content-center align-items-center" onclick="fntActive(this)" data-id="' . $arrData[$i]['id'] . '" title= "Reactivar Registro">
                                    <i class="fa-regular fa-arrow-rotate-left fs-12"></i>
                                 </button>';
                }
                if ($activo == 1) {
                    $btnDelete = '<button style="margin-left: 3px; box-shadow: none!important; width: 40px;" class="btn btn-sm btn-outline-danger d-flex justify-content-center align-items-center" onclick="fntDelete(this)" data-id="' . $arrData[$i]['id'] . '" title= "Eliminar Registro">
                                    <i class="fa-regular fa-trash-can fs-12"></i>
                                </button>';
                }

                $btnView = ($this->permisosMod['r']) ? $btnView : '';
                $btnEdit = ($this->permisosMod['u']) ? $btnEdit : '';
                $btnDelete = ($this->permisosMod['d']) ? $btnDelete : '';

                $arrData[$i]['options'] = '<div class="d-flex justify-content-center align-items-center">' . $btnView . ' ' . $btnEdit  . ' ' . $btnDelete . '</div>';
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene los datos de Registro seleccionado.
     * 
     * @param int $id 
     * Identificador de Registro
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * * data (array). En caso de ser exitoso, el elemento data contiene la información solicitada.
     * * dataEspecialidad (array). En caso de ser exitoso, el elemento dataEspecialidad contiene la información solicitada.
     * 
     * @return json json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     */
    public function get(int $id)
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_ALTA];
            if (!$this->permisosMod['r']) {
                die(json_encode(getResponse('Acceso restringido.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Asignar y Limpiar parametros recibidos ]*/
            $record_id = intval(strClean($id));

            /*-------------------------------------------
            [ Valida datos de post ]*/
            if ($record_id == 0) {
                die(json_encode(getResponse('Debe seleccionar un registro.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Instanciar Modelo ]*/
            $class_model = new TutorialesModel;

            /*-------------------------------------------
            [ Obtiene array con los datos del registro ]*/
            $arrData = $class_model->selectRecord($record_id);
            if (empty($arrData)) {
                die(json_encode(getResponse('Datos no encontrados.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Devulve resultados encontrados. ]*/
            $arrRespuesta = getResponse('Datos encontrados', "ok", false);
            $arrRespuesta['data'] = $arrData;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Datos no encontrados.'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrRespuesta, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene la lista registros para llenar un Select
     * 
     * @return string $htmlOptions
     */
    public function getAllSelect(): string
    {

        try {

            $htmlOptions = '';

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_ALTA];
            if (!$this->permisosMod['r']) {
                die($htmlOptions);
            }

            /*-------------------------------------------
            [ Instanciar Modelo ]*/
            $class_model = new TutorialesModel;


            $arrData = $class_model->selectAll();
            if (empty($arrData)) {
                die($htmlOptions);
            }

            for ($i = 0; $i < count($arrData); $i++) {
                if ($arrData[$i]['activo'] == 1) {
                    $htmlOptions .= '<option value="' . $arrData[$i]['id'] . '">' . $arrData[$i]['titulo'] . '</option>';
                }
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        die($htmlOptions);
    }

    /**
     * Guardar datos de Registro
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde = '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * 
     * @return json json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     */
    public function set()
    {

        try {


            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_ALTA];

            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $id = intval(strclean($_POST['id_record']));
            $titulo = strclean($_POST['titulo']);
            $menu = strclean($_POST['menu']);
            $submenu = strclean($_POST['submenu']);
            $descripcion = strclean($_POST['descripcion']);

            $files =  $_FILES;
            $name = $_FILES['adjunto_file']['name'];
            $n = $name . date('YmdHis');
            $archivo = encode($n) . '.mp4';

            /*-------------------------------------------
            [ Validar Datos Recibidos ]*/
            // (trim($archivo) == '') ? die(json_encode(getResponse('Debe indicar archivo.'), JSON_UNESCAPED_UNICODE)) : false;
            (trim($titulo) == '') ? die(json_encode(getResponse('Debe indicar titulo.'), JSON_UNESCAPED_UNICODE)) : false;
            (trim($menu) == '') ? die(json_encode(getResponse('Debe indicar menu.'), JSON_UNESCAPED_UNICODE)) : false;
            (trim($submenu) == '') ? die(json_encode(getResponse('Debe indicar submenu.'), JSON_UNESCAPED_UNICODE)) : false;
            (trim($descripcion) == '') ? die(json_encode(getResponse('Debe indicar descripcion.'), JSON_UNESCAPED_UNICODE)) : false;
            if ($id === 0) {
                (trim($name) == '') ? die(json_encode(getResponse('Debe seleccionar archivo a cargar.'), JSON_UNESCAPED_UNICODE)) : false;
            }

            /*-------------------------------------------
            [ Se asigan variables de Sesion. ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');
            $usuario_id_register = $this->session->get('usuario_id');


            /*-------------------------------------------
            [ Instanciar Modelo ]*/
            $class_model = new TutorialesModel;
            $class_model->setId($id);
            $class_model->setArchivo($archivo);
            $class_model->setVideoAdjunto($_FILES);
            $class_model->setTitulo($titulo);
            $class_model->setMenu($menu);
            $class_model->setSubmenu($submenu);
            $class_model->setDescripcion($descripcion);
            $class_model->setActivo(1);

            $class_model->setEmpresaId($empresa_id);
            $class_model->setSucursalId($sucursal_id);
            $class_model->setUsuarioIdCreated($usuario_id_register);
            $class_model->setUsuarioIdUpdated($usuario_id_register);

            /*-------------------------------------------
            [ Actualizar Registro de Unidad Medica si pasa las validaciones. ]*/
            if ($id == 0) {

                if (!$this->permisosMod['c']) {
                    die(json_encode(getResponse('No cuenta con los suficientes privilegios para realizar esta acción.'), JSON_UNESCAPED_UNICODE));
                }

                /*-------------------------------------------
                [ Valida Registro antes de insertar ]*/
                $result = $class_model->validaExistRecord($titulo);
                if ($result == true) {
                    /*-------------------------------------------
                    [ Retorna anticipado respuesta json_encode ]*/
                    die(json_encode(getResponse('El Titulo que desea realizar ya existe, verifique. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: Tut101</span>.'), JSON_UNESCAPED_UNICODE));
                }

                $response = $class_model->insertRecord($class_model);
                if ($response == false) {
                    die(json_encode(getResponse('Error al realizar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: Tut101a</span>.'), JSON_UNESCAPED_UNICODE));
                }
            } else {

                if (!$this->permisosMod['u']) {
                    die(json_encode(getResponse('No cuenta con los suficientes privilegios para realizar esta acción.'), JSON_UNESCAPED_UNICODE));
                }

                $result = $class_model->validExistRecordUpdate($titulo, $id);
                if ($result == true) {
                    die(json_encode(getResponse('El Titulo que desea realizar ya existe, verifique. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: Tut102</span>.'), JSON_UNESCAPED_UNICODE));
                }

                $tutorial_model = new TutorialesModel;
                $tutorial = $tutorial_model->selectRecord($id);
                $class_model->setArchivo($tutorial['archivo']);

                $response = $class_model->updateRecord($class_model);
                if ($response == false) {
                    die(json_encode(getResponse('Error al actualizar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: Tut102a</span>.'), JSON_UNESCAPED_UNICODE));
                }
            }


            /*-------------------------------------------
            [ Respuesta Exitosa  ]*/
            $arrResponse = getResponse('Registro realizado exitosamente', 'ok', true);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Error al actualizar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CG100</span>.'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Elminar Registro
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * 
     * @return json json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     */
    public function delete()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_ALTA];
            if (!$this->permisosMod['d']) {
                die(json_encode(getResponse('No cuenta con los suficientes privilegios para realizar esta acción.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Se aignan las variables de Sesión ]*/
            $usuario_id_register = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $id = intval($_POST['id']);


            /*-------------------------------------------
            [ Validar Datos Recibidos ]*/
            if ($id == 0) {
                die(json_encode(getResponse('Debe seleccionar un registro.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Instanciar Modelo. ]*/
            $class_model = new TutorialesModel;
            $class_model->setId($id);
            $class_model->setActivo(0);
            $class_model->setUsuarioIdUpdated($usuario_id_register);
            $response = $class_model->deleteRecord($class_model);


            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if ($response == false) {
                die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: Tut104</span>.'), JSON_UNESCAPED_UNICODE));
            }


            $arrResponse = getResponse('Registro Eliminado exitosamente.', 'ok', true);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: Tut105</span>.'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Activar Registro 
     * 
     * @response $arrResponse, donde:
     * * respuesta (string). Valores 'ok'/'error'. 'ok' en caso de que la respuesta sea existosa repsonde '', caso contrario = 'error'.
     * * mostrar_mensaje (bool). Valores true/false en caso de que se desee mostrar modal con los resultados de la respuesta.
     * * tiempo (int). Total de segundos que desea que se muestre el mensaje modal de la respuesta.
     * * mensaje (string). Contiene el mensaje que aparecerá en el modal.
     * 
     * @return json json_encode($arrResponse, JSON_UNESCAPED_UNICODE).
     */
    public function active()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_ALTA];
            if (!$this->permisosMod['d']) {
                die(json_encode(getResponse('No cuenta con los suficientes privilegios para realizar esta acción.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Se aignan las variables de Sesión ]*/
            $usuario_id_register = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $id = intval($_POST['id']);


            /*-------------------------------------------
            [ Validar Datos Recibidos ]*/
            if ($id == 0) {
                die(json_encode(getResponse('Debe seleccionar un registro.'), JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Instanciar Modelo. ]*/
            $class_model = new TutorialesModel;
            $class_model->setId($id);
            $class_model->setActivo(0);
            $class_model->setUsuarioIdUpdated($usuario_id_register);
            $response = $class_model->activeRecord($class_model);


            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if ($response == false) {
                die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: Tut106</span>.'), JSON_UNESCAPED_UNICODE));
            }


            $arrResponse = getResponse('Registro Reactivado exitosamente.', 'ok', true);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: Tut107</span>.'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    // =====================================================
    // [ Consulta de Tutoriales ]
    // =====================================================

    /**
     * Carga la Vista.
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function TutorialesBuscar()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_BUSCAR];

            // Valida si tiene acceso a la pagina.
            if (!$this->permisosMod['r']) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_TUTORIALES_BUSCAR);

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion = $configuracion_model->selectConfiguracion();
            $data['configuracion'] = $configuracion;

            // Asigna los permisos de Módulo y SideBar
            $data['permisos'] = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;


            //Id de Menu para script de Pemisos de Boton exportar a Excel
            $data['menu'] = MOD_TUTORIALES_BUSCAR;

            //Header
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] = "panel, administracion, hdsolutions";

            //Form Principal
            $data['page_form_title'] = "<i class='fa-brands fa-youtube fa-fw text-primary text-shadow-info'></i> Tutoriales";

            //Breadcrump
            $data['page_breadcrumb'] = "Consultar";

            //Card Principal
            $data['page_card_title'] =  $menu['card_title'];
            $data['page_card_description'] = $menu['descripcion'];

            //JS Principal
            $data['page_functions_js'] = "tutoriales_buscar.js";

            //Call Vista
            $this->views->getView($this, "tutoriales_buscar", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Obtiene la lista registros para llenar un Select con los datos del menu
     * 
     * @return string $htmlOptions
     */
    public function getSelectMenu(): string
    {

        try {

            $htmlOptions = '';

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_BUSCAR];
            if (!$this->permisosMod['r']) {
                die($htmlOptions);
            }

            /*-------------------------------------------
            [ Instanciar Modelo ]*/
            $class_model = new TutorialesModel;


            $arrData = $class_model->selectAllMenu();
            if (empty($arrData)) {
                die($htmlOptions);
            }

            // $htmlOptions .= '<option value="Todos">Todos</option>';

            for ($i = 0; $i < count($arrData); $i++) {
                $htmlOptions .= '<option value="' . $arrData[$i]['menu'] . '">' . $arrData[$i]['menu'] . '</option>';
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        die($htmlOptions);
    }

    /**
     * Obtiene la lista registros para llenar un Select con los datos del sub menu
     * 
     * @return string $htmlOptions
     */
    public function getSelectSubmenu($menu): string
    {

        try {

            $htmlOptions = '';

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_BUSCAR];
            if (!$this->permisosMod['r']) {
                die($htmlOptions);
            }

            /*-------------------------------------------
            [ Instanciar Modelo ]*/
            $class_model = new TutorialesModel;


            $arrData = $class_model->selectAllSubmenu($menu);
            if (empty($arrData)) {
                die($htmlOptions);
            }

            $htmlOptions .= '<option value="Todos">Todos</option>';
            for ($i = 0; $i < count($arrData); $i++) {
                $htmlOptions .= '<option value="' . $arrData[$i]['submenu'] . '">' . $arrData[$i]['submenu'] . '</option>';
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        die($htmlOptions);
    }

    /**
     * Obtiene la lista de Registros para llenar la tabla en DataTable.net
     * 
     * @return string $arrData
     * json_encode($arrData, JSON_UNESCAPED_UNICODE)
     * 
     */
    public function getFiltro($filtro)
    {

        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_BUSCAR];
            if (!$this->permisosMod['r']) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }


            $filtro = strClean($filtro);


            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeInLeft";

            /*-------------------------------------------
            [ Obtiene el array con la lista de catálogo de roles ]*/
            $class_model = new TutorialesModel;
            $arrData = $class_model->selectAllFiltro($filtro);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene la lista de Registros relacionados al Menu Principal
     * 
     * @return string $arrData
     * json_encode($arrData, JSON_UNESCAPED_UNICODE)
     * 
     */
    public function getFiltroMenu($filtro)
    {

        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_BUSCAR];
            if (!$this->permisosMod['r']) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }


            $filtro = strClean($filtro);


            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeInLeft";

            /*-------------------------------------------
            [ Obtiene el array con la lista de catálogo de roles ]*/
            $class_model = new TutorialesModel;
            $arrData = $class_model->selectAllFiltroMenu($filtro);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene la lista de Registros relacionados al Menu Principal
     * 
     * @return string $arrData
     * json_encode($arrData, JSON_UNESCAPED_UNICODE)
     * 
     */
    public function getFiltroSubMenu($filtro)
    {

        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_TUTORIALES_BUSCAR];
            if (!$this->permisosMod['r']) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }

            $filtro = strClean($filtro);

            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeInLeft";

            /*-------------------------------------------
            [ Obtiene el array con la lista de catálogo de roles ]*/
            $class_model = new TutorialesModel;
            $arrData = $class_model->selectAllFiltroSubMenu($filtro);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
    }
}
