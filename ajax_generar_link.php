<?php
ob_start();
session_start(); 
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idLead'])) {
    $idLead = $_POST['idLead'];
    $monto = $_POST['monto'];

    // Credenciales
    $merchantId = id_OPAY;
    $privateKey = sk_OPAY; // REEMPLAZA CON TU LLAVE PRIVADA (sk_...)
// El Order ID debe ser consistente para poder recuperarlo
    $orderId = 'LIQ-' . $idLead.'-1'; 
    $base_url = "https://sandbox-api.openpay.mx/v1/$merchantId/checkouts";

    // Función auxiliar para consultar a Openpay
    function callOpenpay($url, $method, $key, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $key . ":");
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['code' => $httpCode, 'data' => json_decode($response)];
    }

    // 1. Intentar crear el Checkout
    $payload = [
        'amount' => (float)$monto,
        'currency' => 'MXN',
        'description' => 'Liquidación de saldo - Lead #' . $idLead,
        'order_id' => $orderId,
        'send_email' => false,
        'expiration_date' => date('Y-m-d H:i', strtotime('+3 days')),
        'redirect_url' => "http://localhost/DsJumpers/gracias.php?IdLead=$idLead&token=" . md5($idLead . "SECRETO_DSJUMPERS"),
                'customer' => [
            'name' => 'Juan',
            'last_name' => 'Perez',
            'email' => 'juan.perez@ejemplo.com',
            'phone_number' => '5512345678'
        ]
    ];

    $res = callOpenpay($base_url, 'POST', $privateKey, $payload);

    // 2. Si ya existe (Error 409 o Error Code 2005)
    if ($res['code'] == 409 || (isset($res['data']->error_code) && $res['data']->error_code == 2005)) {
        
        // Consultamos la lista de checkouts filtrando por nuestro Order ID
        $searchUrl = $base_url . "?order_id=" . $orderId;
        $searchRes = callOpenpay($searchUrl, 'GET', $privateKey);

        if ($searchRes['code'] == 200 && !empty($searchRes['data'])) {
            // Buscamos el que esté disponible
            foreach ($searchRes['data'] as $checkout) {
                if ($checkout->order_id == $orderId && $checkout->status == 'available') {
                    echo json_encode([
                        'success' => true, 
                        'checkout_link' => $checkout->checkout_link,
                        'info' => 'Link recuperado'
                    ]);
                    exit;
                }
            }
        }
        
        // Si llegamos aquí es porque existe pero quizás ya está pagado o expirado
        echo json_encode(['success' => false, 'error' => 'La orden ya existe pero no está disponible para pago. Verifique su panel.']);
    } 
    // 3. Respuesta de éxito normal
    elseif ($res['code'] == 201 || $res['code'] == 200) {
        echo json_encode(['success' => true, 'checkout_link' => $res['data']->checkout_link]);
    } 
    // 4. Otros errores
    else {
        echo json_encode(['success' => false, 'error' => $res['data']->description ?? 'Error desconocido']);
    }
}