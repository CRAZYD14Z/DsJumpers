
<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['Idioma'];?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración con Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>        

    <link rel="stylesheet" href="css/lead.css" />

    <style>

        /* Evita que las filas de las tablas se corten entre páginas */
        tr { page-break-inside: avoid; }
        table { page-break-inside: auto; }
        thead { display: table-header-group; }

        /* Asegura que el contenedor sea interpretado correctamente */
        #contrato-dsj {
            background: white !important;
            overflow: visible !important;
            height: auto !important;
        }

    </style>
</head>
<body >
<div id="contrato-dsj" style="font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333; max-width: 900px; margin: auto; line-height: 1.4; font-size: 13px; background-color: #fff; padding: 20px;">

    <table width="100%" style="border-collapse: collapse; margin-bottom: 30px;">
        <tr>
            <td style="vertical-align: top; width: 60%;">
                <img src="https://rental.software/users/DsJumpers/images/LOGO - DSJUMPERS_979149.jpg" style="width: 180px; margin-bottom: 15px;">
                <div style="font-size: 12px; color: #555;">
                    <strong style="font-size: 16px; color: #000; display: block; margin-bottom: 4px;">*company_name*</strong>
                    *company_address*<br>
                    <strong>Phone:</strong> *company_phone*
                </div>
            </td>
            <td style="vertical-align: top; text-align: right; width: 40%;">
                <div style="background-color: #f9f9f9; border: 1px solid #eee; padding: 15px; border-radius: 4px;">
                    <p style="margin: 0; font-size: 14px;"><strong>Invoice:</strong> #*leadid*</p>
                    <p style="margin: 5px 0 0; color: #777;"><strong>Order Date:</strong> *contractsentdate*</p>
                </div>
            </td>
        </tr>
    </table>

    <table width="100%" style="border-collapse: collapse; margin-bottom: 25px; border: 1px solid #eee;">
        <tr>
            <td style="width: 55%; padding: 15px; border-right: 1px solid #eee; vertical-align: top;">
                <h4 style="margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; color: #999; letter-spacing: 1px;">Renter & Location</h4>
                <strong style="font-size: 14px; display: block; margin-bottom: 3px;">*organization*</strong>
                <strong>*ctfirstname* *ctlastname*</strong><br>
                *eventstreet*<br>
                *eventcity*, *eventstate* *eventzip*<br>
                <span style="color: #666;">*phones*</span>
            </td>
            <td style="width: 45%; padding: 15px; vertical-align: top; background-color: #fafafa;">
                <h4 style="margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; color: #999; letter-spacing: 1px;">Event Timing</h4>
                <p style="margin: 0 0 5px 0;"><strong>Start:</strong> *startdate* *starttime*</p>
                <p style="margin: 0 0 5px 0;"><strong>End:</strong> *enddate* *endtime*</p>
                <p style="margin: 0;"><strong>Type:</strong> *deliverytype*</p>
            </td>
        </tr>
    </table>

<table width="100%" style="border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr style="border-bottom: 2px solid #333;">
            <th style="text-align: left; padding: 10px; font-weight: 600;">Rental Item Description</th>
            <th style="text-align: center; padding: 10px; font-weight: 600; width: 80px;">Qty</th>
            <th style="text-align: right; padding: 10px; font-weight: 600; width: 120px;">Total</th>
        </tr>
    </thead>
    <tbody id="lista-productos">
        <tr class="item-fila" style="border-bottom: 1px solid #eee; display: none;">
            <td style="padding: 12px 10px;">
                <div style="font-weight: 500;">*rentalname*</div>
                <div style="font-size: 11px; color: #777;">*fullrentaltime*</div>
            </td>
            <td style="padding: 12px 10px; text-align: center;">*rentalqty*</td>
            <td style="padding: 12px 10px; text-align: right; font-weight: 600;">$*rentaltotalprice*</td>
        </tr>
    </tbody>
