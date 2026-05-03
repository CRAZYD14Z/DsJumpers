<?php
ob_start();
session_start(); 
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();

require __DIR__ . '/vendor/autoload.php';

//use Dotenv\Dotenv;
use Square\SquareClient;
//use Square\Checkout\Requests\CreatePaymentLinkRequest;
use Square\Checkout\PaymentLinks\Requests\CreatePaymentLinkRequest;
use Square\Types\QuickPay;
use Square\Types\Money;
use Square\Types\CheckoutOptions;
use Square\Types\PrePopulatedData;
use Square\Types\BuyerEmailHint;
use Square\Exceptions\SquareApiException;
use Square\Exceptions\SquareException;

// ── Entorno ────────────────────────────────────────────────────────────────────
//Dotenv::createImmutable(__DIR__ . '/../')->load();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}
    
$idLead = $_POST['idLead'];
$monto = $_POST['monto'];

$stmt = $db->prepare("SELECT NombreCompania, WebSite, Currency FROM  account");
$stmt->execute();
$account = $stmt->fetch();   

$stmt = $db->prepare("SELECT * FROM  square_account");
$stmt->execute();
$square_account = $stmt->fetch();     
$accessToken = $square_account['Token'];
$locationId  = $square_account['LocalId'];    

$stmt = $db->prepare("SELECT Organization, Customer FROM  lead WHERE Id = ?");
$stmt->execute([$idLead]);
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


// ── Input ──────────────────────────────────────────────────────────────────────
//$input         = json_decode(file_get_contents('php://input'), true);
$description   = 'Liquidación de saldo - Lead #' . $idLead;
$currency      = $account['Currency'];
$customerName  = $Client['Nombres']." ".$Client['Apellidos'];
$customerEmail = $Client['Correo'];
$expiryHours   = 24;
$note          = '';


if ($monto <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El monto debe ser mayor a 0']);
    exit;
}


// ── Convertir a centavos ───────────────────────────────────────────────────────
// Monedas sin decimales (JPY, etc.) se manejan igual, pero aquí usamos USD/MXN
$amountCents = (int) round($monto * 100);

// ── Cliente Square ─────────────────────────────────────────────────────────────
$isSandbox = 'sandbox';
$square = new SquareClient(
    token: $accessToken,
    options: $isSandbox
        ? ['baseUrl' => 'https://connect.squareupsandbox.com']
        : []
);

// ── Calcular fecha de expiración (ISO 8601) ────────────────────────────────────
$expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
    ->modify("+{$expiryHours} hours")
    ->format('Y-m-d\TH:i:s\Z');

// ── Construir descripción completa ─────────────────────────────────────────────
$fullDescription = $description;
if ($note) {
    $fullDescription .= " — {$note}";
}
if ($customerName) {
    $fullDescription .= " | Cliente: {$customerName}";
}

$orderId = 'LIQ-'.$idLead.'-1'; 
//uniqid('link_', true)
// ── Crear Payment Link via Checkout API ────────────────────────────────────────


// ══════════════════════════════════════════════════════════════════════════════
// VALIDAR SI YA EXISTE UN LINK DE PAGO PARA ESTE LEAD
// Estrategia: listar los payment links y buscar por idempotencyKey / descripción
// ══════════════════════════════════════════════════════════════════════════════
try {
    $existingLink = null;

    // Listar los payment links recientes (máx 100 por página)
    $listResponse = $square->checkout->paymentLinks->list();

    foreach ($listResponse as $link) {
        // Comparamos por el checkoutPageUrl o por la descripción que contiene el idLead
        // Square no expone el idempotencyKey en el listado, así que buscamos
        // en la descripción o en el orderId asociado al link
        $linkDescription = $link->getDescription() ?? '';
        $linkOrderId     = $link->getOrderId()     ?? '';

        // Buscar por descripción (contiene el nombre del lead) 
        // O si guardas el orderId en tu BD, compara directamente
        if (
            str_contains($linkDescription, "| Cliente: {$customerName}") ||
            str_contains($linkDescription, $idLead)
        ) {
            $existingLink = $link;
            break;
        }
    }

    if ($existingLink !== null) {
        // ── Ya existe un link — devolver el existente sin crear uno nuevo ──────
        echo json_encode([
            'success'       => true,
            'already_exists' => true,
            'checkout_link' => $existingLink->getUrl(),
            'link_id'       => $existingLink->getId(),
            'order_id'      => $existingLink->getOrderId(),
            'expires'       => $expiresAt,
            'message'       => 'Se encontró un link de pago existente para este lead.',
        ]);
        exit;
    }

} catch (SquareApiException $e) {
    // Si falla el listado no bloqueamos — intentamos crear de todas formas
    error_log('Square list error: ' . $e->getMessage());
}



try {
    $response = $square->checkout->paymentLinks->create(
        request: new CreatePaymentLinkRequest([
            'idempotencyKey' => $orderId,

            // QuickPay: forma rápida de crear un link sin necesidad de Order previa
            'quickPay' => new QuickPay([
                'name'       => $description,
                'priceMoney' => new Money([
                    'amount'   => $amountCents,
                    'currency' => $currency,
                ]),
                'locationId' => $locationId,
            ]),

            // Opciones del checkout
            'checkoutOptions' => new CheckoutOptions([
                'askForShippingAddress' => false,
                'redirectUrl'           => $account['WebSite']. "tnks_square.php?IdLead=$idLead&token=".md5($idLead . $account['NombreCompania']),  // URL de retorno tras pago
            ]),

            // Pre-llenar el email del comprador
            'prePopulatedData' => new PrePopulatedData([
                'buyerEmail' => $customerEmail,
            ]),

            // Nota interna
            'description' => $fullDescription,
        ])
    );

    $paymentLink = $response->getPaymentLink();

    echo json_encode([
        
        'success'  => true,
        'checkout_link'  => $paymentLink->getUrl(),
        'link_id'  => $paymentLink->getId(),
        'order_id' => $paymentLink->getOrderId(),
        'expires'  => $expiresAt,
    ]);

} catch (SquareApiException $e) {
    $body = json_decode($e->getBody(), true);
    $errorMsg = $body['errors'][0]['detail'] ?? $e->getMessage();

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $errorMsg,
        'code'    => $body['errors'][0]['code'] ?? null,
    ]);

} catch (SquareException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error de conexión con Square: ' . $e->getMessage(),
    ]);
}
//
//http://localhost/eventgo_wpage/tnks_square.php?IdLead=71&token=e01ff1f7487bf02f074b0c56c8a62a72