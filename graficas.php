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
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f6f9;
        }
        .dashboard-header {
            max-width: 1200px;
            margin: 0 auto 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .filtros {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 13px;
        }
        .form-group input, button {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }

        /* CONTENEDOR PRINCIPAL: Cuadrícula de 2x2 */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2 columnas iguales (cada una 50% del ancho) */
            gap: 20px; /* Espacio entre las gráficas */
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Tarjeta individual para cada gráfica (Ocupará 1/4 en pantallas grandes) */
        .chart-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .chart-card h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            color: #333;
            text-align: center;
        }

        /* El contenedor interno de Chart.js para controlar la altura */
        .chart-container {
            position: relative;
            height: 280px; /* Altura fija controlada para que no se deforme */
            width: 100%;
        }

        /* RESPONSIVO: En pantallas medianas/chicas (Tablets y Celulares) se vuelven de 1 sola columna */
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

</head>
<body class="bg-light">

<?php
    include_once 'nav.php';
?>
<br>
<br>


<div class="dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-md-center pb-3 mb-4 border-bottom">
    <h2 class="h3 mb-3 mb-md-0 text-dark">Dashboard del Sistema</h2>
    
    <!-- Contenedor de filtros con Flexbox responsivo -->
    <div class="filtros d-flex flex-column flex-sm-row gap-3 align-items-sm-end">
        
        <!-- Tipo de Reporte -->
        <div class="form-group">
            <label for="tipo_reporte" class="form-label small fw-semibold text-secondary mb-1">Tipo de Reporte:</label>
            <select id="tipo_reporte" class="form-select form-select-sm" style="min-width: 180px;">
                <option value="rentas">Reporte de Rentas</option>
                <option value="ventas">Reporte de Ventas</option>
                
            </select>
        </div>

        <!-- Fecha Inicio -->
        <div class="form-group">
            <label for="fecha_inicio" class="form-label small fw-semibold text-secondary mb-1">Fecha Inicio:</label>
            <input type="date" id="fecha_inicio" class="form-control form-control-sm" value="2026-01-01">
        </div>
        
        <!-- Fecha Fin -->
        <div class="form-group">
            <label for="fecha_fin" class="form-label small fw-semibold text-secondary mb-1">Fecha Fin:</label>
            <input type="date" id="fecha_fin" class="form-control form-control-sm" value="2026-12-31">
        </div>
        
        <!-- Botón de Filtrado -->
        <button onclick="actualizarDashboard()" class="btn btn-primary btn-sm px-3" type="button">
            <i class="bi bi-filter me-1"></i> Filtrar Periodo
        </button>
    </div>
</div>
<div class="dashboard-grid">

    <div class="chart-card">
        <h3>Ventas por Categoría</h3>
        <div class="chart-container">
            <canvas id="graficaCategorias"></canvas>
        </div>
    </div>
<style>

/* Asegura que la tabla no rompa el diseño y sea responsiva */
.table-responsive {
    width: 100%;
    overflow-x: auto; /* Scroll horizontal solo si la pantalla es muy chica */
    max-height: 280px; /* Para que mida lo mismo que tus contenedores de gráficas */
    overflow-y: auto;
}

.tabla-reporte {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: right;
}

.tabla-reporte th, .tabla-reporte td {
    padding: 8px 10px;
    border-bottom: 1px solid #eee;
}

.tabla-reporte th {
    background-color: #f8f9fa;
    position: sticky; /* Deja el encabezado fijo al hacer scroll vertical */
    top: 0;
    color: #333;
}

.tabla-reporte td:first-child, .tabla-reporte th:first-child {
    text-align: left; /* El nombre del mes se alinea a la izquierda */
    font-weight: bold;
}
/* Estilos para la fila de totales que va al principio */
.tabla-reporte .fila-totales td {
    background-color: #e9ecef; /* Fondo gris claro */
    font-weight: bold;
    color: #1d2124;
    border-bottom: 2px solid #ced4da;
    position: sticky;
    top: 33px; /* Se posiciona justo debajo del th del encabezado (ajusta según el padding de tu th) */
    z-index: 1;
}

