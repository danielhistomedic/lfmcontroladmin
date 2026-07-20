<!-- Modal Seguimiento Prospectos -->
<div class="modal fade" id="modalUploadEmpleados" tabindex="-1" aria-labelledby="modalUploadEmpleadosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- loading start -->
            <div class="dimmer-modal active">
                <div class="lds-ring">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
            <!-- loading end -->

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Carga Masiva de Empleados</h5>
                <button type="button" aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span aria-hidden="true">×</span></button>
            </div>

            <div class="modal-body">

                <form class="theme-form needs-validation-modal position-relative" id="formSeguimiento" novalidate="">

                    <!-- <div class="form-group col-12 mb-0">
                        <label class="form-label">Datos de Prospecto:</label>

                    </div> -->

                    <!-- <div class="form-group col-12">
                        <hr>
                    </div> -->
                    <div class="form-group col-12">
                        <label class="form-label" for="comboSucursalModal">Sucursal:</label>
                        <select class="select2 custom-select" id="comboSucursalModal" name="sucursal_modal_id" style="width: 100%" required=""></select>
                        <div class="invalid-feedback">Valor requerido.</div>
                    </div>

                    <div class="form-group  col-12">
                        <label class="form-label" for="adjunto_file">Seleccione el archivo que desea cargar:</label>
                        <input type="file" class="form-control btn-square" name="adjunto_file" id="adjunto_file" placeholder="Seleccionar archivo" autocomplete="off">
                        <div class="invalid-feedback">Valor requerido.</div>
                    </div>

                    <!-- <input type="hidden" name="prospecto_id_modal" id="prospecto_id_modal" value="">
                    <input type="hidden" name="prospecto_seguimiento_id" id="prospecto_seguimiento_id" value=""> -->

                </form>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" form="formSeguimiento"><i class="fa-regular fa-floppy-disk fa-lg fa-fw me-1"></i>Guardar</button>
                <button type="button" class="btn btn-info" data-bs-dismiss="modal"><i class="fa-regular fa-circle-xmark fa-lg fa-fw"></i>Cerrar</button>
            </div>

        </div>
    </div>
</div>
<!-- Modal Seguimiento Prospectos -->