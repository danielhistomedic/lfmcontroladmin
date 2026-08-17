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
}


