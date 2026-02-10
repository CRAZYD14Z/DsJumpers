<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function enviarContratoPorEmail($datosGenerales, $productos, $emailDestino) {
    // 1. Configurar Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true); // Vital para cargar el LOGO desde la URL
    $options->set('defaultFont', 'Helvetica');
    $dompdf = new Dompdf($options);

    // 2. Cargar el HTML (el código minimalista que diseñamos)
    $html = file_get_contents('plantilla_contrato.html');

    // 3. Reemplazar datos generales
    foreach ($datosGenerales as $key => $value) {
        $html = str_replace("*$key*", $value, $html);
    }

    // 4. Manejar múltiples productos (Lógica de filas)
    $filasProductos = "";
    foreach ($productos as $item) {
        $filasProductos .= "
        <tr style='border-bottom: 1px solid #eee;'>
            <td style='padding: 12px 10px;'>
                <div style='font-weight: 500;'>{$item['rentalname']}</div>
                <div style='font-size: 11px; color: #777;'>{$item['fullrentaltime']}</div>
            </td>
            <td style='padding: 12px 10px; text-align: center;'>{$item['rentalqty']}</td>
            <td style='padding: 12px 10px; text-align: right; font-weight: 600;'>\${$item['rentaltotalprice']}</td>
        </tr>";
    }
    
    // Reemplazamos el marcador de posición que pongas en tu HTML para la lista
    //$html = str_replace("", $filasProductos, $html);

    // 5. Renderizar PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    // 6. Obtener el output del PDF
    $pdfOutput = $dompdf->output();
    file_put_contents("Contrato_Nro_105.pdf", $pdfOutput);
    // 7. Enviar por correo (Ejemplo usando PHPMailer)
//    $mail = new PHPMailer\PHPMailer\PHPMailer();
    // ... configurar SMTP ...
//    $mail->addAddress($emailDestino);
//    $mail->Subject = "Tu Contrato de DsJumpers - #" . $datosGenerales['leadid'];
//    $mail->Body    = "Adjunto encontrarás el contrato para tu evento.";
    
    // Adjuntar el PDF generado en memoria
//    $mail->addStringAttachment($pdfOutput, 'Contrato_DsJumpers.pdf');

//    return $mail->send();
}


$datosGenerales = [
        'leadid' => "90210",
        'contractsentdate' => "",
        'company_name' =>  "DsJumpers LLC",
        'company_address' => "",
        'company_phone' => "",
        'organization' => "",
        'ctfirstname' => "",
        'ctlastname' => "",
        'eventstreet' => "",
        'eventcity' => "",
        'eventstate' => "",
        'eventzip' => "",
        'phones' => "",
        'startdate' => "",
        'starttime' => "",
        'enddate' => "",
        'endtime' => "",
        'deliverytype' => "",

        'subtotal' =>  "450.00",
        'taxrate' =>  "36.00",
        'salestax' =>  "36.00",
        'total' =>  "486.00",
        'ctr_balance_due' =>  "100.00",

        'electric' => "",
        'signature' => "",
        'signeddate' => ""    

];

// 2. Productos (Lista de items rentados)
$productos = [
    [
        'rentalname'        => 'Bounce House 15x15',
        'fullrentaltime'    => 'Full Day Rental',
        'rentalqty'         => '1',
        'rentaltotalprice'  => '150.00'
    ],
    [
        'rentalname'        => 'Cotton Candy Machine',
        'fullrentaltime'    => 'Includes supplies for 50',
        'rentalqty'         => '1',
        'rentaltotalprice'  => '65.00'
    ],
    [
        'rentalname'        => 'Folding Table',
        'fullrentaltime'    => 'Standard 6ft',
        'rentalqty'         => '5',
        'rentaltotalprice'  => '35.00'
    ]
];

// 3. Llamada a la función pasándole las variables
$emailCliente = 'cliente@ejemplo.com';
$resultado = enviarContratoPorEmail($datosGenerales, $productos, $emailCliente);
?>