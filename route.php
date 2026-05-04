<?php
    ob_start();
    session_start(); 
    include_once 'valid_login.php';
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();

    $Idioma = $_SESSION['Idioma'];
    $query = "select Traduccion FROM  programas_traduccion where Programa = 'route' AND Idioma = ? ORDER BY Id";            
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
<link rel="stylesheet" href="css/route.css" />
</head>
<body>
    
<div class="app-shell">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-nav">
            <?php include_once 'nav.php'; ?>
        </div>
    </header>

    <!-- MAIN GRID -->
    <div class="main-grid">

        <!-- ── PANEL IZQUIERDO ───────────────────────────────────────── -->
        <aside class="panel-left">

            <!-- FECHA -->
            <div class="panel-section">
                <div class="panel-label">
                    <span class="step-num">0</span>
                    <i class="fas fa-calendar-alt"></i>
                    <?= Trd(1) ?>
                </div>
                <?php
                    $fecha_actual = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
                ?>
                <div class="date-input-wrap">
                    <input type="date" id="fecha-operacion"
                        value="<?php echo $fecha_actual; ?>"
                        min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- VEHÍCULOS -->
            <div class="panel-section">
                <div class="panel-label">
                    <span class="step-num">1</span>
                    <i class="fas fa-truck"></i>
                    <?= Trd(2) ?>
                    <span class="selection-counter">
                        <span class="counter-bubble" id="cnt-vehiculos">0</span>
                    </span>
                </div>
                <div id="lista-vehiculos"></div>
            </div>

            <!-- ENTREGAS -->
            <div class="panel-section" style="flex:1; display:flex; flex-direction:column; overflow:hidden; border-bottom:none;">
                <div class="panel-label">
                    <span class="step-num">2</span>
                    <i class="fas fa-boxes"></i>
                    <?= Trd(3) ?>
                    <span class="selection-counter">
                        <span class="counter-bubble" id="cnt-envios">0</span>
                    </span>
                </div>
                <div class="list-scroll" id="lista-envios"></div>
            </div>

            <!-- FOOTER ACCIÓN -->
            <div class="panel-footer">
                <button id="btn-optimizar" class="btn-optimizar">
                    <i class="fas fa-route"></i>
                    <?= Trd(4) ?>
                </button>
            </div>

        </aside>

        <!-- ── MAPA ──────────────────────────────────────────────────── -->
        <main class="map-wrap">
            <div id="map"></div>
            <!-- FAB móvil: optimizar desde vista mapa -->
            <button class="map-fab" id="map-fab-optimizar" style="display:none;" onclick="document.getElementById('btn-optimizar').click()">
                <i class="fas fa-route"></i> <?= Trd(5) ?>
            </button>
        </main>

        <!-- ── PANEL DERECHO ─────────────────────────────────────────── -->
        <aside class="panel-right">

            <!-- RUTAS SUGERIDAS -->
            <div id="panel-resultados" class="d-none" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
                <div class="panel-right-header">
                    <h6><i class="fas fa-map-marked-alt me-2" style="color:var(--accent-2)"></i><?= Trd(6) ?></h6>
                </div>
                <div class="panel-right-scroll">
                    <div id="controles-rutas"></div>
                </div>
                <div class="panel-footer">
                    <button class="btn-save-routes d-none" id="guardarRutaOptima" onclick="mostrarModalConfirmacion('optima')">
                        <i class="fas fa-floppy-disk"></i>
                        <?= Trd(7) ?>
                    </button>
                </div>
            </div>

            <!-- VACÍO (cuando no hay rutas) -->
            <div id="panel-vacio" class="d-flex flex-column align-items-center justify-content-center" style="flex:1;">
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-map-signs"></i></div>
                    <p><?= Trd(8) ?><br><?= Trd(9) ?> <strong><?= Trd(10) ?></strong>.</p>
                </div>
            </div>

            <!-- EDICIÓN MANUAL -->
            <div id="panel-orden-manual" class="d-none" style="padding: .75rem 1rem; border-top: 1px solid var(--border);">
                <div class="manual-header">
                    <span class="manual-header-title"><i class="fas fa-grip-vertical me-1"></i><?= Trd(11) ?></span>
                    <button class="btn-trazar" onclick="iniciarRutaManual()"><?= Trd(12) ?></button>
                </div>
                <div class="manual-body">
                    <small class="text-muted" style="font-size:10px; display:block; margin-bottom:.4rem;">
                        <?= Trd(13) ?>
                    </small>
                    <ul id="lista-orden-manual" class="mb-0 ps-0" style="list-style:none;"></ul>
                    <div class="manual-actions mt-2">
                        <button class="btn-save-manual d-none" id="guardarRutaManual" onclick="mostrarModalConfirmacion('manual')">
                            <i class="fas fa-floppy-disk"></i> <?= Trd(14) ?>
                        </button>
                        <button class="btn-clear-manual" id="limpiarRutaManual" onclick="limpiarRutaManual()">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>

        </aside>

    </div><!-- /.main-grid -->

    <!-- NAVEGACIÓN INFERIOR (solo móvil) -->
    <nav class="mobile-nav" style="display:none;">
        <button class="mobile-nav-btn mob-active" data-panel="left">
            <i class="fas fa-list-check"></i>
            <span><?= Trd(15) ?></span>
            <span class="nav-badge" id="nav-badge-sel" style="display:none;">0</span>
        </button>
        <button class="mobile-nav-btn" data-panel="map">
            <i class="fas fa-map"></i>
            <span><?= Trd(16) ?></span>
        </button>
        <button class="mobile-nav-btn" data-panel="right">
            <i class="fas fa-route"></i>
            <span><?= Trd(17) ?></span>
            <span class="nav-badge" id="nav-badge-rutas" style="display:none;">✓</span>
        </button>
    </nav>

