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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once '../api/functions.php'; 

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
// 3. QUE BASE DE DATOS USAR
// ----------------------------------------------------

/*
$key = "tu_clave_secreta_super_segura";
$headers = getallheaders();

try {
    // 1. Validar Token
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    
    $clienteId = $decoded->cliente_id;

    // 2. Consultar DB Principal para saber a qué base de datos ir
    $masterHost = 'localhost';
    $masterUser = 'root';
    $masterPass = '';
    $masterDB   = 'db_principal';
    
    $pdoMaster = new PDO("mysql:host=$masterHost;dbname=$masterDB", $masterUser, $masterPass);
    $stmt = $pdoMaster->prepare("SELECT nombre_db FROM clientes WHERE id = ?");
    $stmt->execute([$clienteId]);
    $configCliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$configCliente) throw new Exception("Cliente no configurado.");

    // 3. Conexión DINÁMICA a la base de datos del cliente
    $dbNombreCliente = $configCliente['nombre_db'];
    $pdoCliente = new PDO("mysql:host=$masterHost;dbname=$dbNombreCliente", $masterUser, $masterPass);

    // 4. Consultar Productos
    $query = $pdoCliente->query("SELECT id, nombre, precio FROM productos");
    $productos = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "cliente_contexto" => $dbNombreCliente,
        "data" => $productos
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => $e->getMessage()]);
}
*/


    $key = "8gT!sFpQ2@vR9aL4uW7jY$0xKzC3hB6mO1eN5iZ&";
    $headers = getallheaders();


    // 1. Validar Token
    $authHeader = $headers['Authorization'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    
    $clienteId = $decoded->cliente_id;

    //die($clienteId);
// ----------------------------------------------------
// 4. INICIALIZACIÓN Y LECTURA DE LA SOLICITUD
// ----------------------------------------------------

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

// Obtener y limpiar los segmentos de la URI (ej: /api/clientes/123 -> clientes, 123)
$request_uri = $_SERVER['REQUEST_URI'];
// Determinar la base para eliminarla de la URI
$base_path = '/api-web'; 
$path = trim(str_replace($base_path, '', $request_uri), '/'); 
$segments = explode('/', $path);

$resource = $segments[0]; // Ej: 'login', 'clientes', 'productos'
$id = $segments[1] ?? null; // Ej: ID si existe


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
//die($resource);
switch ($resource) {
    case 'discounts':
        get_discounts($resource,$db, $method, $id, $data);
    break;    
    case 'products':
        products($resource,$db, $method, $id, $data);
    break;
    case 'categories':
        categories($resource,$db, $method, $id, $data);
    break;
    case 'surfaces':
        surfaces($resource,$db, $method, $id, $data);
    break;    
    case 'products_categories':
        products_categories($resource,$db, $method, $id, $data);
    break;
    case 'get_accesories':
        get_accesories($resource,$db, $method, $id, $data);
    break;    
    case 'process_quote':
        process_quote($resource,$db, $method, $id, $data);
    break;
    case 'account':
        account($resource,$db, $method, $id, $data);
    break;   
    case 'sendmail':
        sendmail($resource,$db, $method, $id, $data);
    break;
    case 'sendbook':
        sendbook($resource,$db, $method, $id, $data);
    break;
    case 'cart_update':
        cart_update($resource,$db, $method, $id, $data);
    break;    
    default:
        // Manejar rutas no definidas
        http_response_code(404);
        echo json_encode(["message" => "Recurso '" . $resource . "' no encontrado."]);
        break;
    
}
function account($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $sql = "SELECT * FROM account";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $account = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "account"=>$account
            ]);
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}            


function sendbook($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
            $sql = "SELECT * FROM account ";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);                

            //RECUPERAR PLANTILLA
            $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '7' AND Idioma = 'es'";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $Template = $stmt->fetch(PDO::FETCH_ASSOC);

            //RECUPERAR Quote
            $sql = "SELECT IdQuote FROM quotes WHERE UUID = :uuid";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":uuid", $data->UUID); 
            $stmt->execute();
            $quote = $stmt->fetch(PDO::FETCH_ASSOC);

            $pdfBinary = base64_decode($data->PDF);
            $stmt = $db->prepare("UPDATE quotes SET Contrato = ? WHERE UUID = ?");
            $stmt->execute([$pdfBinary,$data->UUID]);

            //RECUPERAR Lead
            $sql = "SELECT * FROM lead WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $quote['IdQuote']); 
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);

            //RECUPERAR Customer
            $sql = "SELECT * FROM customers WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $lead['Customer']); 
            $stmt->execute();
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            //RECUPERAR venue
            $sql = "SELECT * FROM venues WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $lead['Venue']); 
            $stmt->execute();
            $venue = $stmt->fetch(PDO::FETCH_ASSOC);            


            $header = "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: text/html; charset=UTF-8\r\n";
            $header .= $Template['Nombre']."\r\n";            
                        // Incluimos el teléfono en el cuerpo del correo
            $cuerpo = "<html>".$Template['Template']."</html>";

            $valores = [
                'company_logo'      => $account['Logo'],
                'company_name' => $account['NombreCompania'],
                'ctfirstname'  => $customer['Nombres'],
                'leadid'       => $lead['Folio'],
                'total'  => $lead['Total'],
                'apayment'  => $lead['DepositAmount'],
                'balancedue'  => $lead['Balance'],
                'link_to_accept'  => URL_BASE."/makepayment.php?Id=".$data->UUID."&base=".$account['WebSite'],
                'eventstreet' => $venue['Direccion'],
                'eventcity'    => $venue['Ciudad'],
                'startdate'  => $lead['StartDateTime'],
                'company_name'  => $account['NombreCompania'],
                'company_phone'  => $account['TelefonoOficina'],
                'company_city'  => $account['Ciudad'],

            ];            

            $cuerpo = generarHtmlCotizacion($cuerpo, $valores);

            $mail['correo'] = $customer['Correo'];
            $mail['archivo_base64'] = '';
            $mail['nombre_archivo'] = '';
            $mail['Subject'] = $header;
            $mail['Body'] = $cuerpo;
            $mail['echo'] = 'X';

            $mail = (object) $mail;            


            sendmail('',$db, 'POST', '', $mail);            

            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
}            

