<!-- Modal Formulario Permisos -->
<div class="modal fade effect-scale modalPermisos" id="modalPermisos" tabindex="-1" aria-labelledby="modalPermisos1" aria-modal="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content loading-panel-showing2">

            <div class="loading-panel2">
                <div class="bounce-loader">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                    <div class="bounce3"></div>
                </div>
            </div>

            <div class="modal-header">
                <h5 class="modal-title" id="gridModalLabel">
                    <div class="form-group col-12">
                        <div class=" subtitulos_panel">
                            <p class="mb-0 fw-600 text-primary fw-semibold"><i class="fa-regular fa-file-lock text-secondary text-4 me-1"></i> Permisos para el Rol <span class="ms-1 text-secondary"><?= $data['rol']; ?></span></p>
                        </div>
                    </div>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" role="button"></button>
            </div>

            <div class="modal-body modal-body-permisos position-relative">

                <form action="" id="formPermisos" name="formPermisos">

                    <div class="row">

                        <div class="col-12">

                            <div class="">

                                <input type="hidden" id="rol_id" name="rol_id" value="<?= $data['rol_id']; ?>" required="">

                                <!-- <div class="table-responsive"> -->

                                <table class="table table-no-more table-bordered table-striped mb-0">

                                    <thead class="gradient-custom-content">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th class="text-center">Modulo</th>
                                            <th class="text-center">Leer</th>
                                            <th class="text-center">Crear</th>
                                            <th class="text-center">Modificar</th>
                                            <th class="text-center">Eliminar</th>
                                            <th class="text-center">Exportar</th>
                                            <th class="text-center">Imprimir</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $no = 1;
                                        $modulos = $data['modulos'];
                                        for ($i = 0; $i < count($modulos); $i++) {

                                            $permisos = $modulos[$i]['permisos'];
                                            $rCheck = $permisos['r'] == 1 ? " checked " : "";
                                            $cCheck = $permisos['c'] == 1 ? " checked " : "";
                                            $uCheck = $permisos['u'] == 1 ? " checked " : "";
                                            $dCheck = $permisos['d'] == 1 ? " checked " : "";
                                            $eCheck = $permisos['e'] == 1 ? " checked " : "";
                                            $pCheck = $permisos['p'] == 1 ? " checked " : "";
                                            $modulo_id = $modulos[$i]['id'];
                                        ?>
                                            <tr>
                                                <td data-title="No" class="text-end">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <?= $no; ?>
                                                        <input type="hidden" name="modulos[<?= $i; ?>][modulo_id]" value="<?= $modulo_id ?>" required>
                                                    </div>
                                                </td>

                                                <td data-title="Modulo" class="text-end">
                                                    <div class="d-flex justify-content-start align-items-center">
                                                        <span><?= $modulos[$i]['name']; ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-start align-items-center">
                                                        <span class="text-info"><?= $modulos[$i]['descripcion']; ?></span>
                                                    </div>
                                                </td>


                                                <td data-title="Leer" class="text-end">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="checkbox-custom checkbox-warning">
                                                            <input class="selectcheck_permisos" type="checkbox" id="leer<?= $i; ?>" name="modulos[<?= $i; ?>][r]" <?= $rCheck ?>>
                                                            <label for="leer<?= $i; ?>"></label>
                                                        </div>
                                                    </div>
                                                </td>


                                                <td data-title="Crear" class="text-end">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="checkbox-custom checkbox-warning">
                                                            <input class="selectcheck_permisos" type="checkbox" id="crear<?= $i; ?>" name="modulos[<?= $i; ?>][c]" <?= $cCheck ?>>
                                                            <label for="crear<?= $i; ?>"></label>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td data-title="Modificar" class="text-end">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="checkbox-custom checkbox-warning">
                                                            <input class="selectcheck_permisos" type="checkbox" id="modificar<?= $i; ?>" name="modulos[<?= $i; ?>][u]" <?= $uCheck ?>>
                                                            <label for="modificar<?= $i; ?>"></label>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td data-title="Eliminar" class="text-end">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="checkbox-custom checkbox-warning">
                                                            <input class="selectcheck_permisos" type="checkbox" id="eliminar<?= $i; ?>" name="modulos[<?= $i; ?>][d]" <?= $dCheck ?>>
                                                            <label for="eliminar<?= $i; ?>"></label>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td data-title="Exportar" class="text-end">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="checkbox-custom checkbox-warning">
                                                            <input class="selectcheck_permisos" type="checkbox" id="exportar<?= $i; ?>" name="modulos[<?= $i; ?>][e]" <?= $eCheck ?>>
                                                            <label for="exportar<?= $i; ?>"></label>
                                                        </div>
                                                    </div>
                                                </td>


                                                <td data-title="Imprimir" class="text-end">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="checkbox-custom checkbox-warning">
                                                            <input class="selectcheck_permisos" type="checkbox" id="imprimir<?= $i; ?>" name="modulos[<?= $i; ?>][p]" <?= $pCheck ?>>
                                                            <label for="imprimir<?= $i; ?>"></label>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>
                                        <?php
                                            $no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <!-- </div> -->

                            </div>

                        </div>

                    </div>
                </form>
            </div>
            <!--end modal-body-->

            <div class="modal-footer">

                <button class="ms-2 btn btn-info hvr-grow-shadow" role="button" id="btnSeleccionarTodos_Permisos">
                    <i class="fa-regular fa-circle-check f-16"></i>
                    <span class="">Seleccionar Todos</span>
                </button>

                <button class="ms-2 btn btn-info hvr-grow-shadow" role="button" id="btnDesSeleccionarTodos_Permisos">
                    <i class="fa-regular fa-circle-check f-16"></i>
                    <span class="">Des-Seleccionar Todos</span>
                </button>


                <?php if ($data[0]['permisosMod']['c'] || $data[0]['permisosMod']['u']) { ?>
                    <button class="btn btn-secondary ms-auto hvr-grow-shadow" type="button" id="btnActionFormPermisos">
                        <i class="fa-regular fa-floppy-disk-pen fa-fw fa-lg me-1"></i>Guardar
                    </button>
                <?php } ?>

                <button aria-label="Close" class="ms-2 btn btn-danger hvr-grow-shadow" data-bs-dismiss="modal" role="button">
                    <i class="fa-regular fa-arrow-up-from-square fa-rotate-270 f-16 me-1"></i>
                    <span class="">Cerrar</span>
                </button>
            </div>
            <!--end modal-footer-->

        </div>
        <!--end modal-content-->

    </div>

</div>
<!-- Fin Modal Formulario Permisos -->