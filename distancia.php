<?php
// Configuración
$apiKey = 'AIzaSyCRw-m6FwodZdcIPw1rtAKWqvyziRm1ihM'; // <--- PEGA TU CLAVE AQUÍ
$cpOrigen = urlencode("44840,MX");
$cpDestino = urlencode("45615,MX");

// Construir URL
$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$cpOrigen}&destinations={$cpDestino}&units=imperial&key={$apiKey}";

echo "--- Iniciando prueba de conexión ---\n";

// Realizar la petición
$response = file_get_contents($url);
$data = json_decode($response, true);

// 1. Verificar errores de conexión o API Key
if ($data['status'] !== 'OK') {
    echo "❌ ERROR DE CONFIGURACIÓN:\n";
    echo "Estado: " . $data['status'] . "\n";
    if (isset($data['error_message'])) {
        echo "Mensaje: " . $data['error_message'] . "\n";
    }
    echo "Revisa: Que la API Key sea correcta y la 'Distance Matrix API' esté habilitada.\n";
    exit;
}

// 2. Verificar si encontró la ruta
$elemento = $data['rows'][0]['elements'][0];

if ($elemento['status'] === 'OK') {
    echo "✅ ¡CONEXIÓN EXITOSA!\n";
    echo "------------------------------\n";
    echo "Origen: " . $data['origin_addresses'][0] . "\n";
    echo "Destino: " . $data['destination_addresses'][0] . "\n";
    echo "Distancia en carretera: " . $elemento['distance']['text'] . "\n";
    echo "Tiempo estimado: " . $elemento['duration']['text'] . "\n";
    echo "------------------------------\n";
} else {
    echo "❌ ERROR DE RUTA: " . $elemento['status'] . "\n";
    echo "Esto sucede si los códigos postales no existen o no hay ruta terrestre entre ellos.\n";
}