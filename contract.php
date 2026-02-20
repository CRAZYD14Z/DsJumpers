<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
$lang ='es';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang;?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato Digital Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">        
    <style>
        :root { --primary-color: #0d6efd; }
        body { background-color: #f4f7f9; min-height: 100vh; }
        
        /* Contenedor principal para Fullscreen */
        #main-container { background-color: #f4f7f9; padding: 20px; transition: all 0.3s; }

        /* Indicador flotante */
        #scroll-indicator {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: rgba(13, 110, 253, 0.9);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            pointer-events: none;
            transition: opacity 0.5s ease;
        }

        .contract-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .signature-area {
            border-top: 2px solid #eee;
            margin-top: 50px;
            padding-top: 30px;
        }

        .canvas-container {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            background: #fafafa;
            height: 250px;
            touch-action: none;
        }

        canvas { width: 100%; height: 100%; cursor: url('https://img.icons8.com/ios-filled/20/000000/edit--v1.png'), auto; }
    </style>

<style>
        @media only screen and (max-width: 600px) {
            .responsive-table { width: 100% !important; }
            .stack-column { display: block !important; width: 100% !important; box-sizing: border-box; border: none !important; }
            .item-description { width: auto !important; }
            .policy-columns { column-count: 1 !important; }
            .signature-box { width: 100% !important; margin-bottom: 20px; }
            .total-table { width: 100% !important; }
        }
    </style>

</head>
<body>

<?php
    if (!isset($_GET['Id'])){
        echo "Enlace no válido.";
        die();
    }

    $token = $_GET['Id']; // El UUID de la URL
    $ahora = date("Y-m-d H:i:s");

    $stmt = $db->prepare("SELECT * FROM quotes WHERE UUID = ? AND Status = 'A'");
    $stmt->execute([$token]);
    $cotizacion = $stmt->fetch();

    if ($cotizacion) {
        // Verificar si la fecha actual es mayor a la de expiración
        if ($ahora > $cotizacion['ExpDate']) {
            echo "Lo sentimos, esta cotización ha caducado el " . $cotizacion['ExpDate']." $ahora";
            die();
        }
    } else {
        echo "Enlace no válido.";
        die();
    }
?>

<div id="scroll-indicator">
    <span>⬇ Deslice hacia abajo para leer y firmar</span>
</div>

<div id="main-container" class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="contract-content">
                <h1 class="text-center mb-5">Contrato</h1>
                
                <div id="Contract">


                    <?php
                        $query = "select Template FROM document_center WHERE Tipo = 'contract' AND IdTemplate = 2 AND Activo = 1 AND Idioma ='$lang'";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $Template = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $Template['Template'];
                    ?>                


                </div>

                <div class="signature-area" id="signature-section">
                    <h4 class="mb-4">Aceptación y Firma</h4>
                    <form id="signature-form">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Firma Digital</label>
                            <div class="canvas-container">
                                <canvas id="signature-pad"></canvas>
                            </div>
                            <div class="mt-2 d-flex justify-content-between">
                                <small class="text-muted">Use su ratón o dedo para firmar arriba</small>
                                <button type="button" id="clear-btn" class="btn btn-link btn-sm text-danger text-decoration-none">Borrar firma</button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Nombre Completo del Firmante</label>
                            <input type="text" id="signer-name" class="form-control form-control-lg" placeholder="Escriba su nombre completo" required>
                        </div>                        

                        <div class="d-grid gap-2 mt-5">
                            <button type="submit" class="btn btn-primary btn-lg">Validar y Enviar Contrato</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    $(document).ready(function() {
        // 1. Inicializar Signature Pad
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }

        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        // 2. Control del Indicador de Scroll
        $(window).scroll(function() {
            // Si el usuario llega a la sección de firma (o cerca de ella), ocultar indicador
            const signatureOffset = $('#signature-section').offset().top - window.innerHeight;
            if ($(window).scrollTop() > signatureOffset) {
                $('#scroll-indicator').css('opacity', '0');
            } else {
                $('#scroll-indicator').css('opacity', '1');
            }
        });

        // 3. Botón Limpiar
        $('#clear-btn').click(function() {
            signaturePad.clear();
        });

        // 4. Envío de Formulario
        $('#signature-form').submit(function(e) {
            e.preventDefault();
            if (signaturePad.isEmpty()) {
                alert("Por favor, firme el documento antes de continuar.");
                return;
            }
            
            const data = {
                nombre: $('#signer-name').val(),
                firma: signaturePad.toDataURL()
            };
            
            console.log("Datos capturados:", data);
            //alert("¡Contrato firmado con éxito por " + data.nombre + "!");
            const dataURL = signaturePad.toDataURL();

            $('#img-firma-tabla').attr('src', dataURL).show();

            //const contenidoDiv = document.getElementById('Contract').innerHTML;
            //contenidoDiv = contenidoDiv.replace("*signeddate*", "<?php echo date('Y-m-d')?>"); 
            //contenidoDiv = contenidoDiv.replace("*customerdsname*", $('#signer-name').val()); 
            //$('#Contract').html(Contract);
            let contenido = $('#Contract').html(); 

            // 2. Ejecutamos los reemplazos (encadenados para mayor limpieza)
            contenido = contenido.replace("*signeddate*", "<?php echo date('Y-m-d')?>")
                                .replace("*customerdsname*", $('#signer-name').val()); 

            // 3. Volvemos a insertar el contenido procesado en el div
            $('#Contract').html(contenido);            

            const contenidoDiv = document.getElementById('Contract').innerHTML;            

            const datos = { 
                token: '<?php echo $token;?>',
                contrato: contenidoDiv 
            };

            fetch('pdf.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            })
            .then(response => response.json())
            .then(data => {
                //console.log('Éxito:', data);
                //alert('Contrato guardado correctamente ' + data.UUID);
                var url = "makepayment.php?Id="+data.UUID;
                $(location).attr('href', url);
            })
            .catch((error) => {
                console.error('Error:', error);
            });            


        });

        LoadDocument();

        

    });


function LoadDocument(){

    <?php
        $query = "select NombreCompania, Direccion,Direccion2, Ciudad,CP,Estado,Pais,TelefonoCelular FROM account";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "select * FROM lead WHERE Id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $cotizacion['IdQuote']);
        $stmt->execute();
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);    

        $query = "select * FROM lead_detail WHERE IdLead = ".$lead['Id']." ORDER BY Id";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $lead_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "select * FROM customers WHERE Id = ".$lead['Customer'];
        $stmt = $db->prepare($query);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);        

        $query = "select * FROM organizations WHERE Id = ".$lead['Organization'];
        $stmt = $db->prepare($query);
        $stmt->execute();
        $organization = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "select * FROM venues WHERE Id = ".$lead['Venue'];
        $stmt = $db->prepare($query);
        $stmt->execute();
        $venue = $stmt->fetch(PDO::FETCH_ASSOC);                


    ?>

