<?php

/**
 * Clase Core Mysql 
 */
class Mysql extends Conexion
{

    private $conexion;
    private $strquery;
    private $arrvalues;

    /**
     * Método Constructor de Core Mysql
     * * Inicializa la clase Core Conexion
     * * Inicializa la variable $conexion desde el método this->conexion->connect()
     */
    public function __construct()
    {
        try {
            $this->conexion = new Conexion();
            $this->conexion = $this->conexion->connect();
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }


    /**
     * Método MySQL create
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Valores a actualizar y parámetros condicionales relacionados a la instrucción sql que se está enviando.
     * 
     * 
     * @return bool TRUE on success or FALSE on failure.
     */
    public function create(string $query, array $arrvalues): bool
    {

        try {

            $result = false;

            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);

            $stm = $this->conexion->prepare($this->getStrquery());
            $result = $stm->execute($this->getArrvalues());

            //
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $result;
    }



    /**
     * Método MySQL Insert
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Valores a insertar.
     * 
     * @return int $lastInsert
     * * $lastInsert > 0 en caso de ser exitoso.
     * * $lastInsert == 0 en caso de falla
     * 
     */
    public function insert(string $query, array $arrvalues): int
    {
        try {

            $lastInsert = 0;

            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);

            $stm = $this->conexion->prepare($this->getStrquery());
            $result = $stm->execute($this->getArrvalues());

            if ($result == true) {
                $lastInsert = $this->conexion->lastInsertId();
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $lastInsert;
    }


    /**
     * Método MySQL selectModel
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Parámetros condicionales relacionados a la instrucción sql que se está enviando.
     * 
     * @return array
     * * Fetches the next row from a result set.
     * * The return value of this function on success depends on the fetch type. In all cases, array empty is returned on failure.
     * * En este caso es del tipo: 
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     */
    public function selectModel(string $query, array $arrvalues): array
    {

        try {

            $data = array();

            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);

            $stm = $this->conexion->prepare($this->getStrquery());
            $result = $stm->execute($this->getArrvalues());

            if ($result == true) {
                $response = $stm->fetch(PDO::FETCH_ASSOC);
                if ($response != false) {
                    $data = $response;
                }
            }

            //
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $data;
    }


    /**
     * Método MySQL selectLike
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Parámetros condicionales relacionados a la instrucción sql que se está enviando.
     * 
     * @return array
     * * PDOStatement::fetchAll returns an array containing all of the remaining rows in the result set. 
     * * The array represents each row as either an array of column values or an object with properties corresponding to each column name.
     * * Using this method to fetch large result sets will result in a heavy demand on system and possibly network resources. 
     * * Rather than retrieving all of the data and manipulating it in PHP, consider using the database server to manipulate the result sets. 
     * * For example, use the WHERE and ORDER BY clauses in SQL to restrict results before retrieving and processing them with PHP.
     */
    public function selectLike(string $query, array $arrvalues): array
    {

        try {

            $data = array();
            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);

            $stm = $this->conexion->prepare($this->getStrquery());
            $stm->bindParam(':filter', $arrvalues['filter'], PDO::PARAM_STR);
            $result = $stm->execute();

            if ($result == true) {
                $data = $stm->fetchAll(PDO::FETCH_ASSOC);
            }

            //
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $data;
    }


    /**
     * Método MySQL selectLike
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Parámetros condicionales relacionados a la instrucción sql que se está enviando.
     * 
     * @return array
     * * PDOStatement::fetchAll returns an array containing all of the remaining rows in the result set. 
     * * The array represents each row as either an array of column values or an object with properties corresponding to each column name.
     * * Using this method to fetch large result sets will result in a heavy demand on system and possibly network resources. 
     * * Rather than retrieving all of the data and manipulating it in PHP, consider using the database server to manipulate the result sets. 
     * * For example, use the WHERE and ORDER BY clauses in SQL to restrict results before retrieving and processing them with PHP.
     */
    public function selectLikeResidentes(string $query, array $arrvalues): array
    {

        try {

            $data = array();
            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);

            $stm = $this->conexion->prepare($this->getStrquery());
            $stm->bindParam(':calle', $arrvalues['calle'], PDO::PARAM_STR);

            if (count($arrvalues) > 1) {
                $stm->bindParam(':numero', $arrvalues['numero'], PDO::PARAM_STR);
            }

            $result = $stm->execute();

            if ($result == true) {
                $data = $stm->fetchAll(PDO::FETCH_ASSOC);
            }

            //
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $data;
    }





    /**
     * Método MySQL select
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Parámetros condicionales relacionados a la instrucción sql que se está enviando.
     * 
     * @return array
     * * PDOStatement::fetchAll returns an array containing all of the remaining rows in the result set. 
     * * The array represents each row as either an array of column values or an object with properties corresponding to each column name.
     * * Using this method to fetch large result sets will result in a heavy demand on system and possibly network resources. 
     * * Rather than retrieving all of the data and manipulating it in PHP, consider using the database server to manipulate the result sets. 
     * * For example, use the WHERE and ORDER BY clauses in SQL to restrict results before retrieving and processing them with PHP.
     */
    public function select(string $query, array $arrvalues): array
    {

        try {

            $data = array();

            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);
            $stm = $this->conexion->prepare($this->getStrquery());
            $result = $stm->execute($this->getArrvalues());
            if ($result == true) {
                $data = $stm->fetchAll(PDO::FETCH_ASSOC);
            }

            //
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $data;
    }


    /**
     * Método MySQL select para output vallidacion
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Parámetros condicionales relacionados a la instrucción sql que se está enviando.
     * 
     * @return array|false
     * * PDOStatement::fetchAll returns an array containing all of the remaining rows in the result set. 
     * * The array represents each row as either an array of column values or an object with properties corresponding to each column name.
     * * Using this method to fetch large result sets will result in a heavy demand on system and possibly network resources. 
     * * Rather than retrieving all of the data and manipulating it in PHP, consider using the database server to manipulate the result sets. 
     * * For example, use the WHERE and ORDER BY clauses in SQL to restrict results before retrieving and processing them with PHP.
     */
    public function selectOutput(string $query, array $arrvalues)
    {

        try {

            $data = array();

            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);
            $stm = $this->conexion->prepare($this->getStrquery());
            $result = $stm->execute($this->getArrvalues());
            if ($result == true) {
                $data = $stm->fetchAll(PDO::FETCH_ASSOC);
            }

            //
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            return false;
        }

        return $data;
    }



    /**
     * Método MySQL update
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Valores a actualizar y parámetros condicionales relacionados a la instrucción sql que se está enviando.
     * 
     * 
     * @return bool TRUE on success or FALSE on failure.
     */
    public function update(string $query, array $arrvalues): bool
    {

        try {

            $result = false;

            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);

            $stm = $this->conexion->prepare($this->getStrquery());
            $result = $stm->execute($this->getArrvalues());

            //
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }

        return $result;
    }

