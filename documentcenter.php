
<?php
    ob_start();
    session_start(); 
    // Incluye la clase de conexión a la BD
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    
    
    $Idioma = 'es';
    $_SESSION['Idioma'] = $Idioma;
    
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración con Navbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php
        include_once 'nav.php';
        $IdTabla = "documents";
    ?>

    <div class="container mt-4  shadow rounded" id="listado_<?php echo $IdTabla;?>">
        <br>

        <!-- Contenedor de la tabla -->
        <div id="table-container_<?php echo $IdTabla;?>" class="table-responsive">
            <!-- La tabla se generará aquí -->

            <table class="table table-sm table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="text-align: center">Tipo</th>
                        <th style="text-align: center">Descripcion</th>
                    </tr>
                </thead>
                <tbody >

<?php

    $query = "select Tipo,Nombre,Descripcion FROM document_types WHERE Idioma = ? ORDER BY Tipo";
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $Idioma);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $Traducciones[]='';
    if ($resultados) {
        foreach ($resultados as $registro) {
            //$Traducciones[]=$registro['Traduccion'];
            echo '
                    <tr onclick="window.location.href = '."'crud.php?Id=document_center&Id2=".$registro['Tipo']."'".';" style="cursor: pointer;">
                        <td style="text-align: left">'.$registro['Nombre'].'</td>
                        <td style="text-align: left">'.$registro['Descripcion'].'</td>
                    </tr>            
            
            ';
        }
    }     

?>                


                </tbody>
            </table>
        </div>
        <!-- Contenedor de paginación -->
        <div id="pagination-container_<?php echo $IdTabla;?>" class="pagination-container mt-3">
            <!-- La paginación se generará aquí -->
        </div>
    </div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
</script>
</body>
</html>