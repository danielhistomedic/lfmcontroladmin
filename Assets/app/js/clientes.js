/*==================================================================
[ Variables ]*/
var controller = "Clientes";


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
                // var input_file = document.querySelector('.dropify-wrapper');
                // input_file.style["border"] = "1px solid #dc3545";

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
    [ Cambiar Password ]*/
    $('.show-hide-cliente').show();
    $('.show-hide span').addClass('show');

    $('.show-hide-cliente span').click(function() {
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
    $('.show-hide-cliente-confirm').show();
    $('.show-hide span').addClass('show');

    $('.show-hide-cliente-confirm span').click(function() {
        if ($(this).hasClass('show')) {
            if (document.querySelector('#password_confirm')) {
                $('#password_confirm').attr('type', 'text');
            }
            $(this).removeClass('show');
        } else {
            if (document.querySelector('#password_confirm')) {
                $('#password_confirm').attr('type', 'password');
            }
            $(this).addClass('show');
        }
    });


    $('form button[type="submit"]').on('click', function() {
        $('.show-hide-confirm span').addClass('show');
        $('.show-hide-confirm').parent().find('#password');
        $('.show-hide-confirm span').addClass('show');
        $('.show-hide-confirm').parent().find('#password_confirm');
    });




});

/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

    /*-------------------------------------------
    [ Funciones Fill Selects ]*/
    fillSelectListaPrecios();
    // fillSelectCategorias();
    // fillSelectSexo();

}, false)


/*==================================================================
[ Funciones Fill Selects e Inicializa Select2]*/

function fillSelectListaPrecios() {

    if (document.querySelector('#select_lista_precios')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML('ListaPrecios', 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#select_lista_precios').innerHTML = responseHTML;

            //Asignar Valor Default después de Inicializar Seleclt2
            $total_registros = document.getElementById('select_lista_precios').options.length;
            if ($total_registros > 1) {
                $('#select_lista_precios').val('');
                $('#select_lista_precios').trigger('change');
            } else {
                // document.getElementById('clear_combo_sucursal').style.display = 'none';
            }

        });
    }

}

//function fillSelectCategorias() {

//    if (document.querySelector('#select_categorias')) {
//        /*-------------------------------------------
//        [ Ajax LLenar Select de catalogo ]*/
//        getFunctionHTML('Categorias', 'getSelectRecords', '', function(responseHTML) {

//            document.querySelector('#select_categorias').innerHTML = responseHTML;

//            //Asignar Valor Default después de Inicializar Seleclt2
//            $total_registros = document.getElementById('select_categorias').options.length;
//            if ($total_registros > 1) {
//                $('#select_categorias').val('');
//                $('#select_categorias').trigger('change');
//            } else {
//                // document.getElementById('clear_combo_sucursal').style.display = 'none';
//            }

//        });
//    }