/* Un leve cambio para que visualmente se note la separación */
.tabla-reporte tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
<div class="chart-card">
    <h3>Histórico de Ventas de los Últimos 3 Años</h3>
    <div class="table-responsive">
<table class="tabla-reporte" id="tablaUltimosAnios">
    <thead>
        <tr id="cabeceraTabla">
            <th>Mes</th>
            </tr>
    </thead>
    <tbody>
        <tr><td colspan="4" style="text-align:center;">Cargando datos...</td></tr>
    </tbody>
</table>
    </div>
</div>

<div class="chart-card">
    <h3>Rendimiento por Producto</h3>
    <div class="table-responsive">
        <table class="tabla-reporte" id="tablaProductos">
            <thead>
                <tr>
                    <th style="text-align: left;">Producto</th>
                    <th>Qty</th>
                    <th>% Qty</th>
                    <th>Avg</th>
                    <th>Sales</th>
                    <th>% Sales</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="6" style="text-align:center;">Cargando productos...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="chart-card">
    <h3>Comparativa de Ventas Mensuales (3 Años)</h3>
    <div class="chart-container">
        <canvas id="graficaVentasColumnas"></canvas>
    </div>
</div>

<div class="chart-card">
    <h3>Cobros: Pagado vs Pendiente</h3>
    <div class="chart-container">
        <canvas id="graficaPagosCuentas"></canvas>
    </div>
</div>

</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
const TOKEN = localStorage.getItem('apiToken'); 

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

<script>
// Objeto para almacenar todas las gráficas instaladas y poder destruirlas/actualizarlas
let instanciasCharts = {};

// Función principal que dispara la actualización de todo el dashboard
function actualizarDashboard() {
    const inicio = document.getElementById('fecha_inicio').value;
    const fin = document.getElementById('fecha_fin').value;
    const tipo = document.getElementById('tipo_reporte').value;
    cargarGraficaCategorias(inicio, fin,tipo);
    cargarTablaHistorica(inicio, fin,tipo);
    cargarTablaProductos(inicio, fin,tipo);    
    cargarGraficaColumnas(inicio, fin,tipo);
    cargarGraficaPagos(inicio, fin,tipo);
}

// 1. FUNCIÓN PARA LA PRIMERA GRÁFICA (Dona)
async function cargarGraficaCategorias(inicio, fin,tipo) {
    const respuesta = await fetch(`data.php?inicio=${inicio}&fin=${fin}&tipo=${tipo}&id=${1}`);
    const datos = await respuesta.json();

    if (datos.error) return;

    const ctx = document.getElementById('graficaCategorias').getContext('2d');

    // Destruir si ya existía antes de redibujar
    if (instanciasCharts['categorias']) {
        instanciasCharts['categorias'].destroy();
    }

    instanciasCharts['categorias'] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: datos.labels,
            datasets: [{
                label: 'Total Ventas ($)',
                data: datos.totales,
                backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }, /* Cambiado a bottom para que quepa mejor en 1/4 de pantalla */
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let index = context.dataIndex;
                            return ` $${context.raw.toLocaleString()} (${datos.unidades[index]} unds)`;
                        }
                    }
                }
            }
        }
    });
}

// Cargar todo al iniciar la página por primera vez
document.addEventListener("DOMContentLoaded", actualizarDashboard);


