<?php

require 'Libraries/Custom/cfdiutils/vendor/autoload.php';

use CfdiUtils\OpenSSL\OpenSSL;

/**
 * Clase EFirmaModel
 */
class EFirmaModel extends Mysql
{

    // table efirma
    private $id;
    private $rfc;
    private $certificado;
    private $llave;
    private $password;
    private $fecha_ini;
    private $fecha_fin;
    private $estatus_descarga;
    private $empresa_id;
    private $sucursal_id;

    private $created_at;
    private $usuario_id_created;
    private $updated_at;
    private $usuario_id_updated;

    private $files;

    const TABLA = "efirma";
    const PREFIJO_TABLA = "ssf_";

    /**
     * Método Constructor de EFirmaModel.
     * Inicializa Mysql::__construct
     * 
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene datos de registro determinado.
     * 
     * @param object $modelo
     * Envío del modelo por valor, que contine la información a actualizar y 
     * los parámetros condicionales para realizar el update.
     * 
     * @return array $arrResponse
     * 
     */
    public function selectRecord(EFirmaModel $model): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.* ";
            $sql .= "FROM " . self::PREFIJO_TABLA . SELF::TABLA . " t ";
            $sql .= "WHERE ";
            $sql .= "empresa_id = :empresa_id ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'empresa_id' => $model->getEmpresaId()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $arrResponse = $this->selectModel($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array con la lista de registros o empty en caso de error ]*/
        return $arrResponse;
    }

    /**
     * Actualiza datos da Configfuracion.
     * 
     * @param object $modelo
     * Envío del modelo por valor, que contine la información a actualizar y 
     * los parámetros condicionales para realizar el update.
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function updateRecord(EFirmaModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $respuesta_files = $this->setFilesPemEFirma($model->getPassword(), $model->getFiles());

            if (!$respuesta_files) {
                return false;
            }

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
            $sql .= "rfc = :rfc, ";
            $sql .= "certificado = 1, ";
            $sql .= "llave = 1, ";
            $sql .= "pass = :pass, ";
            $sql .= "empresa_id = :empresa_id, ";
            $sql .= "sucursal_id = :sucursal_id, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE ";
            $sql .= "empresa_id = :empresa_id ";

            /*-------------------------------------------
            [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
            $arrData = [
                'empresa_id' => $model->getEmpresaId(),
                'rfc' => $model->getRfc(),
                'pass' => $model->getPassword(),
                'sucursal_id' => $model->getSucursalId(),
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $response = $this->update($sql, $arrData);


            /*-------------------------------------------
            [ Commit Transaction ]*/
            $this->getConexion()->commit();
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
     * Actualiza Estatus para activar la Descarga Masiva.
     * 
     * @param object $modelo
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
    public function activarDescargaMasiva(EFirmaModel $model): bool
    {

        try {


            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            $estatus_descarga  = $model->getEstatusDescarga();
            if ($estatus_descarga == 0) {
                $sql_date = "fecha_fin = current_timestamp, ";
            } else {
                $sql_date = "fecha_ini = current_timestamp, ";
            }

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE efirma SET ";
            $sql .= "estatus_descarga = :estatus_descarga, ";
            $sql .= $sql_date;
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE  ";
            $sql .= "id = :id ";

            /*-------------------------------------------
                [ Datos a actualizar y parámetros condicionales para realizar el update ]*/
            $arrData = [
                'id' => $model->getId(),
                'estatus_descarga' => $estatus_descarga,
                'usuario_id_register' => $model->getUsuarioIdUpdated()
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $respuesta = $this->update($sql, $arrData);

            /*-------------------------------------------
            [ Evalúa respuesta ]*/
            if ($respuesta == true) {
                $this->getConexion()->commit();
                return true;
            } else {
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

    //*==================================================================
    // [ Funciones de Operacion ]*/

    private function setCertPEM($path): bool
    {
        try {

            $response = false;

            //Delete file if exist
            if (file_exists('Assets/files/efirma/certificado.pem')) {
                unlink('Assets/files/efirma/certificado.pem');
            }

            /*-------------------------------------------
            [ Instanciar Clase de OpenSSL ]*/
            $openssl = new OpenSSL();

            $cerFile = $path;
            $cerPemFile = 'Assets/files/efirma/certificado.pem';
            $openssl = new \CfdiUtils\OpenSSL\OpenSSL();

            // guardar el certificado en PEM a partir del archivo DER usando openssl
            $openssl->derCerConvert($cerFile, $cerPemFile);


            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $response;
    }

    private function setKeyPEM($path, $password): bool
    {

        try {

            $response = false;

            //Delete file if exist
            if (file_exists('Assets/files/efirma/llave.pem')) {
                unlink('Assets/files/efirma/llave.pem');
            }

            /*-------------------------------------------
            [ Instanciar Clase de OpenSSL ]*/
            $openssl = new OpenSSL();

            /*-------------------------------------------
            [ Preparar Datos para conversión ]*/
            // $password = openssl_decrypt($password, METHODENCRIPT, KEY);

            // $keyDerFile = media() . '/files/efirma/llave.key';
            $keyDerFile = $path;
            $keyPemFile = 'Assets/files/efirma/llave.pem';
            $keyDerPass = $password;
            $keyPemPass = '';

            /*-------------------------------------------
            [ convertir la llave original DER a formato PEM con nueva contraseña, guardar en $keyPemFile
              lo mismo que los dos pasos anteriores pero en una llamada ]*/
            $openssl->derKeyProtect($keyDerFile, $keyDerPass, $keyPemFile, $keyPemPass);

            $response = true;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $response;
    }

    /**
     * Actualiza datos da una Efirma de un Cliente determinado.
     * 
     * @param array $files
     * Array con los datos de los archivos a convertir y generar pem
     * 
     * 
     * @return bool $response
     * * true - indica que fue exitoso.
     * * false - en caso de falla.
     * 
     */
    public function setFilesPemEFirma(string $password_key, array $files): bool
    {

        $response = false;

        //*==================================================================
        // [ Guardar Archivos de Certificado y LLave ]*/

        //Upload Certificado
        $tmp_cer = $files['certificado']['tmp_name'];
        $ruta_doctos = "Assets/files/efirma/";
        $ruta_cer =  $ruta_doctos . 'certificado.cer';
        $response_up_cer =  move_uploaded_file($tmp_cer, $ruta_cer);

        //Upload Llave
        $tmp_key = $files['llave']['tmp_name'];
        $ruta_doctos = "Assets/files/efirma/";
        $ruta_key =  $ruta_doctos . 'llave.key';
        $response_up_key =  move_uploaded_file($tmp_key, $ruta_key);

        if ($response_up_cer == true && $response_up_key == true) {

            //*==================================================================
            // [ Generar archivo pem de certificado ]*/
            $res_cer = $this->setCertPEM($ruta_cer);

            //*==================================================================
            // [ Generar archivo pem de certificado ]*/
            $res_key = $this->setKeyPEM($ruta_key, $password_key);

            //*==================================================================
            // [ Evalua respuestas ]*/
            if ($res_cer == true && $res_key == true) {
                $response = true;
            }
        }

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
     * Get the value of rfc
     */
    public function getRfc()
    {
        return $this->rfc;
    }

    /**
     * Set the value of rfc
     */
    public function setRfc($rfc): self
    {
        $this->rfc = $rfc;

        return $this;
    }

    /**
     * Get the value of certificado
     */
    public function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * Set the value of certificado
     */
    public function setCertificado($certificado): self
    {
        $this->certificado = $certificado;

        return $this;
    }

    /**
     * Get the value of llave
     */
    public function getLlave()
    {
        return $this->llave;
    }

    /**
     * Set the value of llave
     */
    public function setLlave($llave): self
    {
        $this->llave = $llave;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     */
    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of fecha_ini
     */
    public function getFechaIni()
    {
        return $this->fecha_ini;
    }

    /**
     * Set the value of fecha_ini
     */
    public function setFechaIni($fecha_ini): self
    {
        $this->fecha_ini = $fecha_ini;

        return $this;
    }

    /**
     * Get the value of fecha_fin
     */
    public function getFechaFin()
    {
        return $this->fecha_fin;
    }

    /**
     * Set the value of fecha_fin
     */
    public function setFechaFin($fecha_fin): self
    {
        $this->fecha_fin = $fecha_fin;

        return $this;
    }

    /**
     * Get the value of estatus_descarga
     */
    public function getEstatusDescarga()
    {
        return $this->estatus_descarga;
    }

    /**
     * Set the value of estatus_descarga
     */
    public function setEstatusDescarga($estatus_descarga): self
    {
        $this->estatus_descarga = $estatus_descarga;

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
