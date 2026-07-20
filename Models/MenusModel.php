<?php

/**
 * Clase MenusModel
 */
class MenusModel extends Mysql
{

    private $id;
    private $menu;
    private $descripcion;
    private $url;
    private $icon;
    private $activo;
    private $created_at;
    private $updated_at;
    private $usuario_id_created;
    private $usuario_id_updated;
    private $modulo_id;


    const TABLA = "modulos";
    const PREFIJO_TABLA = "ssf_";

    /**
     * Método Constructor de MenusModel.
     * Inicializa Mysql::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene la lista de menus para el buscador de menus general.
     * 
     * @return array $request
     * 
     */
    public function selectMenus($filter): array
    {

        try {


            $filter = "%" . $filter . "%";

            $sql = "SELECT ";
            $sql .= "m.id, m.url, m.icon, m.name, m.descripcion, m.activo ";
            $sql .= "FROM " . self::PREFIJO_TABLA . self::TABLA . " m ";
            $sql .= "WHERE ";
            $sql .= "(m.descripcion LIKE :filter OR ";
            $sql .= " m.tags LIKE :filter OR ";
            $sql .= " m.name LIKE :filter) and ";
            $sql .= "m.activo = 1 and ";
            $sql .= "m.menu_search = 1 ";
            $sql .= "order by m.name, m.descripcion LIMIT 12";

            /*-------------------------------------------
            [ Paramteros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'filter' => $filter
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $request = $this->selectLike($sql, $arr_values);

            /*-------------------------------------------
            [ Retorna array con la lista de registros ]*/
            return $request;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }

    /**
     * Obtiene los datos de un modulo determinado
     * 
     * @return array $request
     * 
     */
    public function selectMenu($menu_id): array
    {

        try {

            /*-------------------------------------------
            [ Instruccion sql ]*/
            $sql = "SELECT ";
            $sql .= "m.* ";
            $sql .= "FROM " . self::PREFIJO_TABLA . self::TABLA . " m ";
            $sql .= "WHERE ";
            $sql .= "id = :menu_id";

            /*-------------------------------------------
            [ Paramteros condicionales, se envía vacío en caso de no aplicar ]*/
            $arr_values = [
                'menu_id' => $menu_id
            ];

            /*-------------------------------------------
            [ Ejecuta el Metodo select de MySQL ]*/
            $request = $this->selectModel($sql, $arr_values);

            /*-------------------------------------------
            [ Retorna array con la lista de registros ]*/
            return $request;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
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
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set the value of menu
     *
     * @return  self
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;

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
     *
     * @return  self
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get the value of url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the value of url
     *
     * @return  self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the value of icon
     *
     * @return  self
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

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
     *
     * @return  self
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get the value of created_at
     */
    public function getCreated_at()
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */
    public function setCreated_at($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get the value of updated_at
     */
    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    /**
     * Set the value of updated_at
     *
     * @return  self
     */
    public function setUpdated_at($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Get the value of usuario_id_created
     */
    public function getUsuario_id_created()
    {
        return $this->usuario_id_created;
    }

    /**
     * Set the value of usuario_id_created
     *
     * @return  self
     */
    public function setUsuario_id_created($usuario_id_created)
    {
        $this->usuario_id_created = $usuario_id_created;

        return $this;
    }

    /**
     * Get the value of usuario_id_updated
     */
    public function getUsuario_id_updated()
    {
        return $this->usuario_id_updated;
    }

    /**
     * Set the value of usuario_id_updated
     *
     * @return  self
     */
    public function setUsuario_id_updated($usuario_id_updated)
    {
        $this->usuario_id_updated = $usuario_id_updated;

        return $this;
    }

    /**
     * Get the value of modulo_id
     */
    public function getModulo_id()
    {
        return $this->modulo_id;
    }

    /**
     * Set the value of modulo_id
     *
     * @return  self
     */
    public function setModulo_id($modulo_id)
    {
        $this->modulo_id = $modulo_id;

        return $this;
    }
}
