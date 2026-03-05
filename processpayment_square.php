<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
$lang ='es';

require 'vendor/autoload.php';

use Square\SquareClient;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\Money;
use Square\Types\Currency;
use Square\Exceptions\SquareApiException;
use Square\Exceptions\SquareException;

// ── Configuración ─────────────────────────────────────────────────────────────
$accessToken = accessToken_square;
$locationId  = locId_square;

// ── Recibir token del frontend ─────────────────────────────────────────────────
//$input = json_decode(file_get_contents('php://input'), true);
$token_id =  $_POST['token_id'] ?? null;

if (!$token_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token de pago requerido']);
    exit;
}



    // 2. Recibir datos del formulario
    $tokenId    = $_POST['token_id'] ?? null;
    $token      = $_POST['token'] ?? null;
    $deviceId   = $_POST['deviceIdHiddenFieldName'] ?? null;
    $amount     = $_POST['amount'] ?? 0;
    
    $amount = $amount * 100;

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

// ── Inicializar cliente Square (v45) ───────────────────────────────────────────
$square = new SquareClient(
    token: $accessToken,
    options: ['baseUrl' => 'https://connect.squareupsandbox.com'] // sandbox
    // Para producción: omitir baseUrl o usar 'https://connect.squareup.com'
);

// ── Crear el pago ──────────────────────────────────────────────────────────────
try {
    $response = $square->payments->create(
        request: new CreatePaymentRequest([
            'idempotencyKey' => uniqid('Pago_', true), // clave única por transacción
            'sourceId'       => $token_id,
            'locationId'     => $locationId,
            'amountMoney'    => new Money([
                'amount'   => $amount,           // en centavos: 1000 = $10.00 USD
                'currency' => Currency::Usd->value,
            ]),
            'note' => 'Pago de anticipo/servicio - ' . $customerData['name'],
        ])
    );

    $payment = $response->getPayment();

    $sqlPay = "INSERT INTO payments (IdLead,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus) 
                            VALUES  (?,?,now(),'Square',?,?,?,'A')";
    $stmtPay = $db->prepare($sqlPay);
    $stmtPay->execute([$cotizacion['IdQuote'],$Folio,$amount/100,Currency::Usd->value,$payment->getId()]);    

    $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
    $stmt->execute([$Folio,$lead['IdBranch']]);        

    //if ($amount == 0){

    //}
    //else{
        $stmt = $db->prepare(" UPDATE lead SET Status = ? WHERE Id = ?");
        $stmt->execute(['confirmed', $lead['IdBranch']]);
    //}    


    echo json_encode([
        'success'    => true,
        'payment_id' => $payment->getId(),
        'status'     => $payment->getStatus(),
        'amount'     => $payment->getAmountMoney()->getAmount() / 100,
        'currency'   => $payment->getAmountMoney()->getCurrency(),
        'status' => 'success',
        'message' => '¡Pago realizado con éxito!',
        'transaction_id' => $payment->getId(),
        'url' => 'successpayment.php?Id='.$token.'&TId='.$payment->getId()
    ]);

} catch (SquareApiException $e) {
    // Error de la API de Square (4xx / 5xx)
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
        'code'    => $e->getCode(),
        'body'    => json_decode($e->getBody(), true),
        'status' => 'error',
        'error_code' => $e->getCode(),
        'description' => $e->getMessage()        

    ]);
} catch (SquareException $e) {
    // Error de red u otro error del SDK
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error interno: ' . $e->getMessage(),
        'status' => 'error',
        'description' => $e->getMessage()
    ]);
}