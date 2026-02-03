<!-- SECCION VENUES -->
    <b>VENUE</b>
    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="Venue" class="form-label">Venue Name</label>
            <input type="hidden" id="IdVenue" name="IdVenue" >
            <select class="form-select select-auto" id="Venue" name="Venue"  onchange="load_venue(this.value)">
            </select>    
            
        </div>
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="EventName" class="form-label">Event Name</label>
            <input type="text" class="form-control" id="EventName" name="EventName" placeholder="">
        </div>        
    </div>
    <div class="row">
        <div class='col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8'>
            <label for="EventStreet" class="form-label">Street</label>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id= "EventStreet" name="EventStreet" placeholder="">
                <span class="input-group-text bg-light"><i class="fa-solid fa-map-location-dot" onclick="abrirRutaGoogleMaps()"></i></span>
                
            </div>  
        </div>
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="Surface" class="form-label">Surface</label>
            <input type="hidden" id="IdSurface" name="IdSurface" >
            <select class="form-select select-auto" id="Surface" name="Surface">
                <option></option> 

                        <?php
                            $query = "select Id,Nombre FROM  surfaces ORDER BY Nombre";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($resultados) {
                                foreach ($resultados as $registro) {
                                    echo "<option value='".$registro['Id']."'>".$registro['Nombre']."</option>";
                                }
                            }
                        ?>                
            </select>    
        </div>        
    </div>    
    <div class="row">
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="EventState" class="form-label">Event State</label>
            <input type="hidden" id="IdEventState" name="IdEventState" >
            <select class="form-select select-auto" id="EventState" name="EventState">
                <option></option> 
                <option value="AR">Argentina</option>
                <option value="BR">Brasil</option>
                <option value="CL">Chile</option>
                <option value="MX">México</option>
            </select>    
        </div>
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="EventCity" class="form-label">City</label>
            <input type="text" class="form-control" id="EventCity" name="EventCity" placeholder=""> 
        </div>    
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="EventZip" class="form-label">Event Zip</label>
            <input type="text" class="form-control" id="EventZip" name="EventZip" placeholder="00000"> 
        </div>        
    </div>     
    <div class="row">        
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <label for="DeliveryType" class="form-label">Delivery type</label>
            <input type="hidden" id="IdDeliveryType" name="IdDeliveryType" >
            <select class="form-select select-auto" id="DeliveryType" name="DeliveryType">
                <option></option> 
                <option value="AR">Argentina</option>
                <option value="BR">Brasil</option>
                <option value="CL">Chile</option>
                <option value="MX">México</option>
            </select>    
        </div>        
        <div class='col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4'>
            <br><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="Blacklisted" name="BlackListed">
                <label class="form-check-label" for="BlackListed">
                Blacklisted
                </label>
            </div>        
        </div>        
    </div>   
    <div class="row">
        <div class='col-12'>
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="nota1-tab" data-bs-toggle="tab" data-bs-target="#nota1" type="button" role="tab">
                        Nota 1
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="nota2-tab" data-bs-toggle="tab" data-bs-target="#nota2" type="button" role="tab">
                        Nota 2
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="nota1" role="tabpanel" aria-labelledby="nota1-tab">
                    <label class="form-label text-muted small">Contenido de la Nota 1</label>
                    <textarea class="form-control form-control-minimal" rows="5" placeholder="" id="Note_1" name="Note_1"></textarea>
                </div>
                <div class="tab-pane fade" id="nota2" role="tabpanel" aria-labelledby="nota2-tab">
                    <label class="form-label text-muted small">Contenido de la Nota 2</label>
                    <textarea class="form-control form-control-minimal" rows="5" placeholder=""  id="Note_2" name="Note_2"></textarea>
                </div>
            </div>   
        </div>   
    </div> 