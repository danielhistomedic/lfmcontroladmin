<?php

/**
 * Controlador Personal 
 */
class Personal extends Controllers
{

    private $session;
    private $permisosMod;

    const prefijo_msj_error = "personal";

    /**
     * Método Constructor de Controlador Personal.
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
     * Carga la Vista Personal. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function Personal()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_RECURSOS_HUMANOS];

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_RECURSOS_HUMANOS);

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
            $data['usuario']['rol_id'] = $this->session->get('rol_id');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            //Id de Menu para script de Pemisos de Boton exportar a Excel
            $data['menu'] = MOD_RECURSOS_HUMANOS;

            //Header
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] =  $menu['tags'];

            //Form Principal
            $data['icon_form_title'] = $menu['icon_form_title'];
            $data['page_form_title'] = $menu['icon_form_title'] . $menu['form_title'];

            //Breadcrump
            $data['page_breadcrumb'] = '<span>Herramientas</span> <span class="mx-2">/</span><span>' . $menu['breadcrumb'] . '</span>';

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

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_RECURSOS_HUMANOS];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }
            $modal_permisos = $arrPermisos[MOD_PERMISOS];
            if (!$modal_permisos['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Variables. ]*/
            $arrData = array();
            $data_animation = "fadeIn";

            /*-------------------------------------------
            [ Asignar variables de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Instanciar Modelo y asignar variables ]*/
            $class_model = new PersonalModel;
            // $class_model->setEmpresaId($empresa_id);
            // $class_model->setSucursalId($sucursal_id);
            $arrData = $class_model->selectRecords($class_model);

            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            for ($i = 0; $i < count($arrData); $i++) {

                // { "data": "activo" },
                $activo = $arrData[$i]['iActivo'];
                if ($activo == 1) {
                    $arrData[$i]['activo'] = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-success"><i class="fa-sharp fa-regular fa-circle-check"></i> Activo</button>';
                } else {
                    $arrData[$i]['activo'] = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-danger"><i class="fa-sharp fa-regular fa-circle-xmark"></i> Baja</button>';
                }

                // { "data": "options" }
                $arrData[$i]['options'] = '';

                $btnView = '';
                $btnPermisosModal = '';
                $btnEdit = '';
                $btnDelete = '';
                $btnView = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-info panel_lista_registros" data-animation="' . $data_animation . '" onclick="fntView(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Ver Detalle de Registro"><i class="fa-sharp fa-regular fa-magnifying-glass "></i> </button>';

                // if ($activo == 1) {
                //     $btnEdit .= '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-secondary panel_crear_editar" data-animation="' . $data_animation . '" onclick="fntEdit(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Editar Registro"><i class="fa-sharp fa-regular fa-pen-to-square "></i> </button>';
                // } else {
                //     $btnEdit = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-success" onclick="fntActive(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Reactivar Registro"><i class="fa-sharp fa-regular fa-rotate-right "></i> </button>';
                // }

                // if ($activo == 1) {
                //     $btnDelete = '<button type="button" class="mb-1 mt-1 me-1 btn btn-xs btn-outline-danger" onclick="fntDeleteRecord(this)" data-id="' . openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY) . '" title= "Suspender Registro"><i class="fa-sharp fa-regular fa-regular fa-trash-can "></i> </button>';
                // }

                $btnView = ($this->permisosMod['r']) ? $btnView : '';
                $btnPermisosModal = ($modal_permisos['r']) ? $btnPermisosModal : '';
                $btnEdit = ($this->permisosMod['u']) ? $btnEdit : '';
                $btnDelete = ($this->permisosMod['d']) ? $btnDelete : '';
                $arrData[$i]['options'] = '' . $btnView;

                $arrData[$i]['id'] = openssl_encrypt($arrData[$i]['id'], METHODENCRIPT, KEY);
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
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_RECURSOS_HUMANOS];
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
            $class_model = new PersonalModel;
            // $class_model->setEmpresaId($empresa_id);
            // $class_model->setSucursalId($sucursal_id);
            $class_model->setIcvemedico($record_id);

            /*-------------------------------------------
            [ Obtiene array con los datos obtenidos ]*/
            $arrData = $class_model->selectRecord($class_model);
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
                $arrResponse['data']['id'] = openssl_encrypt($record_id, METHODENCRIPT, KEY);
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
     * Obtiene la lista de registros, para crear el html con los options que llenaran el Select correspondiente.
     * 
     * @return string $htmlOptions
     * 
     */
    public function getSelectRecords()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }

