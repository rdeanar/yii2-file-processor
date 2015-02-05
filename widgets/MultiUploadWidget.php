<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace deanar\fileProcessor\widgets;

use \Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;
use deanar\fileProcessor\assets\UploadAssets;
use deanar\fileProcessor\assets\BaseAssets;
use deanar\fileProcessor\helpers\FileHelper;


class MultiUploadWidget extends BaseUploadWidget
{
    public $sortUrl = null;
    public $multiple = true;

    private $options_allowed = ['autoUpload', 'multiple', 'accept', 'duplicate', 'maxSize', 'maxFiles', 'imageSize'];

    public function init()
    {
        parent::init();
        $this->sortUrl = Url::toRoute('fp/base/sort', true);
    }

    /**
     * Return options array for fileapi
     * @return array
     */
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
            'sortUrl'               => $this->sortUrl,
            'additionalData'        => $additionalData,
            'alreadyUploadedFiles'  => $this->getAlreadyUploadedByReference($this->type, $this->type_id),
            'options'               => $this->generateOptionsArray(),
        ]);

        $fileApiInitSettings = <<<EOF
        var FileAPI = {
            debug: $this->debug, media: true, staticPath: '$upload_asset->baseUrl/jquery.fileapi/FileAPI/', 'url' : '$this->uploadUrl'
        };
EOF;

        $fileApiRun = <<<EOF
        file_processor.multi_upload($settingsJson);
EOF;

        $this->getView()->registerJs($fileApiInitSettings, View::POS_HEAD);
        $this->getView()->registerJs($fileApiRun);

        return $this->render('multi_upload_widget', array(
            'hash'          => $this->hash,
            'identifier'    => $this->identifier,
            'uploadUrl'     => $this->uploadUrl,
            'multiple'      => $this->multiple,
            'htmlOptions'   => $this->getHtmlOptionsWithBaseClasses(['b-upload', 'fp_multi_upload']),
        ));
    }

}
