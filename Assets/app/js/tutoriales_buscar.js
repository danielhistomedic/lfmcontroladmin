/*==================================================================
[ Variables de Archivo ]*/

let table;
let tableElement = "#tableRecords";
let tableElementJS = "tableRecords";
let configTable = "";

let controllers = "Tutoriales";
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
                cargarResultadoBusqueda(event);
            }
        }, false);
    });


    /*==================================================================
    [ Botons de Accion ]*/


    /*-------------------------------------------
    [ Agregar evento click a Filtros Opciones ]*/
    if (document.getElementById("btnAplicarFiltro_Menu")) {
        let btnElement = document.getElementById('btnAplicarFiltro_Menu');
        btnElement.onclick = function() {
            let menu = $('#comboMenus').select2().val();
            cargarResultadoBusquedaMenu(menu)
        };
    }

    if (document.getElementById("btnAplicarFiltro_Submenu")) {
        let btnElement = document.getElementById('btnAplicarFiltro_Submenu');
        btnElement.onclick = function() {
            let menu = $('#comboMenus').select2().val();
            let submenu = $('#comboSubmenus').select2().val();
            cargarResultadoBusquedaSubMenu(menu, submenu);
        };
    }


    /*==================================================================
      [ Select2 ]*/

    $('#comboMenus').on('select2:select', function(e) {
        var data = e.params.data;
        cargarResultadoBusquedaMenu(data.id);
    });

    /*-------------------------------------------
    [ Inicializa Select2 ]*/
    $('#comboSubmenus').select2({
        language: "es",
        placeholder: 'Seleccione una opcion',
        minimumResultsForSearch: Infinity
    });

    $('#comboSubmenus').on('select2:select', function(e) {
        let menu = $('#comboMenus').select2().val();
        var data = e.params.data;
        cargarResultadoBusquedaSubMenu(menu, data.id);
    });

});

/*==================================================================
[ Window ]*/

