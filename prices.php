<?php
    ob_start();
    session_start(); 
    // Incluye la clase de conexión a la BD
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    
    $Idioma = 'es';
    $_SESSION['Idioma'] = $Idioma;

    $query = "select Traduccion FROM programas_traduccion where Programa = 'prices' AND Idioma = ? ORDER BY Id";            
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $Idioma);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $Traducciones[]=''; // El índice 0 queda vacío según tu lógica original
    if ($resultados) {
        foreach ($resultados as $registro) {
            $Traducciones[]=$registro['Traduccion'];
        }
    }    
    function Trd_2($Id){
        global $Traducciones;
        echo isset($Traducciones[$Id]) ? $Traducciones[$Id] : "Trd_2[$Id]";
    }
?>
<!DOCTYPE html>
<html lang="<?php echo $Idioma;?>">
<head>
    <meta charset="UTF-8">
    <title><?php Trd_2(1)?></title>
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
            max-width: 800px; 
            margin: 0 auto 20px auto; 
            height: 350px; 
        }
    </style>
</head>
<body>
<?php
    include_once 'nav.php';
?>
<div class="container">
    <h2><?php Trd_2(2)?></h2>
    
    <div id="contenedor-funciones"></div>

    <textarea id="json-output" rows="8" ></textarea>
    <button onclick="cargar()">Cargar</button>
    <div class="seccion-proyeccion">
        <h3><?php Trd_2(3)?></h3>

        <div class="grafica-container">
            <canvas id="costosChart"></canvas>
        </div>        
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Diccionario de traducciones para ser usado en JS
//readonly style="display: none"
const txt = {
    lblCostoAcumulado: "<?php Trd_2(4)?>",
    lblEjeX: "<?php Trd_2(5)?>",
    lblEjeY: "<?php Trd_2(6)?>",
    f1Precio: "<?php Trd_2(7)?>",
    selDefault: "<?php Trd_2(8)?>",
    optIndefinido: "<?php Trd_2(9)?>",
    optHasta: "<?php Trd_2(10)?>",
    optCada: "<?php Trd_2(11)?>",
    f2Cargo: "<?php Trd_2(12)?>",
    f2PorCada: "<?php Trd_2(13)?>",
    f3Despues: "<?php Trd_2(14)?>",
    f3Precio: "<?php Trd_2(15)?>",
    placeholderCant: "<?php Trd_2(16)?>"
};

let configTotal = [];
let myChart; 

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

function proyectarTarifa() {
    if (configTotal.length === 0) return;

    const conv = { 
        "hora": 1, "horas": 1, 
        "dia": 24, "dias": 24, 
        "semana": 168, "semanas": 168 
    };
    
    let data = [];
    let costo = 0;
    const MAX_H = 72; 

    const f1 = configTotal.find(c => c.funcion === "f1");
    if (!f1) return;

    let p1 = parseFloat(f1.precio) || 0;
    costo = p1; 

    if (f1.tipo === "Indefinido") {
        for (let h = 0; h <= MAX_H; h++) {
            data.push({ h, costo: p1 });
        }
    } 
    else if (f1.tipo === "Cada") {
        let horasF1 = (parseFloat(f1.tiempo) || 1) * conv[f1.unidad];
        const f2 = configTotal.find(c => c.funcion === "f2");

        for (let h = 0; h <= MAX_H; h++) {
            if (f2 && f2.tipo === "Hasta") {
                let limiteF2 = (parseFloat(f2.tiempo) || 1) * conv[f2.unidad];
                if (h > 0 && h < limiteF2 && h % horasF1 === 0) {
                    costo += p1;
                }
                if (h >= limiteF2) {
                    const f3 = configTotal.find(c => c.funcion === "f3");
                    if (f3 && f3.tipo === "Cada") {
                        let horasF3 = (parseFloat(f3.tiempo) || 1) * conv[f3.unidad];
                        if ((h - limiteF2) % horasF3 === 0) {
                            costo += parseFloat(f3.precio);
                        }
                    }
                }
            } else {
                if (h > 0 && h % horasF1 === 0) costo += p1;
            }
            data.push({ h, costo });
        }
    } 
    else if (f1.tipo === "Hasta") {
        let horasF1 = (parseFloat(f1.tiempo) || 1) * conv[f1.unidad];
        const f3 = configTotal.find(c => c.funcion === "f3");

        for (let h = 0; h <= MAX_H; h++) {
            if (h > horasF1 && f3) {
                let horasF3 = (parseFloat(f3.tiempo) || 1) * conv[f3.unidad];
                if (f3.tipo === "Hasta") {
                    if (h <= horasF1 + horasF3) costo = parseFloat(f3.precio);
                } else if (f3.tipo === "Cada") {
                    if ((h - horasF1) % horasF3 === 0) costo += parseFloat(f3.precio);
                }
            }
            data.push({ h, costo });
        }
    }
    renderGrafica(data);
}

