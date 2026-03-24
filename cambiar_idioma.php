<?php
ob_start();
session_start(); 
if (isset($_POST['lang'])) {
    $nuevo_idioma = $_POST['lang'];
    if (in_array($nuevo_idioma, ['es', 'en'])) {
        $_SESSION['Idioma'] = $nuevo_idioma;
        echo json_encode(['status' => 'success']);
        exit;
    }
}
echo json_encode(['status' => 'error']);
?>