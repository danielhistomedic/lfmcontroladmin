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

                    <form id="formRecords" class="validate-form needs-validation" novalidate>

                        <div class="form-group col-12 col-sm-6 col-xl-4 position-relative">
                            <label class="form-label" for="password">Contraseña:</label>
                            <input class="form-control btn-square" id="password" name="password" type="password" placeholder="Ingrese contraseña" required="">
                            <div class="show-hide-cambiar"><span class="show"> </span></div>
                            <div class="invalid-feedback">Valor requerido.</div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-xl-4 position-relative">
                            <label class="form-label" for="confirmar_password">Confirmar Contraseña:</label>
                            <input class="form-control btn-square" id="confirmar_password" name="confirmar_password" type="password" placeholder="Confirmar contraseña" required="">
                            <div class="show-hide-cambiar-confirm"><span class="show"> </span></div>
                            <div class="invalid-feedback">Valor requerido.</div>
                        </div>

                        <div class="form-group col-12 ">
                            <hr class="mt-2 mb-2">
                        </div>

                        <div class="form-group col-12 mb-0">
                            <button class="btn btn-secondary hvr-float-shadow <?php
                                                                                if ($data['permisosMod']['c'] == 0 && $data['permisosMod']['u'] == 0) {
                                                                                    echo 'disabled';
                                                                                } ?>" type="submit" id="btnCambiarPassword">
                                <i class="fa-regular fa-floppy-disk-pen fa-fw fa-lg me-1"></i>Guardar
                            </button>
                        </div>

                    </form>

                </div>

            </section>
        </div>




    </div>


    <!-- end: page -->
</section>
<!-- FIN CONTENIDO VISTA -->


<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<!-- Theme Custom -->


<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>