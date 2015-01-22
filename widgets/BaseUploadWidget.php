<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace deanar\fileProcessor\widgets;

use \Yii;
use deanar\fileProcessor\models\Uploads;
use yii\helpers\Url;


class BaseUploadWidget extends \yii\base\Widget
{
    public $type;
    public $type_id;
    public $hash;

    public $identifier = 'file-processor-item';
    public $uploadUrl = null;
    public $removeUrl = null;

    public $multiple = false;

    public $options = [];
    public $htmlOptions = [];

    public $debug;

    public function init()
    {
        parent::init();
        $this->hash        = rand(111111, 999999);
        $this->uploadUrl   = Url::toRoute('fp/base/upload', true);
        $this->removeUrl   = Url::toRoute('fp/base/remove', true);
        $this->identifier .= '-' . $this->hash;
        $this->htmlOptions['id'] = $this->identifier;

        $this->debug       = Yii::$app->getModule('fp')->debug ? 'true' : 'false';
    }

    /**
     * Return array of already uploaded files. Used for display uploads in update form.
     * @param $type
     * @param $type_id
     * @param string $variation
     * @return array
     */
    public function getAlreadyUploadedByReference($type, $type_id, $variation = '_thumb')
    {
        if (is_null($type_id)) return [];

        $uploads = array();

        $array = Uploads::findByReference($type, $type_id);

        foreach ($array as $item) {
            /**
             * @var $item Uploads
             */
            array_push($uploads,
                array(
                    'src' => $item->getPublicFileUrl($variation),
                    'type' => $item->mime,
                    'name' => $item->original,
                    'size' => $item->size,
                    'data' => array(
                        'id' => $item->id,
                        'type' => $item->type,
                        'type_id' => $item->type_id,
                    )
                ));
        }

        return $uploads;
    }

    public function getHtmlOptionsWithBaseClasses($base_class_names){
        if(empty($this->htmlOptions['class'])){
            $this->htmlOptions['class'] = implode(' ', $base_class_names);
        }else {
            $this->htmlOptions['class'] = str_replace('  ', ' ',
                implode(' ',
                    array_unique(
                        array_merge(
                            $base_class_names,
                            explode(' ', $this->htmlOptions['class'])
                        )
                    )
                )
            );
        }

        return $this->htmlOptions;
    }
}
