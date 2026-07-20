<?php

require 'Libraries/phpspreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use PhpOffice\PhpSpreadsheet\IOFactory;


/**
 * Controlador Layout 
 */
class Layout extends Controllers
{

    private $session;
    private $permisosMod;

    /**
     * Método Constructor de Controlador Layout.
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
    public function Layout()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $this->permisosMod = $arrPermisos[MOD_VENTAS_LAYOUT];

            // Valida si tiene acceso a la pagina.
            if (!$this->permisosMod['r']) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            // Obtener datos de Modulo
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_VENTAS_LAYOUT);

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion = $configuracion_model->selectConfiguracion();
            $data['configuracion'] = $configuracion;

            // Asigna los permisos de Módulo y SideBar
            $data['permisos'] = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;


            //Id de Menu para script de Pemisos de Boton exportar a Excel
            $data['menu'] = MOD_VENTAS_LAYOUT;

            //Header
            $data['page_title'] = $menu['name'];
            $data['meta_description'] = $menu['descripcion'];
            $data['meta_keywords'] = "panel, administracion, hdsolutions";

            //Form Principal 
            $data['page_form_title'] = "<i class='fa-regular fa-rectangle-history fa-fw text-primary text-shadow-primary'></i> " . $menu['form_title'];

            //Breadcrump
            $data['page_breadcrumb'] = $menu['breadcrumb'];

            //Card Principal
            $data['page_card_title'] =  $menu['card_title'];
            $data['page_card_description'] = $menu['descripcion'];

            //JS Principal
            $data['page_functions_js'] = "" . $menu['js'] . ".js";

            //Call Vista
            $this->views->getView($this, "layout", $data);
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
            $this->permisosMod = $arrPermisos[MOD_VENTAS_LAYOUT];
            if (!$this->permisosMod['r']) {
                die(json_encode($arrData, JSON_UNESCAPED_UNICODE));
            }

            /*-------------------------------------------
            [ Variables. ]*/
            $data_animation = "fadeInLeft";

            /*-------------------------------------------
            [ Obtiene el array con la lista de catálogo de roles ]*/
            $class_model = new LayoutModel;
            $arrData = $class_model->selectAll();

