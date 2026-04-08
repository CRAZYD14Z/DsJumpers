<?php
    ob_start();
    session_start(); 
    // Incluye la clase de conexión a la BD
    include_once 'valid_login.php';
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    //$_SESSION['Idioma'] = 'es';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['Idioma'];?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración con Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

        .product-img { object-fit: cover; border-radius: 8px; background: #fff; }
        .table-success-light { background-color: rgba(25, 135, 84, 0.08) !important; transition: 0.3s; }
        .qty-input { width: 60px !important; font-weight: bold; }
        .row-check { transform: scale(1.4); cursor: not-allowed; }
        .badge-base { background-color: #0dcaf0; color: #000; font-size: 0.65rem; }
        .badge-acc { background-color: #ffc107; color: #000; font-size: 0.65rem; }
        .main-row { border-left: 5px solid var(--ds-primary); }
    </style>    

<style>
    /* ... tus estilos anteriores ... */

    /* Ajuste para controles de cantidad en móviles */
    @media (max-width: 576px) {
        .qty-container {
            flex-direction: column; /* Apila el título si fuera necesario */
            align-items: center;
        }
        
        .input-group-mobile {
            width: 100% !important;
            max-width: 140px; /* Evita que sea gigante pero da buen espacio táctil */
            margin: 0 auto;
        }

        .qty-input {
            width: 50px !important;
            font-size: 1.1rem; /* Más fácil de leer en móvil */
        }

        /* Hacer los botones más grandes para dedos (Touch targets) */
        .btn-minus, .btn-plus {
            padding: 0.5rem 0.75rem;
        }
        
        /* Ocultar etiquetas pequeñas en móvil para ganar espacio */
        .ps-5 {
            padding-left: 1rem !important;
        }
    }
</style>

<style>
    /* Estilo para cuando el input está al máximo */
    .assorted-qty:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }
    
    /* Animación de "error" si intenta excederse */
    .is-invalid {
        border-color: #dc3545 !important;
        animation: shake 0.2s ease-in-out 0s 2;
    }

    @keyframes shake {
        0% { margin-left: 0rem; }
        25% { margin-left: 0.2rem; }
        75% { margin-left: -0.2rem; }
        100% { margin-left: 0rem; }
    }

    /* Botón deshabilitado visualmente */
    .btn-plus:disabled {
        background-color: #f8f9fa;
        color: #dee2e6;
        border-color: #dee2e6;
        cursor: not-allowed;
    }
</style>
<style>
    .x-small { font-size: 0.65rem; padding: 2px 6px; }
    .table-success-row { background-color: rgba(25, 135, 84, 0.1) !important; transition: 0.3s; }
    
    @media (max-width: 576px) {
        .custom-qty-group { width: 100% !important; max-width: 130px; margin: 0 auto; }
        .btn-minus, .btn-plus { padding: 8px 12px; }
        .assorted-qty { font-size: 1rem; width: 45px !important; }
    }

    /* Animación de error si intenta exceder */
    .qty-limit-error { border-color: #dc3545 !important; animation: shake 0.2s 2; }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        50% { transform: translateX(5px); }
    }
</style>
</head>
<body>
<?php
    include_once 'nav.php';
$id_op = $_GET['IdOperation'];
//EN ENTREGA METER LA FIRMA Y LA LIGA PARA EL PROCESO DE LIQUIDACION 
$stages = ['BODEGA','SURTIDO', 'CARGA', 'INSTALACION', 'PRUEBA FUNCIONAMIENTO', 'ENTREGA', 'RECOLECCION', 'LAVADO', 'LIMPIEZA', 'DOBLADO', 'ALMACENADO'];

$stage = $db->query("SELECT status FROM operation_master WHERE id_operation = $id_op")->fetch();


$currentStage = $stage['status'];
$nextIdx = array_search($currentStage, $stages) + 1;

$rows = $db->query("SELECT * FROM v_operation_checklist WHERE id_operation = $id_op AND stage = '".$stages[$nextIdx]."' ORDER BY id_product,id_accesory,id_accesory_base ")->fetchAll();

$items = [];
foreach ($rows as $row) {
    $pId = $row['id_product'];



    if (!isset($items[$pId])) {

    $query = "SELECT *  from products_images WHERE Product = ".$pId." ORDER BY Orden LIMIT 1";
    $stmtigm = $db->prepare($query);
    $stmtigm->execute();
    $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);
    if ($Img)
        $Img = $Img['Image'];        

        $items[$pId] = [
            'id'        => $row['id_checklist'],
            'name'      => $row['Product'],
            'image'     => $Img,
            'requested' => $row['requested_quantity'],
            'assorted'  => $row['assorted_quantity'],
            'children'  => []
        ];
    }
    if ($row['id_accesory_base'] || $row['id_accesory']) {
        
        if ($row['id_accesory_base'])
            $ItemId = $row['id_accesory_base'];
        else
            $ItemId = $row['id_accesory'];
        
        $query = "SELECT *  from products_images WHERE Product = ".$ItemId." ORDER BY Orden LIMIT 1";
        $stmtigm = $db->prepare($query);
        $stmtigm->execute();
        $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);
        if ($Img)
            $Img = $Img['Image'];        

        $items[$pId]['children'][] = [
            'id'       => $row['id_checklist'],
            'name'     => $row['Base'] ?? $row['Accesory'],
            'image'     => $Img,
            'type'     => $row['id_accesory_base'] ? 'BASE' : 'ACCESORIO',
            'requested' => $row['requested_quantity'],
            'assorted' => $row['assorted_quantity']
        ];
    }
} 

