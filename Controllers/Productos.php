<?php

/**
 * Controlador Productos 
 */
class Productos extends Controllers
{

    private $session;
    private $permisosMod;

    /**
     * Método Constructor de Controlador Productos.
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
     * Carga la Vista Productos. 
     * Este método llama el metodo getview($controller, $view, $data=""), donde:
     * * $controller = $this, 
     * * $view = Nombre del archivo de la vista, 
     * * $data = Array con los siguentes datos:
     * 1) Datos de encabezado de la pagina html, 
     * 2) Archivo *.js correspondiente a la vista.
     * ** NOTA. El array $data puede ampliarse segun la necesidad de la vista.
     */
    public function Productos()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_CAT_PRODUCTOS];

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_CAT_PRODUCTOS);

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
            $data['menu'] = MOD_CAT_PRODUCTOS;
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
            $this->permisosMod = $arrPermisos[MOD_CAT_PRODUCTOS];
            $modal_permisos = $arrPermisos[MOD_PERMISOS];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Asignar varibales de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeIn";

            /*-------------------------------------------
            [ Obtiene el array con la lista de registros ]*/
            $class_model = new ProductosModel;
            $arrData = $class_model->selectRecords();

            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            for ($i = 0; $i < count($arrData); $i++) {

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


                //Categorias
                $categorias = '';
                $arrCategorias = $class_model->selectCategorias($arrData[$i]['id']);
                for ($j = 0; $j < count($arrCategorias); $j++) {
                    if ($j == 0) {
                        $categorias = $arrCategorias[$j]['categoria'];
                    } else {
                        $categorias = $categorias . ', ' . $arrCategorias[$j]['categoria'];
                    }
                }
                $arrData[$i]['categorias'] = $categorias;


                //Lista Precios
                $lista_precios = '';
                $arrPrecios = $class_model->selectListaPrecios($arrData[$i]['id']);
                for ($j = 0; $j < count($arrPrecios); $j++) {
                    $lista_precios .= '<i class="fa-sharp fa-solid fa-circle fa-2xs me-1"></i>' . $arrPrecios[$j]['name'] . ': ' . formatMoney($arrPrecios[$j]['precio']) . '<br>';
                }
                $arrData[$i]['lista_precios'] = $lista_precios;

                if ($arrData[$i]['oferta'] == 0) {
                    $arrData[$i]['oferta'] = 'NO';
                } else {
                    $arrData[$i]['oferta'] = 'SI';
                }
                $arrData[$i]['precio_oferta'] =  formatMoney($arrData[$i]['precio_oferta']);


                //Existencias
                $existencias = '';
                $arrPrecios = $class_model->selectExistenciasProducto($arrData[$i]['id'], $sucursal_id);
                for ($j = 0; $j < count($arrPrecios); $j++) {
                    $existencias .= '<i class="fa-sharp fa-solid fa-circle fa-2xs me-1"></i>' . $arrPrecios[$j]['sucursal'] . ': ' . $arrPrecios[$j]['cantidad'] . '<br>';
                }
                $arrData[$i]['existencias'] = $existencias;

                //Limite Minimo
                $limite_minimo = '';
                // $arrPrecios = $class_model->selectExistenciasProducto($arrData[$i]['id'], $sucursal_id);
                for ($j = 0; $j < count($arrPrecios); $j++) {
                    $limite_minimo .= '<i class="fa-sharp fa-solid fa-circle fa-2xs me-1"></i>' . $arrPrecios[$j]['sucursal'] . ': ' . $arrPrecios[$j]['limite_minimo'] . '<br>';
                }
                $arrData[$i]['limite_minimo'] = $limite_minimo;
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
            $this->permisosMod = $arrPermisos[MOD_CAT_PRODUCTOS];
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
            $class_model = new ProductosModel;
            $arrData = $class_model->selectRecord($record_id);
            if (empty($arrData)) {

                die(json_encode(getResponse('Lo sentimos, Datos no encontrados'), JSON_UNESCAPED_UNICODE));
            } else {

                $arrResponse = getResponse('Datos encontrados', 'ok', false);
                $arrResponse['data'] = $arrData;
                $arrResponse['dataId'] = openssl_encrypt($record_id, METHODENCRIPT, KEY);

                $imagenes = $class_model->selectImagenes($record_id);
                $arrResponse['data']['imagenes'] = $imagenes;

                $categorias = $class_model->selectCategorias($arrData['id']);
                $arrResponse['data']['categorias'] = $categorias;

                $precios = $class_model->selectPreciosProducto($arrData['id'], $sucursal_id);
                $arrResponse['data']['precios'] = $precios;

                $ventas_dirigidas = $class_model->selectVentasDirgidas($arrData['id']);
                $arrResponse['data']['ventas_dirigidas'] = $ventas_dirigidas;

                $ventas_cruzadas = $class_model->selectVentasCruzadas($arrData['id']);
                $arrResponse['data']['ventas_cruzadas'] = $ventas_cruzadas;

                $existencias = $class_model->selectExistenciasProducto($arrData['id'], $sucursal_id);
                $arrResponse['data']['existencias'] = $existencias;
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
            $this->permisosMod = $arrPermisos[MOD_CAT_PRODUCTOS];

            $arrResponse = array();

            /*-------------------------------------------
            [ Se aignan las variables de Sesión ]*/
            $sucursal_id = $this->session->get('sucursal_id');
            $empresa_id = $this->session->get('empresa_id');
            $usuario_id_register = $this->session->get('usuario_id');

            $post = $_POST;


            // oferta:"on"
            // precio_oferta:"55"

            /*-------------------------------------------
            [ Se aignan las variables del POST ]*/
            $record_id = $_POST['id'];
            $name = strclean($_POST['name']);
            $descripcion = strclean($_POST['descripcion']);
            $files_auxiliar = $_POST['files_auxiliar'];
            $files_auxiliar_id = $_POST['files_auxiliar_id'];
            $name_lowercase = strtolower($name);
            $slug = str_replace(" ", "_", $name_lowercase);

            $alterna = strclean($_POST['alterna']);
            $marca_id = strclean($_POST['marca_id']);
            $linea_producto_id = strclean($_POST['linea_producto_id']);
            $categorias = $_POST['categorias'];
            $sku = strclean($_POST['sku']);
            $precios = $_POST['precios'];
            $lista_precios_id = $_POST['lista_precios_id'];

            foreach ($precios as $key => $value) {
                if ($value == 0) {
                    die(json_encode(getResponse("El valor de ningún precio puede ser cero"), JSON_UNESCAPED_UNICODE));
                }
            }

            $productos_precios = array();
            $productos_precios['precio'] = $precios;
            $productos_precios['lista_precios_id'] = $lista_precios_id;
            $unidad_medida_id = strclean($_POST['unidad_medida_id']);

            $limite_minimo = floatval(strclean($_POST['limite_minimo']));
            $cantidad = floatval(strclean($_POST['cantidad']));

            $oferta = 0;
            $precio_oferta = 0;
            if (isset($_POST['oferta'])) {
                $oferta_post = strclean($_POST['oferta']);
                if ($oferta_post == 'on') {
                    $oferta = 1;
                }
                $precio_oferta = floatval(strclean($_POST['precio_oferta']));
            }


            $rate = intval(strclean($_POST['rate']));
            $recomendaciones_mes = 0;
            if (isset($_POST['recomendaciones_mes'])) {
                $recomendaciones_mes_post = strclean($_POST['recomendaciones_mes']);
                if ($recomendaciones_mes_post == 'on') {
                    $recomendaciones_mes = 1;
                }
            }





            $ventas_dirigidas = array();
            if (isset($_POST['ventas_dirigidas'])) {
                $ventas_dirigidas = $_POST['ventas_dirigidas'];
            }

            $ventas_cruzadas = array();
            if (isset($_POST['ventas_dirigidas'])) {
                $ventas_cruzadas = $_POST['ventas_cruzadas'];
            }



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
            $arrFiles =  $_FILES;

            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $class_model = new ProductosModel;
            $class_model->setId($record_id);
            $class_model->setName($name);
            $class_model->setDescripcion($descripcion);
            $class_model->setSlug($slug);
            // $class_model->setImage($adjunto);
            $class_model->setEmpresaId($empresa_id);
            $class_model->setSucursalId($sucursal_id);
            $class_model->setUsuarioIdCreated($usuario_id_register);

            $class_model->setAlterna($alterna);
            $class_model->setMarcaId($marca_id);
            $class_model->setLineaProductoId($linea_producto_id);
            $class_model->setCategorias($categorias);
            $class_model->setSku($sku);
            $class_model->setPrecios($productos_precios);

            $class_model->setOferta($oferta);
            $class_model->setPrecioOferta($precio_oferta);
            $class_model->setUnidadMedidaId($unidad_medida_id);

            $class_model->setLimiteMinimo($limite_minimo);
            $class_model->setCantidad($cantidad);

            $class_model->setVentasDirigidas($ventas_dirigidas);
            $class_model->setVentasCruzadas($ventas_cruzadas);

            $class_model->setRate($rate);
            $class_model->setRecomendacionesMes($recomendaciones_mes);

            if ($record_id == 0) {

                /*==========================================
                [ Crear Nuevo Registro ]*/

                $class_model->setFiles($arrFiles);

                /*-------------------------------------------
                [ Vaida imagen principal ]*/
                if ($arrFiles['adjunto']['name'][0] == '') {
                    die(json_encode(getResponse("Debe seleccionar una Imagen Principal"), JSON_UNESCAPED_UNICODE));
                }

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

                $arrFiles['adjunto']['slug'] = $files_auxiliar;
                $arrFiles['adjunto']['image_id'] = $files_auxiliar_id;

                $class_model->setFiles($arrFiles);

                /*-------------------------------------------
                [ Vaida imagen principal ]*/
                if ($arrFiles['adjunto']['name'][0] == '' && $arrFiles['adjunto']['slug'][0] == '') {
                    die(json_encode(getResponse("Debe seleccionar una Imagen Principal"), JSON_UNESCAPED_UNICODE));
                }

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
            $this->permisosMod = $arrPermisos[MOD_CAT_PRODUCTOS];
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
            [ Asignar variables de sesión ]*/
            $usuario_id_register = $this->session->get('usuario_id');
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');


            /*-------------------------------------------
            [ Se desencriptan datos ]*/
            $record_id = intval(strClean(openssl_decrypt($record_id, METHODENCRIPT, KEY)));


            /*-------------------------------------------
            [ Se aignan las variables al Modelo ]*/
            $class_model = new ProductosModel;
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
            $this->permisosMod = $arrPermisos[MOD_CAT_PRODUCTOS];
            if (!$this->permisosMod['r']) {
                die($htmlOptions);
            }

            $productos_model = new ProductosModel;
            $arrData = $productos_model->selectRecordsCatalogo();
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
