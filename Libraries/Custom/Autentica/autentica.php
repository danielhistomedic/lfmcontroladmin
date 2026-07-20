<?php

function ObtenerToken($service_url, $action, $fileKey, $rutaCer, $rfc)
{

  $public_key = '';
  $cert_array = trim(file_get_contents($rutaCer));
  $fechaInicio = gmdate("Y-m-d\TH:i:s", time());
  $fechaFin = gmdate("Y-m-d\TH:i:s", time() + 300);
  $digestBase64 = DigestTimeStamp1($fechaInicio, $fechaFin);
  $certificado = clean_cert($cert_array);
  $signedInfo = CreateDataToSign($digestBase64);
  $signature = SignData($signedInfo, $fileKey);
  $envelopeFinal = EnvelopeFinal($fechaInicio, $fechaFin, $digestBase64, $signature, $certificado);
  SaveFile2($envelopeFinal, 'Assets/files/' . $rfc . '/token/envioToken.xml');
  $respuesta = SendData($envelopeFinal, $service_url, $action, '');
  SaveFile2($respuesta, 'Assets/files/' . $rfc . '/token/RespuestaToken.xml');
  $xmldoc = new DOMDocument();
  $xmldoc->loadXML($respuesta, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

  $node = $xmldoc->getElementsByTagName('AutenticaResponse')->item(0)->nodeValue;
  return $node;
}

function DigestTimeStamp1($fechaInicio, $fechaFin)
{
  $timeStamp = '<u:Timestamp xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" u:Id="_0">' .
    '<u:Created>' . $fechaInicio . '</u:Created><u:Expires>' . $fechaFin . '</u:Expires></u:Timestamp>';
  return (string)base64_encode(sha1($timeStamp, TRUE));
}

function CreateDataToSign($digest1)
{
  $infoToSign = '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#"><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></CanonicalizationMethod><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod><Reference URI="#_0"><Transforms><Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"></Transform></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod><DigestValue>' . $digest1 . '</DigestValue></Reference></SignedInfo>';
  return $infoToSign;
}

function SignData($data, $key)
{
  $private_key = file_get_contents($key);
  if (!openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA1)) {
    throw new Exception('Failure Signing Data: ' . openssl_error_string() . ' - ' . OPENSSL_ALGO_SHA1);
    return;
  } else {
    return base64_encode($signature);
  }
}

function EnvelopeFinal($fechaInicio, $fechaFin, $digest, $signature, $cert_array)
{

  // $guid = "uuid-726d6583-bb04-4d74-8841-f0d404dffadb-4";
  // $envelope = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><s:Header><ActivityId CorrelationId="c5478a6f-8f27-43f4-bc97-bea17612517c" xmlns="http://schemas.microsoft.com/2004/09/ServiceModel/Diagnostics">00000000-0000-0000-0000-000000000000</ActivityId><o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><u:Timestamp u:Id="_0"><u:Created>' . $fechaInicio . '</u:Created><u:Expires>' . $fechaFin . '</u:Expires></u:Timestamp><o:BinarySecurityToken u:Id="' . $guid . '" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . $cert_array . '</o:BinarySecurityToken><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/><Reference URI="#_0"><Transforms><Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/><DigestValue>' . $digest . '</DigestValue></Reference></SignedInfo><SignatureValue>' . $signature . '</SignatureValue><KeyInfo><o:SecurityTokenReference><o:Reference ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" URI="#' . $guid . '"/></o:SecurityTokenReference></KeyInfo></Signature></o:Security></s:Header><s:Body><Autentica xmlns="http://DescargaMasivaTerceros.gob.mx"/></s:Body></s:Envelope>';

  $guid = "uuid-b246ed31-bfec-804a-5212-095ac6d97d3c-1";
  $envelope = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><s:Header><o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><u:Timestamp u:Id="_0"><u:Created>' . $fechaInicio . '</u:Created><u:Expires>' . $fechaFin . '</u:Expires></u:Timestamp><o:BinarySecurityToken u:Id="' . $guid . '" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . $cert_array . '</o:BinarySecurityToken><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/><Reference URI="#_0"><Transforms><Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/><DigestValue>' . $digest . '</DigestValue></Reference></SignedInfo><SignatureValue>' . $signature . '</SignatureValue><KeyInfo><o:SecurityTokenReference><o:Reference ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" URI="#' . $guid . '"/></o:SecurityTokenReference></KeyInfo></Signature></o:Security></s:Header><s:Body><Autentica xmlns="http://DescargaMasivaTerceros.gob.mx"/></s:Body></s:Envelope>';
  // echo $envelope;

  return $envelope;
}

