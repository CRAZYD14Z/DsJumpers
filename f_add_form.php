<?php 
function armar_formulario_add($tabla,$etiqueta,$idioma){
    global $db;
            $query = "            
                SELECT
                    modal_add.*, 
                    titulos_campos_tablas.Titulo, 
                    placeholder_campos_tablas.Titulo as PlaceHolder, 
                    placeholder_campos_tablas.Validacion
                FROM
                    modal_add
                    INNER JOIN
                    titulos_campos_tablas
                    ON 
                        modal_add.Tabla = titulos_campos_tablas.Tabla AND
                        modal_add.Campo = titulos_campos_tablas.Campo
                    LEFT JOIN
                    placeholder_campos_tablas
                    ON 
                        titulos_campos_tablas.Tabla = placeholder_campos_tablas.Tabla AND
                        titulos_campos_tablas.Campo = placeholder_campos_tablas.Campo AND
                        titulos_campos_tablas.Idioma = placeholder_campos_tablas.Idioma
                WHERE
                    modal_add.Tabla = ? AND
                    modal_add.Etiqueta = ? AND
                    modal_add.TipoCampo <> 'eval' AND
                    modal_add.TipoCampo <> 'auto' AND
                    titulos_campos_tablas.Idioma = ?
                ORDER BY
                    modal_add.Id ASC, 
                    modal_add.Fila ASC
            ";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $tabla);
            $stmt->bindValue(2, $etiqueta);
            $stmt->bindValue(3, $idioma);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                $FilaN=0;
                echo '<div class="row">';
                foreach ($resultados as $row) {

                    $Fila           = $row['Fila'];
                    $ColXS          = $row['ColXS'];
                    $ColSM          = $row['ColSM'];
                    $ColMD          = $row['ColMD'];
                    $ColLG          = $row['ColLG'];
                    $ColXL          = $row['ColXL'];
                    $ColXXL         = $row['ColXXL'];
                    $Titulo         = $row['Titulo'];
                    $Campo          = $row['Campo'];
                    $TipoCampo      = $row['TipoCampo'];
                    $Requerido      = $row['Requerido'];
                    $LargoMin       = $row['LargoMin'];
                    $LargoMax       = $row['LargoMax'];
                    $PlaceHolder    = $row['PlaceHolder'];
                    $Validacion     = $row['Validacion'];
                    $Patron         = $row['Patron'];
                    $SoloLectura    = $row['SoloLectura'];
                    $Alineacion     = $row['Alineacion'];
                    $AnchoPX        = $row['AnchoPX'];
                    $TablaDts       = $row['TablaDts'];
                    $CampoValor     = $row['CampoValor'];
                    $CampoDescripcion = $row['CampoDescripcion'];
                    $Filtro         = $row['Filtro'];
                    $Regla          = $row['Regla'];
                    $TablaDts2      = $row['TablaDts2'];
                    $CampoFiltro2   = $row['CampoFiltro2'];
                    $CampoValor2    = $row['CampoValor2'];
                    $CampoDescripcion2  = $row['CampoDescripcion2'];
                    $Receptor       = $row['Receptor'];                    
                    $Filtro2        = $row['Filtro2'];

                    $form_control = " form-control  form-control-sm";
                    $form_select  = " form-select form-select-sm";

                    if ($FilaN != $Fila AND $FilaN != 0){
                        echo '</div>';
                        echo '<div class="row">';
                    }   
                    $FilaN = $Fila;

                    $aps="'";
                    if ($Patron!="")
                        $Patron = ' pattern="'.$Patron.'" ';            
                                                
                    if ($Requerido=='X')
                        $Requerido = 'required=""';
                    else
                        $Requerido = '';
                    if ($SoloLectura=='X')
                        $SoloLectura = 'readonly="readonly"';
                    else
                        $SoloLectura = '';                    

                    echo "<div class='col-$ColXS col-sm-$ColSM col-md-$ColMD col-lg-$ColLG col-xl-$ColXL col-xxl-$ColXXL'>";

                    switch ($TipoCampo) {
                        case 'option':
                                $option= substr($Campo, 0, -2);
                                echo '<input class="form-check-input" type="radio" name="'.$option.'" id="'.$Campo.'" value = "'.$Alineacion.'">';
                                echo '<label class="form-check-label" for="'.$Campo.'">';
                                echo $Titulo;
                                echo '</label>';
                            break;                        
                        case 'label':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            break;                        
                        case 'titulo':
                            echo '<h4 class="mb-4">'.$Titulo.'</h4>';
                            break;                          
                        case 'hidden':
                            echo '<input name="'.$Campo.'" id="'.$Campo.'"  type="'.$TipoCampo.'" >';
                            break;                          
                        case 'text':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'random':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" value="'.generarCodigoAlfanumerico().'" class="'.$form_control.'" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;                            
                        case 'number';
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' numbers-only" type="number" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.' >';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'decimal';
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' decimals" type="number" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.' >';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'currency';
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' decimals currency" type="text" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.' >';                            
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;   
                        case 'area';
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" style="" rows="'.$LargoMin.'" cols="'.$LargoMax.'" '.$SoloLectura.'></textarea>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'html';
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" style="" rows="'.$LargoMin.'" cols="'.$LargoMax.'" '.$SoloLectura.'></textarea>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;                            
                        case 'range':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" type="'.$TipoCampo.'" class="form-range" style="" min="'.$LargoMin.'" max="'.$LargoMax.'" />';
                            break;
                        case 'date':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" type="'.$TipoCampo.'" style="" '.$Requerido.' '.$SoloLectura.'>';
                            break;
                        case 'time':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" type="'.$TipoCampo.'" style="" '.$Requerido.' '.$SoloLectura.'>';
                            break;
                        case 'tel':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            break;
                        case 'url':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            break;
                        case 'password':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.' '.$SoloLectura.'>';
                            break;
                        case 'color':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" type="'.$TipoCampo.'" >';
                            break;
                        case 'email':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" type="'.$TipoCampo.'" style="text-align: '.$Alineacion.';" '.$Requerido.'  minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            break;
                        case 'select':
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            if ($TablaDts2!= "")
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.'" style=""  onChange="change_send2(this.value,'.$aps.$Campo.$aps.','.$aps.$Receptor.$aps.','.$aps.$tabla.$aps.')" '.$Requerido.' '.$SoloLectura.'>';
                            else
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.'" style="" '.$Requerido.' '.$SoloLectura.'>';

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
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'checkbox':
                            echo '<br><div class="form-check  form-switch">';
                                echo '<input name="'.$Campo.'" id="'.$Campo.'" class="form-check-input" type="'.$TipoCampo.'" '.$Requerido.' '.$SoloLectura.'>';
                                    echo '<label class="form-check-label" for="'.$Campo.'">';
                                        echo $Titulo;
                                    echo '</label>';
                            echo '</div>';
                            break;
                        case 'img':
                            echo '<label class="form-label" for="'.$Campo.'">'.$Titulo.'</label>';
                            echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'" class="'.$form_control.'" type="file"  accept="'.$Filtro.'">';
                            echo '<input name="'.$Campo.'" id="'.$Campo.'"  type="hidden" >';
                            //echo '<input type="file" id="uploadfiles"  accept="image/*" />';
                            if ($Regla != "")
                                echo '<small class="form-text text-muted">'.$Regla.'</small>';

                            echo "</div>";
                            echo '<div class="form-row">';
                                echo "<div class='form-group col-xl-12 col-lg-12 col-md-12 col-sm-12'>";
                                    echo "<div id='gallery_".$Campo."'>";
                                    echo "</div>";
                                echo "</div>";                    

                            echo "
                            <script>
                            var ".$Campo." = document.querySelector('#file_".$Campo."');
                            ".$Campo.".addEventListener('change', function () {
                                var files = this.files;
                                for(var i=0; i<files.length; i++){
                                    uploadFile(this.files[i],'".$Campo."','img'); 
                                    previewImage(this.files[i],'".$Campo."');
                                }
                            }, false);                    
                            </script>
                            ";
                            break;

                        case 'file':
                            echo '<label class="form-label" for="'.$Campo.'">'.$Titulo.'</label>';
                            echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'" class="'.$form_control.'" type="file"  accept="'.$Filtro.'">';
                            echo '<input name="'.$Campo.'" id="'.$Campo.'"  type="hidden" >';
                            //echo '<input type="file" id="uploadfiles"  accept="image/*" />';
                            if ($Regla != "")
                                echo '<small class="form-text text-muted">'.$Regla.'</small>';

                            echo "</div>";
                            echo '<div class="form-row">';
                                echo "<div class='form-group col-xl-12 col-lg-12 col-md-12 col-sm-12'>";
                                    echo "<div id='gallery_".$Campo."'>";
                                    echo "</div>";
                                echo "</div>";                    

                            echo "
                            <script>
                            var ".$Campo." = document.querySelector('#file_".$Campo."');
                            ".$Campo.".addEventListener('change', function () {
                                var files = this.files;
                                for(var i=0; i<files.length; i++){
                                    uploadFile(this.files[i],'".$Campo."','file'); 
                                }
                            }, false);                    
                            </script>
                            ";
                            break;                            

                            case 'complete':


                                echo "<label for='$Campo' class='form-label'>$Titulo</label>";

                                    echo '<select name="'.$Campo.'" id="'.$Campo.'" class="selectpicker form-control border-1  rounded " style="" '.$Requerido.' '.$SoloLectura.'>';

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
                                echo '<div class="invalid-feedback">'.$Validacion.'</div>';

                            break;                            


                    }
                    echo '</div>';
                }
                echo '</div>';
            }
}

function generarCodigoAlfanumerico() {
    // 1. Obtenemos el timestamp en microsegundos y lo convertimos a base 36
    // Esto nos da una base temporal única y alfanumérica
    $timestamp = base_convert(str_replace('.', '', microtime(true)), 10, 36);
    
    // 2. Definimos los caracteres permitidos para la parte aleatoria
    $caracteres = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randomPart = "";
    
    // 3. Generamos caracteres aleatorios suficientes para completar
    for ($i = 0; $i < 16; $i++) {
        $randomPart .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    // 4. Combinamos, limitamos a 16 caracteres y convertimos a mayúsculas
    $combinado = strtoupper(substr($timestamp . $randomPart, 0, 16));
    
    // 5. Aplicamos el formato ####-####-####-####
    return implode("-", str_split($combinado, 4));
}

?>