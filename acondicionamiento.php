<?php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD
include_once 'valid_login.php';
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();
define('ID_CLIENTE' , $_SESSION['id_cliente']);
include_once 'head.php';
?>
    <style>
        .table-container { background: white; border-radius: 12px; overflow: hidden; }
        .img-report { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        .row-child { background-color: #f8f9fa; font-size: 0.9em; }
        .indent { padding-left: 30px !important; }
        .badge-stage { font-size: 0.75em; }
    </style>
<?php

$operations = $db->query("SELECT * FROM v_operations WHERE `status` = 'ACONDICIONAMIENTO' ORDER BY id_operation")->fetchAll();

$operaciones = [];
foreach ($operations as $op) {
    $rows = $db->query("SELECT * FROM v_operation_checklist WHERE `stage` IN ('LAVADO', 'LIMPIEZA', 'REPARACION') AND assorted_quantity > '0' AND id_operation = ".$op['Id_operation']." ORDER BY  id_checklist")->fetchAll();

    foreach ($rows as $row) {
        $opId = $row['id_operation'];

        // Si es la primera vez que vemos esta operación, inicializamos su espacio
        if (!isset($operaciones[$opId])) {
            $operaciones[$opId] = [
                'cliente' => $op['NombreOrganizacion'] ? $op['NombreOrganizacion'] : $op['NombreCliente'] ." ".$op['ApellidosCliente']  , // Ajusta según tu columna de cliente
                'folio' => $op['Folio'],
                'items'   => []
            ];
        }

        // Buscamos la imagen del ítem (Producto o Accesorio)
        $itemIdForImg = $row['id_product'];
        if ($row['id_accesory_base']) $itemIdForImg = $row['id_accesory_base'];
        if ($row['id_accesory']) $itemIdForImg = $row['id_accesory'];

        $query = "SELECT Image from products_images WHERE Product = ? ORDER BY Orden LIMIT 1";
        $stmtigm = $db->prepare($query);
        $stmtigm->execute([$itemIdForImg]);
        $img = $stmtigm->fetchColumn();

        // Agregamos el ítem al grupo de esta operación
        $operaciones[$opId]['items'][] = [
            'name'         => $row['id_accesory_base'] || $row['id_accesory'] ? ($row['Base'] ?? $row['Accesory']) : $row['Product'],
            'image'        =>  CFPUBLICURL.'/'.ID_CLIENTE.'/products_images/thumbnails/'.$img,
            'stage'        => $row['stage'],
            'requested'    => $row['requested_quantity'],
            'assorted'     => $row['assorted_quantity'],
            'verification' => $row['verification_stage'],
            'tipo'         => $row['id_accesory_base'] ? 'BASE' : ($row['id_accesory'] ? 'ACCESORIO' : 'PRODUCTO')
        ];
    }
}
?>

<?php
    include_once 'nav.php';
?>

<body>

<div class="container my-5">
    <div class="card shadow">
        <div class="card-header text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-black">Reporte de Operaciones: Lavado, Limpieza y Reparación</h4>
            <button id="btnPDF" class="btn btn-danger">
                <i class="bi bi-file-pdf"></i> Exportar a PDF
            </button>
        </div>
    </div>

        <div class="container pb-5">
            <div class="table-container shadow-sm border">
                <div class="table-responsive">

                
                    <table class="table table-hover align-middle m-0" id="reportTable">

                        <thead class=" text-black">
                            <tr>
                                <th style="width: 80px;"></th>
                                <th>Producto</th>
                                <th class="text-center">Etapa</th>
                                
                                <th class="text-center">A procesar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($operaciones as $idOp => $datos): ?>
                                <tr class="table-light">
                                    <td colspan="4" class="py-3 border-start border-primary ">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <span class="text-muted small d-block">Nº OPERACIÓN</span>
                                                <strong class="h5">#<?= $datos['folio'] ?></strong>
                                            </div>
                                            <div class="col-md-8">
                                                <span class="text-muted small d-block">CLIENTE</span>
                                                <strong class="h5"><?= htmlspecialchars($datos['cliente']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <?php foreach ($datos['items'] as $item): ?>
                                    <tr>
                                        <td class="text-center">



                                            <?php if($item['image']): ?>
                                                <img src="<?= $item['image'] ?>" data-img-src="<?= $item['image'] ?>" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light text-muted d-flex align-items-center justify-content-center rounded" style="width: 50px; height: 50px; font-size: 10px;">SIN FOTO</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= $item['name'] ?></div>
                                            <span class="badge bg-light text-muted border" style="font-size: 0.7rem;"><?= $item['tipo'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= getStageColor($item['stage']) ?> px-3 py-2">
                                                <?= $item['stage'] ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="h6 mb-0"><?= $item['assorted'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>                


                
            </div>
        </div>
    </div>
</div>




<?php
// Función auxiliar para colores
function getStageColor($stage) {
    return match($stage) {
        'LAVADO' => 'bg-info text-dark',
        'LIMPIEZA' => 'bg-warning text-dark',
        'REPARACION' => 'bg-danger',
        default => 'bg-light text-dark'
    };
}
?>            

            </div>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>


<script>
$("#btnPDF").click(function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4');

    // Título elegante y minimalista
    doc.setFont("helvetica", "bold");
    doc.setFontSize(20);
    doc.setTextColor(40, 40, 40);
    doc.text("REPORTE DE ACONDICIONAMIENTO", 40, 50);
    
    // Línea decorativa sutil debajo del título
    doc.setDrawColor(230, 230, 230);
    doc.line(40, 65, 555, 65);

    doc.autoTable({
        html: '#reportTable',
        startY: 90,
        theme: 'plain', // Cambiamos a 'plain' para quitar el fondo de las celdas
        styles: {
            font: 'helvetica',
            fontSize: 9,
            cellPadding: 12, // Más espacio interno para que "respire"
            textColor: [80, 80, 80],
            lineColor: [245, 245, 245], // Líneas divisorias casi invisibles
            lineWidth: 0.5,
            verticalAlign: 'middle'
        },
        headStyles: {
            fillColor: [255, 255, 255], // Fondo blanco en cabecera
            textColor: [120, 120, 120], // Texto gris suave
            fontStyle: 'bold',
            fontSize: 8,
            borderBottom: { color: [200, 200, 200], width: 1 } // Solo línea inferior
        },
        columnStyles: {
            0: { cellWidth: 50 }, // Imagen
            3: { halign: 'center', fontStyle: 'bold' } // Cantidad
        },
        didParseCell: function(data) {
            // Estilo para la fila del Cliente (Sección)
            if (data.row.section === 'body' && data.cell.raw.colSpan > 1) {
                data.cell.styles.fillColor = [250, 250, 250]; // Gris casi blanco
                data.cell.styles.textColor = [0, 0, 0];
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.fontSize = 10;
                data.cell.styles.cellPadding = 15;
            }

            // Colores sutiles para la Etapa (solo texto, sin badges pesados)
            if (data.column.index === 2 && data.row.section === 'body') {
                const stage = data.cell.text[0].trim().toUpperCase();
                if (stage === 'LAVADO') data.cell.styles.textColor = [0, 123, 255];
                if (stage === 'LIMPIEZA') data.cell.styles.textColor = [214, 158, 0];
                if (stage === 'REPARACION') data.cell.styles.textColor = [220, 53, 69];
            }
        },
        didDrawCell: function(data) {
            // Renderizado de imagen con esquinas redondeadas (opcional)
            if (data.column.index === 0 && data.cell.section === 'body' && data.cell.raw.querySelector('img')) {
                const imgElement = data.cell.raw.querySelector('img');
                const imgSrc = imgElement.getAttribute('data-img-src');
                if (imgSrc) {
                    // Dibujamos la imagen un poco más pequeña para que el padding luzca
                    doc.addImage(imgSrc, 'JPEG', data.cell.x + 10, data.cell.y + 8, 30, 30);
                }
            }
        },
        margin: { left: 40, right: 40 }
    });

    // Pie de página
    const pageCount = doc.internal.getNumberOfPages();
    for(let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(150);
        doc.text(`Página ${i} de ${pageCount}`, 40, doc.internal.pageSize.height - 30);
    }

    doc.save('Reporte_Minimalista.pdf');
});

    $('.lang-option').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'cambiar_idioma.php',
            type: 'POST',
            data: { lang: $(this).data('lang') },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Recargamos para que el servidor lea la nueva sesión de idioma
                    location.reload(); 
                }
            }
        });
        
    });

    $(document).ajaxSuccess(function(event, xhr, settings) {
        const nuevoToken = xhr.getResponseHeader('Authorization-Update');
        if (nuevoToken) {
            localStorage.setItem('apiToken', nuevoToken);
            console.log("Token actualizado globalmente desde: " + settings.url);
        }
    }); 
</script>
</body>
</html>