</div><!-- /.app-shell -->

<div class="modal fade" id="modalConfirmarRuta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= Trd(18) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="textoConfirmacion"><?= Trd(19) ?></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-bs-dismiss="modal"><?= Trd(20) ?></button>
        <button type="button" class="btn btn-success" id="btnAceptarGuardado"><?= Trd(21) ?></button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCRw-m6FwodZdcIPw1rtAKWqvyziRm1ihM&callback=initMap" async defer></script>

<script>

/* ─── CONFIG ────────────────────────────────────────────────────────── */
const LOGIN_URL   = '<?php echo URL_BASE; ?>/api/login';
const API_BASE_URL = '<?php echo URL_BASE; ?>/api/';
const TOKEN = localStorage.getItem('apiToken');


$(document).ready(function() {

});

/* ─── CAMBIAR IDIOMA ─────────────────────────────────────────────────── */
$('.lang-option').on('click', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'cambiar_idioma.php',
        type: 'POST',
        data: { lang: $(this).data('lang') },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') location.reload();
        }
    });
});

function mostrarModalConfirmacion(tipo) {
    if (rutasParaGuardar.length === 0 && tipo == 'optima') {
        mostrarToast("<?= Trd(22) ?>", "warning");
        return;
    }

    tipoG = tipo;
    // Actualizar el texto del modal dinámicamente
    const mensaje = `<?= Trd(23) ?>`;
    $('#textoConfirmacion').html(mensaje);

    // Mostrar el modal
    const myModal = new bootstrap.Modal(document.getElementById('modalConfirmarRuta'));
    myModal.show();
}


$('#btnAceptarGuardado').on('click', function() {
    const btn = $(this);
    
    // Bloquear botón para evitar doble clic
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> <?= Trd(24) ?>');

    if (tipoG == 'optima'){
        guardarRutaOptima();
    }
    else{
        guardarRutaManual();
    }
});



<?php
/* ─── BACKEND: CARGA DE DATOS ─────────────────────────────────────────── */

function calcularCarga(array $productos, float $factorHolgura = 1.2): array
{
    $volumenTotalCm3 = 0.0;
    $pesoTotal       = 0.0;
    $detalle         = [];
    $errores         = [];

    foreach ($productos as $i => $producto) {
        $nombre   = $producto['nombre']   ?? "Producto #$i";
        $cantidad = (int)   ($producto['cantidad']  ?? 1);
        $peso     = (float) ($producto['peso']      ?? 0);

        if (!empty($producto['volumen']) && $producto['volumen'] > 0) {
            $volumenUnitario = (float) $producto['volumen'];
        } elseif (
            !empty($producto['alto'])    &&
            !empty($producto['ancho'])   &&
            !empty($producto['profundo'])
        ) {
            $volumenUnitario = (float)$producto['alto']
                             * (float)$producto['ancho']
                             * (float)$producto['profundo'];
        } else {
            $errores[] = "[$nombre] Sin volumen ni dimensiones. Omitido.";
            continue;
        }

        if ($cantidad <= 0) {
            $errores[] = "[$nombre] Cantidad inválida. Se usó 1.";
            $cantidad = 1;
        }

        $volumenLinea     = $volumenUnitario * $cantidad;
        $pesoLinea        = $peso * $cantidad;
        $volumenTotalCm3 += $volumenLinea;
        $pesoTotal       += $pesoLinea;

        $detalle[] = [
            'nombre'            => $nombre,
            'cantidad'          => $cantidad,
            'volumen_unitario'  => round($volumenUnitario, 4),
            'volumen_linea_cm3' => round($volumenLinea, 4),
            'peso_linea_kg'     => round($pesoLinea, 4),
        ];
    }

    $volumenConHolgura = $volumenTotalCm3 * $factorHolgura;

    return [
        'volumen_total_cm3' => round($volumenConHolgura, 4),
        'volumen_total_m3'  => round($volumenConHolgura / 1_000_000, 6),
        'peso_total_kg'     => round($pesoTotal, 4),
        'factor_holgura'    => $factorHolgura,
        'detalle'           => $detalle,
        'errores'           => $errores,
    ];
}

