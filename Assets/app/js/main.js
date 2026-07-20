/*==================================================================
[ Developer ]*/

// Capturamos el click y lo pasamos a una funcion
// document.onclick = captura_click;

// function captura_click(e) {
//     // Funcion para capturar el click del raton
//     var HaHechoClick;
//     HaHechoClick = e.target;
//     console.log(HaHechoClick);
// }



/*==================================================================
[ Constantes ]*/

/* -- Configuración de Idioma español para Datatable Versión Completa. -- */
const idioma_espanol_complete = {
    "processing": "Procesando...",
    "lengthMenu": "Mostrar _MENU_ registros",
    "zeroRecords": "No se encontraron resultados",
    "emptyTable": "No se encontraron resultados",
    "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
    "search": "Buscar:",
    "infoThousands": ",",
    "loadingRecords": "Cargando...",
    "paginate": {
        "first": "Primero",
        "last": "Último",
        "next": "Siguiente",
        "previous": "Anterior"
    },
    "aria": {
        "sortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sortDescending": ": Activar para ordenar la columna de manera descendente"
    },
    "buttons": {
        "copy": "Copiar",
        "colvis": "Visibilidad",
        "collection": "Colección",
        "colvisRestore": "Restaurar visibilidad",
        "copyKeys": "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br \/> <br \/> Para cancelar, haga clic en este mensaje o presione escape.",
        "copySuccess": {
            "1": "Copiada 1 fila al portapapeles",
            "_": "Copiadas %d fila al portapapeles"
        },
        "copyTitle": "Copiar al portapapeles",
        "csv": "CSV",
        "excel": "Excel",
        "pageLength": {
            "-1": "Mostrar todas las filas",
            "1": "Mostrar 1 fila",
            "_": "Mostrar %d filas"
        },
        "pdf": "PDF",
        "print": "Imprimir"
    },
    "autoFill": {
        "cancel": "Cancelar",
        "fill": "Rellene todas las celdas con <i>%d<\/i>",
        "fillHorizontal": "Rellenar celdas horizontalmente",
        "fillVertical": "Rellenar celdas verticalmentemente"
    },
    "decimal": ",",
    "searchBuilder": {
        "add": "Añadir condición",
        "button": {
            "0": "Constructor de búsqueda",
            "_": "Constructor de búsqueda (%d)"
        },
        "clearAll": "Borrar todo",
        "condition": "Condición",
        "conditions": {
            "date": {
                "after": "Despues",
                "before": "Antes",
                "between": "Entre",
                "empty": "Vacío",
                "equals": "Igual a",
                "not": "No",
                "notBetween": "No entre",
                "notEmpty": "No Vacio"
            },
            "moment": {
                "after": "Despues",
                "before": "Antes",
                "between": "Entre",
                "empty": "Vacío",
                "equals": "Igual a",
                "not": "No",
                "notBetween": "No entre",
                "notEmpty": "No vacio"
            },
            "number": {
                "between": "Entre",
                "empty": "Vacio",
                "equals": "Igual a",
                "gt": "Mayor a",
                "gte": "Mayor o igual a",
                "lt": "Menor que",
                "lte": "Menor o igual que",
                "not": "No",
                "notBetween": "No entre",
                "notEmpty": "No vacío"
            },
            "string": {
                "contains": "Contiene",
                "empty": "Vacío",
                "endsWith": "Termina en",
                "equals": "Igual a",
                "not": "No",
                "notEmpty": "No Vacio",
                "startsWith": "Empieza con"
            }
        },
        "data": "Data",
        "deleteTitle": "Eliminar regla de filtrado",
        "leftTitle": "Criterios anulados",
        "logicAnd": "Y",
        "logicOr": "O",
        "rightTitle": "Criterios de sangría",
        "title": {
            "0": "Constructor de búsqueda",
            "_": "Constructor de búsqueda (%d)"
        },
        "value": "Valor"
    },
    "searchPanes": {
        "clearMessage": "Borrar todo",
        "collapse": {
            "0": "Paneles de búsqueda",
            "_": "Paneles de búsqueda (%d)"
        },
        "count": "{total}",
        "countFiltered": "{shown} ({total})",
        "emptyPanes": "Sin paneles de búsqueda",
        "loadMessage": "Cargando paneles de búsqueda",
        "title": "Filtros Activos - %d"
    },
    "select": {
        "1": "%d fila seleccionada",
        "_": "%d filas seleccionadas",
        "cells": {
            "1": "1 celda seleccionada",
            "_": "$d celdas seleccionadas"
        },
        "columns": {
            "1": "1 columna seleccionada",
            "_": "%d columnas seleccionadas"
        }
    },
    "thousands": "."
}

