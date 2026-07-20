<!-- Modal Seguimiento Prospectos -->
<div class="modal fade" id="modalSeleccionarCandidato" tabindex="-1" aria-labelledby="modalSeleccionarCandidatoLabel" aria-hidden="true">
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
                <h5 class="modal-title fw-bold">Seleccionar Candidato de Bolsa de Trabajo</h5>
                <button type="button" aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span aria-hidden="true">×</span></button>
            </div>

            <div class="modal-body">

                <form class="theme-form needs-validation-modal-candidatos position-relative" id="formSeleccionarCandidato" novalidate="">

                    <!-- <div class="form-group col-12 mb-0">
                        <label class="form-label">Datos de Prospecto:</label>

                    </div> -->

                    <!-- <div class="form-group col-12">
                        <hr>
                    </div> -->
                    <div class="form-group col-12">
                        <label class="form-label" for="comboSucursalCandidatoModal">Sucursal:</label>
                        <select class="select2 custom-select" id="comboSucursalCandidatoModal" name="sucursal_candidato_modal_id" style="width: 100%" required=""></select>
                        <div class="invalid-feedback">Valor requerido.</div>
                    </div>

                    <div class="form-group col-12">
                        <label class="form-label" for="comboCandidatosModal">Candidato:</label>
                        <select class="select2 custom-select" id="comboCandidatosModal" name="candidato_modal_id" style="width: 100%" required=""></select>
                        <div class="invalid-feedback">Valor requerido.</div>
                    </div>
                    <div class="form-group col-12">
                        <div class="card border-0 bg-color-grey">
                            <div class="card-body">
                                <i class="icon-user-following icons text-color-primary text-8"></i>
                                <h4 class="card-title mt-2 mb-1 text-4 font-weight-bold">Card Title</h4>
                                <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur rhoncus nulla dui, in dapi.</p>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="candidato_id_modal" id="candidato_id_modal" value="">

                    <!-- <input type="hidden" name="prospecto_id_modal" id="prospecto_id_modal" value="">
                    <input type="hidden" name="prospecto_seguimiento_id" id="prospecto_seguimiento_id" value=""> -->

                </form>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" form="formSeleccionarCandidato"><i class="fa-regular fa-floppy-disk fa-lg fa-fw me-1"></i>Guardar</button>
                <button type="button" class="btn btn-info" data-bs-dismiss="modal"><i class="fa-regular fa-circle-xmark fa-lg fa-fw"></i>Cerrar</button>
            </div>

        </div>
    </div>
</div>
<!-- Modal Seguimiento Prospectos -->