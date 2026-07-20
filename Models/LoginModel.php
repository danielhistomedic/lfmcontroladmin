<?php

/**
 * Clase LoginModel
 */
class LoginModel extends Mysql
{

    const TABLA = "login_token";
    const PREFIJO_TABLA = "ssf_";


    /**
     * Método Constructor de LoginModel.
     * Inicializa Mysql::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Marcar Como expirada la sesión de login token
     * 
     * 
     * @param int $tokenId
     * estatus expiración de sesión remember
     * 
     * @return bool $response
     * 
     */
    public function markAsExpired($tokenId): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "UPDATE " . self::PREFIJO_TABLA . self::TABLA . " SET ";
            $sql .= "is_expired = :is_expired ";
            $sql .= "WHERE  ";
            $sql .= "id = :id";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $expired = 1;
            $arr_values = [
                'is_expired' => $expired,
                'id' => $tokenId
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo selectModel de MySQL ]*/
            $response = $this->update($sql, $arr_values);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array asociativo con los datos del registro o empty en caso de error ]*/
        return $response;
    }

    /**
     * Insertar nuevo token de cookies
     * 
     * @param string $username
     * Usuario de login token
     * 
     * @param string $random_password_hash
     * password hash random
     * 
     * @param string $random_selector_hash
     * string random selector
     * 
     * @param string $expiry_date
     * gfecha de expiración del remember de login token
     * 
     * @return bool $response
     * 
     */
    public function insertToken(string $username, string $random_password_hash, string $random_selector_hash, string $expiry_date): bool
    {

        try {

            $response = false;

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "INSERT INTO " . self::PREFIJO_TABLA . self::TABLA . " SET ";
            $sql .= "usuario = :usuario, ";
            $sql .= "password_hash = :password_hash, ";
            $sql .= "selector_hash = :selector_hash, ";
            $sql .= "expiry_date = :expiry_date ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $expired = 1;
            $arr_values = [
                'usuario' => $username,
                'password_hash' => $random_password_hash,
                'selector_hash' => $random_selector_hash,
                'expiry_date' => $expiry_date
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo insert de MySQL ]*/
            $lastInsertId = $this->insert($sql, $arr_values);
            if ($lastInsertId > 0) {
                $response = true;
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        /*-------------------------------------------
        [ Retorna array asociativo con los datos del registro o empty en caso de error ]*/
        return $response;
    }
}
