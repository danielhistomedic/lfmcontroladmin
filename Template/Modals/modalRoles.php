<!-- Modal Formulario Roles -->
<div class="modal fade" id="modalFormRol" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header headerRegister">
                <h5 class="modal-title" id="titleModal">Nuevo Rol</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="tile mb-0">

                    <div class="tile-body">

                        <!-- Formulario -->
                        <form id="formRol" name="formRol" name="formUsuario" class="">

                            <input type="hidden" id="inputIdRol" name="inputIdRol" value="">

                            <div class="form-group">
                                <label class="control-label label-style " for="inputNombre">Nombre del Rol</label>
                                <input class="form-control inputForm100" type="text" id="inputNombre" name="inputNombre" placeholder="Nombre del rol">
                            </div>

                            <div class="form-group">
                                <label class="control-label label-style " for="inputDescripcion">Descripcion</label>
                                <textarea class="form-control inputForm100" id="inputDescripcion" name="inputDescripcion" rows="3" placeholder="Descripción del rol"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="control-label label-style " for="comboStatus">Estatus</label>
                                <select class="form-control selectForm100" id="comboStatus" name="comboStatus" style="width: 100%;">
                                    <option value="" selected="selected" disabled>Seleccione una opcion</option>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>

                            <div class="tile-footer">
                                <button id="btnActionForm" class="btn btn-primary" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i>
                                    <span id="btnText">Guardar</span>
                                </button>
                                <a class="btn btn-secondary ml-1" href="#" data-dismiss="modal">
                                    <i class="fa fa-fw fa-lg fa-times-circle"></i>Cancelar
                                </a>
                            </div>

                        </form>
                        <!-- Fin Formulario -->

                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
<!-- Fin Modal Formulario Roles -->



<!-- Modal Vista Rol -->
<div class="modal fade" id="modalViewRol" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header header-primary">
                <h5 class="modal-title" id="titleModal">Datos del Rol</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="view-container">

                    <ul class="view-responsive-table">
                        <!-- <li class="view-table-header">
                            <div class="view-col view-col-1">Dato</div>
                            <div class="view-col view-col-2">Descripción</div>
                        </li> -->
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Rol:</div>
                            <div class="view-col view-col-2" id="celRol"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Descripcion:</div>
                            <div class="view-col view-col-2" id="celDescripcion"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Estatus:</div>
                            <div class="view-col view-col-2" id="celEstatus"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Usuario registro:</div>
                            <div class="view-col view-col-2" id="celUsuarioRegistro"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Fecha registro:</div>
                            <div class="view-col view-col-2" id="celFechaRegistro"></div>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>

        </div>

    </div>

</div>
<!-- Fin Modal Vista Rol -->