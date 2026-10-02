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
$db=new ConexionSimulada([[['valor'=>0,'fecha'=>'2024-02-01']],[$headers[0]],[$headers[1]],[$quantities],[['vendedor_id'=>'V1','nombre'=>'Vendedor','proyectos'=>'10']],[['vendedor_id'=>'V1','clasificacion_id'=>3,'clasificacion'=>'Bombas','proyectos'=>'10','declinados'=>'2']],[['vendedor_id'=>'V1','estatus_id'=>6,'estatus'=>'Pedido','proyectos'=>'10','declinados'=>'2']],[['clasificacion_id'=>3,'estatus_id'=>6,'estatus'=>'Pedido','proyectos'=>'10','declinados'=>'2']]]);
$model=new ModeloSimulado($db);
$actual=$model->dashboard(2024,2,"V'1");
verificar($actual['proyectos_por_vendedor'][0]['proyectos']===10, 'Cantidad entera por vendedor');
verificar($db->calls[4][1]===['2024-02-01','2024-03-01',"V'1"] && str_contains($db->calls[4][0],'COUNT(*) AS proyectos') && !str_contains($db->calls[4][0],'activo'), 'Proyectos del mes por vendedor incluyen todos los estados');
verificar(count($db->calls)===8,'Consultas por conjunto');
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
verificar(!str_contains($db->calls[2][0],'v.activo'),'Conservar las condiciones de colocados');
cerca($actual['cotizado'],2000,'Tipo de cambio cero usa divisor 1');
verificar($actual['cantidades']===$quantities,'Cantidades independientes de los conjuntos cotizado y colocado');
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
verificar($actual['estatus_por_clasificacion'][0]['declinados']===2 && $db->calls[7][1]===$db->calls[4][1] && str_contains($db->calls[7][0],'s.cEstatusReporte'), 'Desglose de clasificacion con filtros y nombre del reporte');
// La lista conserva fecha, estado y alcance; no multiplica proyectos por documentos.
$listDb = new ConexionSimulada([[['total'=>21]], [['id'=>21,'proyecto_id'=>'P21']]]);
$list = (new ModeloSimulado($listDb))->declinados(2024,2,"V'1",100);
verificar($list['pagina']===2 && $list['paginas']===2 && $list['total']===21, 'Paginación limitada al último resultado');
verificar($listDb->calls[0][1]===['2024-02-01','2024-03-01',"V'1"] && $listDb->calls[1][1]===$listDb->calls[0][1], 'Lista y total comparten mes y vendedor');
verificar(str_contains($listDb->calls[1][0], "v.activo = 'CERRADO'") && str_contains($listDb->calls[1][0], 'LIMIT 20 OFFSET 20'), 'Estado y tamaño de página controlados');
foreach ($listDb->calls as [$sql]) verificar(str_contains($sql,'v.clasificacion_proyecto_id IN (2,3,4,5) AND (v.estatus_proyecto_id IS NULL OR v.estatus_proyecto_id <> 2)'), 'Clasificaciones en listado y total de declinados');
$emptyList = (new ModeloSimulado(new ConexionSimulada([[['total'=>0]],[]])))->declinados(2024,2,'',1);
verificar($emptyList['proyectos']===[] && $emptyList['paginas']===1, 'Lista vacía válida');
$tableDb = new ConexionSimulada([[['total'=>8]],[['total'=>1]],[['id'=>5,'proyecto_id'=>'P5']]]);
$tableOptions = ['draw'=>3,'start'=>0,'length'=>10,'order_column'=>2,'order_dir'=>'asc','search'=>"100% O'Neil",'filters'=>[3=>'Cliente']];
$tableResult = (new ModeloSimulado($tableDb))->declinadosTabla(2024,2,'V1',$tableOptions);
verificar($tableResult['draw']===3 && $tableResult['recordsTotal']===8 && $tableResult['recordsFiltered']===1, 'Contrato DataTables con total y total filtrado');
verificar($tableDb->calls[0][1]===['2024-02-01','2024-03-01','V1'] &&
    in_array("%100!% O'Neil%", $tableDb->calls[1][1],true), 'Búsqueda parametrizada con comodines literales');
verificar(str_contains($tableDb->calls[2][0],'ORDER BY v.fecha ASC, v.id DESC LIMIT 10 OFFSET 0'), 'Orden real de fecha y paginación DataTables');

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
        $this->calls[]=[$year,$month,$seller,$options];
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
$_GET['lista']='invalida'; verificar(llamarLista($api)[0]===400,'Rechazar tipo de lista desconocido');
http_response_code(200);

// Historial: contrato compartido y permisos por proyecto.
class VentasModel {
    public static array $calls=[];
    public function selectSeguimientoVenta(int $id): array { self::$calls[]=$id; return [['id'=>768,'venta_id'=>$id,'seguimiento'=>'Prueba']]; }
}
$api->model = new class {
    public array $calls=[];
    public bool $allowed=true;
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

// Renderiza sólo la vista con datos sintéticos y sin cargar las plantillas del portal.
function base_url() { return '/portal'; }
function assets() { return '/portal/Assets'; }
function version() { return 'test'; }
$report['tipo_cambio']=20; $report['fecha_tipo_cambio']='2024-02-01';
$report['cantidades']=$quantities;
$report['proyectos_por_vendedor'][0]['nombre']='<script>alert("XSS")</script>';
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
verificar(str_contains($html,'Cantidades (crítico)') && str_contains($html,'col-12 col-md-3') && str_contains($html,'sin cotización a cliente enviada vinculada'), 'Tarjeta crítica en el primer cuarto de la fila');
verificar(substr_count($html,'data-bs-target="#modal-declinados-ventas"')===2, 'Ambas tarjetas abren el modal de declinados solicitado');
verificar(!str_contains($html,'Colocado / cotizado'),'Retirar el indicador de relación anterior');
verificar(!str_contains($html,'Ventas por vendedor') && !str_contains($html,'ventas-productos') && !str_contains($html,'ventas-cruce') && !str_contains($html,'Criterios del reporte'), 'Secciones inferiores retiradas');
foreach ($db->calls as [$sql]) verificar(!str_contains($sql,'tb_ventas_detalle') && !str_contains($sql,'tb_ventas_cotizacion_cliente_detalle'), 'Sin consultas de partidas para secciones retiradas');
echo "OK: agregaciones, conciliación, permisos por conjunto, consultas parametrizadas, vacío, bisiesto, divisor de respaldo, folios ambiguos y renderizado seguro.\n";
