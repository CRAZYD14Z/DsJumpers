<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
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


#pdf-canvas {
    max-height: 160px; /* Limita la altura del preview */
    object-fit: contain;
    background-color: #eee;
}

.anticipo-card {
    cursor: pointer;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    transition: all 0.2s;
}

.selected-anticipo {
    border-color: #0d6efd;
    background-color: #f0f7ff;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.2);
}        

    </style>
</head>
<body>

<?php
    if (!isset($_GET['Id'])){
        echo "Enlace no válido.";
        die();
    }

    $token = $_GET['Id']; // El UUID de la URL
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


        $query = "select * FROM lead WHERE Id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $cotizacion['IdQuote']);
        $stmt->execute();
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);    

        $query = "select * FROM lead_detail WHERE IdLead = ".$lead['Id']." ORDER BY Id";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $lead_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "select * FROM customers WHERE Id = ".$lead['Customer'];
        $stmt = $db->prepare($query);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);      

        $query = "select * FROM organizations WHERE Id = ".$lead['Organization'];
        $stmt = $db->prepare($query);
        $stmt->execute();
        $organization = $stmt->fetch(PDO::FETCH_ASSOC);        

        if ($lead['Customer'] > 0){

            $Nom =$customer['Nombres'];
            $Ape =$customer['Apellidos'];
            $Correo = $customer['Correo'];        

        }
        else{
            $Nom =$organization['Nombre'];
            $Ape ='';
            $Correo = $organization['Correo'];
        }

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-payment p-4">
                <h4 class="mb-4 fw-bold text-center">Checkout</h4>
                
                <form id="payment-form" action="#" method="POST">
                    <input type="hidden" name="token_id" id="token_id">
                    <input type="hidden" name="token" id="token" value="<?php echo $token ?>">
                    <input type="hidden" name="amount" id="monto-final" value="<?php echo $lead['DepositAmount']?>">

                    <div class="mb-4">                    
                        <h6 class="fw-bold mb-3">Contrato</h6>
                        
                        <div class="row g-3 align-items-center bg-light p-3 rounded border">
                            <div class="col-5 col-sm-4">
                                <div class="position-relative border bg-white rounded overflow-hidden shadow-sm" style="min-height: 120px;">
                                    <canvas id="pdf-canvas" class="w-100 d-block"></canvas>
                                    <div id="loader-pdf" class="position-absolute top-50 start-50 translate-middle">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-7 col-sm-8">
                                <p class="mb-1 fw-bold text-truncate" id="pdf-name">Contrato de Servicios</p>
                                <p class="mb-2 text-muted small">
                                    <i class="bi bi-file-earmark-pdf"></i> Documento legal listo para revisión.
                                </p>
                                <a href="<?php echo $token?>.pdf" class="btn btn-outline-primary btn-sm" download>
                                    <i class="bi bi-download"></i> Descargar PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Información del Cliente</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="text" class="form-control" name="name" placeholder="Nombre" value="<?php echo $Nom;?>" required>
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control" name="last_name" placeholder="Apellidos" value="<?php echo $Ape;?>"required>
                            </div>
                            <div class="col-12">
                                <input type="email" class="form-control" name="email" placeholder="correo@ejemplo.com" value="<?php echo $Correo;?>" required>
                            </div>
                        </div>
                    </div>


                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Datos de Tarjeta</h6>
                        <div class="mb-2">
                            <input type="text" class="form-control" placeholder="Nombre en la tarjeta" data-openpay-card="holder_name">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control only-numbers" placeholder="Número de tarjeta" data-openpay-card="card_number" maxlength="16">
                        </div>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="text" class="form-control only-numbers" placeholder="Mes (MM)" data-openpay-card="expiration_month" maxlength="2">
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control only-numbers" placeholder="Año (AA)" data-openpay-card="expiration_year" maxlength="2">
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control only-numbers" placeholder="CVV" data-openpay-card="cvv2" maxlength="4">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2 opacity-75">
                        <span class="text-muted">Monto Total:</span>
                        <span class="fw-bold">$<?php echo number_format($lead['Total'], 2, '.', ',') ;?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Saldo pendiente (después del pago):</span>
                        <span class="fw-bold">$<?php echo number_format($lead['Balance'], 2, '.', ',') ;?></span>
                    </div>

                    <hr>

                    <div class="p-3 bg-light border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold d-block text-primary">Monto a pagar hoy</span>
                                <small class="text-muted">Por concepto de anticipo</small>
                            </div>
                            <h2 class="fw-bold mb-0 text-primary" id="display-pago-hoy">$<?php echo number_format($lead['DepositAmount'], 2, '.', ',') ;?></h2>
                        </div>
                    </div>

                    <button class="btn btn-pay w-100" id="pay-button">Confirmar Pago</button>
                    
                    <div class="text-center mt-3">
                        <img src="https://www.openpay.mx/_ipx/_/img/header/openpay-color.svg" alt="Openpay" style="height: 25px; opacity: 0.6;">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Configuración Openpay
        OpenPay.setId('<?php echo id_OPAY?>');
        OpenPay.setApiKey(pk_OPAY);
        OpenPay.setSandboxMode(true);
        OpenPay.deviceData.setup("payment-form", "deviceIdHiddenFieldName");

        //const montoBase = 1000.00;

        // --- VALIDACIONES DE INPUTS ---
        $('.only-numbers').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, ''); // Elimina cualquier cosa que no sea número
        });



        // --- PROCESAR PAGO ---
        $('#pay-button').on('click', function(e) {
            e.preventDefault();
            
            // 1. Referencia al botón para feedback visual
            var $btn = $(this);
            $btn.prop("disabled", true).text("Procesando...");

            // 2. Validación manual de campos requeridos (Nombre, Email, etc.)
            let valid = true;
            $('#payment-form input[required]').each(function() {
                if ($(this).val().trim() === "") {
                    $(this).addClass('is-invalid'); // Clase de Bootstrap para error
                    valid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!valid) {
                alert("Por favor completa los datos del cliente marcados como obligatorios.");
                $btn.prop("disabled", false).text("Confirmar Pago");
                return;
            }

            // 3. Crear Token con Openpay
            // extractFormAndCreate lee automáticamente los campos con 'data-openpay-card'
            OpenPay.token.extractFormAndCreate('payment-form', function(res) {
                // --- CASO ÉXITO: Token generado ---
                var token_id = res.data.id;
                $('#token_id').val(token_id);

                // Enviamos los datos al backend.php mediante AJAX
                var datosFormulario = $('#payment-form').serialize();

                $.ajax({
                    type: "POST",
                    url: "processpayment.php",
                    data: datosFormulario,
                    dataType: "json",
                    success: function(respuestaBackend) {
                        if(respuestaBackend.status === 'success') {
                            //alert("¡Pago Exitoso! ID de transacción: " + respuestaBackend.transaction_id);
                            // Opcional: Redirigir a página de éxito
                            //window.location.href =  respuestaBackend.url;
                            window.location.replace(respuestaBackend.url);
                        } else if (respuestaBackend.status === 'pending') {
                            // Manejo de 3D Secure (Si el banco pide autenticación extra)
                            window.location.href = respuestaBackend.url;
                        }
                    },
                    error: function(err) {
                        var errorMsg = err.responseJSON ? err.responseJSON.description : "Error interno en el servidor.";
                        alert("Error en el cobro: " + errorMsg);
                        $btn.prop("disabled", false).text("Confirmar Pago");
                    }
                });

            }, function(err) {
                // --- CASO ERROR: Fallo al generar el token (ej. tarjeta inválida) ---
                var desc = err.data.description != undefined ? err.data.description : err.message;
                alert("Error con la tarjeta: " + desc);
                $btn.prop("disabled", false).text("Confirmar Pago");
            });
        });


    const url = '<?php echo $token;?>.pdf'; // Ruta de tu PDF

    const pdfjsLib = window['pdfjs-dist/build/pdf'] || window.pdfjsLib;
    
    if (pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

        pdfjsLib.getDocument(url).promise.then(pdf => {
            pdf.getPage(1).then(page => {
                const canvas = document.getElementById('pdf-canvas');
                const context = canvas.getContext('2d');

                // Ajustamos la escala para que se vea bien en la columna pequeña
                const viewport = page.getViewport({ scale: 0.5 });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };

                page.render(renderContext).promise.then(() => {
                    // Ocultar el spinner cuando termine de renderizar
                    $('#loader-pdf').fadeOut();
                });
            });
        }).catch(err => {
            console.error("Error al cargar PDF:", err);
            $('#loader-pdf').html('<small class="text-danger">Error</small>');
        });
    }    


    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>