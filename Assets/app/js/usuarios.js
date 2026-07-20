/*==================================================================
[ Variables ]*/
var controller = "Usuarios";


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
        $(tableElement).on('draw.dt', function() {
            table.columns.adjust();
        });

        /*-------------------------------------------
        [ DataTable - Se ejecuta después de dar click en el primer elemento td del tr de la tabla ]*/
        $(tableElement).on('click', 'tbody tr>td', function() {});
    }

    /*==================================================================
    [ Password ]*/
    $('.show-hide').show();
    $('.show-hide span').addClass('show');

    $('.show-hide span').click(function() {
        if ($(this).hasClass('show')) {
            if (document.querySelector('input[name="password"]')) {
                $('input[name="password"]').attr('type', 'text');
            }
            $(this).removeClass('show');
        } else {
            if (document.querySelector('input[name="password"]')) {
                $('input[name="password"]').attr('type', 'password');
            }
            $(this).addClass('show');
        }
    });

    /*==================================================================
    [ Confirmar ]*/

    $('.show-hide-confirm').show();
    $('.show-hide-confirm span').addClass('show');

    $('.show-hide-confirm span').click(function() {
        if ($(this).hasClass('show')) {
            if (document.querySelector('input[name="password_confirm"]')) {
                $('input[name="password_confirm"]').attr('type', 'text');
            }
            $(this).removeClass('show');
        } else {
            if (document.querySelector('input[name="password_confirm"]')) {
                $('input[name="password_confirm"]').attr('type', 'password');
            }
            $(this).addClass('show');
        }
    });


    $('form button[type="submit"]').on('click', function() {
        $('.show-hide span').addClass('show');
        $('.show-hide').parent().find('input[name="password"]').attr('type', 'password');
        $('.show-hide-confirm span').addClass('show');
        $('.show-hide-confirm').parent().find('input[name="password_confirm"]').attr('type', 'password');
    });


    /*==================================================================
     [ Select2 ]*/

    // $('#comboSucursal').on('select2:select', function(e) {
    //     var data = e.params.data;
    //     fillSelectPuestos(data.id);
    // });


});


/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

    /*-------------------------------------------
    [ Funciones Fill Selects ]*/
    fillSelectRoles();
    // fillSelectSexo();
    // fillSelectSucursales();

}, false)

$(window).resize(function() {
    if ($.fn.DataTable.isDataTable(table)) {
        table.columns.adjust();
    }
});


/*==================================================================
[ Funciones Fill Selects e Inicializa Select2]*/

