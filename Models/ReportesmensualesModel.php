<?php

/** Reporte mensual: reglas de MOSTRAR_ESTATUS_PEDIDOS en frmRegistrarProyecto. */
class ReportesmensualesModel extends Mysql
{
    private const FILTRO_PROYECTOS = 'v.clasificacion_proyecto_id IN (2,3,4,5) AND (v.estatus_proyecto_id IS NULL OR v.estatus_proyecto_id <> 2)';

    /** Rangos separados para cada combinacion de anio y mes seleccionados. */
    private static function periodo(string $column, int|array $years, int|array $months, array &$params): string
    {
        $months = array_values(array_unique(is_array($months) ? $months : [$months]));
        $years = array_values(array_unique(is_array($years) ? $years : [$years]));
        if (!$years || count($years) > 101 || !$months || count($months) > 12) {
            throw new InvalidArgumentException('Periodo no valido.');
        }
        sort($months, SORT_NUMERIC);
        sort($years, SORT_NUMERIC);
        $ranges = [];
        $params = [];
        foreach ($years as $year) {
            if (!is_int($year) || $year < 2000 || $year > 2100) throw new InvalidArgumentException('Anio no valido.');
            foreach ($months as $month) {
                if (!is_int($month) || $month < 1 || $month > 12) throw new InvalidArgumentException('Mes no valido.');
                $start = sprintf('%04d-%02d-01', $year, $month);
                $params[] = $start;
                $params[] = (new DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d');
                $ranges[] = "$column >= ? AND $column < ?";
            }
        }
        return count($ranges) === 1 ? $ranges[0] : '(' . implode(' OR ', array_map(static fn($range) => "($range)", $ranges)) . ')';
    }
    // Usa la conexión central; propaga errores para distinguir error de un mes vacío.
    private function consultar(string $sql, array $params = []): array
    {
        $stmt = $this->getConexion()->prepare($sql);
        if (!$stmt->execute($params)) {
            throw new RuntimeException('No se pudo consultar el reporte mensual.');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function vendedores(string $alcance): array
    {
        $where = 'WHERE ' . self::FILTRO_PROYECTOS . ($alcance === '' ? '' : ' AND v.ccveusuario_vendedor = ?');
        return $this->consultar(
            "SELECT v.ccveusuario_vendedor AS id,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS nombre
            FROM tb_ventas v LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            $where GROUP BY v.ccveusuario_vendedor, m.cNombre, m.cPriApellido, m.cSegApellido ORDER BY nombre",
            $alcance === '' ? [] : [$alcance]
        );
    }

    public function proyectoDeclinadoAutorizado(int $ventaId, string $seller): bool
    {
        $scope = ' AND ' . self::FILTRO_PROYECTOS . ($seller === '' ? '' : ' AND v.ccveusuario_vendedor = ?');
        return $this->consultar(
            "SELECT v.id FROM tb_ventas v WHERE v.id = ? AND v.activo = 'CERRADO'$scope",
            $seller === '' ? [$ventaId] : [$ventaId, $seller]
        ) !== [];
    }

    /** Historial de un proyecto del reporte dentro del alcance del usuario. */
    public function proyectoReporteAutorizado(int $ventaId, string $seller): bool
    {
        $scope = self::FILTRO_PROYECTOS . ($seller === '' ? '' : ' AND v.ccveusuario_vendedor = ?');
        return $this->consultar("SELECT v.id FROM tb_ventas v WHERE v.id = ? AND $scope",
            $seller === '' ? [$ventaId] : [$ventaId, $seller]) !== [];
    }

    /** Condición única para el KPI crítico, su listado y acceso al historial. */
    private static function condicionInternaSinCliente(): string
    {
        return "COALESCE(v.activo,'ACTIVO') <> 'CERRADO' AND EXISTS (
            SELECT 1 FROM tb_compras_cotizacion_interna ci
            WHERE ci.venta_id = v.id AND ci.enviado = 1 AND NOT EXISTS (
                SELECT 1 FROM tb_ventas_cotizacion_cliente cliente
                WHERE cliente.cotizacion_interna_id = ci.id AND cliente.enviado = 1
            )
        )";
    }

    public function proyectoInternaSinClienteAutorizado(int $ventaId, string $seller): bool
    {
        $condition = self::condicionInternaSinCliente();
        $scope = ' AND ' . self::FILTRO_PROYECTOS . ($seller === '' ? '' : ' AND v.ccveusuario_vendedor = ?');
        return $this->consultar(
            "SELECT v.id FROM tb_ventas v WHERE v.id = ? AND $condition$scope",
            $seller === '' ? [$ventaId] : [$ventaId, $seller]
        ) !== [];
    }

    public function dashboard(int|array $year, int|array $month, string $seller): array
    {
        $dateParams = [];
        $projectPeriod = self::periodo('v.fecha', $year, $month, $dateParams);
        $scope = ' AND ' . self::FILTRO_PROYECTOS . ($seller === '' ? '' : ' AND v.ccveusuario_vendedor = ?');
        $params = $seller === '' ? $dateParams : [...$dateParams, $seller];
        $periodParams = [];
        $sentPeriod = self::periodo('COALESCE(cc.fecha, v.fecha_cotizacion, v.fecha)', $year, $month, $periodParams);
        $placedPeriod = self::periodo('fecha_pedido', $year, $month, $periodParams);
        $rateRows = $this->consultar('SELECT valor, fecha FROM tb_historial_tipos_cambio WHERE idMoneda = 3 ORDER BY fecha DESC, id DESC LIMIT 1');
        $rate = (float)($rateRows[0]['valor'] ?? 0);
        $divisor = $rate == 0 ? 1.0 : $rate;
        $columns = "v.id, v.proyecto_id, v.ccveusuario_vendedor AS vendedor_id, v.moneda_id,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS vendedor";
        // Cotizaciones enviadas: se excluye CERRADO por indicación del usuario para este dashboard.
        $sent = $this->consultar("SELECT $columns, 'cotizado' AS tipo,
            SUM(COALESCE(cc.total, 0)) AS monto, COUNT(*) AS cotizaciones,
            COALESCE(MAX(cc.fecha), v.fecha_cotizacion, v.fecha) AS fecha
            FROM tb_ventas_cotizacion_cliente cc INNER JOIN tb_ventas v ON v.id = cc.venta_id
            LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            WHERE cc.enviado = 1 AND v.estatus_pedido_reporte IN (1,2)
            AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'
            AND $sentPeriod $scope
            GROUP BY v.id, v.proyecto_id, v.ccveusuario_vendedor, v.moneda_id, v.fecha_cotizacion, v.fecha, m.cNombre, m.cPriApellido, m.cSegApellido", $params);
        // Una fila por proyecto como en el listado de escritorio; los pedidos sólo determinan pertenencia al período.
        $placed = $this->consultar("SELECT $columns, 'colocado' AS tipo, COALESCE(v.total,0) AS monto,
            p.fecha AS fecha FROM tb_ventas v
            INNER JOIN (SELECT venta_id, MIN(fecha_pedido) AS fecha FROM tb_pedidos_cliente
                WHERE $placedPeriod GROUP BY venta_id) p ON p.venta_id=v.id
            INNER JOIN cat_clasificacion_proyectos cl ON cl.id=v.clasificacion_proyecto_id
            INNER JOIN cat_estatus_proyecto e ON e.Id=v.estatus_proyecto_id
            INNER JOIN cat_clientes c ON c.id=v.cliente_id
            INNER JOIN cat_medico m ON m.ccvemedico=v.ccveusuario_vendedor
            WHERE v.estatus_pedido_reporte=2 $scope ORDER BY v.id", $params);
        $projects = [];
        foreach ($placed as $row) {
            $folio = (string)$row['proyecto_id'];
            // El GROUP BY del escritorio es indeterminado si un folio pertenece a varias ventas.
            if (isset($projects[$folio])) {
                throw new UnexpectedValueException('Hay folios de proyecto duplicados en el período; se requiere revisar su conciliación.');
            }
            $projects[$folio] = true;
        }
        $headers = array_merge($sent, $placed);
        // Cantidades independientes del pipeline: todos los proyectos registrados en el mes.
        // Los documentos se agrupan por venta_id para contar proyectos, no documentos ni partidas.
        $criticalCondition = self::condicionInternaSinCliente();
        $counts = $this->consultar("SELECT COUNT(*) AS total_proyectos,
            COALESCE(SUM(CASE WHEN v.activo = 'CERRADO' THEN 1 ELSE 0 END),0) AS declinados,
            COALESCE(SUM(CASE WHEN cc.venta_id IS NOT NULL AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'
                THEN 1 ELSE 0 END),0) AS cotizacion_cliente,
            COALESCE(SUM(CASE WHEN pc.venta_id IS NOT NULL AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'
                THEN 1 ELSE 0 END),0) AS orden_compra_cliente,
            COALESCE(SUM(CASE WHEN $criticalCondition THEN 1 ELSE 0 END),0) AS interna_sin_cliente
            FROM tb_ventas v
            LEFT JOIN (SELECT venta_id FROM tb_ventas_cotizacion_cliente
                WHERE enviado = 1 GROUP BY venta_id) cc ON cc.venta_id=v.id
            LEFT JOIN (SELECT venta_id FROM tb_pedidos_cliente
                WHERE enviado = 1 GROUP BY venta_id) pc ON pc.venta_id=v.id
            WHERE $projectPeriod $scope", $params);
        if (!$counts) throw new RuntimeException('No se pudieron obtener las cantidades de proyectos.');
        $quantities = array_map('intval', $counts[0]);
        $projectsBySeller = $this->consultar("SELECT v.ccveusuario_vendedor AS vendedor_id,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS nombre,
            COUNT(*) AS proyectos
            FROM tb_ventas v LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            WHERE $projectPeriod $scope
            GROUP BY v.ccveusuario_vendedor, m.cNombre, m.cPriApellido, m.cSegApellido
            ORDER BY proyectos DESC, nombre ASC, v.ccveusuario_vendedor ASC", $params);
        foreach ($projectsBySeller as &$sellerRow) $sellerRow['proyectos'] = (int)$sellerRow['proyectos'];
        unset($sellerRow);
        // Clasificaciones del catálogo, con declinados separados dentro de cada total.
        $statusesBySeller = $this->consultar("SELECT v.ccveusuario_vendedor AS vendedor_id,
            v.clasificacion_proyecto_id AS clasificacion_id, COALESCE(s.clasificacion, 'Sin clasificación') AS clasificacion,
            COUNT(*) AS proyectos,
            SUM(CASE WHEN v.activo = 'CERRADO' THEN 1 ELSE 0 END) AS declinados
            FROM tb_ventas v LEFT JOIN cat_clasificacion_proyectos s ON s.id = v.clasificacion_proyecto_id
            WHERE $projectPeriod $scope
            GROUP BY v.ccveusuario_vendedor, v.clasificacion_proyecto_id, s.clasificacion
            ORDER BY v.clasificacion_proyecto_id ASC, v.ccveusuario_vendedor ASC", $params);
        foreach ($statusesBySeller as &$statusRow) {
            $statusRow['proyectos'] = (int)$statusRow['proyectos'];
            $statusRow['declinados'] = (int)$statusRow['declinados'];
        }
        unset($statusRow);
        $projectStatuses = $this->consultar("SELECT v.ccveusuario_vendedor AS vendedor_id,
            v.estatus_proyecto_id AS estatus_id, COALESCE(s.cEstatusReporte, 'Sin Estatus') AS estatus,
            COUNT(*) AS proyectos, SUM(CASE WHEN v.activo = 'CERRADO' THEN 1 ELSE 0 END) AS declinados
            FROM tb_ventas v LEFT JOIN cat_estatus_proyecto s ON s.Id = v.estatus_proyecto_id
            WHERE $projectPeriod $scope
            GROUP BY v.ccveusuario_vendedor, v.estatus_proyecto_id, s.cEstatusReporte
            ORDER BY v.estatus_proyecto_id ASC, v.ccveusuario_vendedor ASC", $params);
        foreach ($projectStatuses as &$statusRow) {
            $statusRow['proyectos'] = (int)$statusRow['proyectos'];
            $statusRow['declinados'] = (int)$statusRow['declinados'];
        }
        unset($statusRow);
        $annualParams = [];
        $annualPeriod = self::periodo('v.fecha', $year, range(1,12), $annualParams);
        if ($seller !== '') $annualParams[] = $seller;
        return self::resumir($headers, $divisor)
            // El desglose por clasificación utiliza el mismo conjunto y estatus del catálogo.
            + [
                'cantidades' => $quantities,
                'proyectos_por_vendedor' => $projectsBySeller,
                'clasificaciones_por_vendedor' => $statusesBySeller,
                'estatus_por_vendedor' => $projectStatuses,
                'estatus_por_clasificacion' => $this->estatusPorClasificacion($scope, $annualParams, $annualPeriod),
                'anios_seleccionados' => is_array($year) ? $year : [$year],
                'meses_seleccionados' => is_array($month) ? $month : [$month],
                'tipo_cambio' => $rate,
                'fecha_tipo_cambio' => $rateRows[0]['fecha'] ?? null
            ];
    }

    private function estatusPorClasificacion(string $scope, array $params, string $projectPeriod): array
    {
        $rows = $this->consultar("SELECT v.clasificacion_proyecto_id AS clasificacion_id,
            v.ccveusuario_vendedor AS vendedor_id,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS vendedor,
            YEAR(v.fecha) AS anio, MONTH(v.fecha) AS mes,
            v.estatus_proyecto_id AS estatus_id, COALESCE(s.cEstatusReporte, 'Sin Estatus') AS estatus,
            COUNT(*) AS proyectos, SUM(CASE WHEN v.activo = 'CERRADO' THEN 1 ELSE 0 END) AS declinados
            FROM tb_ventas v LEFT JOIN cat_estatus_proyecto s ON s.Id = v.estatus_proyecto_id
            LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            WHERE $projectPeriod $scope
            GROUP BY v.clasificacion_proyecto_id, YEAR(v.fecha), MONTH(v.fecha), v.estatus_proyecto_id, s.cEstatusReporte, v.ccveusuario_vendedor, m.cNombre, m.cPriApellido, m.cSegApellido
            ORDER BY v.clasificacion_proyecto_id ASC, anio ASC, mes ASC, v.estatus_proyecto_id ASC", $params);
        foreach ($rows as &$row) {
            $row['proyectos'] = (int)$row['proyectos'];
            $row['declinados'] = (int)$row['declinados'];
        }
        unset($row);
        return $rows;
    }

    /** Lista paginada con las mismas condiciones del indicador Declinados. */
    public function declinados(int|array $year, int|array $month, string $seller, int $page): array
    {
        $dateParams = [];
        $projectPeriod = self::periodo('v.fecha', $year, $month, $dateParams);
        $where = "$projectPeriod AND v.activo = 'CERRADO' AND " . self::FILTRO_PROYECTOS;
        $params = $dateParams;
        if ($seller !== '') {
            $where .= ' AND v.ccveusuario_vendedor = ?';
            $params[] = $seller;
        }
        $count = $this->consultar("SELECT COUNT(*) AS total FROM tb_ventas v WHERE $where", $params);
        if (!$count) throw new RuntimeException('No se pudo obtener la lista de declinados.');
        $total = (int)$count[0]['total'];
        $pageSize = 20;
        $pages = max(1, (int)ceil($total / $pageSize));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $pageSize;
        $rows = $this->consultar("SELECT v.id, v.proyecto_id, v.fecha, v.titulo, v.activo,
            COALESCE(c.nombre_comercial, 'Sin cliente') AS cliente,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS vendedor
            FROM tb_ventas v LEFT JOIN cat_clientes c ON c.id = v.cliente_id
            LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            WHERE $where ORDER BY v.fecha DESC, v.id DESC LIMIT $pageSize OFFSET $offset", $params);
        return ['proyectos' => $rows, 'total' => $total, 'pagina' => $page, 'paginas' => $pages, 'por_pagina' => $pageSize];
    }

    /** DataTables: búsqueda y paginación en servidor, dentro del alcance autorizado. */
    public function declinadosTabla(int|array $year, int|array $month, string $seller, array $options, string $lista = 'declinados'): array
    {
        $dateParams = [];
        $projectPeriod = self::periodo('v.fecha', $year, $month, $dateParams);
        $condition = $lista === 'interna_sin_cliente' ? self::condicionInternaSinCliente() : "v.activo = 'CERRADO'";
        $where = "$projectPeriod AND $condition AND " . self::FILTRO_PROYECTOS;
        $params = $dateParams;
        if ($seller !== '') {
            $where .= ' AND v.ccveusuario_vendedor = ?';
            $params[] = $seller;
        }
        if (in_array($lista, ['estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo'], true)) {
            $where = "$projectPeriod AND " . self::FILTRO_PROYECTOS;
            $params = $dateParams;
            if ($seller !== '') { $where .= ' AND v.ccveusuario_vendedor = ?'; $params[] = $seller; }
            $where .= ' AND v.clasificacion_proyecto_id = ?';
            $params[] = $options['clasificacion_id'];
            if ($lista === 'vendedor_clasificacion') {
                $where .= ' AND v.ccveusuario_vendedor = ?';
                $params[] = $options['vendedor_id'];
            } elseif ($lista === 'estatus_clasificacion' && $options['estatus_id'] === null) $where .= ' AND v.estatus_proyecto_id IS NULL';
            elseif ($lista === 'estatus_clasificacion') { $where .= ' AND v.estatus_proyecto_id = ?'; $params[] = $options['estatus_id']; }
            $where .= $options['segmento'] === 'declinados'
                ? " AND v.activo = 'CERRADO'" : " AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'";
        }
        $joins = 'FROM tb_ventas v LEFT JOIN cat_clientes c ON c.id=v.cliente_id
            LEFT JOIN cat_medico m ON m.ccvemedico=v.ccveusuario_vendedor
            LEFT JOIN cat_clasificacion_proyectos cl ON cl.id=v.clasificacion_proyecto_id';
        $sellerName = "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor')";
        $fields = [
            1 => 'v.proyecto_id',
            2 => "DATE_FORMAT(v.fecha, '%d/%m/%Y')",
            3 => "COALESCE(c.nombre_comercial, 'Sin cliente')",
            4 => $sellerName,
            5 => "COALESCE(cl.clasificacion, 'Sin clasificación')",
            6 => "COALESCE(v.titulo,'')",
            7 => 'v.activo'
        ];
        $total = $this->consultar("SELECT COUNT(*) AS total FROM tb_ventas v WHERE $where", $params);
        if (!$total) throw new RuntimeException('No se pudo consultar la tabla.');
        $filteredWhere = $where;
        $filteredParams = $params;
        $like = static fn($text) => '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $text) . '%';
        if ($options['search'] !== '') {
            $clauses = [];
            foreach ($fields as $field) {
                $clauses[] = "$field LIKE ? ESCAPE '!'";
                $filteredParams[] = $like($options['search']);
            }
            $filteredWhere .= ' AND (' . implode(' OR ', $clauses) . ')';
        }
        foreach ($fields as $index => $field) {
            if (($options['filters'][$index] ?? '') !== '') {
                $filteredWhere .= " AND $field LIKE ? ESCAPE '!'";
                $filteredParams[] = $like($options['filters'][$index]);
            }
        }
        $filtered = $this->consultar("SELECT COUNT(*) AS total $joins WHERE $filteredWhere", $filteredParams);
        if (!$filtered) throw new RuntimeException('No se pudo consultar la tabla.');
        $orderIndex = (int)$options['order_column'];
        $order = $orderIndex === 2 ? 'v.fecha' : ($fields[$orderIndex] ?? 'v.id');
        $direction = $options['order_dir'] === 'asc' ? 'ASC' : 'DESC';
        $groupOrder = in_array($lista, ['estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo'], true)
            ? "$sellerName ASC, v.ccveusuario_vendedor ASC, COALESCE(c.nombre_comercial, 'Sin cliente') ASC, v.cliente_id ASC, " : '';
        $length = max(5, min(100, (int)$options['length']));
        $offset = max(0, min(1000000, (int)$options['start']));
        $rows = $this->consultar("SELECT v.id, v.proyecto_id, v.fecha, v.titulo, v.activo,
            v.ccveusuario_vendedor AS vendedor_id, v.cliente_id,
            COALESCE(c.nombre_comercial, 'Sin cliente') AS cliente, $sellerName AS vendedor,
            COALESCE(cl.clasificacion, 'Sin clasificación') AS clasificacion
            $joins WHERE $filteredWhere ORDER BY $groupOrder$order $direction, v.id DESC LIMIT $length OFFSET $offset", $filteredParams);
        return [
            'draw' => (int)$options['draw'],
            'recordsTotal' => (int)$total[0]['total'],
            'recordsFiltered' => (int)$filtered[0]['total'],
            'data' => $rows
        ];
    }

    /** Totales del comparativo, con la misma conversion y redondeo por proyecto. */
    public static function resumir(array $headers, float $divisor): array
    {
        $result = ['cotizado'=>0.0, 'colocado'=>0.0, 'proyectos'=>0,
            'cotizaciones_enviadas'=>0, 'proyectos_cotizados'=>0, 'proyectos_colocados'=>0];
        $unique = [];
        foreach ($headers as $row) {
            $type = $row['tipo'];
            $factor = (int)$row['moneda_id'] === 1 ? 1 / $divisor : 1;
            $result[$type] += round((float)$row['monto'] * $factor, 2);
            $result['proyectos_'.$type.'s']++;
            if ($type === 'cotizado') $result['cotizaciones_enviadas'] += (int)($row['cotizaciones'] ?? 0);
            $unique[(string)$row['id']] = true;
        }
        $result['proyectos'] = count($unique);
        return $result;
    }
}
