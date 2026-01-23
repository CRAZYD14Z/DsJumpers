<?php
    ob_start();
    session_start(); 
    // Incluye la clase de conexión a la BD
    include_once 'config/config.php';     
    include_once 'config/database.php'; 
    $database = new Database();
    $db = $database->getConnection();
    $IdTabla = $_GET['Id'];
    $Id2 = '';
    if (isset($_GET['Id2']))
        $Id2 = $_GET['Id2'];

    $Idioma = $_SESSION['Idioma'];
    $Idioma = 'es';
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
        return $Traducciones[$Id];
    }
?>
<!DOCTYPE html>
<html lang="<?php echo $Idioma;?>">
<head>  
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Page with jQuery</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />    

    <script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>    



    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/choices.js@9.0.1/public/assets/styles/choices.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>     

<?php
    if ($IdTabla == 'item_prices' OR $IdTabla == 'products'){
    echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
    $query = "select Traduccion FROM programas_traduccion where Programa = 'prices' AND Idioma = ? ORDER BY Id";            
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $Idioma);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $Traducciones2[]=''; // El índice 0 queda vacío según tu lógica original
    if ($resultados) {
        foreach ($resultados as $registro) {
            $Traducciones2[]=$registro['Traduccion'];
        }
    }    
    function Trd_2($Id){
        global $Traducciones2;
        echo isset($Traducciones2[$Id]) ? $Traducciones2[$Id] : "Trd_2[$Id]";
    }    

