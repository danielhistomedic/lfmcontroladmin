<!-- Custom Genral js-->
<script src="<?= assets(); ?>/app/js/main.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/app/js/formats.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/app/js/alertas.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/app/js/animate.js?v=<?= version(); ?>"></script>

<!-- Custom javascripts from View-->
<?php if ($data['page_functions_js'] != "") { ?>
    <script src="<?= assets(); ?>/app/js/<?= $data['page_functions_js']; ?>?v=<?= version(); ?>"></script>
<?php } ?>


</body>

</html>