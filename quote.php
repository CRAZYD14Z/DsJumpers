<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
    include_once 'head.php';
?>   
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

    $Tip=0;
    if (isset($_GET['Tip'])){
        $Tip=$_GET['Tip'];
    }

    $APay=20;
    if (isset($_GET['APay'])){
        $APay=$_GET['APay'];
    }    

    if ($APay != 20 AND $APay != 50 AND $APay != 100){
        echo "Anticipo  $APay no válido.";
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
    <span>⬇ Deslice hacia abajo para acetar</span>
</div>

<div id="main-container" class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="contract-content">
                <h1 class="text-center mb-5">Cotización</h1>
                
                <div id="Quote">

                    <?php
                        $query = "select Template FROM document_center WHERE Tipo = 'quote' AND IdTemplate = 4 AND Activo = 1 AND Idioma ='$lang'";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $Template = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo $Template['Template'];
                    ?>

                </div>

                <div class="signature-area" id="signature-section">

                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-confirmacion-tip" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); font-family: 'Segoe UI', Arial, sans-serif;">
    <div style="background-color: #fff; margin: 15% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="font-size: 18px; font-weight: bold; color: #002d72; margin-bottom: 15px;">Confirmar Propina</div>
        <p style="font-size: 14px; color: #555; margin-bottom: 25px;">
            ¿Deseas agregar <span id="text-monto-confirmar" style="font-weight: bold; color: #27ae60;">$0.00</span> como propina para el equipo?
        </p>
        <div style="display: flex; justify-content: space-around;">
            <button id="btn-cancelar-tip" style="padding: 10px 20px; border: 1px solid #ccc; background: #fff; border-radius: 4px; cursor: pointer; color: #666;">Cancelar</button>
            <button id="btn-aceptar-tip" style="padding: 10px 20px; border: none; background: #27ae60; color: #fff; border-radius: 4px; cursor: pointer; font-weight: bold;">Sí, agregar</button>
        </div>
    </div>
</div>


<div id="modal-remove-tip" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); font-family: 'Segoe UI', Arial, sans-serif;">
    <div style="background-color: #fff; margin: 15% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="font-size: 18px; font-weight: bold; color: #002d72; margin-bottom: 15px;">Remover Propina</div>
        <p style="font-size: 14px; color: #555; margin-bottom: 25px;">
            ¿Deseas retirar la propina para el equipo?
        </p>
        <div style="display: flex; justify-content: space-around;">
            <button id="btn-cancelar-remove-tip" style="padding: 10px 20px; border: 1px solid #ccc; background: #fff; border-radius: 4px; cursor: pointer; color: #666;">Cancelar</button>
            <button id="btn-aceptar-remove-tip" style="padding: 10px 20px; border: none; background: #27ae60; color: #fff; border-radius: 4px; cursor: pointer; font-weight: bold;">Sí, remover</button>
        </div>
    </div>
</div>


<div id="modal-change-apay" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); font-family: 'Segoe UI', Arial, sans-serif;">
    <div style="background-color: #fff; margin: 15% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="font-size: 18px; font-weight: bold; color: #002d72; margin-bottom: 15px;">Cambiar Anticipo</div>
        <p style="font-size: 14px; color: #555; margin-bottom: 25px;">
            ¿Deseas cambiar el anticipo a <span id="text-monto-anticipo" style="font-weight: bold; color: #27ae60;">$0.00</span>?
        </p>
        <div style="display: flex; justify-content: space-around;">
            <button id="btn-cancelar-apay" style="padding: 10px 20px; border: 1px solid #ccc; background: #fff; border-radius: 4px; cursor: pointer; color: #666;">Cancelar</button>
            <button id="btn-aceptar-apay" style="padding: 10px 20px; border: none; background: #27ae60; color: #fff; border-radius: 4px; cursor: pointer; font-weight: bold;">Sí, cambiar</button>
        </div>
    </div>
