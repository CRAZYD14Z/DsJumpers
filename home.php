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

<?php
$mesActual  = (int)date('m');
$anioActual = (int)date('Y');

if ($_SESSION['Idioma'] == 'en'){
    $meses = [
        1=>'January', 2=>'February', 3=>'March', 4=>'April',
        5=>'May',     6=>'June',     7=>'July',  8=>'August',
        9=>'September', 10=>'October', 11=>'November', 12=>'December'
    ];
}
else{
    $meses = [
        1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
        5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
        9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
    ];
}

?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['Idioma'];?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración con Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">    

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/home.css">



</head>
<body>
<?php
    include_once 'nav.php';
?>

<!-- ═══════════════════════ HEADER ═══════════════════════════ -->
<div id="cal-header">
    <button id="btn-toggle-sidebar" title="<?php echo ($_SESSION['Idioma'] == 'en') ? "View agenda" : "Ver agenda"; ?>">
        <i class="bi bi-list-ul"></i>
    </button>

    <span class="brand">
        <i class="bi bi-calendar3 me-1" style="color:var(--accent)"></i>
        <?php echo ($_SESSION['Idioma'] == 'en') ? "Calendar" : "Agenda"; ?>
    </span>

    <div id="nav-mes">
        <button id="btn-prev" title="<?php echo ($_SESSION['Idioma'] == 'en') ? "Previous month" : "Mes anterior"; ?>">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button id="lbl-mes" class="btn btn-sm btn-light fw-bold mx-1" data-bs-toggle="modal" data-bs-target="#modalSelectorFecha">—</button>
        <button id="btn-next" title="<?php echo ($_SESSION['Idioma'] == 'en') ? "Next month" : "Mes siguiente"; ?>">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    <button id="btn-hoy">
        <i class="bi bi-record-circle me-1"></i>
        <?php echo ($_SESSION['Idioma'] == 'en') ? "Today" : "Hoy"; ?>
    </button>

    <div class="leyenda-wrap" id="leyenda-wrap">
        <span class="leyenda-item">
            <span class="leyenda-dot" style="background:#10b981"></span>
            <?php echo ($_SESSION['Idioma'] == 'en') ? "Confirmed" : "Confirmado"; ?>
        </span>
        <span class="leyenda-item">
            <span class="leyenda-dot" style="background:#f59e0b"></span>
            <?php echo ($_SESSION['Idioma'] == 'en') ? "Pending" : "Pendiente"; ?>
        </span>
        <span class="leyenda-item">
            <span class="leyenda-dot" style="background:#ef4444"></span>
            <?php echo ($_SESSION['Idioma'] == 'en') ? "Cancelled" : "Cancelado"; ?>
        </span>
    </div>
</div>

<!-- ═══════════════════════ BODY ══════════════════════════════ -->
<div id="app-body">

    <!-- ── SIDEBAR ── -->
    <div id="sidebar">
        <div id="sidebar-title">
            <i class="bi bi-calendar-event me-1"></i>
            <?php echo ($_SESSION['Idioma'] == 'en') ? "Events of the month" : "Eventos del mes"; ?>
        </div>
        <div id="lista-eventos">
            <div class="sidebar-empty">
                <i class="bi bi-hourglass-split d-block fs-2 mb-2"></i>
                <?php echo ($_SESSION['Idioma'] == 'en') ? "Loading..." : "Cargando…"; ?>
            </div>
        </div>
        <div id="sidebar-count">—</div>
    </div>

    <!-- ── CALENDARIO ── -->
    <div id="cal-wrap">
        <div id="days-header">
            <?php if ($_SESSION['Idioma'] == 'en'): ?>
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            <?php else: ?>
                <div>Dom</div><div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div>
            <?php endif; ?>
        </div>
        <div id="cal-grid">
            <div id="cal-spinner">
                <div class="spin-ring"></div>
                <span class="spin-label">
                    <?php echo ($_SESSION['Idioma'] == 'en') ? "Loading events..." : "Cargando eventos…"; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════ MODAL DETALLE ════════════════════ -->
