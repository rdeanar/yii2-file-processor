<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace deanar\fileProcessor;

use \Yii;
use deanar\fileProcessor\models;
use deanar\fileProcessor\assets\DisplayWidgetAssets;


class DisplayWidget extends \yii\base\Widget
{
    public $type;
    public $type_id;
    public $variation = null;
    public $htmlOptions = ['class'=>'img-thumbnail'];

    public function init()
    {
        parent::init();
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        if( is_null($this->variation) ) return '';

        $asset = DisplayWidgetAssets::register($this->getView());

        $uploads = models\Uploads::getUploads($this->type, $this->type_id);
        return $this->render('display_widget', [
            'uploads'     => $uploads,
            'variation'   => $this->variation,
            'htmlOptions' => $this->htmlOptions,
        ]);
    }

}
