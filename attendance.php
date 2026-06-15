<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'valid_login.php';
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();

$Idioma = $_SESSION['Idioma'];
$query = "select Traduccion FROM  programas_traduccion where Programa = 'leads' AND Idioma = ? ORDER BY Id";            
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
        body { background-color: #f4f7f6; }
        .sticky-search { position: sticky; top: 0; z-index: 1020; background: rgba(244, 247, 246, 0.95); padding: 20px 0; }
        .table-container { background: white; border-radius: 12px; overflow: hidden; }
        /* Bordes laterales de colores según status */
        .status-parcial { background: #6c757d !important; }        
        .status-cotizado { background: #17a2b8 !important; }        
        .status-confirmado { background: #28a745 !important; }
        .status-pendiente { background: #ffc107 !important; }
        .status-completado { background: #007bff !important; }
        .status-cancelado { background: #dc3545 !important; }

.clickable-row:hover {
    background-color: rgba(13, 110, 253, 0.05) !important; /* Un azul muy tenue */
    transition: background-color 0.2s ease;
}        

    </style>
</head>
<body class="bg-light">

<?php
    include_once 'nav.php';
?>
<br>
<br>


<div class="container vh-100 d-flex justify-content-center align-items-center" style="background-color: #f8f9fa;">

    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 400px; border-radius: 12px; background-color: #ffffff;">
        
        <div class="card-header border-0 bg-transparent text-center pt-5 pb-4">
            <h5 class="fw-semibold text-dark mb-1" style="letter-spacing: -0.5px; font-size: 1.4rem;">Asistencia</h5>
            <p class="text-muted small mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">REGISTRO DE JORNADA</p>
        </div>
        
        <div class="card-body px-4 pb-5 pt-0">
            
            <div id="alertContainer" class="small"></div>

            <form id="attendanceForm">
                <div class="mb-4">
                    <label for="operator_id" class="form-label text-muted fw-medium small mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">COLABORADOR</label>
                    <select class="form-select custom-minimal-select py-2.5" id="operator_id" required>
                        <option value="">Selecciona tu nombre...</option>
                        <?php
                        if ($_SESSION['role_id'] == "ADMIN")
                            $res = $db->query("SELECT Id, Nombres, Apellidos FROM operators WHERE Estatus = 'A'  OR Estatus IS NULL");
                        else    
                            $res = $db->query("SELECT Id, Nombres, Apellidos FROM operators WHERE Estatus = 'A' AND Usuario = '".$_SESSION['user']."' OR Estatus IS NULL");
                        foreach ($res as $row) {
                            echo "<option value='{$row['Id']}'>{$row['Nombres']} {$row['Apellidos']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <input type="hidden" id="latitud" name="latitud">
                <input type="hidden" id="longitud" name="longitud">

                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-minimal btn-minimal-success w-100 py-3 btn-marcar" data-accion="entrada">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Entrada
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-minimal btn-minimal-danger w-100 py-3 btn-marcar" data-accion="salida">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Salida
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-4">
                <span class="text-muted small d-inline-flex align-items-center" style="font-size: 0.78rem; font-weight: 400;">
                    <i id="geoIcon" class="fa-solid fa-circle text-warning me-2" style="font-size: 6px;"></i>
                    <span id="geoStatus">Localizando dispositivo...</span>
                </span>
            </div>
            
        </div>
    </div>
</div>

<style>
    /* Reset de bordes y enfoque para el selector */
    .custom-minimal-select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background-color: #fff;
        color: #1e293b;
        font-size: 0.95rem;
        transition: all 0.2s ease-in-out;
    }
    .custom-minimal-select:focus {
        border-color: #0f172a;
        box-shadow: none;
    }

    /* Estilo base de los botones planos */
    .btn-minimal {
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    
    /* Botón Entrada: Verde sutil orgánico */
    .btn-minimal-success {
        background-color: #f0fdf4;
        color: #166534;
    }
    .btn-minimal-success:hover {
        background-color: #dcfce7;
        color: #15803d;
    }

    /* Botón Salida: Rojo sutil orgánico */
    .btn-minimal-danger {
        background-color: #fef2f2;
        color: #991b1b;
    }
    .btn-minimal-danger:hover {
        background-color: #fee2e2;
        color: #b91c1c;
    }
    
    /* Estados activos */
    .btn-minimal:active {
        transform: scale(0.98);
    }
</style>



<script>

const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
const TOKEN = localStorage.getItem('apiToken'); 

$(document).ready(function() {
// 1. Solicitar coordenadas GPS inmediatamente al cargar la página
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            $('#latitud').val(position.coords.latitude);
            $('#longitud').val(position.coords.longitude);
            $('#geoStatus').text("Ubicación GPS lista y verificada").removeClass('text-danger').addClass('text-success');
        }, function(error) {
            $('#geoStatus').text("Error: Por favor activa el GPS de tu dispositivo.").addClass('text-danger');
            showAlert('danger', 'Es obligatorio permitir el uso del GPS para poder registrar asistencia.');
        }, { enableHighAccuracy: true });
    } else {
        $('#geoStatus').text("Tu navegador no soporta geolocalización.");
    }

    // 2. Evento Click de los botones
    $('.btn-marcar').click(function() {
        let opId = $('#operator_id').val();
        let lat = $('#latitud').val();
        let lng = $('#longitud').val();
        let accion = $(this).data('accion');

        if (!opId) {
            showAlert('warning', 'Por favor, selecciona un trabajador.');
            return;
        }

        if (!lat || !lng) {
            showAlert('danger', 'No se han detectado tus coordenadas GPS. Inténtalo de nuevo.');
            return;
        }

        // Deshabilitar botones temporalmente durante la petición
        $('.btn-marcar').prop('disabled', true);

        // Envío Ajax
        $.ajax({
            url:  API_BASE_URL + 'attendance/',
            type: 'POST',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: {
                operator_id: opId,
                latitud: lat,
                longitud: lng,
                accion: accion
            },
            success: function(response) {
                if (response.status === 'success') {
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                }
                $('.btn-marcar').prop('disabled', false);
            },
            error: function() {
                showAlert('danger', 'Ocurrió un error interno en el servidor de asistencia.');
                $('.btn-marcar').prop('disabled', false);
            }
        });
    });

    // Función auxiliar para alertas Bootstrap estilizadas
    function showAlert(type, message) {
        let alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`;
        $('#alertContainer').html(alertHtml);
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