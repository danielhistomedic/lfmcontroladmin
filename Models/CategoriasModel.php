<?php

/**
 * Clase CategoriasModel
 */
class CategoriasModel extends Mysql
{

    private $id;
    private $empresa_id;
    private $sucursal_id;

    private $name;
    private $descripcion;

    private $slug;
    private $image;

    private $activo;

    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    private $files;

    const TABLA = "categorias";
    const PREFIJO_TABLA = "ssf_";


    /**
     * Método Constructor de CategoriasModel.
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
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return array $arrResponse
     * * Retorna array de tipo:
     *   fetchAll(PDO::FETCH_ASSOC): returns an array containing all of the remaining rows in the result set. 
     * 
     */
    public function selectRecords(CategoriasModel $model): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, us.usuario ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " t ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios us ON (us.id = t.usuario_id_updated) ";
            $sql .= "WHERE  ";
            $sql .= "t.empresa_id = :empresa_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                "empresa_id" => $model->getEmpresaId()
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
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * 
     * @return array $arrResponse
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectRecord(CategoriasModel $model): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, CONCAT_WS(' ', dat_gen.nombre, dat_gen.paterno, dat_gen.materno)  as usuario ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " t ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios usr ON (usr.id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales dat_gen ON (dat_gen.usuario_id = usr.id) ";
            $sql .= "WHERE ";
            $sql .= "t.id = :record_id and ";
            $sql .= "t.empresa_id = :empresa_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'record_id' => $model->getId(),
                'empresa_id' => $model->getEmpresaId()
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
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return bool $response
     * * true indica si el registro ya existe.
     * * false en caso de que no exista.
     * 
     */
    public function validInsertExist(CategoriasModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT id FROM " . self::PREFIJO_TABLA . SELF::TABLA . " ";
            $sql .= "WHERE ";
            $sql .= "t.empresa_id = :empresa_id and ";
            $sql .= "t.name = :name";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'empresa_id' => $model->getEmpresaId(),
                'name' => $model->getName()
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
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return bool $response
     * * true indica si el registro ya existe.
     * * false en caso de que no exista.
     * 
     */
    public function validUpdateExistRecord(CategoriasModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "";
            $sql .= "SELECT * FROM " . self::PREFIJO_TABLA . SELF::TABLA . " ";
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
     * @param object $modelo
     * Envío del modelo por valor.
     *
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function insertRecord(CategoriasModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Registrar ]*/
            $id = $this->recordCreate($model);
            if ($id > 0) {
                $model->setId($id);
                if ($this->insertAdjunto($model)) {
                    $this->getConexion()->commit();
                    return true;
                }
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ rollBack ]*/
        $this->getConexion()->rollBack();

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }


    /**
     * Subrutina dentro de insertRecord para crear el registro
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     * @return int $response
     * * > 0 - indica que fue exitoso.
     * * = 0 - en caso de falla.
     * 
     * 
     */
    public function recordCreate(CategoriasModel $model): int
    {


        $response = 0;

        /*-------------------------------------------
        [ Instruccion sql ]*/
        $sql = "INSERT INTO " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "name = :name, ";
        $sql .= "descripcion = :descripcion, ";
        $sql .= "slug = :slug, ";
        $sql .= "image = :image, ";
        $sql .= "activo = :activo, ";
        $sql .= "created_at = current_timestamp, ";
        $sql .= "usuario_id_created = :usuario_id_register, ";
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";

        /*-------------------------------------------
        [ Datos a insertar ]*/
        $arrData = [
            'empresa_id' => $model->getEmpresaId(),
            'sucursal_id' => $model->getSucursalId(),
            'name' => $model->getName(),
            'descripcion' => $model->getDescripcion(),
            'slug' => $model->getSlug(),
            'image' => $model->getImage(),
            'activo' => $model->getActivo(),
            'usuario_id_register' => $model->getUsuarioIdCreated()
        ];

        /*-------------------------------------------
        [ Ejecuta el Metodo insert de MySQL ]*/
        $lastInsertId = $this->insert($sql, $arrData);
        $response = $lastInsertId;

        /*-------------------------------------------
        [ Retorna Respuesta ]*/
        return $response;
    }


    /**
     * Subrutina dentro de updateRecord o insertRecord para insertar los videos adjuntos
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    private function insertAdjunto(CategoriasModel $model): bool
    {

        try {


            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen de los arhvios adjuntos ]*/
            $file = $model->getFiles();

            if (empty($file)) {
                return true;
            }

            if ($file['adjunto']['name'] == '') {
                return true;
            }

            $ruta_doctos = "Assets/files/categorias/";

            /*-------------------------------------------
            [ En caso de ser solo actualización de datos, se retorna true ]*/
            if ($file['adjunto']['error'] == 0) {
                $tmp_cer1 = $file['adjunto']['tmp_name'];
                $ruta_file1 =  $ruta_doctos . $model->getImage();
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
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateRecord(CategoriasModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Actualizar Datos de Registro ]*/
            if ($this->recordUpdate($model)) {
                if ($this->insertAdjunto($model)) {
                    $this->getConexion()->commit();
                    return true;
                }
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
     * Subrutina dentro de updateRecord para crear el registro
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     *
     */
    public function recordUpdate(CategoriasModel $model): bool
    {

        $repsonse = false;

        /*-------------------------------------------
            [ Instruccion sql ]*/
        $sql = "UPDATE " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "name = :name, ";
        $sql .= "descripcion = :descripcion, ";
        $sql .= "slug = :slug, ";
        if ($model->getImage() != '') {
            $sql .= "image = '" . $model->getImage() . "', ";
        }
        $sql .= "updated_at = current_timestamp, ";
        $sql .= "usuario_id_updated = :usuario_id_register ";
        $sql .= "WHERE  ";
        $sql .= "id = :id ";

        /*-------------------------------------------
        [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
        $arrData = [
            'id' => $model->getId(),
            'empresa_id' => $model->getEmpresaId(),
            'sucursal_id' => $model->getSucursalId(),
            'name' => $model->getName(),
            'descripcion' => $model->getDescripcion(),
            'slug' => $model->getSlug(),
            'usuario_id_register' => $model->getUsuarioIdUpdated()
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
     * Envío del modelo por valor.
     *
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateEstatusRecord(CategoriasModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
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
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo update de MySQL ]*/
            $respuesta = $this->update($sql, $arrData);
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
}
