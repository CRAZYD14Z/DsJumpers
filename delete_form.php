<?php
function delete_form($IdTabla){
?>
    <!-- Modal -->
    <div class="modal fade" id="delete_<?php echo $IdTabla;?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><?php echo Trd(8)?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <b><?php echo Trd(9)?></b>
            <p><?php echo Trd(10)?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Trd(6)?></button>
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="deleteRecord(IdDelete,'<?php echo $IdTabla;?>')"><?php echo Trd(11)?></button>
        </div>
        </div>
    </div>
    </div>
<?php
}
?>