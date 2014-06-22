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
    public $space_replacement = '_';
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
}
