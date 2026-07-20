/*==================================================================
[ Variables ]*/
var controller = "Portafolio";


var table;
var tableElement = "#tableRecords";
var tableElementJS = "tableRecords";
var configTable = "";

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

                //valida droífy validate
                var input_file = document.querySelector('.dropify-wrapper');
                input_file.style["border"] = "1px solid #dc3545";

            } else {
                setRecord(event, this);
            }
        }, false);
    });


    /*==================================================================
    [ Botons de Accion ]*/

    /*-------------------------------------------
    [ Agregar evento click a Nuevo ]*/
    if (document.getElementById("btnCreate")) {
        let btnElement = document.getElementById('btnCreate');
        btnElement.onclick = function() { fntNuevo() };
    }

    /*-------------------------------------------
       [ Agregar evento click a Editar  ]*/
    if (document.getElementById("btnEditar")) {
        var btnElement = document.getElementById('btnEditar');
        btnElement.onclick = function() { fntEdit(this) };
    }

    /*-------------------------------------------
     [ Agregar evento click a Regresar a Listado  ]*/
    if (document.querySelector(".btnReturnList")) {
        let btnElement = document.querySelector('.btnReturnList');
        btnElement.onclick = function() { fntReturnList() };
    }

    /*==================================================================
     [ DataTable ]*/

    if (document.getElementById(tableElementJS)) {

        /* Inicializamos lo filtros de columna */
        var elemento_clone = tableElement + ' thead tr';
        var elemento_appendto = tableElement + ' thead';
        $(elemento_clone).clone(true).appendTo(elemento_appendto);

        var elemento_each = tableElement + ' thead tr:eq(1) th';
        $(elemento_each).each(function(i) {

            //Nombre de la columna
            var title = $(this).text();

            //Crear el elemento imput en cada columna
            $(this).html('<div class="form-group mb-0"><input type="text" style="max-height: 12px;" class="form-control form-control-sm text-center" placeholder="Filtrar ' + title + '"/></div>');

            // EVento del input creado en cada columna
            $('input', this).on('keyup change', function() {
                if (table.column(i).search() != this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        /*-------------------------------------------
        [ DataTable Inicializa ]*/
        setConfigTable(controller, 'getListaRecords');
        table = $(tableElement).DataTable(configTable);

        /*-------------------------------------------
        [ DataTable - Se ejecuta después de inicializar la tabla ]*/
        $(tableElement).on('init.dt', function() {
            validaPermisoExportar(menu);
        });

        /*-------------------------------------------
        [ DataTable - Se ejecuta después de terminar ajax ]*/
        $(tableElement).on('xhr.dt', function(e, settings, json, xhr) {});

        /*-------------------------------------------
         [ DataTable - Se ejecuta después de redibujarse la tabla ]*/
        $(tableElement).on('draw.dt', function() {});

        /*-------------------------------------------
        [ DataTable - Se ejecuta después de dar click en el primer elemento td del tr de la tabla ]*/
        $(tableElement).on('click', 'tbody tr>td', function() {});
    }


    /*==================================================================
     [ Select2 ]*/




});

/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

    /*-------------------------------------------
    [ Funciones Fill Selects ]*/
    fillSelectServicios();
    // fillSelectSexo();

}, false)


/*==================================================================
[ Funciones Fill Selects e Inicializa Select2]*/


function fillSelectServicios() {

    if (document.querySelector('#comboServicios')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML('Servicios', 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#comboServicios').innerHTML = responseHTML;

            //Asignar Valor Default 
            $total_registros = document.getElementById('comboServicios').options.length;
            if ($total_registros > 1) {
                $('#comboServicios').val('');
                $('#comboServicios').trigger('change');
            } else {
                // document.getElementById('clear_combo_sucursal').style.display = 'none';
            }

        });
    }

}

// function fillSelectSexo() {

//     if (document.querySelector('#comboSexo')) {

//         //Asignar Valor Default después de Inicializar Seleclt2
//         $total_registros = document.getElementById('comboSexo').options.length;
//         if ($total_registros > 1) {
//             $('#comboSexo').val('');
//             $('#comboSexo').trigger('change');
//         } else {
//             // document.getElementById('clear_combo_sucursal').style.display = 'none';
//         }

//     }

// }


/*==================================================================
[ Barra de Botones y Botones de Accion ]*/


