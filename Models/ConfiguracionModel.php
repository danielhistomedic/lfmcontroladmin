<?php

/**
 * Clase ConfiguracionModel
 */
class ConfiguracionModel extends Mysql
{

    private $id;
    private $empresa_id;

    private $smtp_host;
    private $smtp_usuario;
    private $smtp_password;
    private $smtp_puerto;

    private $telefono_contacto;
    private $email_contacto;
    private $url_tienda;
    private $nombre_remitente;
    private $email_remitente;
    private $sitio_web;

    private $updated_at;
    private $usuario_id_updated;

    private $email_destino;

    const TABLA = "configuracion";
    const PREFIJO_TABLA = "ssf_";

    /**
     * Método Constructor de ConfiguracionModel.
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
    public function selectRecord(ConfiguracionModel $model): array
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
    public function updateRecord(ConfiguracionModel $model): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Begin Transaction ]*/
            $this->getConexion()->beginTransaction();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "UPDATE " . self::PREFIJO_TABLA . SELF::TABLA . " SET ";
            $sql .= "smtp_host = :smtp_host, ";
            $sql .= "smtp_usuario = :smtp_usuario, ";
            $sql .= "smtp_password = :smtp_password, ";
            $sql .= "smtp_puerto = :smtp_puerto, ";
            $sql .= "telefono_contacto = :telefono_contacto, ";
            $sql .= "email_contacto = :email_contacto, ";
            $sql .= "url_tienda = :url_tienda, ";
            $sql .= "nombre_remitente = :nombre_remitente, ";
            $sql .= "email_remitente = :email_remitente, ";
            $sql .= "email_destino = :email_destino, ";
            $sql .= "sitio_web = :sitio_web, ";
            $sql .= "updated_at = current_timestamp, ";
            $sql .= "usuario_id_updated = :usuario_id_register ";
            $sql .= "WHERE ";
            $sql .= "empresa_id = :empresa_id ";

            /*-------------------------------------------
            [ Datos a actualizar y parámetros condicionales para realizar el update ]*/

            $arrData = [
                'empresa_id' => $model->getEmpresaId(),
                'smtp_host' => $model->getSmtpHost(),
                'smtp_usuario' => $model->getSmtpUsuario(),
                'smtp_password' => $model->getSmtpPassword(),
                'smtp_puerto' => $model->getSmtpPuerto(),
                'telefono_contacto' => $model->getTelefonoContacto(),
                'email_contacto' => $model->getEmailContacto(),
                'url_tienda' => $model->getUrlTienda(),
                'nombre_remitente' => $model->getNombreRemitente(),
                'email_remitente' => $model->getEmailRemitente(),
                'email_destino' => $model->getEmailDestino(),
                'sitio_web' => $model->getSitioWeb(),
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
     * Get the value of smtp_host
     */
    public function getSmtpHost()
    {
        return $this->smtp_host;
    }

    /**
     * Set the value of smtp_host
     */
    public function setSmtpHost($smtp_host): self
    {
        $this->smtp_host = $smtp_host;

        return $this;
    }

    /**
     * Get the value of smtp_usuario
     */
    public function getSmtpUsuario()
    {
        return $this->smtp_usuario;
    }

    /**
     * Set the value of smtp_usuario
     */
    public function setSmtpUsuario($smtp_usuario): self
    {
        $this->smtp_usuario = $smtp_usuario;

        return $this;
    }

    /**
     * Get the value of smtp_password
     */
    public function getSmtpPassword()
    {
        return $this->smtp_password;
    }

    /**
     * Set the value of smtp_password
     */
    public function setSmtpPassword($smtp_password): self
    {
        $this->smtp_password = $smtp_password;

        return $this;
    }

    /**
     * Get the value of smtp_puerto
     */
    public function getSmtpPuerto()
    {
        return $this->smtp_puerto;
    }

    /**
     * Set the value of smtp_puerto
     */
    public function setSmtpPuerto($smtp_puerto): self
    {
        $this->smtp_puerto = $smtp_puerto;

        return $this;
    }

    /**
     * Get the value of telefono_contacto
     */
    public function getTelefonoContacto()
    {
        return $this->telefono_contacto;
    }

    /**
     * Set the value of telefono_contacto
     */
    public function setTelefonoContacto($telefono_contacto): self
    {
        $this->telefono_contacto = $telefono_contacto;

        return $this;
    }

    /**
     * Get the value of email_contacto
     */
    public function getEmailContacto()
    {
        return $this->email_contacto;
    }

    /**
     * Set the value of email_contacto
     */
    public function setEmailContacto($email_contacto): self
    {
        $this->email_contacto = $email_contacto;

        return $this;
    }

    /**
     * Get the value of url_tienda
     */
    public function getUrlTienda()
    {
        return $this->url_tienda;
    }

    /**
     * Set the value of url_tienda
     */
    public function setUrlTienda($url_tienda): self
    {
        $this->url_tienda = $url_tienda;

        return $this;
    }

    /**
     * Get the value of nombre_remitente
     */
    public function getNombreRemitente()
    {
        return $this->nombre_remitente;
    }

    /**
     * Set the value of nombre_remitente
     */
    public function setNombreRemitente($nombre_remitente): self
    {
        $this->nombre_remitente = $nombre_remitente;

        return $this;
    }

    /**
     * Get the value of email_remitente
     */
    public function getEmailRemitente()
    {
        return $this->email_remitente;
    }

    /**
     * Set the value of email_remitente
     */
    public function setEmailRemitente($email_remitente): self
    {
        $this->email_remitente = $email_remitente;

        return $this;
    }

    /**
     * Get the value of sitio_web
     */
    public function getSitioWeb()
    {
        return $this->sitio_web;
    }

    /**
     * Set the value of sitio_web
     */
    public function setSitioWeb($sitio_web): self
    {
        $this->sitio_web = $sitio_web;

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
     * Get the value of email_destino
     */
    public function getEmailDestino()
    {
        return $this->email_destino;
    }

    /**
     * Set the value of email_destino
     */
    public function setEmailDestino($email_destino): self
    {
        $this->email_destino = $email_destino;

        return $this;
    }
}
