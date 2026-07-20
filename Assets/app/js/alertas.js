"use strict";

/*==================================================================
[ Constantes ]*/

/* -- Constantes para usar el icono representativo en los mensajes modales de Alerta. -- */
const iconMensajeSuccess = '<i class="fa-light fa-face-smile-wink f-20"></i>';
const iconMensajeError = '<i class="fa-light fa-face-frown-slight f-20"></i>';
const iconMensajeWarning = '<i class="fa-light fa-face-thinking f-20"></i>';
const iconMensajeInfo = '<i class="fa-light fa-face-grin-wink f-20"></i>';

/*==================================================================
[ Funciones Alertas ]*/

/*-------------------------------------------
[ Animate ]*/

function testAnim(x) {
    $('.modal .modal-dialog').attr('class', 'modal-dialog  ' + x + '  animated');
};

var modal_animate_custom = {
    init: function() {

        let modals = document.querySelectorAll(".modal-alerta");

        for (let index = 0; index < modals.length; index++) {
            const modal = modals[index];
            $(modal).on('show.bs.modal', function(e) {
                var anim = $('#entrance').val();
                testAnim(anim);
            })
            $(modal).on('hide.bs.modal', function(e) {
                var anim = $('#exit').val();
                testAnim(anim);
            })

        }

    }
};

(function($) {
    "use strict";
    modal_animate_custom.init()
})(jQuery);


/*==================================================================
[ Promise ]*/

function mensajeAlertaModal(params) {

    return new Promise(function(resolve, reject) {

        try {

            $('.modalTitle').html(params.title);
            $('.modalMensaje').html(params.text);
            $('.modalButtonTitle').html(params.textButton);
            if (params.textCancelButton == undefined) {
                params.textCancelButton = "No";
                $('.modalCancelButtonTitle').html(params.textCancelButton);
            } else {
                $('.modalCancelButtonTitle').html(params.textCancelButton);
            }

            if (params.icon == 'success') {
                var modalAlerta = "modalSuccess";

            } else if (params.icon == 'warning') {
                var modalAlerta = "modalWarning";

            } else if (params.icon == 'info') {
                var modalAlerta = "modalInfo";

            } else if (params.icon == 'error') {
                var modalAlerta = "modalDanger";

            } else {
                var modalAlerta = "modalUndefined";

            }

            var modalEl = document.getElementById(modalAlerta);
            var myModalAlert = new bootstrap.Modal(modalEl, {
                keyboard: false
            })


            // -------------------------------------------
            // [ Ejecuta el toggle para mostrar el modal ] ---- //
            myModalAlert.toggle();


            // -------------------------------------------
            // [ Envia resolve para cerrar automaticamente el Modal ] ---- //
            if (modalAlerta != "modalWarning") {
                setTimeout(() => {
                    myModalAlert.hide()
                    resolve({
                        dismissTimer: true,
                        dismissUser: false,
                        dismiss: true
                    });
                }, params.timer);
            }


            // -------------------------------------------
            // [ Envia resolve al ejecutarse el evento de cerrar el modal manualmente ] ---- //
            modalEl.addEventListener('hidden.bs.modal', function(event) {
                resolve({
                    dismissTimer: false,
                    dismissUser: true,
                    dismiss: true
                });
            })

            // -------------------------------------------
            // [ Envia resolve al ejecutarse el evento de cerrar el modal manualmente ] ---- //
            var btnWarinign = document.querySelector(".btn-warning-si")
            btnWarinign.addEventListener('click', function(event) {
                resolve({
                    dismissTimer: false,
                    dismissUser: true,
                    dismiss: true,
                    si: true
                });
            })

        } catch (error) {
            reject('error');
        }

    });

}


/*==================================================================
[ Alerta ]*/

function alerta_success_login(responseObj, divLoading) {

    if (responseObj.mostrar_mensaje == true) {

        mensajeAlertaModal({
            icon: 'success',
            timer: responseObj.tiempo,
            title: iconMensajeSuccess + ' ¡Atención!',
            text: responseObj.mensaje,
            textButton: 'Cerrar'
        }).then(function(result) {
            if (result.dismissTimer == true) {
                if (responseObj.email_enviado == 'no') {
                    var responseObj_warning = {
                        mostrar_mensaje: true,
                        tiempo: 3000,
                        mensaje: 'Email de confirmación no se envió. Actualice manualmente el password del usuario.'
                    }
                    alerta_warning_only(responseObj_warning, divLoading)
                } else {
                    divLoading.style.display = "none";
                }
            };
            if (result.dismissUser == true) {
                if (responseObj.email_enviado == 'no') {
                    var responseObj_warning = {
                        mostrar_mensaje: true,
                        tiempo: 3000,
                        mensaje: 'Email de confirmación no se envió. Actualice manualmente el password del usuario.'
                    }
                    alerta_warning_only(responseObj_warning, divLoading)
                } else {
                    divLoading.style.display = "none";
                }
            }
        });
    } else {
        divLoading.style.display = "none";
    }
}

