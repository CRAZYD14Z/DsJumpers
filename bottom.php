<div id="barra-inferior" class="fixed-bottom barra-minimalista bg-white border-top shadow-lg">
    <div class="container-fluid d-flex flex-column align-items-center py-2 px-4">
        
        <!-- FILA 1: Estatus del Lead -->
        <div class="w-100 d-flex justify-content-center align-items-center mb-2 pb-2 border-bottom border-light-subtle">
            <span class="text-muted small me-2 fw-semibold">Estatus:</span>
            <?php 
            $status = isset($lead) ? strtolower($lead['Status']) : 'nuevo';
            $badgeColor = 'bg-secondary';
            
            if (strpos($status, 'parcial') !== false || strpos($status, 'draft') !== false) {
                $badgeColor = 'text-bg-warning';
            } elseif (strpos($status, 'cotizado') !== false || strpos($status, 'quoted') !== false) {
                $badgeColor = 'text-bg-info';
            } elseif (strpos($status, 'confirmado') !== false || strpos($status, 'confirmed') !== false) {
                $badgeColor = 'text-bg-success';
            } elseif (strpos($status, 'cancelado') !== false || strpos($status, 'canceled') !== false) {
                $badgeColor = 'text-bg-danger';
            }
            ?>
            <span class="badge rounded-pill <?= $badgeColor ?> px-3 py-1.5 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                <?= isset($lead) ? $lead['Status'] : 'NUEVO (DRAFT)' ?>
            </span>

            <!-- Si está cancelado y hay motivo, se muestra aquí de forma limpia -->
            <?php if (isset($lead) && $status === 'canceled' && !empty($lead['CancellationReason'])): ?>
                <span class="text-danger small ms-3 italic" style="font-size: 0.8rem;">
                    <strong>Motivo:</strong> <?= htmlspecialchars($lead['CancellationReason']) ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- FILA 2: Grupo de Botones de Acción -->
        <div class="w-100 d-flex flex-wrap justify-content-between align-items-center gap-2">
            
            <?php if ((isset($lead) && $lead['Status'] != 'canceled') || !isset($lead)) { ?>
                <!-- Acciones secundarias (Documentos) -->
                <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-sm btn-outline-secondary px-3" onclick="LoadDocument('Quote')"><?php echo Trd(65)?></button>
                    <button class="btn btn-sm btn-outline-secondary px-3" onclick="LoadDocument('Contract')"><?php echo Trd(66)?></button>
                    <button class="btn btn-sm btn-outline-secondary px-3" onclick="LoadDocument('Invoice')"><?php echo Trd(67)?></button>
                    <button class="btn btn-sm btn-outline-secondary px-3" onclick="LoadDocument('Picking')"><?php echo Trd(68)?></button>            
                    <?php
                    if (strpos($status, 'confirmado') !== false || strpos($status, 'confirmed') !== false){
                        echo '<button class="btn btn-sm btn-outline-primary px-3" onclick="ProcesarSinPago()">Reprocesar</button>';
                    }
                    else{
                        echo '<button class="btn btn-sm btn-outline-primary px-3" onclick="ProcesarSinPago()">'.Trd(154).'</button>';
                    }
                    ?>
                    

                </div>

                <!-- Acciones principales de control -->
                <div class="d-flex gap-2 ms-auto">
                    <button class="btn btn-light border fw-semibold px-4 rounded-3 shadow-none text-secondary" type="button" onclick='Cancelar()'>
                        <i class="fa-solid fa-xmark me-1"></i><?php echo Trd(153)?>
                    </button>                

                    <button class="btn btn-primary fw-semibold px-4 rounded-3 shadow-sm" type="button" onclick='recalculate();autosave_lead();'>
                        <i class="fa-solid fa-floppy-disk me-1"></i><?php echo Trd(164)?>
                    </button>                
                </div>
                
            <?php } else { ?>
                <!-- Si el lead está cancelado, centramos el botón de reactivación -->
                <div class="w-100 d-flex justify-content-center">
                    <button class="btn btn-primary fw-semibold px-5 rounded-3 shadow-sm" type="button" onclick='Reactive()'>
                        <i class="fa-solid fa-star me-1"></i><?php echo Trd(160)?>
                    </button>
                </div>
            <?php } ?>            
                
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