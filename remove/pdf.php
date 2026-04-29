<?php

include_once 'config/config.php';     
include_once 'config/database.php'; 

require 'vendor/autoload.php';
require_once 'api/functions.php';

use Dompdf\Dompdf;
use Dompdf\Options;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

    $database = new Database();
    $db = $database->getConnection();

$json = file_get_contents('php://input');
$data = json_decode($json);

$contenido ="
<style>
  body {
    width: 100%;
    margin: 0;
    padding: 0;
    font-size: 10pt; /* Los puntos son más precisos para impresión que los px */
  }
  
  h1 {
    font-size: 10pt;
  }
</style>
".$data->contrato;

// Aquí iría tu lógica de SQL (ejemplo con PDO)
// $stmt = $pdo->prepare("INSERT INTO contratos (cuerpo) VALUES (?)");
// $stmt->execute([$contenido]);

//echo json_encode(["status" => "ok"]);

$token = $data->token;
// 2. Configurar opciones (Permitir imágenes y assets remotos)
$options = new Options();
    $options->set('isRemoteEnabled', true); // Vital para cargar el LOGO desde la URL
    $options->set('isHtml5ParserEnabled', true);    
    $options->set('chroot', __DIR__);
    $options->set('defaultFont', 'Helvetica');
    $options->set('defaultDpi', 200); //
    $dompdf = new Dompdf($options);

$dompdf = new Dompdf($options);
// 3. Obtener el HTML del contrato dinámico
//$id_contrato = $_GET['Id'] ?? '';
// Usamos la URL absoluta. En Site5 sería https://tusitio.com/...
//$url = "http://localhost/dsJumpers/plantilla_contrato.html";

// Obtenemos el contenido ya renderizado por PHP
//$html = file_get_contents($url); 

// 4. Cargar el HTML en Dompdf
$dompdf->loadHtml($contenido);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
// 6. Obtener el output del PDF
$pdfOutput = $dompdf->output();
file_put_contents($token.".pdf", $pdfOutput);
/*
$data = json_encode([
    "UUID" => $token,
    "PDF" => base64_encode($pdfOutput)
]);
*/

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
            $stmt->bindValue(":uuid", $token); 
            $stmt->execute();
            $quote = $stmt->fetch(PDO::FETCH_ASSOC);

            $pdfBinary = base64_decode($pdfOutput);
            $stmt = $db->prepare("UPDATE quotes SET Contrato = ? WHERE UUID = ?");
            $stmt->execute([$pdfBinary,$token]);

            //RECUPERAR Lead
            $sql = "SELECT * FROM lead WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":id", $quote['IdQuote']); 
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);

            $query = "select * FROM organizations WHERE Id = ".$lead['Organization'];
            $stmt = $db->prepare($query);
            $stmt->execute();
            $organization = $stmt->fetch(PDO::FETCH_ASSOC);            

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
            if ($customer){
                $nombreCliente = $customer['Nombres'];
                $correoCliente =$customer['Correo'];
            }
            else{
                $nombreCliente = $organization['Nombre'];
                $correoCliente =$organization['Correo'];

            }

            $valores = [
                'company_logo'      => $account['Logo'],
                'company_name' => $account['NombreCompania'],
                'ctfirstname'  => $nombreCliente,
                'leadid'       => $lead['Folio'],
                'total'  => $lead['Total'],
                'apayment'  => $lead['DepositAmount'],
                'balancedue'  => $lead['Balance'],
                'link_to_accept'  => URL_BASE."/makepayment.php?Id=".$token."&base=".$account['WebSite'],
                'eventstreet' => $venue['Direccion'],
                'eventcity'    => $venue['Ciudad'],
                'startdate'  => $lead['StartDateTime'],
                'company_name'  => $account['NombreCompania'],
                'company_phone'  => $account['TelefonoOficina'],
                'company_city'  => $account['Ciudad'],

            ];            

            $cuerpo = generarHtmlCotizacion($cuerpo, $valores);

            $mail['correo'] = $correoCliente;
            $mail['archivo_base64'] = '';
            $mail['nombre_archivo'] = '';
            $mail['Subject'] = $header;
            $mail['Body'] = $cuerpo;
            $mail['echo'] = 'X';

            $mail = (object) $mail;            


            sendmail('',$db, 'POST', '', $mail);    








http_response_code(200);
echo json_encode(array(
    "data" => 'Ok',
    "document" => $token.".pdf",
    "UUID" => $token
));

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



?>