/* -- Configuración de Idioma español para Datatable. -- */
const idioma_espanol = {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "No se encontraron resultados",
    "sInfo": "Registros del _START_ al _END_ de un total de _TOTAL_ registros",
    "sInfoEmpty": "No se encontraron resultados",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
    "sInfoPostFix": "",
    "searchPlaceholder": "Buscar...",
    "sSearch": "",
    "sUrl": "",
    "sInfoThousands": ",",
    "sLoadingRecords": "Cargando...",
    "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
    },
    "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    },
    "buttons": {
        "copy": "Copiar",
        "excel": "<i class='fas fa-file-excel'></i>&nbsp;&nbsp;Excel",
        "colvis": "<i class='fas fa-columns'></i>&nbsp;&nbsp;Columnas",
        "colvisRestore": "<i class='far fa-window-restore'></i>&nbsp;&nbsp;Restablecer Vista de Columnas&nbsp;&nbsp;&nbsp;"
    },
    'select': {
        'rows': {
            _: '[%d filas seleccionadas]',
            0: '[Ninguna linea seleccionada]',
            1: '[1 fila seleccionada]'
        }
    },
    "decimal": ".",
    "thousands": ","

};

/*==================================================================
[ DOMContentLoaded ]*/

document.addEventListener("DOMContentLoaded", function (event) {


    /*-------------------------------------------
    [ Form - Autocomplete ]*/

    // Menus
    if (document.querySelector('.search_menu')) {

        var menus = document.querySelectorAll('.search_menu');

        for (let m = 0; m < menus.length; m++) {
            var element = menus[m];
            $(element).autocomplete({
                source: function (request, response) {
                    get_menus(request.term, function (result) {
                        response(result);
                    });
                },
                minLength: 3,
                select: function (event, ui) {
                    window.location.href = ui.item.url;
                }
            }).data('ui-autocomplete')._renderItem = function (ul, item) {

                return $('<li class="ui-automplete-row"></li>')
                    .data('item.autocomplete', item)
                    .append(item.label)
                    .appendTo(ul);
            }
        }


    }

    /*==================================================================
    [ Theme ]*/

    /*-------------------------------------------
    [ Agregar evento click para Cambiar de Tema Light o Dark ]*/
    $(".mode").on("click", function () {

        $('.mode i').toggleClass("fa-moon").toggleClass("fa-lightbulb-on");
        $('body').toggleClass("dark-only");

        let theme = "";
        theme = document.querySelector('body').getAttribute('class');
        let formData = new FormData();
        formData.append('theme', theme);
        postFunctionData(formData, 'Usuarios', 'setTheme', function (responseObj) { });

    });


    /*==================================================================
    [ Mostrar Mas Form ]*/

    /*-------------------------------------------
    [ Agregar evento click para Mostrar Mas/Mostrar Menos ]*/
    if (document.querySelector(".mostrar_mas_menos")) {
        let more_less = document.querySelector(".mostrar_mas_menos");
        let mostrar_mas = document.getElementById("mostrar_mas");
        let mostrar_menos = document.getElementById("mostrar_menos");
        more_less.addEventListener("click", function () {
            mostrar_mas.classList.toggle("d-none");
            mostrar_menos.classList.toggle("d-none");
        });
    }

    /*-------------------------------------------
    [ Theme ]*/
    $('#light_mode').on('click', function () {

        document.querySelector('body').classList.remove('dark-mode');
        document.querySelector('body').classList.remove('transparent-mode');
        document.querySelector('body').classList.remove('bg-img1');
        document.querySelector('body').classList.remove('bg-img2');
        document.querySelector('body').classList.remove('bg-img3');
        document.querySelector('body').classList.remove('bg-img4');
        $('body').addClass('gradient-header');
        // $('body').addClass('gradient-menu');
        document.getElementById('light_mode').classList.toggle('d-none');
        document.getElementById('transparent_mode').classList.toggle('d-none');

        // Establece el tema del usuario;
        let formData = new FormData();
        formData.append('theme', 'light-mode');
        postFunctionData(formData, 'Usuarios', 'setTheme', function (responseObj) { });

    })

    $('#transparent_mode').on('click', function () {

        document.querySelector('body').classList.add('transparent-mode');
        document.querySelector('body').classList.add('bg-img2');
        document.querySelector('body').classList.remove('dark-mode');
        document.querySelector('body').classList.remove('light-mode');
        document.querySelector('body').classList.remove('bg-img1');
        document.querySelector('body').classList.remove('bg-img3');
        document.querySelector('body').classList.remove('bg-img4');
        $('body').removeClass('gradient-header');
        // $('body').removeClass('gradient-menu');
        document.getElementById('light_mode').classList.toggle('d-none');
        document.getElementById('transparent_mode').classList.toggle('d-none');

        // Establece el tema del usuario;
        let formData = new FormData();
        formData.append('theme', 'transparent-mode');
        postFunctionData(formData, 'Usuarios', 'setTheme', function (responseObj) { });

    })


    // datepicker
    if (document.querySelector('[data-toggle="datepicker"]')) {

        $('[data-toggle="datepicker"]').datepicker({
            language: 'es-ES',
            format: 'dd/mm/yyyy',
            autoHide: true,
            weekStart: 0
        });
    }

    // mask
    if (document.querySelector('.inputDateMask')) {
        $('.inputDateMask').mask('99/99/9999');
    }
    if (document.querySelector('.inputDateMaskTime')) {
        $('.inputDateMaskTime').mask('99:99');
    }


    function onKeyDownHandler(event) {

        var codigo = event.which || event.keyCode;

        // console.log("Presionada: " + codigo);

        // if (codigo === 13) {
        //     console.log("Tecla ENTER");
        // }

        // if (codigo >= 65 && codigo <= 90) {
        //     console.log(String.fromCharCode(codigo));
        // }

        return codigo;


    }


});



