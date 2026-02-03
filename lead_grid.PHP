    <div class="row">
        <div class='col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3'>
            <label for="category" class="form-label">Categoria:</label>

            <div class="input-group" id="CategorySelect" name="CategorySelect" >
                <div class="input-group input-group-minimal">
                    <input type="hidden" id="IdCategory" name="IdCategory" >            
                    <input type="text" class="form-control input-minimal" placeholder="Buscar registro..." readonly id="Category" name="Category" >
                    <button class="btn btn-minimal" type="button" id="btn-inicio" onclick="reset_cat()">
                        X
                    </button>                    
                </div>
            </div>            

            <div class="scroll-container" id ="Categories" name = "Categories" >
                <table class="table table-custom-cat table-hover">
                    <thead>
                        <tr>
                        <th scope="col">Categorias</th>
                        </tr>
                    </thead>
                    <tbody id ="Categories_List" name = "Categories_List">
                        <?php
                            $query = "select Id,Nombre FROM  categories ORDER BY Nombre";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($resultados) {
                                foreach ($resultados as $registro) {
                                    echo "<tr><td style='display: none'>".$registro['Id']."</td>";
                                    echo "<td>".$registro['Nombre']."</td></tr>";
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>            

            <div class="input-group" id="ProductSelect" name="ProductSelect"  style="display: none">
                <div class="input-group input-group-minimal">
                    <button class="btn btn-minimal" type="button" id="btn-inicio" onclick="reset_cat()">
                        &laquo;
                    </button>
                    <button class="btn btn-minimal" type="button" id="btn-anterior" onclick="reset_prod()">
                        &lsaquo;
                    </button>
                    <div class="divider"></div>
                    <input type="hidden" id="IdProducto" name="IdProducto" >
                    <input type="text" class="form-control input-minimal" placeholder="Buscar registro..." readonly id="NombreProducto" name="NombreProducto" >
                </div>
            </div>            

            <div class="scroll-container" id ="Category_Products" name = "Category_Products" style="display: none" >
                <table class="table table-custom-prd table-hover">
                    <thead>
                        <tr>
                        <th scope="col">Items</th>
                        <th scope="col">Avail</th>
                        <th scope="col">Price</th>
                        </tr>
                    </thead>
                    <tbody id ="Category_Products_List" name = "Category_Products_List">
                        <tr>
                            <td style='display: none'>1</td>
                            <td>Producto</td>
                            <td>1</td>
                            <td style="text-align: right;">$1500.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="scroll-container" id ="Products_Elements" name = "Products_Elements" style="display: none">
                <table class="table table-custom-sprd table-hover">
                    <thead>
                        <tr>
                        <th scope="col">Items</th>
                        <th scope="col">Avail</th>
                        <th scope="col">Price</th>
                        </tr>
                    </thead>
                    <tbody id ="Products_Elements_List" name = "Products_Elements_List">
                        <tr>
                            <td style='display: none'>1</td>
                            <td>Relacion</td>
                            <td><i class="fa-solid fa-infinity"></i></td>
                            <td style="text-align: right;">$1500.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
        <div class='col-12 col-sm-12 col-md-9 col-lg-9 col-xl-9 col-xxl-9'>
            <br>
            <div class="scroll-container">

                <div class="container mt-4">
                    <div class="row d-none d-md-flex pb-2 border-bottom fw-bold ">
                        <div class="col-md-3">Nombre</div>
                        <div class="col-md-1 text-center"></div>
                        <div class="col-md-1">Disp.</div>
                        <div class="col-md-1">Cant.</div>
                        <div class="col-md-2">Desc.</div>
                        <div class="col-md-1 text-center">Imp.</div>
                        <div class="col-md-2">Precio</div>
                        <div class="col-md-1 text-center"></div>
                    </div>
                    <div id = "lead_detail">


                    </div>
                </div>            
            </div>
        </div>
    </div>
    <!-- SECCION PIE -->
    <div class="row">
        <!-- SECCION REFERIDOS -->
        <div class='col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6'>
            <div class="row">
                <div class='col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12'>
                    <label for="Referal" class="form-label">Referal</label>
                    <select class="form-select select-auto" id="Referal" name="Referal">

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
                                        <input type="text" class="form-control text-end " placeholder="0.00" readonly id="Item_Totals">
                                    </div>                                      
                                </td>
                            </tr>

                            <tr>
                                <td colspan="4">
                                    <div class="icon-inputs">
                                        <div class="icon-input-group">
                                            <i class="fa-solid fa-scale-balanced"></i>
                                            <input type="text" placeholder="0"  id="Scale">
                                        </div>
                                        <div class="icon-input-group">
                                            <i class="fa-solid fa-plug-circle-bolt"></i>
                                            <input type="text" placeholder="0"  id="Plugs">
                                        </div>
                                        <div class="icon-input-group">
                                            <i class="fa-solid fa-people-carry-box"></i>
                                            <input type="text" placeholder="0"  id="People">
                                        </div>
                                        <div class="icon-input-group">
                                            <i class="fa-solid fa-people-group"></i>
                                            <input type="text" placeholder="0"  id="Group">
                                        </div>
                                    </div>

                                </td>
                            </tr>

                            <tr>
                                <td >
                                    <input type="text" class="form-control" id="Distance_Charges" name="Distance_Charges" placeholder="Distance Charges" readonly>
                                </td>
                                <td >
                                    <input class="form-check-input" type="checkbox" id="Distance_Charges_check" name="Distance_Charges_check" onchange="recalculate_totals()">
                                </td>
                                <td >
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end decimals" value="0.00" id="Distance_Charges_Total" name="Distance_Charges_Total" onchange="recalculate_totals()" >
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
                                    <input class="form-check-input" type="checkbox" id="Staff_Charges_check" name="Staff_Charges_check" onchange="recalculate_totals()">
                                </td>
                                <td >
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end decimals" value="0.00" id="Staff_Charges_Total" name="Staff_Charges_Total"  onchange="recalculate_totals()">
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
                                    <input class="form-check-input" type="checkbox" id="Discount_Charges_check" name="Discount_Charges_check" onchange="recalculate_totals()">
                                </td>
                                <td >
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end decimals" value="0.00" id="Discount_Charges_Total" name="Discount_Charges_Total" onchange="recalculate_totals()">
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
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="SubTotal" name="SubTotal" readonly>
                                    </div>                            
                                </td>
                            </tr> 
                            
                            <tr>
                                <td  colspan="2">
                                    Sales Tax    
                                    <input class="form-check-input" type="checkbox" id="Tax" name="Tax" readonly>
                                    Excempt
                                </td>
                                <td  colspan="2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">%</span>
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="TaxPc" name="TaxPc" readonly>
                                    </div>                            
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="TaxAm" name="TaxAm" readonly>
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
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="Total" name="Total" readonly>
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
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="Deposit" name="Depopsit" readonly>
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
                                        <input type="text" class="form-control text-end" placeholder="0.00" id="Balance" name="Balance" readonly>
                                    </div>                            
                                </td>
                            </tr>                     
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
    </div>