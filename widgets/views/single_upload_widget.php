<?
/**
* @author Mikhail Razumovskiy <rdeanar@gmail.com>
*/

use \deanar\fileProcessor\Module;
?>

<?=\yii\helpers\Html::beginTag('div', $htmlOptions)?>

    <input type="hidden" name="fp_hash[]" value="<?= $hash ?>"/>

    <div class="js-preview userpic__preview"></div>

    <div class="js-delete" title="<?=Module::t('REMOVE_FILE')?>" style="display: none;">✖</div>


    <div class="btn btn-success js-browse js-controls" style="display: none;">
        <span class="btn-txt"><?=Module::t('CHOOSE_FILE')?></span>
        <input type="file" name="filedata">
    </div>
    <div class="process js-upload js-controls" style="display: none;">
        <div class="progress progress-success">
            <div class="js-progress bar"></div>
        </div>
        <span class="btn-txt"><?=Module::t('UPLOADING_PROCESS')?></span>
    </div>

</div>

<hr/>

<? if($crop){ ?>
<script type="text/template" id="fp_single_upload_modal_bs">
<div class="modal" id="<?=$identifier?>_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?=Module::t('MODAL_CROP_TITLE')?></h4>
            </div>
            <div class="modal-body">
                <div class="js-img"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary js-upload"><?=Module::t('MODAL_CROP_AND_UPLOAD')?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</script>

    <script type="text/template" id="fp_single_upload_modal_jq">
        <div id="popup" class="popup" style="display: none;">
            <div class="popup__body">
                <div class="js-img"></div>
            </div>
            <div style="margin: 0 0 5px; text-align: center;">
                <div class="js-upload btn btn_browse btn_browse_small"><?=Module::t('MODAL_CROP_AND_UPLOAD')?></div>
            </div>
        </div>
    </script>

<? } //if crop ?>