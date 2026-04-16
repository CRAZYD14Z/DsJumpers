<?php
require '../vendor/autoload.php'; // Carga la librería de Google Auth

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;

// 1. Ruta al archivo JSON que descargaste de Google Cloud
$rutaJson = __DIR__ . '/gaxi-487815-79b122d9b0f0.json';

// 2. Definir el "Scope" (alcance) de la API
$scope = 'https://www.googleapis.com/auth/cloud-platform';

try {
    // 3. Generar las credenciales y obtener el Token
    $creds = new ServiceAccountCredentials($scope, $rutaJson);
    $tokenArray = $creds->fetchAuthToken();
    $accessToken = $tokenArray['access_token'];

    // 4. Tu JSON que viene del JavaScript
    $jsonReceived = $_POST['json_google'];

    // 5. Configurar cURL con el Token OAuth2
    // IMPORTANTE: Cambia "TU_PROYECTO_ID" por el ID real de tu proyecto en Google Cloud
    $url = "https://routeoptimization.googleapis.com/v1/projects/gaxi-487815:optimizeTours";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonReceived);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken // Aquí va el token en lugar de la API Key
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo json_encode(["error" => "Error de Google: " . $response]);
    } else {
        echo $response;
    }

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}