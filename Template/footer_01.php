</div>

</section>

<!-- Constantes -->
<script>
    const base_url = "<?= base_url(); ?>";
    const base_url_sitio = "<?= base_url_sitio(); ?>";
    const assets = "<?= assets(); ?>";
    let menu = "<?= $data['menu']; ?>";
    let session_sucursal_id = "<?= $data['sucursal_id']; ?>";
    let theme_chart;
    <?php
    $theme = $data['theme'];
    if ($theme == 'dark') { ?>
        theme_chart = "dark";
    <?php } else { ?>
        theme_chart = "walden";
    <?php } ?>
</script>

<!-- Modals -->
<?php require_once("Template/Modals/modalAlertas.php"); ?>


<!-- Vendor -->
<script src="<?= assets(); ?>/vendor/jquery/jquery.js"></script>
<script src="<?= assets(); ?>/vendor/jquery-browser-mobile/jquery.browser.mobile.js"></script>
<script src="<?= assets(); ?>/vendor/popper/umd/popper.min.js"></script>
<script src="<?= assets(); ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= assets(); ?>/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?= assets(); ?>/vendor/common/common.js"></script>
<script src="<?= assets(); ?>/vendor/nanoscroller/nanoscroller.js"></script>
<script src="<?= assets(); ?>/vendor/magnific-popup/jquery.magnific-popup.js"></script>
<script src="<?= assets(); ?>/vendor/jquery-placeholder/jquery.placeholder.js"></script>

<!-- Specific Page Vendor -->
<script src="<?= assets(); ?>/vendor/jquery-ui/jquery-ui.js"></script>
<script src="<?= assets(); ?>/vendor/jqueryui-touch-punch/jquery.ui.touch-punch.js"></script>
<script src="<?= assets(); ?>/vendor/jquery-appear/jquery.appear.js"></script>
<script src="<?= assets(); ?>/vendor/jquery-validation/jquery.validate.js"></script>
<script src="<?= assets(); ?>/vendor/select2/js/select2.js"></script>
<script src="<?= assets(); ?>/vendor/select2/js/i18n/es.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<script src="<?= assets(); ?>/vendor/jquery.easy-pie-chart/jquery.easypiechart.js"></script>
<script src="<?= assets(); ?>/vendor/flot/jquery.flot.js"></script>
<script src="<?= assets(); ?>/vendor/flot.tooltip/jquery.flot.tooltip.js"></script>
<script src="<?= assets(); ?>/vendor/flot/jquery.flot.pie.js"></script>
<script src="<?= assets(); ?>/vendor/flot/jquery.flot.categories.js"></script>
<script src="<?= assets(); ?>/vendor/flot/jquery.flot.resize.js"></script>
<script src="<?= assets(); ?>/vendor/jquery-sparkline/jquery.sparkline.js"></script>
<script src="<?= assets(); ?>/vendor/raphael/raphael.js"></script>
<script src="<?= assets(); ?>/vendor/morris/morris.js"></script>
<script src="<?= assets(); ?>/vendor/gauge/gauge.js"></script>
<script src="<?= assets(); ?>/vendor/snap.svg/snap.svg.js"></script>
<script src="<?= assets(); ?>/vendor/liquid-meter/liquid.meter.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/jquery.vmap.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/data/jquery.vmap.sampledata.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/maps/jquery.vmap.world.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.africa.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.asia.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.australia.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.europe.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.north-america.js"></script>
<script src="<?= assets(); ?>/vendor/jqvmap/maps/continents/jquery.vmap.south-america.js"></script>

