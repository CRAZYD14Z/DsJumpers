<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
require 'vendor/autoload.php';
include_once 'config/config.php';     
include_once 'config/database.php'; 
include_once 'api/functions.php'; 
$database = new Database();
$db = $database->getConnection();
$lang ='es';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang;?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Seguro | Openpay</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://openpay.s3.amazonaws.com/openpay.v1.min.js"></script>
    <script type='text/javascript' src="https://openpay.s3.amazonaws.com/openpay-data.v1.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #333; }
        .card-payment { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .form-control { border-radius: 8px; padding: 12px; border: 1px solid #dee2e6; }
        .form-control:focus { box-shadow: none; border-color: #000; }
        .btn-pay { background: #000; color: #fff; border: none; padding: 14px; border-radius: 8px; font-weight: 600; transition: 0.3s; }
        .btn-pay:hover { background: #333; color: #fff; }
        .btn-pay:disabled { background: #ccc; }
        .input-group-text { background: transparent; border-radius: 8px; }
        .anticipo-card { cursor: pointer; border: 2px solid #eee; border-radius: 10px; transition: 0.2s; }
        .anticipo-card:hover { border-color: #000; }
        .selected-anticipo { border-color: #000 !important; background-color: #f8f9fa; }
        .text-detail { font-size: 0.85rem; color: #6c757d; }



    </style>
</head>
<body>
<?php

    if (!isset($_GET['Id'])){
        echo "Enlace no válido.";
        die();
    }

    if (!isset($_GET['TId'])){
        echo "Enlace no válido.";
        die();
    }    

    $token = $_GET['Id']; // El UUID de la URL
    $TId = $_GET['TId']; // El UUID de la URL
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

    $sql = "SELECT * FROM account ";
    $stmt = $db->prepare($sql);
    //$stmt->bindValue(":name", $data->Product); 
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);                

    //RECUPERAR PLANTILLA
    $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '8' AND Idioma = 'es'";
    $stmt = $db->prepare($sql);
    //$stmt->bindValue(":name", $data->Product); 
    $stmt->execute();
    $Template = $stmt->fetch(PDO::FETCH_ASSOC);    


    //RECUPERAR Lead
    $sql = "SELECT * FROM lead WHERE Id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":id", $cotizacion['IdQuote']); 
    $stmt->execute();
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);

    //RECUPERAR Customer
    $sql = "SELECT * FROM customers WHERE Id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":id", $lead['Customer']); 
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    //RECUPERAR venue
    $sql = "SELECT * FROM venues WHERE Id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":id", $lead['Venue']); 
    $stmt->execute();
    $venue = $stmt->fetch(PDO::FETCH_ASSOC);       


    $header = "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: text/html; charset=UTF-8\r\n";
    $header .= $Template['Nombre']."\r\n";            
                // Incluimos el teléfono en el cuerpo del correo
    $cuerpo = "<html>".$Template['Template']."</html>";

    $valores = [
        'company_logo'      => $account['Logo'],
        'company_name' => $account['NombreCompania'],
        'ctfirstname'  => $customer['Nombres'],
        'leadid'       => $lead['Folio'],
        'total'  => $lead['Total'],
        'apayment'  => $lead['DepositAmount'],
        'balancedue'  => $lead['Balance'],
        'link_to_accept'  => '',
        'eventstreet' => $venue['Direccion'],
        'eventcity'    => $venue['Ciudad'],
        'startdate'  => $lead['StartDateTime'],
        'company_name'  => $account['NombreCompania'],
        'company_phone'  => $account['TelefonoOficina'],
        'company_city'  => $account['Ciudad'],

    ];       

    $cuerpo = generarHtmlCotizacion($cuerpo, $valores);

    $datosConexion = [
        'host'             => $account['ServidorS'],
        'username'         => $account['UsuarioS'],
        'password'         => $account['PasswordS'],
        'port'             => $account['PortS'],
        'encryption'       => '',
        'nombre_remitente' => $account['NombreCompania']
    ];
    $archivos = [];

    $resultado = enviarEmail(
        $datosConexion, 
        $customer['Correo'], 
        $header,
        $cuerpo,
        $archivos,
        $cotizacion['Contrato'],
        $cotizacion['UUID'].".PDF"
    );    

    // Supongamos que estas son tus variables con los datos de la DB
    $pdfContenido = $cotizacion['Contrato']; // El binario del PDF
    $pdfNombre = $cotizacion['UUID'].".PDF";   // El nombre original (ej: "contrato_45.pdf")

    // Convertimos a Base64
    $base64 = base64_encode($pdfContenido);

    // Creamos el Data URI
    $pdfDataUri = "data:application/pdf;base64," . $base64;

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-payment p-4">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
            </div>
            
            <h1 class="fw-bold text-dark">¡Pago Recibido con Éxito!</h1>
            <p class="lead text-muted mb-5">Tu reservación ha sido confirmada. Hemos enviado el comprobante a tu correo electrónico.</p>

            <div class="card border-0 shadow-sm bg-light mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Monto Pagado (Anticipo):</span>
                        <span class="fw-bold text-success">+$<?php echo number_format($lead['DepositAmount'], 2, '.', ',') ;?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total del Servicio:</span>
                        <span class="fw-medium">$<?php echo number_format($lead['Total'], 2, '.', ',') ;?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Saldo Pendiente:</span>
                        <h4 class="fw-bold mb-0 text-primary">$<?php echo number_format($lead['Balance'], 2, '.', ',') ;?></h4>
                    </div>
                    <small class="d-block text-center mt-3 text-muted">
                        * El saldo restante se liquidará el día del evento.
                    </small>
                </div>
            </div>

            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">

                <a href="<?php echo $pdfDataUri; ?>" 
                download="<?php echo $pdfNombre; ?>" 
                class="btn btn-primary btn-lg px-4 gap-3">
                Descargar PDF
                </a>            

                <a href="<?php echo URL_BASE;?>" class="btn btn-outline-secondary btn-lg px-4">Volver al Inicio</a>
            </div>
            
            <p class="mt-5 text-muted small">ID de Transacción: # <?php echo $TId;?></p>
        </div>            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    window.history.pushState(null, "", window.location.href);
    window.onpopstate = function() {
        window.history.pushState(null, "", window.location.href);
        alert("El pago ya fue procesado. No puedes volver atrás.");
    };
</script>
</body>
</html>