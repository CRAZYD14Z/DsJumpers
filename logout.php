<?php
session_start();

// 1. Detectar entorno y configurar rutas bases
$is_local = ($_SERVER['SERVER_NAME'] == 'localhost');
$cookie_path = $is_local ? '/DsJumpers/' : '/';
$base_url = $is_local ? '/DsJumpers/' : '/';
$is_secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

// 2. Recuperar la empresa antes de destruir la cookie
$company = $_COOKIE['saved_company'] ?? '';

// 3. Borrar la cookie usando EXACTAMENTE los mismos parámetros con los que fue creada
if (isset($_COOKIE['saved_company'])) {
    setcookie("saved_company", "", [
        'expires'  => time() - 3600,
        'path'     => $cookie_path,
        'domain'   => '',
        'secure'   => $is_secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// 4. Destruir la sesión de PHP
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params["path"],
        'domain'   => $params["domain"],
        'secure'   => $params["secure"],
        'httponly' => $params["httponly"],
        'samesite' => $params["samesite"] ?? 'Lax'
    ]);
}
session_destroy();

// 5. Redirección
if (!empty($company)) {
    header("Location: " . $base_url . $company . "/login");
} else {
    header("Location: " . $base_url . "error_instancia.php");
}
exit();
?>