</table>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 30px;">
        <table style="width: 300px; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0; color: #666;">Subtotal</td>
                <td style="padding: 6px 0; text-align: right;">$*subtotal*</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #666;">Sales Tax (*taxrate*%)</td>
                <td style="padding: 6px 0; text-align: right;">$*salestax*</td>
            </tr>
            <tr style="border-top: 1px solid #333; font-size: 15px;">
                <td style="padding: 10px 0;"><strong>Total</strong></td>
                <td style="padding: 10px 0; text-align: right;"><strong>$*total*</strong></td>
            </tr>
            <tr style="color: #c0392b; font-weight: 600;">
                <td style="padding: 6px 0;">Balance Due</td>
                <td style="padding: 6px 0; text-align: right;">$*ctr_balance_due*</td>
            </tr>
        </table>
    </div>

    <div style="page-break-after: always; border-bottom: 1px dashed #eee; margin: 40px 0;"></div>

    <div style="font-size: 10.5px; text-align: justify; color: #444; line-height: 1.3;">
        <h3 style="text-align: center; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; color: #000;">Rental Agreement & Policies</h3>
        
        <div style="column-count: 2; column-gap: 30px;">

        <ol>
			<li style="list-style-type:decimal;"><strong>Identity of parties:</strong> For this Rental Agreement, Company shall mean *company_name*, its owners, officers, directors, employees, contractors, and agents. &quot;Customer&quot; shall mean the person(s) listed in the &quot;Event Location &amp; Renter&quot; section on the invoice page of this agreement as well as the person signing the agreement (if different), and their agents and/or employees.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Equipment, Rent, Payment, and Term of Rental Agreement:</strong> Customer rents from Company certain equipment described on the invoice page of this agreement. The rental fee set forth is payable, in full, in advance, and the rental term shall be that listed as &quot;Start Time&quot; on the invoice page of this agreement, but all of Customer&#39;s obligations arising under the terms and conditions of this Rental Agreement shall run from the actual delivery of the rental equipment to the actual pickup of the rental equipment by Company. If the equipment is delivered and accepted by Customer, then Customer shall not be entitled to any refund whatsoever if Customer elects not to use the equipment for reasons not covered in other provisions of this agreement.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Operation:</strong> Customer agrees to provide <span style="text-decoration:underline;">&nbsp; *electric* &nbsp;</span> electrical outlet(s) rated at 115 volts with 20 amperes capacity per motor unit within 50 feet of each equipment. No electrical cords are to be used that are not supplied by Company. If the blower stops or the air pressure is low, remove all users immediately, and then check on the problem. The electrical cord should be plugged into an outlet and be the only thing operating on that electrical circuit. &nbsp;Circuit breakers should also be checked. &nbsp;The customer is subject to an additional charge for all service calls due to electricity.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>&nbsp;Weather:</strong> Company does not guarantee weather conditions. We reserve the right to cancel or reschedule your rental before delivery if severe weather conditions are imminent or if we have reason to believe that the equipment and/or its users may be in danger. We also reserve the right to pick up the equipment early should weather conditions change. If we pick up the equipment early, Customer will be entitled to reschedule the rental on another day within 30 days. Some examples of severe weather conditions are extreme temperatures, high winds, rain, snow, thunder, and/or lightning.<strong>In the event of severe weather during a rental, Customer agrees that they will unplug the inflatable, allow it to deflate, and not use the equipment until severe weather ends.</strong></li>
			<br>
			<li style="list-style-type:decimal;"><strong>Return Check Policy:</strong> If a check is returned to Company for insufficient funds, Customer agrees to pay the total rental balance as well as an additional $75.00 return fee.
				<br>
				<br>
			</li>
			<li style="list-style-type:decimal;"><strong>Care of the Rental Equipment:</strong> Customer shall be responsible for any damage to any of the Rental Equipment not caused by ordinary wear and tear. &quot;Ordinary wear and tear&quot; shall mean only the normal deterioration of the rental equipment caused by ordinary, reasonable, and proper use of the rental equipment. Customer shall be liable to Company for all damage, which is not &quot;ordinary wear and tear&quot; including, but is not limited to, cutting or tearing of vinyl or netting, damage due to overturning, overloading, exceeding rated capacities, breakage, improper use, abuse, lack of cleaning, contamination of or dirtying of rental equipment with non-approval items such as chemicals, food, paint, silly string, mud, clay, or other materials. Customer will also be liable for damage done by weather if the damage could have been prevented by Customer deflating the equipment as covered in article three (3) above.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Warranties:</strong> Company makes no warranties either expressed or implied as to the condition or performance of any equipment and/or property leased by Customer from Company. By signing this contract, Customer agrees that any warranty of merchantability or fitness for a particular purpose is hereby disclaimed. By signing this contract, Customer agrees that no expressed warranty as to the condition or performance of any equipment and/or property leased by Customer is hereby disclaimed. Customer understands that the only warranties about said equipment and/or property is that which is stated in the instruction manual for said equipment and/or property, which Customer has received a copy of.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Possession:</strong> Customer&#39;s right to possession of the Rental Equipment begins upon the items being delivered to Customer&#39;s premises and terminates on the actual pick up by Company. Retention of possession or any failure to permit the pickup of the equipment at or after the end of the &quot;Rental period&quot; specified constitutes a material breach of this agreement. If the equipment is not returned for any reason, including theft, the Customer is obligated to pay to Company the full replacement value for such equipment listed on the invoice page of this agreement, plus all incidental costs associated with the attempted pick up or recovery of the equipment by Company. Customer shall not cause nor permit these items, or any of them, to be sublet, rented, sold, or removed from the delivery address, or otherwise transfer such items. If rental items are not returned and/or levied upon for any reason whatsoever, Company may take possession of said items without further notice or legal process and use whatever force is necessary to do so. Customer hereby agrees to indemnify, defend, and hold Company harmless from all claims and costs arising from such retaking. If rental items are stolen, or otherwise moved from the delivery address, Customer shall notify Company immediately.
				<br>
				<br>

				<ol>
					<li style="list-style-type:upper-alpha;"><strong>General Misuse:</strong> Do not allow riders to play or climb on walls, sides, or roof of inflatables. Do not allow water or a water hose near a dry inflatable. If the inflatable should become wet, have an adult wipe down equipment before riders return. Make sure the equipment is not wet when riders return, with the exception of units designed to have wet areas such as slip and slides, water slides, dunk tanks, etc.</li>
					<br>
					<li style="list-style-type:upper-alpha;"><strong>Negligence or Abuse:</strong> The following fees may be assessed for negligence or abuse of inflatable: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;

						<ol style="margin-top:0;margin-bottom:0;padding-inline-start:48px;">
							<li style="list-style-type:lower-roman;">Spilled Food, drink, or the uses of foreign substances $50</li>
							<li style="list-style-type:lower-roman;">Negligence or damage to the equipment(s): Full Repair Cost</li>
							<li style="list-style-type:lower-roman;">Damage beyond repair: Full Replacement Cost</li>
							<li style="list-style-type:lower-roman;">Excessive Cleaning: $50</li>
							<br>
						</ol>
					</li>
				</ol>
			</li>
			<li style="list-style-type:decimal;"><strong>Rules of Operation:</strong> Equipment must be operated over a smooth, compatible surface such as grass or hard top surface. The equipment may NOT be operated on rough surfaces such as rocks, brick, glass, or any jagged objects. Equipment may also not be operated over any exposed irrigation or utility lines or access points for such lines. The equipment cannot be moved by Customer after being placed by Company employees/contractors. &nbsp;Equipment MUST BE properly anchored before use. Equipment will be anchored initially by Company employees/contractors and the anchors MUST NOT be removed during the period of use. Never attempt to relocate, adjust, or service a blower. Never use during high winds, gusty winds, thunderstorms, or lightning. &nbsp;The equipment can turn over in high winds, even if anchored, and this could result in severe injuries to the users. &nbsp;Do not resume use until adverse weather conditions have ceased. Always follow the manufacturer&#39;s guidelines located on the equipment itself.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Overnight Rental:</strong> Unless otherwise agreed, equipment will be picked up on the same day it is dropped off. Customer agrees that any overnight rental will be in a secured location and that the equipment will be deflated, covered, and blowers removed to a secure indoor location when not in use.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Equipment problems:</strong> Should any equipment develop a problem, or not function correctly at any time, or Customer does not understand the operating instructions, the Customer agrees to immediately cease use of the equipment. In particular, if the inflatable equipment begins to deflate, Customer will immediately have the riders exit the equipment and then check for one of the following conditions: 1) The motor has stopped; in which case check the power cord connection at the outlet where the equipment plugs in to make sure that it has not been unplugged; 2) If the motor continues to run, check for blockage of the air intake screen on the side of the blower unit. Also, check both air tubes on the back of the equipment for snugness and tighten the ties if necessary; 3) If either of these steps corrects the problem, fully re-inflate the equipment before permitting anyone to use the equipment; 4) If you cannot correct the problem, call our office.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Pick-up Inspection</strong>: If Customer chooses to deflate the equipment before the arrival of the pick-up attendant, it must be re-inflated before it is packed up. The equipment will be inspected and may receive a preliminary cleaning before removal.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Cancellation/Refund Policy:</strong> Customer will only receive a full refund of the deposit if Customer cancels due to rain or inclement weather conditions before delivery. Inclement weather is the only variable that will necessitate a refund. A REFUND WILL NOT BE ISSUED for any other reason except inclement weather. The deadline for weather cancellations is no later than 8 AM on the date of the rental to receive a refund. If the equipment malfunctions or is inoperable, it is the sole responsibility of the Customer to notify Company immediately. If Company is not notified and given a chance to correct the problem, NO REFUND will be issued.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Release of Liability:</strong> Customer understands and acknowledges that play on an amusement device entails both known and unknown risks including, but not limited to, physical injury from falling, slipping, crashing or colliding, emotional injury, paralysis, distress, damage or death to any participant. Customer agrees to indemnify and hold Company harmless from any and all claims, actions, suits, proceedings, costs, expenses, fees, damages and liabilities, including, but not limited to, reasonable attorney&#39;s fees and costs, arising by reason of injury, damage, or death to persons or property, in connection with or resulting from the use of the leased equipment. This includes, but is not limited to, the manufacture, selection, delivery, possession, use, operation, or return of the equipment. Customer hereby releases and holds harmless Company from injuries or damages incurred as a result of the use of the leased equipment. Company cannot, under any circumstances, be held liable for injuries as a result of inappropriate use, God, nature, or other conditions beyond its control or knowledge. Customer also agrees to indemnify and hold harmless Company from any loss, damage, theft or destruction of the equipment during the term of the lease and any extensions thereof.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Rules and Supervision:</strong> Participants must be supervised at ALL times. Please go over all the rules to participants before using the equipment. Customer agrees to supervise both the equipment and its use at all times the equipment is in the possession of Customer. The Customer assumes the role of operator while equipment is in their possession. Accompanying the contract is a set of Manufacturer directions for use.
				<br>
				<br>

				<ol>
					<li style="list-style-type:upper-alpha;">Customer is responsible for enforcing posted rules, rules listed in this document, and any attached rules/code of conduct provided. Customer is responsible for ensuring that the size and number of persons entering the equipment does not exceed the manufacturer&#39;s maximum occupancy.</li>
					<br>
					<li style="list-style-type:upper-alpha;">Unless otherwise specified by the manufacturer, Customer agrees to have at least 1 person of average strength per equipment at all times. This person (Customer/operator) will be responsible for the operation of the rented equipment.</li>
					<br>
					<li style="list-style-type:upper-alpha;">Instructions for safety and operation will be reviewed at time of setup and include but are not limited to:

						<ol>
							<li style="list-style-type:lower-roman;">NO SHOES in inflatables.</li>
							<li style="list-style-type:lower-roman;">SOCKS REQUIRED when indicated.</li>
							<li style="list-style-type:lower-roman;">All persons must remove shoes, glasses, jewelry, belts, sharp objects etc.</li>
							<li style="list-style-type:lower-roman;">NO flips or somersaults etc.</li>
							<li style="list-style-type:lower-roman;">NO roughhousing, horseplay in/or around equipment.</li>
							<li style="list-style-type:lower-roman;">NO climbing, hanging, standing or pulling on sides, tops, backs of equipment</li>
							<li style="list-style-type:lower-roman;">NO crawling under bottom or edge of equipment.</li>
							<li style="list-style-type:lower-roman;">NO taping, fastening or hanging anything to or in the inflatable equipment. Severe injury risk can occur.</li>
							<li style="list-style-type:lower-roman;">NO FOOD, DRINKS, CANDY, ANIMALS, SHOES, SILLY STRING or SHARP OBJECTS are to be allowed in the rentals at any time.</li>
						</ol>
					</li>
				</ol>
			</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Customer Acknowledgement</strong>: Customer acknowledges and certifies that they have had sufficient opportunity to read this entire Agreement and agrees to be bound by all the terms and conditions on all pages and they understand its content and that they execute it freely, intelligently, and without duress of any kind.</li>
			<br>
			<li style="list-style-type:decimal;"><strong>Entire Agreement:</strong> This Agreement constitutes the full Agreement between Company and Customer. Any prior agreements, whether written or oral, promises, negotiations, or representations not expressly set forth herein shall be of no force or effect. Customer acknowledges the receipt of the Rental Equipment that is the subject of the Rental Agreement and General Release and the fact that it is in good working order.</li>
		</ol>            