async function cargarTablaHistorica(inicio, fin,tipo) {
    //const respuesta = await fetch('data_tabla.php');
    const respuesta = await fetch(`data.php?inicio=${inicio}&fin=${fin}&tipo=${tipo}&id=${2}`);
    const respuestaJson = await respuesta.json();

    if (respuestaJson.error) return;

    // Separamos las definiciones de años y los renglones del reporte
    const anios = respuestaJson.anios;
    const datos = respuestaJson.datos;

    // 1. Renderizar dinámicamente los encabezados con los años correctos
    const cabecera = document.getElementById('cabeceraTabla');
    cabecera.innerHTML = `
        <th>Mes</th>
        <th>${anios.menos2}</th>
        <th>${anios.menos1}</th>
        <th>${anios.actual}</th>
    `;

    const tbody = document.querySelector('#tablaUltimosAnios tbody');
    tbody.innerHTML = ''; 

    // Variables para acumular los totales
    let sumaMenos2 = 0;
    let sumaMenos1 = 0;
    let sumaActual = 0;

    // Calcular las sumas usando las llaves dinámicas que vienen del backend
    datos.forEach(fila => {
        sumaMenos2 += fila[anios.menos2];
        sumaMenos1 += fila[anios.menos1];
        sumaActual += fila[anios.actual];
    });

    const formatearMoneda = (valor) => valor.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });

    // 2. Insertar fila de TOTALES al principio
    const trTotal = document.createElement('tr');
    trTotal.className = 'fila-totales'; 
    trTotal.innerHTML = `
        <td>TOTAL</td>
        <td>${formatearMoneda(sumaMenos2)}</td>
        <td>${formatearMoneda(sumaMenos1)}</td>
        <td>${formatearMoneda(sumaActual)}</td>
    `;
    tbody.appendChild(trTotal);

    // 3. Insertar los 12 meses debajo
    datos.forEach(fila => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${fila.mes}</td>
            <td>${formatearMoneda(fila[anios.menos2])}</td>
            <td>${formatearMoneda(fila[anios.menos1])}</td>
            <td>${formatearMoneda(fila[anios.actual])}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Añade esta línea dentro de tu función actualizarDashboard(inicio, fin):
// cargarTablaProductos(inicio, fin);

async function cargarTablaProductos(inicio, fin,tipo) {
    //const respuesta = await fetch(`data_productos.php?inicio=${inicio}&fin=${fin}`);
    const respuesta = await fetch(`data.php?inicio=${inicio}&fin=${fin}&tipo=${tipo}&id=${3}`);
    const datos = await respuesta.json();

    if (datos.error) return;

    const tbody = document.querySelector('#tablaProductos tbody');
    tbody.innerHTML = ''; // Limpiar renglones

    // Acumuladores para la fila superior de totales
    let totalQty = 0;
    let totalSales = 0;

    datos.forEach(row => {
        totalQty += parseInt(row.Qty);
        totalSales += parseFloat(row.Sales);
    });

    // Calcular el Ticket Promedio General (Ventas Totales / Piezas Totales)
    let avgGeneral = totalQty > 0 ? (totalSales / totalQty) : 0;

    const formataMoneda = (val) => val.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
    const formataPorcentaje = (val) => `${parseFloat(val).toFixed(1)}%`;

    // 1. Inserción de la fila TOTAL al principio
    const trTotal = document.createElement('tr');
    trTotal.className = 'fila-totales';
    trTotal.innerHTML = `
        <td style="text-align: left;">TOTALES</td>
        <td>${totalQty.toLocaleString()}</td>
        <td>100.0%</td>
        <td>${formataMoneda(avgGeneral)}</td>
        <td>${formataMoneda(totalSales)}</td>
        <td>100.0%</td>
    `;
    tbody.appendChild(trTotal);

    // 2. Inserción de cada fila de producto
    datos.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align: left; font-weight: normal;">${row.producto}</td>
            <td>${parseInt(row.Qty).toLocaleString()}</td>
            <td style="color: #666; font-size:11px;">${formataPorcentaje(row.PorcentajeQty)}</td>
            <td>${formataMoneda(parseFloat(row.AvgPrice))}</td>
            <td style="font-weight: bold;">${formataMoneda(parseFloat(row.Sales))}</td>
            <td style="color: #666; font-size:11px; font-weight: bold;">${formataPorcentaje(row.PorcentajeSales)}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Recuerda llamar a esta función dentro de tu DOMContentLoaded o función inicial:
// cargarGraficaColumnas();

let chartColumnas; // Variable global para controlar la instancia de esta gráfica

async function cargarGraficaColumnas(inicio, fin,tipo) {
    //const respuesta = await fetch('data_tabla.php');
    const respuesta = await fetch(`data.php?inicio=${inicio}&fin=${fin}&tipo=${tipo}&id=${2}`);
    const json = await respuesta.json();

    if (json.error) {
        console.error(json.error);
        return;
    }

    const anios = json.anios;
    const datos = json.datos;

    // 1. Mapear los datos para Chart.js
    const labelsMeses = datos.map(fila => fila.mes);
    const ventasAnioAntepasado = datos.map(fila => fila[anios.menos2]);
    const ventasAnioPasado = datos.map(fila => fila[anios.menos1]);
    const ventasAnioActual = datos.map(fila => fila[anios.actual]);

    const ctx = document.getElementById('graficaVentasColumnas').getContext('2d');

    // Destruir la gráfica si ya existía para evitar duplicados al recargar
    if (chartColumnas) {
        chartColumnas.destroy();
    }

    // 2. Crear la gráfica de barras agrupadas (columnas)
    chartColumnas = new Chart(ctx, {
        type: 'bar', // Tipo barra/columna
        data: {
            labels: labelsMeses, // Eje X: Enero, Febrero, etc.
            datasets: [
                {
                    label: `Año ${anios.menos2}`,
                    data: ventasAnioAntepasado,
                    backgroundColor: '#5ec306', // Gris claro
                    borderRadius: 4 // Bordes ligeramente redondeados
                },
                {
                    label: `Año ${anios.menos1}`,
                    data: ventasAnioPasado,
                    backgroundColor: '#3b82f6', // Azul intermedio
                    borderRadius: 4
                },
                {
                    label: `Año ${anios.actual}`,
                    data: ventasAnioActual,
                    backgroundColor: '#ff8104', // Azul oscuro (Destaca el año actual)
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Obliga a respetar el tamaño del contenedor (1/4 de pantalla)
            plugins: {
                legend: {
                    position: 'bottom', // Leyendas abajo para dar más espacio a las barras
                },
                tooltip: {
                    callbacks: {
                        // Formatear los números del tooltip como moneda en pesos mexicanos
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Formatear los números del eje Y para que no se vean amontonados
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-MX');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false // Quita las líneas verticales de fondo para un diseño más limpio
                    }
                }
            }
        }
    });
}

// Recuerda mandarla a llamar en tu inicializador: 
// cargarGraficaPagos();

let chartPagos;

async function cargarGraficaPagos(inicio, fin,tipo) {
    // Puedes pasarle el año dinámicamente desde un select si lo deseas, aquí por defecto va al script
    const anioActual = new Date().getFullYear(); // 2026
    //const respuesta = await fetch(`data_pagos.php?anio=${anioActual}`);
    const respuesta = await fetch(`data.php?anio=${anioActual}&tipo=${tipo}&id=${4}`);
    const datos = await respuesta.json();

    if (datos.error) return;

    const ctx = document.getElementById('graficaPagosCuentas').getContext('2d');

    if (chartPagos) {
        chartPagos.destroy();
    }

    chartPagos = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: datos.labels,
            datasets: [
                {
                    label: 'Pagado',
                    data: datos.pagado,
                    backgroundColor: '#10b981', // Verde éxito
                    borderRadius: 4
                },
                {
                    label: 'Pendiente por Cobrar',
                    data: datos.pendiente,
                    backgroundColor: '#ef4444', // Rojo advertencia
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let valor = context.raw || 0;
                            return ` ${context.dataset.label}: ${valor.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })}`;
                        }
                    }
                }
            },
            scales: {
                x: { 
                    stacked: true, // Apila las barras en el eje X
                    grid: { display: false } 
                },
                y: { 
                    stacked: true, // Apila las barras en el eje Y
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-MX');
                        }
                    }
                }
            }
        }
    });
}

// Modifica tu listener inicial para que también pinte la tabla
document.addEventListener("DOMContentLoaded", () => {
    //actualizarDashboard();
});


</script>

</body>
</html>