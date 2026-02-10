<?php
// ----------------------------------------------------
// 1. INCLUSIONES Y DEPENDENCIAS
// ----------------------------------------------------

// Incluye el autoloader de Composer (para JWT)
require '../vendor/autoload.php';

// Incluye las configuraciones globales (SECRET_KEY, DB_USER, etc.)
include_once '../config/config.php'; 

// Incluye la clase de conexión a la BD
include_once '../config/database.php'; 

// Incluye las librerías de JWT
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;

// Incluye las funciones de manejo (simuladas)
include_once 'handlers.php'; 

// ----------------------------------------------------
// 2. CONFIGURACIÓN DE ENCABEZADOS (HEADERS)
// ----------------------------------------------------

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Incluir OPTIONS para CORS
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejo de solicitudes OPTIONS (preflight requests de CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ----------------------------------------------------
// 3. INICIALIZACIÓN Y LECTURA DE LA SOLICITUD
// ----------------------------------------------------

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

// Obtener y limpiar los segmentos de la URI (ej: /api/clientes/123 -> clientes, 123)
$request_uri = $_SERVER['REQUEST_URI'];
// Determinar la base para eliminarla de la URI
$base_path = '/api'; 
$path = trim(str_replace($base_path, '', $request_uri), '/'); 
$segments = explode('/', $path);

$resource = $segments[1]; // Ej: 'login', 'clientes', 'productos'
$id = $segments[2] ?? null; // Ej: ID si existe
// ----------------------------------------------------
// 4. ENRUTAMIENTO Y AUTENTICACIÓN
// ----------------------------------------------------

// --- A. LOGIN (No requiere Token) ---
if ($resource === 'login' && $method === 'POST') {
    handle_login_request($db, $data); // Llama a la función de login en Handlers.php
    exit();
} 

// --- B. MIDDLEWARE DE AUTENTICACIÓN (Para todas las demás rutas) ---
if ($resource !== 'login') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["message" => "$authHeader Acceso denegado. Token no proporcionado o formato incorrecto."]);
        exit();
    }

    $jwt = $matches[1];
    $decoded_token = null;

    try {
        // La validación de la firma y la expiración ocurren aquí
        $decoded_token = JWT::decode($jwt, new Key(SECRET_KEY, 'HS256'));
        // El token es válido. La información del usuario está en $decoded_token
        
    } catch (ExpiredException $e) {
        http_response_code(401);
        echo json_encode(["message" => "Acceso denegado. Token expirado."]);
        exit();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Acceso denegado. Token inválido: " . $e->getMessage()]);
        exit();
    }
    
    // Opcional: Puedes adjuntar los datos del usuario del token a la solicitud si lo necesitas
    // $user_id = $decoded_token->data->id;
}


// --- C. ENRUTAMIENTO CRUD PROTEGIDO ---
$IDS='';
unset($IDS);
if (isset($_SERVER['HTTP_ID2']))
    $IDS[] = $_SERVER['HTTP_ID2'] ?? '';
if (isset($_SERVER['HTTP_ID3']))
    $IDS[] = $_SERVER['HTTP_ID3'] ?? '';
if (isset($_SERVER['HTTP_ID4']))
    $IDS[] = $_SERVER['HTTP_ID4'] ?? '';
if (isset($_SERVER['HTTP_ID5']))
    $IDS[] = $_SERVER['HTTP_ID5'] ?? '';
//print_r($IDS);
//die();
switch ($resource) {
    case 'clientes':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'customers':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'categories':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break; 
    case 'customer_type':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'wharehouses':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break; 
    case 'gifcard':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products_categories':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products_images':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products_files':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'packing_list':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'related_products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;                
    case 'distance_charges':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'distance_charges_zip_code':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'distance_charges_distance':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'distance_charges_states':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'distance_charges_totals':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'account':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        
    case 'venues':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break; 
    case 'organizations':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'surfaces':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'upselling_products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;    
    case 'relationship_products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'cost_products':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'item_prices':
        
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'products_item_price':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'customer_addresses':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;
    case 'document_center':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;        

    case 'price_lists':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;

    case 'detail_price_lists':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;

    case 'get_price':
        get_price($resource,$db, $method, $id, $data);
        break;
    case 'get_json_price':
        get_json_price($resource,$db, $method, $id, $data);
        break;
    case 'clone_record':
        clone_record($resource,$db, $method, $id, $data);
        break;
    case 'copy_records':
        copy_records($resource,$db, $method, $id, $data);
        break;  
    case 'orden':
        orden($resource,$db, $method, $id, $data);
        break;
    case 'ajustar_precio':
        ajustar_precio($resource,$db, $method, $id, $data);
        break;                        
    case 'get_products_categories':
        get_products_categories($resource,$db, $method, $id, $data);
        break; 
    case 'get_related_products':
        get_related_products($resource,$db, $method, $id, $data);
        break;
    case 'get_organization':
        get_organization($resource,$db, $method, $id, $data);
        break;
    case 'save_organization':
        save_organization($resource,$db, $method, $id, $data);
        break;        
    case 'get_customers':
        get_customers($resource,$db, $method, $id, $data);
        break;
    case 'save_customer':
        save_customer($resource,$db, $method, $id, $data);
        break;        

    case 'save_venue':
        save_venue($resource,$db, $method, $id, $data);
        break;                

    case 'get_referals':
        get_referals($resource,$db, $method, $id, $data);
        break;        
    case 'get_venues':
        get_venues($resource,$db, $method, $id, $data);
        break;
    case 'distance_charge':
        distance_charge($resource,$db, $method, $id, $data);
        break;
    case 'lead_auto_save':
        lead_auto_save($resource,$db, $method, $id, $data);
        break;        
    default:
        // Manejar rutas no definidas
        http_response_code(404);
        echo json_encode(["message" => "Recurso '" . $resource . "' no encontrado."]);
        break;
    case 'template':
        get_template($resource,$db, $method, $id, $data);
        break;        
}

?>