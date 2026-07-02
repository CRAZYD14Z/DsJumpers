<?php
// Procesar la solicitud si viene por AJAX
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    header('Content-Type: application/json');
    
    $query = urlencode($_GET['buscar']);
    
    // Segmentado a Estados Unidos (us) y con &addressdetails=1 para desglose obligatorio
    $countryCode = 'us'; 
    $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&addressdetails=1&limit=5&countrycodes={$countryCode}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MiAplicacionPHP/1.0 (contacto@midominio.com)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo $response;
    } else {
        echo json_encode(['error' => 'No se pudo conectar con el servicio']);
    }
    exit;
}
?>