<?php
/** Texto, etiquetas y campos compartidos del portal, también para entradas independientes y Login. */
$fontRoot = dirname(__DIR__) . '/Assets/app/css/';
?>
<link rel="stylesheet" href="<?= assets(); ?>/app/css/font-family.css?v=<?= filemtime($fontRoot . 'font-family.css'); ?>">
<link rel="stylesheet" href="<?= assets(); ?>/app/css/form-labels.css?v=<?= filemtime($fontRoot . 'form-labels.css'); ?>">
<link rel="stylesheet" href="<?= assets(); ?>/app/css/form-controls.css?v=<?= filemtime($fontRoot . 'form-controls.css'); ?>">
<?php unset($fontRoot); ?>
