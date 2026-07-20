<?php

/**
 * Clase Core Conexion 
 */
class Conexion
{

    private $conn;

    /**
     * Método Constructor de Core Conexion
     */
    public function __construct()
    {
        $connectionString = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";" . DB_CHARSET . ";";
        try {
            $this->conn = new PDO($connectionString, DB_USER, DB_PASSWORD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            // $this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,  1);
            // $this->conn->setAttribute(   PDO_MYSQL_ATTR_USE_BUFFERED_QUERY, 1);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            // $session = new Session();
            // $session->close();
        }
    }

    /**
     * Método para retornar la Conexión PDO (Represents a connection between PHP and a database server.)
     * 
     */
    public function connect()
    {
        return $this->conn;
    }
}
