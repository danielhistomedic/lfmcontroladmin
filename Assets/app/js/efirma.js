/*==================================================================
[ Variables ]*/
var controller = "EFirma";

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
    $('.show-hide-efirma').show();
    $('.show-hide-efirma span').addClass('show');

    $('.show-hide-efirma span').click(function() {
        if ($(this).hasClass('show')) {
            if (document.querySelector('#password')) {
                $('#password').attr('type', 'text');
            }
            $(this).removeClass('show');
        } else {
            if (document.querySelector('#password')) {
                $('#password').attr('type', 'password');
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

    document.getElementById('rfc').value = data.rfc;

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