function alerta_success(responseObj, divLoading) {

    if (responseObj.mostrar_mensaje == true) {

        mensajeAlertaModal({
            icon: 'success',
            timer: responseObj.tiempo,
            title: iconMensajeSuccess + ' ¡Atención!',
            text: responseObj.mensaje,
            textButton: 'Cerrar'
        }).then(function(result) {
            if (result.dismissTimer == true) {
                if (divLoading != "") {
                    divLoading.style.display = "none";
                }
                if (typeof table !== 'undefined') {
                    if ($.fn.DataTable.isDataTable(table)) {
                        setTimeout(() => {
                            table.columns.adjust();
                        }, 500);
                    }
                }
            };
            if (result.dismissUser == true) {
                if (divLoading != "") {
                    divLoading.style.display = "none";
                }
                if (typeof table !== 'undefined') {
                    if ($.fn.DataTable.isDataTable(table)) {
                        setTimeout(() => {
                            table.columns.adjust();
                        }, 200);
                    }
                }
            }
        });
    } else {
        if (divLoading != "") {
            divLoading.style.display = "none";
        }
        if (typeof table !== 'undefined') {
            if ($.fn.DataTable.isDataTable(table)) {
                setTimeout(() => {
                    table.columns.adjust();
                }, 200);
            }
        }
    }
}

function alerta_error(responseObj, divLoading) {

    if (responseObj.mostrar_mensaje == true) {

        mensajeAlertaModal({
            icon: 'error',
            timer: responseObj.tiempo,
            title: iconMensajeError + ' ¡Atención!',
            text: responseObj.mensaje,
            textButton: 'Cerrar'
        }).then(function(result) {
            if (result.dismissTimer == true) {
                if (divLoading != "") {
                    divLoading.style.display = "none";
                }
                if (typeof table !== 'undefined') {
                    if ($.fn.DataTable.isDataTable(table)) {
                        setTimeout(() => {
                            table.columns.adjust();
                        }, 500);
                    }
                }
            };
            if (result.dismissUser == true) {
                if (divLoading != "") {
                    divLoading.style.display = "none";
                }
                if (typeof table !== 'undefined') {
                    if ($.fn.DataTable.isDataTable(table)) {
                        setTimeout(() => {
                            table.columns.adjust();
                        }, 200);
                    }
                }
            }
        });
    } else {
        if (divLoading != "") {
            divLoading.style.display = "none";
        }
        if (typeof table !== 'undefined') {
            if ($.fn.DataTable.isDataTable(table)) {
                setTimeout(() => {
                    table.columns.adjust();
                }, 200);
            }
        }
    }
}

function alerta_warning_only(responseObj, divLoading) {

    if (responseObj.mostrar_mensaje == true) {

        mensajeAlertaModal({
            icon: 'error',
            timer: responseObj.tiempo,
            title: iconMensajeError + ' ¡Atención!',
            text: responseObj.mensaje,
            textButton: 'Cerrar'
        }).then(function(result) {
            if (result.dismissTimer == true) {
                if (divLoading != "") {
                    divLoading.style.display = "none";
                }
                if (typeof table !== 'undefined') {
                    if ($.fn.DataTable.isDataTable(table)) {
                        setTimeout(() => {
                            table.columns.adjust();
                        }, 500);
                    }
                }
            };
            if (result.dismissUser == true) {
                if (divLoading != "") {
                    divLoading.style.display = "none";
                }
                if (typeof table !== 'undefined') {
                    if ($.fn.DataTable.isDataTable(table)) {
                        setTimeout(() => {
                            table.columns.adjust();
                        }, 200);
                    }
                }
            }
        });
    } else {
        if (divLoading != "") {
            divLoading.style.display = "none";
        }
        if (typeof table !== 'undefined') {
            if ($.fn.DataTable.isDataTable(table)) {
                setTimeout(() => {
                    table.columns.adjust();
                }, 200);
            }
        }
    }
}

function alerta_warning(responseObj, divLoading) {

    if (responseObj.mostrar_mensaje == true) {

        mensajeAlertaModal({
            icon: 'warning',
            timer: responseObj.tiempo,
            title: iconMensajeError + ' ¡Atención!',
            text: responseObj.mensaje,
            textButton: 'Cerrar'
        }).then(function(result) {
            if (result.dismissTimer == true) {
                if (divLoading != "") {
                    divLoading.style.display = "none";
                }
                if (typeof table !== 'undefined') {
                    if ($.fn.DataTable.isDataTable(table)) {
                        setTimeout(() => {
                            table.columns.adjust();
                        }, 500);
                    }
                }
            };
            if (result.dismissUser == true) {
                if (divLoading != "") {
                    divLoading.style.display = "none";
                }
                if (typeof table !== 'undefined') {
                    if ($.fn.DataTable.isDataTable(table)) {
                        setTimeout(() => {
                            table.columns.adjust();
                        }, 200);
                    }
                }
            }
        });
    } else {
        if (divLoading != "") {
            divLoading.style.display = "none";
        }
        if (typeof table !== 'undefined') {
            if ($.fn.DataTable.isDataTable(table)) {
                setTimeout(() => {
                    table.columns.adjust();
                }, 200);
            }
        }
    }
}