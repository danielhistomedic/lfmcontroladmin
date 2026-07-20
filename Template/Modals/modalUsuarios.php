<!-- Modal Formulario Usuarios -->
<div class="modal fade" id="modalFormUsuario" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header headerRegister">
                <h5 class="modal-title" id="titleModal">Nuevo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="tile mb-0">

                    <div class="tile-body">

                        <!-- Formulario -->
                        <form id="formUsuario" name="formUsuario" class="form-horizontal">

                            <input type="hidden" id="inputIdUsuario" name="inputIdUsuario" value="">

                            <div class="form-row">
                                <div class="form-group col-12 col-md-6">
                                    <label class="control-label label-style " for="inputIdentificacion">Identificacion</label>
                                    <input class="form-control inputForm100" type="text" id="inputIdentificacion" name="inputIdentificacion" placeholder="Identificacion">
                                </div>
                            </div>

                            <div class="form-row">

                                <div class="form-group col-12 col-md-6">
                                    <label class="control-label label-style " for="inputNombre">Nombre</label>
                                    <input class="form-control inputForm100" type="text" id="inputNombre" name="inputNombre" placeholder="Nombre">
                                </div>

                                <div class="form-group col-12 col-md-6">
                                    <label class="control-label label-style " for="inputApellidos">Apellidos</label>
                                    <input class="form-control inputForm100" type="text" id="inputApellidos" name="inputApellidos" placeholder="Apellidos">
                                </div>

                            </div>

                            <div class="form-row">

                                <div class="form-group col-12 col-md-6">
                                    <label class="control-label label-style " for="inputTelefono">Telefono</label>
                                    <input class="form-control inputForm100 valid validNumber" type="text" id="inputTelefono" onkeypress="return controlTag(event);" name="inputTelefono" placeholder="Telefono">
                                </div>

                                <div class="form-group col-12 col-md-6">
                                    <label class="control-label label-style " for="inputEmail">Email</label>
                                    <input class="form-control inputForm100 valid validEmail" type="email" id="inputEmail" name="inputEmail" placeholder="Email">
                                </div>

                            </div>

                            <div class="form-row">

                                <div class="form-group col-12">
                                    <label class="control-label label-style " for="comboRoles">Tipo de Usuario (Rol)</label>
                                    <select class="form-control selectForm100" id="comboRoles" name="comboRoles" style="width: 100%;">
                                    </select>
                                </div>

                            </div>


                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label class="control-label label-style " for="comboEspecialidad">Especialidad(es)</label>
                                    <select class="form-control selectForm100" id="comboEspecialidad" name="comboEspecialidad[]" multiple="multiple" style="width: 100%;">
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">

                                <div class="form-group col-12 col-md-6">
                                    <label class="control-label label-style " for="inputPassword">Password</label>
                                    <input class="form-control inputForm100" type="password" id="inputPassword" name="inputPassword" placeholder="Password">
                                </div>

                                <div class="form-group col-12 col-md-6">
                                    <label class="control-label label-style " for="comboStatus">Estatus</label>
                                    <select class="form-control selectForm100" id="comboStatus" name="comboStatus" style="width: 100%;">
                                        <option value="" selected="selected" disabled>Seleccione una opcion</option>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>

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
<!-- Fin Modal Formulario Usuarios -->



<!-- Modal Vista Usuario -->
<div class="modal fade" id="modalViewUsuario" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header header-primary">
                <h5 class="modal-title" id="titleModal">Datos del Usuario</h5>
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
                            <div class="view-col view-col-1">Usuario:</div>
                            <div class="view-col view-col-2" id="celUsuario"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Nombre:</div>
                            <div class="view-col view-col-2" id="celNombre"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Apellidos:</div>
                            <div class="view-col view-col-2" id="celApellido"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Teléfono:</div>
                            <div class="view-col view-col-2" id="celTelefono"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Email:</div>
                            <div class="view-col view-col-2" id="celEmail"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Tipo Usuario:</div>
                            <div class="view-col view-col-2" id="celTipoUsuario"></div>
                        </li>
                        <li class="view-table-row">
                            <div class="view-col view-col-1">Especialidades:</div>
                            <div class="view-col view-col-2" data-label="Especialidades" id="celEspecialidades"></div>
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
<!-- Fin Modal Vista Usuario -->