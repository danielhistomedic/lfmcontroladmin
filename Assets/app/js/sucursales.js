/*==================================================================
[ Variables de Archivo ]*/

let table;
let tableElement = "#tableRecords";
let tableElementJS = "tableRecords";
let configTable = "";

let controllers = "Sucursales";
let formElement = document.getElementById('formRegistration');
let formElementJS = "#formRegistration";

let list = document.getElementById('record_list');
let editar = document.getElementById('create_edit');
let view = document.getElementById('view');

var divLoading = document.querySelector('.dimmer');

/*==================================================================
[ DOMContentLoaded ]*/

document.addEventListener("DOMContentLoaded", function(event) {


    /*==================================================================
    [ Form ]*/

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
                set(event);
            }
        }, false);
    });


    /*==================================================================
    [ Botons de Accion ]*/


    /*-------------------------------------------
    [ Agregar evento click a Nuevo  ]*/
    if (document.getElementById("btnCrear")) {
        let btnElement = document.getElementById('btnCrear');
        btnElement.onclick = function() { fntNew() };
    }

    /*-------------------------------------------
    [ Agregar evento click a Editar  ]*/

    if (document.getElementById("btnEditar")) {
        let btnElement = document.getElementById('btnEditar');
        btnElement.onclick = function() { fntEdit(this) };
    }

    /*-------------------------------------------
    [ Agregar evento click a Cancelar Edicion/Creación   ]*/

    if (document.querySelector(".btnReturnList")) {
        let btnElement = document.querySelectorAll('.btnReturnList');
        for (let index = 0; index < btnElement.length; index++) {
            const element = btnElement[index];
            element.onclick = function() { fntReturnList() };
        }
    }


    /*==================================================================
    [ DataTable ]*/

    if (document.getElementById(tableElementJS)) {

        /*-------------------------------------------
        [ DataTable Inicializa ]*/
        setConfigTable(controllers, 'getAllDatatable');
        table = $(tableElement).DataTable(configTable);

        //______Select2 
        $('.form-select').select2({
            minimumResultsForSearch: Infinity
        });


        /*-------------------------------------------
        [ DataTable - Se ejecuta después de inicializar la tabla ]*/
        $(tableElement).on('init.dt', function() {

            //Valida si se activa el botón excel .
            validaPermisoExportar(menu);

        });

        /*-------------------------------------------
         [ DataTable - Se ejecuta después de terminar ajax ]*/
        $(tableElement).on('xhr.dt', function(e, settings, json, xhr) {


        });

        /*-------------------------------------------
        [ DataTable - Se ejecuta después de redibujarse la tabla ]*/
        $(tableElement).on('draw.dt', function() {
            table.columns.adjust();
        });

        /*-------------------------------------------
        [ DataTable - Se ejecuta después de dar click en el primer elemento td del tr de la tabla ]*/
        $(tableElement).on('click', 'tbody tr>td', function() {

        });

    }

});

/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {


}, false)

$(window).resize(function() {
    if ($.fn.DataTable.isDataTable(table)) {
        table.columns.adjust();
    }
});

$(document).on('click', '[data-bs-toggle="sidebar"]', function(event) {
    adjustColumns();
});

function adjustColumns() {
    if ($.fn.DataTable.isDataTable(table)) {
        setTimeout(() => {
            table.columns.adjust();
        }, 200);
    }
}

/*==================================================================
[ Funciones Fill Selects e Inicializa Select2]*/


/*==================================================================
[ Barra de Botones y Botones de Accion ]*/

function fntNew() {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
    document.getElementById("btnCrear").classList.add("active");
    document.querySelector(".btnReturnList").classList.remove("active");


    /*-------------------------------------------
    [ Limpiar Form ]*/
    document.getElementById('id_record').value = '';
    resetForm(formElement, formElementJS);


    /*-------------------------------------------
    [ Asignar variables ]*/
    view.style.display = "none";
    list.style.display = "none";
    editar.style.display = "block";

    //-----------------------------------
    //[ Animación de Paneles ]

    //Elemento que dispara la animación
    let eLOrigen = document.getElementById("btnCrear");

    //Elemento que recibe la animación
    let eLDestino = editar;

    // Ejecuta Funcion de animacion mostrar el Panel de Edición de Datos
    animationButton(eLOrigen, eLDestino);

}

