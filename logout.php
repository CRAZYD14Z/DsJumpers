<?php
session_start();

// 1. Detectar entorno y configurar rutas bases
$is_local = ($_SERVER['SERVER_NAME'] == 'localhost');
$cookie_path = $is_local ? '/DsJumpers/' : '/';
$base_url = $is_local ? '/DsJumpers/' : '/';

// 2. Recuperamos la empresa desde la cookie
$company = isset($_COOKIE['saved_company']) ? $_COOKIE['saved_company'] : '';

// 3. Borramos la cookie usando el mismo path con el que se creó
if (isset($_COOKIE['saved_company'])) {
    setcookie("saved_company", "", time() - 3600, $cookie_path, "", !$is_local, true);
}

// 4. Destruimos la sesión de PHP
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 5. Redirección inteligente usando la base del entorno
if (!empty($company)) {
    header("Location: " . $base_url . $company . "/login");
} else {
    header("Location: " . $base_url . "error_instancia.php");
}
exit();
?>