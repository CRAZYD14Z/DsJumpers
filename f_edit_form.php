<?php 
function armar_formulario_edit($tabla,$etiqueta,$idioma){
    global $db;
            $query = "            
                SELECT
                    modal_edit.*, 
                    titulos_campos_tablas.Titulo, 
                    placeholder_campos_tablas.Titulo as PlaceHolder, 
                    placeholder_campos_tablas.Validacion
                FROM
                    modal_edit
                    INNER JOIN
                    titulos_campos_tablas
                    ON 
                        modal_edit.Tabla = titulos_campos_tablas.Tabla AND
                        modal_edit.Campo = titulos_campos_tablas.Campo
                    LEFT JOIN
                    placeholder_campos_tablas
                    ON 
                        titulos_campos_tablas.Tabla = placeholder_campos_tablas.Tabla AND
                        titulos_campos_tablas.Campo = placeholder_campos_tablas.Campo AND
                        titulos_campos_tablas.Idioma = placeholder_campos_tablas.Idioma
                WHERE
                    modal_edit.Tabla = ? AND
                    modal_edit.Etiqueta = ? AND
                    modal_edit.TipoCampo <> 'eval' AND
                    modal_edit.TipoCampo <> 'auto' AND
                    titulos_campos_tablas.Idioma = ?
                ORDER BY
                    modal_edit.Id ASC, 
                    modal_edit.Fila ASC
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
                    $Campo          = "edit_".$row['Campo'];
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
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" data-tipo="area" rows="'.$LargoMin.'" cols="'.$LargoMax.'" '.$SoloLectura.'></textarea>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                        case 'html';
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.'" data-tipo="html" rows="'.$LargoMin.'" cols="'.$LargoMax.'" '.$SoloLectura.' ></textarea>';
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
                            if ($SoloLectura!="")
                                $SoloLectura = "disabled";
                            echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            if ($TablaDts2!= "")
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.'" style=""  onChange="change_send2(this.value,'.$aps. str_replace("edit_","",$Campo).$aps.','.$aps.$Receptor.$aps.','.$aps.$tabla.$aps.')" '.$Requerido.' '.$SoloLectura.'>';
                            else
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.'" style="" '.$Requerido.' '.$SoloLectura.'>';

                            $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                            $columnExists = $checkColumn->fetch();                            

                            if ($columnExists) {
                                $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$idioma'  $Filtro ";
                            }
                            else{
                                $query ="SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1  $Filtro ";
                            }                            

                            
                            $stmt_dts = $db->prepare($query);
                            $stmt_dts->execute();
                            $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                            if ($tabla_dts) {
                                echo '<option selected> ... </option>';
                                foreach ($tabla_dts as $tabla_dt) {
                                    $Valor         = $tabla_dt[$CampoValor];
                                    $Descripcion   = $tabla_dt[$CampoDescripcion];
                                    echo '<option value="'.$Valor.'">'. ($Descripcion).'</option>';
                                }
                            }
                            echo '</select>';
                            break;

                        case 'checkbox':
                            echo '<br><div class="form-check  form-switch">';
                                echo '<input name="'.$Campo.'" id="'.$Campo.'" class="form-check-input" type="'.$TipoCampo.'" '.$Requerido.' '.$SoloLectura.'>';
                                    echo '<label class="form-check-label" for="'.$Campo.'">';
                                        echo $Titulo;
                                    echo '</label>';
                            echo '</div>';
                            break;
                        case 'button':
                                echo "<br><button type='button' class='btn btn-primary' name='$Campo' id='$Campo'>$Titulo</button>";
                            break;                            
                case 'img':
                    echo '<label class="form-label" for="'.$Campo.'">'.$Titulo.'</label>';
                    echo '<input name="'.$Campo.'" id="'.$Campo.'" class="border  form-control " type="file"  accept="'.$Filtro.'">';
                    echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'"  type="hidden" >';
                    echo '<input name="file_'.$Campo.'_1" id="file_'.$Campo.'_1"  type="hidden" >';
                    //echo '<input type="file" id="uploadfiles"  accept="image/*" />';
                    if ($Regla != "")
                        echo '<small class="form-text text-muted">'.$Regla.'</small>';


                    echo "</div>";
                    echo '<div class="form-row">';
                        echo "<div class='form-group col-xl-12 col-lg-12 col-md-12 col-sm-12'>";
                            echo "<img src='' id='gallery_file2_$Campo' name ='gallery_file2_$Campo'>";
                            echo "<div id='gallery_file_".$Campo."'>";
                                //echo "<img src='' id='gallery_file_$Campo' name ='gallery_file_$Campo'>";
                            echo "</div>";
                        echo "</div>";                    

                    echo "
                    <script>
                    var ".$Campo." = document.querySelector('#".$Campo."');
                    ".$Campo.".addEventListener('change', function () {
                        var files = this.files;
                        for(var i=0; i<files.length; i++){
                            uploadFile(this.files[i],'file_".$Campo."','img'); // call the function to upload the file
                            document.getElementById('gallery_file2_".$Campo."').style.display='none';
                            
                            previewImage(this.files[i],'file_".$Campo."');
                        }
                    }, false);                    
                    </script>
                    ";
                    break;  

                case 'file':
                    echo '<label class="form-label" for="'.$Campo.'">'.$Titulo.'</label>';
                    echo '<input name="'.$Campo.'" id="'.$Campo.'" class="border  form-control " type="file"  accept="'.$Filtro.'">';
                    echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'"  type="hidden" >';
                    echo '<input name="file_'.$Campo.'_1" id="file_'.$Campo.'_1"  type="hidden" >';
                    //echo '<input type="file" id="uploadfiles"  accept="image/*" />';
                    if ($Regla != "")
                        echo '<small class="form-text text-muted">'.$Regla.'</small>';


                    echo "</div>";
                    echo '<div class="form-row">';
                        echo "<div class='form-group col-xl-12 col-lg-12 col-md-12 col-sm-12'>";
                            echo "<img src='' id='gallery_file2_$Campo' name ='gallery_file2_$Campo'>";
                            echo "<div id='gallery_file_".$Campo."'>";
                                //echo "<img src='' id='gallery_file_$Campo' name ='gallery_file_$Campo'>";
                            echo "</div>";
                        echo "</div>";                    

                    echo "
                    <script>
                    var ".$Campo." = document.querySelector('#".$Campo."');
                    ".$Campo.".addEventListener('change', function () {
                        var files = this.files;
                        for(var i=0; i<files.length; i++){
                            uploadFile(this.files[i],'file_".$Campo."','file'); // call the function to upload the file
                            document.getElementById('gallery_file2_".$Campo."').style.display='none';
                        }
                    }, false);                    
                    </script>
                    ";
                    break;                      

                    

                    case 'complete':
                        echo "<label for='$Campo' class='form-label'>$Titulo</label>";
                            echo '<select name="'.$Campo.'" id="'.$Campo.'" data-tipo="complete" class="selectpicker form-control border-1  rounded " style="" '.$Requerido.' '.$SoloLectura.'>';
                            if ($Campo == "edit_ZonaHoraria"){
                                // Obtener todos los identificadores de zonas horarias
                                $zonas = DateTimeZone::listIdentifiers();
                                // Definir una zona por defecto (opcional)
                                //$zona_seleccionada = 'America/Mexico_City'; 
                                echo '<option value = "" selected> ... </option>';
                                foreach ($zonas as $zona):
                                    echo '<option value="'.$zona.'">'. ($zona).'</option>';
                                endforeach;
                            }
                            else{
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
?>