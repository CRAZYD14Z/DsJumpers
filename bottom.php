<div id="barra-inferior" class="fixed-bottom barra-minimalista">
    <div class="container-fluid d-flex justify-content-center align-items-center py-2"> <div class="button-group">
            <button class="btn-minimal" onclick="LoadDocument('Quote')">Cotización</button>
            <button class="btn-minimal" onclick="LoadDocument('Contract')">Contrato</button>
            <button class="btn-minimal" onclick="LoadDocument('Invoice')">Factura</button>
            <button class="btn-minimal" onclick="LoadDocument('Picking')">Envío</button>
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