?>



    <style>
        
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        #contenedor-funciones { margin-top: 20px; }
        #edit_contenedor-funciones { margin-top: 20px; }
        .linea-config { 
            background: #fff; margin-bottom: 15px; padding: 15px; border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 5px solid #3498db;
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        select, input { padding: 8px; border: 1px solid #ddd; border-radius: 4px; outline: none; }
        input[type="number"] { width: 80px; }
        textarea { 
            width: 100%; margin-top: 20px; font-family: 'Courier New', monospace; 
            background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; border: none; 
        }
        .etiqueta-f { font-weight: bold; color: #3498db; min-width: 30px; }
        
        /* Estilos Proyección */
        .seccion-proyeccion { margin-top: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tabla-costos { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabla-costos th, .tabla-costos td { border: 1px solid #eee; padding: 10px; text-align: center; }
        .tabla-costos th { background: #3498db; color: white; }
        .fila-highlight { background-color: #ebf5fb; font-weight: bold; }

        /* Estilos para la gráfica */
        .grafica-container { 
            width: 100%; 
            max-width: 800px; 
            margin: 0 auto 20px auto; 
            height: 350px; 
        }
    </style>


<?php
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

<body>
    <?php
        include_once 'nav.php';
        $allowed_tables = [
            'clientes',
            'customers',
            'categories',
            'customer_type',
            'wharehouses',
            'gifcard',
            'products',
            'distance_charges',
            'account',
            'venues',
            'organizations',
            'surfaces',
            'item_prices',
            'document_center',
            'price_lists'
        ];
        if (!in_array($IdTabla, $allowed_tables)) {
            die("<h3>".Trd(3)."</h3></div></body></html>");
        }   
    ?> 
    <div class="container mt-4 ">
        <h3 class="mb-4"> <?php echo Trd(1);?> <?php echo $IdTabla?></h3>
    </div>
    <?php
        include_once 'add_listado.php';     

        include_once 'f_add_form.php'; 
        include_once 'add_form.php'; 
        
        include_once 'f_edit_form.php';        
        include_once 'edit_form.php'; 

        include_once 'delete_form.php'; 
        if ($IdTabla == 'products'){
            add_listado($IdTabla);
?>
        <div class="container" id = "add_form_<?php echo $IdTabla?>_clone" style="display: none">
        <br>
        <h4 class="mb-4">Clonar producto:</h4>
            <form name="add_<?php echo $IdTabla?>_clone" id="add_<?php echo $IdTabla?>_clone" class="needs-validation" novalidate>
                <div class="row">
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                <?php
                            echo "<label for='CodigoOrigen_clone' class='form-label'>Producto a Clonar</label>";
                                echo '<select name="CodigoOrigen_clone" id="CodigoOrigen_clone" class="selectpicker form-control border-1  rounded " style="" >';
                                $Valores_Productos ='';
                            $query ="SELECT Id,Name FROM products ";
                            $stmt_dts = $db->prepare($query);
                            $stmt_dts->execute();
                            $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                            if ($tabla_dts) {
                                $Valores_Productos .='<option value = "" selected> ... </option>';
                                foreach ($tabla_dts as $tabla_dt) {
                                    $Valor         = $tabla_dt['Id'];
                                    $Descripcion   = $tabla_dt['Name'];
                                    $Valores_Productos.='<option value="'.$Valor.'">'. ($Descripcion).'</option>';
                                }
                            }
                            echo $Valores_Productos;
                            echo '</select>';
                ?>
                    </div>
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                        <label for='Name_clone' class='form-label'>Nombre Producto</label>
                            <input name="Name_clone" id="Name_clone" class=" form-control  form-control-sm" type="text" style="text-align: left;" required=""  minlength="5" maxlength="255" placeholder="ingresa el nombre para el nuevo producto"  >
                    </div>                        
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                        <br>
                        <button class="btn btn-primary" type="button" onclick="Clonar('add','<?php echo $IdTabla?>')">Clonar</button>                
                    </div>                        
                </div>
                
            </form>
        </div>
<?php
            add_form($IdTabla,$Idioma,'M');

?>
        <div class="container" id = "add_form_<?php echo $IdTabla?>_clone_edit" style="display: none">
        <br>
        <h4 class="mb-4">Clonar producto a :</h4>
            <form name="add_<?php echo $IdTabla?>_clone_edit" id="add_<?php echo $IdTabla?>_clone_edit" class="needs-validation" novalidate>
                <div class="row">
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                        <input name="product_clone_edit" id="product_clone_edit"  type="hidden"  >
                        <label for='Name_clone_edit' class='form-label'>Nombre Producto</label>
                            <input name="Name_clone_edit" id="Name_clone_edit" class=" form-control  form-control-sm" type="text" style="text-align: left;" required=""  minlength="5" maxlength="255" placeholder="ingresa el nombre para el nuevo producto"  >
                    </div>                        
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                        <br>
                        <button class="btn btn-primary" type="button" onclick="Clonar('edit','<?php echo $IdTabla?>')">Clonar</button>                
                    </div>                        
                </div>
                
            </form>
        </div>
<?php            

            edit_form($IdTabla,$Idioma,'I');

            echo '<br><h4 class="mb-4">'.Trd(29).'</h4>';
            echo '<div class="container" id="edit_form_'.$IdTabla.'" style="">';
            $TablePrices ='products_item_price';
            ?>
                <form name="edit_<?php echo $TablePrices?>" id="edit_<?php echo $TablePrices?>" class="needs-validation" novalidate>
                    <input type="hidden" name="edit_Producto" id="edit_Producto" >
                    <div class="row">
                        <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                            <?php
                            $Campo = "edit_producto_origen_pl";
                            $TablaDts = "products";
                            $CampoValor = "Id";
                            $CampoDescripcion = "Name";
                            $Filtro =   "";
                            $Titulo = Trd(26);
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" data-tipo="complete" class="selectpicker form-control border-1  rounded " style="" onchange="get_price(this.value)" >';
                                    $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                                    $columnExists = $checkColumn->fetch();                            
                                    if ($columnExists) {
                                            $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$idioma' $Filtro ";
                                    }
                                    else{
                                        $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1  $Filtro ";
                                    }
                                    $stmt_dts = $db->prepare($query);
                                    $stmt_dts->execute();
                                    $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                                    if ($tabla_dts) {
                                        echo '<option value = "" selected> ... </option>';
                                        foreach ($tabla_dts as $tabla_dt) {
                                            $Valor         = $tabla_dt[$CampoValor];
                                            $Descripcion   = $tabla_dt[$CampoDescripcion];
                                            echo '<option value="'.$Valor.'">'. ($Descripcion).'</option>';
                                        }
                                    }
                            echo '</select>';
                            ?>
                        </div>

                        <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                            <?php
                            $Campo = "edit_ItemPrice";
                            $TablaDts = "item_prices";
                            $CampoValor = "Id";
                            $CampoDescripcion = "PriceName";
                            $Filtro =   "";
                            $Titulo = Trd(27);
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" data-tipo="complete" class="selectpicker form-control border-1  rounded " style="" onchange="get_json_price(this.value)" >';

                                    $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                                    $columnExists = $checkColumn->fetch();                            
                                    if ($columnExists) {
                                            $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$idioma' $Filtro ";
                                    }
                                    else{
                                        $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1  $Filtro ";
                                    }
                                    $stmt_dts = $db->prepare($query);
                                    $stmt_dts->execute();
                                    $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                                    if ($tabla_dts) {
                                        echo '<option value = "" selected> ... </option>';
                                        foreach ($tabla_dts as $tabla_dt) {
                                            $Valor         = $tabla_dt[$CampoValor];
                                            $Descripcion   = $tabla_dt[$CampoDescripcion];
                                            echo '<option value="'.$Valor.'">'. ($Descripcion).'</option>';
                                        }
                                    }
                            echo '</select>';
                            ?>
                        </div>
                        <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>

                            <br><div class="form-check  form-switch">
                            <input name="edit_Taxable" id="edit_Taxable" class="form-check-input" type="checkbox">
                                <label class="form-check-label" for="edit_Taxable">
                                    <?php echo Trd(28)?>
                                </label>
                            </div>

                        </div>
                    </div>
                    <div id="edit_contenedor-funciones">
                        
                    </div>

                    <input type="hidden" id ="edit_JsonPrice" name ="edit_JsonPrice">

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button class="btn btn-primary" type="submit"><?php echo Trd(7)?></button>
                    </div>
                </form>            
            <?php
            echo "</div>";
            $Tabla = 'products_categories';
            echo '<br><h4 class="mb-4">'.Trd(30).'</h4>';
                add_listado($Tabla);                
                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');
            $Tabla2 = 'products_images';
            echo '<br><h4 class="mb-4">'.Trd(31).'</h4>';
                add_listado($Tabla2);
                add_form($Tabla2,$Idioma,'D');
                edit_form($Tabla2,$Idioma,'D');
            $Tabla3 = 'packing_list';
            echo '<br><h4 class="mb-4">'.Trd(32).'</h4>';
?>
        <div class="container" id = "add_form_<?php echo $Tabla3?>_clone" style="">
            <form name="add_<?php echo $Tabla3?>_clone" id="add_<?php echo $Tabla3?>_clone" class="needs-validation" novalidate>
                <div class="row">
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                <?php
                            echo "<label for='CodigoOrigen_clone_$Tabla3' class='form-label'>Copiar información de: </label>";
                                echo '<select name="CodigoOrigen_clone_'.$Tabla3.'" id="CodigoOrigen_clone_'.$Tabla3.'" class="selectpicker form-control border-1  rounded " style="" >';
                                echo $Valores_Productos;
                            echo '</select>';
                ?>
                    </div>
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                        <br>
                        <button class="btn btn-primary" type="button" onclick="Copiar(3)">Copiar</button>
                    </div>                        
                </div>
                
            </form>
        </div>
<?php
                add_listado($Tabla3);
                add_form($Tabla3,$Idioma,'D');
                edit_form($Tabla3,$Idioma,'D');
            $Tabla4 = 'related_products';
            echo '<br><h4 class="mb-4">'.Trd(33).'</h4>';
?>
        <div class="container" id = "add_form_<?php echo $Tabla4?>_clone" style="">
            <form name="add_<?php echo $Tabla4?>_clone" id="add_<?php echo $Tabla4?>_clone" class="needs-validation" novalidate>
                <div class="row">
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                <?php
                            echo "<label for='CodigoOrigen_clone_$Tabla4' class='form-label'>Copiar información de: </label>";
                                echo '<select name="CodigoOrigen_clone_'.$Tabla4.'" id="CodigoOrigen_clone_'.$Tabla4.'" class="selectpicker form-control border-1  rounded " style="" >';
                                echo $Valores_Productos;
                            echo '</select>';
                ?>
                    </div>
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                        <br>
                        <button class="btn btn-primary" type="button" onclick="Copiar(4)">Copiar</button>
                    </div>                        
                </div>
                
            </form>
        </div>
<?php            
                add_listado($Tabla4);
                add_form($Tabla4,$Idioma,'D');
                edit_form($Tabla4,$Idioma,'D');                                
            $Tabla5 = 'upselling_products';
            echo '<br><h4 class="mb-4">'.Trd(34).'</h4>';
?>
        <div class="container" id = "add_form_<?php echo $Tabla5?>_clone" style="">
            <form name="add_<?php echo $Tabla5?>_clone" id="add_<?php echo $Tabla5?>_clone" class="needs-validation" novalidate>
                <div class="row">
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                <?php
                            echo "<label for='CodigoOrigen_clone_$Tabla5' class='form-label'>Copiar información de: </label>";
                                echo '<select name="CodigoOrigen_clone_'.$Tabla5.'" id="CodigoOrigen_clone_'.$Tabla5.'" class="selectpicker form-control border-1  rounded " style="" >';
                                echo $Valores_Productos;
                            echo '</select>';
                ?>
                    </div>
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                        <br>
                        <button class="btn btn-primary" type="button" onclick="Copiar(5)">Copiar</button>
                    </div>                        
                </div>
                
            </form>
        </div>
<?php            
                add_listado($Tabla5);
                add_form($Tabla5,$Idioma,'D');
                edit_form($Tabla5,$Idioma,'D');
            $Tabla6 = 'relationship_products';
            echo '<br><h4 class="mb-4">'.Trd(35).'</h4>';
?>
        <div class="container" id = "add_form_<?php echo $Tabla6?>_clone" style="">
            <form name="add_<?php echo $Tabla6?>_clone" id="add_<?php echo $Tabla6?>_clone" class="needs-validation" novalidate>
                <div class="row">
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                <?php
                            echo "<label for='CodigoOrigen_clone_$Tabla6' class='form-label'>Copiar información de: </label>";
                                echo '<select name="CodigoOrigen_clone_'.$Tabla6.'" id="CodigoOrigen_clone_'.$Tabla6.'" class="selectpicker form-control border-1  rounded " style="" >';
                                echo $Valores_Productos;
                            echo '</select>';
                ?>
                    </div>
                    <div class='col-12 col-sm-12 col-md-8 col-lg-4 col-xl-4 col-xxl-4'>
                        <br>
                        <button class="btn btn-primary" type="button" onclick="Copiar(6)">Copiar</button>
                    </div>                        
                </div>
                
            </form>
        </div>
<?php            
                add_listado($Tabla6);
                add_form($Tabla6,$Idioma,'D');
                edit_form($Tabla6,$Idioma,'D');                
            $Tabla7 = 'cost_products';
            echo '<br><h4 class="mb-4">'.Trd(36).'</h4>';
                add_listado($Tabla7);
                add_form($Tabla7,$Idioma,'D');
                edit_form($Tabla7,$Idioma,'D');
            $Tabla8 = 'products_files';
            echo '<br><h4 class="mb-4">'.Trd(37).'</h4>';
                add_listado($Tabla8);
                add_form($Tabla8,$Idioma,'D');
                edit_form($Tabla8,$Idioma,'D');


            echo '</div>';  

            delete_form($IdTabla);
            delete_form($Tabla);
            delete_form($Tabla2);
            delete_form($Tabla3);
            delete_form($Tabla4);
            delete_form($Tabla5);
            delete_form($Tabla6);
            delete_form($Tabla7);
            delete_form($Tabla8);
            

        }
        elseif ($IdTabla == 'distance_charges'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
                //add_listado($Tabla);
                //add_form($Tabla,$Idioma,'F');
            edit_form($IdTabla,$Idioma,'I');

            $Tabla = 'distance_charges_zip_code';
            echo '<h4 class="mb-4">'.Trd(38).'</h4>';
                add_listado($Tabla);                
                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');

            $Tabla2 = 'distance_charges_distance';
            echo '<h4 class="mb-4">'.Trd(39).'</h4>';
                add_listado($Tabla2);
                add_form($Tabla2,$Idioma,'D');
                edit_form($Tabla2,$Idioma,'D');

            $Tabla3 = 'distance_charges_states';
            echo '<h4 class="mb-4">'.Trd(40).'</h4>';
                add_listado($Tabla3);
                add_form($Tabla3,$Idioma,'D');
                edit_form($Tabla3,$Idioma,'D');

            $Tabla4 = 'distance_charges_totals';
            echo '<h4 class="mb-4">'.Trd(41).'</h4>';
                add_listado($Tabla4);
                add_form($Tabla4,$Idioma,'D');
                edit_form($Tabla4,$Idioma,'D');

            echo '</div>';            


            delete_form($IdTabla);
            delete_form($Tabla);
            delete_form($Tabla2);
            delete_form($Tabla3);
            delete_form($Tabla4);


        }
        elseif ($IdTabla == 'account'){
            edit_form($IdTabla,$Idioma,'M','');
        }
        elseif ($IdTabla == 'item_prices'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
            edit_form($IdTabla,$Idioma,'M');
            delete_form($IdTabla);
            ?>
            <div class="container">
                <div class="seccion-proyeccion">
                    <h3><?php Trd_2(3)?></h3>

                    <div class="grafica-container">
                        <canvas id="costosChart"></canvas>
                    </div>        
                </div>
            </div>
            <?php
        }        
        elseif ($IdTabla == 'customers'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
            edit_form($IdTabla,$Idioma,'I');

            $Tabla = 'customer_addresses';
            echo '<h4 class="mb-4">Direcciones Extras</h4>';
                add_listado($Tabla);                
                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');

            echo '</div>';
            delete_form($IdTabla);
            delete_form($Tabla);                

        }
        elseif ($IdTabla == 'price_lists'){
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
            edit_form($IdTabla,$Idioma,'I');

            $Tabla = 'detail_price_lists';
            echo '<h4 class="mb-4">Detalle lista precios</h4>';
                add_listado($Tabla);                
                add_form($Tabla,$Idioma,'D');
                edit_form($Tabla,$Idioma,'D');

            echo '</div>';
            delete_form($IdTabla);
            delete_form($Tabla);                

        }        
        else{
            add_listado($IdTabla);
            add_form($IdTabla,$Idioma,'M');
            edit_form($IdTabla,$Idioma,'M');
            delete_form($IdTabla);            
        }

    ?>    
<script>
    const LOGIN_URL =  '<?php echo URL_BASE;?>/api/login';
    const API_BASE_URL = '<?php echo URL_BASE;?>/api/';    
    const TOKEN = localStorage.getItem('apiToken'); 
    let IdSelected = '<?php echo $Id2;?>';
    let IdDelete = '';


    function Cancel_add(Id){
        $("#listado_"+Id).show();
        $("#add_form_"+Id).hide();
        if (Id=='products'){
            $("#add_form_products_clone").hide();
        }
    }
    function Cancel_edit(Id){
        $("#listado_"+Id).show();
        $("#edit_form_"+Id).hide();
        if (Id=='products'){
            $("#add_form_products_clone_edit").hide();
        }
    }    

    function AgregarRegistro(Id){
        $("#listado_"+Id).hide();
        $("#add_form_"+Id).show();
        if (Id=='products'){
            $("#add_form_products_clone").show();
        }
    }

    function Clonar(Id,IdTabla){

        if (Id == 'add'){
            if (!$('#CodigoOrigen_clone').val()){
                alert('Necesita seleccionar producto a copiar')
                return;
            }
            if (!$('#Name_clone').val()){
                alert('Necesita ingresar el nombre para el nuevo producto')
                return;
            }            
            var misHeaders = {
                'Authorization': 'Bearer ' + TOKEN
            };

            misHeaders['ID2'] = 'products';
            misHeaders['ID3'] = 'Id';
            misHeaders['ID4'] = $('#Name_clone').val();

            $.ajax({
                url: API_BASE_URL + "clone_record/"+$('#CodigoOrigen_clone').val(),
                type: 'POST',
                contentType: 'application/json', // Mantenemos el Content-Type como JSON
                headers: misHeaders,

                success: function(response) {
                    let IdInsert = 0;
                    Object.entries(response).forEach(([clave, valor]) => {
                        if (clave == "Id")
                            IdInsert = valor;
                    });
                    setTimeout(() => {
                        showToast('✅ <?php echo Trd(12)?> ' + IdInsert);
                    }, 500);

                    $("#add_form_products_clone").hide();
                    //$("#listado_"+IdTabla).show();
                    $("#add_form_"+IdTabla).hide();
                    getRecordData(IdInsert,IdTabla)
                    //$("#add_form_products_clone_edit").hide();

                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';

                    setTimeout(() => {
                        showToast('❌ <?php echo Trd(13)?> ' + errorMessage);
                    }, 500);
                }
            });        

        }
        else{
            if (!$('#Name_clone_edit').val()){
                alert('Necesita ingresar el nombre para el nuevo producto')
                return;
            }         
            //IdSelected

            var misHeaders = {
                'Authorization': 'Bearer ' + TOKEN
            };

            misHeaders['ID2'] = 'products';
            misHeaders['ID3'] = 'Id';
            misHeaders['ID4'] = $('#Name_clone_edit').val();

            $.ajax({
                url: API_BASE_URL + "clone_record/"+IdSelected,
                type: 'POST',
                contentType: 'application/json', // Mantenemos el Content-Type como JSON
                headers: misHeaders,

                success: function(response) {
                    let IdInsert = 0;
                    Object.entries(response).forEach(([clave, valor]) => {
                        if (clave == "Id")
                            IdInsert = valor;
                    });
                    setTimeout(() => {
                        showToast('✅ <?php echo Trd(12)?> ' + IdInsert);
                    }, 500);

                    $("#add_form_products_clone_edit").hide();
                    //$("#listado_"+IdTabla).show();
                    $("#edit_form_"+IdTabla).hide();
                    getRecordData(IdInsert,IdTabla)
                    //$("#add_form_products_clone_edit").hide();

                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';

                    setTimeout(() => {
                        showToast('❌ <?php echo Trd(13)?> ' + errorMessage);
                    }, 500);
                }
            });              

        }
    }    

    function Copiar(Id){
    
            var misHeaders = {
                'Authorization': 'Bearer ' + TOKEN
            };

            //misHeaders['ID2'] = 'products';
            //misHeaders['ID3'] = 'Id';
            //misHeaders['ID4'] = $('#Name_clone_edit').val();    

        switch (Id) {
        case 3:
            //alert($('#CodigoOrigen_clone_packing_list').val())
            misHeaders['ID2'] = 'packing_list';
            misHeaders['ID3'] = 'Producto_pl';
            misHeaders['ID4'] = $('#CodigoOrigen_clone_packing_list').val();                
            break;
        case 4:
            //alert($('#CodigoOrigen_clone_related_products').val())
            misHeaders['ID2'] = 'related_products';
            misHeaders['ID3'] = 'Producto_rp';
            misHeaders['ID4'] = $('#CodigoOrigen_clone_related_products').val();               
            break;
        case 5:
            //alert($('#CodigoOrigen_clone_upselling_products').val())
            misHeaders['ID2'] = 'upselling_products';
            misHeaders['ID3'] = 'Producto_up';
            misHeaders['ID4'] = $('#CodigoOrigen_clone_upselling_products').val();             
            break;
        case 6:
            //alert($('#CodigoOrigen_clone_relationship_products').val())
            misHeaders['ID2'] = 'relationship_products';
            misHeaders['ID3'] = 'Producto_sp';
            misHeaders['ID4'] = $('#CodigoOrigen_clone_relationship_products').val();               
            break;
        default:
            break;
        }    



            $.ajax({
                url: API_BASE_URL + "copy_records/"+IdSelected,
                type: 'POST',
                contentType: 'application/json', // Mantenemos el Content-Type como JSON
                headers: misHeaders,

                success: function(response) {
                    let IdInsert = 0;
                    Object.entries(response).forEach(([clave, valor]) => {
                        if (clave == "Id")
                            IdInsert = valor;
                    });

        switch (Id) {
        case 3:
listado('packing_list');               
            break;
        case 4:
listado('related_products');;               
            break;
        case 5:
listado('upselling_products');             
            break;
        case 6:
listado('relationship_products');               
            break;
        default:
            break;
        }                       

                    
                    
                    
                    

                    setTimeout(() => {
                        showToast('✅ <?php echo Trd(12)?> ' + IdInsert);
                    }, 500);

                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';

                    setTimeout(() => {
                        showToast('❌ <?php echo Trd(13)?> ' + errorMessage);
                    }, 500);
                }
            });            

    }

    function createNewRecord(form,IdTabla) {
        if (!TOKEN) {
            $('#apiMessage').text('Error: Token de autenticación no encontrado. Inicie sesión.').css('color', 'red');
            return;
        }

        const formDataArray = form.serializeArray();
        const formData = {};

        $.each(formDataArray, function(index, field) {
            // 1. Buscamos el elemento real en el DOM para poder leer sus atributos
            // Usamos el selector de atributo [name="..."]
            const inputReal = $('[name="' + field.name + '"]', form);
            const tipo = inputReal.attr('type');
            //alert(tipo);
            // 2. Aplicamos la lógica según el tipo o clase
            if (inputReal.hasClass('currency')) {
                formData[field.name] = LimpiaMonedaMejorada(field.value);
            } 
            else if (tipo === 'hidden') {
                // Si es hidden, asignamos IdSelected
                formData[field.name] = IdSelected;
            } 
            else {
                // Para el resto, el valor tal cual
                formData[field.name] = field.value;
            }
        });

        // 3. Realizar la solicitud AJAX
        $.ajax({
            url: API_BASE_URL + IdTabla,
            type: 'POST',
            contentType: 'application/json', // Mantenemos el Content-Type como JSON
            headers: {
                'Authorization': 'Bearer ' + TOKEN 
            },
            // Enviamos el objeto convertido a JSON String
            data: JSON.stringify(formData),
            success: function(response) {
                listado(IdTabla);
                $("#listado_"+IdTabla).show();
                $("#add_form_"+IdTabla).hide();
                setTimeout(() => {
                    showToast('✅ <?php echo Trd(12)?> ' + response.message);
                }, 500);
                form[0].reset(); 
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                $("#listado_"+IdTabla).show();
                $("#add_form_"+IdTabla).hide();
                setTimeout(() => {
                    showToast('❌ <?php echo Trd(13)?> ' + errorMessage);
                }, 500);
            }
        });
    }


    function updateRecord(form,IdTabla) {
        if (!TOKEN) {
            $('#apiMessage').text('Error: Token de autenticación no encontrado. Inicie sesión.').css('color', 'red');
            return;
        }

        const disabledFields = form.find(':disabled');
        disabledFields.prop('disabled', false);
        const formDataArray = form.serializeArray();
        disabledFields.prop('disabled', true);
        const formData = {};
        // Es crucial que los 'name' del formulario coincidan con las claves JSON de tu API (Nombre, Direccion)
        $.each(formDataArray, function(index, field) {
            // Usamos el campo 'name' del formulario como la clave del objeto
            var fieldName = field.name;
            fieldName = fieldName.replace("edit_", "");
            if ($("#"+fieldName).hasClass('currency')) {
                formData[fieldName] = LimpiaMonedaMejorada(field.value);
            }
            else{
                formData[fieldName] = field.value;
            }
        });
        // 3. Realizar la solicitud AJAX
        $.ajax({
            url: API_BASE_URL + IdTabla,
            type: 'PUT',
            contentType: 'application/json', // Mantenemos el Content-Type como JSON
            headers: {
                'Authorization': 'Bearer ' + TOKEN 
            },
            // Enviamos el objeto convertido a JSON String
            data: JSON.stringify(formData),
            success: function(response) {
                if (IdTabla != 'products_item_price'){
                    listado(IdTabla);
                    $("#listado_"+IdTabla).show();
                    $("#edit_form_"+IdTabla).hide();
                    form[0].reset(); 
                }
                setTimeout(() => {
                    showToast('✅ <?php echo Trd(14)?> ' + response.message);
                }, 500);
                
            },
            error: function(xhr) {
                $("#listado_"+IdTabla).show();
                $("#edit_form_"+IdTabla).hide();                
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                setTimeout(() => {
                    showToast('❌ <?php echo Trd(15)?> ' + errorMessage);
                }, 500);
            }
        });
    }    



$(document).ready(function() {

    attemptLogin('admin', '1234'); 
    
    if (TOKEN) {
        //getRecordData(1); 
    } else {
        console.warn('No se encontró el token. Necesita iniciar sesión primero.');
    }

    // Agregar Bootstrap JS si es necesario para funcionalidades adicionales
    //if (typeof bootstrap === 'undefined') {
    //    $.getScript("https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js");
    //}

    listado('<?php echo $IdTabla?>')

    $(".currency").on("blur", function(){
        var numero = LimpiaMonedaMejorada($(this).val());
        $(this).val(formatter.format(numero));
    });
    
    $(".currency").on("focus", function(){
        var valor = $(this).val();
        var numero = LimpiaMonedaMejorada(valor);
        $(this).val(numero === 0 ? "" : numero.toString());
    });

    <?php 
        if ($IdTabla =='account'){
            echo "getRecordData(1,'".$IdTabla."');";
        }
    
    ?>

});

function attemptLogin(username, password) {
    $.ajax({
        url: LOGIN_URL,
        type: 'POST',
        contentType: 'application/json', // Indica que enviamos JSON
        data: JSON.stringify({
            username: username,
            password: password
        }),
        success: function(response) {
            // Éxito: Guardar el token para futuras llamadas
            const jwtToken = response.jwt;
            console.log('Login exitoso. Token:', jwtToken);
            
            // *** Almacena el token de forma segura (ej: localStorage) ***
            localStorage.setItem('apiToken', jwtToken); 
            
        },
        error: function(xhr, status, error) {
            // Error: Credenciales inválidas (401) o error del servidor
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido.';
            console.error('Error de login:', errorMessage);
            alert('Fallo el inicio de sesión: ' + errorMessage);
        }
    });
}

function listado(IdTabla) {

    var misHeaders = {
        'Authorization': 'Bearer ' + TOKEN
    };

    // 2. Agregamos ID2 solo si IdSelected no es null, undefined o vacío
    if (IdSelected) {
        misHeaders['ID2'] = IdSelected;
    }    

    $.ajax({
        url: API_BASE_URL + IdTabla+'/?page=1&limit=10&like='+$('#Search_'+IdTabla).val()+'&lang=<?php echo $Idioma?>',
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: misHeaders,
        success: function(response) {
            renderTable(response.data,response.titulos,IdTabla,response.tipos);
            renderPagination(response.metadata,IdTabla);            
        },
        error: function(xhr, status, error) {
            if (xhr.status === 401) {
                console.error('Acceso denegado. Token expirado o inválido.');
                // Aquí puedes redirigir al login o limpiar el token
            } else {
                console.error('Error al obtener registro:', error);
            }
        }
    });
}

function getRecordData(Id,IdTabla) {
    if (IdTabla == 'products')
        IdSelected = Id;

    if (IdTabla == 'distance_charges')
        IdSelected = Id;    

    if (IdTabla == 'customers')
        IdSelected = Id;    

    if (IdTabla == 'price_lists')
        IdSelected = Id;

    $("#listado_"+IdTabla).hide();
    $("#edit_form_"+IdTabla).show();
    //alert(IdTabla)
    $.ajax({
        url: API_BASE_URL + IdTabla+'/' + Id,
        type: 'GET',
        dataType: 'json', // Indica que esperamos JSON
        headers: {
            // *** Aquí se adjunta el token en el encabezado Authorization ***
            'Authorization': 'Bearer ' + TOKEN 
        },
        success: function(response) {
            //console.log('Datos del registro:', response);
            Object.entries(response).forEach(([clave, valor]) => {
                // Buscar input por id igual a la clave (case-insensitive)
                const input = document.getElementById("edit_"+clave);
                //alert("edit_"+clave +" "+ input.type + " " + valor)
                if (input) {
                    //alert(input.type)
                    //alert(valor)
                    if (input.type === "checkbox") {
                        // Si el valor es 1, true o "1", se marca (checked = true)
                        input.checked = (valor == 1);
                    }
                    else if (input.type === "select-one") {
                        if (input.dataset.tipo =='complete'){
                            //alert(clave)
                            const instance = myChoicesInstances["edit_" + clave];

                            if (instance) {
                                //alert(valor)
                                // 2. Usamos el método de Choices para cambiar el valor
                                instance.setChoiceByValue(String(valor));
                                //alert(valor)
                            }

                        }
                        else{
                        var checkSelect = setInterval(function() {
                            var $select = $("#edit_" + clave);
                            if ($select.find('option').length > 0) {
                                $select.val(valor).trigger('change');
                                console.log("Asignado con éxito. Deteniendo intentos.");
                                clearInterval(checkSelect); // Esto detiene el bucle de espera
                            } else {
                                console.log("El select aún está vacío, esperando 2 segundos más...");
                            }
                        }, 500); // Se ejecuta cada 500ms
                        //var $select = $("#edit_" + clave);                        
                        //if ($select.find('option').length === 0) {
                        //    setTimeout(function() {
                        //        $select.val(valor).trigger('change');
                        //    }, 500);
                        //} else {
                        //    $select.val(valor).trigger('change');
                        //}                        
                        }
                    }
                    else if (input.type === "textarea") {
                        if (input.dataset.tipo =='html'){
                            //alert(valor)
                            $('#edit_'+clave).summernote('code',valor);
                        }
                        else
                            input.value = valor || '';
                    }
                    else {
                        // Para inputs normales (text, hidden, etc.)
                        //alert(input.type)
                        //alert(valor)
                        if ((IdTabla == 'products_images' || IdTabla == 'related_products')  && input.type == 'file'){
                            var $select = $("#file_edit_" + clave+"_1")
                            $select.val(valor);
                            //alert(clave)
                            CargaImagen(clave,'gallery_file2_edit_'+clave);
                        }
                        else{
                            input.value = valor || '';
                        }
                        
                    }
                }
                else{
                    for (let i = 1; i <= 10; i++) {
                        const input = document.getElementById("edit_"+clave+"_"+i);
                        if (input) {

                            if (input.value == valor) {
                                input.checked = true; 
                            } else {
                                input.checked = false; // Opcional: desmarca los demás
                            }                        

                        }
                    }
                }
            });
            if (IdTabla == 'products'){
                $("#add_form_products_clone_edit").show();
                listado('products_categories');
                listado('products_images');
                listado('packing_list');
                listado('related_products');
                listado('upselling_products');
                listado('relationship_products');
                listado('cost_products');
                listado('products_files');
                getRecordData(IdSelected,'products_item_price')
            }
            else if (IdTabla == 'distance_charges'){
                listado('distance_charges_zip_code');
                listado('distance_charges_distance');
                listado('distance_charges_states');
                listado('distance_charges_totals');
            }
            else if (IdTabla == 'customers'){
                listado('customer_addresses');
            }
            else if (IdTabla == 'price_lists'){
                listado('detail_price_lists');
            }else if (IdTabla == 'item_prices' || IdTabla == 'products_item_price'){
                cargar();
                if (IdTabla == 'products_item_price'){
                    //const contenedor = document.getElementById('edit_contenedor-funciones');
                    //const inputs = contenedor.querySelectorAll('input');
                    //inputs.forEach(input => {
                    //    input.disabled = true;
                    //});
                    $('#edit_contenedor-funciones').find('input, select, textarea').prop('disabled', true);
                }
            }
            <?php 
                //if ($IdTabla == 'products'){
                //    echo "listado('products_categories')";
                //}
            ?>
        },
        error: function(xhr, status, error) {
            if (xhr.status === 401) {
                console.error('Acceso denegado. Token expirado o inválido.');
                // Aquí puedes redirigir al login o limpiar el token
            } else {
                console.error('Error al obtener registro:', error);
            }
        }
    });
}

function questionDelete(Id,IdTabla){
    IdDelete= Id;
}

function deleteRecord(Id,IdTabla) {
    if (!TOKEN) return;
    $.ajax({
        url: API_BASE_URL + IdTabla,
        type: 'DELETE',
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + TOKEN
        },
        data: JSON.stringify({
            Id: Id,
            IId: Id
        }),
        success: function(response) {
            listado(IdTabla);
            console.log('Registro borrado:', response.message);
            setTimeout(() => {
                showToast('✅ <?php echo Trd(16)?> ' + response.message);
            }, 500);            
        },
        error: function(xhr) {
            console.error('Error al crear:', xhr.responseJSON.message);
            setTimeout(() => {
                showToast('❌ <?php echo Trd(17)?> ' + errorMessage);
            }, 500);            
        }
    });
}


        // Función para renderizar la tabla
        function renderTable(data,titulos,IdTabla,tipos) {
            //alert(IdTabla)
            const $container = $('#table-container_'+IdTabla);
            
            if (data.length === 0) {
                $container.html('<div class="alert alert-info text-center"><?php echo Trd(42)?></div>');
                return;
            }
            
            // Obtener nombres de columnas (usando las claves del primer objeto)
            const columns = Object.keys(data[0]);
            
            // Crear la tabla
            let html = '<table class="table table-sm table-striped table-hover">';
            
            // Cabecera de la tabla
            html += '<thead class="table-light">';
            html += '<tr>';
            //columns.forEach(col => {
            //    html += `<th >${formatColumnName(col)}</th>`;
            //});
            let alineaciones = [];
            titulos.forEach(row => {
                html += `<th style="text-align: center">${row['Titulo'] || ''}</th>`;
                let alineacion = row['Alineacion'] || 'center';
                alineaciones.push(alineacion);
            });

            let Tipos = [];
            tipos.forEach(row => {
                let tipo = row['TipoCampo'];
                Tipos.push(tipo);
            });
            if (IdTabla == 'products_images')
                html += `<th >Orden</th>`;

            html += `<th ></th>`;
            html += '</tr>';
            html += '</thead>';
            
            // Cuerpo de la tabla
            html += '<tbody>';

            data.forEach(row => {
                html += '<tr>';
                Id='';
                idx=0;

                columns.forEach(col => {                
                if (Tipos[idx] == 'checkbox'){
                    if (row[col] == 1)
                        html += `<td style="text-align: ${alineaciones[idx]}" >✅</td>`;
                    else
                        html += `<td style="text-align: ${alineaciones[idx]}" >❌</td>`;
                }
                else if (Tipos[idx] == 'file'){
                        html += `<td style="text-align: ${alineaciones[idx]}" ><a href="ajax/tmp/${row[col] || ''}" target="_blank" rel="noopener noreferrer">${row[col] || ''}</a></td>`;
                }                
                else{
                    html += `<td style="text-align: ${alineaciones[idx]}" >${row[col] || ''}</td>`;
                }
                    if (col == 'Id' || col == 'IId' || col == 'IdWhareHouse')
                        Id = row[col];
                    idx=idx+1;
                });

            if (IdTabla == 'products_images'){
                html += `<td style="text-align: center">
                            
                    <button class="btn-nav" title="Inicio" onclick="orden('I',${IdSelected},${Id})">
                        <i class="fa-solid fa-angles-left"></i>
                    </button>
                    <button class="btn-nav" title="Anterior" onclick="orden('A',${IdSelected},${Id})">
                        <i class="fa-solid fa-angle-left"></i>
                    </button>
                    <button class="btn-nav" title="Siguiente" onclick="orden('S',${IdSelected},${Id})">
                        <i class="fa-solid fa-angle-right"></i>
                    </button>
                    <button class="btn-nav" title="Último" onclick="orden('U',${IdSelected},${Id})">
                        <i class="fa-solid fa-angles-right"></i>
                    </button>

                        </td>`;                
            }
                

                html += `<td style="text-align: center">
                            <button type="button" class="btn btn-primary btn-sm" onclick="getRecordData('${Id}','${IdTabla}')">
                                <i class="fas fa-pen"></i>
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" onclick="questionDelete('${Id}','${IdTabla}')"  data-bs-toggle="modal" data-bs-target="#delete_${IdTabla}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>`;                        

                html += '</tr>';
            });

            html += '</tbody>';
            html += '</table>';
            
            $container.html(html);
        }

        // Función para formatear nombres de columnas
        function formatColumnName(columnName) {
            return columnName
                .replace(/([A-Z])/g, ' $1')
                .replace(/^./, str => str.toUpperCase());
        }

        // Función para renderizar la paginación
        function renderPagination(metadata,IdTabla) {
            const $container = $('#pagination-container_'+IdTabla);
            
            // Información de metadata
            const metadataHtml = `
                <div class="metadata-info">
                    <?php echo Trd(18)?> <strong>${(metadata.pagina_actual - 1) * metadata.registros_por_pagina + 1}</strong> 
                    <?php echo Trd(19)?> <strong>${Math.min(metadata.pagina_actual * metadata.registros_por_pagina, metadata.total_registros)}</strong> 
                    <?php echo Trd(20)?> <strong>${metadata.total_registros}</strong> <?php echo Trd(21)?>
                </div>
            `;
            
            // Generar paginación solo si hay más de una página
            let paginationHtml = '';
            if (metadata.total_paginas > 1) {
                paginationHtml = '<nav><ul class="pagination">';
                
                // Botón Anterior
                if (metadata.pagina_actual > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" onclick="changePage(${metadata.pagina_actual - 1},'${IdTabla}')" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    `;
                }
                
                // Números de página
                const totalPages = metadata.total_paginas;
                const currentPage = metadata.pagina_actual;
                
                // Lógica para mostrar páginas (máximo 5 páginas visibles)
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                
                // Ajustar si estamos cerca del final
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }
                
                // Primera página si no está incluida
                if (startPage > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" onclick="changePage(1,'${IdTabla}')">1</a>
                        </li>
                    `;
                    if (startPage > 2) {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                // Páginas visibles
                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" onclick="changePage(${i},'${IdTabla}')">${i}</a>
                        </li>
                    `;
                }
                
                // Última página si no está incluida
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" onclick="changePage(${totalPages},'${IdTabla}')">${totalPages}</a>
                        </li>
                    `;
                }
                
                // Botón Siguiente
                if (metadata.pagina_actual < metadata.total_paginas) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" onclick="changePage(${metadata.pagina_actual + 1},'${IdTabla}')" aria-label="Siguiente">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    `;
                }
                
                paginationHtml += '</ul></nav>';
            }
            
            $container.html(metadataHtml + paginationHtml);
        }

        // Función para cambiar de página (simulación)
        function changePage(pageNumber,IdTabla) {
            // Aquí normalmente harías una nueva llamada AJAX
            // Para este ejemplo, solo actualizamos la página actual
            console.log(`Cambiando a página ${pageNumber}`);
            
            // Simular carga
            const loadingAlert = '<div class="alert alert-info text-center"><?php echo Trd(43)?> ' + pageNumber + '...</div>';
            $('#table-container').html(loadingAlert);
            $.ajax({
                url: API_BASE_URL + IdTabla +'/?page='+pageNumber+'&limit=10&like='+$('#Search_'+IdTabla).val()+'&lang=<?php echo $Idioma?>',
                type: 'GET',
                dataType: 'json', // Indica que esperamos JSON
                headers: {
                    // *** Aquí se adjunta el token en el encabezado Authorization ***
                    'Authorization': 'Bearer ' + TOKEN 
                },
                success: function(response) {
                    setTimeout(() => {
                            renderTable(response.data,response.titulos,IdTabla,response.tipos);
                            renderPagination(response.metadata,IdTabla);                            
                        // Mostrar mensaje de éxito
                        showToast(`<?php echo Trd(22)?> ${pageNumber} <?php echo Trd(23)?>`);
                    }, 500);                    
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 401) {
                        console.error('Acceso denegado. Token expirado o inválido.');
                        // Aquí puedes redirigir al login o limpiar el token
                    } else {
                        console.error('Error al obtener registro:', error);
                    }
                }
            });            


        }

        // Función para mostrar notificaciones toast
        function showToast(message) {
            // Crear toast si no existe
            if ($('.toast').length === 0) {
                $('body').append(`
                    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header">
                                <strong class="me-auto">Sistema</strong>
                                <small><?php echo Trd(24)?></small>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">${message}</div>
                        </div>
                    </div>
                `);
            }
            
            // Mostrar toast
            const toastElement = document.getElementById('liveToast');
            const toast = new bootstrap.Toast(toastElement);
            $('.toast-body').text(message);
            toast.show();
        }

        // Función principal para inicializar
        //function initializeTable() {
        //    renderTable(responseData.data);
        //    renderPagination(responseData.metadata);
        //}

        //VALIDACION
        (function () {
        'use strict'
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
            })
        })()

        //SELECCION AUTOMATICA
        function activarSeleccionAutomatica(formSelector) {
            const formulario = document.querySelector(formSelector);
            if (!formulario) return;

            formulario.addEventListener('focusin', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                e.target.select();
            }
            });
        }        
        activarSeleccionAutomatica("#add_<?php  echo $IdTabla; ?>" );
        activarSeleccionAutomatica("#edit_<?php  echo $IdTabla; ?>" );

        //SOLO CAPUTAR NUMEROS
        $(".numbers-only").keypress(function (e) {
            if (e.which != 8 && e.which != 0  && (e.which < 48 || e.which > 57)) {
                return false;
            }
        });
        //CAPTURAR DECIMALES
        $(".decimals").keypress(function (e) {
            if(e.which == 46){
                if($(this).val().indexOf('.') != -1) {
                    return false;
                }
            }    
            if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
                return false;
            }
        });          


        //PONER FORMATO DE MONEDA A CAMPOS CURRENCY
        var formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        function FormatoMoneda(Campo){
            var valor = $("#"+Campo).val();
            var numero = LimpiaMonedaMejorada(valor);
            $("#"+Campo).val(formatter.format(numero));
        }

        function FormatoMonedav(Valor){
            return formatter.format(Valor);
        }

        function LimpiaMonedaMejorada(Valor){
            // Eliminar todos los símbolos no numéricos excepto punto decimal
            var valorLimpio = Valor.replace(/[^0-9.-]/g, '');
            
            // Convertir a número (float)
            var numero = parseFloat(valorLimpio);
            
            // Si no es número válido, retornar 0
            return isNaN(numero) ? 0 : numero;
        }


        function change_send2(valor,campo,receptor,tabla){
            var parametros = {"action":"ajax","valor":valor,'campo':campo,'receptor':receptor,'tabla':tabla};
            $.ajax({
                type: "POST",
                url:'ajax/buscarvalor2.php',
                data: parametros,
                beforeSend: function(objeto){
                    //$('#loadModal').show();
                    //$('.modal-backdrop').show();
                },
                success:function(data){
                    $('#'+receptor+' option').remove();
                    var obj = JSON.parse(JSON.stringify(data));
                    $.each(obj, function(key,value) {
                        $('#'+receptor).append($('<option>', { 
                            value: value.Id,
                            text : value.Descripcion 
                        }));                    

                    });
                }
            }).fail( function( jqXHR, textStatus, errorThrown ) {
                alert( 'Error!!' );
            });
        }  


        function uploadFile(file,field,type){
            //alert(file)
            //alert(field)
            //alert(type)
            var url = 'ajax/upload.php';
            var xhr = new XMLHttpRequest();
            var fd = new FormData();
            xhr.open("POST", url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Every thing ok, file uploaded
                    //console.log(xhr.responseText); // handle response.
                    //alert(field)
                    //alert(type)
                    //alert(xhr.responseText)
                    $("#"+field).val(xhr.responseText);

                }
            };
            fd.append("upload_file", file);
            xhr.send(fd);
        }

        function previewImage(file,field) {
            
            var galleryId = "gallery_"+field;
            //alert(file)
            //alert(galleryId)
            //$("#galleryId").html("");

            var gallery = document.getElementById(galleryId);
            gallery.innerHTML = ""
            //alert(gallery.innerHTML)
            var imageType = /image.*/;
        
            if (!file.type.match(imageType)) {
                throw "File Type must be an image";
            }
        
            var thumb = document.createElement("div");
            thumb.classList.add('thumbnail'); // Add the class thumbnail to the created div
        
            var img = document.createElement("img");
            img.file = file;
            img.style.width = "150px";
            //img.style.height = "100%";

            thumb.appendChild(img);
            gallery.appendChild(thumb);
            //$("#galleryId").html(thumb);
        
            // Using FileReader to display the image content
            var reader = new FileReader();
            reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
            reader.readAsDataURL(file);
        }        


        function CargaImagen(Id,div){
            //alert(Id)
            //alert("ajax/tmp/"+$('#file_edit_'+Id+"_1").val())
            $("#"+div).attr("src","ajax/tmp/"+$('#file_edit_'+Id+"_1").val());
            $("#"+div).attr("width","120px");
        }


        const myChoicesInstances = {};

        // Seleccionamos todos los elementos con la clase .selectpicker
        const pickers = document.querySelectorAll('.selectpicker');

        pickers.forEach((picker) => {
            // 1. Inicializamos Choices para cada picker individualmente
            const instance = new Choices(picker, {
                    placeholder: false,
                    itemSelectText: '',
                    searchEnabled: true,
                    // Forzamos el renderizado si hay problemas de timing
                    silent: false
            });


            const key = picker.id || picker.name;
            if (key) {
                myChoicesInstances[key] = instance;
            }    

            // 2. Aplicamos las clases personalizadas al contenedor generado
            // Obtenemos las clases originales del select
            let customClasses = picker.getAttribute('class');
            
            // Choices envuelve el select en un contenedor padre. 
            // Accedemos a él y le transferimos las clases.
            if (picker.parentElement) {
                picker.parentElement.classList.add(...customClasses.split(' '));
            }
        });

        function orden(orden,Idp,Id){
        $.ajax({
            url: API_BASE_URL + 'orden',
            type: 'PUT',
            contentType: 'application/json', // Mantenemos el Content-Type como JSON
            headers: {
                'Authorization': 'Bearer ' + TOKEN 
            },
            // Enviamos el objeto convertido a JSON String
            data:JSON.stringify({
                orden: orden,
                Idp: Idp,
                Id:Id
            }),
            success: function(response) {
                listado('products_images');
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                setTimeout(() => {
                    showToast('❌ <?php echo Trd(15)?> ' + errorMessage);
                }, 500);
            }
        });            

        }
