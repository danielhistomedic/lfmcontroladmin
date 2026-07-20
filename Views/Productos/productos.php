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

            <section class="card card-featured mb-4">

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
                                                <th class="border-bottom-0 fw-semibold text-center">SKU</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Nombre</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Descripcion</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Clave Alterna</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Marca</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Linea de Producto</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Categorias</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Unidad Medida</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Lista Precios</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Oferta</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Precio Oferta</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Existencias</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Limite Minimo</th>
                                                <th class="border-bottom-0 fw-semibold text-center">Valoracion</th>
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

                                <!-- Datos Generales -->
                                <section class="card card-modern card-big-info mt-3">
                                    <div class="card-body shadow-sm">
                                        <div class="row">

                                            <div class="col-lg-2-5 col-xl-1-5">
                                                <i class="text-primary card-big-info-icon bx bx-box"></i>
                                                <h2 class="card-big-info-title">Información General</h2>
                                                <p class="card-big-info-desc">Añade aquí la descripción del producto con todos los detalles e información necesaria</p>
                                            </div>

                                            <div class="p-4 col-lg-3-5 col-xl-4-5">

                                                <div class="row">

                                                    <div class="form-group mb-3 col-12 col-sm-6 col-xl-3 mt-3">
                                                        <label class="control-label" for="sku">SKU: <i class="fa-regular fa-circle-question text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="SKU se refiere a un identificador único para cada producto y servicio que se puede comprar."></i></label>
                                                        <input class="form-control btn-square" id="sku" name="sku" type="text" placeholder="Ingrese SKU" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>

                                                    <div class="form-group mb-3 col-12 col-sm-6 col-xl-3">
                                                        <label class="control-label" for="alterna">Clave Alterna: <i class="fa-regular fa-circle-question text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Opcional. Clave Alterna se refiere a un identificador único de control interno."></i></label>
                                                        <input class="form-control btn-square" id="alterna" name="alterna" type="text" placeholder="Ingrese Clave Alterna">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>

                                                    <div class="form-group mb-3 col-12">
                                                        <label class="control-label" for="name">Nombre:</label>
                                                        <input class="form-control btn-square" id="name" name="name" type="text" placeholder="Ingrese Nombre" required="">
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>

                                                    <div class="form-group mb-3 col-12">
                                                        <label class="control-label" for="descripcion">Descripcion:</label>
                                                        <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
                                                        <div class="invalid-feedback">Valor requerido.</div>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </section>


                                <!-- Tabs Clasificacion, Precios, etc. -->
                                <section class="card card-modern card-big-info">
                                    <div class="card-body shadow-sm">
                                        <div class="tabs-modern row" style="min-height: 490px;">
                                            <div class="col-lg-2-5 col-xl-1-5">
                                                <div class="nav flex-column tabs" id="tab" role="tablist" aria-orientation="vertical" style="cursor: pointer;">
                                                    <a class="nav-link active" id="clasificacion-tab" data-bs-toggle="pill" data-bs-target="#clasificacion" role="tab" aria-controls="clasificacion" aria-selected="true">Clasificación</a>
                                                    <a class="nav-link" id="price-tab" data-bs-toggle="pill" data-bs-target="#price" role="tab" aria-controls="price" aria-selected="false">Lista de Precios</a>
                                                    <a class="nav-link" id="inventory-tab" data-bs-toggle="pill" data-bs-target="#inventory" role="tab" aria-controls="inventory" aria-selected="false">Inventario</a>
                                                    <a class="nav-link" id="linked-products-tab" data-bs-toggle="pill" data-bs-target="#linked-products" role="tab" aria-controls="linked-products" aria-selected="false">Productos Relacionados</a>
                                                </div>
                                            </div>
                                            <div class="col-lg-3-5 col-xl-4-5">
                                                <div class="tab-content pt-0" id="tabContent">

                                                    <!-- clasificacion -->
                                                    <div class="tab-pane fade show active" id="clasificacion" role="tabpanel" aria-labelledby="clasificaciones-tab">


                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0">Categoria(s):</label>
                                                            <div class="col-lg-7 col-xl-9">
                                                                <select multiple data-plugin-selectTwo class="form-control populate select2 custom-select" id="select_categorias" name="categorias[]" data-plugin-options='{ "language": "es", "placeholder": "Seleccione una o más categorías..." }' required>
                                                                </select>
                                                                <div class="invalid-feedback">Valor requerido.</div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0">Marca:</label>
                                                            <div class="col-lg-7 col-xl-9">
                                                                <select data-plugin-selectTwo class="form-control populate select2 custom-select" id="select_marcas" name="marca_id" data-plugin-options='{ "language": "es", "placeholder": "Seleccione Marca..." }' required>

                                                                </select>
                                                                <div class="invalid-feedback">Valor requerido.</div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0">Linea de Producto:</label>
                                                            <div class="col-lg-7 col-xl-9">
                                                                <select data-plugin-selectTwo class="form-control populate select2 custom-select" id="select_lineas_producto" name="linea_producto_id" data-plugin-options='{ "language": "es", "placeholder": "Seleccione Linea de Producto..." }' required>

                                                                </select>
                                                                <div class="invalid-feedback">Valor requerido.</div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0">Unidad de Medida:</label>
                                                            <div class="col-lg-7 col-xl-9">
                                                                <select data-plugin-selectTwo class="form-control populate select2 custom-select" id="select_unidad_medida" name="unidad_medida_id" data-plugin-options='{ "language": "es", "placeholder": "Seleccione Linea de Producto..." }' required>
                                                                </select>
                                                                <div class="invalid-feedback">Valor requerido.</div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group pt-0 col-12 ">
                                                            <hr class="mt-2 mb-2" style="height: 5px;">
                                                        </div>


                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="rate">Valoración (Max. 5): <i class="fa-regular fa-circle-question text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Valoración del producto, se refeleja en la cantodad de estrellas que visualiza el cliente"></i></label>
                                                            <div class="col-lg-7 col-xl-6">
                                                                <input type="text" class="form-control " id="rate" name="rate" value="" required />
                                                            </div>
                                                        </div>

                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-4 col-xl-3 control-label text-lg-end mb-0">Recomendado del Mes:</label>
                                                            <div class="col-lg-3">
                                                                <div class="checkbox-custom checkbox-primary">
                                                                    <input type="checkbox" id="recomendaciones_mes" name="recomendaciones_mes">
                                                                    <label for="recomendaciones_mes"></label>
                                                                </div>
                                                                <div class="invalid-feedback">Valor requerido.</div>
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <!-- price -->
                                                    <div class="tab-pane fade" id="price" role="tabpanel" aria-labelledby="price-tab">

                                                        <div id="lista_precios" class="mt-0">

                                                        </div>

                                                        <div class="form-group pt-0 col-12 ">
                                                            <hr class="mt-2 mb-2" style="height: 5px;">
                                                        </div>

                                                        <div class="form-group row align-items-center">
                                                            <label class="col-lg-4 col-xl-3 control-label text-lg-end mb-0 text-danger">Activar Oferta:</label>
                                                            <div class="col-lg-3">
                                                                <div class="checkbox-custom checkbox-primary">
                                                                    <input type="checkbox" id="oferta" name="oferta">
                                                                    <label for="oferta"></label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row align-items-center">
                                                            <label class="col-lg-4 col-xl-3 control-label text-lg-end mb-0 text-danger">Precio Oferta:</label>
                                                            <div class="col-lg-3">
                                                                <input type="text" class="form-control disabled" id="precio_oferta" name="precio_oferta" value="0" />
                                                            </div>
                                                        </div>

                                                    </div>


                                                    <!-- linked-products -->
                                                    <div class="tab-pane fade" id="linked-products" role="tabpanel" aria-labelledby="linked-products-tab">
                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0">Ventas dirigidas <i class="fa-regular fa-circle-question text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="La venta dirigida (en inglés Up-Selling) es cuando les damos a los clientes la opción de comprar un artículo o servicio ligeramente mejor que el que están considerando comprar. Básicamente es una alternativa, opción, mejora o variación del producto actual, tal vez más cara o que lo complementa."></i></label>
                                                            <div class="col-lg-7 col-xl-9">
                                                                <select multiple data-plugin-selectTwo class="form-control " id="select_ventas_dirigidas" name="ventas_dirigidas[]" data-plugin-options='{ "placeholder": "Buscar producto..." }'>

                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row align-items-center">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0">Ventas Cruzadas <i class="fa-regular fa-circle-question text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Ventas cruzadas son productos que promocionas, en base al producto seleccionado"></i></label>
                                                            <div class="col-lg-7 col-xl-9">
                                                                <select multiple data-plugin-selectTwo class="form-control " id="select_ventas_cruzadas" name="ventas_cruzadas[]" data-plugin-options='{ "placeholder": "Buscar producto..." }'>

                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- inventory -->
                                                    <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="cantidad">Cantidad: <i class="fa-regular fa-circle-question text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Existencias disponibles."></i></label>
                                                            <div class="col-lg-7 col-xl-6">
                                                                <input type="text" class="form-control " id="cantidad" name="cantidad" value="" required />
                                                            </div>
                                                        </div>

                                                        <div class="form-group row align-items-center pb-3">
                                                            <label class="col-lg-5 col-xl-3 control-label text-lg-end mb-0" for="limite_minimo">Limite Minimo de Existencias: <i class="fa-regular fa-circle-question text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Cuando el inventario de un producto llega a esta cantidad recibirás un aviso por correo electrónico."></i> </label>
                                                            <div class="col-lg-7 col-xl-6">
                                                                <input type="text" class="form-control " id="limite_minimo" name="limite_minimo" value="" required />
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- Imagenes -->
                                <section class="card card-modern card-big-info">
                                    <div class="card-body shadow-sm">
                                        <div class="row">
                                            <div class="col-lg-2-5 col-xl-1-5">
                                                <i class="text-primary card-big-info-icon bx bx-camera"></i>
                                                <h2 class="card-big-info-title">Imagenes Adicionales de Producto</h2>
                                                <p class="card-big-info-desc">Cargue la imagen de su producto. Puedes agregar varias imágenes dando clic en la nube.</p>
                                            </div>
                                            <div class="p-4 col-lg-3-5 col-xl-4-5">
                                                <div class="row">

                                                    <div class="form-group col-sm-12 mb-4 mb-lg-0 mt-3" data-input="1">
                                                        <input type="hidden" id="adjunto_hidden_1" name="files_auxiliar[]" value="">
                                                        <label class="control-label" for="adjunto1">Imagen Principal:</label>
                                                        <div id="dropify_id_1">
                                                            <input type="file" class="dropify" id="adjunto1" name="adjunto[]" data-default-file="" data-bs-height="180" data-allowed-file-extensions="png jpg" data-max-file-size-preview="1M" required />
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-lg-4 col-sm-12 mb-4 mb-lg-0" data-input="2">
                                                        <input type="hidden" id="adjunto_hidden_2" name="files_auxiliar[]" value="">
                                                        <label class="control-label" for="adjunto2">Imagen 2:</label>
                                                        <div id="dropify_id_2">
                                                            <input type="file" class="dropify" id="adjunto2" name="adjunto[]" data-default-file="" data-bs-height="180" data-allowed-file-extensions="png jpg" data-max-file-size-preview="1M" />
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-lg-4 col-sm-12 mb-4 mb-lg-0" data-input="3">
                                                        <input type="hidden" id="adjunto_hidden_3" name="files_auxiliar[]" value="">
                                                        <label class="control-label" for="adjunto3">Imagen 3:</label>
                                                        <div id="dropify_id_3">
                                                            <input type="file" class="dropify" id="adjunto3" name="adjunto[]" data-default-file="" data-bs-height="180" data-allowed-file-extensions="png jpg" data-max-file-size-preview="1M" />
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-lg-4 col-sm-12 mb-4 mb-lg-0" data-input="4">
                                                        <input type="hidden" id="adjunto_hidden_4" name="files_auxiliar[]" value="">
                                                        <label class="control-label" for="adjunto4">Imagen 4:</label>
                                                        <div id="dropify_id_4">
                                                            <input type="file" class="dropify" id="adjunto4" name="adjunto[]" data-default-file="" data-bs-height="180" data-allowed-file-extensions="png jpg" data-max-file-size-preview="1M" />
                                                        </div>
                                                    </div>

                                                </div>
                                                <input type="hidden" id="adjunto_hidden_1_id" name="files_auxiliar_id[]" value="">
                                                <input type="hidden" id="adjunto_hidden_2_id" name="files_auxiliar_id[]" value="">
                                                <input type="hidden" id="adjunto_hidden_3_id" name="files_auxiliar_id[]" value="">
                                                <input type="hidden" id="adjunto_hidden_4_id" name="files_auxiliar_id[]" value="">
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

                                    <h4 class="mb-0 text-secondary font-weight-bold pt-3">Datos Generales</h4>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 pt-3">
                                        <label class="control-label ">Estatus:</label>
                                        <p class="mt-0 text-secondary" id="estatus_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-3 pt-3">
                                        <label class="control-label ">SKU: </label>
                                        <p class="mt-0 text-secondary" id="sku_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-3 col-xxl-3  pt-3">
                                        <label class="control-label ">Clave Alterna: </label>
                                        <p class="mt-0 text-secondary" id="alterna_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-6 col-xxl-6  pt-3">
                                        <label class="control-label ">Nombre: </label>
                                        <p class="mt-0 text-secondary" id="nombre_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12  ">
                                        <label class="control-label ">Descripción: </label>
                                        <p class="mt-0 text-secondary" id="descripcion_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group pt-0 col-12 ">
                                        <hr class="mt-2 mb-2" style="height: 5px;">
                                    </div>

                                    <h4 class="mb-0 text-secondary font-weight-bold">Clasificación</h4>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  pt-3">
                                        <label class="control-label ">Categorías: </label>
                                        <p class="mt-0 text-secondary" id="categorias_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  pt-3">
                                        <label class="control-label ">Marca: </label>
                                        <p class="mt-0 text-secondary" id="marca_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  pt-3">
                                        <label class="control-label ">Linea de Producto: </label>
                                        <p class="mt-0 text-secondary" id="linea_producto_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  pt-3">
                                        <label class="control-label ">Unidad de Medida: </label>
                                        <p class="mt-0 text-secondary" id="unidad_medida_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  pt-3">
                                        <label class="control-label ">Valoracion: </label>
                                        <p class="mt-0 text-secondary" id="rate_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3  pt-3">
                                        <label class="control-label ">Recomendacion del mes: </label>
                                        <p class="mt-0 text-secondary" id="recomendaciones_mes_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group pt-0 col-12">
                                        <!-- <label class="control-label text-primary" for="">Lista de Precios</label> -->
                                        <hr class="mt-2 mb-2" style="height: 5px;">
                                    </div>

                                    <h4 class="mb-0 text-secondary font-weight-bold ">Listas de Precios</h4>

                                    <div id="lista_precios_read" class="row">

                                        <!-- <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                            <label class="control-label ">Estatus:</label>
                                            <p class="mt-0 text-secondary" id="estatus_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                        </div> -->

                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 pt-3">
                                        <label class="control-label text-danger">Oferta:</label>
                                        <p class="mt-0 text-secondary" id="oferta_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label text-danger ">Precio Oferta:</label>
                                        <p class="mt-0 text-secondary" id="precio_oferta_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group pt-0 col-12 ">
                                        <hr class="mt-2 mb-2" style="height: 5px;">
                                    </div>

                                    <h4 class="mb-0 text-secondary font-weight-bold">Inventario</h4>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 pt-3 ">
                                        <label class="control-label text-danger ">Cantidad:</label>
                                        <p class="mt-0 text-secondary" id="cantidad_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <label class="control-label text-danger ">Límite minimo de existencia:</label>
                                        <p class="mt-0 text-secondary" id="limite_minimo_read"><i class="fa-light fa-brake-warning"></i> No Registrado</p>
                                    </div>

                                    <div class="form-group pt-0 col-12 ">
                                        <hr class="mt-2 mb-2" style="height: 5px;">
                                    </div>

                                    <h4 class="mb-0 text-secondary font-weight-bold pt-3">Imagenes</h4>

                                    <div class="form-group col-12 ">
                                        <div class="d-flex flex-column justify-content-center align-items-start">
                                            <label class="control-label mb-0">Imagen Principal:</label>
                                            <div class="thumbnail-gallery mt-0">
                                                <a class="img-thumbnail lightbox" id="image1_read_lightbox" href="" data-plugin-options='{ "type":"image" }'>
                                                    <img id="image1_read" class="img-fluid" width="180" src="">
                                                    <span class="zoom">
                                                        <i class="bx bx-search"></i>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <div class="d-flex flex-column justify-content-center align-items-start">
                                            <label class="control-label mb-0">Imagen 2:</label>
                                            <div class="thumbnail-gallery mt-0">
                                                <a class="img-thumbnail lightbox" id="image2_read_lightbox" href="" data-plugin-options='{ "type":"image" }'>
                                                    <img id="image2_read" class="img-fluid" width="180" src="">
                                                    <span class="zoom">
                                                        <i class="bx bx-search"></i>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <div class="d-flex flex-column justify-content-center align-items-start">
                                            <label class="control-label mb-0">Imagen 3:</label>
                                            <div class="thumbnail-gallery mt-0">
                                                <a class="img-thumbnail lightbox" id="image3_read_lightbox" href="" data-plugin-options='{ "type":"image" }'>
                                                    <img id="image3_read" class="img-fluid" width="180" src="">
                                                    <span class="zoom">
                                                        <i class="bx bx-search"></i>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-lg-4 col-xxl-3 ">
                                        <div class="d-flex flex-column justify-content-center align-items-start">
                                            <label class="control-label mb-0">Imagen 4:</label>
                                            <div class="thumbnail-gallery mt-0">
                                                <a class="img-thumbnail lightbox" id="image4_read_lightbox" href="" data-plugin-options='{ "type":"image" }'>
                                                    <img id="image4_read" class="img-fluid" width="180" src="">
                                                    <span class="zoom">
                                                        <i class="bx bx-search"></i>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
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