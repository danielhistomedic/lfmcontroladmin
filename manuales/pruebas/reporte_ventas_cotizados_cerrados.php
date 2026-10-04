<?php
/** PHP CLI: SQL con datos en memoria; sin credenciales ni conexiones externas. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start(); require __DIR__.'/reporte_ventas_mensuales.php'; ob_end_clean();
$memory = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$memory->sqliteCreateFunction('CONCAT_WS', static fn($separator, ...$parts) => implode($separator, array_filter($parts, static fn($value) => $value !== null)));
$memory->exec('CREATE TABLE tb_ventas (id INTEGER PRIMARY KEY, proyecto_id TEXT, fecha TEXT, fecha_cotizacion TEXT, clasificacion_proyecto_id INTEGER, estatus_proyecto_id INTEGER, activo TEXT, ccveusuario_vendedor TEXT, moneda_id INTEGER, estatus_pedido_reporte INTEGER)');
$memory->exec('CREATE TABLE tb_ventas_cotizacion_cliente (id INTEGER PRIMARY KEY, venta_id INTEGER, fecha TEXT, enviado INTEGER, cotizacion_interna_id INTEGER)');
$memory->exec('CREATE TABLE tb_pedidos_cliente (id INTEGER, venta_id INTEGER, enviado INTEGER, fecha_pedido TEXT)');
$memory->exec('CREATE TABLE tb_compras_cotizacion_interna (id INTEGER, venta_id INTEGER, enviado INTEGER)');
$memory->exec('CREATE TABLE cat_medico (ccvemedico TEXT, cNombre TEXT, cPriApellido TEXT, cSegApellido TEXT)');
$memory->exec('CREATE TABLE cat_clasificacion_proyectos (id INTEGER PRIMARY KEY, clasificacion TEXT)');
$memory->exec("INSERT INTO cat_clasificacion_proyectos VALUES (2,'DIVERSOS'),(3,'BOMBAS FLOWSERVE'),(4,'VALVULAS FLOWSERVE'),(5,'SELLOS FLOWSERVE')");
$memory->exec('CREATE TABLE tb_ventas_detalle (id INTEGER, tipo_partida TEXT)');
$memory->exec('CREATE TABLE tb_ventas_cotizacion_cliente_detalle (cotizacion_cliente_id INTEGER, venta_detalle_id_partida INTEGER, cantidad REAL, precio_unitario REAL)');
$project = $memory->prepare('INSERT INTO tb_ventas VALUES (?, ?, ?, NULL, ?, ?, ?, ?, 3, 1)');
$quote = $memory->prepare('INSERT INTO tb_ventas_cotizacion_cliente VALUES (?, ?, ?, ?, NULL)');
$detail = $memory->prepare('INSERT INTO tb_ventas_cotizacion_cliente_detalle VALUES (?, 1, 1, ?)');
$memory->exec("INSERT INTO tb_ventas_detalle VALUES (1, 'PRODUCTO')");
foreach ([
    [1,'2024-02-10',2,3,'CERRADO','V1',1],
    [2,'2024-01-10',2,3,'CERRADO','V1',1],
    [3,'2024-02-10',2,2,'CERRADO','V1',1],
    [4,'2024-02-10',2,null,null,'V1',1],
    [5,'2024-02-10',2,3,'CERRADO','V2',1],
    [6,'2024-02-10',2,3,'CERRADO','V1',0],
    [7,'2024-02-10',1,3,'CERRADO','V1',1],
] as [$id,$date,$class,$status,$active,$seller,$sent]) {
    $project->execute([$id,'P'.$id,$date,$class,$status,$active,$seller]);
    $quote->execute([$id,$id,'2024-02-15',$sent]);
    $detail->execute([$id,$id*100]);
}
// Una segunda cotizacion no duplica el proyecto; su importe si corresponde a otra partida enviada.
$quote->execute([8,1,'2024-02-16',1]);
$detail->execute([8,50]);
$capture = new ConexionSimulada($currencyResponses);
(new ModeloSimulado($capture))->dashboard(2024,2,'V1');
$execute = static function(array $call) use ($memory): array {
    [$sql,$params] = $call;
    $statement = $memory->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
};
$counts = $execute($capture->calls[3])[0];
verificar((int)$counts['cotizacion_cliente']===2, 'Cotizados actuales incluyen cerrado y NULL, excluyen estatus 2, otra clase, vendedor y no enviados');
verificar((int)$execute($capture->calls[8])[0]['cotizacion_cliente_anteriores']===1, 'Cotizados anteriores incluyen cerrados sin duplicar proyectos');
$amounts = $execute($capture->calls[1]);
cerca(array_sum(array_column($amounts,'monto')),750, 'Importe y vendedor incluyen exactamente las partidas cotizadas elegibles');
$localCounts=$execute($capture->calls[16]);
$localRows=ReportesmensualesModel::resumenClasificacionLocal($localCounts,$amounts,1);
verificar(count($localRows)===1 && $localRows[0]['clasificacion_id']===2 && $localRows[0]['cotizado']===3,
    'Cantidad local incluye actuales y anteriores sin duplicar cotizaciones');
cerca($localRows[0]['importe_cotizado'],750,'Importe local concilia con general');
cerca($localRows[0]['cotizado_usd'],750,'Desglose original conserva la misma moneda');
verificar(count($execute($capture->calls[17]))===4,'Botones usan IDs y descripciones del catalogo existente');
$annualCounts = $execute($capture->calls[13]);
$february = array_values(array_filter($annualCounts,static fn($row)=>(int)$row['mes']===2 && $row['tipo']==='cotizado'));
verificar(count($annualCounts)===1 && (int)$february[0]['proyectos']===3,
    'SQL anual ejecutado: mismas cantidades mensuales del KPI y dos tipos por cada mes');
$memory->sqliteCreateFunction('YEAR',static fn($date)=>(int)substr($date,0,4));
$memory->sqliteCreateFunction('MONTH',static fn($date)=>(int)substr($date,5,2));
$annualAmounts = $execute($capture->calls[14]);
cerca(array_sum(array_column($annualAmounts,'monto')),750,'SQL monetario anual conserva las partidas y no multiplica importes');
$builder = new ReflectionMethod(ReportesmensualesModel::class, 'documentadosPeriodoSql');
$quoted = $execute($builder->invoke(null,2024,2,'V1','cotizados'));
verificar(array_column($quoted,'id')===[1,2,4], 'Conjunto de grafica, vendedor y detalle cotizado incluye cerrados una sola vez');
[$placedSql] = $builder->invoke(null,2024,2,'V1','colocados');
verificar(str_contains($placedSql,"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'"), 'Filtro de colocados permanece intacto');
echo "OK: cerrados incluidos en cantidades, importes y desglose cotizado; estatus 2 excluido.\n";
