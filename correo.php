<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



function enviarEmail($config, $destinatario, $asunto, $mensaje, $adjuntos = []) {
    $mail = new PHPMailer(true);

    try {
        // --- Configuración del Servidor ---
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = $config['encryption']; // 'tls' o 'ssl'
        $mail->Port       = $config['port'];
        $mail->CharSet    = 'UTF-8';
        //$mail->SMTPDebug = 2;

        // --- Remitente y Destinatario ---
        $mail->setFrom($config['username'], $config['nombre_remitente']);
        $mail->addAddress($destinatario);

        // --- Adjuntos ---
        if (!empty($adjuntos)) {
            foreach ($adjuntos as $ruta) {
                if (file_exists($ruta)) {
                    $mail->addAttachment($ruta);
                }
            }
        }

        // --- Contenido ---
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;
        $mail->AltBody = strip_tags($mensaje); // Versión en texto plano

        $mail->send();
        return ["status" => true, "message" => "Correo enviado con éxito."];

    } catch (Exception $e) {
        return ["status" => false, "message" => "Error al enviar: {$mail->ErrorInfo}"];
    }
}


$datosConexion = [
    'host'             => 'shared99.accountservergroup.com',
    'username'         => 'contacto@gaxybrincolines.com',
    'password'         => 'S33MAvj[snaw',
    'port'             => 465,
    'encryption'       => PHPMailer::ENCRYPTION_SMTPS,
    'nombre_remitente' => 'Mi Empresa'
];

$archivos = [
    '550e8400-e29b-41d4-a716-446655440000.pdf'
];

$resultado = enviarEmail(
    $datosConexion, 
    'jdiaz_huerta@hotmail.com', 
    'Asunto de Prueba', 
    '<h1>Hola!</h1><p>Este es un correo con adjuntos.</p>', 
    $archivos
);

if ($resultado['status']) {
    echo "¡Listo!";
} else {
    echo "Fallo: " . $resultado['message'];
}

?>