<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();

$Idioma = $_SESSION['Idioma'];
$query = "select Traduccion FROM  programas_traduccion where Programa = 'payments' AND Idioma = ? ORDER BY Id";            
$stmt = $db->prepare($query);
$stmt->bindValue(1, $Idioma);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$Traducciones[]='';
if ($resultados) {
    foreach ($resultados as $registro) {
        $Traducciones[]=$registro['Traduccion'];
    }
}    
function Trd($Id){
    global $Traducciones;
    return $Traducciones[$Id];
}


include_once 'head.php';
?>

    <style>
        .card-header { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; }
        .label-custom { font-weight: 600; color: #6c757d; font-size: 0.85rem; text-transform: uppercase; }
        .value-custom { font-size: 1.1rem; color: #212529; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-weight: bold; }

/* Contenedor responsivo estándar de Bootstrap */
.table-responsive-custom {
    overflow-x: auto;
}

@media (max-width: 768px) {
    /* Escondemos el encabezado de la tabla en móvil */
    .table-mobile-responsive thead {
        display: none;
    }
    
    .table-mobile-responsive tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
    }
    
    .table-mobile-responsive tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: right;
        border-bottom: 1px solid #eee;
        padding: 0.75rem 1rem !important;
    }

    /* Creamos etiquetas usando el atributo data-label */
    .table-mobile-responsive tbody td::before {
        content: attr(data-label);
        font-weight: bold;
        text-align: left;
        color: #6c757d;
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    .table-mobile-responsive tbody td:last-child {
        border-bottom: 0;
        background-color: #f8f9fa;
    }
    
    /* Ajuste de los footers de la tabla */
    .table-mobile-responsive tfoot tr {
        display: block;
        text-align: right;
        padding: 0.5rem;
    }
}        

    </style>

</head>
<body >
<?php
    include_once 'nav.php';
?>
<div class="container-fluid px-3">
<?php
    $PayPlatform ='OPAY';
    if ($PayPlatform == 'OPAY')
        $URLGenerate_Link = 'ajax_generar_link.php';
    else
        $URLGenerate_Link = 'ajax_generar_link_square.php';
    

    if (isset($_GET['IdLead']) AND $_GET['IdLead'] > 0 ){
        $IdLead = $_GET['IdLead'];
        $query = "select Id,IdBranch,Folio,StartDateTime,EndDateTime, Organization,NombreOrganizacion,Customer,NombreCliente,ApellidosCliente, Venue,Lugar Estado,Ciudad, Total FROM v_leads WHERE Id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $IdLead);
        $stmt->execute();
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($lead) {
            $query = "select             
                        Id,
                        IdLead,
                        Folio,
                        DateTime,
                        Platform,
                        Amount,
                        Currency,
                        TransactionId,
                        Estatus
            FROM payments WHERE IdLead = $IdLead AND Estatus = 'A' ORDER BY Id";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // ... Tu lógica de consultas aquí (incluyendo el cálculo del total pagado) ...
    $totalPagado = 0;
    foreach ($payments as $p) {
        $totalPagado += $p['Amount'];
    }
    $saldoPendiente = $lead['Total'] - $totalPagado;    

?>


<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="fw-bold"><?php echo Trd(1)?> <span class="text-primary">#<?php echo $lead['Folio']; ?></span></h2>
        </div>
        <div class="col text-end">
            <?php if ($saldoPendiente > 0): ?>
                <button class="btn btn-success btn-lg shadow-sm" id="btnPagar">
                    <i class="bi bi-credit-card-2-back"></i> <?php echo Trd(2)?>  ($<?php echo number_format($saldoPendiente, 2); ?>)
                </button>
                <br>
                <br>
                <button class="btn btn-warning btn-lg shadow-sm" onclick=" mostrarModalPagoEfectivo(<?php echo $saldoPendiente;?>,'')" >
                    <i class="bi bi-credit-card-2-back"></i> <?php echo Trd(41)?>  ($<?php echo number_format($saldoPendiente, 2); ?>)
                </button>                

            <?php else: ?>
                <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> <?php echo Trd(3)?> </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header fw-bold"><?php echo Trd(4)?></div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="label-custom mb-0"><?php echo Trd(5)?></p>
                        <p class="value-custom fw-bold text-info">
                            <?php echo $lead['Organization'] > 0 ? Trd(6) : Trd(7); ?>
                        </p>
                    </div>
                    <div class="mb-3">
                        <p class="label-custom mb-0"> <?php echo Trd(8)?></p>
                        <p class="value-custom">
                            <?php 
                                echo $lead['Organization'] > 0 
                                     ? $lead['NombreOrganizacion'] 
                                     : $lead['NombreCliente'] . " " . $lead['ApellidosCliente']; 
                            ?>
                        </p>
                    </div>
                    <hr>
                    <p class="label-custom mb-0"><?php echo Trd(9)?> </p>
                    <p class="value-custom mb-1"><i class="bi bi-geo-alt"></i> <?php echo $lead['Venue']; ?></p>
                    <small class="text-muted"><?php echo $lead['Ciudad'] . ", " . $lead['Estado']; ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <?php echo Trd(10)?>
                <span class="badge bg-primary"><?php echo count($payments); ?> <?php echo Trd(11)?> </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-mobile-responsive">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4"><?php echo Trd(12)?></th>
                                <th><?php echo Trd(13)?></th>
                                <th><?php echo Trd(14)?></th>
                                <th><?php echo Trd(15)?></th>
                                <th class="text-end pe-4"><?php echo Trd(16)?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr><td colspan="5" class="text-center py-4"><?php echo Trd(17)?></td></tr>
                            <?php else: ?>
                                <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td class="ps-4" data-label="Folio Pago">#<?php echo $p['Folio']; ?></td>
                                    <td data-label="Fecha"><?php echo date('d/m/Y H:i', strtotime($p['DateTime'])); ?></td>
                                    <td data-label="Plataforma"><span class="badge bg-light text-dark border"><?php echo $p['Platform']; ?></span></td>
                                    <td data-label="Transacción"><small class="text-muted text-break"><?php echo $p['TransactionId']; ?></small></td>
                                    <td class="text-end pe-4 fw-bold text-success" data-label="Monto">$<?php echo number_format($p['Amount'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4" class="text-end d-none d-md-table-cell"><?php echo Trd(18)?></td>
                                <td class="text-end pe-4 fs-6"><span class="d-md-none"><?php echo Trd(19)?> </span>$<?php echo number_format($lead['Total'], 2); ?></td>
                            </tr>
                            <tr class="text-success">
                                <td colspan="4" class="text-end d-none d-md-table-cell"><?php echo Trd(20)?></td>
                                <td class="text-end pe-4 fs-6"><span class="d-md-none"><?php echo Trd(21)?></span>$<?php echo number_format($totalPagado, 2); ?></td>
                            </tr>
                            <?php if($saldoPendiente > 0): ?>
                            <tr class="text-danger border-top border-danger">
                                <td colspan="4" class="text-end d-none d-md-table-cell"><?php echo Trd(22)?></td>
                                <td class="text-end pe-4 fs-5"><span class="d-md-none"><?php echo Trd(23)?> </span>$<?php echo number_format($saldoPendiente, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalPagoLabel"><i class="bi bi-send-check"></i> <?php echo Trd(24)?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="loadingPago">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2"><?php echo Trd(25)?></p>
                </div>

                <div id="pagoContent" style="display:none;">
                    <p class="text-muted"><?php echo Trd(26)?></p>
                    <h2 class="fw-bold text-dark mb-4" id="montoModal">$0.00</h2>
                    
                    <div class="input-group mb-3">
                        <input type="text" id="urlPagoInput" class="form-control" readonly>
                        <button class="btn btn-outline-primary" type="button" id="btnCopiar">
                            <i class="bi bi-clipboard"></i> <?php echo Trd(27)?>
                        </button>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="#" id="btnWhatsApp" target="_blank" class="btn btn-success btn-lg">
                            <i class="bi bi-whatsapp"></i><?php echo Trd(28)?> 
                        </a>
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><?php echo Trd(29)?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalPagoEfectivo" tabindex="-1" aria-labelledby="modalPagoEfectivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalPagoEfectivoLabel"><i class="bi bi-cash-stack"></i> <?php echo Trd(30)?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="loadingPagoEfectivo" class="text-center">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2"><?php echo Trd(31)?></p>
                </div>

                <div id="pagoEfectivoContent" style="display:none;">
                    <!-- Monto a pagar -->
                    <div class="text-center mb-4">
                        <p class="text-muted"><?php echo Trd(32)?></p>
                        <!-- Monto a pagar -->
                        <!--<h2 class="fw-bold text-dark" id="montoEfectivoModal">$0.00</h2>-->
                    <input type="number" 
                        id="montoEfectivoModal" 
                        class="monto-input" 
                        step="any" 
                        min="0" 
                        max="<?php echo $saldoPendiente; ?>" 
                        placeholder="0.00" 
                        style="text-align: right; font-size: 1.5rem; padding: 0.75rem; width: 100%; border-radius: 8px; border: 1px solid #ccc;">
                    </div>

                    <!-- Opciones de pago -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-cash display-4 text-success"></i>
                                    <h5 class="card-title mt-2"><?php echo Trd(33)?></h5>
                                    <p class="card-text small text-muted"><?php echo Trd(34)?></p>

                                    <button type="button" class="btn btn-outline-secondary flex-fill" id="btnConfirmarPago" onclick="ProcesarP('E')" >
                                        <i class="bi bi-check-circle"></i> <?php echo Trd(39)?>
                                    </button>                                    
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-bank display-4 text-primary"></i>
                                    <h5 class="card-title mt-2"><?php echo Trd(36)?></h5>
                                    <p class="card-text small text-muted"><?php echo Trd(37)?></p>
                                    
                                    <!-- Datos bancarios (se llenarán dinámicamente) -->
                                    <div id="datosBancarios" class="mt-3">
                                        <!-- Banco -->
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-bank2"></i></span>
                                            <input type="text" id="bancoInfo" class="form-control form-control-sm" readonly value="Banco Ejemplo">
                                        </div>
                                        
                                        <!-- Tipo de cuenta -->
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-card-text"></i></span>
                                            <input type="text" id="tipoCuentaInfo" class="form-control form-control-sm" readonly value="Cuenta Corriente">
                                        </div>
                                        
                                        <!-- Número de cuenta -->
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" id="numeroCuentaInfo" class="form-control form-control-sm" readonly value="1234-5678-9012-3456">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" id="btnCopiarCuenta" title="Copiar número de cuenta">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Titular -->
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                            <input type="text" id="titularInfo" class="form-control form-control-sm" readonly value="Nombre del Titular">
                                        </div>
                                        
                                        <!-- CI/RUC -->
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-card-heading"></i></span>
                                            <input type="text" id="documentoInfo" class="form-control form-control-sm" readonly value="12345678-9">
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-outline-secondary flex-fill" id="btnConfirmarPagoT" onclick="ProcesarP('T')" >
                                        <i class="bi bi-check-circle"></i> <?php echo Trd(45)?>
                                    </button>                                        

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instrucciones adicionales -->
                    <div class="alert alert-warning border-0 bg-light mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                            </div>
                            <div class="small">
                                <strong><?php echo Trd(38)?></strong><br>
                                <?php echo Trd(39)?>
                            </div>
                        </div>
                    </div>


                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-link text-muted small" data-bs-dismiss="modal">
                            <?php echo Trd(29)?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="close-window-container" style="display: none;" class="text-center mt-3">
    <button type="button" class="btn btn-danger" onclick="cerrarPestana()">
        <i class="fas fa-times-circle me-1"></i><?php echo Trd(43)?> 
    </button>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-primary text-white">
      <strong class="me-auto"><i class="fas fa-bell me-2"></i><?php echo Trd(44)?> </strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toast-message">
      </div>
  </div>
</div>


<script>
    

    const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
    const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
    const TOKEN = localStorage.getItem('apiToken'); 


    $('.lang-option').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'cambiar_idioma.php',
            type: 'POST',
            data: { lang: $(this).data('lang') },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Recargamos para que el servidor lea la nueva sesión de idioma
                    location.reload(); 
                }
            }
        });
        
    });


