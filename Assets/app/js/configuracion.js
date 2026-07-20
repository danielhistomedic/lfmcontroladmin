/*==================================================================
[ Variables ]*/
var controller = "Configuracion";

var divLoading = document.querySelector('.loading-panel');
var divLoadingForm = document.querySelector('.loading-form');

/*==================================================================
[ DOMContentLoaded ]*/

document.addEventListener("DOMContentLoaded", function(event) {

    /*-------------------------------------------
    [ Form - Agregar evento onsubmit ala todos los formularios de la pagina ]*/
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');

    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
            } else {
                setRecord(event);
            }
        }, false);
    });

    /*==================================================================
    [ Password ]*/
    $('.show-hide-smtp').show();
    $('.show-hide-smtp span').addClass('show');

    $('.show-hide-smtp span').click(function() {
        if ($(this).hasClass('show')) {
            if (document.querySelector('#smtp_password')) {
                $('#smtp_password').attr('type', 'text');
            }
            $(this).removeClass('show');
        } else {
            if (document.querySelector('#smtp_password')) {
                $('#smtp_password').attr('type', 'password');
            }
            $(this).addClass('show');
        }
    });


});

/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

    fntEdit();

}, false)


/*==================================================================
[ Carga de Datos ]*/

function fntEdit() {

    /*-------------------------------------------
    [ Loading ]*/
    divLoading.style.display = 'flex';

    /*-------------------------------------------
    [ Loading ]*/
    divLoading.style.display = 'flex';
    getFunctionData(controller, 'getRecord', '', (responseObj) => {

        if (responseObj.respuesta == "ok") {

            /** -- Cargar Datos recibidos -- */
            cargarDatosEdit(responseObj.data);

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, divLoading);

        } else {
            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, divLoading);
        }

    });

}

function cargarDatosEdit(data) {

    document.getElementById('record_id').value = data.id;

    document.getElementById('smtp_host').value = data.smtp_host;
    document.getElementById('smtp_usuario').value = data.smtp_usuario;
    document.getElementById('smtp_password').value = data.smtp_password;
    document.getElementById('smtp_puerto').value = data.smtp_puerto;

    document.getElementById('telefono_contacto').value = data.telefono_contacto;
    document.getElementById('email_contacto').value = data.email_contacto;
    document.getElementById('url_tienda').value = data.url_tienda;

    document.getElementById('nombre_remitente').value = data.nombre_remitente;
    document.getElementById('email_remitente').value = data.email_remitente;
    document.getElementById('sitio_web').value = data.sitio_web;

    document.getElementById('email_destino').value = data.email_destino;

}

function setRecord(event) {

    /*-------------------------------------------
    [ Evita la recarga de la pagina. ]*/
    event.preventDefault();

    /*-------------------------------------------
    [ Loading ]*/
    divLoading.style.display = 'flex';


    /*-------------------------------------------
    [ Ajax ]*/
    let form = document.getElementById("formRecords");
    postFunction(form, controller, 'setRecord', function(responseObj) {

        if (responseObj.respuesta == "ok") {

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, divLoading);

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, divLoading);
        }

    });


}