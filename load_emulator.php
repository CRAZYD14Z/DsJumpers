<?php
    ob_start();
    session_start(); 
    include_once 'valid_login.php';
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();

    include_once 'head.php';
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Outfit:wght@300;400;500;600;700&display=swap');
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#f0f2f7;--s1:#ffffff;--s2:#f5f7fb;--s3:#eaecf3;
  --bd:#d4d9e8;--bd2:#bcc4d8;
  --t1:#1a2035;--t2:#4a5572;--t3:#8892aa;
  --acc:#2563eb;--acc2:#059669;--acc3:#ea580c;
  --p1:#dc2626;--p2:#ea580c;--p3:#ca8a04;--p4:#16a34a;--p5:#64748b;
  --lifo:#7c3aed;--zone-door:#059669;--zone-mid:#2563eb;--zone-back:#ea580c;
  --frag:#db2777;
  --r:8px;--r2:12px;
}
body{background:var(--bg);color:var(--t1);font-family:'Outfit',sans-serif;height:100vh;display:flex;flex-direction:column;overflow:hidden;}
.topbar{display:flex;align-items:center;gap:12px;padding:10px 18px;background:var(--s1);border-bottom:1px solid var(--bd);flex-shrink:0;z-index:20;box-shadow:0 1px 4px rgba(0,0,0,.06);}
.brand{display:flex;align-items:center;gap:10px;}
.brand-icon{width:34px;height:34px;background:linear-gradient(135deg,var(--acc),var(--acc2));border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.brand h1{font-size:14px;font-weight:700;letter-spacing:.2px;color:var(--t1);}
.brand span{font-size:11px;color:var(--t3);font-family:'IBM Plex Mono',monospace;}
.strategy-tabs{display:flex;gap:4px;margin-left:12px;background:var(--s2);border:1px solid var(--bd);border-radius:var(--r);padding:3px;}
.stab{padding:5px 10px;border-radius:6px;border:none;font-family:'Outfit',sans-serif;font-size:11px;font-weight:600;cursor:pointer;color:var(--t2);background:transparent;transition:all .18s;white-space:nowrap;}
.stab:hover{color:var(--t1);background:var(--s3);}
.stab.active{background:var(--acc);color:#fff;}
.topstats{margin-left:auto;display:flex;gap:18px;align-items:center;}
.tstat{text-align:right;}
.tstat-v{font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:600;color:var(--t1);}
.tstat-l{font-size:10px;color:var(--t3);text-transform:uppercase;letter-spacing:.6px;}
.main{display:flex;flex:1;overflow:hidden;}
.panel{width:290px;background:var(--s1);border-right:1px solid var(--bd);display:flex;flex-direction:column;overflow:hidden;flex-shrink:0;box-shadow:2px 0 8px rgba(0,0,0,.04);}
.pblock{padding:12px 14px;border-bottom:1px solid var(--bd);}
.pblock h2{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--t3);margin-bottom:8px;}
.row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;}
.field label{display:block;font-size:10px;color:var(--t3);margin-bottom:3px;font-family:'IBM Plex Mono',monospace;}
.field input,.field select{width:100%;background:var(--s2);border:1px solid var(--bd);border-radius:6px;color:var(--t1);font-family:'IBM Plex Mono',monospace;font-size:11px;padding:5px 7px;outline:none;transition:border-color .15s;}
.field input:focus{border-color:var(--acc);box-shadow:0 0 0 2px rgba(37,99,235,.1);}
.field.full{grid-column:1/-1;}
.btn{border:none;border-radius:var(--r);font-family:'Outfit',sans-serif;font-weight:600;cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:center;gap:5px;}
.btn-acc{background:var(--acc);color:#fff;font-size:12px;padding:7px 14px;}
.btn-acc:hover{background:#1d4ed8;}
.btn-ghost{background:var(--s2);color:var(--t2);border:1px solid var(--bd);font-size:11px;padding:6px 10px;}
.btn-ghost:hover{border-color:var(--acc);color:var(--acc);background:rgba(37,99,235,.05);}
.btn-lifo{background:rgba(124,58,237,.08);color:var(--lifo);border:1px solid rgba(124,58,237,.25);font-size:11px;padding:6px 10px;}
.btn-lifo:hover{background:rgba(124,58,237,.15);}
.btn-danger{background:rgba(220,38,38,.06);color:var(--p1);border:1px solid rgba(220,38,38,.2);font-size:11px;padding:6px 8px;}
.btn-danger:hover{background:rgba(220,38,38,.12);}
.btn:disabled{opacity:.35;cursor:not-allowed;}
.zone-legend{display:flex;gap:6px;flex-wrap:wrap;}
.zone-tag{display:flex;align-items:center;gap:4px;font-size:10px;font-weight:600;padding:3px 7px;border-radius:5px;}
.zone-tag.door{background:rgba(5,150,105,.08);color:var(--zone-door);border:1px solid rgba(5,150,105,.2);}
.zone-tag.mid{background:rgba(37,99,235,.08);color:var(--zone-mid);border:1px solid rgba(37,99,235,.2);}
.zone-tag.back{background:rgba(234,88,12,.08);color:var(--zone-back);border:1px solid rgba(234,88,12,.2);}
.zone-tag.frag{background:rgba(219,39,119,.08);color:var(--frag);border:1px solid rgba(219,39,119,.2);}
.items-scroll{flex:1;overflow-y:auto;padding:4px 0;}
.items-scroll::-webkit-scrollbar{width:4px;}
.items-scroll::-webkit-scrollbar-track{background:var(--s2);}
.items-scroll::-webkit-scrollbar-thumb{background:var(--bd);border-radius:2px;}
.item-row{display:flex;align-items:center;gap:7px;padding:7px 14px;cursor:pointer;border-left:3px solid transparent;transition:all .12s;}
.item-row:hover{background:var(--s2);}
.item-row.active{background:rgba(37,99,235,.06);border-left-color:var(--acc);}
.item-row.done{opacity:.45;}
.item-row.done .step-n{background:var(--s3);color:var(--t3);}
.cdot{width:9px;height:9px;border-radius:3px;flex-shrink:0;}
.iinfo{flex:1;min-width:0;}
.iname{font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--t1);}
.imeta{font-size:10px;color:var(--t3);font-family:'IBM Plex Mono',monospace;display:flex;gap:6px;margin-top:1px;flex-wrap:wrap;}
.zone-pip{font-size:9px;padding:1px 4px;border-radius:3px;font-weight:700;}
.zone-pip.door{color:var(--zone-door);background:rgba(5,150,105,.1);}
.zone-pip.mid{color:var(--zone-mid);background:rgba(37,99,235,.1);}
.zone-pip.back{color:var(--zone-back);background:rgba(234,88,12,.1);}
.step-n{font-family:'IBM Plex Mono',monospace;font-size:10px;padding:2px 5px;border-radius:4px;background:var(--s3);color:var(--t3);flex-shrink:0;}
.step-n.active{background:var(--acc);color:#fff;}
.controls{padding:12px 14px;border-top:1px solid var(--bd);flex-shrink:0;background:var(--s2);}
.step-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;}
.step-ctr{font-family:'IBM Plex Mono',monospace;font-size:12px;color:var(--t3);}
.step-ctr strong{color:var(--acc2);}
.step-name{font-size:11px;font-weight:600;color:var(--t1);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-align:right;}
.prog-track{height:3px;background:var(--bd);border-radius:2px;margin-bottom:8px;overflow:hidden;}
.prog-fill{height:100%;background:linear-gradient(90deg,var(--acc),var(--acc2));border-radius:2px;transition:width .3s;}
.occ-row{display:flex;justify-content:space-between;font-size:10px;color:var(--t3);margin-bottom:4px;}
.occ-track{height:5px;background:var(--bd);border-radius:3px;overflow:hidden;margin-bottom:10px;}
.occ-fill{height:100%;border-radius:3px;transition:width .35s,background .35s;}
.btn-row{display:flex;gap:5px;margin-bottom:5px;}
.no-fit{background:rgba(220,38,38,.05);border:1px solid rgba(220,38,38,.15);border-radius:var(--r);padding:7px 10px;margin-top:6px;}
.no-fit-title{font-size:10px;color:var(--p1);font-weight:700;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;}
.no-fit-item{font-size:10px;color:var(--t2);padding:2px 0;display:flex;align-items:center;gap:5px;}
.viewport{flex:1;position:relative;overflow:hidden;background:radial-gradient(ellipse 80% 70% at 45% 30%,#e8edf8 0%,#dde3f0 100%);}
#c{display:block;width:100%!important;height:100%!important;}
#tip{position:absolute;background:var(--s1);border:1px solid var(--bd2);border-radius:var(--r);padding:10px 13px;font-size:11px;line-height:1.8;pointer-events:none;display:none;z-index:50;font-family:'IBM Plex Mono',monospace;box-shadow:0 6px 24px rgba(0,0,0,.12);max-width:210px;color:var(--t1);}
#tip strong{color:var(--acc);font-size:12px;display:block;margin-bottom:3px;}
#tip .tag{display:inline-flex;align-items:center;gap:3px;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:700;margin-top:3px;}
#tip .tag.door{background:rgba(5,150,105,.1);color:var(--zone-door);}
#tip .tag.mid{background:rgba(37,99,235,.1);color:var(--zone-mid);}
#tip .tag.back{background:rgba(234,88,12,.1);color:var(--zone-back);}
#tip .tag.frag{background:rgba(219,39,119,.1);color:var(--frag);}
.hint{position:absolute;bottom:14px;right:14px;font-size:10px;color:var(--t3);text-align:right;pointer-events:none;line-height:2;}
.zone-overlay{position:absolute;bottom:0;left:0;right:0;height:4px;display:flex;pointer-events:none;}
.zo-door{flex:1;background:var(--zone-door);opacity:.5;}
.zo-mid{flex:1;background:var(--zone-mid);opacity:.5;}
.zo-back{flex:1;background:var(--zone-back);opacity:.5;}
.rot-badge{display:inline-flex;align-items:center;gap:2px;font-size:9px;padding:1px 4px;border-radius:3px;background:rgba(37,99,235,.08);color:var(--acc);border:1px solid rgba(37,99,235,.2);}
</style>
</head>
<body>
<?php
    include_once 'nav.php';
?>
<?php
    function obtenerColorPorId($id) {
        $colores = [
            "#3A7BD5","#2C3E50","#E74C3C","#27AE60","#F1C40F",
            "#8E44AD","#2980B9","#E67E22","#16A085","#C0392B",
            "#7F8C8D","#D35400","#1ABC9C","#9B59B6","#34495E"
        ];
        return $colores[$id % count($colores)];
    }

    $query = "SELECT id_route from daily_route WHERE date = ? AND id_vehicle = ?";
    $stmt = $db->prepare($query);
    $stmt->bindValue(1,$_GET['FechaInicial'] );
    $stmt->bindValue(2,$_GET['VehiculoId'] );
    $stmt->execute();
    $daily_route = $stmt->fetch(PDO::FETCH_ASSOC);    

    $query = "SELECT height, width,depth from vehicles WHERE id_vehicle = ?";
    $stmt = $db->prepare($query);
    $stmt->bindValue(1,$_GET['VehiculoId'] );
    $stmt->execute();
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);       

    $query = "SELECT * from route_stops WHERE id_route = ? ORDER BY visit_order DESC";
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $daily_route['id_route']);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $Vehiculo = '';
    $Operador = '';

    $productos_finales = [];
    if ($resultados) {
        foreach ($resultados as $reg) {

            if ($Vehiculo == ''){

              $query = "SELECT * from v_operations WHERE Id_operation = ?";
              $stmt = $db->prepare($query);
              $stmt->bindValue(1, $reg['id_operation']);
              $stmt->execute();
              $operation = $stmt->fetch(PDO::FETCH_ASSOC);            

              $Vehiculo = $operation['vehiculo']. " ".$operation['placas'];
              $Operador = $operation['NombresChofer']. " ".$operation['ApellidosChofer'];
              
            }        

            $query = "SELECT * from v_operation_checklist WHERE Id_operation = ? AND STAGE = 'SURTIDO'";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $reg['id_operation']);
            $stmt->execute();
            $products_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($products_db) {
                foreach ($products_db as $prd) {
                    if ($prd['id_accesory_base'] == 0 && $prd['id_accesory'] == 0 && $prd['load'] == 1) {
                        $productos_finales[] = [
                            "nombre"   => trim($prd['Product']),
                            "h"        => (float)$prd['height'],
                            "w"        => (float)$prd['width'],
                            "d"        => (float)$prd['depth'],
                            "peso"     => (float)$prd['Weight'],
                            "qty"      => (int)$prd['requested_quantity'],
                            "color"    => obtenerColorPorId($reg['visit_order']),
                            "prioridad"=> $reg['visit_order'],
                            "entrega"  => "2026-04-01",
                            "fragil"   => (bool)$prd['fragile'],
                            "apilable" => (bool)$prd['stackable'],
                            "girar"    => true,
                            "tipo"     => "prd"
                        ];
                    } elseif ($prd['id_accesory_base'] != 0 && $prd['id_accesory'] == 0 && $prd['load_base'] == 1) {
                        $productos_finales[] = [
                            "nombre"   => trim($prd['Base']),
                            "h"        => (float)$prd['height_base'],
                            "w"        => (float)$prd['width_base'],
                            "d"        => (float)$prd['depth_base'],
                            "peso"     => (float)$prd['Weight_base'],
                            "qty"      => (int)$prd['requested_quantity'],
                            "color"    => obtenerColorPorId($reg['visit_order']),
                            "prioridad"=> $reg['visit_order'],
                            "entrega"  => "2026-04-01",
                            "fragil"   => (bool)$prd['fragile_base'],
                            "apilable" => (bool)$prd['stackable_base'],
                            "girar"    => true,
                            "tipo"     => "base"
                        ];
                    } elseif ($prd['id_accesory_base'] == 0 && $prd['id_accesory'] != 0 && $prd['load_accesory'] == 1) {
                        $productos_finales[] = [
                            "nombre"   => trim($prd['Accesory']),
                            "h"        => (float)$prd['height_accesory'],
                            "w"        => (float)$prd['width_accesory'],
                            "d"        => (float)$prd['depth_accesory'],
                            "peso"     => (float)$prd['Weight_accesory'],
                            "qty"      => (int)$prd['requested_quantity'],
                            "color"    => obtenerColorPorId($reg['visit_order']),
                            "prioridad"=> $reg['visit_order'],
                            "entrega"  => "2026-04-01",
                            "fragil"   => (bool)$prd['fragile_accesory'],
                            "apilable" => (bool)$prd['stackable_accesory'],
                            "girar"    => true,
                            "tipo"     => "acc"
                        ];
                    }
                }
            }
        }
    }

    $json = json_encode($productos_finales, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    $js_obj = preg_replace('/"([^"]+)":/', '$1:', $json);
?>

<!-- TOPBAR -->
<header class="topbar">
  <div class="brand">
    <div class="brand-icon">🚛</div>
    <div>
      <h1>Motor de Carga 3D</h1>
      <span id="strat-label">LIFO + Prioridad activo</span>
    </div>
  </div>
  <div class="strategy-tabs">
    <button class="stab" data-s="BLF_VOLUME" onclick="setStrategy(this)">Volumen</button>
    <button class="stab" data-s="LIFO" onclick="setStrategy(this)">LIFO</button>
    <button class="stab" data-s="PRIORITY" onclick="setStrategy(this)">Prioridad</button>
    <button class="stab active" data-s="LIFO_PRIORITY" onclick="setStrategy(this)">LIFO + P</button>
  </div>
  <div class="topstats">
    <div class="tstat"><div class="tstat-v" id="ts-occ">0%</div><div class="tstat-l">Ocupación</div></div>
    <div class="tstat"><div class="tstat-v" id="ts-items" style="color:var(--acc2)">0/0</div><div class="tstat-l">Items</div></div>
    <div class="tstat"><div class="tstat-v" id="ts-peso" style="color:var(--acc3)">0 kg</div><div class="tstat-l">Peso</div></div>
  </div>
</header>

<div class="main">
<aside class="panel">


<div class="pblock" style="background: rgba(56, 189, 248, 0.03);">
  <h2>Logística y Transporte</h2>
  <div class="field" style="margin-bottom: 8px;">
    <label>VEHÍCULO / PLACAS</label>
    <input type="text" value="<?php echo $Vehiculo;?>" readonly style="color: var(--acc2);">
  </div>
  <div class="row3">
    <div class="field full"><label>OPERADOR</label><input type="text" value="<?php echo $Operador;?>" readonly></div>
  </div>
  <div class="field" style="margin-top: 8px;">
    <label>RUTA DESTINO</label>
    <div style="font-size: 11px; color: var(--t2); padding: 5px; background: var(--s2); border-radius: 4px; border: 1px solid var(--bd);">
      Total Paradas: <span id="info-paradas">0</span>
    </div>
  </div>

  <div class="pblock">
    <h2>Contenedor (cm)</h2>
    <div class="row3">
      <div class="field"><label>ALTO Y</label><input type="text" id="ch" readonly></div>
      <div class="field"><label>ANCHO X</label><input type="text" id="cw" readonly></div>
      <div class="field"><label>PROF Z</label><input type="text" id="cd"  readonly></div>
    </div>
  </div>

</div>  


  <div class="pblock">
    <h2>Zonas de entrega</h2>
    <div class="zone-legend">
      <div class="zone-tag door">FONDO</div>
      <div class="zone-tag mid">CENTRO</div>
      <div class="zone-tag back">PUERTA</div>
      <div class="zone-tag frag">Frágil</div>
    </div>
  </div>
  <div style="padding:8px 14px 5px;border-bottom:1px solid var(--bd);flex-shrink:0;">
    <h2 style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--t3);">
      Orden de carga
    </h2>
  </div>
  <div class="items-scroll" id="item-list"></div>
  <div class="controls">
    <div class="step-row">
      <span class="step-ctr">Paso <strong id="sc">0</strong> / <span id="st">0</span></span>
      <span class="step-name" id="sn">—</span>
    </div>
    <div class="prog-track"><div class="prog-fill" id="pf" style="width:0%"></div></div>
    <div class="occ-row"><span>Ocupación volumétrica</span><span id="op2">0%</span></div>
    <div class="occ-track"><div class="occ-fill" id="of" style="width:0%"></div></div>
