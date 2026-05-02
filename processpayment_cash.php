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
try {
    // 2. Recibir datos del formulario
    $tokenId    = $_POST['token_id'] ?? null;
    $IdLead     = $_POST['token'] ?? null;
    $monto      = $_POST['monto'] ?? 0;
    $referencia = $_POST['referencia'] ?? null;
    $tipo_pago  = $_POST['tipo-pago'] ?? null;
    $Currency = 'MXN';
    $ahora = date("Y-m-d H:i:s");

    $stmt = $db->prepare("SELECT IdBranch FROM lead WHERE Id = ? ");
    $stmt->execute([$IdLead]);
    $lead = $stmt->fetch();    

    $Folio = 0;    
    $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Pay'");
    $stmt->execute([$lead['IdBranch']]);
    $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($Payments){
        $Folio = $Payments['Folio'];
    }
    $Folio+=1;

    $sqlPay = "INSERT INTO payments (IdLead,Folio,DateTime,Platform,Amount,Currency,TransactionId,Estatus,Usuario) 
                            VALUES  (?,?,now(),?,?,?,?,'A','')";
    $stmtPay = $db->prepare($sqlPay);
    $stmtPay->execute([$IdLead,$Folio,$tipo_pago,$monto,$Currency,$referencia]);    

    $stmt = $db->prepare(" UPDATE folios sET Folio = ? WHERE IdBranch = ? AND Type = 'Pay'");
    $stmt->execute([$Folio,$lead['IdBranch']]);        

    $stmt = $db->prepare(" UPDATE lead SET Status = ?,Balance = Balance - ? WHERE Id = ?");
    $stmt->execute(['confirmed',$monto, $IdLead]);

    
    $query = "select * FROM payments WHERE IdLead = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $IdLead);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);    

    echo json_encode([
        'status' => 'success',
        'message' => '¡Pago realizado con éxito!',
        'pagos' => $payments
    ]);

} catch (\Exception $e) {
    // Errores generales del sistema
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'description' => $e->getMessage()
    ]);
} 