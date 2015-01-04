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


class SingleUploadWidget extends BaseUploadWidget
{
    private $options_allowed = ['autoUpload', 'accept', 'duplicate', 'maxSize', 'maxFiles', 'imageSize'];

    public function init()
    {
        parent::init();
    }

    private function generateOptionsArray(){
        if (empty($this->options)) return [];
        $return = [];

        $this->options['maxFiles'] = 1;
        $this->multiple = false;

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

        $settingsJson = Json::encode([
            'identifier'            => $this->identifier,
            'uploadUrl'             => $this->uploadUrl,
            'removeUrl'             => $this->removeUrl,
            'additionalData'        => $additionalData,
            'alreadyUploadedFiles'  => $this->getAlreadyUploadedByReference($this->type, $this->type_id, 'original'),
            'options'               => $this->generateOptionsArray(),
        ]);

        $fileApiInitSettings = <<<EOF
        var FileAPI = {
            debug: true, media: true, staticPath: '$upload_asset->baseUrl', 'url' : '$this->uploadUrl'
        };
EOF;

    $fileApiRun = <<<EOF
        file_processor.single_upload($settingsJson);
EOF;

        $this->getView()->registerJs($fileApiInitSettings);
        $this->getView()->registerJs($fileApiRun);

            return $this->render('single_upload_widget', array(
                'hash' => $this->hash,

                'identifier' => $this->identifier,
                'uploadUrl' => $this->uploadUrl,
                'multiple' => $this->multiple,
            ));
    }

}