function sendmail($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
        try{
            $contenidoBinario = base64_decode($data->archivo_base64);
            $nombreArchivo = $data->nombre_archivo;

            $sql = "SELECT * FROM account";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            $datosConexion = [
                'host'             => $account['ServidorS'],
                'username'         => $account['UsuarioS'],
                'password'         => $account['PasswordS'],
                'port'             => $account['PortS'],
                'encryption'       => PHPMailer::ENCRYPTION_SMTPS,
                'nombre_remitente' => $account['NombreCompania']
            ];
            $archivos = [];

            $resultado = enviarEmail(
                $datosConexion, 
                $data->correo, 
                $data->Subject,
                $data->Body,
                $archivos,
                $contenidoBinario,
                $nombreArchivo
            );            
            if (isset($data->echo))
                return;
            http_response_code(200);
            echo json_encode([
                "send" => true,
                "status" => $resultado['status'],
                "message"=>$resultado['message']." ".$data->correo
            ]);
        } catch (PDOException $e) {
            http_response_code(405);
            echo json_encode([
                "send" => false,
                "status" => 'fail',
                "message"=>$e->getMessage()
            ]);
        }
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
}

function products($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            
            $sql = "SELECT * FROM products WHERE Name = :name";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($productos as $product) {
                $sql = "SELECT Image FROM products_images WHERE Product = :idproduct AND Orden = 1";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct", $product['Id']); 
                $stmt->execute();
                $Image = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            foreach ($productos as $product) {
                $sql = "SELECT Image FROM products_images WHERE Product = :idproduct ORDER BY Orden";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $Images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }            

            foreach ($productos as $product) {
                $sql = "SELECT Producto_rp, Producto_r, Image, 'Nombre producto extra' as Name, '122' as Price, '0' as Quantity  FROM v_related_products WHERE Producto_rp = :idproduct ";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $Accesories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if ($Accesories) {
                foreach ($Accesories as $index =>$Acc ) {
                    $resultados_p = precios($data,$Accesories[$index]['Producto_r']);
                    if ($resultados_p) {
                        $Accesories[$index]['Price'] = $resultados_p[0]['Price'];
                        $Accesories[$index]['Name'] = $resultados_p[0]['ProductName'];
                        $Accesories[$index]['Quantity'] = $resultados_p[0]['Quantity'];
                    }
                }   
            }         

            foreach ($productos as $product) {
                $sql = "
                    SELECT
                        v_upselling_products.Producto_up, 
                        v_upselling_products.Producto_rup, 
                        v_upselling_products.`Name`, 
                        products_images.Orden, 
                        products_images.Image,
                        '0' as Price,
                        '0' as Quantity 
                    FROM
                        v_upselling_products
                        left outer JOIN
                        products_images
                        ON 
                            v_upselling_products.Producto_rup = products_images.Product
                        WHERE Orden = 1 AND Producto_up = :idproduct";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$product['Id']); 
                $stmt->execute();
                $UpSelling = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }                 

            if ($UpSelling) {
                foreach ($UpSelling as $index =>$Ups ) {
                    $resultados_p = precios($data,$UpSelling[$index]['Producto_rup']);
                    if ($resultados_p) {
                        $UpSelling[$index]['Price'] = $resultados_p[0]['Price'];
                        $UpSelling[$index]['Name'] = $resultados_p[0]['ProductName'];
                        $UpSelling[$index]['Quantity'] = $resultados_p[0]['Quantity'];
                    }
                }   
            }   




            //$DateS_raw = isset($_GET['DateS']) ? $_GET['DateS'] : date('Y-m-d\TH:i');
            //$DateE_raw = isset($_GET['DateE']) ? $_GET['DateE'] : date('Y-m-d\TH:i');




            $resultados_p = precios($data,$product['Id']);



            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($productos),
                "data" => $productos,
                "Image" => $Image,
                "Images" => $Images,
                "Accesories" => $Accesories,
                "UpSelling"=>$UpSelling,
                "Resultadosp"=>$resultados_p

            ]);
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function get_discounts($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 

            // 1. Definimos el SQL como un simple string (texto)
            $sql = "select * FROM discounts WHERE DateExp > now() AND Active = 1 AND (Used < Quantity OR Unlimited = 1) ORDER BY Name";

            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);

            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 

            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();

            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "data" => $discounts
            ]);
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function categories($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            // 1. Definimos el SQL como un simple string (texto)
            $sql = "SELECT Id, Nombre, Imagen FROM categories ";

            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);

            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 

            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();

            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($productos),
                "data" => $productos
            ]);
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function surfaces($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            // 1. Definimos el SQL como un simple string (texto)
            $sql = "SELECT Id, Nombre FROM surfaces ";

            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);

            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            //$stmt->bindValue(":name", $data->Product); 

            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();

            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $surfaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($surfaces),
                "data" => $surfaces
            ]);
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}


