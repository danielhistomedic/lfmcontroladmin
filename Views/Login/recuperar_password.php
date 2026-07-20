<!DOCTYPE html>
<html lang="es">

<head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


    <!-- Meta -->
    <meta name="description" content="<?= $data['page_content']; ?>">
    <meta name="author" content="Ing. Ind. Victor Daniel Perez Vargas">

    <title><?= $data['page_tag']; ?></title>

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= assets(); ?>/img/favicon.ico">

    <!-- Font Awesome -->
    <link href="<?= assets(); ?>/iconfonts/fontawesome/css/all.css" rel="stylesheet">

    <!-- Material Design Icons -->
    <link rel="stylesheet" href="<?= assets(); ?>/iconfonts/materialdesignicons-6.1.95/css/materialdesignicons.min.css">

    <!-- BOOTSTRAP CSS -->
    <link href="<?= assets(); ?>/plugins/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- MDB -->
    <link href="<?= assets(); ?>/plugins/MDB5-3.9.0/css/mdb.min.css" rel="stylesheet" />

    <!-- Animate -->
    <link href="<?= assets(); ?>/lib/animate/animate.css" rel="stylesheet" type="text/css">

    <!-- Select2 -->
    <link href="<?= assets(); ?>/lib/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

    <!-- Bracket CSS -->
    <link rel="stylesheet" href="<?= assets(); ?>/css/bracket.css?v=<?= version(); ?>">
    <link rel="stylesheet" href="<?= assets(); ?>/css/custom.css?v=<?= version(); ?>">

    <!-- STYLE CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="<?= assets(); ?>/css/style.css?v=<?= version(); ?>" />
    <link href="<?= assets(); ?>/css/skin-modes.css" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="<?= assets(); ?>/colors/color1.css?v=<?= version(); ?>" />

    <!-- Custom CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="<?= assets(); ?>/css/main.css?v=<?= version(); ?>" />
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="<?= assets(); ?>/css/main-dark-mode.css?v=<?= version(); ?>" />


</head>

<body>
    <?php require_once("Template/loading.php"); ?>

    <div class="row no-gutters flex-row-reverse ht-100v">

        <!-- bg-gray-100  bg-delicate  -->
        <div class="col-lg-6 bg-custom-login bg-img-login-right-recoverpassword d-flex align-items-center justify-content-center">

            <!-- Preloader -->
            <div class="dimmer active">
                <div class="lds-double-ring">
                    <div></div>
                    <div></div>
                </div>
            </div>

            <div class="bg-login-right-cover">
            </div>


            <div class="z-index-10 login-wrapper wd-250 wd-sm-350 mg-y-30">

                <div class="tx-center ml-1 login-logo-right">
                    <img src="<?= assets(); ?>/img/logo-sm-dark.png" height="120" alt="logo" class="auth-logo">
                </div>

                <h4 class="tx-inverse tx-center tx-uppercase tx-semibold">¿Olvidaste tu Contraseña?</h4>
                <p class="tx-center mg-b-60 tx-custom-subtitle">No te preocupes, coloca los datos requeridos para generar una nueva</p>

                <!-- Form Login -->
                <form name="formResetPassword" id="formResetPassword" action="" data-parsley-validate>

                    <div class="form-group">
                        <label class="d-block tx-12 tx-spacing-1 ml-1 login-label">Introduzca email de su cuenta:</label>
                        <span class="bar-left-input-init"><i class="fa-thin fa-at fa-fw tx-18 lh-0 op-6"></i></span>
                        <input type="email" class="form-control" name="inputEmailReset" id="inputEmailReset" placeholder="Ingrese email" value="" required>
                    </div><!-- form-group -->

                    <div class="form-group">
                        <button type="submit" class="btn btn-pill btn-primary-gradient me-2 btn-inicio-guardar d-flex justify-content-center align-items-center" id="btnActionFormReset">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fa-regular fa-paper-plane fa-fw fa-lg me-1"></i>
                                <span class="">Enviar Solicitud</span>
                                <i class="fa-thin fa-loader fa-spin fa-fw fa-lg ms-2" style="display:none;"></i>
                            </div>
                        </button>
                    </div>

                </form>
                <!-- Fin Form Login -->

                <div class="mg-t-60 tx-center"><a href=" <?= base_url(); ?>/login" class="tx-primary"><i class="fa-regular fa-angle-left fa-fw"></i> Regresar a Inicio de Sesión</a></div>

            </div><!-- login-wrapper -->

        </div><!-- col -->



        <div class="col-lg-6 bg-white panel-login-left bg-img-login-left-recoverpassword d-flex align-items-center justify-content-center">

            <div class="bg-login-left-cover">
            </div>

            <div class="wd-350 wd-xl-450 mg-y-30 login-info">
                <div class="tx-center">
                    <img src="<?= assets(); ?>/img/logo-sm-dark.png" height="120" alt="logo" class="auth-logo">
                </div>
                <h5 class="tx-inverse tx-center tx-gray-100 mt-2 mb-0 ">Sistema de Gestión Médico-Administrativa</h5>
                <div class="tx-center tx-gray-200 mg-b-60">Expediente Clínico Electrónico</div>

                <h5 class="tx-inverse tx-gray-100">¿Por qué Histoclin?</h5>
                <p class="tx-gray-200 tx-13">En Histoclin deseamos que tengas una experiencia única en el manejo de tus expedientes clínicos.</p>
                <p class="tx-gray-200 tx-13 mg-b-60">Nos comprometemos a darte el mejor servicio que mereces, para que el uso de Histoclin sea un complemento amigable a tu proceso de Consulta y no una carga adicional.</p>

            </div><!-- wd-500 -->

        </div>

    </div><!-- row -->


    <!-- ########## START: MODAL ALERTAS ########## -->
    <?php require_once("Views/Template/Modals/modalAlertas.php"); ?>
    <!-- ########## END: MODAL ALERTAS ########## -->

    <!-- Custom Scripts -->
    <script>
        const base_url = "<?= base_url(); ?>";
    </script>

    <!-- JQUERY JS -->
    <script src="<?= assets(); ?>/js/jquery.min.js?v=<?= version(); ?>"></script>

    <!-- BOOTSTRAP JS -->
    <script src="<?= assets(); ?>/plugins/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js?v=<?= version(); ?>"></script>

    <!-- MDB -->
    <script type="text/javascript" src="<?= assets(); ?>/plugins/MDB5-3.9.0/js/mdb.min.js?v=<?= version(); ?>"></script>

    <!-- Bracket -->
    <!-- <script src="<?= assets(); ?>/template/js/bracket.js"></script> -->

    <!-- Parsley -->
    <script src="<?= assets(); ?>/lib/parsleyjs/parsley.min.js?v=<?= version(); ?>"></script>
    <script src="<?= assets(); ?>/lib/parsleyjs/i18n/es.js?v=<?= version(); ?>"></script>

    <!-- CUSTOM JS-->
    <!-- <script src="<?= assets(); ?>/js/custom.js?v=<?= version(); ?>"></script> -->

    <!-- Custom javascripts-->
    <script src="<?= assets(); ?>/js/functions/function_animate.min.js?v=<?= version(); ?>"></script>
    <script src="<?= assets(); ?>/js/functions/functions_admin.js?v=<?= version(); ?>"></script>
    <!-- <script src="<?= assets(); ?>/js/functions/herramientas/function_usuarios.js?v=<?= version(); ?>"></script> -->
    <script src="<?= assets(); ?>/js/functions/<?= $data['page_functions_js']; ?>?v=<?= version(); ?>"></script>


</body>

</html>