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

                    <div class="btn-group flex-wrap" role="group" aria-label="Basic example">
                        <button style="" class="btn btn-outline-primary  <?= $disabled = ($data['permisosMod']['c']) ? '' : 'disabled'; ?>" type="button" id="btnCreate" data-animation="fadeInDown" title="Nuevo Registro"><i class="fa-regular fa-circle-plus fa-lg"></i> Nuevo</button>
                        <button style="" class="btn btn-outline-primary  active btnReturnList " type="button" id="btnHistorial" data-animation="fadeInRight" title="Historial de Registros."><i class="fa-regular fa-bars-staggered fa-lg"></i> Historial</button>
                    </div>

                    <!-- Lista -->
                    <div class="" id="panel_lista_registros">

                        <div class="row">

                            <!-- Subtitulos Lista de Registros -->
                            <div class="form-group col-12 mt-4">
                                <div class="border-bottom subtitulos_panel">
                                    <p class="mb-0 fw-600 text-primary fw-semibold"><i class="fa-regular fa-bars-staggered text-secondary text-4"></i> Lista de Registros.</p>
                                </div>
                            </div>
                            <!-- Fin Lista de Registros -->

                            <div class="form-group col-12 mb-0">

                                <!-- Tabla de Registros -->
                                <div class="table-responsive export-table">
                                    <table id="tableRecords" class="table table-bordered text-nowrap table-striped table-hover key-buttons border-bottom w-100">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Estatus</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Fecha Registro</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Nombre</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Telefono</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Correo</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Domicilio de Envío</th>
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

                                <!-- Usuuario -->
                                <section class="card card-modern card-big-info mt-3">
                                    <div class="card-body shadow-sm">
                                        <div class="row">
                                            <div class="col-lg-2-5 col-xl-1-5">
                                                <i class="text-primary card-big-info-icon bx bx-lock"></i>
                                                <h2 class="card-big-info-title">Cuenta de Acceso</h2>
                                                <p class="card-big-info-desc">Agregue aquí la información de la cuenta del cliente.</p>
                                            </div>
                                            <div class="col-lg-3-5 col-xl-4-5">
                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0">Email / Usuario:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="email" class="form-control valid" id="usuario" name="usuario" value="" required="" aria-invalid="false">
                                                    </div>
                                                </div>
                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="password">Contraseña</label>
                                                    <div class="col-lg-7 col-xl-6 position-relative">
                                                        <input type="password" class="form-control  valid" id="password" name="password" value="" aria-invalid="false">
                                                        <div class="show-hide-cliente"><span class="show"> </span></div>
                                                    </div>
                                                </div>
                                                <div class="form-group row align-items-center">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="password_confirm">Confirmar Contraseña:</label>
                                                    <div class="col-lg-7 col-xl-6 position-relative">
                                                        <input type="password" class="form-control " id="password_confirm" name="password_confirm" value="">
                                                        <div class="show-hide-cliente-confirm"><span class="show"> </span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>


                                <!-- Datos Generales del Cliente -->
                                <section class="card card-modern card-big-info mt-3">

                                    <div class="card-body shadow-sm">

                                        <div class="row">

                                            <div class="col-lg-2-5 col-xl-1-5">
                                                <i class="text-primary card-big-info-icon bx bx-user-circle"></i>
                                                <h2 class="card-big-info-title">Datos Generales</h2>
                                                <p class="card-big-info-desc">Añade aquí los datos generales del cliente con todos los detalles y la información necesaria.</p>
                                            </div>

                                            <div class="col-lg-3-5 col-xl-4-5">

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="nombre">Nombre:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="nombre" name="nombre" rplaceholder="Ingrese Nombre" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="paterno">Apellido Paterno:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="paterno" name="paterno" rplaceholder="Ingrese Apellido Paterno" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="materno">Apellido Paterno:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="materno" name="materno" rplaceholder="Ingrese Apellido Materno" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>


                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="telefono">Telefono:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="number" class="form-control " id="telefono" name="telefono" rplaceholder="Ingrese Telefono" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="email">Email:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="email" class="form-control " id="email" name="email" rplaceholder="Ingrese Email" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="select_lista_precios">Lista de Precios:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <select data-plugin-selectTwo class="form-control populate select2 custom-select" id="select_lista_precios" name="lista_precios_id" data-plugin-options='{ "language": "es", "placeholder": "Seleccione Lista de Precios..." }' required>

                                                        </select>
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <!-- <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="adjunto">Foto Perfil:</label>
                                                    <div class="col-lg-7 col-xl-6" id="dropify_id">
                                                        <input type="file" class="dropify" id="adjunto" name="adjunto" data-default-file="" data-bs-height="180" data-allowed-file-extensions="png jpg" data-max-file-size-preview="1M" />
                                                    </div>
                                                </div> -->

                                            </div>
                                        </div>
                                    </div>
                                </section>


                                <!-- Domicilio de Envío -->
                                <section class="card card-modern card-big-info mt-3">

                                    <div class="card-body shadow-sm">

                                        <div class="row">

                                            <div class="col-lg-2-5 col-xl-1-5">
                                                <i class="text-primary card-big-info-icon bx bx-location-plus "></i>
                                                <h2 class="card-big-info-title">Domicilio de Envío</h2>
                                                <p class="card-big-info-desc">Añade aquí los datos de domicilio de envío del cliente con todos los detalles y la información necesaria.</p>
                                            </div>

                                            <div class="col-lg-3-5 col-xl-4-5">

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="calle">Calle:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="calle" name="calle" rplaceholder="Ingrese calle" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="num_exterior">Num Exterior:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="num_exterior" name="num_exterior" rplaceholder="Ingrese numero exterior" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="num_interior">Num Interior:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="num_interior" name="num_interior" rplaceholder="Ingrese numero interior">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>


                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="colonia">Colonia:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="colonia" name="colonia" rplaceholder="Ingrese Colonia" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>


                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="ciudad">Ciudad:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="ciudad" name="ciudad" rplaceholder="Ingrese Ciudad" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="estado">Estado:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="estado" name="estado" rplaceholder="Ingrese Estado" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="cp">Codigo Postal:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="cp" name="cp" rplaceholder="Ingrese Codigo Postal" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>


                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="pais">Pais:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="pais" name="pais" rplaceholder="Ingrese Pais" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="referencias">Referencias:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="referencias" name="referencias" rplaceholder="Ingrese Referencias">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </section>


                                <!-- Datos de Facturación -->
                                <section class="card card-modern card-big-info mt-3">

                                    <div class="card-body shadow-sm">

                                        <div class="row">

                                            <div class="col-lg-2-5 col-xl-1-5">
                                                <i class="text-primary card-big-info-icon fa-light fa-file-invoice ps-3"></i>
                                                <h2 class="card-big-info-title">Datos de Facturación</h2>
                                                <p class="card-big-info-desc">Añade aquí los datos para la factiración del cliente con todos los detalles y la información necesaria.</p>
                                            </div>

                                            <div class="col-lg-3-5 col-xl-4-5">

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="rfc">RFC:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="rfc" name="rfc" rplaceholder="Ingrese rfc">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="razon_social">Nombre o Razon Social:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="razon_social" name="razon_social" rplaceholder="Ingrese Nombre o Razon Social">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="codigo_postal">Codigo Postal:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="codigo_postal" name="codigo_postal" rplaceholder="Ingrese Codigo Postal">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>


                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="regimen">Régimen Fiscal:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="regimen" name="regimen" rplaceholder="Ingrese Régimen Fiscal">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>


                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="uso_cfdi">Uso de CFDI:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="uso_cfdi" name="uso_cfdi" rplaceholder="Ingrese Uso de CFDI">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                                <div class="form-group row align-items-center pb-3">
                                                    <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="email_fact">Email:</label>
                                                    <div class="col-lg-7 col-xl-6">
                                                        <input type="text" class="form-control " id="email_fact" name="email_fact" rplaceholder="Ingrese Email">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </section>


                                <div class="form-group col-12 mb-0 mt-3">
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

                                    <h4 class="mb-0 pt-3 text-secondary font-weight-bold">Datos Generales</h4>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 pt-3">
                                        <label class="control-label ">Estatus:</label>
                                        <p class="mt-0 text-secondary" id="estatus_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  ">
                                        <label class="control-label ">Usuario: </label>
                                        <p class="mt-0 text-secondary" id="usuario_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  ">
                                        <label class="control-label ">Nombre: </label>
                                        <p class="mt-0 text-secondary" id="nombre_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  ">
                                        <label class="control-label ">Apellido Paterno: </label>
                                        <p class="mt-0 text-secondary" id="paterno_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  ">
                                        <label class="control-label ">Apellido Materno: </label>
                                        <p class="mt-0 text-secondary" id="materno_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Telefono: </label>
                                        <p class="mt-0 text-secondary" id="telefono_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Correo: </label>
                                        <p class="mt-0 text-secondary" id="email_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Lista de Precios: </label>
                                        <p class="mt-0 text-secondary" id="listaprecios_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group pt-0 col-12 ">
                                        <hr class="mt-2 mb-2" style="height: 5px;">
                                    </div>

                                    <h4 class="mb-0 text-secondary font-weight-bold">Domicilio de Envío</h4>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 pt-3">
                                        <label class="control-label ">Calle: </label>
                                        <p class="mt-0 text-secondary" id="calle_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Numero Exterior: </label>
                                        <p class="mt-0 text-secondary" id="num_exterior_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Numero Interior: </label>
                                        <p class="mt-0 text-secondary" id="num_interior_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Colonia: </label>
                                        <p class="mt-0 text-secondary" id="colonia_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Ciudad: </label>
                                        <p class="mt-0 text-secondary" id="ciudad_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Estado: </label>
                                        <p class="mt-0 text-secondary" id="estado_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Codigo Postal: </label>
                                        <p class="mt-0 text-secondary" id="cp_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">País: </label>
                                        <p class="mt-0 text-secondary" id="pais_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Referencias: </label>
                                        <p class="mt-0 text-secondary" id="referencias_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group pt-0 col-12 ">
                                        <hr class="mt-2 mb-2" style="height: 5px;">
                                    </div>

                                    <h4 class="mb-0 text-secondary font-weight-bold">Datos Fiscales</h4>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 pt-3">
                                        <label class="control-label ">RFC: </label>
                                        <p class="mt-0 text-secondary" id="rfc_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Razon Social: </label>
                                        <p class="mt-0 text-secondary" id="razon_social_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Codigo Postal: </label>
                                        <p class="mt-0 text-secondary" id="codigo_postal_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Regimen: </label>
                                        <p class="mt-0 text-secondary" id="regimen_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Uso CFDI: </label>
                                        <p class="mt-0 text-secondary" id="uso_cfdi_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label ">Email Facturacion: </label>
                                        <p class="mt-0 text-secondary" id="email_fact_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <!-- <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <div class="d-flex flex-column justify-content-center align-items-start">
                                            <label class="control-label mb-0">Imagen:</label>
                                            <div class="thumbnail-gallery mt-0">
                                                <a class="img-thumbnail lightbox" id="image_read_lightbox" href="" data-plugin-options='{ "type":"image" }'>
                                                    <img id="image_read" class="img-fluid" width="180" src="">
                                                    <span class="zoom">
                                                        <i class="bx bx-search"></i>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div> -->

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
                                            <p class="mt-0 text-secondary" id="fechaRegistro_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                        </div>

                                        <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                            <label class="control-label ">Usuario Registró:</label>
                                            <p class="mt-0 text-secondary" id="usuarioRegistro_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            <!-- Fin Datos Generales y de Contacto -->

                            <div class="form-group col-12 mb-0">
                                <div class="d-flex justify-content-start align-items-center d-flex-pacientes-inicio">
                                    <button class="btn btn-secondary hvr-float-shadow <?= $disabled = ($data['permisosMod']['u']) ? '' : 'disabled'; ?>" type="submit" id="btnEditar">
                                        <i class="fa-regular fa-floppy-disk-pen fa-fw fa-lg me-1"></i>Editar
                                    </button>
                                </div>
                            </div>

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


<!-- Footer Admin 02 -->
<?php require_once("Template/footer_02.php"); ?>