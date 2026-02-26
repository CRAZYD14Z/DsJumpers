<?php
ob_start();
session_start(); 
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();

require_once 'vendor/autoload.php';

$merchantId = id_OPAY;
$privateKey = sk_OPAY;

$idLead         = $_GET['IdLead'] ?? null;
$tokenRecibido  = $_GET['token'] ?? null;
$idCheckout     = $_GET['id'] ?? null;

$tokenEsperado = md5($idLead . "SECRETO_DSJUMPERS");

if (!$idLead || !$idCheckout || $tokenRecibido !== $tokenEsperado) {
    die("Acceso no autorizado o parámetros incompletos.");
}

$pagoRegistrado = false;
$mensaje = "";

try {
    // Intentamos consultar el checkout
    $url = "https://sandbox-api.openpay.mx/v1/$merchantId/checkouts/$idCheckout";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $privateKey . ":");
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $checkout = json_decode($response);

    // SI EL CHECKOUT DA 404, PERO TENEMOS UN ORDER_ID, BUSCAMOS POR TRANSACCION
    if ($httpCode == 404) {
        $orderIdBusqueda = 'LIQ-' . $idLead;
        $urlBusqueda = "https://sandbox-api.openpay.mx/v1/$merchantId/charges?order_id=" . $orderIdBusqueda;
        
        curl_setopt($ch, CURLOPT_URL, $urlBusqueda);
        $responseBusqueda = curl_exec($ch);
        $charges = json_decode($responseBusqueda);

        if (!empty($charges) && $charges[0]->status == 'completed') {
            // Si encontramos la transacción exitosa, simulamos el objeto checkout
            $transactionId = $charges[0]->id;
            $montoPagado = $charges[0]->amount;
            $statusPago = 'completed';
        } else {
            $statusPago = 'not_found';
        }
    } else {
        $statusPago = $checkout->status;
        $transactionId = $checkout->charge->id ?? null;
        $montoPagado = $checkout->charge->amount ?? 0;
    }


    if ($statusPago == 'completed') {

        $stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
        $stmt->execute([$idLead]);
        $lead = $stmt->fetch();    


        $Folio = 0;    
        $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
        $stmt->execute([$lead['IdBranch']]);
        $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Payments){
            $Folio = $Payments['Folio'];
        }
        $Folio+=1;    


        $stmt = $db->prepare("SELECT COUNT(*) FROM payments WHERE TransactionId = ?");
        $stmt->execute([$transactionId]);
        if ($stmt->fetchColumn() > 0) {
            $pagoRegistrado = true;
            $mensaje = "Este pago ya había sido procesado anteriormente.";
        } else {
            try {
                $query = "INSERT INTO payments (IdLead, Folio, DateTime, Platform, Amount, Currency, TransactionId, Estatus) 
                          VALUES (?, ?, NOW(), 'OPENPAY_LINK', ?, 'MXN', ?, 'A')";
                $stmt = $db->prepare($query);
                $stmt->execute([$idLead, $Folio, $montoPagado, $transactionId]);

                // 6. ACTUALIZAR ESTADO DEL LEAD A 'CONFIRMADO'
                //$update = $db->prepare("UPDATE v_leads SET status = 'CONFIRMADO' WHERE Id = ?");
                //$update->execute([$idLead]);


                $pagoRegistrado = true;
                $mensaje = "¡Pago registrado con éxito!";
            } catch (Exception $e) {
                $mensaje = "Error al guardar en BD: " . $e->getMessage();
            }
        }        
        $mensaje = "Pago verificado y registrado con éxito.";
    } else {
        $mensaje = "No se pudo verificar el pago. Si ya realizaste el depósito, contacta a soporte.";
    }
    curl_close($ch);
} catch (Exception $e) {
    $mensaje = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card shadow-lg border-0 p-5">
                <?php if ($pagoRegistrado): ?>
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="fw-bold">¡Gracias por tu pago!</h1>
                    <p class="text-muted fs-5"><?php echo $mensaje; ?></p>
                    <hr>
                    <p>ID de Transacción: <strong><?php echo $transactionId ?? 'N/A'; ?></strong></p>
                    <a href="index.php" class="btn btn-primary btn-lg mt-3">Volver al Inicio</a>
                <?php else: ?>
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="fw-bold">Atención</h1>
                    <p class="text-muted"><?php echo $mensaje; ?></p>
                    <a href="index.php" class="btn btn-outline-secondary mt-3">Regresar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>