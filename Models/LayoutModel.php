<?php

/**
 * Clase LayoutModel
 */
class LayoutModel extends Mysql
{


    private $id;
    private $titulo;

    private $procesado;
    private $adjunto_output;
    private $adjunto_general;

    private $empresa_id;
    private $sucursal_id;


    private $activo;
    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    private $files;

    private $session;
    private $acceso_global;

    /**
     * Método Constructor de LayoutModel.
     * Inicializa Mysql::__construct
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        [ Validación de Acceso Global ]*/
        $arrPermisos = getPermisosGlobal();
        $this->acceso_global = $arrPermisos[MOD_ACCESO_GLOBAL];

        /*-------------------------------------------
        [ Variables de Sesion]*/
        $this->session = new Session;
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
            $sql .= "t.*,  s.nombre as sucursal ";
            $sql .= "FROM layout t ";
            $sql .= "INNER JOIN sucursales s ON (s.id = t.sucursal_id) ";
            $sql .= "ORDER BY t.titulo ";

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
     * Obtiene la lista de Registros.
     * 
     * @return array $arrResponse
     * 
     */
    public function selectAllNoProcesados(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "t.* ";
            $sql .= "FROM layout t ";

            $sql .= "WHERE t.procesado = 0 ";

            $sql .= "ORDER BY t.titulo ";

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
            $sql .= "t.*, s.nombre as sucursal, CONCAT_WS(' ', usr_dg.nombre, usr_dg.paterno, usr_dg.materno) AS usuario ";
            $sql .= "FROM layout t ";
            $sql .= "INNER JOIN usuarios_datos_generales usr_dg ON (usr_dg.usuario_id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN sucursales s ON (s.id = t.sucursal_id) ";
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
            $sql .= "FROM layout t ";
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
    public function insertRecord(LayoutModel &$modelo): bool
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
                $modelo->setId($id);
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
     * Subrutina dentro de insertRecord para crear el Registro
     * 
     * @param object $model
     * Envío del modelo por referencia con los datos requeridos para el insert
     *
     *
     */
    private function recordCreate(LayoutModel &$modelo): int
    {

        $result = 0;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "INSERT INTO layout SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "titulo = :titulo, ";
        $sql .= "adjunto_output = :adjunto_output, ";
        // $sql .= "adjunto_general = :adjunto_general, ";
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
            'titulo' => $modelo->getTitulo(),
            'adjunto_output' => $modelo->getAdjuntoOutput(),
            // 'adjunto_general' => $modelo->getAdjuntoGeneral(),
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
            $sql .= "FROM layout t ";
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
     * Envío de object LayoutModel por referencia, que contine la información
     * que contiene los parametros necesarios para realizar el registro.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateRecord(LayoutModel &$modelo): bool
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
     * Envío de object LayoutModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function recordUpdate(LayoutModel $modelo): bool
    {

        $response = false;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE layout SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "titulo = :titulo, ";
        $sql .= "adjunto_output = :adjunto_output, ";
        // $sql .= "adjunto_general = :adjunto_general, ";
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
            'titulo' => $modelo->getTitulo(),
            'adjunto_output' => $modelo->getAdjuntoOutput(),
            // 'adjunto_general' => $modelo->getAdjuntoGeneral(),
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
     * Envío de object LayoutModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function deleteRecord(LayoutModel $modelo): bool
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
     * Envío de object LayoutModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     */
    public function recordDelete(LayoutModel $modelo): bool
    {

        $response = false;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE layout SET ";
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
     * Envío de object LayoutModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function activeRecord(LayoutModel $modelo): bool
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
     * Envío de object LayoutModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     */
    public function recordActive(LayoutModel $modelo): bool
    {

        $response = false;


        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE layout SET ";
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
    private function insertAdjunto(LayoutModel $modelo): bool
    {

        try {


            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen de los arhvios adjuntos ]*/
            $file = $modelo->getFiles();

            if (empty($file)) {
                return false;
            }

            $ruta_doctos = "Assets/files/output/";

            /*-------------------------------------------
            [ En caso de ser solo actualización de datos, se retorna true ]*/
            if ($file['adjunto_output']['error'] == 0) {
                $tmp_cer1 = $file['adjunto_output']['tmp_name'];
                $ruta_file1 =  $ruta_doctos . $modelo->getAdjuntoOutput();
                $response_up_file1 =  move_uploaded_file($tmp_cer1, $ruta_file1);
                if ($response_up_file1 == false) {
                    return false;
                }
            }

            // if ($file['adjunto_general']['error'] == 0) {
            //     $tmp_cer2 = $file['adjunto_general']['tmp_name'];
            //     $ruta_file2 =  $ruta_doctos . $modelo->getAdjuntoGeneral();
            //     $response_up_file2 =  move_uploaded_file($tmp_cer2, $ruta_file2);
            //     if ($response_up_file2 == false) {
            //         return false;
            //     }
            // }

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
     * Get the value of procesado
     */
    public function getProcesado()
    {
        return $this->procesado;
    }

    /**
     * Set the value of procesado
     */
    public function setProcesado($procesado): self
    {
        $this->procesado = $procesado;

        return $this;
    }

    /**
     * Get the value of adjunto_output
     */
    public function getAdjuntoOutput()
    {
        return $this->adjunto_output;
    }

    /**
     * Set the value of adjunto_output
     */
    public function setAdjuntoOutput($adjunto_output): self
    {
        $this->adjunto_output = $adjunto_output;

        return $this;
    }

    /**
     * Get the value of adjunto_general
     */
    public function getAdjuntoGeneral()
    {
        return $this->adjunto_general;
    }

    /**
     * Set the value of adjunto_general
     */
    public function setAdjuntoGeneral($adjunto_general): self
    {
        $this->adjunto_general = $adjunto_general;

        return $this;
    }

    /**
     * Get the value of files
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set the value of files
     */
    public function setFiles($files): self
    {
        $this->files = $files;

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
     * Get the value of session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set the value of session
     */
    public function setSession($session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get the value of acceso_global
     */
    public function getAccesoGlobal()
    {
        return $this->acceso_global;
    }

    /**
     * Set the value of acceso_global
     */
    public function setAccesoGlobal($acceso_global): self
    {
        $this->acceso_global = $acceso_global;

        return $this;
    }
}
