<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Cobro</title>

    <!-- BOOTSTRAP CSS -->
    <!-- <link href="<?= assets(); ?>/plugins/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet" /> -->

    <!-- MDB -->
    <!-- <link href="<?= assets(); ?>/plugins/MDB5-3.9.0/css/mdb.min.css" rel="stylesheet" /> -->

    <!-- STYLE CSS -->
    <!-- <link href="<?= assets(); ?>/css/style.css" rel="stylesheet" /> -->

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            line-height: 1.4;
        }

        .border {
            border: solid 1px #ccc;
        }

        .border-bottom {
            border-bottom: 1px solid #ccc;
        }

        .pos-r {
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            font-size: 12px;
            font-weight: 600;
            padding: 6px 8px;
            text-transform: uppercase;
        }

        table td {
            font-size: 13px;
            font-weight: 400;
            padding: 6px 8px;
            line-height: 1.4;
        }

        .report-total {
            font-size: 15px;
            font-weight: 600;
        }

        .report-total-final {
            font-size: 16px;
            font-weight: 700;
        }

        .head-center {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .marco {
            border: solid 1px #ccc;
            padding: 0px 13px 8px 13px;
            border-radius: 5px;
            margin: 10px;
        }

        .marco-folio {
            border: solid 1px #333;
            padding: 10px 20px;
            border-radius: 5px;

        }

        .marco-observaciones {
            border: solid 1px #333;
            padding: 10px 20px;
            border-radius: 5px;

        }

        .text-start {
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .wd100 {
            width: 100%;
        }

        .wdMain {
            width: 93%;
        }

        .wd80 {
            width: 80%;
        }


        .wd50 {
            width: 50%;
        }

        .wd55 {
            width: 55%;
        }

        .wd60 {
            width: 60%;
        }

        .wd65 {
            width: 65%;
        }

        .wd20 {
            width: 20%;
        }

        .wd25 {
            width: 25%;
        }

        .wd20 {
            width: 20%;
        }

        .fs-9 {
            font-size: 9px;
        }

        .fs-10 {
            font-size: 10px;
        }

        .fs-11 {
            font-size: 11px;
        }

        .fs-12 {
            font-size: 12px;
        }

        .fs-13 {
            font-size: 13px;
        }

        .fs-14 {
            font-size: 14px;
        }

        .fs-15 {
            font-size: 15px;
        }

        .fs-16 {
            font-size: 16px;
        }

        .fs-17 {
            font-size: 17px;
        }

        .fs-18 {
            font-size: 18px;
        }

        .fs-19 {
            font-size: 19px;
        }

        .fs-20 {
            font-size: 20px;
        }

        .text-bold {
            font-weight: bold;
        }



        .mt-0 {
            margin-top: 0;
        }

        .mt-1 {
            margin-top: 4px;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-3 {
            margin-top: 16px;
        }

        .mt-4 {
            margin-top: 24px;
        }

        .mt-5 {
            margin-top: 48px;
        }


        .ms-0 {
            margin-left: 0;
        }

        .ms-1 {
            margin-left: 4px;
        }

        .ms-2 {
            margin-left: 8px;
        }

        .ms-3 {
            margin-left: 16px;
        }

        .ms-4 {
            margin-left: 24px;
        }

        .ms-5 {
            margin-left: 48px;
        }


        .me-0 {
            margin-right: 0;
        }

        .me-1 {
            margin-right: 4px;
        }

        .me-2 {
            margin-right: 8px;
        }

        .me-3 {
            margin-right: 16px;
        }

        .me-4 {
            margin-right: 24px;
        }

        .me-5 {
            margin-right: 48px;
        }


        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 16px;
        }

        .mb-4 {
            margin-bottom: 24px;
        }

        .mb-5 {
            margin-bottom: 48px;
        }


        .pt-0 {
            padding-top: 0;
        }

        .pt-1 {
            padding-top: 4px;
        }

        .pt-2 {
            padding-top: 8px;
        }

        .pt-3 {
            padding-top: 16px;
        }

        .pt-4 {
            padding-top: 24px;
        }

        .pt-5 {
            padding-top: 48px;
        }

        .pe-0 {
            padding-right: 0;
        }

        .pe-1 {
            padding-right: 4px;
        }

        .pe-2 {
            padding-right: 8px;
        }

        .pe-3 {
            padding-right: 16px;
        }

        .pe-4 {
            padding-right: 24px;
        }

        .pe-5 {
            padding-right: 48px;
        }

        .pb-0 {
            padding-bottom: 0;
        }

        .pb-1 {
            padding-bottom: 4px;
        }

        .pb-2 {
            padding-bottom: 8px;
        }

        .pb-3 {
            padding-bottom: 16px;
        }

        .pb-4 {
            padding-bottom: 24px;
        }

        .pb-5 {
            padding-bottom: 48px;
        }

        .ps-0 {
            padding-left: 0;
        }

        .ps-1 {
            padding-left: 4px;
        }

        .ps-2 {
            padding-left: 8px;
        }

        .ps-3 {
            padding-left: 16px;
        }

        .ps-4 {
            padding-left: 24px;
        }

        .ps-5 {
            padding-left: 48px;

        }

        table,
        th,
        td {
            /* border: 1px solid #ccc;
            border-collapse: collapse; */

        }
    </style>

</head>

<body>

    <!-- border: solid 1px #ccc;  border-radius: 5px;-->
    <div class="pos-r" style="width: 89%;   
            padding: 0px 13px 0px 13px;
                       margin: 6px 16px 0px 4px; max-height:50%;">

        <!-- subrayado fecha -->
        <span style="position: absolute; top: 154px; left: 200px;">______</span>
        <span style="position: absolute; top: 154px; left: 285px;">______________</span>
        <span style="position: absolute; top: 154px; left: 440px;">_______</span>
        <!-- subrayado fecha  -->

        <!-- nombre residente -->
        <span style="position: absolute; top: 220px; left: 15px;">___________________________________________________________________</span>
        <!-- nombre residente -->

        <!-- domicilio -->
        <span style="position: absolute; top: 254px; left: 190px;">____________________________</span>
        <span style="position: absolute; top: 254px; left: 435px;">________</span>
        <span style="position: absolute; top: 254px; left: 545px;">________</span>
        <span style="position: absolute; top: 254px; left: 655px;">______</span>
        <!-- domicilio -->


        <!-- importe-->
        <!-- <span style="position: absolute; top: 291px; left: 155px;">_____________</span> -->
        <!-- importe -->


        <!-- importe con letra-->
        <!-- <span style="position: absolute; top: 330px; left: 195px;">______________________________________</span> -->
        <!-- importe con letra -->



        <!-- Encabezado -->
        <table class="mt-0">
            <tbody>
                <tr>
                    <td class="text-start wd50 fs-18 text-bold">COMPROBANTE DE REGISTRO DE RECIBO ANTERIOR</td>
                    <td class="text-end wd50 fs-18 text-bold">FOLIO DE REGISTRO: <span style="color:red;">No.</span></td>
                </tr>
            </tbody>
        </table>
        <table class="mt-2">
            <tbody>
                <tr>
                    <td class="wd25 text-start" style="padding-left: 30px;"> <img src="<?= assets(); ?>/images/brand/logo_main.png" alt="logo" style="width: 80px;"> </td>
                    <td class="wd50 text-center fs-14">
                        <span class="text-bold">Fraccionamiento Los Amores De Don Juan Téllez AC</span>
                        <span>Avenida Victoria S/N, Col. Jagüey de Téllez, Mpio.</span>
                        <span>Zempoala Hidalgo, México. C.P. 43845</span>
                    </td>
                    <td class="wd25" style="color:red;">
                        <table class="" style="margin-left: 78px;">
                            <tbody>
                                <tr>
                                    <td class="marco-folio">
                                        <span class="text-bold fs-16"><?= $data['folio']; ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Fin Encabezado -->

        <!-- fecha, residente, qr domicilio -->
        <table class="mt-2">
            <tbody>

                <tr>
                    <td class="wd80">
                        <table class="">
                            <tbody>
                                <tr>
                                    <td class="fs-16 wd100 pb-3">
                                        <span>FECHA DE REGISTRO:</span>
                                        <span style="color:blue; font-weight:bold; margin-left: 20px; margin-right: 20px;"><?= formatDate_Dia($data['created_at']); ?></span>
                                        <span style="margin-left: 20px; margin-right: 20px;">DE</span>
                                        <span style="color:blue; font-weight:bold; margin-left: 20px; margin-right: 20px;"><?= formatDate_Mes($data['created_at']); ?></span>
                                        <span style="margin-left: 20px; margin-right: 20px;">DEL</span>
                                        <span style="color:blue; font-weight:bold; margin-left: 20px; margin-right: 20px;"><?= formatDate_Anio($data['created_at']); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fs-16 wd100 pb-2">
                                        <span>DATOS DE RESIDENTE:</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fs-16 wd100">
                                        <span style="color:blue; font-weight:bold; margin-left: 20px; margin-right: 20px;"><?= $data['residente']; ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="wd20" align="center">
                        <img class="ms-4" src="<?= assets(); ?>/files/temp/qr.png" alt="logo" style="width: 80px;">
                    </td>
                </tr>

            </tbody>

        </table>
        <!-- Fin fecha, residente, qr domicilio -->

        <!-- domicilio -->
        <table class="mt-2">
            <tbody>
                <tr>
                    <td style="width:50%;" class="fs-16 pb-2">
                        <span>DOMICILIO EN CALLE:</span>
                        <span style="color:blue; font-weight:bold; margin-left: 15px;"><?= $data['calle']; ?></span>
                    </td>
                    <td style="width:15%;" class="fs-16 pb-2" align="center">
                        <span>MZ.</span>
                        <span style="color:blue; font-weight:bold; margin-left: 15px;"><?= $data['mza']; ?></span>
                    </td>
                    <td style="width:15%;" class="fs-16 pb-2" align="center">
                        <span>LT.</span>
                        <span style="color:blue; font-weight:bold; margin-left: 15px;"><?= $data['lote']; ?></span>
                    </td>
                    <td style="width:20%;" class="fs-16 pb-2" align="center">
                        <span>NUM.</span>
                        <span style="color:blue; font-weight:bold; margin-right: 10px;"><?= $data['numero']; ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- domicilio -->


        <!-- cantidad -->
        <!-- <table class="mt-2">
            <tbody>
                <tr>
                    <td class="wd100 fs-16 pb-2">
                        <span>LA CANTIDAD DE:</span>
                        <span style="color:blue; font-weight:bold; margin-left: 20px;"><?= formatMoney($data['importe']); ?></span>
                        <span style="margin-left: 15px;">PESOS.</span>
                    </td>
                </tr>
            </tbody>
        </table> -->
        <!-- cantidad -->


        <!-- Importe con letra -->
        <!-- <table class="mt-2">
            <tbody>
                <tr>
                    <td class="wd100 fs-16 pb-2">
                        <span>IMPORTE CON LETRA:</span>
                        <span style="color:blue; font-weight:bold; margin-left: 20px;">
                            <?= $data['importe_letra']; ?>
                        </span>
                        <span style="margin-left: 20px;"> M.N.</span>
                    </td>
                </tr>
            </tbody>
        </table> -->
        <!-- Importe con letra -->

        <!-- Concepto -->
        <table class="mt-2">
            <tbody>
                <tr>
                    <td class="wd100 fs-16 pb-2">
                        <span>FOLIO(S) RECIBO(S) ANTERIOR(ES):</span>
                        <span style="color:blue; font-weight:bold; margin-left: 2px;"><?= $data['folio_anterior']; ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Concepto -->


        <!-- Concepto -->
        <table class="mt-2">
            <tbody>
                <tr>
                    <td class="wd100 fs-16 pb-2">
                        <span>MESES QUE AMPARA EL COMPROBANTE:</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Concepto -->

        <!-- Concepto Descripcion -->
        <table class="marco-observaciones" style="border-color: #333;">
            <tbody>
                <tr>
                    <td class="wd100 fs-16 pb-4">
                        <span style="color:blue; font-weight:bold; margin-left: 2px;"><?= $data['concepto']; ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Concepto Descripcion -->

        <table class="mt-1">
            <tbody>
                <tr>
                    <td class="wd50 fs-16" align="left">
                        <span style="font-weight:bold; margin-right: 5px;">REGISTRÓ:</span>
                        <span style="margin-right: auto;"><?= $data['usuario']; ?></span>
                    </td>
                    <td class="wd50 fs-9" align="right">
                        <p>Comprobante NO Valido sin firma y sello de la Administración.</p>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>


</body>

</html>