?>
<div id="api-feedback" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

<div class="container my-4">
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list-check me-2 text-warning"></i> Estatus actual [ <b id="current-stage"><?php echo $stage['status']?></b>  ] </h5>

        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 70px;">OK</th>
                            <th>Imagen</th>
                            <th>Producto / Componente</th>
                            <th class="text-center" style="width: 100px;">Pedido</th>
                            <th class="text-center" style="width: 180px;">Surtido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $pId => $item): ?>
                            <tr class="main-row border-bottom shadow-sm">
                                <td class="text-center">
                                    <input class="form-check-input row-check" type="checkbox" data-id="<?= $item['id'] ?>" disabled>
                                </td>
                                <td>
                                    <img src="ajax/tmp/<?= $item['image'] ?>" class="rounded border" width="55" height="55" >
                                </td>
                                <td>
                                    <div class="fw-bold"><?= $item['name'] ?></div>
                                    <span class="badge bg-secondary x-small">PRODUCTO</span>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-center bg-light fw-bold requested-qty" value="<?= $item['requested'] ?>" readonly>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm custom-qty-group">
                                        <button class="btn btn-outline-secondary btn-minus px-3" type="button"><i class="fas fa-minus"></i></button>
                                        <input type="number" class="form-control text-center assorted-qty fw-bold" value="<?= $item['assorted'] ?>" min="0" max="<?= $item['requested'] ?>" inputmode="numeric">
                                        <button class="btn btn-outline-secondary btn-plus px-3" type="button"><i class="fas fa-plus"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <?php foreach ($item['children'] as $child): ?>
                                <tr class="bg-light border-bottom border-white">
                                    <td class="text-center">
                                        <input class="form-check-input row-check" type="checkbox" data-id="<?= $child['id'] ?>" disabled>
                                    </td>
                                    <td>
                                        <img src="ajax/tmp/<?= $child['image'] ?>" class="rounded border ms-3" width="40" height="40" >
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-muted"><?= $child['name'] ?></div>
                                        <span class="badge <?= ($child['type'] == 'BASE') ? 'bg-info text-dark' : 'bg-warning text-dark' ?> x-small">
                                            <?= $child['type'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-center bg-transparent border-0 requested-qty" value="<?= $child['requested'] ?>" readonly>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm px-3 custom-qty-group">
                                            <button class="btn btn-outline-secondary btn-minus" type="button"><i class="fas fa-minus small"></i></button>
                                            <input type="number" class="form-control text-center assorted-qty bg-white" value="<?= $child['assorted'] ?>" min="0" max="<?= $child['requested'] ?>" inputmode="numeric">
                                            <button class="btn btn-outline-secondary btn-plus" type="button"><i class="fas fa-plus small"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row p-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold"><i class="fas fa-camera me-1"></i> Foto de Evidencia (Opcional)</label>
                    <input type="file" id="evidence-file" class="form-control form-control-sm" accept="image/*" capture="environment">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold"><i class="fas fa-map-marker-alt me-1"></i> Ubicación</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="geo-location" class="form-control bg-light" placeholder="Obteniendo coordenadas..." readonly>
                        <button class="btn btn-outline-secondary" type="button" id="btn-refresh-geo"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold"><i class="fas fa-sticky-note me-1"></i> Notas de la Etapa</label>
                    <textarea id="stage-notes" class="form-control form-control-sm" rows="5" placeholder="Observaciones adicionales..." ></textarea>
                </div>
            </div>              

        </div>

        <div class="card-footer bg-white py-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div id="status-text" class="small text-muted">
                    <i class="fas fa-info-circle me-1"></i> Completa el surtido para avanzar.
                </div>
                <button id="btn-update-stage" class="btn btn-success px-5 fw-bold shadow-sm" disabled>
                    <span id="btn-text">MARCAR COMO <span id="next-stage-label">CARGA</span></span>
                    <span id="btn-loader" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>






    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

    const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
    const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
    const TOKEN = localStorage.getItem('apiToken'); 

    function attemptLogin(username, password) {
        $.ajax({
            url: LOGIN_URL,
            type: 'POST',
            contentType: 'application/json', // Indica que enviamos JSON
            data: JSON.stringify({
                username: username,
                password: password
            }),
            success: function(response) {
                // Éxito: Guardar el token para futuras llamadas
                const jwtToken = response.jwt;
                //console.log('Login exitoso. Token:', jwtToken);
                
                // *** Almacena el token de forma segura (ej: localStorage) ***
                localStorage.setItem('apiToken', jwtToken); 
                
            },
            error: function(xhr, status, error) {
                // Error: Credenciales inválidas (401) o error del servidor
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido.';
                //console.error('Error de login:', errorMessage);
                //alert('Fallo el inicio de sesión: ' + errorMessage);
            }
        });
    }    


    $(document).ready(function() {
        attemptLogin('admin', '1234'); 
        if (TOKEN) {
            //getRecordData(1); 
        } else {
            console.warn('No se encontró el token. Necesita iniciar sesión primero.');
        }
    });


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

    function getCoords() {
        if (navigator.geolocation) {
            $('#geo-location').val("Localizando...");
            navigator.geolocation.getCurrentPosition(function(position) {
                const coords = `${position.coords.latitude}, ${position.coords.longitude}`;
                $('#geo-location').val(coords);
            }, function(error) {
                $('#geo-location').val("Permiso denegado / GPS apagado");
            });
        }
    }


$(document).ready(function() {



    const stages = ['SURTIDO', 'CARGA', 'INSTALACION', 'PRUEBA FUNCIONAMIENTO', 'ENTREGA', 'RECOLECCION', 'LAVADO', 'LIMPIEZA', 'DOBLADO', 'ALMACENADO'];

    getCoords();
    $('#btn-refresh-geo').click(getCoords);


    // 1. Lógica de botones +/-
    $('.btn-plus, .btn-minus').on('click', function() {
        const row = $(this).closest('tr');
        const input = row.find('.assorted-qty');
        const max = parseInt(row.find('.requested-qty').val());
        let current = parseInt(input.val()) || 0;

        if ($(this).hasClass('btn-plus')) {
            if (current < max) {
                input.val(current + 1).trigger('change');
            } else {
                input.addClass('qty-limit-error');
                setTimeout(() => input.removeClass('qty-limit-error'), 400);
            }
        } else {
            if (current > 0) {
                input.val(current - 1).trigger('change');
            }
        }
    });

    // 2. Validación de reglas de negocio
    function validateLogic(row) {
        const req = parseInt(row.find('.requested-qty').val());
        let ass = parseInt(row.find('.assorted-qty').val()) || 0;
        const check = row.find('.row-check');

        // Impedir que surtan más de lo pedido
        if (ass > req) {
            ass = req;
            row.find('.assorted-qty').val(req);
        }

        if (ass === req && req > 0) {
            check.prop('checked', true);
            row.addClass('table-success-row');
        } else {
            check.prop('checked', false);
            row.removeClass('table-success-row');
        }

        checkGlobalStatus();
    }

    function checkGlobalStatus() {
        const total = $('.row-check').length;
        const ready = $('.row-check:checked').length;
        const currentStage = $('#current-stage').text().trim();
        const nextIdx = stages.indexOf(currentStage) + 1;

        if (nextIdx < stages.length) {
            $('#next-stage-label').text(stages[nextIdx]);
        }

        $('#btn-update-stage').prop('disabled', ready < total);
    }

    $('.assorted-qty').on('change keyup', function() {
        validateLogic($(this).closest('tr'));
    });


$('#btn-update-stage').on('click', function() {
        const btn = $(this);
        const currentStage = $('#current-stage').text().trim();
        const actualStage  = stages[stages.indexOf(currentStage) + 1];
        const nextStage = stages[stages.indexOf(currentStage) + 2];
        
        // Creamos el objeto FormData para soportar el archivo de imagen
        let formData = new FormData();
        
        // Datos básicos
        formData.append('id_op', <?php echo $id_op?>);
        formData.append('currentStage', actualStage);
        formData.append('next_stage', nextStage);
        formData.append('coords', $('#geo-location').val());
        formData.append('notes', $('#stage-notes').val());

        // Archivo de imagen (si existe)
        const fileInput = $('#evidence-file')[0].files[0];
        if (fileInput) {
            formData.append('evidence_img', fileInput);
        }

        // Datos del checklist
        let items = [];
        $('.row-check:checked').each(function() {
            const row = $(this).closest('tr');
            if($(this).data('id')) {
                items.push({
                    id: $(this).data('id'),
                    qty: row.find('.assorted-qty').val()
                });
            }
        });
        formData.append('items', JSON.stringify(items));

        // UI Feedback: Cargando
        btn.prop('disabled', true);
        $('#btn-text').addClass('d-none');
        $('#btn-loader').removeClass('d-none');

        $.ajax({
            url: API_BASE_URL + 'api/process_stage_change/',
            method: 'POST',
            data: formData,
            headers: { 'Authorization': 'Bearer ' + TOKEN },            
            processData: false, // Vital para FormData
            contentType: false, // Vital para FormData
            success: function(response) {
                //showToast('Etapa ' + nextStage + ' registrada correctamente', 'success');
                //setTimeout(() => location.reload(), 1500);
                history.back();
            },
            error: function() {
                showToast('Error al procesar el cambio', 'danger');
                btn.prop('disabled', false);
                $('#btn-text').removeClass('d-none');
                $('#btn-loader').addClass('d-none');
            }
        });
    });

    function resetBtn(btn) {
        btn.prop('disabled', false);
        $('#btn-text').removeClass('d-none');
        $('#btn-loader').addClass('d-none');
    }

    function showToast(msg, type) {
        const toast = `<div class="alert alert-${type} alert-dismissible fade show shadow" role="alert">
            ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        $('#api-feedback').append(toast);
        setTimeout(() => $('.alert').alert('close'), 4000);
    }

    // Inicializar al cargar
    $('tr').each(function() { validateLogic($(this)); });
});


</script>
</body>
</html>