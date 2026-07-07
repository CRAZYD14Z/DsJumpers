<?php
    ob_start();
    session_start(); 
    include_once '../config/config.php';     
    include_once '../config/database.php';    
    $database = new Database();
    $db = $database->getConnection();
// Procesar la solicitud si viene por AJAX
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    header('Content-Type: application/json');
    
    $sql = "SELECT Pais FROM account LIMIT 1";
    $stmt = $db->prepare($sql);
    //$stmt->bindValue(":name", $data->Product); 
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);    

    if ($account['Pais'] == 'USA')
        $account['Pais'] = 'US';

    $query = urlencode($_GET['buscar']);
    // Segmentado a Estados Unidos (us) y con &addressdetails=1 para desglose obligatorio
    $countryCode = strtolower($account['Pais']); 
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