<?php
/** PHP CLI: consultas y endpoint con conexion/sesion simuladas, sin acceso a MySQL. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start(); require __DIR__ . '/reporte_ventas_mensuales.php'; ob_end_clean();

$financialDb = new ConexionSimulada([
    [['moneda_id'=>1,'moneda'=>'MXN','total'=>'1200.00','pedidos'=>2],['moneda_id'=>3,'moneda'=>'USD','total'=>'150.00','pedidos'=>1]],
    [['grupo'=>'Flowserve','moneda_id'=>1,'moneda'=>'MXN','total'=>'1200.00','pedidos'=>2],['grupo'=>'Diversos','moneda_id'=>3,'moneda'=>'USD','total'=>'150.00','pedidos'=>1]],
    [['anio'=>2026,'mes'=>9,'moneda_id'=>3,'moneda'=>'USD','pedidos'=>1,'total'=>'150.00','productos'=>'100.00','servicios'=>'50.00']]
]);
$financial = (new ModeloSimulado($financialDb))->colocadosFinanciero([2026,2024],[2,9],'V1');
verificar(count($financial['totales'])===2 && $financial['totales'][0]['total']==='1200.00', 'Conservar importes originales sin conversion');
foreach ($financialDb->calls as [$sql,$params]) {
    verificar(substr_count($sql,'?')===count($params), 'Parametros coinciden con rangos y vendedor');
    verificar(str_contains($sql,'pc.fecha_pedido >= ?') && !str_contains($sql,'v.fecha >= ?'), 'Usar fecha del pedido');
    verificar(str_contains($sql,'pd.cantidad_pedido * pd.precio_unitario') && str_contains($sql,'pc.moneda_id'), 'Subtotal sin IVA y moneda provienen del pedido');
    verificar(str_contains($sql,'pc.enviado = 1') && str_contains($sql,"COALESCE(v.activo,'ACTIVO') <> 'CERRADO'")
        && str_contains($sql,'v.ccveusuario_vendedor = ?') && str_contains($sql,'v.clasificacion_proyecto_id IN (2,3,4,5)'), 'Conservar alcance comercial y vendedor');
    verificar(!str_contains($sql,'pc.total'), 'No sumar importes con IVA');
    verificar(str_contains($sql,'INNER JOIN tb_pedidos_cliente_detalle pd ON pd.pedido_id = pc.id') && str_contains($sql,'INNER JOIN tb_ventas_detalle vd ON vd.id = pd.venta_detalle_id'), 'Relaciones directas de partidas');
    verificar(str_contains($sql,'COUNT(DISTINCT pc.id) AS pedidos') && !str_contains($sql,'pc.subtotal') && !str_contains($sql,'pd.importe'), 'Pedidos distintos, sin encabezados ni importe almacenado');
    verificar(str_contains($sql, "vd.tipo_partida = 'PRODUCTO'") && str_contains($sql, "vd.tipo_partida = 'SERVICIO'"), 'Separar productos y servicios');
}
verificar(str_contains($financialDb->calls[1][0],"IN (2,3,4) THEN 'Flowserve' ELSE 'Diversos'"), 'Clasificaciones Flowserve y Diversos');
$clientDb = new ConexionSimulada([[['total'=>4]],[['total'=>1]],[['entidad_id'=>12,'nombre'=>'Cliente','moneda'=>'USD','total'=>'150.00']]]);
$options = ['draw'=>4,'start'=>10,'length'=>10,'search'=>'a%_','order_column'=>2,'order_dir'=>'desc'];
$clientRows = (new ModeloSimulado($clientDb))->colocadosFinanciero(2026,9,'V1','clientes',$options);
verificar($clientRows['draw']===4 && $clientRows['recordsTotal']===4 && $clientRows['recordsFiltered']===1, 'Paginacion de clientes');
verificar(str_contains($clientDb->calls[2][0],'pd.cantidad_pedido * pd.precio_unitario') && !str_contains($clientDb->calls[2][0],'pc.total'), 'Clientes muestran subtotales sin IVA');
verificar(str_contains($clientDb->calls[2][0],'GROUP BY pc.cliente_id, pc.moneda_id') &&
    str_contains($clientDb->calls[2][0],'total DESC, entidad_id ASC, pc.moneda_id ASC') &&
    str_contains($clientDb->calls[2][0],'LIMIT 10 OFFSET 10') && in_array('%a!%!_%',$clientDb->calls[2][1],true), 'Cliente del pedido, monedas separadas y busqueda literal');
verificar(str_contains($clientDb->calls[2][0], 'ORDER BY CASE pc.moneda_id WHEN 3 THEN 0 WHEN 1 THEN 1 ELSE 2 END ASC'), 'USD primero, MXN despues antes del importe descendente');
$sellerDb = new ConexionSimulada([[['total'=>1]],[]]);
(new ModeloSimulado($sellerDb))->colocadosFinanciero(2026,9,'V1','vendedores',['search'=>'']);
verificar(str_contains($sellerDb->calls[1][0],'pd.cantidad_pedido * pd.precio_unitario') && !str_contains($sellerDb->calls[1][0],'pc.total'), 'Vendedores muestran subtotales sin IVA');
verificar(count($sellerDb->calls)===2 && str_contains($sellerDb->calls[1][0],'GROUP BY v.ccveusuario_vendedor, pc.moneda_id'), 'Vendedor del proyecto y moneda del pedido');
$emptyDb = new ConexionSimulada([[],[],[]]);
verificar((new ModeloSimulado($emptyDb))->colocadosFinanciero(2026,9,'')['totales']===[], 'Resumen vacio valido');

verificar($financial['mensual'][0]['productos'] === '100.00' && $financial['mensual'][0]['servicios'] === '50.00', 'Desglose mensual conserva decimales');
verificar(str_contains($financialDb->calls[2][0], 'GROUP BY YEAR(pc.fecha_pedido), MONTH(pc.fecha_pedido), pc.moneda_id'), 'Meses separados por moneda');
$detailDb = new ConexionSimulada([[['total'=>1]],[['pedido_id'=>4,'venta_id'=>3,'subtotal_partida'=>'150.00']]]);
$details = (new ModeloSimulado($detailDb))->colocadosFinanciero(2026,9,'V1','detalle',['start'=>10,'length'=>10]);
verificar($details['recordsFiltered'] === 1 && str_contains($detailDb->calls[1][0], 'pd.cantidad_pedido * pd.precio_unitario AS subtotal_partida') && str_contains($detailDb->calls[1][0], 'LIMIT 10 OFFSET 10'), 'Detalle paginado con subtotal exclusivo por partida');
foreach (['pc.num_orden_compra','pc.venta_id','v.proyecto_id','mat.ccvematerial AS clave','mat.ccn','pd.ccvematerial AS codigo_cliente','pd.descripcion','pd.precio_unitario'] as $field) verificar(str_contains($detailDb->calls[1][0], $field), 'Campos reales de detalle');

verificar(str_contains($detailDb->calls[1][0], 'MAX(ccveMaterialAlmacen) AS ccn FROM tb_materiales GROUP BY ccvematerial') && str_contains($detailDb->calls[1][0], 'mat ON mat.ccvematerial = pd.ccvematerial'), 'Materiales opcionales sin multiplicar partidas');
$searchDetailDb = new ConexionSimulada([[['total'=>7]],[]]);
(new ModeloSimulado($searchDetailDb))->colocadosFinanciero(2026,9,'V1','detalle',
    ['search'=>'a%_','order_column'=>11,'order_dir'=>'desc','start'=>5]);
foreach ($searchDetailDb->calls as [$sql,$params]) {
    verificar(str_contains($sql, "CONCAT_WS(' ',") && in_array('%a!%!_%',$params,true), 'Busqueda literal sobre todas las partidas y conteo filtrado');
}
verificar(str_contains($searchDetailDb->calls[1][0], 'ORDER BY pd.cantidad_pedido * pd.precio_unitario DESC, pc.id ASC, pd.id ASC LIMIT 5 OFFSET 5'), 'Orden numerico de subtotal y pagina de cinco');

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
$_GET['seccion']='detalle'; $_GET['anio']=['2024','2026'];
verificar(llamarFinanciero($api)[0]===400, 'Detalle exige un solo mes y anio');
$_GET['anio']=['2026']; $_GET['mes']=['9']; $_GET['order_column']='11';
verificar(llamarFinanciero($api)[0]===200 && $api->model->calls[count($api->model->calls)-1][3] === 'detalle', 'Detalle mensual mantiene autorizacion y alcance');
$_GET['order_column']='12'; verificar(llamarFinanciero($api)[0]===400, 'Rechazar columna fuera del detalle');
$_GET['order_column']='11';
Session::$active=false; verificar(llamarFinanciero($api)[0]===401, 'Rechazar sesion vencida');
Session::$active=true; $testPermissions=[]; verificar(llamarFinanciero($api)[0]===403, 'Comprobar permiso del modulo');
$testPermissions=[139=>['r'=>1]]; $_SERVER['REQUEST_METHOD']='POST'; verificar(llamarFinanciero($api)[0]===405, 'Solo lectura GET');
$_SERVER['REQUEST_METHOD']='GET'; $api->model->fail=true;
$error=llamarFinanciero($api); verificar($error[0]===500 && !str_contains(json_encode($error),'sensitive'), 'Error controlado sin detalles SQL');
verificar(str_contains($yearsHtml,'data-bs-target="#modal-colocados-financiero"') && str_contains($yearsHtml,'colocados-clientes-tabla'), 'Tarjeta verde abre el modal financiero');
echo "OK: resumen por moneda, grupos Flowserve/Diversos, clientes/vendedores paginados, filtros, permisos, vacio y errores seguros.\n";

verificar(str_contains($yearsHtml, 'id="resumen-colocados-financiero"') && str_contains($yearsHtml, 'id="panel-colocados-clientes-tabla"'), 'Seccion financiera integrada');
preg_match_all('/\bid="([^"]+)"/', $yearsHtml, $ids);
verificar(count($ids[1]) === count(array_unique($ids[1])), 'Sin IDs duplicados entre seccion y modal');
verificar(strpos($yearsHtml, 'id="resumen-colocados-financiero"') < strpos($yearsHtml, 'id="ventas-comparativo"'), 'Resumen dentro de analisis financiero antes del comparativo');