<div class="modal fade" id="modalEvento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titulo">—</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-status-wrap" class="mb-3"></div>
                <div id="modal-fecha"   class="detail-row"></div>
                <div id="modal-hora"    class="detail-row"></div>
                <div id="modal-lugar"   class="detail-row"></div>
                <div id="modal-desc"    class="detail-row"></div>
            </div>
            <div class="modal-footer">
                <a id="btn-abrir-evento" href="#" class="btn btn-sm btn-primary me-auto">
                    <i class="bi bi-box-arrow-up-right"></i> 
                    <?php echo ($_SESSION['Idioma'] == 'en') ? "Open event" : "Abrir evento"; ?>
                </a>

                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <?php echo ($_SESSION['Idioma'] == 'en') ? "Close" : "Cerrar"; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSelectorFecha" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm"> <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">
                    <?php echo ($_SESSION['Idioma'] == 'en') ? "Select Date" : "Seleccionar Fecha"; ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-7">
                        <label class="form-label small text-muted mb-1"><?php echo ($_SESSION['Idioma'] == 'en') ? "Month" : "Mes"; ?></label>
                        <select id="select-modal-mes" class="form-select form-select-sm">
                            <?php foreach ($meses as $num => $nombre): ?>
                                <option value="<?php echo $num; ?>"><?php echo $nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label small text-muted mb-1"><?php echo ($_SESSION['Idioma'] == 'en') ? "Year" : "Año"; ?></label>
                        <select id="select-modal-anio" class="form-select form-select-sm">
                            <?php for ($a = $anioActual - 5; $a <= $anioActual + 5; $a++): ?>
                                <option value="<?php echo $a; ?>"><?php echo $a; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-xs btn-secondary" data-bs-dismiss="modal">
                    <?php echo ($_SESSION['Idioma'] == 'en') ? "Cancel" : "Cancelar"; ?>
                </button>
                <button type="button" id="btn-aplicar-fecha" class="btn btn-sm btn-primary">
                    <?php echo ($_SESSION['Idioma'] == 'en') ? "Apply" : "Aplicar"; ?>
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


/* ══════════════════════════════════════════════════════════════
   AGENDA CALENDARIO  —  lógica completa
══════════════════════════════════════════════════════════════ */
    const IDIOMA_SISTEMA = "<?php echo isset($_SESSION['Idioma']) ? $_SESSION['Idioma'] : 'es'; ?>";
    
    const MESES = IDIOMA_SISTEMA === 'en' 
        ? ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
        : ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// API endpoint (relativo al mismo servidor)
const API_URL = 'api_eventos.php';

// ── Estado ──────────────────────────────────────────────────
let mesActual  = <?= $mesActual ?>;
let anioActual = <?= $anioActual ?>;
let todosEventos = [];
let eventoModal  = null;
let modalBS      = null;
let IdLead = 0;

$(document).ready(function() {
    $("#SearchButton").on("click", function() {
        busqueda = $('#search_text').val();
        cargarEventos(mesActual, anioActual, busqueda)
    });
});

// ── Utilidades ───────────────────────────────────────────────
const today = new Date();
const todayStr = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;