$stmt = $db->prepare("Select Lat,Lng FROM account WHERE Id = 1");
$stmt->execute();
$account = $stmt->fetch(PDO::FETCH_ASSOC); 
// Vehículos
$stmt = $db->prepare(
    "
        SELECT 
            id_vehicle AS id, 
            description AS nombre, 
            maxvolume AS volumen,
            weight AS peso, 
            hour_cost AS costoporhora, 
            km_cost AS costoporkm
        FROM vehicles 
        WHERE active = 1 
        AND id_vehicle NOT IN (
            SELECT id_vehicle 
            FROM daily_route 
            WHERE date = ?
        )
    "
);
$stmt->bindValue(1, $fecha_actual);
$stmt->execute();
$vehiculos_procesados = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $reg) {
    $vehiculos_procesados[] = [
        "id"           => "V" . $reg['id'],
        "nombre"       => trim($reg['nombre']),
        "volumen"      => (int)$reg['volumen'],
        "peso"         => (int)$reg['peso'],
        "costoporhora" => (float)$reg['costoporhora'],
        "costoporkm"   => (float)$reg['costoporkm'],
        "lat"          => $account['Lat'],
        "lng"          => $account['Lng']
    ];
}

// Envíos
$dataEnvios = [];
$stmt = $db->prepare(
    "SELECT * FROM v_operations
     WHERE DATE_FORMAT(DeliveryDateTime,'%Y-%m-%d') = ? AND vehiculo IS NULL"
);
$stmt->bindValue(1, $fecha_actual);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($resultados) {
    foreach ($resultados as $reg) {
        $stmt2 = $db->prepare(
            "SELECT * FROM v_operation_checklist WHERE Id_operation = ?"
        );
        $stmt2->bindValue(1, $reg['Id_operation']);
        $stmt2->execute();
        $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $productos_finales = [];
        if ($products) {
            foreach ($products as $prd) {
                if ($prd['id_accesory_base'] == 0 && $prd['id_accesory'] == 0 && $prd['load'] == 1) {
                    $productos_finales[] = [
                        "nombre"   => trim($prd['Product']),
                        "alto"     => (float)$prd['height'],
                        "ancho"    => (float)$prd['width'],
                        "profundo" => (float)$prd['depth'],
                        "volumen"  => (float)$prd['volume'],
                        "peso"     => (float)$prd['Weight'],
                        "cantidad" => (int)$prd['requested_quantity']
                    ];
                } elseif ($prd['id_accesory_base'] != 0 && $prd['id_accesory'] == 0 && $prd['load_base'] == 1) {
                    $productos_finales[] = [
                        "nombre"   => trim($prd['Base']),
                        "alto"     => (float)$prd['height_base'],
                        "ancho"    => (float)$prd['width_base'],
                        "profundo" => (float)$prd['depth_base'],
                        "volumen"  => (float)$prd['volume_base'],
                        "peso"     => (float)$prd['Weight_base'],
                        "cantidad" => (int)$prd['requested_quantity']
                    ];
                } elseif ($prd['id_accesory_base'] == 0 && $prd['id_accesory'] != 0 && $prd['load_accesory'] == 1) {
                    $productos_finales[] = [
                        "nombre"   => trim($prd['Accesory']),
                        "alto"     => (float)$prd['height_accesory'],
                        "ancho"    => (float)$prd['width_accesory'],
                        "profundo" => (float)$prd['depth_accesory'],
                        "volumen"  => (float)$prd['volume_accesory'],
                        "peso"     => (float)$prd['Weight_accesory'],
                        "cantidad" => (int)$prd['requested_quantity']
                    ];
                }
            }
        }
        $carga = calcularCarga($productos_finales, 1.2);
        $date = new DateTime($reg['DeliveryDateTime']);
        $horaInicio = $date->format('H:i');
        //AGREGAMOS 1 HORA PARA LA VENTANA DE ENTREGA
        $date->modify('+1 hour');
        $horaFin = $date->format('H:i');
        $dataEnvios[] = [
            "id"      => 'E' . $reg['Id_operation'],
            "lugar"   => $reg['Lugar'],
            "cliente" => $reg['NombreOrganizacion'] ?? ($reg['NombreCliente'] . " " . $reg['ApellidosCliente']),
            "volumen" => $carga['volumen_total_m3'],
            "peso"    => $carga['peso_total_kg'],
            "ventana" => [$horaInicio, $horaFin],
            "lat"     => $reg['Lat'],
            "lng"     => $reg['Lng'],
            "duracion"=> '360'
        ];
    }
}
?>

/* ─── DATOS ──────────────────────────────────────────────────────────── */
const dataVehiculos = <?php echo json_encode($vehiculos_procesados, JSON_UNESCAPED_UNICODE); ?>;
const dataEnvios    = <?php echo json_encode($dataEnvios,           JSON_UNESCAPED_UNICODE); ?>;


let vehiculo_s = ''; 
let envios_s = ''
let datosRuta_s = '';
let rutasParaGuardar = [];
let tipoG = '';

console.log("Vehículos:", dataVehiculos);
console.log("Envíos:",    dataEnvios);

if (!dataVehiculos.length) console.warn("Sin vehículos activos.");
if (!dataEnvios.length)    console.warn("Sin envíos para esta fecha.");

/* ─── MAPA ───────────────────────────────────────────────────────────── */
let map;
const colores = ['#f97316', '#0ea5e9', '#10b981', '#8b5cf6', '#ef4444', '#f59e0b'];

function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 20.6736, lng: -103.344 },
        zoom: 12,
        styles: [
            { featureType: "poi",            stylers: [{ visibility: "off" }] },
            { featureType: "transit.station",stylers: [{ visibility: "off" }] }
        ],
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
        zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_CENTER }
    });
    renderListas();
}