<div class="btn-row">
  <button class="btn btn-ghost" id="bp" onclick="stepBack()" disabled>◀</button>
  <button class="btn btn-acc" id="bn" onclick="stepForward()" style="flex:1">Siguiente ▶</button>
</div>
<div class="btn-row">
  <button class="btn btn-ghost" onclick="runAll()" style="flex:1;font-size:11px;">▶▶ Todo</button>
  <button class="btn btn-danger" onclick="resetSim()">↺</button>
</div>

<button class="btn" id="btn-finalizar" disabled 
        style="width:100%; margin-top:8px; background: var(--s3); color: var(--t3); border: 1px solid var(--bd); transition: all 0.3s;">
  VERIFICANDO CARGA...
</button>
    <div id="nf-zone" style="display:none;"></div>
  </div>
</aside>

<div class="viewport" id="vp">
  <canvas id="c"></canvas>
  <div id="tip"></div>
  <div class="zone-overlay"><div class="zo-door"></div><div class="zo-mid"></div><div class="zo-back"></div></div>
  <div class="hint">🖱 Arrastrar: rotar · Rueda: zoom · Der: pan</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
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


// ═══════════════════════════════════════════════════════════════
//  DATOS
// ═══════════════════════════════════════════════════════════════
let strategy    = 'LIFO';
let container   = {h:<?php echo $vehicle['height']?>, w:<?php echo $vehicle['width']?>, d:<?php echo $vehicle['depth']?>};
let products    = <?php echo $js_obj; ?>;
let simResult   = null;
let curStep     = 0;

