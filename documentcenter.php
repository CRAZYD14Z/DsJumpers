
<?php
    ob_start();
    session_start(); 
    // Incluye la clase de conexión a la BD
    include_once 'valid_login.php';    
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    
    $Idioma = $_SESSION['Idioma'];
    
    $query = "select Traduccion FROM  programas_traduccion where Programa = 'documentcenter' AND Idioma = ? ORDER BY Id";
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
        return $Traducciones[$Id];
    }

    include_once 'head.php';
?>
</head>
<body>
    <?php
        include_once 'nav.php';
        $IdTabla = "documents";
    ?>

<div class="container-fluid p-4 bg-white border-0 shadow-sm rounded-4 mb-4" id="listado_<?php echo $IdTabla;?>">
        
        <h5 class="mb-4 text-dark fw-bold border-bottom pb-2">
            <i class="fa-solid fa-folder-open text-primary me-2 small"></i>Tipos de Documentos
        </h5>

        <div id="table-container_<?php echo $IdTabla;?>" class="table-responsive rounded-3">
            
            <table class="table table-striped table-hover align-middle mb-0" style="font-size: 0.95rem;">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="py-3 px-4 fw-semibold text-uppercase tracking-wider small" style="width: 25%;">Tipo</th>
                        <th class="py-3 px-4 fw-semibold text-uppercase tracking-wider small">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $query = "SELECT Tipo, Nombre, Descripcion FROM document_types WHERE Idioma = ? ORDER BY Tipo";
                    $stmt = $db->prepare($query);
                    $stmt->bindValue(1, $Idioma);
                    $stmt->execute();
                    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($resultados) {
                        foreach ($resultados as $registro) {
                            echo '
                            <tr onclick="window.location.href = \'crud.php?Id=document_center&Id2='.$registro['Tipo'].'\';" style="cursor: pointer;">
                                <td class="py-3 px-4 text-dark fw-semibold">
                                    <i class="fa-regular fa-file-lines text-muted me-2 small"></i>'.htmlspecialchars($registro['Nombre']).'
                                </td>
                                <td class="py-3 px-4 text-secondary text-wrap">
                                    '.htmlspecialchars($registro['Descripcion']).'
                                </td>
                            </tr>';
                        }
                    } else {
                        // Estado Vacío Elegante (Empty State) en caso de no haber registros
                        echo '
                        <tr>
                            <td colspan="2" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fs-3 mb-3 d-block text-black-50"></i>
                                <span class="fw-medium">No se encontraron tipos de documentos registrados.</span>
                            </td>
                        </tr>';
                    }
                ?> 
                </tbody>
            </table>
        </div>

        <div id="pagination-container_<?php echo $IdTabla;?>" class="pagination-container mt-4 d-flex justify-content-end">
            </div>
    </div>


<script>



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