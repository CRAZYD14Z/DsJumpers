<?php
ob_start();
session_start(); 
include_once 'config/config.php'; 
include_once 'config/database.php'; 
require 'idioma.php'; 
header('Content-Type: application/json');

$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($usuario) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => $texts['error_vacios']]);
    exit;
}

$database = new DatabaseLogin();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT id, user, password, database_id, nombre, role_id FROM usuarios WHERE user = ? ");
$stmt->execute([$usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['usuario_id']     = $user['id'];
    $_SESSION['usuario_nombre'] = $user['nombre'];
    $_SESSION['role_id']        = $user['role_id'];

    $stmt = $db->prepare("SELECT Id, nombre_db, estatus, fecha_termino FROM data_bases WHERE Id = ?");
    $stmt->execute([$user['database_id']]);
    $SDB = $stmt->fetch(PDO::FETCH_ASSOC); 
    if ($SDB){
        $fecha_hoy = date('Y-m-d');
        $fecha_termino = $SDB['fecha_termino'];
        if ($fecha_hoy > $fecha_termino) {
            echo json_encode(['status' => 'error', 'message' =>"La cuenta no está vigente. Su acceso expiró el " . $fecha_termino ]);        
            exit(); // Detenemos la ejecución
        }    

        if ($SDB['estatus'] == 'Activo'){
            $_SESSION['nombre_db']    = $SDB['nombre_db'];
            $_SESSION['id_cliente']   = $SDB['Id'];

            $loginUrl = URL_BASE."/api/login";

            // 1. Preparar los datos
            $data = [
                'username'          => $usuario,
                'password'          => $password,
                'usuario_nombre'    => $user['nombre'],
                'role_id'           => $user['role_id'],
                'data_base'         => $SDB['nombre_db'],
                'usuario_id'        => $user['id'],
                'id_cliente'        => $SDB['Id']
            ];

            // 2. Convertir los datos a formato JSON
            $payload = json_encode($data);

            // 3. Inicializar cURL
            $ch = curl_init($loginUrl);

            // 4. Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devuelve la respuesta como string
            curl_setopt($ch, CURLOPT_POST, true);           // Define el método POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Adjunta los datos JSON
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);

            // 5. Ejecutar la petición
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // 6. Manejo de errores de cURL
            if (curl_errno($ch)) {
                echo 'Error en cURL: ' . curl_error($ch);
            } else {
                // 7. Procesar la respuesta
                if ($httpCode === 200) {
                    $result = json_decode($response, true);
                    $jwtToken = $result['jwt'] ?? null;
                                        
                    $_SESSION['apiToken'] = $jwtToken;
                    
                    //echo "Login exitoso. Token guardado en sesión.";
                    echo json_encode(['status' => 'success', 'jwtToken' => $jwtToken]);
                } else {
                    $errorResponse = json_decode($response, true);
                    $errorMessage = $errorResponse['message'] ?? 'Error desconocido.';
                    echo json_encode(['status' => 'error', 'message' => "Fallo el inicio de sesión: " . $errorMessage]);
                    
                }
            }
            // 8. Cerrar conexión
            curl_close($ch);
        }
        else{
            echo json_encode(['status' => 'error', 'message' => 'La cuenta no esta activa']);
        }
    }
    else{
        echo json_encode(['status' => 'error', 'message' => 'No existe cuenta.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => $texts['error_login']]);
}
?>