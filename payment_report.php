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

include_once 'head.php';
?>

<style>
/* Clase personalizada para filas clickeables */
.fila-lead {
    cursor: pointer;
    transition: background-color 0.2s;
}

/* Resaltado al pasar el mouse (opcional si no usas table-hover de Bootstrap) */
.fila-lead:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
}

</style>
</head>
<body>
<?php

    include_once 'nav.php';

?>



<div class="container my-5">
    <div class="card shadow">
        <div class="card-header text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-black">Reporte de Operaciones: Lavado, Limpieza y Reparación</h4>
        </div>
    </div>  

    <div class="container mt-4">
        <div class="row g-3 mb-4">
            <div class="col-md-3"><input type="date" id="fInicio" class="form-control"></div>
            <div class="col-md-3"><input type="date" id="fFin" class="form-control"></div>
            <div class="col-md-3"><input type="text" id="filtroUsuario" class="form-control" placeholder="Nombre Usuario"></div>
            <div class="col-md-3"><button id="btnBuscar" class="btn btn-primary w-100">Consultar</button></div>
        </div>
    </div>


<div class="container pb-5">
    <div class="table-container shadow-sm border">
        <div class="table-responsive">

        <table class="table table-hover align-middle m-0">
            <thead class="table-light">
                <tr>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Plataforma</th>
                    <th class="text-end">Monto</th>
                </tr>
            </thead>
            <tbody id="tablaPagos">
                </tbody>
        </table>
        </div>
    </div>
</div>

    
</div>

<script>

    const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
    const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
    const TOKEN = localStorage.getItem('apiToken'); 


$('#btnBuscar').click(function() {

    let formData = new FormData();
    formData.append('fecha_inicio',  $('#fInicio').val());
    formData.append('fecha_fin', $('#fFin').val());
    formData.append('usuario', $('#filtroUsuario').val());
    $.ajax({
        url: API_BASE_URL + 'api/payment_report/',
        method: 'POST',
        data: formData,
        headers: { 'Authorization': 'Bearer ' + TOKEN },            
        processData: false, // Vital para FormData
        contentType: false, // Vital para FormData
        success: function(result) {
            data = result.data;

// Suponiendo que 'data' es el array que viene de tu API
let reportData = {};
let granTotal = 0;

data.forEach(item => {
    if (!reportData[item.Usuario]) {
        reportData[item.Usuario] = { 
            movimientos: [], 
            subtotal: 0,
            plataformas: { 'Cash': 0, 'Transfer': 0, 'Otra': 0 } 
        };
    }
    
    // Clasificar plataforma (ajusta los nombres según tu BD)
    let p = item.Platform;
    let monto = parseFloat(item.Amount) || 0;
    
    if (reportData[item.Usuario].plataformas.hasOwnProperty(p)) {
        reportData[item.Usuario].plataformas[p] += monto;
    } else {
        reportData[item.Usuario].plataformas['Otra'] += monto;
    }
    
    reportData[item.Usuario].movimientos.push(item);
    reportData[item.Usuario].subtotal += monto;
    granTotal += monto;
});

// Construir la tabla
let html = '';
for (let usuario in reportData) {
    let grupo = reportData[usuario];
    
    // 1. Filas de detalle
    grupo.movimientos.forEach(mov => {
        html += `<tr class="fila-lead" onclick="window.location.href='lead.php?IdLead=${mov.Id}'">
            <td>${mov.Folio}</td>
            <td>${mov.Cliente}</td>
            <td>${mov.Platform}</td>
            <td class="text-end">$${parseFloat(mov.Amount).toFixed(2)}</td>
        </tr>`;
    });
    
    // 2. Fila de resumen detallado por usuario
    html += `<tr class="table-info fw-bold">
        <td colspan="4">
            Resumen ${usuario}: 
            <span class="ms-3">Cash: $${grupo.plataformas['Cash'].toFixed(2)}</span> | 
            <span class="ms-3">Transfer: $${grupo.plataformas['Transfer'].toFixed(2)}</span> | 
            <span class="ms-3">Otra: $${grupo.plataformas['Otra'].toFixed(2)}</span> | 
            <span class="ms-3 text-primary">Total Usuario: $${grupo.subtotal.toFixed(2)}</span>
        </td>
    </tr>`;
}

// 3. Fila de Gran Total
html += `<tr class="table-dark text-white fw-bold">
    <td colspan="3" class="text-end">GRAN TOTAL ACUMULADO:</td>
    <td class="text-end">$${granTotal.toFixed(2)}</td>
</tr>`;

$('#tablaPagos').html(html);            


        },
        error: function() {
        }
    });   
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