function SendData($soap_request, $url, $action, $token)
{
  //echo $soap_request;
  if ($token == '') {

    $headers = array(
      'Content-Type: text/xml;charset=UTF-8',
      'SOAPAction: ' . $action,
      'Content-Length: ' . strlen($soap_request),
    );
  } else {

    $headers = array(
      'Content-Type: text/xml;charset=UTF-8',
      'SOAPAction: "' . $action . '"',
      'Content-Length: ' . strlen($soap_request),
      'Authorization: WRAP access_token="' . $token . '"',
    );
  }

  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  //curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');

  $result = curl_exec($ch);

  curl_close($ch);

  return $result;
}

function SendDataSolicitud($soap_request, $url, $action, $token)
{

  $headers = array(
    'Content-Type: text/xml; charset=utf-8',
    'SOAPAction: "' . $action . '"',
    'Content-Length: ' . strlen($soap_request),
    'Authorization: WRAP access_token="' . $token . '"',
  );

  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);

  curl_close($ch);

  return $result;
}

function SaveFile($xml, $ruta)
{
  try {
    $xmldoc = new DOMDocument();
    $xmldoc->preservWhiteSpace = false;
    $xmldoc->formatOutput = false;
    $xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
    $nodeValue = $xmldoc->getElementsByTagName('Paquete')->item(0)->nodeValue;

    $output_file = $ruta;
    $content = base64_decode($nodeValue);
    $file = fopen($output_file, "w+");
    fwrite($file, $content);

    fclose($file);
  } catch (\Throwable $th) {
    getLoggerSystem()->error(getMensajeError($th));
  }
}

function SaveFileIdPaquete($paquete, $ruta): bool
{
  try {

    $response = false;
    $output_file = $ruta;
    $content = base64_decode($paquete);
    $file = fopen($output_file, "w+");
    fwrite($file, $content);

    fclose($file);

    $response = true;
    return $response;
  } catch (\Throwable $th) {
    getLoggerSystem()->error(getMensajeError($th));
    return $response;
  }
}

function SaveFile2($xml, $ruta)
{
  try {
    $output_file = $ruta;
    $content = $xml;
    $file = fopen($output_file, "w+");
    fwrite($file, $content);
    fclose($file);
  } catch (\Throwable $th) {
    getLoggerSystem()->error(getMensajeError($th));
  }
}

function clean_cert($cer = "")
{
  $lines = preg_replace("/\r|\s/", "", $cer);
  $lines = explode("\n", $cer);
  if (!is_array($lines)) return false;
  $cert = '';
  foreach ($lines as $line) {
    $line = preg_replace("/\n|\r|\n\r/", "", $line);
    if (preg_match("/CERTIFICATE\-\-\-\-\-$/", $line)) continue;
    $cert .= $line;
  }
  return $cert;
}

function signXML($xml = "", $rutaKey, $rutaCer, $pass, $tag = "solicitud")
{
  $cer  = trim(file_get_contents($rutaCer));
  $key  = trim(file_get_contents($rutaKey));
  // $pass = 'qwerty12';
  $pkey = openssl_get_privatekey($key, $pass);
  if (!$pkey) die("Error de llave\n");
  $xml = preg_replace("/\n|\r|\t/", "", $xml);

  $xmldoc = new DOMDocument();
  $xmldoc->preservWhiteSpace = false;
  $xmldoc->formatOutput = false;
  $xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
  $root = $xmldoc->documentElement;
  //if ($tag) 
  $node = $xmldoc->getElementsByTagName($tag)->item(0);
  //else 
  //  $node = $root;
  $datos = $node->C14N(false, false, NULL, NULL);
  //echo $datos;
  // obtenemos la digestion
  $digestvalue = base64_encode(hash('sha1', $datos, true));
  // creamos estructura de firma
  $signature = $xmldoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
  $node->appendChild($signature);
  $signedinfo = $xmldoc->createElement('SignedInfo');
  $signature->appendChild($signedinfo);
  // Cannocalization
  $nn = $xmldoc->createElement('CanonicalizationMethod');
  $signedinfo->appendChild($nn);
  $nn->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
  // SignatureMethod
  $nn = $xmldoc->createElement('SignatureMethod');
  $signedinfo->appendChild($nn);
  $nn->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
  // Reference
  $reference = $xmldoc->createElement('Reference');
  $signedinfo->appendChild($reference);
  $reference->setAttribute('URI', '');
  // Transforms
  $transforms = $xmldoc->createElement('Transforms');
  $reference->appendChild($transforms);
  // Transform
  $nn = $xmldoc->createElement('Transform');
  $transforms->appendChild($nn);
  $nn->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');

  $nn = $xmldoc->createElement('DigestMethod');
  $reference->appendChild($nn);
  $nn->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
  // DigestValue
  $nn = $xmldoc->createElement('DigestValue', $digestvalue);
  $reference->appendChild($nn);
  $datos = $signedinfo->C14N(false, false, NULL, NULL);
  //echo $datos;
  $signaturevalue = '';
  // firmamos los datos
  $res = openssl_sign($datos, $signaturevalue, $pkey);
  $signaturevalue = base64_encode($signaturevalue);
  // SignatureValue
  $nn = $xmldoc->createElement('SignatureValue', $signaturevalue);
  $signature->appendChild($nn);
  // KeyInfo
  $keyinfo = $xmldoc->createElement('KeyInfo');
  $signature->appendChild($keyinfo);
  // X509Data
  $x509data = $xmldoc->createElement('X509Data');
  $keyinfo->appendChild($x509data);
  // cargamos certificado
  $cert = clean_cert($cer);
  // X509Certificate
  $nn = $xmldoc->createElement('X509Certificate', $cert);
  $x509data->appendChild($nn);
  openssl_free_key($pkey);
  return $xmldoc->saveXML();
}