            /*-------------------------------------------
            [ Personaliza los datos del array ]*/
            for ($i = 0; $i < count($arrData); $i++) {


                // { "data": "procesado" },
                $procesado = $arrData[$i]['procesado'];
                if ($arrData[$i]['adjunto_general'] == '') {
                    $procesado_complementario = 1;
                } else {
                    $procesado_complementario = $arrData[$i]['procesado_complementario'];
                }

                if ($procesado == 1 && $procesado_complementario == 1) {
                    $arrData[$i]['procesado'] = '<div class="d-flex justify-content-center align-items-center"><span class="badge bg-primary-gradient">Descargado</span></div>';
                } else {
                    $arrData[$i]['procesado'] = '<div class="d-flex justify-content-center align-items-center"><span class="badge bg-warning-gradient">Pendiente de Descargar</span></div>';
                }




                // { "data": "activo" },
                $activo = $arrData[$i]['activo'];
                if ($activo == 1) {
                    $arrData[$i]['activo'] = '<div class="d-flex justify-content-center align-items-center"><span class="badge bg-success-gradient">Activo</span></div>';
                } else {
                    $arrData[$i]['activo'] = '<div class="d-flex justify-content-center align-items-center"><span class="badge bg-danger-gradient">Inactivo</span></div>';
                }

                // { "data": "options" }
                $btnView = '';
                $btnEdit = '';
                $btnDelete = '';
                $btnExtraer = '';
                $btnExtraerCompl = '';

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
                // <i class="fa-sharp fa-solid fa-download"></i>
                if ($activo == 1) {
                    $btnExtraer = '<button style="margin-left: 3px; box-shadow: none!important; width: 40px;" class="btn btn-sm btn-outline-default d-flex justify-content-center align-items-center" onclick="fntExtraer(this)" data-id="' . $arrData[$i]['id'] . '" title= "Extarer Datos Output">
                                        <i class="fa-regular fa-download fs-12"></i>
                                    </button>';

                    $btnExtraerCompl = '<button style="margin-left: 3px; box-shadow: none!important; width: 40px;" class="btn btn-sm btn-outline-default d-flex justify-content-center align-items-center" onclick="fntExtraerComplementario(this)" data-id="' . $arrData[$i]['id'] . '" title= "Extarer Datos Output Complementario">
                                        <i class="fa-regular fa-download fs-12"></i>
                                    </button>';
                }

                $btnView = ($this->permisosMod['r']) ? $btnView : '';
                $btnEdit = ($this->permisosMod['u']) ? $btnEdit : '';
                $btnDelete = ($this->permisosMod['d']) ? $btnDelete : '';
                $btnExtraer = ($this->permisosMod['u']) ? $btnExtraer : '';
                $btnExtraerCompl = ($this->permisosMod['u']) ? $btnExtraerCompl : '';

                $btnEdit = ($procesado == 0) ? $btnEdit : '';
                $btnDelete = ($procesado == 0) ? $btnDelete : '';
                $btnExtraer = ($procesado == 0) ? $btnExtraer : '';
                $btnExtraerCompl = ($procesado_complementario == 0) ? $btnExtraerCompl : '';

                $arrData[$i]['options'] = '<div class="d-flex justify-content-center align-items-center">' . $btnView . ' ' . $btnEdit  . ' ' . $btnDelete . ' ' . $btnExtraer . ' ' . $btnExtraerCompl . '</div>';
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
            $this->permisosMod = $arrPermisos[MOD_VENTAS_LAYOUT];
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
            $class_model = new LayoutModel;

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
            [ Instanciar Modelo ]*/
            $class_model = new LayoutModel;


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
     * Obtiene la lista registros para llenar un Select
     * 
     * @return string $htmlOptions
     */
    public function getAllSelectNoProcesados(): string
    {

        try {

            $htmlOptions = '';

            /*-------------------------------------------
            [ Instanciar Modelo ]*/
            $class_model = new LayoutModel;


            $arrData = $class_model->selectAllNoProcesados();
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
            $this->permisosMod = $arrPermisos[MOD_VENTAS_LAYOUT];

            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $id = intval(strclean($_POST['id_record']));
            $titulo = strclean($_POST['titulo']);
            $sucursal_id = intval(strclean($_POST['sucursal_id']));

            /*-------------------------------------------
            [ Validar Datos Recibidos ]*/
            if (trim($titulo) == '') {
                die(json_encode(getResponse('Debe indicar Titulo de Layout.'), JSON_UNESCAPED_UNICODE));
            }

            if ($sucursal_id == 0) {
                die(json_encode(getResponse('Debe indicar Sucursal.'), JSON_UNESCAPED_UNICODE));
            }

            $files =  $_FILES;

            if ($_FILES['adjunto_output']['error'] == 0) {
                $arrParams = explode('.', $_FILES['adjunto_output']['name']);
                $index = count($arrParams) - 1;
                $file_extension = strClean($arrParams[$index]);

                $name = $_FILES['adjunto_output']['name'];
                $n = $name . date('YmdHis');
                $adjunto_output = encode($n) . '.' . trim($file_extension);
            } else {
                $adjunto_output = '';
            }

            if (trim($adjunto_output) == '') {
                die(json_encode(getResponse('Debe indicar archivo output.'), JSON_UNESCAPED_UNICODE));
            }

            // if ($_FILES['adjunto_general']['error'] == 0) {
            //     $arrParams = explode('.', $_FILES['adjunto_general']['name']);
            //     $index = count($arrParams) - 1;
            //     $file_extension = strClean($arrParams[$index]);

            //     $name_ine = $_FILES['adjunto_general']['name'];
            //     $n = $name_ine . date('YmdHis');
            //     $adjunto_general = encode($n) . '.' . trim($file_extension);
            // } else {
            //     $adjunto_general = '';
            // }

            /*-------------------------------------------
            [ Se asigan variables de Sesion. ]*/
            $usuario_id_register = $this->session->get('usuario_id');
            $empresa_id = $this->session->get('empresa_id');

            /*-------------------------------------------
            [ Instanciar Modelo ]*/
            $class_model = new LayoutModel;
            $class_model->setId($id);
            $class_model->setEmpresaId($empresa_id);
            $class_model->setSucursalId($sucursal_id);
            $class_model->setTitulo($titulo);

            $class_model->setAdjuntoOutput($adjunto_output);
            // $class_model->setAdjuntoGeneral($adjunto_general);

            $class_model->setActivo(1);
            $class_model->setUsuarioIdCreated($usuario_id_register);
            $class_model->setUsuarioIdUpdated($usuario_id_register);

            $class_model->setFiles($files);


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
                    die(json_encode(getResponse('El Registro que desea realizar ya existe, verifique. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CT101</span>.'), JSON_UNESCAPED_UNICODE));
                }

                $response = $class_model->insertRecord($class_model);
                if ($response == false) {
                    die(json_encode(getResponse('Error al realizar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CT101a</span>.'), JSON_UNESCAPED_UNICODE));
                }
            } else {

                if (!$this->permisosMod['u']) {
                    die(json_encode(getResponse('No cuenta con los suficientes privilegios para realizar esta acción.'), JSON_UNESCAPED_UNICODE));
                }

                $result = $class_model->validExistRecordUpdate($titulo, $id);
                if ($result == true) {
                    die(json_encode(getResponse('El Registro que desea realizar ya existe, verifique. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CT102</span>.'), JSON_UNESCAPED_UNICODE));
                }

                $response = $class_model->updateRecord($class_model);
                if ($response == false) {
                    die(json_encode(getResponse('Error al actualizar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CT102a</span>.'), JSON_UNESCAPED_UNICODE));
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
            $this->permisosMod = $arrPermisos[MOD_VENTAS_LAYOUT];
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
            $class_model = new LayoutModel;
            $class_model->setId($id);
            $class_model->setActivo(0);
            $class_model->setUsuarioIdUpdated($usuario_id_register);
            $response = $class_model->deleteRecord($class_model);


            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if ($response == false) {
                die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CT104</span>.'), JSON_UNESCAPED_UNICODE));
            }


            $arrResponse = getResponse('Registro Eliminado exitosamente.', 'ok', true);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CT105</span>.'), JSON_UNESCAPED_UNICODE));
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
            $this->permisosMod = $arrPermisos[MOD_VENTAS_LAYOUT];
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
            $class_model = new LayoutModel;
            $class_model->setId($id);
            $class_model->setActivo(0);
            $class_model->setUsuarioIdUpdated($usuario_id_register);
            $response = $class_model->activeRecord($class_model);


            /*-------------------------------------------
            [ Evalúa respuesta  ]*/
            if ($response == false) {
                die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CT106</span>.'), JSON_UNESCAPED_UNICODE));
            }


            $arrResponse = getResponse('Registro Reactivado exitosamente.', 'ok', true);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Error al eliminar el registro, intente nuevamente. <br/><span class="text-danger fs-10 fwt-italic">Codido de Error: CT107</span>.'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    public function extraerDatos(): array
    {
        try {


            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $id = intval($_POST['id']);

            /*-------------------------------------------
            [ Se aignan las variables de Sesión ]*/
            $usuario_id_register = $this->session->get('usuario_id');

            //*==================================================================
            // [ Instanciar el modelo de clientes ]*/
            $layout_model = new LayoutModel;
            $layout = $layout_model->selectRecord($id);
            $adjunto_output = $layout['adjunto_output'];
            $sucursal_id = $layout['sucursal_id'];
            $empresa_id = $layout['empresa_id'];

            $ruta_archivo_output = 'Assets/files/output/' . $adjunto_output;


            //*==================================================================
            // [ Extraer Datos Archivo Output ]*/

            // Se carga el documento que se va a procesar.
            $documento = IOFactory::load($ruta_archivo_output);

            // Se lee la hoja que deseas
            $hoja_actual = $documento->getSheet(0);

            // Se obtiene las filas que solo contienen información.
            $numero_filas = $hoja_actual->getHighestDataRow();

            // Se obtiene las columnas y filas que solo contienen información.
            // $letra = $hoja_actual->getHighestColumn();
            // $max_column = Coordinate::columnIndexFromString($letra);
            $max_column = 16;

            // Variables de arreglo para almacenar los datos del archivo de excel.
            $arrExcel = array();
            $arrColumna = array();
            $tot_reg_no_validos = 0;

            // Ciclo para llenar los array con los datos que contiene el archivod e excel.
            for ($indice_fila = 2; $indice_fila <= $numero_filas; $indice_fila++) {

                //columna Fecha Ingreso
                $valida_column_3 = $hoja_actual->getCellByColumnAndRow(3, $indice_fila);

                if ($valida_column_3->getValue() != '') {

                    for ($indice_columna = 1; $indice_columna <= $max_column; $indice_columna++) {

                        $valor = $hoja_actual->getCellByColumnAndRow($indice_columna, $indice_fila);

                        /** Valida la fecha de la columna 1 */
                        if ($indice_columna == 3 || $indice_columna == 11) {

                            $valida_fecha = $valor->getFormattedValue();
                            if ($indice_columna == 3) {
                                $valida_fecha = formatDateTime_DB($valida_fecha);
                            } else if ($indice_columna == 11) {
                                $valida_fecha = formatDateTime_DB_Int($valida_fecha);
                            }
                            if ($valida_fecha == false) {
                                $arrColumna['columna_' . $indice_columna] = null;
                                // $tot_reg_no_validos += 1;
                                // $this->setEstatus($descarga_cliente_excel_id, 2);
                                // die(json_encode(getResponse("El archivo de excel, no contiene datos validos. Columna " . $indice_columna . " Fila " . $indice_fila . "<br/><span class='text-danger fs-11 fwt-italic'>Codido de Error: desc_excel_1026-a</span>"), JSON_UNESCAPED_UNICODE));
                            } else {
                                $arrColumna['columna_' . $indice_columna] = $valida_fecha;
                            }
                        } else {
                            $arrColumna['columna_' . $indice_columna] = $valor->getValue();
                        }
                    }
                    $arrExcel[] = $arrColumna;
                }
            }

            $layout_output_model = new LayoutOutputModel;
            $layout_output_model->setArrLayout($arrExcel);
            $layout_output_model->setLayoutId($id);
            $layout_output_model->setUsuarioIdCreated($usuario_id_register);
            $layout_output_model->setSucursalId($sucursal_id);
            $layout_output_model->setEmpresaId($empresa_id);


            // Proceso de Inserción de Datos Extraidos
            $resultado_delete = $layout_output_model->deleteLayout($layout_output_model);
            if ($resultado_delete == false) {
                die(json_encode(getResponse("Code Error: extraer_datos_2005, Error extraer los datos del layout de excel, intente nuevamente"), JSON_UNESCAPED_UNICODE));
            }

            $resultado = $layout_output_model->insertRecord($layout_output_model);

            // Evalúa respuesta
            if ($resultado == false) {
                die(json_encode(getResponse("Code Error: extraer_datos_2005, Error extraer los datos del layout de excel, intente nuevamente"), JSON_UNESCAPED_UNICODE));
            }


            $arrResponse = getResponse("Archivos extraidos exitosamente", "ok", true);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse("Code Error: descarga_cliente_excel_1029,  Error desconocido."), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    public function extraerDatosComplementario(): array
    {
        try {


            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $id = intval($_POST['id']);

            /*-------------------------------------------
            [ Se aignan las variables de Sesión ]*/
            $usuario_id_register = $this->session->get('usuario_id');

            //*==================================================================
            // [ Instanciar el modelo de clientes ]*/
            $layout_model = new LayoutModel;
            $layout = $layout_model->selectRecord($id);
            $adjunto_ouput_complementario = $layout['adjunto_general'];
            $ruta_archivo_output_complementario = 'Assets/files/output/' . $adjunto_ouput_complementario;


            //*==================================================================
            // [ Extraer Datos Archivo Output ]*/

            // Se carga el documento que se va a procesar.
            $documento = IOFactory::load($ruta_archivo_output_complementario);

            // Se lee la hoja que deseas
            $hoja_actual = $documento->getSheet(0);

            // Se obtiene las filas que solo contienen información.
            $numero_filas = $hoja_actual->getHighestDataRow();

            // Se obtiene las columnas y filas que solo contienen información.
            // $letra = $hoja_actual->getHighestColumn();
            // $max_column = Coordinate::columnIndexFromString($letra);
            $max_column = 83;

            // Variables de arreglo para almacenar los datos del archivo de excel.
            $arrExcel = array();
            $arrColumna = array();
            $tot_reg_no_validos = 0;

            // Ciclo para llenar los array con los datos que contiene el archivod e excel.
            for ($indice_fila = 2; $indice_fila <= $numero_filas; $indice_fila++) {

                //columna Fecha Ingreso
                $valida_column_64 = $hoja_actual->getCellByColumnAndRow(64, $indice_fila);

                if ($valida_column_64->getValue() != '') {

                    for ($indice_columna = 1; $indice_columna <= $max_column; $indice_columna++) {

                        $valor = $hoja_actual->getCellByColumnAndRow($indice_columna, $indice_fila);

                        /** Valida la fecha de la columna 1 */
                        if ($indice_columna == 45 || $indice_columna == 74) {

                            $valida_fecha = $valor->getFormattedValue();
                            $valida_fecha = formatDateTime_DB($valida_fecha);
                            if ($valida_fecha == false) {
                                $arrColumna['columna_' . $indice_columna] = null;
                            } else {
                                $arrColumna['columna_' . $indice_columna] = $valida_fecha;
                            }
                        } else {
                            $arrColumna['columna_' . $indice_columna] = $valor->getValue();
                        }
                    }
                    $arrExcel[] = $arrColumna;
                }
            }

            // 23 Teléfonos	
            // 26 Hub	
            // 31 Motivo de la cancelacion	
            // 33 Sub-Estado	
            // 35 Clave Vendedor	
            // 45 Fecha de la orden	
            // 49 Nº de orden	
            // 56 Estado	
            // 57 Compañía	
            // 60 Dirección	
            // 64 Nº de cuenta	
            // 71 Motivo de reprogramacion	
            // 74 Ultima Modificacion	
            // 75 Comentarios

            $layout_output_model = new LayoutOutputModel;
            $layout_output_model->setArrLayout($arrExcel);
            $layout_output_model->setLayoutId($id);
            $layout_output_model->setUsuarioIdCreated($usuario_id_register);

            // Proceso de Inserción de Datos Extraidos
            $resultado_delete = $layout_output_model->deleteLayoutComplementario($layout_output_model);
            if ($resultado_delete == false) {
                die(json_encode(getResponse("Code Error: extraer_datos_2005, Error extraer los datos del layout de excel, intente nuevamente"), JSON_UNESCAPED_UNICODE));
            }

            $resultado = $layout_output_model->insertRecordComplementario($layout_output_model);

            // Evalúa respuesta
            if ($resultado == false) {
                die(json_encode(getResponse("Code Error: extraer_datos_2005, Error extraer los datos del layout de excel, intente nuevamente"), JSON_UNESCAPED_UNICODE));
            }


            $arrResponse = getResponse("Archivos extraidos exitosamente", "ok", true);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse("Code Error: descarga_cliente_excel_1029,  Error desconocido."), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