/* ─── RENDER LISTAS ──────────────────────────────────────────────────── */
function renderListas() {
    // Vehículos
    const $contV = $('#lista-vehiculos').empty();
    if (!dataVehiculos.length) {
        $contV.html('<div class="empty-state"><div class="empty-icon"><i class="fas fa-truck-slash"></i></div><p><?= Trd(35) ?></p></div>');
    }
    dataVehiculos.forEach(v => {
        const card = $(`
            <label class="vehicle-card" for="chk-v-${v.id}">
                <input class="check-vehiculo" type="checkbox" id="chk-v-${v.id}" value="${v.id}">
                <div class="vehicle-icon"><i class="fas fa-truck"></i></div>
                <div class="vehicle-info flex-grow-1">
                    <span class="v-name">${v.nombre}</span>
                    <span class="v-specs">${v.volumen} m³ &nbsp;·&nbsp; ${v.peso} kg</span>
                </div>
            </label>
        `);
        $contV.append(card);
    });

    // Envíos
    const $contE = $('#lista-envios').empty();
    if (!dataEnvios.length) {
        $contE.html('<div class="empty-state"><div class="empty-icon"><i class="fas fa-box-open"></i></div><p><?= Trd(36) ?></p></div>');
    }
    dataEnvios.forEach(e => {
        const card = $(`
            <label class="shipment-card" for="chk-e-${e.id}">
                <input class="form-check-input check-envio" type="checkbox" id="chk-e-${e.id}" value="${e.id}">
                <div class="shipment-icon"><i class="fas fa-box"></i></div>
                <div class="shipment-info">
                    <span class="s-lugar">${e.lugar}</span>
                    <span class="s-cliente">${e.cliente}</span>
                    <span class="s-time"><i class="far fa-clock me-1"></i>${e.ventana[0]} – ${e.ventana[1]}</span>
                    <span class="badge-ruta-envio" id="badge-ruta-${e.id}"></span>
                    <span class="meta-pill">${e.volumen} m³</span>
                    <span class="meta-pill">${e.peso} kg</span>
                </div>

            </label>
        `);
        $contE.append(card);
    });

    actualizarContadores();
}

/* ─── CONTADORES ─────────────────────────────────────────────────────── */
function actualizarContadores() {
    $('#cnt-vehiculos').text($('.check-vehiculo:checked').length);
    $('#cnt-envios').text($('.check-envio:checked').length);
}

$(document).on('change', '.check-vehiculo, .check-envio', function() {
    const $card = $(this).closest('.vehicle-card, .shipment-card');
    $card.toggleClass('selected', this.checked);
    actualizarContadores();
});

$(document).on('change', '.check-envio', actualizarPanelOrden);

/* ─── OPTIMIZAR ──────────────────────────────────────────────────────── */
$('#btn-optimizar').on('click', function() {
    $('#panel-orden-manual').addClass('d-none');

    const vSelected = $('.check-vehiculo:checked').map(function() { return $(this).val(); }).get();
    const eSelected = $('.check-envio:checked').map(function() { return $(this).val(); }).get();

    if (!vSelected.length || !eSelected.length) {
        mostrarToast('<?= Trd(25) ?>', 'warning');
        return;
    }

    const cacheID  = btoa(vSelected.sort().join('') + eSelected.sort().join(''));
    const cached   = localStorage.getItem(cacheID);

    if (cached) {
        console.log("Cache hit — usando ruta guardada.");
        procesarRespuesta(JSON.parse(cached));
        return;
    }

    const $btn = $(this);
    $btn.html('<i class="fas fa-sync spin me-2"></i>Consultando Google…').prop('disabled', true);

    const requestBody = buildOptimizeToursJson(dataVehiculos, dataEnvios, $('#fecha-operacion').val());

    $.ajax({
        url: 'ajax/procesar_ruta.php',
        method: 'POST',
        data: { json_google: JSON.stringify(requestBody) },
        success: (response) => {
            const data = JSON.parse(response);
            localStorage.setItem(cacheID, response);
            procesarRespuesta(data);
            $btn.html('<i class="fas fa-route me-2"></i><?= Trd(26) ?>').prop('disabled', false);
        },
        error: () => {
            mostrarToast('<?= Trd(34) ?>', 'danger');
            $btn.html('<i class="fas fa-route me-2"></i><?= Trd(26) ?>Armar Ruta Óptima').prop('disabled', false);
        }
    });
});

