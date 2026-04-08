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
                        <th><?php echo Trd(3)?></th>
                        <th><?php echo Trd(4)?></th>
                        <th><?php echo Trd(5)?></th>
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

$(document).on('click', '.btn-ejecutar-carga', function(e) {
    e.stopPropagation();
    const vehiculoId = $(this).data('vid');
    const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
    const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
    var parts = fechaInicial.split(" ");     
    const url = `load_emulator.php?VehiculoId=${vehiculoId}&FechaInicial=${encodeURIComponent(parts[0])}`;
    window.location.href = url;
});

$(document).on('click', '.btn-eliminar-ruta', function(e) {
    e.stopPropagation();
    const vehiculoId = $(this).data('vid');
    const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
    const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
    var parts = fechaInicial.split(" ");     


    let formData = new FormData();
    
    formData.append('vehiculoId', vehiculoId);
    formData.append('fecha', parts[0]);

    $.ajax({
        url: API_BASE_URL + 'api/delete_route/',
        method: 'POST',
        data: formData,
        headers: { 'Authorization': 'Bearer ' + TOKEN },            
        processData: false, // Vital para FormData
        contentType: false, // Vital para FormData
        success: function(response) {
            location.reload();
        },
        error: function() {
        }
    });        

});




$(document).ready(function() {

    attemptLogin('admin', '1234'); 
    
    if (TOKEN) {
        //getRecordData(1); 
    } else {
        console.warn('No se encontró el token. Necesita iniciar sesión primero.');
    }        

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
            url:  API_BASE_URL + 'operation/',
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

function renderTable(data) {
    let rows = '';
    const grupos = {};
    
    // 1. Agrupar los datos
    $.each(data, function(i, item) {
        const grupoKey = `${item.id_vehicle}_${item.vehiculo}_${item.Organization}_${item.placas}_${item.StartDateTime}`;
        
        if (!grupos[grupoKey]) {
            grupos[grupoKey] = {
                id_vehicle: item.id_vehicle,
                nombreVehiculo: item.vehiculo,
                placas: item.placas,
                organizacion: item.Organization,
                id_driver: item.id_driver,
                NombresChofer: item.NombresChofer,
                ApellidosChofer: item.ApellidosChofer,
                items: []
            };
        }
        grupos[grupoKey].items.push(item);
    });

    // 2. Renderizar los grupos
    $.each(grupos, function(key, grupo) {
        
        // VALIDACIÓN: ¿Todos los status son "CARGAR"?
        const todosParaCargar = grupo.items.every(item => item.Status.toUpperCase() === "SURTIDO");

        const fechasCarga = grupo.items.map(it => it.StartDateTime);
        
        // Creamos el botón solo si la condición se cumple
        const botonCargar = todosParaCargar 
            ? `<button class="btn btn-sm btn-success ms-3 btn-ejecutar-carga" 
                       data-vid="${grupo.id_vehicle}" 
                       data-fechas='${JSON.stringify(fechasCarga)}'>
                   <i class="fas fa-file-upload me-1"></i> Cargar Unidad
               </button>`
            : '';

        let todosParaEliminar = grupo.items.every(item => item.Status.toUpperCase() === "BODEGA");
        if (!todosParaEliminar)
            todosParaEliminar = grupo.items.every(item => item.Status.toUpperCase() === "SURTIDO");
        if(!todosParaEliminar)
            todosParaEliminar = grupo.items.every(item => item.Status.toUpperCase() === "CARGA");
/*
        let todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "BODEGA");
        if (!todosParaChofer)
            todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "SURTIDO");
        if(!todosParaChofer)
            todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "CARGA");        
*/
        const botonEliminar = todosParaEliminar ? `<button class="btn btn-sm btn-danger ms-3 btn-eliminar-ruta" 
                       data-vid="${grupo.id_vehicle}" 
                       data-fechas='${JSON.stringify(fechasCarga)}'>
                   <i class="fa-solid fa-trash-can"></i> Eliminar Ruta
               </button>`:'';
/*
        const botonChofer = todosParaChofer ? `<button class="btn btn-sm btn-primary ms-3 btn-chofer-ruta" 
                       data-vid="${grupo.id_vehicle}" 
                       data-fechas='${JSON.stringify(fechasCarga)}'>
                   <i class="fa-solid fa-user"></i> Asigar Operador
               </button>`:'';               
*/
        // Fila de encabezado de grupo
        rows += `
            <tr class="table-light">
                <td colspan="5" class="py-3 ps-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-truck me-2 text-primary"></i>
                        <span class="fw-bold text-uppercase">
                            ${grupo.nombreVehiculo} 
                            <small class="text-muted ms-2">[${grupo.placas}]</small>
                        </span>
                        
                        ${botonCargar}
                        ${botonEliminar}

                        <span class="badge bg-secondary ms-auto me-4">
                            ${grupo.organizacion > 0 ? 'Org: ' + grupo.organizacion : 'Particular'}
                        </span>
                    </div>
                </td>
            </tr>
        `;

        // Filas de items (Misma lógica anterior)
        $.each(grupo.items, function(j, item) {
            const statusClass = "status-" + item.Status.toLowerCase().replace(/\s/g, '');
            const badgeClass = getBadgeColor(item.Status);
            
            rows += `
                <tr class="${statusClass} clickable-row" data-id="${item.Id_operation}" data-status="${item.Status}" style="cursor: pointer;">
                    <td class="ps-5">
                        <div class="small text-muted">#${item.Folio}</div>
                        <div class="fw-semibold">${item.StartDateTime}</div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${item.NombreMostrar}</div>
                        <div class="small text-muted italic">${item.Organization > 0 ? '<?php echo Trd(10)?>' : '<?php echo Trd(11)?>'}</div>
                    </td>
                    <td>
                        <div>${item.Lugar}</div>
                        <div class="small text-secondary">${item.Ciudad}, ${item.Estado}</div>
                    </td>
                    <td><span class="badge rounded-pill ${badgeClass}">${item.Status}</span></td>
                    <td class="text-end pe-4 fw-bold text-dark">$${parseFloat(item.Total).toFixed(2)}</td>
                </tr>
            `;
        });
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
    const status = $(this).data('status'); // Obtenemos el ID del atributo data-id

    if (status == "SURTIDO")
        return;
    
    if (leadId) {
        // Redirección a la página de detalles
        window.location.href = `operations.php?IdOperation=${leadId}`;
    }
});    

    // Carga inicial
    fetchLeads();
});    

</script>


</body>
</html>