function products_categories($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 
/*
            // 1. Definimos el SQL como un simple string (texto)
            $sql = "            
                SELECT
                    v_products_categories.Category, 
                    products_images.Product as IdProduct, 
                    products.`Name` as ProductName,
                    products_images.Image
                FROM
                    v_products_categories
                    INNER JOIN
                    products_images
                    ON 
                        v_products_categories.Product = products_images.Product
                    INNER JOIN
                    products
                    ON 
                        products_images.Product = products.Id
                WHERE
                    v_products_categories.Nombre = :name AND
                    products_images.Orden = 1            
            
            ";

            // 2. Preparamos la consulta
            $stmt = $db->prepare($sql);

            // 3. Vinculamos el valor (asegúrate que $data->Product exista)
            $stmt->bindValue(":name", $data->Category); 

            // 4. EJECUTAMOS la consulta (Paso vital que faltaba)
            $stmt->execute();

            // 5. Obtenemos los resultados desde el $stmt, no desde $db ni $query
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
*/
            $sql = 'SELECT Id FROM categories WHERE Nombre = :name';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":name", $data->Category);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            $IdCat = $category['Id'];

            $DateS_raw = isset($data->SD) ? $data->SD."T".$data->SH : date('Y-m-d\TH:i');
            $DateE_raw = isset($data->ED) ? $data->ED."T".$data->EH  : date('Y-m-d\TH:i');

            $objDateS = new DateTime($DateS_raw);
            $objDateE = new DateTime($DateE_raw);

            $fechaS = $objDateS->format('Ymd'); // 2026-02-04
            $horaS  = $objDateS->format('H:i');   // 18:00

            $fechaE = $objDateE->format('Ymd'); // 2026-02-05
            $horaE  = $objDateE->format('H:i');   // 02:00

            $DayWeek = date('w', strtotime($fechaS));

            switch ($DayWeek) {
                case '0':
                    $DayWeek =' AND Do = 1 ';
                break;
                case '1':
                    $DayWeek =' AND Lu = 1 ';
                break;
                case '2':
                    $DayWeek =' AND Ma = 1 ';
                break;
                case '3':
                    $DayWeek =' AND Mi = 1 ';
                break;
                case '4':
                    $DayWeek =' AND Ju = 1 ';
                break;
                case '5':
                    $DayWeek =' AND Vi = 1 ';
                break;
                case '6':
                    $DayWeek =' AND Sa = 1 ';
                break;
            }

            //RECUPERAR TODO EL DETALLE DE EVENTOS ACTIVOS DE ESTA FECHA PARA RESTAR LAS CANTIDADES DE LOS PRODUCTOS

            $fechaS_db = $objDateS->format('Y-m-d H:i:s');
            $fechaE_db = $objDateE->format('Y-m-d H:i:s');

            $query = "
                SELECT IdProduct, SUM(Quantity) as Quantity 
                FROM v_leads_detail 
                WHERE Status = 'quoted' 
                AND (StartDateTime < :DateE AND EndDateTime > :DateS)
                AND Unlimited = 0
                GROUP BY IdProduct
                
                UNION

                SELECT
                    relationship_products.Producto_rsp as IdProduct, 
                    count(relationship_products.Producto_rsp) as Quantity
                FROM
                    v_leads_detail
                    INNER JOIN
                    relationship_products
                    ON 
                        v_leads_detail.IdProduct = relationship_products.Producto_sp
                        
                WHERE v_leads_detail.Status = 'quoted' 
                AND (v_leads_detail.StartDateTime < :DateEE AND v_leads_detail.EndDateTime > :DateSS)
                AND v_leads_detail.Unlimited = 0
                GROUP BY relationship_products.Producto_rsp		                

            ";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':DateS', $fechaS_db);
            $stmt->bindParam(':DateE', $fechaE_db);
            $stmt->bindParam(':DateSS', $fechaS_db);
            $stmt->bindParam(':DateEE', $fechaE_db);            
            $stmt->execute();                

            $ocupados = $stmt->fetchAll(PDO::FETCH_ASSOC);                

            $cantidadesOcupadas = array_column($ocupados, 'Quantity', 'IdProduct');                

            $query = "
                SELECT * FROM v_items_prices_lists
                WHERE Category = :idCat  AND 
                            Estatus_price_list = 1 AND
                            Estatus_price = 1 AND 
                :date BETWEEN  FechaHoraInicio AND FechaHoraFin  $DayWeek                                    
            ";                            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':idCat', $IdCat, PDO::PARAM_INT);
            $stmt->bindParam(':date', $fechaS, PDO::PARAM_STR);
            //$stmt->bindValue(1, $IdCat);
            //$stmt->bindValue(2, $Date);
            $stmt->execute();
            $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados_p) {
                foreach ($resultados_p as $index => $Precio) {
                        $JsonPrice = $Precio['JsonPrice'];
                        $JsonPrice = html_entity_decode($JsonPrice);
                        $ingreso =  $objDateS->format('Y-m-d H:i:00');
                        $salida  = $objDateE->format('Y-m-d H:i:00');
                        // Modificamos directamente el arreglo usando el índice
                        $resultados_p[$index]['Price'] = calcularCostoEstanciaPHP($JsonPrice, $ingreso, $salida);
                            if (isset($cantidadesOcupadas[$resultados_p[$index]['Producto']])) {
                                $cantidadOcupada = $cantidadesOcupadas[$resultados_p[$index]['Producto']];
                            } else {
                                $cantidadOcupada = 0; // Si no está en el arreglo, nadie lo ha rentado
                            }                            
                        $resultados_p[$index]['Quantity'] = $resultados_p[$index]['Quantity'] - $cantidadOcupada;
                        $query = "SELECT *  from products_images WHERE Product = ".$resultados_p[$index]['Producto']." ORDER BY Orden LIMIT 1";
                        $stmtigm = $db->prepare($query);
                        $stmtigm->execute();
                        $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);                             
                        if ($Img)
                            $resultados_p[$index]['Image'] = $Img['Image'];
                        //if ($resultados_p[$index]['Quantity'] <= 0)
                        //    unset($resultados_p[$index]);
                    }
            }            



            // 6. Respuesta JSON
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "total" => count($resultados_p),
                "data" => $resultados_p
            ]);
        break;

        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function get_accesories($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'GET': 
            $productoId = $_GET['producto_id'] ?? 0;
            
                $sql = "SELECT Producto_rp, Producto_r, Image, 'Nombre producto extra' as Name, '122' as Price  FROM v_related_products WHERE Producto_rp = :idproduct ";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":idproduct",$productoId); 
                $stmt->execute();
                $Accesories = $stmt->fetchAll(PDO::FETCH_ASSOC);            

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "Accesories" => $Accesories
            ]);            
        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function process_quote($table_name,$db, $method, $id, $data){
    global $IDS;
    switch ($method) {
        case 'POST': 

            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
                exit;
            }            


            /*
                        $data['cliente']['nombre'],
                        $data['cliente']['apellidos'],
                        $data['cliente']['organizacion'],
                        $data['cliente']['telefono'],
                        $data['cliente']['correo'],            
                        $data['cliente']['direccion'],
                        $data['cliente']['ciudad'],
                        $data['cliente']['colonia'],
                        $data['cliente']['cp'],
            */

try {
//    $pdo = new PDO("mysql:host=localhost;dbname=tu_base_de_datos", "usuario", "password");
    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Verificar si el cliente ya existe (usualmente por Correo)
    
    $stmtCheck = $db->prepare("SELECT Id FROM customers WHERE Correo = ? LIMIT 1");
    $stmtCheck->execute([$data->cliente->correo]);
    $clienteExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($clienteExistente) {
        // --- ACTUALIZAR CLIENTE ---
        $idCliente = $clienteExistente['Id'];
        $sqlUpd = "UPDATE customers SET 
                    Nombres = ?, 
                    Apellidos = ?, 
                    NombreEmpresa = ?, 
                    TelefonoCelular = ?, 
                    Direccion = ?, 
                    Direccion2 = ?, 
                    Ciudad = ?, 
                    CP = ?,
                    FechaCambio = now() 
                   WHERE Id = ?";
        
        $stmtUpd = $db->prepare($sqlUpd);
        $stmtUpd->execute([
            $data->cliente->nombre,
            $data->cliente->apellidos ?? '', // Evitar nulls
            $data->cliente->organizacion,
            $data->cliente->telefono,
            $data->cliente->direccion,
            $data->cliente->colonia, // Usado como Direccion2
            $data->cliente->ciudad,
            $data->cliente->cp,
            $idCliente
        ]);
        
        $mensaje = "Cliente actualizado correctamente.";
    } else {
        // --- INSERTAR CLIENTE ---
        $sqlIns = "INSERT INTO customers (
                    Nombres, Apellidos, NombreEmpresa, Correo, TelefonoCelular, 
                    Direccion, Direccion2, Ciudad, CP, Pais, Lenguaje,Estatus,FechaCreacion,FechaCambio
                   ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'MX', 'es', 'A',now(),now())";
        
        $stmtIns = $db->prepare($sqlIns);
        $stmtIns->execute([
            $data->cliente->nombre,
            $data->cliente->apellidos ?? '',
            $data->cliente->organizacion,
            $data->cliente->correo,
            $data->cliente->telefono,
            $data->cliente->direccion,
            $data->cliente->colonia,
            $data->cliente->ciudad,
            $data->cliente->cp
        ]);
        
        $idCliente = $db->lastInsertId();
        //$mensaje = "Nuevo cliente registrado.";
    }

    // Ahora ya tienes el $idCliente listo para usarlo en la tabla de reservaciones
    //echo "ID del cliente: " . $idCliente;

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}            