BY SIGNING MY NAME ON THIS CONTRACT, I ACKNOWLEDGE THAT I HAVE COMPLETELY READ AND UNDERSTAND THIS CONTRACT, I WILL AGREE TO BE FULLY INSTRUCTED BY COMPANY PERSONNEL AS A TRAINED OPERATOR FOR THE AFOREMENTIONED EQUIPMENT AND HAVE HAD ALL OF MY QUESTIONS ANSWERED TO MY SATISFACTION.  I UNDERSTAND THAT I AM MUTUALLY RESPONSIBLE FOR ADHERING TO THE TERMS IN THIS AGREEMENT.        

        </div>

        <p style="margin-top: 20px; font-weight: bold; text-align: center; border: 1px solid #333; padding: 10px; text-transform: uppercase;">
            I confirm that I have read, understood, and agreed to all terms and safety rules.
        </p>
    </div>

    <table width="100%" style="margin-top: 40px; border-collapse: collapse;">
        <tr>
            <td style="width: 45%; border-top: 1px solid #333; padding-top: 8px; text-align: center;">
                <div style="font-family: 'Courier New', Courier, monospace; font-size: 18px; min-height: 40px; color: #002d72;">*signature*</div>
                <div style="font-size: 10px; color: #999; text-transform: uppercase;">Customer Digital Signature</div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%; border-top: 1px solid #333; padding-top: 8px; text-align: center; vertical-align: top;">
                <div style="font-size: 13px; min-height: 40px; padding-top: 10px;">*signeddate*</div>
                <div style="font-size: 10px; color: #999; text-transform: uppercase;">Date Signed</div>
            </td>
        </tr>
    </table>