/* ─── BUILD JSON ─────────────────────────────────────────────────────── */
function buildOptimizeToursJson(dataVehiculos, dataEnvios, fecha = "2026-04-01") {
    const timezone = "-06:00";

    const vehicles = dataVehiculos.map(v => ({
        label: v.nombre,
        startLocation: { latitude: v.lat, longitude: v.lng },
        endLocation:   { latitude: v.lat, longitude: v.lng },
        loadLimits: {
            volumen_dm3: { maxLoad: String(v.volumen) },
            peso_kg:     { maxLoad: String(v.peso) }
        },
        costPerHour:      v.costoporhora,
        costPerKilometer: v.costoporkm,
        travelDurationMultiple: 1.0
    }));

    const shipments = dataEnvios.map(e => ({
        label: `${e.id}-${e.cliente}`,
        deliveries: [{
            arrivalLocation: { latitude: e.lat, longitude: e.lng },
            duration: `${e.duracion}s`,
            timeWindows: [{
                startTime: `${fecha}T${e.ventana[0]}:00${timezone}`,
                endTime:   `${fecha}T${e.ventana[1]}:00${timezone}`
            }]
        }],
        loadDemands: {
            volumen_dm3: { amount: String(Math.ceil(e.volumen)) },
            peso_kg:     { amount: String(e.peso) }
        },
        penaltyCost: 500.0
    }));

    return {
        model: {
            globalStartTime: `${fecha}T08:00:00${timezone}`,
            globalEndTime:   `${fecha}T20:00:00${timezone}`,
            vehicles,
            shipments
        }
    };
}



async function procesarRespuesta(data) {
    const directionsService = new google.maps.DirectionsService();
    
    // Reiniciar el acumulador al empezar un nuevo procesamiento
    rutasParaGuardar = [];

    $('#panel-resultados').removeClass('d-none');
    $('#panel-vacio').hide();
    $('#controles-rutas').empty();

    // Limpiar mapa previo
    if (window.polylines) Object.values(window.polylines).forEach(p => p.setMap(null));
    if (window.routeMarkersByRoute) {
        Object.values(window.routeMarkersByRoute).forEach(markers => markers.forEach(m => m.setMap(null)));
    }
    window.polylines = {};
    window.routeMarkersByRoute = {};

    for (const [i, route] of data.routes.entries()) {
        if (!route.visits || !route.visits.length) continue;

        const color        = colores[i % colores.length];
        const vNameLabel   = (route.vehicleLabel || "").trim();
        const vNameDisplay = vNameLabel || `Unidad ${i + 1}`;

        $('#controles-rutas').append(`
            <div class="route-item">
                <div class="route-dot" style="background:${color}"></div>
                <div class="route-item-info">
                    <div class="route-item-name">${vNameDisplay}</div>
                    <div class="route-item-stops">${route.visits.length} parada${route.visits.length !== 1 ? 's' : ''}</div>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input route-toggle" type="checkbox" checked data-vid="${i}">
                </div>
            </div>
        `);

        const vehiculoOriginal = dataVehiculos.find(v => v.nombre.trim() === vNameLabel);
        if (!vehiculoOriginal) continue;

        const originPos = { lat: parseFloat(vehiculoOriginal.lat), lng: parseFloat(vehiculoOriginal.lng) };
        const puntos    = [originPos];
        const enviosEnEstaRuta = [];

        route.visits.forEach((visit, idx) => {
            const sLabel = (visit.shipmentLabel || "").trim();
            const envioOriginal = dataEnvios.find(e => 
                sLabel === `${e.id}-${e.cliente}`.trim() || sLabel === e.cliente.trim() || sLabel === e.id.trim()
            );

            if (envioOriginal) {
                puntos.push({ lat: parseFloat(envioOriginal.lat), lng: parseFloat(envioOriginal.lng) });
                enviosEnEstaRuta.push(envioOriginal);
                
                const letra = String.fromCharCode(65 + idx);
                $(`#badge-ruta-${envioOriginal.id}`).html(`
                    <span class="ruta-badge" style="background:${color}">${letra} · ${vNameDisplay}</span>
                `);
            }
        });

        if (puntos.length < 2) continue;

        (function(ridx, rcolor, pts, vObj, eList) {
            const origin      = pts[0];
            const destination = pts[pts.length - 1];
            const waypoints   = pts.slice(1, pts.length - 1).map(p => ({
                location: new google.maps.LatLng(p.lat, p.lng), stopover: true
            }));

            directionsService.route({
                origin:      new google.maps.LatLng(origin.lat, origin.lng),
                destination: new google.maps.LatLng(destination.lat, destination.lng),
                waypoints,
                travelMode:  google.maps.TravelMode.DRIVING,
                optimizeWaypoints: false
            }, (result, status) => {
                if (status !== 'OK') return;

                const renderer = new google.maps.DirectionsRenderer({
                    map, suppressMarkers: true,
                    polylineOptions: { strokeColor: rcolor, strokeWeight: 6, strokeOpacity: 0.85 }
                });
                renderer.setDirections(result);
                window.polylines[ridx] = renderer;

                // --- GUARDAR EN EL ARREGLO EN LUGAR DE ENVIAR POR AJAX ---
                rutasParaGuardar.push({
                    vehiculo: vObj,
                    envios:   eList,
                    datosRuta: {
                        polyline:  result.routes[0].overview_polyline,
                        distancia: result.routes[0].legs.reduce((acc, leg) => acc + leg.distance.value, 0),
                        duracion:  result.routes[0].legs.reduce((acc, leg) => acc + leg.duration.value, 0)
                    }
                });

                // Marcadores (Código igual a la versión anterior)
                window.routeMarkersByRoute[ridx] = [];
                const mOrigen = new google.maps.Marker({ position: origin, map, icon: 'https://maps.google.com/mapfiles/kml/pal2/icon13.png' });
                window.routeMarkersByRoute[ridx].push(mOrigen);

                for (let j = 1; j < pts.length; j++) {
                    const letra = String.fromCharCode(64 + j);
                    const mParada = new google.maps.Marker({
                        position: pts[j], map,
                        label: { text: letra, color: 'white', fontWeight: 'bold' }
                    });
                    window.routeMarkersByRoute[ridx].push(mParada);
                }
            });
        })(i, color, puntos, vehiculoOriginal, enviosEnEstaRuta);
    }

    // Mostrar el botón que pedirá la confirmación final
    $('#guardarRutaOptima').removeClass('d-none');
}

