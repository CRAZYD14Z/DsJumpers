<?php
ob_start();
session_start(); 
include_once '../config/config.php';     
include_once '../config/database.php';    
$database = new Database();
$db = $database->getConnection();

// Procesar la solicitud si viene por AJAX
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    // 1. Obtener el país de la base de datos
    $sql = "SELECT Pais FROM account LIMIT 1"; // Añadido LIMIT 1 por seguridad
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_ASSOC);    

    if (!$account) {
        echo json_encode(['error' => 'No se encontró el país en la configuración de la cuenta']);
        exit;
    }

    if ($account['Pais'] == 'USA') {
        //$account['Pais'] = 'US';
        $countryCode = strtolower($account['Pais']); 
    }
    else{
        $countryCode = strtolower($account['Pais']); 
    }

    $query = urlencode($_GET['buscar']); // Aquí viajará el CP
    
    
    // Construimos la URL apuntando a Nominatim
    $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&addressdetails=1&limit=1&countrycodes={$countryCode}";
    
    // 2. Llamada cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // IMPORTANTE: Nominatim requiere un User-Agent real o bloqueará la petición
    curl_setopt($ch, CURLOPT_USERAGENT, 'MiAplicacionPHP/1.0 (contacto@midominio.com)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // 3. Procesar el resultado
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        // Validar si encontramos resultados
        if (!empty($data) && isset($data[0]['address'])) {
            $address = $data[0]['address'];
            
            // Nominatim puede llamar a la ciudad de distintas formas según la población
            $ciudad = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? 'No encontrada';
            $estado = $address['state'] ?? 'No encontrado';
            

            if ($account['Pais'] =='US')
                $account['Pais'] = 'USA';

            $sql = "SELECT Id FROM estados_pais  WHERE CodigoPais  = :pais  AND Estado = :estado LIMIT 1"; // Añadido LIMIT 1 por seguridad
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":pais", $account['Pais']); 
            $stmt->bindValue(":estado", strtoupper($estado) ); 
            $stmt->execute();
            $state = $stmt->fetch(PDO::FETCH_ASSOC);              


            // Devolvemos solo la información limpia que necesitas
            echo json_encode([
                'status' => 'success',
                'pais' => $account['Pais'],
                'ciudad' => $ciudad,
                'estado' => $state['Id']
            ], JSON_UNESCAPED_UNICODE);
            
        } else {
            echo json_encode(['error' => 'No se encontraron resultados para ese Código Postal']);
        }
    } else {
        echo json_encode(['error' => 'No se pudo conectar con el servicio de mapas']);
    }
    exit;
}
?>