$('#btnPagar').on('click', function() {
    const idLead = <?php echo $lead['Id']; ?>;
    const saldo = <?php echo $saldoPendiente; ?>;
    
    // Configurar modal inicial
    $('#montoModal').text('$' + saldo.toLocaleString('es-MX', {minimumFractionDigits: 2}));
    $('#loadingPago').show();
    $('#pagoContent').hide();
    
    // Mostrar el modal
    const modalPago = new bootstrap.Modal(document.getElementById('modalPago'));
    modalPago.show();

    // Petición AJAX
    $.ajax({
        url: '<?php echo $URLGenerate_Link;?>',
        method: 'POST',
        data: { idLead: idLead, monto: saldo, user: 'admin' },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                const url = response.checkout_link;
                $('#urlPagoInput').val(url);
                
                // Configurar botón WhatsApp
                const msj = encodeURIComponent("<?php echo Trd(40)?> " + url);
                $('#btnWhatsApp').attr('href', 'https://wa.me/?text=' + msj);
                
                $('#loadingPago').hide();
                $('#pagoContent').fadeIn();
            } else {
                alert('Error: ' + response.error);
                modalPago.hide();
            }
        },
        error: function() {
            alert('Error de conexión con el servidor.');
            modalPago.hide();
        }
    });
});

