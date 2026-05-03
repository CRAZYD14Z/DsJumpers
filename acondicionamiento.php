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
include_once 'nav.php';
?>
<body>

<div class="container my-5">
    <div class="card shadow mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Reporte de Mantenimiento</h4>
            <div class="d-flex gap-2">
                <!-- Selector de Agrupación -->
                <select id="groupSelector" class="form-select form-select-sm" style="width: 200px;">
                    <option value="operation">Agrupar por Operación</option>
                    <option value="product">Agrupar por Producto</option>
                </select>
                <button id="btnPDF" class="btn btn-danger btn-sm">PDF</button>
            </div>
        </div>
    </div>

    <div class="table-responsive shadow-sm border bg-white">
        <table class="table align-middle m-0" id="reportTable">
            <thead class="table-dark">
                <tr>
                    <th style="width: 80px;"></th>
                    <th>Detalle</th>
                    <th class="text-center">Etapa</th>
                    <th class="text-center">Cantidad</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Se llena mediante JS -->
            </tbody>
        </table>
    </div>
</div>

<script>

const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
const TOKEN = localStorage.getItem('apiToken'); 

$(document).ready(function() {
    // Escuchar el cambio en el selector
    $('#groupSelector').on('change', function() {
        loadData();
    });

    function loadData() {
        const group = $('#groupSelector').val();
        const $tableBody = $('#tableBody');

        // Estado de carga
        $tableBody.html('<tr><td colspan="4" class="text-center py-4">Cargando datos...</td></tr>');

        $.ajax({
            url:  API_BASE_URL + 'acondicionamiento/',
            type: 'GET',
            data: { group_by: group },
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            success: function(data) {
                $tableBody.empty(); // Limpiar tabla

                if (data.length === 0) {
                    $tableBody.html('<tr><td colspan="4" class="text-center">No hay registros pendientes.</td></tr>');
                    return;
                }

                $.each(data, function(i, grupo) {
                    // Fila de encabezado de grupo (Operación o Producto)
                    let headerRow = `
                        <tr class="table-secondary">
                            <td colspan="4" class="fw-bold text-uppercase" style="letter-spacing: 1px;">
                                <i class="bi bi-folder2-open me-2"></i> ${grupo.label}
                            </td>
                        </tr>`;
                    $tableBody.append(headerRow);

                    // Filas de items
                    $.each(grupo.items, function(j, item) {
                        // Lógica de marca "Evento en Puerta"
                        const alertBadge = item.urgente 
                            ? '<span class="badge bg-warning text-dark ms-2"><i class="bi bi-exclamation-triangle-fill"></i> EVENTO EN PUERTA</span>' 
                            : '';
                        
                        // Determinar color de badge por etapa (puedes reutilizar tu función PHP aquí si prefieres)
                        const stageClass = (item.stage === 'REPARACION') ? 'bg-danger' : 'bg-info text-dark';

                        let itemRow = `
                            <tr>
                                <td class="text-center">
                                    <img src="${item.image}" class="rounded shadow-sm border" data-img-src="${item.image}"
                                        style="width:50px; height:50px; object-fit:cover;" 
                                        onerror="this.src='https://placehold.co/50x50?text=SIN+FOTO'">
                                </td>
                                <td>
                                    <div class="fw-bold">${item.name} ${alertBadge}</div>
                                    <small class="text-muted">
                                        ${item.tipo} | <span class="text-primary"><a href="operations.php?IdOperation=${item.IdOp}">#${item.folio}</a> </span> | ${item.cliente}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge ${stageClass} px-3 py-2">${item.stage}</span>
                                </td>
                                <td class="text-center">
                                    <span class="h5 mb-0">${item.assorted}</span>
                                </td>
                            </tr>`;
                        $tableBody.append(itemRow);
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error("Error en la carga:", error);
                $tableBody.html('<tr><td colspan="4" class="text-center text-danger">Error al cargar los datos.</td></tr>');
            }
        });
    }

    // Carga inicial
    loadData();
});
</script>




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