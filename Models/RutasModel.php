<?php

/**
 * Clase RutasModel
 */
class RutasModel extends Mysql
{
    private $id;
    private $empresa_id;
    private $sucursal_id;
    private $activo;

    private $rutas;


    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    const TABLA = "rutas";
    const PREFIJO_TABLA = "ssf_";


    /**
     * Método Constructor de RutasModel.
     * Inicializa Mysql::__construct
     * 
     */
    public function __construct()
    {
        parent::__construct();
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
    public function selectRecord(RutasModel $modelo): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, us.usuario, s.nombre as sucursal, ";
            $sql .= "CONCAT_WS(' ', dat_gen.nombre, dat_gen.paterno, dat_gen.materno)  as usuario ";
            $sql .= "FROM " . self::PREFIJO_TABLA . self::TABLA . " t ";
            $sql .= "INNER JOIN  " . self::PREFIJO_TABLA . "sucursales s ON (s.id = t.sucursal_id) ";
            $sql .= "INNER JOIN  " . self::PREFIJO_TABLA . "usuarios us ON (us.id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales dat_gen ON (dat_gen.usuario_id = us.id) ";
            $sql .= "WHERE ";
            $sql .= "t.empresa_id = :empresa_id";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'empresa_id' => $modelo->getEmpresaId()
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
    public function updateRecord(RutasModel $modelo): bool
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
    private function recordUpdate(RutasModel &$modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . self::TABLA . " SET ";
            $sql .= "rutas = :rutas, ";
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
                'rutas' => $modelo->getRutas(),
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
     * Get the value of rutas
     */
    public function getRutas()
    {
        return $this->rutas;
    }

    /**
     * Set the value of rutas
     */
    public function setRutas($rutas): self
    {
        $this->rutas = $rutas;

        return $this;
    }
}