try {
    // 1. Verificar si el lugar ya existe por Dirección y CP
    // Esto evita duplicar el mismo domicilio en la base de datos
    $stmtCheckVenue = $db->prepare("SELECT Id FROM venues WHERE Direccion = ? AND CP = ? LIMIT 1");
    $stmtCheckVenue->execute([
        $data->lugar->direccion,
        $data->lugar->cp
    ]);
    $venueExistente = $stmtCheckVenue->fetch(PDO::FETCH_ASSOC);

    if ($venueExistente) {
        // --- ACTUALIZAR LUGAR ---
        $idVenue = $venueExistente['Id'];
        $sqlUpdVenue = "UPDATE venues SET 
                        Direccion2 = ?, 
                        Ciudad = ?, 
                        Estado = ?, 
                        Pais = ? ,
                        FechaCambio = now()
                       WHERE Id = ?";
        
        $stmtUpdVenue = $db->prepare($sqlUpdVenue);
        $stmtUpdVenue->execute([
            $data->lugar->colonia, // Mapeado a Direccion2
            $data->lugar->ciudad ?? 'Guadalajara', // Valor por defecto o del array
            $data->lugar->estado ?? 'Jalisco',
            'MX',
            $idVenue
        ]);
    } else {
        // --- INSERTAR LUGAR ---
        $sqlInsVenue = "INSERT INTO venues (
                            Nombre, Direccion, Direccion2, Ciudad, CP, Estado, Pais,FechaCreacion,FechaCambio
                        ) VALUES (?, ?, ?, ?, ?, ?, ?,now(),now())";
        
        $stmtInsVenue = $db->prepare($sqlInsVenue);
        $stmtInsVenue->execute([
            'Evento de ' . $data->cliente->nombre, // Nombre descriptivo temporal
            $data->lugar->direccion,
            $data->lugar->colonia,
            $data->lugar->ciudad ?? 'Guadalajara',
            $data->lugar->cp,
            $data->lugar->estado ?? 'Jalisco',
            'MX'
        ]);
        
        $idVenue = $db->lastInsertId();
    }

    // El $idVenue ahora está listo para guardarse en la tabla 'reservaciones'
    // Ejemplo: $stmtReserva->execute([$idCliente, $idVenue, ...]);

} catch (PDOException $e) {
    die("Error al procesar el lugar: " . $e->getMessage());
}


