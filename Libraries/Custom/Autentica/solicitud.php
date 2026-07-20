<?php


require_once('xmlseclibs.php');
include('Autentica.php');

$fileKey =   media() . '/files/PEVV740321EN4/csd.key.pem';
$rutaCer =  media() . '/files/PEVV740321EN4/csd.cer.pem';

//$cert_array=trim(file_get_contents($rutaCer));
$token = ObtenerToken(
	'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/Autenticacion/Autenticacion.svc',
	'http://DescargaMasivaTerceros.gob.mx/IAutenticacion/Autentica',
	$fileKey,
	$rutaCer
);

// echo $token;


$rfcEmisor = '';
$rfcReceptor = 'PESH8805072Q5';
$rfcSolicitante = 'PESH8805072Q5';
$fechaInicial = '2021-02-16T00:00:00';
$fechaFinal = '2021-02-18T23:59:59';
//5ba9d696-b663-4a71-a7b0-ec2482d0b80d Listo
//01e13e2e-4aef-4068-9b39-f538793005ee

echo '<br>';
echo 'Solicitar';

Solicitar($rfcEmisor, $rfcReceptor, $rfcSolicitante, $fechaInicial, $fechaFinal, $token, $fileKey, $rutaCer);

function Solicitar($rfcEmisor, $rfcReceptor, $rfcSolicitante, $fechaInicial, $fechaFinal, $token, $fileKey, $rutaCer)
{

	$url = "https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/SolicitaDescargaService.svc";
	$action = "http://DescargaMasivaTerceros.sat.gob.mx/ISolicitaDescargaService/SolicitaDescarga";
	$xml = '<des:SolicitaDescarga xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx"><des:solicitud RfcEmisor="' . $rfcEmisor . '" RfcReceptor="' . $rfcReceptor . '" RfcSolicitante="' . $rfcSolicitante . '" FechaInicial="' . $fechaInicial . '" FechaFinal="' . $fechaFinal . '" TipoSolicitud="CFDI"/></des:SolicitaDescarga>';

	$res = signXML($xml, $fileKey, $rutaCer);
	$res = substr($res, 21, strlen($res));

	echo '<br>';
	echo $res;
	$envelope2 = ObtenerSOAP($res);

	echo '<br>';
	echo $envelope2;
	$resultado = SendData($envelope2, $url, $action, $token);
	echo ($resultado);
}

function ObtenerSOAP($xml)
{

	$envelope = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx"><soapenv:Header/><soapenv:Body>' . $xml . '</soapenv:Body></soapenv:Envelope>';
	return $envelope;
}
