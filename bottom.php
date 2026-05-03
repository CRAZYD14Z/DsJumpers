<div id="barra-inferior" class="fixed-bottom barra-minimalista">
    <div class="container-fluid d-flex justify-content-center align-items-center py-2"> <div class="button-group">
            <button class="btn-minimal" onclick="LoadDocument('Quote')"><?php echo Trd(65)?></button>
            <button class="btn-minimal" onclick="LoadDocument('Contract')"><?php echo Trd(66)?></button>
            <button class="btn-minimal" onclick="LoadDocument('Invoice')"><?php echo Trd(67)?></button>
            <button class="btn-minimal" onclick="LoadDocument('Picking')"><?php echo Trd(68)?></button>
            <button class="btn-minimal" onclick="ProcesarSinPago()">Procesar sin pago</button>
            <button class="btn-minimalr" onclick="Cancelar()">Cancelar</button>
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