/*==================================================================
[ Window ]*/

window.addEventListener('load', function () {

    setTheme();
    getDepartamentosDisponibles();
    getApartadosEnProceso();
    getContratosProximosAVencer();

}, false)

window.jQuery(document).on('select2:open', e => {

    const id = e.target.id;
    if (document.querySelector('.select2-dropdown span input.select2-search__field')) {
        const target = document.querySelector('.select2-dropdown span input.select2-search__field');
        target.focus();
    }

})

window.onmousedown = function () {

    // console.log('onmousedown');

    getFunctionData('UsuarioSesion', 'getEstatusSesion', '', function (repsonseObj) {
        if (repsonseObj.data.respuesta == 'Cerrar Sesion') {
            window.location.href = base_url + "/login";
            // location.reload();
        }
    });

    /*==================================================================
    [ Funciones SetInterval ]*/
    // const milisegundos = 5 * 1000;
    // setInterval(function() {
    //     getFunctionData('UsuarioSesion', 'getEstatusSesion', '', function(repsonseObj) {
    //         console.log(repsonseObj);
    //         if (repsonseObj.data.respuesta == 'Cerrar Sesion') {
    //             window.location.href = base_url + "/login";
    //             // location.reload();
    //         }
    //     });
    // }, milisegundos);

}



/*==================================================================
[ Functions Windows Load ]*/

function setTheme() {

    /*==================================================================
    [ Establece el tema del usuario; ]*/
    getFunctionHTML('Usuarios', 'getTheme', '', function (tema) {

        if (tema == 'light-mode') {
            document.querySelector('body').classList.remove('dark-mode');
            document.querySelector('body').classList.remove('transparent-mode');
            document.querySelector('body').classList.remove('bg-img1');
            document.querySelector('body').classList.remove('bg-img2');
            document.querySelector('body').classList.remove('bg-img3');
            document.querySelector('body').classList.remove('bg-img4');
            $('body').addClass('gradient-header');
            // $('body').addClass('gradient-menu');
        } else {
            document.querySelector('body').classList.add('transparent-mode');
            document.querySelector('body').classList.add('bg-img2');
            document.querySelector('body').classList.remove('dark-mode');
            document.querySelector('body').classList.remove('light-mode');
            document.querySelector('body').classList.remove('bg-img1');
            document.querySelector('body').classList.remove('bg-img3');
            document.querySelector('body').classList.remove('bg-img4');
            $('body').removeClass('gradient-header');
            // $('body').removeClass('gradient-menu');
        }

        let bodyTransparent = $('body').hasClass('transparent-mode');
        if (!bodyTransparent) {
            if (document.getElementById('light_mode')) {
                document.getElementById('light_mode').classList.toggle('d-none');
                document.getElementById('transparent_mode').classList.toggle('d-none');
            }
        }

    });

}