function fillSelectRoles() {

    if (document.querySelector('#comboRoles')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo de roles ]*/
        getFunctionHTML('Roles', 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#comboRoles').innerHTML = responseHTML;

            //Asignar Valor Default después de Inicializar Seleclt2
            $total_registros = document.getElementById('comboRoles').options.length;
            if ($total_registros > 1) {
                $('#comboRoles').val('');
                $('#comboRoles').trigger('change');
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

// function fillSelectSucursales() {

//     if (document.querySelector('#comboSucursal')) {
//         /*-------------------------------------------
//         [ Ajax LLenar Select de catalogo de roles ]*/
//         getFunctionHTML('Sucursales', 'getAllSelect', '', function(responseHTML) {

//             document.querySelector('#comboSucursal').innerHTML = responseHTML;

//             //Asignar Valor Default después de Inicializar Seleclt2
//             $total_registros = document.getElementById('comboSucursal').options.length;
//             if ($total_registros > 1) {
//                 $('#comboSucursal').val('');
//                 $('#comboSucursal').trigger('change');
//             } else {
//                 // document.getElementById('clear_combo_sucursal').style.display = 'none';
//             }

//         });
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
    // let m_sucursal_id = $('#comboSucursal').val();

    let formElement = document.getElementById('formRecords');
    document.getElementById("record_id").value = '';
    resetForm(formElement, '#formRecords');

    // $('#comboSucursal').val(m_sucursal_id);
    // $('#comboSucursal').trigger('change');

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
    document.getElementById("usuario").focus();

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
    [ Valida que las contraseñas coincidan ]*/
    let inputPassword = document.getElementById('password').value;
    let inputConfirmPassword = document.getElementById('password_confirm').value;

    if (inputPassword != inputConfirmPassword) {

        var responseObj = {
            mostrar_mensaje: true,
            tiempo: 3000,
            mensaje: 'Las Contraseñas No Coinciden.'
        }
        alerta_error(responseObj, divLoading)
        return;

    }

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

    /*===========================================
    [ Obtiene datos de Form  ]*/
    document.getElementById("record_id").value = responseObj.data.id;

    document.getElementById("usuario").value = responseObj.data.usuario;
    document.getElementById("nombre").value = responseObj.data.nombre;
    document.getElementById("paterno").value = responseObj.data.paterno;
    document.getElementById("materno").value = responseObj.data.materno;
    document.getElementById("telefono").value = responseObj.data.telefono;
    document.getElementById("email").value = responseObj.data.email;


    /*-------------------------------------------
    [ Asigna loa valores de selects2  ]*/
    // $('#comboSucursal').val(responseObj.data.sucursal_id);
    // $('#comboSucursal').trigger('change');

    // $('#comboSexo').val(responseObj.data.sexo_id);
    // $('#comboSexo').trigger('change');

    $('#comboRoles').val(responseObj.data.rol_id);
    $('#comboRoles').trigger('change');

    document.getElementById("password").value = "";
    document.getElementById("password_confirm").value = "";

    divLoading.style.display = 'none';

}

function cargarDatos(responseObj) {

    /*-------------------------------------------
    [ Valida boton de edición de datos  ]*/
    if (responseObj.data.activo == 0) {
        document.getElementById("btnEditar").classList.add("d-none");
    } else {
        document.getElementById("btnEditar").classList.remove("d-none");
    }

    /*-------------------------------------------
    [ Asigna valor del registro a editar  ]*/
    document.getElementById("record_id").value = responseObj.data.id;

    /*-------------------------------------------
    [ Llena lso datos del form view  ]*/
    let nombre = "";
    nombre = responseObj.data.nombre + ' ' + responseObj.data.paterno + ' ' + responseObj.data.materno;
    document.getElementById("nombre_read").innerHTML = nombre;
    // document.getElementById("sucursal_read").innerHTML = responseObj.data.sucursal;

    // document.getElementById("sexo_read").innerHTML = responseObj.data.sexo;
    document.getElementById("email_read").innerHTML = responseObj.data.email;
    document.getElementById("telefono_read").innerHTML = responseObj.data.telefono;

    document.getElementById("rol_read").innerHTML = responseObj.data.rol;

    let estatus =
        responseObj.data.activo == 1 ?
        '<span class="mb-1 mt-1 me-1 btn btn-xs btn-success"><i class="fa-sharp fa-regular fa-circle-check"></i> Activo</span>' :
        '<span class="mb-1 mt-1 me-1 btn btn-xs btn-danger"><i class="fa-sharp fa-regular fa-circle-xmark"></i> Eliminado</span>';
    document.getElementById("estatus_read").innerHTML = estatus;

    document.getElementById("fechaRegistro_read").innerHTML = responseObj.data.updated_at;
    document.getElementById("usuarioRegistro_read").innerHTML = responseObj.data.usuario_register;

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
            // { className: "text-center", targets: [0, 2] },
            // { className: "text-center", targets: [5, 6, 7, 8] }
        ],
        "columns": [
            { "data": "options" },
            { "data": "activo" },
            { "data": "created_at" },
            // { "data": "sucursal" },
            { "data": "usuario" },
            { "data": "nombre" },
            { "data": "email" },
            { "data": "telefono" },
            { "data": "rol" }
        ],
        language: idioma_espanol

    };

}