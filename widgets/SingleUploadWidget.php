<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace deanar\fileProcessor\widgets;

use \Yii;
use yii\helpers\Json;
use yii\web\View;
use deanar\fileProcessor\assets\UploadAsset;
use deanar\fileProcessor\assets\FileAPIAsset;
use deanar\fileProcessor\helpers\FileHelper;


class SingleUploadWidget extends BaseUploadWidget
{
    public $crop = true;
    public $preview = true;

    public $previewSize = [200, 200];

    private $options_allowed = ['autoUpload', 'accept', 'maxSize', 'imageSize', 'imageAutoOrientation'];

    protected $language_keys = [
        'UPLOAD_ERROR',
        'UPLOAD_ERROR_DETAILED',
        'REMOVE_FILE_CONFIRM',
        'FLASH_NOT_SUPPORTED',
    ];

    public function init()
    {
        parent::init();

        $this->crop     = (bool)$this->crop;
        $this->preview  = (bool)$this->preview;

        if($this->crop) {
            $this->preview = true;
            $this->options['accept'] = 'image/*';
        }else{
            // without crop control
            $this->options['autoUpload'] = true;
        }

        if(is_array($this->previewSize) && count($this->previewSize) >= 2){
            $normalizePreviewSize = function($value) {
                $value = intval($value);
                if($value < 50 OR $value > 500){
                    $value = 200;
                }
                return $value;
            };
            $this->previewSize = array_map($normalizePreviewSize, $this->previewSize);
        }else{
            $this->previewSize = [200, 200];
        }

    }

    private function generateOptionsArray(){
        if (empty($this->options)) return [];
        $return = [];

        $this->options['maxFiles'] = 1;
        $this->multiple = false;
        $return['imageAutoOrientation'] = false;

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
        $upload_asset = UploadAsset::register($this->getView());
        $fileapi_asset = FileAPIAsset::register($this->getView());

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
            'crop'                  => $this->crop,
            'preview'               => $this->preview,
            'previewSize'           => $this->previewSize,
        ]);

        $fileApiInitSettings = <<<EOF
        var FileAPI = {
            debug: $this->debug, media: true, staticPath: '$fileapi_asset->baseUrl/FileAPI/', 'url' : '$this->uploadUrl'
        };
EOF;

    $fileApiRun = <<<EOF
        file_processor.single_upload($settingsJson);
EOF;

        $this->getView()->registerJs($fileApiInitSettings, View::POS_HEAD);
        $this->getView()->registerJs($fileApiRun);

        $params = array(
            'hash'          => $this->hash,

            'identifier'    => $this->identifier,
            'uploadUrl'     => $this->uploadUrl,
            'multiple'      => $this->multiple,
            'crop'          => $this->crop,
            'preview'       => $this->preview,
            'htmlOptions'   => $this->getHtmlOptionsWithBaseClasses($this->isSimple() ? ['fp_single_simple_upload'] : ['fp_single_upload']),
        );

        if($this->isSimple()) {
            return $this->render('single_upload_widget_simple', $params);
        }else{
            return $this->render('single_upload_widget', $params);
        }
    }

    /**
     * @return bool
     * With or without preview (simple)
     */
    public function isSimple(){
        return $this->preview === false;
    }

}
