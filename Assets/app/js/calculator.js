/*-------------------------------------------
[ Edad en base a una fecha dada ]*/

function calcularEdad(fecha_nacimiento) {

    const edad = fechaNac => {

        if (!fechaNac || isNaN(new Date(fechaNac))) return;
        const hoy = new Date();
        const dateNac = new Date(fechaNac);
        if (hoy - dateNac < 0) return;
        let dias = hoy.getUTCDate() - dateNac.getUTCDate();
        let meses = hoy.getUTCMonth() - dateNac.getUTCMonth();
        let years = hoy.getUTCFullYear() - dateNac.getUTCFullYear();
        if (dias < 0) {
            meses--;
            dias = 30 + dias;
        }
        if (meses < 0) {
            years--;
            meses = 12 + meses;
        }

        let years_str;
        let meses_str;
        let dias_str;

        if (years == 0) {
            years_str = '';
        } else if (years == 1) {
            years_str = years + ' año, ';
        } else {
            years_str = years + ' años, ';
        }

        if (meses == 0) {
            meses_str = meses + ' meses, ';
        } else if (meses == 1) {
            meses_str = meses + ' mes, ';
        } else {
            meses_str = meses + ' meses, ';
        }

        if (dias == 0) {
            dias_str = dias + ' dias ';
        } else if (dias == 1) {
            dias_str = dias + ' dia ';
        } else {
            dias_str = dias + ' dias ';
        }

        return [years_str, meses_str, dias_str];

    }

    let suEdad = edad(fecha_nacimiento);
    if (suEdad) {
        return `${suEdad[0]} ${suEdad[1]} ${suEdad[2]}`;
    } else {
        return '';
    }


}