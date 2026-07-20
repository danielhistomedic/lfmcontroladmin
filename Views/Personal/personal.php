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

                <div class="card-body loading-panel-showing">

                    <div class="loading-panel">
                        <div class="bounce-loader">
                            <div class="bounce1"></div>
                            <div class="bounce2"></div>
                            <div class="bounce3"></div>
                        </div>
                    </div>

                    <div class="btn-group flex-wrap" role="group" aria-label="submenu" style="display: block;">
                        <!-- <button style="" class="btn btn-outline-primary  <?= $disabled = ($data['permisosMod']['c']) ? '' : 'disabled'; ?>" type="button" id="btnCreate" data-animation="fadeInDown" title="Nuevo Registro"><i class="fa-regular fa-circle-plus fa-lg"></i> Nuevo</button> -->
                        <button style="" class="btn btn-outline-primary active btnReturnList d-none" type="button" id="btnHistorial" data-animation="fadeInRight" title="Historial de Registros."><i class="fa-regular fa-bars-staggered fa-lg"></i> Historial</button>
                    </div>

                    <!-- Lista -->
                    <div class="" id="panel_lista_registros">

                        <div class="row">

                            <!-- Subtitulos Lista de Roles Registrados -->
                            <div class="form-group col-12 ">
                                <div class="border-bottom subtitulos_panel">
                                    <p class="mb-0 fw-600 text-primary fw-semibold"><i class="fa-regular fa-bars-staggered text-secondary text-4"></i> Lista de Registros.</p>
                                </div>
                            </div>
                            <!-- Fin Lista de Roles Registrados -->

                            <div class="form-group col-12 mb-0">

                                <!-- Tabla de Registros -->
                                <div class="table-responsive export-table">
                                    <table id="tableRecords" class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Estatus</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Fecha Registro</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Departamento</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Puesto</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Num Empleado</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Cargo</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Nombre</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Telefono</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Email</th>
                                                <th class="border-bottom-0 fw-semibold text-center">CURP</th>
                                                <th class="border-bottom-0 fw-semibold text-center">JEFE INMEDIATO</th>
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
                    <div class="" id="panel_crear_editar" style="display: none;">

                        <form class="theme-form needs-validation position-relative" id="formRecords" novalidate="">

                            <div class="row m-t-10">

                                <!-- Subtitulos Editar Datos -->
                                <div class="form-group col-12 mt-4">
                                    <div class="border-bottom subtitulos_panel">
                                        <p class="mb-0 fw-600 text-primary fw-semibold"><i class="fa-regular fa-pencil text-secondary text-4"></i> Datos a Registrar/Editar.</p>
                                    </div>
                                </div>
                                <!-- Fin Subtitulos Editar Datos -->

                                <div class="form-group col-12 col-sm-6 col-xl-4">
                                    <label class="control-label" for="cdscareaafectada">Departamento:</label>
                                    <input class="form-control btn-square" id="cdscareaafectada" name="cdscareaafectada" type="text" placeholder="" autocomplete="off" required="">
                                    <div class="invalid-feedback">Valor requerido.</div>
                                </div>

                                <div class="form-group col-12 col-sm-6 col-xl-8">
                                    <label class="control-label" for="descripcion">Descripcion:</label>
                                    <input class="form-control btn-square" id="descripcion" name="descripcion" type="text" placeholder="Ingrese Descripción" autocomplete="off" required="">
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


                    <!-- Vista de Datos -->
                    <div class="p-1" id="panel_vista_datos" style="display: none;">

                        <div class="row">

                            <!-- Datos de Registro -->
                            <div class="col-12 mb-3">

                                <!-- Subtitulos Editar Datos -->
                                <div class="form-group col-12 mt-4">
                                    <div class="border-bottom subtitulos_panel">
                                        <p class="mb-0 fw-600 text-primary fw-semibold"><i class="fa-regular fa-file-lines text-secondary text-4"></i> Datos de Registro Seleccionado.</p>
                                    </div>
                                </div>
                                <!-- Fin Subtitulos Editar Datos -->

                                <div class="row">

                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-3 pt-3 ">
                                        <label class="control-label ">Estatus:</label>
                                        <p class="mt-0 text-secondary" id="estatus_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-3 ">
                                        <label class="control-label ">Departamento: </label>
                                        <p class="mt-0 text-secondary" id="cdscareaafectada_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-3 ">
                                        <label class="control-label ">Puesto: </label>
                                        <p class="mt-0 text-secondary" id="cdscservicio_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-3 ">
                                        <label class="control-label ">Numero de Empleado: </label>
                                        <p class="mt-0 text-secondary" id="cMatricula_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-3 ">
                                        <label class="control-label ">Cargo: </label>
                                        <p class="mt-0 text-secondary" id="cCargo_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-3 ">
                                        <label class="control-label ">Nombre: </label>
                                        <p class="mt-0 text-secondary" id="nombre_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-6 col-xxl-6 ">
                                        <label class="control-label ">Telefono: </label>
                                        <p class="mt-0 text-secondary" id="telefono_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-6 col-xxl-6 ">
                                        <label class="control-label ">Email: </label>
                                        <p class="mt-0 text-secondary" id="email_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-6 col-xxl-6 ">
                                        <label class="control-label ">CURP: </label>
                                        <p class="mt-0 text-secondary" id="cCURP_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-6 col-xxl-6 ">
                                        <label class="control-label ">Jefe Inmediato: </label>
                                        <p class="mt-0 text-secondary" id="jefe_in_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-6 col-xxl-6 ">
                                        <label class="control-label ">Perfil de Acceso Sistema Local: </label>
                                        <p class="mt-0 text-secondary" id="cdscperfil_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>


                                    <div class="form-group pt-0 col-12 ">
                                        <hr class="mt-2 mb-2" style="height: 5px;">
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

                                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  pt-3">
                                            <label class="control-label ">Fecha de Registro:</label>
                                            <p class="mt-0 text-secondary" id="fchregistro_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                        </div>

                                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                            <label class="control-label ">Usuario Registró:</label>
                                            <p class="mt-0 text-secondary" id="registro_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            <!-- Fin Datos Generales y de Contacto -->

                            <!-- <div class="form-group col-12 mb-0">
                                <div class="d-flex justify-content-start align-items-center d-flex-pacientes-inicio">
                                    <button class="btn btn-secondary hvr-float-shadow <?= $disabled = ($data['permisosMod']['u']) ? '' : 'disabled'; ?>" type="submit" id="btnEditar">
                                        <i class="fa-regular fa-floppy-disk-pen fa-fw fa-lg me-1"></i>Editar
                                    </button>
                                </div>
                            </div> -->

                        </div>
                    </div>
                    <!-- Fin Vista de Datos -->


                </div>

            </section>
        </div>
    </div>

</section>
<!-- FIN CONTENIDO VISTA -->


<!-- Footer Admin 01 -->
<?php require_once("Template/footer_01.php"); ?>

<!-- Theme Custom -->
<div id="loadModalPermisos"></div>

<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>