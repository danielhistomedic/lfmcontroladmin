<?php

/** Reporte mensual: reglas de MOSTRAR_ESTATUS_PEDIDOS en frmRegistrarProyecto. */
class ReportesmensualesModel extends Mysql
{
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
        $where = $alcance === '' ? '' : 'WHERE v.ccveusuario_vendedor = ?';
        return $this->consultar("SELECT v.ccveusuario_vendedor AS id,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS nombre
            FROM tb_ventas v LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            $where GROUP BY v.ccveusuario_vendedor, m.cNombre, m.cPriApellido, m.cSegApellido ORDER BY nombre",
            $alcance === '' ? [] : [$alcance]);
    }

    public function dashboard(int $year, int $month, string $seller): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = (new DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d');
        $scope = $seller === '' ? '' : ' AND v.ccveusuario_vendedor = ?';
        $params = $seller === '' ? [$start, $end] : [$start, $end, $seller];
        $rateRows = $this->consultar('SELECT valor, fecha FROM tb_historial_tipos_cambio WHERE idMoneda = 3 ORDER BY fecha DESC, id DESC LIMIT 1');
        $rate = (float)($rateRows[0]['valor'] ?? 0);
        $divisor = $rate == 0 ? 1.0 : $rate;
        $columns = "v.id, v.proyecto_id, v.ccveusuario_vendedor AS vendedor_id, v.moneda_id,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', m.cNombre, m.cPriApellido, m.cSegApellido)), ''), 'Sin vendedor') AS vendedor";
        // Cotizaciones enviadas: no se agrega un filtro ACTIVO ni de estatus de proyecto.
        $sent = $this->consultar("SELECT $columns, 'cotizado' AS tipo,
            SUM(COALESCE(cc.total, 0)) AS monto, COUNT(*) AS cotizaciones,
            COALESCE(MAX(cc.fecha), v.fecha_cotizacion, v.fecha) AS fecha
            FROM tb_ventas_cotizacion_cliente cc INNER JOIN tb_ventas v ON v.id = cc.venta_id
            LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            WHERE cc.enviado = 1 AND v.estatus_pedido_reporte IN (1,2)
            AND COALESCE(cc.fecha, v.fecha_cotizacion, v.fecha) >= ?
            AND COALESCE(cc.fecha, v.fecha_cotizacion, v.fecha) < ? $scope
            GROUP BY v.id, v.proyecto_id, v.ccveusuario_vendedor, v.moneda_id, v.fecha_cotizacion, v.fecha, m.cNombre, m.cPriApellido, m.cSegApellido", $params);
        // NOT EXISTS comprende todo el historial, no sólo el mes seleccionado.
        $fallback = $this->consultar("SELECT $columns, 'cotizado' AS tipo, COALESCE(v.total,0) AS monto,
            COALESCE(v.fecha_cotizacion,v.fecha) AS fecha
            FROM tb_ventas v LEFT JOIN cat_medico m ON m.ccvemedico = v.ccveusuario_vendedor
            WHERE COALESCE(v.activo,'ACTIVO') = 'ACTIVO' AND v.estatus_pedido_reporte IN (1,2)
            AND COALESCE(v.fecha_cotizacion,v.fecha) >= ? AND COALESCE(v.fecha_cotizacion,v.fecha) < ? $scope
            AND NOT EXISTS (SELECT 1 FROM tb_ventas_cotizacion_cliente cc WHERE cc.venta_id=v.id AND cc.enviado=1)", $params);
        // Una fila por proyecto como en el listado de escritorio; los pedidos sólo determinan pertenencia al período.
        $placed = $this->consultar("SELECT $columns, 'colocado' AS tipo, COALESCE(v.total,0) AS monto,
            p.fecha AS fecha FROM tb_ventas v
            INNER JOIN (SELECT venta_id, MIN(fecha_pedido) AS fecha FROM tb_pedidos_cliente
                WHERE fecha_pedido >= ? AND fecha_pedido < ? GROUP BY venta_id) p ON p.venta_id=v.id
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
        $headers = array_merge($sent, $fallback, $placed);
        $lines = [];
        // Consultas por conjunto, sin N+1; sólo partidas de proyectos autorizados del mes.
        foreach (['cotizado' => $sent, 'colocado' => $placed] as $type => $rows) {
            if (!$rows) continue;
            $ids = array_column($rows, 'id');
            $marks = implode(',', array_fill(0, count($ids), '?'));
            if ($type === 'cotizado') {
                $sql = "SELECT cc.venta_id, vd.id AS partida_id, vd.subclasificacion_id, s.subclasificacion,
                    d.ccveunidad AS unidad, d.cantidad AS cantidad, COALESCE(d.importe,0) AS monto
                    FROM tb_ventas_cotizacion_cliente_detalle d
                    INNER JOIN tb_ventas_cotizacion_cliente cc ON cc.id=d.cotizacion_cliente_id
                    INNER JOIN tb_ventas v ON v.id=cc.venta_id
                    LEFT JOIN tb_ventas_detalle vd ON vd.id=d.venta_detalle_id_partida AND vd.venta_id=cc.venta_id
                    LEFT JOIN cat_subclasificacion_proyectos s ON s.id=vd.subclasificacion_id
                    WHERE cc.enviado=1 AND COALESCE(cc.fecha,v.fecha_cotizacion,v.fecha)>=?
                    AND COALESCE(cc.fecha,v.fecha_cotizacion,v.fecha)<? AND cc.venta_id IN ($marks)";
            } else {
                $sql = "SELECT pc.venta_id, vd.id AS partida_id, vd.subclasificacion_id, s.subclasificacion,
                    d.ccveunidad AS unidad, d.cantidad_pedido AS cantidad, COALESCE(d.importe,0) AS monto
                    FROM tb_pedidos_cliente_detalle d INNER JOIN tb_pedidos_cliente pc ON pc.id=d.pedido_id
                    LEFT JOIN tb_ventas_detalle vd ON vd.id=d.venta_detalle_id AND vd.venta_id=pc.venta_id
                    LEFT JOIN cat_subclasificacion_proyectos s ON s.id=vd.subclasificacion_id
                    WHERE pc.fecha_pedido>=? AND pc.fecha_pedido<? AND pc.venta_id IN ($marks)";
            }
            foreach ($this->consultar($sql, array_merge([$start, $end], $ids)) as $line) {
                $line['tipo'] = $type;
                $lines[] = $line;
            }
        }
        if ($fallback) {
            $ids = array_column($fallback, 'id');
            $marks = implode(',', array_fill(0, count($ids), '?'));
            foreach ($this->consultar("SELECT vd.venta_id, vd.id AS partida_id, vd.subclasificacion_id,
                s.subclasificacion, vd.ccveunidad AS unidad, vd.cantidad, 0 AS monto
                FROM tb_ventas_detalle vd LEFT JOIN cat_subclasificacion_proyectos s ON s.id=vd.subclasificacion_id
                WHERE vd.venta_id IN ($marks)", $ids) as $line) {
                $line['tipo'] = 'cotizado';
                $lines[] = $line;
            }
        }
        return self::resumir($headers, $lines, $divisor, (int)(new DateTimeImmutable($start))->format('t'))
            + ['tipo_cambio' => $rate, 'fecha_tipo_cambio' => $rateRows[0]['fecha'] ?? null];
    }

    public static function resumir(array $headers, array $lines, float $divisor, int $days): array
    {
        $result = ['cotizado' => 0.0, 'colocado' => 0.0, 'proyectos' => 0, 'cotizaciones_enviadas' => 0,
            'proyectos_cotizados' => 0, 'proyectos_colocados' => 0, 'vendedores' => [], 'productos' => [], 'cruce' => [], 'diario' => []];
        for ($day=1; $day<=$days; $day++) $result['diario'][] = ['dia'=>$day, 'cotizado'=>0.0, 'colocado'=>0.0];
        $index = []; $unique = []; $sums = [];
        foreach ($headers as $h) {
            $type = $h['tipo']; $id = (string)$h['id']; $seller = (string)$h['vendedor_id'];
            $factor = (int)$h['moneda_id'] === 1 ? 1 / $divisor : 1;
            $h['factor'] = $factor; $h['usd'] = round((float)$h['monto'] * $factor, 2);
            $index[$type][$id] = $h; $unique[$id] = true;
            $result[$type] += $h['usd']; $result['proyectos_'.$type.'s']++;
            if ($type === 'cotizado') $result['cotizaciones_enviadas'] += (int)($h['cotizaciones'] ?? 0);
            if (!isset($result['vendedores'][$seller])) $result['vendedores'][$seller] = ['nombre'=>$h['vendedor'], 'cotizado'=>0.0, 'colocado'=>0.0];
            $result['vendedores'][$seller][$type] += $h['usd'];
            $day = (int)substr((string)$h['fecha'],8,2);
            if ($day>=1 && $day<=$days) $result['diario'][$day-1][$type] += $h['usd'];
        }
        foreach ($lines as $line) {
            $type = $line['tipo']; $id = (string)$line['venta_id'];
            if (!isset($index[$type][$id])) continue;
            $h = $index[$type][$id];
            $amount = (float)$line['monto'] * $h['factor'];
            $sums[$type][$id] = ($sums[$type][$id] ?? 0) + $amount;
            self::agregarPartida($result, $h, $line, $amount);
        }
        foreach ($index as $type => $rows) foreach ($rows as $id => $h) {
            $difference = $h['usd'] - ($sums[$type][$id] ?? 0);
            if (abs($difference) > 0.000001) self::agregarPartida($result, $h,
                ['subclasificacion_id'=>'conciliacion', 'subclasificacion'=>'Sin desglose / diferencia con total de proyecto', 'partida_id'=>null, 'cantidad'=>0, 'unidad'=>''], $difference);
        }
        $result['proyectos'] = count($unique);
        foreach (['productos','cruce'] as $section) {
            foreach ($result[$section] as &$row) {
                $row['partidas'] = count($row['partidas']);
                $row['unidades_cotizadas'] = self::unidades($row['unidades_cotizadas']);
                $row['unidades_vendidas'] = self::unidades($row['unidades_vendidas']);
                $row['participacion'] = $result['colocado'] != 0 ? $row['colocado'] / $result['colocado'] * 100 : 0;
            }
            unset($row);
            $result[$section] = array_values($result[$section]);
        }
        $result['vendedores'] = array_values($result['vendedores']);
        return $result;
    }

    private static function agregarPartida(array &$result, array $h, array $line, float $amount): void
    {
        $sub = (string)($line['subclasificacion_id'] ?? 'sin');
        $name = $line['subclasificacion'] ?: 'Sin subclasificación';
        foreach (['productos'=>$sub, 'cruce'=>json_encode([(string)$h['vendedor_id'],$sub])] as $section=>$key) {
            if (!isset($result[$section][$key])) $result[$section][$key] = ['nombre'=>$name, 'subclasificacion_id'=>$sub,
                'vendedor'=>$h['vendedor'], 'vendedor_id'=>(string)$h['vendedor_id'], 'partidas'=>[],
                'unidades_cotizadas'=>[], 'unidades_vendidas'=>[], 'cotizado'=>0.0, 'colocado'=>0.0];
            $row = &$result[$section][$key];
            $row[$h['tipo']] += $amount;
            if ($line['partida_id'] !== null) $row['partidas'][(string)$line['partida_id']] = true;
            $unit = trim((string)$line['unidad']) ?: 'Sin unidad';
            $field = $h['tipo'] === 'colocado' ? 'unidades_vendidas' : 'unidades_cotizadas';
            if ((float)$line['cantidad'] != 0) $row[$field][$unit] = ($row[$field][$unit] ?? 0) + (float)$line['cantidad'];
            unset($row);
        }
    }

    private static function unidades(array $units): string
    {
        ksort($units); $labels=[];
        foreach ($units as $unit=>$quantity) $labels[] = rtrim(rtrim(number_format($quantity,4,'.',','),'0'),'.').' '.$unit;
        return implode(' · ', $labels) ?: '—';
    }
}
