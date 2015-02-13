<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace deanar\fileProcessor;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

class Module extends \yii\base\Module
{
    public $debug = false;

    public $image_driver;
    public $variations_config = [];
    public $upload_dir = 'uploads';
    public $default_quality = 95;
    public $default_resize_mod = 'inset';
    public $unlink_files = true;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'deanar\fileProcessor\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $default = require(__DIR__ . '/variations_default.php');
        $this->variations_config = ArrayHelper::merge($this->variations_config, $default);

        $this->registerTranslations();
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        return true;
    }

    /**
     * Register widget translations.
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['deanar/fileProcessor*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
//            'sourceLanguage' => 'en-US',
            'basePath' => '@deanar/fileProcessor/messages',
            'fileMap' => [
                'deanar/fileProcessor' => 'fp.php',
            ],
            'forceTranslation' => true
        ];
    }

    /**
     * Translate shortcut
     *
     * @param $message
     * @param array $params
     * @param null $language
     *
     * @return string
     */
    public static function t($message, $params = [], $language = null)
    {
        return \Yii::t('deanar/fileProcessor', $message, $params, $language);
    }
}
