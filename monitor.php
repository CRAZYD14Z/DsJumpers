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
    $refresh_interval = 60;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monitor de Rutas — Tiempo Real</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

<style>
:root {
    --bg:          #f0f2f7;
    --surface:     #ffffff;
    --surface2:    #f7f8fc;
    --border:      #dde1ec;
    --border2:     #c8cedf;
    --blue:        #3b6ef8;
    --blue-soft:   #eef2ff;
    --blue-mid:    #c7d4fd;
    --green:       #16a34a;
    --green-soft:  #dcfce7;
    --yellow:      #d97706;
    --yellow-soft: #fef3c7;
    --red:         #dc2626;
    --red-soft:    #fee2e2;
    --text:        #1e2742;
    --text2:       #4a5578;
    --muted:       #8e97b4;
    --shadow-sm:   0 1px 3px #1e274212, 0 1px 2px #1e27420a;
    --shadow-md:   0 4px 16px #1e274214, 0 2px 6px #1e274208;
    --shadow-xl:   0 20px 60px #1e274225, 0 8px 24px #1e27421a;
    --font-mono:   'DM Mono', monospace;
    --font-main:   'Sora', sans-serif;
    --radius:      14px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); color: var(--text); font-family: var(--font-main); min-height: 100vh; }

/* ═══════ HEADER ═══════ */
.app-header {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 28px;
    height: 62px;
    display: flex; align-items: center; gap: 16px;
    position: sticky; top: 0; z-index: 200;
    box-shadow: var(--shadow-sm);
}
.logo-wrap { display: flex; align-items: center; gap: 10px; }
.logo-icon {
    width: 36px; height: 36px; background: var(--blue); border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1rem; box-shadow: 0 2px 8px #3b6ef840; flex-shrink: 0;
}
.logo-title { font-size: 1.05rem; font-weight: 700; color: var(--text); }
.logo-title span { color: var(--blue); }
.header-right { margin-left: auto; display: flex; align-items: center; gap: 16px; }
#clock {
    font-family: var(--font-mono); font-size: .9rem; color: var(--text2);
    background: var(--surface2); border: 1px solid var(--border);
    border-radius: 8px; padding: 4px 12px; letter-spacing: .06em;
}
.live-pill {
    display: flex; align-items: center; gap: 6px;
    background: var(--green-soft); border: 1px solid #bbf7d0;
    border-radius: 99px; padding: 4px 12px;
    font-size: .72rem; font-weight: 600; color: var(--green);
    letter-spacing: .08em; text-transform: uppercase;
}
.live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); animation: blink 1.5s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

#countdown-bar { height: 3px; background: var(--border); }
#countdown-fill { height: 100%; background: linear-gradient(90deg,var(--blue),#6fa3ff); width: 100%; transition: width 1s linear; }

/* ═══════ MAIN ═══════ */
.main-wrap { padding: 24px 28px; max-width: 1280px; margin: auto; }

