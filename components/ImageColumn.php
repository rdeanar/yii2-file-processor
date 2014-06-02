<?php
/**
 * Created by PhpStorm.
 * User: deanar
 * Date: 03/06/14
 * Time: 01:23
 */


namespace deanar\fileProcessor\components;

use \yii\grid\Column;
/**
 * ImageColumn displays first image attached to model.
 *
 * To add a ImageColumn to the [[GridView]], add it to the [[GridView::columns|columns]] configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => 'deanar\fileProcessor\components\ImageColumn',
 *         'variation' => '_thumb',
 *         // you may configure additional properties here
 *         'header' => 'Image',
 *         'empty' => 'No Image',
 *         'type' => 'projects',
 *     ],
 * ]
 * ```
 *
 * @author deanar <rdeanar@gmail.com>
 */
class ImageColumn extends Column
{
    //TODO lightbox with full preview

    public $header = '#'; //TODO add header value to locales
    public $empty = 'No image'; //TODO add default value and add to locales
    public $type = null;
    public $variation = null; // Maybe set default variation to '_thumb'
    public $htmlOptions = [];

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $imageTag = $model->getFirstFile($this->type)->imgTag($this->variation, true, $this->htmlOptions);
        return $imageTag ? $imageTag : $this->empty;
    }
}
