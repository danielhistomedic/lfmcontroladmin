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
$lines = [
    ['venta_id'=>1,'tipo'=>'cotizado','partida_id'=>10,'subclasificacion_id'=>5,'subclasificacion'=>'Bombas','unidad'=>'PZA','cantidad'=>2,'monto'=>1000],
    ['venta_id'=>1,'tipo'=>'cotizado','partida_id'=>10,'subclasificacion_id'=>5,'subclasificacion'=>'Bombas','unidad'=>'PZA','cantidad'=>1,'monto'=>500],
    ['venta_id'=>1,'tipo'=>'colocado','partida_id'=>10,'subclasificacion_id'=>5,'subclasificacion'=>'Bombas','unidad'=>'PZA','cantidad'=>2,'monto'=>2000],
    ['venta_id'=>2,'tipo'=>'cotizado','partida_id'=>20,'subclasificacion_id'=>null,'subclasificacion'=>null,'unidad'=>'HR','cantidad'=>4,'monto'=>0],
    // Una fila fuera del conjunto autorizado no debe agregarse.
    ['venta_id'=>99,'tipo'=>'cotizado','partida_id'=>90,'subclasificacion_id'=>5,'subclasificacion'=>'Bombas','unidad'=>'PZA','cantidad'=>100,'monto'=>9000],
];
$report=ReportesmensualesModel::resumir($headers,$lines,20,29);
cerca($report['cotizado'],130,'Total cotizado convertido');
cerca($report['colocado'],80,'Total colocado del proyecto, sin multiplicarlo por pedidos');
verificar($report['proyectos']===2 && $report['proyectos_cotizados']===2 && $report['proyectos_colocados']===1,'Conteos únicos y por concepto');
verificar($report['cotizaciones_enviadas']===2,'Contar documentos enviados sin confundirlos con proyectos ni respaldos');
foreach (['productos','cruce','vendedores','diario'] as $section) {
    cerca(array_sum(array_column($report[$section],'cotizado')),130,'Conciliación cotizado '.$section);
    cerca(array_sum(array_column($report[$section],'colocado')),80,'Conciliación colocado '.$section);
}
$bombas=array_values(array_filter($report['productos'],fn($r)=>$r['subclasificacion_id']==='5'))[0];
verificar($bombas['partidas']===1,'No duplicar partidas por documentos ni conceptos');
verificar($bombas['unidades_cotizadas']==='3 PZA' && $bombas['unidades_vendidas']==='2 PZA','Cantidades comerciales por concepto');
$delta=array_values(array_filter($report['productos'],fn($r)=>$r['subclasificacion_id']==='conciliacion'))[0];
cerca($delta['colocado'],-20,'Conservar diferencias negativas sin inventar asignación');
cerca($delta['cotizado'],55,'Respaldo sin partidas valoradas y diferencia cotizada');
cerca(array_sum(array_column($report['productos'],'participacion')),100,'Participación colocada conciliada');
verificar(count($report['diario'])===29 && $report['diario'][28]['cotizado']===30.0,'Febrero bisiesto');
$empty=ReportesmensualesModel::resumir([],[],1,31);
verificar($empty['proyectos']===0 && $empty['productos']===[] && count($empty['diario'])===31,'Mes vacío');

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
$db=new ConexionSimulada([[['valor'=>0,'fecha'=>'2024-02-01']],[$headers[0]],[$headers[1]],[$lines[0]],[$lines[2]],[$quantities]]);
$model=new ModeloSimulado($db);
$actual=$model->dashboard(2024,2,"V'1");
verificar(count($db->calls)===6,'Consultas por conjunto');
verificar($db->calls[1][1]===['2024-02-01','2024-03-01',"V'1"],'Límites del mes y vendedor parametrizado');
verificar(str_contains($db->calls[1][0],'cc.enviado = 1') && str_contains($db->calls[3][0],'cc.enviado=1'),'Sólo cotizaciones enviadas en importes y partidas');
verificar(str_contains($db->calls[1][0], "COALESCE(v.activo,'ACTIVO') <> 'CERRADO'"), 'Excluir declinados de importes cotizados conservando activo NULL');
verificar(!str_contains($db->calls[2][0],'v.activo'),'Conservar las condiciones de colocados');
cerca($actual['cotizado'],2000,'Tipo de cambio cero usa divisor 1');
verificar($actual['cantidades']===$quantities,'Cantidades independientes de los conjuntos cotizado y colocado');
verificar($db->calls[5][1]===['2024-02-01','2024-03-01',"V'1"],'Cantidades con mes de proyecto y alcance autorizado');
verificar(str_contains($db->calls[5][0], 'WHERE enviado = 1 GROUP BY venta_id'), 'Contar proyectos con cotización enviada en cualquier fecha según la consulta solicitada');
verificar(substr_count($db->calls[5][0],'GROUP BY venta_id')===2,'Agrupar documentos por proyecto antes del JOIN');
verificar(str_contains($db->calls[5][0], "COALESCE(v.activo,'ACTIVO') <> 'CERRADO' AND EXISTS") &&
    str_contains($db->calls[5][0], 'WHERE ci.venta_id = v.id AND ci.enviado = 1 AND NOT EXISTS') &&
    str_contains($db->calls[5][0], 'WHERE cliente.cotizacion_interna_id = ci.id AND cliente.enviado = 1'),
    'Contar proyectos no declinados con interna enviada sin cotización a cliente enviada vinculada');