function renderGrafica(data) {
    const ctx = document.getElementById('costosChart').getContext('2d');

    if (myChart) {
        myChart.destroy();
    }

    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => `${d.h} hrs`),
            datasets: [{
                label: txt.lblCostoAcumulado,
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
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: { display: true, text: txt.lblEjeX }
                },
                y: {
                    title: { display: true, text: txt.lblEjeY },
                    beginAtZero: true
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${txt.lblCostoAcumulado}: $${context.raw}`;
                        }
                    }
                }
            }
        }
    });
}

function ejecutarFuncion1() {
    const id = "f1_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F1:</span>
        ${txt.f1Precio} <input type="number" class="val-precio" value="0">
        <select class="sel-tipo">
            <option value="">${txt.selDefault}</option>
            <option value="Indefinido">${txt.optIndefinido}</option>
            <option value="Hasta">${txt.optHasta}</option>
            <option value="Cada">${txt.optCada}</option>
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
                <input type="number" class="val-t" placeholder="${txt.placeholderCant}">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dia"><?php Trd_2(19)?></option><option value="hora"><?php Trd_2(17)?></option><option value="semana"><?php Trd_2(21)?></option>
                    <option value="dias"><?php Trd_2(20)?></option><option value="horas"><?php Trd_2(18)?></option><option value="semanas"><?php Trd_2(22)?></option>
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
                    <option value="dias"><?php Trd_2(20)?></option><option value="horas"><?php Trd_2(18)?></option><option value="semanas"><?php Trd_2(22)?></option>
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

function ejecutarFuncion2(pPrev, tPrev, uPrev) {
    const id = "f2_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F2:</span>
        ${txt.f2Cargo} ${pPrev} ${txt.f2PorCada} ${tPrev} ${uPrev} | 
        <select class="sel-tipo">
            <option value="">${txt.selDefault}</option>
            <option value="Indefinido">${txt.optIndefinido}</option>
            <option value="Hasta">${txt.optHasta}</option>
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

function ejecutarFuncion3(tPrev, uPrev) {
    const id = "f3_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F3:</span>
        ${txt.f3Despues} ${tPrev} ${uPrev} ${txt.f3Precio} <input type="number" class="val-precio" value="0">
        <select class="sel-tipo">
            <option value="">${txt.selDefault}</option>
            <option value="Indefinido">${txt.optIndefinido}</option>
            <option value="Cada">${txt.optCada}</option>
            <option value="Hasta">${txt.optHasta}</option>
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
                    <option value="dia"><?php Trd_2(19)?></option><option value="hora"><?php Trd_2(17)?></option><option value="semana"><?php Trd_2(21)?></option>
                    <option value="dias"><?php Trd_2(20)?></option><option value="horas"><?php Trd_2(18)?></option><option value="semanas"><?php Trd_2(22)?></option>
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
                    <option value="dias"><?php Trd_2(20)?></option><option value="horas"><?php Trd_2(18)?></option><option value="semanas"><?php Trd_2(22)?></option>
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

function cargar() {
    try {
        const data = JSON.parse($('#json-output').val());
        if (!Array.isArray(data) || data.length === 0) return;

        // 1. Limpiar el estado actual
        $('#contenedor-funciones').empty();
        configTotal = [];

        // 2. Identificar las funciones presentes
        const f1 = data.find(item => item.funcion === "f1");
        const f2 = data.find(item => item.funcion === "f2");
        const f3 = data.find(item => item.funcion === "f3");

        // 3. Reconstruir F1
        if (f1) {
            ejecutarFuncion1(); 
            const $f1Element = $('.linea-config').last();
            $f1Element.find('.val-precio').val(f1.precio);
            $f1Element.find('.sel-tipo').val(f1.tipo).trigger('change');
            
            if (f1.tiempo) {
                $f1Element.find('.val-t').val(f1.tiempo);
                $f1Element.find('.sel-tt').val(f1.unidad).trigger('change');
            }
        }

        // 4. Reconstruir F2 (si existe)
        if (f2) {
            const $f2Element = $(`[id^="f2_"]`); // Busca el elemento F2 recién creado por el trigger de F1
            $f2Element.find('.sel-tipo').val(f2.tipo).trigger('change');
            
            if (f2.tiempo) {
                $f2Element.find('.val-t').val(f2.tiempo).trigger('change');
            }
        }

        // 5. Reconstruir F3 (si existe)
        if (f3) {
            const $f3Element = $(`[id^="f3_"]`);
            $f3Element.find('.val-precio').val(f3.precio);
            $f3Element.find('.sel-tipo').val(f3.tipo).trigger('change');

            if (f3.tiempo) {
                $f3Element.find('.val-t').val(f3.tiempo);
                $f3Element.find('.sel-tt').val(f3.unidad).trigger('change');
            }
        }

        // Forzar actualización de la gráfica al terminar
        proyectarTarifa();

    } catch (e) {
        alert("Error al leer el JSON: " + e.message);
    }
}

</script>
</body>
</html>