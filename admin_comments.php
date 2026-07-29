<?php
// admin_comments.php
ob_start();
session_start(); 
// Incluye la clase de conexión a la BD y sesiones
include_once 'valid_login.php';
include_once 'config/config.php';     
include_once 'config/database.php'; 
$database = new Database();
$db = $database->getConnection();

$Idioma = $_SESSION['Idioma'];
// Cambiamos el programa a 'comments' en las traducciones para que cargue tus textos personalizados
$query = "select Traduccion FROM  programas_traduccion where Programa = 'comments' AND Idioma = ? ORDER BY Id";            
$stmt = $db->prepare($query);
$stmt->bindValue(1, $Idioma);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$Traducciones[]='';
if ($resultados) {
    foreach ($resultados as $registro) {
        $Traducciones[]=$registro['Traduccion'];
    }
}    
function Trd($Id){
    global $Traducciones;
    // Si aún no configuras traducciones, puedes descomentar la línea de abajo como fallback temporal:
    // return "Texto ".$Id; 
    return $Traducciones[$Id];
}

include_once 'head.php';
?>
<style>
        body { background-color: #f4f7f6; }
        .sticky-search { position: sticky; top: 0; z-index: 1020; background: rgba(244, 247, 246, 0.95); padding: 20px 0; }
        .table-container { background: white; border-radius: 12px; overflow: hidden; }
        
        /* Colores laterales adaptados a los estatus de comentarios */
        .status-approved { background: #28a745 !important; }
        .status-pending { background: #ffc107 !important; }
        .status-rejected { background: #dc3545 !important; }

        .clickable-row:hover {
            background-color: rgba(13, 110, 253, 0.05) !important;
            transition: background-color 0.2s ease;
        }        
        .stars-gold { color: #f59e0b; }
</style>
</head>
<body>

<?php
    include_once 'nav.php';
?>
<br>
<br>
<div class="container pb-5">
    <div class="sticky-search">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-2">
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-transparent"><i class="bi bi-search text-primary"></i></span>
                            <!-- Trd(1): Placeholder de búsqueda, ej: "Buscar comentarios..." -->
                            <input type="text" id="txtSearch" class="form-control border-0 shadow-none" placeholder="<?php echo Trd(1)?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container shadow-sm border">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <!-- Ajuste de Encabezados con traducciones -->
                        <th class="ps-4"><?php echo Trd(2) /* "ID / Fecha" */?></th>
                        <th><?php echo Trd(3) /* "Cliente" */?></th>
                        <th><?php echo Trd(4) /* "Opinión" */?></th>
                        <th><?php echo Trd(5) /* "Calificación" */?></th>
                        <th class="text-center"><?php echo Trd(6) /* "Destacado" */?></th>
                        <th class="text-end pe-4"><?php echo Trd(7) /* "Estatus" */?></th>
                    </tr>
                </thead>
                <tbody id="commentsData">
                </tbody>
            </table>
        </div>
    </div>

    <div id="loadingIndicator" class="text-center my-4" style="display:none;">
        <div class="spinner-grow text-primary" role="status"></div>
        <p class="text-muted small"><?php echo Trd(8) /* "Cargando..." */?></p>
    </div>
</div>

<!-- MODAL PARA CAMBIAR ESTATUS Y MODERAR COMENTARIO -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="commentModalLabel"><?php echo Trd(9) /* "Moderar Comentario" */?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         <input type="hidden" id="modalReviewId">
         <div class="mb-3">
             <strong class="d-block text-muted small"><?php echo Trd(3) /* "Cliente" */?></strong>
             <div id="modalAuthorName" class="fw-bold"></div>
             <div id="modalAuthorMeta" class="text-muted small italic"></div>
         </div>
         <div class="mb-3">
             <strong class="d-block text-muted small"><?php echo Trd(4) /* "Opinión" */?></strong>
             <p id="modalReviewText" class="bg-light p-3 rounded border text-dark italic" style="font-size: 0.95rem;"></p>
         </div>
         
         <hr>
         
         <!-- Campos modificables -->
         <div class="mb-3">
             <label for="modalStatusSelect" class="form-label fw-semibold small"><?php echo Trd(7) /* "Estatus" */?></label>
             <select id="modalStatusSelect" class="form-select">
                 <option value="pending">Pending</option>
                 <option value="approved">Approved</option>
                 <option value="rejected">Rejected</option>
             </select>
         </div>
         <div class="mb-3 form-check form-switch">
             <input class="form-check-input" type="checkbox" role="switch" id="modalFeaturedSwitch">
             <label class="form-check-input-label fw-semibold small" for="modalFeaturedSwitch"><?php echo Trd(6) /* "Destacar en diseño Bento (Grande)" */?></label>
         </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Trd(10) /* "Cancelar" */?></button>
        <button type="button" class="btn btn-primary" id="btnSaveChanges"><?php echo Trd(11) /* "Guardar Cambios" */?></button>
      </div>
    </div>
  </div>
</div>

<script>
const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
const TOKEN = localStorage.getItem('apiToken'); 

$(document).ready(function() {
    let currentPage = 1;
    let isFetching = false;
    let noMoreData = false;
    let timer;
    let loadedComments = {}; // Almacén en memoria de los comentarios cargados para el modal

    // Función Principal con AJAX (Consume tu API apuntando al nuevo endpoint de comentarios)
    function fetchComments(reset = false) {
        if (isFetching || (noMoreData && !reset)) return;

        if (reset) {
            currentPage = 1;
            noMoreData = false;
            $('#commentsData').empty();
            loadedComments = {};
        }

        isFetching = true;
        $('#loadingIndicator').fadeIn();

        $.ajax({
            url:  API_BASE_URL + 'comments_admin/', // Tu endpoint administrativo
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: {
                page: currentPage,
                search: $('#txtSearch').val()
            },
            success: function(response) {
                if (!response || response.length === 0) {
                    noMoreData = true;
                    if (currentPage === 1) {
                        $('#commentsData').html('<tr><td colspan="6" class="text-center py-5 text-muted"><?php echo Trd(12) /* "No se encontraron comentarios" */?></td></tr>');
                    }
                } else {
                    renderTable(response);
                    currentPage++;
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la petición:", error);
                alert("<?php echo Trd(13) /* "Error al conectar con la API" */?>");
            },
            complete: function() {
                isFetching = false;
                $('#loadingIndicator').fadeOut();
            }
        });
    }

    // Renderizar filas
    function renderTable(data) {
        let rows = '';
        $.each(data, function(i, item) {
            // Guardamos en el objeto temporal para el acceso rápido del modal
            loadedComments[item.id] = item;

            const statusClass = "status-" + item.status.toLowerCase();
            const badgeClass = getBadgeColor(item.status);
            const stars = '<span class="stars-gold">' + '★'.repeat(item.rating) + '</span>' + '☆'.repeat(5 - item.rating);
            
            // Recortamos el texto para que no rompa la tabla si es muy largo
            const shortText = item.review_text.length > 60 ? item.review_text.substring(0, 60) + '...' : item.review_text;
            const featuredBadge = item.is_featured == 1 ? '<span class="badge bg-primary">Bento Box</span>' : '<span class="text-muted small">-</span>';

            rows += `
                <tr class="${statusClass} clickable-row" data-id="${item.id}" style="cursor: pointer;">
                    <td class="ps-4">
                        <div class="fw-semibold">#${item.id}</div>
                        <div class="small text-muted">${item.created_at}</div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${item.author_name}</div>
                        <div class="small text-muted italic">${item.author_meta}</div>
                    </td>
                    <td>
                        <div class="small text-secondary italic">"${shortText}"</div>
                    </td>
                    <td><div class="small">${stars}</div></td>
                    <td class="text-center">${featuredBadge}</td>
                    <td class="text-end pe-4"><span class="badge rounded-pill ${badgeClass}">${item.status.toUpperCase()}</span></td>
                </tr>
            `;
        });
        $('#commentsData').append(rows);
    }

    function getBadgeColor(status) {
        const s = status.toLowerCase();
        if (s.includes('approved')) return 'bg-success';
        if (s.includes('pending')) return 'bg-warning text-dark';
        if (s.includes('rejected')) return 'bg-danger';
        return 'bg-secondary';
    }

    // Evento Scroll (Infinite Scroll)
    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
            fetchComments();
        }
    });

    // Evento de Búsqueda (Debounce)
    $('#txtSearch').on('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            fetchComments(true);
        }, 500);
    });

    // CLIC EN LA FILA: Abre el Modal con los datos correspondientes
    $('#commentsData').on('click', '.clickable-row', function() {
        const reviewId = $(this).data('id');
        const item = loadedComments[reviewId];
        
        if (item) {
            $('#modalReviewId').val(item.id);
            $('#modalAuthorName').text(item.author_name);
            $('#modalAuthorMeta').text(item.author_meta);
            $('#modalReviewText').text(item.review_text);
            $('#modalStatusSelect').val(item.status);
            $('#modalFeaturedSwitch').prop('checked', item.is_featured == 1);
            
            $('#commentModal').modal('show');
        }
    });    

    // ACCIÓN: Guardar cambios desde el Modal (Hace un PUT / POST a tu API)
    $('#btnSaveChanges').on('click', function() {
        const id = $('#modalReviewId').val();
        const status = $('#modalStatusSelect').val();
        const is_featured = $('#modalFeaturedSwitch').is(':checked') ? 1 : 0;

        $.ajax({
            url: API_BASE_URL + 'comments_admin_update', // Endpoint de guardado
            type: 'POST', // O 'PUT' dependiendo de tus estándares de API
            contentType: 'application/json',
            headers: { 'Authorization': 'Bearer ' + TOKEN },
            data: JSON.stringify({
                id: id,
                status: status,
                is_featured: is_featured
            }),
            success: function(response) {
                // Si la actualización es exitosa, cerramos modal y refrescamos la tabla completa
                $('#commentModal').modal('hide');
                fetchComments(true); 
            },
            error: function(xhr) {
                alert("No se pudieron guardar los cambios de moderación.");
            }
        });
    });

    // Carga inicial
    fetchComments();
});    

// Sincronización Global de Tokens heredada de tu script original
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