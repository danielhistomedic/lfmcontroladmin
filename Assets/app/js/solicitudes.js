/*==================================================================
[ Variables ]*/
var controller = "Solicitudes";


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
            } else {
                setRecord(event, this);
            }
        }, false);
    });


    /*==================================================================
    [ Botons de Accion ]*/

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
        $(tableElement).on('draw.dt', function() {
            table.columns.adjust();
        });

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
    // fillSelectRoles();
    fillSelectAtendida();

}, false)

$(window).resize(function() {
    if ($.fn.DataTable.isDataTable(table)) {
        table.columns.adjust();
    }
});


/*==================================================================
[ Funciones Fill Selects e Inicializa Select2]*/

function fillSelectAtendida() {

    if (document.querySelector('#comboAtendida')) {

        //Asignar Valor Default después de Inicializar Seleclt2
        $total_registros = document.getElementById('comboAtendida').options.length;
        if ($total_registros > 1) {
            $('#comboAtendida').val('');
            $('#comboAtendida').trigger('change');
        } else {
            // document.getElementById('clear_combo_sucursal').style.display = 'none';
        }

    }

}


/*==================================================================
[ Barra de Botones y Botones de Accion ]*/


function fntReturnList() {

    /*-------------------------------------------
    [ Activar/Desactivar ]*/
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

function fntAtendida(btnElement) {

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
    formData.append('atendida', 1);
    postFunctionData(formData, controller, 'setAtendida', function(responseObj) {
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
    document.getElementById("record_id").value = responseObj.data.id;
    document.getElementById("sucursal").value = responseObj.data.sucursal;
    document.getElementById("nombre").value = responseObj.data.nombre;
    document.getElementById("telefono").value = responseObj.data.telefono;
    document.getElementById("email").value = responseObj.data.email;
    // document.getElementById("adjunto_identificacion").value = responseObj.data.adjunto_identificacion;
    // document.getElementById("adjunto_aviso_funcionamiento").value = responseObj.data.adjunto_aviso_funcionamiento;
    // document.getElementById("adjunto_cofepris").value = responseObj.data.adjunto_cofepris;
    // document.getElementById("adjunto_csf").value = responseObj.data.adjunto_csf;
    // document.getElementById("adjunto_comprobante_domicilio").value = responseObj.data.adjunto_comprobante_domicilio;
    document.getElementById("comentarios").value = responseObj.data.comentarios;

    /*-------------------------------------------
    [ Asigna loa valores de selects2  ]*/
    $('#comboAtendida').val(responseObj.data.atendida);
    $('#comboAtendida').trigger('change');

    divLoading.style.display = 'none';

}

function cargarDatos(responseObj) {

    /*-------------------------------------------
    [ Asigna valor del registro a editar  ]*/
    document.getElementById("record_id").value = responseObj.data.id;

    /*-------------------------------------------
    [ Llena los datos del form view  ]*/
    document.getElementById("sucursal_read").innerHTML = responseObj.data.sucursal;
    document.getElementById("nombre_read").innerHTML = responseObj.data.nombre;
    document.getElementById("telefono_read").innerHTML = responseObj.data.telefono;
    document.getElementById("email_read").innerHTML = responseObj.data.email;
    document.getElementById("adjunto_identificacion_read").innerHTML = responseObj.data.adjunto_identificacion;
    document.getElementById("adjunto_identificacion_read").setAttribute('href', base_url_sitio + '/Assets/files/clientes/' + responseObj.data.adjunto_identificacion);

    // document.getElementById("adjunto_aviso_funcionamiento_read").innerHTML = responseObj.data.adjunto_aviso_funcionamiento;
    // document.getElementById("adjunto_aviso_funcionamiento_read").setAttribute('href', base_url_sitio + '/Assets/files/clientes/' + responseObj.data.adjunto_aviso_funcionamiento);

    document.getElementById("adjunto_cofepris_read").innerHTML = responseObj.data.adjunto_cofepris;
    document.getElementById("adjunto_cofepris_read").setAttribute('href', base_url_sitio + '/Assets/files/clientes/' + responseObj.data.adjunto_cofepris);

    document.getElementById("adjunto_csf_read").innerHTML = responseObj.data.adjunto_csf;
    document.getElementById("adjunto_csf_read").setAttribute('href', base_url_sitio + '/Assets/files/clientes/' + responseObj.data.adjunto_csf);

    document.getElementById("adjunto_comprobante_domicilio_read").innerHTML = responseObj.data.adjunto_comprobante_domicilio;
    document.getElementById("adjunto_comprobante_domicilio_read").setAttribute('href', base_url_sitio + '/Assets/files/clientes/' + responseObj.data.adjunto_comprobante_domicilio);

    let atendida =
        responseObj.data.atendida == 0 ? 'NO' : 'SI';
    document.getElementById("atendida_read").innerHTML = atendida;

    document.getElementById("usuario_atendio_read").innerHTML = responseObj.data.usuario_atendio;
    document.getElementById("comentarios_read").innerHTML = responseObj.data.comentarios;

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
        "order": [],
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
            { className: "text-center", targets: [0, 1, 2, 8] },
            { className: "text-start", targets: [3, 4, 5, 6, 7, 9] },
            { className: "text-wrap min-wd-300 max-wd-300", targets: [10] }
        ],
        "columns": [
            { "data": "options" },
            { "data": "activo" },
            { "data": "created_at" },
            { "data": "sucursal" },
            { "data": "nombre" },
            { "data": "telefono" },
            { "data": "email" },
            { "data": "atendida" },
            { "data": "updated_atendio" },
            { "data": "usuario_atendio" },
            { "data": "comentarios" }
        ],
        'language': idioma_espanol

    };

}

{
    /* <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
    <th class="border-bottom-0 fw-semibold text-center">Estatus</th>
    <th class="border-bottom-0 fw-semibold text-center">Fecha Registro</th>
    <th class="border-bottom-0 fw-semibold text-center">Sucursal</th>
    <th class="border-bottom-0 fw-semibold text-center">Nombre</th>
    <th class="border-bottom-0 fw-semibold text-center">Telefono</th>
    <th class="border-bottom-0 fw-semibold text-center">Email</th>

    <th class="border-bottom-0 fw-semibold text-center">Identificacion</th>
    <th class="border-bottom-0 fw-semibold text-center">Aviso de Funcionamiento</th>
    <th class="border-bottom-0 fw-semibold text-center">Aviso de Cofepris</th>
    <th class="border-bottom-0 fw-semibold text-center">Constancia de Situación Fiscal</th>
    <th class="border-bottom-0 fw-semibold text-center">Comprobante de Domicilio</th>

    <th class="border-bottom-0 fw-semibold text-center">Atendida</th>
    <th class="border-bottom-0 fw-semibold text-center">Atendida Fecha</th>
    <th class="border-bottom-0 fw-semibold text-center">Atendida Usuario</th>
    <th class="border-bottom-0 fw-semibold text-center">Atendida Comentarios</th> */
}


// `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
// `empresa_id` int(10) unsigned NOT NULL,
// `sucursal_id` int(10) unsigned NOT NULL,
// `nombre` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Nombre o Razon Social',
// `telefono` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Telefono',
// `email` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Email',
// `adjunto_identificacion` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'INE, pasaporte, etc.',
// `adjunto_aviso_funcionamiento` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Aviso de Funcionamiento',
// `adjunto_cofepris` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Permiso Cofepris',
// `adjunto_csf` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL COMMENT 'Constancia de situacion Fiscal',
// `adjunto_comprobante_domicilio` varchar(45) COLLATE utf8mb4_unicode_520_ci NOT NULL,
// `activo` int(10) unsigned NOT NULL COMMENT '0 = inactivo, 1 = activo',
// `created_at` datetime NOT NULL COMMENT 'Fecha de creación del registro',
// `usuario_id_created` int(10) unsigned NOT NULL COMMENT 'Usuario que creó el registro originalmente',
// `updated_at` datetime DEFAULT NULL COMMENT 'Fecha de actualización o modificación del registro',
// `usuario_id_updated` int(10) unsigned DEFAULT NULL COMMENT 'Usuario que actualizó o modificó el registro',
// `atendida` int(10) unsigned DEFAULT '0',
// `updated_atendio` datetime DEFAULT NULL,
// `usuario_id_atendio` int(10) unsigned DEFAULT NULL,
// `comentarios` varchar(1000) C