/* ═══════ SUMMARY ═══════ */
.summary-strip { display: grid; grid-template-columns: repeat(auto-fit,minmax(148px,1fr)); gap: 12px; margin-bottom: 22px; }
.stat-pill {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 12px; padding: 14px 18px;
    box-shadow: var(--shadow-sm); display: flex; flex-direction: column; gap: 4px;
}
.sp-label { font-size: .65rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
.sp-value { font-family: var(--font-mono); font-size: 1.7rem; line-height: 1.1; color: var(--text); }
.sp-value.c-blue   { color: var(--blue); }
.sp-value.c-green  { color: var(--green); }
.sp-value.c-yellow { color: var(--yellow); }
.sp-value.sm       { font-size: 1rem; margin-top: 4px; }

/* ═══════ TOOLBAR ═══════ */
.toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; }
.tb-label { font-size: .7rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
.tb-input {
    font-family: var(--font-mono); font-size: .84rem;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 8px; padding: 6px 12px; color: var(--text);
    outline: none; width: 128px; transition: border-color .2s;
}
.tb-input:focus { border-color: var(--blue); }
.tb-btn {
    font-family: var(--font-main); font-size: .77rem; font-weight: 600;
    padding: 6px 14px; border-radius: 8px; border: 1px solid var(--border);
    background: var(--surface); color: var(--text2); cursor: pointer;
    transition: all .16s; display: flex; align-items: center; gap: 6px;
}
.tb-btn:hover { background: var(--surface2); border-color: var(--border2); color: var(--text); }
.tb-btn.primary { background: var(--blue); border-color: var(--blue); color: #fff; box-shadow: 0 2px 8px #3b6ef830; }
.tb-btn.primary:hover { background: #2d5ee0; }

/* ═══════ RUTA CARD ═══════ */
.ruta-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); margin-bottom: 14px; overflow: hidden;
    box-shadow: var(--shadow-sm); transition: box-shadow .2s;
}
.ruta-card:hover { box-shadow: var(--shadow-md); }

.ruta-header {
    display: flex; align-items: center; gap: 14px;
    padding: 16px 20px; cursor: pointer; user-select: none;
    border-bottom: 1px solid transparent; transition: background .15s;
}
.ruta-header:hover { background: var(--surface2); }
.ruta-card.open > .ruta-header { border-bottom-color: var(--border); }

.ruta-badge {
    width: 48px; height: 48px; border-radius: 13px;
    background: var(--blue-soft); border: 2px solid var(--blue-mid);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; font-weight: 800; color: var(--blue); flex-shrink: 0;
}
.ruta-info { flex: 1; min-width: 0; }
.ruta-name { font-size: .97rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ruta-meta { font-size: .73rem; color: var(--muted); margin-top: 2px; font-family: var(--font-mono); }

/* SVG ring */
.ring-svg { width: 52px; height: 52px; flex-shrink: 0; }
.ring-svg circle { fill: none; stroke-width: 5; stroke-linecap: round; }
.ring-bg { stroke: var(--border); }
.ring-fg { stroke-dasharray: 138 138; stroke-dashoffset: 138; transition: stroke-dashoffset .8s cubic-bezier(.4,0,.2,1); }

.ruta-pct-box { text-align: right; flex-shrink: 0; min-width: 54px; }
.ruta-pct-num { font-family: var(--font-mono); font-size: 1.7rem; line-height: 1; }
.ruta-pct-lbl { font-size: .62rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }

.chevron { color: var(--muted); font-size: .95rem; transition: transform .25s; flex-shrink: 0; }
.ruta-card:not(.open) .chevron { transform: rotate(-90deg); }

/* thin progress bar */
.thin-bar { height: 4px; background: var(--bg); }
.thin-bar-fill { height: 100%; transition: width .8s cubic-bezier(.4,0,.2,1); }

/* ═══════ RUTA BODY ═══════ */
.ruta-body { display: none; padding: 18px 20px; }
.ruta-card.open .ruta-body { display: block; }

/* ═══════ ITEMS GRID ═══════ */
.items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }

.item-card {
    background: var(--surface2); border: 1px solid var(--border);
    border-radius: 12px; padding: 14px 16px;
    transition: box-shadow .18s, border-color .18s, transform .15s;
    position: relative;
}
.item-card:hover {
    box-shadow: var(--shadow-md); border-color: var(--blue-mid);
    transform: translateY(-2px);
}
.item-card:active { transform: translateY(0); }

.item-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.item-id  { font-family: var(--font-mono); font-size: .7rem; font-weight: 500; color: var(--muted); }
.status-chip { font-size: .62rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; padding: 3px 9px; border-radius: 99px; }
.chip-done    { background: var(--green-soft);  color: var(--green);  border: 1px solid #86efac; }
.chip-partial { background: var(--yellow-soft); color: var(--yellow); border: 1px solid #fcd34d; }
.chip-pending { background: var(--red-soft);    color: var(--red);    border: 1px solid #fca5a5; }

.item-track { height: 8px; border-radius: 99px; background: var(--border); overflow: hidden; margin-bottom: 7px; }
.item-fill  { height: 100%; border-radius: 99px; transition: width .7s ease; }

.item-row { display: flex; justify-content: space-between; font-family: var(--font-mono); font-size: .71rem; color: var(--muted); margin-bottom: 8px; }

/* Stepper dots */
.step-dots { display: flex; gap: 4px; flex-wrap: wrap; }
.step-dot {
    width: 14px; height: 14px; border-radius: 50%;
    border: 2px solid var(--border);
    background: var(--surface);
    position: relative; flex-shrink: 0;
    transition: all .2s;
}
.step-dot.done { border-color: transparent; }
.step-dot.done::after {
    content: '';
    position: absolute; inset: 0;
    border-radius: 50%;
}
.step-dot[title] { cursor: default; }

.item-last { font-size: .71rem; color: var(--muted); margin-top: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-last strong { color: var(--text2); }

.click-hint {
    position: absolute; bottom: 9px; right: 12px;
    font-size: .6rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    color: var(--blue); opacity: 0; transition: opacity .2s;
    display: flex; align-items: center; gap: 3px;
}
.item-card:hover .click-hint { opacity: 1; }

/* ═══════ COLOR HELPERS ═══════ */
.c-green  { color: var(--green);  }
.c-yellow { color: var(--yellow); }
.c-red    { color: var(--red);    }
.c-blue   { color: var(--blue);   }

/* ═══════ MODAL OVERLAY ═══════ */
.modal-overlay {
    position: fixed; inset: 0;
    background: #1e274270;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 1000;
    display: flex; align-items: flex-start; justify-content: center;
    padding: 24px 16px;
    overflow-y: auto;
    opacity: 0; pointer-events: none;
    transition: opacity .22s;
}
.modal-overlay.visible { opacity: 1; pointer-events: all; }

.detail-modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 18px;
    width: 100%; max-width: 740px;
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    transform: translateY(18px) scale(.98);
    transition: transform .25s cubic-bezier(.4,0,.2,1);
    margin: auto;
}
.modal-overlay.visible .detail-modal { transform: translateY(0) scale(1); }

/* modal header */
.modal-head {
    display: flex; align-items: center; gap: 14px;
    padding: 18px 22px;
    border-bottom: 1px solid var(--border);
    background: var(--surface2);
}
.modal-head-badge {
    width: 46px; height: 46px; border-radius: 12px;
    background: var(--blue-soft); border: 2px solid var(--blue-mid);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 800; color: var(--blue); flex-shrink: 0;
}
.modal-head-info { flex: 1; }
.modal-head-title { font-size: 1rem; font-weight: 700; color: var(--text); }
.modal-head-sub { font-size: .73rem; color: var(--muted); font-family: var(--font-mono); margin-top: 2px; }
.modal-close {
    width: 34px; height: 34px; border-radius: 8px;
    border: 1px solid var(--border); background: var(--surface);
    color: var(--muted); cursor: pointer; display: flex;
    align-items: center; justify-content: center; font-size: 1rem;
    transition: all .15s; flex-shrink: 0;
}
.modal-close:hover { background: var(--red-soft); border-color: #fca5a5; color: var(--red); }

/* modal body */
.modal-body { padding: 22px; }

/* overall % bar inside modal */
.modal-pct-row {
    display: flex; align-items: center; gap: 12px;
    background: var(--surface2); border: 1px solid var(--border);
    border-radius: 12px; padding: 14px 18px; margin-bottom: 22px;
}
.modal-pct-num { font-family: var(--font-mono); font-size: 2rem; line-height: 1; font-weight: 500; }
.modal-pct-track { flex: 1; height: 10px; border-radius: 99px; background: var(--border); overflow: hidden; }
.modal-pct-fill  { height: 100%; border-radius: 99px; transition: width .7s ease; }
.modal-pct-lbl   { font-size: .7rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }

/* timeline */
.timeline { position: relative; padding-left: 28px; }
.timeline::before {
    content: '';
    position: absolute; left: 10px; top: 0; bottom: 0;
    width: 2px; background: var(--border); border-radius: 2px;
}
.tl-item { position: relative; margin-bottom: 8px; }
.tl-item:last-child { margin-bottom: 0; }

.tl-dot {
    position: absolute; left: -22px; top: 14px;
    width: 16px; height: 16px; border-radius: 50%;
    border: 3px solid var(--border); background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    font-size: .5rem; z-index: 1;
    transition: all .2s;
}
.tl-dot.done   { background: var(--green);  border-color: var(--green);  color: #fff; }
.tl-dot.active { background: var(--yellow); border-color: var(--yellow); color: #fff; }

.tl-card {
    background: var(--surface2); border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden;
    transition: border-color .2s;
}
.tl-card.done-card   { border-left: 3px solid var(--green); }
.tl-card.active-card { border-left: 3px solid var(--yellow); }
.tl-card.pending-card{ border-left: 3px solid var(--border2); opacity: .6; }

/* accordion header */
.tl-acc-head {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px; cursor: pointer; user-select: none;
    transition: background .15s;
}
.tl-acc-head:hover { background: var(--border); border-radius: 9px; }
.tl-acc-icon { font-size: .85rem; color: var(--muted); flex-shrink: 0; }
.tl-acc-name { font-size: .85rem; font-weight: 600; color: var(--text); flex: 1; }
.tl-acc-time { font-family: var(--font-mono); font-size: .72rem; color: var(--muted); flex-shrink: 0; }
.tl-acc-chev { color: var(--muted); font-size: .8rem; transition: transform .2s; flex-shrink: 0; }
.tl-acc-head.collapsed .tl-acc-chev { transform: rotate(-90deg); }

/* step badge */
.step-num-badge {
    width: 22px; height: 22px; border-radius: 6px;
    font-family: var(--font-mono); font-size: .68rem; font-weight: 500;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.snb-done    { background: var(--green-soft);  color: var(--green);  }
.snb-active  { background: var(--yellow-soft); color: var(--yellow); }
.snb-pending { background: var(--border);      color: var(--muted);  }

/* accordion body */
.tl-acc-body { display: none; padding: 0 14px 14px; }
.tl-acc-body.show { display: block; }

/* detail sections inside accordion */
.detail-section { margin-bottom: 14px; }
.detail-section:last-child { margin-bottom: 0; }
.detail-section-title {
    font-size: .65rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
    color: var(--muted); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;
}
.detail-section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* info grid */
.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; }
.info-item { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 10px 12px; }
.info-label { font-size: .63rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); margin-bottom: 3px; }
.info-value { font-family: var(--font-mono); font-size: .82rem; color: var(--text); word-break: break-all; }

/* notes */
.notes-box {
    background: var(--yellow-soft); border: 1px solid #fcd34d;
    border-radius: 8px; padding: 10px 14px;
    font-size: .83rem; color: #92400e; line-height: 1.5;
}

/* photo */
.photo-wrap { border-radius: 10px; overflow: hidden; border: 1px solid var(--border); background: var(--surface2); }
.photo-wrap img { width: 100%; height: auto; display: block; max-height: 260px; object-fit: cover; }
.photo-placeholder {
    height: 90px; display: flex; align-items: center; justify-content: center;
    color: var(--muted); font-size: .82rem; gap: 8px;
}

/* firma */
.firma-wrap {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 10px; padding: 14px; text-align: center;
}
.firma-wrap img { max-height: 100px; max-width: 100%; border-radius: 6px; }
.firma-empty { color: var(--muted); font-size: .8rem; display: flex; align-items: center; justify-content: center; gap: 6px; height: 60px; }

/* map link */
.map-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .8rem; font-weight: 600; color: var(--blue);
    background: var(--blue-soft); border: 1px solid var(--blue-mid);
    border-radius: 8px; padding: 6px 14px; text-decoration: none;
    transition: all .15s;
}
.map-link:hover { background: var(--blue); color: #fff; }
.geo-text { font-family: var(--font-mono); font-size: .75rem; color: var(--muted); margin-top: 4px; }

/* ═══════ SKELETON / ERROR ═══════ */
.skeleton { background: linear-gradient(90deg,var(--border) 25%,#e8eaf0 50%,var(--border) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 10px; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.error-box { background: var(--red-soft); border: 1px solid #fca5a5; border-radius: 12px; padding: 18px 22px; color: var(--red); font-size: .86rem; font-weight: 500; }

::-webkit-scrollbar { width: 6px; background: var(--bg); }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

@media(max-width:600px){
    .main-wrap { padding: 14px; }
    .summary-strip { grid-template-columns: 1fr 1fr; }
    .ruta-header, .ruta-body { padding: 12px 14px; }
    .items-grid { grid-template-columns: 1fr 1fr; }
    .info-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<?php
    include_once 'nav.php';
?>
<br>
<div id="countdown-bar"><div id="countdown-fill"></div></div>

<!-- MAIN -->
<div class="main-wrap">

    <div class="summary-strip">
        <div class="stat-pill"><span class="sp-label">Rutas activas</span><span class="sp-value c-blue" id="stat-rutas">—</span></div>
        <div class="stat-pill"><span class="sp-label">Total operaciones</span><span class="sp-value" id="stat-items">—</span></div>
        <div class="stat-pill"><span class="sp-label">Al 100 %</span><span class="sp-value c-green" id="stat-done">—</span></div>
        <div class="stat-pill"><span class="sp-label">Promedio global</span><span class="sp-value c-yellow" id="stat-avg">—</span></div>
        <div class="stat-pill"><span class="sp-label">Actualización</span><span class="sp-value sm" id="stat-last">—</span></div>
    </div>

    <div class="toolbar">
        <span class="tb-label">Filtrar ruta:</span>
        <input id="filter-ruta" type="text" class="tb-input" placeholder="Todas…">
        <span class="tb-label">Fecha:</span>
        <input id="date-ruta" type="date" class="tb-input" value ="<?php echo date("Y-m-d")?>">        
        <button class="tb-btn" id="btn-expand-all"><i class="bi bi-arrows-expand"></i> Expandir</button>
        <button class="tb-btn" id="btn-collapse-all"><i class="bi bi-arrows-collapse"></i> Colapsar</button>
        <button class="tb-btn primary ms-auto" id="btn-refresh"><i class="bi bi-arrow-clockwise"></i> Actualizar</button>
    </div>

    <div id="cards-container">
        <div style="display:flex;flex-direction:column;gap:12px">
            <div class="skeleton" style="height:78px"></div>
            <div class="skeleton" style="height:78px"></div>
            <div class="skeleton" style="height:78px"></div>
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="detail-overlay">
    <div class="detail-modal" id="detail-modal">
        <div class="modal-head" id="modal-head"></div>
        <div class="modal-body" id="modal-body"></div>
    </div>
</div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
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

        fetchData();        


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








const API_URL     = 'api.php?action=stats';
const REFRESH_SEC = <?= $refresh_interval ?>;

const OP_ICONS = {
    'SURTIDO'              :'bi-box-seam',
    'CARGA'                :'bi-truck',
    'INSTALACION'          :'bi-tools',
    'PRUEBA FUNCIONAMIENTO':'bi-cpu',
    'ENTREGA'              :'bi-clipboard-check',
    'RECOLECCION'          :'bi-arrow-return-left',
    'ACONDICIONAMIENTO'    :'bi-wrench-adjustable',
    'ALMACENADO'           :'bi-archive',
};
//    'DOBLADO'              :'bi-layers',
/* ── helpers ── */
function pctColor(p){ return p>=100?'var(--green)':p>=50?'var(--yellow)':'var(--red)'; }
function pctCls(p)  { return p>=100?'c-green':p>=50?'c-yellow':'c-red'; }
function chipHtml(p){
    if(p>=100) return '<span class="status-chip chip-done"><i class="bi bi-check-circle-fill me-1"></i>Completo</span>';
    if(p>0)    return '<span class="status-chip chip-partial"><i class="bi bi-clock-fill me-1"></i>En proceso</span>';
    return           '<span class="status-chip chip-pending"><i class="bi bi-circle me-1"></i>Pendiente</span>';
}

/* ── render ruta card ── */
function renderRuta(r){
    const pct    = r.pct_global;
    const offset = Math.round(138 - (pct/100)*138);
    const id     = `rc-${r.ruta}`;

    /* step dots for each item */
    const stepColors = ['var(--green)','var(--green)','var(--green)','var(--green)','var(--green)','var(--green)','var(--green)','var(--green)','var(--green)'];

    const itemCards = r.items.map(it => {
        const hora = it.ultima_hora ? it.ultima_hora.substring(11,16) : '--';

        /* mini stepper dots */
        const dots = it.steps.map((s,i)=>{
            const col = s.done ? pctColor(100) : 'var(--border2)';
            const icon = s.done ? '✓' : '';
            return `<span class="step-dot ${s.done?'done':''}"
                style="${s.done?'background:'+col+';border-color:'+col : ''}"
                title="${s.operation_type}">
                ${s.done?'<i class="bi bi-check" style="color:#fff;font-size:.5rem;line-height:1"></i>':''}
            </span>`;
        }).join('');

        return `
        <div class="item-card">
            <div class="item-top">
                <span class="item-id"><i class="bi bi-hash"></i>OP-${it.id_operacion}</span>
                ${chipHtml(it.pct)}
            </div>
            <div class="item-track">
                <div class="item-fill" style="width:${it.pct}%;background:${pctColor(it.pct)}"></div>
            </div>
            <div class="item-row">
                <span>${it.client}</span>
            </div>       
            <div class="item-row">
                <span>${it.completados}/${it.total} pasos</span>
                <span class="${pctCls(it.pct)}" style="font-weight:600">${it.pct}%</span>
            </div>
            <div class="step-dots">${dots}</div>
            <div class="item-last" style="margin-top:8px">Último: <strong>${it.ultima_operacion}</strong> @ ${hora}</div>
            <div class="item-top">
                <span class="status-chip chip-done" style="cursor: pointer;" onclick="window.open('tel:${it.phone}')"><i class="bi bi-telephone"></i></i>Call</span>
            </div>                        
            <div class="item-top">
                <span class="click-hint" style="cursor: pointer;"  onclick="openDetail(${r.ruta}, ${it.id_operacion})"><i class="bi bi-eye" ></i></i>Ver detalle</span>
            </div>                        
        </div>
        
        `;
    }).join('');

    return `
    <div class="ruta-card" id="${id}" data-ruta="${r.ruta}">
        <div class="ruta-header" onclick="toggleCard('${id}')">
            <div class="ruta-badge">${r.ruta}</div>
            <div class="ruta-info">
                <div class="ruta-name">Ruta ${r.ruta} &mdash; ${r.operador}</div>
                <div class="ruta-meta"><i class="bi bi-truck"></i> ${r.vehiculo} &bull; <i class="bi bi-boxes"></i> ${r.total_items} operaciones</div>
            </div>
            <svg class="ring-svg" viewBox="0 0 50 50">
                <circle class="ring-bg" cx="25" cy="25" r="22"/>
                <circle class="ring-fg" cx="25" cy="25" r="22"
                    transform="rotate(-90 25 25)"
                    style="stroke:${pctColor(pct)};stroke-dashoffset:${offset}"/>
            </svg>
            <div class="ruta-pct-box">
                <div class="ruta-pct-num ${pctCls(pct)}">${pct}%</div>
                <div class="ruta-pct-lbl">Global</div>
            </div>
            <i class="bi bi-chevron-down chevron"></i>
        </div>
        <div class="thin-bar"><div class="thin-bar-fill" style="width:${pct}%;background:${pctColor(pct)}"></div></div>
        <div class="ruta-body">
            <div class="items-grid">${itemCards}</div>
        </div>
    </div>`;
}

function toggleCard(id){
    const $c = $(`#${id}`);
    $c.toggleClass('open');
}

/* ── MODAL ── */
let _allData = [];   // cache de rutas

function openDetail(rutaId, opId){
    const ruta = _allData.find(r => String(r.ruta) === String(rutaId));
    if(!ruta) return;
    const item = ruta.items.find(i => i.id_operacion == opId);
    if(!item) return;

    /* HEAD */
    $('#modal-head').html(`
        <div class="modal-head-badge">${ruta.ruta}</div>
        <div class="modal-head-info">
            <div class="modal-head-title">Ruta ${ruta.ruta} &mdash; OP-${item.id_operacion}</div>
            <div class="modal-head-sub">
                <i class="bi bi-person"></i> ${ruta.operador}
                &nbsp;&bull;&nbsp;
                <i class="bi bi-truck"></i> ${ruta.vehiculo}
            </div>
        </div>
        <button class="modal-close" onclick="closeDetail()"><i class="bi bi-x-lg"></i></button>
    `);

    /* BODY */
    let bodyHtml = '';

    /* % global de la operación */
    bodyHtml += `
    <div class="modal-pct-row">
        <div>
            <div class="modal-pct-num ${pctCls(item.pct)}">${item.pct}%</div>
            <div class="modal-pct-lbl">Completado</div>
        </div>
        <div class="modal-pct-track">
            <div class="modal-pct-fill" style="width:${item.pct}%;background:${pctColor(item.pct)}"></div>
        </div>
        <div>${chipHtml(item.pct)}</div>
    </div>`;

    /* Timeline de pasos */
    bodyHtml += '<div class="timeline">';

    item.steps.forEach((step, idx) => {
        const isDone    = step.done;
        const isActive  = !isDone && item.steps.slice(0,idx).some(s=>s.done);
        const icon      = OP_ICONS[step.operation_type] || 'bi-circle';
        const dotCls    = isDone ? 'done' : isActive ? 'active' : '';
        const cardCls   = isDone ? 'done-card' : isActive ? 'active-card' : 'pending-card';
        const snbCls    = isDone ? 'snb-done' : isActive ? 'snb-active' : 'snb-pending';
        const hora      = isDone && step.fechahora ? step.fechahora.substring(11,16) : '--';
        const fecha     = isDone && step.fechahora ? step.fechahora.substring(0,10) : '';
        const accId     = `acc-${opId}-${idx}`;
        //step.firma ='';
        /* solo las operaciones con datos interesantes se abren */
        //const hasContent = isDone && (step.notas || step.url_photo || step.firma || step.geolocation);
        const hasContent = isDone && (step.notas || step.url_photo ||  step.geolocation);

        bodyHtml += `
        <div class="tl-item">
            <div class="tl-dot ${dotCls}">
                ${isDone ? '<i class="bi bi-check" style="color:#fff;font-size:.55rem"></i>' : ''}
            </div>
            <div class="tl-card ${cardCls}">
                <div class="tl-acc-head ${!hasContent ? '' : ''}" onclick="toggleAcc('${accId}',${hasContent})">
                    <div class="step-num-badge ${snbCls}">${step.step}</div>
                    <i class="bi ${icon} tl-acc-icon"></i>
                    <span class="tl-acc-name">${step.operation_type}</span>
                    <span class="tl-acc-time">${isDone ? fecha+' '+hora : 'Pendiente'}</span>
                    ${hasContent ? '<i class="bi bi-chevron-down tl-acc-chev collapsed"></i>' : '<i class="bi bi-dash tl-acc-chev" style="opacity:.3"></i>'}
                </div>
                ${hasContent ? `
                <div class="tl-acc-body" id="${accId}">
                    ${buildStepDetail(step, ruta, item)}
                </div>` : ''}
            </div>
        </div>`;
    });

    bodyHtml += '</div>'; /* /timeline */

    $('#modal-body').html(bodyHtml);

    /* abrir automáticamente el último paso completado */
    const lastDoneIdx = [...item.steps].reverse().findIndex(s=>s.done && (s.notas||s.url_photo||s.firma||s.geolocation));
    if(lastDoneIdx !== -1){
        const realIdx = item.steps.length - 1 - lastDoneIdx;
        const accId = `acc-${opId}-${realIdx}`;
        $(`#${accId}`).addClass('show');
        $(`#${accId}`).prev('.tl-acc-head').find('.tl-acc-chev').removeClass('collapsed');
    }

    $('#detail-overlay').addClass('visible');
    $('body').css('overflow','hidden');
}

function buildStepDetail(step, ruta, item){
    let html = '';

    /* ── Info general ── */
    html += `
    <div class="detail-section">
        <div class="detail-section-title"><i class="bi bi-info-circle"></i> Información</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Operación</div>
                <div class="info-value">${step.operation_type}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Fecha y hora</div>
                <div class="info-value">${step.fechahora || '—'}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Operador</div>
                <div class="info-value">${ruta.operador}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Vehículo</div>
                <div class="info-value">${ruta.vehiculo}</div>
            </div>
            <div class="info-item">
                <div class="info-label">ID Operación</div>
                <div class="info-value">OP-${item.id_operacion}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Ruta</div>
                <div class="info-value">${ruta.ruta}</div>
            </div>
        </div>
    </div>`;

    /* ── Observaciones / Notas ── */
    if(step.notas){
        html += `
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-chat-text"></i> Observaciones</div>
            <div class="notes-box"><i class="bi bi-quote me-1"></i>${step.notas}</div>
        </div>`;
    }

    /* ── Foto ── */
    html += `
    <div class="detail-section">
        <div class="detail-section-title"><i class="bi bi-camera"></i> Fotografía</div>
        <div class="photo-wrap">`;
    if(step.url_photo){
        html += `<img src="${step.url_photo}" alt="Foto ${step.operation_type}" onerror="this.parentElement.innerHTML='<div class=\\'photo-placeholder\\'><i class=\\'bi bi-image-slash\\'></i> Imagen no disponible</div>'">`;
    } else {
        html += `<div class="photo-placeholder"><i class="bi bi-camera-slash"></i> Sin fotografía registrada</div>`;
    }
    html += `</div></div>`;

    /* ── Geolocalización ── */
    html += `
    <div class="detail-section">
        <div class="detail-section-title"><i class="bi bi-geo-alt"></i> Ubicación</div>`;
    if(step.geolocation){
        const coords = step.geolocation.trim();
        const mapUrl = `https://maps.google.com/?q=${encodeURIComponent(coords)}`;
        html += `
        <a href="${mapUrl}" target="_blank" class="map-link">
            <i class="bi bi-geo-alt-fill"></i> Ver en Google Maps
        </a>
        <div class="geo-text">${coords}</div>`;
    } else {
        html += `<div class="info-item" style="display:inline-flex;align-items:center;gap:6px;padding:10px 14px">
            <i class="bi bi-geo-slash" style="color:var(--muted)"></i>
            <span style="font-size:.8rem;color:var(--muted)">Sin geolocalización registrada</span>
        </div>`;
    }
    html += `</div>`;

    /* ── Firma ── */
    html += `
    <div class="detail-section">
        <div class="detail-section-title"><i class="bi bi-pen"></i> Firma</div>
        <div class="firma-wrap">`;
    if(step.firma && step.firma.startsWith('data:image')){
        html += `<img src="${step.firma}" alt="Firma digital">`;
    } else if(step.firma) {
        html += `<img src="${step.firma}" alt="Firma digital" onerror="this.outerHTML='<div class=\\'firma-empty\\'><i class=\\'bi bi-pen-slash\\'></i> Firma no disponible</div>'">`;
    } else {
        html += `<div class="firma-empty"><i class="bi bi-pen-slash"></i> Sin firma registrada</div>`;
    }
    html += `</div></div>`;

    return html;
}

function toggleAcc(id, hasContent){
    if(!hasContent) return;
    const $body = $(`#${id}`);
    const $chev = $body.prev('.tl-acc-head').find('.tl-acc-chev');
    $body.toggleClass('show');
    $chev.toggleClass('collapsed');
}

function closeDetail(){
    $('#detail-overlay').removeClass('visible');
    $('body').css('overflow','');
}

// Cerrar al hacer click fuera
$('#detail-overlay').on('click', function(e){
    if($(e.target).is('#detail-overlay')) closeDetail();
});
$(document).on('keydown', function(e){ if(e.key==='Escape') closeDetail(); });

/* ── reloj ── */
function tickClock(){
    const n=new Date(), p=x=>String(x).padStart(2,'0');
    $('#clock').text(`${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`);
}
setInterval(tickClock,1000); tickClock();

/* ── countdown ── */
let left=REFRESH_SEC;
function tickCountdown(){
    left--;
    if(left<=0){ left=REFRESH_SEC; fetchData(); }
    $('#countdown-fill').css('width',(left/REFRESH_SEC*100)+'%');
}
let cdTimer=setInterval(tickCountdown,1000);

/* ── fetch ── */
let openSet=new Set();

function fetchData() {
    $('.ruta-card.open').each(function() { 
        openSet.add($(this).data('ruta')); 
    });
    
    const fv = $('#filter-ruta').val().trim().toLowerCase();
    $('#btn-refresh').html('<i class="bi bi-arrow-clockwise" style="animation:spin .7s linear infinite;display:inline-block"></i> Actualizando…');

    let formData = new FormData();
    formData.append('date', $('#date-ruta').val());    

    $.ajax({
        url: API_BASE_URL + 'api/data_monitor/',
        method: 'POST',
        data: formData,
        headers: { 'Authorization': 'Bearer ' + TOKEN },
        processData: false, // Vital para FormData
        contentType: false, // Vital para FormData
        // Si necesitas enviar datos en el body del POST, agrégalos aquí:
        // data: { action: 'get_rutas', filter: fv }, 
        success: function(data) {
            if (!data.success) { 
                showError('No existen datos para la fecha seleccionada.'); 
                return; 
            }
            
            _allData = data.rutas;
            let rutas = data.rutas;
            
            if (fv) {
                rutas = rutas.filter(r => String(r.ruta).toLowerCase().includes(fv));
            }

            // Actualización de estadísticas
            $('#stat-rutas').text(rutas.length);
            $('#stat-items').text(rutas.reduce((a, r) => a + r.total_items, 0));
            $('#stat-done').text(rutas.filter(r => r.pct_global >= 100).length);
            $('#stat-avg').text((rutas.length ? Math.round(rutas.reduce((a, r) => a + r.pct_global, 0) / rutas.length) : 0) + '%');
            $('#stat-last').text(data.timestamp.substring(11, 19));

            if (!rutas.length) {
                $('#cards-container').html('<div style="text-align:center;padding:60px;color:var(--muted);font-size:.9rem;font-weight:600">SIN RESULTADOS</div>');
                return;
            }

            // Renderizado
            let html = '';
            rutas.forEach(r => { html += renderRuta(r); });
            $('#cards-container').html(html);

            // Restaurar estado de tarjetas abiertas
            if (openSet.size > 0) {
                openSet.forEach(rutaId => { 
                    $(`[data-ruta="${rutaId}"]`).addClass('open'); 
                });
            }
        },
        error: function() {
            showError('No se pudo conectar. Reintentando en ' + REFRESH_SEC + 's…');
        },
        complete: function() {
            $('#btn-refresh').html('<i class="bi bi-arrow-clockwise"></i> Actualizar');
        }
    });
}

function showError(msg){
    $('#cards-container').html(`<div class="error-box"><i class="bi bi-exclamation-triangle-fill me-2"></i>${msg}</div>`);
}

$('#btn-refresh').on('click',()=>{ left=REFRESH_SEC; $('#countdown-fill').css('width','100%'); fetchData(); });
$('#btn-expand-all').on('click',()=>{ $('.ruta-card').addClass('open'); $('.ruta-card').each(function(){ openSet.add($(this).data('ruta')); }); });
$('#btn-collapse-all').on('click',()=>{ $('.ruta-card').removeClass('open'); openSet.clear(); });
$('#filter-ruta').on('input',()=>{ clearInterval(cdTimer); openSet.clear(); fetchData(); left=REFRESH_SEC; cdTimer=setInterval(tickCountdown,1000); });

$('<style>@keyframes spin{to{transform:rotate(360deg)}}</style>').appendTo('head');

</script>
</body>
</html>