function fntReturnList() {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
    document.getElementById("btnCrear").classList.remove("active");
    document.querySelector(".btnReturnList").classList.add("active");

    /*-------------------------------------------
    [ Asignación de variables ]*/
    list.style.display = "block";
    editar.style.display = "none";
    view.style.display = "none";

    //-----------------------------------
    //[ Animación de Paneles ]

    //Elemento que dispara la animación
    let eLOrigen = document.querySelector(".btnReturnList");

    //Elemento que recibe la animación
    let eLDestino = list;

    // Ejecuta Funcion de animacion mostrar el Panel de Edición de Datos
    animationButton(eLOrigen, eLDestino);

}

function set(event) {

    /*-------------------------------------------
    [ Evita la recarga de la pagina. ]*/
    event.preventDefault();

    /*-------------------------------------------
    [ Mostrar Loading en div. ]*/
    divLoading.style.display = "flex";

    /*-------------------------------------------
    [ Ajax ]*/
    postFunction(formElement, controllers, 'set', function(responseObj) {

        if (responseObj.respuesta == "ok") {

            /** -- Recargar tabla -- */
            table.ajax.reload(null, false);

            /** -- Regresar a Listado -- */
            fntReturnList();

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, divLoading);

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, divLoading);
        }

    });

}

function fntEdit(btnElement) {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
    document.getElementById("btnCrear").classList.remove("active");
    document.querySelector(".btnReturnList").classList.remove("active");

    /*-------------------------------------------
    [ Obtiene id de registro ]*/
    var idRegistro;
    if (btnElement.getAttribute("data-id")) {
        idRegistro = btnElement.getAttribute("data-id");
    } else {
        idRegistro = document.getElementById("id_record").value;
    }


    /*-------------------------------------------
    [ Deshabilita elemento para prevenir doble registro ]*/
    addLoadingButtonOpcionesDataTable(btnElement, 'fa-pencil-alt')


    /*-------------------------------------------
     [ Ajax ]*/
    getFunctionData(controllers, 'get', idRegistro, function(responseObj) {

        if (responseObj.respuesta == "ok") {

            // --- Carga los datos recibidos ---
            cargarDatosEdit(responseObj);

            /*-------------------------------------------
            [ Animación de Paneles ]*/
            view.style.display = "none";
            list.style.display = "none";
            editar.style.display = "block";

            //Elemento que dispara la animación
            let eLOrigen = $(btnElement);

            //Elemento que recibe la animación
            let eLDestino = editar;

            // Ejecuta Funcion de animacion mostrar el Panel de Edición de Datos
            animationButton(eLOrigen, eLDestino);

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");
        }

        // --- Restablecer Icon ---
        removeLoadingButtonOpcionesDataTable(btnElement, 'fa-pencil-alt');

    });

}



/*==================================================================
[ Botones Datatable ]*/

function fntActive(btnElement) {

    /*-------------------------------------------
     [ Obtiene id de registro ]*/
    let idRegistro = btnElement.getAttribute("data-id");

    /*-------------------------------------------
    [ Deshabilita elemento para prevenir doble registro ]*/
    addLoadingButtonOpcionesDataTable(btnElement, 'fa-arrow-rotate-left')


    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    formData.append('id', idRegistro);
    formData.append('estatus', 1);

    postFunctionData(formData, controllers, 'active', function(responseObj) {

        if (responseObj.respuesta == "ok") {

            // --- Recarga datos de tabla ---
            table.ajax.reload(null, false);

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, "");
        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");
        }

        // --- Restablecer Icon ---
        removeLoadingButtonOpcionesDataTable(btnElement, 'fa-arrow-rotate-left');

    });

}

function fntDelete(btnElement) {

    /*-------------------------------------------
     [ Obtiene id de registro ]*/
    let idRegistro = btnElement.getAttribute("data-id");

    /*-------------------------------------------
    [ Deshabilita elemento para prevenir doble registro ]*/
    addLoadingButtonOpcionesDataTable(btnElement, 'fa-trash-can')

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    formData.append('id', idRegistro);
    formData.append('estatus', 0);

    postFunctionData(formData, controllers, 'delete', function(responseObj) {

        if (responseObj.respuesta == "ok") {

            // --- Recarga datos de tabla ---
            table.ajax.reload(null, false);

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, "");

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");

        }

        // --- Restablecer Icon ---
        removeLoadingButtonOpcionesDataTable(btnElement, 'fa-trash-can');

    });


}