function diasEnMes(m, a) {
    return new Date(a, m, 0).getDate();          // m sin -1 → último día
}
function primerDiaSemana(m, a) {
    return new Date(a, m-1, 1).getDay();         // 0=Dom
}
function fechaStr(a, m, d) {
    return `${a}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
}
function formatFechaLarga(str) {
    const [a,m,d] = str.split('-');
    const fd = new Date(+a, +m-1, +d);
    return fd.toLocaleDateString('es-MX', {weekday:'long', year:'numeric', month:'long', day:'numeric'});
}
function minToHHMM(min) {
    return `${String(Math.floor(min/60)).padStart(2,'0')}:${String(min%60).padStart(2,'0')}`;
}

// ── Cargar eventos vía AJAX ───────────────────────────────────
function cargarEventos(mes, anio, busqueda) {
    $('#cal-spinner').addClass('show');
    $('#cal-grid').find('.cal-cell').remove();

    $.ajax({
        url: API_URL,
        method: 'GET',
        data: { mes, anio, busqueda },
        dataType: 'json',
        success(resp) {
            if (resp.ok) {
                todosEventos = resp.eventos;
                renderCalendario(mes, anio);
                renderSidebar(resp.eventos);
            }
        },
        error() {
            // Fallback: datos simulados en JS si el PHP no está disponible
            //todosEventos = generarEventosSimulados(mes, anio);
            renderCalendario(mes, anio);
            //renderSidebar(todosEventos);
        },
        complete() {
            $('#cal-spinner').removeClass('show');
        }
    });
}

// ── Render calendario ─────────────────────────────────────────
function renderCalendario(mes, anio) {
    const grid   = $('#cal-grid');
    const celdas = grid.find('.cal-cell');
    celdas.remove();

    const primer = primerDiaSemana(mes, anio);
    const totalD = diasEnMes(mes, anio);

    // mes anterior
    const mesAnterior   = mes === 1 ? 12 : mes - 1;
    const anioAnterior  = mes === 1 ? anio - 1 : anio;
    const diasMesAnt    = diasEnMes(mesAnterior, anioAnterior);

    // índice de eventos por fecha
    const evMap = {};
    todosEventos.forEach(e => {
        if (!evMap[e.fecha]) evMap[e.fecha] = [];
        evMap[e.fecha].push(e);
    });

    const totalCeldas = 42;   // 6 filas × 7 cols

    for (let i = 0; i < totalCeldas; i++) {
        let dia, fechaISO, esOtroMes = false;

        if (i < primer) {
            dia        = diasMesAnt - primer + i + 1;
            fechaISO   = fechaStr(anioAnterior, mesAnterior, dia);
            esOtroMes  = true;
        } else if (i >= primer + totalD) {
            dia        = i - (primer + totalD) + 1;
            const mesSig  = mes === 12 ? 1 : mes + 1;
            const anioSig = mes === 12 ? anio + 1 : anio;
            fechaISO   = fechaStr(anioSig, mesSig, dia);
            esOtroMes  = true;
        } else {
            dia      = i - primer + 1;
            fechaISO = fechaStr(anio, mes, dia);
        }

        const esHoy   = fechaISO === todayStr && !esOtroMes;
        const evs     = evMap[fechaISO] || [];
        const MAX_VIS = 3;

        let cellClass = 'cal-cell';
        if (esOtroMes) cellClass += ' otro-mes';
        if (esHoy)     cellClass += ' hoy';
        if (evs.length) cellClass += ' has-events';

        // Número del día
        const numHtml = `<div class="cell-num">${dia}</div>`;

        // Píldoras de eventos
        let pillsHtml = '<div class="cell-events">';
        const visibles = evs.slice(0, MAX_VIS);
        visibles.forEach(ev => {
            const c = ev.estatus_info.color;
            pillsHtml += `<div class="ev-pill"
                style="background:${c}"
                data-id="${ev.id}"
                title="${ev.titulo} — ${ev.hora}">${ev.hora} ${ev.titulo}</div>`;
        });
        if (evs.length > MAX_VIS) {
            pillsHtml += `<div class="ev-more" data-fecha="${fechaISO}">+${evs.length - MAX_VIS} más</div>`;
        }
        pillsHtml += '</div>';

        grid.append(
            `<div class="${cellClass}" data-fecha="${fechaISO}">
                ${numHtml}${pillsHtml}
            </div>`
        );
    }

    // Eventos de celdas
    grid.on('click', '.ev-pill', function(e) {
        e.stopPropagation();
        const id = +$(this).data('id');
        abrirModal(todosEventos.find(ev => ev.id === id));
    });
    grid.on('click', '.ev-more', function(e) {
        e.stopPropagation();
        const fecha = $(this).data('fecha');
        filtrarSidebar(fecha);
    });
    grid.on('click', '.cal-cell', function() {
        const fecha = $(this).data('fecha');
        filtrarSidebar(fecha);
    });
}

// ── Sidebar ──────────────────────────────────────────────────
let filtroFecha = null;

function renderSidebar(evs) {
    const lista = $('#lista-eventos');
    lista.empty();
    filtroFecha = null;
    actualizarSidebar(evs);
}

function filtrarSidebar(fecha) {
    filtroFecha = fecha;
    const evs = todosEventos.filter(e => e.fecha === fecha);
    actualizarSidebar(evs, fecha);
    // En móvil: mostrar sidebar
    if ($(window).width() < 768) $('#sidebar').addClass('open');
}

function actualizarSidebar(evs, fecha = null) {
    const lista  = $('#lista-eventos');
    const count  = $('#sidebar-count');
    const titulo = fecha
        ? formatFechaLarga(fecha)
        : `${MESES[mesActual-1]} ${anioActual}`;

    $('#sidebar-title').html(
        `<i class="bi bi-calendar-event me-1"></i>${titulo}`
    );

    lista.empty();

    if (!evs.length) {
        lista.html('<div class="sidebar-empty"><i class="bi bi-calendar-x d-block fs-2 mb-2"></i>Sin eventos</div>');
        count.text('0 eventos');
        return;
    }

    evs.forEach(ev => {
        const c   = ev.estatus_info.color;
        const lbl = ev.estatus_info.label;
        lista.append(`
            <div class="ev-card" data-id="${ev.id}" style="border-left-color:${c}">
                <div class="ev-hora">${ev.fecha} · ${ev.hora}</div>
                <div class="ev-titulo">${ev.titulo}</div>
                <span class="ev-badge" style="background:${c}">${lbl}</span>
            </div>
        `);
    });

    count.text(`${evs.length} evento${evs.length !== 1 ? 's' : ''}`);

    lista.on('click', '.ev-card', function() {
        const id = +$(this).data('id');
        abrirModal(todosEventos.find(e => e.id === id));
    });
}

// ── Modal ─────────────────────────────────────────────────────
function abrirModal(ev) {
    if (!ev) return;
    const c   = ev.estatus_info.color;
    const lbl = ev.estatus_info.label;

    // calcular hora fin
    const [h, m] = ev.hora.split(':').map(Number);
    const finMin  = h * 60 + m + ev.duracion;
    const horaFin = minToHHMM(finMin);

    IdLead = ev.idev;

    $('#modal-titulo').text(ev.titulo);
    $('#modal-status-wrap').html(
        `<span class="status-badge-lg" style="background:${c}">${lbl}</span>`
    );
    $('#modal-fecha').html(
        `<i class="bi bi-calendar3"></i>
         <div><span class="detail-label"><?php echo ($_SESSION['Idioma'] == 'en') ? "Date" : "Fecha"; ?></span>
              <span class="detail-val">${formatFechaLarga(ev.fecha)}</span></div>`
    );
    $('#modal-hora').html(
        `<i class="bi bi-clock"></i>
         <div><span class="detail-label"><?php echo ($_SESSION['Idioma'] == 'en') ? "Event hours" : "Horas de evento"; ?></span>
              <span class="detail-val">(${ev.duracion} hrs)</span></div>`
    );
    $('#modal-lugar').html(
        `<i class="bi bi-geo-alt"></i>
         <div><span class="detail-label"><?php echo ($_SESSION['Idioma'] == 'en') ? "Venue" : "Lugar"; ?></span>
              <span class="detail-val">${ev.lugar}</span></div>`
    );
    $('#modal-desc').html(
        `<i class="bi bi-card-text"></i>
         <div><span class="detail-label"><?php echo ($_SESSION['Idioma'] == 'en') ? "Description" : "Descripción"; ?></span>
              <span class="detail-val">${ev.desc}</span></div>`
    );

    $('#btn-abrir-evento').attr('href', 'lead.php?IdLead=' + ev.idev);    

    if (!modalBS) modalBS = new bootstrap.Modal(document.getElementById('modalEvento'));
    modalBS.show();
}

// ── Navegación ────────────────────────────────────────────────
function actualizarLabel() {
    
// Cambia el texto del ahora botón
    $('#lbl-mes').text(`${MESES[mesActual-1]} ${anioActual}`);
    
    // Sincroniza los selectores internos del modal con el mes y año en los que estás navegando actualmente
    $('#select-modal-mes').val(mesActual);
    $('#select-modal-anio').val(anioActual);

}

function irMes(mes, anio) {
    mesActual  = mes;
    anioActual = anio;
    actualizarLabel();
    busqueda = $('#search_text').val();
    cargarEventos(mes, anio, busqueda);
}

$('#btn-prev').on('click', () => {
    let m = mesActual - 1, a = anioActual;
    if (m < 1) { m = 12; a--; }
    irMes(m, a);
});
$('#btn-next').on('click', () => {
    let m = mesActual + 1, a = anioActual;
    if (m > 12) { m = 1; a++; }
    irMes(m, a);
});
$('#btn-hoy').on('click', () => {
    irMes(<?= $mesActual ?>, <?= $anioActual ?>);
});

// ── Sidebar toggle (móvil) ────────────────────────────────────
$('#btn-toggle-sidebar').on('click', () => {
    const sb = $('#sidebar');
    if (sb.hasClass('open')) {
        sb.removeClass('open');
        renderSidebar(todosEventos);   // restablecer lista completa
    } else {
        sb.addClass('open');
    }
});

// Cerrar sidebar al clic en grid (móvil)
$('#cal-grid').on('click', () => {
    if ($(window).width() < 768) $('#sidebar').removeClass('open');
});

// ── INIT ──────────────────────────────────────────────────────
$(function() {
    actualizarLabel();
    busqueda = $('#search_text').val();
    cargarEventos(mesActual, anioActual,busqueda);
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

$('#btn-aplicar-fecha').on('click', function() {
    // Obtener los valores seleccionados en el modal
    const nuevoMes  = parseInt($('#select-modal-mes').val());
    const nuevoAnio = parseInt($('#select-modal-anio').val());
    
    // Reutilizamos tu función para viajar al mes, renderizar y recargar eventos vía AJAX
    irMes(nuevoMes, nuevoAnio);
    
    // Cerrar el modal automáticamente
    const modalSelector = bootstrap.Modal.getInstance(document.getElementById('modalSelectorFecha'));
    if (modalSelector) {
        modalSelector.hide();
    }
});    

</script>
</body>
</html>