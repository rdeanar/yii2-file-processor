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

    public function init()
    {
        parent::init();
        $this->hash        = rand(111111, 999999);
        $this->uploadUrl   = Url::toRoute('fp/base/upload', true);
        $this->removeUrl   = Url::toRoute('fp/base/remove', true);
        $this->identifier .= '-' . $this->hash;
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
}
