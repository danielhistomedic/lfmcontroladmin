/* -------------------------------------------
[ Agregar eventos para animaciones en elementos Buttons ] */

function animationButton(eLOrigen, eLDestino) {

    // console.log(eLOrigen);
    var animation = $(eLOrigen).attr('data-animation');
    // console.log(animation);
    $(eLDestino).addClass(animation + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
        $(eLOrigen).removeClass(animation);
        $(eLOrigen).removeClass('animated');
    });

}

function animationElement(eLOrigen, eLDestino, result) {

    // console.log(eLOrigen);
    var animation = $(eLOrigen).attr('data-animation');
    // console.log(animation);
    $(eLDestino).addClass(animation + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
        $(eLOrigen).removeClass(animation);
        $(eLOrigen).removeClass('animated');
        result(true);
    });

}

function animationBtnEdit(btnEditar, eLDestino) {

    var animation = $(btnEditar).attr('data-animation');
    $(eLDestino).addClass(animation + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
        $(btnEditar).removeClass(animation);
        $(btnEditar).removeClass('animated');
    });

}

function animationBtnCancelar(btnCancelar, eLDestino) {

    var animation = $(btnCancelar).attr('data-animation');
    $(eLDestino).addClass(animation + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
        $(btnCancelar).removeClass(animation);
        $(btnCancelar).removeClass('animated');
    });

}