function fntView(btnElement) {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
    document.getElementById("btnCrear").classList.remove("active");
    document.querySelector(".btnReturnList").classList.remove("active");

    /*-------------------------------------------
     [ Obtiene id de registro ]*/
    let idRegistro = btnElement.getAttribute("data-id");

    /*-------------------------------------------
    [ Deshabilita elemento para prevenir doble registro ]*/
    addLoadingButtonOpcionesDataTable(btnElement, 'fa-eye')

    /*-------------------------------------------
     [ Ajax ]*/
    getFunctionData(controllers, 'get', idRegistro, function(responseObj) {

        if (responseObj.respuesta == "ok") {

            // --- Cargar datos recibidos ---
            cargarDatos(responseObj);

            //-----------------------------------
            //[ Animación de Paneles ]
            list.style.display = "none";
            editar.style.display = "none";
            view.style.display = "block";

            //Elemento que dispara la animación
            let eLOrigen = $('.view');

            //Elemento que recibe la animación
            let eLDestino = view;

            // Ejecuta Funcion de animacion mostrar el Panel de Edición de Datos
            animationButton(eLOrigen, eLDestino);

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");
        }

        // --- Restablecer Icon ---
        removeLoadingButtonOpcionesDataTable(btnElement, 'fa-eye');

    });

}


/*==================================================================
[ Carga de Datos ]*/

function cargarDatosEdit(responseObj) {

    /*-------------------------------------------
    [ Obtiene datos de Form  ]*/
    document.getElementById("id_record").value = responseObj.data.id;
    document.getElementById("nombre").value = responseObj.data.nombre;
    document.getElementById("email").value = responseObj.data.email;
    document.getElementById("telefono").value = responseObj.data.telefono;
}

function cargarDatos(responseObj) {

    /*-------------------------------------------
    [ Llena los datos del form view  ]*/
    document.getElementById("id_record").value = responseObj.data.id;
    document.getElementById("nombre_read").innerHTML = responseObj.data.nombre;
    document.getElementById("email_read").innerHTML = responseObj.data.email;
    document.getElementById("telefono_read").innerHTML = responseObj.data.telefono;

    let estatus =
        responseObj.data.activo == 1 ?
        '<span class="badge bg-success-gradient  me-1 mb-1 mt-1">Activo</span>' :
        '<span class="badge bg-danger-gradient  me-1 mb-1 mt-1">Inactivo</span>';
    document.getElementById("estatus_read").innerHTML = estatus;

    document.getElementById("fechaRegistro_read").innerHTML = responseObj.data.updated_at;
    document.getElementById("usuarioRegistro_read").innerHTML = responseObj.data.usuario;

}


/*==================================================================
[ DataTable ]*/

function setConfigTable(controlador, metodo) {

    configTable = {
        scrollX: "100%",
        fixedHeader: true,
        "destroy": true,
        "select": true,
        "order": [
            [0, "desc"]
        ],
        "iDisplayLength": 5,
        "lengthMenu": [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Todos"]
        ],
        'ajax': {
            "url": " " + base_url + "/" + controlador + "/" + metodo + "",
            'dataSrc': ''
        },
        dom: 'Blfrtip',
        'buttons': [{
                extend: 'excelHtml5',
                autoFilter: true,
                sheetName: 'System',
                extend: 'excel',
                messageTop: "",
                title: 'MEGACOM - Lista de Registros',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'colvis',
                columnText: function(dt, idx, title) {
                    return '<i class="fa-regular fa-angle-right"></i>&nbsp;&nbsp;' + title;
                },
                postfixButtons: ['colvisRestore']
            }
        ],
        columnDefs: [
            { className: "text-start", targets: [1, 2, 3] },
            { className: "text-center", targets: [0, 4, 5] }
        ],
        "columns": [
            { "data": "created_at" },
            { "data": "nombre" },
            { "data": "email" },
            { "data": "telefono" },
            { "data": "activo" },
            { "data": "options" }
        ],
        'language': idioma_espanol

    };

}