const FHI = '<?php echo $lead['StartDateTime']?>';
const FHF = '<?php echo $lead['EndDateTime']?>';
const FHIp = FHI.split(' ')
const FHFp = FHF.split(' ')


    const datosGenerales = {
        leadid: "",
        contractsentdate: "",
        company_name: "<?php echo $account['NombreCompania']?>",
        company_address:"<?php echo $account['Direccion']." ".$account['Direccion2'];?>",
        company_city:"<?php echo $account['Ciudad']." ".$account['CP'];?>",
        company_state:"<?php echo $account['Estado'];?>",
        company_phone:"<?php echo $account['TelefonoCelular']?>",

        organization:"<?php echo isset($organization['Nombre']) ? $organization['Nombre'] : '' ?>",
        ctfirstname:"<?php echo isset($customer['Nombres']) ? $customer['Nombres'] : '' ?>",
        ctlastname:"<?php echo isset($customer['Apellidos']) ? $customer['Apellidos'] : '' ?>",
        eventstreet:"<?php echo $venue['Direccion']?>",
        eventcity:"<?php echo $venue['Ciudad']?>",
        eventstate:"<?php echo $venue['Estado']?>",
        eventzip:"<?php echo $venue['CP']?>",
        phones:"<?php echo $venue['Telefono']?>",
        startdate: FHIp[0],
        starttime: FHIp[1],
        enddate: FHFp[0],
        endtime: FHFp[1],
        deliverytype:"ON SITE",

        itemtotals: "<?php echo $lead['ItemTotals']?>",
        distanecharges: "<?php echo $lead['DistanceCharges']?>",
        staffcosts: "<?php echo $lead['StafCost']?>",
        discount: "<?php echo $lead['Discount']?>",

        subtotal: "<?php echo $lead['SubTotal']?>",
        taxexcempt: "<?php echo $lead['TaxId']?>",
        taxrate: "<?php echo $lead['TaxPc']?>",
        salestax: "<?php echo $lead['TaxAmount']?>",
        tip: "<?php echo $lead['Tip']?>",
        total: "<?php echo $lead['Total']?>",
        apayment: "<?php echo $lead['DepositAmount']?>",
        ctr_balance_due: "<?php echo $lead['Balance']?>",

        electric:""
    };

    const productos = [];
    const descuentos = [];

