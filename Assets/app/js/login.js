var divLoadingForm = document.querySelector('.loading-form');


/*==================================================================
[ DOMContentLoaded ]*/


document.addEventListener("DOMContentLoaded", function(event) {


    /*==================================================================
    [ Form ]*/

    // // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');

    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
            } else {
                login(event, this);
            }
        }, false);
    });


    /*==================================================================
    [ Otras funciones ]*/
    positionCursor();

});




/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

    ShowHideInputPassword();

}, false);


/*==================================================================
[ Otras Funciones ]*/

function ShowHideInputPassword() {

    /*==================================================================
    [ Show hide input ]*/

    $('.show-hide-login').show();
    $('.show-hide-login span').addClass('show');

    $('.show-hide-login span').click(function() {
        if ($(this).hasClass('show')) {
            if (document.querySelector('#pass')) {
                $('#pass').attr('type', 'text');
            }
            $(this).removeClass('show');
        } else {
            if (document.querySelector('#pass')) {
                $('#pass').attr('type', 'password');
            }
            $(this).addClass('show');
        }
    });

    $('form button[type="submit"]').on('click', function() {
        $('.show-hide-login span').addClass('show');
        $('.show-hide-login').parent().find('#pass').attr('type', 'password');
    });

}

function login(event, form) {

    /*-------------------------------------------
    [ Evita la recarga de la pagina. ]*/
    event.preventDefault()

    /*-------------------------------------------
     [ Loading ]*/
    divLoadingForm.style.display = 'flex';

    /*-------------------------------------------
    [ Valida datos de ingreso de usuario ]*/
    postFunctionLogin(form, 'Login', 'validaAcceso', function(responseObj) {

        if (responseObj.respuesta == "ok") {

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, divLoadingForm);

            /* -- Redireciona a la pagina de inicio -- */
            window.location = base_url + '/inicio';

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, divLoadingForm);
        }

    });

}

function positionCursor() {

    var usuario = document.getElementById('email').value;
    if (usuario == '') {
        setTimeout(() => {
            document.getElementById('email').focus();
        }, 500);

    } else {
        setTimeout(() => {
            document.getElementById('pass').focus();
        }, 2000);
    }


}


/*==================================================================
[ Functions Set Ajax ]*/

/**
 * Establece o valida los registros en la base, y recibe un objeto de datos con el resultado de la función ejeuctada a través de ajax, con el metodo POST.
 * @param form Formulario con los datos que se envían al controlador. 
 * @param controller Nombre del controlador a ejecutar. 
 * @param method Nombre del metodo del controlador a ejecutar. 
 * @param result Resultado devuelto por el ajax en formato de objeto.
 * 
 */
function postFunctionLogin(form, controller, method, result) {

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (xhttp.responseText === '') {
                var dataObj = {
                    'respuesta': 'error',
                    'mostrar_mensaje': true,
                    'tiempo': 3000,
                    'mensaje': 'Error desconocido.'
                };
                result(dataObj);
            } else {
                var dataObj = JSON.parse(xhttp.responseText);
                result(dataObj);
            }
        }
    };
    var ajaxUrl = base_url + '/' + controller + '/' + method;
    let formData = new FormData(form);
    xhttp.open("POST", ajaxUrl, true);
    xhttp.send(formData);
}