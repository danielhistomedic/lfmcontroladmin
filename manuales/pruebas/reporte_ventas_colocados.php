<?php
/** PHP CLI: consultas y endpoint con conexion/sesion simuladas, sin acceso a MySQL. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start(); require __DIR__ . '/reporte_ventas_mensuales.php'; ob_end_clean();

$financialDb = new ConexionSimulada([
    [['moneda_id'=>1,'moneda'=>'MXN','total'=>'1200.00','pedidos'=>2],['moneda_id'=>3,'moneda'=>'USD','total'=>'150.00','pedidos'=>1]],
    [['grupo'=>'Flowserve','moneda_id'=>1,'moneda'=>'MXN','total'=>'1200.00','pedidos'=>2],['grupo'=>'Diversos','moneda_id'=>3,'moneda'=>'USD','total'=>'150.00','pedidos'=>1]]
]);
$financial = (new ModeloSimulado($financialDb))->colocadosFinanciero([2026,2024],[2,9],'V1');
verificar(count($financial['totales'])===2 && $financial['totales'][0]['total']==='1200.00', 'Conservar importes originales sin conversion');
foreach ($financialDb->calls as [$sql,$params]) {
    verificar(substr_count($sql,'?')===count($params), 'Parametros coinciden con rangos y vendedor');
    verificar(str_contains($sql,'pc.fecha_pedido >= ?') && !str_contains($sql,'v.fecha >= ?'), 'Usar fecha del pedido');
    verificar(str_contains($sql,'SUM(COALESCE(pc.total,0))') && str_contains($sql,'pc.moneda_id'), 'Total y moneda provienen del pedido');
    verificar(str_contains($sql,'pc.enviado = 1') && str_contains($sql,"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'")
        && str_contains($sql,'v.ccveusuario_vendedor = ?') && str_contains($sql,'v.clasificacion_proyecto_id IN (2,3,4,5)'), 'Conservar alcance comercial y vendedor');
    verificar(!str_contains($sql,'tb_pedidos_cliente_detalle') && !str_contains($sql,'tb_historial_tipos_cambio'), 'Sin multiplicar pedidos por partidas ni convertir moneda');
}
verificar(str_contains($financialDb->calls[1][0],"IN (2,3,4) THEN 'Flowserve' ELSE 'Diversos'"), 'Clasificaciones Flowserve y Diversos');
$clientDb = new ConexionSimulada([[['total'=>4]],[['total'=>1]],[['entidad_id'=>12,'nombre'=>'Cliente','moneda'=>'USD','total'=>'150.00']]]);
$options = ['draw'=>4,'start'=>10,'length'=>10,'search'=>'a%_','order_column'=>2,'order_dir'=>'desc'];
$clientRows = (new ModeloSimulado($clientDb))->colocadosFinanciero(2026,9,'V1','clientes',$options);
verificar($clientRows['draw']===4 && $clientRows['recordsTotal']===4 && $clientRows['recordsFiltered']===1, 'Paginacion de clientes');
verificar(str_contains($clientDb->calls[2][0],'GROUP BY pc.cliente_id, pc.moneda_id') &&
    str_contains($clientDb->calls[2][0],'ORDER BY total DESC, entidad_id ASC, pc.moneda_id ASC') &&
    str_contains($clientDb->calls[2][0],'LIMIT 10 OFFSET 10') && in_array('%a!%!_%',$clientDb->calls[2][1],true), 'Cliente del pedido, monedas separadas y busqueda literal');
$sellerDb = new ConexionSimulada([[['total'=>1]],[]]);
(new ModeloSimulado($sellerDb))->colocadosFinanciero(2026,9,'V1','vendedores',['search'=>'']);
verificar(count($sellerDb->calls)===2 && str_contains($sellerDb->calls[1][0],'GROUP BY v.ccveusuario_vendedor, pc.moneda_id'), 'Vendedor del proyecto y moneda del pedido');
$emptyDb = new ConexionSimulada([[],[]]);
verificar((new ModeloSimulado($emptyDb))->colocadosFinanciero(2026,9,'')['totales']===[], 'Resumen vacio valido');

$api->model = new class {
    public array $calls = [];
    public bool $fail = false;
    public function colocadosFinanciero($years,$months,$seller,$section,$options) {
        $this->calls[]=[$years,$months,$seller,$section,$options];
        if ($this->fail) throw new RuntimeException('SQL sensitive detail');
        return ['totales'=>[],'grupos'=>[]];
    }
};
function llamarFinanciero($api): array {
    http_response_code(200); ob_start(); $api->colocadosfinanciero();
    $payload = ob_get_clean(); return [http_response_code(),json_decode($payload,true,512,JSON_THROW_ON_ERROR)];
}
Session::$active=true; Session::$values=['rol_id'=>4,'ccveusuario'=>'V1']; $testPermissions=[139=>['r'=>1]];
$_SERVER['REQUEST_METHOD']='GET'; $_GET=['anio'=>['2026','2024'],'mes'=>['9','2'],'vendedor'=>''];
verificar(llamarFinanciero($api)[0]===200 && $api->model->calls[0] === [[2024,2026],[2,9],'V1','resumen',[]], 'Normalizar periodos y restringir TODOS al vendedor de sesion');
$_GET['vendedor']='V2'; verificar(llamarFinanciero($api)[0]===403 && count($api->model->calls)===1, 'Rechazar otro vendedor sin consultar');
$_GET['vendedor']=''; $_GET['mes']=['13']; verificar(llamarFinanciero($api)[0]===400, 'Rechazar periodo invalido');
$_GET['mes']=['9']; $_GET['seccion']='clientes'; $_GET['length']='10000'; verificar(llamarFinanciero($api)[0]===400, 'Limitar paginacion');
$_GET['length']='10'; $_GET['order_column']='2; DROP'; verificar(llamarFinanciero($api)[0]===400, 'Rechazar orden malformado');
$_GET['order_column']='2'; $_GET['search']=['malformado']; verificar(llamarFinanciero($api)[0]===400, 'Rechazar busqueda malformada');
$_GET['search']=''; verificar(llamarFinanciero($api)[0]===200, 'Tabla autorizada');
Session::$active=false; verificar(llamarFinanciero($api)[0]===401, 'Rechazar sesion vencida');
Session::$active=true; $testPermissions=[]; verificar(llamarFinanciero($api)[0]===403, 'Comprobar permiso del modulo');
$testPermissions=[139=>['r'=>1]]; $_SERVER['REQUEST_METHOD']='POST'; verificar(llamarFinanciero($api)[0]===405, 'Solo lectura GET');
$_SERVER['REQUEST_METHOD']='GET'; $api->model->fail=true;
$error=llamarFinanciero($api); verificar($error[0]===500 && !str_contains(json_encode($error),'sensitive'), 'Error controlado sin detalles SQL');
verificar(str_contains($yearsHtml,'data-bs-target="#modal-colocados-financiero"') && str_contains($yearsHtml,'colocados-clientes-tabla'), 'Tarjeta verde abre el modal financiero');
echo "OK: resumen por moneda, grupos Flowserve/Diversos, clientes/vendedores paginados, filtros, permisos, vacio y errores seguros.\n";