<?php

    if ($lead_details) {
        foreach ($lead_details as $lead_detail) {
            $query = "select * FROM products WHERE Id = ". $lead_detail['IdProduct'];
            $stmt = $db->prepare($query);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);  
            
            $query = "SELECT *  from products_images WHERE Product = ". $lead_detail['IdProduct']." ORDER BY Orden LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $Images = $stmt->fetch(PDO::FETCH_ASSOC);     

            echo "
                let item".$lead_detail['Id']." = {
                    rentalname_url_photo: 'ajax/tmp/".$Images['Image']."',
                    rentalname: '".$product['Name']."',
                    fullrentaltime: '',
                    rentalqty: '".$lead_detail['Quantity']."',
                    rentaltotalprice: '".$lead_detail['Price']."'
                };
                productos.push(item".$lead_detail['Id'].");
            ";

        }
    }

    $query = "
    SELECT
        lead_discounts.Id, 
        lead_discounts.IdLead, 
        lead_discounts.IdDiscount, 
        discounts.`Name`, 
        lead_discounts.Type, 
        lead_discounts.Amount, 
        lead_discounts.AmountVal
    FROM
        lead_discounts
        INNER JOIN
        discounts
        ON 
            lead_discounts.IdDiscount = discounts.Id        
    WHERE lead_discounts.IdLead = ".$lead['Id']." ORDER BY lead_discounts.Id";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);    

    if ($discounts) {
        foreach ($discounts as $discount) {
            echo "
            let descuento".$discount['Id']." = {
                concepto: '".$discount['Name']."',
                monto:'".$discount['AmountVal']."'
            };
            descuentos.push(descuento".$discount['Id'].");";
        }
    }          

?>


    const $contenedor = $('#contrato-dsj');
    const $cuerpoTabla = $('#lista-productos');
    const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();
    ejecutarRenderizadoContract($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos);

        <?php 
            if ($lead['Tip'] > 0){
                echo"
                    $('#tips').show();
                ";
            }
            else{
                echo"
                    $('#tips').hide();
                ";
            }
        ?>    

}    

function ejecutarRenderizadoContract($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos) {
    // 1. Limpiar productos previos (excepto la plantilla)
    $cuerpoTabla.find('tr:not(.item-fila)').remove();

    // 2. Procesar y agregar cada producto
    productos.forEach(producto => {
        let nuevaFilaHtml = $filaPlantilla[0].outerHTML;
        $.each(producto, function(key, val) {
            let regex = new RegExp('\\*' + key + '\\*', 'g');
            nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
        });
        
        let $nuevaFila = $(nuevaFilaHtml).removeClass('item-fila').css('display', ''); // Quitar display:none
        $cuerpoTabla.append($nuevaFila);
    });


    const $contenedorDescuentos = $contenedor.find('#extra_discounts');
    const $filaOriginal = $contenedorDescuentos.find('tr').first();

    if ($filaOriginal.length > 0) {
        const htmlPlantillaDesc = $filaOriginal[0].outerHTML;
        $contenedorDescuentos.empty(); // Limpiamos después de copiar la plantilla

        descuentos.forEach(desc => {
            let filaDescHtml = htmlPlantillaDesc
                .replace('*conceptdiscount*', desc.concepto)
                .replace('*discountconcept*', desc.monto);
            $contenedorDescuentos.append(filaDescHtml);
        });
    }    


    // 3. Lógica para ocultar IDs si el valor es 0
    $.each(datosGenerales, function(key, val) {
        // Buscamos el elemento que tenga el ID igual a la 'key'
        let $elemento = $contenedor.find('#' + key);
        
        if (val === 0 || val === "0") {
            $elemento.hide(); // Oculta el elemento si es cero
        } else {
            $elemento.show(); // Se asegura de mostrarlo si tiene valor
        }

        // 4. Reemplazar etiquetas en el HTML (Mantenemos tu lógica de reemplazo)
        let regex = new RegExp('\\*' + key + '\\*', 'g');
        let contenidoActual = $contenedor.html();
        $contenedor.html(contenidoActual.replace(regex, val ?? ''));
    });

}



</script>

</body>
</html>