<?php
ob_start();
session_start(); 
include_once 'config/config.php'; 
include_once 'config/database.php'; 
require 'idioma.php'; 
header('Content-Type: application/json');

$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($usuario) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => $texts['error_vacios']]);
    exit;
}

   $database = new Database();
    $db = $database->getConnection();

$stmt = $db->prepare("SELECT id, usuario, password, nombre FROM usuarios WHERE usuario = ?");
$stmt->execute([$usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_nombre'] = $user['nombre'];
    echo json_encode(['status' => 'success', 'message' => $texts['exito']]);
} else {
    echo json_encode(['status' => 'error', 'message' => $texts['error_login']]);
}
?>