function getDepartamentosDisponibles() {

    if (document.getElementById('departamentos_disponibles')) {
        getFunctionHTML('Departamentos', 'getTotalDepartamentosDisponibles', '', function (responseHTML) {
            document.getElementById('departamentos_disponibles').innerHTML = responseHTML;
        });
    }

}

function getApartadosEnProceso() {

    if (document.getElementById('apartados_proceso')) {
        getFunctionHTML('Apartados', 'getTotalApartadosEnProceso', '', function (responseHTML) {
            document.getElementById('apartados_proceso').innerHTML = responseHTML;
        });
    }

}

function getContratosProximosAVencer() {

    if (document.getElementById('proximos_a_vencer')) {
        getFunctionHTML('Contratos', 'getTotalProxAVencer', '', function (responseHTML) {
            document.getElementById('proximos_a_vencer').innerHTML = responseHTML;
        });
    }

}



/*==================================================================
[ Functions Globales ]*/

function fillSelectClientes() {

    if (document.querySelector('#comboClientes')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo de roles ]*/
        getFunctionHTML('Clientes', 'getSelectClientes', '', function (responseHTML) {

            document.querySelector('#comboClientes').innerHTML = responseHTML;
            /*-------------------------------------------
            [ Inicializa Select2 ]*/
            $('#comboClientes').select2({
                language: "es",
                placeholder: 'Seleccione una opcion'
            });


            getFunctionData('Clientes', 'getSessionCliente', '', function (responseObj) {

                if (responseObj.cliente_id == 0) {
                    $('#comboClientes').val('');
                } else {
                    $('#comboClientes').val(responseObj.cliente_id);
                }
                //Asignar Valor Default después de Inicializar Seleclt2
                $('#comboClientes').trigger('change');
            });


        });
    }

}


/*==================================================================
[ Functions Get Ajax ]*/

/**
 * Metodo GET. Obtiene el html a través de ajax, con el metodo GET.
 * @param controller Nombre del controlador a ejecutar. 
 * @param method Nombre del metodo del controlador a ejecutar. 
 * @param filtro Cadena que se desea filtrar.
 * @param html Resultado devuelto por el ajax en formato de cadena en formato html.
 * 
 */
function getFunctionHTML(controller, method, filtro, html) {

    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var response = xhttp.responseText;
            html(response);
        }
    };
    var ajaxUrl = base_url + '/' + controller + '/' + method + '/' + filtro;
    xhttp.open("GET", ajaxUrl, true);
    xhttp.setRequestHeader('Cache-Control', 'no-cache');
    xhttp.send();

}

/**
 * Metodo GET. Obtiene objeto de datos a través de ajax, con el metodo GET.
 * @param controller Nombre del controlador a ejecutar. 
 * @param method Nombre del metodo del controlador a ejecutar. 
 * @param filtro Cadena que se desea filtrar.
 * @param result Resultado devuelto por el ajax en formato de objeto.
 * 
 */
function getFunctionData(controller, method, filtro, result) {

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(xhttp.responseText);
            result(response);
        }
    };
    var ajaxUrl = base_url + '/' + controller + '/' + method + '/' + filtro;
    xhttp.open("GET", ajaxUrl, true);
    xhttp.setRequestHeader('Cache-Control', 'no-cache');
    xhttp.send();
}

/**
 * Metodo GET. Obtiene la lista de menus del sistema para el llenado de autocomplete.
 * @param filtro Cadena que se desea filtrar 
 * @param result Objeto con los datos obtenidos de acuerdo al filtro especificado. 
 */
function get_menus(filtro, result) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var dataObj = JSON.parse(xhttp.responseText);
            result(dataObj);
        }
    };
    var ajaxUrl = base_url + '/Menus/getMenus/' + filtro;
    xhttp.open("GET", ajaxUrl, true);
    xhttp.send();
}



/*==================================================================
[ Functions Set Ajax ]*/

