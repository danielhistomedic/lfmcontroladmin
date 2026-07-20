<?php

/**
 * Clase EmpresasModel
 */
class EmpresasModel extends Mysql
{

    /*-------------------------------------------
    [ table empresas ]*/
    private $id;
    private $tipo_licencia_id;
    private $estatus_licencia_id;
    private $fecha_limite_prueba;
    private $nombre;
    private $usuario_id;

    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    const TABLA = "empresas_usuarios";
    const PREFIJO_TABLA = "ssf_";

    /**
     * Método Constructor de EmpresasModel.
     * Inicializa Mysql::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }



    /**
     * Obtiene total de Empresas a las que pertenece un usuario determinado
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return array $arrResponse
     * * array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * 
     */
    public function getEmpresasUsuarios(EmpresasModel $model): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, e.nombre as empresa, s.nombre as sucursal ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " t ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "empresas e ON (e.id = t.empresa_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "sucursales s ON (s.id = t.sucursal_id) ";
            $sql .= "WHERE ";
            $sql .= "t.usuario_id = :usuario_id";

            /*-------------------------------------------
            [ Parametros condicionales ]*/
            $arr_values = [
                'usuario_id' => $model->getUsuarioId()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $arrResponse = $this->select($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array asociativo con los datos del registro o empty en caso de error ]*/
        return $arrResponse;
    }


    //*==================================================================
    // [ GETTERS & SETTERS ]*/



    /**
     * Get /*-------------------------------------------
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set /*-------------------------------------------
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of tipo_licencia_id
     */
    public function getTipoLicenciaId()
    {
        return $this->tipo_licencia_id;
    }

    /**
     * Set the value of tipo_licencia_id
     */
    public function setTipoLicenciaId($tipo_licencia_id): self
    {
        $this->tipo_licencia_id = $tipo_licencia_id;

        return $this;
    }

    /**
     * Get the value of estatus_licencia_id
     */
    public function getEstatusLicenciaId()
    {
        return $this->estatus_licencia_id;
    }

    /**
     * Set the value of estatus_licencia_id
     */
    public function setEstatusLicenciaId($estatus_licencia_id): self
    {
        $this->estatus_licencia_id = $estatus_licencia_id;

        return $this;
    }

    /**
     * Get the value of fecha_limite_prueba
     */
    public function getFechaLimitePrueba()
    {
        return $this->fecha_limite_prueba;
    }

    /**
     * Set the value of fecha_limite_prueba
     */
    public function setFechaLimitePrueba($fecha_limite_prueba): self
    {
        $this->fecha_limite_prueba = $fecha_limite_prueba;

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
     * Get the value of usuario_id
     */
    public function getUsuarioId()
    {
        return $this->usuario_id;
    }

    /**
     * Set the value of usuario_id
     */
    public function setUsuarioId($usuario_id): self
    {
        $this->usuario_id = $usuario_id;

        return $this;
    }
}
