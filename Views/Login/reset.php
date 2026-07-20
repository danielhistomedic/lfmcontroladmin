<!DOCTYPE html>
<html lang="es">

<head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


    <!-- Meta -->
    <meta name="description" content="<?= $data['page_content']; ?>">
    <meta name="author" content="Histoclin Sistema - Ing. Daniel Perez">

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
        <div class="col-12 bg-custom-login bg-img-login-right d-flex align-items-center justify-content-center">

            <div class="dimmer active">
                <div class="lds-ring">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>

            <div class="bg-login-right-cover">
            </div>


            <div class="z-index-10 login-wrapper wd-250 wd-sm-450 mg-y-30 p-4 shadow-sm reset-border">

                <div class="tx-center mg-b-10">
                    <img src="<?= assets(); ?>/img/logo-sm-dark.png" height="90" alt="logo" class="auth-logo">
                </div>

                <h4 class="tx-inverse tx-center tx-uppercase tx-semibold">Restablecer Contraseña</h4>
                <p class="tx-center mg-b-30 tx-custom-subtitle">Estas a un paso de restablecer tu contraseña, llena los datos requeridos y ¡listo!.</p>

                <!-- Form Login -->
                <form name="formCambiarPassword" id="formCambiarPassword" action="" data-parsley-validate>

                    <input type="hidden" name="inputUserId" value="<?= $data['userId']; ?>">

                    <!-- Password -->
                    <div class="form-group show-password-parent">
                        <label class="d-block tx-12 tx-spacing-1 ml-1 login-label" for="inputResetPassword">Nueva Contraseña:</label>
                        <span class="bar-left-input-init"><i class="fa-thin fa-unlock-keyhole fa-fw tx-18 lh-0 op-6"></i></span>
                        <input type="password" class="form-control inputForm100" name="inputResetPassword" id="inputResetPassword" placeholder="Ingrese Nueva Contraseña" required="">
                        <span class="show-password show text-primary"><i class="fa-regular fa-eye mostrar-password"></i></span>
                    </div><!-- form-group -->
                    <!-- Fin Password -->

                    <!-- Password -->
                    <div class="form-group show-password-parent">
                        <label class="d-block tx-12 tx-spacing-1 ml-1 login-label" for="inputResetConfirmPassword">Confirmar Nueva Contraseña:</label>
                        <span class="bar-left-input-init"><i class="fa-thin fa-unlock-keyhole fa-fw tx-18 lh-0 op-6"></i></span>
                        <input type="password" class="form-control inputForm100" name="inputResetConfirmPassword" id="inputResetConfirmPassword" placeholder="Confirmar Nueva Contraseña" required="">
                        <span class="show-password show text-primary"><i class="fa-regular fa-eye mostrar-password"></i></span>
                    </div><!-- form-group -->
                    <!-- Fin Password -->

                    <div class="form-group">
                        <button type="submit" class="btn btn-pill btn-primary-gradient me-2 btn-inicio-guardar d-flex justify-content-center align-items-center" id="btnActionFormResetPassword">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="fa-regular fa-user-unlock fa-fw fa-lg me-1"></i>
                                <span class="">Restablecer Contraseña</span>
                                <i class="fa-thin fa-loader fa-spin fa-fw fa-lg ms-2" style="display:none;"></i>
                            </div>
                        </button>
                    </div>

                </form>
                <!-- Fin Form Login -->

            </div><!-- login-wrapper -->

        </div><!-- col -->


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