<?php

/**
 * Clase ProductosModel
 */
class ProductosModel extends Mysql
{

    private $id;
    private $empresa_id;
    private $sucursal_id;

    private $name;
    private $descripcion;

    private $slug;
    private $image;
    private $rate;
    private $recomendaciones_mes;

    private $activo;

    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    private $files;
    private $session;
    private $acceso_global;

    const CONSTANTES_MODEL = array(
        "tabla" => "productos"
    );


    //Nuevos Campos
    private $alterna;
    private $marca_id;
    private $linea_producto_id;
    private $categorias;
    private $sku;
    private $precios;
    private $oferta;
    private $precio_oferta;
    private $unidad_medida_id;
    private $limite_minimo;
    private $cantidad;
    private $ventas_dirigidas;
    private $ventas_cruzadas;




    /**
     * Método Constructor de ProductosModel.
     * Inicializa Mysql::__construct
     * 
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        [ Variables de Sesion]*/
        $this->setSession(new Session);

        /*-------------------------------------------
        [ Validación de Acceso Global ]*/
        $arrPermisos = getPermisosGlobal();
        $this->setAccesoGlobal($arrPermisos[MOD_ACCESO_GLOBAL]);
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
            $sql .= "t.*, ";
            $sql .= "m.name as marca, ";
            $sql .= "lp.name as linea_producto, ";
            $sql .= "um.name as unidad_medida, ";
            $sql .= "us.usuario ";
            $sql .= "FROM " . ProductosModel::CONSTANTES_MODEL['tabla'] . " t ";
            $sql .= "INNER JOIN usuarios us ON (us.id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN marcas m ON (m.id = t.marca_id) ";
            $sql .= "INNER JOIN lineasproducto lp ON (lp.id = t.linea_producto_id) ";
            $sql .= "INNER JOIN unidadesmedida um ON (um.id = t.unidad_medida_id) ";

