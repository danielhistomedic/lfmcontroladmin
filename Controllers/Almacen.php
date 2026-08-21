<?php

/**
 * Controlador Almacén
 * Módulo: Productos e Inventario de Almacén
 */
class Almacen extends Controllers
{

    private $session;
    private $permisosMod;

    const prefijo_msj_error = "almacen";

    /**
     * Método Constructor de Controlador Almacen.
     * Inicializa Controllers::__construct.
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
     * Carga la Vista Productos. 
     * URL: /almacen/productos
     */
    public function productos()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_ALMACEN_PRODUCTOS] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];

            // Valida si tiene acceso a la pagina.
            if (empty($this->permisosMod['r'])) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_ALMACEN_PRODUCTOS);

            // Asigna los permisos de Módulo y SideBar
            $data['permisos']    = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;

            //Datos de Usuario en Sesión.
            $data['empresa_id']            = $this->session->get('empresa_id');
            $data['sucursal_id']           = $this->session->get('sucursal_id');
            $data['theme']                 = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email']      = $this->session->get('email');
            $data['usuario']['rol']        = $this->session->get('rol');
            $data['usuario']['rol_id']     = $this->session->get('rol_id');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            //Id de Menu para script de Permisos
            $data['menu'] = MOD_ALMACEN_PRODUCTOS;

            //Header
            $data['page_title']        = !empty($menu['name'])        ? $menu['name']        : 'Productos y Existencias';
            $data['meta_description']  = !empty($menu['descripcion']) ? $menu['descripcion'] : 'Consultar los productos y existencias de almacén';
            $data['meta_keywords']     = !empty($menu['tags'])        ? $menu['tags']        : 'almacen, productos, existencias, stock';

            //Form Principal
            $data['icon_form_title']  = !empty($menu['icon_form_title']) ? $menu['icon_form_title'] : '<i class="fa-sharp fa-light fa-boxes-stacked text-primary me-2"></i>';
            $data['page_form_title']  = $data['icon_form_title'] . (!empty($menu['form_title']) ? $menu['form_title'] : ' Productos y Existencias');

            //Breadcrumb
            $data['page_breadcrumb']       = 'Almacén / Productos';
            $data['page_card_title']       = !empty($menu['card_title']) ? $menu['card_title'] : 'Productos y Existencias de Almacén';
            $data['page_card_description'] = $data['meta_description'];

            //JS de la página
            $data['page_functions_js'] = !empty($menu['js']) ? $menu['js'] : 'almacen_productos.js';

            //Call Vista
            $this->views->getView($this, "productos", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
        }
    }

    /**
     * Carga la Vista Inventario. 
     * URL: /almacen/inventario
     */
    public function inventario()
    {

        try {

            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_ALMACEN_INVENTARIO] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];

            // Valida si tiene acceso a la pagina.
            if (empty($this->permisosMod['r'])) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_ALMACEN_INVENTARIO);

            // Asigna los permisos de Módulo y SideBar
            $data['permisos']    = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;

            //Datos de Usuario en Sesión.
            $data['empresa_id']            = $this->session->get('empresa_id');
            $data['sucursal_id']           = $this->session->get('sucursal_id');
            $data['theme']                 = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email']      = $this->session->get('email');
            $data['usuario']['rol']        = $this->session->get('rol');
            $data['usuario']['rol_id']     = $this->session->get('rol_id');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            //Id de Menu para script de Permisos
            $data['menu'] = MOD_ALMACEN_INVENTARIO;

            //Header
            $data['page_title']        = !empty($menu['name'])        ? $menu['name']        : 'Inventario';
            $data['meta_description']  = !empty($menu['descripcion']) ? $menu['descripcion'] : 'Consultar el inventario de almacenes y subalmacenes de la empresa';
            $data['meta_keywords']     = !empty($menu['tags'])        ? $menu['tags']        : 'almacen, inventario, subalmacenes, stock';

            //Form Principal
            $data['icon_form_title']  = !empty($menu['icon_form_title']) ? $menu['icon_form_title'] : '<i class="fa-sharp fa-light fa-warehouse text-primary me-2"></i>';
            $data['page_form_title']  = $data['icon_form_title'] . (!empty($menu['form_title']) ? $menu['form_title'] : ' Inventario');

            // Breadcrumb
            $data['page_breadcrumb']       = 'Almacén / Inventario';
            $data['page_card_title']       = !empty($menu['card_title']) ? $menu['card_title'] : 'Inventario de Almacenes y Subalmacenes';
            $data['page_card_description'] = $data['meta_description'];

            // JS de la página
            $data['page_functions_js'] = !empty($menu['js']) ? $menu['js'] : 'almacen_inventario.js';

            // Obtener lista de almacenes para el filtro
            $almacenModel = new AlmacenModel();
            $data['almacenes'] = $almacenModel->getAlmacenes();

            // Call Vista
            $this->views->getView($this, "inventario", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
        }
    }

    /**
     * Endpoint AJAX para obtener existencias de inventario filtradas por almacén y/o producto.
     * URL: /almacen/getInventario
     */
    public function getInventario()
    {
        try {
            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $permisosMod = $arrPermisos[MOD_ALMACEN_INVENTARIO] ?? ['r' => 0];

            if (empty($permisosMod['r'])) {
                echo json_encode(['status' => false, 'msg' => 'Acceso no permitido.', 'data' => []], JSON_UNESCAPED_UNICODE);
                die();
            }

            $almacen  = $_POST['almacen']  ?? $_GET['almacen']  ?? '';
            $producto = $_POST['producto'] ?? $_GET['producto'] ?? '';

            $almacenModel = new AlmacenModel();
            $arrInventario = $almacenModel->getInventarioData($almacen, $producto);
            $arrKpis       = $almacenModel->getKpisInventario($almacen, $producto);

            $arrData = [];
            foreach ($arrInventario as $key => $row) {
                // Fotos
                $fotos = [];
                for ($i = 1; $i <= 5; $i++) {
                    $imgKey = "img" . $i;
                    if (!empty($row[$imgKey])) {
                        $imgFile = trim($row[$imgKey]);
                        $imgPath = "Assets/files/productos/" . $imgFile;
                        if (file_exists($imgPath)) {
                            $fotos[] = base_url() . "/" . $imgPath;
                        }
                    }
                }

                $arrData[] = [
                    'icvematerial'   => $row['icvematerial'],
                    'Clave'          => $row['Clave'],
                    'CCN'            => $row['CCN'],
                    'cDescripcion'   => $row['cDescripcion'],
                    'marca'          => $row['marca'],
                    'unidad_medida'  => $row['unidad_medida'],
                    'submarca'       => $row['submarca'],
                    'linea_producto' => $row['linea_producto'],
                    'categoria'      => $row['categoria'],
                    'modelo'         => $row['modelo'],
                    'num_catalogo'   => $row['num_catalogo'],
                    'num_parte'      => $row['num_parte'],
                    'serie'          => $row['serie'],
                    'material'       => $row['material'],
                    'grupo'          => $row['grupo'],
                    'clave_sat'      => $row['clave_sat'],
                    'almacen'        => $row['cdscalmacen'],
                    'existencia'     => floatval($row['existencia']),
                    'reservadas'     => floatval($row['reservadas'] ?? 0),
                    'disponibles'    => floatval($row['disponibles'] ?? ($row['existencia'] - ($row['reservadas'] ?? 0))),
                    'costo_promedio' => floatval($row['costo_promedio']),
                    'costo_ultimo'   => floatval($row['costo_ultimo']),
                    'moneda'         => $row['moneda'] ?? '',
                    'fotos'          => $fotos
                ];
            }

            echo json_encode([
                'status'          => true,
                'total_registros' => count($arrData),
                'kpis'            => $arrKpis,
                'data'            => $arrData
            ], JSON_UNESCAPED_UNICODE);
            die();
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
            echo json_encode(['status' => false, 'msg' => 'Error al procesar la solicitud.', 'data' => []], JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    /**
     * Endpoint AJAX para alimentar el selector de producto (Select2 autocompletado).
     * URL: /almacen/getSelectProductos
     */
    public function getSelectProductos()
    {
        try {
            $search = $_POST['q'] ?? $_GET['q'] ?? $_POST['search'] ?? $_GET['search'] ?? '';

            $almacenModel = new AlmacenModel();
            $arrProductos = $almacenModel->buscarProductosSelect($search);

            echo json_encode([
                'status'  => true,
                'results' => $arrProductos
            ], JSON_UNESCAPED_UNICODE);
            die();
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
            echo json_encode(['status' => false, 'results' => []], JSON_UNESCAPED_UNICODE);
            die();
        }
    }


    /**
     * Endpoint AJAX para obtener productos y respuesta inteligente.
     * URL: /almacen/getProductos
     */
    public function getProductos()
    {
        try {
            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $permisosMod = $arrPermisos[MOD_ALMACEN_PRODUCTOS] ?? ['r' => 0];

            if (empty($permisosMod['r'])) {
                echo json_encode(['status' => false, 'msg' => 'Acceso no permitido.', 'data' => []], JSON_UNESCAPED_UNICODE);
                die();
            }

            $busqueda = $_POST['busqueda'] ?? $_GET['busqueda'] ?? '';

            $almacenModel = new AlmacenModel();
            $arrProductos = $almacenModel->searchProductosAlmacen($busqueda);

            $respuestaHTML = "";
            if (!empty($busqueda)) {
                if (!empty($arrProductos)) {
                    $respuestaHTML = "<div class='fw-bold mb-2' style='color: #1e293b; font-size: 1rem;'>Tenemos:</div>";
                    $respuestaHTML .= "<ul class='list-unstyled mb-0 ms-1'>";
                    $topProducts = array_slice($arrProductos, 0, 5);
                    foreach ($topProducts as $prod) {
                        $existencia = floatval($prod['existencias_almacen']) > 0 ? floatval($prod['existencias_almacen']) : floatval($prod['existencia_base']);
                        $reservadas = floatval($prod['reservadas_almacen'] ?? 0);
                        $disponibles = $existencia - $reservadas;
                        $desgloseStr = !empty($prod['desgloses_almacen']) ? "(" . $prod['desgloses_almacen'] . ")" : "";
                        
                        $claveStr = !empty($prod['Clave']) ? trim($prod['Clave']) : '';
                        $ccnStr   = !empty($prod['CCN']) ? trim($prod['CCN']) : '';
                        
                        $infoCodigos = "";
                        if (!empty($claveStr) && !empty($ccnStr)) {
                            $infoCodigos = "<span class='badge bg-dark me-1' style='font-size: 0.8rem;'>Clave: " . htmlspecialchars($claveStr) . "</span> <span class='badge bg-secondary me-2' style='font-size: 0.8rem;'>CCN: " . htmlspecialchars($ccnStr) . "</span> ";
                        } else if (!empty($claveStr)) {
                            $infoCodigos = "<span class='badge bg-dark me-2' style='font-size: 0.8rem;'>Clave: " . htmlspecialchars($claveStr) . "</span> ";
                        } else if (!empty($ccnStr)) {
                            $infoCodigos = "<span class='badge bg-secondary me-2' style='font-size: 0.8rem;'>CCN: " . htmlspecialchars($ccnStr) . "</span> ";
                        }

                        $colorDisponibles = $disponibles > 0 ? '#16a34a' : ($existencia > 0 ? '#dc2626' : '#2563eb');

                        $respuestaHTML .= "<li class='mb-2 pb-1'>";
                        $respuestaHTML .= "<div class='fw-bold text-dark' style='font-size: 0.95rem;'>• " . $infoCodigos . htmlspecialchars($prod['cDescripcion']) . "</div>";
                        $respuestaHTML .= "<div class='ms-3 mt-1' style='font-size: 0.85rem; color: #2563eb; font-weight: 500;'>";
                        $respuestaHTML .= "<span style='font-size: 0.7rem; vertical-align: middle; margin-right: 3px;'>▪</span> ";
                        $respuestaHTML .= "Existencias: <strong style='color: #1d4ed8; font-weight: 700;'>" . intval($existencia) . "</strong>, ";
                        $respuestaHTML .= "Reservadas: <strong style='color: #d97706; font-weight: 700;'>" . intval($reservadas) . "</strong> y ";
                        $respuestaHTML .= "Disponibles: <strong style='color: " . $colorDisponibles . "; font-weight: 700;'>" . intval($disponibles) . "</strong>";
                        if (!empty($desgloseStr)) {
                            $respuestaHTML .= " <span style='color: #64748b; font-size: 0.95em;'>" . htmlspecialchars($desgloseStr) . "</span>";
                        }
                        $respuestaHTML .= "</div>";
                        $respuestaHTML .= "</li>";
                    }
                    $respuestaHTML .= "</ul>";
                    if (count($arrProductos) > 5) {
                        $respuestaHTML .= "<div class='mt-2 text-muted fst-italic' style='font-size: 0.84rem;'><i class='fa-solid fa-circle-info me-1'></i> *(Y " . (count($arrProductos) - 5) . " producto(s) adicional(es) mostrado(s) en la tabla a continuación)*</div>";
                    }
                } else {
                    $respuestaHTML = "<div class='text-danger fw-semibold'><i class='fa-solid fa-circle-exclamation me-1'></i> No se encontraron existencias o productos coincidentes para la búsqueda: \"" . htmlspecialchars($busqueda) . "\".</div>";
                }
            } else {
                $respuestaHTML = "<div class='text-muted'><i class='fa-solid fa-boxes-stacked me-1'></i> Mostrando listado general de productos activos en existencias.</div>";
            }

            // Preparar filas con fotos preprocesadas
            $arrData = [];
            foreach ($arrProductos as $key => $row) {
                $existenciaTotal = floatval($row['existencias_almacen']) > 0 ? floatval($row['existencias_almacen']) : floatval($row['existencia_base']);
                $reservadasTotal = floatval($row['reservadas_almacen'] ?? 0);
                $disponiblesTotal = $existenciaTotal - $reservadasTotal;

                // Fotos
                $fotos = [];
                for ($i = 1; $i <= 5; $i++) {
                    $imgKey = "img" . $i;
                    if (!empty($row[$imgKey])) {
                        $imgFile = trim($row[$imgKey]);
                        $imgPath = "Assets/files/productos/" . $imgFile;
                        if (file_exists($imgPath)) {
                            $fotos[] = base_url() . "/" . $imgPath;
                        }
                    }
                }

                $arrData[$key] = [
                    'icvematerial'       => $row['icvematerial'],
                    'Clave'              => $row['Clave'],
                    'CCN'                => $row['CCN'],
                    'cDescripcion'       => $row['cDescripcion'],
                    'marca'              => $row['marca'],
                    'unidad_medida'      => $row['unidad_medida'],
                    'submarca'           => $row['submarca'],
                    'linea_producto'     => $row['linea_producto'],
                    'categoria'          => $row['categoria'],
                    'modelo'             => $row['modelo'],
                    'num_catalogo'       => $row['num_catalogo'],
                    'num_parte'          => $row['num_parte'],
                    'serie'              => $row['serie'],
                    'material'           => $row['material'],
                    'grupo'              => $row['grupo'],
                    'clave_sat'          => $row['clave_sat'],
                    'clave_cliente'      => $row['clave_cliente'] ?? '',
                    'existencia'         => intval($existenciaTotal),
                    'reservadas'         => intval($reservadasTotal),
                    'disponibles'        => intval($disponiblesTotal),
                    'desgloses_almacen'  => $row['desgloses_almacen'] ?? '',
                    'fotos'              => $fotos
                ];
            }

            $arrResponse = [
                'status'                => true,
                'busqueda'              => $busqueda,
                'respuesta_inteligente' => trim($respuestaHTML),
                'total_registros'       => count($arrData),
                'data'                  => $arrData
            ];

            echo json_encode($arrResponse, JSON_UNESCAPED_UNICODE);
            die();
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
            echo json_encode(['status' => false, 'msg' => 'Error al procesar la solicitud.'], JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    /**
     * Carga la Vista Control de Productos Reservados. 
     * URL: /almacen/reservados
     */
    public function reservados()
    {
        try {
            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            if (empty($arrPermisos)) {
                $this->session->redirect('inicio');
                return;
            }
            $this->permisosMod = $arrPermisos[MOD_ALMACEN_RESERVADOS] ?? ['r' => 0, 'c' => 0, 'u' => 0, 'd' => 0];

            // Valida si tiene acceso a la pagina.
            if (empty($this->permisosMod['r'])) {
                echo "<h4>Lo sentimos, Acceso restringido</h4>";
                die();
            }

            /*-------------------------------------------
            [ Obtener datos de Modulo ]*/
            $menus_model = new MenusModel;
            $menu = $menus_model->selectMenu(MOD_ALMACEN_RESERVADOS);

            // Asigna los permisos de Módulo y SideBar
            $data['permisos']    = $arrPermisos;
            $data['permisosMod'] = $this->permisosMod;

            //Datos de Usuario en Sesión.
            $data['empresa_id']            = $this->session->get('empresa_id');
            $data['sucursal_id']           = $this->session->get('sucursal_id');
            $data['theme']                 = $this->session->get('theme');
            $data['usuario']['nombre_solo'] = $this->session->get('nombre_solo');
            $data['usuario']['email']      = $this->session->get('email');
            $data['usuario']['rol']        = $this->session->get('rol');
            $data['usuario']['rol_id']     = $this->session->get('rol_id');

            // Configuracion
            $configuracion_model = new ConfiguracionModel;
            $configuracion_model->setEmpresaId($data['empresa_id']);
            $configuracion = $configuracion_model->selectRecord($configuracion_model);
            $data['configuracion'] = $configuracion;

            //Id de Menu para script de Permisos
            $data['menu'] = MOD_ALMACEN_RESERVADOS;

            //Header
            $data['page_title']        = !empty($menu['name'])        ? $menu['name']        : 'Control de Productos Reservados';
            $data['meta_description']  = !empty($menu['descripcion']) ? $menu['descripcion'] : 'Control y consulta de productos reservados por cliente en almacén';
            $data['meta_keywords']     = !empty($menu['tags'])        ? $menu['tags']        : 'almacen, reservas, reservados, apartados, clientes, stock';

            //Form Principal
            $data['icon_form_title']  = !empty($menu['icon_form_title']) ? $menu['icon_form_title'] : '<i class="fa-sharp fa-light fa-box-archive text-primary me-2"></i>';
            $data['page_form_title']  = $data['icon_form_title'] . (!empty($menu['form_title']) ? $menu['form_title'] : ' Control de Productos Reservados');

            // Breadcrumb
            $data['page_breadcrumb']       = 'Almacén / Control de Productos Reservados';
            $data['page_card_title']       = !empty($menu['card_title']) ? $menu['card_title'] : 'Lista de Productos Reservados por Cliente';
            $data['page_card_description'] = $data['meta_description'];

            // JS de la página
            $data['page_functions_js'] = !empty($menu['js']) ? $menu['js'] : 'almacen_reservados.js';

            // Obtener lista de almacenes y clientes para filtros
            $almacenModel = new AlmacenModel();
            $data['almacenes'] = $almacenModel->getAlmacenes();
            $data['clientes']  = $almacenModel->getClientesConReservas();

            // Call Vista
            $this->views->getView($this, "reservados", $data);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
        }
    }

    /**
     * Endpoint AJAX para obtener productos reservados filtrados por cliente, almacén o antigüedad.
     * URL: /almacen/getReservados
     */
    public function getReservados()
    {
        try {
            /*-------------------------------------------
            [ Validación de Permisos ]*/
            $arrPermisos = getPermisosGlobal();
            $permisosMod = $arrPermisos[MOD_ALMACEN_RESERVADOS] ?? ['r' => 0];

            if (empty($permisosMod['r'])) {
                echo json_encode(['status' => false, 'msg' => 'Acceso no permitido.', 'data' => []], JSON_UNESCAPED_UNICODE);
                die();
            }

            $clienteId  = $_POST['cliente_id'] ?? $_GET['cliente_id'] ?? '';
            $almacen    = $_POST['almacen']    ?? $_GET['almacen']    ?? '';
            $antiguedad = $_POST['antiguedad'] ?? $_GET['antiguedad'] ?? '';

            $almacenModel = new AlmacenModel();
            $arrReservas = $almacenModel->getReservadosData($clienteId, $almacen, $antiguedad);
            $arrKpis     = $almacenModel->getKpisReservados($clienteId, $almacen);

            $arrData = [];
            foreach ($arrReservas as $row) {
                // Fotos
                $fotos = [];
                for ($i = 1; $i <= 5; $i++) {
                    $imgKey = "img" . $i;
                    if (!empty($row[$imgKey])) {
                        $imgFile = trim($row[$imgKey]);
                        $imgPath = "Assets/files/productos/" . $imgFile;
                        if (file_exists($imgPath)) {
                            $fotos[] = base_url() . "/" . $imgPath;
                        }
                    }
                }

                // Cálculo textual amigable del tiempo que lleva reservado
                $min  = intval($row['minutos_transcurridos'] ?? 0);
                $hrs  = intval($row['horas_transcurridas'] ?? 0);
                $dias = intval($row['dias_transcurridos'] ?? 0);

                if ($dias >= 1) {
                    $remHrs = $hrs % 24;
                    $tiempoTexto = $dias == 1 ? "1 día" : "{$dias} días";
                    if ($remHrs > 0) $tiempoTexto .= ", {$remHrs} h";
                } else if ($hrs >= 1) {
                    $remMin = $min % 60;
                    $tiempoTexto = $hrs == 1 ? "1 hora" : "{$hrs} horas";
                    if ($remMin > 0) $tiempoTexto .= ", {$remMin} min";
                } else {
                    $tiempoTexto = $min <= 1 ? "Menos de 1 min" : "{$min} minutos";
                }

                // Nivel de antigüedad para alertas y colores
                $nivelAntiguedad = 'reciente'; // < 7 días
                if ($dias > 30) {
                    $nivelAntiguedad = 'critica'; // > 30 días
                } else if ($dias > 15) {
                    $nivelAntiguedad = 'urgente'; // 16 - 30 días
                } else if ($dias >= 7) {
                    $nivelAntiguedad = 'atencion'; // 7 - 15 días
                }

                // Fecha formateada
                $fechaFmt = !empty($row['fchregistro']) ? date('d/m/Y H:i', strtotime($row['fchregistro'])) : 'N/D';

                $arrData[] = [
                    'id'                   => $row['id'],
                    'cliente_id'           => intval($row['cliente_id'] ?? 0),
                    'cliente_nombre'       => !empty($row['cliente_nombre']) ? $row['cliente_nombre'] : 'Sin cliente asignado',
                    'cliente_razon_social' => $row['cliente_razon_social'] ?? '',
                    'tiempo_texto'         => $tiempoTexto,
                    'dias_transcurridos'   => $dias,
                    'horas_transcurridas'  => $hrs,
                    'nivel_antiguedad'     => $nivelAntiguedad,
                    'fecha_registro'       => $fechaFmt,
                    'fecha_registro_raw'   => $row['fchregistro'],
                    'cantidad'             => floatval($row['cantidad']),
                    'unidad_medida'        => $row['unidad_medida'] ?: 'pza',
                    'clave'                => $row['clave'],
                    'ccn'                  => $row['ccn'],
                    'material_descripcion' => $row['material_descripcion'],
                    'almacen'              => $row['almacen'],
                    'ccvealmacen'          => $row['ccvealmacen'],
                    'pedido_id'            => !empty($row['pedido_id']) ? intval($row['pedido_id']) : 0,
                    'orden_compra'         => (!empty($row['orden_compra']) && $row['orden_compra'] !== 'S/N' && $row['orden_compra'] !== 'NA') ? $row['orden_compra'] : '',
                    'marca'                => $row['marca'],
                    'submarca'             => $row['submarca'],
                    'linea_producto'       => $row['linea_producto'],
                    'categoria'            => $row['categoria'],
                    'modelo'               => $row['modelo'] ?? '',
                    'num_catalogo'         => $row['num_catalogo'] ?? '',
                    'num_parte'            => $row['num_parte'] ?? '',
                    'serie'                => $row['serie'] ?? '',
                    'material'             => $row['material'] ?? '',
                    'grupo'                => $row['grupo'] ?? '',
                    'clave_sat'            => $row['clave_sat'] ?? '',
                    'usuario_reserva'      => $row['ccveusuario'] ?: 'Sistema',
                    'fotos'                => $fotos
                ];
            }

            echo json_encode([
                'status'          => true,
                'total_registros' => count($arrData),
                'kpis'            => $arrKpis,
                'data'            => $arrData
            ], JSON_UNESCAPED_UNICODE);
            die();
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
            echo json_encode(['status' => false, 'msg' => 'Error al procesar la solicitud.', 'data' => []], JSON_UNESCAPED_UNICODE);
            die();
        }
    }

    /**
     * Endpoint AJAX para rotar una fotografía de producto y persistir los cambios en el archivo físico.
     * URL: /almacen/rotarFoto
     */
    public function rotarFoto()
    {
        try {
            if (!$this->session->getStatus()) {
                echo json_encode(['status' => false, 'msg' => 'Sesión no válida o expirada.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            $fotoUrl = $_POST['foto'] ?? '';
            $direccion = $_POST['direccion'] ?? 'der'; // 'izq' (antihorario 90°) o 'der' (horario 90°)

            if (empty($fotoUrl)) {
                echo json_encode(['status' => false, 'msg' => 'No se especificó la imagen a rotar.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            // Obtener solo el nombre del archivo para prevenir Directory Traversal
            $parsedPath = parse_url($fotoUrl, PHP_URL_PATH);
            $fileName = basename($parsedPath);
            $filePath = "Assets/files/productos/" . $fileName;

            if (!file_exists($filePath) || !is_file($filePath)) {
                echo json_encode(['status' => false, 'msg' => 'El archivo de imagen no fue encontrado: ' . $fileName], JSON_UNESCAPED_UNICODE);
                die();
            }

            // Validar extensión
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                echo json_encode(['status' => false, 'msg' => 'Formato no compatible para rotación (' . $ext . ').'], JSON_UNESCAPED_UNICODE);
                die();
            }

            // En PHP GD: imagerotate rota en sentido ANTIHORARIO (counter-clockwise)
            // Girar Izquierda (counter-clockwise 90°): $degrees = 90
            // Girar Derecha (clockwise 90° = 270° counter-clockwise): $degrees = 270
            $degrees = ($direccion === 'izq' || $direccion === 'left') ? 90 : 270;

            $sourceImage = null;
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $sourceImage = @imagecreatefromjpeg($filePath);
                    break;
                case 'png':
                    $sourceImage = @imagecreatefrompng($filePath);
                    break;
                case 'webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $sourceImage = @imagecreatefromwebp($filePath);
                    }
                    break;
            }

            if (!$sourceImage) {
                echo json_encode(['status' => false, 'msg' => 'No se pudo procesar la imagen seleccionada.'], JSON_UNESCAPED_UNICODE);
                die();
            }

            // Rotar preservando transparencias o formato original
            if ($ext === 'png') {
                imagealphablending($sourceImage, false);
                imagesavealpha($sourceImage, true);
                $transparent = imagecolorallocatealpha($sourceImage, 0, 0, 0, 127);
                $rotatedImage = imagerotate($sourceImage, $degrees, $transparent);
                if ($rotatedImage) {
                    imagealphablending($rotatedImage, false);
                    imagesavealpha($rotatedImage, true);
                    imagepng($rotatedImage, $filePath, 9);
                    imagedestroy($rotatedImage);
                }
            } elseif ($ext === 'webp') {
                $rotatedImage = imagerotate($sourceImage, $degrees, 0);
                if ($rotatedImage) {
                    imagewebp($rotatedImage, $filePath, 95);
                    imagedestroy($rotatedImage);
                }
            } else {
                $rotatedImage = imagerotate($sourceImage, $degrees, 0);
                if ($rotatedImage) {
                    imagejpeg($rotatedImage, $filePath, 95);
                    imagedestroy($rotatedImage);
                }
            }

            imagedestroy($sourceImage);

            echo json_encode([
                'status'    => true,
                'msg'       => 'Fotografía girada y guardada correctamente.',
                'filename'  => $fileName,
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
            die();
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, self::prefijo_msj_error));
            echo json_encode(['status' => false, 'msg' => 'Error inesperado al rotar la imagen.'], JSON_UNESCAPED_UNICODE);
            die();
        }
    }
}


