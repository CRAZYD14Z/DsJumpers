<?php
ob_start();
session_start(); 
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();


$merchantId = id_OPAY;
$privateKey = sk_OPAY; // REEMPLAZA CON TU LLAVE PRIVADA (sk_...)

$base_url = "https://sandbox-api.openpay.mx/v1/$merchantId/checkouts"; // Sandbox

// Datos del pago
$montoLiquidar = 800.00;
$orderId = 'LIQ-' . time();

$data = [
    'amount' => (float)$montoLiquidar,
    'currency' => 'MXN',
    'description' => 'Liquidación de saldo pendiente - DsJumpers',
    'order_id' => $orderId,
    'send_email' => false,
    'customer' => [
        'name' => 'Juan',
        'last_name' => 'Perez',
        'email' => 'juan.perez@ejemplo.com',
        'phone_number' => '5512345678'
    ],    
    'expiration_date' => date('Y-m-d H:i', strtotime('+3 days')),
    'redirect_url' => 'http://localhost/DsJumpers/gracias.php'
];

// Configuración de CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ":"); // La llave privada va aquí
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response);

if ($httpCode == 201 || $httpCode == 200) {
    // ÉXITO
    $urlPago = $result->checkout_link;
    echo "<h3>Link de Pago Generado Directamente via API</h3>";
    echo "URL: <a href='$urlPago' target='_blank'>$urlPago</a>";
} else {
    // ERROR
    echo "<h3>Error al generar el link</h3>";
    echo "Código HTTP: $httpCode <br>";
    echo "Descripción: " . ($result->description ?? 'Error desconocido');
}