            if (!$this->getAccesoGlobal()['r']) {
                $sql .= "WHERE  ";
                $sql .= "t.sucursal_id = " . $this->getSession()->get('sucursal_id') . " ";
            }

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
            $sql .= "t.*, ";
            $sql .= "m.name as marca, ";
            $sql .= "lp.name as linea_producto, ";
            $sql .= "um.name as unidad_medida, ";
            $sql .= "CONCAT_WS(' ', dat_gen.nombre, dat_gen.paterno, dat_gen.materno)  as usuario ";
            $sql .= "FROM " . ProductosModel::CONSTANTES_MODEL['tabla'] . " t ";
            $sql .= "INNER JOIN usuarios usr ON (usr.id = t.usuario_id_updated) ";
            $sql .= "INNER JOIN usuarios_datos_generales dat_gen ON (dat_gen.usuario_id = usr.id) ";
            $sql .= "INNER JOIN marcas m ON (m.id = t.marca_id) ";
            $sql .= "INNER JOIN lineasproducto lp ON (lp.id = t.linea_producto_id) ";
            $sql .= "INNER JOIN unidadesmedida um ON (um.id = t.unidad_medida_id) ";
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
            $sql = "SELECT id FROM " . ProductosModel::CONSTANTES_MODEL['tabla'] . " ";
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
            $sql .= "SELECT * FROM " . ProductosModel::CONSTANTES_MODEL['tabla'] . " ";
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
     * Envío del modelo \ProductosModel por referencia, para asiganr el valor lastInsertId al modelo.
     *
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function insertRecord(ProductosModel &$modelo): bool
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
                if ($this->insertImagenes($modelo)) {
                    if ($this->insertExistencias($modelo)) {
                        if ($this->insertCategorias($modelo)) {
                            if ($this->insertPrecios($modelo)) {
                                if ($this->insertVentasDirigidas($modelo)) {
                                    if ($this->insertVentasCruzadas($modelo)) {
                                        $this->getConexion()->commit();
                                        return true;
                                    }
                                }
                            }
                        }
                    }
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
     * Subrutina dentro de insertRecord para crear el registro
     * 
     * @param object ProductosModel $model
     * Envío del modelo por referencia con los datos requeridos para el insert
     *
     */
    public function recordCreate(ProductosModel &$modelo): int
    {


        $repsonse = 0;

        /*-------------------------------------------
            [ Instruccion sql ]*/
        $sql = "INSERT INTO " . ProductosModel::CONSTANTES_MODEL['tabla'] . " SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "name = :name, ";
        $sql .= "descripcion = :descripcion, ";
        $sql .= "slug = :slug, ";
        $sql .= "activo = :activo, ";
        $sql .= "alterna = :alterna, ";
        $sql .= "marca_id = :marca_id, ";
        $sql .= "linea_producto_id = :linea_producto_id, ";
        $sql .= "sku = :sku, ";
        $sql .= "oferta = :oferta, ";
        $sql .= "precio_oferta = :precio_oferta, ";
        $sql .= "unidad_medida_id = :unidad_medida_id, ";
        $sql .= "rate = :rate, ";
        $sql .= "recomendaciones_mes = :recomendaciones_mes, ";
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
            'slug' => $modelo->getSlug(),
            'activo' => $modelo->getActivo(),
            'alterna' => $modelo->getAlterna(),
            'marca_id' => $modelo->getMarcaId(),
            'linea_producto_id' => $modelo->getLineaProductoId(),
            'sku' => $modelo->getSku(),
            'oferta' => $modelo->getOferta(),
            'precio_oferta' => $modelo->getPrecioOferta(),
            'unidad_medida_id' => $modelo->getUnidadMedidaId(),
            'rate' => $modelo->getRate(),
            'recomendaciones_mes' => $modelo->getRecomendacionesMes(),
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
     * @param object $ProductosModel 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function insertImagenes(ProductosModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen y de los arhvios adjuntos ]*/
            $arrFiles = $modelo->getFiles();
            $producto_id = $modelo->getId();
            $ruta_doctos = "Assets/files/productos/";

            if (empty($arrFiles)) {
                return true;
            }

            /*-------------------------------------------
            [ Delete registros ]*/
            // $sql = "DELETE FROM productos_imagenes WHERE producto_id = :producto_id";
            // $arrDataDelete = ['producto_id' => $producto_id];
            // if (!$this->delete($sql, $arrDataDelete)) {
            //     return false;
            // };


            $contador = count($arrFiles['adjunto']['name']);

            for ($i = 0; $i < $contador; $i++) {

                if (intval($arrFiles['adjunto']['error'][$i]) == 0) {

                    $arrParams = explode('.', strClean($arrFiles['adjunto']['name'][$i]));
                    $index = count($arrParams) - 1;
                    $file_extension = strClean($arrParams[$index]);

                    $file_name = strClean($arrFiles['adjunto']['name'][$i]);
                    $n = $file_name . date('YmdHis');
                    $adjunto = encode($n) . '.' . trim($file_extension);


                    $slug = hash('md5', $adjunto);

                    /*-------------------------------------------
                    [ Insertar datos ]*/
                    $sql = "INSERT INTO productos_imagenes SET ";
                    $sql .= "imagen = :imagen, ";
                    $sql .= "slug = :slug, ";
                    $sql .= "producto_id = :producto_id, ";
                    if ($i == 0) {
                        $sql .= "principal = 1 ";
                    } else {
                        $sql .= "principal = 0 ";
                    }
                    $arrData = [
                        'imagen' => $adjunto,
                        'slug' => $slug,
                        'producto_id' => $producto_id
                    ];
                    $lastInsertId = $this->insert($sql, $arrData);
                    if ($lastInsertId == 0) {
                        return false;
                    }

                    /*-------------------------------------------
                    [ En caso de ser solo actualización de datos, se retorna true ]*/
                    if (intval($arrFiles['adjunto']['error'][$i]) == 0) {
                        $tmp_cer1 = $arrFiles['adjunto']['tmp_name'][$i];
                        $ruta_file1 =  $ruta_doctos . $adjunto;
                        $response_up_file1 =  move_uploaded_file($tmp_cer1, $ruta_file1);
                        if ($response_up_file1 == false) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
            }

            /*-------------------------------------------
            [ En caso de que no haya ningun error ]*/
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
    public function updateRecord(ProductosModel $modelo): bool
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
                if ($this->updateImagenes($modelo)) {
                    if ($this->insertExistencias($modelo)) {
                        if ($this->insertCategorias($modelo)) {
                            if ($this->insertPrecios($modelo)) {
                                if ($this->insertVentasDirigidas($modelo)) {
                                    if ($this->insertVentasCruzadas($modelo)) {
                                        $this->getConexion()->commit();
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $this->getConexion()->rollBack();
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
     * @param object ProductosModel $model
     * Envío del modelo por referencia con los datos requeridos para el update
     *
     *
     */
    public function recordUpdate(ProductosModel &$modelo): bool
    {

        $repsonse = false;

        /*-------------------------------------------
            [ Instruccion sql ]*/
        $sql = "UPDATE " . ProductosModel::CONSTANTES_MODEL['tabla'] . " SET ";
        $sql .= "empresa_id = :empresa_id, ";
        $sql .= "sucursal_id = :sucursal_id, ";
        $sql .= "name = :name, ";
        $sql .= "descripcion = :descripcion, ";
        $sql .= "slug = :slug, ";
        $sql .= "alterna = :alterna, ";
        $sql .= "marca_id = :marca_id, ";
        $sql .= "linea_producto_id = :linea_producto_id, ";
        $sql .= "sku = :sku, ";
        $sql .= "oferta = :oferta, ";
        $sql .= "precio_oferta = :precio_oferta, ";
        $sql .= "unidad_medida_id = :unidad_medida_id, ";
        $sql .= "rate = :rate, ";
        $sql .= "recomendaciones_mes = :recomendaciones_mes, ";
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
            'name' => $modelo->getName(),
            'descripcion' => $modelo->getDescripcion(),
            'slug' => $modelo->getSlug(),
            'alterna' => $modelo->getAlterna(),
            'marca_id' => $modelo->getMarcaId(),
            'linea_producto_id' => $modelo->getLineaProductoId(),
            'sku' => $modelo->getSku(),
            'oferta' => $modelo->getOferta(),
            'precio_oferta' => $modelo->getPrecioOferta(),
            'unidad_medida_id' => $modelo->getUnidadMedidaId(),
            'rate' => $modelo->getRate(),
            'recomendaciones_mes' => $modelo->getRecomendacionesMes(),
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
     * Subrutina dentro de updateRecord o insertRecord para insertar los videos adjuntos
     * 
     * @param object $ProductosModel 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function updateImagenes(ProductosModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen y de los arhvios adjuntos ]*/
            $arrFiles = $modelo->getFiles();
            $producto_id = $modelo->getId();
            $ruta_doctos = "Assets/files/productos/";

            if (empty($arrFiles)) {
                return true;
            }

            $contador = count($arrFiles['adjunto']['name']);

            for ($i = 0; $i < $contador; $i++) {

                if (intval($arrFiles['adjunto']['error'][$i]) == 0) {

                    // =====================
                    $arrParams = explode('.', strClean($arrFiles['adjunto']['name'][$i]));
                    $index = count($arrParams) - 1;
                    $file_extension = strClean($arrParams[$index]);
                    $file_name = strClean($arrFiles['adjunto']['name'][$i]);
                    $n = $file_name . date('YmdHis');
                    $imagen_new = encode($n) . '.' . trim($file_extension);

                    $slug_new = hash('md5', $imagen_new);


                    /*-------------------------------------------
                    [ Obtener data de producto imagen ]*/
                    $sql = "SELECT * FROM productos_imagenes WHERE id = :image_id";
                    $arrDataImagen = ['image_id' => strClean($arrFiles['adjunto']['image_id'][$i])];
                    $arrProductoImagen = $this->selectModel($sql, $arrDataImagen);

                    if (empty($arrProductoImagen)) {

                        /*-------------------------------------------
                        [ Insertar datos ]*/
                        $sql = "INSERT INTO productos_imagenes SET ";
                        $sql .= "imagen = :imagen, ";
                        $sql .= "slug = :slug, ";
                        $sql .= "producto_id = :producto_id, ";
                        if ($i == 0) {
                            $sql .= "principal = 1 ";
                        } else {
                            $sql .= "principal = 0 ";
                        }
                        $arrData = [
                            'imagen' => $imagen_new,
                            'slug' => $slug_new,
                            'producto_id' => $producto_id
                        ];
                        $lastInsertId = $this->insert($sql, $arrData);
                        if ($lastInsertId == 0) {
                            return false;
                        }
                    } else {

                        /*-------------------------------------------
                        [ Insertar datos ]*/
                        $sql = "UPDATE productos_imagenes SET ";
                        $sql .= "imagen = :imagen_new, ";
                        $sql .= "slug = :slug_new ";
                        $sql .= "WHERE ";
                        $sql .= "id = :image_id  ";

                        $arrData = [
                            'imagen_new' => $imagen_new,
                            'slug_new' => $slug_new,
                            'image_id' => $arrProductoImagen['id']
                        ];
                        $response = $this->update($sql, $arrData);
                        if (!$response) {
                            return false;
                        }

                        /*-------------------------------------------
                        Eliminar imagen anterior de servidor ]*/
                        $ruta_file_delete =  $ruta_doctos . $arrProductoImagen['imagen'];
                        if (!unlink($ruta_file_delete)) {
                            return false;
                        }
                    }

                    /*-------------------------------------------
                    [ En caso de ser solo actualización de datos, se retorna true ]*/
                    if (intval($arrFiles['adjunto']['error'][$i]) == 0) {
                        $tmp_cer1 = $arrFiles['adjunto']['tmp_name'][$i];
                        $ruta_file1 =  $ruta_doctos . $imagen_new;
                        $response_up_file1 =  move_uploaded_file($tmp_cer1, $ruta_file1);
                        if ($response_up_file1 == false) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {

                    //Verificamos si tiene un slug pero no imagen para elimnar el registro

                    if (strClean($arrFiles['adjunto']['slug'][$i]) == '') {

                        /*-------------------------------------------
                        [ Obtener data de producto imagen ]*/
                        $sql = "SELECT * FROM productos_imagenes WHERE id = :image_id";
                        $arrDataImagen = ['image_id' => strClean($arrFiles['adjunto']['image_id'][$i])];
                        $arrProductoImagen = $this->selectModel($sql, $arrDataImagen);

                        if (!empty($arrProductoImagen)) {

                            /*-------------------------------------------
                            [ Insertar datos ]*/
                            $sql = "DELETE FROM productos_imagenes ";
                            $sql .= "WHERE ";
                            $sql .= "id = :image_id  ";

                            $arrData = [
                                'image_id' => $arrProductoImagen['id']
                            ];
                            $response = $this->delete($sql, $arrData);
                            if (!$response) {
                                return false;
                            }

                            /*-------------------------------------------
                            Eliminar imagen anterior de servidor ]*/
                            $ruta_file_delete =  $ruta_doctos . $arrProductoImagen['imagen'];
                            if (!unlink($ruta_file_delete)) {
                                return false;
                            }
                        }
                    }
                }
            }

            /*-------------------------------------------
            [ En caso de que no haya ningun error ]*/
            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }


        /*-------------------------------------------
        [ Retorna Id de Recibo Insertado ]*/
        return $response;
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
    public function updateEstatusRecord(ProductosModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . ProductosModel::CONSTANTES_MODEL['tabla'] . " SET ";
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

    /**
     * Obtiene la lista de registros para llenar DataTable o Selects
     * 
     * @param int $producto_id
     * Identificador de producto
     * 
     * 
     * @return array $arrResponse
     * 
     */
    public function selectImagenes($producto_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.* ";
            $sql .= "FROM productos_imagenes t ";
            $sql .= "WHERE ";
            $sql .= "t.producto_id = :producto_id ";
            $sql .= "ORDER BY t.principal DESC, id";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = ['producto_id' => $producto_id];

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
     * Subrutina dentro de updateRecord o insertRecord para insertar las categorias de un producto
     * 
     * @param object $ProductosModel 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function insertCategorias(ProductosModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen y de los arhvios adjuntos ]*/
            $arrCategorias = $modelo->getCategorias();
            $producto_id = $modelo->getId();

            /*-------------------------------------------
            [ Eliminar registros previos de categorias asociadas ]*/
            $sql = "DELETE FROM productos_categorias ";
            $sql .= "WHERE ";
            $sql .= "producto_id = :producto_id ";
            $arrDataDelete = ['producto_id' => $producto_id];
            $result_delete = $this->delete($sql, $arrDataDelete);
            if (!$result_delete) {
                return false;
            }

            for ($i = 0; $i < count($arrCategorias); $i++) {

                /*-------------------------------------------
                [ Insertar datos ]*/
                $sql = "INSERT INTO productos_categorias SET ";
                $sql .= "categoria_id = :categoria_id, ";
                $sql .= "producto_id = :producto_id ";
                $arrData = [
                    'categoria_id' => strClean($arrCategorias[$i]),
                    'producto_id' => $producto_id
                ];
                $lastInsertId = $this->insert($sql, $arrData);
                if ($lastInsertId == 0) {
                    return false;
                }
            }

            /*-------------------------------------------
            [ En caso de que no haya ningun error ]*/
            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }


        /*-------------------------------------------
        [ Retorna Id de Recibo Insertado ]*/
        return $response;
    }

    /**
     * Obtiene la lista de registros de categorias de un producto determinado
     * 
     * @param int $producto_id
     * Identificador de producto
     * 
     * @return array $arrResponse
     * 
     */
    public function selectCategorias(int $producto_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, ";
            $sql .= "c.name as categoria ";
            $sql .= "FROM productos_categorias t ";
            $sql .= "INNER JOIN categorias c ON (c.id = t.categoria_id) ";
            $sql .= "WHERE ";
            $sql .= "t.producto_id = :producto_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'producto_id' => $producto_id
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
     * Obtiene la lista de registros de precios de un producto determinado
     * 
     * @param int $producto_id
     * Identificador de producto
     * 
     * @return array $arrResponse
     * 
     */
    public function selectListaPrecios(int $producto_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, ";
            $sql .= "lp.name, ";
            $sql .= "lp.descripcion ";
            $sql .= "FROM productos_precios t ";
            $sql .= "INNER JOIN listaprecios lp ON (lp.id = t.lista_precios_id) ";
            $sql .= "WHERE ";
            $sql .= "t.producto_id = :producto_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'producto_id' => $producto_id
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
     * Subrutina dentro de updateRecord o insertRecord para insertar los precios de un producto
     * 
     * @param object $ProductosModel 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function insertPrecios(ProductosModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen y de los arhvios adjuntos ]*/
            $arrPrecios = $modelo->getPrecios();
            $producto_id = $modelo->getId();
            $sucursal_id = $modelo->getSucursalId();

            /*-------------------------------------------
            [ Eliminar registros previos de categorias asociadas ]*/
            $sql = "DELETE FROM productos_precios ";
            $sql .= "WHERE ";
            $sql .= "producto_id = :producto_id and ";
            $sql .= "sucursal_id = :sucursal_id ";
            $arrDataDelete = [
                'producto_id' => $producto_id,
                'sucursal_id' => $sucursal_id
            ];
            $result_delete = $this->delete($sql, $arrDataDelete);
            if (!$result_delete) {
                return false;
            }
            $cont = count($arrPrecios['precio']);
            for ($i = 0; $i < $cont; $i++) {

                /*-------------------------------------------
                [ Insertar datos ]*/
                $sql = "INSERT INTO productos_precios SET ";
                $sql .= "lista_precios_id = :lista_precios_id, ";
                $sql .= "sucursal_id = :sucursal_id, ";
                $sql .= "producto_id = :producto_id, ";
                $sql .= "precio = :precio ";
                $arrData = [
                    'precio' => floatval(strClean($arrPrecios['precio'][$i])),
                    'lista_precios_id' => strClean($arrPrecios['lista_precios_id'][$i]),
                    'producto_id' => $producto_id,
                    'sucursal_id' => $sucursal_id
                ];
                $lastInsertId = $this->insert($sql, $arrData);
                if ($lastInsertId == 0) {
                    return false;
                }
            }

            /*-------------------------------------------
            [ En caso de que no haya ningun error ]*/
            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }


        /*-------------------------------------------
        [ Retorna Id de Recibo Insertado ]*/
        return $response;
    }

    /**
     * Obtiene la lista de registros de precios de un producto determinado
     * 
     * @param int $producto_id
     * Identificador de producto
     * 
     * 
     * @param int $sucursal_id
     * Identificador de sucursal
     * 
     * @return array $arrResponse
     * 
     */
    public function selectPreciosProducto(int $producto_id, int $sucursal_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.* ";
            $sql .= "FROM productos_precios t ";
            $sql .= "WHERE ";
            $sql .= "t.producto_id = :producto_id and ";
            $sql .= "t.sucursal_id = :sucursal_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'producto_id' => $producto_id,
                'sucursal_id' => $sucursal_id
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
     * Obtiene la lista de registros para llenar Selects de catalgoo de prodcutos
     * 
     * @return array $arrResponse
     * 
     */
    public function selectRecordsCatalogo(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.* ";
            $sql .= "FROM " . ProductosModel::CONSTANTES_MODEL['tabla'] . " t ";

            if (!$this->getAccesoGlobal()['r']) {
                $sql .= "WHERE  ";
                $sql .= "t.sucursal_id = " . $this->getSession()->get('sucursal_id') . " ";
            }

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
     * Subrutina dentro de updateRecord o insertRecord para insertar las ventas dirigidas de un producto
     * 
     * @param object $ProductosModel 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function insertVentasDirigidas(ProductosModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen y de los arhvios adjuntos ]*/
            $arrVentasDirigidas = $modelo->getVentasDirigidas();
            if (empty($arrVentasDirigidas)) {
                return true;
            }
            $producto_id = $modelo->getId();

            /*-------------------------------------------
            [ Eliminar registros previos de categorias asociadas ]*/
            $sql = "DELETE FROM productos_ventas_dirigidas ";
            $sql .= "WHERE ";
            $sql .= "producto_id = :producto_id ";
            $arrDataDelete = ['producto_id' => $producto_id];
            $result_delete = $this->delete($sql, $arrDataDelete);
            if (!$result_delete) {
                return false;
            }

            for ($i = 0; $i < count($arrVentasDirigidas); $i++) {

                /*-------------------------------------------
                [ Insertar datos ]*/
                $sql = "INSERT INTO productos_ventas_dirigidas SET ";
                $sql .= "producto_recomendado_id = :producto_recomendado_id, ";
                $sql .= "producto_id = :producto_id ";
                $arrData = [
                    'producto_recomendado_id' => strClean($arrVentasDirigidas[$i]),
                    'producto_id' => $producto_id
                ];
                $lastInsertId = $this->insert($sql, $arrData);
                if ($lastInsertId == 0) {
                    return false;
                }
            }

            /*-------------------------------------------
            [ En caso de que no haya ningun error ]*/
            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }


        /*-------------------------------------------
        [ Retorna Id de Recibo Insertado ]*/
        return $response;
    }

    /**
     * Obtiene la lista de registros de producto de ventas dirigidas de un producto determinado
     * 
     * @param int $producto_id
     * Identificador de producto
     * 
     * @return array $arrResponse
     * 
     */
    public function selectVentasDirgidas(int $producto_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, ";
            $sql .= "p.name as producto ";
            $sql .= "FROM productos_ventas_dirigidas t ";
            $sql .= "INNER JOIN productos p ON (p.id = t.producto_recomendado_id) ";
            $sql .= "WHERE ";
            $sql .= "t.producto_id = :producto_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'producto_id' => $producto_id
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
     * Subrutina dentro de updateRecord o insertRecord para insertar las ventas cruzadas de un producto
     * 
     * @param object $ProductosModel 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function insertVentasCruzadas(ProductosModel $modelo): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Se asignan la varibales de origen y de los arhvios adjuntos ]*/
            $arrVentasCruzadas = $modelo->getVentasCruzadas();
            if (empty($arrVentasCruzadas)) {
                return true;
            }
            $producto_id = $modelo->getId();

            /*-------------------------------------------
            [ Eliminar registros previos de categorias asociadas ]*/
            $sql = "DELETE FROM productos_ventas_cruzadas ";
            $sql .= "WHERE ";
            $sql .= "producto_id = :producto_id ";
            $arrDataDelete = ['producto_id' => $producto_id];
            $result_delete = $this->delete($sql, $arrDataDelete);
            if (!$result_delete) {
                return false;
            }

            for ($i = 0; $i < count($arrVentasCruzadas); $i++) {

                /*-------------------------------------------
                [ Insertar datos ]*/
                $sql = "INSERT INTO productos_ventas_cruzadas SET ";
                $sql .= "producto_promociona_id = :producto_promociona_id, ";
                $sql .= "producto_id = :producto_id ";
                $arrData = [
                    'producto_promociona_id' => strClean($arrVentasCruzadas[$i]),
                    'producto_id' => $producto_id
                ];
                $lastInsertId = $this->insert($sql, $arrData);
                if ($lastInsertId == 0) {
                    return false;
                }
            }

            /*-------------------------------------------
            [ En caso de que no haya ningun error ]*/
            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }


        /*-------------------------------------------
        [ Retorna Id de Recibo Insertado ]*/
        return $response;
    }

    /**
     * Obtiene la lista de registros de producto de ventas cruzadas de un producto determinado
     * 
     * @param int $producto_id
     * Identificador de producto
     * 
     * @return array $arrResponse
     * 
     */
    public function selectVentasCruzadas(int $producto_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, ";
            $sql .= "p.name as producto ";
            $sql .= "FROM productos_ventas_cruzadas t ";
            $sql .= "INNER JOIN productos p ON (p.id = t.producto_promociona_id) ";
            $sql .= "WHERE ";
            $sql .= "t.producto_id = :producto_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'producto_id' => $producto_id
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
     * Subrutina dentro de updateRecord o insertRecord para insertar las existencias de un producto
     * 
     * @param object $ProductosModel 
     * Envío de object ContratosModel por valor, que contine la información a actualizar y
     * los parámetros condicionales para realizar el update.
     * 
     * 
     */
    private function insertExistencias(ProductosModel $modelo): bool
    {

        try {

            $response = false;

            // productos_inventario
            // `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            // `sucursal_id` int(10) unsigned NOT NULL,
            // `producto_id` int(10) unsigned NOT NULL,
            // `cantidad` double NOT NULL DEFAULT '0',

            /*-------------------------------------------
            [ Se asignan la varibales de origen y de los arhvios adjuntos ]*/
            $producto_id = $modelo->getId();
            $sucursal_id = $modelo->getSucursalId();
            $cantidad = $modelo->getCantidad();
            $limite_minimo = $modelo->getLimiteMinimo();

            /*-------------------------------------------
            [ Eliminar registros previos de categorias asociadas ]*/
            $sql = "SELECT id FROM productos_inventario ";
            $sql .= "WHERE ";
            $sql .= "producto_id = :producto_id and ";
            $sql .= "sucursal_id = :sucursal_id ";
            $arrDataSelect = [
                'producto_id' => $producto_id,
                'sucursal_id' => $sucursal_id
            ];
            $result_select = $this->selectModel($sql, $arrDataSelect);
            if (empty($result_select)) {

                /*-------------------------------------------
                [ Insertar datos ]*/
                $sql = "INSERT INTO productos_inventario SET ";
                $sql .= "sucursal_id = :sucursal_id, ";
                $sql .= "producto_id = :producto_id, ";
                $sql .= "cantidad = :cantidad, ";
                $sql .= "limite_minimo = :limite_minimo ";
                $arrData = [
                    'cantidad' => $cantidad,
                    'producto_id' => $producto_id,
                    'sucursal_id' => $sucursal_id,
                    'limite_minimo' => $limite_minimo
                ];
                $lastInsertId = $this->insert($sql, $arrData);
                if ($lastInsertId == 0) {
                    return false;
                }
            } else {

                /*-------------------------------------------
                [ Actualizar datos ]*/
                $sql = "UPDATE productos_inventario SET ";
                $sql .= "cantidad = :cantidad, ";
                $sql .= "limite_minimo = :limite_minimo ";
                $sql .= "WHERE ";
                $sql .= "id = :id";
                $arrData = [
                    'id' => $result_select['id'],
                    'cantidad' => $cantidad,
                    'limite_minimo' => $limite_minimo
                ];
                $result = $this->update($sql, $arrData);
                if (!$result) {
                    return false;
                }
            }

            /*-------------------------------------------
            [ En caso de que no haya ningun error ]*/
            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }


        /*-------------------------------------------
        [ Retorna Id de Recibo Insertado ]*/
        return $response;
    }

    /**
     * Obtiene la lista de registros de existencias de un producto determinado
     * 
     * @param int $producto_id
     * Identificador de producto
     * 
     * @param int $sucursal_id
     * Identificador de sucursal
     * 
     * @return array $arrResponse
     * 
     */
    public function selectExistenciasProducto(int $producto_id, int $sucursal_id): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.*, ";
            $sql .= "s.nombre as sucursal ";
            $sql .= "FROM productos_inventario t ";
            $sql .= "INNER JOIN sucursales s ON (s.id = t.sucursal_id) ";
            $sql .= "WHERE ";
            $sql .= "t.producto_id = :producto_id and ";
            $sql .= "t.sucursal_id = :sucursal_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'producto_id' => $producto_id,
                'sucursal_id' => $sucursal_id
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
     * Get the value of alterna
     */
    public function getAlterna()
    {
        return $this->alterna;
    }

    /**
     * Set the value of alterna
     */
    public function setAlterna($alterna): self
    {
        $this->alterna = $alterna;

        return $this;
    }

    /**
     * Get the value of marca_id
     */
    public function getMarcaId()
    {
        return $this->marca_id;
    }

    /**
     * Set the value of marca_id
     */
    public function setMarcaId($marca_id): self
    {
        $this->marca_id = $marca_id;

        return $this;
    }

    /**
     * Get the value of linea_producto_id
     */
    public function getLineaProductoId()
    {
        return $this->linea_producto_id;
    }

    /**
     * Set the value of linea_producto_id
     */
    public function setLineaProductoId($linea_producto_id): self
    {
        $this->linea_producto_id = $linea_producto_id;

        return $this;
    }

    /**
     * Get the value of categorias
     */
    public function getCategorias()
    {
        return $this->categorias;
    }

    /**
     * Set the value of categorias
     */
    public function setCategorias($categorias): self
    {
        $this->categorias = $categorias;

        return $this;
    }

    /**
     * Get the value of sku
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set the value of sku
     */
    public function setSku($sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get the value of precios
     */
    public function getPrecios()
    {
        return $this->precios;
    }

    /**
     * Set the value of precios
     */
    public function setPrecios($precios): self
    {
        $this->precios = $precios;

        return $this;
    }

    /**
     * Get the value of oferta
     */
    public function getOferta()
    {
        return $this->oferta;
    }

    /**
     * Set the value of oferta
     */
    public function setOferta($oferta): self
    {
        $this->oferta = $oferta;

        return $this;
    }

    /**
     * Get the value of precio_oferta
     */
    public function getPrecioOferta()
    {
        return $this->precio_oferta;
    }

    /**
     * Set the value of precio_oferta
     */
    public function setPrecioOferta($precio_oferta): self
    {
        $this->precio_oferta = $precio_oferta;

        return $this;
    }

    /**
     * Get the value of unidad_medida_id
     */
    public function getUnidadMedidaId()
    {
        return $this->unidad_medida_id;
    }

    /**
     * Set the value of unidad_medida_id
     */
    public function setUnidadMedidaId($unidad_medida_id): self
    {
        $this->unidad_medida_id = $unidad_medida_id;

        return $this;
    }

    /**
     * Get the value of limite_minimo
     */
    public function getLimiteMinimo()
    {
        return $this->limite_minimo;
    }

    /**
     * Set the value of limite_minimo
     */
    public function setLimiteMinimo($limite_minimo): self
    {
        $this->limite_minimo = $limite_minimo;

        return $this;
    }

    /**
     * Get the value of cantidad
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set the value of cantidad
     */
    public function setCantidad($cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get the value of ventas_dirigidas
     */
    public function getVentasDirigidas()
    {
        return $this->ventas_dirigidas;
    }

    /**
     * Set the value of ventas_dirigidas
     */
    public function setVentasDirigidas($ventas_dirigidas): self
    {
        $this->ventas_dirigidas = $ventas_dirigidas;

        return $this;
    }

    /**
     * Get the value of ventas_cruzadas
     */
    public function getVentasCruzadas()
    {
        return $this->ventas_cruzadas;
    }

    /**
     * Set the value of ventas_cruzadas
     */
    public function setVentasCruzadas($ventas_cruzadas): self
    {
        $this->ventas_cruzadas = $ventas_cruzadas;

        return $this;
    }

    /**
     * Get the value of rate
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set the value of rate
     */
    public function setRate($rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get the value of recomendaciones_mes
     */
    public function getRecomendacionesMes()
    {
        return $this->recomendaciones_mes;
    }

    /**
     * Set the value of recomendaciones_mes
     */
    public function setRecomendacionesMes($recomendaciones_mes): self
    {
        $this->recomendaciones_mes = $recomendaciones_mes;

        return $this;
    }
}
