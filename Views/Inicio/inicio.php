<!-- Header Admin 01 -->
<?php require_once("Template/header_01.php"); ?>

<!-- Theme Custom CSS -->


<!-- Header Admin 01 -->
<?php require_once("Template/header_02.php"); ?>


<!-- CONTENIDO DE VISTA -->
<section role="main" class="content-body fondo-inicio">
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

        <!-- <i class="fa-regular fa-file-circle-check"></i> -->

        <div class="col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3 mb-3">
            <a href="<?= base_url(); ?>/oportunidad" class=" card-inicio">
                <div class="card card-inicio">
                    <div class="card-body text-center btn-inicio">
                        <img class="img_icons" src="<?= assets(); ?>/img/icons/oportunidad_venta.png" alt="">
                        <!-- <i class="fa-duotone fa-regular fa-file-circle-check text-secondary fa-3x"></i> -->
                        <h6 class="fw-semibold text-4 text-success">Oportunidad de Venta</h6>
                        <p class="mb-0 fs-12">Módulo para el registro de <br>Requerimientos de Clientes.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-4  col-xxl-3 mb-3">
            <a href="<?= base_url(); ?>/cotizacionclientes" class=" card-inicio">
                <div class="card card-inicio">
                    <div class="card-body text-center btn-inicio">
                        <img class="img_icons" src="<?= assets(); ?>/img/icons/cotizacion_clientes.png" alt="">
                        <!-- <i class="fa-duotone fa-regular fa-file-circle-check text-secondary fa-3x"></i> -->
                        <h6 class="fw-semibold text-4 text-success">Cotizacion a Clientes</h6>
                        <p class="mb-0 fs-12">Módulo para el registro y envio de <br>Cotización de Clientes.</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-lg-4 col-xl-4  col-xxl-3 mb-3">
            <a href="<?= base_url(); ?>/cotizacionclientes" class=" card-inicio">
                <div class="card card-inicio">
                    <div class="card-body text-center btn-inicio">
                        <img class="img_icons" src="<?= assets(); ?>/img/icons/registro_pedidos.png" alt="">
                        <!-- <i class="fa-duotone fa-regular fa-file-circle-check text-secondary fa-3x"></i> -->
                        <h6 class="fw-semibold text-4 text-success">Registro de Pedidos</h6>
                        <p class="mb-0 fs-12">Módulo de registro de ordenes de compra <br>recibidas por el Cliente.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- <div class="d-none col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
            <a href="<?= base_url(); ?>/productos" class="card-inicio">
                <div class="card card-inicio">
                    <div class="card-body text-center  btn-inicio">
                        <i class="fa-duotone fa-box-open text-secondary fa-3x"></i>
                        <h6 class="fw-semibold text-4">Registro de Productos</h6>
                        <p class="mb-0  fs-12">Módulo de Registro y/o Actualización de Productos de Tienda</p>
                    </div>
                </div>
            </a>
        </div> -->

    </div>


    <!-- end: page -->
</section>
<!-- FIN CONTENIDO VISTA -->


<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<!-- Theme Custom -->
<script src="<?= assets(); ?>/js/examples/examples.dashboard.js"></script>


<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>