// ═══════════════════════════════════════════════════════════════
//  ALGORITMO BIN-PACKING 3D  — CORREGIDO
//
//  CORRECCIONES:
//  1. gravitySink(): baja cada candidato hasta el nivel real de
//     soporte (suelo o tope de caja debajo), eliminando el
//     "flotado" que ocurría cuando el candidato venía del
//     extremo superior de otra caja.
//  2. orientations(): respeta la propiedad `girar`. Si girar=false
//     solo se usa la orientación original (w,h,d) sin rotar.
//  3. Candidatos ampliados: se agregan las esquinas completas de
//     cada cara superior de cada caja ya colocada, dando más
//     puntos de apoyo y reduciendo huecos.
// ═══════════════════════════════════════════════════════════════

function collides(a, b) {
  // Separación estricta — usamos epsilon pequeño para evitar
  // falsos positivos por cajas perfectamente adyacentes
  const EPS = 0.001;
  return a.x + EPS < b.x + b.w && a.x + a.w - EPS > b.x
      && a.y + EPS < b.y + b.h && a.y + a.h - EPS > b.y
      && a.z + EPS < b.z + b.d && a.z + a.d - EPS > b.z;
}

function inBounds(b, C) {
  const EPS = 0.001;
  return b.x >= -EPS && b.y >= -EPS && b.z >= -EPS
      && b.x + b.w <= C.w + EPS
      && b.y + b.h <= C.h + EPS
      && b.z + b.d <= C.d + EPS;
}

