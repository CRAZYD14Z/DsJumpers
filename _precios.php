<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurador Dinámico con Historial</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background-color: #f4f7f6; }
        #contenedor-funciones { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .linea-config { 
            margin-bottom: 15px; 
            padding: 15px; 
            background: #fff;
            border-left: 5px solid #28a745;
            display: flex; 
            align-items: center; 
            gap: 10px; 
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        textarea { width: 100%; margin-top: 20px; font-family: 'Courier New', monospace; background: #2d2d2d; color: #a6e22e; padding: 15px; border: none; border-radius: 5px; }
        input, select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>

    <h2>Configuración de Tarifas Dinámicas</h2>
    <div id="contenedor-funciones"></div>

    <h3>JSON de Configuración:</h3>
    <textarea id="json-output" rows="12" readonly></textarea>

<script>
let configTotal = [];

$(document).ready(function() {
    ejecutarFuncion1();
});

function actualizarJSON() {
    $('#json-output').val(JSON.stringify(configTotal, null, 2));
}

// Función para eliminar del JSON los pasos que ya no existen en el HTML
function sincronizarJSON() {
    const idsExistentes = $('.linea-config').map(function() { return this.id; }).get();
    configTotal = configTotal.filter(item => idsExistentes.includes(item.id_linea));
    actualizarJSON();
}

function limpiarHijos(elementoPadre) {
    $(elementoPadre).nextAll().remove();
    sincronizarJSON();
}

// --- FUNCIÓN 1 ---
function ejecutarFuncion1() {
    const id = "linea_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <strong>F1:</strong> El precio es <input type="number" class="val-precio" value="100">
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
        const seleccion = $(this).val();
        const contenedor = $(`#${id} .dinamico`);
        const precio = $(`#${id} .val-precio`).val();
        contenedor.empty();

        let registro = { id_linea: id, funcion: "f1", precio: precio, tipo: seleccion };
        configTotal.push(registro);
        actualizarJSON();

        if (seleccion === "Cada") {
            contenedor.append(`
                <input type="number" class="val-tiempo" placeholder="Tiempo">
                <select class="sel-tipotiempo">
                    <option value="">--</option>
                    <option value="dia">dia</option><option value="hora">hora</option><option value="semana">semana</option>
                    <option value="dias">dias</option><option value="horas">horas</option><option value="semanas">semanas</option>
                </select>
            `);
            $(`#${id} .sel-tipotiempo`).on('change', function() {
                const tt = $(this).val();
                if(!tt) return;
                const inputT = $(`#${id} .val-tiempo`);
                ["dia", "hora", "semana"].includes(tt) ? inputT.hide().val(1) : inputT.show();
                
                registro.tiempo = inputT.val();
                registro.unidad = tt;
                actualizarJSON();
                ejecutarFuncion2(precio, registro.tiempo, tt);
            });
        } else if (seleccion === "Hasta") {
            contenedor.append(`
                <input type="number" class="val-tiempo" placeholder="Tiempo">
                <select class="sel-tipotiempo">
                    <option value="">--</option><option value="dias">dias</option><option value="horas">horas</option><option value="semanas">semanas</option>
                </select>
            `);
            $(`#${id} .sel-tipotiempo`).on('change', function() {
                const tt = $(this).val();
                const t = $(`#${id} .val-tiempo`).val();
                if(!tt || !t) return;
                registro.tiempo = t;
                registro.unidad = tt;
                actualizarJSON();
                ejecutarFuncion3(t, tt);
            });
        }
    });
}

// --- FUNCIÓN 2 ---
function ejecutarFuncion2(precioPrev, tiempoPrev, tipoPrev) {
    const id = "linea_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span><strong>F2:</strong> Cargo ${precioPrev} por cada ${tiempoPrev} ${tipoPrev}</span>
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
        const seleccion = $(this).val();
        const contenedor = $(`#${id} .dinamico`);
        contenedor.empty();

        let registro = { id_linea: id, funcion: "f2", tipo: seleccion };
        configTotal.push(registro);
        actualizarJSON();

        if (seleccion === "Hasta") {
            contenedor.append(` <input type="number" class="val-tiempo"> <span>${tipoPrev}</span> `);
            $(`#${id} .val-tiempo`).on('blur', function() {
                const t = $(this).val();
                if(!t) return;
                registro.tiempo = t;
                registro.unidad = tipoPrev;
                actualizarJSON();
                ejecutarFuncion3(t, tipoPrev);
            });
        }
    });
}

// --- FUNCIÓN 3 ---
function ejecutarFuncion3(tiempoPrev, tipoPrev) {
    const id = "linea_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span><strong>F3:</strong> Después de ${tiempoPrev} ${tipoPrev} el precio es</span>
        <input type="number" class="val-precio" value="0">
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
        const seleccion = $(this).val();
        const precio = $(`#${id} .val-precio`).val();
        const contenedor = $(`#${id} .dinamico`);
        contenedor.empty();

        let registro = { id_linea: id, funcion: "f3", precio: precio, tipo: seleccion };
        configTotal.push(registro);
        actualizarJSON();

        if (seleccion === "Cada") {
            contenedor.append(`
                <input type="number" class="val-tiempo">
                <select class="sel-tipotiempo">
                    <option value="">--</option>
                    <option value="dia">dia</option><option value="hora">hora</option><option value="semana">semana</option>
                    <option value="dias">dias</option><option value="horas">horas</option><option value="semanas">semanas</option>
                </select>
            `);
            $(`#${id} .sel-tipotiempo`).on('change', function() {
                const tt = $(this).val();
                if(!tt) return;
                const inputT = $(`#${id} .val-tiempo`);
                ["dia", "hora", "semana"].includes(tt) ? inputT.hide().val(1) : inputT.show();
                registro.tiempo = inputT.val();
                registro.unidad = tt;
                actualizarJSON();
                ejecutarFuncion2(precio, registro.tiempo, tt);
            });
        } else if (seleccion === "Hasta") {
            contenedor.append(`
                <input type="number" class="val-tiempo">
                <select class="sel-tipotiempo">
                    <option value="">--</option><option value="dias">dias</option><option value="horas">horas</option><option value="semanas">semanas</option>
                </select>
            `);
            $(`#${id} .sel-tipotiempo`).on('change', function() {
                const tt = $(this).val();
                const t = $(`#${id} .val-tiempo`).val();
                if(!tt || !t) return;
                registro.tiempo = t;
                registro.unidad = tt;
                actualizarJSON();
                ejecutarFuncion3(t, tt);
            });
        }
    });
}
</script>
</body>
</html>