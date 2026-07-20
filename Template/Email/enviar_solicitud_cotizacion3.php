<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <title>Solicitud de Cotización</title>
</head>

<body style="margin:0;padding:0;background:#eef2f7;font-family:'Segoe UI',Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#eef2f7">
        <tr>
            <td align="center" style="padding:30px 15px;">

                ```
                <!-- Contenedor Principal -->
                <table width="800" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:800px;background:#ffffff;border-radius:10px;overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background:#0a6ed1;padding:25px 35px;">
                            <table width="100%">
                                <tr>
                                    <td>

                                        <img style="width: 125px; height: auto;" src="https://lfmcontrol.com.mx/Assets/img/lfmcontrol/logo_email_lfm_blanco.png" alt="logo">

                                        <div style="font-size:26px;font-weight:600;color:#ffffff;">
                                            Solicitud de Cotización
                                        </div>
                                        <div style="font-size:14px;color:#d9ecff;margin-top:5px;">
                                            Módulo de Gestión de Compras
                                        </div>
                                    </td>
                                    <td align="right">
                                        <div style="font-size:13px;color:#ffffff;">
                                            Folio
                                        </div>
                                        <div style="font-size:22px;font-weight:bold;color:#ffffff;">
                                            {FOLIO}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Mensaje -->
                    <tr>
                        <td style="padding:30px 35px 10px 35px;color:#3b3b3b;">

                            <p style="margin-top:0;">
                                Estimado proveedor:
                            </p>

                            <p>
                                Le invitamos a participar en el siguiente proceso de cotización.
                                Agradeceremos nos comparta su propuesta económica considerando las especificaciones indicadas.
                            </p>

                        </td>
                    </tr>

                    <!-- Tarjetas Resumen -->
                    <tr>
                        <td style="padding:0 35px 25px 35px;">

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>

                                    <td width="25%" style="padding-right:10px;">
                                        <table width="100%" bgcolor="#f7f9fc"
                                            style="border-left:4px solid #0a6ed1;">
                                            <tr>
                                                <td style="padding:15px;">
                                                    <div style="font-size:11px;color:#777;">
                                                        FECHA SOLICITUD:
                                                    </div>
                                                    <div style="font-size:15px;font-weight:600;">
                                                        {FECHA}
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>


                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Tabla -->
                    <tr>
                        <td style="padding:0 35px 20px 35px;">

                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="border:1px solid #dfe3e8;border-collapse:collapse;">

                                <tr bgcolor="#f4f6f8">
                                    <th style="padding:12px;border-bottom:1px solid #dfe3e8;">#</th>
                                    <th style="padding:12px;border-bottom:1px solid #dfe3e8;" align="left">
                                        Descripción
                                    </th>
                                    <th style="padding:12px;border-bottom:1px solid #dfe3e8;">
                                        Cantidad
                                    </th>
                                    <th style="padding:12px;border-bottom:1px solid #dfe3e8;">
                                        Unidad
                                    </th>
                                    <th style="padding:12px;border-bottom:1px solid #dfe3e8;" align="left">
                                        Especificaciones
                                    </th>
                                </tr>

                                {DETALLE_PRODUCTOS}

                            </table>

                        </td>
                    </tr>

                    <!-- Requisitos -->
                    <tr>
                        <td style="padding:0 35px 25px 35px;">

                            <table width="100%" bgcolor="#f8fbff"
                                style="border-left:4px solid #0a6ed1;">
                                <tr>
                                    <td style="padding:20px;">

                                        <div style="font-size:16px;font-weight:600;margin-bottom:10px;">
                                            Información requerida en su propuesta
                                        </div>

                                        <ul style="margin:0;padding-left:20px;color:#555;">
                                            <li>Precio unitario y total.</li>
                                            <li>Tiempo estimado de entrega.</li>
                                            <li>Condiciones de pago.</li>
                                            <li>Vigencia de la cotización.</li>
                                            <li>Garantías aplicables.</li>
                                        </ul>

                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Botón -->
                    <tr>
                        <td align="center" style="padding:10px 35px 30px 35px;">

                            <a href="mailto:{CORREO_COMPRADOR}"
                                style="background:#0a6ed1;
                      color:#ffffff;
                      text-decoration:none;
                      padding:14px 35px;
                      border-radius:4px;
                      display:inline-block;
                      font-weight:600;">
                                Responder Cotización
                            </a>

                        </td>
                    </tr>

                    <tr>

                        <td style="padding:30px 35px 10px 35px;color:#3b3b3b;">

                            <div style="border-top:1px solid #e5e5e5; margin-top:12px; margin-bottom:10px;"></div>

                            <table cellpadding="0" cellspacing="0" border="0" style="font-family:Calibri, Arial, sans-serif; color:#444; border:1px solid #dcdcdc; border-radius:2px; box-shadow:0 2px 10px rgba(0,0,0,0.08); background:#ffffff; padding:18px;">
                                <tr> <!-- Logo -->
                                    <td style="vertical-align:top; padding-right:20px;"> <img src="https://lfmcontrol.com.mx/Assets/img/lfmcontrol/logo_email_lfm.png" width="150" alt="Logo" style="display:block;"> </td>
                                    <td style="border-left:3px solid #00809F; padding-left:15px; vertical-align:top;">
                                        <div style="font-size:20px; font-weight:bold; color:#1f1f1f; line-height:22px;"> C_USUARIO_ENVIA </div>
                                        <div style="font-size:13px; color: #666; padding-top:2px;"> C_PUESTO_ENVIA </div>
                                        <div style="font-size:13px; color: #00809F; padding-top:8px; font-weight:bold;"> LFM Control de Fluidos </div>
                                        <table cellpadding="0" cellspacing="0" border="0" style="margin-top:10px; font-size:13px; line-height:22px;">
                                            <tr>
                                                <td width="75" style="color:#666;"> Celular: </td>
                                                <td> <a href="tel:+52C_TEL_CELULAR_ENVIA" style="color:#444; text-decoration:none;"> C_TEL_CELULAR_ENVIA </a> </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666;"> Oficina: </td>
                                                <td> <a href="tel:+52C_TEL_OFICINA" style="color:#444; text-decoration:none;"> C_TEL_OFICINA </a> </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666;"> Correo: </td>
                                                <td> <a href="mailto:C_CORREO_ENVIA" style="color: #00809F; text-decoration:none;"> C_CORREO_ENVIA </a> </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666;"> Web: </td>
                                                <td> <a target="_blank" href="https://lfmcontrol.com.mx/" style="color: #00809F; text-decoration:none;"> lfmcontrol.com.mx </a> </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <p style='font-size:11px; color: #7f8c8d;'>La información contenida en este correo electrónico, así como los anexos que se adjuntan a la misma es confidencial.
                                El contenido del presente comunicado es propiedad de LFM CONTROL DE FLUIDOS S. DE R.L. DE C.V. y es considerado como secreto profesional, y es para uso exclusivo de la persona a quien se dirige.
                                El uso, difusión, o copia de todo o parte del presente comunicado queda estrictamente prohibido y puede ser ilegal.
                                Si usted ha recibido este mensaje por error, favor de notificar inmediatamente al remitente devolviendo el correo electrónico, y destruya la presente comunicación,
                                cualquier copia realizada a la misma y, en su caso, los anexos adjuntos.
                                LFM CONTROL DE FLUIDOS S. DE R.L. DE C.V., bajo ninguna circunstancia formalizará sus compromisos por medio de un contrato oral (total o parcialmente) y en general
                                no estará sujeta a ningún contrato excepto que, y hasta que, un contrato conteniendo todos los términos negociados sea
                                firmado por una persona debidamente apoderada de LFM CONTROL DE FLUIDOS S. DE R.L. DE C.V.
                            </p>



                        </td>


                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background:#2f3c48;padding:25px;color:#ffffff;">

                            <div style=' text-align:center; padding:15px; font-size:12px; color:#c59d5f;'>
                                <span>©LFM Control de Fluidos - Todos los derechos reservados.</span><br>
                                <span>Desarrollado por ©HistoMedic.</span>
                            </div>

                            <!-- <div style="font-size:16px;font-weight:600;">
                                {EMPRESA}
                            </div>

                            <div style="margin-top:10px;font-size:13px;color:#d8d8d8;">
                                Solicitado por: {NOMBRE_USUARIO}<br>
                                Correo: {CORREO}<br>
                                Teléfono: {TELEFONO}
                            </div> -->

                        </td>
                    </tr>

                </table>
                ```

            </td>
        </tr>
    </table>

</body>

</html>