/*
            $data['lugar']['direccion'],
            $data['lugar']['colonia'],
            $data['lugar']['cp'],
            $data['lugar']['referencias'],
            $data['lugar']['superficie'],
            $data['lugar']['tipo_entrega'],
            $data['lugar']['tax'],
            $data['lugar']['cupon'],
*/
            $Total = 0;
            
            foreach ($data->reserva->items as $item) {
                $Total+= $item->cantidad * $item->precio;
                if (!empty($item->adicionales)) {
                    foreach ($item->adicionales as $extra) {
                        $Total+= $extra->precio * $item->cantidad ;
                    }
                }
            }



            

            $Folio = 0;    
            $IdBranch = 1;
            $stmt = $db->prepare("select MAX(Folio) as Folio FROM folios WHERE IdBranch = ? AND Type = 'Lead'");
            $stmt->execute([$IdBranch]);
            $Payments = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($Payments){
                $Folio = $Payments['Folio'];
            }
            $Folio+=1;            

            
            $Cupon = $data->cupon->cupon;
            $TipoCupon = $data->cupon->tipocupon;
            $DescuentoCupon = $data->cupon->descuento;
            $MontoCupon= 0;
            //print_r($data->cupon);
            //echo $data->cupon->cupon."*";
            //foreach ($data->cupon as $cpn) {
            //    $Cupon =  $cupon->cupon;
            //    $TipoCupon =  $cupon->tipocupon;
            //    $DescuentoCupon =  $cupon->descuento;
            if ($Cupon != ""){
                if ($TipoCupon == 'percentage' ){
                    $MontoCupon = $Total * ($DescuentoCupon / 100);
                }
                else{
                    $MontoCupon = $DescuentoCupon;
                }
            }                
            //}

            $Subtotal = $Total - $MontoCupon;