            /*-------------------------------------------
            [ Variables locales. ]*/
            $htmlOptions = '';

            /*-------------------------------------------
            [ Asignar varibales de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Generar HTML ]*/
            $htmlOptions .= '<option value="" selected="selected" disabled>Seleccione una opcion</option>';
            $class_model = new PersonalModel;
            // $class_model->setEmpresaId($empresa_id);
            $arrData = $class_model->selectRecords($class_model, true);
            if (count($arrData) > 0) {
                for ($i = 0; $i < count($arrData); $i++) {
                    if ($arrData[$i]['iActivo'] == 1) {
                        $htmlOptions .= '<option value="' . $arrData[$i]['id'] . '">' . $arrData[$i]['cNombre'] . ' ' . $arrData[$i]['cPriApellido'] . $arrData[$i]['cSegApellido']  . ' ' . '</option>';
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
          
    //*==================================================================
    // [ Dashboard ]*/

    /**
     * Obtiene los datos del grafico de Ventas por Periodo Diario
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
    public function getListPersonalChart()
    {

        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = !empty($arrPermisos[MOD_DASHBOARD_RH]) ? $arrPermisos[MOD_DASHBOARD_RH] : (!empty($arrPermisos[MOD_DASHBOARD]) ? $arrPermisos[MOD_DASHBOARD] : ['r' => 0]);
            if (!$this->permisosMod['r']) {
                $arrResponse['data'] = $arrData;
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeIn";
            $tipo_font = "fa-light";
            $arrFiltro = array();

            /*-------------------------------------------
            [ Limpiar y subdividir parametros recibidos. ]*/
            $departamento = strClean($_POST['departamento']);
            $estatus = strClean($_POST['estado']);
            if ($estatus == 'Activos') {
                $estatus = 1;
            } else {
                $estatus = 0;
            }

            /*-------------------------------------------
            [ Obtiene array con los datos del cliente ]*/
            $personal_model = new PersonalModel;
            $filtro = "WHERE t.control_rh = 1 and t.cdscareaafectada = '" . $departamento . "' and iActivo = '" . $estatus . "'";

            $arrData = $personal_model->selectPersonalFiltros($filtro);
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados para el gráfico'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Code Error repventas_1001. Error Desconocido para el gráfico'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
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
    public function getTotalEmpleados()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = !empty($arrPermisos[MOD_DASHBOARD_RH]) ? $arrPermisos[MOD_DASHBOARD_RH] : (!empty($arrPermisos[MOD_DASHBOARD]) ? $arrPermisos[MOD_DASHBOARD] : ['r' => 0]);
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            // /*-------------------------------------------
            // [ Se reciben Datos del POST, y se limpian los datos recibidos ]*/
            // $record_id = strClean($_POST['record_id']);

            // /*-------------------------------------------
            // [ Valida Datos de Form ]*/
            // if ($record_id == '') {
            //     die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            // }

            /*-------------------------------------------
            [ Asignar variables de sesión ]*/
            // $empresa_id = $this->session->get('empresa_id');
            // $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            // $record_id = intval(openssl_decrypt($record_id, METHODENCRIPT, KEY));
            // if ($record_id == 0) {
            //     die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            // }

            /*-------------------------------------------
            [ Instanciar Modelo y asignar datos al modelo. ]*/
            $class_model = new PersonalModel;
            // $class_model->setIcvemedico($record_id);

            /*-------------------------------------------
            [ Obtiene array con los datos obtenidos ]*/
            $arrData = $class_model->selectTotalEmpleados();
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
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
    public function getTotalEmpleadosActivos()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = !empty($arrPermisos[MOD_DASHBOARD_RH]) ? $arrPermisos[MOD_DASHBOARD_RH] : (!empty($arrPermisos[MOD_DASHBOARD]) ? $arrPermisos[MOD_DASHBOARD] : ['r' => 0]);
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            // /*-------------------------------------------
            // [ Se reciben Datos del POST, y se limpian los datos recibidos ]*/
            // $record_id = strClean($_POST['record_id']);

            // /*-------------------------------------------
            // [ Valida Datos de Form ]*/
            // if ($record_id == '') {
            //     die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            // }

            /*-------------------------------------------
            [ Asignar variables de sesión ]*/
            // $empresa_id = $this->session->get('empresa_id');
            // $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            // $record_id = intval(openssl_decrypt($record_id, METHODENCRIPT, KEY));
            // if ($record_id == 0) {
            //     die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            // }

            /*-------------------------------------------
            [ Instanciar Modelo y asignar datos al modelo. ]*/
            $class_model = new PersonalModel;
            // $class_model->setIcvemedico($record_id);

            /*-------------------------------------------
            [ Obtiene array con los datos obtenidos ]*/
            $arrData = $class_model->selectTotalEmpleadosActivos();
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
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
    public function getTotalEmpleadosBaja()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = !empty($arrPermisos[MOD_DASHBOARD_RH]) ? $arrPermisos[MOD_DASHBOARD_RH] : (!empty($arrPermisos[MOD_DASHBOARD]) ? $arrPermisos[MOD_DASHBOARD] : ['r' => 0]);
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            // /*-------------------------------------------
            // [ Se reciben Datos del POST, y se limpian los datos recibidos ]*/
            // $record_id = strClean($_POST['record_id']);

            // /*-------------------------------------------
            // [ Valida Datos de Form ]*/
            // if ($record_id == '') {
            //     die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            // }

            /*-------------------------------------------
            [ Asignar variables de sesión ]*/
            // $empresa_id = $this->session->get('empresa_id');
            // $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Dessencriptar datos ]*/
            // $record_id = intval(openssl_decrypt($record_id, METHODENCRIPT, KEY));
            // if ($record_id == 0) {
            //     die(json_encode(getResponse('Debe seleccionar un registro'), JSON_UNESCAPED_UNICODE));
            // }

            /*-------------------------------------------
            [ Instanciar Modelo y asignar datos al modelo. ]*/
            $class_model = new PersonalModel;
            // $class_model->setIcvemedico($record_id);

            /*-------------------------------------------
            [ Obtiene array con los datos obtenidos ]*/
            $arrData = $class_model->selectTotalEmpleadosBaja();
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
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
     * Obtiene los datos del grafico de Ventas por Periodo Diario
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
    public function getTotalEmpleadosPuestos()
    {

        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = !empty($arrPermisos[MOD_DASHBOARD_RH]) ? $arrPermisos[MOD_DASHBOARD_RH] : (!empty($arrPermisos[MOD_DASHBOARD]) ? $arrPermisos[MOD_DASHBOARD] : ['r' => 0]);
            if (!$this->permisosMod['r']) {
                $arrResponse['data'] = $arrData;
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeIn";
            $tipo_font = "fa-light";
            $arrFiltro = array();

            /*-------------------------------------------
            [ Limpiar y subdividir parametros recibidos. ]*/
            // $fecha_inicial = strClean($_POST['fecha_hora_ini']);
            // $fecha_final = strClean($_POST['fecha_hora_final']);

            /*-------------------------------------------
            [ Obtiene array con los datos del cliente ]*/
            $personal_model = new PersonalModel;
            // $personal_model->setFechaInicio($fecha_inicial);
            // $personal_model->setFechaFin($fecha_final);

            $arrData = $personal_model->selectTotalEmpleadosPuesto($personal_model);
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados para el gráfico'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Code Error repventas_1001. Error Desconocido para el gráfico'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene los datos del grafico de Ventas por Periodo Diario
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
    public function getTotalEmpleadosDepartamentos()
    {

        try {

            $arrData = array();

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = !empty($arrPermisos[MOD_DASHBOARD_RH]) ? $arrPermisos[MOD_DASHBOARD_RH] : (!empty($arrPermisos[MOD_DASHBOARD]) ? $arrPermisos[MOD_DASHBOARD] : ['r' => 0]);
            if (!$this->permisosMod['r']) {
                $arrResponse['data'] = $arrData;
                die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeIn";
            $tipo_font = "fa-light";
            $arrFiltro = array();

            /*-------------------------------------------
            [ Limpiar y subdividir parametros recibidos. ]*/
            // $fecha_inicial = strClean($_POST['fecha_hora_ini']);
            // $fecha_final = strClean($_POST['fecha_hora_final']);

            /*-------------------------------------------
            [ Obtiene array con los datos del cliente ]*/
            $personal_model = new PersonalModel;
            // $personal_model->setFechaInicio($fecha_inicial);
            // $personal_model->setFechaFin($fecha_final);

            $arrData = $personal_model->selectTotalEmpleadosDepartamentos($personal_model);
            if (empty($arrData)) {
                die(json_encode(getResponse('Lo sentimos, Datos no encontrados para el gráfico'), JSON_UNESCAPED_UNICODE));
            } else {
                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Code Error repventas_1001. Error Desconocido para el gráfico'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
