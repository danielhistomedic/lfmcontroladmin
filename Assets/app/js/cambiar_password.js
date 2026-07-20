/*==================================================================
[ Variables ]*/
var controller = "Usuarios";

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
                cambiarPassword(event);
            }
        }, false);
    });

    /*==================================================================
    [ Cambiar Password ]*/
    $('.show-hide-cambiar').show();
    $('.show-hide span').addClass('show');

    $('.show-hide-cambiar span').click(function() {
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

    /*==================================================================
    [ Cambiar Password Confirmar ]*/
    $('.show-hide-cambiar-confirm').show();
    $('.show-hide span').addClass('show');

    $('.show-hide-cambiar-confirm span').click(function() {
        if ($(this).hasClass('show')) {
            if (document.querySelector('#confirmar_password')) {
                $('#confirmar_password').attr('type', 'text');
            }
            $(this).removeClass('show');
        } else {
            if (document.querySelector('#confirmar_password')) {
                $('#confirmar_password').attr('type', 'password');
            }
            $(this).addClass('show');
        }
    });

    $('form button[type="submit"]').on('click', function() {
        $('.show-hide-confirm span').addClass('show');
        $('.show-hide-confirm').parent().find('#password');
        $('.show-hide-confirm span').addClass('show');
        $('.show-hide-confirm').parent().find('#confirmar_password');
    });

});


/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

}, false)



/*==================================================================
[ Funciones ]*/

function cambiarPassword(event) {

    /*-------------------------------------------
    [ Evita la recarga de la pagina. ]*/
    event.preventDefault();

    /*-------------------------------------------
    [ Loading ]*/
    divLoading.style.display = 'flex';


    /*-------------------------------------------
    [ Ajax ]*/
    let form = document.getElementById("formRecords");
    postFunction(form, controller, 'actualizarPassword', function(responseObj) {

        if (responseObj.respuesta == "ok") {

            /** -- ResetForm -- */
            resetForm(form, '#formRecords');

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, divLoading);

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, divLoading);
        }

    });


}