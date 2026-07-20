/**
 * Llena con ceros a la izquierda de un numero dado.
 *
 * @param {int|float} number. Valor del numero a dar formato con ceros a la izquierda;
 * @param {int} len. Cantidad de ceros a la izquierda que desea colocar;
 * @return {string} Cadena con el numero dado con la cantidad de ceros indicada.
 */
const fillLeft = (number, len) =>
    "0".repeat(len - number.toString().length) + number.toString();

/**
 * Da formato de nombre de mes a un valor numerico de mes espcecificado.
 *
 * @param {int} value. Valor numerico del mes como se obtiene del getMonth();
 * @return {string} meses[value]. Nombre completo del mes.
 */
const mesDescripcion = (value) => {
    var meses = new Array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    return meses[value];
}


/**
 * Modifica formato de una cadena de fecha.
 *
 * @param {string} date la fecha en formato yyyy-mm-dd
 * @return {string} fecha en formato dd/mm/yyyy.
 */
function getFechaActual_ddmmyyyy() {

    var fecha = date('d/m/Y');
    return fecha;

}



/**
 * Modifica formato de una cadena de fecha.
 *
 * @param {string} date la fecha en formato yyyy-mm-dd
 * @return {string} fecha en formato dd/mm/yyyy.
 */
function Formato_Fecha_ddmmyyyy(date) {

    try {
        // date origen = yyyy-mm-dd
        var parts = date.split('-');
        var dia = parts[2].substring(0, 2);
        var fecha_res = dia + '/' + parts[1] + '/' + parts[0];
        return fecha_res;
    } catch (error) {
        return '';
    }

}


/**
 * Modifica formato de una cadena de fecha y hora
 *
 * @param {string} date la fecha en formato yyyy-mm-dd H:i:s
 * @return {string} fecha en formato dd/mm/yyyy.
 */
function Formato_Fecha_ddmmyyyyHis(date) {

    try {
        // date origen = yyyy-mm-dd H:i:s
        var parts = date.split('-');
        var dia = parts[2].substring(0, 2);

        var time_sel = parts[2].substring(3, 11);

        var fecha_res = dia + '/' + parts[1] + '/' + parts[0] + ' ' + time_sel;
        return fecha_res;
    } catch (error) {
        return '';
    }


}


/**
 * Modifica formato de una cadena de fecha.
 *
 * @param {string} date la fecha en formato dd/mm/yyyy
 * @return {string} fecha en formato yyyy/mm/dd.
 */
function Formato_Fecha_yyyymmdd_diag(date) {

    try {
        // date origen = dd/mm/yyyy
        var parts = date.split('/');
        var fecha_res = parts[2] + '/' + parts[1] + '/' + parts[0];
        return fecha_res;
    } catch (error) {
        return '';
    }

}


/**
 * Modifica formato de una cadena de fecha.
 *
 * @param {string} date la fecha en formato dd/mm/yyyy
 * @return {string} fecha en formato mm/dd/yyyy.
 */
function Formato_Fecha_mmddyyyy(date) {

    try {
        // date origen = dd/mm/yyyy
        var parts = date.split('/');
        var fecha_res = parts[1] + '/' + parts[0] + '/' + parts[2];
        return fecha_res;
    } catch (error) {

    }

}



/**
 * Modifica formato de una cadena de fecha.
 *
 * @param {string} date la fecha en formato dd/mm/yyyy
 * @return {string} fecha en formato yyyy-mm-dd.
 */
function Formato_Fecha_yyyymmdd(date) {
    try {
        var parts = date.split('/');
        var fecha_res = parts[2] + '-' + parts[1] + '-' + parts[0];
        return fecha_res;
    } catch (error) {

    }

}



/**
 * Da formato de moneda a un monto espcecificado.
 *
 * @param {float} monto. Valor del monto a dar formato
 * @return {string} Cadena con el valor del monto en formato de cadena con el signo de la moneda.
 */
function Formato_Moneda(monto) {
    monto_convertido = new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" }).format(monto);
    return monto_convertido;
}

/**
 * Da formato de nombre de mes a un valor numerico de mes espcecificado.
 *
 * @param {int} mes. Mes en formato de numero entero como se obtiene de getMonth();
 * @return {string} mes_name. Cadena con el nombre completo del mes.
 */
function Formato_MesName(mes) {

    var arrMeses = new Array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    var mes_name;
    mes_base = parseInt(mes);
    mes_name = arrMeses[mes_base];

    return mes_name;
}