function fntNuevo() {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
    document.getElementById("btnCreate").classList.add("active");
    document.querySelector(".btnReturnList").classList.remove("active");

    /*-------------------------------------------
    [ Limpiar Form ]*/

    let formElement = document.getElementById('formRecords');
    document.getElementById("record_id").value = '';
    resetForm(formElement, '#formRecords');

    $('#comboServicios').val('');
    $('#comboServicios').trigger('change');

    //reset dropify
    $('.dropify-clear').click();

    /*-------------------------------------------
    [ Asignar variables ]*/
    let list = document.getElementById('panel_lista_registros');
    let editar = document.getElementById('panel_crear_editar');
    let view = document.getElementById('panel_vista_datos');
    view.style.display = "none";
    list.style.display = "none";
    editar.style.display = "block";

    //-----------------------------------
    //[ Animación de Paneles ]

    //Elemento que dispara la animación
    let eLOrigen = document.getElementById("btnCreate");

    //Elemento que recibe la animación
    let eLDestino = editar;

    // Ejecuta Funcion de animacion mostrar el Panel de Edición de Datos
    animationButton(eLOrigen, eLDestino);

    // Establece el foco en el campo inicial de registro.
    document.getElementById("name").focus();

}

function fntReturnList() {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
    document.getElementById("btnCreate").classList.remove("active");
    document.querySelector(".btnReturnList").classList.add("active");

    /*-------------------------------------------
    [ Asignar Variables ]*/
    let list = document.getElementById('panel_lista_registros');
    let editar = document.getElementById('panel_crear_editar');
    let view = document.getElementById('panel_vista_datos');
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


/*==================================================================
[ Botones Datatable ]*/


function fntEdit(btnElement) {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
    document.getElementById("btnCreate").classList.remove("active");
    document.querySelector(".btnReturnList").classList.remove("active");

    /*-------------------------------------------
     [ Obtiene id de registro ]*/
    var idRegistro;
    if (btnElement.getAttribute("data-id")) {
        idRegistro = btnElement.getAttribute("data-id");
    } else {

        idRegistro = document.getElementById("record_id").value;
    }


    /*-------------------------------------------
    [ Loading ]*/
    divLoading.style.display = 'flex';

    /*-------------------------------------------
    [ Asignar variables ]*/
    let list = document.getElementById('panel_lista_registros');
    let editar = document.getElementById('panel_crear_editar');
    let view = document.getElementById('panel_vista_datos');

    /*-------------------------------------------
     [ Ajax ]*/
    var formData = new FormData();
    formData.append('record_id', idRegistro);
    postFunctionData(formData, controller, 'getRecord', function(responseObj) {
        if (responseObj.respuesta == "ok") {

            // --- Carga los datos recibidos ---
            cargarDatosEdit(responseObj);

            //-----------------------------------
            //[ Animación de Paneles ]
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

        /*-------------------------------------------
        [ Loading ]*/
        divLoading.style.display = 'none';

    });

}

function fntView(btnElement) {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
    document.getElementById("btnCreate").classList.remove("active");
    document.querySelector(".btnReturnList").classList.remove("active");

    /*-------------------------------------------
     [ Obtiene id de registro ]*/
    let idRegistro = btnElement.getAttribute("data-id");

    /*-------------------------------------------
     [ Loading ]*/
    divLoading.style.display = 'flex';

    /*-------------------------------------------
    [ Asignar variables ]*/
    let list = document.getElementById('panel_lista_registros');
    let editar = document.getElementById('panel_crear_editar');
    let view = document.getElementById('panel_vista_datos');

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    formData.append('record_id', idRegistro);
    postFunctionData(formData, controller, 'getRecord', function(responseObj) {

        if (responseObj.respuesta == "ok") {

            // --- Cargar datos recibidos ---
            cargarDatos(responseObj);

            //-----------------------------------
            //[ Animación de Paneles ]
            list.style.display = "none";
            editar.style.display = "none";
            view.style.display = "block";

            //Elemento que dispara la animación
            let eLOrigen = $('.panel_vista_datos');

            //Elemento que recibe la animación
            let eLDestino = view;

            // Ejecuta Funcion de animacion mostrar el Panel de Edición de Datos
            animationButton(eLOrigen, eLDestino);

        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");
        }

        /*-------------------------------------------
        [ Loading ]*/
        divLoading.style.display = 'none';

    });

}

function fntActive(btnElement) {

    /*-------------------------------------------
     [ Obtiene id de registro ]*/
    let idRegistro = btnElement.getAttribute("data-id");

    /*-------------------------------------------
      [ Loading ]*/
    divLoading.style.display = 'flex';

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    formData.append('record_id', idRegistro);
    formData.append('estatus', 1);
    postFunctionData(formData, controller, 'setEstatusRecord', function(responseObj) {
        if (responseObj.respuesta == "ok") {

            // --- Recarga datos de tabla ---
            table.ajax.reload(null, false);

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, divLoading);
        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, divLoading);
        }

    });

}

