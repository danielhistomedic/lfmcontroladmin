/*==================================================================
[ Variables ]*/
var controller = "Legales";

var divLoading = document.querySelector('.loading-panel');
var divLoadingForm = document.querySelector('.loading-form');


/*==================================================================
[ DOMContentLoaded ]*/

document.addEventListener("DOMContentLoaded", function(event) {

    /*==================================================================
    [ Form ]*/

    /*-------------------------------------------
    [ Form - Agregar evento onsubmit a todos los formularios de la pagina 
    y Obtener todos los formularios a los que queremos aplicar estilos de validación de Bootstrap personalizados.]*/
    var forms = document.getElementsByClassName('needs-validation');

    // Bucle sobre los objetos seleccionado y evitar el envío
    var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
            } else {
                setRecord(event, this);
            }
        }, false);
    });

    /*==================================================================
    [ Botons de Accion ]*/

    /*==================================================================
    [ DataTable ]*/

    /*==================================================================
    [ Select2 ]*/

    /*==================================================================
    [ summernote ]*/

    $('.summernote').summernote({
        lang: 'es-ES', // default: 'en-US'
        placeholder: 'Ingrese Texto Correspondiente',
        tabsize: 1,
        height: 150,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            // ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['codeview', 'help']]
        ]
    });


});

/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

    /*-------------------------------------------
    [ Funciones Fill Selects ]*/
    // fillSelectRoles();
    fntEdit();

}, false)

/*==================================================================
[ Funciones Fill Selects e Inicializa Select2]*/


/*==================================================================
[ Barra de Botones y Botones de Accion ]*/

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


/*==================================================================
[ Botones Datatable ]*/


/*==================================================================
[ Carga de Datos ]*/


function fntEdit() {

    /*-------------------------------------------
    [ Loading ]*/
    divLoading.style.display = 'flex';

    /*-------------------------------------------
     [ Ajax ]*/
    var formData = new FormData();
    postFunctionData(formData, controller, 'getRecord', function(responseObj) {
        if (responseObj.respuesta == "ok") {

            // --- Carga los datos recibidos ---
            cargarDatosEdit(responseObj);

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");
        }

        /*-------------------------------------------
        [ Loading ]*/
        divLoading.style.display = 'none';

    });

}


function cargarDatosEdit(responseObj) {

    /*-------------------------------------------
    [ Mostrar Loading en div. ]*/
    divLoading.style.display = "flex";

    /*-------------------------------------------
    [ Obtiene datos de Form  ]*/
    document.getElementById("record_id").value = responseObj.data.id;

    $("#terminos").summernote("code", responseObj.data.terminos);
    $("#aviso").summernote("code", responseObj.data.aviso);
    $("#envio").summernote("code", responseObj.data.envio);

    divLoading.style.display = 'none';

}

/*==================================================================
[ DataTable ]*/