// ─── CONSTANTES DE SOPORTE ───────────────────────────────────────────────────
// Un item puede apoyarse sobre otro solo si la intersección de sus huellas en
// XZ es >= MIN_SUPPORT_RATIO de la huella del item que se está colocando.
// Evita acomodos "de puntitas" donde un item grande descansa en 1 cm de otro.
const MIN_SUPPORT_RATIO = 0.30; // 30 % de la base debe estar soportada

/**
 * Calcula el área de intersección entre la huella XZ de `box` y la de `p`.
 */
function overlapArea(box, p) {
  const ix = Math.min(box.x + box.w, p.x + p.w) - Math.max(box.x, p.x);
  const iz = Math.min(box.z + box.d, p.z + p.d) - Math.max(box.z, p.z);
  return (ix > 0 && iz > 0) ? ix * iz : 0;
}

/**
 * Devuelve true si `box` tiene soporte suficiente para descansar en `floorY`.
 *
 * Reglas:
 *  - Si floorY === 0 (suelo del contenedor) siempre tiene soporte.
 *  - Si floorY > 0, la suma del área de intersección con TODAS las cajas cuya
 *    superficie superior toca floorY debe ser >= MIN_SUPPORT_RATIO * base(box).
 *  - Solo cuentan cajas que son apilables (apilable === true).
 */
function hasSufficientSupport(box, placed, floorY) {
  if (floorY < 0.001) return true; // descansa en el suelo → siempre ok

  const baseArea = box.w * box.d;
  if (baseArea < 0.001) return true;

  let supportedArea = 0;
  for (const p of placed) {
    if (!p.apilable) continue;                         // no apilable → no da soporte
    if (Math.abs((p.y + p.h) - floorY) > 0.001) continue; // no está al nivel correcto
    supportedArea += overlapArea(box, p);
  }
  // También suma el suelo si alguna parte de la caja lo toca
  if (floorY < 0.001) supportedArea = baseArea;

  return supportedArea / baseArea >= MIN_SUPPORT_RATIO;
}

/**
 * Calcula el nivel Y real al que debe bajar `box` por gravedad.
 *
 * Reglas:
 *  1. Solo considera cajas con apilable=true como posible piso.
 *  2. Baja al piso más alto que cumpla soporte suficiente (MIN_SUPPORT_RATIO).
 *  3. Si ninguna caja da soporte suficiente → cae al suelo (y=0).
 *
 * Esto corrige dos bugs anteriores:
 *  - Cajas flotando sobre huecos (no-apilable debajo, o solape mínimo).
 *  - Items apoyándose parcialmente (1 cm) sobre otro item.
 */
function gravitySink(box, placed) {
  // Recolectar todos los niveles candidatos: suelo + tope de cada apilable
  const levels = new Set([0]);
  for (const p of placed) {
    if (p.apilable) levels.add(p.y + p.h);
  }

  // Ordenar de mayor a menor: queremos el piso más alto posible
  const sorted = [...levels].sort((a, b) => b - a);

  for (const lvl of sorted) {
    if (lvl >= box.y + box.h - 0.001) continue; // el nivel está por encima del tope → skip
    const testBox = {...box, y: lvl};
    if (hasSufficientSupport(testBox, placed, lvl)) {
      return lvl;
    }
  }
  return 0; // cae al suelo si nada da soporte
}

/**
 * Genera las orientaciones permitidas para un item.
 * Si girar=false  → solo orientación original (w,h,d).
 * Si girar=true   → las 6 permutaciones únicas de ejes.
 */
function orientations(item) {
  const {w, h, d, girar} = item;
  if (!girar) return [{w, h, d}];
  const seen = new Set(), r = [];
  for (const [a, b, c] of [[w,h,d],[w,d,h],[h,w,d],[h,d,w],[d,w,h],[d,h,w]]) {
    const k = `${a}_${b}_${c}`;
    if (!seen.has(k)) { seen.add(k); r.push({w:a, h:b, d:c}); }
  }
  return r;
}

/**
 * Genera puntos candidatos de inserción.
 * Para cada caja ya colocada se generan 3 puntos en sus extremos
 * más las 4 esquinas de su cara superior — esto cubre casos donde
 * el BLF clásico dejaba huecos porque solo usaba extremos de arista.
 */
function candidates(placed) {
  const pts = [{x:0, y:0, z:0}];
  for (const p of placed) {
    // extremos de arista (clásicos)
    pts.push({x: p.x + p.w, y: p.y,       z: p.z      });
    pts.push({x: p.x,       y: p.y + p.h,  z: p.z      });
    pts.push({x: p.x,       y: p.y,        z: p.z + p.d});
    // esquinas de cara superior (nuevos — reducen huecos)
    pts.push({x: p.x,       y: p.y + p.h,  z: p.z      });
    pts.push({x: p.x + p.w, y: p.y + p.h,  z: p.z      });
    pts.push({x: p.x,       y: p.y + p.h,  z: p.z + p.d});
    pts.push({x: p.x + p.w, y: p.y + p.h,  z: p.z + p.d});
  }
  // Eliminar duplicados exactos
  const seen = new Set();
  return pts.filter(p => {
    const k = `${p.x}_${p.y}_${p.z}`;
    if (seen.has(k)) return false;
    seen.add(k); return true;
  });
}

// ═══════════════════════════════════════════════════════════════
//  SCORE DE DENSIDAD  (peso + volumen normalizado)
//  Items con score alto → se colocan primero → quedan abajo.
//  60 % peso, 40 % volumen (peso manda por física real).
// ═══════════════════════════════════════════════════════════════
function baseScore(item, maxPeso, maxVol) {
  return 0.6 * ((item.peso || 0) / maxPeso)
       + 0.4 * ((item.w * item.h * item.d) / maxVol);
}

function enrichWithScore(items) {
  const maxPeso = Math.max(...items.map(i => i.peso || 0), 1);
  const maxVol  = Math.max(...items.map(i => i.w * i.h * i.d), 1);
  return items.map(i => ({...i, _score: baseScore(i, maxPeso, maxVol)}));
}

// ═══════════════════════════════════════════════════════════════
//  CLAVE DE GRUPO DE ENTREGA
//  Agrupa por (prioridad + entrega) — la misma combinación forma
//  un "palet virtual" que queremos mantener junto en el contenedor.
// ═══════════════════════════════════════════════════════════════
function groupKey(item) {
  return `${item.prioridad}||${item.entrega || 'X'}`;
}

// ═══════════════════════════════════════════════════════════════
//  ORDEN DE GRUPOS  (de más lejano/menos urgente → más próximo/urgente)
//  Los grupos del fondo se procesan primero para que el BLF los
//  empuje hacia Z alto (fondo del vehículo).
// ═══════════════════════════════════════════════════════════════
function sortGroups(groupMap, strat) {
  const keys = [...groupMap.keys()];
  keys.sort((ka, kb) => {
    const a = groupMap.get(ka)[0];
    const b = groupMap.get(kb)[0];
    if (strat === 'LIFO' || strat === 'LIFO_PRIORITY') {
      const da = a.entrega||'9999', db = b.entrega||'9999';
      if (da !== db) return db.localeCompare(da); // fecha lejana primero
    }
    if (strat === 'PRIORITY' || strat === 'LIFO_PRIORITY') {
      if (a.prioridad !== b.prioridad) return b.prioridad - a.prioridad; // P5 antes que P1
    }
    return 0;
  });
  return keys;
}

