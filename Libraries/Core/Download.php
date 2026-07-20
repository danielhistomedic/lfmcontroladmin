<?php

$file = $_GET['file'];
$rfc =  $_GET['rfc'];
$enlace = "Assets/files/" . $rfc . "/xml/" . $file;
header("Content-Disposition: attachment; filename=" . $file . " ");
header("Content-Type: application/octet-stream");
header("Content-Length: " . filesize($enlace));
readfile($enlace);