<?php





//ESTE BLOQUE ES PARA PINTAR EL PROCESO DE PRECIOS !!



if ($IdTabla == 'item_prices' OR $IdTabla == 'products'){
?>

// Diccionario de traducciones para ser usado en JS
//readonly style="display: none"
const txt = {
    lblCostoAcumulado: "<?php Trd_2(4)?>",
    lblEjeX: "<?php Trd_2(5)?>",
    lblEjeY: "<?php Trd_2(6)?>",
    f1Precio: "<?php Trd_2(7)?>",
    selDefault: "<?php Trd_2(8)?>",
    optIndefinido: "<?php Trd_2(9)?>",
    optHasta: "<?php Trd_2(10)?>",
    optCada: "<?php Trd_2(11)?>",
    f2Cargo: "<?php Trd_2(12)?>",
    f2PorCada: "<?php Trd_2(13)?>",
    f3Despues: "<?php Trd_2(14)?>",
    f3Precio: "<?php Trd_2(15)?>",
    placeholderCant: "<?php Trd_2(16)?>"
};

let configTotal = [];
let myChart; 

$(document).ready(function() {
    ejecutarFuncion1();
});

function actualizarJSON() {
    const idsExistentes = $('.linea-config').map(function() { return this.id; }).get();
    configTotal = configTotal.filter(item => idsExistentes.includes(item.id_linea));
    $('#JsonPrice').val(JSON.stringify(configTotal, null, 2));
    $('#edit_JsonPrice').val(JSON.stringify(configTotal, null, 2));
    proyectarTarifa(); 
}

function limpiarHijos(elementoPadre) {
    $(elementoPadre).nextAll().remove();
    actualizarJSON();
}

function proyectarTarifa() {
    if (configTotal.length === 0) return;

    const conv = { 
        "hora": 1, "horas": 1, 
        "dia": 24, "dias": 24, 
        "semana": 168, "semanas": 168 
    };
    
    let data = [];
    let costo = 0;
    const MAX_H = 72; 

    const f1 = configTotal.find(c => c.funcion === "f1");
    if (!f1) return;

    let p1 = parseFloat(f1.precio) || 0;
    costo = p1; 

    if (f1.tipo === "Indefinido") {
        for (let h = 0; h <= MAX_H; h++) {
            data.push({ h, costo: p1 });
        }
    } 
    else if (f1.tipo === "Cada") {
        let horasF1 = (parseFloat(f1.tiempo) || 1) * conv[f1.unidad];
        const f2 = configTotal.find(c => c.funcion === "f2");

        for (let h = 0; h <= MAX_H; h++) {
            if (f2 && f2.tipo === "Hasta") {
                let limiteF2 = (parseFloat(f2.tiempo) || 1) * conv[f2.unidad];
                if (h > 0 && h < limiteF2 && h % horasF1 === 0) {
                    costo += p1;
                }
                if (h >= limiteF2) {
                    const f3 = configTotal.find(c => c.funcion === "f3");
                    if (f3 && f3.tipo === "Cada") {
                        let horasF3 = (parseFloat(f3.tiempo) || 1) * conv[f3.unidad];
                        if ((h - limiteF2) % horasF3 === 0) {
                            costo += parseFloat(f3.precio);
                        }
                    }
                }
            } else {
                if (h > 0 && h % horasF1 === 0) costo += p1;
            }
            data.push({ h, costo });
        }
    } 
    else if (f1.tipo === "Hasta") {
        let horasF1 = (parseFloat(f1.tiempo) || 1) * conv[f1.unidad];
        const f3 = configTotal.find(c => c.funcion === "f3");

        for (let h = 0; h <= MAX_H; h++) {
            if (h > horasF1 && f3) {
                let horasF3 = (parseFloat(f3.tiempo) || 1) * conv[f3.unidad];
                if (f3.tipo === "Hasta") {
                    if (h <= horasF1 + horasF3) costo = parseFloat(f3.precio);
                } else if (f3.tipo === "Cada") {
                    if ((h - horasF1) % horasF3 === 0) costo += parseFloat(f3.precio);
                }
            }
            data.push({ h, costo });
        }
    }
    renderGrafica(data);
}

function renderGrafica(data) {
    const ctx = document.getElementById('costosChart').getContext('2d');

    if (myChart) {
        myChart.destroy();
    }

    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => `${d.h} hrs`),
            datasets: [{
                label: txt.lblCostoAcumulado,
                data: data.map(d => d.costo),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#3498db',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: { display: true, text: txt.lblEjeX }
                },
                y: {
                    title: { display: true, text: txt.lblEjeY },
                    beginAtZero: true
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${txt.lblCostoAcumulado}: $${context.raw}`;
                        }
                    }
                }
            }
        }
    });
}

function ejecutarFuncion1() {
    const id = "f1_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F1:</span>
        ${txt.f1Precio} <input type="number" class="val-precio" value="0">
        <select class="sel-tipo">
            <option value="">${txt.selDefault}</option>
            <option value="Indefinido">${txt.optIndefinido}</option>
            <option value="Hasta">${txt.optHasta}</option>
            <option value="Cada">${txt.optCada}</option>
        </select>
        <span class="dinamico"></span>
    </div>`;
    $('#contenedor-funciones').append(html);
    $('#edit_contenedor-funciones').append(html);

    $(`#${id} .sel-tipo`).on('change', function() {
        limpiarHijos(`#${id}`);
        const precio = $(`#${id} .val-precio`).val();
        const tipo = $(this).val();
        let registro = { id_linea: id, funcion: "f1", precio: precio, tipo: tipo };
        configTotal.push(registro);
        
        const contenedor = $(`#${id} .dinamico`).empty();
        
        if (tipo === "Cada") {
            contenedor.append(`
                <input type="number" class="val-t" placeholder="${txt.placeholderCant}">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dia"><?php Trd_2(19)?></option><option value="hora"><?php Trd_2(17)?></option><option value="semana"><?php Trd_2(21)?></option>
                    <option value="dias"><?php Trd_2(20)?></option><option value="horas"><?php Trd_2(18)?></option><option value="semanas"><?php Trd_2(22)?></option>
                </select>`);
            
            $(`#${id} .sel-tt`).on('change', function() {
                const tt = $(this).val();
                const vt = $(`#${id} .val-t`);
                ["dia", "hora", "semana"].includes(tt) ? vt.hide().val(1) : vt.show();
                registro.tiempo = vt.val(); registro.unidad = tt;
                actualizarJSON();
                ejecutarFuncion2(precio, registro.tiempo, tt);
            });
        } else if (tipo === "Hasta") {
            contenedor.append(`
                <input type="number" class="val-t">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dias"><?php Trd_2(20)?></option><option value="horas"><?php Trd_2(18)?></option><option value="semanas"><?php Trd_2(22)?></option>
                </select>`);
            
            $(`#${id} .sel-tt`).on('change', function() {
                registro.tiempo = $(`#${id} .val-t`).val(); registro.unidad = $(this).val();
                actualizarJSON();
                ejecutarFuncion3(registro.tiempo, registro.unidad);
            });
        }
        actualizarJSON();
    });
}

function ejecutarFuncion2(pPrev, tPrev, uPrev) {
    const id = "f2_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F2:</span>
        ${txt.f2Cargo} ${pPrev} ${txt.f2PorCada} ${tPrev} ${uPrev} | 
        <select class="sel-tipo">
            <option value="">${txt.selDefault}</option>
            <option value="Indefinido">${txt.optIndefinido}</option>
            <option value="Hasta">${txt.optHasta}</option>
        </select>
        <span class="dinamico"></span>
    </div>`;
    $('#contenedor-funciones').append(html);
    $('#edit_contenedor-funciones').append(html);

    $(`#${id} .sel-tipo`).on('change', function() {
        limpiarHijos(`#${id}`);
        const tipo = $(this).val();
        let registro = { id_linea: id, funcion: "f2", precio: pPrev, tipo: tipo };
        configTotal.push(registro);
        
        if (tipo === "Hasta") {
            $(`#${id} .dinamico`).html(`<input type="number" class="val-t"> <span>${uPrev}</span>`);
            $(`#${id} .val-t`).on('change', function() {
                registro.tiempo = $(this).val(); registro.unidad = uPrev;
                actualizarJSON();
                ejecutarFuncion3(registro.tiempo, uPrev);
            });
        }
        actualizarJSON();
    });
}

