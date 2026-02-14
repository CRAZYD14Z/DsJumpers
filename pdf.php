<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


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

// (Opcional) Configurar tamaño de papel
   $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 6. Obtener el output del PDF
    $pdfOutput = $dompdf->output();
    file_put_contents("Contrato_Nvo.pdf", $pdfOutput);
?>