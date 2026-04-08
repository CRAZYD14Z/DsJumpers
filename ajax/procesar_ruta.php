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

$jsonReceived1 = '

{
  "model": {
    "globalStartTime": "2025-04-03T08:00:00-06:00",
    "globalEndTime": "2025-04-03T20:00:00-06:00",

    "vehicles": [
      {
        "label": "Vehiculo-1-Van",
        "startLocation": {
          "latitude": 20.6597,
          "longitude": -103.3496
        },
        "endLocation": {
          "latitude": 20.6597,
          "longitude": -103.3496
        },
        "loadLimits": {
          "volumen_dm3": { "maxLoad": "200" },
          "peso_kg":    { "maxLoad": "500" }
        },
        "costPerHour": 150.0,
        "costPerKilometer": 8.0,
        "travelDurationMultiple": 1.0
      },
      {
        "label": "Vehiculo-2-Pickup",
        "startLocation": {
          "latitude": 20.6597,
          "longitude": -103.3496
        },
        "endLocation": {
          "latitude": 20.6597,
          "longitude": -103.3496
        },
        "loadLimits": {
          "volumen_dm3": { "maxLoad": "120" },
          "peso_kg":    { "maxLoad": "300" }
        },
        "costPerHour": 120.0,
        "costPerKilometer": 6.0,
        "travelDurationMultiple": 1.0
      },
      {
        "label": "Vehiculo-3-Moto",
        "startLocation": {
          "latitude": 20.6597,
          "longitude": -103.3496
        },
        "endLocation": {
          "latitude": 20.6597,
          "longitude": -103.3496
        },
        "loadLimits": {
          "volumen_dm3": { "maxLoad": "30" },
          "peso_kg":    { "maxLoad": "50" }
        },
        "costPerHour": 80.0,
        "costPerKilometer": 3.0,
        "travelDurationMultiple": 0.85
      }
    ],

    "shipments": [
      {
        "label": "Entrega-01-Zapopan-Centro",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.7214, "longitude": -103.3909 },
          "duration": "420s",
          "timeWindows": [{
            "startTime": "2025-04-03T09:00:00-06:00",
            "endTime":   "2025-04-03T12:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "25" },
          "peso_kg":    { "amount": "10" }
        },
        "penaltyCost": 500.0
      },
      {
        "label": "Entrega-02-Tlaquepaque",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.6429, "longitude": -103.3100 },
          "duration": "300s",
          "timeWindows": [{
            "startTime": "2025-04-03T09:00:00-06:00",
            "endTime":   "2025-04-03T13:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "15" },
          "peso_kg":    { "amount": "8" }
        },
        "penaltyCost": 400.0
      },
      {
        "label": "Entrega-03-Chapalita",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.6760, "longitude": -103.4020 },
          "duration": "360s",
          "timeWindows": [{
            "startTime": "2025-04-03T10:00:00-06:00",
            "endTime":   "2025-04-03T13:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "40" },
          "peso_kg":    { "amount": "20" }
        },
        "penaltyCost": 450.0
      },
      {
        "label": "Entrega-04-Providencia",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.6878, "longitude": -103.3780 },
          "duration": "240s",
          "timeWindows": [{
            "startTime": "2025-04-03T08:00:00-06:00",
            "endTime":   "2025-04-03T11:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "8" },
          "peso_kg":    { "amount": "3" }
        },
        "penaltyCost": 350.0
      },
      {
        "label": "Entrega-05-Tonala",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.6240, "longitude": -103.2340 },
          "duration": "480s",
          "timeWindows": [{
            "startTime": "2025-04-03T11:00:00-06:00",
            "endTime":   "2025-04-03T15:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "60" },
          "peso_kg":    { "amount": "35" }
        },
        "penaltyCost": 400.0
      },
      {
        "label": "Entrega-06-Oblatos",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.6990, "longitude": -103.3150 },
          "duration": "300s",
          "timeWindows": [{
            "startTime": "2025-04-03T09:00:00-06:00",
            "endTime":   "2025-04-03T12:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "12" },
          "peso_kg":    { "amount": "6" }
        },
        "penaltyCost": 300.0
      },
      {
        "label": "Entrega-07-Huentitan",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.7350, "longitude": -103.3300 },
          "duration": "360s",
          "timeWindows": [{
            "startTime": "2025-04-03T13:00:00-06:00",
            "endTime":   "2025-04-03T17:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "20" },
          "peso_kg":    { "amount": "12" }
        },
        "penaltyCost": 350.0
      },
      {
        "label": "Entrega-08-Americana",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.6720, "longitude": -103.3680 },
          "duration": "180s",
          "timeWindows": [{
            "startTime": "2025-04-03T08:00:00-06:00",
            "endTime":   "2025-04-03T10:30:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "5" },
          "peso_kg":    { "amount": "2" }
        },
        "penaltyCost": 500.0
      },
      {
        "label": "Entrega-09-Tequesquitlan",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.6550, "longitude": -103.2950 },
          "duration": "420s",
          "timeWindows": [{
            "startTime": "2025-04-03T14:00:00-06:00",
            "endTime":   "2025-04-03T18:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "30" },
          "peso_kg":    { "amount": "18" }
        },
        "penaltyCost": 300.0
      },
      {
        "label": "Entrega-10-Zapopan-Norte",
        "deliveries": [{
          "arrivalLocation": { "latitude": 20.7480, "longitude": -103.4100 },
          "duration": "300s",
          "timeWindows": [{
            "startTime": "2025-04-03T15:00:00-06:00",
            "endTime":   "2025-04-03T19:00:00-06:00"
          }]
        }],
        "loadDemands": {
          "volumen_dm3": { "amount": "18" },
          "peso_kg":    { "amount": "9" }
        },
        "penaltyCost": 350.0
      }
    ]
  }
}
';

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