    /**
     * Método MySQL delete
     * 
     * @param string $query
     * Instrucción sql que se envía
     * 
     * @param array $arrvalues
     * Parámetros condicionales relacionados a la instrucción sql que se está enviando.
     * 
     * @return bool TRUE on success or FALSE on failure.
     */
    public function delete(string $query, array $arrvalues): bool
    {

        try {

            $result = false;

            $this->setStrquery($query);
            $this->setArrvalues($arrvalues);

            $stm = $this->conexion->prepare($this->getStrquery());
            $result = $stm->execute($this->getArrvalues());
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        return $result;
    }

    //*==================================================================
    // [ GETTERS & SETTERS ]*/

    /**
     * Get the value of conexion
     */
    public function getConexion()
    {
        return $this->conexion;
    }

    /**
     * Set the value of conexion
     *
     * @return  self
     */
    public function setConexion($conexion)
    {
        $this->conexion = $conexion;

        return $this;
    }

    /**
     * Get the value of strquery
     */
    public function getStrquery()
    {
        return $this->strquery;
    }

    /**
     * Set the value of strquery
     *
     * @return  self
     */
    public function setStrquery($strquery)
    {
        $this->strquery = $strquery;

        return $this;
    }

    /**
     * Get the value of arrvalues
     */
    public function getArrvalues()
    {
        return $this->arrvalues;
    }

    /**
     * Set the value of arrvalues
     *
     * @return  self
     */
    public function setArrvalues($arrvalues)
    {
        $this->arrvalues = $arrvalues;

        return $this;
    }
}
