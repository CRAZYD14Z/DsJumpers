<?php
// ----------------------------------------------------
// 1. INCLUSIONES Y DEPENDENCIAS
// ----------------------------------------------------

// Incluye el autoloader de Composer (para JWT)
require '../vendor/autoload.php';

//include_once '../config/config.php'; 

require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key     = $_ENV['SECRET_KEY'];
$url_base       = $_ENV['URL_BASE'];
$google_api_key = $_ENV['GOOGLE_API_KEY'];

$host       = $_ENV['host'];
$db_name    = $_ENV['db_name'];
$username   = $_ENV['username'];
$password   = $_ENV['password'];
$port       = $_ENV['port'];

$CFendpoint     = $_ENV['endpoint'];
$CFkey          = $_ENV['key'];
$CFsecret       = $_ENV['secret'];
$CFpublicurl    = $_ENV['publicurl'];


define('SECRET_KEY', $secret_key);
define('URL_BASE', $url_base);
define('GOOGLE_API_KEY', $google_api_key);

define('HOST', $host);
define('USERNAME', $username);
define('PASSWORD', $password);
define('PORT',$port);

define('CFENDPOINT',$CFendpoint);
define('CFKEY',$CFkey);
define('CFSECRET',$CFsecret);
define('CFPUBLICURL',$CFpublicurl);

date_default_timezone_set('America/Mexico_City');

// Incluye la clase de conexión a la BD
include_once '../config/database.php'; 

// Incluye las librerías de JWT
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye las funciones de manejo (simuladas)
include_once 'functions.php'; 
include_once 'process_op.php'; 
include_once 'handlers.php'; 


// ----------------------------------------------------
// 2. CONFIGURACIÓN DE ENCABEZADOS (HEADERS)
// ----------------------------------------------------

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Incluir OPTIONS para CORS
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, ID2,ID3,ID4");

// Manejo de solicitudes OPTIONS (preflight requests de CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ----------------------------------------------------
// 3. INICIALIZACIÓN Y LECTURA DE LA SOLICITUD
// ----------------------------------------------------


$method = $_SERVER['REQUEST_METHOD'];

// Obtener y limpiar los segmentos de la URI (ej: /api/clientes/123 -> clientes, 123)
$request_uri = $_SERVER['REQUEST_URI'];
// Determinar la base para eliminarla de la URI
$base_path = '/api'; 
$path = trim(str_replace($base_path, '', $request_uri), '/'); 
$segments = explode('/', $path);

$resource = $segments[0]; // Ej: 'login', 'clientes', 'productos'
$id = $segments[1] ?? null; // Ej: ID si existe

if ($resource != 'process_stage_change' )
    $data = json_decode(file_get_contents("php://input"));
else $data= '';
// ----------------------------------------------------
// 4. ENRUTAMIENTO Y AUTENTICACIÓN
// ----------------------------------------------------

// --- A. LOGIN (No requiere Token) ---
if ($resource === 'login' && $method === 'POST') {
    handle_login_request( $data); // Llama a la función de login en Handlers.php
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
        // Opcional: Puedes adjuntar los datos del usuario del token a la solicitud si lo necesitas
        // $user_id = $decoded_token->data->id;
        $db_name = $decoded_token->data->base_datos;
        define('ID_CLIENTE', $decoded_token->data->id_cliente);

        $now = time();
        // Si faltan menos de 600 segundos (10 minutos) para que expire
        //echo $decoded_token->exp- $now;
        if (($decoded_token->exp - $now) < 600) {
            // Generar un nuevo token con el mismo payload
            $decoded_token->exp = time() + 3600;

            $nuevoToken = JWT::encode((array)$decoded_token, SECRET_KEY, 'HS256');
            // Enviar el nuevo token en un header para que el cliente lo actualice
            header("Authorization-Update: " . $nuevoToken);
        }        

    } catch (ExpiredException $e) {
        http_response_code(401);
        echo json_encode(["message" => "Acceso denegado. Token expirado."]);
        exit();
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Acceso denegado. Token inválido: " . $e->getMessage()]);
        exit();
    }
    


}

define('DB_NAME', $db_name);  

$database = new Database();
$db = $database->getConnection();


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


    case 'save_route':
        save_route($resource,$db, $method, $id, $data);
        break;

    case 'swap_order':
        swap_order($resource,$db, $method, $id, $data);
        break;

    case 'reschedule':
        reschedule($resource,$db, $method, $id, $data);
        break;

    case 'payment_report':
        payment_report($resource,$db, $method, $id, $data);
        break;

    case 'process_pay':
        process_pay($resource,$db, $method, $id, $data);
        break;
    case 'reassign_route';
        reassign_route($resource,$db, $method, $id, $data);
    break;      
    case 'process_operation';
        process_operation($resource,$db, $method, $id, $data);
    break;  
    case 'data_monitor':
        data_monitor($resource,$db, $method, $id, $data);
    break;  

    case 'assign_operator':
        assign_operator($resource,$db, $method, $id, $data);
    break;        

    case 'delete_route':
        delete_route($resource,$db, $method, $id, $data);
    break;        
    case 'process_stage_change_em':
        process_stage_change_em($resource,$db, $method, $id, $data);
    break;    
    case 'process_stage_change':
        process_stage_change($resource,$db, $method, $id, $data);
    break;
    case 'inventory_stock':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;            
    case 'discounts':
        handle_generic_crud($resource,$db, $method, $id, $data);
        break;    
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
    case 'get_venue':
        get_venue($resource,$db, $method, $id, $data);
        break;        
    case 'distance_charge':
        distance_charge($resource,$db, $method, $id, $data);
        break;
    case 'lead_auto_save':
        lead_auto_save($resource,$db, $method, $id, $data);
        break;
    case 'template':
        get_template($resource,$db, $method, $id, $data);
        break;
    case 'leads':
        leads($resource,$db, $method, $id, $data);
        break;
    case 'pending_payments':
        pending_payments($resource,$db, $method, $id, $data);
        break;
    case 'operation':
        operation($resource,$db, $method, $id, $data);
        break;        
    case 'get_packing_list':
        get_packing_list($resource,$db, $method, $id, $data);
        break;        
    case 'sendmail':
        sendmail($resource,$db, $method, $id, $data);
    break;
    case 'acondicionamiento':
        acondicionamiento($resource,$db, $method, $id, $data);
    break;    
    case 'cancel_lead':
        cancel_lead($resource,$db, $method, $id, $data);
    break;    
    case 'extra_event':
        extra_event($resource,$db, $method, $id, $data);
    break;   
    case 'extra_event_delete':
        extra_event_delete($resource,$db, $method, $id, $data);
    break;        
    

    default:
        // Manejar rutas no definidas
        http_response_code(404);
        echo json_encode(["message" => "Recurso '" . $resource . "' no encontrado."]);
        break;
    
}

$db = null;

?>