function ejecutarFuncion3(tPrev, uPrev) {
    const id = "f3_" + Date.now();
    const html = `
    <div class="linea-config" id="${id}">
        <span class="etiqueta-f">F3:</span>
        ${txt.f3Despues} ${tPrev} ${uPrev} ${txt.f3Precio} <input type="number" class="val-precio" value="0">
        <select class="sel-tipo">
            <option value="">${txt.selDefault}</option>
            <option value="Indefinido">${txt.optIndefinido}</option>
            <option value="Cada">${txt.optCada}</option>
            <option value="Hasta">${txt.optHasta}</option>
        </select>
        <span class="dinamico"></span>
    </div>`;
    $('#contenedor-funciones').append(html);
    $('#edit_contenedor-funciones').append(html);

    $(`#${id} .sel-tipo`).on('change', function() {
        limpiarHijos(`#${id}`);
        const p = $(`#${id} .val-precio`).val();
        const tipo = $(this).val();
        let registro = { id_linea: id, funcion: "f3", precio: p, tipo: tipo };
        configTotal.push(registro);

        const contenedor = $(`#${id} .dinamico`).empty();
        if (tipo === "Cada") {
            contenedor.append(`
                <input type="number" class="val-t">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dia"><?php Trd_2(19)?></option><option value="hora"><?php Trd_2(17)?></option><option value="semana"><?php Trd_2(21)?></option>
                    <option value="dias"><?php Trd_2(20)?></option><option value="horas"><?php Trd_2(18)?></option><option value="semanas"><?php Trd_2(22)?></option>
                </select>`);
            
            $(`#${id} .sel-tt`).on('change', function() {
                const tt = $(this).val();
                const vt = $(`#${id} .val-t`);
                ["dia", "hora", "semana"].includes(tt) ? vt.hide().val(1) : vt.show();
                registro.tiempo = vt.val(); registro.unidad = tt;
                actualizarJSON();
                ejecutarFuncion2(p, registro.tiempo, tt);
            });
        } else if (tipo === "Hasta") {
            contenedor.append(`
                <input type="number" class="val-t">
                <select class="sel-tt">
                    <option value="">--</option>
                    <option value="dias"><?php Trd_2(20)?></option><option value="horas"><?php Trd_2(18)?></option><option value="semanas"><?php Trd_2(22)?></option>
                </select>`);
            
            $(`#${id} .sel-tt`).on('change', function() {
                registro.tiempo = $(`#${id} .val-t`).val(); registro.unidad = $(this).val();
                actualizarJSON();
                ejecutarFuncion3(registro.tiempo, registro.unidad);
            });
        }
        actualizarJSON();
    });
}

