<?php
// Incluye el autoloader generado por Composer
require 'vendor/autoload.php';

use \Firebase\JWT\JWT;

// Clave secreta para la prueba
$key = 'una_clave_secreta_para_prueba';

// Payload de prueba
$payload = array(
    "iss" => "http://ejemplo.com", // Emisor
    "aud" => "http://ejemplo.org", // Audiencia
    "iat" => time(),              // Emitido en
    "nbf" => time(),              // Válido a partir de
    "data" => [
        'userId' => 123,
        'username' => 'testuser'
    ]
);

echo "<h2>Paso 1: Codificar el Token (Generar JWT)</h2>";

try {
    // Intenta codificar (firmar) el token
    $jwt = JWT::encode($payload, $key, 'HS256');
    
    echo "✅ **¡Instalación exitosa!** <br>";
    echo "Token generado: <code>" . $jwt . "</code> <br><br>";

    // --- Decodificación ---
    echo "<h2>Paso 2: Decodificar el Token (Verificar Firma)</h2>";

    // Intenta decodificar (verificar) el token
    $decoded = JWT::decode($jwt, new \Firebase\JWT\Key($key, 'HS256'));
    
    echo "✅ **Decodificación exitosa.** <br>";
    echo "Payload decodificado: <pre>" . print_r((array)$decoded->data, true) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ **ERROR:** Parece que hay un problema con la instalación o la librería no se pudo cargar. <br>";
    echo "Mensaje de error: " . $e->getMessage();
}
?>