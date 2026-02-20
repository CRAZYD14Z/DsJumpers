<?php
require_once 'vendor/autoload.php';

use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApiTransactionError;
use Openpay\Data\OpenpayApiRequestError;
use Openpay\Data\OpenpayApiConnectionError;
use Openpay\Data\OpenpayApiAuthError;


// 1. Configuración de credenciales (Asegúrate de usar las tuyas)
$merchantId = 'mles9ufd4m3rlilw00i8';
$privateKey = 'sk_ab545fdf98b446e78ed7ef908d1687a2'; // REEMPLAZA CON TU LLAVE PRIVADA (sk_...)
$countryCode = 'MX';
$clientIp = $_SERVER['REMOTE_ADDR'];
$isSandbox = true;

try {$openpay = Openpay::getInstance($merchantId, $privateKey,$countryCode,$clientIp);
    Openpay::setProductionMode(!$isSandbox);

    // 2. Recibir datos del formulario
    $tokenId    = $_POST['token_id'] ?? null;
    $token      = $_POST['token'] ?? null;
    $deviceId   = $_POST['deviceIdHiddenFieldName'] ?? null;
    $amount     = $_POST['amount'] ?? 0;
    
    // Datos del cliente
    $customerData = [
        'name' => $_POST['name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone_number' => $_POST['phone'] ?? '5500000000'
    ];

    if (!$tokenId || !$deviceId) {
        throw new Exception("Faltan identificadores de seguridad (Token/Device ID).");
    }

    // 3. Preparar el objeto del cargo
    $chargeRequest = [
        'method' => 'card',
        'source_id' => $tokenId,
        'amount' => (float)$amount,
        'currency' => 'MXN',
        'description' => 'Pago de anticipo/servicio - ' . $customerData['name'],
        'device_session_id' => $deviceId, // Vital para el sistema antifraude
        'customer' => $customerData,
        // Si quieres habilitar 3D Secure para mayor seguridad:
        // 'use_3d_secure' => true,
        // 'redirect_url' => 'https://tu-sitio.com/pago-completado',
    ];

    // 4. Realizar el cargo
    $charge = $openpay->charges->create($chargeRequest);

    // 5. Respuesta según el estado del pago
    if ($charge->status == 'completed') {
        echo json_encode([
            'status' => 'success',
            'message' => '¡Pago realizado con éxito!',
            'transaction_id' => $charge->id,
            'url' => 'successpayment.php?Id='.$token
        ]);
    } else {
        // En caso de pagos pendientes (como 3D Secure)
        echo json_encode([
            'status' => 'pending',
            'url' => $charge->payment_method->url
        ]);
    }

} catch (OpenpayApiTransactionError $e) {
    // Errores específicos de la transacción (ej. fondos insuficientes)
    http_response_code(402);
    echo json_encode([
        'status' => 'error',
        'error_code' => $e->getErrorCode(),
        'description' => $e->getMessage()
    ]);
} catch (\Exception $e) {
    // Errores generales del sistema
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'description' => $e->getMessage()
    ]);
}