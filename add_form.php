<?php
function add_form($IdTabla,$Idioma,$Tp){
    global $db;
    ?>  
    <?php
        if ($Tp=='I' OR $Tp=='M'){
            echo '<div class="container mt-4 shadow rounded" id = "add_form_'.$IdTabla.'" style="display: none">';
        }
        elseif($Tp == 'D'){
            echo '<div class="container" id = "add_form_'.$IdTabla.'" style="display: none">';
        }
    ?>
    
        <br>
        <h4 class="mb-4"><?php echo Trd(4)?></h4>
        <form name="add_<?php echo $IdTabla?>" id="add_<?php echo $IdTabla?>" class="needs-validation" novalidate>
        <?php 
        $query = "SELECT COUNT(DISTINCT(modal_add.Etiqueta)) as NoEtiquetas FROM modal_add WHERE Tabla = ? ";
        $stmt = $db->prepare($query);
        $stmt->bindValue(1, $IdTabla);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($resultados) {
            foreach ($resultados as $registro) {
                $NoEtiquetas = $registro['NoEtiquetas'];
            }
        }

        if ($NoEtiquetas== 1 ){
            armar_formulario_add($IdTabla,"General",$Idioma);
        }
        else{
            //echo '<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">';
            $query = "            
            SELECT
                DISTINCT( modal_add.Etiqueta ) as Etiqueta,
                etiquetas_tablas.Titulo
            FROM
                modal_add
                INNER JOIN
                etiquetas_tablas
                ON 
                    modal_add.Tabla = etiquetas_tablas.Tabla AND
                    modal_add.Etiqueta = etiquetas_tablas.Etiqueta
                    WHERE 
                    modal_add.Tabla = ? AND 
                    etiquetas_tablas.Idioma = ?
                ORDER BY modal_add.Id
            ";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $IdTabla);
            $stmt->bindValue(2, $Idioma);
            $stmt->execute();
            $Tab = 0;
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                foreach ($resultados as $registro) {
                    $Tab++;
                    $Etiqueta = $registro['Titulo'];
                    if ($Tab==1){
                        //echo '
                        //    <li class="nav-item" role="presentation">
                        //        <button class="nav-link active" id="pills-'.$Tab.'-tab" data-bs-toggle="pill" data-bs-target="#pills-'.$Tab.'" type="button" role="tab" aria-controls="pills-'.$Tab.'" aria-selected="true">'.$Etiqueta.'</button>
                        //    </li>                        
                        //';
                        
                    }
                    else{
                        //echo '
                        //    <li class="nav-item" role="presentation">
                        //        <button class="nav-link" id="pills-'.$Tab.'-tab" data-bs-toggle="pill" data-bs-target="#pills-'.$Tab.'" type="button" role="tab" aria-controls="pills-'.$Tab.'" aria-selected="false">'.$Etiqueta.'</button>
                        //    </li>                        
                        //';
                        
                    }
                }
            }            
            //echo '</ul>';
            //echo '<div class="tab-content" id="pills-tabContent">';
            $Tab = 0;
            foreach ($resultados as $registro) {
                    $Tab++;
                    $Etiqueta = $registro['Etiqueta'];
                    
                    if ($Tab==1){
                        //echo '<div class="tab-pane fade show active" id="pills-'.$Tab.'" role="tabpanel" aria-labelledby="pills-'.$Tab.'-tab">';
                        echo '<h4 class="mb-4">'.$registro['Titulo'].'</h4>';
                            armar_formulario_add($IdTabla,$Etiqueta,$Idioma);
                        //echo '</div>';
                    }
                    else{
                        //echo '<div class="tab-pane fade" id="pills-'.$Tab.'" role="tabpanel" aria-labelledby="pills-'.$Tab.'-tab">';
                        echo '<h4 class="mb-4">'.$registro['Titulo'].'</h4>';
                            armar_formulario_add($IdTabla,$Etiqueta,$Idioma);
                        //echo '</div>';
                    }             
            }
            //echo '</div>';
        } 
        if ($IdTabla == 'item_prices'){
            ?>
            <div class="container">
                <h2><?php Trd_2(2)?></h2>
                <div id="contenedor-funciones"></div>
            </div>
            <?php 
        }
        ?>
        <br>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button class="btn btn-secondary" type="button" onclick=' Cancel_add("<?php echo $IdTabla;?>");'><?php echo Trd(6)?></button>
            <button class="btn btn-primary" type="submit"><?php echo Trd(7)?></button>
        </div>
        </form>
        <br>
    <?php
        if ($Tp=='F' OR $Tp=='M'){
            echo "</div>";
        }
        elseif($Tp == 'D'){
            echo "</div>";
        }
    ?>        
    
    <script>

        // Escuchar el evento de envío del formulario
        $('#add_<?php echo $IdTabla?>').on('submit', function(e) {

            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('was-validated');
                return false;
            }        

            e.preventDefault(); 
            createNewRecord($(this),'<?php echo $IdTabla?>'); // Pasamos el formulario a la función
        });

        $(document).ready(function() {
        <?php
            $query = "SELECT Campo  FROM modal_add WHERE Tabla = ? AND TipoCampo ='html'";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $IdTabla);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                foreach ($resultados as $registro) {
                    $Campo = $registro['Campo'];
                    echo "$('#$Campo').summernote({height: 480});";
                }
            }
        ?>
        });


    </script>
<?php }?>