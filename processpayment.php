<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
$lang ='es';

require_once 'vendor/autoload.php';

use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApiTransactionError;
use Openpay\Data\OpenpayApiRequestError;
use Openpay\Data\OpenpayApiConnectionError;
use Openpay\Data\OpenpayApiAuthError;


// 1. Configuración de credenciales (Asegúrate de usar las tuyas)
$merchantId = id_OPAY;
$privateKey = sk_OPAY; // REEMPLAZA CON TU LLAVE PRIVADA (sk_...)
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
    
    $ahora = date("Y-m-d H:i:s");

    $stmt = $db->prepare("SELECT * FROM quotes WHERE UUID = ? AND Status = 'A'");
    $stmt->execute([$token]);
    $cotizacion = $stmt->fetch();
    if ($cotizacion) {
        // Verificar si la fecha actual es mayor a la de expiración
        if ($ahora > $cotizacion['ExpDate']) {
            echo "Lo sentimos, esta cotización ha caducado el " . $cotizacion['ExpDate']." $ahora";
            die();
        }
    } else {
        echo "Enlace no válido.";
        die();
    }        

    $stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
    $stmt->execute([$cotizacion['IdQuote']]);
    $lead = $stmt->fetch();    


    $Folio = 0;    
    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
    $stmt->execute([$lead['IdBranch']]);
    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($Payments){
        $Folio = $Payments['Folio'];
    }
    $Folio+=1;

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
    $Currency = 'MXN';
    // 3. Preparar el objeto del cargo
    $chargeRequest = [
        'method' => 'card',
        'source_id' => $tokenId,
        'amount' => (float)$amount,
        'currency' => $Currency,
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
    
        $sqlPay = "INSERT INTO payments (IdLead,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus) 
                                VALUES  (?,?,now(),'OpenPay',?,?,?,'A')";
        $stmtPay = $db->prepare($sqlPay);
        $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$amount,$Currency,$charge->id]);    

        $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
        $stmt->execute([$Folio,$lead['IdBranch']]);        

        //if ($amount == 0){

        //}
        //else{
            $stmt = $db->prepare(" UPDATE lead SET Status = ? WHERE Id = ?");
            $stmt->execute(['confirmed', $lead['IdBranch']]);
        //}

        echo json_encode([
            'status' => 'success',
            'message' => '¡Pago realizado con éxito!',
            'transaction_id' => $charge->id,
            'url' => 'successpayment.php?Id='.$token.'&TId='.$charge->id
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