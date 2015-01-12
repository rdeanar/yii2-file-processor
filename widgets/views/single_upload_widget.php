<?
/**
* @author Mikhail Razumovskiy <rdeanar@gmail.com>
*/
?>

<div id="<?= $identifier ?>" class="fp_single_upload">

    <input type="hidden" name="fp_hash[]" value="<?= $hash ?>"/>

    <div class="js-preview userpic__preview"></div>

    <div class="js-delete" style="display: none;">✖</div>


    <div class="btn btn-success js-browse js-controls" style="display: none;">
        <span class="btn-txt">Choose</span>
        <input type="file" name="filedata">
    </div>
    <div class="process js-upload js-controls" style="display: none;">
        <div class="progress progress-success">
            <div class="js-progress bar"></div>
        </div>
        <span class="btn-txt">Uploading</span>
    </div>

</div>

<hr/>

<? if($crop){ ?>
<script type="text/template" id="fp_single_upload_modal_bs">
<div class="modal" id="<?=$identifier?>_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
<!--                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
                <h4 class="modal-title">Crop</h4>
            </div>
            <div class="modal-body">
                <div class="js-img"></div>
            </div>
            <div class="modal-footer">
<!--                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
                <button type="button" class="btn btn-primary js-upload">Crop & upload</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</script>

<?
 /*
 //for jquery.modal plugin
<div id="popup" class="popup" style="display: none;">
    <div class="popup__body"><div class="js-img"></div></div>
    <div style="margin: 0 0 5px; text-align: center;">
        <div class="js-upload btn btn_browse btn_browse_small">Upload</div>
    </div>
</div>
 */
?>

<? } //if crop ?>