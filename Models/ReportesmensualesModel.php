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
    private static function periodoDeclinados(int|array $years, int|array $months, array &$params): string
    {
        $projectParams = [];
        $declineParams = [];
        $projectPeriod = self::periodo('v.fecha', $years, $months, $projectParams);
        $declinePeriod = self::periodo('v.fecha_declina', $years, $months, $declineParams);
        $params = [...$projectParams, ...$declineParams];
        // La unión equivale a proyectos del período + anteriores declinados en él,
        // sin multiplicar los proyectos que cumplen ambas fechas.
        return "(($projectPeriod) OR ($declinePeriod))";
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
        $placedPeriod = self::periodo('pc.fecha_pedido', $year, $month, $periodParams);
        $rateRows = $this->consultar('SELECT valor, fecha FROM tb_historial_tipos_cambio WHERE idMoneda = 3 ORDER BY fecha DESC, id DESC LIMIT 1');
        $rate = (float)($rateRows[0]['valor'] ?? 0);
        $divisor = $rate == 0 ? 1.0 : $rate;
        $columns = "v.id, v.proyecto_id, v.clasificacion_proyecto_id AS clasificacion_id, v.ccveusuario_vendedor AS vendedor_id, v.moneda_id,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS vendedor";
        // Cotizaciones enviadas: incluir proyectos cerrados, conservando los filtros comerciales.
        $sentSql = "SELECT $columns, 'cotizado' AS tipo,
            SUM(CASE WHEN v.moneda_id = 3 THEN COALESCE(cp.subtotal_partidas, 0) ELSE 0 END) AS monto_usd_original,
            SUM(CASE WHEN v.moneda_id = 1 THEN COALESCE(cp.subtotal_partidas, 0) ELSE 0 END) AS monto_mxn_original,
            SUM(COALESCE(cp.subtotal_partidas, 0)) AS monto, COUNT(DISTINCT cc.id) AS cotizaciones,
            COALESCE(MAX(cc.fecha), v.fecha_cotizacion, v.fecha) AS fecha
            FROM tb_ventas_cotizacion_cliente cc INNER JOIN tb_ventas v ON v.id = cc.venta_id
            INNER JOIN (SELECT cd.cotizacion_cliente_id, SUM(cd.cantidad * cd.precio_unitario) AS subtotal_partidas
                FROM tb_ventas_cotizacion_cliente_detalle cd
                INNER JOIN tb_ventas_detalle vd ON vd.id = cd.venta_detalle_id_partida
                WHERE vd.tipo_partida IN ('PRODUCTO','SERVICIO') GROUP BY cd.cotizacion_cliente_id) cp
                ON cp.cotizacion_cliente_id = cc.id
            LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            WHERE cc.enviado = 1 AND v.estatus_pedido_reporte IN (1,2)
            AND $sentPeriod $scope
            GROUP BY v.id, v.proyecto_id, v.clasificacion_proyecto_id, v.ccveusuario_vendedor, v.moneda_id, v.fecha_cotizacion, v.fecha, m.cNombre, m.cPriApellido, m.cSegApellido";
        $sent = $this->consultar($sentSql, $params);
        // Pedidos enviados: sumar cada partida una vez y convertir con el tipo de cambio existente.
        $placedSqlMoney = "SELECT v.id, v.proyecto_id, v.clasificacion_proyecto_id AS clasificacion_id, v.ccveusuario_vendedor AS vendedor_id,
            3 AS moneda_id, COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS vendedor,
            'colocado' AS tipo,
            SUM(CASE WHEN pc.moneda_id = 3 THEN pd.cantidad_pedido * pd.precio_unitario ELSE 0 END) AS monto_usd_original,
            SUM(CASE WHEN pc.moneda_id = 1 THEN pd.cantidad_pedido * pd.precio_unitario ELSE 0 END) AS monto_mxn_original,
            SUM(CASE WHEN pc.moneda_id = 1
                THEN (pd.cantidad_pedido * pd.precio_unitario) / $divisor
                ELSE pd.cantidad_pedido * pd.precio_unitario END) AS monto, MIN(pc.fecha_pedido) AS fecha
            FROM tb_pedidos_cliente pc
            INNER JOIN tb_pedidos_cliente_detalle pd ON pd.pedido_id = pc.id
            INNER JOIN tb_ventas_detalle vd ON vd.id = pd.venta_detalle_id
            INNER JOIN tb_ventas v ON v.id = pc.venta_id
            LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            WHERE pc.enviado = 1 AND vd.tipo_partida IN ('PRODUCTO','SERVICIO')
                AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO' AND v.estatus_pedido_reporte = 2
                AND $placedPeriod $scope
            GROUP BY v.id, v.proyecto_id, v.clasificacion_proyecto_id, v.ccveusuario_vendedor, m.cNombre, m.cPriApellido, m.cSegApellido ORDER BY v.id";
        $placed = $this->consultar($placedSqlMoney, $params);
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
            COALESCE(SUM(CASE WHEN cc.venta_id IS NOT NULL
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
        $annualStatuses = $this->estatusPorClasificacion($scope, $annualParams, $annualPeriod);
        // Complemento del KPI: cada proyecto externo al período cuenta una sola vez,
        // aunque tenga varias cotizaciones enviadas durante los meses seleccionados.
        $quotationParams = [];
        $quotationPeriod = self::periodo('cc.fecha', $year, $month, $quotationParams);
        $previousParams = [...$dateParams, ...$quotationParams];
        if ($seller !== '') $previousParams[] = $seller;
        $previousQuoted = $this->consultar("SELECT COUNT(DISTINCT v.id) AS cotizacion_cliente_anteriores
            FROM tb_ventas v
            WHERE NOT COALESCE(($projectPeriod), 0)
                AND EXISTS (SELECT 1 FROM tb_ventas_cotizacion_cliente cc
                    WHERE cc.venta_id = v.id AND cc.enviado = 1 AND ($quotationPeriod))
                $scope", $previousParams);
        if (!$previousQuoted) throw new RuntimeException('No se pudieron obtener los proyectos anteriores cotizados.');
        $quantities['cotizacion_cliente_periodo'] = $quantities['cotizacion_cliente'];
        $quantities['cotizacion_cliente_anteriores'] = (int)$previousQuoted[0]['cotizacion_cliente_anteriores'];
        $quantities['cotizacion_cliente'] += $quantities['cotizacion_cliente_anteriores'];
        $orderParams = [];
        $orderPeriod = self::periodo('pc.fecha_pedido', $year, $month, $orderParams);
        $previousOrderParams = [...$dateParams, ...$orderParams];
        if ($seller !== '') $previousOrderParams[] = $seller;
        $previousPlaced = $this->consultar("SELECT COUNT(DISTINCT v.id) AS orden_compra_cliente_anteriores
            FROM tb_ventas v
            WHERE NOT COALESCE(($projectPeriod), 0)
                AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'
                AND EXISTS (SELECT 1 FROM tb_pedidos_cliente pc
                    WHERE pc.venta_id = v.id AND pc.enviado = 1 AND ($orderPeriod))
                $scope", $previousOrderParams);
        if (!$previousPlaced) throw new RuntimeException('No se pudieron obtener los proyectos anteriores colocados.');
        $quantities['orden_compra_cliente_periodo'] = $quantities['orden_compra_cliente'];
        $quantities['orden_compra_cliente_anteriores'] = (int)$previousPlaced[0]['orden_compra_cliente_anteriores'];
        $quantities['orden_compra_cliente'] += $quantities['orden_compra_cliente_anteriores'];
        $declineParams = [];
        $declinePeriod = self::periodo('v.fecha_declina', $year, $month, $declineParams);
        $previousDeclineParams = [...$dateParams, ...$declineParams];
        if ($seller !== '') $previousDeclineParams[] = $seller;
        $previousDeclined = $this->consultar("SELECT COUNT(DISTINCT v.id) AS declinados_anteriores
            FROM tb_ventas v
            WHERE NOT COALESCE(($projectPeriod), 0) AND ($declinePeriod)
                AND v.activo = 'CERRADO' $scope", $previousDeclineParams);
        if (!$previousDeclined) throw new RuntimeException('No se pudieron obtener los proyectos anteriores declinados.');
        $quantities['declinados_periodo'] = $quantities['declinados'];
        $quantities['declinados_anteriores'] = (int)$previousDeclined[0]['declinados_anteriores'];
        $quantities['declinados'] += $quantities['declinados_anteriores'];
        [$quotedSql,$quotedParams] = self::cotizadosPeriodoSql($year,$month,$seller);
        // Un nombre por vendedor: conserva la cardinalidad del conjunto de proyectos.
        $sellerNames = "LEFT JOIN (SELECT ccvemedico,
            MAX(NULLIF(TRIM(CONCAT_WS(' ', cNombre, cPriApellido, cSegApellido)), '')) AS nombre
            FROM cat_medico GROUP BY ccvemedico) nombres";
        $quotedPeriods = $this->consultar("SELECT vendedor_id, YEAR(fecha_reporte) AS anio,
            MONTH(fecha_reporte) AS mes, origen, COUNT(*) AS proyectos,
            COALESCE(MAX(nombres.nombre), 'Sin vendedor') AS nombre
            FROM ($quotedSql) cotizados $sellerNames ON nombres.ccvemedico = cotizados.vendedor_id
            GROUP BY vendedor_id, YEAR(fecha_reporte), MONTH(fecha_reporte), origen", $quotedParams);
        [$placedSql,$placedParams] = self::documentadosPeriodoSql($year,$month,$seller,'colocados');
        $placedPeriods = $this->consultar("SELECT vendedor_id, YEAR(fecha_reporte) AS anio,
            MONTH(fecha_reporte) AS mes, origen, COUNT(*) AS proyectos,
            COALESCE(MAX(nombres.nombre), 'Sin vendedor') AS nombre
            FROM ($placedSql) colocados $sellerNames ON nombres.ccvemedico = colocados.vendedor_id
            GROUP BY vendedor_id, YEAR(fecha_reporte), MONTH(fecha_reporte), origen", $placedParams);
        $evolutionClasses = [];
        $evolution = $this->evolucionMensual($year, $month, $seller, $divisor, [
            ['cotizado', $sentSql, $sentPeriod, 'COALESCE(cc.fecha, v.fecha_cotizacion, v.fecha)'],
            ['colocado', $placedSqlMoney, $placedPeriod, 'pc.fecha_pedido']
        ], $evolutionClasses);
        $classificationQueries = [];
        $classificationParams = [];
        foreach (['cotizados'=>'cotizado','colocados'=>'colocado'] as $type=>$metric) {
            [$sql,$queryParams] = self::documentadosPeriodoSql($year,$month,$seller,$type);
            $classificationQueries[] = "SELECT v.clasificacion_proyecto_id AS clasificacion_id, documentos.vendedor_id,
                '$metric' AS tipo, COUNT(*) AS proyectos, COALESCE(MAX(nombres.nombre), 'Sin vendedor') AS nombre
                FROM ($sql) documentos INNER JOIN tb_ventas v ON v.id = documentos.id
                $sellerNames ON nombres.ccvemedico = documentos.vendedor_id
                GROUP BY v.clasificacion_proyecto_id, documentos.vendedor_id";
            array_push($classificationParams,...$queryParams);
        }
        $classificationRows = $this->consultar(implode(' UNION ALL ', $classificationQueries),$classificationParams);
        $classificationCatalog = $this->consultar('SELECT id, clasificacion FROM cat_clasificacion_proyectos WHERE id IN (2,3,4,5) ORDER BY id');
        return self::resumir($headers, $divisor)
            // El desglose por clasificación utiliza el mismo conjunto y estatus del catálogo.
            + [
                'cantidades' => $quantities,
                'proyectos_por_vendedor' => $projectsBySeller,
                'clasificaciones_por_vendedor' => $statusesBySeller,
                'estatus_por_vendedor' => $projectStatuses,
                'estatus_por_clasificacion' => $annualStatuses,
                'cotizados_por_periodo' => $quotedPeriods,
                'colocados_por_periodo' => $placedPeriods,
                'evolucion_mensual' => $evolution,
                'evolucion_clasificacion' => $evolutionClasses,
                'comparativo_clasificacion_local' => self::resumenClasificacionLocal($classificationRows,$headers,$divisor),
                'clasificaciones_comparativo' => $classificationCatalog,
                'anios_seleccionados' => is_array($year) ? $year : [$year],
                'meses_seleccionados' => is_array($month) ? $month : [$month],
                'tipo_cambio' => $rate,
                'tipo_cambio_aplicado' => $divisor,
                'cotizado_moneda_original' => [
                    'USD' => array_sum(array_column($sent, 'monto_usd_original')),
                    'MXN' => array_sum(array_column($sent, 'monto_mxn_original'))
                ],
                'colocado_moneda_original' => [
                    'USD' => array_sum(array_column($placed, 'monto_usd_original')),
                    'MXN' => array_sum(array_column($placed, 'monto_mxn_original'))
                ],
                'fecha_tipo_cambio' => $rateRows[0]['fecha'] ?? null
            ];
    }

    /** Cada mes aplica el mismo conjunto del KPI y las mismas partidas/conversion monetaria. */
    private function evolucionMensual(int|array $years, int|array $months, string $seller, float $divisor, array $moneyQueries, array &$byClass): array
    {
        $years = array_values(array_unique(is_array($years) ? $years : [$years]));
        sort($years, SORT_NUMERIC);
        $months = array_values(array_unique(is_array($months) ? $months : [$months]));
        sort($months, SORT_NUMERIC);
        $result = [];
        $countQueries = [];
        $countParams = [];
        foreach ($years as $year) {
            foreach ($months as $month) {
                $result["$year-$month"] = ['anio'=>$year,'mes'=>$month,'cotizado'=>0,'colocado'=>0,'importe_cotizado'=>0.0,'importe_colocado'=>0.0];
                foreach (['cotizados'=>'cotizado','colocados'=>'colocado'] as $type=>$key) {
                    [$sql,$params] = self::documentadosPeriodoSql($year,$month,$seller,$type);
                    $countQueries[] = "SELECT $year AS anio, $month AS mes, v.clasificacion_proyecto_id AS clasificacion_id,
                        '$key' AS tipo, COUNT(*) AS proyectos FROM ($sql) documentos INNER JOIN tb_ventas v ON v.id = documentos.id
                        GROUP BY v.clasificacion_proyecto_id";
                    array_push($countParams,...$params);
                }
            }
        }
        foreach ($this->consultar(implode(' UNION ALL ', $countQueries), $countParams) as $row) {
            $key = $row['anio'].'-'.$row['mes'];
            $classKey = $key.'-'.(int)($row['clasificacion_id'] ?? 0);
            if (!isset($byClass[$classKey])) $byClass[$classKey] = array_replace($result[$key],['cotizado'=>0,'colocado'=>0,'clasificacion_id'=>(int)($row['clasificacion_id'] ?? 0)]);
            $byClass[$classKey][$row['tipo']] += (int)$row['proyectos'];
            $result[$key][$row['tipo']] += (int)$row['proyectos'];
        }
        foreach ($moneyQueries as [$type,$sql,$selectedPeriod,$date]) {
            $params = [];
            $filteredPeriod = self::periodo($date,$years,$months,$params);
            if ($seller !== '') $params[] = $seller;
            // Reutilizar el SQL monetario original con los filtros globales y separar por mes.
            $sql = str_replace($selectedPeriod,$filteredPeriod,$sql);
            $sql = preg_replace('/^SELECT /',"SELECT YEAR($date) AS anio, MONTH($date) AS mes, ",$sql,1);
            $sql = str_replace('GROUP BY v.id, v.proyecto_id',"GROUP BY YEAR($date), MONTH($date), v.id, v.proyecto_id",$sql);
            foreach ($this->consultar($sql,$params) as $row) {
                $key = $row['anio'].'-'.$row['mes'];
                $classKey = $key.'-'.(int)($row['clasificacion_id'] ?? 0);
                if (!isset($byClass[$classKey])) $byClass[$classKey] = array_replace($result[$key],['cotizado'=>0,'colocado'=>0,'importe_cotizado'=>0.0,'importe_colocado'=>0.0,'clasificacion_id'=>(int)($row['clasificacion_id'] ?? 0)]);
                $amount = self::resumir([$row],$divisor)[$type];
                $result[$key]['importe_'.$type] += $amount;
                $byClass[$classKey]['importe_'.$type] += $amount;
            }
        }
        $byClass = array_values($byClass);
        return array_values($result);
    }

    /** Desglose local sin volver a consultar partidas ni recalcular su conversion. */
    public static function resumenClasificacionLocal(array $counts, array $headers, float $divisor): array
    {
        $rows = [];
        foreach (array_merge($counts,$headers) as $row) {
            $class = (int)($row['clasificacion_id'] ?? 0);
            $seller = (string)($row['vendedor_id'] ?? '');
            $key = json_encode([$class,$seller]);
            if (!isset($rows[$key])) $rows[$key] = ['clasificacion_id'=>$class,'vendedor_id'=>$seller,'nombre'=>$row['nombre'] ?? $row['vendedor'] ?? 'Sin vendedor',
                'cotizado'=>0,'colocado'=>0,'importe_cotizado'=>0.0,'importe_colocado'=>0.0,
                'cotizado_usd'=>0.0,'cotizado_mxn'=>0.0,'colocado_usd'=>0.0,'colocado_mxn'=>0.0];
            $type = $row['tipo'];
            if (array_key_exists('monto',$row)) {
                $rows[$key]['importe_'.$type] += self::resumir([$row],$divisor)[$type];
                $rows[$key][$type.'_usd'] += (float)($row['monto_usd_original'] ?? 0);
                $rows[$key][$type.'_mxn'] += (float)($row['monto_mxn_original'] ?? 0);
            } else $rows[$key][$type] += (int)$row['proyectos'];
        }
        return array_values($rows);
    }

    /** Un registro por proyecto del KPI; anteriores se asignan a su primera cotizacion del filtro. */
    private static function cotizadosPeriodoSql(int|array $years, int|array $months, string $seller): array
    {
        return self::documentadosPeriodoSql($years,$months,$seller,'cotizados');
    }

    private static function documentadosPeriodoSql(int|array $years, int|array $months, string $seller, string $type): array
    {
        [$table,$alias,$date] = match ($type) {
            'cotizados' => ['tb_ventas_cotizacion_cliente','cc','cc.fecha'],
            'colocados' => ['tb_pedidos_cliente','pc','pc.fecha_pedido'],
            default => throw new InvalidArgumentException('Tipo de documento no valido.')
        };
        $projectParams = [];
        $quoteParams = [];
        $projectPeriod = self::periodo('v.fecha',$years,$months,$projectParams);
        $quotePeriod = self::periodo($date,$years,$months,$quoteParams);
        $params = [...$projectParams,...$projectParams,...$quoteParams,...$projectParams];
        $scope = self::FILTRO_PROYECTOS;
        if ($seller !== '') { $scope .= ' AND v.ccveusuario_vendedor = ?'; $params[] = $seller; }
        $activeFilter = $type === 'colocados' ? "AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'" : '';
        $sql = "SELECT v.id, v.ccveusuario_vendedor AS vendedor_id,
            CASE WHEN ($projectPeriod) THEN v.fecha ELSE q.fecha END AS fecha_reporte,
            CASE WHEN ($projectPeriod) THEN 'periodo' ELSE 'anteriores' END AS origen
            FROM tb_ventas v LEFT JOIN (
                SELECT $alias.venta_id, MIN($date) AS fecha FROM $table $alias
                WHERE $alias.enviado = 1 AND ($quotePeriod) GROUP BY $alias.venta_id
            ) q ON q.venta_id = v.id
            WHERE (($projectPeriod) OR q.fecha IS NOT NULL)
                $activeFilter
                AND EXISTS (SELECT 1 FROM $table $alias WHERE $alias.venta_id = v.id AND $alias.enviado = 1)
                AND $scope";
        return [$sql,$params];
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
        $projectPeriod = self::periodoDeclinados($year, $month, $dateParams);
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

    /** Distribución del mismo conjunto de declinados, antes de paginar el detalle. */
    public function declinadosResumen(int|array $year, int|array $month, string $seller): array
    {
        $params = [];
        $period = self::periodoDeclinados($year, $month, $params);
        $where = "$period AND v.activo = 'CERRADO' AND " . self::FILTRO_PROYECTOS;
        if ($seller !== '') { $where .= ' AND v.ccveusuario_vendedor = ?'; $params[] = $seller; }
        // Contar ventas antes de unir nombres evita duplicarlas si el catálogo repite una clave.
        $rows = $this->consultar("SELECT proyectos.vendedor_id,
            COALESCE(nombres.nombre, 'Sin vendedor') AS nombre, proyectos.proyectos
            FROM (SELECT COALESCE(v.ccveusuario_vendedor,'') AS vendedor_id, COUNT(*) AS proyectos
                FROM tb_ventas v WHERE $where GROUP BY COALESCE(v.ccveusuario_vendedor,'')) proyectos
            LEFT JOIN (SELECT ccvemedico,
                MAX(NULLIF(TRIM(CONCAT_WS(' ', cNombre, cPriApellido, cSegApellido)),'')) AS nombre
                FROM cat_medico GROUP BY ccvemedico) nombres ON nombres.ccvemedico = proyectos.vendedor_id
            ORDER BY proyectos.proyectos DESC, nombre ASC, proyectos.vendedor_id ASC", $params);
        $total = 0;
        foreach ($rows as &$row) { $row['proyectos'] = (int)$row['proyectos']; $total += $row['proyectos']; }
        unset($row);
        foreach ($rows as &$row) $row['porcentaje'] = $total > 0 ? round($row['proyectos'] / $total * 100, 2) : 0;
        unset($row);
        return ['total'=>$total, 'vendedores'=>$rows];
    }

    /** DataTables: búsqueda y paginación en servidor, dentro del alcance autorizado. */
    public function declinadosTabla(int|array $year, int|array $month, string $seller, array $options, string $lista = 'declinados'): array
    {
        $dateParams = [];
        $projectPeriod = self::periodo('v.fecha', $year, $month, $dateParams);
        $condition = $lista === 'interna_sin_cliente' ? self::condicionInternaSinCliente() : "v.activo = 'CERRADO'";
        if ($lista === 'declinados') $projectPeriod = self::periodoDeclinados($year, $month, $dateParams);
        $where = "$projectPeriod AND $condition AND " . self::FILTRO_PROYECTOS;
        $params = $dateParams;
        if ($seller !== '') {
            $where .= ' AND v.ccveusuario_vendedor = ?';
            $params[] = $seller;
        }
        if ($lista === 'declinados' && isset($options['declinado_vendedor'])) {
            $where .= " AND COALESCE(v.ccveusuario_vendedor,'') = ?";
            $params[] = $options['declinado_vendedor'];
        }
        if (in_array($lista,['cotizados_periodo','colocados_periodo'],true)) {
            [$quotedSql,$params] = self::documentadosPeriodoSql($options['periodo_anios'] ?? $year,$options['periodo_meses'] ?? $month,$seller,
                $lista === 'cotizados_periodo' ? 'cotizados' : 'colocados');
            $reportParams = [];
            $reportPeriod = self::periodo('cotizados.fecha_reporte',$year,$month,$reportParams);
            $where = "v.id IN (SELECT cotizados.id FROM ($quotedSql) cotizados WHERE $reportPeriod)";
            array_push($params,...$reportParams);
        }
        if (in_array($lista, ['estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo','estatus_periodo'], true)) {
            $where = "$projectPeriod AND " . self::FILTRO_PROYECTOS;
            $params = $dateParams;
            if ($seller !== '') { $where .= ' AND v.ccveusuario_vendedor = ?'; $params[] = $seller; }
            if ($lista !== 'estatus_periodo') {
                $where .= ' AND v.clasificacion_proyecto_id = ?';
                $params[] = $options['clasificacion_id'];
            }
            if ($lista === 'vendedor_clasificacion') {
                $where .= ' AND v.ccveusuario_vendedor = ?';
                $params[] = $options['vendedor_id'];
            } elseif ($lista === 'estatus_periodo' && $options['estatus_id'] === 'colocados') $where .= ' AND v.estatus_proyecto_id >= 6';
            elseif ($lista === 'estatus_periodo' && $options['estatus_id'] === 'proceso_cotizacion') $where .= ' AND v.estatus_proyecto_id IN (1,3)';
            elseif (in_array($lista, ['estatus_clasificacion','estatus_periodo'], true) && $options['estatus_id'] === null) $where .= ' AND v.estatus_proyecto_id IS NULL';
            elseif (in_array($lista, ['estatus_clasificacion','estatus_periodo'], true)) { $where .= ' AND v.estatus_proyecto_id = ?'; $params[] = $options['estatus_id']; }
            if ($lista !== 'estatus_periodo' || $options['segmento'] !== 'todos') {
                $where .= $options['segmento'] === 'declinados'
                    ? " AND v.activo = 'CERRADO'" : " AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'";
            }
        }
        $joins = 'FROM tb_ventas v LEFT JOIN cat_clientes c ON c.id=v.cliente_id
            LEFT JOIN cat_medico m ON m.ccvemedico=v.ccveusuario_vendedor
            LEFT JOIN cat_clasificacion_proyectos cl ON cl.id=v.clasificacion_proyecto_id';
        $sellerName = "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor')";
        if ($lista === 'declinados') {
            $joins = 'FROM tb_ventas v LEFT JOIN cat_clientes c ON c.id=v.cliente_id
                LEFT JOIN (SELECT ccvemedico,
                    MAX(NULLIF(TRIM(CONCAT_WS(\' \', cNombre, cPriApellido, cSegApellido)),\'\')) AS nombre
                    FROM cat_medico GROUP BY ccvemedico) m ON m.ccvemedico=v.ccveusuario_vendedor
                LEFT JOIN cat_clasificacion_proyectos cl ON cl.id=v.clasificacion_proyecto_id';
            $sellerName = "COALESCE(m.nombre, 'Sin vendedor')";
        }
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
        $groupOrder = in_array($lista, ['estatus_clasificacion','vendedor_clasificacion','clasificacion_periodo','estatus_periodo'], true)
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

    /** Mismo conjunto de proyectos del KPI; importes originales de partidas sin IVA. */
    public function colocadosFinanciero(int|array $year, int|array $month, string $seller, string $section = 'resumen', array $options = []): array
    {
        $projectParams = [];
        $orderParams = [];
        $scopeYears = $section === 'detalle' ? ($options['periodo_anios'] ?? $year) : $year;
        $scopeMonths = $section === 'detalle' ? ($options['periodo_meses'] ?? $month) : $month;
        $projectPeriod = self::periodo('v.fecha', $scopeYears, $scopeMonths, $projectParams);
        $period = self::periodo('pc.fecha_pedido', $scopeYears, $scopeMonths, $orderParams);
        $params = [...$projectParams,...$orderParams];
        $where = "(v.fecha_proyecto_periodo IS NOT NULL OR ($period)) AND pc.enviado = 1
            AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO' AND " . self::FILTRO_PROYECTOS;
        if ($seller !== '') { $where .= ' AND v.ccveusuario_vendedor = ?'; $params[] = $seller; }
        $base = "FROM tb_pedidos_cliente pc INNER JOIN (
            SELECT v.id, v.fecha, v.proyecto_id, v.cliente_id, v.ccveusuario_vendedor,
                v.clasificacion_proyecto_id, v.estatus_proyecto_id, v.activo,
                CASE WHEN ($projectPeriod) THEN v.fecha ELSE NULL END AS fecha_proyecto_periodo
            FROM tb_ventas v
            ) v ON v.id = pc.venta_id
            LEFT JOIN tb_pedidos_cliente_detalle pd ON pd.pedido_id = pc.id
            LEFT JOIN tb_ventas_detalle vd ON vd.id = pd.venta_detalle_id";
        $reportDate = 'COALESCE(v.fecha_proyecto_periodo, pc.fecha_pedido)';
        $line = 'pd.cantidad_pedido * pd.precio_unitario';
        $product = "COALESCE(SUM(CASE WHEN vd.tipo_partida = 'PRODUCTO' THEN $line ELSE 0 END),0)";
        $service = "COALESCE(SUM(CASE WHEN vd.tipo_partida = 'SERVICIO' THEN $line ELSE 0 END),0)";
        $total = "($product + $service)";
        $currency = "CASE WHEN pc.moneda_id = 1 THEN 'MXN' WHEN pc.moneda_id = 3 THEN 'USD' ELSE CONCAT('Moneda ', COALESCE(pc.moneda_id, 'sin identificar')) END";
        if ($section === 'resumen') {
            $totals = $this->consultar("SELECT pc.moneda_id, $currency AS moneda, $total AS total, $product AS productos, $service AS servicios,
                COUNT(DISTINCT v.id) AS proyectos, COUNT(DISTINCT pc.id) AS pedidos $base WHERE $where GROUP BY pc.moneda_id ORDER BY pc.moneda_id", $params);
            $group = "CASE WHEN v.clasificacion_proyecto_id IN (2,3,4) THEN 'Flowserve' ELSE 'Diversos' END";
            $subtotals = [];
            foreach ([1,2,3] as $subclassification) {
                $subtotals[] = "COALESCE(SUM(CASE WHEN vd.subclasificacion_id = $subclassification
                    AND vd.tipo_partida IN ('PRODUCTO','SERVICIO') THEN $line ELSE 0 END),0)
                    AS subclasificacion_$subclassification,
                    MAX(CASE WHEN sc.id = $subclassification THEN sc.subclasificacion ELSE NULL END)
                    AS subclasificacion_{$subclassification}_nombre";
                foreach (['PRODUCTO'=>'productos','SERVICIO'=>'servicios'] as $type=>$key) {
                    $subtotals[] = "COALESCE(SUM(CASE WHEN vd.subclasificacion_id = $subclassification
                        AND vd.tipo_partida = '$type' THEN $line ELSE 0 END),0)
                        AS subclasificacion_{$subclassification}_$key";
                }
            }
            $subtotalsSql = implode(', ', $subtotals);
            $groups = $this->consultar("SELECT $group AS grupo, pc.moneda_id, $currency AS moneda,
                $total AS total, $product AS productos, $service AS servicios, $subtotalsSql,
                COUNT(DISTINCT v.id) AS proyectos, COUNT(DISTINCT pc.id) AS pedidos $base
                LEFT JOIN cat_subclasificacion_proyectos sc ON sc.id = vd.subclasificacion_id AND sc.id IN (1,2,3)
                WHERE $where
                GROUP BY $group, pc.moneda_id ORDER BY grupo, pc.moneda_id", $params);
            $monthly = $this->consultar("SELECT YEAR($reportDate) AS anio, MONTH($reportDate) AS mes,
                pc.moneda_id, $currency AS moneda, COUNT(DISTINCT v.id) AS proyectos, COUNT(DISTINCT pc.id) AS pedidos,
                $total AS total, $product AS productos, $service AS servicios
                $base WHERE $where GROUP BY anio, mes, pc.moneda_id
                ORDER BY anio, mes, CASE pc.moneda_id WHEN 3 THEN 0 WHEN 1 THEN 1 ELSE 2 END", $params);
            $counts = $this->consultar("SELECT COUNT(DISTINCT v.id) AS proyectos,
                COUNT(DISTINCT CASE WHEN v.fecha_proyecto_periodo IS NOT NULL THEN v.id END) AS proyectos_periodo,
                COUNT(DISTINCT CASE WHEN v.fecha_proyecto_periodo IS NULL THEN v.id END) AS proyectos_anteriores,
                COUNT(DISTINCT pc.id) AS pedidos $base WHERE $where", $params);
            return ['totales'=>$totals, 'grupos'=>$groups, 'mensual'=>$monthly, 'conteo'=>$counts[0] ?? []];
        }
        if ($section === 'detalle') {
            $detailParams = [];
            $detailPeriod = self::periodo($reportDate, $year, $month, $detailParams);
            $where .= " AND ($detailPeriod) AND vd.tipo_partida IN ('PRODUCTO','SERVICIO')";
            array_push($params,...$detailParams);
            $length = max(5,min(100,(int)($options['length'] ?? 5)));
            $offset = max(0,min(1000000,(int)($options['start'] ?? 0)));
            $detailBase = $base . ' LEFT JOIN (
                SELECT ccvematerial, MAX(ccveMaterialAlmacen) AS ccn FROM tb_materiales GROUP BY ccvematerial
            ) mat ON mat.ccvematerial = pd.ccvematerial';
            $columns = ['v.proyecto_id','pc.num_orden_compra','pc.fecha_pedido',$currency,'vd.tipo_partida',
                'mat.ccvematerial','mat.ccn','pd.ccvematerial','COALESCE(pd.descripcion, vd.descripcion)',
                'pd.cantidad_pedido','pd.precio_unitario',$line];
            $search = $options['search'] ?? '';
            if ($search !== '') {
                $where .= " AND CONCAT_WS(' ', " . implode(', ', $columns) . ") LIKE ? ESCAPE '!'";
                $params[] = '%' . str_replace(['!','%','_'], ['!!','!%','!_'], $search) . '%';
            }
            $order = $columns[(int)($options['order_column'] ?? 2)] ?? $columns[2];
            $direction = ($options['order_dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
            $count = $this->consultar("SELECT COUNT(*) AS total $detailBase WHERE $where", $params);
            $rows = $this->consultar("SELECT pc.id AS pedido_id, pc.venta_id, v.proyecto_id, pc.num_orden_compra,
                pc.fecha_pedido, $currency AS moneda, vd.tipo_partida,
                mat.ccvematerial AS clave, mat.ccn, pd.ccvematerial AS codigo_cliente,
                COALESCE(pd.descripcion, vd.descripcion) AS descripcion,
                pd.cantidad_pedido, pd.precio_unitario, $line AS subtotal_partida
                $detailBase WHERE $where ORDER BY $order $direction, pc.id ASC, pd.id ASC LIMIT $length OFFSET $offset", $params);
            return ['data'=>$rows,'recordsFiltered'=>(int)($count[0]['total'] ?? 0)];
        }
        if (!in_array($section, ['clientes','vendedores'], true)) throw new InvalidArgumentException('Seccion no valida.');
        $entity = $section === 'clientes' ? 'pc.cliente_id' : 'v.ccveusuario_vendedor';
        $name = $section === 'clientes' ? "COALESCE(c.nombre_comercial,'Sin cliente')"
            : "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)),''),'Sin vendedor')";
        $joins = $base . ($section === 'clientes' ? ' LEFT JOIN cat_clientes c ON c.id = pc.cliente_id'
            : ' LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor');
        $group = "$entity, pc.moneda_id";
        $countSql = "SELECT COUNT(*) AS total FROM (SELECT $entity, pc.moneda_id $joins WHERE $where GROUP BY $group) grupos";
        $totalRows = $this->consultar($countSql, $params);
        $filteredWhere = $where;
        $filteredParams = $params;
        $search = $options['search'] ?? '';
        if ($search !== '') {
            $filteredWhere .= " AND ($name LIKE ? ESCAPE '!' OR $currency LIKE ? ESCAPE '!')";
            $pattern = '%' . str_replace(['!','%','_'], ['!!','!%','!_'], $search) . '%';
            array_push($filteredParams, $pattern, $pattern);
        }
        $filtered = $search === '' ? $totalRows : $this->consultar("SELECT COUNT(*) AS total FROM (
            SELECT $entity, pc.moneda_id $joins WHERE $filteredWhere GROUP BY $group) grupos", $filteredParams);
        $order = [0=>'nombre',1=>'moneda',2=>'total'][(int)($options['order_column'] ?? 2)] ?? 'total';
        $direction = ($options['order_dir'] ?? 'desc') === 'desc' ? 'DESC' : 'ASC';
        $length = max(5,min(100,(int)($options['length'] ?? 10)));
        $offset = max(0,min(1000000,(int)($options['start'] ?? 0)));
        $rows = $this->consultar("SELECT $entity AS entidad_id, MAX($name) AS nombre, pc.moneda_id,
            $currency AS moneda, $total AS total, $product AS productos, $service AS servicios, COUNT(DISTINCT pc.id) AS pedidos
            $joins WHERE $filteredWhere GROUP BY $group
            ORDER BY CASE pc.moneda_id WHEN 3 THEN 0 WHEN 1 THEN 1 ELSE 2 END ASC,
                $order $direction, entidad_id ASC, pc.moneda_id ASC
            LIMIT $length OFFSET $offset", $filteredParams);
        return ['draw'=>(int)($options['draw'] ?? 1),'recordsTotal'=>(int)($totalRows[0]['total'] ?? 0),
            'recordsFiltered'=>(int)($filtered[0]['total'] ?? 0),'data'=>$rows];
    }

    /** Totales del comparativo, con la misma conversion y redondeo por proyecto. */
    public static function resumir(array $headers, float $divisor): array
    {
        $result = ['cotizado'=>0.0, 'colocado'=>0.0, 'proyectos'=>0,
            'cotizaciones_enviadas'=>0, 'proyectos_cotizados'=>0, 'proyectos_colocados'=>0];
        $unique = [];
        $sellerAmounts = [];
        foreach ($headers as $row) {
            $type = $row['tipo'];
            $factor = (int)$row['moneda_id'] === 1 ? 1 / $divisor : 1;
            $amount = round((float)$row['monto'] * $factor, 2);
            $result[$type] += $amount;
            $sellerId = (string)($row['vendedor_id'] ?? '');
            if (!isset($sellerAmounts[$sellerId])) {
                $sellerAmounts[$sellerId] = ['vendedor_id'=>$sellerId,'nombre'=>$row['vendedor'] ?? 'Sin vendedor',
                    'importe_cotizado'=>0.0,'importe_colocado'=>0.0];
            }
            $sellerAmounts[$sellerId]['importe_'.$type] += $amount;
            $result['proyectos_'.$type.'s']++;
            if ($type === 'cotizado') $result['cotizaciones_enviadas'] += (int)($row['cotizaciones'] ?? 0);
            $unique[(string)$row['id']] = true;
        }
        $result['proyectos'] = count($unique);
        $result['importes_por_vendedor'] = array_values($sellerAmounts);
        return $result;
    }
}
