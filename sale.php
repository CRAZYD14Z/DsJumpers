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
$query = "select Traduccion FROM  programas_traduccion where Programa = 'sales' AND Idioma = ? ORDER BY Id";            
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

    $query = "select * FROM account";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($account['Pais'] == 'USA' ){
        $account['Pais'] = 'US';
    }else{
        $account['Pais'] = 'MX';
    }

    $query = "select * FROM paypal_account";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $paypal_account = $stmt->fetch(PDO::FETCH_ASSOC);    

?>
<link rel="stylesheet" href="css/lead.css" />

    <style>
        /* ── Integración de Paleta y Tipografía Minimalista de lead.css ── */
        input[type="datetime-local"] {
            font-size: 0.8rem;
            color: #444;
            border: 1px solid #dee2e6;
        }
        input[type="datetime-local"]:focus {
            border-color: #0d6efd;
            box-shadow: none;
        }

        .input-group-text {
            font-size: 0.8rem;
            color: #6c757d;
            border-color: #dee2e6;
        }

        body {
            background-color: #f8f9fa;
            color: #444;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: .92rem;
            min-height: 100vh;
            padding-top: 70px; /* Espacio para el nav superior fijo */
        }


        /* ── Contenedores y Paneles Estilo lead.css ── */
        .main-wrapper {
            max-width: 1300px;
            margin: 0 auto;
            padding: 1.2rem 1rem 3rem;
        }

        .card-panel {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .card-header-custom {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: .75rem 1.1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 700;
            font-size: .95rem;
            color: #333;
        }
        .card-body-custom { padding: 1.1rem; }

        /* ── Controles de Formulario Unificados ── */
        .form-label {
            font-weight: 600;
            font-size: .82rem;
            color: #6c757d;
            margin-bottom: .3rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: .88rem;
            color: #444;
            transition: border-color .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: none;
        }

        /* ── Listado de Productos Estilo custom-row ── */
        .product-row {
            border-bottom: 1px solid #eee;
            padding: 12px 10px;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
        }
        .product-row:hover {
            background-color: #fcfcfc;
        }
        .product-row .prod-name {
            font-weight: 600;
            font-size: .88rem;
            color: #222;
        }
        .product-row .prod-badge {
            font-size: .7rem;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            padding: .4rem 0;
            font-size: .88rem;
        }
        .summary-line.total {
            border-top: 1px solid #dee2e6;
            margin-top: .4rem;
            padding-top: .6rem;
            font-weight: 700;
            font-size: 1.2rem;
            color: #0d6efd;
        }

        /* ── Botones Minimalistas ── */
        .btn-minimal {
            background: transparent;
            border: none;
            padding: 5px 15px;
            margin: 0 2px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #555;
            border-radius: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-minimal:hover {
            background-color: rgba(0, 0, 0, 0.04);
            color: #000;
        }

        .btn-primary-custom {
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: .5rem 1.2rem;
            font-weight: 500;
            font-size: .88rem;
            transition: background .15s;
        }
        .btn-primary-custom:hover { background: #0b5ed7; color:#fff; }

        .btn-outline-custom {
            border: 1px solid #dee2e6;
            background: transparent;
            color: #555;
            border-radius: 6px;
            padding: .45rem 1.1rem;
            font-weight: 500;
            font-size: .88rem;
            transition: all .15s;
        }
        .btn-outline-custom:hover { border-color: #0d6efd; color: #0d6efd; background-color: rgba(13,110,253,0.02); }

        /* Barras de Mensajes / Alertas Estilo lead.css */
        #barra-mensajes {
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            z-index: 1050;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-top: 1px solid rgba(0,0,0,0.05);
            display: none;
        }
        .msg-minimal-exito   { background: rgba(232, 245, 233, 0.95); color: #2e7d32; padding: 10px 20px; }
        .msg-minimal-error   { background: rgba(253, 235, 235, 0.95); color: #c62828; padding: 10px 20px; }
        .msg-minimal-alerta  { background: rgba(255, 248, 225, 0.95); color: #f9a825; padding: 10px 20px; }
        .msg-minimal-normal  { background: rgba(255, 255, 255, 0.95); color: #333; padding: 10px 20px; }

        .btn-cerrar-mini {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            opacity: 0.5;
            font-weight: bold;
        }
        .btn-cerrar-mini:hover { opacity: 1; }

        .lookup-bar {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: .8rem 1.1rem;
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-bottom: 1rem;
        }

        .required-star { color: #dc3545; }
        .tracking-tight { letter-spacing: -0.02em; }
        .select2-results__option { padding: 8px 12px !important; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear { margin-right: 0.5rem; }

        /* Ajustes responsivos móviles para filas de productos */
        @media (max-width: 767.98px) {
            .product-row {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
                border: 1px solid #eee;
                border-radius: 8px;
                margin-bottom: 15px;
            }
            .product-row > div {
                width: 100%;
                margin-bottom: 8px;
            }
        }
    </style>
</head>
<body>
<?php
    include_once 'nav.php';
?>


<!-- ══════════════════════════════════════════════
     BARRA DE MENSAJES (ESTILO LEAD.CSS)
══════════════════════════════════════════════ -->
<div id="barra-mensajes">
    <div id="barra-contenido" class="d-flex justify-content-between align-items-center w-100">
        <span id="barra-texto"></span>
        <span class="btn-cerrar-mini" onclick="$('#barra-mensajes').slideUp(200)">Cerrar</span>
    </div>
</div>

<!-- ══════════════════════════════════════════════
     MAIN WRAPPER
══════════════════════════════════════════════ -->
<div class="main-wrapper">

    <!-- Buscador superior de Venta -->
    <div class="lookup-bar">
        <i class="fa fa-search text-muted"></i>
        <input type="number" id="lookup-sale-id" class="form-control form-control-sm" placeholder="Editar venta existente — ingresa el # de venta" style="max-width:280px">
        <button class="btn-primary-custom py-1" onclick="loadSale()">
            <i class="fa fa-file-pen me-1"></i>Cargar
        </button>
        <span id="lookup-status" class="text-muted small"></span>

    <div class="d-flex gap-2 align-items-center">
        <span id="sale-mode-badge" class="badge bg-light text-secondary border fw-medium" style="font-size:.8rem; display:none;"></span>
        <button class="btn-minimal" onclick="resetAll()">
            <i class="fa fa-rotate-left me-1"></i>Nueva venta
        </button>
    </div>        

    </div>

    <div class="row g-3">
        <div class="col-12">

            <!-- Fila superior: Cliente y Dirección -->
            <div class="row g-3 mb-3">
                <!-- PASO 1: CLIENTE -->
                <div class="col-md-6">
                    <div id="section-customer" class="card-panel h-100">
                        <div class="card-header-custom">
                            <i class="fa fa-user-circle text-muted"></i> Paso 1 — Cliente
                            <span class="ms-auto">
                                <button class="btn-minimal" onclick="openNewCustomerModal()">
                                    <i class="fa fa-plus me-1"></i>Nuevo cliente
                                </button>
                            </span>
                        </div>
                        <div class="card-body-custom">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Buscar cliente <span class="required-star">*</span></label>
                                    <select id="customer-select" class="form-select" style="width:100%">
                                        <option value="">— Escribe para buscar —</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2" id="customer-info-box" style="display:none">
                                    <div class="p-2 rounded border bg-light small" id="customer-info-text"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PASO 2: DIRECCIÓN -->
                <div class="col-md-6">
                    <div id="section-address" class="card-panel h-100" style="opacity:.5;pointer-events:none">
                        <div class="card-header-custom">
                            <i class="fa fa-location-dot text-muted"></i> Paso 2 — Dirección de Envío
                            <span class="ms-auto">
                                <button class="btn-minimal" onclick="openNewAddressModal()">
                                    <i class="fa fa-plus me-1"></i>Nueva dirección
                                </button>
                            </span>
                        </div>
                        <div class="card-body-custom">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Seleccionar dirección <span class="required-star">*</span></label>
                                    <select id="address-select" class="form-select" style="width:100%">
                                        <option value="">— Selecciona primero un cliente —</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2" id="address-info-box" style="display:none">
                                    <div class="p-2 rounded border bg-light small" id="address-info-text"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 3: PRODUCTOS -->
            <div id="section-products" class="card-panel mb-3" style="opacity:.5;pointer-events:none">
                <div class="card-header-custom">
                    <i class="fa fa-box-open text-muted"></i> Paso 3 — Productos
                </div>
                <div class="card-body-custom">
                    <div class="row g-2 mb-3">
                        <div class="col-md-10">
                            <label class="form-label">Buscar producto</label>
                            <select id="product-search" class="form-select" style="width:100%">
                                <option value="">— Escribe para buscar —</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn-primary-custom w-100" onclick="addProductToCart()">
                                <i class="fa fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </div>

                    <div id="cart-items" class="border rounded overflow-hidden">
                        <div class="p-4 text-center text-muted small" id="cart-empty">
                            <i class="fa fa-cart-shopping d-block mb-2 fs-4 text-black-50"></i>
                            Sin productos — agrega uno arriba
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 4: PAGO Y NOTAS -->
            <div id="section-payment" class="card-panel mb-3" style="opacity:.5;pointer-events:none">
                <div class="card-header-custom">
                    <i class="fa fa-credit-card text-muted"></i> Paso 4 — Pago, Notas y Registro de Venta
                </div>
                <div class="card-body-custom">
                    
                    <div class="row g-3 mb-3 bg-fluid p-3 rounded border bg-light">
                        <div class="col-md-6">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.7rem;"><i class="fa fa-user me-1"></i> Cliente Confirmado</div>
                            <div id="step4-customer-summary" class="small fw-semibold text-dark">—</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.7rem;"><i class="fa fa-map-marker-alt me-1"></i> Dirección Confirmada</div>
                            <div id="step4-address-summary" class="small fw-semibold text-dark">—</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del pagador <span class="required-star">*</span></label>
                            <input type="text" id="payer-name" class="form-control" placeholder="Nombre">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido del pagador <span class="required-star">*</span></label>
                            <input type="text" id="payer-lastname" class="form-control" placeholder="Apellido">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email del pagador <span class="required-star">*</span></label>
                            <input type="email" id="payer-email" class="form-control" placeholder="email@ejemplo.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Método de pago <span class="required-star">*</span></label>
                            <select id="payment-method" class="form-select">
                                <option value="">— Selecciona —</option>
                                <option value="cash">Efectivo</option>
                                <option value="card">Tarjeta crédito / débito</option>
                                <option value="transfer">Transferencia bancaria</option>
                                <option value="oxxo">OXXO Pay</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Token / Referencia de pago</label>
                            <input type="text" id="gateway-token" class="form-control" placeholder="Referencia o token">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado del pago</label>
                            <select id="payment-status" class="form-select">
                                <option value="pending">Pendiente</option>
                                <option value="completed">Completado</option>
                                <option value="failed">Fallido</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas de la venta</label>
                            <textarea id="cart-notes" class="form-control" rows="3" placeholder="Instrucciones especiales, observaciones..."></textarea>
                        </div>
                    </div>

                    <hr class="my-4" style="border-top:1px solid #dee2e6;">

                    <div class="row justify-content-end mb-3">
                        <div class="col-md-4">
                            <div class="summary-line">
                                <span class="text-muted">Subtotal</span>
                                <span id="sum-subtotal" class="fw-medium">$0.00</span>
                            </div>
                            <div class="summary-line">
                                <span class="text-muted">Descuentos</span>
                                <span id="sum-discount" class="text-success fw-medium">-$0.00</span>
                            </div>
                            <div class="summary-line total">
                                <span>Total a registrar</span>
                                <span id="sum-total">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-end g-2">
                        <div class="col-md-4">
                            <button class="btn-primary-custom w-100 py-2" id="btn-save-sale" onclick="saveSale()">
                                <i class="fa fa-floppy-disk me-1"></i>
                                <span id="btn-save-label">Registrar venta</span>
                            </button>
                        </div>
                    </div>

                    <div id="sale-saved-info" class="mt-3" style="display:none">
                        <div class="alert alert-success py-2 mb-0 small text-center border-0" style="background-color:#e8f5e9; color:#2e7d32;">
                            <i class="fa fa-check-circle me-1"></i>
                            Venta guardada exitosamente — <strong id="saved-sale-id"></strong>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════
     MODAL: NUEVO CLIENTE
══════════════════════════════════════════════ -->
<div class="modal fade" id="modalNewCustomer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold text-dark"><i class="fa fa-user-plus me-2 text-muted"></i>Nuevo Cliente</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre <span class="required-star">*</span></label>
                        <input type="text" id="nc-firstname" class="form-control" placeholder="Nombre">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido <span class="required-star">*</span></label>
                        <input type="text" id="nc-lastname" class="form-control" placeholder="Apellido">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="required-star">*</span></label>
                        <input type="email" id="nc-email" class="form-control" placeholder="email@ejemplo.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono <span class="required-star">*</span></label>
                        <input type="tel" id="nc-phone" class="form-control" placeholder="10 dígitos">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado (clave) <span class="required-star">*</span></label>
                        <input type="text" id="nc-state" class="form-control" placeholder="JAL, CDMX...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña temporal</label>
                        <input type="password" id="nc-password" class="form-control" placeholder="••••••••">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn-primary-custom py-1" onclick="saveNewCustomer()">
                    <i class="fa fa-save me-1"></i>Guardar cliente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL: NUEVA DIRECCIÓN
══════════════════════════════════════════════ -->
<div class="modal fade" id="modalNewAddress" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold text-dark"><i class="fa fa-location-dot me-2 text-muted"></i>Nueva Dirección</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Alias <span class="required-star">*</span></label>
                        <input type="text" id="na-alias" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Ciudad <span class="required-star">*</span></label>
                        <input type="text" id="na-city" class="form-control" placeholder="">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Calle y número <span class="required-star">*</span></label>
                        <input type="text" id="na-street" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Colonia <span class="required-star">*</span></label>
                        <input type="text" id="na-colonia" class="form-control" placeholder="">
                    </div>                    
                    <div class="col-md-4">
                        <label class="form-label">C.P. <span class="required-star">*</span></label>
                        <input type="text" id="na-zip" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pais <span class="required-star">*</span></label>
                        <select id="na-country" name="pais" class="form-control">
                            <option value="">Seleccione un país...</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Estado <span class="required-star">*</span></label>
                        <select id="na-state" name="estado" class="form-control">
                            <option value="">Seleccione un estado...</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Referencias</label>
                        <input type="text" id="na-references" class="form-control" placeholder="">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="na-default">
                            <label class="form-check-label text-muted" for="na-default" style="font-size:0.85rem;">Marcar como dirección principal</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn-primary-custom py-1" onclick="saveNewAddress()">
                    <i class="fa fa-save me-1"></i>Guardar dirección
                </button>
            </div>
        </div>
    </div>
</div>


<script>

const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
const TOKEN = localStorage.getItem('apiToken'); 
const ID_CLIENTE = '<?= $_SESSION['id_cliente']; ?>'; 
const CFPUBLICURL = '<?= CFPUBLICURL ?>';     


const API_BASE = 'api.php';

const state = {
    mode: 'new',
    saleId: null,
    customer: null,
    address: null,
    cart: [],
    subtotal: 0,
    discountTotal: 0,
    total: 0,
};

$(function () {
    initCustomerSelect();
    initAddressSelect();
    initProductSelect();
    updateSaleBadge();
    
});

function getcountry() {
    $.getJSON(API_BASE, { action: 'get_country' }, function (res) {
        // Asumiendo que tu función resp(true, $datos) devuelve el array en la propiedad 'data' o directo en 'res'
        // Si tu API envuelve los datos en un objeto, ajusta 'res.data' o usa 'res' según corresponda
        const paises = res.data || res; 
        
        const $selectCountry = $('#na-country');
        $selectCountry.empty().append('<option value="">Seleccione un país...</option>');

        $.each(paises, function(index, item) {
            // Usamos item.Codigo como VALUE y item.Pais como TEXTO
            $selectCountry.append($('<option>', {
                value: item.Codigo,
                text: item.Pais
            }));
        });
    });
}

function getstates() {
    const countryVal = $('#na-country').val();
    const $selectState = $('#na-state');

    // Si no hay país seleccionado, limpiamos el select de estados y salimos
    if (!countryVal) {
        $selectState.empty().append('<option value="">Seleccione un estado...</option>');
        return;
    }

    $.getJSON(API_BASE, { action: 'get_states', ctry: countryVal }, function (res) {
        const estados = res.data || res;

        $selectState.empty().append('<option value="">Seleccione un estado...</option>');

        $.each(estados, function(index, item) {
            // Usamos item.Id como VALUE y item.Estado como TEXTO
            $selectState.append($('<option>', {
                value: item.Id,
                text: item.Estado
            }));
        });
    });
}

/* ────────────────────────────────────────────
   SELECT2 — Clientes
──────────────────────────────────────────── */
function initCustomerSelect() {
    $('#customer-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Escribe nombre o email...',
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: API_BASE,
            dataType: 'json',
            delay: 300,
            data: params => ({ action: 'search_customers', q: params.term }),
            processResults: data => ({
                results: (data.data || []).map(c => ({
                    id: c.id,
                    text: `${c.firstname} ${c.lastname} — ${c.email}`,
                    raw: c
                }))
            }),
        }
    }).on('select2:select', function (e) {
        const c = e.params.data.raw;
        state.customer = c;
        showCustomerInfo(c);
        loadAddresses(c.id);
        enableSection('section-address');
        updateStep4Summary();
    }).on('select2:clear', function () {
        state.customer = null;
        state.address = null;
        $('#address-select').empty().append('<option value="">— Selecciona primero un cliente —</option>');
        $('#customer-info-box').hide();
        $('#address-info-box').hide();
        disableSection('section-address');
        disableSection('section-products');
        disableSection('section-payment');
        updateStep4Summary();
    });
}

function showCustomerInfo(c) {
    $('#customer-info-text').html(`
        <strong>${c.firstname} ${c.lastname}</strong><br>
        <i class="fa fa-envelope fa-xs text-muted me-1"></i>${c.email}<br>
        <i class="fa fa-phone fa-xs text-muted me-1"></i>${c.phone || '—'}
    `);
    $('#customer-info-box').show();
}

/* ────────────────────────────────────────────
   SELECT2 — Direcciones
──────────────────────────────────────────── */
function initAddressSelect() {
    $('#address-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Selecciona una dirección',
        allowClear: true,
    }).on('select2:select', function (e) {
        const a = e.params.data.raw;
        state.address = a;
        showAddressInfo(a);
        enableSection('section-products');
        enableSection('section-payment');
        updateStep4Summary();
    }).on('select2:clear', function () {
        state.address = null;
        $('#address-info-box').hide();
        disableSection('section-products');
        disableSection('section-payment');
        updateStep4Summary();
    });
}

function loadAddresses(customerId) {
    return new Promise((resolve) => {
        $.getJSON(API_BASE, { action: 'get_addresses', customer_id: customerId }, function (res) {
            const $sel = $('#address-select');
            $sel.empty().append('<option value="">— Selecciona —</option>');
            (res.data || []).forEach(a => {
                const opt = new Option(
                    `${a.Alias} — ${a.Street}, ${a.City}${a.is_default == 1 ? ' ★' : ''}`,
                    a.Id, false, a.Is_default == 1
                );
                opt.dataset = a;
                $(opt).data('raw', a);
                $sel.append(opt);
            });
            $sel.trigger('change');

            const def = (res.data || []).find(a => a.Is_default == 1);
            if (def) {
                $sel.val(def.Id).trigger('change');
                state.address = def;
                showAddressInfo(def);
                enableSection('section-products');
                enableSection('section-payment');
                updateStep4Summary();
            }
            resolve();
        });
    });
}

function showAddressInfo(a) {
    $('#address-info-text').html(`
        <strong>${a.Alias}</strong><br>
        ${a.Street}, ${a.Colonia}<br>
        ${a.City}, ${a.State} ${a.Zip}
    `);
    $('#address-info-box').show();
}

/* ────────────────────────────────────────────
   SELECT2 — Productos
──────────────────────────────────────────── */
function initProductSelect() {
    $('#product-search').select2({
        theme: 'bootstrap-5',
        placeholder: 'Escribe nombre de producto...',
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: API_BASE,
            dataType: 'json',
            delay: 300,
            data: params => ({ action: 'search_products', q: params.term }),
            processResults: data => ({
                results: (data.data || []).map(p => ({
                    id: p.Id,
                    text: `${p.Name} — $${parseFloat(p.SalePrice).toFixed(2)}`,
                    raw: p
                }))
            }),
        }
    });
}

/* ────────────────────────────────────────────
   CART — Acciones
──────────────────────────────────────────── */
async function addProductToCart() {
    const sel = $('#product-search').select2('data')[0];
    if (!sel || !sel.raw) { showBarraMessage('Selecciona un producto', 'alerta'); return; }

    const p = sel.raw;

    if (state.cart.find(i => i.id == p.Id)) {
        showBarraMessage('Este producto ya está en el carrito', 'normal');
        return;
    }

    let stock = null;
    if (p.OnlyRequest != '1') {
        const stockRes = await $.getJSON(API_BASE, { action: 'get_stock', product_id: p.Id });
        stock = stockRes.data ? stockRes.data.total : 0;
        if (stock < 1) {
            showBarraMessage(`Sin existencia para "${p.Name}"`, 'error');
            return;
        }
    }

    const finalPrice = parseFloat(p.SalePrice) - parseFloat(p.Discount || 0);

    state.cart.push({
        id: p.Id,
        name: p.Name,
        salePrice: parseFloat(p.SalePrice),
        discount: parseFloat(p.Discount || 0),
        price: finalPrice,
        qty: 1,
        onlyRequest: p.OnlyRequest == '1',
        stock: stock,
        isSpecial: false,
    });

    renderCart();
    updateTotals();
    $('#product-search').val(null).trigger('change');
}

function renderCart() {
    const $container = $('#cart-items');
    $container.empty();

    if (state.cart.length === 0) {
        $container.html(`
            <div class="p-4 text-center text-muted small" id="cart-empty">
                <i class="fa fa-cart-shopping d-block mb-2 fs-4 text-black-50"></i>
                Sin productos — agrega uno arriba
            </div>`);
        return;
    }

    state.cart.forEach((item, idx) => {
        const stockBadge = item.onlyRequest
            ? `<span class="badge bg-warning-subtle text-warning border prod-badge">Bajo pedido</span>`
            : item.stock > 10
                ? `<span class="badge bg-success-subtle text-success border prod-badge"><i class="fa fa-check me-1"></i>${item.stock} disp.</span>`
                : `<span class="badge bg-warning-subtle text-warning border prod-badge"><i class="fa fa-triangle-exclamation me-1"></i>${item.stock} disp.</span>`;

        const maxQty = item.onlyRequest ? 9999 : item.stock;
        const lineTotal = (item.price * item.qty).toFixed(2);
        const hasDiscount = item.discount > 0;

        const row = `
        <div class="product-row" id="cart-row-${idx}">
            <div style="flex: 2;">
                <div class="prod-name">${item.name}</div>
                <div class="mt-1 d-flex gap-2 flex-wrap align-items-center">
                    ${stockBadge}
                    ${hasDiscount ? `<span class="badge bg-success-subtle text-success border prod-badge">-$${item.discount.toFixed(2)}</span>` : ''}
                    <div class="form-check form-switch ms-1 mb-0 small">
                        <input class="form-check-input" type="checkbox" id="special-${idx}" ${item.isSpecial ? 'checked' : ''} onchange="toggleSpecial(${idx}, this.checked)">
                        <label class="form-check-label text-muted" for="special-${idx}" style="font-size:0.75rem;">Prod. especial</label>
                    </div>
                </div>
            </div>
            <div style="flex: 1;" class="text-md-center">
                <div class="text-muted" style="font-size:.75rem; text-decoration:${hasDiscount?'line-through':''};">${hasDiscount?'$'+item.salePrice.toFixed(2):''}</div>
                <strong class="text-dark">$${item.price.toFixed(2)}</strong>
            </div>
            <div style="flex: 1;" class="text-md-center">
                <input type="number" min="1" max="${maxQty}" value="${item.qty}" class="form-control form-control-sm text-center d-inline-block" style="width:70px;" onchange="updateQty(${idx}, this.value)">
            </div>
            <div style="flex: 1;" class="text-md-center"><strong class="text-dark">$${lineTotal}</strong></div>
            <div style="flex: 0 0 auto;" class="text-end">
                <button class="btn btn-sm btn-light border text-danger" onclick="removeItem(${idx})"><i class="fa fa-trash-can"></i></button>
            </div>
        </div>`;
        $container.append(row);
    });
}

function updateQty(idx, val) {
    const item = state.cart[idx];
    let qty = parseInt(val) || 1;
    if (!item.onlyRequest && qty > item.stock) {
        qty = item.stock;
        showBarraMessage(`Cantidad máxima disponible: ${item.stock}`, 'alerta');
    }
    if (qty < 1) qty = 1;
    state.cart[idx].qty = qty;
    renderCart();
    updateTotals();
}

function removeItem(idx) {
    state.cart.splice(idx, 1);
    renderCart();
    updateTotals();
}

function toggleSpecial(idx, val) {
    state.cart[idx].isSpecial = val;
}

function updateTotals() {
    let subtotal = 0, discountTotal = 0;
    state.cart.forEach(item => {
        subtotal      += item.salePrice * item.qty;
        discountTotal += item.discount  * item.qty;
    });
    const total = subtotal - discountTotal;
    state.subtotal = subtotal;
    state.discountTotal = discountTotal;
    state.total = total;

    $('#sum-subtotal').text(`$${subtotal.toFixed(2)}`);
    $('#sum-discount').text(`-$${discountTotal.toFixed(2)}`);
    $('#sum-total').text(`$${total.toFixed(2)}`);
}

/* ────────────────────────────────────────────
   RÉPLICA DE INFORMACIÓN (PASO 4)
──────────────────────────────────────────── */
function updateStep4Summary() {
    if (state.customer) {
        $('#step4-customer-summary').html(`${state.customer.firstname} ${state.customer.lastname} (${state.customer.email})`);
    } else {
        $('#step4-customer-summary').text('— No se ha seleccionado cliente —');
    }

    if (state.address) {
        $('#step4-address-summary').html(`[${state.address.alias}] ${state.address.street}, ${state.address.city}, ${state.address.state}`);
    } else {
        $('#step4-address-summary').text('— No se ha seleccionado dirección —');
    }
}

/* ────────────────────────────────────────────
   GUARDAR VENTA
──────────────────────────────────────────── */
async function saveSale() {
    if (!state.customer) { showBarraMessage('Selecciona un cliente', 'error'); return; }
    if (!state.address)  { showBarraMessage('Selecciona una dirección de envío', 'error'); return; }
    if (state.cart.length === 0) { showBarraMessage('Agrega al menos un producto', 'error'); return; }

    const payerName   = $('#payer-name').val().trim();
    const payerLast   = $('#payer-lastname').val().trim();
    const payerEmail  = $('#payer-email').val().trim();
    const payMethod   = $('#payment-method').val();

    if (!payerName || !payerLast || !payerEmail || !payMethod) {
        showBarraMessage('Completa los datos del pagador y método de pago', 'error');
        return;
    }

    const payload = {
        action: state.mode === 'edit' ? 'update_sale' : 'create_sale',
        sale_id: state.saleId,
        customer_id: state.customer.id,
        address_id: state.address.id,
        total_amount: state.total.toFixed(2),
        cart_notes: $('#cart-notes').val(),
        payer_name: payerName,
        payer_lastname: payerLast,
        payer_email: payerEmail,
        payment_method: payMethod,
        gateway_token: $('#gateway-token').val(),
        payment_status: $('#payment-status').val(),
        device_fingerprint: navigator.userAgent,
        cart_json: JSON.stringify(state.cart),
        items: state.cart.map(i => ({
            product_id: i.id,
            product_name_snapshot: i.name,
            price: i.price,
            quantity: i.qty,
            is_special_production: i.isSpecial ? 1 : 0,
        })),
    };

    $('#btn-save-sale').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');

    try {
        const res = await $.ajax({
            url: API_BASE,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
        });

        if (res.success) {
            state.saleId = res.data.sale_id;
            state.mode = 'edit';
            showBarraMessage(`Venta ${state.mode === 'edit' ? 'actualizada' : 'registrada'} correctamente — #${res.data.sale_id}`, 'exito');
            $('#saved-sale-id').text(`#${res.data.sale_id}`);
            $('#sale-saved-info').show();
            $('#btn-save-label').text('Guardar cambios');
            updateSaleBadge();
        } else {
            showBarraMessage(res.message || 'Error al guardar', 'error');
        }
    } catch (e) {
        showBarraMessage('Error de conexión con la API', 'error');
    } finally {
        $('#btn-save-sale').prop('disabled', false).html(`<i class="fa fa-floppy-disk me-1"></i><span id="btn-save-label">${state.mode === 'edit' ? 'Guardar cambios' : 'Registrar venta'}</span>`);
    }
}

/* ────────────────────────────────────────────
   CARGAR VENTA (Edición)
──────────────────────────────────────────── */
async function loadSale() {
    const id = parseInt($('#lookup-sale-id').val());
    if (!id) { showBarraMessage('Ingresa un número de venta válido', 'alerta'); return; }

    $('#lookup-status').html('<span class="spinner-border spinner-border-sm"></span>');

    try {
        const res = await $.getJSON(API_BASE, { action: 'get_sale', sale_id: id });
        if (!res.success) { showBarraMessage(res.message || 'Venta no encontrada', 'error'); $('#lookup-status').html(''); return; }

        const s = res.data;
        state.mode   = 'edit';
        state.saleId = s.id;

        $('#payer-name').val(s.payer_name);
        $('#payer-lastname').val(s.payer_lastname);
        $('#payer-email').val(s.payer_email);
        $('#payment-method').val(s.payment_method);
        $('#gateway-token').val(s.gateway_token);
        $('#payment-status').val(s.payment_status);
        $('#cart-notes').val(s.cart_notes);

        state.customer = s.customer;
        const optC = new Option(`${s.customer.firstname} ${s.customer.lastname} — ${s.customer.email}`, s.customer.id, true, true);
        $('#customer-select').append(optC).trigger('change');
        showCustomerInfo(s.customer);

        await loadAddresses(s.customer.id);
        $('#address-select').val(s.address_id).trigger('change');

        state.cart = (s.items || []).map(it => ({
            id: it.product_id,
            name: it.product_name_snapshot,
            salePrice: parseFloat(it.price),
            discount: 0,
            price: parseFloat(it.price),
            qty: parseInt(it.quantity),
            onlyRequest: false,
            stock: null,
            isSpecial: it.is_special_production == 1,
        }));
        renderCart();
        updateTotals();

        enableSection('section-address');
        enableSection('section-products');
        enableSection('section-payment');
        updateStep4Summary();
        updateSaleBadge();
        $('#btn-save-label').text('Guardar cambios');
        $('#lookup-status').html('<span class="text-success small"><i class="fa fa-check me-1"></i>Cargado</span>');

    } catch (e) {
        showBarraMessage('Error al cargar la venta', 'error');
    }
}

/* ────────────────────────────────────────────
   MODALES Y HELPERS
──────────────────────────────────────────── */
function openNewCustomerModal() {
    ['nc-firstname','nc-lastname','nc-email','nc-phone','nc-state','nc-password'].forEach(id => $(`#${id}`).val(''));
    new bootstrap.Modal('#modalNewCustomer').show();
}

async function saveNewCustomer() {
    const data = {
        action: 'create_customer',
        firstname: $('#nc-firstname').val().trim(),
        lastname:  $('#nc-lastname').val().trim(),
        email:     $('#nc-email').val().trim(),
        phone:     $('#nc-phone').val().trim(),
        state:     $('#nc-state').val().trim(),
        password:  $('#nc-password').val(),
    };
    if (!data.firstname || !data.lastname || !data.email || !data.phone) {
        showBarraMessage('Completa los campos obligatorios', 'alerta'); return;
    }
    try {
        const res = await $.ajax({ url: API_BASE, method: 'POST', contentType:'application/json', data: JSON.stringify(data), dataType:'json' });
        if (res.success) {
            showBarraMessage('Cliente creado correctamente', 'exito');
            bootstrap.Modal.getInstance('#modalNewCustomer').hide();
            state.customer = res.data;
            const opt = new Option(`${res.data.firstname} ${res.data.lastname} — ${res.data.email}`, res.data.id, true, true);
            $('#customer-select').append(opt).trigger('change');
            showCustomerInfo(res.data);
            loadAddresses(res.data.id);
            enableSection('section-address');
        }
    } catch(e) { showBarraMessage('Error de conexión', 'error'); }
}

function openNewAddressModal() {
    if (!state.customer) { showBarraMessage('Selecciona primero un cliente', 'alerta'); return; }
    ['na-alias','na-country','na-state','na-city','na-colonia','na-street','na-zip','na-references'].forEach(id => $(`#${id}`).val('') );
    $('#na-default').prop('checked', false);
    new bootstrap.Modal('#modalNewAddress').show();
}

async function saveNewAddress() {
    const data = {
        action: 'create_address',
        customer_id: state.customer.id,
        alias:       $('#na-alias').val().trim(),
        country:     $('#na-country').val().trim(),
        state:       $('#na-state').val().trim(),
        city:        $('#na-city').val().trim(),
        street:      $('#na-street').val().trim(),
        colonia:     $('#na-colonia').val().trim(),
        zip:         $('#na-zip').val().trim(),
        references:  $('#na-references').val().trim(),
        is_default:  $('#na-default').is(':checked') ? 1 : 0,
    };
    if (!data.alias || !data.state || !data.city || !data.street || !data.colonia || !data.zip) {
        showBarraMessage('Completa los campos obligatorios', 'alerta'); return;
    }
    try {
        const res = await $.ajax({ url: API_BASE, method:'POST', contentType:'application/json', data: JSON.stringify(data), dataType:'json' });
        if (res.success) {
            showBarraMessage('Dirección guardada', 'exito');
            bootstrap.Modal.getInstance('#modalNewAddress').hide();
            await loadAddresses(state.customer.id);
            $('#address-select').val(res.data.id).trigger('change');
        }
    } catch(e) { showBarraMessage('Error de conexión', 'error'); }
}

function enableSection(id)  { $(`#${id}`).css({ opacity:'1', 'pointer-events':'all' }); }
function disableSection(id) { $(`#${id}`).css({ opacity:'.5', 'pointer-events':'none' }); }

function updateSaleBadge() {
    const badge = $('#sale-mode-badge');
    if (state.mode === 'edit' && state.saleId) {
        badge.text(`✏️ Editando venta #${state.saleId}`).show();
    } else {
        badge.text('').hide();
    }
}

function resetAll() {
    if (!confirm('¿Iniciar una nueva venta? Se perderán los cambios no guardados.')) return;
    state.mode = 'new'; state.saleId = null; state.customer = null; state.address = null; state.cart = [];
    $('#customer-select').val(null).trigger('change');
    $('#address-select').empty().append('<option value="">— Selecciona primero un cliente —</option>');
    $('#customer-info-box, #address-info-box, #sale-saved-info').hide();
    $('#payer-name, #payer-lastname, #payer-email, #gateway-token, #cart-notes').val('');
    $('#payment-method, #payment-status').val('');
    $('#lookup-sale-id, #lookup-status').val('');
    $('#btn-save-label').text('Registrar venta');
    ['section-address','section-products','section-payment'].forEach(disableSection);
    renderCart();
    updateTotals();
    updateStep4Summary();
    updateSaleBadge();
}

/* Interfaz Unificada del Manejo de Notificaciones mediante el barra-mensajes de lead.css */
function showBarraMessage(msg, type='normal') {
    const classes = ['msg-minimal-normal', 'msg-minimal-exito', 'msg-minimal-error', 'msg-minimal-alerta'];
    const $barra = $('#barra-mensajes');
    const $contenido = $('#barra-contenido');

    $contenido.removeClass(classes.join(' '));

    if (type === 'exito') $contenido.addClass('msg-minimal-exito');
    else if (type === 'error') $contenido.addClass('msg-minimal-error');
    else if (type === 'alerta') $contenido.addClass('msg-minimal-alerta');
    else $contenido.addClass('msg-minimal-normal');

    $('#barra-texto').text(msg);
    $barra.slideDown(250);

    // Auto ocultado a los 4 segundos
    setTimeout(() => { $barra.slideUp(200); }, 4000);
}


document.addEventListener("DOMContentLoaded", function() {
    <?php
    if (isset($_GET['IdSale'])){
        ?>
            $('#lookup-sale-id').val(<?= $_GET['IdSale'] ?>);
            loadSale();
        <?php
    }
    ?>

});


$(document).ready(function() {
    // 1. Cargar los países al iniciar la página
    getcountry();

    // 2. Escuchar cuando cambie el país para cargar sus estados correspondientes
    $('#na-country').on('change', function() {
        getstates();
    });
});

</script>
</body>
</html>