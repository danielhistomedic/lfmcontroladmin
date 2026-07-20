<?php

/**
 * Clase PersonalModel
 */
class PersonalModel extends Mysql
{


    private $icvemedico;
    private $cnombre;
    private $cpriapellido;
    private $csegapellido;
    private $cMatricula;
    private $cdsctipousuario;
    private $cdscareaafectada;
    private $cCURP;
    private $cDomicilio;
    private $cCargo;
    private $icveperfil;
    private $iActivo;
    private $email;
    private $cempleo;
    private $ccveusuario_jefe_inmediato;

    const TABLA = "cat_medico";
    const PREFIJO_TABLA = "";

    /**
     * Método Constructor de PersonalModel.
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
    public function selectRecords(PersonalModel $modelo, bool $ordenarAlfabeticamente = false): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "cat_medico.icvemedico as id, ";
            $sql .= "cat_medico.cempleo, ";
            $sql .= "cat_medico.cnombre, ";
            $sql .= "cat_medico.cpriapellido, ";
            $sql .= "cat_medico.csegapellido, ";
            $sql .= "cat_medico.cdscareaafectada, ";
            $sql .= "cat_medico.cdscservicio, ";
            $sql .= "cat_medico.cdsctipousuario, ";
            $sql .= "cat_medico.user, ";
            $sql .= "cat_medico.ccvemedico, ";
            $sql .= "cat_perfiles.cdscperfil, ";
            $sql .= "cat_medico.icvemedico, ";
            $sql .= "cat_medico.iActivo, ";
            $sql .= "cat_medico.usuariofiel, ";
            $sql .= "cat_medico.cMatricula, ";
            $sql .= "cat_medico.cCURP, ";
            $sql .= "cat_medico.cDomicilio, ";
            $sql .= "cat_medico.cDomicilio as telefono, ";
            $sql .= "cat_medico.email, ";
            $sql .= "cat_medico.cdsctipousuario, ";
            $sql .= "cat_medico.cEspecialidad, ";
            $sql .= "cat_medico.cSubEspecialidad, ";
            $sql .= "cat_medico.cEscuela, ";
            $sql .= "cat_medico.cCedulaProf, ";
            $sql .= "cat_medico.formacion_especifique, ";
            $sql .= "cat_medico.cCargo, ";
            $sql .= "CONCAT_WS(' ', cat_medico.cempleo, cat_medico.cnombre, cat_medico.cpriapellido, cat_medico.csegapellido) AS nombre, ";
            $sql .= "CONCAT_WS(' ', usr.cnombre, usr.cpriapellido, usr.csegapellido) AS registro, ";
            $sql .= "CONCAT_WS(' ', usr_upd.cnombre, usr_upd.cpriapellido, usr_upd.csegapellido) AS actualizo, ";
            $sql .= "CONCAT_WS(' ', usr_susp.cnombre, usr_susp.cpriapellido, usr_susp.csegapellido) AS suspende, ";
            $sql .= "CONCAT_WS(' ', jefe_in.cnombre, jefe_in.cpriapellido, jefe_in.csegapellido) AS jefe_in, ";
            $sql .= "cat_medico.fchregistro, ";
            $sql .= "cat_medico.ccveusuariosuspende, ";
            $sql .= "cat_medico.fchregistrosuspende, ";
            $sql .= "cat_medico.fchregistroactualiza, ";
            $sql .= "IF(cat_medico.revocado = 1, 'USUARIO REVOCADO', '') as revocado, ";
            $sql .= "IF(cat_medico.iActivo = 1, 'ACTIVO', 'SUSPENDIDO') as Estatus, ";
            $sql .= "cat_medico.fecha_caducidad_acceso as fecha_ingreso, ";
            $sql .= "cat_medico.formacion_especifique, ";
            $sql .= "cat_medico.cEmpleo, ";
            $sql .= "cat_medico.cEspecialidad, ";
            $sql .= "cat_medico.cSubEspecialidad ";
            $sql .= "FROM cat_medico ";
            $sql .= "INNER JOIN cat_perfiles ON (cat_perfiles.icveperfil = cat_medico.icveperfil) ";
            $sql .= "LEFT JOIN cat_medico usr ON (usr.ccvemedico = cat_medico.ccveusuarioregistra) ";
            $sql .= "LEFT JOIN cat_medico usr_upd ON (usr_upd.ccvemedico = cat_medico.ccveusuarioactualiza) ";
            $sql .= "LEFT JOIN cat_medico usr_susp ON (usr_susp.ccvemedico = cat_medico.ccveusuariosuspende) ";
            $sql .= "LEFT JOIN cat_medico jefe_in ON (jefe_in.ccvemedico = cat_medico.ccveusuario_jefe_inmediato) ";
            $sql .= "WHERE  ";
            $sql .= "cat_medico.control_rh = 1  ";

            if ($ordenarAlfabeticamente) {
                $sql .= "ORDER BY cat_medico.cnombre, cat_medico.cpriapellido, cat_medico.csegapellido";
            } else {
                $sql .= "ORDER BY cat_medico.cdscareaafectada, cat_medico.cdscservicio";
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
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectRecord(PersonalModel $modelo): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "cat_medico.icvemedico as id, ";
            $sql .= "cat_medico.cempleo, ";
            $sql .= "cat_medico.cnombre, ";
            $sql .= "cat_medico.cpriapellido, ";
            $sql .= "cat_medico.csegapellido, ";
            $sql .= "cat_medico.cdscareaafectada, ";
            $sql .= "cat_medico.cdscservicio, ";
            $sql .= "cat_medico.cdsctipousuario, ";
            $sql .= "cat_medico.user, ";
            $sql .= "cat_medico.ccvemedico, ";
            $sql .= "cat_perfiles.cdscperfil, ";
            $sql .= "cat_medico.icvemedico, ";
            $sql .= "cat_medico.iActivo, ";
            $sql .= "cat_medico.usuariofiel, ";
            $sql .= "cat_medico.cMatricula, ";
            $sql .= "cat_medico.cCURP, ";
            $sql .= "cat_medico.cDomicilio, ";
            $sql .= "cat_medico.cDomicilio as telefono, ";
            $sql .= "cat_medico.email, ";
            $sql .= "cat_medico.cdsctipousuario, ";
            $sql .= "cat_medico.cEspecialidad, ";
            $sql .= "cat_medico.cSubEspecialidad, ";
            $sql .= "cat_medico.cEscuela, ";
            $sql .= "cat_medico.cCedulaProf, ";
            $sql .= "cat_medico.formacion_especifique, ";
            $sql .= "cat_medico.cCargo, ";
            $sql .= "CONCAT_WS(' ', cat_medico.cempleo, cat_medico.cnombre, cat_medico.cpriapellido, cat_medico.csegapellido) AS nombre, ";
            $sql .= "CONCAT_WS(' ', usr.cnombre, usr.cpriapellido, usr.csegapellido) AS registro, ";
            $sql .= "CONCAT_WS(' ', usr_upd.cnombre, usr_upd.cpriapellido, usr_upd.csegapellido) AS actualizo, ";
            $sql .= "CONCAT_WS(' ', usr_susp.cnombre, usr_susp.cpriapellido, usr_susp.csegapellido) AS suspende, ";
            $sql .= "CONCAT_WS(' ', jefe_in.cnombre, jefe_in.cpriapellido, jefe_in.csegapellido) AS jefe_in, ";
            $sql .= "cat_medico.fchregistro, ";
            $sql .= "cat_medico.ccveusuariosuspende, ";
            $sql .= "cat_medico.fchregistrosuspende, ";
            $sql .= "cat_medico.fchregistroactualiza, ";
            $sql .= "IF(cat_medico.revocado = 1, 'USUARIO REVOCADO', '') as revocado, ";
            $sql .= "IF(cat_medico.iActivo = 1, 'ACTIVO', 'SUSPENDIDO') as Estatus, ";
            $sql .= "cat_medico.fecha_caducidad_acceso as fecha_ingreso, ";
            $sql .= "cat_medico.formacion_especifique, ";
            $sql .= "cat_medico.cEmpleo, ";
            $sql .= "cat_medico.cEspecialidad, ";
            $sql .= "cat_medico.cSubEspecialidad ";
            $sql .= "FROM cat_medico ";
            $sql .= "INNER JOIN cat_perfiles ON (cat_perfiles.icveperfil = cat_medico.icveperfil) ";
            $sql .= "LEFT JOIN cat_medico usr ON (usr.ccvemedico = cat_medico.ccveusuarioregistra) ";
            $sql .= "LEFT JOIN cat_medico usr_upd ON (usr_upd.ccvemedico = cat_medico.ccveusuarioactualiza) ";
            $sql .= "LEFT JOIN cat_medico usr_susp ON (usr_susp.ccvemedico = cat_medico.ccveusuariosuspende) ";
            $sql .= "LEFT JOIN cat_medico jefe_in ON (jefe_in.ccvemedico = cat_medico.ccveusuario_jefe_inmediato) ";
            $sql .= "where cat_medico.control_rh = 1 AND cat_medico.icvemedico = :registro_id ";


            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'registro_id' => $modelo->getIcvemedico()
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
        
    //*==================================================================
    // [ Dashboard ]*/

