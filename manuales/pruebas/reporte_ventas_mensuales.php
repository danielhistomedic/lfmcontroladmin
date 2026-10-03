<?php
/** Ejecutar con PHP CLI. No carga bootstrap, credenciales ni conexiones reales. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
class Mysql {}
require_once __DIR__.'/../../Models/ReportesmensualesModel.php';

function verificar(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}
function cerca(float $actual, float $expected, string $message): void
{
    verificar(abs($actual-$expected)<0.00001, $message);
}
$headers = [
    ['id'=>1,'proyecto_id'=>'A','vendedor_id'=>'V1','vendedor'=>'José','moneda_id'=>1,'tipo'=>'cotizado','monto'=>2000,'fecha'=>'2024-02-10','cotizaciones'=>2],
    ['id'=>1,'proyecto_id'=>'A','vendedor_id'=>'V1','vendedor'=>'José','moneda_id'=>1,'tipo'=>'colocado','monto'=>1600,'fecha'=>'2024-02-20'],
    ['id'=>2,'proyecto_id'=>'B','vendedor_id'=>'V2','vendedor'=>'Ana','moneda_id'=>3,'tipo'=>'cotizado','monto'=>30,'fecha'=>'2024-02-29'],
];
$report=ReportesmensualesModel::resumir($headers,20);
cerca($report['cotizado'],130,'Total cotizado convertido');
cerca($report['colocado'],80,'Total colocado sin duplicar pedidos');
verificar($report['importes_por_vendedor'] === [
    ['vendedor_id'=>'V1','nombre'=>'José','importe_cotizado'=>100.0,'importe_colocado'=>80.0],
    ['vendedor_id'=>'V2','nombre'=>'Ana','importe_cotizado'=>30.0,'importe_colocado'=>0.0]
], 'Importes por vendedor con la misma conversion y redondeo del total');
cerca(array_sum(array_column($report['importes_por_vendedor'],'importe_cotizado')),$report['cotizado'],'Conciliar importe cotizado por vendedor');
cerca(array_sum(array_column($report['importes_por_vendedor'],'importe_colocado')),$report['colocado'],'Conciliar importe colocado por vendedor');
verificar($report['proyectos']===2 && $report['proyectos_cotizados']===2 && $report['proyectos_colocados']===1,'Conteos por proyecto');
verificar($report['cotizaciones_enviadas']===2,'Documentos enviados');
verificar(ReportesmensualesModel::resumir([],1)['proyectos']===0,'Mes vacio');

// Simula la conexión central y verifica parámetros y ramas sin conectarse a MySQL.
class ConexionSimulada
{
    public array $calls=[];
    public function __construct(public array $responses) {}
    public function prepare($sql) {
        $owner=$this;
        return new class($owner,$sql) {
            public function __construct(private $owner,private $sql) {}
            public function execute($params) {
                verificar(substr_count($this->sql,'?')===count($params),'Todos los valores SQL están parametrizados');
                $this->owner->calls[]=[$this->sql,$params]; return true;
            }
            public function fetchAll($mode) { return array_shift($this->owner->responses); }
        };
    }
}
class ModeloSimulado extends ReportesmensualesModel
{
    public function __construct(public ConexionSimulada $fake) {}
    public function getConexion() { return $this->fake; }
}
$quantities=['total_proyectos'=>10,'declinados'=>2,'cotizacion_cliente'=>6,'orden_compra_cliente'=>3,'interna_sin_cliente'=>4];
$db=new ConexionSimulada([[['valor'=>0,'fecha'=>'2024-02-01']],[$headers[0]],[$headers[1]],[$quantities],[['vendedor_id'=>'V1','nombre'=>'Vendedor','proyectos'=>'10']],[['vendedor_id'=>'V1','clasificacion_id'=>3,'clasificacion'=>'Bombas','proyectos'=>'10','declinados'=>'2']],[['vendedor_id'=>'V1','estatus_id'=>6,'estatus'=>'Pedido','proyectos'=>'10','declinados'=>'2']],[['clasificacion_id'=>3,'estatus_id'=>6,'estatus'=>'Pedido','proyectos'=>'10','declinados'=>'2']],[['cotizacion_cliente_anteriores'=>'8']],[['orden_compra_cliente_anteriores'=>2]],[['declinados_anteriores'=>3]],[],[]]);
$model=new ModeloSimulado($db);
$actual=$model->dashboard(2024,2,"V'1");
verificar($actual['proyectos_por_vendedor'][0]['proyectos']===10, 'Cantidad entera por vendedor');
verificar($db->calls[4][1]===['2024-02-01','2024-03-01',"V'1"] && str_contains($db->calls[4][0],'COUNT(*) AS proyectos') && !str_contains($db->calls[4][0],'activo'), 'Proyectos del mes por vendedor incluyen todos los estados');
verificar(count($db->calls)===13,'Consultas por conjunto');
foreach ([11,12] as $index) {
    verificar(str_contains($db->calls[$index][0], 'FROM cat_medico GROUP BY ccvemedico) nombres')
        && str_contains($db->calls[$index][0], "COALESCE(MAX(nombres.nombre), 'Sin vendedor') AS nombre"),
        'Desglose financiero reutiliza conteos y nombres sin multiplicar proyectos ni consultas');
}
verificar(str_contains($db->calls[12][0],'MIN(pc.fecha_pedido) AS fecha')
    && str_contains($db->calls[12][0],'pc.enviado = 1')
    && str_contains($db->calls[12][0],"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'")
    && str_contains($db->calls[12][0],'v.estatus_proyecto_id <> 2')
    && str_contains($db->calls[12][0],"THEN 'periodo' ELSE 'anteriores' END AS origen")
    && $db->calls[12][1]===$db->calls[11][1], 'Barra colocados usa los mismos grupos, filtros y exclusiones del KPI');
$placedListDb = new ConexionSimulada([[['total'=>5]],[['total'=>5]],[]]);
(new ModeloSimulado($placedListDb))->declinadosTabla(2026,9,'V1',
    ['draw'=>1,'start'=>0,'length'=>10,'order_column'=>2,'order_dir'=>'desc','search'=>'','filters'=>[],
        'periodo_anios'=>[2026],'periodo_meses'=>[9,10]], 'colocados_periodo');
foreach ($placedListDb->calls as [$sql,$params]) verificar(str_contains($sql,'cotizados.fecha_reporte >= ?')
    && str_contains($sql,'MIN(pc.fecha_pedido) AS fecha') && str_contains($sql,'pc.enviado = 1')
    && str_contains($sql,'v.ccveusuario_vendedor = ?') && array_slice($params,-2)===['2026-09-01','2026-10-01'],
    'Listado de colocados coincide con su barra y conserva el alcance autorizado');
verificar(str_contains($db->calls[11][0],'MIN(cc.fecha) AS fecha')
    && str_contains($db->calls[11][0],'cc.enviado = 1')
    && str_contains($db->calls[11][0],"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'")
    && str_contains($db->calls[11][0],'v.estatus_proyecto_id <> 2')
    && str_contains($db->calls[11][0],"THEN 'periodo' ELSE 'anteriores' END AS origen"),
    'Grafica cotizada reutiliza ambos grupos del KPI y asigna anteriores una sola vez');
verificar($db->calls[11][1]===['2024-02-01','2024-03-01','2024-02-01','2024-03-01',
    '2024-02-01','2024-03-01','2024-02-01','2024-03-01',"V'1"], 'Fechas y vendedor parametrizados para el conjunto cotizado');
$quotedListDb = new ConexionSimulada([[['total'=>5]],[['total'=>5]],[]]);
(new ModeloSimulado($quotedListDb))->declinadosTabla(2026,9,'V1',
    ['draw'=>1,'start'=>0,'length'=>10,'order_column'=>2,'order_dir'=>'desc','search'=>'','filters'=>[],
        'periodo_anios'=>[2026],'periodo_meses'=>[9,10]], 'cotizados_periodo');
foreach ($quotedListDb->calls as [$sql,$params]) verificar(str_contains($sql,'cotizados.fecha_reporte >= ?')
    && str_contains($sql,'MIN(cc.fecha) AS fecha') && str_contains($sql,'v.ccveusuario_vendedor = ?')
    && array_slice($params,-2)===['2026-09-01','2026-10-01'], 'Listado de la barra usa su mes dentro del conjunto global autorizado');
[$previousDeclineSql,$previousDeclineParams] = $db->calls[10];
verificar($previousDeclineParams===['2024-02-01','2024-03-01','2024-02-01','2024-03-01',"V'1"],
    'Declinados anteriores conservan el periodo y vendedor autorizado');
verificar(str_contains($previousDeclineSql,'COUNT(DISTINCT v.id) AS declinados_anteriores')
    && str_contains($previousDeclineSql,'NOT COALESCE((v.fecha >= ? AND v.fecha < ?), 0)')
    && str_contains($previousDeclineSql,'v.fecha_declina >= ? AND v.fecha_declina < ?')
    && str_contains($previousDeclineSql,"v.activo = 'CERRADO'")
    && str_contains($previousDeclineSql,'v.estatus_proyecto_id <> 2')
    && str_contains($previousDeclineSql,'v.ccveusuario_vendedor = ?')
    && !str_contains($previousDeclineSql,'JOIN'), 'Proyectos anteriores declinados segun fecha_declina, sin duplicar los registrados en el periodo');
[$previousOrderSql,$previousOrderParams] = $db->calls[9];
verificar($previousOrderParams===['2024-02-01','2024-03-01','2024-02-01','2024-03-01',"V'1"],
    'Proyectos anteriores colocados usan el mismo periodo y vendedor');
verificar(str_contains($previousOrderSql,'COUNT(DISTINCT v.id)')
    && str_contains($previousOrderSql,'NOT COALESCE((v.fecha >= ? AND v.fecha < ?), 0)')
    && str_contains($previousOrderSql,'AND EXISTS (SELECT 1 FROM tb_pedidos_cliente pc')
    && str_contains($previousOrderSql,'pc.enviado = 1 AND (pc.fecha_pedido >= ? AND pc.fecha_pedido < ?)')
    && !str_contains($previousOrderSql,'JOIN'), 'Contar cada proyecto anterior una vez, por pedido enviado dentro del periodo');
foreach ([$db->calls[3][0],$previousOrderSql] as $orderCountSql) {
    verificar(str_contains($orderCountSql,"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'")
        && str_contains($orderCountSql,'v.estatus_proyecto_id <> 2')
        && str_contains($orderCountSql,'v.ccveusuario_vendedor = ?'),
        'Ambos grupos colocados excluyen cerrados y estatus 2 y conservan el vendedor autorizado');
}
[$previousSql,$previousParams] = $db->calls[8];
verificar($previousParams===['2024-02-01','2024-03-01','2024-02-01','2024-03-01',"V'1"],
    'Aplicar periodo de proyecto, fecha de cotizacion y vendedor con parametros propios');
verificar(str_contains($previousSql,'COUNT(DISTINCT v.id)') && str_contains($previousSql,'AND EXISTS (SELECT 1 FROM tb_ventas_cotizacion_cliente cc')
    && !str_contains($previousSql,'JOIN'), 'Multiples cotizaciones no multiplican los proyectos anteriores');
verificar(str_contains($previousSql,'NOT COALESCE((v.fecha >= ? AND v.fecha < ?), 0)')
    && str_contains($previousSql,'cc.enviado = 1 AND (cc.fecha >= ? AND cc.fecha < ?)'),
    'Excluir todos los proyectos del periodo y usar exclusivamente la fecha de cotizacion enviada');
verificar(str_contains($previousSql,"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'")
    && str_contains($previousSql,'v.ccveusuario_vendedor = ?')
    && str_contains($previousSql,'v.clasificacion_proyecto_id IN (2,3,4,5)')
    && !str_contains($previousSql,'fecha_cotizacion') && !str_contains($previousSql,'estatus_pedido_reporte'),
    'Reutilizar las reglas del KPI, sin imponer las reglas distintas del calculo de importes');
verificar(str_contains($db->calls[1][0], 'SUM(cd.cantidad * cd.precio_unitario) AS subtotal_partidas')
    && !str_contains($db->calls[1][0], 'cc.total'), 'Cotizado usa subtotal sin IVA');
verificar(str_contains($db->calls[2][0], 'pd.cantidad_pedido * pd.precio_unitario')
    && !str_contains($db->calls[2][0], 'v.total'), 'Colocado usa subtotal del proyecto sin IVA');
verificar(str_contains($db->calls[7][0], 'YEAR(v.fecha) AS anio')
    && str_contains($db->calls[7][0], 'MONTH(v.fecha) AS mes')
    && str_contains($db->calls[7][0], 'GROUP BY v.clasificacion_proyecto_id, YEAR(v.fecha), MONTH(v.fecha)'),
    'Separar cantidades por clasificacion, anio, mes y estatus sin nuevas consultas');

// Meses no consecutivos, sin duplicar meses ni proyectos al agregar el periodo.
$multiDb = new ConexionSimulada([[['valor'=>20]], [], [], [$quantities], [], [], [], [], [['cotizacion_cliente_anteriores'=>0]],[['orden_compra_cliente_anteriores'=>0]],[['declinados_anteriores'=>0]],[],[]]);
(new ModeloSimulado($multiDb))->dashboard(2024, [12,2,9,2], 'V1');
$multiParams = ['2024-02-01','2024-03-01','2024-09-01','2024-10-01','2024-12-01','2025-01-01','V1'];
foreach (array_slice($multiDb->calls, 1, 6) as [$sql,$params]) {
    verificar($params === $multiParams, 'Todos los indicadores usan solo los meses seleccionados y el vendedor');
    verificar(substr_count($sql, ' OR ') >= 2, 'Rangos de meses separados');
    verificar(str_contains($sql, 'v.clasificacion_proyecto_id IN (2,3,4,5)'), 'Conservar clasificaciones globales');
    verificar(str_contains($sql, 'v.estatus_proyecto_id <> 2'), 'Conservar exclusion de estatus');
}
verificar($multiDb->calls[8][1] === [...array_slice($multiParams,0,-1),...array_slice($multiParams,0,-1),'V1'],
    'Proyectos anteriores excluyen la union de todos los meses seleccionados');
verificar($multiDb->calls[9][1] === $multiDb->calls[8][1], 'Colocados conservan todos los meses no consecutivos');
verificar($multiDb->calls[10][1] === $multiDb->calls[8][1], 'Declinados conservan todos los meses no consecutivos');
$multiListDb = new ConexionSimulada([[['total'=>0]], [['total'=>0]], []]);
(new ModeloSimulado($multiListDb))->declinadosTabla(2024, [2,9,12], 'V1',
    ['draw'=>1,'start'=>0,'length'=>10,'order_column'=>2,'order_dir'=>'desc','search'=>'','filters'=>[]]);
foreach ($multiListDb->calls as [$sql,$params]) verificar($params === [...array_slice($multiParams,0,-1),...array_slice($multiParams,0,-1),'V1'], 'Modal y total mantienen la seleccion multiple');

$yearsDb = new ConexionSimulada([[['valor'=>20]], [], [], [$quantities], [], [], [], [], [['cotizacion_cliente_anteriores'=>0]],[['orden_compra_cliente_anteriores'=>0]],[['declinados_anteriores'=>0]],[],[]]);
(new ModeloSimulado($yearsDb))->dashboard([2026,2024,2026], [2,12], 'V1');
$yearsParams = ['2024-02-01','2024-03-01','2024-12-01','2025-01-01',
    '2026-02-01','2026-03-01','2026-12-01','2027-01-01','V1'];
foreach (array_slice($yearsDb->calls,1,6) as [$sql,$params]) {
    verificar($params === $yearsParams, 'Combinar solo los anios y meses seleccionados sin duplicados');
    verificar(str_contains($sql, ' OR ') && str_contains($sql,'v.estatus_proyecto_id <> 2'), 'Conservar filtros sobre todos los rangos');
}
verificar($yearsDb->calls[8][1] === [...array_slice($yearsParams,0,-1),...array_slice($yearsParams,0,-1),'V1'],
    'Aplicar todos los anios seleccionados al complemento de cotizaciones');
verificar($yearsDb->calls[9][1] === $yearsDb->calls[8][1], 'Colocados conservan todos los anios seleccionados');
verificar($yearsDb->calls[10][1] === $yearsDb->calls[8][1], 'Declinados conservan todos los anios seleccionados');
$compositionDb = new ConexionSimulada([[],[],[],[array_replace($quantities,['cotizacion_cliente'=>46])],[],[],[],[],[['cotizacion_cliente_anteriores'=>8]],[['orden_compra_cliente_anteriores'=>2]],[['declinados_anteriores'=>3]],[],[]]);
$composition = (new ModeloSimulado($compositionDb))->dashboard(2024,2,'');
verificar($composition['cantidades']['cotizacion_cliente']===54
    && $composition['cantidades']['cotizacion_cliente_periodo']===46
    && $composition['cantidades']['cotizacion_cliente_anteriores']===8, 'Total principal es la suma exacta de ambos grupos');
verificar(!str_contains($compositionDb->calls[8][0],'v.ccveusuario_vendedor = ?')
    && count($compositionDb->calls[8][1])===4,'TODOS conserva el alcance global sin vendedor ficticio');
verificar($composition['cantidades']['orden_compra_cliente']===5
    && $composition['cantidades']['orden_compra_cliente_periodo']===3
    && $composition['cantidades']['orden_compra_cliente_anteriores']===2, 'Colocados suman ambos grupos sin alterar el conteo original');
verificar(!str_contains($compositionDb->calls[9][0],'v.ccveusuario_vendedor = ?')
    && count($compositionDb->calls[9][1])===4, 'TODOS aplica tambien a los proyectos anteriores colocados');
verificar($composition['cantidades']['declinados']===5
    && $composition['cantidades']['declinados_periodo']===2
    && $composition['cantidades']['declinados_anteriores']===3, 'Declinados suman los dos conjuntos disjuntos');
verificar(!str_contains($compositionDb->calls[10][0],'v.ccveusuario_vendedor = ?')
    && count($compositionDb->calls[10][1])===4, 'Declinados anteriores permiten el filtro TODOS');
$emptyQuantities = array_fill_keys(array_keys($quantities),0);
$emptyDb = new ConexionSimulada([[],[],[],[$emptyQuantities],[],[],[],[],[['cotizacion_cliente_anteriores'=>0]],[['orden_compra_cliente_anteriores'=>0]],[['declinados_anteriores'=>0]],[],[]]);
$emptyDashboard = (new ModeloSimulado($emptyDb))->dashboard(2024,2,'');
verificar($emptyDashboard['cantidades']['cotizacion_cliente']===0
    && $emptyDashboard['cantidades']['cotizacion_cliente_periodo']===0
    && $emptyDashboard['cantidades']['cotizacion_cliente_anteriores']===0,'Ambos componentes se muestran como cero en un periodo vacio');
verificar($emptyDashboard['cantidades']['orden_compra_cliente']===0
    && $emptyDashboard['cantidades']['orden_compra_cliente_periodo']===0
    && $emptyDashboard['cantidades']['orden_compra_cliente_anteriores']===0, 'Colocados vacios muestran cero en ambos componentes');
verificar($emptyDashboard['cantidades']['declinados']===0
    && $emptyDashboard['cantidades']['declinados_periodo']===0
    && $emptyDashboard['cantidades']['declinados_anteriores']===0, 'Declinados vacios muestran cero en ambos componentes');
$yearsListDb = new ConexionSimulada([[['total'=>0]], [['total'=>0]], []]);
(new ModeloSimulado($yearsListDb))->declinadosTabla([2024,2026], [2,12], 'V1',
    ['draw'=>1,'start'=>0,'length'=>10,'order_column'=>2,'order_dir'=>'desc','search'=>'','filters'=>[]]);
foreach ($yearsListDb->calls as [$sql,$params]) verificar($params === [...array_slice($yearsParams,0,-1),...array_slice($yearsParams,0,-1),'V1'], 'Modal conserva multiples anios y meses');
foreach ([1,2,3,4,5,6,7] as $queryIndex) {
    verificar(str_contains($db->calls[$queryIndex][0], 'v.clasificacion_proyecto_id IN (2,3,4,5) AND (v.estatus_proyecto_id IS NULL OR v.estatus_proyecto_id <> 2)'),
        'Filtro global en cotizados, colocados, cantidades y proyectos por vendedor');
}
$sellerDb = new ConexionSimulada([[],[]]);
$sellerModel = new ModeloSimulado($sellerDb);
$sellerModel->vendedores('V1');
$sellerModel->proyectoDeclinadoAutorizado(633,'V1');
foreach ($sellerDb->calls as [$sql]) verificar(str_contains($sql,'v.clasificacion_proyecto_id IN (2,3,4,5) AND (v.estatus_proyecto_id IS NULL OR v.estatus_proyecto_id <> 2)'), 'Clasificaciones en vendedores e historial declinado');
verificar($db->calls[1][1]===['2024-02-01','2024-03-01',"V'1"],'Límites del mes y vendedor parametrizado');
verificar(str_contains($db->calls[1][0], "COALESCE(v.activo,'ACTIVO') <> 'CERRADO'"), 'Excluir declinados de importes cotizados conservando activo NULL');
verificar(str_contains($db->calls[2][0], 'pc.enviado = 1') && str_contains($db->calls[2][0], "vd.tipo_partida IN ('PRODUCTO','SERVICIO')"), 'Colocados solo pedidos enviados y tipos validos');
cerca($actual['cotizado'],2000,'Tipo de cambio cero usa divisor 1');
verificar($actual['cantidades']===array_replace($quantities,['cotizacion_cliente'=>14,'cotizacion_cliente_periodo'=>6,'cotizacion_cliente_anteriores'=>8,'orden_compra_cliente'=>5,'orden_compra_cliente_periodo'=>3,'orden_compra_cliente_anteriores'=>2,'declinados'=>5,'declinados_periodo'=>2,'declinados_anteriores'=>3]),'Cantidades independientes de los conjuntos cotizado y colocado');
verificar($db->calls[3][1]===['2024-02-01','2024-03-01',"V'1"],'Cantidades con mes de proyecto y alcance autorizado');
verificar(str_contains($db->calls[3][0], 'WHERE enviado = 1 GROUP BY venta_id'), 'Contar proyectos con cotización enviada en cualquier fecha según la consulta solicitada');
verificar(substr_count($db->calls[3][0],'GROUP BY venta_id')===2,'Agrupar documentos por proyecto antes del JOIN');
verificar(str_contains($db->calls[3][0], "COALESCE(v.activo,'ACTIVO') <> 'CERRADO' AND EXISTS") &&
    str_contains($db->calls[3][0], 'WHERE ci.venta_id = v.id AND ci.enviado = 1 AND NOT EXISTS') &&
    str_contains($db->calls[3][0], 'WHERE cliente.cotizacion_interna_id = ci.id AND cliente.enviado = 1'),
    'Contar proyectos no declinados con interna enviada sin cotización a cliente enviada vinculada');
verificar(substr_count($db->calls[3][0],'WHERE enviado = 1 GROUP BY venta_id')===2,'Cotizaciones y pedidos deben estar enviados, sin restringir su fecha');
verificar(str_contains($db->calls[3][0], "pc.venta_id IS NOT NULL AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'"), 'Excluir declinados del conteo de pedidos, conservando el total de proyectos');
verificar(str_contains($db->calls[3][0],"v.activo = 'CERRADO'"),'Usar el estatus explícito solicitado');
verificar(str_contains($db->calls[3][0], "cc.venta_id IS NOT NULL AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'"), 'Excluir declinados sólo del conteo de cotizaciones, conservando total y declinados');
verificar(!str_contains($db->calls[2][0],'v.fecha >=') && str_contains($db->calls[2][0],'fecha_pedido >= ?'),'Colocados incluyen proyectos anteriores con pedido en el mes');
$dup=$headers[1]; $dup['id']=3;
$duplicateModel=new ModeloSimulado(new ConexionSimulada([[],[],[$headers[1],$dup]]));
try { $duplicateModel->dashboard(2024,2,''); verificar(false,'Rechazar folios ambiguos'); }
catch (RuntimeException $ex) { verificar(str_contains($ex->getMessage(),'duplicados'),'Error identificable de folios duplicados'); }

verificar($actual['clasificaciones_por_vendedor'][0]['proyectos']===10 && $actual['clasificaciones_por_vendedor'][0]['clasificacion']==='Bombas', 'Desglose por vendedor con nombre real de estatus');
verificar($db->calls[5][1]===$db->calls[4][1] && str_contains($db->calls[5][0],'s.id = v.clasificacion_proyecto_id') && str_contains($db->calls[5][0],'s.clasificacion'), 'Desglose usa catalogo y filtros de la grafica general');
verificar($actual['clasificaciones_por_vendedor'][0]['declinados']===2 && str_contains($db->calls[5][0], "v.activo = 'CERRADO'"), 'Declinados como subconjunto del total');
verificar($actual['estatus_por_vendedor'][0]['declinados']===2 && $db->calls[6][1]===$db->calls[4][1] && str_contains($db->calls[6][0],'ORDER BY v.estatus_proyecto_id ASC'), 'Estatus ordenados por ID y filtros compartidos');
verificar($actual['estatus_por_clasificacion'][0]['declinados']===2 && count($db->calls[7][1])===25 && $db->calls[7][1][0]==='2024-01-01' && $db->calls[7][1][23]==='2025-01-01' && $db->calls[7][1][24]==="V'1" && str_contains($db->calls[7][0],'s.cEstatusReporte'), 'Desglose de clasificacion con filtros y nombre del reporte');
// La lista conserva fecha, estado y alcance; no multiplica proyectos por documentos.
$listDb = new ConexionSimulada([[['total'=>21]], [['id'=>21,'proyecto_id'=>'P21']]]);
$list = (new ModeloSimulado($listDb))->declinados(2024,2,"V'1",100);
verificar($list['pagina']===2 && $list['paginas']===2 && $list['total']===21, 'Paginación limitada al último resultado');
verificar($listDb->calls[0][1]===['2024-02-01','2024-03-01','2024-02-01','2024-03-01',"V'1"] && $listDb->calls[1][1]===$listDb->calls[0][1], 'Lista y total comparten mes y vendedor');
verificar(str_contains($listDb->calls[1][0], "v.activo = 'CERRADO'") && str_contains($listDb->calls[1][0], 'LIMIT 20 OFFSET 20'), 'Estado y tamaño de página controlados');
foreach ($listDb->calls as [$sql]) verificar(str_contains($sql,'((v.fecha >= ? AND v.fecha < ?) OR (v.fecha_declina >= ? AND v.fecha_declina < ?))'),
    'Lista usa la union de ambas fechas, sin contar dos veces proyectos que cumplen ambas');
foreach ($listDb->calls as [$sql]) verificar(str_contains($sql,'v.clasificacion_proyecto_id IN (2,3,4,5) AND (v.estatus_proyecto_id IS NULL OR v.estatus_proyecto_id <> 2)'), 'Clasificaciones en listado y total de declinados');
$emptyList = (new ModeloSimulado(new ConexionSimulada([[['total'=>0]],[]])))->declinados(2024,2,'',1);
verificar($emptyList['proyectos']===[] && $emptyList['paginas']===1, 'Lista vacía válida');
$tableDb = new ConexionSimulada([[['total'=>8]],[['total'=>1]],[['id'=>5,'proyecto_id'=>'P5']]]);
$tableOptions = ['draw'=>3,'start'=>0,'length'=>10,'order_column'=>2,'order_dir'=>'asc','search'=>"100% O'Neil",'filters'=>[3=>'Cliente']];
$tableResult = (new ModeloSimulado($tableDb))->declinadosTabla(2024,2,'V1',$tableOptions);
verificar($tableResult['draw']===3 && $tableResult['recordsTotal']===8 && $tableResult['recordsFiltered']===1, 'Contrato DataTables con total y total filtrado');
verificar($tableDb->calls[0][1]===['2024-02-01','2024-03-01','2024-02-01','2024-03-01','V1'] &&
    in_array("%100!% O'Neil%", $tableDb->calls[1][1],true), 'Búsqueda parametrizada con comodines literales');
foreach ($tableDb->calls as [$sql]) verificar(str_contains($sql,'v.fecha_declina >= ? AND v.fecha_declina < ?'), 'Total, filtro y pagina del modal incluyen declinados anteriores');
verificar(str_contains($tableDb->calls[2][0],'ORDER BY v.fecha ASC, v.id DESC LIMIT 10 OFFSET 0'), 'Orden real de fecha y paginación DataTables');

$drillDb = new ConexionSimulada([[['total'=>2]],[['total'=>2]],[['id'=>633,'vendedor_id'=>'V1','cliente_id'=>3]]]);
$drillOptions = $tableOptions + ['clasificacion_id'=>5,'estatus_id'=>3,'segmento'=>'no_declinados'];
$statusDb = new ConexionSimulada([[['total'=>2]],[['total'=>2]],[]]);
(new ModeloSimulado($statusDb))->declinadosTabla(2026,9,'V1',$drillOptions,'estatus_periodo');
verificar($statusDb->calls[0][1]===['2026-09-01','2026-10-01','V1',3], 'Estatus mensual sin limitar a una clasificacion');
verificar(str_contains($statusDb->calls[0][0],'v.estatus_proyecto_id = ?') &&
    !str_contains($statusDb->calls[0][0],'v.clasificacion_proyecto_id = ?') &&
    str_contains($statusDb->calls[2][0],'v.ccveusuario_vendedor ASC'), 'Modal de estatus agrupado por vendedor conserva alcance');
$placedStatusDb = new ConexionSimulada([[['total'=>3]],[['total'=>3]],[]]);
$placedStatusOptions = array_replace($drillOptions, ['estatus_id'=>'colocados','search'=>'','filters'=>[]]);
(new ModeloSimulado($placedStatusDb))->declinadosTabla(2026,9,'V1',$placedStatusOptions,'estatus_periodo');
foreach ($placedStatusDb->calls as [$sql,$params]) {
    verificar(str_contains($sql,'v.estatus_proyecto_id >= 6') && !str_contains($sql,'v.estatus_proyecto_id = ?'), 'Modal incluye todos los estatus colocados');
    verificar($params === ['2026-09-01','2026-10-01','V1'], 'Grupo conserva mes y vendedor');
}
$quotationDb = new ConexionSimulada([[['total'=>3]],[['total'=>3]],[]]);
(new ModeloSimulado($quotationDb))->declinadosTabla(2026,9,'V1',array_replace($placedStatusOptions, ['estatus_id'=>'proceso_cotizacion']),'estatus_periodo');
foreach ($quotationDb->calls as [$sql,$params]) {
    verificar(str_contains($sql,'v.estatus_proyecto_id IN (1,3)'), 'Proceso de cotizacion incluye ambos estatus');
    verificar($params === ['2026-09-01','2026-10-01','V1'], 'Grupo conserva periodo y vendedor');
}
(new ModeloSimulado($drillDb))->declinadosTabla(2026,9,'V1',$drillOptions,'estatus_clasificacion');
verificar($drillDb->calls[0][1]===['2026-09-01','2026-10-01','V1',5,3], 'Desglose por clase y estatus parametrizados');
verificar(str_contains($drillDb->calls[0][0],"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'") && str_contains($drillDb->calls[2][0],'v.cliente_id ASC'), 'Segmento no declinado y agrupacion vendedor cliente');
// Ejecuta el endpoint con sesión/modelo simulados, sin cargar el bootstrap real.
$criticalDb = new ConexionSimulada([[['total'=>10]],[['total'=>10]],[['id'=>633,'proyecto_id'=>'P633']]]);
$criticalModel = new ModeloSimulado($criticalDb);
$criticalTable = $criticalModel->declinadosTabla(2026,9,'V1',$tableOptions,'interna_sin_cliente');
verificar($criticalTable['recordsTotal']===10 && $criticalDb->calls[0][1]===['2026-09-01','2026-10-01','V1'], 'Lista critica dentro del mes y vendedor');
foreach ($criticalDb->calls as [$sql]) {
    verificar(str_contains($sql,'v.clasificacion_proyecto_id IN (2,3,4,5) AND (v.estatus_proyecto_id IS NULL OR v.estatus_proyecto_id <> 2)'), 'Clasificaciones en lista critica');
    verificar(str_contains($sql,"COALESCE(v.activo,'ACTIVO') <> 'CERRADO' AND EXISTS")
        && str_contains($sql,'ci.enviado = 1 AND NOT EXISTS')
        && str_contains($sql,'cliente.cotizacion_interna_id = ci.id AND cliente.enviado = 1'), 'Total y lista usan la condicion critica del KPI');
}
$accessDb = new ConexionSimulada([[['id'=>633]],[]]);
$accessModel = new ModeloSimulado($accessDb);
verificar($accessModel->proyectoInternaSinClienteAutorizado(633,'V1') && !$accessModel->proyectoInternaSinClienteAutorizado(633,'V2'), 'Historial critico verifica proyecto y vendedor');
verificar($accessDb->calls[0][1]===[633,'V1'], 'Acceso por ID parametrizado');
verificar(str_contains($accessDb->calls[0][0],'v.clasificacion_proyecto_id IN (2,3,4,5) AND (v.estatus_proyecto_id IS NULL OR v.estatus_proyecto_id <> 2)'), 'Clasificaciones en historial critico');
class Controllers { public $model; public function __construct() {} }
class Session {
    public static bool $active = true;
    public static array $values = ['rol_id'=>4,'ccveusuario'=>'V1'];
    public function getStatus() { return self::$active; }
    public function get($key) { return self::$values[$key] ?? null; }
}
define('MOD_REPORTES_MENSUALES_VENTAS',139);
$testPermissions=[139=>['r'=>1]];
function getPermisosGlobal() { global $testPermissions; return $testPermissions; }
function getLoggerSystem() { return new class { public function error($message) {} }; }
require_once __DIR__.'/../../Controllers/Reportesmensuales.php';
$api = new Reportesmensuales();
$api->model = new class {
    public array $calls=[];
    public function declinados($year,$month,$seller,$page) {
        $this->calls[]=[$year,$month,$seller,$page];
        return ['proyectos'=>[],'total'=>0,'pagina'=>1,'paginas'=>1,'por_pagina'=>20];
    }
    public function declinadosTabla($year,$month,$seller,$options,$lista='declinados') {
        $this->calls[]=[$year,$month,$seller,$options,$lista];
        return ['draw'=>$options['draw'],'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[], 'lista'=>$lista];
    }
};
function llamarLista($api): array {
    http_response_code(200); ob_start(); $api->declinados();
    return [http_response_code(),json_decode(ob_get_clean(),true,512,JSON_THROW_ON_ERROR)];
}
$_SERVER['REQUEST_METHOD']='GET'; $_GET=['anio'=>'2024','mes'=>'2','vendedor'=>'','pagina'=>'1'];
verificar(llamarLista($api)[0]===200 && $api->model->calls[0]===[2024,2,'V1',1], 'TODOS restringido al vendedor de sesión');
$_GET['vendedor']='V2'; verificar(llamarLista($api)[0]===403 && count($api->model->calls)===1, 'Rechazar otro vendedor sin consultar datos');
$_GET['vendedor']=''; $_GET['mes']='13'; verificar(llamarLista($api)[0]===400, 'Rechazar período inválido');
$_GET['mes']='2'; Session::$active=false; verificar(llamarLista($api)[0]===401, 'Sesión vencida responde JSON');
Session::$active=true; $testPermissions=[]; verificar(llamarLista($api)[0]===403, 'Verificar permiso del módulo');
$testPermissions=[139=>['r'=>1]]; $_SERVER['REQUEST_METHOD']='POST'; verificar(llamarLista($api)[0]===405, 'Sólo GET');
$_SERVER['REQUEST_METHOD']='GET'; Session::$values=['rol_id'=>1,'ccveusuario'=>'ADMIN'];
verificar(llamarLista($api)[0]===200 && $api->model->calls[1]===[2024,2,'',1], 'TODOS para usuario autorizado general');
$_GET['datatable']='1'; $_GET['draw']='2'; $_GET['length']='10';
$tableResponse=llamarLista($api);
verificar($tableResponse[0]===200 && $tableResponse[1]['data']['draw']===2, 'Modo DataTables del endpoint');
$_GET['length']='10000'; verificar(llamarLista($api)[0]===400, 'Limitar filas solicitadas');
$_GET['length']='10'; $_GET['f3']=['malformado']; verificar(llamarLista($api)[0]===400, 'Rechazar filtro de columna malformado');
unset($_GET['f3']); $_GET['lista']='interna_sin_cliente';
verificar(llamarLista($api)[1]['data']['lista']==='interna_sin_cliente', 'Endpoint selecciona lista critica');
$_GET['lista']='estatus_clasificacion'; $_GET['clasificacion_id']='5'; $_GET['estatus_id']='3'; $_GET['segmento']='declinados';
verificar(llamarLista($api)[0]===200,'Endpoint de proyectos del estatus');
$_GET['clasificacion_id']=['5']; verificar(llamarLista($api)[0]===400,'Rechazar clase malformada');
$_GET['lista']='estatus_periodo'; unset($_GET['clasificacion_id']);
verificar(llamarLista($api)[0]===200,'Endpoint de estatus no requiere clasificacion');
$_GET['estatus_id']=['3']; verificar(llamarLista($api)[0]===400,'Rechazar estatus malformado');
$_GET['estatus_id']='proceso_cotizacion'; verificar(llamarLista($api)[0]===200,'Aceptar grupo proceso de cotizacion');
$_GET['estatus_id']='colocados'; verificar(llamarLista($api)[0]===200,'Aceptar grupo de colocados');
$_GET['lista']='estatus_clasificacion'; $_GET['clasificacion_id']='5'; verificar(llamarLista($api)[0]===400,'Grupo solo en grafica general');
$_GET['lista']='estatus_periodo';
$_GET['estatus_id']='sin_estatus'; verificar(llamarLista($api)[0]===200,'Permitir proyectos sin estatus');
$_GET['segmento']='invalido'; verificar(llamarLista($api)[0]===400,'Rechazar segmento desconocido');
$_GET['lista']='invalida'; verificar(llamarLista($api)[0]===400,'Rechazar tipo de lista desconocido');
http_response_code(200);

$_SERVER['REQUEST_METHOD'] = 'GET';
Session::$active = true;
$testPermissions = [139=>['r'=>1]];
$_GET = ['anio'=>'2024','mes'=>['12','2','9','2'],'vendedor'=>'','pagina'=>'1'];
verificar(llamarLista($api)[0]===200 && end($api->model->calls)[1]===[2,9,12], 'Endpoint normaliza meses multiples sin duplicados');
foreach ([[], ['2','13'], [['2']], ['2 OR 1=1']] as $invalidMonths) {
    $_GET['mes'] = $invalidMonths;
    verificar(llamarLista($api)[0]===400, 'Rechazar seleccion multiple malformada');
}
$_GET = ['anio'=>['2026','2024','2026'],'mes'=>['2','12'],'vendedor'=>'','pagina'=>'1'];
verificar(llamarLista($api)[0]===200 && end($api->model->calls)[0]===[2024,2026], 'Endpoint normaliza anios multiples');
foreach ([[], ['2024','2101'], [['2024']], ['2024 OR 1=1']] as $invalidYears) {
    $_GET['anio'] = $invalidYears;
    verificar(llamarLista($api)[0]===400, 'Rechazar seleccion de anios malformada');
}
$_GET = ['anio'=>'2024','mes'=>'2','vendedor'=>'','pagina'=>'1'];
$_GET = ['anio'=>'2026','mes'=>'9','vendedor'=>'','datatable'=>'1','lista'=>'cotizados_periodo',
    'periodo_anio'=>['2024','2026'],'periodo_mes'=>['9','10']];
verificar(llamarLista($api)[0]===200 && end($api->model->calls)[4]==='cotizados_periodo'
    && end($api->model->calls)[3]['periodo_anios']===[2024,2026]
    && end($api->model->calls)[3]['periodo_meses']===[9,10], 'Desglose de cotizados acepta y conserva el filtro global');
$_GET['lista']='colocados_periodo';
verificar(llamarLista($api)[0]===200 && end($api->model->calls)[4]==='colocados_periodo'
    && end($api->model->calls)[3]['periodo_meses']===[9,10], 'Desglose de colocados acepta y conserva el filtro global');
$_GET['periodo_mes']=['10']; verificar(llamarLista($api)[0]===400, 'Rechazar mes de desglose externo al filtro global');
$_GET = ['anio'=>'2024','mes'=>'2','vendedor'=>'','pagina'=>'1'];

$_SERVER['REQUEST_METHOD']='GET';
$_GET=['anio'=>'2026','mes'=>'9','datatable'=>'1','lista'=>'vendedor_clasificacion',
    'clasificacion_id'=>'3','vendedor_id'=>'V1','segmento'=>'no_declinados'];
verificar(llamarLista($api)[0]===200, 'Endpoint acepta lista por clasificacion y vendedor');
$_GET['vendedor_id']=['V1'];
verificar(llamarLista($api)[0]===400, 'Rechazar vendedor malformado');

// Historial: contrato compartido y permisos por proyecto.
class VentasModel {
    public static array $calls=[];
    public function selectSeguimientoVenta(int $id): array { self::$calls[]=$id; return [['id'=>768,'venta_id'=>$id,'seguimiento'=>'Prueba']]; }
}
$api->model = new class {
    public array $calls=[];
    public bool $allowed=true;
    public function proyectoReporteAutorizado($id,$seller) { $this->calls[]=[$id,$seller,'reporte']; return $this->allowed; }
    public function proyectoDeclinadoAutorizado($id,$seller) { $this->calls[]=[$id,$seller]; return $this->allowed; }
    public function proyectoInternaSinClienteAutorizado($id,$seller) { $this->calls[]=[$id,$seller,'interna']; return $this->allowed; }
};
function llamarSeguimientos($api): array {
    http_response_code(200); ob_start(); $api->seguimientos();
    return [http_response_code(),json_decode(ob_get_clean(),true,512,JSON_THROW_ON_ERROR)];
}
$_SERVER['REQUEST_METHOD']='POST'; $_POST=['venta_id'=>'633']; Session::$values=['rol_id'=>4,'ccveusuario'=>'V1'];
$r=llamarSeguimientos($api);
verificar($r[0]===200 && $r[1]['respuesta']==='ok' && VentasModel::$calls===[633] && $api->model->calls[0]===[633,'V1'],'Historial del ID elegido y vendedor autenticado');
$_POST['lista']='interna_sin_cliente';
verificar(llamarSeguimientos($api)[0]===200 && $api->model->calls[1]===[633,'V1','interna'],'Historial desde la lista critica');
$_POST['lista']='invalida'; verificar(llamarSeguimientos($api)[0]===400,'Tipo de historial desconocido');
unset($_POST['lista']); VentasModel::$calls=[633];
$api->model->allowed=false;
verificar(llamarSeguimientos($api)[0]===403 && VentasModel::$calls===[633],'Rechazar proyecto ajeno o no declinado');
$api->model->allowed=true; $_POST['venta_id']=['633']; verificar(llamarSeguimientos($api)[0]===400,'Rechazar ID malformado');
$_POST['venta_id']='633'; Session::$active=false; verificar(llamarSeguimientos($api)[0]===401,'Historial requiere sesion');
Session::$active=true; $testPermissions=[]; verificar(llamarSeguimientos($api)[0]===403,'Historial requiere permiso del reporte');
$testPermissions=[139=>['r'=>1]]; $_SERVER['REQUEST_METHOD']='GET'; verificar(llamarSeguimientos($api)[0]===405,'Historial solo acepta POST');
$_SERVER['REQUEST_METHOD']='POST'; Session::$values=['rol_id'=>1]; verificar(llamarSeguimientos($api)[0]===200,'Administrador autorizado');
http_response_code(200);

$_SERVER['REQUEST_METHOD']='POST'; $_POST=['venta_id'=>'633','lista'=>'clasificacion_periodo'];
Session::$values=['rol_id'=>4,'ccveusuario'=>'V1'];
verificar(llamarSeguimientos($api)[0]===200 && end($api->model->calls)===[633,'V1','reporte'], 'Historial desde clasificaciones aplica alcance del vendedor');
$api->model->allowed=false;
verificar(llamarSeguimientos($api)[0]===403,'Historial de clasificacion rechaza proyecto fuera del alcance');
$api->model->allowed=true;
$followupDb = new ConexionSimulada([[['id'=>633]]]);
verificar((new ModeloSimulado($followupDb))->proyectoReporteAutorizado(633,'V1'), 'Proyecto autorizado para historial');
verificar($followupDb->calls[0][1]===[633,'V1'] && str_contains($followupDb->calls[0][0],'v.estatus_proyecto_id <> 2'), 'Historial conserva filtros del reporte y permisos');

// Renderiza sólo la vista con datos sintéticos y sin cargar las plantillas del portal.
// El modal por vendedor conserva el alcance autorizado y agrega la seleccion concreta.
$classificationModalDb = new ConexionSimulada([[['total'=>1]], [['total'=>1]], []]);
(new ModeloSimulado($classificationModalDb))->declinadosTabla(2026,9,'V1',
    ['draw'=>1,'start'=>0,'length'=>10,'order_column'=>2,'order_dir'=>'desc','search'=>'','filters'=>[],
    'clasificacion_id'=>3,'segmento'=>'no_declinados'], 'clasificacion_periodo');
foreach ($classificationModalDb->calls as [$sql,$params]) {
    verificar($params===['2026-09-01','2026-10-01','V1',3], 'Modal de clasificacion respeta mes, vendedor autorizado y clasificacion');
    verificar(str_contains($sql,"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'"), 'Tramo normal excluye declinados');
}
$sellerModalDb = new ConexionSimulada([[['total'=>1]], [['total'=>1]], []]);
(new ModeloSimulado($sellerModalDb))->declinadosTabla(2026,9,'V1',
    ['draw'=>1,'start'=>0,'length'=>10,'order_column'=>2,'order_dir'=>'desc','search'=>'','filters'=>[],
    'clasificacion_id'=>3,'vendedor_id'=>'V2','segmento'=>'declinados'], 'vendedor_clasificacion');
foreach ($sellerModalDb->calls as [$sql,$params]) {
    verificar($params===['2026-09-01','2026-10-01','V1',3,'V2'], 'No reemplazar el vendedor autorizado al abrir el modal');
    verificar(str_contains($sql,"v.activo = 'CERRADO'") && str_contains($sql,'v.clasificacion_proyecto_id = ?'), 'Conservar clasificacion y segmento');
}
function base_url() { return '/portal'; }
function assets() { return '/portal/Assets'; }
function version() { return 'test'; }
$report['tipo_cambio']=20; $report['fecha_tipo_cambio']='2024-02-01';
$report['cantidades']=array_replace($quantities,['cotizacion_cliente'=>54,'cotizacion_cliente_periodo'=>46,'cotizacion_cliente_anteriores'=>8,'orden_compra_cliente'=>13,'orden_compra_cliente_periodo'=>11,'orden_compra_cliente_anteriores'=>2,'declinados'=>5,'declinados_periodo'=>2,'declinados_anteriores'=>3]);
$report['proyectos_por_vendedor'][0]['nombre']='<script>alert("XSS")</script>';
$report['proyectos_por_vendedor'][0]['proyectos']=10;
$data=['page_form_title'=>'Reporte ventas','page_breadcrumb'=>'Ventas','filtros'=>['anio'=>2024,'mes'=>2,'vendedor'=>''],
    'reporte'=>$report,'reporte_error'=>'','vendedores'=>[['id'=>'V1','nombre'=>'José']], 'usuario'=>['rol_id'=>4]];
$view=file_get_contents(__DIR__.'/../../Views/Reportesmensuales/reporte_ventas.php');
$view=preg_replace('/<\?php require_once\([^;]+; \?>/','',$view);
ob_start(); eval('?>'.$view); $html=ob_get_clean();
verificar(str_contains($html,'&lt;script&gt;alert'),'Escape HTML de subclasificaciones');
verificar(!str_contains($html,'<script>alert'),'Sin inyección HTML');
verificar(str_contains($html,'\\u003Cscript\\u003E'),'Escape de JSON incrustado');
verificar(str_contains($html,'TODOS') && str_contains($html,'José'),'UTF-8 y filtros');
verificar(str_contains($html,'Cantidades') && !str_contains($html,'>Importes</h4>') && str_contains($html,'Pedidos Colocados'),'Conservar Cantidades y retirar el bloque Importes');
verificar(str_contains($html,'Pedidos Cotizados en el Período')
    && str_contains($html,'Proyectos del período: 46') && str_contains($html,'Proyectos anteriores: 8')
    && str_contains($html,'class="ventas-valor">54</div>'), 'Mostrar el total principal y sus componentes con menor jerarquia');
verificar(str_contains($html,'Pedidos Colocados en el Período')
    && str_contains($html,'Proyectos del período: 11') && str_contains($html,'Proyectos anteriores: 2')
    && str_contains($html,'class="ventas-valor">13</div>')
    && substr_count($html,'data-bs-target="#modal-colocados-financiero"')===1,
    'Colocados conservan su modal y muestran total y desglose');
verificar(str_contains($html,'Declinados en el Período') && str_contains($html,'Proyectos del período: 2')
    && str_contains($html,'Proyectos anteriores: 3') && str_contains($html,'class="ventas-valor">5</div>'),
    'Tarjeta roja muestra el total ampliado y su composicion');
verificar(str_contains($html,'Cantidades (crítico)') && str_contains($html,'col-12 col-md-3') && str_contains($html,'sin cotización a cliente enviada vinculada'), 'Tarjeta crítica en el primer cuarto de la fila');
verificar(substr_count($html,'data-bs-target="#modal-declinados-ventas"')===2, 'Ambas tarjetas abren el modal de declinados solicitado');
verificar(str_contains($html, 'Importes sin IVA en USD') && !str_contains($html, 'con IVA'), 'Pantalla informa subtotales sin IVA');
verificar(!str_contains($html,'Colocado / cotizado'),'Retirar el indicador de relación anterior');
verificar(!str_contains($html,'Ventas por vendedor') && !str_contains($html,'ventas-productos') && !str_contains($html,'ventas-cruce') && !str_contains($html,'Criterios del reporte'), 'Secciones inferiores retiradas');
foreach ($db->calls as $index => [$sql]) if (!in_array($index, [1,2], true)) verificar(!str_contains($sql,'tb_ventas_detalle'), 'Partidas solo en los importes de pedidos');

$data['filtros']['mes'] = [2,9,12];
ob_start(); eval('?>'.$view); $multiHtml=ob_get_clean();
verificar(str_contains($multiHtml, 'name="mes[]"') && str_contains($multiHtml, 'multiple required'), 'Select2 permite varios meses');
verificar(str_contains($multiHtml, 'Febrero, Septiembre, Diciembre') && str_contains($multiHtml, 'data-mes="2,9,12"'), 'Vista y modal conservan meses seleccionados');
verificar(str_contains($multiHtml, '01/02/2024 al 29/02/2024 | 01/09/2024 al 30/09/2024 | 01/12/2024 al 31/12/2024'), 'Periodo exacto sin meses intermedios');
$data['filtros']['anio'] = [2024,2026];
ob_start(); eval('?>'.$view); $yearsHtml=ob_get_clean();
verificar(str_contains($yearsHtml,'name="anio[]"') && str_contains($yearsHtml,'data-anio="2024,2026"'), 'Select2 y modal conservan multiples anios');
verificar(str_contains($yearsHtml,'Febrero, Septiembre, Diciembre 2024, 2026') && str_contains($yearsHtml,'01/02/2026 al 28/02/2026'), 'Titulos y periodos corresponden a todos los anios');
echo "OK: agregaciones, conciliación, permisos por conjunto, consultas parametrizadas, vacío, bisiesto, divisor de respaldo, folios ambiguos y renderizado seguro.\n";
