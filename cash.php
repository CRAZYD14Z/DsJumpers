<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
$lang ='es';
$amount     = $_POST['monto'] ?? 0;
$idLead     = $_POST['idLead'] ?? 0;
$Currency   = 'MXN';

try {

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


    $sqlPay = "INSERT INTO payments (IdLead,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus) 
                            VALUES  (?,?,now(),'Cash',?,?,?,'A')";
    $stmtPay = $db->prepare($sqlPay);
    $stmtPay->execute([$idLead,$Folio,$amount,$Currency,'']);    

    $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
    $stmt->execute([$Folio,$lead['IdBranch']]);         


        echo json_encode([
            'success' => true,
            'status' => 'success',
            'message' => '¡Pago realizado con éxito!'
        ]);    
} catch (\Exception $e) {
    // Errores generales del sistema
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

?>