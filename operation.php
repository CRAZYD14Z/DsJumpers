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
$Operadores ='';
$query ="SELECT Id, Nombres, Apellidos  from operators WHERE Estatus = 'A' AND Tipo = 'DRIVER'";
$stmt = $db->prepare($query);
$stmt->execute();      
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);       
if ($resultados) {
    foreach ($resultados as $registro) {
        $Operadores.='{ Id: '.$registro['Id'].', Nombre: "'.$registro['Nombres'].' '.$registro['Apellidos'].'" },';
    }
}

$asignaciones ='';
$query ="SELECT id_vehicle, DATE_FORMAT(StartDateTime, '%Y-%m-%d') as StartDateTime, id_driver from v_operations WHERE id_driver > 0 ";
$stmt = $db->prepare($query);
$stmt->execute();      
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);       
if ($resultados) {
    foreach ($resultados as $registro) {
        $asignaciones.='{ IdVehiculo: '.$registro['id_vehicle'].', Fecha: "'.$registro['StartDateTime'].'", IdOperador: '.$registro['id_driver'].' },';
    }
}


include_once 'head.php';
?>
<style>
        body { background-color: #f4f7f6; }

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
        /* Efecto de foco en el select */
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        /* Redondear bordes del modal */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }

        /* Estilo para el encabezado */
        .modal-header {
            border-bottom: 1px solid #f0f0f0;
        }        

    </style>
</head>
<body>

<?php
    include_once 'nav.php';
?>

<br>
<br>

<div class="container my-5">
    <div class="card shadow">
        <div class="card-header text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-black">Reporte de Operaciones: Lavado, Limpieza y Reparación</h4>
        </div>
    </div>     
       
<div class="container pb-5">
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
</div>

<div class="modal fade" id="modalOperadores" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold text-dark">
          <i class="bi bi-person-badge me-2"></i>Asignar Personal
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body p-4">
        <div class="text-center mb-4">
            <div class="display-6 text-primary mb-2">
                <i class="bi bi-truck"></i>
            </div>
            <p class="text-muted small">Seleccione un operador disponible para la unidad y fecha seleccionada.</p>
        </div>

        <div class="form-floating">
          <select class="form-select border-primary-subtle" id="selectOperador" aria-label="Selección de operador">
            </select>
          <label for="selectOperador">Operador disponible</label>
        </div>
        
        <div id="infoRuta" class="mt-3 p-2 bg-light rounded-3 small text-center text-secondary d-none">
            </div>
      </div>

      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btnGuardarAsignacion" class="btn btn-primary px-4 fw-bold">
          <i class="bi bi-check2-circle me-1"></i>Confirmar
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalRutas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold text-dark">
          <i class="bi bi-person-badge me-2"></i>Asignar a otra Ruta
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body p-4">
        <div class="text-center mb-4">
            <div class="display-6 text-primary mb-2">
                <i class="bi bi-truck"></i>
            </div>
            <p class="text-muted small">Seleccione la ruta a la cual se asigará.</p>
        </div>

        <div class="form-floating">
          <select class="form-select border-primary-subtle" id="selectRoute" aria-label="Selección de operador">
            </select>
          <label for="selectRoute">Ruta disponible</label>
        </div>
        
        <div id="infoRuta" class="mt-3 p-2 bg-light rounded-3 small text-center text-secondary d-none">
            </div>
      </div>

      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btnGuardarAsignacionRuta" class="btn btn-primary px-4 fw-bold">
          <i class="bi bi-check2-circle me-1"></i>Confirmar
        </button>
      </div>
    </div>
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

$(document).on('click', '.btn-ejecutar-carga', function(e) {
    e.stopPropagation();
    const vehiculoId = $(this).data('vid');
    const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
    const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
    var parts = fechaInicial.split(" ");     
    const url = `load_emulator.php?VehiculoId=${vehiculoId}&FechaInicial=${encodeURIComponent(parts[0])}`;
    window.location.href = url;
});



