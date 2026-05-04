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
    $query = "select Traduccion FROM  programas_traduccion where Programa = 'monitor' AND Idioma = ? ORDER BY Id";            
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

    $refresh_interval = 60;
    include_once 'head.php';    
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/monitor.css">
</head>
<body>

<?php
    include_once 'nav.php';
?>
<br>
<br>
<div id="countdown-bar">
    <div id="countdown-fill">
        
    </div>
</div>

<!-- MAIN -->
<div class="main-wrap">

    <div class="summary-strip">
        <div class="stat-pill"><span class="sp-label"><?= Trd(1) ?></span><span class="sp-value c-blue" id="stat-rutas">—</span></div>
        <div class="stat-pill"><span class="sp-label"><?= Trd(2) ?></span><span class="sp-value" id="stat-items">—</span></div>
        <div class="stat-pill"><span class="sp-label"><?= Trd(3) ?></span><span class="sp-value c-green" id="stat-done">—</span></div>
        <div class="stat-pill"><span class="sp-label"><?= Trd(4) ?></span><span class="sp-value c-yellow" id="stat-avg">—</span></div>
        <div class="stat-pill"><span class="sp-label"><?= Trd(5) ?></span><span class="sp-value sm" id="stat-last">—</span></div>
    </div>

    <div class="toolbar">
        <span class="tb-label"><?= Trd(6) ?></span>
        <input id="filter-ruta" type="text" class="tb-input" placeholder="<?= Trd(7) ?>">
        <span class="tb-label"><?= Trd(8) ?></span>
        <input id="date-ruta" type="date" class="tb-input" value ="<?php echo date("Y-m-d")?>">        
        <button class="tb-btn" id="btn-expand-all"><i class="bi bi-arrows-expand"></i><?= Trd(9) ?> </button>
        <button class="tb-btn" id="btn-collapse-all"><i class="bi bi-arrows-collapse"></i> <?= Trd(10) ?></button>
        <button class="tb-btn primary ms-auto" id="btn-refresh"><i class="bi bi-arrow-clockwise"></i> <?= Trd(11) ?></button>
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

<!-- JS 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
-->
<script>


    const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
    const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
    const TOKEN = localStorage.getItem('apiToken'); 

    $(document).ready(function() {
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

    $(document).ajaxSuccess(function(event, xhr, settings) {
        const nuevoToken = xhr.getResponseHeader('Authorization-Update');
        if (nuevoToken) {
            localStorage.setItem('apiToken', nuevoToken);
            console.log("Token actualizado globalmente desde: " + settings.url);
        }
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
    if(p>=100) return '<span class="status-chip chip-done"><i class="bi bi-check-circle-fill me-1"></i><?= Trd(12) ?></span>';
    if(p>0)    return '<span class="status-chip chip-partial"><i class="bi bi-clock-fill me-1"></i><?= Trd(13) ?></span>';
    return           '<span class="status-chip chip-pending"><i class="bi bi-circle me-1"></i><?= Trd(14) ?></span>';
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
                <span>${it.completados}/${it.total} <?= Trd(15) ?></span>
                <span class="${pctCls(it.pct)}" style="font-weight:600">${it.pct}%</span>
            </div>
            <div class="step-dots">${dots}</div>
            <div class="item-last" style="margin-top:8px"><?= Trd(16) ?> <strong>${it.ultima_operacion}</strong> @ ${hora}</div>
            <div class="item-top">
                <span class="status-chip chip-done" style="cursor: pointer;" onclick="window.open('tel:${it.phone}')"><i class="bi bi-telephone"></i></i><?= Trd(17) ?></span>
            </div>                        
            <div class="item-top">
                <span class="click-hint" style="cursor: pointer;"  onclick="openDetail(${r.ruta}, ${it.id_operacion})"><i class="bi bi-eye" ></i></i><?= Trd(18) ?></span>
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
                <div class="ruta-meta"><i class="bi bi-truck"></i> ${r.vehiculo} &bull; <i class="bi bi-boxes"></i> ${r.total_items} <?= Trd(19) ?></div>
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
            <div class="modal-head-title"><?= Trd(20) ?> ${ruta.ruta} &mdash; OP-${item.id_operacion}</div>
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
            <div class="modal-pct-lbl"><?= Trd(21) ?></div>
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
                    <span class="tl-acc-time">${isDone ? fecha+' '+hora : '<?= Trd(22) ?>'}</span>
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
        <div class="detail-section-title"><i class="bi bi-info-circle"></i> <?= Trd(23) ?></div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label"><?= Trd(24) ?></div>
                <div class="info-value">${step.operation_type}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><?= Trd(25) ?></div>
                <div class="info-value">${step.fechahora || '—'}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><?= Trd(26) ?></div>
                <div class="info-value">${ruta.operador}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><?= Trd(27) ?></div>
                <div class="info-value">${ruta.vehiculo}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><?= Trd(28) ?></div>
                <div class="info-value">OP-${item.id_operacion}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><?= Trd(29) ?></div>
                <div class="info-value">${ruta.ruta}</div>
            </div>
        </div>
    </div>`;

    /* ── Observaciones / Notas ── */
    if(step.notas){
        html += `
        <div class="detail-section">
            <div class="detail-section-title"><i class="bi bi-chat-text"></i><?= Trd(30) ?></div>
            <div class="notes-box"><i class="bi bi-quote me-1"></i>${step.notas}</div>
        </div>`;
    }

    /* ── Foto ── */
    html += `
    <div class="detail-section">
        <div class="detail-section-title"><i class="bi bi-camera"></i><?= Trd(31) ?> </div>
        <div class="photo-wrap">`;
    if(step.url_photo){
        html += `<img src="${step.url_photo}" alt="Foto ${step.operation_type}" onerror="this.parentElement.innerHTML='<div class=\\'photo-placeholder\\'><i class=\\'bi bi-image-slash\\'></i> <?= Trd(32) ?></div>'">`;
    } else {
        html += `<div class="photo-placeholder"><i class="bi bi-camera-slash"></i> <?= Trd(33) ?></div>`;
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
            <span style="font-size:.8rem;color:var(--muted)"><?= Trd(34) ?></span>
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
        html += `<img src="${step.firma}" alt="Firma digital" onerror="this.outerHTML='<div class=\\'firma-empty\\'><i class=\\'bi bi-pen-slash\\'></i> <?= Trd(35) ?></div>'">`;
    } else {
        html += `<div class="firma-empty"><i class="bi bi-pen-slash"></i> <?= Trd(36) ?></div>`;
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
    $('#btn-refresh').html('<i class="bi bi-arrow-clockwise" style="animation:spin .7s linear infinite;display:inline-block"></i><?= Trd(37) ?> ');

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
                showError('<?= Trd(38) ?>'); 
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
                $('#cards-container').html('<div style="text-align:center;padding:60px;color:var(--muted);font-size:.9rem;font-weight:600"><?= Trd(39) ?></div>');
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
            showError('<?= Trd(40) ?> ' + REFRESH_SEC + 's…');
        },
        complete: function() {
            $('#btn-refresh').html('<i class="bi bi-arrow-clockwise"></i> <?= Trd(41) ?>');
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