verificar(substr_count($db->calls[5][0],'WHERE enviado = 1 GROUP BY venta_id')===2,'Cotizaciones y pedidos deben estar enviados, sin restringir su fecha');
verificar(str_contains($db->calls[5][0], "pc.venta_id IS NOT NULL AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'"), 'Excluir declinados del conteo de pedidos, conservando el total de proyectos');
verificar(str_contains($db->calls[5][0],"v.activo = 'CERRADO'"),'Usar el estatus explícito solicitado');
verificar(str_contains($db->calls[5][0], "cc.venta_id IS NOT NULL AND COALESCE(v.activo,'ACTIVO') <> 'CERRADO'"), 'Excluir declinados sólo del conteo de cotizaciones, conservando total y declinados');
verificar(!str_contains($db->calls[2][0],'v.fecha >=') && str_contains($db->calls[2][0],'fecha_pedido >= ?'),'Colocados incluyen proyectos anteriores con pedido en el mes');
$dup=$headers[1]; $dup['id']=3;
$duplicateModel=new ModeloSimulado(new ConexionSimulada([[],[],[$headers[1],$dup]]));
try { $duplicateModel->dashboard(2024,2,''); verificar(false,'Rechazar folios ambiguos'); }
catch (RuntimeException $ex) { verificar(str_contains($ex->getMessage(),'duplicados'),'Error identificable de folios duplicados'); }

// Renderiza sólo la vista con datos sintéticos y sin cargar las plantillas del portal.
function base_url() { return '/portal'; }
function assets() { return '/portal/Assets'; }
function version() { return 'test'; }
$report['tipo_cambio']=20; $report['fecha_tipo_cambio']='2024-02-01';
$report['cantidades']=$quantities;
$report['productos'][0]['nombre']='<script>alert("XSS")</script>';
$data=['page_form_title'=>'Reporte ventas','page_breadcrumb'=>'Ventas','filtros'=>['anio'=>2024,'mes'=>2,'vendedor'=>''],
    'reporte'=>$report,'reporte_error'=>'','vendedores'=>[['id'=>'V1','nombre'=>'José']], 'usuario'=>['rol_id'=>4]];
$view=file_get_contents(__DIR__.'/../../Views/Reportesmensuales/reporte_ventas.php');
$view=preg_replace('/<\?php require_once\([^;]+; \?>/','',$view);
ob_start(); eval('?>'.$view); $html=ob_get_clean();
verificar(str_contains($html,'&lt;script&gt;alert'),'Escape HTML de subclasificaciones');
verificar(!str_contains($html,'<script>alert'),'Sin inyección HTML');
verificar(str_contains($html,'\\u003Cscript\\u003E'),'Escape de JSON incrustado');
verificar(str_contains($html,'TODOS') && str_contains($html,'José'),'UTF-8 y filtros');
verificar(str_contains($html,'Cantidades') && !str_contains($html,'>Importes</h4>') && str_contains($html,'Orden Compra Cliente'),'Conservar Cantidades y retirar el bloque Importes');
verificar(str_contains($html,'Cantidades (crítico)') && str_contains($html,'col-12 col-md-3') && str_contains($html,'sin cotización a cliente enviada vinculada'), 'Tarjeta crítica en el primer cuarto de la fila');
verificar(!str_contains($html,'Colocado / cotizado'),'Retirar el indicador de relación anterior');
echo "OK: agregaciones, conciliación, permisos por conjunto, consultas parametrizadas, vacío, bisiesto, divisor de respaldo, folios ambiguos y renderizado seguro.\n";
