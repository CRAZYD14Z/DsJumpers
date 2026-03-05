<?php
ob_start();
session_start(); 
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();

require_once 'vendor/autoload.php';

use Square\SquareClient;
//use Square\Orders\Requests\RetrieveOrderRequest;
use Square\Payments\Requests\ListPaymentsRequest;

$idLead    = intval($_GET['IdLead'] ?? 0);
$token     = $_GET['token'] ?? '';
$tokenReal = md5($idLead . "SECRETO_DSJUMPERS");

$tokenEsperado = md5($idLead . "SECRETO_DSJUMPERS");


// Verificar que el token sea válido (evita acceso directo)
if (!hash_equals($tokenReal, $token)) {
    die('Acceso no autorizado');
}


$pagoRegistrado = false;
$mensaje = "";

$orderId = 'LIQ-' . $idLead.'-1'; 

$square = new SquareClient(
    token: accessToken_square,
    options: ['baseUrl' => 'https://connect.squareupsandbox.com']
);

$resultado = verificarPagoLink($square, $orderId);

if ($resultado['pagado'] == 'COMPLETED ') {
    // Marcar como pagado en tu BD
    // $db->query("UPDATE leads SET pagado = 1 WHERE id_lead = ?", [$idLead]);

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

        //echo "🎉 ¡Gracias! Tu pago fue procesado exitosamente.";
} else {
    $mensaje = "No se pudo verificar el pago. Si ya realizaste el depósito, contacta a soporte.";
    //echo "⚠️ Aún no detectamos tu pago. Estado: " . $resultado['estado'];
}


function verificarPagoLink(SquareClient $square, string $orderId): array
{
    $payments = $square->payments->list(
        new ListPaymentsRequest(['orderId' => $orderId])
    );

    foreach ($payments as $payment) {
            return [
                'pagado'    => $payment->getStatus(),
                'estado'    => $payment->getStatus()
            ];    
    }

    return [
        'pagado'    => 'NO PAGADO',
        'estado'    => 'NO PAGADO'
    ];      

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