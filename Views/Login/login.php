<!doctype html>
<html class="fixed">

<head>

    <!-- Basic -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="<?= $data['meta_keywords']; ?>" />
    <meta name="description" content="<?= $data['meta_description']; ?>">
    <meta name="author" content="<?= $data['meta_author']; ?>">

    <!-- FAVICON -->
    <link rel="icon" type="image/x-icon" href="<?= assets(); ?>/img/favicon.ico" />
    <!-- <link rel="icon" type="image/png" href="<?= assets(); ?>/img/favicon.png" /> -->

    <!-- TITLE -->
    <title><?= $data['title']; ?></title>

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

<body class="login-img">

    <?php require_once("Template/loading.php"); ?>

    <!-- start: page -->
    <section class="body-sign">
        <div class="center-sign">

            <div class="panel card-sign position-relative">

                <a href="javascript:void(0)" class="logo float-start" style="position: absolute; top: -30px;">
                    <img src="<?= assets(); ?>/img/logo_ini.png" width="auto" height="100" alt="Logo" />
                </a>

                <div class="card-title-sign mt-3 mb-0 text-end">
                    <h2 class="title text-uppercase font-weight-bold m-0"><i class="bx bx-user-circle me-1 text-6 position-relative top-5"></i> Iniciar Sesión</h2>
                </div>
                <div class="card-body loading-form-showing">

                    <div class="loading-form">
                        <div class="bounce-loader">
                            <div class="bounce1"></div>
                            <div class="bounce2"></div>
                            <div class="bounce3"></div>
                        </div>
                    </div>

                    <form id="form" class="row needs-validation mb-2" action="" method="" novalidate="">

                        <div class="form-group mb-3">
                            <label for="email">Usuario</label>
                            <div class="input-group">
                                <input id="email" name="email" type="email" class="form-control " value="<?php if (isset($_GET['usuario'])) {
                                                                                                                echo $_GET['usuario'];
                                                                                                            } ?>" required />
                                <span class="input-group-text">
                                    <i class="bx bx-user text-4"></i>
                                </span>
                                <div class="invalid-feedback">Ingrese usuario correctamente.</div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <!-- <div class="clearfix">
                                <label for="pass" class="float-start">Contraseña</label>
                                <a href="pages-recover-password.html" class="float-end">¿Olvidaste tu contraseña?</a>
                            </div> -->
                            <div class="input-group position-relative">
                                <input id="pass" name="pass" type="password" class="form-control " value="<?php if (isset($_GET['pwd'])) {
                                                                                                                echo $_GET['pwd'];
                                                                                                            } ?>" required />
                                <input type="hidden" name="token" value="<?= $data['token'] ?>">
                                <div class="show-hide-login"><span class="show"> </span></div>
                                <span class="input-group-text">
                                    <i class="bx bx-lock text-4"></i>
                                </span>
                                <div class="invalid-feedback">Debe indicar contraseña.</div>
                            </div>
                        </div>

                        <div class="form-group col-12 pt-0 ">
                            <div class="row ">
                                <div class="col-8 mt-2">
                                    <div class="checkbox-custom checkbox-default">
                                        <input id="recordarme" name="recordarme" type="checkbox" />
                                        <label for="recordarme">Recordarme</label>
                                        <div class="invalid-feedback">Debe marcar la casilla.</div>
                                    </div>
                                </div>
                                <div class="col-sm-4 text-end">
                                    <button type="submit" class="btn btn-primary mt-2 hvr-float-shadow">Ingresar</button>
                                </div>
                            </div>

                        </div>



                        <!-- <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                            <span>or</span>
                        </span> -->

                        <!-- <div class="mb-1 text-center">
                            <a class="btn btn-facebook mb-3 ms-1 me-1" href="#">Connect with <i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-twitter mb-3 ms-1 me-1" href="#">Connect with <i class="fab fa-twitter"></i></a>
                        </div> -->

                        <!-- <p class="text-center mt-3">¿Aún no tienes cuenta? <a href="<?= base_url(); ?>/registro"> ¡Registrarse!</a></p> -->

                    </form>
                </div>
            </div>

            <p class="text-center text-muted mt-3 mb-3">LFM Control de Fluidos&copy;2024. Todos los derechos reservados.</p>
        </div>
    </section>
    <!-- end: page -->

    <!-- Modals -->
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