// ═══════════════════════════════════════════════════════════════
//  COLOCAR UN ITEM  (BLF con gravedad + soporte + no-apilable)
//  Devuelve el item colocado con coordenadas, o null si no cabe.
//
//  zMin / zMax:  límite Z del slot asignado al grupo (palet virtual).
//                Si zMin=0 y zMax=C.d el item puede ir en cualquier Z.
// ═══════════════════════════════════════════════════════════════
function placeItem(item, placed, C, zMin, zMax) {
  const orients = orientations(item);
  let best    = null;
  let bestOri = null;

  for (const ori of orients) {
    const pts = candidates(placed);

    for (const pt of pts) {
      let box = {x: pt.x, y: pt.y, z: pt.z, w: ori.w, h: ori.h, d: ori.d};

      // ── Gravedad ────────────────────────────────────────────
      box.y = gravitySink(box, placed);

      // ── Bounds generales ────────────────────────────────────
      if (!inBounds(box, C)) continue;

      // ── Restricción de zona Z del grupo (palet virtual) ─────
      // El item debe quedar COMPLETAMENTE dentro del slot Z.
      if (box.z < zMin - 0.001 || box.z + box.d > zMax + 0.001) continue;

      // ── Soporte suficiente ───────────────────────────────────
      if (!hasSufficientSupport(box, placed, box.y)) continue;

      // ── Colisiones + no-apilable ────────────────────────────
      let blocked = false;
      for (const p of placed) {
        if (collides(box, p)) { blocked = true; break; }
        if (!p.apilable) {
          const touching = Math.abs(box.y - (p.y + p.h)) < 0.002;
          if (touching && overlapArea(box, p) > 0.001) { blocked = true; break; }
        }
      }
      if (blocked) continue;

      // ── BLF: Y más bajo → Z más bajo → X más bajo ───────────
      if (!best
        || box.y < best.y - 0.001
        || (Math.abs(box.y - best.y) < 0.001 && box.z < best.z - 0.001)
        || (Math.abs(box.y - best.y) < 0.001 && Math.abs(box.z - best.z) < 0.001 && box.x < best.x - 0.001)
      ) {
        best    = {...box};
        bestOri = ori;
      }
    }
  }

  if (!best) return null;

  const zRel = (best.z + best.d / 2) / C.d;
  return {
    ...best,
    nombre:    item.label,
    color:     item.color,
    peso:      item.peso,
    prioridad: item.prioridad,
    entrega:   item.entrega,
    fragil:    item.fragil,
    apilable:  item.apilable,
    girar:     item.girar,
    grupo:     groupKey(item),
    ori_w:     bestOri.w,
    ori_h:     bestOri.h,
    ori_d:     bestOri.d,
    zona:      zRel >= .66 ? 'door' : zRel >= .33 ? 'mid' : 'back',
    paso:      placed.length + 1,
  };
}

// ═══════════════════════════════════════════════════════════════
//  SIMULATE  — motor principal con agrupación por entrega (palet)
//
//  Estrategia de paletización:
//  1. Expandir items individuales y agrupar por (prioridad+entrega).
//  2. Ordenar grupos según estrategia logística (LIFO/Prioridad).
//  3. Estimar el ancho Z que cada grupo necesita para asignarle
//     un slot exclusivo en el eje Z del contenedor.
//  4. Dentro de cada slot, ordenar los items del grupo
//     pesados→livianos y fragiles al final, y ejecutar BLF
//     restringido a ese slot.
//  5. Si un item no cabe en el slot de su grupo, intentar colocarlo
//     en cualquier Z libre (fallback) para no perder mercancía.
// ═══════════════════════════════════════════════════════════════
function simulate() {
  const C = container;

  // ── 1. Expandir y enriquecer ────────────────────────────────
  let allItems = [];
  for (const p of products) {
    for (let i = 0; i < p.qty; i++) {
      allItems.push({
        ...p,
        label: p.qty > 1 ? `${p.nombre} #${i+1}` : p.nombre,
      });
    }
  }
  allItems = enrichWithScore(allItems);

  // ── 2. Agrupar por clave de entrega ─────────────────────────
  const groupMap = new Map();
  for (const item of allItems) {
    const k = groupKey(item);
    if (!groupMap.has(k)) groupMap.set(k, []);
    groupMap.get(k).push(item);
  }

  // ── 3. Ordenar grupos (los del fondo primero) ───────────────
  const sortedKeys = sortGroups(groupMap, strategy);

  // ── 4. Asignar slots Z proporcionales al volumen de cada grupo
  //       El slot Z es la "profundidad" reservada para ese palet.
  //       Usamos el máximo ancho de item (en Z) de cada grupo como
  //       mínimo, y distribuimos el resto proporcional al volumen total.
  const totalVol = allItems.reduce((s, i) => s + i.w * i.h * i.d, 0) || 1;

  const slots = []; // [{key, zStart, zEnd}]
  let zCursor = 0;

  for (const key of sortedKeys) {
    const grpItems = groupMap.get(key);
    const grpVol   = grpItems.reduce((s, i) => s + i.w * i.h * i.d, 0);
    // Profundidad proporcional al volumen del grupo; mínimo 1 cm
    let slotDepth = Math.max(1, (grpVol / totalVol) * C.d);
    // Asegurar que el último grupo tome el espacio restante
    if (key === sortedKeys[sortedKeys.length - 1]) {
      slotDepth = C.d - zCursor;
    }
    slots.push({key, zStart: zCursor, zEnd: Math.min(zCursor + slotDepth, C.d)});
    zCursor += slotDepth;
    if (zCursor >= C.d) break;
  }

  // ── 5. Colocar grupo por grupo ──────────────────────────────
  const placed   = [];
  const unplaced = [];

  for (const slot of slots) {
    const grpItems = groupMap.get(slot.key) || [];

    // Dentro del grupo: pesados+voluminosos primero, frágiles al final
    const norm = grpItems.filter(i => !i.fragil).sort((a, b) => b._score - a._score);
    const frag = grpItems.filter(i =>  i.fragil).sort((a, b) => b._score - a._score);
    const ordered = [...norm, ...frag];

    for (const item of ordered) {
      // Intento 1: colocar dentro del slot asignado al grupo
      let result = placeItem(item, placed, C, slot.zStart, slot.zEnd);

      // Intento 2 (fallback): si no cabe en el slot, intentar en TODO el contenedor
      // así no perdemos el item aunque se mezcle un poco con otro grupo
      if (!result) {
        result = placeItem(item, placed, C, 0, C.d);
      }

      if (result) {
        placed.push(result);
      } else {
        unplaced.push({...item});
      }
    }
  }

  const volC = C.w * C.h * C.d;
  const volU = placed.reduce((s, p) => s + p.w * p.h * p.d, 0);
  return {
    placed, unplaced, slots, volC, volU,
    pct:   (volC > 0 ? volU / volC * 100 : 0).toFixed(1),
    pesoT: placed.reduce((s, p) => s + p.peso, 0).toFixed(1),
  };
}