window.addEventListener('load', function() {

    fillSelectMenus();

    /*-------------------------------------------
    [ Funciones Fill Selects ]*/
    setTimeout(() => {
        document.getElementById('tutorial_filtro').focus();
    }, 500);

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

function fillSelectMenus() {

    if (document.querySelector('#comboMenus')) {
        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo de roles ]*/
        getFunctionHTML(controllers, 'getSelectMenu', '', function(responseHTML) {

            document.querySelector('#comboMenus').innerHTML = responseHTML;
            /*-------------------------------------------
            [ Inicializa Select2 ]*/
            $('#comboMenus').select2({
                language: "es",
                placeholder: 'Seleccione una opcion',
                minimumResultsForSearch: Infinity
            });
            //Asignar Valor Default después de Inicializar Seleclt2
            $('#comboMenus').val('');
            $('#comboMenus').trigger('change');
        });
    }

}

function fillSelectSubmenus(menu) {

    if (document.querySelector('#comboSubmenus')) {

        document.querySelector('.dimmer-object').style.display = 'flex';


        /*-------------------------------------------
        [ Ajax LLenar Select de catalogo de roles ]*/
        getFunctionHTML(controllers, 'getSelectSubmenu', menu, function(responseHTML) {

            document.querySelector('#comboSubmenus').innerHTML = responseHTML;
            /*-------------------------------------------
            [ Inicializa Select2 ]*/
            $('#comboSubmenus').select2({
                language: "es",
                placeholder: 'Seleccione una opcion',
                minimumResultsForSearch: Infinity
            });
            //Asignar Valor Default después de Inicializar Seleclt2
            $('#comboSubmenus').val('');
            $('#comboSubmenus').trigger('change');


            document.querySelector('.dimmer-object').style.display = 'none';
        });
    }

}


/*==================================================================
[ Barra de Botones y Botones de Accion ]*/

function cargarResultadoBusqueda(event) {

    /*-------------------------------------------
    [ Evita la recarga de la pagina. ]*/
    event.preventDefault();

    /*-------------------------------------------
    [ Mostrar Loading en div. ]*/
    var divLoading = document.querySelector('.dimmer-object-search');
    divLoading.style.display = "flex";

    /*-------------------------------------------
    [ Limpiar contenido ]*/
    let contents = document.getElementById('contents');
    contents.innerHTML = '';


    let filtro = document.getElementById('tutorial_filtro').value;


    /*-------------------------------------------
     [ Ajax LLenar Select de catalogo de roles ]*/
    getFunctionData(controllers, 'getFiltro', filtro, function(data) {

        if (data.length > 0) {

            for (let index = 0; index < data.length; index++) {

                const element = data[index];
                contents.innerHTML += '<div class="row border-bottom mb-2"> \
                                            <div class="form-group d-md-flex"> \
                                                <div class="card overflow-hidden"> \
                                                    <video class="" title="' + element.titulo + '" width="100%" controls> \
                                                        <source src="' + assets + '/files/videos/' + element.archivo + '" type="video/mp4"> \
                                                        Tu Navegador no soporta HTML video. \
                                                    </video> \
                                                </div> \
                                                <div class="ms-0 ms-md-4 mt-3 mt-md-0"> \
                                                    <ol class="breadcrumb text-primary fs-12 mb-3"> \
                                                        <li class="breadcrumb-item">' + element.menu + '</li> \
                                                        <li class="breadcrumb-item">' + element.submenu + '</li> \
                                                    </ol> \
                                                    <h4 class="fw-bold">' + element.titulo + '</h4> \
                                                    <p>' + element.descripcion + '</p> \
                                                </div> \
                                            </div> \
                                        </div>';

            }

        } else {

            contents.innerHTML = '<div class="row border-bottom mb-2"> \
                                        <div class="form-group d-md-flex"> \
                                            <p><i class="fa-regular fa-face-thinking text-danger"></i> No se encontraron resultados.</p> \
                                        </div> \
                                    </div>';

        }


        divLoading.style.display = 'none';
    });

}

function cargarResultadoBusquedaMenu(menu) {

    /*-------------------------------------------
    [ Mostrar Loading en div. ]*/
    var divLoading = document.querySelector('.dimmer');
    divLoading.style.display = "flex";

    /*-------------------------------------------
    [ Limpiar contenido ]*/
    let contents = document.getElementById('contents');
    contents.innerHTML = '';


    let filtro = menu;


    /*-------------------------------------------
     [ Ajax LLenar Select de catalogo de roles ]*/
    getFunctionData(controllers, 'getFiltroMenu', filtro, function(data) {

        if (data.length > 0) {

            for (let index = 0; index < data.length; index++) {

                const element = data[index];
                contents.innerHTML += '<div class="row border-bottom mb-2"> \
                                            <div class="form-group d-md-flex"> \
                                                <div class="card overflow-hidden"> \
                                                    <video class="" title="' + element.titulo + '" width="100%" controls> \
                                                        <source src="' + assets + '/files/videos/' + element.archivo + '" type="video/mp4"> \
                                                        Tu Navegador no soporta HTML video. \
                                                    </video> \
                                                </div> \
                                                <div class="ms-0 ms-md-4 mt-3 mt-md-0"> \
                                                    <ol class="breadcrumb text-primary fs-12 mb-3"> \
                                                        <li class="breadcrumb-item">' + element.menu + '</li> \
                                                        <li class="breadcrumb-item">' + element.submenu + '</li> \
                                                    </ol> \
                                                    <h4 class="fw-bold">' + element.titulo + '</h4> \
                                                    <p>' + element.descripcion + '</p> \
                                                </div> \
                                            </div> \
                                        </div>';

            }

        } else {

            contents.innerHTML = '<div class="row border-bottom mb-2"> \
                                        <div class="form-group d-md-flex"> \
                                            <p><i class="fa-regular fa-face-thinking text-danger"></i> No se encontraron resultados.</p> \
                                        </div> \
                                    </div>';

        }


        divLoading.style.display = 'none';

        /*-------------------------------------------
        [ Cargar datos de submenu asociados al menu seleccinado ]*/
        fillSelectSubmenus(filtro);

    });

}

function cargarResultadoBusquedaSubMenu(menu, submenu) {

    /*-------------------------------------------
    [ Mostrar Loading en div. ]*/
    var divLoading = document.querySelector('.dimmer');
    divLoading.style.display = "flex";

    /*-------------------------------------------
    [ Limpiar contenido ]*/
    let contents = document.getElementById('contents');
    contents.innerHTML = '';


    let filtro = menu + '*' + submenu;


    /*-------------------------------------------
     [ Ajax LLenar Select de catalogo de roles ]*/
    getFunctionData(controllers, 'getFiltroSubMenu', filtro, function(data) {

        if (data.length > 0) {

            for (let index = 0; index < data.length; index++) {

                const element = data[index];
                contents.innerHTML += '<div class="row border-bottom mb-2"> \
                                            <div class="form-group d-md-flex"> \
                                                <div class="card overflow-hidden"> \
                                                    <video class="" title="' + element.titulo + '" width="100%" controls> \
                                                        <source src="' + assets + '/files/videos/' + element.archivo + '" type="video/mp4"> \
                                                        Tu Navegador no soporta HTML video. \
                                                    </video> \
                                                </div> \
                                                <div class="ms-0 ms-md-4 mt-3 mt-md-0"> \
                                                    <ol class="breadcrumb text-primary fs-12 mb-3"> \
                                                        <li class="breadcrumb-item">' + element.menu + '</li> \
                                                        <li class="breadcrumb-item">' + element.submenu + '</li> \
                                                    </ol> \
                                                    <h4 class="fw-bold">' + element.titulo + '</h4> \
                                                    <p>' + element.descripcion + '</p> \
                                                </div> \
                                            </div> \
                                        </div>';

            }

        } else {

            contents.innerHTML = '<div class="row border-bottom mb-2"> \
                                        <div class="form-group d-md-flex"> \
                                            <p><i class="fa-regular fa-face-thinking text-danger"></i> No se encontraron resultados.</p> \
                                        </div> \
                                    </div>';

        }


        divLoading.style.display = 'none';

        $('#comboMenus').select2({
            language: "es",
            placeholder: 'Seleccione una opcion',
            minimumResultsForSearch: Infinity
        });

    });

}




/*==================================================================
[ Botones Datatable ]*/



/*==================================================================
[ Carga de Datos ]*/


/*==================================================================
[ DataTable ]*/