<?php

/**
 * Clase PermisosModel
 */
class PermisosModel extends Mysql
{



    private $id;
    private $rol_id;
    private $permisos;
    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    const TABLA = "permisos";
    const PREFIJO_TABLA = "ssf_";


    /**
     * Método Constructor de PermisosModel.
     * Inicializa Mysql::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene la lista del catálogo de modulos.
     * 
     * @return array $arrResponse
     * 
     */
    public function selectModulos()
    {

        try {

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT t.* FROM " . self::PREFIJO_TABLA . "modulos t WHERE t.activo = 1 ORDER BY t.orden";

            /*-------------------------------------------
            [ Paramteros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);

            /*-------------------------------------------
            [ Retorna array con la lista de registros ]*/
            return $arrResponse;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Obtiene la lista de permisos de un rol determinado.
     * 
     * @return array $arrResponse
     * 
     */
    public function selectPermisosRol(int $rol_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT t.permisos ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " t ";
            $sql .= "WHERE t.rol_id = :rol_id";

            /*-------------------------------------------
            [ Paramteros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'rol_id' => $rol_id
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->selectModel($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros ]*/
        return $arrResponse;
    }

    /**
     * Elimina todos los permisos de un rol determinado.
     * 
     * @param int $rol_id
     * Identificador de rol que se va a elminar los permisos
     * 
     * @return bool $response
     * * true si se ejecuto correctamente.
     * * false en caso de falla. 
     * 
     */
    public function deletePermisos(int $rol_id): bool
    {

        try {

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "DELETE FROM " . self::PREFIJO_TABLA . SELF::TABLA . " WHERE rol_id = :rol_id";

            /*-------------------------------------------
            [ Paramteros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'rol_id' =>  $rol_id
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo delete de MySQL ]*/
            $response = $this->delete($sql, $arr_values);

            /*-------------------------------------------
            [ Retorna true|false ]*/
            return $response;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Inserta todos los permisos seleccionados para un rol determinado.
     * 
     * @param string $permisos
     * Permisos de Rol en formato json
     * 
     * @param string $permisos
     * Permisos de Rol en formato json
     * 
     * @param int $usuario_id_register
     * Identificador de usuario que realiza el registro
     * 
     * @return bool $result.
     * true = exitoso
     * false = en caso de falla
     * 
     */
    public function insertPermisos(string $permisos, int $rol_id,  int $usuario_id_register): bool
    {

        try {

            $result = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "INSERT INTO " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
            $sql .= "permisos = :permisos, ";
            $sql .= "rol_id = :rol_id, ";
            $sql .= "created_at = current_timestamp, ";
            $sql .= "usuario_id_created = :usuario_id_register, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";

            /*-------------------------------------------
            [ Paramteros condicionales, se envía vacío en caso de no aplicar ]*/
            $arrData = [
                'rol_id' => $rol_id,
                'permisos' => $permisos,
                'usuario_id_register' => $usuario_id_register
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $response = $this->insert($sql, $arrData);

            /*-------------------------------------------
            [ Retorna true|false ]*/
            $result = $response > 0 ? true : false;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            $result = false;
        }

        return $result;
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
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of rol_id
     */
    public function getRol_id()
    {
        return $this->rol_id;
    }

    /**
     * Set the value of rol_id
     *
     * @return  self
     */
    public function setRol_id($rol_id)
    {
        $this->rol_id = $rol_id;

        return $this;
    }

    /**
     * Get the value of permisos
     */
    public function getPermisos()
    {
        return $this->permisos;
    }

    /**
     * Set the value of permisos
     *
     * @return  self
     */
    public function setPermisos($permisos)
    {
        $this->permisos = $permisos;

        return $this;
    }

    /**
     * Get the value of created_at
     */
    public function getCreated_at()
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */
    public function setCreated_at($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get the value of usuario_id_created
     */
    public function getUsuario_id_created()
    {
        return $this->usuario_id_created;
    }

    /**
     * Set the value of usuario_id_created
     *
     * @return  self
     */
    public function setUsuario_id_created($usuario_id_created)
    {
        $this->usuario_id_created = $usuario_id_created;

        return $this;
    }

    /**
     * Get the value of updated_at
     */
    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    /**
     * Set the value of updated_at
     *
     * @return  self
     */
    public function setUpdated_at($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Get the value of usuario_id_updated
     */
    public function getUsuario_id_updated()
    {
        return $this->usuario_id_updated;
    }

    /**
     * Set the value of usuario_id_updated
     *
     * @return  self
     */
    public function setUsuario_id_updated($usuario_id_updated)
    {
        $this->usuario_id_updated = $usuario_id_updated;

        return $this;
    }
}