// ═══════════════════════════════════════════════════════════════
//  THREE.JS
// ═══════════════════════════════════════════════════════════════
const cvs = document.getElementById('c');
const vp  = document.getElementById('vp');
const renderer = new THREE.WebGLRenderer({canvas:cvs, antialias:true, alpha:true});
renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
renderer.shadowMap.enabled = true;
renderer.shadowMap.type    = THREE.PCFSoftShadowMap;
const scene  = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(42, 1, .1, 12000);

scene.add(new THREE.AmbientLight(0xffffff, .75));
const sun = new THREE.DirectionalLight(0xffffff, .6);
sun.position.set(400, 600, 300); sun.castShadow = true; scene.add(sun);
const fill = new THREE.DirectionalLight(0x93c5fd, .25);
fill.position.set(-300, 100, -200); scene.add(fill);

// Orbit manual
let drag=false, rDrag=false, pm={x:0,y:0};
let sph={t:Math.PI/4, p:Math.PI/3, r:900};
let pan=new THREE.Vector3(), tgt=new THREE.Vector3();

function camUpdate() {
  const x = sph.r*Math.sin(sph.p)*Math.sin(sph.t);
  const y = sph.r*Math.cos(sph.p);
  const z = sph.r*Math.sin(sph.p)*Math.cos(sph.t);
  camera.position.set(tgt.x+pan.x+x, tgt.y+pan.y+y, tgt.z+pan.z+z);
  camera.lookAt(tgt.x+pan.x, tgt.y+pan.y, tgt.z+pan.z);
}
cvs.addEventListener('mousedown', e=>{drag=true; rDrag=e.button===2; pm={x:e.clientX,y:e.clientY};});
cvs.addEventListener('contextmenu', e=>e.preventDefault());
window.addEventListener('mouseup', ()=>drag=false);
window.addEventListener('mousemove', e=>{
  if (!drag) return;
  const dx=e.clientX-pm.x, dy=e.clientY-pm.y;
  if (rDrag) {
    const r=new THREE.Vector3();
    r.crossVectors(camera.getWorldDirection(new THREE.Vector3()), new THREE.Vector3(0,1,0)).normalize();
    pan.addScaledVector(r, -dx*.5); pan.y += dy*.5;
  } else {
    sph.t -= dx*.005;
    sph.p  = Math.max(.05, Math.min(Math.PI-.05, sph.p+dy*.005));
  }
  pm={x:e.clientX, y:e.clientY}; camUpdate();
});
cvs.addEventListener('wheel', e=>{sph.r=Math.max(60,sph.r+e.deltaY*.9); camUpdate();}, {passive:true});

// Raycaster / tooltip
const rc=new THREE.Raycaster(), mv=new THREE.Vector2();
const tip=document.getElementById('tip');
let meshes=[];

cvs.addEventListener('mousemove', e=>{
  const rect=cvs.getBoundingClientRect();
  mv.x=((e.clientX-rect.left)/rect.width)*2-1;
  mv.y=-((e.clientY-rect.top)/rect.height)*2+1;
  rc.setFromCamera(mv, camera);
  const hits=rc.intersectObjects(meshes);
  if (hits.length) {
    const d=hits[0].object.userData;
    const pc={'1':'#ef4444','2':'#f97316','3':'#eab308','4':'#22c55e','5':'#64748b'}[d.prioridad]||'#fff';
    const zc=d.zona||'mid';
    tip.style.cssText=`display:block;left:${e.clientX-rect.left+16}px;top:${e.clientY-rect.top-8}px;`;
    tip.innerHTML=`<strong>${d.nombre}</strong>
      Paso: ${d.paso} &nbsp;<span class="tag ${zc}">${d.zona}</span><br>
      Pos: (${d.x.toFixed(1)}, ${d.y.toFixed(1)}, ${d.z.toFixed(1)}) cm<br>
      Dim: ${d.w}×${d.h}×${d.d} cm<br>
      ${d.ori_w!==undefined&&(d.ori_w!==d.w||d.ori_h!==d.h||d.ori_d!==d.d)
        ? `<span style="color:var(--acc)">↻ Rotado: ${d.ori_w}×${d.ori_h}×${d.ori_d}</span><br>`:''}
      Peso: ${d.peso} kg &nbsp;·&nbsp; Y-base: ${d.y.toFixed(1)} cm<br>
      <span style="color:${pc}">● P${d.prioridad}</span>
      ${d.entrega?`&nbsp;·&nbsp;📅 ${d.entrega}`:''}
      ${d.fragil?`<span class="tag frag">🔺 frágil</span>`:''}
      ${!d.apilable?`<span style="font-size:10px;color:#fb923c">⛔ no apilable</span>`:''}
      ${d.girar?`<span class="rot-badge">↻ giro</span>`:''}`;
  } else {
    tip.style.display='none';
  }
});

function resize() {
  const w=vp.clientWidth, h=vp.clientHeight;
  renderer.setSize(w,h,false); camera.aspect=w/h; camera.updateProjectionMatrix();
}
new ResizeObserver(resize).observe(vp); resize();

const boxGrp=new THREE.Group(); scene.add(boxGrp);
let cwire=null;
let zonePlanes=[], slotPlanes=[];

// Paleta de colores para slots de grupos de entrega
const SLOT_PALETTE=[0xdc2626,0x2563eb,0x059669,0xea580c,0x7c3aed,0xdb2777,0xca8a04,0x0891b2];

function buildZonePlanes(C) {
  zonePlanes.forEach(p=>scene.remove(p)); zonePlanes=[];
  // Franjas de fondo (LIFO door/mid/back)
  const defs=[
    {zStart:0,      zEnd:C.d*.33, col:0x34d399},
    {zStart:C.d*.33,zEnd:C.d*.66, col:0x38bdf8},
    {zStart:C.d*.66,zEnd:C.d,     col:0xfb923c},
  ];
  for (const def of defs) {
    const len=def.zEnd-def.zStart;
    const g=new THREE.BoxGeometry(C.w,.5,len);
    const m=new THREE.Mesh(g, new THREE.MeshBasicMaterial({color:def.col,transparent:true,opacity:.05,side:THREE.DoubleSide}));
    m.position.set(C.w/2,.3,def.zStart+len/2);
    scene.add(m); zonePlanes.push(m);
  }
}

/**
 * Dibuja planos divisorios verticales (XY) en los límites Z de cada slot
 * de grupo de entrega. Cada slot tiene su propio color — facilita ver
 * visualmente qué palet va en qué zona.
 */
