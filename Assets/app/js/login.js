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

    event.preventDefault();
    if (form.dataset.sending === '1') { return; }
    form.dataset.sending = '1';
    var submit = form.querySelector('button[type="submit"]');
    if (submit) { submit.disabled = true; }
    if (divLoadingForm) { divLoadingForm.style.display = 'flex'; }
    postFunctionLogin(form, 'Login', 'validaAcceso', function(responseObj) {
        delete form.dataset.sending;
        if (submit) { submit.disabled = false; }
        if (divLoadingForm) { divLoadingForm.style.display = 'none'; }
        if (responseObj.respuesta === 'ok') {
            window.location = base_url + '/inicio';
        } else {
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
    var completed = false;
    // Todos los fallos restituyen el formulario, incluidos HTTP, red, timeout y JSON inválido.
    function finish(response) {
        if (completed) { return; }
        completed = true;
        result(response);
    }
    function failure(message) {
        finish({ respuesta: 'error', mostrar_mensaje: true, tiempo: 3000, mensaje: message });
    }
    xhttp.onload = function() {
        try {
            var response = JSON.parse(xhttp.responseText);
            if (!response || (response.respuesta !== 'ok' && response.respuesta !== 'error')) {
                throw new Error('Respuesta inválida');
            }
            if (xhttp.status < 200 || xhttp.status >= 300) { response.respuesta = 'error'; }
            finish(response);
        } catch (error) {
            failure('No se pudo procesar la respuesta. Recargue la página e intente nuevamente.');
        }
    };
    xhttp.onerror = function() { failure('No se pudo conectar. Revise su conexión e intente nuevamente.'); };
    xhttp.ontimeout = function() { failure('El acceso tardó demasiado. Intente nuevamente.'); };
    xhttp.onabort = function() { failure('La solicitud fue cancelada. Intente nuevamente.'); };
    try {
        xhttp.open('POST', base_url + '/' + controller + '/' + method, true);
        xhttp.timeout = 30000;
        xhttp.send(new FormData(form));
    } catch (error) {
        failure('No se pudo enviar el formulario. Intente nuevamente.');
    }
}