$(document).on('click', '.btn-chofer-ruta', function(e) {
    e.stopPropagation();
    const vehiculoId = $(this).data('vid');
    const listaFechas = $(this).data('fechas'); // Es el array de StartDateTime
    const fechaInicial = listaFechas.length > 0 ? listaFechas[0] : '';
    var parts = fechaInicial.split(" ");     
    //const url = `load_emulator.php?VehiculoId=${vehiculoId}&FechaInicial=${encodeURIComponent(parts[0])}`;
    //window.location.href = url;
    //alert('Asignar Chofer')
    abrirModalAsignacion(vehiculoId, parts[0])
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

// Datos iniciales
let operadores = [<?php echo $Operadores;?>];

let asignaciones = [ <?php echo $asignaciones;?>];

let rutas = [];


function abrirAsignacion(event,date,route,Id_operation){

    date = date.substring(0, 10); 

    event.stopPropagation();
    //alert(date)
    //alert(route)
    //alert(JSON.stringify(rutas))
    //alert(Id_operation)
    const $select = $('#selectRoute');    

    $select.empty().append('<option value="" selected disabled>Seleccione una ruta...</option>');

        rutas.forEach(op => {
            if (op.NombreChofer == undefined )
                op.NombreChofer = 'S/A';
            //alert(op.Fecha +" "+  date)
            if (op.Fecha == date){
                //alert(op.Ruta +" "+  route)
                if (op.Ruta != route)
                    $select.append(`<option value="${op.Ruta}"> ${op.nombreVehiculo}  ${op.placas}  - ${op.NombreChofer} </option>`);
            }
        });    
    
    var numeroOpciones = $('#selectRoute option').length; 
    if (numeroOpciones == 1){
        $select.append('<option value="">⚠️ No hay rutas alternas para asignar</option>');
        $('#btnGuardarAsignacionRuta').prop('disabled', true);
    }
    else{
        $('#btnGuardarAsignacionRuta').prop('disabled', false);
    }

    $('#btnGuardarAsignacionRuta').off('click').on('click', function() {
        const idRoute = parseInt($select.val());
        if (idRoute) {
            reasignarRuta(Id_operation, date, idRoute);
            // Efecto visual antes de cerrar
            $(this).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
            setTimeout(() => {
                $('#modalOperadores').modal('hide');
                $(this).html('<i class="bi bi-check2-circle me-1"></i>Confirmar');
            }, 600);
        }
    });    
    $('#modalRutas').modal('show');
}


function reasignarRuta(Id_operation, date, idRoute) {

    let formData = new FormData();
    formData.append('idOperation', Id_operation);
    formData.append('date', date);
    formData.append('idRoute', idRoute);
    $.ajax({
        url: API_BASE_URL + 'api/reassign_route/',
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

    //console.log("Asignación exitosa:", asignaciones);
    // Aquí puedes llamar a una función para refrescar tu tabla o vista

}


function abrirModalAsignacion(IdVehiculo, fecha) {
    const ocupados = asignaciones.filter(a => a.Fecha === fecha).map(a => a.IdOperador);
    const disponibles = operadores.filter(op => !ocupados.includes(op.Id));

    const $select = $('#selectOperador');
    const $info = $('#infoRuta');

    // Mostrar info de contexto en el modal
    //$info.html(`IdVehiculo: <b>#${IdVehiculo}</b> | Fecha: <b>${fecha}</b>`).removeClass('d-none');
    $info.html(` Fecha: <b>${fecha}</b>`).removeClass('d-none');

    $select.empty().append('<option value="" selected disabled>Seleccione una persona...</option>');

    if (disponibles.length === 0) {
        $select.append('<option value="">⚠️ No hay operadores libres</option>');
        $('#btnGuardarAsignacion').prop('disabled', true);
    } else {
        $('#btnGuardarAsignacion').prop('disabled', false);
        disponibles.forEach(op => {
            $select.append(`<option value="${op.Id}">👤 ${op.Nombre}</option>`);
        });
    }

    $('#btnGuardarAsignacion').off('click').on('click', function() {
        const idOp = parseInt($select.val());
        if (idOp) {
            agregarAsignacion(IdVehiculo, fecha, idOp);
            // Efecto visual antes de cerrar
            $(this).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
            setTimeout(() => {
                $('#modalOperadores').modal('hide');
                $(this).html('<i class="bi bi-check2-circle me-1"></i>Confirmar');
            }, 600);
        }
    });

    $('#modalOperadores').modal('show');
}

/**
 * Agrega el registro al arreglo de asignaciones
 */
function agregarAsignacion(idVehiculo, fecha, idOperador) {
    asignaciones.push({
        IdVehiculo: idVehiculo,
        Fecha: fecha,
        IdOperador: idOperador
    });


    let formData = new FormData();
    formData.append('vehiculoId', idVehiculo);
    formData.append('fecha', fecha);
    formData.append('operadorId', idOperador);
    $.ajax({
        url: API_BASE_URL + 'api/assign_operator/',
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

    //console.log("Asignación exitosa:", asignaciones);
    // Aquí puedes llamar a una función para refrescar tu tabla o vista

}

// Variable global para almacenar la ubicación actual
let posicionActual = null;

// Intentar obtener la ubicación apenas cargue el script
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        (position) => {
            posicionActual = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            console.log("Ubicación de origen lista.");
        },
        (error) => {
            console.warn("No se pudo pre-cargar la ubicación:", error.message);
        },
        { enableHighAccuracy: true, timeout: 5000 }
    );
}

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
        const grupoKey = `${item.id_vehicle}_${item.vehiculo}_${item.placas}_${item.StartDateTime}`;
        
        if (!grupos[grupoKey]) {
            grupos[grupoKey] = {
                id_vehicle: item.id_vehicle,
                nombreVehiculo: item.vehiculo,
                placas: item.placas,
                
                id_driver: item.id_driver,
                NombresChofer: item.NombresChofer,
                ApellidosChofer: item.ApellidosChofer,
                Date:item.StartDateTime,
                items: []
            };

            var SDate = item.StartDateTime;
            SDate = SDate.substring(0, 10); 

            rutas.push({
                IdVehiculo: item.id_vehicle,
                nombreVehiculo: item.vehiculo,
                placas: item.placas,          
                NombresChofer: item.NombresChofer,      
                Fecha: SDate,
                Ruta: item.id_route
            });
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

        let todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "BODEGA");
        if (!todosParaChofer)
            todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "SURTIDO");
        if(!todosParaChofer)
            todosParaChofer = grupo.items.every(item => item.Status.toUpperCase() === "CARGA");        

        const botonEliminar = todosParaEliminar ? `<button class="btn btn-sm btn-danger ms-3 btn-eliminar-ruta" 
                       data-vid="${grupo.id_vehicle}" 
                       data-fechas='${JSON.stringify(fechasCarga)}'>
                   <i class="fa-solid fa-trash-can"></i> Eliminar Ruta
               </button>`:'';


        let  botonChofer= '';

        if (grupo.id_driver == 0){

            botonChofer = todosParaChofer ? `<button class="btn btn-sm btn-primary ms-3 btn-chofer-ruta" 
                        data-vid="${grupo.id_vehicle}" 
                        data-fechas='${JSON.stringify(fechasCarga)}'>
                    <i class="fa-solid fa-user"></i> Asigar Operador
                </button>`:'';        

        }


        // Fila de encabezado de grupo
        rows += `
            <tr class="table-light">
                <td colspan="5" class="py-3 ps-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-truck me-2 text-primary"></i>
                        <span class="fw-bold text-uppercase">
                            ${grupo.nombreVehiculo} 
                            <small class="text-muted ms-2">[${grupo.placas}]</small></br>
                            Operador:
                            <small class="text-muted ms-2">[${grupo.NombresChofer != null ? grupo.NombresChofer+' '+grupo.ApellidosChofer   : ''}]</small>                            
                        </span>
                        ${botonCargar}
                        ${botonEliminar}
                        ${botonChofer}

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
            
// Validamos si botonChofer tiene contenido
const isDisabled = (botonChofer != ""  && item.Status == 'CARGA') ? "disabled" : "";
const iddisplay = (botonChofer == ""  && item.Status == 'CARGA') ? "" : "d-none";
const clickClass = isDisabled ? "" : "clickable-row";
const cursorStyle = isDisabled ? "default" : "pointer";

rows += `
    <tr class="${statusClass} ${clickClass}" data-id="${item.Id_operation}" data-status="${item.Status}" style="cursor: ${cursorStyle};">
        <td class="ps-5">
            <div class="small text-muted">#${item.Folio}</div>
            <div class="fw-semibold">${item.StartDateTime}</div>
        </td>
        <td>
            <div class="fw-bold text-dark">${item.NombreMostrar}</div>
            <div class="small text-muted italic">${item.Organization > 0 ? '<?php echo Trd(10)?>' : '<?php echo Trd(11)?>'}</div>
        </td>
        <td>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div>${item.Lugar}</div>
                    <div class="small text-secondary">${item.Ciudad}, ${item.Estado}</div>
                </div>

                <button type="button" 
                        class="btn btn-sm btn-outline-primary ms-2"
                        
                        onclick="abrirAsignacion(event,'${item.StartDateTime}','${item.id_route}','${item.Id_operation}')">
                        <i class="fa-solid fa-up-down"></i> Reasignar
                </button>

                <button type="button" 
                        class="btn btn-sm btn-outline-info ms-2  ${iddisplay}" 
                        onclick="abrirRutaEnMaps(event, '${item.Lat}', '${item.Lng}')">
                        <i class="fa-solid fa-route"></i> Ruta
                </button>
            </div>
        </td>
        <td><span class="badge rounded-pill ${badgeClass}">${item.Status}</span></td>
        <td class="text-end pe-4 fw-bold text-dark">$${parseFloat(item.Total).toFixed(2)}</td>
    </tr>`;
        });
    });

    $('#leadsData').append(rows);
}

function abrirRutaEnMaps(event, destLat, destLng) {
    // Evita que al hacer clic en el botón se dispare el evento de la fila (tr)
    event.stopPropagation();

    // Verificamos si el navegador soporta geolocalización
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            // Éxito: Tenemos la ubicación del dispositivo
            const originLat = position.coords.latitude;
            const originLng = position.coords.longitude;
            
            // Construimos la URL con origen y destino
            const url = `https://www.google.com/maps/dir/?api=1&origin=${originLat},${originLng}&destination=${destLat},${destLng}&travelmode=driving`;
            
            window.open(url, '_blank');
        }, function(error) {
            // Error o permiso denegado: Abrimos maps solo con el destino (Google usará ubicación aproximada)
            console.warn("No se pudo obtener la ubicación exacta:", error.message);
            const urlFallback = `https://www.google.com/maps/dir/?api=1&destination=${destLat},${destLng}`;
            window.open(urlFallback, '_blank');
        });
    } else {
        // El navegador no soporta geolocalización
        const urlFallback = `https://www.google.com/maps/dir/?api=1&destination=${destLat},${destLng}`;
        window.open(urlFallback, '_blank');
    }
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
//        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
//            fetchLeads();
//        }
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

function abrirRutaEnMaps_(event, destLat, destLng) {
    event.stopPropagation(); // Evitamos el conflicto con la fila

    let url;
    const destino = `${destLat},${destLng}`;

    if (posicionActual) {
        // Usamos la ubicación que ya teníamos guardada
        const origen = `${posicionActual.lat},${posicionActual.lng}`;
        url = `https://www.google.com/maps/dir/?api=1&origin=${origen}&destination=${destino}&travelmode=driving`;
    } else {
        // Si por alguna razón no se ha cargado (el usuario fue muy rápido o denegó permiso)
        // Google Maps tratará de determinar el origen por su cuenta
        url = `https://www.google.com/maps/dir/?api=1&destination=${destino}`;
    }

    window.open(url, '_blank');
}

</script>


</body>
</html>