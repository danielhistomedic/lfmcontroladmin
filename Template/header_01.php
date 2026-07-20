<!doctype html>
<html class="fixed sidebar-light">
<!-- <html class="fixed"> -->

<head>

    <!-- Basic -->
    <meta charset="UTF-8">

    <title><?= $data['page_title']; ?></title>

    <meta name="keywords" content="<?= $data['meta_keywords']; ?>" />
    <meta name="description" content="<?= $data['meta_description']; ?>">
    <meta name="author" content="Histoclin Sistemas - Ing. Daniel Perez">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="<?= assets(); ?>/img/favicon.ico" />


    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />


    <!-- Web Fonts  -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->

    <link rel="stylesheet" href="<?= assets(); ?>/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/animate/animate.compat.css">

    <link rel="stylesheet" href="<?= assets(); ?>/vendor/font-awesome/css/sharp-light.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/font-awesome/css/sharp-regular.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/font-awesome/css/sharp-solid.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/font-awesome/css/all.min.css" />

    <link rel="stylesheet" href="<?= assets(); ?>/vendor/boxicons/css/boxicons.min.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/jquery-ui/jquery-ui.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/jquery-ui/jquery-ui.theme.css" />

    <link rel="stylesheet" href="<?= assets(); ?>/vendor/hover/hover.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/select2/css/select2.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/select2-bootstrap-theme/select2-bootstrap.min.css" />

    <!-- <link href="<?= assets(); ?>/vendor/datatable/Bootstrap-5-5.3.0/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="<?= assets(); ?>/vendor/datatable/DataTables-1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/Buttons-2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/ColReorder-1.7.0/css/colReorder.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/DateTime-1.5.1/css/dataTables.dateTime.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/FixedColumns-4.3.0/css/fixedColumns.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/FixedHeader-3.4.0/css/fixedHeader.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/KeyTable-2.10.0/css/keyTable.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/Responsive-2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/RowGroup-1.4.0/css/rowGroup.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/RowReorder-1.4.1/css/rowReorder.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/Scroller-2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/SearchBuilder-1.5.0/css/searchBuilder.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/SearchPanes-2.2.0/css/searchPanes.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/Select-1.7.0/css/select.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= assets(); ?>/vendor/datatable/StateRestore-1.3.0/css/stateRestore.bootstrap5.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?= assets(); ?>/vendor/bootstrap-multiselect/css/bootstrap-multiselect.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/morris/morris.css" />

    <!-- summernote -->
    <link rel="stylesheet" type="text/css" href="<?= assets(); ?>/vendor/summernote-0.8.20/summernote-lite.css" />

    <!-- Datepicker-Master -->
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/datepicker-master/datepicker.css?v=<?= version(); ?>">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/css/theme.css" />

    <!-- Theme Layout -->
    <link rel="stylesheet" href="<?= assets(); ?>/css/layouts/modern.css" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/css/skins/default.css?v=<?= version(); ?>" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/css/custom.css?v=<?= version(); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assets(); ?>/app/css/custom.css?v=<?= version(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?= assets(); ?>/app/css/fancy_upload.css?v=<?= version(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?= assets(); ?>/app/css/dropify.css?v=<?= version(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?= assets(); ?>/app/css/datatable-style.css?v=<?= version(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?= assets(); ?>/app/css/select2-style.css?v=<?= version(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?= assets(); ?>/app/css/date-picker-style.css?v=<?= version(); ?>" />

    <!-- Head Libs -->
    <script src="<?= assets(); ?>/vendor/modernizr/modernizr.js"></script>