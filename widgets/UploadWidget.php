<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace deanar\fileProcessor\widgets;

use \Yii;
use yii\helpers\Json;
use deanar\fileProcessor\models\Uploads;
use deanar\fileProcessor\assets\UploadAssets;
use deanar\fileProcessor\assets\BaseAssets;
use deanar\fileProcessor\helpers\FileHelper;
use yii\helpers\Url;


class UploadWidget extends \yii\base\Widget
{
    public $type;
    public $type_id;
    public $hash;

    public $identifier = 'file-processor-item';
    public $uploadUrl = null;
    public $removeUrl = null;
    public $sortUrl = null;

    public $multiple = true;

    public $options = [];

    private $options_allowed = ['autoUpload', 'multiple', 'accept', 'duplicate', 'maxSize', 'maxFiles', 'imageSize'];

    public function init()
    {
        parent::init();
        $this->hash        = rand(111111, 999999);
        $this->uploadUrl   = Url::toRoute('fp/base/upload', true);
        $this->removeUrl   = Url::toRoute('fp/base/remove', true);
        $this->sortUrl     = Url::toRoute('fp/base/sort', true);
        $this->identifier .= '-' . $this->hash;
    }

    private function generateOptionsArray(){
        if (empty($this->options)) return [];
        $return = [];

        if(!isset($this->options['multiple'])) $this->options['multiple'] = true;
        if($this->options['multiple'] === false){
            $this->options['maxFiles'] = 1;
            $this->options['multiple'] = true; // hack, because if false you can add many files, but uploaded will be only the last one.
        }
        $this->multiple = $this->options['multiple'];

        foreach($this->options as $option_name => $option_value){
            if( !in_array($option_name, $this->options_allowed)) continue;

            if($option_name == 'maxSize'){
                $option_value = FileHelper::sizeToBytes($option_value);
            }

            $return[$option_name] = $option_value;
        }
        return $return;
    }


    /**
     * Return array of already uploaded files. Used for display uploads in update form.
     * @param $type
     * @param $type_id
     * @return array
     */
    public function getAlreadyUploadedByReference($type, $type_id)
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
                    'src' => $item->getPublicFileUrl('_thumb'),
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



    /**
     * Renders the widget.
     */
    public function run()
    {
        $base_asset = BaseAssets::register($this->getView());
        $upload_asset = UploadAssets::register($this->getView());

        $additionalData = array(
            'type' => $this->type,
            'type_id' => $this->type_id,
            'hash' => $this->hash,
            Yii::$app->request->csrfParam => Yii::$app->request->getCsrfToken(),
        );

        $alreadyUploadedFiles = $this->getAlreadyUploadedByReference($this->type, $this->type_id);


        $settings = [
            'identifier'            => $this->identifier,
            'uploadUrl'             => $this->uploadUrl,
            'removeUrl'             => $this->removeUrl,
            'sortUrl'               => $this->sortUrl,
            'additionalData'        => $additionalData,
            'alreadyUploadedFiles'  => $alreadyUploadedFiles,
            'options'               => $this->generateOptionsArray(),
        ];

        $settingsJson = Json::encode($settings);

        $fileApiInitSettings = <<<EOF
        var FileAPI = {
            debug: false, media: true, staticPath: '$upload_asset->baseUrl', 'url' : '$this->uploadUrl'
        };
EOF;


        $fileApiRun = <<<EOF
        file_processor.multi_upload($settingsJson);
EOF;

        $this->getView()->registerJs($fileApiInitSettings);
        $this->getView()->registerJs($fileApiRun);

        return $this->render('upload_widget', array(
            'hash' => $this->hash,

            'identifier' => $this->identifier,
            'uploadUrl' => $this->uploadUrl,
            'multiple' => $this->multiple,
        ));
    }

}