function buildSlotDividers(C, slots) {
  slotPlanes.forEach(p=>scene.remove(p)); slotPlanes=[];
  if (!slots || slots.length <= 1) return;

  slots.forEach((slot, idx) => {
    const col = SLOT_PALETTE[idx % SLOT_PALETTE.length];
    const depth = slot.zEnd - slot.zStart;

    // Plano de suelo coloreado para el slot
    const gFloor = new THREE.PlaneGeometry(C.w, depth);
    const mFloor = new THREE.Mesh(gFloor, new THREE.MeshBasicMaterial({
      color: col, transparent:true, opacity:.07, side:THREE.DoubleSide
    }));
    mFloor.rotation.x = -Math.PI/2;
    mFloor.position.set(C.w/2, 0.6, slot.zStart + depth/2);
    scene.add(mFloor); slotPlanes.push(mFloor);

    // Pared divisoria vertical en zStart (salvo primer slot)
    if (idx > 0) {
      const gWall = new THREE.PlaneGeometry(C.w, C.h);
      const mWall = new THREE.Mesh(gWall, new THREE.MeshBasicMaterial({
        color: col, transparent:true, opacity:.13, side:THREE.DoubleSide
      }));
      mWall.position.set(C.w/2, C.h/2, slot.zStart);
      scene.add(mWall); slotPlanes.push(mWall);

      // Línea de borde del divisor
      const eg = new THREE.EdgesGeometry(gWall);
      const el = new THREE.LineSegments(eg, new THREE.LineBasicMaterial({
        color: col, transparent:true, opacity:.6
      }));
      el.position.copy(mWall.position);
      scene.add(el); slotPlanes.push(el);
    }
  });
}


function buildContainer(C) {
  if (cwire) scene.remove(cwire);
  const g=new THREE.BoxGeometry(C.w,C.h,C.d);
  cwire=new THREE.LineSegments(new THREE.EdgesGeometry(g),
    new THREE.LineBasicMaterial({color:0x2563eb,transparent:true,opacity:.25}));
  cwire.position.set(C.w/2,C.h/2,C.d/2);
  scene.add(cwire);
  if (window._fl) scene.remove(window._fl);
  window._fl=new THREE.Mesh(new THREE.PlaneGeometry(C.w,C.d),
    new THREE.MeshStandardMaterial({color:0xc7d2e8,transparent:true,opacity:.6,side:THREE.DoubleSide}));
  window._fl.rotation.x=-Math.PI/2;
  window._fl.position.set(C.w/2,.2,C.d/2);
  window._fl.receiveShadow=true;
  scene.add(window._fl);
  tgt.set(C.w/2,C.h/2,C.d/2);
  pan.set(0,0,0);
  sph.r=Math.max(C.w,C.h,C.d)*2.4;
  camUpdate();
  buildZonePlanes(C);
}


const zoneEmissive={
  door: new THREE.Color(0x059669),
  mid:  new THREE.Color(0x2563eb),
  back: new THREE.Color(0xea580c),
};

function addMesh(item, highlight=false) {
  // Usar dimensiones de la orientación final aplicada
  const W = item.ori_w ?? item.w;
  const H = item.ori_h ?? item.h;
  const D = item.ori_d ?? item.d;

  const g   = new THREE.BoxGeometry(W*.97, H*.97, D*.97);
  const col = new THREE.Color(item.color||'#38bdf8');
  const mat = new THREE.MeshStandardMaterial({
    color: col, transparent:true, opacity: highlight ? .95 : .78,
    roughness:.45, metalness:.1,
    emissive: highlight ? (zoneEmissive[item.zona]||new THREE.Color(0)) : new THREE.Color(0),
    emissiveIntensity: highlight ? .3 : 0,
  });
  const mesh=new THREE.Mesh(g, mat);
  mesh.position.set(item.x + W/2, item.y + H/2, item.z + D/2);
  mesh.castShadow=true; mesh.receiveShadow=true;
  mesh.userData={...item, w:W, h:H, d:D};

  const eg=new THREE.EdgesGeometry(g);
  mesh.add(new THREE.LineSegments(eg, new THREE.LineBasicMaterial({color:0x000000,transparent:true,opacity:.3})));
  if (item.fragil) {
    mesh.add(new THREE.LineSegments(eg, new THREE.LineBasicMaterial({color:0xf472b6,transparent:true,opacity:.6})));
  }
  // Borde turquesa para items rotados
  if (item.girar && (item.ori_w!==item.w || item.ori_h!==item.h || item.ori_d!==item.d)) {
    mesh.add(new THREE.LineSegments(eg, new THREE.LineBasicMaterial({color:0x38bdf8,transparent:true,opacity:.5})));
  }

  boxGrp.add(mesh); meshes.push(mesh);

  if (highlight) {
    mesh.scale.set(.01,.01,.01);
    const t0=performance.now();
    (function anim(){
      const t=Math.min((performance.now()-t0)/380,1);
      const s=1-Math.pow(1-t,3);
      mesh.scale.set(s,s,s);
      if (t<1) requestAnimationFrame(anim);
      else { mat.emissive.set(0); mat.emissiveIntensity=0; }
    })();
  }
  return mesh;
}

function clearMeshes(){boxGrp.clear(); meshes=[];}
(function loop(){requestAnimationFrame(loop); renderer.render(scene,camera);})();

// ═══════════════════════════════════════════════════════════════
//  UI
// ═══════════════════════════════════════════════════════════════
const prioColors={'1':'#ef4444','2':'#f97316','3':'#eab308','4':'#22c55e','5':'#64748b'};

function recalculate(){
  container.w=parseFloat(document.getElementById('cw').value)||250;
  container.h=parseFloat(document.getElementById('ch').value)||200;
  container.d=parseFloat(document.getElementById('cd').value)||400;
  simResult=simulate(); curStep=0;
  buildContainer(container); clearMeshes();
  // Dibujar separadores de palets (slots de grupo de entrega)
  buildSlotDividers(container, simResult.slots||[]);
  renderList(); updateStepUI(); updateHeader(); renderNoFit();
}

function stepForward(){
  if (!simResult||curStep>=simResult.placed.length) return;
  curStep++;
  clearMeshes();
  for (let i=0;i<curStep;i++) addMesh(simResult.placed[i], i===curStep-1);
  updateStepUI(); updateHeader();
}
function stepBack(){
  if (curStep<=0) return; curStep--;
  clearMeshes();
  for (let i=0;i<curStep;i++) addMesh(simResult.placed[i], false);
  updateStepUI(); updateHeader();
}
function resetSim(){curStep=0; clearMeshes(); updateStepUI(); updateHeader();}
function runAll(){
  if (!simResult) return; curStep=0; clearMeshes();
  let i=0;
  (function next(){
    if (i>=simResult.placed.length){updateHeader();return;}
    curStep=i+1; addMesh(simResult.placed[i],true);
    updateStepUI(); i++; setTimeout(next,110);
  })();
}
function jumpTo(step){
  curStep=step; clearMeshes();
  for (let i=0;i<curStep;i++) addMesh(simResult.placed[i], i===curStep-1);
  updateStepUI(); updateHeader();
}

