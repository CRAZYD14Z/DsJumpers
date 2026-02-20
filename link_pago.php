<?php
require_once 'vendor/autoload.php';

use Openpay\Data\Openpay;

// 1. Configuración (4 parámetros obligatorios)
$merchantId = 'mles9ufd4m3rlilw00i8';
$privateKey = 'sk_ab545fdf98b446e78ed7ef908d1687a2'; // REEMPLAZA CON TU LLAVE PRIVADA (sk_...)
$countryCode = 'MX';
$clientIp = $_SERVER['REMOTE_ADDR'];
$isSandbox = true;

try {
    $openpay = Openpay::getInstance($merchantId, $privateKey, $countryCode, $clientIp);
    Openpay::setProductionMode(false);

    // 2. Definir el monto a liquidar (Ejemplo: Saldo del 80%)
    $montoTotal = 1000.00;
    $anticipoPagado = 200.00;
    $saldoLiquidar = $montoTotal - $anticipoPagado;

    // 3. Configurar el Checkout (Link de Pago)
    $checkoutData = [
        'amount' => (float)$saldoLiquidar,
        'currency' => 'MXN',
        'description' => 'Liquidación de saldo pendiente - Orden #12345',
        'order_id' => 'ORD-' . time(), // ID único de tu sistema
        'send_email' => true,         // Openpay enviará el link por correo si incluyes el customer
        'customer' => [
            'name' => 'Juan',
            'last_name' => 'Perez',
            'email' => 'juan.perez@ejemplo.com',
            'phone_number' => '5512345678'
        ],
        'expiration_date' => date('Y-m-d\TH:i:s', strtotime('+3 days')), // Expira en 3 días
        'redirect_url' => 'https://tu-sitio.com/pago-finalizado',         // A donde regresa el cliente
    ];

    // 4. Crear el checkout
    $checkout = $openpay->checkouts->create($checkoutData);

    // 5. Obtener la URL para enviar al cliente
    $urlPago = $checkout->checkout_link;

    echo "<h3>Link de Liquidación Generado</h3>";
    echo "Monto a cobrar: $" . number_format($saldoLiquidar, 2) . " MXN<br>";
    echo "Envía este link a tu cliente: <a href='$urlPago' target='_blank'>$urlPago</a>";

} catch (Exception $e) {
    echo "Error al generar el link: " . $e->getMessage();
}