</div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>   
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>

 <?php
        $query = "select NombreCompania, Direccion,Direccion2, Ciudad,CP,Estado,Pais,TelefonoCelular FROM account";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($Tip > 0){
            $query = "UPDATE lead SET Tip = ?, Total = TotalBT + ? WHERE Id = ? ";        
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $Tip);
            $stmt->bindParam(2, $Tip);
            $stmt->bindParam(3, $cotizacion['IdQuote']);
            $stmt->execute();
        }else{
            $query = "UPDATE lead SET  Total = TotalBT WHERE Id = ? ";        
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $cotizacion['IdQuote']);
            $stmt->execute();

            $query = "UPDATE lead SET Tip = 0 WHERE Id = ? ";        
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $cotizacion['IdQuote']);
            $stmt->execute();            
        }
        
        if ($APay > 0){
            $query = "UPDATE lead SET Deposit = ?, DepositAmount = Total * ( ? / 100) WHERE Id = ? ";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $APay);
            $stmt->bindParam(2, $APay);
            $stmt->bindParam(3, $cotizacion['IdQuote']);
            $stmt->execute();

            $query = "UPDATE lead SET Balance = Total - DepositAmount  WHERE Id = ? ";        
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $cotizacion['IdQuote']);
            $stmt->execute();            

        }

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
        balancedue: "<?php echo $lead['Balance']?>",

        electric:"",
        signature:"",
        signeddate:"",
        "link_to_accept":'contract.php?Id=<?php echo $token;?>'
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

    $(document).ready(function() {
        // 1. Inicializar Signature Pad

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

        LoadDocument();

        let montoPendiente = 0;
        let anticipo = 0;

        // 1. Evento para los botones de %
        $(document).on('click', '.btn-tip', function() {
            
            const porcentaje = $(this).data('tip');
            const subtotal = parseFloat(datosGenerales.total) || 0; // Ajusta según tu variable de precio
            montoPendiente = (subtotal * (porcentaje / 100)).toFixed(2);
            
            abrirModalConfirmacion(montoPendiente);
        });

        // 2. Evento para el input manual (cuando pierde el foco y tiene valor)
        $(document).on('change', '#custom-tip', function() {
            const valor = parseFloat($(this).val());
            if (valor > 0) {
                montoPendiente = valor.toFixed(2);
                abrirModalConfirmacion(montoPendiente);
            }
        });

        // Funciones del Modal
        function abrirModalConfirmacion(monto) {
            $('#text-monto-confirmar').text('$' + monto);
            $('#modal-confirmacion-tip').fadeIn(200);
        }

        $(document).on('click', '#btn-cancelar-tip', function() {
            $('#modal-confirmacion-tip').fadeOut(200);
            $('#custom-tip').val(''); // Limpia el input si cancela
            montoPendiente = 0;
        });

        $(document).on('click', '#btn-aceptar-tip', function() {
            $('#modal-confirmacion-tip').fadeOut(200);
            actualizarUrlYRedirigir({ Tip: montoPendiente });
        });

        $(document).on('click', '.btn-tip-remove', function() {
            $('#modal-remove-tip').fadeIn(200);
        });        

        $(document).on('click', '#btn-cancelar-remove-tip', function() {
            $('#modal-remove-tip').fadeOut(200);
        });

        $(document).on('click', '#btn-aceptar-remove-tip', function() {
            $('#modal-remove-tip').fadeOut(200);
            actualizarUrlYRedirigir({ Tip: 0 });
        });




        $(document).on('click', '.btn-apay', function() {
            
            const porcentaje = $(this).data('apay');
            const subtotal = parseFloat(datosGenerales.total) || 0; // Ajusta según tu variable de precio
            anticipo = porcentaje;
            //alert (anticipo)
            abrirModalAnticipo((subtotal * (porcentaje / 100)).toFixed(2));
        });        

        function abrirModalAnticipo(monto) {
            $('#text-monto-anticipo').text('$' + monto);
            $('#modal-change-apay').fadeIn(200);
        }        

        $(document).on('click', '#btn-cancelar-apay', function() {
            $('#modal-change-apay').fadeOut(200);
        });        


        $(document).on('click', '#btn-aceptar-apay', function() {
            $('#modal-confirmacion-tip').fadeOut(200);
            actualizarUrlYRedirigir({ APay: anticipo });
        });

function actualizarUrlYRedirigir(nuevosParams) {

    // 1. Capturamos los parámetros actuales de la URL
    let params = new URLSearchParams(window.location.search);

    // 2. Iteramos sobre los nuevos parámetros y los aplicamos
    // Esto sobreescribe si ya existen o los crea si no.
    Object.keys(nuevosParams).forEach(key => {
        params.set(key, nuevosParams[key]);
    });

    // 3. Construimos la nueva URL de destino
    // window.location.pathname contiene la ruta sin los parámetros viejos
    let nuevaUrl = window.location.origin + window.location.pathname + '?' + params.toString();

    // 4. Redirigimos
    window.location.href = nuevaUrl;
}        


        <?php 
            if ($lead['Tip'] > 0){
                echo"
                    $('#tip-add').hide();
                    $('#tip-remove').show();
                    $('#tips').show();
                ";
            }
            else{
                echo"
                    $('#tip-add').show();
                    $('#tip-remove').hide();
                    $('#tips').hide();
                ";
            }

            if ($lead['Deposit'] == 20){
                echo "$('#apay-20').prop('disabled', true);
                      $('#apay-20').css('background-color', '#ccc');
                      $('#apay-20').css('cursor', 'default');
                ";
            }
            elseif ($lead['Deposit'] == 50){
                echo "$('#apay-50').prop('disabled', true);
                      $('#apay-50').css('background-color', '#ccc');
                      $('#apay-50').css('cursor', 'default');                
                ";
            }
            elseif($lead['Deposit'] == 100){
                echo "$('#apay-100').prop('disabled', true);
                      $('#apay-100').css('background-color', '#ccc');
                      $('#apay-100').css('cursor', 'default');";
            }

        ?>

    });


function LoadDocument(){

            const $contenedor = $('#cotizacion-dsj');
            const $cuerpoTabla = $('#lista-productos');
            const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();
            ejecutarRenderizadoQuote($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos);

}    

function ejecutarRenderizadoQuote($contenedor, $cuerpoTabla, $filaPlantilla, datosGenerales, productos,descuentos) {
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