/**
 * Metodo POST. Establece, Valida u Obtiene los registros en la base, y recibe un objeto de datos con el resultado de la función ejeuctada a través de ajax, con el metodo POST.
 * @param form Formulario con los datos que se envían al controlador. 
 * @param controller Nombre del controlador a ejecutar. 
 * @param method Nombre del metodo del controlador a ejecutar. 
 * @param result Resultado devuelto por el ajax en formato de objeto.
 * 
 */
function postFunction(form, controller, method, result) {

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (xhttp.responseText === '') {
                var dataObj = null;
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

/**
 * Metodo POST. Establece, Valida u Obtiene los registros en la base, y recibe un objeto de datos con el resultado de la función ejeuctada a través de ajax, con el metodo POST.
 * @param formData Formulario tipo FormData con los datos que se envían al controlador. 
 * @param controller Nombre del controlador a ejecutar. 
 * @param method Nombre del metodo del controlador a ejecutar. 
 * @param result Resultado devuelto por el ajax en formato de objeto.
 * 
 */
function postFunctionData(formData, controller, method, result) {

    var xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {

            if (xhttp.responseText === '') {
                var dataObj = null;
                result(dataObj);
            } else {
                var dataObj = JSON.parse(xhttp.responseText);
                result(dataObj);
            }
        }
    };
    var ajaxUrl = base_url + '/' + controller + '/' + method;
    xhttp.open("POST", ajaxUrl, true);
    xhttp.send(formData);
}


/*==================================================================
[ Funciones Header All Forms ]*/

function fntActivarHorizontalMenu(pageUrl) {

    // $(".horizontalMenu-list li a").each(function() {

    //     // var pageUrl = window.location.href.split(/[?#]/)[0];
    //     if (this.href == pageUrl) {
    //         $(this).addClass("active");
    //         $(this).parent().addClass("active"); // add active to li of the current link
    //         $(this).parent().parent().prev().addClass("active"); // add active class to an anchor
    //         $(this).parent().parent().prev().click(); // click the item to make it drop
    //     }
    // });

}


/*==================================================================
[ Funciones DataTable ]*/

function ReDesignButonExcel() {

    // select2-search__field bg-light

    // if (document.querySelector(".dt-buttons button")) {
    //     var boton_excel = document.querySelectorAll('.dt-buttons button');
    //     for (let index = 0; index < boton_excel.length; index++) {
    //         boton_excel[index].classList.remove("btn-secondary");
    //         boton_excel[index].classList.add("btn-primary");
    //     }
    //     var boton_excel_icon = document.querySelectorAll('.dt-buttons button span i');
    //     for (let index = 0; index < boton_excel_icon.length; index++) {
    //         boton_excel_icon[index].classList.remove("fas");
    //         boton_excel_icon[index].classList.add("fa-regular");
    //     }

    // }

}

function validaPermisoExportar(modulo_id) {

    // controller, method, filtro, result
    /*-------------------------------------------
    [ Ajax ]*/
    getFunctionData('Permisos', 'getPermisosMod', modulo_id, function (responseObj) {

        // var btnButtons = document.querySelectorAll(".buttons-colvis");
        // for (let index = 0; index < btnButtons.length; index++) {
        //     const element = btnButtons[index];
        //     element.style.display = "block";
        // }

        let permiso_exportar_excel = responseObj.e;
        if (permiso_exportar_excel == 1) {
            var btnExcel = document.querySelectorAll(".buttons-excel");
            for (let index = 0; index < btnExcel.length; index++) {
                const element = btnExcel[index];
                element.style.display = "block";
            }
        }

        var inputSearch = document.querySelector(".dataTables_filter label input");
        inputSearch.setAttribute("placeholder", "Buscar...")

    });

}


/*==================================================================
[ Funciones Loading ]*/

function addLoadingButtonOpcionesDataTable(btnOpciones, class_old) {

    /*-------------------------------------------
    [ Deshabilita para evitar doble acción ]*/
    btnOpciones.setAttribute('disabled', 'disabled');

    /*-------------------------------------------
    [ Agrega Loadign para hacer el efecto (before de ajax) ]*/
    // 0: text
    // 1: i.fa-regular.tx-14.fa-cog.fa-spin.fa-eye
    // 2: text
    var children = btnOpciones.childNodes;

    loading = children[0];
    loading.classList.remove(class_old);
    loading.classList.add('fa-cog', 'fa-spin');


}

function removeLoadingButtonOpcionesDataTable(btnOpciones, class_old) {

    /*-------------------------------------------
    [ Deshabilita para evitar doble acción ]*/
    btnOpciones.removeAttribute('disabled');

    /*-------------------------------------------
    [ Agrega Loadign para hacer el efecto (before de ajax) ]*/
    // 0: text
    // 1: i.fa-regular.tx-14.fa-cog.fa-spin.fa-eye
    // 2: text
    var children = btnOpciones.childNodes;
    loading.classList.remove('fa-cog', 'fa-spin');
    loading = children[0];
    loading.classList.add(class_old);

}

function addLoadingIconRowTable(btnOpciones, class_old) {

    /*-------------------------------------------
    [ Deshabilita para evitar doble acción ]*/
    btnOpciones.setAttribute('disabled', 'disabled');

    /*-------------------------------------------
    [ Agrega Loadign para hacer el efecto (before de ajax) ]*/
    var children = btnOpciones.childNodes;
    span = children[1];
    children = span.childNodes;
    loading = children[0];
    loading.classList.remove(class_old);
    loading.classList.add('fa-cog', 'fa-spin');

}

function removeLoadingIconRowTable(btnOpciones, class_old) {

    /*-------------------------------------------
    [ Deshabilita para evitar doble acción ]*/
    btnOpciones.removeAttribute('disabled');

    /*-------------------------------------------
    [ Agrega Loadign para hacer el efecto (before de ajax) ]*/
    var children = btnOpciones.childNodes;
    loading.classList.remove('fa-cog', 'fa-spin');
    span = children[1];
    children = span.childNodes;
    loading = children[0];
    loading.classList.add(class_old);

}


/*==================================================================
[ Funciones Form ]*/

function resetForm(formElement, form_id = "") {

    /*-------------------------------------------
    [ Reset Form ]*/
    formElement.reset();

    /*-------------------------------------------
      [ Reset Selects ]*/
    let selector = form_id + ' .select2';
    if (document.querySelector(selector)) {
        var selectsForm = document.querySelectorAll(selector);
        selectsForm.forEach(function myFunction(item, index) {
            selectsForm[index].value = "";
            $(selectsForm[index]).trigger('change');
        });
    }

}


/*==================================================================
[ Funciones Text ]*/

function toggleText(eL) {

    eL.classList.toggle("d-none");

    var eL2 = eL.nextSibling
    eL2.classList.toggle("modes_menu--mostrar");
    eL2.classList.toggle("d-none");


}


/*==================================================================
[ Funciones Diversas ]*/

function monthDiff(fecha1, fecha2) {

    fecha1 = Formato_Fecha_yyyymmdd(fecha1);
    var d1 = new Date(fecha1);

    fecha2 = Formato_Fecha_yyyymmdd(fecha2);
    var d2 = new Date(fecha2);

    var months;
    months = (d2.getFullYear() - d1.getFullYear()) * 12;
    months -= d1.getMonth();
    months += d2.getMonth();

    return months <= 0 ? 0 : months;
}

/**
 * obtiene la fecha actual del servidor
 */
function getFechaActual() {
    // crea un nuevo objeto `Date`
    var today = new Date();

    // `getDate()` devuelve el día del mes (del 1 al 31)
    var day = today.getDate();
    day = zfill(day, 2);

    // `getMonth()` devuelve el mes (de 0 a 11)
    var month = today.getMonth() + 1;
    month = zfill(month, 2);

    // `getFullYear()` devuelve el año completo
    var year = today.getFullYear();

    // muestra la fecha de hoy en formato `MM/DD/YYYY`
    return `${day}/${month}/${year}`;
}

function zfill(number, width) {
    var numberOutput = Math.abs(number); /* Valor absoluto del número */
    var length = number.toString().length; /* Largo del número */
    var zero = "0"; /* String de cero */

    if (width <= length) {
        if (number < 0) {
            return ("-" + numberOutput.toString());
        } else {
            return numberOutput.toString();
        }
    } else {
        if (number < 0) {
            return ("-" + (zero.repeat(width - length)) + numberOutput.toString());
        } else {
            return ((zero.repeat(width - length)) + numberOutput.toString());
        }
    }
}


function addDays(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}