/* ─── TOGGLE RUTAS ───────────────────────────────────────────────────── */
$(document).on('change', '.route-toggle', function() {
    const vid      = $(this).data('vid');
    const targetMap = this.checked ? map : null;

    if (window.polylines?.[vid]) window.polylines[vid].setMap(targetMap);
    if (window.routeMarkersByRoute?.[vid]) {
        window.routeMarkersByRoute[vid].forEach(m => m.setMap(targetMap));
    }
});

/* ─── CAMBIAR FECHA ──────────────────────────────────────────────────── */
$('#fecha-operacion').on('change', function() {
    const v = $(this).val();
    if (v) window.location.href = 'route.php?fecha=' + v;
});

/* ─── RUTA MANUAL ────────────────────────────────────────────────────── */
const COLOR_MANUAL = "#8b5cf6";
let rutaManualRenderer = null;
let rutaManualMarkers  = [];

function iniciarRutaManual() {
    limpiarRutaManual();
    const vehiculo = obtenerVehiculoSeleccionado();
    const envios   = obtenerEnviosSeleccionados2();

    if (!vehiculo) { mostrarToast('<?= Trd(27) ?>', 'warning'); return; }
    if (!envios.length) { mostrarToast('<?= Trd(28) ?>', 'warning'); return; }

    trazarRutaManual(vehiculo, envios);
    $('#guardarRutaManual').removeClass('d-none');
}

function obtenerVehiculoSeleccionado() {
    const ids = $('.check-vehiculo:checked').map((_, el) => el.value).get();
    if (ids.length !== 1) return null;
    return dataVehiculos.find(v => v.id === ids[0]) || null;
}

function obtenerEnviosSeleccionados() {
    const ids = $('.check-envio:checked').map((_, el) => el.value).get();
    return dataEnvios.filter(e => ids.includes(e.id));
}

function obtenerEnviosSeleccionados2() {
    const idsEnPanel = $('#lista-orden-manual [data-id]').map((_, el) => el.dataset.id).get();
    if (idsEnPanel.length > 0) {
        return idsEnPanel.map(id => dataEnvios.find(e => e.id === id)).filter(Boolean);
    }
    const ids = $('.check-envio:checked').map((_, el) => el.value).get();
    return dataEnvios.filter(e => ids.includes(e.id));
}

function trazarRutaManual(vehiculo, envios) {
    const directionsService = new google.maps.DirectionsService();
    const originPos    = { lat: parseFloat(vehiculo.lat), lng: parseFloat(vehiculo.lng) };
    const destinoPos   = { lat: parseFloat(envios[envios.length - 1].lat), lng: parseFloat(envios[envios.length - 1].lng) };
    const waypoints    = envios.slice(0, -1).map(e => ({
        location: new google.maps.LatLng(parseFloat(e.lat), parseFloat(e.lng)),
        stopover: true
    }));

    directionsService.route({
        origin:      new google.maps.LatLng(originPos.lat, originPos.lng),
        destination: new google.maps.LatLng(destinoPos.lat, destinoPos.lng),
        waypoints,
        travelMode:        google.maps.TravelMode.DRIVING,
        optimizeWaypoints: false
    }, (result, status) => {
        if (status !== "OK") {
            mostrarToast("<?= Trd(29) ?>: " + status, 'danger');
            return;
        }

        rutaManualRenderer = new google.maps.DirectionsRenderer({
            map, suppressMarkers: true,
            polylineOptions: { strokeColor: COLOR_MANUAL, strokeWeight: 6, strokeOpacity: 0.85 }
        });
        rutaManualRenderer.setDirections(result);

        const mOrigen = new google.maps.Marker({
            position: originPos, map,
            icon: 'https://maps.google.com/mapfiles/kml/pal2/icon13.png'
        });
        rutaManualMarkers.push(mOrigen);

        envios.forEach((e, idx) => {
            const letra = String.fromCharCode(65 + idx);
            const pos   = { lat: parseFloat(e.lat), lng: parseFloat(e.lng) };
            const mEnvio = new google.maps.Marker({
                position: pos, map,
                label: { text: letra, color: "white", fontWeight: "bold" },
                title: `Envío ${letra}`
            });
            rutaManualMarkers.push(mEnvio);
            $(`#badge-ruta-${e.id}`).html(`<span class="ruta-badge" style="background:${COLOR_MANUAL}">${letra} · Manual</span>`);
        });


        const datosRuta = {
            polyline: result.routes[0].overview_polyline, // La línea de la ruta
            distancia: result.routes[0].legs.reduce((acc, leg) => acc + leg.distance.value, 0), // Metros totales
            duracion: result.routes[0].legs.reduce((acc, leg) => acc + leg.duration.value, 0), // Segundos totales
            bounds: result.routes[0].bounds // Para encuadrar el mapa luego
        };


        vehiculo_s = vehiculo;
        envios_s = envios;
        datosRuta_s = datosRuta;

    });

}

