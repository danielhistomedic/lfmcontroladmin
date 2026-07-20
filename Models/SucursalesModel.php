<?php

/**
 * Clase SucursalesModel
 */
class SucursalesModel extends Mysql
{

    private $id;
    private $nombre;
    private $telefono;
    private $email;

    private $activo;
    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    private $session;
    private $acceso_global;

    /**
     * Método Constructor de SucursalesModel.
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
            $sql .= "t.* ";
            $sql .= "FROM sucursales t ";

            // validacion de acceso global
            if (!$this->acceso_global['r']) {
                $sql .= "WHERE ";
                $sql .= "t.id = " . $this->session->get('sucursal_id') . " ";
            }

            $sql .= "ORDER BY t.nombre ";

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
            $sql .= "t.*, CONCAT_WS(' ', usr_dg.nombre, usr_dg.paterno, usr_dg.materno) AS usuario ";
            $sql .= "FROM sucursales t ";
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
            $sql .= "t.nombre ";
            $sql .= "FROM sucursales t ";
            $sql .= "WHERE ";
            $sql .= "t.nombre = :valid_text ";

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
    public function insertRecord(SucursalesModel &$modelo): bool
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
     * Subrutina dentro de insertRecord para crear el Registro
     * 
     * @param object $model
     * Envío del modelo por referencia con los datos requeridos para el insert
     *
     *
     */
    private function recordCreate(SucursalesModel &$modelo): int
    {

        $result = 0;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "INSERT INTO sucursales SET ";
        $sql .= "nombre = :nombre, ";
        $sql .= "telefono = :telefono, ";
        $sql .= "email = :email, ";
        $sql .= "activo = 1, ";
        $sql .= "created_at = current_timestamp, ";
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_created = :usuario_id_register, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";

        /*-------------------------------------------
        [ Datos a insertar ]*/
        $arrData = [
            'nombre' => $modelo->getNombre(),
            'telefono' => $modelo->getTelefono(),
            'email' => $modelo->getEmail(),
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
            $sql .= "FROM sucursales t ";
            $sql .= "WHERE ";
            $sql .= "t.nombre = :nombre and ";
            $sql .= "t.id NOT IN(:id) ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'nombre' => $text_value,
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
     * Envío de object SucursalesModel por referencia, que contine la información
     * que contiene los parametros necesarios para realizar el registro.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateRecord(SucursalesModel &$modelo): bool
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
     * Subrutina dentro de updateRecord para actualizar datos de registro
     * 
     * @param object $model 
     * Envío de object SucursalesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function recordUpdate(SucursalesModel $modelo): bool
    {

        $response = false;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE sucursales SET ";
        $sql .= "nombre = :nombre, ";
        $sql .= "telefono = :telefono, ";
        $sql .= "email = :email, ";
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
            'nombre' => $modelo->getNombre(),
            'telefono' => $modelo->getTelefono(),
            'email' => $modelo->getEmail(),
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
     * Envío de object SucursalesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function deleteRecord(SucursalesModel $modelo): bool
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
     * Envío de object SucursalesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     */
    public function recordDelete(SucursalesModel $modelo): bool
    {

        $response = false;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE sucursales SET ";
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
     * Envío de object SucursalesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function activeRecord(SucursalesModel $modelo): bool
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
     * Envío de object SucursalesModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     */
    public function recordActive(SucursalesModel $modelo): bool
    {

        $response = false;


        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "UPDATE sucursales SET ";
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
     * Get the value of nombre
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set the value of nombre
     */
    public function setNombre($nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get the value of telefono
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set the value of telefono
     */
    public function setTelefono($telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     */
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }
}
