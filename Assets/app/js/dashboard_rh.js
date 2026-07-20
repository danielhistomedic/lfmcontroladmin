/*==================================================================
[ Variables ]*/

/*==================================================================
[ DOMContentLoaded ]*/

document.addEventListener("DOMContentLoaded", function (event) {

    /*==================================================================
    [ Form ]*/


    /*==================================================================
    [ Botons de Accion ]*/

    document.querySelectorAll(".btnCerrar").forEach(btn => {
        btn.addEventListener("click", function () {
            document.getElementById('detalle-personal').classList.add('d-none');
        });
    });

    /*==================================================================
    [ DataTable ]*/


    /*==================================================================
    [ Select2 ]*/


});



/*==================================================================
[ Window ]*/

window.addEventListener('load', function () {

    getPersonalTotal();
    getTotalEmpleadosActivos();
    getTotalEmpleadosBaja();
    getTotalGraficoEmpleadosDepartamentos();
    getTotalGraficoEmpleadosPuestos();

}, false)



/*==================================================================
[ Personal ]*/

function getPersonalTotal() {

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    // formData.append('record_id', idRegistro);
    postFunctionData(formData, 'Personal', 'getTotalEmpleados', function (responseObj) {

        if (responseObj.respuesta == "ok") {

            // --- Cargar datos recibidos ---
            document.getElementById('total_empleados').innerHTML = responseObj.data.total_empleados;

        } else {

            document.getElementById('total_empleados').innerHTML = "0";
            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");
        }

        /*-------------------------------------------
        [ Loading ]*/
        // divLoading.style.display = 'none';

    });

}

function getTotalEmpleadosActivos() {

    // /*-------------------------------------------
    //  [ Obtiene id de registro ]*/
    // let idRegistro = btnElement.getAttribute("data-id");

    /*-------------------------------------------
     [ Loading ]*/
    // divLoading.style.display = 'flex';

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    // formData.append('record_id', idRegistro);
    postFunctionData(formData, 'Personal', 'getTotalEmpleadosActivos', function (responseObj) {

        if (responseObj.respuesta == "ok") {

            // --- Cargar datos recibidos ---
            document.getElementById('total_empleados_activos').innerHTML = responseObj.data.total_empleados_activos;

        } else {

            document.getElementById('total_empleados_activos').innerHTML = "0";
            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");
        }

        /*-------------------------------------------
        [ Loading ]*/
        // divLoading.style.display = 'none';

    });

}

function getTotalEmpleadosBaja() {

    // /*-------------------------------------------
    //  [ Obtiene id de registro ]*/
    // let idRegistro = btnElement.getAttribute("data-id");

    /*-------------------------------------------
     [ Loading ]*/
    // divLoading.style.display = 'flex';

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    // formData.append('record_id', idRegistro);
    postFunctionData(formData, 'Personal', 'getTotalEmpleadosBaja', function (responseObj) {

        if (responseObj.respuesta == "ok") {

            // --- Cargar datos recibidos ---
            document.getElementById('total_empleados_baja').innerHTML = responseObj.data.total_empleados_baja;

        } else {


            document.getElementById('total_empleados_baja').innerHTML = "0";
            /** -- Mensaje de Alerta -- */
            alerta_error(responseObj, "");
        }

        /*-------------------------------------------
        [ Loading ]*/
        // divLoading.style.display = 'none';

    });

}

const chartPersonalDepartamentos = echarts.init(document.getElementById('chart_personal_departamentos'));

chartPersonalDepartamentos.on('click', function (params) {

    let departamento = params.name;
    let estado = params.seriesName; // Activos o Inactivos

    console.log(departamento);
    console.log(estado);
    getListChartPersonalFiltro(departamento, estado);



});