</div>

<button onclick="generarPDFContrato()" class="btn btn-outline-dark btn-sm shadow-sm mb-3">
    <i class="fas fa-file-pdf me-2"></i> Descargar PDF
</button>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>        
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    


<script>

function renderContrato(datosGenerales, productos) {
    const $contenedor = $('#contrato-dsj');
    const $cuerpoTabla = $('#lista-productos');
    const $filaPlantilla = $cuerpoTabla.find('.item-fila').first();

    // 1. Limpiar productos previos (excepto la plantilla)
    $cuerpoTabla.find('tr:not(.item-fila)').remove();

    // 2. Procesar y agregar cada producto
    productos.forEach(producto => {
        let nuevaFilaHtml = $filaPlantilla[0].outerHTML;
        
        // Reemplazar etiquetas del producto
        $.each(producto, function(key, val) {
            let regex = new RegExp('\\*' + key + '\\*', 'g');
            nuevaFilaHtml = nuevaFilaHtml.replace(regex, val ?? '');
        });

        // Convertir a elemento jQuery, quitar el 'display: none' y añadir a la tabla
        let $nuevaFila = $(nuevaFilaHtml).clone().removeClass('item-fila').show();
        $cuerpoTabla.append($nuevaFila);
    });

    // 3. Procesar datos generales en todo el contenedor
    let htmlFinal = $contenedor.html();
    $.each(datosGenerales, function(key, val) {
        let regex = new RegExp('\\*' + key + '\\*', 'g');
        htmlFinal = htmlFinal.replace(regex, val ?? '');
    });

    $contenedor.html(htmlFinal);
    //alert(0)
}