    /**
     * Obtiene la lista de personal con filtros
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
    public function selectPersonalFiltros($filtro): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "t.cNombre, ";
            $sql .= "t.cPriApellido, ";
            $sql .= "t.cSegApellido, ";
            $sql .= "t.cdscareaafectada, ";
            $sql .= "t.cdscservicio, ";
            $sql .= "t.cDomicilio as telefono ";
            $sql .= "FROM cat_medico t ";
            $sql .= $filtro;
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
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectTotalEmpleados(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "count(cat_medico.icvemedico) as total_empleados ";
            $sql .= "FROM cat_medico ";
            $sql .= "WHERE ";
            $sql .= "cat_medico.control_rh = 1 ";
            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

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
     * Obtiene datos de un Registro determinado.
     * 
     * @return array $arrResponse
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectTotalEmpleadosActivos(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "count(cat_medico.icvemedico) as total_empleados_activos ";
            $sql .= "FROM cat_medico where control_rh = 1 and iActivo = 1 ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

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
     * Obtiene datos de un Registro determinado.
     * 
     * @return array $arrResponse
     * * Array de tipo asociativo con los nombres 
     *   de las columnas indicadas en la instrucción sql.
     * * Retorna array de tipo:
     *   fetch(PDO::FETCH_ASSOC): returns an array indexed by column name as returned in your result set
     * 
     */
    public function selectTotalEmpleadosBaja(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/

            $sql = "SELECT ";
            $sql .= "count(cat_medico.icvemedico) as total_empleados_baja ";
            $sql .= "FROM cat_medico where control_rh = 1 and iActivo = 0 ";

            /*-------------------------------------------
            [ Parametros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [];

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
     * @param bool $ordenarAlfabeticamente
     * True, ordena los registros alfabeticamente sobre el campo principal de la tabla.
     * caso contrario ordena por fecha de registro descendente.
     * 
     * @return array $arrResponse
     * * Retorna array de tipo:
     *   fetchAll(PDO::FETCH_ASSOC): returns an array containing all of the remaining rows in the result set. 
     * 
     */
    public function selectTotalEmpleadosPuesto(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "cdscservicio as puestos, ";
            $sql .= "SUM(CASE WHEN iActivo = 1 THEN 1 ELSE 0 END) as activos, ";
            $sql .= "SUM(CASE WHEN iActivo = 0 THEN 1 ELSE 0 END) as inactivos ";
            $sql .= "FROM cat_medico ";
            $sql .= "GROUP BY puestos ORDER BY activos DESC, cdscservicio; ";

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
    public function selectTotalEmpleadosDepartamentos(): array
    {

        try {

            $arrResponse = array();

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "cdscareaafectada as departamento, ";
            $sql .= "SUM(CASE WHEN iActivo = 1 THEN 1 ELSE 0 END) as activos, ";
            $sql .= "SUM(CASE WHEN iActivo = 0 THEN 1 ELSE 0 END) as inactivos ";
            $sql .= "FROM cat_medico ";
            $sql .= "GROUP BY departamento ORDER BY activos DESC, cdscareaafectada; ";


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
    
    //*==================================================================
    // [ GETTERS & SETTERS ]*/

    /**
     * Get the value of icvemedico
     */
    public function getIcvemedico()
    {
        return $this->icvemedico;
    }

    /**
     * Set the value of icvemedico
     */
    public function setIcvemedico($icvemedico): self
    {
        $this->icvemedico = $icvemedico;

        return $this;
    }

    /**
     * Get the value of cnombre
     */
    public function getCnombre()
    {
        return $this->cnombre;
    }

    /**
     * Set the value of cnombre
     */
    public function setCnombre($cnombre): self
    {
        $this->cnombre = $cnombre;

        return $this;
    }

    /**
     * Get the value of cpriapellido
     */
    public function getCpriapellido()
    {
        return $this->cpriapellido;
    }

    /**
     * Set the value of cpriapellido
     */
    public function setCpriapellido($cpriapellido): self
    {
        $this->cpriapellido = $cpriapellido;

        return $this;
    }

    /**
     * Get the value of csegapellido
     */
    public function getCsegapellido()
    {
        return $this->csegapellido;
    }

    /**
     * Set the value of csegapellido
     */
    public function setCsegapellido($csegapellido): self
    {
        $this->csegapellido = $csegapellido;

        return $this;
    }

    /**
     * Get the value of cMatricula
     */
    public function getCMatricula()
    {
        return $this->cMatricula;
    }

    /**
     * Set the value of cMatricula
     */
    public function setCMatricula($cMatricula): self
    {
        $this->cMatricula = $cMatricula;

        return $this;
    }

    /**
     * Get the value of cdsctipousuario
     */
    public function getCdsctipousuario()
    {
        return $this->cdsctipousuario;
    }

    /**
     * Set the value of cdsctipousuario
     */
    public function setCdsctipousuario($cdsctipousuario): self
    {
        $this->cdsctipousuario = $cdsctipousuario;

        return $this;
    }

    /**
     * Get the value of cdscareaafectada
     */
    public function getCdscareaafectada()
    {
        return $this->cdscareaafectada;
    }

    /**
     * Set the value of cdscareaafectada
     */
    public function setCdscareaafectada($cdscareaafectada): self
    {
        $this->cdscareaafectada = $cdscareaafectada;

        return $this;
    }

    /**
     * Get the value of cCURP
     */
    public function getCCURP()
    {
        return $this->cCURP;
    }

    /**
     * Set the value of cCURP
     */
    public function setCCURP($cCURP): self
    {
        $this->cCURP = $cCURP;

        return $this;
    }

    /**
     * Get the value of cDomicilio
     */
    public function getCDomicilio()
    {
        return $this->cDomicilio;
    }

    /**
     * Set the value of cDomicilio
     */
    public function setCDomicilio($cDomicilio): self
    {
        $this->cDomicilio = $cDomicilio;

        return $this;
    }

    /**
     * Get the value of cCargo
     */
    public function getCCargo()
    {
        return $this->cCargo;
    }

    /**
     * Set the value of cCargo
     */
    public function setCCargo($cCargo): self
    {
        $this->cCargo = $cCargo;

        return $this;
    }

    /**
     * Get the value of icveperfil
     */
    public function getIcveperfil()
    {
        return $this->icveperfil;
    }

    /**
     * Set the value of icveperfil
     */
    public function setIcveperfil($icveperfil): self
    {
        $this->icveperfil = $icveperfil;

        return $this;
    }

    /**
     * Get the value of iActivo
     */
    public function getIActivo()
    {
        return $this->iActivo;
    }

    /**
     * Set the value of iActivo
     */
    public function setIActivo($iActivo): self
    {
        $this->iActivo = $iActivo;

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
     * Get the value of cempleo
     */
    public function getCempleo()
    {
        return $this->cempleo;
    }

    /**
     * Set the value of cempleo
     */
    public function setCempleo($cempleo): self
    {
        $this->cempleo = $cempleo;

        return $this;
    }

    /**
     * Get the value of ccveusuario_jefe_inmediato
     */
    public function getCcveusuarioJefeInmediato()
    {
        return $this->ccveusuario_jefe_inmediato;
    }

    /**
     * Set the value of ccveusuario_jefe_inmediato
     */
    public function setCcveusuarioJefeInmediato($ccveusuario_jefe_inmediato): self
    {
        $this->ccveusuario_jefe_inmediato = $ccveusuario_jefe_inmediato;

        return $this;
    }
}
