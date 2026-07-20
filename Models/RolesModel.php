<?php

/**
 * Clase RolesModel
 */
class RolesModel extends Mysql
{

    private $id;
    private $name;
    private $descripcion;
    private $empresa_id;
    private $sucursal_id;
    private $activo;

    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    const TABLA = "roles";
    const PREFIJO_TABLA = "ssf_";

    /**
     * Método Constructor de RolesModel.
     * Inicializa Mysql::__construct
     * 
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene la lista de registros para llenar DataTable o Selects
     *
     * @param bool $ordenarAlfabeticamente
     * True, ordena los registros alfabeticamente sobre el campo principal de la tabla.
     * caso contrario ordena por fecha de registro descendente.
     * 
     * @return array $arrResponse
     * * Retorna array de tipo:
     *   fetchAll(PDO::FETCH_ASSOC): returns an array containing all of the remaining rows in the result set. 
     * 
     */
    public function selectRecords(RolesModel $modelo, bool $ordenarAlfabeticamente = false): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, us.usuario ";
            $sql .= "FROM " . self::PREFIJO_TABLA . self::TABLA . " t ";
            $sql .= "INNER JOIN  " . self::PREFIJO_TABLA . "usuarios us ON (us.id = t.usuario_id_updated) ";
            $sql .= "WHERE ";
            $sql .= "t.empresa_id = :empresa_id ";
            if ($ordenarAlfabeticamente) {
                $sql .= "ORDER BY t.name";
            } else {
                $sql .= "ORDER BY t.created_at DESC";
            }

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'empresa_id' => $modelo->getEmpresaId()
            ];

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
     * Obtiene datos de un Registro determinado.
     * 
     * @return array $arrResponse
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectRecord(RolesModel $modelo): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, ";
            $sql .= "s.nombre as sucursal, ";
            $sql .= "CONCAT_WS(' ', dat_gen.nombre, dat_gen.paterno, dat_gen.materno)  as usuario ";
            $sql .= "FROM " . self::PREFIJO_TABLA . self::TABLA . " t ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios usr ON (usr.id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales dat_gen ON (dat_gen.usuario_id = usr.id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "sucursales s ON (s.id = t.sucursal_id) ";
            $sql .= "WHERE ";
            $sql .= "t.empresa_id = :empresa_id and ";
            $sql .= "t.id = :record_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'empresa_id' => $modelo->getEmpresaId(),
                'record_id' => $modelo->getId()
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
     * Valida para evitar registros nuevos duplicados
     * 
     * @return bool $response
     * * true indica si el registro ya existe.
     * * false en caso de que no exista.
     * 
     */
    public function validInsertExist(RolesModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT t.name FROM " . self::PREFIJO_TABLA . self::TABLA . " t ";
            $sql .= "WHERE ";
            $sql .= "t.empresa_id = :empresa_id and ";
            $sql .= "t.name = :name";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'empresa_id' => $modelo->getEmpresaId(),
                'name' => $modelo->getName()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $arrResponse = $this->selectModel($sql, $arr_values);
            $response = !empty($arrResponse) ? true : false;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Validación para evitar registros existentes se puedan duplicar
     * 
     * @return bool $response
     * * true indica si el registro ya existe.
     * * false en caso de que no exista.
     * 
     */
    public function validUpdateExistRecord(RolesModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT t.name FROM " . self::PREFIJO_TABLA . self::TABLA . " t ";
            $sql .= "WHERE ";
            $sql .= "t.id != :id and ";
            $sql .= "t.empresa_id = :empresa_id and ";
            $sql .= "t.name = :name";


            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'id' => $modelo->getId(),
                'empresa_id' => $modelo->getEmpresaId(),
                'name' => $modelo->getName()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $arrResponse = $this->selectModel($sql, $arr_values);
            $response = !empty($arrResponse) ? true : false;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Guardar datos del Nuevo Registro.
     * 
     * @param object &$modelo
     * Envío del modelo \RolesModel por referencia, para asiganr el valor lastInsertId al modelo.
     *
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function insertRecord(RolesModel &$model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Registrar Datos ]*/
            $id = $this->recordCreate($model);
            if ($id > 0) {
                $model->setId($id);
                $this->getConexion()->commit();
                return true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ RollBack ]*/
        $this->getConexion()->rollBack();

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Subrutina dentro de insertRecord para crear el registro
     * 
     * @param object Model $model
     * Envío del modelo por referencia con los datos requeridos para el insert
     *
     */
    private function recordCreate(RolesModel $modelo): int
    {

        try {

            $response = 0;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "INSERT INTO " . self::PREFIJO_TABLA . self::TABLA . " SET ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "name = :name, ";
            $sql .= "descripcion = :descripcion, ";
            $sql .= "activo = 1, ";
            $sql .= "created_at = current_timestamp, ";
            $sql .= "usuario_id_created = :usuario_id_register, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";


            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'empresa_id' => $modelo->getEmpresaId(),
                'sucursal_id' => $modelo->getSucursalId(),
                'name' => $modelo->getName(),
                'descripcion' => $modelo->getDescripcion(),
                'usuario_id_register' => $modelo->getUsuarioIdCreated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $lastInsertId = $this->insert($sql, $arrData);
            $response = $lastInsertId;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna Respuesta ]*/
        return $response;
    }



    /**
     * Actualiza datos da un Registro determinado.
     * 
     * @param object $model
     * Envío del modelo por valor, que contine la información a actualizar y 
     * los parámetros condicionales para realizar el update.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateRecord(RolesModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Actualizar Datos ]*/
            $result = $this->recordUpdate($modelo);
            if ($result) {
                $this->getConexion()->commit();
                return true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ RollBack ]*/
        $this->getConexion()->rollBack();

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }


    /**
     * Subrutina dentro de updateRecord para actualizar el registro
     * 
     * @param object $model
     * Envío del modelo por referencia con los datos requeridos para el insert
     */
    private function recordUpdate(RolesModel &$modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . self::TABLA . " SET ";
            $sql .= "name = :name, ";
            $sql .= "descripcion = :descripcion, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE  ";
            $sql .= "empresa_id = :empresa_id and ";
            $sql .= "id = :id ";

            /*-------------------------------------------
            [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
            $arrData = [
                'id' => $modelo->getId(),
                'empresa_id' => $modelo->getEmpresaId(),
                'name' => $modelo->getName(),
                'descripcion' => $modelo->getDescripcion(),
                'usuario_id_register' => $modelo->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $response = $this->update($sql, $arrData);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna Respuesta ]*/
        return $response;
    }



    /**
     * Actualiza el estatus activo/eliminado de un Registro determinado.
     * 
     * @param object $modelo
     * Envío del modelo por valor, que contine la información a actualizar y 
     * los parámetros condicionales para realizar el update.
     *
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateEstatusRecord(RolesModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . self::TABLA . " SET ";
            $sql .= "activo = :activo, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE  ";
            $sql .= "empresa_id = :empresa_id and ";
            $sql .= "id = :id ";

            /*-------------------------------------------
            [ Parámetros condicionales para realizar el update ]*/
            $arrData = [
                'id' => $model->getId(),
                'activo' => $model->getActivo(),
                'empresa_id' => $model->getEmpresaId(),
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo delete de MySQL ]*/
            $respuesta = $this->delete($sql, $arrData);
            if ($respuesta == true) {
                $this->getConexion()->commit();
                return true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ RollBack ]*/
        $this->getConexion()->rollBack();

        /*-------------------------------------------
        [ Retorna bool ]*/
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
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     */
    public function setName($name): self
    {
        $this->name = $name;

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
}
