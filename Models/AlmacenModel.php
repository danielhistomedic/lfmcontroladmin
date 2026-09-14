<?php

/**
 * Clase AlmacenModel
 */
class AlmacenModel extends Mysql
{

    /**
     * Método Constructor de AlmacenModel.
     * Inicializa Mysql::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca productos activos (iBaja = 0 y cClasificacion = 'PRODUCTO')
     * e integra existencias desglosadas por almacén y fotos FTP.
     * 
     * @param string $busqueda
     * @return array
     */
    public function searchProductosAlmacen(string $busqueda = ''): array
    {
        try {
            $busqueda = trim($busqueda);

            $sqlBase = "SELECT 
                        m.icvematerial,
                        IFNULL(m.ccvematerial, '') AS Clave,
                        IFNULL(m.ccveMaterialAlmacen, '') AS CCN,
                        IFNULL(m.cDescripcion, '') AS cDescripcion,
                        IFNULL(mar.cdscmarca, '') AS marca,
                        IFNULL(m.ccveunidad, '') AS unidad_medida,
                        IFNULL(sub.cdscsubmarca, '') AS submarca,
                        IFNULL(cla.cdscclave, '') AS linea_producto,
                        IFNULL(cat.categoria, '') AS categoria,
                        IFNULL(m.modelo, '') AS modelo,
                        IFNULL(m.num_catalogo, '') AS num_catalogo,
                        IFNULL(m.num_parte, '') AS num_parte,
                        IFNULL(m.serie, '') AS serie,
                        IFNULL(m.material, '') AS material,
                        IFNULL(m.grupo, '') AS grupo,
                        IFNULL(m.clave_sat, '') AS clave_sat,
                        IFNULL(sap.clave_cliente, '') AS clave_cliente,
                        IFNULL(sap.descripcion_cliente, '') AS descripcion_cliente,
                        IFNULL(m.iExistenciaActual, 0) AS existencia_base,
                        ftp.img1, ftp.img2, ftp.img3, ftp.img4, ftp.img5,
                        (
                            SELECT IFNULL(SUM(ae.iExistenciaActual), 0)
                            FROM tb_almacen_existencias ae
                            WHERE ae.ccvematerial = m.ccvematerial
                        ) AS existencias_almacen,
                        (
                            SELECT IFNULL(SUM(ae.iExistenciaReservado), 0)
                            FROM tb_almacen_existencias ae
                            WHERE ae.ccvematerial = m.ccvematerial
                        ) AS reservadas_almacen,
                        (
                            SELECT IFNULL(SUM(ae.iExistenciaActual - ae.iExistenciaReservado), 0)
                            FROM tb_almacen_existencias ae
                            WHERE ae.ccvematerial = m.ccvematerial
                        ) AS disponibles_almacen,
                        (
                            SELECT GROUP_CONCAT(CONCAT(IFNULL(ca.cdscalmacen, ae.ccvealmacen), ': ', ROUND(ae.iExistenciaActual, 0)) SEPARATOR ' | ')
                            FROM tb_almacen_existencias ae
                            LEFT JOIN cat_almacen ca ON ca.ccvealmacen = ae.ccvealmacen
                            WHERE ae.ccvematerial = m.ccvematerial AND ae.iExistenciaActual > 0
                        ) AS desgloses_almacen
                    FROM tb_materiales m
                    LEFT JOIN cat_marcas mar ON (mar.icvemarca = m.icvemarca)
                    LEFT JOIN cat_submarcas sub ON (sub.id = m.submarca_id)
                    LEFT JOIN cat_claves cla ON (cla.icveclave = m.icveclave)
                    LEFT JOIN cat_categorias cat ON (cat.id = m.categoria_id)
                    LEFT JOIN tb_materiales_ftp ftp ON (ftp.idDocto = m.ccvematerial OR ftp.idDocto = m.icvematerial)
                    LEFT JOIN tb_materiales_claves_sap sap ON (sap.ccvematerial = m.ccvematerial)
                    WHERE m.iBaja = 0 AND m.cClasificacion = 'PRODUCTO' ";

            if (!empty($busqueda)) {
                // Tokenizar palabras clave ignorando stopwords
                $stopwords = ['el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'de', 'del', 'al', 'con', 'en', 'para', 'por', 'sin', 'sobre', 'que', 'quien', 'cual', 'donde', 'como', 'tienen', 'tiene', 'tendran', 'busco', 'necesito', 'hay', 'es', 'son'];
                $cleanString = preg_replace('/[¿\?!\¡,\.\;:"\']/u', '', mb_strtolower($busqueda, 'UTF-8'));
                $words = array_values(array_filter(explode(' ', $cleanString), function ($w) use ($stopwords) {
                    $w = trim($w);
                    return mb_strlen($w, 'UTF-8') > 1 && !in_array($w, $stopwords);
                }));

                if (!empty($words)) {
                    // Intento 1: Coincidencia con AND (todas las palabras)
                    $wherePartsAnd = [];
                    $arrValuesAnd = [];
                    foreach ($words as $i => $word) {
                        $param = ":word_" . $i;
                        $wherePartsAnd[] = "(
                            m.cDescripcion LIKE $param OR 
                            m.ccvematerial LIKE $param OR 
                            m.ccveMaterialAlmacen LIKE $param OR 
                            sap.clave_cliente LIKE $param OR 
                            sap.descripcion_cliente LIKE $param OR 
                            mar.cdscmarca LIKE $param OR 
                            sub.cdscsubmarca LIKE $param OR 
                            cla.cdscclave LIKE $param OR 
                            cat.categoria LIKE $param OR 
                            m.modelo LIKE $param OR 
                            m.num_catalogo LIKE $param OR 
                            m.num_parte LIKE $param OR 
                            m.serie LIKE $param OR 
                            m.material LIKE $param OR 
                            m.grupo LIKE $param OR 
                            m.clave_sat LIKE $param
                        )";
                        $arrValuesAnd["word_" . $i] = "%" . $word . "%";
                    }

                    $sqlAnd = $sqlBase . " AND (" . implode(" AND ", $wherePartsAnd) . ") ORDER BY m.icvematerial DESC LIMIT 500";
                    $arrResponse = $this->select($sqlAnd, $arrValuesAnd);

                    if (!empty($arrResponse)) {
                        return $arrResponse;
                    }

                    // Intento 2: Coincidencia con OR (alguna de las palabras) si AND no dio resultados
                    $wherePartsOr = [];
                    $arrValuesOr = [];
                    foreach ($words as $i => $word) {
                        $param = ":word_or_" . $i;
                        $wherePartsOr[] = "(
                            m.cDescripcion LIKE $param OR 
                            sap.clave_cliente LIKE $param OR 
                            sap.descripcion_cliente LIKE $param OR 
                            mar.cdscmarca LIKE $param OR 
                            sub.cdscsubmarca LIKE $param OR 
                            cla.cdscclave LIKE $param OR 
                            cat.categoria LIKE $param OR 
                            m.modelo LIKE $param
                        )";
                        $arrValuesOr["word_or_" . $i] = "%" . $word . "%";
                    }
                    $sqlOr = $sqlBase . " AND (" . implode(" OR ", $wherePartsOr) . ") ORDER BY m.icvematerial DESC LIMIT 500";
                    $arrResponse = $this->select($sqlOr, $arrValuesOr);
                    return is_array($arrResponse) ? $arrResponse : [];
                } else {
                    $sqlFull = $sqlBase . " AND (m.cDescripcion LIKE :full_search OR m.ccvematerial LIKE :full_search OR sap.clave_cliente LIKE :full_search) ORDER BY m.icvematerial DESC LIMIT 500";
                    $arrResponse = $this->select($sqlFull, ['full_search' => "%" . $busqueda . "%"]);
                    return is_array($arrResponse) ? $arrResponse : [];
                }
            } else {
                $sqlBase .= " ORDER BY m.icvematerial DESC LIMIT 500";
                $arrResponse = $this->select($sqlBase, []);
                return is_array($arrResponse) ? $arrResponse : [];
            }
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene los almacenes activos de cat_almacen
     * 
     * @return array
     */
    public function getAlmacenes(): array
    {
        try {
            $sql = "SELECT ccvealmacen, cdscalmacen FROM cat_almacen WHERE iActivo = 1 ORDER BY cdscalmacen ASC";
            $arrResponse = $this->select($sql, []);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Búsqueda ligera de productos para el filtro autocompletado (Select2)
     * 
     * @param string $search
     * @return array
     */
    public function buscarProductosSelect(string $search = ''): array
    {
        try {
            $search = trim($search);
            $sql = "SELECT 
                        m.ccvematerial AS id,
                        CONCAT(IFNULL(m.cDescripcion, ''), ' [Clave: ', IFNULL(m.ccvematerial, ''), ' | CCN: ', IFNULL(m.ccveMaterialAlmacen, ''), ']') AS text
                    FROM tb_materiales m
                    WHERE m.iBaja = 0 AND m.cClasificacion = 'PRODUCTO' ";
            $arrValues = [];
            if (!empty($search)) {
                $sql .= " AND (m.cDescripcion LIKE :search OR m.ccvematerial LIKE :search OR m.ccveMaterialAlmacen LIKE :search) ";
                $arrValues['search'] = '%' . $search . '%';
            }
            $sql .= " ORDER BY m.cDescripcion ASC LIMIT 50";
            $arrResponse = $this->select($sql, $arrValues);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene existencias e inventario completo filtrado por almacén y/o producto
     * 
     * @param string $almacen
     * @param string $producto
     * @return array
     */
    public function getInventarioData(string $almacen = '', string $producto = ''): array
    {
        try {
            $almacen = trim($almacen);
            $producto = trim($producto);

            $sql = "SELECT 
                        m.icvematerial,
                        IFNULL(m.ccvematerial, '') AS Clave,
                        IFNULL(m.ccveMaterialAlmacen, '') AS CCN,
                        IFNULL(m.cDescripcion, '') AS cDescripcion,
                        IFNULL(mar.cdscmarca, '') AS marca,
                        IFNULL(m.ccveunidad, '') AS unidad_medida,
                        IFNULL(sub.cdscsubmarca, '') AS submarca,
                        IFNULL(cla.cdscclave, '') AS linea_producto,
                        IFNULL(cat.categoria, '') AS categoria,
                        IFNULL(m.modelo, '') AS modelo,
                        IFNULL(m.num_catalogo, '') AS num_catalogo,
                        IFNULL(m.num_parte, '') AS num_parte,
                        IFNULL(m.serie, '') AS serie,
                        IFNULL(m.material, '') AS material,
                        IFNULL(m.grupo, '') AS grupo,
                        IFNULL(m.clave_sat, '') AS clave_sat,
                        ftp.img1, ftp.img2, ftp.img3, ftp.img4, ftp.img5,
                        ae.ccvealmacen,
                        IFNULL(ca.cdscalmacen, ae.ccvealmacen) AS cdscalmacen,
                        IFNULL(ae.iExistenciaActual, 0) AS existencia,
                        IFNULL(ae.iExistenciaReservado, 0) AS reservadas,
                        IFNULL(ae.iExistenciaActual - ae.iExistenciaReservado, 0) AS disponibles,
                        IFNULL(ae.iCostoPromedio, 0) AS costo_promedio,
                        IFNULL(ae.iUltimoCosto, 0) AS costo_ultimo,
                        IFNULL(tc.siglas, '') AS moneda
                    FROM tb_almacen_existencias ae
                    INNER JOIN tb_materiales m ON m.ccvematerial = ae.ccvematerial
                    LEFT JOIN cat_almacen ca ON ca.ccvealmacen = ae.ccvealmacen
                    LEFT JOIN cat_tipos_cambio tc ON tc.id = ae.IdMoneda
                    LEFT JOIN cat_marcas mar ON mar.icvemarca = m.icvemarca
                    LEFT JOIN cat_submarcas sub ON sub.id = m.submarca_id
                    LEFT JOIN cat_claves cla ON cla.icveclave = m.icveclave
                    LEFT JOIN cat_categorias cat ON cat.id = m.categoria_id
                    LEFT JOIN tb_materiales_ftp ftp ON ftp.idDocto = m.ccvematerial
                    WHERE m.iBaja = 0 AND m.cClasificacion = 'PRODUCTO' ";

            $arrValues = [];

            if (!empty($almacen)) {
                $sql .= " AND ae.ccvealmacen = :almacen ";
                $arrValues['almacen'] = $almacen;
            }

            if (!empty($producto)) {
                $sql .= " AND (m.ccvematerial = :producto OR m.ccveMaterialAlmacen = :producto OR m.icvematerial = :producto) ";
                $arrValues['producto'] = $producto;
            }

            $sql .= " ORDER BY ca.cdscalmacen ASC, m.cDescripcion ASC ";

            $arrResponse = $this->select($sql, $arrValues);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene los totales de inventario agrupados por almacén y moneda para las tarjetas KPI
     * 
     * @param string $almacen
     * @param string $producto
     * @return array
     */
    public function getKpisInventario(string $almacen = '', string $producto = ''): array
    {
        try {
            $almacen  = trim($almacen);
            $producto = trim($producto);

            $sql = "SELECT 
                        ae.ccvealmacen,
                        IFNULL(ca.cdscalmacen, ae.ccvealmacen) AS almacen,
                        IFNULL(tc.siglas, 'MXN') AS moneda,
                        COUNT(DISTINCT ae.ccvematerial) AS total_productos,
                        IFNULL(SUM(ae.iExistenciaActual), 0) AS total_existencia,
                        IFNULL(SUM(ae.iExistenciaActual * ae.iCostoPromedio), 0) AS valor_total
                    FROM tb_almacen_existencias ae
                    INNER JOIN tb_materiales m ON m.ccvematerial = ae.ccvematerial
                    LEFT JOIN cat_almacen ca ON ca.ccvealmacen = ae.ccvealmacen
                    LEFT JOIN cat_tipos_cambio tc ON tc.id = ae.IdMoneda
                    WHERE m.iBaja = 0 AND m.cClasificacion = 'PRODUCTO' ";

            $arrValues = [];

            if (!empty($almacen)) {
                $sql .= " AND ae.ccvealmacen = :almacen ";
                $arrValues['almacen'] = $almacen;
            }

            if (!empty($producto)) {
                $sql .= " AND (m.ccvematerial = :producto OR m.ccveMaterialAlmacen = :producto OR m.icvematerial = :producto) ";
                $arrValues['producto'] = $producto;
            }

            $sql .= " GROUP BY ae.ccvealmacen, IFNULL(ca.cdscalmacen, ae.ccvealmacen), IFNULL(tc.siglas, 'MXN')
                      ORDER BY ca.cdscalmacen ASC, tc.siglas ASC ";

            $arrResponse = $this->select($sql, $arrValues);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene el listado de productos reservados activos con cliente, pedido y tiempo de reserva
     * 
     * @param string $clienteId
     * @param string $almacen
     * @param string $antiguedad
     * @return array
     */
    public function getReservadosData(string $clienteId = '', string $almacen = '', string $antiguedad = ''): array
    {
        try {
            $clienteId  = trim($clienteId);
            $almacen    = trim($almacen);
            $antiguedad = trim($antiguedad);

            $sql = "SELECT 
                        r.id,
                        r.ccvematerial,
                        r.ccvealmacen,
                        IFNULL(ca.cdscalmacen, r.ccvealmacen) AS almacen,
                        IFNULL(r.cantidad, 0) AS cantidad,
                        r.fchregistro,
                        r.iActivo,
                        IFNULL(r.ccveusuario, '') AS ccveusuario,
                        TIMESTAMPDIFF(MINUTE, r.fchregistro, NOW()) AS minutos_transcurridos,
                        TIMESTAMPDIFF(HOUR, r.fchregistro, NOW()) AS horas_transcurridas,
                        DATEDIFF(NOW(), r.fchregistro) AS dias_transcurridos,
                        c.id AS cliente_id,
                        IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'Sin cliente asignado')) AS cliente_nombre,
                        IFNULL(c.razon_social, '') AS cliente_razon_social,
                        p.id AS pedido_id,
                        IFNULL(p.num_orden_compra, 'S/N') AS orden_compra,
                        p.fecha_pedido,
                        IFNULL(m.cDescripcion, '') AS material_descripcion,
                        IFNULL(m.ccveMaterialAlmacen, '') AS ccn,
                        IFNULL(m.ccvematerial, r.ccvematerial) AS clave,
                        IFNULL(m.ccveunidad, 'pza') AS unidad_medida,
                        IFNULL(mar.cdscmarca, '') AS marca,
                        IFNULL(sub.cdscsubmarca, '') AS submarca,
                        IFNULL(cla.cdscclave, '') AS linea_producto,
                        IFNULL(cat.categoria, '') AS categoria,
                        IFNULL(m.modelo, '') AS modelo,
                        IFNULL(m.num_catalogo, '') AS num_catalogo,
                        IFNULL(m.num_parte, '') AS num_parte,
                        IFNULL(m.serie, '') AS serie,
                        IFNULL(m.material, '') AS material,
                        IFNULL(m.grupo, '') AS grupo,
                        IFNULL(m.clave_sat, '') AS clave_sat,
                        ftp.img1, ftp.img2, ftp.img3, ftp.img4, ftp.img5
                    FROM tb_almacen_existencias_reservas r
                    LEFT JOIN cat_almacen ca ON ca.ccvealmacen = r.ccvealmacen
                    LEFT JOIN cat_clientes c ON c.id = r.cliente_id
                    LEFT JOIN tb_pedidos_cliente p ON p.id = r.pedido_cliente_id
                    LEFT JOIN tb_materiales m ON m.ccvematerial = r.ccvematerial
                    LEFT JOIN cat_marcas mar ON mar.icvemarca = m.icvemarca
                    LEFT JOIN cat_submarcas sub ON sub.id = m.submarca_id
                    LEFT JOIN cat_claves cla ON cla.icveclave = m.icveclave
                    LEFT JOIN cat_categorias cat ON cat.id = m.categoria_id
                    LEFT JOIN tb_materiales_ftp ftp ON ftp.idDocto = m.ccvematerial
                    WHERE r.iActivo = 1 ";

            $arrValues = [];

            if ($clienteId !== '') {
                if ($clienteId === '0' || $clienteId === 'sin_cliente') {
                    $sql .= " AND (r.cliente_id = 0 OR r.cliente_id IS NULL) ";
                } else {
                    $sql .= " AND r.cliente_id = :cliente_id ";
                    $arrValues['cliente_id'] = $clienteId;
                }
            }

            if (!empty($almacen)) {
                $sql .= " AND r.ccvealmacen = :almacen ";
                $arrValues['almacen'] = $almacen;
            }

            if (!empty($antiguedad)) {
                if ($antiguedad === 'menos7') {
                    $sql .= " AND DATEDIFF(NOW(), r.fchregistro) < 7 ";
                } else if ($antiguedad === '7a15') {
                    $sql .= " AND DATEDIFF(NOW(), r.fchregistro) >= 7 AND DATEDIFF(NOW(), r.fchregistro) <= 15 ";
                } else if ($antiguedad === '15a30') {
                    $sql .= " AND DATEDIFF(NOW(), r.fchregistro) > 15 AND DATEDIFF(NOW(), r.fchregistro) <= 30 ";
                } else if ($antiguedad === 'mas30') {
                    $sql .= " AND DATEDIFF(NOW(), r.fchregistro) > 30 ";
                }
            }

            $sql .= " ORDER BY r.fchregistro DESC ";

            $arrResponse = $this->select($sql, $arrValues);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene estadísticas/indicadores KPI de productos reservados
     * 
     * @param string $clienteId
     * @param string $almacen
     * @return array
     */
    public function getKpisReservados(string $clienteId = '', string $almacen = ''): array
    {
        try {
            $clienteId = trim($clienteId);
            $almacen   = trim($almacen);

            $sql = "SELECT 
                        COUNT(r.id) AS total_partidas,
                        IFNULL(SUM(r.cantidad), 0) AS total_unidades,
                        COUNT(DISTINCT CASE WHEN r.cliente_id > 0 THEN r.cliente_id ELSE NULL END) AS total_clientes,
                        COUNT(DISTINCT r.ccvematerial) AS total_productos_distintos,
                        SUM(CASE WHEN DATEDIFF(NOW(), r.fchregistro) < 7 THEN 1 ELSE 0 END) AS recientes_7d,
                        SUM(CASE WHEN DATEDIFF(NOW(), r.fchregistro) >= 7 AND DATEDIFF(NOW(), r.fchregistro) <= 15 THEN 1 ELSE 0 END) AS atencion_7_15d,
                        SUM(CASE WHEN DATEDIFF(NOW(), r.fchregistro) > 15 THEN 1 ELSE 0 END) AS criticas_mas15d
                    FROM tb_almacen_existencias_reservas r
                    WHERE r.iActivo = 1 ";

            $arrValues = [];

            if ($clienteId !== '') {
                if ($clienteId === '0' || $clienteId === 'sin_cliente') {
                    $sql .= " AND (r.cliente_id = 0 OR r.cliente_id IS NULL) ";
                } else {
                    $sql .= " AND r.cliente_id = :cliente_id ";
                    $arrValues['cliente_id'] = $clienteId;
                }
            }

            if (!empty($almacen)) {
                $sql .= " AND r.ccvealmacen = :almacen ";
                $arrValues['almacen'] = $almacen;
            }

            $arrResponse = $this->selectModel($sql, $arrValues);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene la lista de clientes que tienen reservas activas
     * 
     * @return array
     */
    public function getClientesConReservas(): array
    {
        try {
            $sql = "SELECT DISTINCT 
                        IFNULL(c.id, 0) AS id, 
                        IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'Sin cliente específico / Reserva general')) AS nombre_cliente,
                        COUNT(r.id) AS total_reservas
                    FROM tb_almacen_existencias_reservas r
                    LEFT JOIN cat_clientes c ON c.id = r.cliente_id
                    WHERE r.iActivo = 1
                    GROUP BY IFNULL(c.id, 0), IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'Sin cliente específico / Reserva general'))
                    ORDER BY (CASE WHEN IFNULL(c.id, 0) = 0 THEN 1 ELSE 0 END) ASC, nombre_cliente ASC";
            $arrResponse = $this->select($sql, []);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    // ==================================================================
    // [ MÓDULO: PASES DE SALIDA ]
    // ==================================================================

    /**
     * Obtiene el listado principal de Pases de Salida con filtros, cálculo de días y semáforo
     * 
     * @param array $filtros
     * @return array
     */
    public function getPasesSalidaData(array $filtros = []): array
    {
        try {
            $sql = "SELECT 
                        p.id,
                        IFNULL(p.folio, CONCAT('PS-', LPAD(p.id, 5, '0'))) AS folio,
                        p.fecha,
                        p.ccvealmacen,
                        IFNULL(ca.cdscalmacen, p.ccvealmacen) AS cdscalmacen,
                        p.venta_id,
                        IFNULL(v.proyecto_id, '') AS proyecto_id,
                        IFNULL(v.titulo, '') AS proyecto_titulo,
                        IFNULL(v.cliente_final, '') AS cliente_final,
                        IFNULL(c.id, 0) AS cliente_id,
                        IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'PÚBLICO GENERAL')) AS nombre_cliente,
                        IFNULL(vd.ccvemedico, '') AS ccveusuario_vendedor,
                        IFNULL(NULLIF(TRIM(CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido)), ''), 'NO ASIGNADO') AS nombre_vendedor,
                        IFNULL(p.calidad_salida, 'VENTA') AS calidad_salida,
                        IFNULL(p.calidad_otros_especifique, '') AS calidad_otros_especifique,
                        IFNULL(p.nombre_recibio_salida, '') AS nombre_recibio_salida,
                        IFNULL(p.ccveusuario_recibe, '') AS ccveusuario_recibe,
                        p.fch_usuario_recibe,
                        p.enviado,
                        IFNULL(p.sinc, 0) AS sinc,
                        IFNULL(p.observaciones, '') AS observaciones,
                        IFNULL(p.ccveusuario, '') AS ccveusuario,
                        p.fchregistro,
                        p.fchregistroactualiza,
                        p.ccveusuariocancela,
                        p.fchregistrocancela,
                        IFNULL(p.motivo_cancela, '') AS motivo_cancela,
                        CASE 
                            WHEN p.fchregistrocancela IS NOT NULL THEN 'CANCELADO'
                            WHEN (p.firma_recibe IS NOT NULL AND TRIM(p.firma_recibe) != '') OR p.enviado = 1 THEN 'ENTREGADO'
                            ELSE 'PENDIENTE'
                        END AS estatus,
                        COALESCE(DATE(p.fch_usuario_recibe), p.fecha) AS fecha_entrega,
                        CASE 
                            WHEN p.fchregistrocancela IS NOT NULL THEN 0
                            WHEN UPPER(TRIM(p.calidad_salida)) != 'VENTA' THEN
                                GREATEST(0, DATEDIFF(CURRENT_DATE, COALESCE(DATE(p.fch_usuario_recibe), p.fecha)))
                            ELSE
                                GREATEST(0, DATEDIFF(COALESCE(DATE(p.fch_usuario_recibe), DATE(p.fchregistroactualiza), CURRENT_DATE), p.fecha))
                        END AS dias_transcurridos,
                        CASE 
                            WHEN UPPER(TRIM(p.calidad_salida)) != 'VENTA' AND p.fchregistrocancela IS NULL THEN 1
                            ELSE 0
                        END AS requiere_devolucion,
                        (SELECT COUNT(*) FROM tb_pases_salida_detalle d WHERE d.pase_salida_id = p.id) AS total_partidas,
                        (SELECT IFNULL(SUM(d.cantidad), 0) FROM tb_pases_salida_detalle d WHERE d.pase_salida_id = p.id) AS total_piezas,
                        (SELECT COUNT(*) FROM tb_pases_salida_adjuntos a WHERE a.pase_salida_id = p.id) AS total_adjuntos,
                        CASE WHEN p.firma_recibe IS NOT NULL AND TRIM(p.firma_recibe) != '' THEN 1 ELSE 0 END AS tiene_firma
                    FROM tb_pases_salida p
                    LEFT JOIN tb_ventas v ON v.id = p.venta_id
                    LEFT JOIN cat_clientes c ON c.id = v.cliente_id
                    LEFT JOIN cat_medico vd ON vd.ccvemedico = v.ccveusuario_vendedor
                    LEFT JOIN cat_almacen ca ON ca.ccvealmacen = p.ccvealmacen
                    WHERE 1 = 1 ";

            $arrValues = [];

            // Filtro por Cliente (Texto / Búsqueda o ID numérico)
            $clienteFiltro = !empty($filtros['cliente']) ? trim($filtros['cliente']) : (!empty($filtros['cliente_id']) ? trim($filtros['cliente_id']) : '');
            if ($clienteFiltro !== '') {
                if (is_numeric($clienteFiltro)) {
                    $sql .= " AND (v.cliente_id = :cliente_id OR c.id = :cliente_id) ";
                    $arrValues['cliente_id'] = intval($clienteFiltro);
                } else {
                    $sql .= " AND (c.nombre_comercial LIKE :cliente_txt OR c.razon_social LIKE :cliente_txt OR v.cliente_final LIKE :cliente_txt) ";
                    $arrValues['cliente_txt'] = '%' . $clienteFiltro . '%';
                }
            }

            // Filtro por Estatus
            if (!empty($filtros['estatus'])) {
                $estatus = strtoupper(trim($filtros['estatus']));
                if ($estatus === 'CANCELADO') {
                    $sql .= " AND p.fchregistrocancela IS NOT NULL ";
                } elseif ($estatus === 'ENTREGADO') {
                    $sql .= " AND p.fchregistrocancela IS NULL AND ((p.firma_recibe IS NOT NULL AND TRIM(p.firma_recibe) != '') OR p.enviado = 1) ";
                } elseif ($estatus === 'PENDIENTE') {
                    $sql .= " AND p.fchregistrocancela IS NULL AND (p.firma_recibe IS NULL OR TRIM(p.firma_recibe) = '') AND p.enviado = 0 ";
                }
            }

            // Filtro por Vendedor
            if (!empty($filtros['vendedor'])) {
                $sql .= " AND v.ccveusuario_vendedor = :vendedor ";
                $arrValues['vendedor'] = $filtros['vendedor'];
            }

            // Filtro por Motivo de Salida
            if (!empty($filtros['motivo_salida'])) {
                $sql .= " AND p.calidad_salida = :motivo_salida ";
                $arrValues['motivo_salida'] = $filtros['motivo_salida'];
            }

            // Filtro por Almacén
            if (!empty($filtros['almacen'])) {
                $sql .= " AND p.ccvealmacen = :almacen ";
                $arrValues['almacen'] = $filtros['almacen'];
            }

            // Filtro por Fechas
            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND p.fecha >= :fecha_inicio ";
                $arrValues['fecha_inicio'] = $filtros['fecha_inicio'];
            }
            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND p.fecha <= :fecha_fin ";
                $arrValues['fecha_fin'] = $filtros['fecha_fin'];
            }

            // Filtro por Búsqueda libre (Folio, Proyecto, Recibió)
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (p.folio LIKE :busqueda OR v.proyecto_id LIKE :busqueda OR v.titulo LIKE :busqueda OR p.nombre_recibio_salida LIKE :busqueda) ";
                $arrValues['busqueda'] = '%' . trim($filtros['busqueda']) . '%';
            }

            $sql .= " ORDER BY p.fecha DESC, p.id DESC";

            $arrResponse = $this->select($sql, $arrValues);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene los datos generales de un Pase de Salida por ID, incluyendo la firma en BASE64
     * 
     * @param int $id
     * @return array
     */
    public function getPaseSalidaById(int $id): array
    {
        try {
            $sql = "SELECT 
                        p.id,
                        IFNULL(p.folio, CONCAT('PS-', LPAD(p.id, 5, '0'))) AS folio,
                        p.fecha,
                        p.ccvealmacen,
                        IFNULL(ca.cdscalmacen, p.ccvealmacen) AS cdscalmacen,
                        p.venta_id,
                        IFNULL(v.proyecto_id, '') AS proyecto_id,
                        IFNULL(v.titulo, '') AS proyecto_titulo,
                        IFNULL(v.cliente_final, '') AS cliente_final,
                        IFNULL(c.id, 0) AS cliente_id,
                        IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'PÚBLICO GENERAL')) AS nombre_cliente,
                        IFNULL(vd.ccvemedico, '') AS ccveusuario_vendedor,
                        IFNULL(NULLIF(TRIM(CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido)), ''), 'NO ASIGNADO') AS nombre_vendedor,
                        IFNULL(p.calidad_salida, 'VENTA') AS calidad_salida,
                        IFNULL(p.calidad_otros_especifique, '') AS calidad_otros_especifique,
                        IFNULL(p.nombre_recibio_salida, '') AS nombre_recibio_salida,
                        IFNULL(p.ccveusuario_recibe, '') AS ccveusuario_recibe,
                        p.fch_usuario_recibe,
                        p.enviado,
                        IFNULL(p.sinc, 0) AS sinc,
                        IFNULL(p.observaciones, '') AS observaciones,
                        IFNULL(p.ccveusuario, '') AS ccveusuario,
                        p.fchregistro,
                        p.fchregistroactualiza,
                        p.ccveusuariocancela,
                        p.fchregistrocancela,
                        IFNULL(p.motivo_cancela, '') AS motivo_cancela,
                        p.firma_recibe,
                        CASE 
                            WHEN p.fchregistrocancela IS NOT NULL THEN 'CANCELADO'
                            WHEN (p.firma_recibe IS NOT NULL AND TRIM(p.firma_recibe) != '') OR p.enviado = 1 THEN 'ENTREGADO'
                            ELSE 'PENDIENTE'
                        END AS estatus,
                        CASE 
                            WHEN (p.firma_recibe IS NOT NULL AND TRIM(p.firma_recibe) != '') OR p.enviado = 1 THEN
                                GREATEST(0, DATEDIFF(COALESCE(DATE(p.fchregistroactualiza), CURRENT_DATE), p.fecha))
                            ELSE
                                GREATEST(0, DATEDIFF(CURRENT_DATE, p.fecha))
                        END AS dias_transcurridos
                    FROM tb_pases_salida p
                    LEFT JOIN tb_ventas v ON v.id = p.venta_id
                    LEFT JOIN cat_clientes c ON c.id = v.cliente_id
                    LEFT JOIN cat_medico vd ON vd.ccvemedico = v.ccveusuario_vendedor
                    LEFT JOIN cat_almacen ca ON ca.ccvealmacen = p.ccvealmacen
                    WHERE p.id = :id";
            $arrResponse = $this->selectModel($sql, [':id' => $id]);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene las partidas o materiales del pase de salida
     * 
     * @param int $pase_id
     * @return array
     */
    public function getPaseSalidaDetalleItems(int $pase_id): array
    {
        try {
            $sql = "SELECT 
                        d.id,
                        d.pase_salida_id,
                        d.partida,
                        d.cantidad,
                        IFNULL(d.ccveunidad, 'Pz') AS ccveunidad,
                        IFNULL(d.ccvematerial, '') AS ccvematerial,
                        IFNULL(d.descripcion, '') AS descripcion,
                        IFNULL(d.observaciones, '') AS observaciones,
                        d.fchregistro,
                        d.estatus,
                        d.fecha_retorno,
                        IFNULL(m.ccveMaterialAlmacen, '') AS ccn,
                        IFNULL(m.iExistenciaActual, 0) AS existencia_actual
                    FROM tb_pases_salida_detalle d
                    LEFT JOIN tb_materiales m ON m.ccvematerial = d.ccvematerial
                    WHERE d.pase_salida_id = :pase_id
                    ORDER BY d.partida ASC, d.id ASC";
            $arrResponse = $this->select($sql, [':pase_id' => $pase_id]);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Obtiene los archivos adjuntos relacionados con el pase de salida
     * 
     * @param int $pase_id
     * @return array
     */
    public function getPaseSalidaAdjuntos(int $pase_id): array
    {
        try {
            $sql = "SELECT 
                        a.id,
                        a.pase_salida_id,
                        a.archivo,
                        IFNULL(a.tipo_archivo, '') AS tipo_archivo,
                        IFNULL(a.duracion_segundos, 0) AS duracion_segundos,
                        a.fchregistro,
                        IFNULL(a.ccveusuario, '') AS ccveusuario
                    FROM tb_pases_salida_adjuntos a
                    WHERE a.pase_salida_id = :pase_id
                    ORDER BY a.id ASC";
            $arrResponse = $this->select($sql, [':pase_id' => $pase_id]);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Guarda la entrega física y firma digital en BASE64 en tb_pases_salida y actualiza partidas
     * 
     * @param int $pase_id
     * @param string $firma_base64
     * @param string $nombre_recibe
     * @param string $usuario_recibe
     * @return bool
     */
    public function saveEntregaFirma(int $pase_id, string $firma_base64, string $nombre_recibe, string $usuario_recibe): bool
    {
        try {
            // Actualizar encabezado de pase de salida
            $sqlPase = "UPDATE tb_pases_salida SET 
                            firma_recibe = :firma_recibe,
                            nombre_recibio_salida = :nombre_recibe,
                            ccveusuario_recibe = :usuario_recibe,
                            fch_usuario_recibe = NOW(),
                            enviado = 1,
                            sinc = 1,
                            fchregistroactualiza = NOW()
                        WHERE id = :pase_id AND fchregistrocancela IS NULL";
            $resPase = $this->update($sqlPase, [
                ':firma_recibe'   => $firma_base64,
                ':nombre_recibe'  => $nombre_recibe,
                ':usuario_recibe' => $usuario_recibe,
                ':pase_id'        => $pase_id
            ]);

            if ($resPase) {
                // Al registrar entrega al receptor con firma digital:
                // Si el motivo es VENTA concluye; si es préstamo/demo/garantía/servicio/consignación, el material queda entregado al cliente pero pendiente de retorno al almacén
                $sqlDet = "UPDATE tb_pases_salida_detalle SET 
                                estatus = CASE WHEN UPPER(TRIM((SELECT calidad_salida FROM tb_pases_salida WHERE id = :pase_id))) = 'VENTA' THEN 1 ELSE 0 END,
                                fecha_retorno = NULL
                           WHERE pase_salida_id = :pase_id";
                $this->update($sqlDet, [':pase_id' => $pase_id]);
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return false;
        }
    }

    /**
     * Calcula KPIs de pases de salida respondiendo a los filtros aplicados
     * 
     * @param array $filtros
     * @return array
     */
    public function getKpisPasesSalida(array $filtros = []): array
    {
        try {
            $pases = $this->getPasesSalidaData($filtros);

            $totalPases           = count($pases);
            $pendientesDevolucion = 0;
            $entregados           = 0;
            $menos15Dias          = 0;
            $de15a30Dias          = 0;
            $mas30Dias            = 0;

            foreach ($pases as $p) {
                if (!empty($p['fchregistrocancela'])) {
                    continue;
                }

                $dias = intval($p['dias_transcurridos']);
                $motivo = strtoupper(trim($p['calidad_salida'] ?? ''));
                $esVenta = ($motivo === 'VENTA');
                $fueEntregado = ($p['estatus'] === 'ENTREGADO');

                if ($fueEntregado) {
                    $entregados++;
                }

                // Si el material requiere devolución al almacén (Préstamo, Demo, Garantía, Servicio, Consignación, etc.)
                if (!$esVenta) {
                    $pendientesDevolucion++;
                    if ($dias < 15) {
                        $menos15Dias++;
                    } elseif ($dias <= 30) {
                        $de15a30Dias++;
                    } else {
                        $mas30Dias++;
                    }
                } else {
                    if ($dias < 15) {
                        $menos15Dias++;
                    } elseif ($dias <= 30) {
                        $de15a30Dias++;
                    } else {
                        $mas30Dias++;
                    }
                }
            }

            return [
                'total_pases'    => $totalPases,
                'pendientes'     => $pendientesDevolucion,
                'entregados'     => $entregados,
                'menos_15_dias'  => $menos15Dias,
                'de_15_a_30_dias'=> $de15a30Dias,
                'mas_30_dias'    => $mas30Dias
            ];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [
                'total_pases'    => 0,
                'pendientes'     => 0,
                'entregados'     => 0,
                'menos_15_dias'  => 0,
                'de_15_a_30_dias'=> 0,
                'mas_30_dias'    => 0
            ];
        }
    }

    /**
     * Reporte agregado por Cliente y Motivo de salida
     * 
     * @param array $filtros
     * @return array
     */
    public function getReportePorCliente(array $filtros = []): array
    {
        try {
            $pases = $this->getPasesSalidaData($filtros);
            $agrupado = [];

            foreach ($pases as $p) {
                if (!empty($p['fchregistrocancela'])) {
                    continue;
                }

                $key = $p['nombre_cliente'] . '___' . $p['calidad_salida'];
                if (!isset($agrupado[$key])) {
                    $agrupado[$key] = [
                        'cliente'         => $p['nombre_cliente'],
                        'motivo_salida'   => $p['calidad_salida'],
                        'total_pases'     => 0,
                        'pendientes'      => 0,
                        'entregados'      => 0,
                        'suma_dias'       => 0,
                        'promedio_dias'   => 0,
                        'pases_mas_30'    => 0
                    ];
                }

                $agrupado[$key]['total_pases']++;
                $esVenta = (strtoupper(trim($p['calidad_salida'])) === 'VENTA');
                if ($p['estatus'] === 'ENTREGADO') {
                    $agrupado[$key]['entregados']++;
                }

                // Material que requiere devolución al almacén o pendiente de entrega
                if (!$esVenta || $p['estatus'] === 'PENDIENTE') {
                    $agrupado[$key]['pendientes']++;
                }

                $dias = intval($p['dias_transcurridos']);
                $agrupado[$key]['suma_dias'] += $dias;
                if ($dias > 30) {
                    $agrupado[$key]['pases_mas_30']++;
                }
            }

            foreach ($agrupado as &$item) {
                $item['promedio_dias'] = $item['total_pases'] > 0 ? round($item['suma_dias'] / $item['total_pases'], 1) : 0;
            }
            unset($item);

            // Ordenar por pendientes DESC, pases_mas_30 DESC
            usort($agrupado, function($a, $b) {
                if ($b['pendientes'] !== $a['pendientes']) {
                    return $b['pendientes'] - $a['pendientes'];
                }
                return $b['pases_mas_30'] - $a['pases_mas_30'];
            });

            return array_values($agrupado);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Reporte agregado por Vendedor y Motivo de salida
     * 
     * @param array $filtros
     * @return array
     */
    public function getReportePorVendedor(array $filtros = []): array
    {
        try {
            $pases = $this->getPasesSalidaData($filtros);
            $agrupado = [];

            foreach ($pases as $p) {
                if (!empty($p['fchregistrocancela'])) {
                    continue;
                }

                $key = $p['nombre_vendedor'] . '___' . $p['calidad_salida'];
                if (!isset($agrupado[$key])) {
                    $agrupado[$key] = [
                        'vendedor'        => $p['nombre_vendedor'],
                        'motivo_salida'   => $p['calidad_salida'],
                        'total_pases'     => 0,
                        'pendientes'      => 0,
                        'entregados'      => 0,
                        'suma_dias'       => 0,
                        'promedio_dias'   => 0,
                        'pases_mas_30'    => 0
                    ];
                }

                $agrupado[$key]['total_pases']++;
                $esVenta = (strtoupper(trim($p['calidad_salida'])) === 'VENTA');
                if ($p['estatus'] === 'ENTREGADO') {
                    $agrupado[$key]['entregados']++;
                }

                // Material que requiere devolución al almacén o pendiente de entrega
                if (!$esVenta || $p['estatus'] === 'PENDIENTE') {
                    $agrupado[$key]['pendientes']++;
                }

                $dias = intval($p['dias_transcurridos']);
                $agrupado[$key]['suma_dias'] += $dias;
                if ($dias > 30) {
                    $agrupado[$key]['pases_mas_30']++;
                }
            }

            foreach ($agrupado as &$item) {
                $item['promedio_dias'] = $item['total_pases'] > 0 ? round($item['suma_dias'] / $item['total_pases'], 1) : 0;
            }
            unset($item);

            // Ordenar por pendientes DESC, pases_mas_30 DESC
            usort($agrupado, function($a, $b) {
                if ($b['pendientes'] !== $a['pendientes']) {
                    return $b['pendientes'] - $a['pendientes'];
                }
                return $b['pases_mas_30'] - $a['pases_mas_30'];
            });

            return array_values($agrupado);
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Genera datos clave para el Resumen y Análisis Ejecutivo
     * A, B, C son en función de la fecha de entrega del producto y los tiempos pendientes por devolver el material al almacén
     * 
     * @param array $filtros
     * @return array
     */
    public function getAnalisisEjecutivo(array $filtros = []): array
    {
        try {
            $pases = $this->getPasesSalidaData($filtros);

            $cliPendientes = [];
            $venPendientes = [];
            $motivosCount  = [];
            $pasesCriticos = [];

            foreach ($pases as $p) {
                // Conteo motivos (Card 4)
                $mot = $p['calidad_salida'];
                $motivosCount[$mot] = ($motivosCount[$mot] ?? 0) + 1;

                if (!empty($p['fchregistrocancela'])) {
                    continue;
                }

                $motivoUpper = strtoupper(trim($p['calidad_salida'] ?? ''));
                $esVenta = ($motivoUpper === 'VENTA');
                // Requiere devolución si el motivo es temporal (no venta definitiva)
                $requiereDevolucion = (!$esVenta && intval($p['requiere_devolucion'] ?? 1) === 1);
                $dias = intval($p['dias_transcurridos']);
                $fEntrega = !empty($p['fecha_entrega']) ? $p['fecha_entrega'] : (!empty($p['fch_usuario_recibe']) ? date('Y-m-d', strtotime($p['fch_usuario_recibe'])) : $p['fecha']);
                $fEntregaFmt = !empty($fEntrega) ? date('d/m/Y', strtotime($fEntrega)) : '';

                // A, B, C: Material entregado pendiente por devolver al almacén
                if ($requiereDevolucion) {
                    // A: Clientes con más material pendiente por devolver
                    $cName = $p['nombre_cliente'];
                    if (!isset($cliPendientes[$cName])) {
                        $cliPendientes[$cName] = [
                            'nombre'   => $cName,
                            'total'    => 0,
                            'max_dias' => 0
                        ];
                    }
                    $cliPendientes[$cName]['total']++;
                    if ($dias > $cliPendientes[$cName]['max_dias']) {
                        $cliPendientes[$cName]['max_dias'] = $dias;
                    }

                    // B: Vendedores con más material pendiente por devolver
                    $vName = $p['nombre_vendedor'];
                    if (!isset($venPendientes[$vName])) {
                        $venPendientes[$vName] = [
                            'nombre'   => $vName,
                            'total'    => 0,
                            'max_dias' => 0
                        ];
                    }
                    $venPendientes[$vName]['total']++;
                    if ($dias > $venPendientes[$vName]['max_dias']) {
                        $venPendientes[$vName]['max_dias'] = $dias;
                    }

                    // C: Pases con mayor antigüedad pendientes por devolver al almacén
                    $pasesCriticos[] = [
                        'id'                       => $p['id'],
                        'folio'                    => $p['folio'],
                        'cliente'                  => $p['nombre_cliente'],
                        'vendedor'                 => $p['nombre_vendedor'],
                        'motivo'                   => $p['calidad_salida'],
                        'fecha'                    => $p['fecha'],
                        'fecha_entrega'            => $fEntrega,
                        'fecha_entrega_formateada' => $fEntregaFmt,
                        'dias'                     => $dias,
                        'semaforo'                 => ($dias < 15 ? 'VERDE' : ($dias <= 30 ? 'AMARILLO' : 'ROJO'))
                    ];
                }
            }

            // Ordenar A (Clientes): primero por mayor total de pendientes, luego por mayor días
            $topClientesList = array_values($cliPendientes);
            usort($topClientesList, function($a, $b) {
                if ($b['total'] !== $a['total']) {
                    return $b['total'] - $a['total'];
                }
                return $b['max_dias'] - $a['max_dias'];
            });
            $topClientes = array_slice($topClientesList, 0, 5);

            // Ordenar B (Vendedores): primero por mayor total de pendientes, luego por mayor días
            $topVendedoresList = array_values($venPendientes);
            usort($topVendedoresList, function($a, $b) {
                if ($b['total'] !== $a['total']) {
                    return $b['total'] - $a['total'];
                }
                return $b['max_dias'] - $a['max_dias'];
            });
            $topVendedores = array_slice($topVendedoresList, 0, 5);

            // Ordenar C (Pases críticos / Mayor antigüedad por devolver): días DESC
            usort($pasesCriticos, function($a, $b) {
                return $b['dias'] - $a['dias'];
            });
            $topPasesCriticos = array_slice($pasesCriticos, 0, 5);

            // Ordenar motivos frecuentes (Card 4)
            arsort($motivosCount);
            $topMotivos = [];
            $i = 0;
            foreach ($motivosCount as $mot => $cnt) {
                if ($i++ >= 5) break;
                $topMotivos[] = ['motivo' => $mot, 'total' => $cnt];
            }

            return [
                'top_clientes'    => $topClientes,
                'top_vendedores'  => $topVendedores,
                'pases_criticos'  => $topPasesCriticos,
                'top_motivos'     => $topMotivos,
                'total_urgentes'  => count(array_filter($pasesCriticos, function($it) { return $it['dias'] > 30; }))
            ];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [
                'top_clientes'    => [],
                'top_vendedores'  => [],
                'pases_criticos'  => [],
                'top_motivos'     => [],
                'total_urgentes'  => 0
            ];
        }
    }

    /**
     * Clientes disponibles para el selector de filtros
     * 
     * @return array
     */
    public function getClientesPases(): array
    {
        try {
            $sql = "SELECT DISTINCT 
                        c.id, 
                        IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'Sin Nombre')) AS nombre_cliente
                    FROM cat_clientes c
                    WHERE c.iActivo = 1
                    ORDER BY nombre_cliente ASC";
            $arrResponse = $this->select($sql, []);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Vendedores disponibles para el selector de filtros
     * 
     * @return array
     */
    public function getVendedoresPases(): array
    {
        try {
            $sql = "SELECT DISTINCT 
                        vd.ccvemedico,
                        TRIM(CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido)) AS nombre_vendedor
                    FROM cat_medico vd
                    WHERE vd.iActivo = 1 AND TRIM(CONCAT_WS(' ', vd.cnombre, vd.cpriapellido, vd.csegapellido)) != ''
                    ORDER BY nombre_vendedor ASC";
            $arrResponse = $this->select($sql, []);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }

    /**
     * Motivos de salida disponibles para el filtro
     * 
     * @return array
     */
    public function getMotivosPases(): array
    {
        return [
            'VENTA',
            'PRÉSTAMO',
            'DEMOSTRACIÓN',
            'SERVICIO / MANTENIMIENTO',
            'GARANTÍA',
            'CONSIGNACIÓN',
            'TRASPASO',
            'MERMA / DEVOLUCIÓN',
            'OTRO'
        ];
    }

    /**
     * Obtiene la lista de usuarios activos para selección en firmas y entregas de pases
     * 
     * @return array
     */
    public function getUsuariosSistema(): array
    {
        try {
            $sql = "SELECT 
                        u.id, 
                        u.usuario, 
                        u.ccveusuario,
                        COALESCE(NULLIF(TRIM(CONCAT_WS(' ', dg.nombre, dg.paterno, dg.materno)), ''), u.usuario) as nombre_completo
                    FROM ssf_usuarios u
                    LEFT JOIN ssf_usuarios_datos_generales dg ON dg.usuario_id = u.id
                    WHERE u.activo = 1
                    ORDER BY nombre_completo ASC";
            $arrResponse = $this->select($sql, []);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            getLoggerSystem()->error(getMensajeError($th, "AlmacenModel"));
            return [];
        }
    }
}



