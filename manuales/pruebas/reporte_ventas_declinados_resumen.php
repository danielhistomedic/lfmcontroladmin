<?php
/** Consultas reales sobre SQLite en memoria; no carga conexión ni credenciales. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();
require __DIR__.'/reporte_ventas_mensuales.php';
$memory = new PDO('sqlite::memory:');
$memory->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$memory->sqliteCreateFunction('CONCAT_WS', static function ($separator, ...$values) {
    return implode($separator, array_filter($values, static fn($value)=>$value !== null));
});
$memory->exec('CREATE TABLE tb_ventas (id INTEGER PRIMARY KEY, proyecto_id TEXT, fecha TEXT, fecha_declina TEXT,
    activo TEXT, clasificacion_proyecto_id INTEGER, estatus_proyecto_id INTEGER, ccveusuario_vendedor TEXT,
    cliente_id INTEGER, titulo TEXT)');
$memory->exec('CREATE TABLE cat_medico (ccvemedico TEXT, cNombre TEXT, cPriApellido TEXT, cSegApellido TEXT)');
$memory->exec('CREATE TABLE cat_clientes (id INTEGER PRIMARY KEY, nombre_comercial TEXT)');
$memory->exec('CREATE TABLE cat_clasificacion_proyectos (id INTEGER PRIMARY KEY, clasificacion TEXT)');
$memory->exec("INSERT INTO cat_medico VALUES ('V1','José','García',NULL),('V1','José','García',NULL),('V2','Ana','López',NULL)");
$memory->exec("INSERT INTO cat_clientes VALUES (1,'Cliente')");
$memory->exec("INSERT INTO cat_clasificacion_proyectos VALUES (2,'DIVERSOS')");
$insert = $memory->prepare('INSERT INTO tb_ventas VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)');
foreach ([
    [1,'2026-09-02','2026-09-10','CERRADO',2,3,'V1'], // Ambas fechas: contar una vez.
    [2,'2026-01-02','2026-10-10','CERRADO',2,3,'V1'], // Anterior declinado en el período.
    [3,'2026-10-02',null,'CERRADO',2,null,'V1'],
    [4,'2026-09-02',null,'CERRADO',2,3,'V2'],
    [5,'2026-01-02','2026-09-10','CERRADO',2,3,'V2'],
    [6,'2026-10-02',null,'CERRADO',2,3,null],
    [7,'2026-09-02',null,'CERRADO',2,2,'V1'], // Estatus excluido.
    [8,'2026-09-02',null,'CERRADO',1,3,'V1'], // Clasificación excluida.
    [9,'2026-09-02',null,'ACTIVO',2,3,'V1'],
    [10,'2026-11-01',null,'CERRADO',2,3,'V1'], // Fin exclusivo.
    [11,'2026-08-02','2026-08-10','CERRADO',2,3,'V1'],
] as [$id,$date,$declined,$active,$class,$status,$seller]) {
    $insert->execute([$id,'P'.$id,$date,$declined,$active,$class,$status,$seller,'Título '.$id]);
}
function ejecutarDeclinados(PDO $db, array $call): array {
    $stmt=$db->prepare($call[0]);$stmt->execute($call[1]);return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$captured = new ConexionSimulada([[]]);
(new ModeloSimulado($captured))->declinadosResumen(2026,[9,10],'');
$rows = ejecutarDeclinados($memory,$captured->calls[0]);
verificar(array_column($rows,'proyectos') === [3,2,1], 'Ordenar vendedores por cantidad descendente sin duplicar nombres');
verificar(array_column($rows,'vendedor_id') === ['V1','V2',''], 'Incluir proyectos sin vendedor');
$result = (new ModeloSimulado(new ConexionSimulada([$rows])))->declinadosResumen(2026,[9,10],'');
verificar($result['total'] === 6 && count($result['vendedores']) === 3,'Total único y número de vendedores');
cerca($result['vendedores'][0]['porcentaje'],50,'Porcentaje sobre total del período');
verificar((new ModeloSimulado(new ConexionSimulada([[]])))->declinadosResumen(2026,[9,10],'')['total']===0,'Resumen vacío');
$scoped = new ConexionSimulada([[]]);
(new ModeloSimulado($scoped))->declinadosResumen(2026,[9,10],'V1');
verificar(array_column(ejecutarDeclinados($memory,$scoped->calls[0]),'proyectos')===[3],'Resumen respeta vendedor global');
$options=['draw'=>1,'start'=>0,'length'=>100,'order_column'=>2,'order_dir'=>'desc','search'=>'','filters'=>[]];
foreach ([null=>6,'V1'=>3,'V2'=>2] as $seller=>$expected) {
    $settings=$options;
    if ($seller !== '') $settings['declinado_vendedor']=$seller;
    $db=new ConexionSimulada([[['total'=>$expected]],[['total'=>$expected]],[]]);
    (new ModeloSimulado($db))->declinadosTabla(2026,[9,10],'',$settings);
    $details=ejecutarDeclinados($memory,$db->calls[2]);
    verificar(count($details)===$expected && count(array_unique(array_column($details,'id')))===$expected,'Detalle y resumen concilian sin duplicados');
    verificar((int)ejecutarDeclinados($memory,$db->calls[0])[0]['total']===$expected,'Total de detalle coincide');
}
$db=new ConexionSimulada([[['total'=>1]],[['total'=>1]],[]]);
(new ModeloSimulado($db))->declinadosTabla(2026,[9,10],'',$options+['declinado_vendedor'=>'']);
verificar(array_column(ejecutarDeclinados($memory,$db->calls[2]),'id')===[6],'Filtrar Sin vendedor sin confundirlo con TODOS');
$_SERVER['REQUEST_METHOD']='GET'; Session::$active=true;
// La barra incluye los dos estados; el mismo filtro por vendedor y periodo sigue vigente.
foreach (['todos'=>2,'declinados'=>1,'no_declinados'=>1] as $segment=>$expected) {
    $captured=new ConexionSimulada([[['total'=>$expected]],[['total'=>$expected]],[]]);
    (new ModeloSimulado($captured))->declinadosTabla(2026,[9,10],'V1',$options+['estatus_id'=>3,'segmento'=>$segment],'estatus_periodo');
    verificar((int)ejecutarDeclinados($memory,$captured->calls[0])[0]['total']===$expected,'Barra y segmentos usan mismo conjunto con ambos estados y vendedor');
    $ids=array_unique(array_column(ejecutarDeclinados($memory,$captured->calls[2]),'id'));
    verificar(count($ids)===$expected,'Detalle reutilizado coincide por ID con el conjunto del segmento');
}
$api=new Reportesmensuales();
$api->model=new class {
    public array $calls=[];
    public function declinadosResumen($year,$month,$seller) {
        $this->calls[]=[$year,$month,$seller,'resumen'];return ['total'=>0,'vendedores'=>[]];
    }
    public function declinadosTabla($year,$month,$seller,$options,$lista='declinados') {
        $this->calls[]=[$year,$month,$seller,$options,$lista];
        return ['draw'=>1,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[]];
    }
};
Session::$values=['rol_id'=>4,'ccveusuario'=>'V1'];$testPermissions=[139=>['r'=>1]];
$_GET=['anio'=>['2026'],'mes'=>['9','10'],'vendedor'=>'','resumen'=>'1'];
$response=llamarLista($api);
verificar($response[0]===200 && end($api->model->calls)===[2026,[9,10],'V1','resumen'],'Resumen restringido por sesión');
$_GET['vendedor']='V2';verificar(llamarLista($api)[0]===403,'Resumen impide consultar otro vendedor');
$_GET=['anio'=>'2026','mes'=>['9','10'],'datatable'=>'1','declinado_vendedor'=>'V2'];
verificar(llamarLista($api)[0]===403,'Selección local no amplía alcance de sesión');
$_GET['declinado_vendedor']=['V1'];verificar(llamarLista($api)[0]===400,'Rechazar selección malformada');
$_GET['declinado_vendedor']='V1';verificar(llamarLista($api)[0]===200,'Detalle del vendedor autorizado');
Session::$values=['rol_id'=>1,'ccveusuario'=>'ADMIN'];$_GET['declinado_vendedor']='';
verificar(llamarLista($api)[0]===200 && end($api->model->calls)[3]['declinado_vendedor']==='','Sin vendedor se transmite explícitamente');
Session::$values=['rol_id'=>4,'ccveusuario'=>'V1'];
$_GET=['anio'=>'2026','mes'=>['9','10'],'datatable'=>'1','lista'=>'estatus_periodo','estatus_id'=>'3','segmento'=>'todos','vendedor'=>'V1'];
verificar(llamarLista($api)[0]===200 && end($api->model->calls)[3]['segmento']==='todos','Barra admite ambos estados en el listado existente');
$_GET['vendedor']='V2';verificar(llamarLista($api)[0]===403,'Drill-down no permite ampliar alcance del vendedor autenticado');
$_GET['vendedor']='V1';$_GET['lista']='estatus_clasificacion';$_GET['clasificacion_id']='2';
verificar(llamarLista($api)[0]===400,'Opcion ambos estados queda limitada al listado del periodo');
Session::$active=false;$_GET['resumen']='1';verificar(llamarLista($api)[0]===401,'Resumen exige sesión');
Session::$active=true;$testPermissions=[];verificar(llamarLista($api)[0]===403,'Resumen exige permiso');
echo "OK: resumen y detalle de declinados, fechas, porcentajes, vendedor, vacío y claves de catálogo repetidas.\n";
echo ob_get_clean();