function cargar() {
    try {

        
        const rawValue = $('#edit_JsonPrice').val();
        const decodedValue = $('<div/>').html(rawValue).text();        
        const data = JSON.parse(decodedValue);
        if (!Array.isArray(data) || data.length === 0) return;

        // 1. Limpiar el estado actual
        // $('#contenedor-funciones').empty();
        $('#edit_contenedor-funciones').empty();
        configTotal = [];

        // 2. Identificar las funciones presentes
        const f1 = data.find(item => item.funcion === "f1");
        const f2 = data.find(item => item.funcion === "f2");
        const f3 = data.find(item => item.funcion === "f3");

        // 3. Reconstruir F1
        if (f1) {
            ejecutarFuncion1(); 
            const $f1Element = $('.linea-config').last();
            $f1Element.find('.val-precio').val(f1.precio);
            $f1Element.find('.sel-tipo').val(f1.tipo).trigger('change');
            
            if (f1.tiempo) {
                $f1Element.find('.val-t').val(f1.tiempo);
                $f1Element.find('.sel-tt').val(f1.unidad).trigger('change');
            }
        }

        // 4. Reconstruir F2 (si existe)
        if (f2) {
            const $f2Element = $(`[id^="f2_"]`); // Busca el elemento F2 recién creado por el trigger de F1
            $f2Element.find('.sel-tipo').val(f2.tipo).trigger('change');
            
            if (f2.tiempo) {
                $f2Element.find('.val-t').val(f2.tiempo).trigger('change');
            }
        }

        // 5. Reconstruir F3 (si existe)
        if (f3) {
            const $f3Element = $(`[id^="f3_"]`);
            $f3Element.find('.val-precio').val(f3.precio);
            $f3Element.find('.sel-tipo').val(f3.tipo).trigger('change');

            if (f3.tiempo) {
                $f3Element.find('.val-t').val(f3.tiempo);
                $f3Element.find('.sel-tt').val(f3.unidad).trigger('change');
            }
        }

        // Forzar actualización de la gráfica al terminar
        proyectarTarifa();

    } catch (e) {
        //alert("Error al leer el JSON: " + e.message);
    }
}

    $('#edit_products_item_price').on('submit', function(e) {

        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('was-validated');
            return false;
        }        

        e.preventDefault(); 
        updateRecord($(this),'products_item_price'); // Pasamos el formulario a la función
    });  

    function get_price(Idp){
        $.ajax({
            url: API_BASE_URL +'get_price/' + Idp,
            type: 'GET',
            dataType: 'json', // Indica que esperamos JSON
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            },
            success: function(response) {
                //console.log('Datos del registro:', response);
                Object.entries(response).forEach(([clave, valor]) => {

                const input = document.getElementById("edit_"+clave);

                    switch (clave) {
                    case 'ItemPrice':
                            const instance = myChoicesInstances["edit_" + clave];
                            instance.setChoiceByValue(String(valor));                        
                        break;
                    case 'JsonPrice':
                            input.value = valor || '';
                            cargar();
                    //const contenedor = document.getElementById('edit_contenedor-funciones');
                    //const inputs = contenedor.querySelectorAll('input');
                    //inputs.forEach(input => {
                    //    input.disabled = true;
                    //});
                    $('#edit_contenedor-funciones').find('input, select, textarea').prop('disabled', true);                          
                        break;
                    case 'Taxable':
                            input.checked = (valor == 1);
                        break;    
                    default:

                    }
                });
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    console.error('Acceso denegado. Token expirado o inválido.');
                    // Aquí puedes redirigir al login o limpiar el token
                } else {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                    setTimeout(() => {
                        showToast('❌' + errorMessage);
                    }, 500);                
                    //console.error('Error al obtener registro:', error);
                }
            }
        });
    }
    function get_json_price(Idp){
        $.ajax({
            url: API_BASE_URL +'get_json_price/' + Idp,
            type: 'GET',
            dataType: 'json', // Indica que esperamos JSON
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            },
            success: function(response) {
                //console.log('Datos del registro:', response);
                Object.entries(response).forEach(([clave, valor]) => {

                const input = document.getElementById("edit_"+clave);

                    switch (clave) {
                    case 'JsonPrice':
                            input.value = valor || '';
                            cargar();
                    //const contenedor = document.getElementById('edit_contenedor-funciones');
                    //const inputs = contenedor.querySelectorAll('input');
                    //inputs.forEach(input => {
                    //    input.disabled = true;
                    //});
                    $('#edit_contenedor-funciones').find('input, select, textarea').prop('disabled', true);                           
                        break;
                    case 'Taxable':
                            input.checked = (valor == 1);
                        break;    
                    default:

                    }
                });
            },
            error: function(xhr, status, error) {
                
                if (xhr.status === 401) {
                    console.error('Acceso denegado. Token expirado o inválido.');
                    // Aquí puedes redirigir al login o limpiar el token
                } else {

                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error al comunicarse con la API.';
                    setTimeout(() => {
                        showToast('❌' + errorMessage);
                    }, 500);                
                    //console.error('Error al obtener registro:', error);
                }
            }
        });
    }

<?php
}
?>


    $('#edit_Copiar').on('click', function() {
        //var idBoton = $(this).attr('id'); // Obtiene el ID del botón
        getTemplate( $('#edit_IdTemplate').val(),'edit_Template' );
    });

    $('#Copiar').on('click', function() {
        //var idBoton = $(this).attr('id'); // Obtiene el ID del botón
        getTemplate( $('#IdTemplate').val(),'Template' );
    });    

    function getTemplate(Id,IdCampo) {

        $.ajax({
            url: API_BASE_URL + 'template/' + Id,
            type: 'GET',
            dataType: 'json', // Indica que esperamos JSON
            headers: {
                // *** Aquí se adjunta el token en el encabezado Authorization ***
                'Authorization': 'Bearer ' + TOKEN 
            },
            success: function(response) {
                Object.entries(response).forEach(([clave, valor]) => {
                    $('#'+IdCampo).summernote('code',valor);
                });
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    console.error('Acceso denegado. Token expirado o inválido.');
                    // Aquí puedes redirigir al login o limpiar el token
                } else {
                    console.error('Error al obtener registro:', error);
                }
            }
        });
    }

</script>

</body>
</html>

