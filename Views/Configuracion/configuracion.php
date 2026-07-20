<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Theme Custom CSS -->


<!-- Header Admin 01 -->
<?php require_once("Template/header_02.php"); ?>


<!-- CONTENIDO DE VISTA -->
<section role="main" class="content-body fondo-general">

    <header class="page-header">
        <h2><?= $data['page_form_title']; ?></h2>

        <div class="right-wrapper text-end">
            <ol class="breadcrumbs">
                <li>
                    <a href="<?= base_url() ?>/inicio">
                        <i class="bx bx-home-alt"></i>
                    </a>
                </li>

                <li><span>Inicio</span></li>

                <li><span><?= $data['page_breadcrumb']; ?></span></li>

            </ol>
            <div class="sidebar-right-toggle" style="cursor: default;"> </div>
            <!-- <a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fas fa-chevron-left"></i></a> -->
        </div>
    </header>

    <!-- start: page -->
    <div class="row">

        <div class="col-12">

            <section class="card card-featured shadow-sm mb-4">

                <header class="card-header">
                    <h2 class="card-title"><?= $data['page_card_title']; ?> <i title="Info" style="cursor:pointer;" class="text-primary fa-light fa-circle-question" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="false" aria-controls="collapseInfo"></i></h2>
                    <div class="collapse mt-1" id="collapseInfo">
                        <span class=" text-info fw-normal"><?= $data['page_card_description']; ?></span>
                    </div>
                </header>

                <div class="p-4 card-body loading-panel-showing">

                    <div class="loading-panel">
                        <div class="bounce-loader">
                            <div class="bounce1"></div>
                            <div class="bounce2"></div>
                            <div class="bounce3"></div>
                        </div>
                    </div>

                    <!-- Crear/Editar Datos -->
                    <div class="" id="panel_crear_editar">

                        <form class="theme-form needs-validation position-relative" id="formRecords" novalidate="">

                            <div class="row m-t-10">

                                <!-- Subtitulos Editar Datos -->
                                <div class="form-group col-12 ">
                                    <div class="border-bottom subtitulos_panel">
                                        <p class="mb-0 fw-600 text-primary fw-semibold"><i class="fa-regular fa-pencil text-secondary text-4"></i> Datos a Registrar/Editar.</p>
                                    </div>
                                </div>
                                <!-- Fin Subtitulos Editar Datos -->

                                <!-- SMTP Host  -->
                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="smtp_host">SMTP Host</label>
                                    <input type="text" class="form-control" name="smtp_host" id="smtp_host" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>
                                <!-- Fin SMTP Host  -->

                                <!-- SMTP Usuario  -->
                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="smtp_usuario">SMTP Usuario</label>
                                    <input type="text" class="form-control" name="smtp_usuario" id="smtp_usuario" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>
                                <!-- Fin SMTP Usuario  -->

                                <!-- SMTP Password  -->
                                <div class="form-group col-12 col-sm-6 col-xl-3 position-relative">
                                    <label class="control-label" for="smtp_password">SMTP Contraseña:</label>
                                    <input class="form-control" id="smtp_password" name="smtp_password" type="password" placeholder="" autocomplete="off" required>
                                    <div class="show-hide-smtp"><span class="show"> </span></div>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>
                                <!-- Fin SMTP Password  -->

                                <!-- SMTP Puerto  -->
                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="smtp_puerto">SMTP Puerto</label>
                                    <input type="text" class="form-control" name="smtp_puerto" id="smtp_puerto" placeholder="" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>
                                <!-- Fin SMTP Puerto  -->

                                <div class="form-group col-12 ">
                                    <hr class="mt-2 mb-2">
                                </div>

                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="telefono_contacto">Telefono de Contacto:</label>
                                    <input type="text" class="form-control" name="telefono_contacto" id="telefono_contacto" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="email_contacto">Correo de Contacto:</label>
                                    <input type="text" class="form-control" name="email_contacto" id="email_contacto" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="url_tienda">URL Tienda:</label>
                                    <input type="text" class="form-control" name="url_tienda" id="url_tienda" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="nombre_remitente">Nombre Remitente:</label>
                                    <input type="text" class="form-control" name="nombre_remitente" id="nombre_remitente" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="email_remitente">Email Remitente:</label>
                                    <input type="text" class="form-control" name="email_remitente" id="email_remitente" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="sitio_web">URL Sitio Web:</label>
                                    <input type="text" class="form-control" name="sitio_web" id="sitio_web" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>

                                <div class="form-group col-12 ">
                                    <hr class="mt-2 mb-2">
                                </div>

                                <div class="form-group col-12 col-sm-6 col-xl-3">
                                    <label class="control-label" for="email_destino">Sitio Contactanos - Email Destino:</label>
                                    <input type="text" class="form-control" name="email_destino" id="email_destino" placeholder="" autocomplete="off" required>
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>


                                <div class="form-group col-12 ">
                                    <hr class="mt-2 mb-2">
                                </div>

                                <div class="form-group col-12 mb-0">
                                    <input type="hidden" name="id" id="record_id" value="">
                                    <button class="btn btn-secondary hvr-float-shadow <?php
                                                                                        if ($data['permisosMod']['c'] == 0 && $data['permisosMod']['u'] == 0) {
                                                                                            echo 'disabled';
                                                                                        } ?>" type="submit" id="btnGuardar">
                                        <i class="fa-regular fa-floppy-disk-pen fa-fw fa-lg me-1"></i>Guardar
                                    </button>
                                </div>

                            </div>

                        </form>

                    </div>
                    <!-- Fin Editar Datos -->

                </div>

            </section>
        </div>
    </div>

</section>
<!-- FIN CONTENIDO VISTA -->


<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<!-- Theme Custom -->

<!-- Summernote -->
<!-- <script type="text/javascript" src="<?= assets(); ?>/plugins/summernote-0.8.20/summernote-lite.js"></script> -->
<!-- <script type="text/javascript" src="<?= assets(); ?>/plugins/summernote-0.8.20/lang/summernote-es-ES.js"></script> -->

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>