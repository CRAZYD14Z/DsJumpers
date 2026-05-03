<?php
require '../vendor/autoload.php';
include_once '../api/functions.php'; 

if (isset($_FILES['upload_file'])) {
    $fileName    = "file_" . uniqid();
    $extension   = strtolower(pathinfo($_FILES['upload_file']['name'], PATHINFO_EXTENSION));
    $origen      = "tmp/" . $fileName . "." . $extension;

    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $origen)) {
        $destinot  = "tmp/thumbnail_" . $fileName . ".avif";
        $destino   = "tmp/" . $fileName . ".avif";
        $destinot2 = "tmp/thumbnail_" . $fileName . ".jpg";

        $imgOriginal = cargarImagen($origen);

        if ($imgOriginal) {
            generarThumbnailAVIF($imgOriginal, $destinot, 150);
            generarNormalAVIF($imgOriginal, $destino, 1200);
            generarThumbnailJPG($imgOriginal, $destinot2, 150);

            imagedestroy($imgOriginal);
            unlink($origen);
            echo $fileName . ".avif";
        } else {
            echo "Formato no soportado.";
        }
    }
    exit;
} else {
    echo "No files uploaded ...";
}
