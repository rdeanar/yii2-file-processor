<?php
/**
 * @author Mikhail Razumovskiy <rdeanar@gmail.com>
 */
?>

<?=\yii\helpers\Html::beginTag('div', $htmlOptions)?>

    <input type="hidden" name="fp_hash[]" value="<?= $hash ?>"/>

    <div class="js-preview userpic__preview"></div>

    <div class="btn btn-success btn-small js-fileapi-wrapper js-browse">
        <span>Browse</span>
        <input type="file" name="filedata">
    </div>
      <span class="js-info">
         <span class="js-name b-upload__name"></span>
         <span class="b-upload__size">
             <span class="js-size"></span>
             <div class="js-delete" style="display: none;">✖</div>
         </span>
      </span>
    <?php /*
    <hr>
    <!-- Controls, maybe later -->
    <button class="js-send btn-small btn btn-primary" type="submit">Send</button>
    <button class="js-reset btn-small btn btn-warning" type="reset">reset</button>
*/
    ?>

</div>

<hr/>
