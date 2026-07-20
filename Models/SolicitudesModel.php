<?php

/**
 * Clase SolicitudesModel
 */
class SolicitudesModel extends Mysql
{

    private $id;
    private $empresa_id;
    private $sucursal_id;
    private $nombre;
    private $telefono;
    private $email;
    private $adjunto_identificacion;
    // private $adjunto_aviso_funcionamiento;
    private $adjunto_cofepris;
    private $adjunto_csf;
    private $adjunto_comprobante_domicilio;

    private $atendida;
    private $updated_atendio;
    private $usuario_id_atendio;

    private $activo;
    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;
    private $comentarios;

    const TABLA = "solicitudes_clientes";
    const PREFIJO_TABLA = "ssf_";

    /**
     * Método Constructor de SolicitudesModel.
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
    public function selectRecords(SolicitudesModel $modelo, bool $ordenarAlfabeticamente = false): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, us.usuario, s.nombre as sucursal, ";
            $sql .= "CONCAT_WS(' ', dat_gen.nombre, dat_gen.paterno, dat_gen.materno)  as usuario, ";
            $sql .= "CONCAT_WS(' ', dat_gen_uat.nombre, dat_gen_uat.paterno, dat_gen_uat.materno) as usuario_atendio ";
            $sql .= "FROM " . self::PREFIJO_TABLA . self::TABLA . " t ";
            $sql .= "INNER JOIN  " . self::PREFIJO_TABLA . "sucursales s ON (s.id = t.sucursal_id) ";
            $sql .= "INNER JOIN  " . self::PREFIJO_TABLA . "usuarios us ON (us.id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales dat_gen ON (dat_gen.usuario_id = us.id) ";
            $sql .= "LEFT JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales dat_gen_uat ON (dat_gen_uat.usuario_id = t.usuario_id_atendio) ";
            $sql .= "WHERE ";
            $sql .= "t.empresa_id = :empresa_id ";
            if ($ordenarAlfabeticamente) {
                $sql .= "ORDER BY t.nombre";
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
    public function selectRecord(SolicitudesModel $modelo): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, us.usuario, s.nombre as sucursal, ";
            $sql .= "CONCAT_WS(' ', dat_gen.nombre, dat_gen.paterno, dat_gen.materno)  as usuario, ";
            $sql .= "CONCAT_WS(' ', dat_gen_uat.nombre, dat_gen_uat.paterno, dat_gen_uat.materno) as usuario_atendio ";
            $sql .= "FROM " . self::PREFIJO_TABLA . self::TABLA . " t ";
            $sql .= "INNER JOIN  " . self::PREFIJO_TABLA . "sucursales s ON (s.id = t.sucursal_id) ";
            $sql .= "INNER JOIN  " . self::PREFIJO_TABLA . "usuarios us ON (us.id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales dat_gen ON (dat_gen.usuario_id = us.id) ";
            $sql .= "LEFT JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales dat_gen_uat ON (dat_gen_uat.usuario_id = t.usuario_id_atendio) ";
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
     * Validación para evitar registros existentes se puedan duplicar
     * 
     * @return bool $response
     * * true indica si el registro ya existe.
     * * false en caso de que no exista.
     * 
     */
    public function validUpdateExistRecord(SolicitudesModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT t.nombre FROM " . self::PREFIJO_TABLA . self::TABLA . " t ";
            $sql .= "WHERE ";
            $sql .= "t.id != :id and ";
            $sql .= "t.empresa_id = :empresa_id and ";
            $sql .= "t.nombre = :nombre";


            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'id' => $modelo->getId(),
                'empresa_id' => $modelo->getEmpresaId(),
                'nombre' => $modelo->getNombre()
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
    public function updateRecord(SolicitudesModel $modelo): bool
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
    private function recordUpdate(SolicitudesModel &$modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . self::TABLA . " SET ";
            $sql .= "atendida = :atendida, ";
            $sql .= "comentarios = :comentarios, ";
            $sql .= "updated_atendio = current_timestamp, ";
            $sql .= "usuario_id_atendio = :usuario_id_register ";
            $sql .= "WHERE  ";
            $sql .= "empresa_id = :empresa_id and ";
            $sql .= "id = :id ";

            /*-------------------------------------------
            [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
            $arrData = [
                'id' => $modelo->getId(),
                'empresa_id' => $modelo->getEmpresaId(),
                'atendida' => $modelo->getAtendida(),
                'comentarios' => $modelo->getComentarios(),
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
    public function updateEstatusRecord(SolicitudesModel $model): bool
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

    /**
     * Get the value of adjunto_identificacion
     */
    public function getAdjuntoIdentificacion()
    {
        return $this->adjunto_identificacion;
    }

    /**
     * Set the value of adjunto_identificacion
     */
    public function setAdjuntoIdentificacion($adjunto_identificacion): self
    {
        $this->adjunto_identificacion = $adjunto_identificacion;

        return $this;
    }

    // /**
    //  * Get the value of adjunto_aviso_funcionamiento
    //  */
    // public function getAdjuntoAvisoFuncionamiento()
    // {
    //     return $this->adjunto_aviso_funcionamiento;
    // }

    // /**
    //  * Set the value of adjunto_aviso_funcionamiento
    //  */
    // public function setAdjuntoAvisoFuncionamiento($adjunto_aviso_funcionamiento): self
    // {
    //     $this->adjunto_aviso_funcionamiento = $adjunto_aviso_funcionamiento;

    //     return $this;
    // }

    /**
     * Get the value of adjunto_cofepris
     */
    public function getAdjuntoCofepris()
    {
        return $this->adjunto_cofepris;
    }

    /**
     * Set the value of adjunto_cofepris
     */
    public function setAdjuntoCofepris($adjunto_cofepris): self
    {
        $this->adjunto_cofepris = $adjunto_cofepris;

        return $this;
    }

    /**
     * Get the value of adjunto_csf
     */
    public function getAdjuntoCsf()
    {
        return $this->adjunto_csf;
    }

    /**
     * Set the value of adjunto_csf
     */
    public function setAdjuntoCsf($adjunto_csf): self
    {
        $this->adjunto_csf = $adjunto_csf;

        return $this;
    }

    /**
     * Get the value of adjunto_comprobante_domicilio
     */
    public function getAdjuntoComprobanteDomicilio()
    {
        return $this->adjunto_comprobante_domicilio;
    }

    /**
     * Set the value of adjunto_comprobante_domicilio
     */
    public function setAdjuntoComprobanteDomicilio($adjunto_comprobante_domicilio): self
    {
        $this->adjunto_comprobante_domicilio = $adjunto_comprobante_domicilio;

        return $this;
    }

    /**
     * Get the value of atendida
     */
    public function getAtendida()
    {
        return $this->atendida;
    }

    /**
     * Set the value of atendida
     */
    public function setAtendida($atendida): self
    {
        $this->atendida = $atendida;

        return $this;
    }

    /**
     * Get the value of updated_atendio
     */
    public function getUpdatedAtendio()
    {
        return $this->updated_atendio;
    }

    /**
     * Set the value of updated_atendio
     */
    public function setUpdatedAtendio($updated_atendio): self
    {
        $this->updated_atendio = $updated_atendio;

        return $this;
    }

    /**
     * Get the value of usuario_id_atendio
     */
    public function getUsuarioIdAtendio()
    {
        return $this->usuario_id_atendio;
    }

    /**
     * Set the value of usuario_id_atendio
     */
    public function setUsuarioIdAtendio($usuario_id_atendio): self
    {
        $this->usuario_id_atendio = $usuario_id_atendio;

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
     * Get the value of comentarios
     */
    public function getComentarios()
    {
        return $this->comentarios;
    }

    /**
     * Set the value of comentarios
     */
    public function setComentarios($comentarios): self
    {
        $this->comentarios = $comentarios;

        return $this;
    }
}
