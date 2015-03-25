<?php
/**
 * @author Mikhail Razumovskiy <rdeanar@gmail.com>
 */

use \deanar\fileProcessor\Module;

?>

<?=\yii\helpers\Html::beginTag('div', $htmlOptions)?>
<input type="hidden" name="fp_hash[]" value="<?=$hash?>"/>
<div class="b-upload__dnd">
    <div class="b-upload__hint"><?=Module::t('ADD_FILES_TO_QUEUE_TIP')?></div>

    <ul class="clearfix js-files b-upload__files">
        <li class="js-file-tpl b-thumb js-sort" data-id="<%=uid%>" title="<%-name%>, <%-sizeText%>">
            <div data-fileapi="file.remove" class="b-thumb__del">✖</div>
            <div class="b-thumb__preview">
                <div class="b-thumb__preview__pic"></div>
            </div>
            <% if( /^image/.test(type) ){ %>
                <div data-fileapi="file.rotate.cw" class="b-thumb__rotate"></div>
            <% } %>
            <div class="b-thumb__progress progress-upload progress-small">
                <div class="bar"></div>
            </div>
            <div class="b-thumb__name"><%-name%></div>
        </li>
    </ul>

    <div class="btn btn-success btn-small js-fileapi-wrapper">
        <span><?=Module::t('ADD_FILES_BUTTON')?></span>
        <input type="file" name="filedata" <?= $multiple ? 'multiple' : '' ?> />
    </div> <span class="fp-dragndrop-hint"><?=Module::t('OR_DRAG_N_DROP_HERE')?></span>
    <div class="js-upload btn btn-success btn-small">
        <span><?=Module::t('UPLOAD_BUTTON')?></span>
    </div>
</div>
<?=\yii\helpers\Html::endTag('div')?>
