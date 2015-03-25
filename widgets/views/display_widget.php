<?php
/**
 * @author Mikhail Razumovskiy <rdeanar@gmail.com>
 */
?>

<div class="fp-display-widget">
    <?php
    if(!is_null($uploads)) {
        foreach ($uploads as $upload) {
            /**
             * @var $upload deanar\fileProcessor\models\Uploads;
             */
            $htmlOptions['title'] = $upload->original;
            if ($upload->isImage()) {
                echo $upload->imgTag($variation, true, $htmlOptions);
            } else {
                $htmlOptions['href'] = $upload->getPublicFileUrl('original', true);
                $htmlOptions['target'] = '_blank';
                echo \yii\helpers\Html::tag('a', $upload->original, $htmlOptions);
            }
        }
    }
    ?>
</div>