function fntDeleteRecord(btnElement) {

    /*-------------------------------------------
    [ Obtiene id de registro ]*/
    let idRegistro = btnElement.getAttribute("data-id");

    /*-------------------------------------------
      [ Loading ]*/
    divLoading.style.display = 'flex';

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    formData.append('record_id', idRegistro);
    formData.append('estatus', 0);
    postFunctionData(formData, controller, 'setEstatusRecord', function(responseObj) {
        if (responseObj.respuesta == "ok") {

            // --- Recarga datos de tabla ---
            table.ajax.reload(null, false);

            /** -- Mensaje de Alerta -- */
            alerta_success(responseObj, divLoading);
        } else {

            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, divLoading);
        }

        /*-------------------------------------------
      [ Loading ]*/
        divLoading.style.display = 'none';

    });

}



/*==================================================================
[ Carga de Datos ]*/

function cargarDatosEdit(responseObj) {

    /*-------------------------------------------
    [ Mostrar Loading en div. ]*/
    divLoading.style.display = "flex";

    /*-------------------------------------------
    [ Obtiene datos de Form  ]*/
    document.getElementById("record_id").value = responseObj.dataId;
    document.getElementById("name").value = responseObj.data.name;
    document.getElementById("descripcion").value = responseObj.data.descripcion;

    /*-------------------------------------------
    [ Asigna loa valores de selects2  ]*/
    $('#comboServicios').val(responseObj.data.servicio_id);
    $('#comboServicios').trigger('change');

    /*-------------------------------------------
    [ Asigna imagen dropify  ]*/
    document.getElementById('dropify_id').innerHTML = "";
    var imagen = "Assets/files/portafolio/" + responseObj.data.image;
    document.getElementById('dropify_id').innerHTML = '<input type="file" class="dropify" id="adjunto" name="adjunto" data-default-file="' + imagen + '" data-bs-height="180" data-allowed-file-extensions="png jpg jpeg" data-max-file-size-preview="1M" />';
    $('.dropify').dropify({
        messages: {
            'default': 'Arrastre y suelte un archivo aquí o haga clic',
            'replace': 'Arrastre y suelte o haga clic para reemplazar',
            'remove': 'Remover',
            'error': 'Ups, algo salió mal al añadir la imagen.'
        },
        error: {
            'fileSize': 'El tamaño del archivo es demasiado grande (2Mb máx.)'
        }
    });


    divLoading.style.display = 'none';

}

function cargarDatos(responseObj) {

    document.getElementById("record_id").value = responseObj.dataId;

    /*-------------------------------------------
    [ Llena los datos del form view  ]*/
    document.getElementById("nombre_read").innerHTML = responseObj.data.name;
    document.getElementById("descripcion_read").innerHTML = responseObj.data.descripcion;
    document.getElementById("servicio_id_read").innerHTML = responseObj.data.servicio_direcciona;

    var imagen = "Assets/files/portafolio/" + responseObj.data.image;
    document.getElementById("image_read").setAttribute('src', imagen);
    document.getElementById("image_read_lightbox").setAttribute('href', imagen);


    let estatus =
        responseObj.data.activo == 1 ?
        '<span class="mb-1 mt-1 me-1 btn btn-xs btn-success"><i class="fa-sharp fa-regular fa-circle-check"></i> Activo</span>' :
        '<span class="mb-1 mt-1 me-1 btn btn-xs btn-danger"><i class="fa-sharp fa-regular fa-circle-xmark"></i> Eliminado</span>';
    document.getElementById("estatus_read").innerHTML = estatus;

    document.getElementById("fechaRegistro_read").innerHTML = responseObj.data.updated_at;
    document.getElementById("usuarioRegistro_read").innerHTML = responseObj.data.usuario;


}


/*==================================================================
[ DataTable ]*/

function setConfigTable(controlador, metodo) {

    configTable = {
        orderCellsTop: true,
        fixedHeader: true,
        scrollX: "100%",
        "destroy": true,
        "select": true,
        "order": [
            [2, "desc"]
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
        buttons: [{
                extend: 'excelHtml5',
                autoFilter: true,
                sheetName: controller,
                extend: 'excel',
                messageTop: "",
                title: 'Lista de Registros',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'colvis',
                postfixButtons: ['colvisRestore']
            }
        ],
        columnDefs: [
            { className: "text-center", targets: [0, 1, 2] },
            { className: "text-start", targets: [3, 4, 5] }
        ],
        "columns": [

            { "data": "options" },
            { "data": "activo" },
            { "data": "created_at" },
            { "data": "name" },
            { "data": "descripcion" },
            { "data": "servicio_direcciona" }

        ],
        'language': idioma_espanol

    };

}