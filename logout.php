<?php
session_start();
$URL = "Location: ".$_SESSION['company']."/login";
session_destroy(); // Borra todos los datos de la sesión
header($URL); // Redirige al login
exit();
?>