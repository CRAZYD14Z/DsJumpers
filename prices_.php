<?php
    ob_start();
    session_start(); 
    // Incluye la clase de conexión a la BD
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    
    $Idioma = 'es';
    $_SESSION['Idioma'] = $Idioma;
    
    $Idioma = 'es';
    $query = "select Traduccion FROM  programas_traduccion where Programa = 'prices' AND Idioma = ? ORDER BY Id";            
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
        echo $Traducciones[$Id];
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php Trd(1)?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .container { max-width: 900px; margin: auto; }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        #contenedor-funciones { margin-top: 20px; }
        .linea-config { 
            background: #fff; margin-bottom: 15px; padding: 15px; border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 5px solid #3498db;
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        select, input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; outline: none; }
        input[type="number"] { width: 80px; }
        textarea { 
            width: 100%; margin-top: 20px; font-family: 'Courier New', monospace; 
            background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; border: none; 
        }
        .etiqueta-f { font-weight: bold; color: #3498db; min-width: 30px; }
        
        /* Estilos Proyección */
        .seccion-proyeccion { margin-top: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tabla-costos { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabla-costos th, .tabla-costos td { border: 1px solid #eee; padding: 10px; text-align: center; }
        .tabla-costos th { background: #3498db; color: white; }
        .fila-highlight { background-color: #ebf5fb; font-weight: bold; }

        /* Estilos para la gráfica */
        .grafica-container { 
            width: 100%; 
            max-width: 800px; /* Ancho máximo para la gráfica */
            margin: 0 auto 20px auto; /* Centrar y añadir margen inferior */
            height: 350px; /* Altura fija para la gráfica */
        }
    </style>
</head>
<body>
<?php
    include_once 'nav.php';
?>
<div class="container">
    <h2>Configurador de Tarifas</h2>
    
    <div id="contenedor-funciones">

    </div>
    <!--
    <h3>JSON de Configuración:</h3>
    -->
    <textarea id="json-output" rows="8" readonly style="display: none">

    </textarea>

    <div class="seccion-proyeccion">
        <h3>Proyección de Cálculo de Costos</h3>

        <div class="grafica-container">
            <canvas id="costosChart"></canvas>
        </div>        
        <!--
        <div id="resultado-tabla">
            <p style="color: #999;">Configure las reglas para ver la proyección...</p>
        </div>
        -->
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let configTotal = [];
let myChart; // Variable global para la instancia de Chart.js

$(document).ready(function() {
    ejecutarFuncion1();
});

function actualizarJSON() {
    const idsExistentes = $('.linea-config').map(function() { return this.id; }).get();
    configTotal = configTotal.filter(item => idsExistentes.includes(item.id_linea));
    $('#json-output').val(JSON.stringify(configTotal, null, 2));
    proyectarTarifa(); 
}

function limpiarHijos(elementoPadre) {
    $(elementoPadre).nextAll().remove();
    actualizarJSON();
}

// --- MOTOR DE PROYECCIÓN ---
function proyectarTarifa() {
    if (configTotal.length === 0) return;

    // Diccionario de conversión de unidades a horas
    const conv = { 
        "hora": 1, "horas": 1, 
        "dia": 24, "dias": 24, 
        "semana": 168, "semanas": 168 
    };
    
    let data = [];
    let costo = 0;
    //const MAX_H = 120; // Proyección de 5 días
    const MAX_H = 72; // Proyección de 5 días

    // 1. Obtener la configuración base (F1)
    const f1 = configTotal.find(c => c.funcion === "f1");
    if (!f1) return;

    let p1 = parseFloat(f1.precio) || 0;
    costo = p1; // La hora 0 siempre inicia con el precio base

    // --- CASO: F1 INDEFINIDO ---
    if (f1.tipo === "Indefinido") {
        for (let h = 0; h <= MAX_H; h++) {
            data.push({ h, costo: p1 });
        }
    } 
    // --- CASO: F1 CADA (Entra lógica de F2) ---
    else if (f1.tipo === "Cada") {
        let horasF1 = (parseFloat(f1.tiempo) || 1) * conv[f1.unidad];
        const f2 = configTotal.find(c => c.funcion === "f2");

        for (let h = 0; h <= MAX_H; h++) {
            if (f2 && f2.tipo === "Hasta") {
                let limiteF2 = (parseFloat(f2.tiempo) || 1) * conv[f2.unidad];
                
                // Mientras estemos estrictamente debajo del límite, sumamos ciclos de F1
                if (h > 0 && h < limiteF2 && h % horasF1 === 0) {
                    costo += p1;
                }
                
                // En el momento que tocamos o pasamos el límite, toma el control la F3
                if (h >= limiteF2) {
                    const f3 = configTotal.find(c => c.funcion === "f3");
                    if (f3 && f3.tipo === "Cada") {
                        let horasF3 = (parseFloat(f3.tiempo) || 1) * conv[f3.unidad];
                        // El cobro de F3 inicia exactamente en el límite de F2 y se repite cada ciclo
                        if ((h - limiteF2) % horasF3 === 0) {
                            costo += parseFloat(f3.precio);
                        }
                    }
                }
            } else {
                // Si F2 es indefinido o no existe, F1 suma siempre
                if (h > 0 && h % horasF1 === 0) costo += p1;
            }
            data.push({ h, costo });
        }
    } 
    // --- CASO: F1 HASTA (Salta directo a F3) ---
    else if (f1.tipo === "Hasta") {
        let horasF1 = (parseFloat(f1.tiempo) || 1) * conv[f1.unidad];
        const f3 = configTotal.find(c => c.funcion === "f3");

        for (let h = 0; h <= MAX_H; h++) {
            if (h > horasF1 && f3) {
                let horasF3 = (parseFloat(f3.tiempo) || 1) * conv[f3.unidad];
                if (f3.tipo === "Hasta") {
                    // Mantiene el precio de F3 hasta su tiempo indicado
                    if (h <= horasF1 + horasF3) costo = parseFloat(f3.precio);
                } else if (f3.tipo === "Cada") {
                    // Suma el precio de F3 cada ciclo después de F1
                    if ((h - horasF1) % horasF3 === 0) costo += parseFloat(f3.precio);
                }
            }
            data.push({ h, costo });
        }
    }
    renderGrafica(data);
    //renderTabla(data);
}

function renderGrafica(data) {
    const ctx = document.getElementById('costosChart').getContext('2d');

    // Destruye la instancia anterior de la gráfica si existe
    if (myChart) {
        myChart.destroy();
    }

    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => `${d.h} hrs`),
            datasets: [{
                label: 'Costo Acumulado',
                data: data.map(d => d.costo),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#3498db',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Permitir que el contenedor controle el tamaño
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Tiempo'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Costo ($)'
                    },
                    beginAtZero: true
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Costo: $${context.raw}`;
                        }
                    }
                }
            }
        }
    });
}

function renderTabla(data) {
    let html = `<table class="tabla-costos"><tr><th>Tiempo</th><th>Costo Acumulado</th></tr>`;
    data.forEach((d, i) => {
        const anterior = data[i-1];
        if (d.h === 0 || d.h % 12 === 0 || (anterior && d.costo !== anterior.costo)) {
            let clase = (anterior && d.costo !== anterior.costo) ? 'class="fila-highlight"' : '';
            html += `<tr ${clase}><td>${d.h} hrs</td><td>$${d.costo}</td></tr>`;
        }
    });
    $('#resultado-tabla').html(html + "</table>");
}

// --- FUNCIÓN 1 ---
function ejecutarFuncion1() {
    const id = "f1_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F1:</span>
        El precio es <input type="number" class="val-precio" value="0">
        <select class="sel-tipo">
            <option value="">Seleccione...</option>
            <option value="Indefinido">Indefinido</option>
            <option value="Hasta">Hasta</option>
            <option value="Cada">Cada</option>
        </select>
        <span class="dinamico"></span>
    </div>`;
    $('#contenedor-funciones').append(html);

    $(`#${id} .sel-tipo`).on('change', function() {
        limpiarHijos(`#${id}`);
        const precio = $(`#${id} .val-precio`).val();
        const tipo = $(this).val();
        let registro = { id_linea: id, funcion: "f1", precio: precio, tipo: tipo };
        configTotal.push(registro);
        
        const contenedor = $(`#${id} .dinamico`).empty();
        
        if (tipo === "Cada") {
            contenedor.append(`
                <input type="number" class="val-t" placeholder="Cant.">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dia">dia</option><option value="hora">hora</option><option value="semana">semana</option>
                    <option value="dias">dias</option><option value="horas">horas</option><option value="semanas">semanas</option>
                </select>`);
            
            $(`#${id} .sel-tt`).on('change', function() {
                const tt = $(this).val();
                const vt = $(`#${id} .val-t`);
                ["dia", "hora", "semana"].includes(tt) ? vt.hide().val(1) : vt.show();
                registro.tiempo = vt.val(); registro.unidad = tt;
                actualizarJSON();
                ejecutarFuncion2(precio, registro.tiempo, tt);
            });
        } else if (tipo === "Hasta") {
            contenedor.append(`
                <input type="number" class="val-t">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dias">dias</option><option value="horas">horas</option><option value="semanas">semanas</option>
                </select>`);
            
            $(`#${id} .sel-tt`).on('change', function() {
                registro.tiempo = $(`#${id} .val-t`).val(); registro.unidad = $(this).val();
                actualizarJSON();
                ejecutarFuncion3(registro.tiempo, registro.unidad);
            });
        }
        actualizarJSON();
    });
}

// --- FUNCIÓN 2 ---
function ejecutarFuncion2(pPrev, tPrev, uPrev) {
    const id = "f2_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F2:</span>
        Cargo ${pPrev} por cada ${tPrev} ${uPrev} | 
        <select class="sel-tipo">
            <option value="">Seleccione...</option>
            <option value="Indefinido">Indefinido</option>
            <option value="Hasta">Hasta</option>
        </select>
        <span class="dinamico"></span>
    </div>`;
    $('#contenedor-funciones').append(html);

    $(`#${id} .sel-tipo`).on('change', function() {
        limpiarHijos(`#${id}`);
        const tipo = $(this).val();
        let registro = { id_linea: id, funcion: "f2", precio: pPrev, tipo: tipo };
        configTotal.push(registro);
        
        if (tipo === "Hasta") {
            $(`#${id} .dinamico`).html(`<input type="number" class="val-t"> <span>${uPrev}</span>`);
            $(`#${id} .val-t`).on('change', function() {
                registro.tiempo = $(this).val(); registro.unidad = uPrev;
                actualizarJSON();
                ejecutarFuncion3(registro.tiempo, uPrev);
            });
        }
        actualizarJSON();
    });
}

// --- FUNCIÓN 3 ---
function ejecutarFuncion3(tPrev, uPrev) {
    const id = "f3_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F3:</span>
        Después de ${tPrev} ${uPrev} el precio es <input type="number" class="val-precio" value="0">
        <select class="sel-tipo">
            <option value="">Seleccione...</option>
            <option value="Indefinido">Indefinido</option>
            <option value="Cada">Cada</option>
            <option value="Hasta">Hasta</option>
        </select>
        <span class="dinamico"></span>
    </div>`;
    $('#contenedor-funciones').append(html);

    $(`#${id} .sel-tipo`).on('change', function() {
        limpiarHijos(`#${id}`);
        const p = $(`#${id} .val-precio`).val();
        const tipo = $(this).val();
        let registro = { id_linea: id, funcion: "f3", precio: p, tipo: tipo };
        configTotal.push(registro);

        const contenedor = $(`#${id} .dinamico`).empty();
        if (tipo === "Cada") {
            contenedor.append(`
                <input type="number" class="val-t">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dia">dia</option><option value="hora">hora</option><option value="semana">semana</option>
                    <option value="dias">dias</option><option value="horas">horas</option><option value="semanas">semanas</option>
                </select>`);
            
            $(`#${id} .sel-tt`).on('change', function() {
                const tt = $(this).val();
                const vt = $(`#${id} .val-t`);
                ["dia", "hora", "semana"].includes(tt) ? vt.hide().val(1) : vt.show();
                registro.tiempo = vt.val(); registro.unidad = tt;
                actualizarJSON();
                ejecutarFuncion2(p, registro.tiempo, tt);
            });
        } else if (tipo === "Hasta") {
            contenedor.append(`
                <input type="number" class="val-t">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dias">dias</option><option value="horas">horas</option><option value="semanas">semanas</option>
                </select>`);
            
            $(`#${id} .sel-tt`).on('change', function() {
                registro.tiempo = $(`#${id} .val-t`).val(); registro.unidad = $(this).val();
                actualizarJSON();
                ejecutarFuncion3(registro.tiempo, registro.unidad);
            });
        }
        actualizarJSON();
    });
}
</script>
</body>
</html>