<div class="container-fluid">
    
<button class="btn-pestaña-minimal d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas">
    <i class="fa-solid fa-sliders"></i>
    <span>FILTROS</span>
</button>

    <div class="d-none d-md-block py-2">
        <button class="btn btn-sm btn-outline-dark" onclick="toggleDesktopFilters()">
            <i class="fa-solid fa-columns"></i> Mostrar/Ocultar Filtros
        </button>
    </div>

    <div class="row">
        <div class="offcanvas-md offcanvas-start col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3" tabindex="-1" id="filtersOffcanvas">
            
            <div class="offcanvas-header d-md-none">
                <h5 class="offcanvas-title">Filtros de Búsqueda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filtersOffcanvas"></button>
            </div>

            <div class="offcanvas-body flex-column" id="filters">
                <label for="category" class="form-label">Categoria:</label>

                <div class="mb-3" id="RangeEvent" name="RangeEvent">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold mb-0" style="font-size: 0.85rem;">Rango del Evento:</label>
                        <button type="button" class="btn btn-link p-0 text-decoration-none text-danger" style="font-size: 0.75rem;" onclick="resetRange()">Limpiar</button>
                    </div>
                    
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-light" style="width: 40px;"><i class="fa-regular fa-calendar-plus"></i></span>
                        <input type="datetime-local" class="form-control" id="fechahorainicio" name="fechahorainicio" readonly  data-bs-toggle="modal" data-bs-target="#modalReserva" value = "<?php  echo !empty($lead['StartDateTime']) ? $lead['StartDateTime'] : '';?>">
                    </div>
                    
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light" style="width: 40px;"><i class="fa-regular fa-calendar-check"></i></span>
                        <input type="datetime-local" class="form-control" id="fechahorafin" name="fechahorafin" readonly  data-bs-toggle="modal" data-bs-target="#modalReserva" value = "<?php  echo !empty($lead['EndDateTime']) ? $lead['EndDateTime'] : '';?>">
                    </div>
                </div>

                <div class="input-group" id="CategorySelect" name="CategorySelect">
                    <div class="input-group input-group-minimal">
                        <input type="hidden" id="IdCategory" name="IdCategory">            
                        <input type="text" class="form-control input-minimal" placeholder="" readonly id="Category" name="Category">
                        <button class="btn btn-minimal" type="button" id="btn-inicio" onclick="reset_cat()">X</button>                    
                    </div>
                </div>

                <div class="scroll-container" id="Categories" name="Categories">
                    <table class="table table-custom-cat table-hover">
                        <thead>
                            <tr><th scope="col">Categorias</th></tr>
                        </thead>
                        <tbody id="Categories_List" name="Categories_List">
                            <?php
                                // TU CÓDIGO PHP ORIGINAL
                                $query = "select Id,Nombre FROM categories ORDER BY Nombre";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                if ($resultados) {
                                    foreach ($resultados as $registro) {
                                        // He añadido 'onclick' para que en móvil se cierre al elegir una categoría
                                        echo "<tr  style='cursor:pointer'><td style='display: none'>".$registro['Id']."</td>";
                                        echo "<td>".$registro['Nombre']."</td></tr>";
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>            

                <div class="input-group" id="ProductSelect" name="ProductSelect" style="display: none">
                    <div class="input-group input-group-minimal border">
                        <button class="btn btn-minimal" type="button" id="btn-inicio" onclick="reset_cat()">&laquo;</button>
                        <button class="btn btn-minimal" type="button" id="btn-anterior" onclick="reset_prod()">&lsaquo;</button>
                        <div class="divider"></div>
                        <input type="hidden" id="IdProducto" name="IdProducto">
                        <input type="text" class="form-control input-minimal" placeholder="Buscar registro..." readonly id="NombreProducto" name="NombreProducto">
                    </div>
                </div>            

                <div class="scroll-container" id="Category_Products" name="Category_Products" style="display: none">
                    <table class="table table-custom-prd table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Items</th>
                                <th scope="col">Avail</th>
                                <th scope="col">Price</th>
                            </tr>
                        </thead>
                        <tbody id="Category_Products_List" name="Category_Products_List">
                            </tbody>
                    </table>
                </div>

                <div class="scroll-container" id="Products_Elements" name="Products_Elements" style="display: none">
                    <table class="table table-custom-sprd table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Items</th>
                                <th scope="col">Avail</th>
                                <th scope="col">Price</th>
                            </tr>
                        </thead>
                        <tbody id="Products_Elements_List" name="Products_Elements_List">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class='col-12 col-sm-12 col-md-9 col-lg-9 col-xl-9 col-xxl-9' id="selections">
            <br>
            <div class="scroll-container">
                <div class="container mt-4">
                    <div class="row d-none d-md-flex pb-2 border-bottom fw-bold">
                        <div class="col-md-3">Nombre</div>
                        <div class="col-md-1 text-center"></div>
                        <div class="col-md-1">Disp.</div>
                        <div class="col-md-1">Cant.</div>
                        <div class="col-md-2">Desc.</div>
                        <div class="col-md-1 text-center">Imp.</div>
                        <div class="col-md-2">Precio</div>
                        <div class="col-md-1 text-center"></div>
                    </div>
                    <div id="lead_detail">
                        </div>
                </div>            
            </div>
        </div>
    </div>
</div>

    <!-- SECCION PIE -->
    <div class="row">
        <input type="hidden"  id="IdLead" name="IdLead" value = "<?php  echo !empty($lead['Id']) ? $lead['Id'] : '';?>" >
        <input type="hidden"  id="UUID" name="UUID" value = "<?php echo $UUID['UUID']?>" >
        <!-- SECCION REFERIDOS -->
        <div class='col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6'>
            <div class="row">
                <div class='col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12'>
                    <label for="Referal" class="form-label">Referal</label>
                    <select class="form-select select-auto" id="Referal" name="Referal">
                        <option></option>
                    </select>                    
                </div>    
            </div>
            <div class="row">
                <div class='col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12'>
                    <label for="Tags" class="form-label">Tags</label>
                    <input type="text" class="form-control" id="Tags" name="Tags" placeholder="Tags">
                </div>    
            </div>            
        </div>
        <!-- SECCION TOTALES -->
        <div class='col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6'>
            <div class="row">
                <div class='col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12'>
                    <table class="table table-slim table-striped">
                        <tbody>
                            <tr>
                                <td >
                                    Item Totals
                                </td>
                                <td ></td>
                                <td colspan="2" >
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end " readonly id="Item_Totals" value = "<?php  echo !empty($lead['ItemTotals']) ? $lead['ItemTotals'] : '0.00';?>">
                                    </div>                                      
                                </td>
                            </tr>

                            <tr>
                                <td colspan="4">
                                    <div class="icon-inputs">
                                        <div class="icon-input-group">
                                            <i class="fa-solid fa-scale-balanced"></i>
                                            <input type="number" value="0"  id="Scale" class="numbers-only">  Lbs
                                        </div>
                                        <div class="icon-input-group">
                                            <i class="fa-solid fa-plug-circle-bolt"></i>
                                            <input type="number" value="0"  id="Plugs" class="numbers-only">
                                        </div>
                                        <div class="icon-input-group">
                                            <i class="fa-solid fa-people-carry-box"></i>
                                            <input type="number" value="0"  id="People" class="numbers-only">
                                        </div>
                                        <div class="icon-input-group">
                                            <i class="fa-solid fa-people-group"></i>
                                            <input type="number" value="0"  id="Group" class="numbers-only">
                                        </div>
                                    </div>

                                </td>
                            </tr>

                            <tr>
                                <td >
                                    <input type="text" class="form-control" id="Distance_Charges" name="Distance_Charges" placeholder="Distance Charges" readonly>
                                </td>
                                <td >
                                    <input class="form-check-input" type="checkbox" id="Distance_Charges_check" name="Distance_Charges_check" onchange="recalculate_totals()"
                                    <?php  if (isset($lead['ChkDstC']) && $lead['ChkDstC'] == 1) echo "checked" ;?>
                                    >
                                </td>
                                <td >
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end decimals" value="0.00" id="Distance_Charges_Total" name="Distance_Charges_Total" onchange="recalculate_totals()" 
                                        value = "<?php  echo !empty($lead['DistanceCharges']) ? $lead['DistanceCharges'] : '0.00';?>"
                                        >
                                    </div>                            
                                </td>
                                <td >
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#Distance_Charges_Total').val('0.00');recalculate_totals()">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>    
                                </td>
                            </tr> 
                            <tr>
                                <td >
                                    <input type="text" class="form-control"  id="Staff_Charges" name="Staff_Charges" placeholder="Staff Costs" readonly>
                                </td>
                                <td >
                                    <input class="form-check-input" type="checkbox" id="Staff_Charges_check" name="Staff_Charges_check" onchange="recalculate_totals()"
                                    <?php  if (isset($lead['ChkStCs']) && $lead['ChkStCs'] == 1) echo "checked" ;?>
                                    >
                                </td>
                                <td >
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end decimals" value="0.00" id="Staff_Charges_Total" name="Staff_Charges_Total"  onchange="recalculate_totals()"
                                        value = "<?php  echo !empty($lead['StafCost']) ? $lead['StafCost'] : '0.00';?>"
                                        >
                                    </div>                            
                                </td>
                                <td >
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#Staff_Charges_Total').val('0.00');recalculate_totals()">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>    
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    <input type="text" class="form-control" id="Discount_Charges" name="Discount_Charges" placeholder="Discount" readonly>
                                </td>
                                <td >
                                    <input class="form-check-input" type="checkbox" id="Discount_Charges_check" name="Discount_Charges_check" onchange="recalculate_totals()"
                                    <?php  if (isset($lead['ChkDsc']) && $lead['ChkDsc'] == 1) echo "checked" ;?>
                                    >
                                </td>
                                <td >
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$ -</span>
                                        <input type="text" class="form-control text-end decimals" value="0.00" id="Discount_Charges_Total" name="Discount_Charges_Total" onchange="recalculate_totals()"
                                        value = "<?php  echo !empty($lead['Discount']) ? $lead['Discount'] : '0.00';?>"
                                        >
                                    </div>                            
                                </td>
                                <td >
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#Discount_Charges_Total').val('0.00');recalculate_totals()">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>    
                                </td>
                            </tr> 
                            <tr id="tr_discount">
                                <td >
                                    <select class="form-select" id="DiscountType" name="DiscountType">
                                            <option value="">Discount</option>
                                            <option value="Fee"> Fee</option>
                                            <option value="Cupon"> Cupon</option>
                                    </select>
                                </td>
                                <td >
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="Add_Discount()">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>    
                                </td>                        
                                <td ></td>
                                <td ></td>
                            </tr>

                            <tr>
                                <td >
                                    Subtotal
                                </td>
                                <td ></td>
                                <td  colspan="2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="SubTotal" name="SubTotal" value = "<?php  echo !empty($lead['SubTotal']) ? $lead['SubTotal'] : '0.00';?>" readonly>
                                    </div>                            
                                </td>
                            </tr> 
                            
                            <tr>
                                <td  colspan="2">
                                    Sales Tax    
                                    <input class="form-check-input" type="checkbox" id="Tax" name="Tax" onchange="recalculate_totals()" readonly>
                                    Excempt
                                </td>
                                <td  colspan="2">
                                    <div class="input-group input-group-sm" style= "display: none;" id="Excempt" >
                                        <span class="input-group-text bg-light">ID</span>
                                        <input type="text" class="form-control " value="" id="IDTAX" name="IDTAX" placeholder="#ID TAX" value = "<?php  echo !empty($lead['TaxId']) ? $lead['Taxid'] : '';?>" readonly>
                                    </div>

                                    <div class="input-group input-group-sm" id="NoExcempt1">
                                        <span class="input-group-text bg-light">%</span>
                                        <input type="text" class="form-control text-end" value="0.00" id="TaxPc" name="TaxPc" value = "<?php  echo !empty($lead['TaxPc']) ? $lead['TaxPc'] : '0.00';?>"readonly>
                                    </div>                            
                                    <div class="input-group input-group-sm" id="NoExcempt2">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end" value="0.00" id="TaxAm" name="TaxAm" value = "<?php  echo !empty($lead['TaxAmount']) ? $lead['TaxAmount'] : '0.00';?>" readonly>
                                    </div>
                                </td>
                            </tr>                     

                            <tr>
                                <td >
                                    Total
                                </td>
                                <td ></td>
                                <td  colspan="2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="Total" name="Total" value = "<?php  echo !empty($lead['Total']) ? $lead['Total'] : '0.00';?>" readonly>
                                    </div>                            
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    Required Deposit
                                </td>
                                <td ></td>
                                <td  colspan="2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="hidden" class="form-control text-end" placeholder="0.00" id="Deposit" name="Depopsit" value = "<?php  echo !empty($lead['Deposit']) ? $lead['Deposit'] : '0.00';?>" readonly>
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="DepositAmount" name="DepositAmount" value = "<?php  echo !empty($lead['DepositAmount']) ? $lead['DepositAmount'] : '0.00';?>" readonly>
                                    </div>                            
                                </td>
                            </tr>
                            <tr>
                                <td >
                                    Balance Due
                                </td>
                                <td ></td>
                                <td  colspan="2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="Balance" name="Balance" value = "<?php  echo !empty($lead['Balance']) ? $lead['Balance'] : '0.00';?>" readonly>
                                    </div>                            
                                </td>
                            </tr>                     
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
    </div>

    <script>
    // 1. Lógica para expandir/contraer en ESCRITORIO
// Función para Escritorio (Corrige el problema de visibilidad de Bootstrap)
function toggleDesktopFilters() {
    const filtersCol = document.getElementById('filtersOffcanvas');
    const selectionsCol = document.getElementById('selections');
    
    if (filtersCol.classList.contains('d-md-none-custom')) {
        // MOSTRAR
        filtersCol.classList.remove('d-md-none-custom');
        selectionsCol.classList.replace('col-md-12', 'col-md-9');
        // También quitamos las clases de tamaños mayores por si las usas
        selectionsCol.classList.remove('col-lg-12', 'col-xl-12', 'col-xxl-12');
    } else {
        // OCULTAR
        filtersCol.classList.add('d-md-none-custom');
        selectionsCol.classList.replace('col-md-9', 'col-md-12');
        selectionsCol.classList.add('col-lg-12', 'col-xl-12', 'col-xxl-12');
    }
}

// Función para cerrar automáticamente en móvil al seleccionar
function cerrarOffcanvasMovil() {
    if (window.innerWidth < 768) {
        const offcanvasElement = document.getElementById('filtersOffcanvas');
        const instance = bootstrap.Offcanvas.getInstance(offcanvasElement);
        if (instance) instance.hide();
    }
}


// --- AL CARGAR LA PÁGINA ---
document.addEventListener('DOMContentLoaded', function() {
    const ahora = new Date();
    const minHoy = formatDateForInput(ahora);
    
    // Bloquear fechas pasadas en ambos inputs desde el inicio
    document.getElementById('fechahorainicio').min = minHoy;
    document.getElementById('fechahorafin').min = minHoy;
});

// --- LÓGICA FECHA INICIO ---
document.getElementById('fechahorainicio').addEventListener('change', function() {
    if (!this.value) return;

    const ahora = new Date();
    const inicioSeleccionado = new Date(this.value);

    // 1. Validar que no sea pasado (por si lo escriben manualmente)
    if (inicioSeleccionado < ahora) {
        alert("No puedes seleccionar una fecha u hora del pasado.");
        this.value = formatDateForInput(ahora); // Reset al momento actual
        return;
    }

    // 2. Calcular +8 horas automáticamente
    let fechaFin = new Date(inicioSeleccionado.getTime() + (8 * 60 * 60 * 1000));
    const inputFin = document.getElementById('fechahorafin');
    
    inputFin.value = formatDateForInput(fechaFin);
    inputFin.min = this.value; // El fin no puede ser antes del nuevo inicio
});

// --- LÓGICA FECHA FIN ---
document.getElementById('fechahorafin').addEventListener('change', function() {
    const inicioVal = document.getElementById('fechahorainicio').value;
    const finVal = this.value;
    const ahora = new Date();

    if (finVal) {
        const finSeleccionado = new Date(finVal);

        // 1. Validar que no sea pasado
        if (finSeleccionado < ahora) {
            alert("La fecha de fin no puede ser en el pasado.");
            this.value = inicioVal ? inicioVal : formatDateForInput(ahora);
            return;
        }

        // 2. Validar que no sea anterior al inicio
        if (inicioVal && finSeleccionado < new Date(inicioVal)) {
            alert("La fecha de fin no puede ser anterior al inicio.");
            this.value = inicioVal;
        }
    }
});

/**
 * Función auxiliar para formatear objetos Date al formato YYYY-MM-DDTHH:mm
 * ajustado a la hora local del sistema.
 */
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

function resetRange() {
    const ahora = new Date();
    const minHoy = formatDateForInput(ahora);
    
    document.getElementById('fechahorainicio').value = '';
    document.getElementById('fechahorafin').value = '';
    document.getElementById('fechahorainicio').min = minHoy;
    document.getElementById('fechahorafin').min = minHoy;
}

</script>