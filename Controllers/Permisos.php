<?php

/**
 * Controlador Permisos 
 */
class Permisos extends Controllers
{

    private $session;
    private $permisosMod;

    /**
     * Método Constructor de Controlador Permisos.
     * Inicializa Controllers::__construct
     * Inicializa y valida datos de session.
     */
    public function __construct()
    {
        parent::__construct();

        /*-------------------------------------------
        // [ Validación de Sesion ]*/
        $this->session = new Session;
        if (!$this->session->getStatus()) {
            $this->session->redirect('inicio');
        }
    }


    /**
     * Obtiene la lista de permisos asociados a un rol específico.
     * Ejecuta la funcion getModal(string $nameModal, $data) para mostrar el formualrio 
     * de registro de permisos, donde:
     * 1) $nameModal = Id del elemento modal que se va a ejecutar.
     * 2) $data = Array con los datos complementarios para llenar datos en la vista.
     * 
     * @param int $id Id de Rol
     * 
     */
    public function getPermisosRol(int $id): void
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_PERMISOS];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }
            $modal_permisos = $arrPermisos[MOD_PERMISOS];
            if (!$modal_permisos['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Asignar variables de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');

            /*-------------------------------------------
            [ Asignar y Limpiar parametros recibidos ]*/
            $rol_id = intval(strClean($id));

            if ($rol_id > 0) {


                /*-------------------------------------------
                [ Instanciar Modelo ]*/
                $class_model = new RolesModel;
                $class_model->setEmpresaId($empresa_id);
                $class_model->setSucursalId($sucursal_id);
                $class_model->setId($rol_id);

                $arrRol = $class_model->selectRecord($class_model);

                /*-------------------------------------------
                [ Obtiene array con los permisos del rol asociados a cada modulo ]*/
                $permisos_model = new PermisosModel;
                $arrModulos = $permisos_model->selectModulos();
                $permisos = $permisos_model->selectPermisosRol($rol_id);
                if (!empty($permisos)) {

                    $arrPermisosRol = json_decode($permisos['permisos'], true);

                    /*-------------------------------------------
                    [ Genera array de permisos default ]*/
                    $arrPermisos = array(
                        'r' => 0,
                        'c' => 0,
                        'u' => 0,
                        'd' => 0,
                        'e' => 0,
                        'p' => 0
                    );

                    /*-------------------------------------------
                    [ Genera array de con valores del rol ]*/
                    $arrPermisoRol = array(
                        'rol' => $arrRol['name'],
                        'rol_id' => $rol_id
                    );

                    if (empty($arrPermisosRol)) {

                        /*-------------------------------------------
                        [ Agregar al array de modulos, el array de permisos default ]*/
                        for ($i = 0; $i < count($arrModulos); $i++) {
                            $arrModulos[$i]['permisos'] = $arrPermisos;
                        }
                    } else {

                        /*-------------------------------------------
                        [ Agregar al array de modulos, el array de permisos de la base de datos, correspondientes a cada modulo ]*/
                        for ($i = 0; $i < count($arrModulos); $i++) {

                            $permisos_ =  $arrPermisosRol[$arrModulos[$i]['id']];
                            $arrPermisos = array(
                                'r' => $permisos_['r'],
                                'c' => $permisos_['c'],
                                'u' => $permisos_['u'],
                                'd' => $permisos_['d'],
                                'e' => $permisos_['e'],
                                'p' => $permisos_['p']
                            );
                            $arrModulos[$i]['permisos'] = $arrPermisos;

                            if (!isset($arrModulos[$i]['permisos'])) {
                                $arrPermisos = array('r' => 0, 'c' => 0, 'u' => 0, 'd' => 0, 'e' => 0, 'p' => 0);
                                $arrModulos[$i]['permisos'] = $arrPermisos;
                            }
                        }
                    }

                    /*-------------------------------------------
                    [ Agregar al array de permisos base, el array con los modulos y permisos correspondiente a cada rol ]*/
                    $arrPermisoRol['modulos'] = $arrModulos;

                    $arrPermisoRol[] = getPermisos(MOD_PERMISOS);
                } else {

                    /*-------------------------------------------
                    [ Genera array de permisos default ]*/
                    $arrPermisos = array(
                        'r' => 0,
                        'c' => 0,
                        'u' => 0,
                        'd' => 0,
                        'e' => 0,
                        'p' => 0
                    );

                    /*-------------------------------------------
                    [ Genera array de con valores del rol ]*/
                    $arrPermisoRol = array(
                        'rol' => $arrRol['name'],
                        'rol_id' => $rol_id
                    );

                    /*-------------------------------------------
                    [ Agregar al array de modulos, el array de permisos default ]*/
                    for ($i = 0; $i < count($arrModulos); $i++) {
                        $arrModulos[$i]['permisos'] = $arrPermisos;
                    }

                    /*-------------------------------------------
                    [ Agregar al array de permisos base, el array con los modulos y permisos correspondiente a cada rol ]*/
                    $arrPermisoRol['modulos'] = $arrModulos;

                    $arrPermisoRol[] = getPermisos(MOD_PERMISOS);
                }


                /*-------------------------------------------
                [ Obtiene html del modal de permisos enviando como parametro data, el array $arrPermisoRol ]*/
                $html = getModal("modalPermisos", $arrPermisoRol);
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
    }


    /**
     * Guardar permisos asociados a un rol específico.
     * 
     * @return string json_encode($arrData, JSON_UNESCAPED_UNICODE)
     * 
     */
    public function setPermisos()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_PERMISOS];
            if (!$this->permisosMod['r']) {
                $this->session->redirect('inicio');
                die();
            }
            $modal_permisos = $arrPermisos[MOD_PERMISOS];
            if (!$modal_permisos['r']) {
                $this->session->redirect('inicio');
                die();
            }

            /*-------------------------------------------
            [ Asignar variables de sesión ]*/
            $empresa_id = $this->session->get('empresa_id');
            $sucursal_id = $this->session->get('sucursal_id');
            $usuario_id_register = $this->session->get('usuario_id');

            /*-------------------------------------------
            [ Se reciben Datos del POST con FormData ]*/
            $rol_id = intval($_POST['rol_id']);
            $arrModulos = $_POST['modulos'];

            /*-------------------------------------------
            [ Elimina los permisos actuales ]*/
            $permisos_model = new PermisosModel;
            $permisos_model->deletePermisos($rol_id);

            /*-------------------------------------------
            [ Genera el arreglo para los permisos seleccionados. ]*/
            foreach ($arrModulos as $modulo) {

                $modulo_id = $modulo['modulo_id'];

                $r = empty($modulo['r']) ? 0 : 1;
                $c = empty($modulo['c']) ? 0 : 1;
                $u = empty($modulo['u']) ? 0 : 1;
                $d = empty($modulo['d']) ? 0 : 1;
                $e = empty($modulo['e']) ? 0 : 1;
                $p = empty($modulo['p']) ? 0 : 1;

                $arrPermisos[$modulo_id]['r'] = $r;
                $arrPermisos[$modulo_id]['c'] = $c;
                $arrPermisos[$modulo_id]['u'] = $u;
                $arrPermisos[$modulo_id]['d'] = $d;
                $arrPermisos[$modulo_id]['e'] = $e;
                $arrPermisos[$modulo_id]['p'] = $p;
            }

            /*-------------------------------------------
            [ Genera el archivo json. ]*/
            $permisos_json = json_encode($arrPermisos, JSON_UNESCAPED_UNICODE);


            /*-------------------------------------------
            [ Insertar ]*/
            $response =  $permisos_model->insertPermisos($permisos_json, $rol_id, $usuario_id_register);
            if ($response == true) {
                $arrResponse = getResponse('Permisos asignados correctamente', 'ok', true);
                $arrResponse['arrPermisos'] = $arrPermisos;
            } else {
                die(json_encode(getResponse('Error al realizar el registro'), JSON_UNESCAPED_UNICODE));
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
            die(json_encode(getResponse('Code per_1001. Error desconocido'), JSON_UNESCAPED_UNICODE));
        }

        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Obtiene la lista de permisos especailes asociados a un modulo específico.
     *
     * 
     * @param int $id Id de Rol
     * 
     */
    public function getPermisosMod(int $modulo_id): array
    {
        try {
            $arrPermisosMod = getPermisos($modulo_id);
            $arrResponse = $arrPermisosMod['permisosMod'];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th));
        }
        /*-------------------------------------------
        [ Retorna respuesta json_encode ]*/
        die(json_encode($arrResponse, JSON_UNESCAPED_UNICODE));
    }
}
