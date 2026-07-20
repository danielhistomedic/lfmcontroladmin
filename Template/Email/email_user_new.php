<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" style="width:100%;font-family:lato, 'helvetica neue', helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title>Bienvenida</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700,700i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Work+Sans:100,200,300,400,500,600,700,800,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <style>
        body {
            width: 650px;
            font-family: work-Sans, sans-serif;
            background-color: #f6f7fb;
            display: block;
        }

        a {
            text-decoration: none;
        }

        span {
            font-size: 14px;
        }

        p {
            font-size: 13px;
            line-height: 1.7;
            letter-spacing: 0.7px;
            margin-top: 0;
        }

        .text-center {
            text-align: center
        }
    </style>
</head>

<body style="margin: 30px auto;">
    <table style="width: 100%">
        <tbody>
            <tr>
                <td>
                    <table style="background-color: #f6f7fb; width: 100%">
                        <tbody>
                            <tr>
                                <td>
                                    <table style="width: 650px; margin: 0 auto; margin-bottom: 30px">
                                        <tbody>
                                            <tr>
                                                <td><img src="https://histoclin.mx/files/images/emails/logo-bonar.png" alt=""></td>
                                                <td style="text-align: right; color:#999;">
                                                    <p style="margin-right: 120px;font-size: 24px;">¡Felicidades!</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table style="width: 638px; margin: 0 auto; border-radius: 8px">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 30px 30px 15px 30px">
                                                    <p style="font-size: 18px; font-weight: 600;">Hola <?= $data['nombre_usuario']; ?></p>
                                                    <p>
                                                        Ha sido creada su cuenta para acceder al <strong>Sistema de Administración Bonar</strong>,
                                                        puede entrar al sistema dando click en el botón de abajo, colocando el usuario y
                                                        contraseña asignados en datos de acceso.
                                                        <br> <br> Recuerde que una vez que inicie sesión por primera vez, deberá cambiar su contraseña de acceso para mayor seguridad de su cuenta
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 0px 30px;">
                                                    <p style="font-size: 18px; font-weight: 600;">Datos de acceso:</p>
                                                    <div style="margin-top: 12px; display: flex; justify-content: start; align-items: center;">
                                                        <img src="https://histoclin.mx/files/images/emails/user-regular.png" alt="user" border="0" style="width: 16px; height: 18px;">
                                                        <span style="margin-left: 6px; margin-right: 6px;">Usuario:</span><strong><?= $data['usuario']; ?></strong>
                                                    </div>
                                                    <div style="margin-top: 12px; display: flex; justify-content: start; align-items: center;">
                                                        <img src="https://histoclin.mx/files/images/emails/unlock-regular.png" alt="pass" border="0" style="width: 16px; height: 18px;">
                                                        <span style="margin-left: 6px; margin-right: 6px;">Contraseña:</span><strong><?= $data['password']; ?></strong>
                                                    </div>
                                                    <a href="<?= WEB_LOGIN . '?usuario=' . $data['usuario']; ?>" style="margin: 14px 0; padding: 10px; background-color: #45c1cd; color: #ffffff; display: inline-block; border-radius: 4px; margin-bottom: 18px">
                                                        Iniciar Sessión
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table style="width: 638px; margin: 0 auto; margin-top: 30px; margin-bottom: 30px;">
                                        <tbody>
                                            <tr style="text-align: center">
                                                <td>
                                                    <p style="color: #999; margin-bottom: 0">Para cualquier duda contáctanos en:</p>
                                                    <p style="color: #999; margin-bottom: 0">BONA RESIDENCES</p>
                                                    <p style="color: #999; margin-bottom: 0">Av. Jesús del Monte 33</p>
                                                    <p style="color: #999; margin-bottom: 0">Hacienda de las Palmas, Méx. C.P. 52763</p>
                                                    <p style="color: #999; margin-bottom: 0">Telefono: 55 4025 0120</p>
                                                    <p style="color: #999; margin-bottom: 0">email: soporte@bonare.com.mx</p>
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
        </tbody>
    </table>
</body>

</html>