<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Custom Plugins CSS -->

<!-- Custom Plugins CSS end -->

<!-- Header Admin 02 -->
<?php require_once("Template/header_02.php"); ?>

<!-- Page Header -->
<?php require_once("Template/page_header.php"); ?>

<!-- CONTENIDO PRINCIPAL -->
<!-- app-content open -->
<div class="main-content app-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title"><?= $data['page_form_title']; ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url(); ?>/inicio">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="#">Tutoriales</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $data['page_breadcrumb']; ?></li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->


            <!-- ROW OPEN -->
            <div class="row row-cards mb-5">

                <div class="col-12">

                    <div class="row mb-5">

                        <div class="col-12">

                            <div class="accordion" id="accordionExample">

                                <div class="accordion-item">

                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed active" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                            <i class="fa-solid fa-sliders fa-fw text-secondary me-1"></i> Opciones de Filtro
                                        </button>
                                    </h2>

                                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample" style="">

                                        <div class="accordion-body position-relative">

                                            <div class="dimmer active">
                                                <div class="lds-ring">
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                </div>
                                            </div>

                                            <div class="row">

                                                <div class="form-group col-12 col-md-4">
                                                    <label class="form-label" for="comboMenus">Menus:</label>
                                                    <div class="d-flex" style="position:relative;">
                                                        <select class="select2 custom-select" id="comboMenus" name="comboMenus" style="width: 100%"></select>
                                                        <button class="ms-1 btn btn-outline-primary btn-filtro m0-auto" type="button" id="btnAplicarFiltro_Menu">
                                                            <i class="fa-regular fa-filter fa-lg fa-fw"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="form-group col-12 col-md-4">
                                                    <label class="form-label" for="comboSubmenus">Submenus:</label>
                                                    <div class="d-flex" style="position:relative;">
                                                        <div class="dimmer-object active">
                                                            <div class="lds-ring-object">
                                                                <div></div>
                                                                <div></div>
                                                                <div></div>
                                                                <div></div>
                                                            </div>
                                                        </div>
                                                        <select class="select2 custom-select" id="comboSubmenus" name="comboSubmenus" style="width: 100%">
                                                        </select>
                                                        <button class="ms-1 btn btn-outline-primary btn-filtro m0-auto" type="button" id="btnAplicarFiltro_Submenu">
                                                            <i class="fa-regular fa-filter fa-lg fa-fw"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>

                                            <!-- <div class="form-group mb-0">
                                                <button class="btn btn-secondary" type="button" id="btnBuscar">
                                                    <i class="fa-regular fa-magnifying-glass fa-fw me-1"></i>Buscar
                                                </button>
                                            </div> -->

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <form class="theme-form needs-validation position-relative mb-5" name="formProducto" id="formProducto" novalidate="">
                        <div class="dimmer-object-search active">
                            <div class="lds-ring-object-search">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>

                        <div class="input-group">
                            <input type="text" class="form-control" name="tutorial_filtro" id="tutorial_filtro" placeholder="Busqueda ..." required>
                            <button type="submit" class="input-group-text btn btn-primary rounded-end"><i class="fa fa-search" aria-hidden="true"></i></button>
                            <div class="invalid-feedback">Valor requerido.</div>
                        </div>
                    </form>
                    <!-- <div class="input-group mb-5">
                        <input type="text" class="form-control" placeholder="Buscar" id="buscar" name="buscar">
                        <div class="input-group-text btn btn-primary">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </div>
                    </div> -->

                    <div id="contents">




                        <!-- <div class="row border-bottom mb-2">
                            <div class="form-group d-md-flex">
                                <div class="card overflow-hidden">
                                    <video class="" width="100%" controls>
                                        <source src="<?= assets(); ?>/files/videos/regedit_32_bits.mp4" type="video/mp4">
                                        <source src="<?= assets(); ?>/files/videos/regedit_32_bits.ogg" type="video/ogg">
                                        Tu Navegador no soporta HTML video.
                                    </video>
                                </div>
                                <div class="ms-0 ms-md-4 mt-3 mt-md-0">
                                    <ol class="breadcrumb text-primary fs-12 mb-3">
                                        <li class="breadcrumb-item">INSTALACION</li>
                                        <li class="breadcrumb-item">INSTALAR ACTUALIZACION</li>
                                    </ol>
                                    <h4 class="fw-bold">¿Como abrir un expediente?</h4>
                                    <p>Procedimiento para abrir un expediente clínico de un paciente que ya ha sido recepcionado en el sistema por el personal de recepción.</p>
                                </div>
                            </div>
                        </div>

                        <div class="row border-bottom mb-2">
                            <div class="form-group d-md-flex">
                                <div class="card overflow-hidden">
                                    <video class="" width="100%" controls>
                                        <source src="<?= assets(); ?>/files/videos/regedit_64_bits.mp4" type="video/mp4">
                                        <source src="<?= assets(); ?>/files/videos/regedit_64_bits.ogg" type="video/ogg">
                                        Tu Navegador no soporta HTML video.
                                    </video>
                                </div>
                                <div class="ms-0 ms-md-4 mt-3 mt-md-0">
                                    <ol class="breadcrumb text-primary fs-12 mb-3">
                                        <li class="breadcrumb-item">INSTALACION</li>
                                        <li class="breadcrumb-item">INSTALAR ACTUALIZACION</li>
                                    </ol>
                                    <h4 class="fw-bold">¿Como abrir un expediente?</h4>
                                    <p>Procedimiento para abrir un expediente clínico de un paciente que ya ha sido recepcionado en el sistema por el personal de recepción.</p>
                                </div>
                            </div>
                        </div>

                        <div class="row border-bottom mb-2">
                            <div class="form-group d-md-flex">
                                <div class="card overflow-hidden">
                                    <video class="" width="100%" controls>
                                        <source src="<?= assets(); ?>/files/videos/censo_pacientes_hosp.mp4" type="video/mp4">
                                        <source src="<?= assets(); ?>/files/videos/censo_pacientes_hosp.ogg" type="video/ogg">
                                        Tu Navegador no soporta HTML video.
                                    </video>
                                </div>
                                <div class="ms-0 ms-md-4 mt-3 mt-md-0">
                                    <ol class="breadcrumb text-primary fs-12 mb-3">
                                        <li class="breadcrumb-item">INSTALACION</li>
                                        <li class="breadcrumb-item">INSTALAR ACTUALIZACION</li>
                                    </ol>
                                    <h4 class="fw-bold">¿Como abrir un expediente?</h4>
                                    <p>Procedimiento para abrir un expediente clínico de un paciente que ya ha sido recepcionado en el sistema por el personal de recepción.</p>
                                </div>
                            </div>
                        </div>


                        <div class="row border-bottom mb-2">
                            <div class="form-group d-md-flex">
                                <div class="card overflow-hidden">
                                    <video class="" width="100%" controls>
                                        <source src="<?= assets(); ?>/files/videos/conexion_anydesk.mp4" type="video/mp4">
                                        <source src="<?= assets(); ?>/files/videos/conexion_anydesk.ogg" type="video/ogg">
                                        Tu Navegador no soporta HTML video.
                                    </video>
                                </div>
                                <div class="ms-0 ms-md-4 mt-3 mt-md-0">
                                    <ol class="breadcrumb text-primary fs-12 mb-3">
                                        <li class="breadcrumb-item">INSTALACION</li>
                                        <li class="breadcrumb-item">INSTALAR ACTUALIZACION</li>
                                    </ol>
                                    <h4 class="fw-bold">¿Como abrir un expediente?</h4>
                                    <p>Procedimiento para abrir un expediente clínico de un paciente que ya ha sido recepcionado en el sistema por el personal de recepción.</p>
                                </div>
                            </div>
                        </div> -->


                    </div>

                </div>
            </div>
            <!-- ROW CLOSE -->



        </div>

    </div>
    <!-- CONTAINER CLOSED -->
</div>
<!-- CONTENIDO PRINCIPAL END -->

<!-- Page Footer -->
<?php require_once("Template/page_footer.php"); ?>

<!-- Modals de Formulario -->
<div id="loadModalPermisos">

</div>
<!-- Fin Modals de Formulario -->

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<!-- Custom Plugins JS -->
<!-- OWL CAROUSEL JS-->

<script src="<?= assets(); ?>/plugins/owl-carousel/owl.carousel.js"></script>
<script src="<?= assets(); ?>/js/owl-carousel.js"></script>
<!-- Custom Plugins JS end -->

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_02.php"); ?>