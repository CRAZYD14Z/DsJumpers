<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurador de Tarifa Dinámica</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 30px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 800px; margin: auto; }
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
    </style>
</head>
<body>

<div class="container">
    <h2>Configurador de Tarifas</h2>
    
    <div id="contenedor-funciones"></div>

    <h3>JSON de Configuración:</h3>
    <textarea id="json-output" rows="10" readonly></textarea>
</div>

<script>
let configTotal = [];

$(document).ready(function() {
    ejecutarFuncion1();
});

function actualizarJSON() {
    const idsExistentes = $('.linea-config').map(function() { return this.id; }).get();
    configTotal = configTotal.filter(item => idsExistentes.includes(item.id_linea));
    $('#json-output').val(JSON.stringify(configTotal, null, 2));
}

function limpiarHijos(elementoPadre) {
    $(elementoPadre).nextAll().remove();
    actualizarJSON();
}

// --- FUNCIÓN 1 ---
function ejecutarFuncion1() {
    const id = "f1_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F1:</span>
        El precio es <input type="number" class="val-precio" value="280">
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
        Después de ${tPrev} ${uPrev} el precio es <input type="number" class="val-precio" value="150">
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