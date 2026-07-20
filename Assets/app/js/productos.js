/*==================================================================
[ Variables ]*/
var controller = "Productos";


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
     [ summernote ]*/

    if ($('#descripcion').length > 0) {

        $('#descripcion').summernote({
            lang: 'es-ES', // default: 'en-US'
            placeholder: 'Ingrese Descripión de Producto',
            tabsize: 1,
            height: 150,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                // ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['help']]
            ]
        });

    }

    /*==================================================================
     [ otras funciones ]*/
    checkOferta();


});

/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

    /*-------------------------------------------
    [ Funciones Fill Selects ]*/
    fillSelectCategorias();
    fillSelectMarcas();
    fillSelectLineasProducto();
    fillSelectUnidadesMedida();
    fillSelectProductosVentasDirigidas();
    fillSelectProductosVentasCruzadas();

    /*-------------------------------------------
    [ Otras funciones ]*/
    fillInputsReadsListaPreciosProductos();

}, false)


/*==================================================================
[ Otras funciones ]*/

function fillInputsReadsListaPreciosProductos() {

    if (document.querySelector('#lista_precios')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML('ListaPrecios', 'getInputsListaPreciosProductos', '', function(responseHTML) {
            document.querySelector('#lista_precios').innerHTML = responseHTML;

            getFunctionHTML('ListaPrecios', 'getListaPreciosProductosVista', '', function(responseHTML) {
                document.querySelector('#lista_precios_read').innerHTML = responseHTML;
            });
        });
    }
}


function checkOferta() {

    const checkbox = document.getElementById('oferta');

    checkbox.addEventListener('change', (event) => {

        var precios = document.querySelectorAll('.precios');
        if (event.currentTarget.checked) {
            document.getElementById('precio_oferta').removeAttribute('readonly');
            document.getElementById('precio_oferta').classList.remove('disabled');
            for (let index = 0; index < precios.length; index++) {
                const element = precios[index];
                element.setAttribute('readonly', '');
                element.classList.add('disabled');
            }
            document.getElementById('precio_oferta').focus();
        } else {
            document.getElementById('precio_oferta').setAttribute('readonly', '');
            document.getElementById('precio_oferta').classList.add('disabled');
            for (let index = 0; index < precios.length; index++) {
                const element = precios[index];
                element.removeAttribute('readonly');
                element.classList.remove('disabled');
            }
            document.getElementById('price_0').focus();
        }
    })
}


/*==================================================================
[ Funciones Fill Selects e Inicializa Select2]*/

function fillSelectCategorias() {

    if (document.querySelector('#select_categorias')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML('Categorias', 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#select_categorias').innerHTML = responseHTML;

            //Asignar Valor Default después de Inicializar Seleclt2
            $total_registros = document.getElementById('select_categorias').options.length;
            if ($total_registros > 1) {
                $('#select_categorias').val('');
                $('#select_categorias').trigger('change');
            } else {
                // document.getElementById('clear_combo_sucursal').style.display = 'none';
            }

        });
    }

}

function fillSelectMarcas() {

    if (document.querySelector('#select_marcas')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML('Marcas', 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#select_marcas').innerHTML = responseHTML;

            //Asignar Valor Default después de Inicializar Seleclt2
            $total_registros = document.getElementById('select_marcas').options.length;
            if ($total_registros > 1) {
                $('#select_marcas').val('');
                $('#select_marcas').trigger('change');
            } else {
                // document.getElementById('clear_combo_sucursal').style.display = 'none';
            }

        });
    }

}

function fillSelectLineasProducto() {

    if (document.querySelector('#select_lineas_producto')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML('LineasProducto', 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#select_lineas_producto').innerHTML = responseHTML;

            //Asignar Valor Default después de Inicializar Seleclt2
            $total_registros = document.getElementById('select_lineas_producto').options.length;
            if ($total_registros > 1) {
                $('#select_lineas_producto').val('');
                $('#select_lineas_producto').trigger('change');
            } else {
                // document.getElementById('clear_combo_sucursal').style.display = 'none';
            }

        });
    }

}

