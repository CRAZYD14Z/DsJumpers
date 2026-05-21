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
                    modal_add.Visible = 1 AND
                    titulos_campos_tablas.Idioma = ?
                ORDER BY
                    modal_add.Fila ASC,
                    modal_add.Id ASC
            ";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $tabla);
            $stmt->bindValue(2, $etiqueta);
            $stmt->bindValue(3, $idioma);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                $FilaN = 0;
                // g-4 da una separación horizontal y vertical más generosa y profesional
                echo '<div class="row g-4">';
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
                    $ValorCampo     = $row['Valor'];

                    // Clases estándar de Bootstrap 5 (Quitamos el -sm para dar más aire y control de texto)
                    $form_control = "form-control";
                    $form_select  = "form-select";

                    if ($FilaN != $Fila && $FilaN != 0){
                        echo '</div>';
                        echo '<div class="row g-4">'; 
                    }   
                    $FilaN = $Fila;

                    $aps = "'";
                    if ($Patron != "") {
                        $Patron = ' pattern="'.$Patron.'" ';            
                    }
                                                
                    $Requerido = ($Requerido == 'X') ? 'required' : '';
                    $SoloLectura = ($SoloLectura == 'X') ? 'readonly="readonly"' : '';                    

                    echo "<div class='col-$ColXS col-sm-$ColSM col-md-$ColMD col-lg-$ColLG col-xl-$ColXL col-xxl-$ColXXL'>";

                    // Contenedor por campo para controlar márgenes de forma segura
                    echo '<div class="mb-1">';

                    switch ($TipoCampo) {
                        case 'titulo':
                            echo '<h5 class="border-bottom pb-2 mb-3 text-primary fw-bold">'.$Titulo.'</h5>';
                            break;

                        case 'label':
                            echo "<label class='form-label fw-semibold text-secondary d-block'>$Titulo</label>";
                            break;

                        case 'option':
                            $option = substr($Campo, 0, -2);
                            echo '<div class="form-check my-2">';
                            echo '<input class="form-check-input" type="radio" name="'.$option.'" id="'.$Campo.'" value="'.$Alineacion.'">';
                            echo '<label class="form-check-label fw-medium text-dark" for="'.$Campo.'">'.$Titulo.'</label>';
                            echo '</div>';
                            break;                        
                                                 
                        case 'hidden':
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" type="'.$TipoCampo.'" >';
                            break;                          

                        case 'text':
                        case 'random':
                        case 'number':
                        case 'decimal':
                        case 'currency':
                        case 'tel':
                        case 'url':
                        case 'password':
                        case 'email':
                        case 'date':
                        case 'time':
                            $html_value = ($TipoCampo == 'random') ? generarCodigoAlfanumerico() : $ValorCampo;
                            $input_type = in_array($TipoCampo, ['random', 'currency', 'decimal']) ? 'text' : ($TipoCampo == 'number' ? 'number' : $TipoCampo);
                            $extra_class = ($TipoCampo == 'number') ? ' numbers-only' : (($TipoCampo == 'decimal') ? ' decimals' : (($TipoCampo == 'currency') ? ' decimals currency' : ''));

                            // Etiqueta arriba, con tamaño controlado y peso semibold
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$html_value.'" name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.$extra_class.' w-100" type="'.$input_type.'" style="text-align: '.$Alineacion.';" '.$Requerido.' minlength="'.$LargoMin.'" maxlength="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$Patron.' '.$SoloLectura.'>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                          
                        case 'area':
                        case 'html':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<textarea name="'.$Campo.'" id="'.$Campo.'" class="'.$form_control.' w-100" style="min-height: 100px;" rows="'.$LargoMin.'" cols="'.$LargoMax.'" placeholder="'.$PlaceHolder.'" '.$SoloLectura.'>'.$ValorCampo.'</textarea>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;
                            
                        case 'range':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" type="'.$TipoCampo.'" class="form-range w-100" min="'.$LargoMin.'" max="'.$LargoMax.'" />';
                            break;

                        case 'color':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<input value="'.$ValorCampo.'" name="'.$Campo.'" id="'.$Campo.'" class="form-control form-control-color w-100" type="'.$TipoCampo.'" >';
                            break;

                        case 'select':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            if ($TablaDts2 != "") {
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.' w-100" onChange="change_send2(this.value,'.$aps.$Campo.$aps.','.$aps.$Receptor.$aps.','.$aps.$tabla.$aps.''.$aps.$aps.')" '.$Requerido.' '.$SoloLectura.'>';
                            } else {
                                echo '<select name="'.$Campo.'" id="'.$Campo.'" class="'.$form_select.' w-100" '.$Requerido.' '.$SoloLectura.'>';
                            }

                            $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                            $columnExists = $checkColumn->fetch();                            

                            if ($columnExists) {
                                $query = "SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$idioma' $Filtro ";
                            } else {
                                $query = "SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1 $Filtro ";
                            }                             
                            
                            $stmt_dts = $db->prepare($query);
                            $stmt_dts->execute();
                            $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                            
                            echo '<option value="" selected>...</option>';
                            if ($tabla_dts) {
                                foreach ($tabla_dts as $tabla_dt) {
                                    $Valor = $tabla_dt[$CampoValor];
                                    $Descripcion = $tabla_dt[$CampoDescripcion];
                                    echo '<option value="'.$Valor.'">'.$Descripcion.'</option>';
                                }
                            }
                            echo '</select>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;

                        case 'checkbox':
                            // Separación limpia para los switches dinámicos
                            echo '<div class="form-check form-switch pt-4">';
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" class="form-check-input" type="checkbox" '.$Requerido.' '.$SoloLectura.'>';
                            echo '<label class="form-check-label fw-semibold text-dark small" for="'.$Campo.'">'.$Titulo.'</label>';
                            echo '</div>';
                            break;

                        case 'button':
                            echo "<button type='button' class='btn btn-primary w-100 mt-4 shadow-sm' name='$Campo' id='$Campo'>$Titulo</button>";
                            break;                            

                        case 'img':
                        case 'file':
                            $accept_type = ($TipoCampo == 'img') ? 'img' : 'file';
                            echo "<label class='form-label fw-semibold text-dark small mb-1' for='file_".$Campo."'>$Titulo</label>";
                            echo '<div class="card p-3 border-dashed bg-light shadow-none">';
                            echo '<input name="file_'.$Campo.'" id="file_'.$Campo.'" class="form-control bg-white" type="file" accept="'.$Filtro.'">';
                            echo '<input name="'.$Campo.'" id="'.$Campo.'" type="hidden">';
                            
                            if ($Regla != "") {
                                echo '<div class="form-text small text-muted mt-1">'.$Regla.'</div>';
                            }
                            echo '<div id="gallery_'.$Campo.'" class="row g-2 mt-2"></div>';
                            echo '</div>';

                            echo "
                            <script>
                            var el_".$Campo." = document.querySelector('#file_".$Campo."');
                            el_".$Campo.".addEventListener('change', function () {
                                var files = this.files;
                                for(var i=0; i<files.length; i++){
                                    uploadFile(this.files[i],'".$Campo."','".$accept_type."'); 
                                    ".($TipoCampo == 'img' ? "previewImage(this.files[i],'".$Campo."');" : "")."
                                }
                            }, false);                    
                            </script>
                            ";
                            break;

                        case 'complete':
                            echo "<label for='$Campo' class='form-label fw-semibold text-dark small mb-1'>$Titulo</label>";
                            echo '<select name="'.$Campo.'" id="'.$Campo.'" class="selectpicker form-control border rounded shadow-sm" '.$Requerido.' '.$SoloLectura.'>';

                            $checkColumn = $db->query("SHOW COLUMNS FROM $TablaDts LIKE 'Idioma'");
                            $columnExists = $checkColumn->fetch();                            

                            if ($columnExists) {
                                $query = "SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE Idioma = '$idioma' $Filtro ";
                            } else {
                                $query = "SELECT $CampoValor,$CampoDescripcion FROM $TablaDts WHERE 1 = 1 $Filtro ";
                            }                                     

                            $stmt_dts = $db->prepare($query);
                            $stmt_dts->execute();
                            $tabla_dts = $stmt_dts->fetchAll(PDO::FETCH_ASSOC);
                            
                            echo '<option value="" selected>...</option>';
                            if ($tabla_dts) {
                                foreach ($tabla_dts as $tabla_dt) {
                                    $Valor = $tabla_dt[$CampoValor];
                                    $Descripcion = $tabla_dt[$CampoDescripcion];
                                    echo '<option value="'.$Valor.'">'.$Descripcion.'</option>';
                                }
                            }
                            echo '</select>';
                            echo '<div class="invalid-feedback">'.$Validacion.'</div>';
                            break;                            
                    }
                    echo '</div>'; // Cierra mb-1
                    echo '</div>'; // Cierra col-##
                }
                echo '</div>'; // Cierra row final
            }
}

function generarCodigoAlfanumerico() {
    $timestamp = base_convert(str_replace('.', '', microtime(true)), 10, 36);
    $caracteres = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randomPart = "";
    for ($i = 0; $i < 16; $i++) {
        $randomPart .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    $combinado = strtoupper(substr($timestamp . $randomPart, 0, 16));
    return implode("-", str_split($combinado, 4));
}
?>