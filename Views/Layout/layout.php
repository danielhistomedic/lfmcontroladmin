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
                        <li class="breadcrumb-item"><a href="#">Ventas</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $data['page_breadcrumb']; ?></li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-1 OPEN -->
            <div class="row">

                <div class="col-sm-12">

                    <div class="card border p-0">
                        <div class="card-header bg-header-card">
                            <div class="card-title text-primary d-flex flex-column">
                                <span><?= $data['page_card_title']; ?> <i title="Info" style="cursor:pointer;" class="text-primary fa-light fa-circle-question" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="false" aria-controls="collapseInfo"></i></span>
                                <div class="collapse" id="collapseInfo">
                                    <span class="fs-13 text-info fw-normal"><?= $data['page_card_description']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- loading start -->
                            <div class="dimmer active">
                                <div class="lds-ring">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                            <!-- loading end -->

                            <div class="btn-group" role="group" aria-label="Basic example">
                                <button style="-webkit-box-shadow:none!important; box-shadow:none!important;" class="btn btn-outline-secondary fs-13 <?= $disabled = ($data['permisosMod']['c']) ? '' : 'disabled'; ?>" type="button" id="btnCrear" data-animation="fadeInDown" title="Nuevo Registro"><i class="fa-regular fa-circle-plus fa-lg"></i> Nuevo</button>
                                <button style="-webkit-box-shadow:none!important; box-shadow:none!important;" class="btn btn-outline-secondary fs-13 active btnReturnList " type="button" id="btnHistorial" data-animation="fadeInRight" title="Historial de Registros."><i class="fa-regular fa-bars-staggered fa-lg"></i> Historial</button>
                            </div>

                            <!-- Lista -->
                            <div class="" id="record_list">

                                <div class="row">

                                    <!-- Subtitulos Lista de Registros -->
                                    <div class="form-group col-12 mt-4">
                                        <div class="border-bottom border-primary subtitulos_panel">
                                            <p class="mb-0 fw-600 text-primary"><i class="fa-regular fa-bars-staggered text-info text-info-shadow fs-18"></i> Lista de Registros.</p>
                                        </div>
                                    </div>
                                    <!-- Fin Lista de Registros -->

                                    <div class="form-group col-12 mb-0">

                                        <!-- Tabla de Registros -->
                                        <div class="table-responsive export-table">
                                            <table id="tableRecords" class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="border-bottom-0 fw-semibold text-center">Fecha Registro</th>
                                                        <th class="border-bottom-0 fw-semibold text-center">Sucursal</th>
                                                        <th class="border-bottom-0 fw-semibold text-center">Titulo de Layout</th>
                                                        <th class="border-bottom-0 fw-semibold text-center">Procesado</th>
                                                        <th class="border-bottom-0 fw-semibold text-center">Estatus</th>
                                                        <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                        <!-- Fin Tabla de Registros -->

                                    </div>

                                </div>

                            </div>
                            <!-- Fin Lista -->

                            <!-- Crear/Editar Datos -->
                            <div class="" id="create_edit" style="display: none;">

                                <form class="theme-form needs-validation position-relative" id="formRegistration" novalidate="">

                                    <div class="row m-t-10">

                                        <!-- Subtitulos Editar Datos -->
                                        <div class="form-group col-12 mt-4">
                                            <div class="border-bottom border-primary subtitulos_panel">
                                                <p class="mb-0 fw-600 text-primary"><i class="fa-regular fa-pencil text-info text-info-shadow fs-18"></i> Datos a Registrar/Editar.</p>
                                            </div>
                                        </div>
                                        <!-- Fin Subtitulos Editar Datos -->

                                        <div class="form-group col-12 col-sm-6 ">
                                            <label class="form-label" for="comboSucursal">Sucursal:</label>
                                            <select class="select2 custom-select" id="comboSucursal" name="sucursal_id" style="width: 100%" required=""></select>
                                            <div class="invalid-feedback">Valor requerido.</div>
                                        </div>

                                        <div class="form-group  col-12 col-sm-6 ">
                                            <label class="form-label" for="titulo">Titulo de Layout:</label>
                                            <input class="form-control btn-square" id="titulo" type="text" name="titulo" placeholder="Ingrese Titulo de Layout" required="">
                                            <div class="invalid-feedback">Valor requerido.</div>
                                        </div>

                                        <div class="form-group  col-12 col-sm-6">
                                            <label class="form-label" for="adjunto_output">Layout Excel Output: <span class="d-none text-info fw-normal fst-italic" id="archivo_cargado_output"><i class="fa-solid fa-circle-check text-success"></i> Ya existe archivo cargado.</span></label>
                                            <input type="file" class="form-control btn-square" name="adjunto_output" id="adjunto_output" placeholder="Seleccionar archivo" autocomplete="off" required="">
                                            <div class="invalid-feedback">Valor requerido.</div>
                                        </div>

                                        <!-- <div class="form-group  col-12 col-sm-6">
                                            <label class="form-label" for="adjunto_general">Layout Excel Output Complementarios: <span class="d-none text-info fw-normal fst-italic" id="archivo_cargado_general"><i class="fa-solid fa-circle-check text-success"></i> Ya existe archivo cargado.</span></label>
                                            <input type="file" class="form-control btn-square" name="adjunto_general" id="adjunto_general" placeholder="Seleccionar archivo" autocomplete="off">
                                            <div class="invalid-feedback">Valor requerido.</div>
                                        </div> -->

                                        <div class="form-group col-12 mb-0">
                                            <input type="hidden" name="id_record" id="id_record" value="">
                                            <button class="btn btn-secondary <?php
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

                            <!-- Vista de Datos -->
                            <div class="" id="view" style="display: none;">

                                <div class="row">

                                    <!-- Datos de Registro -->
                                    <div class="col-12">

                                        <!-- Subtitulos Editar Datos de Registro-->
                                        <div class="form-group mt-4">
                                            <div class="border-bottom border-primary subtitulos_panel">
                                                <p class="mb-0 fw-600 text-primary"><i class="fa-regular fa-file-lines text-info text-info-shadow fs-18"></i> Datos de Registro Seleccionado.</p>
                                            </div>
                                        </div>
                                        <!-- Fin Editar Datos de Registro -->

                                        <div class="form-group mt-2">

                                            <div class="row">

                                                <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-2">
                                                    <label class="form-label fs-13">Sucursal: </label>
                                                    <h5 class="vistadatos fs-14 text-muted" id="sucursal_read"><i class="fa-light fa-brake-warning"></i> No Registrado</h5>
                                                </div>

                                                <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-2">
                                                    <label class="form-label fs-13">Titulo de Layout: </label>
                                                    <h5 class="vistadatos fs-14 text-muted" id="titulo_read"><i class="fa-light fa-brake-warning"></i> No Registrado</h5>
                                                </div>

                                                <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-2">
                                                    <label class="form-label fs-13">Layout Excel Output: </label>
                                                    <h5 class="vistadatos fs-14 text-muted" id="adjunto_output_read"><i class="fa-light fa-brake-warning"></i> No Registrado</h5>
                                                </div>

                                                <!-- <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-2">
                                                    <label class="form-label fs-13">Layout Excel Output Complementario: </label>
                                                    <h5 class="vistadatos fs-14 text-muted" id="adjunto_general_read"><i class="fa-light fa-brake-warning"></i> No Registrado</h5>
                                                </div> -->

                                                <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-2">
                                                    <label class="form-label fs-13">Estatus:</label>
                                                    <h5 class="vistadatos fs-14 text-muted" id="estatus_read"><i class="fa-light fa-brake-warning"></i> No Registrado</h5>
                                                </div>

                                                <a class="form-group mostrar_mas_menos" style="text-decoration: underline;" data-bs-toggle="collapse" href="#collapseMostrarMasMenos" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                    <div id="mostrar_mas" class="">
                                                        <span>Mostrar más</span><i class="ms-1 fa-light fa-angle-down"></i>
                                                    </div>
                                                    <div id="mostrar_menos" class="d-none">
                                                        <span>Mostrar menos</span><i class="ms-1 fa-light fa-angle-up"></i>
                                                    </div>
                                                </a>

                                                <div class="collapse row" id="collapseMostrarMasMenos">

                                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-2">
                                                        <label class="form-label fs-13">Fecha de Registro:</label>
                                                        <h5 class="vistadatos fs-14 text-muted" id="fechaRegistro_read"><i class="fa-light fa-brake-warning"></i> No Registrado</h5>
                                                    </div>

                                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-2">
                                                        <label class="form-label fs-13">Usuario Registró:</label>
                                                        <h5 class="vistadatos fs-14 text-muted" id="usuarioRegistro_read"><i class="fa-light fa-brake-warning"></i> No Registrado</h5>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                    <!-- Fin Datos Generales y de Contacto -->

                                    <div class="form-group col-12 mb-0">
                                        <div class="d-flex justify-content-start align-items-center d-flex-pacientes-inicio">
                                            <button class="btn btn-secondary <?= $disabled = ($data['permisosMod']['u']) ? '' : 'disabled'; ?>" type="submit" id="btnEditar">
                                                <i class="fa-regular fa-floppy-disk-pen fa-fw fa-lg me-1"></i>Editar
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- Fin Vista de Datos -->

                        </div>

                    </div>

                </div>

            </div>

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

<!-- Custom Plugins JS end -->

<!-- Footer Admin 01 -->
<?php require_once("Template/footer_02.php"); ?>