
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

    include_once 'head.php';
?>
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



<script>
</script>
</body>
</html>