function generarPDFContrato() {
    const elemento = document.getElementById('contrato-dsj');
    
    // Aseguramos que la página esté al inicio antes de capturar
    window.scrollTo(0, 0);

    const opciones = {
        margin: [10, 10, 10, 10],
        filename: 'Contrato_DsJumpers.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { 
            scale: 2, 
            useCORS: true, 
            letterRendering: true,
            scrollY: 0 // Evita que el scroll actual afecte la captura
        },
        jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' },
        // Esta es la clave para que no corte tablas a la mitad
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    // Usamos el aviso minimalista que creamos antes
    if(typeof lanzarMensaje === 'function') {
        lanzarMensaje("Generando documento...", "normal", 2000);
    }

    html2pdf().set(opciones).from(elemento).save().then(() => {
        if(typeof lanzarMensaje === 'function') {
            lanzarMensaje("PDF descargado con éxito", "exito", 3000);
        }
    });
}

$(document).ready(function() {
    const infoGeneral = {
        leadid: "90210",
        contractsentdate:"",
        company_name: "DsJumpers LLC",
        company_address:"",
        company_phone:"",
        organization:"",
        ctfirstname:"",
        ctlastname:"",
        eventstreet:"",
        eventcity:"",
        eventstate:"",
        eventzip:"",
        phones:"",
        startdate:"",
        starttime:"",
        enddate:"",
        endtime:"",
        deliverytype:"",

        subtotal: "450.00",
        taxrate: "36.00",
        salestax: "36.00",
        total: "486.00",
        ctr_balance_due: "100.00",

        electric:"",
        signature:"",
        signeddate:""
    };

    const misProductos = [
        { 
            rentalname: "15x15 Pink Castle", 
            fullrentaltime: "8 Hours", 
            rentalqty: "1", 
            rentaltotalprice: "250.00" 
        },
        { 
            rentalname: "Cotton Candy Machine", 
            fullrentaltime: "Daily", 
            rentalqty: "1", 
            rentaltotalprice: "85.00" 
        },
        { 
            rentalname: "Folding Chairs (White)", 
            fullrentaltime: "Daily", 
            rentalqty: "20", 
            rentaltotalprice: "115.00" 
        }
    ];
    renderContrato(infoGeneral, misProductos);
});


</script>
</body>
                    </html>