function fillSelectUnidadesMedida() {

    if (document.querySelector('#select_unidad_medida')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML('UnidadesMedida', 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#select_unidad_medida').innerHTML = responseHTML;

            //Asignar Valor Default después de Inicializar Seleclt2
            $total_registros = document.getElementById('select_unidad_medida').options.length;
            if ($total_registros > 1) {
                $('#select_unidad_medida').val('');
                $('#select_unidad_medida').trigger('change');
            } else {
                // document.getElementById('clear_combo_sucursal').style.display = 'none';
            }

        });
    }

}


function fillSelectProductosVentasDirigidas() {

    if (document.querySelector('#select_ventas_dirigidas')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML(controller, 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#select_ventas_dirigidas').innerHTML = responseHTML;

            //Asignar Valor Default después de Inicializar Seleclt2
            $total_registros = document.getElementById('select_ventas_dirigidas').options.length;
            if ($total_registros > 1) {
                $('#select_ventas_dirigidas').val('');
                $('#select_ventas_dirigidas').trigger('change');
            } else {
                // document.getElementById('clear_combo_sucursal').style.display = 'none';
            }

        });
    }

}

function fillSelectProductosVentasCruzadas() {

    if (document.querySelector('#select_ventas_cruzadas')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo ]*/
        getFunctionHTML(controller, 'getSelectRecords', '', function(responseHTML) {

            document.querySelector('#select_ventas_cruzadas').innerHTML = responseHTML;

            //Asignar Valor Default después de Inicializar Seleclt2
            $total_registros = document.getElementById('select_ventas_cruzadas').options.length;
            if ($total_registros > 1) {
                $('#select_ventas_cruzadas').val('');
                $('#select_ventas_cruzadas').trigger('change');
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

    //para mantener la seleccion de la sucursal en sesión.
    let m_sucursal_id = $('#comboSucursal').val();

    let formElement = document.getElementById('formRecords');
    document.getElementById("record_id").value = '';
    resetForm(formElement, '#formRecords');

    $('#comboSucursal').val(m_sucursal_id);
    $('#comboSucursal').trigger('change');


    // select2
    $('#select_categorias').val();
    $('#select_categorias').trigger('change');

    $('#select_marcas').val();
    $('#select_marcas').trigger('change');

    $('#select_lineas_producto').val();
    $('#select_lineas_producto').trigger('change');

    $('#select_categorias').val();
    $('#select_categorias').trigger('change');

    $('#select_ventas_dirigidas').val();
    $('#select_ventas_dirigidas').trigger('change');

    $('#select_ventas_cruzadas').val();
    $('#select_ventas_cruzadas').trigger('change');


    // reset dropify
    $('.dropify-clear').click();

    // reset summernote
    $("#descripcion").summernote("code", '');

    // reset precios
    let precios = document.querySelectorAll('.precios');
    for (let index = 0; index < precios.length; index++) {
        const element = precios[index];
        element.value = '0';
        element.classList.remove('disabled')
        element.removeAttribute('readonly');
    }

    const checkbox = document.getElementById('oferta');
    checkbox.checked = false;
    document.getElementById('precio_oferta').classList.add('disabled');
    document.getElementById('precio_oferta').setAttribute('readonly', '');


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

    // summernote
    // document.getElementById("descripcion").value = responseObj.data.descripcion;
    $("#descripcion").summernote("code", responseObj.data.descripcion);


    document.getElementById("alterna").value = responseObj.data.alterna;
    document.getElementById("sku").value = responseObj.data.sku;

    // document.getElementById("cantidad").value = responseObj.data.cantidad;
    // document.getElementById("limite_minimo").value = responseObj.data.limite_minimo;

    console.log(responseObj.data.existencias);
    let existencias = [];
    for (let index = 0; index < responseObj.data.existencias.length; index++) {
        const element = responseObj.data.existencias[index];
        document.getElementById("cantidad").value = element.cantidad;
        document.getElementById("limite_minimo").value = element.limite_minimo;

    }


    /*-------------------------------------------
    [ Asigna los valores de select2  ]*/
    $('#select_marcas').val(responseObj.data.marca_id);
    $('#select_marcas').trigger('change');

    $('#select_lineas_producto').val(responseObj.data.linea_producto_id);
    $('#select_lineas_producto').trigger('change');

    $('#select_unidad_medida').val(responseObj.data.unidad_medida_id);
    $('#select_unidad_medida').trigger('change');

    /*-------------------------------------------
    [ Asigna los valores de select2 multiselect  ]*/

    let categorias_select = [];
    for (let index = 0; index < responseObj.data.categorias.length; index++) {
        const element = responseObj.data.categorias[index];
        categorias_select.push(element.categoria_id);
    }
    $('#select_categorias').val(categorias_select);
    $('#select_categorias').trigger('change');


    let ventas_dirigidas_select = [];
    for (let index = 0; index < responseObj.data.ventas_dirigidas.length; index++) {
        const element = responseObj.data.ventas_dirigidas[index];
        ventas_dirigidas_select.push(element.producto_recomendado_id);
    }
    $('#select_ventas_dirigidas').val(ventas_dirigidas_select);
    $('#select_ventas_dirigidas').trigger('change');


    let ventas_cruzadas_select = [];
    for (let index = 0; index < responseObj.data.ventas_cruzadas.length; index++) {
        const element = responseObj.data.ventas_cruzadas[index];
        ventas_cruzadas_select.push(element.producto_promociona_id);
    }
    $('#select_ventas_cruzadas').val(ventas_cruzadas_select);
    $('#select_ventas_cruzadas').trigger('change');


    // reset precios
    let precios_reset = document.querySelectorAll('.precios');
    for (let index = 0; index < precios_reset.length; index++) {
        const element = precios_reset[index];
        element.value = '0';
        element.classList.remove('disabled')
        element.removeAttribute('readonly');
    }

    const checkbox = document.getElementById('oferta');
    checkbox.checked = false;
    document.getElementById('precio_oferta').classList.add('disabled');
    document.getElementById('precio_oferta').setAttribute('readonly', '');


    //Rating
    document.getElementById("rate").value = responseObj.data.rate;

    const checkbox_reco_mes = document.getElementById('recomendaciones_mes');
    checkbox_reco_mes.checked = false;
    let recomendaciones_mes = responseObj.data.recomendaciones_mes;
    if (recomendaciones_mes == 1) {
        document.getElementById("recomendaciones_mes").checked = true;
    }


    /*-------------------------------------------
    [ Asigna los valores de precios  ]*/
    let precios_view = document.querySelectorAll('.precios');
    let precios = responseObj.data.precios;

    for (let index = 0; index < precios.length; index++) {
        const element = precios[index];
        let precio = element.precio;
        for (let ix_v = 0; ix_v < precios_view.length; ix_v++) {
            const el_lp_id = precios_view[ix_v];
            // el_lp_id.value = 0;
            let lp_id = el_lp_id.getAttribute("data-id");
            if (lp_id == element.lista_precios_id) {
                el_lp_id.value = precio;
            }
        }
    }

    /*-------------------------------------------
         [ Asigna los valores de precios de oferta en caso de existir  ]*/
    let oferta = responseObj.data.oferta;
    if (oferta == 1) {

        // deshabilita la lista de precios
        for (let index = 0; index < precios_view.length; index++) {
            const element = precios_view[index];
            element.classList.add('disabled')
            element.setAttribute('readonly', '');
        }

        document.getElementById("oferta").checked = true;
        document.getElementById("precio_oferta").value = responseObj.data.precio_oferta;
        document.getElementById("precio_oferta").classList.remove('disabled');
        document.getElementById("precio_oferta").removeAttribute('readonly');
    } else {

        // habilita la lista de precios
        for (let index = 0; index < precios_view.length; index++) {
            const element = precios_view[index];
            element.classList.remove('disabled')
            element.removeAttribute('readonly');
        }
        document.getElementById("oferta").checked = false;
        document.getElementById("precio_oferta").value = 0;
        document.getElementById("precio_oferta").classList.add('disabled');
        document.getElementById("precio_oferta").setAttribute('readonly', '');
    }


    // Imagenes

    /*-------------------------------------------
    [ Limpiar los datos de los html ]*/
    for (let index = 0; index < 4; index++) {

        var element_hidden_slug = 'adjunto_hidden_' + (index + 1);
        var element_input_hidden = document.getElementById(element_hidden_slug);
        element_input_hidden.value = "";

        var element_hidden_id = 'adjunto_hidden_' + (index + 1) + '_id';
        var element_input_hidden_id = document.getElementById(element_hidden_id);
        element_input_hidden_id.value = "";

    }

    for (let index = 0; index < responseObj.data.imagenes.length; index++) {
        const element = responseObj.data.imagenes[index];
        /*-------------------------------------------
        [ Asigna imagen dropify  ]*/
        if (element.imagen != '') {

            var element_hidden_slug = 'adjunto_hidden_' + (index + 1);
            var element_input_hidden = document.getElementById(element_hidden_slug);
            element_input_hidden.value = element.slug;

            var element_hidden_id = 'adjunto_hidden_' + (index + 1) + '_id';
            var element_input_hidden_id = document.getElementById(element_hidden_id);
            element_input_hidden_id.value = element.id;

            var element_id = 'dropify_id_' + (index + 1);
            document.getElementById(element_id).innerHTML = "";
            var image = "Assets/files/productos/" + element.imagen;
            console.log(image);
            document.getElementById(element_id).innerHTML = '<input type="file" class="dropify" id="adjunto1" name="adjunto[]" data-default-file="' + image + '" data-bs-height="180" data-allowed-file-extensions="png jpg" data-max-file-size-preview="1M" />';
        }
    }

    var drEvent = $('.dropify').dropify({
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

    drEvent.on('dropify.beforeClear', function(event, element) {

        var elemento1 = element.element;
        var elemento2 = elemento1.parentNode;
        var elemento3 = elemento2.parentNode;
        var elemento4 = elemento3.parentNode;
        var valor = elemento4.getAttribute('data-input');
        if (valor == 1) {
            document.getElementById('adjunto_hidden_1').value = '';
        }
        if (valor == 2) {
            document.getElementById('adjunto_hidden_2').value = '';
        }
        if (valor == 3) {
            document.getElementById('adjunto_hidden_3').value = '';
        }
        if (valor == 4) {
            document.getElementById('adjunto_hidden_4').value = '';
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
    document.getElementById("alterna_read").innerHTML = responseObj.data.alterna;
    document.getElementById("sku_read").innerHTML = responseObj.data.sku;

    document.getElementById("marca_read").innerHTML = responseObj.data.marca;
    document.getElementById("linea_producto_read").innerHTML = responseObj.data.linea_producto;
    document.getElementById("unidad_medida_read").innerHTML = responseObj.data.unidad_medida;

    document.getElementById("cantidad_read").innerHTML = responseObj.data.cantidad;
    document.getElementById("limite_minimo_read").innerHTML = responseObj.data.limite_minimo;

    // Categorias
    let categorias = '';
    for (let index = 0; index < responseObj.data.categorias.length; index++) {
        const element = responseObj.data.categorias[index];
        if (index == 0) {
            categorias = element.categoria;
        } else {
            categorias = categorias + ', ' + element.categoria;
        }
    }
    document.getElementById("categorias_read").innerHTML = categorias;


    /*-------------------------------------------
     [ Asigna los valores de precios  ]*/
    let precios_view = document.querySelectorAll('.precios_read');
    let precios = responseObj.data.precios;
    console.log(precios);

    for (let index = 0; index < precios.length; index++) {
        const element = precios[index];
        let precio = element.precio;
        for (let ix_v = 0; ix_v < precios_view.length; ix_v++) {
            const el_lp_id = precios_view[ix_v];
            let lp_id = el_lp_id.getAttribute("data-id");
            if (lp_id == element.lista_precios_id) {
                el_lp_id.innerHTML = Formato_Moneda(precio);
            }
        }
    }

    //rate
    document.getElementById("rate_read").innerHTML = responseObj.data.rate;
    let recomendaciones_mes = responseObj.data.recomendaciones_mes;
    if (recomendaciones_mes == 1) {
        document.getElementById("recomendaciones_mes_read").innerHTML = 'SI';
    } else {
        document.getElementById("recomendaciones_mes_read").innerHTML = 'NO';
    }


    /*-------------------------------------------
     [ Asigna los valores de precios de oferta en caso de existir  ]*/
    let oferta = responseObj.data.oferta;
    if (oferta == 1) {
        document.getElementById("oferta_read").innerHTML = 'SI';
        document.getElementById("precio_oferta_read").innerHTML = responseObj.data.precio_oferta;
    } else {
        document.getElementById("oferta_read").innerHTML = 'NO';
        document.getElementById("precio_oferta_read").innerHTML = '$0.00';
    }


    // Imagenes
    for (let index = 0; index < 4; index++) {
        var element_read_id = 'image' + (index + 1) + '_read';
        var element_read_lightbox_id = 'image' + (index + 1) + '_read_lightbox';
        document.getElementById(element_read_id).setAttribute('src', '');
        document.getElementById(element_read_lightbox_id).setAttribute('href', '');
    }
    for (let index = 0; index < responseObj.data.imagenes.length; index++) {
        const element = responseObj.data.imagenes[index];
        if (element.imagen != '') {
            var imagen = "Assets/files/productos/" + element.imagen;
            var element_read_id = 'image' + (index + 1) + '_read';
            var element_read_lightbox_id = 'image' + (index + 1) + '_read_lightbox';
            document.getElementById(element_read_id).setAttribute('src', imagen);
            document.getElementById(element_read_lightbox_id).setAttribute('href', imagen);
        }
    }

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
            { className: "text-center", targets: [0, 1, 2, 3, 6, 7, 8, 9, 12, 14, 15, 16] },
            { className: "text-start text-wrap", targets: [4] },
            { className: "text-start text-wrap min-wd-400 max-wd-500", targets: [5] },
            { className: "text-center text-wrap min-wd-260 max-wd-350", targets: [10, 11] },
            { className: "text-end", targets: [13] }
        ],
        "columns": [

            { "data": "options" },
            { "data": "activo" },
            { "data": "created_at" },
            { "data": "sku" },
            { "data": "name" },
            { "data": "descripcion" },
            { "data": "alterna" },
            { "data": "marca" },
            { "data": "linea_producto" },
            { "data": "unidad_medida" },
            { "data": "categorias" },
            { "data": "lista_precios" },
            { "data": "oferta" },
            { "data": "precio_oferta" },
            { "data": "existencias" },
            { "data": "limite_minimo" },
            { "data": "rate" }
        ],
        'language': idioma_espanol

    };

}

{
    /* <th class="border-bottom-0 fw-semibold text-center">Oferta</th>
    <th class="border-bottom-0 fw-semibold text-center">Precio Oferta</th>
    <th class="border-bottom-0 fw-semibold text-center">Existencias</th>
    <th class="border-bottom-0 fw-semibold text-center">Limite Minimo</th> */
}

{
    /* <th class="border-bottom-0 fw-semibold text-center">Opciones</th>
    <th class="border-bottom-0 fw-semibold text-center">Estatus</th>
    <th class="border-bottom-0 fw-semibold text-center">Fecha Registro</th>
    <th class="border-bottom-0 fw-semibold text-center">SKU</th>
    <th class="border-bottom-0 fw-semibold text-center">Nombre</th>
    <th class="border-bottom-0 fw-semibold text-center">Descripcion</th>
    <th class="border-bottom-0 fw-semibold text-center">Clave Alterna</th>
    <th class="border-bottom-0 fw-semibold text-center">Marca</th>
    <th class="border-bottom-0 fw-semibold text-center">Linea de Producto</th>
    <th class="border-bottom-0 fw-semibold text-center">Categorias</th>
    <th class="border-bottom-0 fw-semibold text-center">Unidad Medida</th>
    <th class="border-bottom-0 fw-semibold text-center">Lista Precios</th> */
}