function signXMLSolicitud($xml = "", $rutaKey, $rutaCer, $pass, $rfc_rec, $tag = "solicitud")
{

  $cer  = trim(file_get_contents($rutaCer));
  $key  = trim(file_get_contents($rutaKey));
  // $pass = 'qwerty12';
  $pkey = openssl_get_privatekey($key, $pass);
  if (!$pkey) die("Error de llave\n");
  $xml = preg_replace("/\n|\r|\t/", "", $xml);

  $xmldoc = new DOMDocument();
  $xmldoc->preservWhiteSpace = false;
  $xmldoc->formatOutput = false;
  $xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
  $root = $xmldoc->documentElement;
  //if ($tag) 
  $node = $xmldoc->getElementsByTagName($tag)->item(0);
  //else 
  //  $node = $root;
  $datos = $node->C14N(false, false, NULL, NULL);
  //echo $datos;

  // RfcReceptores
  $rfc_recepctores = $xmldoc->createElement('RfcReceptores');
  $node->appendChild($rfc_recepctores);

  // RfcReceptor
  $rfc_recepctor = $xmldoc->createElement('RfcReceptor', $rfc_rec);
  $rfc_recepctores->appendChild($rfc_recepctor);

  // obtenemos la digestion
  $digestvalue = base64_encode(hash('sha1', $datos, true));
  // creamos estructura de firma
  $signature = $xmldoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
  $node->appendChild($signature);
  $signedinfo = $xmldoc->createElement('SignedInfo');
  $signature->appendChild($signedinfo);
  // Cannocalization
  $nn = $xmldoc->createElement('CanonicalizationMethod');
  $signedinfo->appendChild($nn);
  $nn->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
  // SignatureMethod
  $nn = $xmldoc->createElement('SignatureMethod');
  $signedinfo->appendChild($nn);
  $nn->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
  // Reference
  $reference = $xmldoc->createElement('Reference');
  $signedinfo->appendChild($reference);
  $reference->setAttribute('URI', '');
  // Transforms
  $transforms = $xmldoc->createElement('Transforms');
  $reference->appendChild($transforms);
  // Transform
  $nn = $xmldoc->createElement('Transform');
  $transforms->appendChild($nn);
  $nn->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');

  $nn = $xmldoc->createElement('DigestMethod');
  $reference->appendChild($nn);
  $nn->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
  // DigestValue
  $nn = $xmldoc->createElement('DigestValue', $digestvalue);
  $reference->appendChild($nn);
  $datos = $signedinfo->C14N(false, false, NULL, NULL);
  //echo $datos;
  $signaturevalue = '';
  // firmamos los datos
  $res = openssl_sign($datos, $signaturevalue, $pkey);
  $signaturevalue = base64_encode($signaturevalue);
  // SignatureValue
  $nn = $xmldoc->createElement('SignatureValue', $signaturevalue);
  $signature->appendChild($nn);
  // KeyInfo
  $keyinfo = $xmldoc->createElement('KeyInfo');
  $signature->appendChild($keyinfo);
  // X509Data
  $x509data = $xmldoc->createElement('X509Data');
  $keyinfo->appendChild($x509data);
  // cargamos certificado
  $cert = clean_cert($cer);
  // X509Certificate
  $nn = $xmldoc->createElement('X509Certificate', $cert);
  $x509data->appendChild($nn);
  openssl_free_key($pkey);
  return $xmldoc->saveXML();
}
