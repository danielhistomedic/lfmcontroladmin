<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" style="width:100%;font-family:lato, 'helvetica neue', helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title>Complemento de Registro de Datos</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;300;400;600;700;800;900&amp;display=swap" rel="stylesheet">

    <style type="text/css">

    </style>
</head>

<body style="text-align: center;margin: 20px auto;width: 650px;font-family:'Nunito Sans', sans-serif;background-color: #e2e2e2;display: block;position: relative;">

    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background-color: #fff; width: 100%; margin: 0; padding: 0; box-sizing: border-box;  border-collapse: collapse;border-spacing: 0;">

        <tbody>

            <tr class="header" style="display: block; text-align: center; padding: 16px 0; padding-top: 0;">
                <td align="center" valign="center" style="background-color: #ffe4e1; padding: 16px 0; display: block; text-align: center;">
                    <img style=" width: 110px; height: auto;" src="https://bbluemegacom.mx/images/email/logo_main.png" class="main-logo">
                </td>
            </tr>

            <tr>
                <td class="section-t" style="position: relative; padding: 0 12px;   margin-top: 12px; display: block;">
                    <table style="width: 100%;  border-collapse: collapse;border-spacing: 0;">
                        <tbody>
                            <tr>
                                <td>
                                    <h1 class="heading-1" style="margin-bottom: 6px; font-weight: bold; font-size: 16px;line-height: 12px; color: #252525;">Hola ¡<?= $data['nombre']; ?>!</h1>
                                    <p class="pera" style=" font-weight: 500; font-size: 14px; line-height: 1.4; text-align: center; color: #939393; margin-bottom: -4px;">
                                        Su pre-registro para integrarte a nuestro equipo como <strong><?= $data['puesto']; ?></strong> ha sido aprobado,
                                        <br>favor de completar el formulario de registro, para finalizar el proceso.
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <td class="section-t" style="   margin-top: 12px;            display: block;">
                                    <a href="<?= $data['url']; ?>" class="button-solid" style=" font-weight: bold; text-decoration: none; font-size: 14px; line-height: 12px; display: inline-block;color: #ffffff;background: #E41561; border-radius: 6px; padding: 12px;">Completar Formulario de Registro</a>
                                </td>
                            </tr>

                            <tr>
                                <td class="section-t" style="display: block;">
                                    <p class="pera" style=" font-weight: 500; font-size: 14px; line-height: 1.4; text-align: center; color: #939393; margin-bottom: -4px;">
                                        Una vez finalizado, uno de nuestros reclutadores lo contactará para indicarle<br> los siguientes pasos a seguir.
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <td class="section-t" style="display: block;">
                                    <p class="pera" style=" font-weight: 500; font-size: 14px; line-height: 1.4; text-align: center; color: #939393; margin-bottom: -4px;">Consulta nuestra <a style=" text-decoration: none;" href="https://bbluemegacom.mx/legal/avisoPrivacidad" target="_blank">Política de Privacidad</a></p>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </td>
            </tr>

            <tr>
                <td class="section-t">
                    <hr style="width:50%; border-bottom: 1px solid #e1e1e1;">
                </td>
            </tr>

            <tr>
                <td class="section-t" style="padding: 0 12px;margin-top: 24px; display: block;    font-weight: 500;    font-size: 14px;    line-height: 1.4;   text-align: center;color: #939393;">
                    <p class="pera" style="margin-bottom:0;margin-top:0">Para cualquier duda puedes contactarnos en:</p>
                    <p class="pera" style="margin-bottom:0;margin-top:0">MEGACOM</p>
                    <p class="pera" style="margin-bottom:0;margin-top:0">Telefono: 6144760975</p>
                    <p class="pera" style="margin-bottom:0;margin-top:0">email: admin@bbluemegacom.mx</p>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="section-t">
                    <table class="footer" style="  border-collapse: collapse; border-spacing: 0; position: relative;width: 100%;">
                        <tbody>
                            <tr>
                                <td class="footer-content" style="background-color: #212121;margin-top:24px; display: block;padding: 20px 0;">
                                    <table border="0" cellpadding="0" cellspacing="0" class="footer-social-icon" align="center" style="vertical-align: middle; margin: 0 auto; width: 326px;  border-collapse: collapse;border-spacing: 0;">
                                        <tbody>
                                            <tr class="social">
                                                <td style="margin:0 auto;">
                                                    <a style=" text-decoration: none;" href="https://www.facebook.com/Megacom.master"><img style="width: 14px;height:auto;" src="https://bbluemegacom.mx/images/email/facebook2.png" alt="fb"></a>
                                                </td>
                                            </tr>
                                            <tr style="">
                                                <td style="margin:0 auto;">
                                                    <p class="pera" style="font-weight: 500;font-size: 12px;line-height: 1.1;text-align: center;color: #939393;margin-bottom: -4px;">Derechos Reservados ©2022 MEGACOM</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <!-- <tr class="header" style="display: block; text-align: center; padding: 16px 0; padding-top: 0;">
                <td align="center" valign="center" style="background-color: #ffe4e1; padding: 16px 0; display: block; text-align: center;">
                    <img style=" width: 110px; height: auto;" src="https://bbluemegacom.mx/images/email/logo_main.png" class="main-logo">
                </td>
            </tr> -->

        </tbody>
    </table>


</body>


</html>