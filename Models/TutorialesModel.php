<?php

/**
 * Clase TutorialesModel
 */
class TutorialesModel extends Mysql
{

    private $id;
    private $empresa_id;
    private $sucursal_id;
    private $archivo;
    private $titulo;
    private $menu;
    private $submenu;
    private $descripcion;
    private $activo;
    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;
    private $video_adjunto;

    /**
     * Método Constructor de TutorialesModel.
     * Inicializa Mysql::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene la lista de Registros.
     * 
     * @return array $arrResponse
     * 
     */
    public function selectAll(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "t.* ";
            $sql .= "FROM tutoriales t ";
            $sql .= "ORDER BY t.descripcion ";

            /*-------------------------------------------
                [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene la lista de Registros de un texto determinado
     * 
     * @return array $arrResponse
     * 
     */
    public function selectAllFiltro($filtro): array
    {

        try {

            $filtro = str_replace(' ', '%', $filtro);

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "t.* ";
            $sql .= "FROM tutoriales t ";
            $sql .= "WHERE ";
            $sql .= "activo = 1 and ";
            $sql .= "(t.titulo LIKE '%" . $filtro . "%' OR ";
            $sql .= " t.menu LIKE '%" . $filtro . "%' OR ";
            $sql .= " t.submenu LIKE '%" . $filtro . "%' OR ";
            $sql .= " t.descripcion LIKE '%" . $filtro . "%') ";
            $sql .= " ORDER BY t.descripcion ";


            // $arrParams = explode(' ', $filtro);
            // $sql_filter = '';

            // for ($i = 0; $i < count($arrParams); $i++) {
            //     $sql_filter .= "(t.titulo LIKE '%" . $arrParams[$i] . "%' OR ";
            //     $sql_filter .= " t.menu LIKE '%" . $arrParams[$i] . "%' OR ";
            //     $sql_filter .= " t.submenu LIKE '%" . $arrParams[$i] . "%' OR ";
            //     $sql_filter .= " t.descripcion LIKE '%" . $arrParams[$i] . "%')";

            //     if ($i != (count($arrParams) - 1)) {
            //         $sql_filter .= " OR ";
            //     }
            // }


            // $arrResponse = array();

            // /*-------------------------------------------
            // [ Instruccion sql ]*/

            // $sql = "SELECT ";
            // $sql .= "t.* ";
            // $sql .= "FROM tutoriales t ";
            // $sql .= "WHERE ";
            // $sql .= "activo = 1 and ";
            // $sql .= $sql_filter;
            // $sql .= " ORDER BY t.descripcion ";


            /*-------------------------------------------
                [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene la lista de Registros de un menu determinado
     * 
     * @return array $arrResponse
     * 
     */
    public function selectAllFiltroMenu($filtro): array
    {

        try {


            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            if ($filtro == 'Todos') {
                $sql = "SELECT ";
                $sql .= "t.* ";
                $sql .= "FROM tutoriales t ";
                $sql .= "WHERE ";
                $sql .= "activo = 1 ";
                $sql .= " ORDER BY t.descripcion ";

                /*-------------------------------------------
                [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
                $arr_values = [];
            } else {

                $sql = "SELECT ";
                $sql .= "t.* ";
                $sql .= "FROM tutoriales t ";
                $sql .= "WHERE ";
                $sql .= "activo = 1 and ";
                $sql .= "menu = :menu ";
                $sql .= " ORDER BY t.descripcion ";

                /*-------------------------------------------
                [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
                $arr_values = ['menu' => $filtro];
            }


            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene la lista de Registros de un menuy submenu determinado
     * 
     * @return array $arrResponse
     * 
     */
    public function selectAllFiltroSubMenu($filtro): array
    {

        try {

            $arrParams = explode('*', $filtro);

            $menu = $arrParams[0];
            $submenu = $arrParams[1];

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            if ($submenu == 'Todos') {
                $submenu_filtro = '';
            } else {
                $submenu_filtro = 'and submenu = :submenu ';
            }

            $sql = "SELECT ";
            $sql .= "t.* ";
            $sql .= "FROM tutoriales t ";
            $sql .= "WHERE ";
            $sql .= "activo = 1 and ";
            $sql .= "menu = :menu ";
            $sql .= $submenu_filtro;
            $sql .= " ORDER BY t.descripcion ";


            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            if ($submenu == 'Todos') {
                $arr_values = [
                    'menu' => $menu
                ];
            } else {
                $arr_values = [
                    'menu' => $menu,
                    'submenu' => $submenu
                ];
            }

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene la lista de Registros de Menus
     * 
     * @return array $arrResponse
     * 
     */
    public function selectAllMenu(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "distinct t.menu ";
            $sql .= "FROM tutoriales t ";
            $sql .= "WHERE t.activo = 1 ";
            $sql .= "ORDER BY t.menu ";

            /*-------------------------------------------
                [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene la lista de Registros de SubMenus
     * 
     * @return array $arrResponse
     * 
     */
    public function selectAllSubmenu($menu): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "distinct t.submenu ";
            $sql .= "FROM tutoriales t ";
            $sql .= "WHERE t.activo = 1 and menu = :menu ";
            $sql .= "ORDER BY t.submenu ";

            /*-------------------------------------------
                [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = ['menu' => $menu];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene datos de un Registro determinada.
     * 
     * @param int $id
     * Identificador de Registro
     * 
     * @return array $arrResponse
     * * array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * 
     */
    public function selectRecord(int $id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, CONCAT_WS(' ', usr_dg.nombre, usr_dg.paterno, usr_dg.materno) AS usuario ";
            $sql .= "FROM tutoriales t ";
            $sql .= "INNER JOIN usuarios_datos_generales usr_dg ON (usr_dg.usuario_id = t.usuario_id_updated) ";
            $sql .= "WHERE ";
            $sql .= "t.id = :id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'id' => $id
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $arrResponse = $this->selectModel($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array asociativo con los datos del registro o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Valida si existe un registro determinado, a partir de un texto dado.
     * 
     * @param string $valid_text
     * Texto que desea validar
     * 
     * @return bool $response
     * * true = Existe.
     * * false = No existe.
     * 
     */
    public function validaExistRecord(string $valid_text): bool
    {

        try {


            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.titulo ";
            $sql .= "FROM tutoriales t ";
            $sql .= "WHERE ";
            $sql .= "t.titulo = :valid_text ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'valid_text' => $valid_text
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
            if (!empty($arrResponse)) {
                return true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array asociativo con los datos del registro o empty en caso de error ]*/
        return $response;
    }

    /**
     * Guardar datos del Nuevo Registro
     * 
     * @param object &$model
     * Envío del modelo por referencia, para asiganr el valor lastInsertId al modelo.
     * 
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function insertRecord(TutorialesModel &$modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Registrar Datos ]*/
            $id =  $this->recordCreate($modelo);
            if ($id > 0) {
                if ($this->insertAdjunto($modelo)) {
                    $modelo->setId($id);
                    $this->getConexion()->commit();
                    return true;
                }
            }

            $this->getConexion()->rollBack();
        } catch (\Throwable $th) {
            $this->getConexion()->rollBack();
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Subrutina dentro de insertRecord para crear el Registro
     * 
     * @param object $model
     * Envío del modelo por referencia con los datos requeridos para el insert
     *
     *
     */
    private function recordCreate(TutorialesModel &$modelo): int
    {

        $result = 0;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "INSERT INTO tutoriales SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "archivo = :archivo, ";
        $sql .= "titulo = :titulo, ";
        $sql .= "menu = :menu, ";
        $sql .= "submenu = :submenu, ";
        $sql .= "descripcion = :descripcion, ";
        $sql .= "activo = 1, ";
        $sql .= "created_at = current_timestamp, ";
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_created = :usuario_id_register, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";

        /*-------------------------------------------
        [ Datos a insertar ]*/
        $arrData = [
            'empresa_id' => $modelo->getEmpresaId(),
            'sucursal_id' => $modelo->getSucursalId(),
            'archivo' => $modelo->getArchivo(),
            'titulo' => $modelo->getTitulo(),
            'menu' => $modelo->getMenu(),
            'submenu' => $modelo->getSubmenu(),
            'descripcion' => $modelo->getDescripcion(),
            'usuario_id_register' => $modelo->getUsuarioIdCreated()
        ];

        /*-------------------------------------------
        [ Ejecuta el Metodo insert de MySQL ]*/
        $lastInsertId = $this->insert($sql, $arrData);
        if ($lastInsertId > 0) {
            $modelo->setId($lastInsertId);
            $result = $lastInsertId;
        }

        /*-------------------------------------------
        [ Retorna Id de Recibo Insertado ]*/
        return $result;
    }

    /**
     * Valida si existe el Registro a Actualizar
     * 
     * @param string $text_value
     * texto del registro a validar
     * 
     * @param int $id
     * Identificador de Registro
     * 
     * @return bool $response
     * * true = Existe.
     * * false = No existe.
     * 
     */
    public function validExistRecordUpdate(string $text_value, int $id): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.id  ";
            $sql .= "FROM tutoriales t ";
            $sql .= "WHERE ";
            $sql .= "t.titulo = :titulo and ";
            $sql .= "t.id NOT IN(:id) ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'titulo' => $text_value,
                'id' => $id
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
            if (!empty($arrResponse)) {
                $response = true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bolleano ]*/
        return $response;
    }

    /**
     * Actualizar Datos de Registro.
     * 
     * @param object &$model 
     * Envío de object TutorialesModel por referencia, que contine la información
     * que contiene los parametros necesarios para realizar el registro.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateRecord(TutorialesModel &$modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            // /*-------------------------------------------
            // [ Guardar Datos  ]*/
            $response =  $this->recordUpdate($modelo);
            if ($response) {
                if ($this->insertAdjunto($modelo)) {
                    $this->getConexion()->commit();
                    return true;
                }
            }

            $this->getConexion()->rollBack();
        } catch (\Throwable $th) {

            $this->getConexion()->rollBack();
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Subrutina dentro de updateRecord para actualizar datos de registro
     * 
     * @param object $model 
     * Envío de object TutorialesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function recordUpdate(TutorialesModel $modelo): bool
    {

        $response = false;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE tutoriales SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "archivo = :archivo, ";
        $sql .= "titulo = :titulo, ";
        $sql .= "menu = :menu, ";
        $sql .= "submenu = :submenu, ";
        $sql .= "descripcion = :descripcion, ";
        $sql .= "activo = :activo, ";
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";
        $sql .= "WHERE ";
        $sql .= "id = :id ";

        /*-------------------------------------------
        [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
        $arrData = [
            'id' => $modelo->getId(),
            'activo' => $modelo->getActivo(),
            'empresa_id' => $modelo->getEmpresaId(),
            'sucursal_id' => $modelo->getSucursalId(),
            'archivo' => $modelo->getArchivo(),
            'titulo' => $modelo->getTitulo(),
            'menu' => $modelo->getMenu(),
            'submenu' => $modelo->getSubmenu(),
            'descripcion' => $modelo->getDescripcion(),
            'usuario_id_register' => $modelo->getUsuarioIdUpdated()
        ];

        /*-------------------------------------------
        [ Ejecuta el Metodo update de MySQL ]*/
        $response = $this->update($sql, $arrData);

        /*-------------------------------------------
        [ devuelve boleeano ]*/
        return $response;
    }

    /**
     * Eliminar Registro.
     * 
     * @param object $model 
     * Envío de object TutorialesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function deleteRecord(TutorialesModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            // /*----------------------------------------
            // [ Guardar Datos  ]*/
            $response = $this->recordDelete($modelo);
            if ($response) {
                $this->getConexion()->commit();
                return true;
            }

            $this->getConexion()->rollBack();
        } catch (\Throwable $th) {
            $this->getConexion()->rollBack();
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Subrutina dentro de deleteRecord para Eliminar Registro
     * 
     * @param object $model 
     * Envío de object TutorialesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     */
    public function recordDelete(TutorialesModel $modelo): bool
    {

        $response = false;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE tutoriales SET ";
        $sql .= "activo = 0, ";
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";
        $sql .= "WHERE ";
        $sql .= "id = :id ";


        /*-------------------------------------------
        [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
        $arrData = [
            'id' => $modelo->getId(),
            'usuario_id_register' => $modelo->getUsuarioIdUpdated()
        ];

        /*-------------------------------------------
        [ Ejecuta el Metodo update de MySQL ]*/
        $response = $this->update($sql, $arrData);

        /*-------------------------------------------
        [ Devuelve boleano ]*/
        return $response;
    }

    /**
     * Activar Registro.
     * 
     * @param object $model 
     * Envío de object TutorialesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function activeRecord(TutorialesModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            // /*-------------------------------------------
            // [ Guardar Datos ]*/
            $response = $this->recordActive($modelo);
            if ($response) {
                $this->getConexion()->commit();
                return true;
            }

            $this->getConexion()->rollBack();
        } catch (\Throwable $th) {
            $this->getConexion()->rollBack();
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Subrutina dentro de activeRecord para Activar registro
     * 
     * @param object $model 
     * Envío de object TutorialesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     */
    public function recordActive(TutorialesModel $modelo): bool
    {

        $response = false;


        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE tutoriales SET ";
        $sql .= "activo = 1, ";
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";
        $sql .= "WHERE ";
        $sql .= "id = :id ";


        /*-------------------------------------------
        [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
        $arrData = [
            'id' => $modelo->getId(),
            'usuario_id_register' => $modelo->getUsuarioIdUpdated()
        ];

        /*-------------------------------------------
        [ Ejecuta el Metodo update de MySQL ]*/
        $response = $this->update($sql, $arrData);

        /*-------------------------------------------
        [ Devuelve boleano ]*/
        return $response;
    }

    /**
     * Subrutina dentro de updateRecord o insertRecord para insertar los videos adjuntos
     * 
     * @param object $model 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function insertAdjunto(TutorialesModel $modelo): bool
    {

        try {


            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen de los arhvios adjuntos ]*/
            $file = $modelo->getVideoAdjunto();

            /*-------------------------------------------
            [ En caso de ser solo actualización de datos, se retorna true ]*/
            if ($file['adjunto_file']['error'] == 4) {
                return true;
            }

            // name:            // "registro maestro actulizacion de tabulador imiem.mp4"
            // type:            // "video/mp4"
            // tmp_name:            // "C:\xampp\tmp\php58B5.tmp"
            // error:            // 0
            // size:            // 7268818

            if ($file['adjunto_file']['error'] == 0) {

                /*-------------------------------------------
                [ Upload File ]*/
                $tmp_cer = $file['adjunto_file']['tmp_name'];
                $ruta_doctos = "Assets/files/videos/";
                $ruta_file =  $ruta_doctos . $modelo->getArchivo();
                $response_up_file =  move_uploaded_file($tmp_cer, $ruta_file);
                if ($response_up_file == false) {
                    return false;
                }
            }

            /*-------------------------------------------
            [ En caso de que no haya nigun error ]*/
            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }


        /*-------------------------------------------
        [ Retorna Id de Recibo Insertado ]*/
        return $response;
    }



    //*==================================================================
    // [ GETTERS & SETTERS ]*/


    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of empresa_id
     */
    public function getEmpresaId()
    {
        return $this->empresa_id;
    }

    /**
     * Set the value of empresa_id
     */
    public function setEmpresaId($empresa_id): self
    {
        $this->empresa_id = $empresa_id;

        return $this;
    }

    /**
     * Get the value of sucursal_id
     */
    public function getSucursalId()
    {
        return $this->sucursal_id;
    }

    /**
     * Set the value of sucursal_id
     */
    public function setSucursalId($sucursal_id): self
    {
        $this->sucursal_id = $sucursal_id;

        return $this;
    }

    /**
     * Get the value of archivo
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set the value of archivo
     */
    public function setArchivo($archivo): self
    {
        $this->archivo = $archivo;

        return $this;
    }

    /**
     * Get the value of titulo
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set the value of titulo
     */
    public function setTitulo($titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get the value of menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set the value of menu
     */
    public function setMenu($menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get the value of submenu
     */
    public function getSubmenu()
    {
        return $this->submenu;
    }

    /**
     * Set the value of submenu
     */
    public function setSubmenu($submenu): self
    {
        $this->submenu = $submenu;

        return $this;
    }

    /**
     * Get the value of descripcion
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set the value of descripcion
     */
    public function setDescripcion($descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }


    /**
     * Get the value of activo
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set the value of activo
     */
    public function setActivo($activo): self
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get the value of created_at
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     */
    public function setCreatedAt($created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get the value of usuario_id_created
     */
    public function getUsuarioIdCreated()
    {
        return $this->usuario_id_created;
    }

    /**
     * Set the value of usuario_id_created
     */
    public function setUsuarioIdCreated($usuario_id_created): self
    {
        $this->usuario_id_created = $usuario_id_created;

        return $this;
    }

    /**
     * Get the value of updated_at
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set the value of updated_at
     */
    public function setUpdatedAt($updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Get the value of usuario_id_updated
     */
    public function getUsuarioIdUpdated()
    {
        return $this->usuario_id_updated;
    }

    /**
     * Set the value of usuario_id_updated
     */
    public function setUsuarioIdUpdated($usuario_id_updated): self
    {
        $this->usuario_id_updated = $usuario_id_updated;

        return $this;
    }

    /**
     * Get the value of video_adjunto
     */
    public function getVideoAdjunto()
    {
        return $this->video_adjunto;
    }

    /**
     * Set the value of video_adjunto
     */
    public function setVideoAdjunto($video_adjunto): self
    {
        $this->video_adjunto = $video_adjunto;

        return $this;
    }
}
