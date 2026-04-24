<?php
//require_once ("../sesion.php");
//require_once ("../../db.php");
if (isset($_FILES['upload_file'])) {    


    $archivo = $_FILES["upload_file"]["tmp_name"]; 
    $tamanio = $_FILES["upload_file"]["size"];
    $tipo    = $_FILES["upload_file"]["type"];
    $nombre  = $_FILES["upload_file"]["name"];
    //$titulo  = $_POST["titulo"];    

    $fileName = "file_".uniqid();
    $partes_ruta = pathinfo("tmp/" . $_FILES['upload_file']['name']);
    if(move_uploaded_file($_FILES['upload_file']['tmp_name'], "tmp/" .$fileName.".".$partes_ruta['extension'])){

        $origen = "tmp/" .$fileName.".".$partes_ruta['extension'];
        $destinot = "tmp/thumbnail_" .$fileName.".avif";
        $destino = "tmp/" .$fileName.".avif";
        $destinot2 = "tmp/thumbnail_" .$fileName.".jpg";
        
        if(procesarImagenAVIF($origen, $destinot, $destino,$destinot2,150))
            echo $fileName.".avif";
        else
            echo $origen;

    }
    exit;
} else {
    echo "No files uploaded ...";
}   

function procesarImagenAVIF($rutaOrigen, $rutaDestinoMini, $rutaDestinoNormal, $rutaDestinoJpg, $anchoMini = 300, $anchoNormal = 1200) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipoMime = $finfo->file($rutaOrigen);

    // Cargar imagen original
    switch ($tipoMime) {
        case 'image/jpeg': $imgOriginal = imagecreatefromjpeg($rutaOrigen); break;
        case 'image/png':  $imgOriginal = imagecreatefrompng($rutaOrigen); break;
        case 'image/webp': $imgOriginal = imagecreatefromwebp($rutaOrigen); break;
        default: return false;
    }

    $anchoOriginal = imagesx($imgOriginal);
    $altoOriginal = imagesy($imgOriginal);

    // Función modificada para guardar en AVIF o JPG según el caso
    $redimensionarYGuardar = function($anchoDestino, $rutaSalida, $esJpg = false) use ($imgOriginal, $anchoOriginal, $altoOriginal, $tipoMime) {
        $altoDestino = ($anchoDestino / $anchoOriginal) * $altoOriginal;
        $lienzo = imagecreatetruecolor($anchoDestino, $altoDestino);

        // Si es JPG, rellenar fondo blanco (porque el JPG no soporta transparencia)
        if ($esJpg) {
            $fondoBlanco = imagecolorallocate($lienzo, 255, 255, 255);
            imagefill($lienzo, 0, 0, $fondoBlanco);
        } elseif ($tipoMime == 'image/png') {
            imagealphablending($lienzo, false);
            imagesavealpha($lienzo, true);
        }

        imagecopyresampled($lienzo, $imgOriginal, 0, 0, 0, 0, $anchoDestino, $altoDestino, $anchoOriginal, $altoOriginal);
        
        // Guardar según formato
        $exito = $esJpg ? imagejpeg($lienzo, $rutaSalida, 70) : imageavif($lienzo, $rutaSalida, 70);
        
        imagedestroy($lienzo);
        return $exito;
    };

    // 1. Generar Mini (AVIF)
    $redimensionarYGuardar($anchoMini, $rutaDestinoMini, false);
    
    // 2. Generar Normal (AVIF)
    $redimensionarYGuardar($anchoNormal, $rutaDestinoNormal, false);
    
    // 3. Generar Versión PDF (JPG Normal) - Usamos el mismo ancho que 'Normal'
    $redimensionarYGuardar($anchoNormal, $rutaDestinoJpg, true);

    imagedestroy($imgOriginal);
    unlink($rutaOrigen);

    return true;
}
?>