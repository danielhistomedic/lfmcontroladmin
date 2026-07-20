<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <title>Solicitud de Cotización</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f4f6f9">
        <tr>
            <td align="center" style="padding:30px 15px;">

                ```
                <!-- Contenedor Principal -->
                <table width="700" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff"
                    style="max-width:700px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.15);">

                    <!-- Encabezado -->
                    <tr>
                        <td bgcolor="#0F4C81" style="padding:25px;color:#ffffff;">
                            <h2 style="margin:0;font-size:24px;">Solicitud de Cotización</h2>
                            <p style="margin:5px 0 0 0;font-size:14px;">
                                Departamento de Compras
                            </p>
                        </td>
                    </tr>

                    <!-- Mensaje -->
                    <tr>
                        <td style="padding:30px;color:#333333;font-size:14px;line-height:1.6;">
                            Estimado proveedor:
                            <br><br>

                            Por medio de la presente, solicitamos de su apoyo para proporcionar una cotización de los siguientes productos y/o servicios:

                            <br><br>

                            <!-- Información General -->
                            <table width="100%" cellpadding="8" cellspacing="0" border="0"
                                style="border:1px solid #dcdcdc;background:#fafafa;">
                                <tr>
                                    <td width="180"><strong>Fecha Solicitud:</strong></td>
                                    <td>{FECHA}</td>
                                </tr>
                                <tr>
                                    <td><strong>Folio:</strong></td>
                                    <td>{FOLIO}</td>
                                </tr>
                                <tr>
                                    <td><strong>Proyecto:</strong></td>
                                    <td>{PROYECTO}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha Límite:</strong></td>
                                    <td>{FECHA_LIMITE}</td>
                                </tr>
                            </table>

                            <br>

                            <!-- Productos -->
                            <table width="100%" cellpadding="8" cellspacing="0" border="1"
                                style="border-collapse:collapse;border-color:#dcdcdc;">
                                <tr bgcolor="#0F4C81">
                                    <th style="color:#ffffff;">#</th>
                                    <th style="color:#ffffff;">Producto / Servicio</th>
                                    <th style="color:#ffffff;">Cantidad</th>
                                    <th style="color:#ffffff;">Unidad</th>
                                    <th style="color:#ffffff;">Observaciones</th>
                                </tr>

                                {DETALLE_PRODUCTOS}

                                <!-- Ejemplo
                        <tr>
                            <td align="center">1</td>
                            <td>Computadora Portátil</td>
                            <td align="center">5</td>
                            <td align="center">PZA</td>
                            <td>Procesador i7, 16GB RAM</td>
                        </tr>
                        -->
                            </table>

                            <br>

                            Agradeceremos nos envíe su propuesta económica incluyendo:

                            <ul style="margin-top:10px;">
                                <li>Precio unitario.</li>
                                <li>Tiempo de entrega.</li>
                                <li>Condiciones de pago.</li>
                                <li>Vigencia de la cotización.</li>
                                <li>Costos de envío (si aplica).</li>
                            </ul>

                            Favor de responder este correo antes de la fecha indicada.

                            <br><br>

                            Gracias por su atención y apoyo.

                        </td>
                    </tr>

                    <!-- Firma -->
                    <tr>
                        <td bgcolor="#f7f7f7" style="padding:25px;border-top:1px solid #e0e0e0;">
                            <strong>{NOMBRE_COMPRADOR}</strong><br>
                            {PUESTO}<br>
                            {EMPRESA}<br>
                            Tel. {TELEFONO}<br>
                            Correo: {CORREO}
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
        ```

    </table>

</body>

</html>