<!-- INTERNAL Data tables js-->
<!-- <script src="<?= assets(); ?>/vendor/datatable/Bootstrap-5-5.3.0/js/bootstrap.bundle.min.js"></script> -->
<script src="<?= assets(); ?>/vendor/datatable/JSZip-3.10.1/jszip.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/pdfmake-0.2.7/pdfmake.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/pdfmake-0.2.7/vfs_fonts.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/DataTables-1.13.6/js/jquery.dataTables.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/DataTables-1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/Buttons-2.4.1/js/dataTables.buttons.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/Buttons-2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/Buttons-2.4.1/js/buttons.colVis.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/Buttons-2.4.1/js/buttons.html5.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/ColReorder-1.7.0/js/dataTables.colReorder.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/DateTime-1.5.1/js/dataTables.dateTime.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/FixedColumns-4.3.0/js/dataTables.fixedColumns.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/FixedHeader-3.4.0/js/dataTables.fixedHeader.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/KeyTable-2.10.0/js/dataTables.keyTable.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/Responsive-2.5.0/js/dataTables.responsive.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/Responsive-2.5.0/js/responsive.bootstrap5.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/RowGroup-1.4.0/js/dataTables.rowGroup.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/RowReorder-1.4.1/js/dataTables.rowReorder.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/Scroller-2.2.0/js/dataTables.scroller.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/SearchBuilder-1.5.0/js/dataTables.searchBuilder.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/SearchBuilder-1.5.0/js/searchBuilder.bootstrap5.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/SearchPanes-2.2.0/js/dataTables.searchPanes.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/SearchPanes-2.2.0/js/searchPanes.bootstrap5.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/Select-1.7.0/js/dataTables.select.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/StateRestore-1.3.0/js/dataTables.stateRestore.min.js"></script>
<script src="<?= assets(); ?>/vendor/datatable/StateRestore-1.3.0/js/stateRestore.bootstrap5.min.js"></script>
<script src="<?= assets(); ?>/vendor/bootstrap-maxlength/bootstrap-maxlength.js"></script>
<script src="<?= assets(); ?>/vendor/ios7-switch/ios7-switch.js"></script>

<!-- FILE UPLOADES JS -->
<script src="<?= assets(); ?>/vendor/fileuploads/js/fileupload.js"></script>
<script src="<?= assets(); ?>/vendor/fileuploads/js/file-upload.js"></script>

<!-- INTERNAL File-Uploads Js-->
<script src="<?= assets(); ?>/vendor/fancyuploder/jquery.ui.widget.js"></script>
<script src="<?= assets(); ?>/vendor/fancyuploder/jquery.fileupload.js"></script>
<script src="<?= assets(); ?>/vendor/fancyuploder/jquery.iframe-transport.js"></script>
<script src="<?= assets(); ?>/vendor/fancyuploder/jquery.fancy-fileupload.js"></script>
<script src="<?= assets(); ?>/vendor/fancyuploder/fancy-uploader.js"></script>

<!-- repeater -->
<script src="<?= assets(); ?>/vendor/repeater/jquery.repeater.min.js"></script>
<script src="<?= assets(); ?>/vendor/repeater/jquery.form-repeater.js"></script>

<!-- Summernote -->
<script type="text/javascript" src="<?= assets(); ?>/vendor/summernote-0.8.20/summernote-lite.js"></script>
<script type="text/javascript" src="<?= assets(); ?>/vendor/summernote-0.8.20/lang/summernote-es-ES.js"></script>

<!-- Datepicker-Master -->
<script src="<?= assets(); ?>/vendor/datepicker-master/datepicker.js?v=<?= version(); ?>"></script>
<script src="<?= assets(); ?>/vendor/datepicker-master/i18n/datepicker.es-ES.js?v=<?= version(); ?>"></script>


<!-- INPUT MASK JS-->
<script src="<?= assets(); ?>/vendor/input-mask/jquery.mask.min.js?v=<?= version(); ?>"></script>

<!-- Theme Base, Components and Settings -->
<script src="<?= assets(); ?>/js/theme.js"></script>

<!-- Theme Custom -->
<script src="<?= assets(); ?>/js/custom.js?v=<?= version(); ?>"></script>

<!-- Theme Initialization Files -->
<script src="<?= assets(); ?>/js/theme.init.js?v=<?= version(); ?>"></script>