// Función para copiar al portapapeles
$('#btnCopiar').on('click', function() {
    const input = document.getElementById('urlPagoInput');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);
    
    $(this).html('<i class="bi bi-check2"></i> ¡<?php echo Trd(47)?>!').removeClass('btn-outline-primary').addClass('btn-primary');
    setTimeout(() => {
        $(this).html('<i class="bi bi-clipboard"></i> <?php echo Trd(46)?>').removeClass('btn-primary').addClass('btn-outline-primary');
    }, 2000);
});



// Función para mostrar el modal de pago en efectivo/transferencia
function mostrarModalPagoEfectivo(monto, datosBancarios = null) {
    const modal = new bootstrap.Modal(document.getElementById('modalPagoEfectivo'));
    
    // Mostrar loading
    document.getElementById('loadingPagoEfectivo').style.display = 'block';
    document.getElementById('pagoEfectivoContent').style.display = 'none';
    
    modal.show();
    
    // Simular carga de datos
    setTimeout(() => {
        // Actualizar monto
        //document.getElementById('montoEfectivoModal').textContent = 
        //    new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(monto);
        document.getElementById('montoEfectivoModal').value =  monto;
        // Si se proporcionan datos bancarios, actualizarlos
        if (datosBancarios) {
            document.getElementById('bancoInfo').value = datosBancarios.banco || 'Banco Ejemplo';
            document.getElementById('tipoCuentaInfo').value = datosBancarios.tipoCuenta || 'Cuenta Corriente';
            document.getElementById('numeroCuentaInfo').value = datosBancarios.numeroCuenta || '1234-5678-9012-3456';
            document.getElementById('titularInfo').value = datosBancarios.titular || 'Nombre del Titular';
            document.getElementById('documentoInfo').value = datosBancarios.documento || '12345678-9';
        }
        
        // Ocultar loading y mostrar contenido
        document.getElementById('loadingPagoEfectivo').style.display = 'none';
        document.getElementById('pagoEfectivoContent').style.display = 'block';
    }, 1000);
}