/*
            $data['reserva']['fecha'],
            $data['reserva']['hInicio'],
            $data['reserva']['hFin'],
*/
                //$Total = 0;
                $fechas = explode(' to ', $data->reserva->fecha);

                $FHI = $fechas[0] .' '. $data->reserva->hInicio; 
                $FHF = $fechas[1] .' '. $data->reserva->hFin;                 
                $Organization = ''; 
                $Customer = $idCliente; 
                $Referal = '';
                $OkT = 0; 
                $WA = 0; 
                $AE = 0; 
                $ME = 0; 
                $CusNt = ''; 
                $Venue = $idVenue; 
                $EventName = ''; 
                $Surface = $data->lugar->superficie;
                $Delivety = $data->lugar->tipo_entrega; 
                $Nt1 = ''; 
                $Nt2 = ''; 
                $Item_Totals = $Total; 
                $ChkDstC = 1; 
                $DstC = 0; 
                $ChkStCs = 0; 
                $StCs = 0; 
                $ChkDsc = 0; 
                $Dsc = 0; 
                $SubT = $Subtotal; 
                $TaxId = ''; 
                $TaxPc = 0; 
                $TaxAm = 0; 
                $Total = $Subtotal; 
                $Depo = 20;
                $DepoA = ($Subtotal * (20 / 100)); 
                $BalDue = $Subtotal - ($Subtotal * (20 / 100)); 
                $Status = 'Pending';
                $IdBranch = 1;
                //$Folio  = '';


            // --- MODO INSERT --
            $sqlLead = "INSERT INTO lead (
                StartDateTime, EndDateTime, Organization, Customer, Referal, 
                OkT, WA, AE, ME, CustomerNote, Venue, EventName, Surface, 
                Delivery, Note1, Note2, ItemTotals, ChkDstC, DistanceCharges, ChkStCs, 
                StafCost, ChkDsc, Discount, SubTotal, TaxId, TaxPc, 
                TaxAmount, Total, Deposit,DepositAmount, Balance, Status,FechaCreacion,FechaCambio,IdBranch,Folio,TotalBT
            ) VALUES (?,?,?,?,?,
                    ?,?,?,?,?,
                    ?,?,?,?,?,
                    ?,?,?,?,?,
                    ?,?,?,?,?,
                    ?,?,?,?,?,?,?,now(),now(),?,?,?)";

            $stmtLead = $db->prepare($sqlLead);
            $stmtLead->execute([
                $FHI, $FHF, $Organization, $Customer, $Referal,
                $OkT, $WA, $AE, $ME, $CusNt, $Venue, $EventName, $Surface,
                $Delivety, $Nt1, $Nt2, $Item_Totals, $ChkDstC, $DstC, $ChkStCs, 
                $StCs, $ChkDsc, $Dsc, $SubT, $TaxId, $TaxPc, 
                $TaxAm, $Total, $Depo,$DepoA, $BalDue, $Status,$IdBranch,$Folio,$Total
            ]);
            $idLead = $db->lastInsertId();

            

            $stmt = $db->prepare(" UPDATE folios SET Folio = ? WHERE IdBranch = ? AND Type = 'Lead'");
            $stmt->execute([$Folio,$IdBranch]);
            $UUID = generar_uuid_v4();
            //$stmt = $db->prepare("INSERT INTO quotes (UUID,IdQuote,ExpDate,Status) VALUES (?,?,NOW() + INTERVAL '2 days',?)");
            $stmt = $db->prepare("INSERT INTO quotes (UUID,IdQuote,ExpDate,Status) VALUES (?,?, NOW() + INTERVAL 2 DAY, ?)");
            $stmt->execute([$UUID,$idLead,'A']);




            $sqlDetail = "INSERT INTO lead_detail (IdLead, IdProduct, IdProductRel, Quantity, Discount, Tax, Price, OrgPrice) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtDetail = $db->prepare($sqlDetail);


            foreach ($data->reserva->items as $item) {
                $stmtDetail->execute([
                    $idLead, 
                    $item->id, 
                    '0', 
                    $item->cantidad, 
                    '0', 
                    '0', 
                    $item->precio * $item->cantidad,
                    $item->precio
                    
                ]);                
                if (!empty($item->adicionales)) {
                    foreach ($item->adicionales as $extra) {
                        $Total+= $extra->precio * $item->cantidad ;
                        $stmtDetail->execute([
                            $idLead,
                            $item->id,
                            $extra->id,
                            $item->cantidad,
                            '0',
                            '0',
                            $extra->precio * $item->cantidad,
                            $extra->precio
                        ]);
                    }
                }
            }

        if ($Cupon!=''){

            $stmt = $db->prepare("select Id FROM discounts WHERE Code = ?");
            $stmt->execute([$Cupon]);
            $IdCupon = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("UPDATE discounts SET Used = Used + 1 WHERE Code = ?");
            $stmt->execute([$Cupon]);
            $UpdateCupon = $stmt->fetch(PDO::FETCH_ASSOC);            

            // --- 4. INSERTAR DESCUENTOS (detalle es un array de objetos) ---
            $sqlDiscounts = "INSERT INTO lead_discounts (IdLead, IdDiscount, Type, Amount,AmountVal) 
                        VALUES (?, ?, ?, ?, ?)";
            $stmtDiscounts = $db->prepare($sqlDiscounts);

            $stmtDiscounts->execute([
                $idLead, 
                $IdCupon['Id'],
                $TipoCupon, 
                $DescuentoCupon,
                $MontoCupon
            ]);

        }

        $stmt = $db->prepare("select UUID FROM quotes WHERE IdQuote = ?");
        $stmt->execute([$idLead]);
        $Quotes = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($Quotes){
            $UUID = $Quotes['UUID'];
        }

            $sql = "SELECT * FROM account ";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $account = $stmt->fetch(PDO::FETCH_ASSOC);                

            //RECUPERAR PLANTILLA
            $sql = "SELECT Nombre, Template FROM document_center WHERE Tipo = 'email' AND IdTemplate = '6' AND Idioma = 'es'";
            $stmt = $db->prepare($sql);
            //$stmt->bindValue(":name", $data->Product); 
            $stmt->execute();
            $Template = $stmt->fetch(PDO::FETCH_ASSOC);        


            $header = "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: text/html; charset=UTF-8\r\n";
            $header .= $Template['Nombre']."\r\n";            
                        // Incluimos el teléfono en el cuerpo del correo
            $cuerpo = "<html>".$Template['Template']."</html>";

            $valores = [
                'company_logo'      => $account['Logo'],
                'company_name' => $account['NombreCompania'],
                'ctfirstname'  => $data->cliente->nombre,
                'eventcity'    => $data->lugar->ciudad,
                'leadid'       => $Folio,
                'startdate'  => $FHI,
                'total'  => $Total,
                'link_to_accept'  => $account['WebSite'].'quote.php?Id='.$UUID,
                'company_name'  => $account['NombreCompania'],
                'company_phone'  => $account['TelefonoOficina'],
                'company_city'  => $account['Ciudad'],

            ];            

            $cuerpo = generarHtmlCotizacion($cuerpo, $valores);

            $mail['correo'] = $data->cliente->correo;
            $mail['archivo_base64'] = '';
            $mail['nombre_archivo'] = '';
            $mail['Subject'] = $header;
            $mail['Body'] = $cuerpo;
            $mail['echo'] = 'X';

            $mail = (object) $mail;            


            sendmail('',$db, 'POST', '', $mail);

        http_response_code(200);
        echo json_encode(["status" => "success", "IdLead" => $idLead, "UUID" => $UUID]);


        break;
        default:
        // ------------------------------------------------------------------
            http_response_code(405);
            echo json_encode(array("message" => "Método HTTP no permitido para este recurso."));
        break;
    }      
    
}

