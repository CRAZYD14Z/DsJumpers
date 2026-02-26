<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
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
            <h2 class="fw-bold">Folio: <span class="text-primary">#<?php echo $lead['Folio']; ?></span></h2>
        </div>
        <div class="col text-end">
            <?php if ($saldoPendiente > 0): ?>
                <button class="btn btn-success btn-lg shadow-sm" id="btnPagar">
                    <i class="bi bi-credit-card-2-back"></i> Pagar Diferencia ($<?php echo number_format($saldoPendiente, 2); ?>)
                </button>
            <?php else: ?>
                <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> TOTALMENTE PAGADO</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header fw-bold">DATOS DEL CLIENTE</div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="label-custom mb-0">Tipo:</p>
                        <p class="value-custom fw-bold text-info">
                            <?php echo $lead['Organization'] > 0 ? 'ORGANIZACIÓN' : 'PARTICULAR'; ?>
                        </p>
                    </div>
                    <div class="mb-3">
                        <p class="label-custom mb-0">Nombre / Razón Social:</p>
                        <p class="value-custom">
                            <?php 
                                echo $lead['Organization'] > 0 
                                     ? $lead['NombreOrganizacion'] 
                                     : $lead['NombreCliente'] . " " . $lead['ApellidosCliente']; 
                            ?>
                        </p>
                    </div>
                    <hr>
                    <p class="label-custom mb-0">Evento en:</p>
                    <p class="value-custom mb-1"><i class="bi bi-geo-alt"></i> <?php echo $lead['Venue']; ?></p>
                    <small class="text-muted"><?php echo $lead['Ciudad'] . ", " . $lead['Estado']; ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                HISTORIAL DE PAGOS
                <span class="badge bg-primary"><?php echo count($payments); ?> registros</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-mobile-responsive">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Folio Pago</th>
                                <th>Fecha</th>
                                <th>Plataforma</th>
                                <th>Transacción</th>
                                <th class="text-end pe-4">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr><td colspan="5" class="text-center py-4">No se han registrado pagos aún.</td></tr>
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
                                <td colspan="4" class="text-end d-none d-md-table-cell">TOTAL COTIZADO:</td>
                                <td class="text-end pe-4 fs-6"><span class="d-md-none">TOTAL COTIZADO: </span>$<?php echo number_format($lead['Total'], 2); ?></td>
                            </tr>
                            <tr class="text-success">
                                <td colspan="4" class="text-end d-none d-md-table-cell">TOTAL PAGADO:</td>
                                <td class="text-end pe-4 fs-6"><span class="d-md-none">TOTAL PAGADO: </span>$<?php echo number_format($totalPagado, 2); ?></td>
                            </tr>
                            <?php if($saldoPendiente > 0): ?>
                            <tr class="text-danger border-top border-danger">
                                <td colspan="4" class="text-end d-none d-md-table-cell">RESTANTE POR PAGAR:</td>
                                <td class="text-end pe-4 fs-5"><span class="d-md-none">SALDO RESTANTE: </span>$<?php echo number_format($saldoPendiente, 2); ?></td>
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
                <h5 class="modal-title" id="modalPagoLabel"><i class="bi bi-send-check"></i> Link de Pago Generado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="loadingPago">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Generando link seguro con Openpay...</p>
                </div>

                <div id="pagoContent" style="display:none;">
                    <p class="text-muted">Se ha generado un link para liquidar el saldo de:</p>
                    <h2 class="fw-bold text-dark mb-4" id="montoModal">$0.00</h2>
                    
                    <div class="input-group mb-3">
                        <input type="text" id="urlPagoInput" class="form-control" readonly>
                        <button class="btn btn-outline-primary" type="button" id="btnCopiar">
                            <i class="bi bi-clipboard"></i> Copiar
                        </button>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="#" id="btnWhatsApp" target="_blank" class="btn btn-success btn-lg">
                            <i class="bi bi-whatsapp"></i> Enviar por WhatsApp
                        </a>
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
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
        url: 'ajax_generar_link.php',
        method: 'POST',
        data: { idLead: idLead, monto: saldo },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                const url = response.checkout_link;
                $('#urlPagoInput').val(url);
                
                // Configurar botón WhatsApp
                const msj = encodeURIComponent("Hola, este es el link para liquidar el saldo de tu evento: " + url);
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
    
    $(this).html('<i class="bi bi-check2"></i> ¡Copiado!').removeClass('btn-outline-primary').addClass('btn-primary');
    setTimeout(() => {
        $(this).html('<i class="bi bi-clipboard"></i> Copiar').removeClass('btn-primary').addClass('btn-outline-primary');
    }, 2000);
});
</script>
</body>
</html>