// Función para copiar número de cuenta
document.getElementById('btnCopiarCuenta').addEventListener('click', function() {
    const numeroCuenta = document.getElementById('numeroCuentaInfo');
    numeroCuenta.select();
    document.execCommand('copy');
    
    // Feedback visual
    const btn = this;
    const iconoOriginal = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-lg"></i>';
    setTimeout(() => {
        btn.innerHTML = iconoOriginal;
    }, 2000);
});



function ProcesarP(Tipo){



    const idLead = <?php echo $lead['Id']; ?>;
    const saldop = <?php echo $saldoPendiente; ?>;
    const saldo = document.getElementById('montoEfectivoModal').value;

    if (saldo > saldop ){
        mostrarToast('Monto mayor al saldo!', true);
        //alert('Monto mayor al saldo!');
        return;
    }

    document.getElementById('loadingPagoEfectivo').style.display = 'block';
    document.getElementById('pagoEfectivoContent').style.display = 'none';        

    // Petición AJAX
    $.ajax({
        url: API_BASE_URL + 'api/process_pay/',
        method: 'POST',
        data: { idLead: idLead, monto: saldo, tipo:Tipo, usuario:'admin'},
        headers: { 'Authorization': 'Bearer ' + TOKEN },
        dataType: 'json',
        success: function(response) {
            if(response.success) {

                setTimeout(() => {
                    location.reload();
                }, 1000);            

                
            } else {
                //alert('Error: ' + response.error);
                //modalPago.hide();
                document.getElementById('loadingPagoEfectivo').style.display = 'none';
                document.getElementById('pagoEfectivoContent').style.display = 'block';                
            }
        },
        error: function() {
            //alert('Error de conexión con el servidor.');
            //modalPago.hide();
            document.getElementById('loadingPagoEfectivo').style.display = 'none';
            document.getElementById('pagoEfectivoContent').style.display = 'block';            
        }
    });   

}

// Función para confirmar pago
//document.getElementById('btnConfirmarPago').addEventListener('click', function() {
    // Aquí puedes agregar la lógica cuando el usuario confirma que realizará el pago
    //console.log('Usuario confirmó que realizará el pago');
    // Puedes enviar un email, guardar en BD, etc.
//});

document.addEventListener("DOMContentLoaded", function() {
    // Verificamos si la pestaña tiene un 'opener' (quien la abrió)
    // y si no es la misma ventana
    if (window.opener && window.opener !== window) {
        document.getElementById('close-window-container').style.display = 'block';
    }
});

function cerrarPestana() {
    // Opcional: Podrías recargar la ventana padre para actualizar saldos
    try {
        window.opener.location.reload(); 
    } catch (e) {
        console.log("No se pudo recargar la página principal");
    }
    
    // Cerrar la pestaña actual
    window.close();
}


function mostrarToast(mensaje, esError = false) {
    const toastElement = document.getElementById('liveToast');
    const toastMessage = document.getElementById('toast-message');
    const toastHeader = toastElement.querySelector('.toast-header');

    // Cambiar color si es error o éxito
    if (esError) {
        toastHeader.classList.replace('bg-primary', 'bg-danger');
    } else {
        toastHeader.classList.replace('bg-danger', 'bg-primary');
    }

    toastMessage.textContent = mensaje;

    // Inicializar y mostrar con Bootstrap 5
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}



</script>
</body>
</html>