function updateStepUI(){
  const total=simResult?.placed.length??0;
  document.getElementById('sc').textContent=curStep;
  document.getElementById('st').textContent=total;
  document.getElementById('bp').disabled=curStep<=0;
  document.getElementById('bn').disabled=curStep>=total;
  document.getElementById('pf').style.width=(total>0?curStep/total*100:0)+'%';
  const item=curStep>0?simResult.placed[curStep-1]:null;
  document.getElementById('sn').textContent=item?.nombre??'—';

  let vU=0;
  for (let i=0;i<curStep;i++){const p=simResult.placed[i]; vU+=p.w*p.h*p.d;}
  const vC=container.w*container.h*container.d;
  const occ=vC>0?(vU/vC*100).toFixed(1):0;
  document.getElementById('op2').textContent=occ+'%';
  const of=document.getElementById('of');
  of.style.width=occ+'%';
  of.style.background=occ>85?'#ef4444':occ>65?'#fb923c':'#34d399';

  // Highlight en lista lateral (usando data-step, no índice DOM)
  highlightListRow(curStep - 1);

// Lógica del botón Carga Completa
  const btnFinalizar = document.getElementById('btn-finalizar');
  if (curStep >= total && total > 0) {
    btnFinalizar.disabled = false;
    btnFinalizar.style.background = 'var(--acc2)'; // Verde esmeralda
    btnFinalizar.style.color = '#000';
    btnFinalizar.innerHTML = '✅ CARGA COMPLETA';
    btnFinalizar.onclick = () => carga_completa();
  } else {
    btnFinalizar.disabled = true;
    btnFinalizar.style.background = 'var(--s3)';
    btnFinalizar.style.color = 'var(--t3)';
    btnFinalizar.innerHTML = `FALTAN ${total - curStep} ITEMS`;
  }
  
  // Actualizar info de paradas en el nuevo panel
  const uniqueStops = new Set(simResult?.placed.map(p => p.prioridad));
  document.getElementById('info-paradas').textContent = uniqueStops.size;

}

function updateHeader(){
  const total=simResult?.placed.length??0;
  let vU=0, pesoU=0;
  for (let i=0;i<curStep;i++){const p=simResult.placed[i]; vU+=p.w*p.h*p.d; pesoU+=p.peso;}
  const vC=container.w*container.h*container.d;
  const occ=vC>0?(vU/vC*100).toFixed(1):0;
  document.getElementById('ts-occ').textContent=occ+'%';
  document.getElementById('ts-items').textContent=`${curStep}/${total}`;
  document.getElementById('ts-peso').textContent=pesoU.toFixed(1)+' kg';
}

function renderList(){
  const el=document.getElementById('item-list');
  el.innerHTML='';
  if (!simResult) return;

  // Mapa grupo → color de slot
  const groupColors={};
  if (simResult.slots) {
    simResult.slots.forEach((s,i)=>{
      groupColors[s.key]='#'+SLOT_PALETTE[i%SLOT_PALETTE.length].toString(16).padStart(6,'0');
    });
  }

  // Construir lista de item-rows con separadores de grupo
  let lastGrp=null;
  let rowIdx=0; // índice solo de .item-row para el highlight de activeStep

  for (let i=0;i<simResult.placed.length;i++){
    const p=simResult.placed[i];
    const pc=prioColors[p.prioridad]||'#fff';
    const zClass=p.zona||'mid';
    const rotated=(p.ori_w&&(p.ori_w!==p.w||p.ori_h!==p.h||p.ori_d!==p.d));
    const grpKey=p.grupo||groupKey(p);
    const grpCol=groupColors[grpKey]||p.color;

    // Separador de grupo
    if (grpKey!==lastGrp){
      lastGrp=grpKey;
      const slot=simResult.slots?.find(s=>s.key===grpKey);
      const slotInfo=slot?` Z ${slot.zStart.toFixed(0)}–${slot.zEnd.toFixed(0)} cm`:'';
      const sep=document.createElement('div');
      sep.style.cssText=`padding:5px 14px 4px;font-size:9px;font-weight:700;
        text-transform:uppercase;letter-spacing:.8px;color:${grpCol};
        background:rgba(0,0,0,.3);border-left:3px solid ${grpCol};
        border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:7px;`;
      sep.innerHTML=`<span style="width:8px;height:8px;border-radius:2px;
          background:${grpCol};flex-shrink:0;display:inline-block;"></span>
        🚚 Entrega P${p.prioridad} · ${p.entrega||'—'}${slotInfo}`;
      el.appendChild(sep);
    }

    const row=document.createElement('div');
    row.className='item-row';
    row.dataset.step=i;   // índice de placed[]
    row.onclick=()=>jumpTo(i+1);
    row.innerHTML=`
      <div class="cdot" style="background:${p.color}"></div>
      <div class="iinfo">
        <div class="iname">${p.nombre}${p.fragil?' 🔺':''}${!p.apilable?' ⛔':''}</div>
        <div class="imeta">
          <span style="color:${pc}">P${p.prioridad}</span>
          <span class="zone-pip ${zClass}">${p.zona.toUpperCase()}</span>
          <span>${(p.ori_w??p.w)}×${(p.ori_h??p.h)}×${(p.ori_d??p.d)}</span>
          ${rotated?`<span class="rot-badge">↻</span>`:''}
        </div>
      </div>
      <div class="step-n">${p.paso}</div>`;
    el.appendChild(row);
  }
}

// Actualizar highlight de filas usando data-step en lugar de índice DOM
function highlightListRow(stepIdx){
  document.querySelectorAll('.item-row').forEach(r=>{
    const s=parseInt(r.dataset.step);
    r.classList.toggle('active', s===stepIdx);
    r.classList.toggle('done',   s<stepIdx);
    r.querySelector('.step-n').classList.toggle('active', s===stepIdx);
    if (s===stepIdx) r.scrollIntoView({block:'nearest',behavior:'smooth'});
  });
}

function renderNoFit(){
  const z=document.getElementById('nf-zone');
  if (!simResult||!simResult.unplaced.length){z.style.display='none';return;}
  z.style.display='block';
  z.innerHTML=`<div class="no-fit">
    <div class="no-fit-title">⚠ No caben (${simResult.unplaced.length})</div>
    ${simResult.unplaced.map(u=>`
      <div class="no-fit-item">
        <span style="color:#ef4444">●</span>
        ${u.label||u.nombre} · ${u.w}×${u.h}×${u.d}cm
      </div>`).join('')}
  </div>`;
}

function setStrategy(btn){
  document.querySelectorAll('.stab').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  strategy=btn.dataset.s;
  const labels={
    'BLF_VOLUME':'Volumen descendente','LIFO':'LIFO por fecha de entrega',
    'PRIORITY':'Prioridad de entrega','LIFO_PRIORITY':'LIFO + Prioridad activo'
  };
  document.getElementById('strat-label').textContent=labels[strategy]||strategy;
  recalculate();
}

// Init
cw=document.getElementById('cw');
ch=document.getElementById('ch');
cd=document.getElementById('cd');
cw.value=container.w;
ch.value=container.h;
cd.value=container.d;
recalculate();

function carga_completa(){

        let formData = new FormData();
        
        // Datos básicos
        formData.append('id_route', <?php echo $daily_route['id_route']?>);
        formData.append('currentStage', 'CARGA');
        formData.append('next_stage', 'INSTALACION');

        $.ajax({
            url: API_BASE_URL + 'api/process_stage_change_em/',
            method: 'POST',
            data: formData,
            headers: { 'Authorization': 'Bearer ' + TOKEN },            
            processData: false, // Vital para FormData
            contentType: false, // Vital para FormData
            success: function(response) {
              history.back();
            },
            error: function() {

            }
        });        



}
</script>
</body>
</html>