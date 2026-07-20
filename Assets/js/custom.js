/* Add here all your JS customizations */


//fancyfileuplod
if (document.getElementById('imagenes_producto')) {
    $('#imagenes_producto').FancyFileUpload({
        params: {
            action: 'fileuploader'
        },
        maxfilesize: 1000000
    });
}