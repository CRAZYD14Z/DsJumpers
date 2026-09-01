<?php
    ob_start();
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); 
    }
    include_once __DIR__ . '/../config/config.php';     
    include_once __DIR__ . '/../config/database.php';    
    $database = new Database();
    $db = $database->getConnection();

// Procesar la solicitud si viene por AJAX
if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    
    $apiKey = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : ($_ENV['GOOGLE_API_KEY'] ?? '');
    if (empty($apiKey)) {
        echo json_encode(['error' => 'GOOGLE_API_KEY no configurada']);
        exit;
    }

    $countryCode = 'us';
    if ($db) {
        try {
            $sql = "SELECT Pais FROM account LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);    

            if ($account && !empty($account['Pais'])) {
                $pais = strtoupper(trim($account['Pais']));
                if ($pais === 'USA' || $pais === 'US') {
                    $countryCode = 'us';
                } elseif ($pais === 'MEXICO' || $pais === 'MÉXICO' || $pais === 'MX') {
                    $countryCode = 'mx';
                } else {
                    $countryCode = strtolower(substr($pais, 0, 2));
                }
            }
        } catch (Exception $e) {
            // Continuar con el valor por defecto si falla la consulta
        }
    }

    $query = urlencode(trim($_GET['buscar']));
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$query}";
    if (!empty($countryCode)) {
        $url .= "&components=country:{$countryCode}";
    }
    $url .= "&key={$apiKey}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['status']) && ($data['status'] === 'OK' || $data['status'] === 'ZERO_RESULTS')) {
            $formattedResults = [];
            
            if ($data['status'] === 'OK' && !empty($data['results'])) {
                foreach ($data['results'] as $result) {
                    $streetNumber = '';
                    $route = '';
                    $locality = '';
                    $sublocality = '';
                    $adminArea2 = '';
                    $adminArea1 = '';
                    $adminArea1Short = '';
                    $countryName = '';
                    $countryShort = '';
                    $postalCode = '';

                    foreach ($result['address_components'] ?? [] as $comp) {
                        $types = $comp['types'] ?? [];
                        if (in_array('street_number', $types)) {
                            $streetNumber = $comp['long_name'];
                        }
                        if (in_array('route', $types)) {
                            $route = $comp['long_name'];
                        }
                        if (in_array('locality', $types)) {
                            $locality = $comp['long_name'];
                        }
                        if (in_array('sublocality', $types) || in_array('sublocality_level_1', $types)) {
                            $sublocality = $comp['long_name'];
                        }
                        if (in_array('administrative_area_level_2', $types)) {
                            $adminArea2 = $comp['long_name'];
                        }
                        if (in_array('administrative_area_level_1', $types)) {
                            $adminArea1 = $comp['long_name'];
                            $adminArea1Short = $comp['short_name'];
                        }
                        if (in_array('country', $types)) {
                            $countryName = $comp['long_name'];
                            $countryShort = $comp['short_name'];
                        }
                        if (in_array('postal_code', $types)) {
                            $postalCode = $comp['long_name'];
                        }
                    }

                    $lat = $result['geometry']['location']['lat'] ?? '';
                    $lng = $result['geometry']['location']['lng'] ?? '';
                    $city = !empty($locality) ? $locality : (!empty($sublocality) ? $sublocality : (!empty($adminArea2) ? $adminArea2 : ''));

                    // Normalizar estado para compatibilidad con el selector de estados
                    $stateForDropdown = $adminArea1;
                    if (!empty($adminArea1)) {
                        $cleanState = strtoupper(strtr($adminArea1, [
                            'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'Ü'=>'U', 'Ñ'=>'N',
                            'á'=>'A', 'é'=>'E', 'í'=>'I', 'ó'=>'O', 'ú'=>'U', 'ü'=>'U', 'ñ'=>'N'
                        ]));
                        
                        if ($db) {
                            try {
                                $paisCode = ($countryShort === 'US' || strtoupper($countryName) === 'UNITED STATES') ? 'USA' : 'MX';
                                $stmtSt = $db->prepare("SELECT Estado FROM estados_pais WHERE CodigoPais = :pais AND (Estado = :estado OR Estado LIKE :like1) LIMIT 1");
                                $stmtSt->execute([':pais' => $paisCode, ':estado' => $cleanState, ':like1' => '%' . $cleanState . '%']);
                                $rowSt = $stmtSt->fetch(PDO::FETCH_ASSOC);
                                if ($rowSt) {
                                    $stateForDropdown = $rowSt['Estado'];
                                } else {
                                    $stateForDropdown = $cleanState;
                                }
                            } catch (Exception $e) {
                                $stateForDropdown = $cleanState;
                            }
                        } else {
                            $stateForDropdown = $cleanState;
                        }
                    }

                    $formattedResults[] = [
                        'display_name' => $result['formatted_address'] ?? '',
                        'lat' => (string)$lat,
                        'lon' => (string)$lng,
                        'lng' => (string)$lng,
                        'place_id' => $result['place_id'] ?? '',
                        'address' => [
                            'road' => $route,
                            'house_number' => $streetNumber,
                            'country' => $countryName,
                            'country_code' => strtolower($countryShort),
                            'city' => $city,
                            'town' => $locality,
                            'suburb' => $sublocality,
                            'village' => $adminArea2,
                            'state' => $stateForDropdown,
                            'state_code' => $adminArea1Short,
                            'postcode' => $postalCode
                        ]
                    ];
                }
            }
            
            echo json_encode($formattedResults, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['error' => 'Google Maps API: ' . ($data['status'] ?? 'DESCONOCIDO')]);
        }
    } else {
        echo json_encode(['error' => 'No se pudo conectar con el servicio de Google Maps' . ($curlError ? ": $curlError" : '')]);
    }
    exit;
}
?>