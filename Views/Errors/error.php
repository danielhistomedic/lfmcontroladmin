<!doctype html>
<html class="fixed">

<head>

    <!-- Basic -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">



    <meta name="keywords" content="error, panel, administracion" />
    <meta name="description" content="<?= $data['page_description']; ?>">
    <meta name="author" content="Histoclin Systems">

    <!-- FAVICON -->
    <link rel="icon" type="image/x-icon" href="<?= assets(); ?>/img/favicon.ico" />
    <!-- <link rel="icon" type="image/png" href="<?= assets(); ?>/img/favicon.png" /> -->

    <!-- TITLE -->
    <title><?= $data['page_title']; ?></title>

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <!-- Web Fonts  -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800|Shadows+Into+Light" rel="stylesheet" type="text/css"> -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"> -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet"> -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/animate/animate.compat.css">
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/font-awesome/css/all.min.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/font-awesome/css/sharp-solid.min.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/boxicons/css/boxicons.min.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/magnific-popup/magnific-popup.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/select2/css/select2.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/select2-bootstrap-theme/select2-bootstrap.min.css" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/vendor/hover/hover.css" />
    <link rel="stylesheet" href="<?= assets(); ?>/app/css/select2-style.css" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/css/theme.css?v=<?= version(); ?>" />

    <!-- Skin CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/css/skins/default.css?v=<?= version(); ?>" />

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/app/css/custom.css?v=<?= version(); ?>">

    <!-- Head Libs -->
    <script src="<?= assets(); ?>/vendor/modernizr/modernizr.js"></script>

</head>

<body class="login-registro-img">

    <?php require_once("Template/loading.php"); ?>

    <!-- start: page -->
    <section class="body-error error-outside">
        <div class="center-error">

            <div class="error-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-lg-8">
                                <a href="/" class="logo">
                                    <img src="<?= assets(); ?>/img/logo.png" height="70" alt="Porto Admin" />
                                </a>
                            </div>
                            <!-- <div class="col-lg-4">
                                <form class="form">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="q" id="q" placeholder="Search...">
                                        <button class="btn btn-default" type="submit"><i class="bx bx-search"></i></button>
                                    </div>
                                </form>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <!-- <a href="javascript:void(0)" class="logo text-center d-block mb-4">
                        <img src="<?= assets(); ?>/img/logo.png" height="80" alt="Logo" />
                    </a> -->
                    <div class="main-error mb-3">
                        <h2 class="error-code text-white text-center font-weight-semibold m-0">404 <i class="fas fa-file"></i></h2>
                        <p class="error-explanation text-white text-center">Lo sentimos, pero la página que estabas buscando no existe..</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <h4 class="text text-white">Aquí hay algunos enlaces útiles</h4>
                    <ul class="nav nav-list flex-column primary">
                        <li class="nav-item">
                            <a class="nav-link text-info" href="<?= base_url() . '/inicio' ?>"><i class="fas fa-caret-right "></i> Inicio</a>
                        </li>
                        <!-- <li class="nav-item">
                            <a target="_blank" class="nav-link text-info" href="<?= base_url(); ?>/tienda"><i class="fas fa-caret-right"></i> Ir a Tienda</a>
                        </li> -->
                        <!-- <li class="nav-item">
                            <a class="nav-link text-info" href="<?= base_url() . '/login' ?>"><i class="fas fa-caret-right "></i> Login</a>
                        </li> -->
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- end: page -->


    <!-- Modals -->
    <div id="loadTerminosCondiciones"></div>
    <?php require_once("Template/Modals/modalAlertas.php"); ?>

    <!-- Constantes -->
    <script>
        const base_url = "<?= base_url(); ?>";
        const assets = "<?= assets(); ?>";
    </script>

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
    <!-- <script src="<?= assets(); ?>/vendor/jquery-validation/jquery.validate.js"></script> -->
    <script src="<?= assets(); ?>/vendor/select2/js/select2.js"></script>
    <script src="<?= assets(); ?>/vendor/select2/js/i18n/es.js"></script>
    <script src="<?= assets(); ?>/vendor/jquery-appear/jquery.appear.js"></script>

    <!-- Theme Base, Components and Settings -->
    <script src="<?= assets(); ?>/js/theme.js?v=<?= version(); ?>"></script>

    <!-- Theme Initialization Files -->
    <script src="<?= assets(); ?>/js/theme.init.js?v=<?= version(); ?>"></script>

    <!-- Theme Custom -->
    <script src="<?= assets(); ?>/js/custom.js?v=<?= version(); ?>"></script>

    <!-- Custom -->
    <script src="<?= assets(); ?>/app/js/animate.js?v=<?= version(); ?>"></script>
    <script src="<?= assets(); ?>/app/js/alertas.js?v=<?= version(); ?>"></script>
    <script src="<?= assets(); ?>/app/js/<?= $data['page_functions_js']; ?>?v=<?= version(); ?>"></script>


</body>

</html>