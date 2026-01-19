<?php
function add_listado($IdTabla){
?>
    <div class="container mt-4  shadow rounded" id="listado_<?php echo $IdTabla;?>">
        <br>
        <div class="row">
            <div class='col-8'>
                <div class="input-group">
                    <input class="form-control form-control-sm" type="search" placeholder="<?php echo Trd(25)?>" id="Search_<?php echo $IdTabla;?>" name="Search_<?php echo $IdTabla;?>" aria-label="Search">
                    <button class="btn btn-primary px-4" type="button" onclick="listado('<?php echo $IdTabla;?>');">
                            <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class='col-4'>
            <button type="button" class="btn btn-primary" onclick='$("#listado_<?php echo $IdTabla;?>").hide();$("#add_form_<?php echo $IdTabla;?>").show();'>
                <i class="fas fa-plus"></i> <small><?php echo Trd(2)?></small>
            </button>                
            </div>
        </div>

        <!-- Contenedor de la tabla -->
        <div id="table-container_<?php echo $IdTabla;?>" class="table-responsive">
            <!-- La tabla se generará aquí -->
        </div>
        <!-- Contenedor de paginación -->
        <div id="pagination-container_<?php echo $IdTabla;?>" class="pagination-container mt-3">
            <!-- La paginación se generará aquí -->
        </div>
    </div>
<?php
}
?>