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
}