function guardarRutaOptima(){

        $.ajax({
            url: API_BASE_URL +'save_route',
            headers: { 'Authorization': 'Bearer ' + TOKEN },            
            method: 'POST',
            data: { todas_las_rutas: JSON.stringify(rutasParaGuardar),
                    fecha: $('#fecha-operacion').val(),
                    tipo: 'optima'
            },
            success: function(res) {
                console.log("Ruta guardada exitosamente");
                mostrarToast("<?= Trd(30) ?>", "success");
                setTimeout(function() {
                    location.reload();
                }, 1000);
                
            },
            error: function(err) {
                console.error("Error al guardar la ruta", err);
            }
        });

}


function guardarRutaManual(){
    enviarRutaAlServidor(vehiculo_s, envios_s, datosRuta_s);
}


function enviarRutaAlServidor(vehiculo, envios, datosRuta) {
    $.ajax({
        url: API_BASE_URL +'save_route',
        headers: { 'Authorization': 'Bearer ' + TOKEN },
        method: 'POST',
        data: {
            id_vehiculo: vehiculo.id,
            puntos_envio: JSON.stringify(envios),
            polyline: datosRuta.polyline,
            distancia_total: datosRuta.distancia,
            duracion_total: datosRuta.duracion,
            fecha: $('#fecha-operacion').val(),
            tipo: 'manual'
        },
        success: function(response) {
            console.log("Ruta guardada exitosamente");
            mostrarToast("<?= Trd(31) ?>", "success");
            setTimeout(function() {
                location.reload();
            }, 1000);
        },
        error: function(err) {
            console.error("Error al guardar la ruta", err);
        }
    });
}


function mostrarRutaGuardada(polylineBase64, vehiculo, envios) {
    // 1. Decodificar y dibujar la Polyline (La ruta)
    const path = google.maps.geometry.encoding.decodePath(polylineBase64);
    
    const rutaVisual = new google.maps.Polyline({
        path: path,
        geodesic: true,
        strokeColor: COLOR_MANUAL,
        strokeOpacity: 0.85,
        strokeWeight: 6,
        map: map
    });

    // 2. Marcador de Origen (Vehículo)
    const originPos = { lat: parseFloat(vehiculo.lat), lng: parseFloat(vehiculo.lng) };
    const mOrigen = new google.maps.Marker({
        position: originPos,
        map: map,
        icon: 'https://maps.google.com/mapfiles/kml/pal2/icon13.png' // Icono de coche para distinguir
    });
    rutaManualMarkers.push(mOrigen);

    // 3. Marcadores de Envíos (Waypoints)
    const bounds = new google.maps.LatLngBounds();
    bounds.extend(originPos);

    envios.forEach((e, idx) => {
        const letra = String.fromCharCode(65 + idx);
        const pos = { lat: parseFloat(e.lat), lng: parseFloat(e.lng) };
        
        const mEnvio = new google.maps.Marker({
            position: pos,
            map: map,
            label: { text: letra, color: "white", fontWeight: "bold" },
            title: `Envío ${letra}`
        });
        
        rutaManualMarkers.push(mEnvio);
        bounds.extend(pos);

        // Opcional: Actualizar los badges en la interfaz si aún existen los elementos
        $(`#badge-ruta-${e.id}`).html(
            `<span class="ruta-badge" style="background:${COLOR_MANUAL}">${letra} · <?= Trd(32) ?></span>`
        );
    });

    // 4. Ajustar el zoom para que todo sea visible
    map.fitBounds(bounds);
}



function limpiarRutaManual() {
    if (rutaManualRenderer) { rutaManualRenderer.setMap(null); rutaManualRenderer = null; }
    rutaManualMarkers.forEach(m => m.setMap(null));
    rutaManualMarkers = [];
    dataEnvios.forEach(e => {
        const $b = $(`#badge-ruta-${e.id}`);
        if ($b.text().includes('Manual')) $b.empty();
    });
    $('#guardarRutaManual').addClass('d-none');
}

/* ─── PANEL ORDEN MANUAL ─────────────────────────────────────────────── */
let sortableManual = null;

function actualizarPanelOrden() {
    const seleccionados = obtenerEnviosSeleccionados();
    const $lista  = $('#lista-orden-manual');
    const $panel  = $('#panel-orden-manual');

    if (!seleccionados.length) {
        $panel.addClass('d-none');
        $lista.empty();
        return;
    }

    const idsSeleccionados = seleccionados.map(e => e.id);
    $lista.find('[data-id]').each((_, el) => {
        if (!idsSeleccionados.includes(el.dataset.id)) $(el).remove();
    });

    const idsEnPanel = $lista.find('[data-id]').map((_, el) => el.dataset.id).get();

    seleccionados.forEach(e => {
        if (!idsEnPanel.includes(e.id)) {
            $lista.append(`
                <li class="manual-item" data-id="${e.id}">
                    <i class="fas fa-grip-vertical manual-grip"></i>
                    <div class="manual-item-name">${e.lugar}<br>
                        <span style="font-weight:400; color:var(--muted); font-size:11px;">${e.cliente}</span>
                    </div>
                    <small style="color:var(--muted); font-size:10px;">${e.ventana[0]}–${e.ventana[1]}</small>
                </li>
            `);
        }
    });

    if (!sortableManual) {
        sortableManual = new Sortable(document.getElementById('lista-orden-manual'), {
            animation: 150,
            handle: '.manual-grip',
            ghostClass: 'bg-light'
        });
    }

    $panel.removeClass('d-none');
}