// }

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

    //para mantener la seleccion de la sucursal en sesión.
    let m_sucursal_id = $('#comboSucursal').val();

    let formElement = document.getElementById('formRecords');
    document.getElementById("record_id").value = '';
    resetForm(formElement, '#formRecords');

    $('#comboSucursal').val(m_sucursal_id);
    $('#comboSucursal').trigger('change');

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
    document.getElementById("usuario").value = responseObj.data.usuario;
    document.getElementById("nombre").value = responseObj.data.nombre;
    document.getElementById("paterno").value = responseObj.data.paterno;
    document.getElementById("materno").value = responseObj.data.materno;
    document.getElementById("telefono").value = responseObj.data.telefono;
    document.getElementById("email").value = responseObj.data.email;

    // Datos domicilio envio
    document.getElementById("calle").value = responseObj.data.calle;
    document.getElementById("num_exterior").value = responseObj.data.num_exterior;
    document.getElementById("num_interior").value = responseObj.data.num_interior;
    document.getElementById("colonia").value = responseObj.data.colonia;
    document.getElementById("ciudad").value = responseObj.data.ciudad;
    document.getElementById("estado").value = responseObj.data.estado;
    document.getElementById("cp").value = responseObj.data.cp;
    document.getElementById("pais").value = responseObj.data.pais;
    document.getElementById("referencias").value = responseObj.data.referencias;



    // Datos facturación
    document.getElementById("rfc").value = responseObj.data.rfc;
    document.getElementById("razon_social").value = responseObj.data.razon_social;
    document.getElementById("codigo_postal").value = responseObj.data.codigo_postal;
    document.getElementById("regimen").value = responseObj.data.regimen;
    document.getElementById("uso_cfdi").value = responseObj.data.uso_cfdi;
    document.getElementById("email_fact").value = responseObj.data.email_fact;

    /*-------------------------------------------
    [ Asigna loa valores de selects2  ]*/
    $('#select_lista_precios').val(responseObj.data.lista_precios_id);
    $('#select_lista_precios').trigger('change');

    /*-------------------------------------------
    [ Asigna imagen dropify  ]*/
    // document.getElementById('dropify_id').innerHTML = "";
    // var imagen = "Assets/files/clientes/" + responseObj.data.image;
    // document.getElementById('dropify_id').innerHTML = '<input type="file" class="dropify" id="adjunto" name="adjunto" data-default-file="' + imagen + '" data-bs-height="180" data-allowed-file-extensions="png jpg" data-max-file-size-preview="1M" />';
    // $('.dropify').dropify({
    //     messages: {
    //         'default': 'Arrastre y suelte un archivo aquí o haga clic',
    //         'replace': 'Arrastre y suelte o haga clic para reemplazar',
    //         'remove': 'Remover',
    //         'error': 'Ups, algo salió mal al añadir la imagen.'
    //     },
    //     error: {
    //         'fileSize': 'El tamaño del archivo es demasiado grande (2Mb máx.)'
    //     }
    // });

    divLoading.style.display = 'none';

}

function cargarDatos(responseObj) {

    document.getElementById("record_id").value = responseObj.dataId;

    /*-------------------------------------------
    [ Llena los datos del form view  ]*/
    document.getElementById("usuario_read").innerHTML = responseObj.data.usuario;
    document.getElementById("nombre_read").innerHTML = responseObj.data.nombre;
    document.getElementById("paterno_read").innerHTML = responseObj.data.paterno;
    document.getElementById("materno_read").innerHTML = responseObj.data.materno;
    document.getElementById("telefono_read").innerHTML = responseObj.data.telefono;
    document.getElementById("email_read").innerHTML = responseObj.data.email;
    document.getElementById("listaprecios_read").innerHTML = responseObj.data.listaprecios;

    // Datos domicilio envio
    document.getElementById("calle_read").innerHTML = responseObj.data.calle;
    document.getElementById("num_exterior_read").innerHTML = responseObj.data.num_exterior;
    document.getElementById("num_interior_read").innerHTML = responseObj.data.num_interior;
    document.getElementById("colonia_read").innerHTML = responseObj.data.colonia;
    document.getElementById("ciudad_read").innerHTML = responseObj.data.ciudad;
    document.getElementById("estado_read").innerHTML = responseObj.data.estado;
    document.getElementById("cp_read").innerHTML = responseObj.data.cp;
    document.getElementById("pais_read").innerHTML = responseObj.data.pais;
    document.getElementById("referencias_read").innerHTML = responseObj.data.pais;

    // Datos facturación
    document.getElementById("rfc_read").innerHTML = responseObj.data.rfc;
    document.getElementById("razon_social_read").innerHTML = responseObj.data.razon_social;
    document.getElementById("codigo_postal_read").innerHTML = responseObj.data.codigo_postal;
    document.getElementById("regimen_read").innerHTML = responseObj.data.regimen;
    document.getElementById("uso_cfdi_read").innerHTML = responseObj.data.uso_cfdi;
    document.getElementById("email_fact_read").innerHTML = responseObj.data.email_fact;


    // var imagen = "Assets/files/clientes/" + responseObj.data.image;
    // document.getElementById("image_read").setAttribute('src', imagen);
    // document.getElementById("image_read_lightbox").setAttribute('href', imagen);

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
            { className: "text-center", targets: [0, 1, 2, 4, 5] },
            { className: "text-start", targets: [3] },
            { className: "text-start text-wrap min-wd-400 max-wd-500", targets: [6] }
        ],
        "columns": [

            { "data": "options" },
            { "data": "activo" },
            { "data": "created_at" },
            { "data": "nombre_completo" },
            { "data": "telefono" },
            { "data": "email" },
            { "data": "domicilio_envio" }

        ],
        'language': idioma_espanol

    };

}