<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function enviarEmail($config, $destinatario, $asunto, $mensaje, $adjuntos = [],$contenidoBinario, $nombreArchivo) {
    
    $mail = new PHPMailer(true);
    

    try {
        // --- Configuración del Servidor ---
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        //$mail->SMTPSecure = $config['encryption']; // 'tls' o 'ssl'
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['port'];
        $mail->CharSet    = 'UTF-8';

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
        if ($nombreArchivo != "")
            $mail->addStringAttachment($contenidoBinario, $nombreArchivo);

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

function generarHtmlCotizacion($html, $datos) {
    $busqueda = array_map(function($key) {
        return '*' . $key . '*';
    }, array_keys($datos));
    $sustitucion = array_values($datos);
    return str_replace($busqueda, $sustitucion, $html);
}

function dividirNombreCompleto($nombreCompleto) {
    // Limpiamos espacios extras
    $nombreCompleto = trim($nombreCompleto);
    $partes = explode(' ', $nombreCompleto);
    $numPartes = count($partes);

    $resultado = [
        'nombres' => '',
        'apellido_paterno' => '',
        'apellido_materno' => ''
    ];

    switch ($numPartes) {
        case 1:
            $resultado['nombres'] = $partes[0];
            break;
        case 2:
            $resultado['nombres'] = $partes[0];
            $resultado['apellido_paterno'] = $partes[1];
            break;
        case 3:
            $resultado['nombres'] = $partes[0];
            $resultado['apellido_paterno'] = $partes[1];
            $resultado['apellido_materno'] = $partes[2];
            break;
        default:
            // Para 4 o más partes, asumimos que los dos últimos son apellidos
            // y todo lo anterior son nombres.
            $resultado['apellido_materno'] = array_pop($partes);
            $resultado['apellido_paterno'] = array_pop($partes);
            $resultado['nombres'] = implode(' ', $partes);
            break;
    }

    return $resultado;
}

?>