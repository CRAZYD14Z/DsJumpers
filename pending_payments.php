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
<body>

<?php
    include_once 'nav.php';
?>
<br>
<br>
<div class="container pb-5">
    <div class="sticky-search">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-2">
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-transparent"><i class="bi bi-search text-primary"></i></span>
                            <input type="text" id="txtSearch" class="form-control border-0 shadow-none" placeholder="<?php echo Trd(1)?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container shadow-sm border">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4"><?php echo Trd(2)?></th>
                        <th class="ps-4"><?php echo Trd(12)?></th>
                        <th><?php echo Trd(3)?></th>
                        <th><?php echo Trd(4)?></th>
                        <th><?php echo Trd(5)?></th>
                        <th class="text-end pe-4"><?php echo Trd(13)?></th>
                        <th class="text-end pe-4"><?php echo Trd(6)?></th>
                    </tr>
                </thead>
                <tbody id="leadsData">
                </tbody>
            </table>
        </div>
    </div>

    <div id="loadingIndicator" class="text-center my-4" style="display:none;">
        <div class="spinner-grow text-primary" role="status"></div>
        <p class="text-muted small"><?php echo Trd(7)?></p>
    </div>
</div>

<script>

const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
const TOKEN = localStorage.getItem('apiToken'); 

/*
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
*/

$(document).ready(function() {
/*
    attemptLogin('admin', '1234');   
    if (TOKEN) {
        //getRecordData(1); 
    } else {
        console.warn('No se encontró el token. Necesita iniciar sesión primero.');
    }        
*/
    let currentPage = 1;
    let isFetching = false;
    let noMoreData = false;
    let timer;

    // Función Principal con AJAX
    function fetchLeads(reset = false) {
        if (isFetching || (noMoreData && !reset)) return;

        if (reset) {
            currentPage = 1;
            noMoreData = false;
            $('#leadsData').empty();
        }

        isFetching = true;
        $('#loadingIndicator').fadeIn();

        $.ajax({
            url:  API_BASE_URL + 'pending_payments/',
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: {
                page: currentPage,
                search: $('#txtSearch').val()
            },
            success: function(response) {
                if (response.length === 0) {
                    noMoreData = true;
                    if (currentPage === 1) {
                        $('#leadsData').html('<tr><td colspan="5" class="text-center py-5 text-muted"><?php echo Trd(8)?></td></tr>');
                    }
                } else {
                    renderTable(response);
                    currentPage++;
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la petición:", error);
                alert("<?php echo Trd(9)?>");
            },
            complete: function() {
                isFetching = false;
                $('#loadingIndicator').fadeOut();
            }
        });
    }

    // Renderizar filas con jQuery
function renderTable(data) {
    let rows = '';
    $.each(data, function(i, item) {
        const statusClass = "status-" + item.Status.toLowerCase().replace(/\s/g, '');
        const badgeClass = getBadgeColor(item.Status);
        
        // Agregamos data-id para identificar el registro al hacer clic
        // Agregamos estilo de cursor pointer para que el usuario sepa que es cliqueable
        rows += `
            <tr class="${statusClass} clickable-row" data-id="${item.Id}" style="cursor: pointer;">
                <td class="ps-4">
                    <div class="fw-semibold">#${item.Folio}</div>
                    <div class="small text-muted">${item.FechaCreacion}</div>
                </td>
                <td class="ps-4">
                    <div class="small text-muted italic">${item.StartDateTime}</div>
                    <div class="small text-muted italic">${item.EndDateTime}</div>
                </td>  
                <td>
                    <div class="fw-bold text-dark">${item.NombreMostrar}</div>
                    <div class="small text-muted italic">${item.Organization > 0 ? '<?php echo Trd(10)?>' : '<?php echo Trd(11)?>'}</div>
                </td>
                <td>
                        <div class="small text-secondary">${item.DeliveryDateTime}</div>
                        <div class="small text-secondary">${item.Lugar}</div>
                        <div class="small text-secondary">${item.Ciudad}, ${item.Estado}</div>
                </td>
                <td><span class="badge rounded-pill ${badgeClass}">${item.Status}</span></td>
                <td class="text-end pe-4 fw-bold text-dark">$${parseFloat(item.Balance).toFixed(2)}</td>
                <td class="text-end pe-4 fw-bold text-dark">$${parseFloat(item.Total).toFixed(2)}</td>

            </tr>
        `;
    });
    $('#leadsData').append(rows);
}

    function getBadgeColor(status) {
        const s = status.toLowerCase();
        if (s.includes('parcial') || s.includes('draft') ) return 'status-parcial';
        if (s.includes('cotizado') || s.includes('quoted')) return 'status-cotizado';
        if (s.includes('confirmado') || s.includes('confirmed')) return 'status-confirmado';
        if (s.includes('pendiente') || s.includes('pending')) return 'status-pendiente';
        if (s.includes('completo') || s.includes('complete')) return 'status-completo';
        if (s.includes('cancelado') || s.includes('canceled')) return 'status-cancelado';
        return 'text-bg-secondary';
    }

    // Evento Scroll (Infinite Scroll)
    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
            fetchLeads();
        }
    });

    // Evento de Búsqueda (Debounce para no saturar el servidor)
    $('#txtSearch').on('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            fetchLeads(true);
        }, 500);
    });

    // Delegación de eventos para filas dinámicas
    $('#leadsData').on('click', '.clickable-row', function() {
        const leadId = $(this).data('id'); // Obtenemos el ID del atributo data-id
        
        if (leadId) {
            // Redirección a la página de detalles
            window.location.href = `payments.php?IdLead=${leadId}`;
        }
    });    
    // Carga inicial
    fetchLeads();
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