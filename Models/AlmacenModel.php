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
                        IFNULL(NULLIF(TRIM(CONCAT_WS(' ', u_reg.cnombre, u_reg.cpriapellido, u_reg.csegapellido)), ''), p.ccveusuario) AS nombre_usuario_registro,
                        IFNULL(NULLIF(TRIM(CONCAT_WS(' ', u_rec.cnombre, u_rec.cpriapellido, u_rec.csegapellido)), ''), p.nombre_recibio_salida) AS nombre_usuario_recibe,
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
                    LEFT JOIN cat_medico u_reg ON u_reg.ccvemedico = p.ccveusuario
                    LEFT JOIN cat_medico u_rec ON u_rec.ccvemedico = p.ccveusuario_recibe
                    WHERE p.id = :id";
            $arrResponse = $this->selectModel($sql, [':id' => $id]);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            if (function_exists('getLoggerSystem')) {
                $logger = getLoggerSystem();
                if ($logger && is_object($logger)) {
                    $logger->error(getMensajeError($th, "AlmacenModel"));
                }
            }
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
                $sqlDet = "UPDATE tb_pases_salida_detalle d
                           JOIN tb_pases_salida p ON p.id = d.pase_salida_id
                           SET d.estatus = CASE WHEN UPPER(TRIM(p.calidad_salida)) = 'VENTA' THEN 1 ELSE 0 END,
                               d.fecha_retorno = NULL
                           WHERE d.pase_salida_id = :pase_id";
                $this->update($sqlDet, [':pase_id' => $pase_id]);
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            if (function_exists('getLoggerSystem')) {
                $logger = getLoggerSystem();
                if ($logger && is_object($logger)) {
                    $logger->error(getMensajeError($th, "AlmacenModel"));
                }
            }
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
                        m.icvemedico AS id,
                        m.ccvemedico,
                        m.ccvemedico AS ccveusuario,
                        COALESCE(NULLIF(TRIM(m.user), ''), NULLIF(TRIM(u.usuario), ''), m.ccvemedico) AS usuario,
                        COALESCE(NULLIF(TRIM(m.email), ''), u.usuario, '') AS email,
                        TRIM(CONCAT_WS(' ', m.cnombre, m.cpriapellido, m.csegapellido)) AS nombre_completo,
                        m.cdsctipousuario,
                        m.cdscareaafectada,
                        m.cCargo
                    FROM cat_medico m
                    LEFT JOIN ssf_usuarios u ON (u.usuario_local = m.user OR u.usuario = m.email)
                    WHERE m.iActivo = 1
                    ORDER BY nombre_completo ASC";
            $arrResponse = $this->select($sql, []);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            if (function_exists('getLoggerSystem')) {
                $logger = getLoggerSystem();
                if ($logger && is_object($logger)) {
                    $logger->error(getMensajeError($th, "AlmacenModel"));
                }
            }
            return [];
        }
    }

    // ==================================================================
    // [ MÓDULO: REPORTE DE NOTAS DE SALIDA ]
    // ==================================================================

    /**
     * Obtiene el listado de Notas de Salida que afectan inventarios (tb_notasalida)
     * con cálculo de estatus (contabilizada, en proceso, cancelada), datos de firma y partidas.
     * 
     * @param array $filtros
     * @return array
     */
    public function getNotasSalidaData(array $filtros = []): array
    {
        try {
            $sql = "SELECT 
                        n.icvenotasalida AS id,
                        n.cNumNota AS folio,
                        n.fchNota AS fecha,
                        n.fchregistro,
                        n.cTipoNota AS tipo_nota,
                        IFNULL(n.cNumDocumentoSalida, '') AS num_documento_salida,
                        n.ccvealmacen,
                        IFNULL(alm_orig.cdscalmacen, n.ccvealmacen) AS almacen_origen,
                        n.ccvealmacenDestino,
                        IFNULL(alm_dest.cdscalmacen, n.ccvealmacenDestino) AS almacen_destino,
                        IFNULL(n.cdscareaafectada_Destino, '') AS area_afectada,
                        IFNULL(n.cliente_id, 0) AS cliente_id,
                        IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'PÚBLICO GENERAL')) AS nombre_cliente,
                        IFNULL(n.venta_id, 0) AS venta_id,
                        IFNULL(v.proyecto_id, '') AS proyecto_id,
                        IFNULL(v.titulo, '') AS proyecto_titulo,
                        IFNULL(v.cliente_final, '') AS cliente_final,
                        IFNULL(n.pedido_cliente_id, 0) AS pedido_cliente_id,
                        IFNULL(n.icvetipodoctoalmacen, 0) AS icvetipodoctoalmacen,
                        IFNULL(tda.cdsctipodoctoalmacen, 'ORDEN DE COMPRA DE CLIENTE') AS tipo_docto_almacen,
                        IFNULL(n.icvetipomovimiento, 0) AS icvetipomovimiento,
                        IFNULL(tm.cdsctipomovimiento, 'SALIDA') AS tipo_movimiento,
                        IFNULL(n.iContabilizada, 0) AS iContabilizada,
                        n.fchContabiliza,
                        IFNULL(n.ccveusuariocontabiliza, '') AS ccveusuariocontabiliza,
                        IFNULL(n.iCancelada, 0) AS iCancelada,
                        n.fchregistrocancela,
                        IFNULL(n.ccveusuariocancela, '') AS ccveusuariocancela,
                        IFNULL(n.cMotivoCancela, '') AS motivo_cancela,
                        CASE 
                            WHEN (n.iCancelada = 1 OR n.fchregistrocancela IS NOT NULL) THEN 'CANCELADA'
                            WHEN (n.iContabilizada = 1 AND (n.iCancelada = 0 OR n.iCancelada IS NULL)) THEN 'CONTABILIZADA'
                            WHEN ((n.iContabilizada = 0 OR n.iContabilizada IS NULL) AND (n.iCancelada = 0 OR n.iCancelada IS NULL)) THEN 'EN PROCESO'
                            ELSE 'EN PROCESO'
                        END AS estatus,
                        IFNULL(n.iFirmaRecibe, 0) AS iFirmaRecibe,
                        CASE 
                            WHEN (n.firma_recibe IS NOT NULL AND TRIM(n.firma_recibe) != '') OR n.iFirmaRecibe = 1 THEN 1 
                            ELSE 0 
                        END AS tiene_firma,
                        n.fch_usuario_recibe,
                        COALESCE(NULLIF(TRIM(n.cRecibeNotaExterna), ''), NULLIF(TRIM(n.cNombreRecibe), ''), '') AS persona_recibe,
                        IFNULL(n.ccveusuarioRecibe, '') AS ccveusuarioRecibe,
                        IFNULL(TRIM(CONCAT_WS(' ', u_rec.cnombre, u_rec.cpriapellido, u_rec.csegapellido)), '') AS usuario_recibe_nombre,
                        IFNULL(n.cNombreSolicita, '') AS nombre_solicita,
                        IFNULL(n.ccveusuarioEntrega, '') AS ccveusuarioEntrega,
                        COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u_ent.cnombre, u_ent.cpriapellido, u_ent.csegapellido)), ''), NULLIF(TRIM(n.cEntregaNotaExterna), ''), '') AS usuario_entrega_nombre,
                        IFNULL(n.cObservaciones, '') AS observaciones,
                        GREATEST(0, DATEDIFF(CURRENT_DATE, n.fchNota)) AS dias_transcurridos,
                        (SELECT COUNT(*) FROM tb_notasalida_detalle d WHERE d.cNumNotaSalida = n.cNumNota) AS total_partidas,
                        (SELECT IFNULL(SUM(d.iCantidad), 0) FROM tb_notasalida_detalle d WHERE d.cNumNotaSalida = n.cNumNota) AS total_piezas,
                        (SELECT IFNULL(SUM(d.iImporte), 0) FROM tb_notasalida_detalle d WHERE d.cNumNotaSalida = n.cNumNota) AS total_importe,
                        (SELECT COUNT(*) FROM tb_notasalida_adjuntos a WHERE a.cNumNota = n.cNumNota OR a.nota_salida_id = n.icvenotasalida) AS total_adjuntos
                    FROM tb_notasalida n
                    LEFT JOIN cat_almacen alm_orig ON alm_orig.ccvealmacen = n.ccvealmacen
                    LEFT JOIN cat_almacen alm_dest ON alm_dest.ccvealmacen = n.ccvealmacenDestino
                    LEFT JOIN cat_clientes c ON c.id = n.cliente_id
                    LEFT JOIN tb_ventas v ON v.id = n.venta_id
                    LEFT JOIN cat_tipodocto_almacen tda ON tda.icvetipodoctoalmacen = n.icvetipodoctoalmacen
                    LEFT JOIN cat_tipomovimiento tm ON tm.icvetipomovimiento = n.icvetipomovimiento
                    LEFT JOIN cat_medico u_rec ON u_rec.ccvemedico = n.ccveusuarioRecibe
                    LEFT JOIN cat_medico u_ent ON u_ent.ccvemedico = n.ccveusuarioEntrega
                    WHERE 1 = 1 ";

            $arrValues = [];

            // Filtro por Estatus
            if (!empty($filtros['estatus'])) {
                $est = strtoupper(trim($filtros['estatus']));
                if ($est === 'CONTABILIZADA') {
                    $sql .= " AND (n.iContabilizada = 1 AND (n.iCancelada = 0 OR n.iCancelada IS NULL) AND n.fchregistrocancela IS NULL) ";
                } elseif ($est === 'EN PROCESO' || $est === 'EN_PROCESO') {
                    $sql .= " AND ((n.iContabilizada = 0 OR n.iContabilizada IS NULL) AND (n.iCancelada = 0 OR n.iCancelada IS NULL) AND n.fchregistrocancela IS NULL) ";
                } elseif ($est === 'CANCELADA') {
                    $sql .= " AND (n.iCancelada = 1 OR n.fchregistrocancela IS NOT NULL) ";
                }
            }

            // Filtro por Estatus de Firma
            if (!empty($filtros['estatus_firma'])) {
                $estFirma = strtoupper(trim($filtros['estatus_firma']));
                if ($estFirma === 'CON_FIRMA' || $estFirma === 'FIRMADA') {
                    $sql .= " AND ((n.firma_recibe IS NOT NULL AND TRIM(n.firma_recibe) != '') OR n.iFirmaRecibe = 1) ";
                } elseif ($estFirma === 'SIN_FIRMA' || $estFirma === 'PENDIENTE') {
                    $sql .= " AND (n.firma_recibe IS NULL OR TRIM(n.firma_recibe) = '') AND (n.iFirmaRecibe = 0 OR n.iFirmaRecibe IS NULL) ";
                }
            }

            // Filtro por Almacén Origen
            if (!empty($filtros['almacen'])) {
                $sql .= " AND n.ccvealmacen = :almacen ";
                $arrValues[':almacen'] = trim($filtros['almacen']);
            }

            // Filtro por Almacén Destino
            if (!empty($filtros['almacen_destino'])) {
                $sql .= " AND n.ccvealmacenDestino = :almacen_destino ";
                $arrValues[':almacen_destino'] = trim($filtros['almacen_destino']);
            }

            // Filtro por Cliente
            $cliFiltro = !empty($filtros['cliente']) ? trim($filtros['cliente']) : (!empty($filtros['cliente_id']) ? trim($filtros['cliente_id']) : '');
            if ($cliFiltro !== '') {
                if (is_numeric($cliFiltro)) {
                    $sql .= " AND (n.cliente_id = :cliente_id OR v.cliente_id = :cliente_id) ";
                    $arrValues[':cliente_id'] = intval($cliFiltro);
                } else {
                    $sql .= " AND (c.nombre_comercial LIKE :cliente_txt OR c.razon_social LIKE :cliente_txt OR n.cdscareaafectada_Destino LIKE :cliente_txt) ";
                    $arrValues[':cliente_txt'] = '%' . $cliFiltro . '%';
                }
            }

            // Filtro por Tipo de Documento
            if (!empty($filtros['tipo_docto'])) {
                $sql .= " AND n.icvetipodoctoalmacen = :tipo_docto ";
                $arrValues[':tipo_docto'] = intval($filtros['tipo_docto']);
            }

            // Filtro por Fechas
            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND n.fchNota >= :fecha_inicio ";
                $arrValues[':fecha_inicio'] = $filtros['fecha_inicio'];
            }
            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND n.fchNota <= :fecha_fin ";
                $arrValues[':fecha_fin'] = $filtros['fecha_fin'];
            }

            // Filtro por Búsqueda General (folio, PO, persona, solicitante)
            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (n.cNumNota LIKE :busqueda 
                               OR n.cNumDocumentoSalida LIKE :busqueda 
                               OR n.cNombreSolicita LIKE :busqueda 
                               OR n.cRecibeNotaExterna LIKE :busqueda 
                               OR n.cNombreRecibe LIKE :busqueda
                               OR v.proyecto_id LIKE :busqueda
                               OR v.titulo LIKE :busqueda) ";
                $arrValues[':busqueda'] = '%' . trim($filtros['busqueda']) . '%';
            }

            $sql .= " ORDER BY n.fchNota DESC, n.icvenotasalida DESC";

            $arrResponse = $this->select($sql, $arrValues);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            if (function_exists('getLoggerSystem')) {
                $logger = getLoggerSystem();
                if ($logger && is_object($logger)) {
                    $logger->error(getMensajeError($th, "AlmacenModel::getNotasSalidaData"));
                }
            }
            return [];
        }
    }

    /**
     * Calcula KPIs resumidos de Notas de Salida respondiendo a los filtros aplicados
     * 
     * @param array $filtros
     * @return array
     */
    public function getKpisNotasSalida(array $filtros = []): array
    {
        try {
            $notas = $this->getNotasSalidaData($filtros);

            $totalNotas       = count($notas);
            $contabilizadas   = 0;
            $enProceso        = 0;
            $canceladas       = 0;
            $conFirma         = 0;
            $pendientesFirma  = 0;
            $totalPiezas      = 0;
            $totalImporte     = 0.0;

            foreach ($notas as $n) {
                $est = $n['estatus'];
                if ($est === 'CONTABILIZADA') {
                    $contabilizadas++;
                } elseif ($est === 'EN PROCESO') {
                    $enProceso++;
                } elseif ($est === 'CANCELADA') {
                    $canceladas++;
                }

                if ($n['tiene_firma'] == 1) {
                    $conFirma++;
                } else {
                    if ($est !== 'CANCELADA') {
                        $pendientesFirma++;
                    }
                }

                if ($est !== 'CANCELADA') {
                    $totalPiezas += floatval($n['total_piezas']);
                    $totalImporte += floatval($n['total_importe']);
                }
            }

            return [
                'total_notas'      => $totalNotas,
                'contabilizadas'   => $contabilizadas,
                'en_proceso'       => $enProceso,
                'canceladas'       => $canceladas,
                'con_firma'        => $conFirma,
                'pendientes_firma' => $pendientesFirma,
                'total_piezas'     => $totalPiezas,
                'total_importe'    => $totalImporte
            ];
        } catch (\Throwable $th) {
            if (function_exists('getLoggerSystem')) {
                $logger = getLoggerSystem();
                if ($logger && is_object($logger)) {
                    $logger->error(getMensajeError($th, "AlmacenModel::getKpisNotasSalida"));
                }
            }
            return [
                'total_notas'      => 0,
                'contabilizadas'   => 0,
                'en_proceso'       => 0,
                'canceladas'       => 0,
                'con_firma'        => 0,
                'pendientes_firma' => 0,
                'total_piezas'     => 0,
                'total_importe'    => 0
            ];
        }
    }

    /**
     * Genera datos de análisis ejecutivo para el Jefe de Almacén
     * 
     * @param array $filtros
     * @return array
     */
    public function getAnalisisNotasSalida(array $filtros = []): array
    {
        try {
            $notas = $this->getNotasSalidaData($filtros);

            $clientesCount = [];
            $almacenesCount = [];
            $notasAtencion = [];

            foreach ($notas as $n) {
                if ($n['estatus'] === 'CANCELADA') continue;

                // Conteo por cliente
                $cli = !empty($n['nombre_cliente']) ? $n['nombre_cliente'] : 'PÚBLICO GENERAL';
                if (!isset($clientesCount[$cli])) {
                    $clientesCount[$cli] = ['nombre' => $cli, 'notas' => 0, 'piezas' => 0];
                }
                $clientesCount[$cli]['notas']++;
                $clientesCount[$cli]['piezas'] += floatval($n['total_piezas']);

                // Conteo por almacén
                $alm = !empty($n['almacen_origen']) ? $n['almacen_origen'] : $n['ccvealmacen'];
                if (!isset($almacenesCount[$alm])) {
                    $almacenesCount[$alm] = ['nombre' => $alm, 'notas' => 0, 'piezas' => 0];
                }
                $almacenesCount[$alm]['notas']++;
                $almacenesCount[$alm]['piezas'] += floatval($n['total_piezas']);

                // Alertas de atención: Sin firma o en proceso con más de 3 días
                $dias = intval($n['dias_transcurridos']);
                if ($n['tiene_firma'] == 0 || $n['estatus'] === 'EN PROCESO') {
                    $notasAtencion[] = [
                        'id'           => $n['id'],
                        'folio'        => $n['folio'],
                        'fecha'        => $n['fecha'],
                        'cliente'      => $cli,
                        'estatus'      => $n['estatus'],
                        'tiene_firma'  => $n['tiene_firma'],
                        'dias'         => $dias,
                        'piezas'       => $n['total_piezas']
                    ];
                }
            }

            // Ordenar clientes por notas desc
            usort($clientesCount, fn($a, $b) => $b['notas'] <=> $a['notas']);
            // Ordenar almacenes por notas desc
            usort($almacenesCount, fn($a, $b) => $b['notas'] <=> $a['notas']);
            // Ordenar atención por días desc
            usort($notasAtencion, fn($a, $b) => $b['dias'] <=> $a['dias']);

            return [
                'top_clientes'      => array_slice($clientesCount, 0, 5),
                'top_almacenes'     => array_slice($almacenesCount, 0, 5),
                'alertas_atencion'  => array_slice($notasAtencion, 0, 8),
                'total_atencion'    => count($notasAtencion)
            ];
        } catch (\Throwable $th) {
            return [
                'top_clientes'      => [],
                'top_almacenes'     => [],
                'alertas_atencion'  => [],
                'total_atencion'    => 0
            ];
        }
    }

    /**
     * Obtiene el encabezado y datos completos de una Nota de Salida por ID
     * 
     * @param int $id
     * @return array
     */
    public function getNotaSalidaById(int $id): array
    {
        try {
            $sql = "SELECT 
                        n.icvenotasalida AS id,
                        n.cNumNota AS folio,
                        n.fchNota AS fecha,
                        n.fchregistro,
                        n.cTipoNota AS tipo_nota,
                        IFNULL(n.cNumDocumentoSalida, '') AS num_documento_salida,
                        n.ccvealmacen,
                        IFNULL(alm_orig.cdscalmacen, n.ccvealmacen) AS almacen_origen,
                        n.ccvealmacenDestino,
                        IFNULL(alm_dest.cdscalmacen, n.ccvealmacenDestino) AS almacen_destino,
                        IFNULL(n.cdscareaafectada_Destino, '') AS area_afectada,
                        IFNULL(n.cliente_id, 0) AS cliente_id,
                        IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'PÚBLICO GENERAL')) AS nombre_cliente,
                        IFNULL(n.venta_id, 0) AS venta_id,
                        IFNULL(v.proyecto_id, '') AS proyecto_id,
                        IFNULL(v.titulo, '') AS proyecto_titulo,
                        IFNULL(v.cliente_final, '') AS cliente_final,
                        IFNULL(n.pedido_cliente_id, 0) AS pedido_cliente_id,
                        IFNULL(n.icvetipodoctoalmacen, 0) AS icvetipodoctoalmacen,
                        IFNULL(tda.cdsctipodoctoalmacen, 'ORDEN DE COMPRA DE CLIENTE') AS tipo_docto_almacen,
                        IFNULL(n.icvetipomovimiento, 0) AS icvetipomovimiento,
                        IFNULL(tm.cdsctipomovimiento, 'SALIDA') AS tipo_movimiento,
                        IFNULL(n.iContabilizada, 0) AS iContabilizada,
                        n.fchContabiliza,
                        IFNULL(n.ccveusuariocontabiliza, '') AS ccveusuariocontabiliza,
                        IFNULL(n.iCancelada, 0) AS iCancelada,
                        n.fchregistrocancela,
                        IFNULL(n.ccveusuariocancela, '') AS ccveusuariocancela,
                        IFNULL(n.cMotivoCancela, '') AS motivo_cancela,
                        CASE 
                            WHEN (n.iCancelada = 1 OR n.fchregistrocancela IS NOT NULL) THEN 'CANCELADA'
                            WHEN (n.iContabilizada = 1 AND (n.iCancelada = 0 OR n.iCancelada IS NULL)) THEN 'CONTABILIZADA'
                            WHEN ((n.iContabilizada = 0 OR n.iContabilizada IS NULL) AND (n.iCancelada = 0 OR n.iCancelada IS NULL)) THEN 'EN PROCESO'
                            ELSE 'EN PROCESO'
                        END AS estatus,
                        IFNULL(n.iFirmaRecibe, 0) AS iFirmaRecibe,
                        IFNULL(n.firma_recibe, '') AS firma_recibe,
                        CASE 
                            WHEN (n.firma_recibe IS NOT NULL AND TRIM(n.firma_recibe) != '') OR n.iFirmaRecibe = 1 THEN 1 
                            ELSE 0 
                        END AS tiene_firma,
                        n.fch_usuario_recibe,
                        COALESCE(NULLIF(TRIM(n.cRecibeNotaExterna), ''), NULLIF(TRIM(n.cNombreRecibe), ''), '') AS persona_recibe,
                        IFNULL(n.ccveusuarioRecibe, '') AS ccveusuarioRecibe,
                        IFNULL(TRIM(CONCAT_WS(' ', u_rec.cnombre, u_rec.cpriapellido, u_rec.csegapellido)), '') AS usuario_recibe_nombre,
                        IFNULL(n.cNombreSolicita, '') AS nombre_solicita,
                        IFNULL(n.ccveusuarioEntrega, '') AS ccveusuarioEntrega,
                        COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u_ent.cnombre, u_ent.cpriapellido, u_ent.csegapellido)), ''), NULLIF(TRIM(n.cEntregaNotaExterna), ''), '') AS usuario_entrega_nombre,
                        IFNULL(n.cObservaciones, '') AS observaciones,
                        IFNULL(n.iSubtotal, 0) AS subtotal,
                        IFNULL(n.iIVA, 0) AS iva,
                        IFNULL(n.iTotal, 0) AS total,
                        GREATEST(0, DATEDIFF(CURRENT_DATE, n.fchNota)) AS dias_transcurridos
                    FROM tb_notasalida n
                    LEFT JOIN cat_almacen alm_orig ON alm_orig.ccvealmacen = n.ccvealmacen
                    LEFT JOIN cat_almacen alm_dest ON alm_dest.ccvealmacen = n.ccvealmacenDestino
                    LEFT JOIN cat_clientes c ON c.id = n.cliente_id
                    LEFT JOIN tb_ventas v ON v.id = n.venta_id
                    LEFT JOIN cat_tipodocto_almacen tda ON tda.icvetipodoctoalmacen = n.icvetipodoctoalmacen
                    LEFT JOIN cat_tipomovimiento tm ON tm.icvetipomovimiento = n.icvetipomovimiento
                    LEFT JOIN cat_medico u_rec ON u_rec.ccvemedico = n.ccveusuarioRecibe
                    LEFT JOIN cat_medico u_ent ON u_ent.ccvemedico = n.ccveusuarioEntrega
                    WHERE n.icvenotasalida = :id";
            $arrResponse = $this->selectModel($sql, [':id' => $id]);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            if (function_exists('getLoggerSystem')) {
                $logger = getLoggerSystem();
                if ($logger && is_object($logger)) {
                    $logger->error(getMensajeError($th, "AlmacenModel::getNotaSalidaById"));
                }
            }
            return [];
        }
    }

    /**
     * Obtiene las partidas detalladas de una Nota de Salida desde tb_notasalida_detalle
     * 
     * @param string $cNumNota
     * @return array
     */
    public function getNotaSalidaDetalleItems(string $cNumNota): array
    {
        try {
            $sql = "SELECT 
                        d.icvenotasalidadetalle AS id,
                        d.cNumNotaSalida,
                        IFNULL(d.ccvematerial, '') AS clave,
                        IFNULL(d.ccveMaterialAlmacen, '') AS ccn,
                        IFNULL(d.cDescripcion, '') AS descripcion,
                        IFNULL(d.iCantidad, 0) AS cantidad,
                        IFNULL(d.ccveunidad, 'pza') AS unidad,
                        IFNULL(d.iCostoUnitario, 0) AS costo_unitario,
                        IFNULL(d.iImporte, 0) AS importe,
                        IFNULL(d.cNumLote, '') AS lote,
                        IFNULL(d.cNumSerie, '') AS serie,
                        IFNULL(d.cCodigoBarras, '') AS codigo_barras,
                        IFNULL(d.iContabilizado, 0) AS contabilizado,
                        IFNULL(d.iPrecioVenta, 0) AS precio_venta,
                        IFNULL(m.iExistenciaActual, 0) AS existencia_actual
                    FROM tb_notasalida_detalle d
                    LEFT JOIN tb_materiales m ON m.ccvematerial = d.ccvematerial
                    WHERE d.cNumNotaSalida = :num_nota
                    ORDER BY d.icvenotasalidadetalle ASC";
            $arrResponse = $this->select($sql, [':num_nota' => $cNumNota]);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            if (function_exists('getLoggerSystem')) {
                $logger = getLoggerSystem();
                if ($logger && is_object($logger)) {
                    $logger->error(getMensajeError($th, "AlmacenModel::getNotaSalidaDetalleItems"));
                }
            }
            return [];
        }
    }

    /**
     * Guarda la firma digital y los datos de recepción en tb_notasalida
     * 
     * @param int $notaId
     * @param string $firmaBase64
     * @param string $nombreRecibe
     * @param string $usuarioRecibe
     * @return bool
     */
    public function saveFirmaNotaSalida(int $notaId, string $firmaBase64, string $nombreRecibe, string $usuarioRecibe): bool
    {
        try {
            $sql = "UPDATE tb_notasalida SET 
                        firma_recibe = :firma_recibe,
                        sinc = 1,
                        iFirmaRecibe = 1,
                        fch_usuario_recibe = NOW(),
                        cRecibeNotaExterna = :nombre_recibe,
                        cNombreRecibe = :nombre_recibe_ext,
                        ccveusuarioRecibe = :usuario_recibe
                    WHERE icvenotasalida = :id AND (iCancelada = 0 OR iCancelada IS NULL)";
            return $this->update($sql, [
                ':firma_recibe'       => $firmaBase64,
                ':nombre_recibe'      => $nombreRecibe,
                ':nombre_recibe_ext'  => $nombreRecibe,
                ':usuario_recibe'     => $usuarioRecibe,
                ':id'                 => $notaId
            ]);
        } catch (\Throwable $th) {
            if (function_exists('getLoggerSystem')) {
                $logger = getLoggerSystem();
                if ($logger && is_object($logger)) {
                    $logger->error(getMensajeError($th, "AlmacenModel::saveFirmaNotaSalida"));
                }
            }
            return false;
        }
    }

    /**
     * Obtiene los clientes con notas de salida registradas
     * 
     * @return array
     */
    public function getClientesNotasSalida(): array
    {
        try {
            $sql = "SELECT DISTINCT 
                        IFNULL(c.id, 0) AS id, 
                        IFNULL(c.nombre_comercial, IFNULL(c.razon_social, 'PÚBLICO GENERAL')) AS nombre_cliente
                    FROM tb_notasalida n
                    LEFT JOIN cat_clientes c ON c.id = n.cliente_id
                    WHERE c.id IS NOT NULL AND c.id > 0
                    ORDER BY nombre_cliente ASC";
            $arrResponse = $this->select($sql, []);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            return [];
        }
    }

    /**
     * Obtiene el catálogo de tipos de documento de almacén activos
     * 
     * @return array
     */
    public function getTiposDoctoAlmacen(): array
    {
        try {
            $sql = "SELECT icvetipodoctoalmacen AS id, cdsctipodoctoalmacen AS nombre 
                    FROM cat_tipodocto_almacen 
                    WHERE iActivo = 1 
                    ORDER BY cdsctipodoctoalmacen ASC";
            $arrResponse = $this->select($sql, []);
            return is_array($arrResponse) ? $arrResponse : [];
        } catch (\Throwable $th) {
            return [];
        }
    }
}



