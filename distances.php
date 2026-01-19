<?php
    ob_start();
    session_start(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>  
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Page with jQuery</title>
    <!-- Include jQuery from a CDN -->
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>    

    
<?php
    // Incluye la clase de conexión a la BD
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    $IdTabla = "distances";
    
    $Idioma = 'es';
    $_SESSION['Idioma'] = $Idioma;

    $query = "select Traduccion FROM  programas_traduccion where Programa = 'crud' AND Idioma = ? ORDER BY Id";            
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
        echo $Traducciones[$Id];
    }
?>
  <style>
        .table-container {
            overflow-x: auto;
        }
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
        }
        <?php 
        $query = "select Campo, Alineacion from listado_ajax where Tabla = ? AND Tipo = 'Lst'";            
        $stmt = $db->prepare($query);
        $stmt->bindValue(1, $IdTabla);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $th='';
        if ($resultados) {
            foreach ($resultados as $registro) {
                echo ".td-".$registro['Campo']." {text-align: ".$registro['Alineacion'].";}";
            }
        }
        ?>
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .metadata-info {
            font-size: 14px;
            color: #6c757d;
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .page-link {
            cursor: pointer;
        }
        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 15px;
            }
            .metadata-info {
                text-align: center;
                width: 100%;
            }
        }
    </style>    
</head>

<?php
    include_once 'f_add_form.php'; 
    include_once 'f_edit_form.php';        
?>

<body>
    <div class="container mt-4 ">
        <h3 class="mb-4" style="text-align: center;" > DISTANCE CHARGES</h3>
    </div>

    <div class="container mt-4  shadow rounded" id="edit_form" >
        <form name="edit_<?php echo $IdTabla?>" id="edit_<?php echo $IdTabla?>" class="needs-validation" novalidate>    
            <div class="tab-pane fade show active" id="pills-Tab1" role="tabpanel" aria-labelledby="pills-Tab1-tab">
            <?php
                armar_formulario_edit("distances","General",$Idioma);
            ?>
            </div>
        </form>
    </div>

    <div class="container mt-4 ">
        <h3 class="mb-4" style="text-align: center;" > ZIP CODES</h3>
    </div>

    <div class="container mt-4  shadow rounded" id="edit_form" >
        <form name="edit_<?php echo $IdTabla?>" id="edit_<?php echo $IdTabla?>" class="needs-validation" novalidate>    
            <div class="tab-pane fade show active" id="pills-Tab1" role="tabpanel" aria-labelledby="pills-Tab1-tab">
                <div class="row">

                </div>    
            </div>
        </form>
    </div>    

    <div class="container mt-4 ">
        <h3 class="mb-4" style="text-align: center;" >DISTANCES</h3>
    </div>

    <div class="container mt-4  shadow rounded" id="edit_form" >
        <form name="edit_<?php echo $IdTabla?>" id="edit_<?php echo $IdTabla?>" class="needs-validation" novalidate>    
            <div class="tab-pane fade show active" id="pills-Tab1" role="tabpanel" aria-labelledby="pills-Tab1-tab">
                <div class="row">

                </div>    
            </div>
        </form>
    </div>

    <div class="container mt-4 ">
        <h3 class="mb-4" style="text-align: center;" > STATES</h3>
    </div>

    <div class="container mt-4  shadow rounded" id="edit_form" >
        <form name="edit_<?php echo $IdTabla?>" id="edit_<?php echo $IdTabla?>" class="needs-validation" novalidate>    
            <div class="tab-pane fade show active" id="pills-Tab1" role="tabpanel" aria-labelledby="pills-Tab1-tab">
                <div class="row">

                </div>    
            </div>
        </form>
    </div>    

    <div class="container mt-4 ">
        <h3 class="mb-4" style="text-align: center;" > TOTALS</h3>
    </div>

    <div class="container mt-4  shadow rounded" id="edit_form" >
        <form name="edit_<?php echo $IdTabla?>" id="edit_<?php echo $IdTabla?>" class="needs-validation" novalidate>    
            <div class="tab-pane fade show active" id="pills-Tab1" role="tabpanel" aria-labelledby="pills-Tab1-tab">
                <div class="row">

                </div>    
            </div>
        </form>
    </div>    

<script>
</script>

</body>
</html>    