/* ─── TOAST ──────────────────────────────────────────────────────────── */
function mostrarToast(msg, tipo = 'info') {
    // Si no hay contenedor, lo crea
    if (!$('#toast-container').length) {
        $('body').append(`<div id="toast-container" style="
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            z-index: 9999; display: flex; flex-direction: column; gap: .5rem;
        "></div>`);
    }

    const colors = {
        success: '#10b981', warning: '#f59e0b', danger: '#ef4444', info: '#0ea5e9'
    };

    const $toast = $(`
        <div style="
            display:flex; align-items:center; gap:.6rem;
            padding:.7rem 1.1rem;
            background:${colors[tipo] ?? colors.info};
            color:white; border-radius:10px;
            font-size:13px; font-weight:500;
            box-shadow: 0 4px 20px rgba(0,0,0,.2);
            animation: slideIn .2s ease;
        ">
            <i class="fas fa-${tipo === 'warning' ? 'exclamation-triangle' : tipo === 'danger' ? 'circle-xmark' : tipo === 'success' ? 'check-circle' : 'info-circle'}"></i>
            ${msg}
        </div>
    `);

    $('#toast-container').append($toast);
    setTimeout(() => $toast.fadeOut(300, function() { $(this).remove(); }), 3500);
}

/* ─── NAVEGACIÓN MÓVIL ───────────────────────────────────────────────── */
const isMobile = () => window.innerWidth <= 768;

let currentMobilePanel = 'left';

function mobileNav(panel, btn) {
    currentMobilePanel = panel;

    // 1. Ocultar todos los paneles
    document.querySelector('.panel-left').classList.remove('mob-visible');
    document.querySelector('.map-wrap').classList.remove('mob-visible');
    document.querySelector('.panel-right').classList.remove('mob-visible');

    // 2. Mostrar el panel seleccionado
    const target = panel === 'left'  ? '.panel-left'
                 : panel === 'map'   ? '.map-wrap'
                 :                     '.panel-right';
    document.querySelector(target).classList.add('mob-visible');

    // 3. Actualizar botones activos
    document.querySelectorAll('.mobile-nav-btn').forEach(b => b.classList.remove('mob-active'));
    if (btn) btn.classList.add('mob-active');

    // 4. Redibujar mapa si se navega a él (fix Google Maps blank)
    if (panel === 'map' && typeof google !== 'undefined' && typeof map !== 'undefined' && map) {
        setTimeout(() => google.maps.event.trigger(map, 'resize'), 50);
    }

    // 5. Limpiar badge de rutas al entrar al panel de rutas
    if (panel === 'right') {
        $('#nav-badge-rutas').hide();
    }
}

function setupMobile() {
    if (!isMobile()) {
        // Desktop: remover clases móviles y mostrar layout normal
        document.querySelector('.panel-left').classList.remove('mob-visible');
        document.querySelector('.map-wrap').classList.remove('mob-visible');
        document.querySelector('.panel-right').classList.remove('mob-visible');
        document.querySelector('.mobile-nav').style.display = 'none';
        return;
    }

    // Mostrar barra inferior
    document.querySelector('.mobile-nav').style.display = 'flex';

    // Activar el panel que corresponde (o 'left' por defecto)
    const activeBtn = document.querySelector(`.mobile-nav-btn[data-panel="${currentMobilePanel}"]`);
    mobileNav(currentMobilePanel, activeBtn);
}

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    setupMobile();

    // Listener para botones de navegación móvil
    $(document).on('click', '.mobile-nav-btn', function() {
        const panel = $(this).data('panel');
        mobileNav(panel, this);
    });
});

// Re-evaluar al rotar o redimensionar
$(window).on('resize', setupMobile);

// Cuando se calculan rutas en móvil: ir al mapa y mostrar badge
const _origProcesarRespuesta = procesarRespuesta;
procesarRespuesta = async function(data) {
    await _origProcesarRespuesta(data);
    if (isMobile()) {
        $('#nav-badge-rutas').text('✓').show();
        const mapBtn = document.querySelector('.mobile-nav-btn[data-panel="map"]');
        mobileNav('map', mapBtn);
        mostrarToast('<?= Trd(33) ?>', 'success');
    }
};

// Badge de selección en tab Entregas
$(document).on('change', '.check-vehiculo, .check-envio', function() {
    if (!isMobile()) return;
    const total = $('.check-vehiculo:checked').length + $('.check-envio:checked').length;
    const $badge = $('#nav-badge-sel');
    total > 0 ? $badge.text(total).show() : $badge.hide();
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

<style>
@keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

</body>
</html>