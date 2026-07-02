<div id="barra-inferior" class="fixed-bottom barra-minimalista">
    <div class="container-fluid d-flex justify-content-center align-items-center py-2"> <div class="button-group">
            <?php if ((isset($lead) AND $lead['Status'] != 'canceled') OR !isset($lead) ){?>
                <button class="btn-minimal" onclick="LoadDocument('Quote')"><?php echo Trd(65)?></button>
                <button class="btn-minimal" onclick="LoadDocument('Contract')"><?php echo Trd(66)?></button>
                <button class="btn-minimal" onclick="LoadDocument('Invoice')"><?php echo Trd(67)?></button>
                <button class="btn-minimal" onclick="LoadDocument('Picking')"><?php echo Trd(68)?></button>            
                <button class="btn-minimal" onclick="ProcesarSinPago()"><?= Trd(154) ?></button>


                <button class="btn btn-light border fw-semibold px-4 rounded-3 shadow-none text-secondary" type="button" onclick='Cancelar()'>
                    <i class="fa-solid fa-xmark me-1"></i><?php echo Trd(153)?>
                </button>                

                <button class="btn btn-primary fw-semibold px-4 rounded-3 shadow-sm" type="button" onclick='recalculate();autosave_lead();'>
                    <i class="fa-solid fa-floppy-disk me-1"></i><?php echo Trd(164)?>
                </button>                


                
            <?php }else{
                ?>
                <button class="btn btn-primary fw-semibold px-4 rounded-3 shadow-sm" type="button" onclick='Reactive()'>
                    <i class="fa-solid fa-star me-1"></i><?php echo Trd(160)?>
                </button>
                <?php
            } ?>            
                
        </div>
    </div>
</div>

<div id="barra-mensajes" class="fixed-bottom d-none" style="z-index: 2000; display: none;">
    <div class="container-fluid d-flex justify-content-between align-items-center py-2 px-4">
        <div class="d-flex align-items-center">
            <span id="mensaje-icono" class="me-2"></span>
            <span id="mensaje-texto" class="fw-light small tracking-tight"></span>
        </div>
        <span class="btn-cerrar-mini" onclick="cerrarBarra()">X</span>
    </div>
</div>