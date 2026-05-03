<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
include_once 'api/process_op.php'; 
$database = new Database();
$db = $database->getConnection();
$lang ='es';

require_once 'vendor/autoload.php';

use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApiTransactionError;
use Openpay\Data\OpenpayApiRequestError;
use Openpay\Data\OpenpayApiConnectionError;
use Openpay\Data\OpenpayApiAuthError;

$stmt = $db->prepare("SELECT * FROM  opay_account");
$stmt->execute();
$opay_account = $stmt->fetch();       

$merchantId = $opay_account['Id'];
$privateKey = $opay_account['SecretKey'];
$countryCode = 'MX';
$clientIp = $_SERVER['REMOTE_ADDR'];
$isSandbox = true;

try {
    $openpay = Openpay::getInstance($merchantId, $privateKey,$countryCode,$clientIp);
    Openpay::setProductionMode(!$isSandbox);

    // 2. Recibir datos del formulario
    $tokenId    = $_POST['token_id'] ?? null;
    $IdLead      = $_POST['token'] ?? null;
    $deviceId   = $_POST['deviceIdHiddenFieldName'] ?? null;
    $amount     = $_POST['monto'] ?? 0;
    
    $ahora = date("Y-m-d H:i:s");

    $stmt = $db->prepare("SELECT IdBranch,Organization, Customer FROM lead WHERE Id = ? ");
    $stmt->execute([$IdLead]);
    $lead = $stmt->fetch();    
    if ($lead['Organization'] > 0){
        $stmt = $db->prepare("SELECT Nombre as Nombres, '' as Apellidos, Correo, TelefonoCelular FROM  organizations WHERE Id = ?");
        $stmt->execute([$lead['Organization']]);
        $Client = $stmt->fetch();
    }  
    else{
        $stmt = $db->prepare("SELECT Nombres, Apellidos, Correo, TelefonoCelular FROM  customers WHERE Id = ?");
        $stmt->execute([$lead['Customer']]);
        $Client = $stmt->fetch();
    }

    // Datos del cliente
    $customerData = [
        'name'      => $Client['Nombres'],
        'last_name' => $Client['Apellidos'],
        'email'     => $Client['Correo'],
        'phone_number' => $Client['TelefonoCelular'] ?? '5500000000'
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
    
        $Folio = 0;    
        $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
        $stmt->execute([$lead['IdBranch']]);
        $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Payments){
            $Folio = $Payments['Folio'];
        }
        $Folio+=1;    

        $sqlPay = "INSERT INTO payments (IdLead,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                                VALUES  (?,?,now(),'OpenPay',?,?,?,'A','')";
        $stmtPay = $db->prepare($sqlPay);
        $stmtPay->execute([$IdLead,$Folio,$amount,$Currency,$charge->id]);    

        $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
        $stmt->execute([$Folio,$lead['IdBranch']]);        


        $stmt = $db->prepare(" UPDATE lead SET Status = ?,Balance = Balance - ? WHERE Id = ?");
        $stmt->execute(['confirmed',$amount, $IdLead]);

        //METER A OPERACION!!
        //process_op($cotizacion['IdQuote'],$db);
        //METER A OPERACION!!

        $query = "select * FROM payments WHERE IdLead = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $IdLead);
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);           

        echo json_encode([
            'status' => 'success',
            'message' => '¡Pago realizado con éxito!',
            'transaction_id' => $charge->id,
            'pagos' => $payments
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