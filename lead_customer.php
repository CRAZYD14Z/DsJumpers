<b><?php echo Trd(33)?></b>
<form id="customers">
    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="Organization" class="form-label"><?php echo Trd(34)?></label>
            <input type="hidden" id="IdOrganization" name="IdOrganization">
            <select class="form-select select-auto" id="Organization" name="Organization"  onchange="" placeho>
                <option></option>
            </select>    
            
        </div>
        <div class='col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="OkText" name="OkText" 
                <?php  if (isset($lead['OkT']) && $lead['OkT'] == 1) echo "checked" ;?>
                >
                <label class="form-check-label" for="OkText">
                    <?php echo Trd(35)?>
                </label>
            </div>         
        </div>     

        <div class='col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="WindAlert" name="WindAlert"
                <?php  if (isset($lead['WA']) && $lead['WA'] == 1) echo "checked" ;?>
                >
                <label class="form-check-label" for="WindAlert">
                    <?php echo Trd(36)?>
                </label>
            </div>         
        </div>     
    </div>

    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="Customer" class="form-label"><?php echo Trd(37)?></label>
            <input type="hidden" id="IdCustomer" name="IdCustomer" >
            <select class="form-select select-auto select-custom-template" id="Customer" name="Customer" onchange="">
                <option></option>
            </select>    
        </div>
        <div class='col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="AutoEmail" name="AutoEmail"
                <?php  if (isset($lead['AE']) && $lead['AE'] == 1) echo "checked" ;?>
                >
                <label class="form-check-label" for="AutoEmail">
                    <?php echo Trd(38)?>
                </label>
            </div>         
        </div>     
        <div class='col-6 col-sm-6 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="ManualEmail" name="ManualEmail"
                <?php  if (isset($lead['ME']) && $lead['ME'] == 1) echo "checked" ;?>
                >
                <label class="form-check-label" for="ManualEmail">
                    <?php echo Trd(39)?>
                </label>
            </div>         
        </div>     
    </div>    
    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="Street" class="form-label"><?php echo Trd(40)?></label>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="Street" name="Street">
                <span class="input-group-text bg-light" style="cursor: pointer;" ><i class="fa-solid fa-file-export " onclick="copy_ad()"></i></span>
                
            </div>              
        </div>
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="Cell" class="form-label"><?php echo Trd(41)?></label>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="" id="Cell" name="Cell">
                <span class="input-group-text bg-light"><i class="fa-solid fa-phone"></i></span>
            </div>  
        </div>        
    </div>    
    <div class="row">
        <div class='col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3'>
            <label for="Country" class="form-label"><?php echo Trd(42)?></label>
            <select class="form-select" id="Country" name="Country">
                <option><?php echo Trd(43)?></option> 
                    <?php
                        $query = "select Codigo,Pais FROM pais WHERE Idioma = 'es' ORDER BY Pais";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($resultados) {
                            foreach ($resultados as $registro) {
                                echo "<option value='".$registro['Codigo']."'>".$registro['Pais']."</option>";
                            }
                        }
                    ?>
            </select>
        </div>
        <div class='col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3'>
            <label for="State" class="form-label"><?php echo Trd(44)?></label>
            <select class="form-select" id="State" name="State">
                <option value=""><?php echo Trd(45)?></option> 

                    <?php
                        $query = "select Id,Estado,CodigoPais FROM   estados_pais  WHERE Idioma = 'es' ORDER BY CodigoPais,Estado";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($resultados) {
                            foreach ($resultados as $registro) {
                                echo "<option data-country='".$registro['CodigoPais']."' value='".$registro['Id']."' hidden disabled>".$registro['Estado']."</option>";
                            }
                        }
                    ?>
            </select>
        </div>
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="City" class="form-label"><?php echo Trd(46)?></label>
            <input type="text" class="form-control" id="City" name="City" placeholder=""> 
        </div>    
        
        <div class='col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2'>
            <label for="Zip" class="form-label"><?php echo Trd(47)?></label>
            <input type="text" class="form-control" id="Zip" name="Zip" placeholder="00000"> 
        </div>        
    </div>     
    <div class="row">
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="CustomerEmail" class="form-label"><?php echo Trd(48)?></label>
            <input type="email" class="form-control" id="CustomerEmail" name="CustomerEmail" placeholder=""> 
        </div>
    </div>     
    <div class="row">
        <div class='col-12 col-sm-12 col-md-4 col-lg-12 col-xl-12 col-xxl-12'>
            <label class="form-label text-muted small"><?php echo Trd(49)?></label>
            <textarea class="form-control form-control-minimal" rows="5" placeholder="" id="CustomerNote" name="CustomerNote" onchange="aplicar_autosave_10();" ><?php  echo !empty($lead['CustomerNote']) ? $lead['CustomerNote'] : '';?></textarea>
        </div>
    </div>
</form> 