function generar_uuid_v4() {
    // Generamos 16 bytes de datos aleatorios
    $data = random_bytes(16);

    // Configuramos el bit de versión a 4 (0100)
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Configuramos los bits de variante (10xx)
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Formateamos en el estándar 8-4-4-4-12
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


function calcularCostoEstanciaPHP($jsonConfig, $inicio, $fin) {
    $configTotal = is_string($jsonConfig) ? json_decode($jsonConfig, true) : $jsonConfig;
    if (empty($configTotal)) return 0;

    $fechaInicio = new DateTime($inicio);
    $fechaFin = new DateTime($fin);
    $intervalo = $fechaInicio->diff($fechaFin);
    
    // Si la fecha fin es menor, costo 0
    if ($fechaFin < $fechaInicio) return 0;

    // Calcular horas totales (equivalente a Math.ceil)
    // Convertimos diferencia a segundos y dividimos por 3600
    $segundos = $fechaFin->getTimestamp() - $fechaInicio->getTimestamp();
    $horasReales = ceil($segundos / 3600);
    
    // REGLA: Mínimo 8 horas
    $h = max($horasReales, 8);

    $conv = [
        "hora" => 1, "horas" => 1, 
        "dia" => 24, "dias" => 24, 
        "semana" => 168, "semanas" => 168
    ];

    // Buscar configuraciones (sustituye al .find de JS)
    $f1 = null; $f2 = null; $f3 = null;
    foreach ($configTotal as $c) {
        if ($c['funcion'] === 'f1') $f1 = $c;
        if ($c['funcion'] === 'f2') $f2 = $c;
        if ($c['funcion'] === 'f3') $f3 = $c;
    }

    if (!$f1) return 0;

    $costoTotal = 0;
    $p1 = floatval($f1['precio'] ?? 0);

    // --- LÓGICA DE CÁLCULO ---
    if ($f1['tipo'] === "Indefinido") {
        $costoTotal = $p1;
    } 
    elseif ($f1['tipo'] === "Cada") {
        $t1 = (floatval($f1['tiempo'] ?? 1)) * $conv[$f1['unidad']];
        $costoTotal = $p1; // Cobro inicial

        $limiteF2 = ($f2 && $f2['tipo'] === "Hasta") 
            ? (floatval($f2['tiempo'] ?? 1)) * $conv[$f2['unidad']] 
            : PHP_INT_MAX;

        if ($h > 0) {
            if ($h < $limiteF2) {
                $costoTotal += floor($h / $t1) * $p1;
            } else {
                // Se cobra F1 hasta el límite definido por F2
                $costoTotal += floor(($limiteF2 - 1) / $t1) * $p1;
            }

            // Aplicar F3 si sobrepasa o iguala el límite de F2
            if ($h >= $limiteF2 && $f3 && $f3['tipo'] === "Cada") {
                $t3 = (floatval($f3['tiempo'] ?? 1)) * $conv[$f3['unidad']];
                $p3 = floatval($f3['precio'] ?? 0);
                
                // Cálculo de ciclos de F3 desde el punto de corte
                $costoTotal += (floor(($h - $limiteF2) / $t3) + 1) * $p3;
            }
        }
    } 
    elseif ($f1['tipo'] === "Hasta") {
        $t1 = (floatval($f1['tiempo'] ?? 1)) * $conv[$f1['unidad']];
        if ($h <= $t1) {
            $costoTotal = $p1;
        } elseif ($f3) {
            $p3 = floatval($f3['precio'] ?? 0);
            $t3 = (floatval($f3['tiempo'] ?? 1)) * $conv[$f3['unidad']];
            
            if ($f3['tipo'] === "Hasta") {
                $costoTotal = $p3;
            } elseif ($f3['tipo'] === "Cada") {
                $costoTotal = $p1 + (floor(($h - $t1) / $t3) * $p3);
            }
        } else {
            $costoTotal = $p1;
        }
    }

    return $costoTotal;
}

function precios($data,$product){

    global $db;

    //print_r($data);

    $DateS_raw = $data->SD . "T" . $data->SH;
    $DateE_raw = $data->ED . "T" . $data->EH;

    $objDateS = new DateTime($DateS_raw);
    $objDateE = new DateTime($DateE_raw);

    $fechaS = $objDateS->format('Y-m-d'); // 2026-02-04
    $horaS  = $objDateS->format('H:i');   // 18:00

    $fechaE = $objDateE->format('Y-m-d'); // 2026-02-05
    $horaE  = $objDateE->format('H:i');   // 02:00

    $DayWeek = date('w', strtotime($fechaS));

    switch ($DayWeek) {
        case '0':
            $DayWeek =' AND Do = 1 ';
        break;
        case '1':
            $DayWeek =' AND Lu = 1 ';
        break;
        case '2':
            $DayWeek =' AND Ma = 1 ';
        break;
        case '3':
            $DayWeek =' AND Mi = 1 ';
        break;
        case '4':
            $DayWeek =' AND Ju = 1 ';
        break;
        case '5':
            $DayWeek =' AND Vi = 1 ';
        break;
        case '6':
            $DayWeek =' AND Sa = 1 ';
        break;
    }

    //RECUPERAR TODO EL DETALLE DE EVENTOS ACTIVOS DE ESTA FECHA PARA RESTAR LAS CANTIDADES DE LOS PRODUCTOS

    $fechaS_db = $objDateS->format('Y-m-d H:i:s');
    $fechaE_db = $objDateE->format('Y-m-d H:i:s');

    $query = "
        SELECT IdProduct, SUM(Quantity) as Quantity 
        FROM v_leads_detail 
        WHERE Status = 'quoted' 
        AND (StartDateTime < :DateE AND EndDateTime > :DateS)
        AND Unlimited = 0
        GROUP BY IdProduct
        
        UNION

        SELECT
            relationship_products.Producto_rsp as IdProduct, 
            count(relationship_products.Producto_rsp) as Quantity
        FROM
            v_leads_detail
            INNER JOIN
            relationship_products
            ON 
                v_leads_detail.IdProduct = relationship_products.Producto_sp
                
        WHERE v_leads_detail.Status = 'quoted' 
        AND (v_leads_detail.StartDateTime < :DateEE AND v_leads_detail.EndDateTime > :DateSS)
        AND v_leads_detail.Unlimited = 0
        GROUP BY relationship_products.Producto_rsp		                

    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':DateS', $fechaS_db);
    $stmt->bindParam(':DateE', $fechaE_db);
    $stmt->bindParam(':DateSS', $fechaS_db);
    $stmt->bindParam(':DateEE', $fechaE_db);            
    $stmt->execute();                

    $ocupados = $stmt->fetchAll(PDO::FETCH_ASSOC);                
    
    $cantidadesOcupadas = array_column($ocupados, 'Quantity', 'IdProduct');                

    $query = "
        SELECT * FROM v_items_prices_lists
        WHERE Producto = :producto  AND 
                    Estatus_price_list = 1 AND
                    Estatus_price = 1 AND 
        :date BETWEEN  FechaHoraInicio AND FechaHoraFin  $DayWeek 
    ";                            
    $stmt = $db->prepare($query);
    $stmt->bindParam(':producto', $product, PDO::PARAM_INT);
    $stmt->bindParam(':date', $fechaS, PDO::PARAM_STR);
    //$stmt->bindValue(1, $IdCat);
    //$stmt->bindValue(2, $Date);
    $stmt->execute();
    $resultados_p = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //echo $product." ".$fechaS."---".$DayWeek ;
    //print_r($resultados_p);    

    if ($resultados_p) {
        foreach ($resultados_p as $index => $Precio) {
                $JsonPrice = $Precio['JsonPrice'];
                $JsonPrice = html_entity_decode($JsonPrice);
                $ingreso =  $objDateS->format('Y-m-d H:i:00');
                $salida  = $objDateE->format('Y-m-d H:i:00');
                // Modificamos directamente el arreglo usando el índice
                $resultados_p[$index]['Price'] = calcularCostoEstanciaPHP($JsonPrice, $ingreso, $salida);
                    if (isset($cantidadesOcupadas[$resultados_p[$index]['Producto']])) {
                        $cantidadOcupada = $cantidadesOcupadas[$resultados_p[$index]['Producto']];
                    } else {
                        $cantidadOcupada = 0; // Si no está en el arreglo, nadie lo ha rentado
                    }                            
                $resultados_p[$index]['Quantity'] = $resultados_p[$index]['Quantity'] - $cantidadOcupada;
                $query = "SELECT *  from products_images WHERE Product = ".$resultados_p[$index]['Producto']." ORDER BY Orden LIMIT 1";
                $stmtigm = $db->prepare($query);
                $stmtigm->execute();
                $Img = $stmtigm->fetch(PDO::FETCH_ASSOC);                             
                if ($Img)
                    $resultados_p[$index]['Image'] = $Img['Image'];
                //if ($resultados_p[$index]['Quantity'] <= 0)
                //    unset($resultados_p[$index]);
            }
    }            

    return $resultados_p;
}

function cart_update($resource,$db, $method, $id, $data){
    global $db;
    $itemsActualizados = [];
    foreach ($data->items as $item) {

        $resultados_p = precios($data,$item->id);
        //print_r($resultados_p);
        

        $itemActualizado = clone $item; // Clonar para no modificar el original
        $itemActualizado->precio = $resultados_p[0]['Price'] ?? $item->precio."*";
        $itemActualizado->existencia = $resultados_p[0]['Quantity'] ?? $item->existencia."*";
        
        $itemsActualizados[] = $itemActualizado;        
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $itemsActualizados
    ]);     

}

?>