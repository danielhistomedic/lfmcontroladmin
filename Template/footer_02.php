<!-- Custom Genral js-->
<script src="<?= assets(); ?>/app/js/main.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/app/js/formats.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/app/js/alertas.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/app/js/animate.js?v=<?= version(); ?>"></script>

<!-- Custom javascripts from View-->
<?php if ($data['page_functions_js'] != "") { ?>
    <?php
    // Revisar el recurso real sin cambiar la versión global ni los contratos de la vista.
    $viewScriptPath = __DIR__ . '/../Assets/app/js/' . $data['page_functions_js'];
    $viewScriptRevision = is_file($viewScriptPath) ? filemtime($viewScriptPath) : version();
    ?>
    <script src="<?= assets(); ?>/app/js/<?= $data['page_functions_js']; ?>?v=<?= version(); ?>.<?= $viewScriptRevision; ?>"></script>
    <?php unset($viewScriptPath, $viewScriptRevision); ?>
<?php } ?>


</body>

</html>