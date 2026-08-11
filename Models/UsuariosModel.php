<?php

/**
 * Clase UsuariosModel
 */
class UsuariosModel extends Mysql
{

    /*-------------------------------------------
    [ table usuarios ]*/
    private $id;
    private $usuario;
    private $pass;
    private $theme;
    private $activo;
    private $token;
    private $token_created;
    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;
    private $empresa_id;
    private $sucursal_id;
    private $datos_generales;
    private $domicilio_envio;
    private $is_cliente;
    private $lista_precios_id;
    private $datos_facturacion;

    const TABLA = "usuarios";
    const PREFIJO_TABLA = "ssf_";

    /**
     * Método Constructor de UsuariosModel.
     * Inicializa Mysql::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene datos de un Usuario determinado.
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return array $arrResponse
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectUsuarioLogin(UsuariosModel $model): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "u.id,  ";
            $sql .= "u.usuario,  ";
            $sql .= "u.theme,  ";
            $sql .= "u.ccveusaurio,  ";
            $sql .= "usr_datgen.nombre, ";
            $sql .= "usr_datgen.paterno, ";
            $sql .= "usr_datgen.materno, ";
            $sql .= "usr_datgen.email, ";
            $sql .= "usr_datgen.telefono, ";
            $sql .= "rol.name as rol, ";
            $sql .= "empusr.activo, ";
            $sql .= "empusr.titular, ";
            $sql .= "empusr.empresa_id, ";
            $sql .= "empusr.sucursal_id, ";
            $sql .= "empusr.rol_id as rol_id, ";
            $sql .= "suc.nombre as nombre_empresa, ";
            $sql .= "suc.nombre as sucursal, ";
            $sql .= "u.updated_at, ";
            $sql .= "CONCAT_WS(' ', usr_datgen_register.nombre, usr_datgen_register.paterno, usr_datgen_register.materno) as usuario_register ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " u ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales usr_datgen ON (usr_datgen.usuario_id = u.id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "empresas_usuarios empusr ON (empusr.usuario_id = u.id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "empresas emp ON (emp.id = empusr.empresa_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "sucursales suc ON (suc.id = empusr.sucursal_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "roles rol ON (rol.id = empusr.rol_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales usr_datgen_register ON (usr_datgen_register.usuario_id = u.usuario_id_updated) ";
            $sql .= "WHERE  ";
            $sql .= "u.id = :usuario_id and ";
            $sql .= "empusr.empresa_id = :empresa_id and ";
            $sql .= "empusr.sucursal_id = :sucursal_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'usuario_id' => $model->getId(),
                'empresa_id' => $model->getEmpresaId(),
                'sucursal_id' =>  $model->getSucursalId()
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
    public function selectRecords(UsuariosModel $model): array
    {

        try {


            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "us.id,  ";
            $sql .= "us.usuario,  ";
            $sql .= "usr_datgen.nombre, ";
            $sql .= "usr_datgen.paterno, ";
            $sql .= "usr_datgen.materno, ";
            $sql .= "usr_datgen.email, ";
            $sql .= "usr_datgen.telefono, ";
            $sql .= "usr_datgen.lista_precios_id, ";
            $sql .= "rol.name as rol, ";
            $sql .= "empusr.activo, ";
            $sql .= "empusr.titular, ";
            $sql .= "empusr.empresa_id, ";
            $sql .= "empusr.sucursal_id, ";
            $sql .= "suc.nombre as nombre_empresa, ";
            $sql .= "suc.nombre as sucursal, ";
            $sql .= "us.updated_at, ";
            $sql .= "us.created_at, ";
            $sql .= "CONCAT_WS(' - ', lp.name, lp.descripcion) as listaprecios, ";
            $sql .= "CONCAT_WS(' ', usr_datgen.nombre, usr_datgen.paterno, usr_datgen.materno) as nombre_completo, ";
            $sql .= "CONCAT('Calle ', domenv.calle, ', Num. ', domenv.num_exterior, ' ', domenv.num_interior, ', Colonia: ', domenv.colonia, ', Ciudad: ', domenv.ciudad, ', Estado: ', domenv.estado, ', CP: ', domenv.cp, ', País: ', domenv.pais) as domicilio_envio, ";
            $sql .= "CONCAT_WS(' ', usr_datgen_register.nombre, usr_datgen_register.paterno, usr_datgen_register.materno) as usuario_register ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " us ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales usr_datgen ON (usr_datgen.usuario_id = us.id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "empresas_usuarios empusr ON (empusr.usuario_id = us.id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "empresas emp ON (emp.id = empusr.empresa_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "sucursales suc ON (suc.id = usr_datgen.sucursal_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "roles rol ON (rol.id = empusr.rol_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales usr_datgen_register ON (usr_datgen_register.usuario_id = us.usuario_id_updated) ";
            $sql .= "LEFT JOIN " . self::PREFIJO_TABLA . "domicilios_envio domenv ON (domenv.usuario_id = us.id) ";
            $sql .= "LEFT JOIN " . self::PREFIJO_TABLA . "listaprecios lp ON (lp.id = usr_datgen.lista_precios_id) ";
            $sql .= "WHERE  ";
            $sql .= "empusr.empresa_id = :empresa_id and ";
            $sql .= "empusr.sucursal_id = :sucursal_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                "empresa_id" => $model->getEmpresaId(),
                "sucursal_id" => $model->getSucursalId()
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
    public function selectRecord(UsuariosModel $model): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "us.id,  ";
            $sql .= "us.usuario,  ";
            $sql .= "usr_datgen.nombre, ";
            $sql .= "usr_datgen.paterno, ";
            $sql .= "usr_datgen.materno, ";
            $sql .= "usr_datgen.email, ";
            $sql .= "usr_datgen.telefono, ";
            $sql .= "usr_datgen.sucursal_id, ";
            $sql .= "usr_datgen.lista_precios_id, ";
            $sql .= "rol.name as rol, ";
            $sql .= "empusr.activo, ";
            $sql .= "empusr.titular, ";
            $sql .= "empusr.empresa_id, ";
            $sql .= "empusr.rol_id as rol_id, ";
            $sql .= "suc.nombre as nombre_empresa, ";
            $sql .= "suc.nombre as sucursal, ";
            $sql .= "us.updated_at, ";
            $sql .= "CONCAT_WS(' - ', lp.name, lp.descripcion) as listaprecios, ";
            $sql .= "domenv.calle, ";
            $sql .= "domenv.num_exterior, ";
            $sql .= "domenv.num_interior, ";
            $sql .= "domenv.colonia, ";
            $sql .= "domenv.ciudad, ";
            $sql .= "domenv.estado, ";
            $sql .= "domenv.cp, ";
            $sql .= "domenv.pais, ";
            $sql .= "domenv.referencias, ";
            $sql .= "fact.rfc, ";
            $sql .= "fact.razon_social, ";
            $sql .= "fact.codigo_postal, ";
            $sql .= "fact.regimen, ";
            $sql .= "fact.uso_cfdi, ";
            $sql .= "fact.email as email_fact, ";
            $sql .= "CONCAT_WS(' ', usr_datgen.nombre, usr_datgen.paterno, usr_datgen.materno) as nombre_completo, ";
            $sql .= "CONCAT('Calle ', domenv.calle, ', Num. ', domenv.num_exterior, ' ', domenv.num_interior, ', Colonia: ', domenv.colonia, ', Ciudad: ', domenv.ciudad, ', Estado: ', domenv.estado, ', CP: ', domenv.cp, ', País: ', domenv.pais) as domicilio_envio, ";
            $sql .= "CONCAT_WS(' ', usr_datgen_register.nombre, usr_datgen_register.paterno, usr_datgen_register.materno) as usuario_register ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " us ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales usr_datgen ON (usr_datgen.usuario_id = us.id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "empresas_usuarios empusr ON (empusr.usuario_id = us.id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "empresas emp ON (emp.id = empusr.empresa_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "sucursales suc ON (suc.id = empusr.sucursal_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "roles rol ON (rol.id = empusr.rol_id) ";
            $sql .= "INNER JOIN " . self::PREFIJO_TABLA . "usuarios_datos_generales usr_datgen_register ON (usr_datgen_register.usuario_id = us.usuario_id_updated) ";
            $sql .= "LEFT JOIN " . self::PREFIJO_TABLA . "domicilios_envio domenv ON (domenv.usuario_id = us.id) ";
            $sql .= "LEFT JOIN " . self::PREFIJO_TABLA . "listaprecios lp ON (lp.id = usr_datgen.lista_precios_id) ";
            $sql .= "LEFT JOIN " . self::PREFIJO_TABLA . "datos_facturacion fact ON (fact.usuario_id = us.id) ";
            $sql .= "WHERE  ";
            $sql .= "us.id = :record_id and ";
            $sql .= "empusr.empresa_id = :empresa_id ";

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
     * Valida los datos de usuario para el acceso al sistema.
     * y retorna los datos de usuario para inicilaizar la sesión start
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return array $arrResponse
     * * array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * 
     */
    public function validaDatosUsuario(UsuariosModel $model): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.id,  ";
            $sql .= "t.usuario,  ";
            $sql .= "t.theme,  ";
            $sql .= "t.activo, ";
            $sql .= "t.created_at, ";
            $sql .= "t.usuario_id_created, ";
            $sql .= "t.updated_at, ";
            $sql .= "t.usuario_id_updated, ";
            $sql .= "t.email_confirmado, ";
            $sql .= "t.token ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " t ";
            $sql .= "WHERE  ";
            $sql .= "t.usuario = :usuario AND ";
            $sql .= "t.pass = :pass";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arrData = [
                'usuario' => $model->getUsuario(),
                'pass' => $model->getPass()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $arrResponse = $this->selectModel($sql, $arrData);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array asociativo con los datos del registro o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Obtiene datos de un Usuario determinado.
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return array $arrResponse
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectRecordFromUsuario(UsuariosModel $model): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.usuario,  ";
            $sql .= "t.theme,  ";
            $sql .= "t.activo, ";
            $sql .= "t.created_at, ";
            $sql .= "t.usuario_id_created, ";
            $sql .= "t.updated_at, ";
            $sql .= "t.usuario_id_updated, ";
            $sql .= "t.email_confirmado, ";
            $sql .= "t.token ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " t ";
            $sql .= "WHERE  ";
            $sql .= "t.usuario = :usuario";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'usuario' => $model->getUsuario()
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
    public function validInsertExist(UsuariosModel $model): bool
    {
        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT * FROM " . self::PREFIJO_TABLA . SELF::TABLA . " WHERE usuario = :texto_valida ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'texto_valida' => $model->getUsuario()
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
     * * true indica si el rol ya existe.
     * * false en caso de que no exista.
     * 
     */
    public function validUpdateExistRecord(UsuariosModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT * FROM " . self::PREFIJO_TABLA . SELF::TABLA . " WHERE usuario = :texto_valida and id != :id";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'texto_valida' => $model->getUsuario(),
                'id' => $model->getId()
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
    public function insertRecord(UsuariosModel &$model): bool
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

                /*-------------------------------------------
                [ Registrar Datos Generales de Usuario ]*/
                if ($this->usuarioCreateDatosGenerales($model)) {

                    /*-------------------------------------------
                    [ Registrar Relación Usuarios - Empresa ]*/
                    if ($this->usuarioCreateRelacionUsuarioEmpresa($model)) {

                        /*-------------------------------------------
                        [ Registrar Domicilio de Envío del cliente ]*/
                        if ($this->usuarioCreateDomicilioEnvio($model)) {

                            /*-------------------------------------------
                            [ Registrar Domicilio de Envío del cliente ]*/
                            if ($this->usuarioCreateDatosFacturacion($model)) {
                                $this->getConexion()->commit();
                                return true;
                            }
                        }
                    }
                };
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
     */
    public function recordCreate(UsuariosModel $model): int
    {

        try {

            $response = 0;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "INSERT INTO " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
            $sql .= "usuario = :usuario, ";
            $sql .= "pass = :pass, ";
            $sql .= "activo = :activo, ";
            $sql .= "theme = :theme, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "created_at = current_timestamp, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_created = :usuario_id_register, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";


            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'usuario' => $model->getUsuario(),
                'pass' => $model->getPass(),
                'activo' => $model->getActivo(),
                'theme' => $model->getTheme(),
                'empresa_id' => $model->getEmpresaId(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' =>  $model->getUsuarioIdCreated()
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
     * Subrutina dentro de insertRecord para crear los datos generales del Usuario
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     */
    public function usuarioCreateDatosGenerales(UsuariosModel $model): bool
    {

        try {

            $response = false;
            $datos_generales =  $model->getDatosGenerales();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "INSERT INTO " . self::PREFIJO_TABLA . "usuarios_datos_generales SET ";
            $sql .= "usuario_id = :usuario_id, ";
            $sql .= "nombre = :nombre, ";
            $sql .= "paterno = :paterno, ";
            $sql .= "materno = :materno, ";
            $sql .= "email = :email, ";
            $sql .= "telefono = :telefono, ";
            $sql .= "lista_precios_id = :lista_precios_id, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "created_at = current_timestamp, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_created = :usuario_id_register, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";

            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'nombre' => $datos_generales['nombre'],
                'paterno' => $datos_generales['paterno'],
                'materno' => $datos_generales['materno'],
                'email' => $datos_generales['email'],
                'telefono' => $datos_generales['telefono'],
                'lista_precios_id' => $datos_generales['lista_precios_id'],
                'empresa_id' => $model->getEmpresaId(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' => $model->getUsuarioIdCreated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $last_insert_id = $this->insert($sql, $arrData);
            if ($last_insert_id > 0) {
                return true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $response;
    }

    /**
     * Subrutina dentro de insertRecord para crear la relación de Usuario con Empresa
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     */
    public function usuarioCreateRelacionUsuarioEmpresa(UsuariosModel $model): bool
    {


        try {

            $response = false;

            $datos_generales =  $model->getDatosGenerales();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "INSERT INTO " . self::PREFIJO_TABLA . "empresas_usuarios SET ";
            $sql .= "usuario_id = :usuario_id, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "activo = 1, ";
            $sql .= "titular = :titular, ";
            $sql .= "rol_id = :rol_id, ";
            $sql .= "created_at = current_timestamp, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_created = :usuario_id_register, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";


            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'titular' => $datos_generales['titular'],
                'rol_id' => $datos_generales['rol_id'],
                'sucursal_id' => $model->getSucursalId(),
                'empresa_id' => $model->getEmpresaId(),
                'usuario_id_register' => $model->getUsuarioIdCreated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $last_insert_id = $this->insert($sql, $arrData);
            if ($last_insert_id > 0) {
                return true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $response;
    }

    /**
     * Subrutina dentro de insertRecord para crear los datos de domicilio de envío del Cliente
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     */
    public function usuarioCreateDomicilioEnvio(UsuariosModel $model): bool
    {

        try {

            $response = false;

            $domicilio_envio =  $model->getDomicilioEnvio();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "INSERT INTO " . self::PREFIJO_TABLA . "domicilios_envio SET ";
            $sql .= "usuario_id = :usuario_id, ";
            $sql .= "calle = :calle, ";
            $sql .= "num_exterior = :num_exterior, ";
            $sql .= "num_interior = :num_interior, ";
            $sql .= "colonia = :colonia, ";
            $sql .= "ciudad = :ciudad, ";
            $sql .= "estado = :estado, ";
            $sql .= "cp = :cp, ";
            $sql .= "pais = :pais, ";
            $sql .= "referencias = :referencias, ";
            $sql .= "activo = :activo, ";
            $sql .= "predeterminado = :predeterminado, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "created_at = current_timestamp, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_created = :usuario_id_register, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";

            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'calle' => $domicilio_envio['calle'],
                'num_exterior' => $domicilio_envio['num_exterior'],
                'num_interior' => $domicilio_envio['num_interior'],
                'colonia' => $domicilio_envio['colonia'],
                'ciudad' => $domicilio_envio['ciudad'],
                'estado' => $domicilio_envio['estado'],
                'cp' => $domicilio_envio['cp'],
                'pais' => $domicilio_envio['pais'],
                'referencias' => $domicilio_envio['referencias'],
                'activo' => $domicilio_envio['activo'],
                'predeterminado' => $domicilio_envio['predeterminado'],
                'empresa_id' => $model->getEmpresaId(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' => $model->getUsuarioIdCreated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $last_insert_id = $this->insert($sql, $arrData);
            if ($last_insert_id > 0) {
                return true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $response;
    }

    /**
     * Subrutina dentro de insertRecord para crear los datos de facturación del Cliente
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     */
    public function usuarioCreateDatosFacturacion(UsuariosModel $model): bool
    {

        try {

            $response = false;

            $datos_facturacion =  $model->getDatosFacturacion();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "INSERT INTO " . self::PREFIJO_TABLA . "datos_facturacion SET ";
            $sql .= "usuario_id = :usuario_id, ";
            $sql .= "rfc = :rfc, ";
            $sql .= "razon_social = :razon_social, ";
            $sql .= "codigo_postal = :codigo_postal, ";
            $sql .= "regimen = :regimen, ";
            $sql .= "uso_cfdi = :uso_cfdi, ";
            $sql .= "email = :email, ";
            $sql .= "activo = :activo, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "created_at = current_timestamp, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_created = :usuario_id_register, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";

            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'rfc' => $datos_facturacion['rfc'],
                'razon_social' => $datos_facturacion['razon_social'],
                'codigo_postal' => $datos_facturacion['codigo_postal'],
                'regimen' => $datos_facturacion['regimen'],
                'uso_cfdi' => $datos_facturacion['uso_cfdi'],
                'email' => $datos_facturacion['email'],
                'activo' => $datos_facturacion['activo'],
                'empresa_id' => $model->getEmpresaId(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' => $model->getUsuarioIdCreated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $last_insert_id = $this->insert($sql, $arrData);
            if ($last_insert_id > 0) {
                return true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $response;
    }

    /**
     * Actualiza datos da un Usuario determinado.
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateRecord(UsuariosModel &$model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Actualizar Datos de Usuario ]*/
            if ($this->recordUpdate($model)) {

                /*-------------------------------------------
                [ Actualizar Datos Generales de Usuario ]*/
                if ($this->usuarioUpdateDatosGenerales($model)) {

                    /*-------------------------------------------
                    [ Actualizar Relación Usuarios - Empresa ]*/
                    if ($this->usuarioUpdateRelacionUsuarioEmpresa($model)) {

                        /*-------------------------------------------
                        [ Actualizar Domicilio Envío ]*/
                        if ($this->usuarioUpdateDomicilioEnvio($model)) {

                            /*-------------------------------------------
                            [ Actualizar Datos de Facturacion ]*/
                            if ($this->usuarioUpdateDatosFacturacion($model)) {

                                /*-------------------------------------------
                                [ Actualizar Contraseña en caso de requerido ]*/
                                $pass = $model->getPass();
                                if ($pass != '') {
                                    $this->usuarioUpdatePassword($model);
                                }
                                $this->getConexion()->commit();
                                return true;
                            }
                        }
                    }
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
    public function recordUpdate(UsuariosModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE ";
            $sql .= "id = :id ";

            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'id' => $model->getId(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' => $model->getUsuarioIdUpdated()
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
     * Subrutina dentro de updateRecord para actualizar datos generales de Uusario determinado
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     */
    public function usuarioUpdateDatosGenerales(UsuariosModel $model): bool
    {

        try {

            $response = false;

            $datos_generales =  $model->getDatosGenerales();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . "usuarios_datos_generales SET ";
            $sql .= "usuario_id = :usuario_id, ";
            $sql .= "nombre = :nombre, ";
            $sql .= "paterno = :paterno, ";
            $sql .= "materno = :materno, ";
            $sql .= "email = :email, ";
            $sql .= "telefono = :telefono, ";
            $sql .= "lista_precios_id = :lista_precios_id, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE ";
            $sql .= "usuario_id = :usuario_id";


            /*-------------------------------------------
            [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'nombre' => $datos_generales['nombre'],
                'paterno' => $datos_generales['paterno'],
                'materno' => $datos_generales['materno'],
                'email' => $datos_generales['email'],
                'telefono' => $datos_generales['telefono'],
                'lista_precios_id' => $datos_generales['lista_precios_id'],
                'empresa_id' => $model->getEmpresaId(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo update de MySQL ]*/
            $response = $this->update($sql, $arrData);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta ]*/
        return $response;
    }

    /**
     * Subrutina dentro de updateRecord para actualizar la relación de Usuario con Empresa
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     */
    public function usuarioUpdateRelacionUsuarioEmpresa(UsuariosModel $model): bool
    {


        try {

            $response = false;

            $datos_generales = array();
            $datos_generales =  $model->getDatosGenerales();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . "empresas_usuarios SET ";
            $sql .= "usuario_id = :usuario_id, ";
            $sql .= "activo = 1, ";
            $sql .= "titular = :titular, ";
            $sql .= "rol_id = :rol_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE ";
            $sql .= "usuario_id = :usuario_id and ";
            $sql .= "empresa_id = :empresa_id";

            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'titular' => $datos_generales['titular'],
                'rol_id' => $datos_generales['rol_id'],
                'sucursal_id' => $model->getSucursalId(),
                'empresa_id' => $model->getEmpresaId(),
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $response = $this->update($sql, $arrData);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna bool ]*/
        return $response;
    }

    /**
     * Subrutina dentro de updateRecord para actualizar datos de domicilio de envio de Usuario determinado
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     */
    public function usuarioUpdateDomicilioEnvio(UsuariosModel $model): bool
    {

        try {

            $response = false;

            $domicilio_envio = $model->getDomicilioEnvio();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . "domicilios_envio SET ";
            $sql .= "calle = :calle, ";
            $sql .= "num_exterior = :num_exterior, ";
            $sql .= "num_interior = :num_interior, ";
            $sql .= "colonia = :colonia, ";
            $sql .= "ciudad = :ciudad, ";
            $sql .= "estado = :estado, ";
            $sql .= "cp = :cp, ";
            $sql .= "pais = :pais, ";
            $sql .= "referencias = :referencias, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE ";
            $sql .= "usuario_id = :usuario_id";


            /*-------------------------------------------
            [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'calle' => $domicilio_envio['calle'],
                'num_exterior' => $domicilio_envio['num_exterior'],
                'num_interior' => $domicilio_envio['num_interior'],
                'colonia' => $domicilio_envio['colonia'],
                'ciudad' => $domicilio_envio['ciudad'],
                'estado' => $domicilio_envio['estado'],
                'cp' => $domicilio_envio['cp'],
                'pais' => $domicilio_envio['pais'],
                'referencias' => $domicilio_envio['referencias'],
                'empresa_id' => $model->getEmpresaId(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' => $model->getUsuarioIdCreated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo update de MySQL ]*/
            $response = $this->update($sql, $arrData);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta ]*/
        return $response;
    }



    /**
     * Subrutina dentro de updateRecord para actualizar datos de facturacion de Usuario determinado
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     */
    public function usuarioUpdateDatosFacturacion(UsuariosModel $model): bool
    {

        try {

            $response = false;

            $datos_facturacion = $model->getDatosFacturacion();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . "datos_facturacion SET ";
            $sql .= "rfc = :rfc, ";
            $sql .= "razon_social = :razon_social, ";
            $sql .= "codigo_postal = :codigo_postal, ";
            $sql .= "regimen = :regimen, ";
            $sql .= "uso_cfdi = :uso_cfdi, ";
            $sql .= "email = :email, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE ";
            $sql .= "usuario_id = :usuario_id";

            /*-------------------------------------------
            [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'rfc' => $datos_facturacion['rfc'],
                'razon_social' => $datos_facturacion['razon_social'],
                'codigo_postal' => $datos_facturacion['codigo_postal'],
                'regimen' => $datos_facturacion['regimen'],
                'uso_cfdi' => $datos_facturacion['uso_cfdi'],
                'email' => $datos_facturacion['email'],
                'empresa_id' => $model->getEmpresaId(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo update de MySQL ]*/
            $response = $this->update($sql, $arrData);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna respuesta ]*/
        return $response;
    }


    /**
     * Subrutina dentro de updateRecord para actualizar constraseña
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     */
    public function usuarioUpdatePassword(UsuariosModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . self::TABLA . " SET ";
            $sql .= "pass = :pass, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "where ";
            $sql .= "id = :id ";


            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'id' => $model->getId(),
                'pass' => $model->getPass(),
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];


            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $response = $this->update($sql, $arrData);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $response;
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
    public function updateEstatusRecord(UsuariosModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . "empresas_usuarios SET ";
            $sql .= "activo = :activo, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE  ";
            $sql .= "usuario_id = :usuario_id and ";
            $sql .= "empresa_id = :empresa_id";

            /*-------------------------------------------
            [ Parámetros condicionales para realizar el update ]*/
            $arrData = [
                'usuario_id' => $model->getId(),
                'activo' => $model->getActivo(),
                'empresa_id' => $model->getEmpresaId(),
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo delete de MySQL ]*/
            $respuesta = $this->update($sql, $arrData);
            if ($respuesta == true) {

                /*-------------------------------------------
                [ Instruccion sql ]*/
                $sql = "UPDATE " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
                $sql .= "activo = :activo ";
                $sql .= "WHERE  ";
                $sql .= "id = :id";

                $arrData = [
                    'id' => $model->getId(),
                    'activo' => $model->getActivo()
                ];
                $respuesta = $this->update($sql, $arrData);

                if ($respuesta == true) {
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
     * Actualiza la contraseña de un registro determinado.
     * 
     * @param object $modelo
     * Envío del modelo por valor.
     *
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updatePassword(UsuariosModel $model): bool
    {
        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . self::TABLA . " SET pass = :pass where id = :id";

            /*-------------------------------------------
            [ Datos a insertar ]*/
            $arrData = [
                'pass' => $model->getPass(),
                'id' => $model->getId()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $response = $this->update($sql, $arrData);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $response;
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
     * Get the value of usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set the value of usuario
     */
    public function setUsuario($usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get the value of pass
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Set the value of pass
     */
    public function setPass($pass): self
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Get the value of theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set the value of theme
     */
    public function setTheme($theme): self
    {
        $this->theme = $theme;

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
     * Get the value of token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the value of token
     */
    public function setToken($token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the value of token_created
     */
    public function getTokenCreated()
    {
        return $this->token_created;
    }

    /**
     * Set the value of token_created
     */
    public function setTokenCreated($token_created): self
    {
        $this->token_created = $token_created;

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
     * Get the value of datos_generales
     */
    public function getDatosGenerales()
    {
        return $this->datos_generales;
    }

    /**
     * Set the value of datos_generales
     */
    public function setDatosGenerales($datos_generales): self
    {
        $this->datos_generales = $datos_generales;

        return $this;
    }

    /**
     * Get the value of domicilio_envio
     */
    public function getDomicilioEnvio()
    {
        return $this->domicilio_envio;
    }

    /**
     * Set the value of domicilio_envio
     */
    public function setDomicilioEnvio($domicilio_envio): self
    {
        $this->domicilio_envio = $domicilio_envio;

        return $this;
    }

    /**
     * Get the value of is_cliente
     */
    public function getIsCliente()
    {
        return $this->is_cliente;
    }

    /**
     * Set the value of is_cliente
     */
    public function setIsCliente($is_cliente): self
    {
        $this->is_cliente = $is_cliente;

        return $this;
    }

    /**
     * Get the value of lista_precios_id
     */
    public function getListaPreciosId()
    {
        return $this->lista_precios_id;
    }

    /**
     * Set the value of lista_precios_id
     */
    public function setListaPreciosId($lista_precios_id): self
    {
        $this->lista_precios_id = $lista_precios_id;

        return $this;
    }

    /**
     * Get the value of datos_facturacion
     */
    public function getDatosFacturacion()
    {
        return $this->datos_facturacion;
    }

    /**
     * Set the value of datos_facturacion
     */
    public function setDatosFacturacion($datos_facturacion): self
    {
        $this->datos_facturacion = $datos_facturacion;

        return $this;
    }
}
