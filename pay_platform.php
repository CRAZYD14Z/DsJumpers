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
</head>
<body>
<?php
    include_once 'nav.php';
?>
<br><br>
<div class="container mt-5">
    <div class="row justify-content-center">
        <!-- Limitamos el ancho a la mitad de la pantalla (col-md-6) -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Configuración de plataforma de Pagos</div>
                <div class="card-body">
                    <form id="paymentForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Plataforma Activa</label>
                            <select class="form-select" id="pay_platform" name="pay_platform">
                                <option value="">Seleccione una opción...</option>
                                <option value="OPAY">OPAY</option>
                                <option value="SQUARE">SQUARE</option>
                            </select>
                        </div>

                        <!-- Campos OPAY -->
                        <div id="section_OPAY" class="payment-section d-none border p-3 rounded bg-light">
                            <h6 class="text-muted border-bottom pb-2">Datos OPAY</h6>
                            <div class="mb-2">
                                <label class="small">ID</label>
                                <input type="text" class="form-control form-control-sm" name="opay_id" id="opay_id">
                            </div>
                            <div class="mb-2">
                                <label class="small">Secret Key</label>
                                <input type="text" class="form-control form-control-sm" name="opay_secret" id="opay_secret">
                            </div>
                            <div class="mb-2">
                                <label class="small">Public Key</label>
                                <input type="text" class="form-control form-control-sm" name="opay_public" id="opay_public">
                            </div>
                        </div>

                        <!-- Campos SQUARE -->
                        <div id="section_SQUARE" class="payment-section d-none border p-3 rounded bg-light">
                            <h6 class="text-muted border-bottom pb-2">Datos SQUARE</h6>
                            <div class="mb-2">
                                <label class="small">ID</label>
                                <input type="text" class="form-control form-control-sm" name="square_id" id="square_id">
                            </div>
                            <div class="mb-2">
                                <label class="small">Local ID</label>
                                <input type="text" class="form-control form-control-sm" name="square_local" id="square_local">
                            </div>
                            <div class="mb-2">
                                <label class="small">Token</label>
                                <input type="text" class="form-control form-control-sm" name="square_token" id="square_token">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success" id="btnSave">
                                <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="btnText">Actualizar Configuración</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-primary text-white">
      <strong class="me-auto"><i class="fas fa-bell me-2"></i> Notificación</strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toast-message">
      </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

    const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
    const API_BASE_URL = '<?php echo URL_BASE;?>/api/';
    const TOKEN = localStorage.getItem('apiToken'); 


$(document).ready(function() {
    // 1. Cargar datos actuales al abrir el programa
    loadCurrentConfig();

    function loadCurrentConfig() {
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };        
        $.ajax({
            url: API_BASE_URL + 'get_pay_platform',
            type: 'GET',
            dataType: 'json',
            headers: misHeaders,    
            success: function(data) {
                if (data) {
                    // Seleccionar la plataforma y disparar el evento change para mostrar la sección
                    $('#pay_platform').val(data.pay_platform).trigger('change');
                    
                    // Llenar campos OPAY
                    $('#opay_id').val(data.opay.Id);
                    $('#opay_secret').val(data.opay.SecretKey);
                    $('#opay_public').val(data.opay.PublicKey);
                    
                    // Llenar campos SQUARE
                    $('#square_id').val(data.square.Id);
                    $('#square_local').val(data.square.LocalId);
                    $('#square_token').val(data.square.Token);
                }
            }
        });
    }

    // Manejar visibilidad de secciones
    $('#pay_platform').on('change', function() {
        let platform = $(this).val();
        $('.payment-section').addClass('d-none');
        if (platform) {
            $('#section_' + platform).removeClass('d-none');
        }
    });

    // Envío del formulario (igual que el anterior)
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        let $btn = $('#btnSave');
        let $spinner = $('#spinner');
        let $text = $('#btnText');
        var misHeaders = {
            'Authorization': 'Bearer ' + TOKEN
        };        
        $.ajax({
            url: API_BASE_URL + 'update_pay_platform',
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: misHeaders,    
            beforeSend: function() {
                // 1. Mostrar spinner y deshabilitar botón
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');
                $text.text(' Actualizando...'); // Opcional: cambiar el texto
            },            
            success: function(res) {
                if(res.status === 'success') {
                    mostrarToast("Configuración actualizada con éxito.", false);
                    
                } else {
                    mostrarToast('Error: ' + res.message, true);
                    
                }
            },
            error: function() {
                mostrarToast('Error crítico en el servidor.' , true);
                
            },
            complete: function() {
                // 2. Restaurar botón al finalizar (éxito o error)
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $text.text('Actualizar Configuración');
            }
        });
    });
});    

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

$(document).ajaxSuccess(function(event, xhr, settings) {
    const nuevoToken = xhr.getResponseHeader('Authorization-Update');
    if (nuevoToken) {
        localStorage.setItem('apiToken', nuevoToken);
        console.log("Token actualizado globalmente desde: " + settings.url);
    }
});    

</script>
</body>
</html>