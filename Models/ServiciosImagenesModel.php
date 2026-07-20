<?php

/**
 * Clase ServiciosImagenesModel
 */
class ServiciosImagenesModel extends Mysql
{

    private $id;
    private $empresa_id;
    private $sucursal_id;

    private $name;
    private $descripcion;

    private $slug;
    private $image;
    private $servicio_id;

    private $activo;

    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    private $files;
    private $session;

    const CONSTANTES_MODEL = array(
        "tabla" => "serviciosimagenes"
    );


    /**
     * Método Constructor de ServiciosImagenesModel.
     * Inicializa Mysql::__construct
     * 
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        [ Variables de Sesion]*/
        $this->setSession(new Session);
    }

    /**
     * Obtiene la lista de registros para llenar DataTable o Selects
     * 
     * @return array $arrResponse
     * 
     */
    public function selectRecords(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, us.usuario, s.name as servicio ";
            $sql .= "FROM " . ServiciosImagenesModel::CONSTANTES_MODEL['tabla'] . " t ";
            $sql .= "INNER JOIN usuarios us ON (us.id = t.usuario_id_updated) ";
            $sql .= "LEFT JOIN servicios s ON (s.id = t.servicio_id) ";

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
     * Obtiene datos de un Registro determinado.
     * 
     * @return array $arrResponse
     * * array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * 
     */
    public function selectRecord(int $record_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, CONCAT_WS(' ', dat_gen.nombre, dat_gen.paterno, dat_gen.materno)  as usuario, s.name as servicio ";
            $sql .= "FROM " . ServiciosImagenesModel::CONSTANTES_MODEL['tabla'] . " t ";
            $sql .= "INNER JOIN usuarios usr ON (usr.id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN usuarios_datos_generales dat_gen ON (dat_gen.usuario_id = usr.id) ";
            $sql .= "LEFT JOIN servicios s ON (s.id = t.servicio_id) ";
            $sql .= "WHERE ";
            $sql .= "t.id = :record_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'record_id' => $record_id
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
    public function validInsertExist(string $texto_valida): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT id FROM " . ServiciosImagenesModel::CONSTANTES_MODEL['tabla'] . " ";
            $sql .= "WHERE ";
            $sql .= "name = :texto_valida";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'texto_valida' => $texto_valida
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
    public function validUpdateExistRecord(string $texto_valida, int $id): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "";
            $sql .= "SELECT * FROM " . ServiciosImagenesModel::CONSTANTES_MODEL['tabla'] . " ";
            $sql .= "WHERE ";
            $sql .= "id != :id and name = :texto_valida";


            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'texto_valida' => $texto_valida,
                'id' => $id
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
     * @param model &$model
     * Envío del modelo \ServiciosImagenesModel por referencia, para asiganr el valor lastInsertId al modelo.
     *
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function insertRecord(ServiciosImagenesModel &$modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();


            /*-------------------------------------------
            [ Registrar ]*/
            $id = $this->recordCreate($modelo);
            if ($id > 0) {
                if ($this->insertAdjunto($modelo)) {
                    $response = true;

                    /*-------------------------------------------
                    [ Commit Transaction ]*/
                    $this->getConexion()->commit();
                }
            } else {

                $response = false;
                /*-------------------------------------------
                [ RollBack ]*/
                $this->getConexion()->rollBack();
            }
        } catch (\Throwable $th) {
            $this->getConexion()->rollBack();
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Subrutina dentro de insertRecord para crear el registro
     * 
     * @param object ServiciosImagenesModel $model
     * Envío del modelo por referencia con los datos requeridos para el insert
     *
     */
    public function recordCreate(ServiciosImagenesModel &$modelo): int
    {


        $repsonse = 0;

        /*-------------------------------------------
            [ Instruccion sql ]*/
        $sql = "INSERT INTO " . ServiciosImagenesModel::CONSTANTES_MODEL['tabla'] . " SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "servicio_id = :servicio_id, ";
        $sql .= "image = :image, ";
        $sql .= "activo = :activo, ";
        $sql .= "created_at = current_timestamp, ";
        $sql .= "usuario_id_created = :usuario_id_register, ";
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";

        /*-------------------------------------------
            [ Datos a insertar ]*/
        $arrData = [
            'empresa_id' => $modelo->getEmpresaId(),
            'sucursal_id' => $modelo->getSucursalId(),
            'servicio_id' => $modelo->getServicioId(),
            'image' => $modelo->getImage(),
            'activo' => $modelo->getActivo(),
            'usuario_id_register' => $modelo->getUsuarioIdCreated()
        ];

        /*-------------------------------------------
        [ Ejecuta el Metodo insert de MySQL ]*/
        $lastInsertId = $this->insert($sql, $arrData);
        $repsonse = $lastInsertId;

        /*-------------------------------------------
        [ Asigna por referencia el valor del id insertado ]*/
        $modelo->setId($lastInsertId);

        /*-------------------------------------------
        [ Retorna Respuesta ]*/
        return $repsonse;
    }


    /**
     * Subrutina dentro de updateRecord o insertRecord para insertar los videos adjuntos
     * 
     * @param object $ServiciosImagenesModel 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function insertAdjunto(ServiciosImagenesModel $modelo): bool
    {

        try {


            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen de los arhvios adjuntos ]*/
            $file = $modelo->getFiles();

            if (empty($file)) {
                return true;
            }

            if ($file['adjunto']['name'] == '') {
                return true;
            }

            $ruta_doctos = "Assets/files/serviciosimagenes/";

            /*-------------------------------------------
            [ En caso de ser solo actualización de datos, se retorna true ]*/
            if ($file['adjunto']['error'] == 0) {
                $tmp_cer1 = $file['adjunto']['tmp_name'];
                $ruta_file1 =  $ruta_doctos . $modelo->getImage();
                $response_up_file1 =  move_uploaded_file($tmp_cer1, $ruta_file1);
                if ($response_up_file1 == false) {
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

    /**
     * Actualiza datos de un Registro determinado.
     * 
     * @param object $model
     * Envío del modelo por valor, que contine la información a actualizar y 
     * los parámetros condicionales para realizar el update.
     * 
     * @param int $usuario_id_register
     * Usuario que realiza el registro.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateRecord(ServiciosImagenesModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Actualizar Datos Generales de Usuario ]*/

            $result = $this->recordUpdate($modelo);
            if ($result) {
                if ($this->insertAdjunto($modelo)) {
                    $response = true;

                    /*-------------------------------------------
                    [ Commit Transaction ]*/
                    $this->getConexion()->commit();
                }
            } else {

                /*-------------------------------------------
                [ RollBack ]*/
                $this->getConexion()->rollBack();
            }
        } catch (\Throwable $th) {
            /*-------------------------------------------
            [ Roll Back ]*/
            $this->getConexion()->rollBack();
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }


    /**
     * Subrutina dentro de updateRecord para crear el registro
     * 
     * @param object ServiciosImagenesModel $model
     * Envío del modelo por referencia con los datos requeridos para el update
     *
     *
     */
    public function recordUpdate(ServiciosImagenesModel &$modelo): bool
    {

        $repsonse = false;

        /*-------------------------------------------
            [ Instruccion sql ]*/
        $sql = "UPDATE " . ServiciosImagenesModel::CONSTANTES_MODEL['tabla'] . " SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "servicio_id = :servicio_id, ";
        if ($modelo->getImage() != '') {
            $sql .= "image = '" . $modelo->getImage() . "', ";
        }
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";
        $sql .= "WHERE  ";
        $sql .= "id = :id ";

        /*-------------------------------------------
        [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
        $arrData = [
            'id' => $modelo->getId(),
            'empresa_id' => $modelo->getEmpresaId(),
            'sucursal_id' => $modelo->getSucursalId(),
            'servicio_id' => $modelo->getServicioId(),
            'usuario_id_register' => $modelo->getUsuarioIdCreated()
        ];

        /*-------------------------------------------
        [ Ejecuta el Metodo insert de MySQL ]*/
        $repsonse = $this->update($sql, $arrData);

        /*-------------------------------------------
        [ Retorna Respuesta ]*/
        return $repsonse;
    }



    /**
     * Actualiza el estatus activo/inactivo de un Registro determinado.
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
    public function updateEstatusRecord(ServiciosImagenesModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . ServiciosImagenesModel::CONSTANTES_MODEL['tabla'] . " SET ";
            $sql .= "activo = :activo, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE  ";
            $sql .= "id = :id ";

            /*-------------------------------------------
            [ Parámetros condicionales para realizar el update ]*/
            $arrData = [
                'id' => $model->getId(),
                'activo' => $model->getActivo(),
                'usuario_id_register' => $model->getUsuarioIdCreated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo delete de MySQL ]*/
            $respuesta = $this->delete($sql, $arrData);


            if ($respuesta == true) {

                $response = true;
                /*-------------------------------------------
                [ Commit Transaction ]*/
                $this->getConexion()->commit();
            } else {

                /*-------------------------------------------
                [ RollBack ]*/
                $this->getConexion()->rollBack();
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

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
     * Get the value of slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     */
    public function setSlug($slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the value of image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     */
    public function setImage($image): self
    {
        $this->image = $image;

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
     * Get the value of servicio_id
     */
    public function getServicioId()
    {
        return $this->servicio_id;
    }

    /**
     * Set the value of servicio_id
     */
    public function setServicioId($servicio_id): self
    {
        $this->servicio_id = $servicio_id;

        return $this;
    }
}