function getListChartPersonalFiltro(departamento, estado) {

    /*-------------------------------------------
    [ Mostrar panel con spinner inmediatamente ]*/
    document.getElementById('titulo').innerText = `${estado} - ${departamento}`;
    document.getElementById('contenido').innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                <span class="text-muted">Cargando empleados...</span>
            </td>
        </tr>`;
    document.getElementById('detalle-personal').classList.remove('d-none');
    document.getElementById('detalle-personal').scrollIntoView({ behavior: 'smooth', block: 'start' });

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    formData.append('departamento', departamento);
    formData.append('estado', estado);
    postFunctionData(formData, 'Personal', 'getListPersonalChart', function (responseObj) {

        if (responseObj.respuesta == "ok") {

            let html = "";
            responseObj.data.forEach(p => {
                html += `<tr>
                            <td class="ps-4"><i class="fa-regular fa-user text-primary"></i></td>
                            <td>${p.cNombre} ${p.cPriApellido} ${p.cSegApellido}</td>
                            <td>${p.cdscservicio}</td>
                            <td>${p.telefono}</td>
                        </tr>`;
            });

            document.getElementById('contenido').innerHTML = html;

        } else {

            document.getElementById('contenido').innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fa-regular fa-triangle-exclamation fa-2x d-block mb-2 text-warning"></i>
                        No se encontraron empleados para esta selección.
                    </td>
                </tr>`;
        }

    });

}

/*==================================================================
[ Gráfico de Personal por Areas ]*/

function getTotalGraficoEmpleadosDepartamentos() {

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    formData.append('fecha_hora_ini', 'dataFilter.fecha_hora_ini');
    formData.append('fecha_hora_final', 'dataFilter.fecha_hora_final');
    postFunctionData(formData, 'Personal', 'getTotalEmpleadosDepartamentos', function (responseObj) {

        if (responseObj.respuesta == "ok") {

            var data = responseObj.data;
            graficarTotalGraficoEmpleadosDepartamentos(data)

        } else {
            // divLoading.style.display = 'none';
        }

    });

}

function graficarTotalGraficoEmpleadosDepartamentos(data) {

    let categorias = data.map(d => d.departamento);
    let activos = data.map(d => d.activos);
    let bajas = data.map(d => d.inactivos);

    chartPersonalDepartamentos.setOption({
        title: {
            text: 'Departamentos'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: ['Activos', 'Bajas']
        },
        xAxis: {
            type: 'category',
            data: categorias,
            axisLabel: {
                interval: 0,
                rotate: 30,
                fontSize: 9,
                color: '#00809F'
            }
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name: 'Activos',
                type: 'bar',
                data: activos,
                label: {
                    show: true,
                    position: 'top'
                }
            },
            {
                name: 'Bajas',
                type: 'bar',
                data: bajas,
                label: {
                    show: true,
                    position: 'top'
                }
            }
        ]
    });

}



/*==================================================================
[ Gráfico de Personal por Puestos ]*/

function getTotalGraficoEmpleadosPuestos() {

    /*-------------------------------------------
    [ Ajax ]*/
    var formData = new FormData();
    postFunctionData(formData, 'Personal', 'getTotalEmpleadosPuestos', function (responseObj) {

        if (responseObj.respuesta == "ok") {

            var data = responseObj.data;
            graficarTotalGraficoEmpleadosPuestos(data)

        } else {
            // divLoading.style.display = 'none';
        }

    });

}

function graficarTotalGraficoEmpleadosPuestos(data) {

    const chartEstado = echarts.init(document.getElementById('chart_personal_puestos'));

    let categorias = data.map(d => d.puestos);
    let activos = data.map(d => d.activos);
    let bajas = data.map(d => d.inactivos);

    chartEstado.setOption({
        title: {
            text: 'Puestos'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: ['Activos', 'Bajas']
        },
        xAxis: {
            type: 'category',
            data: categorias,
            axisLabel: {
                interval: 0,
                rotate: 30,
                fontSize: 9,
                color: '#00809F'
            }
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name: 'Activos',
                type: 'bar',
                data: activos,
                label: {
                    show: true,
                    position: 'top'
                }
            },
            {
                name: 'Bajas',
                type: 'bar',
                data: bajas,
                label: {
                    show: true,
                    position: 'top'
                }
            }
        ]
    });

}


/*==================================================================
[ Funciones Diversas ]*/

function cerrarCaja(eL) {
    document.getElementById("detalle-personal").style.display = "none";
}
