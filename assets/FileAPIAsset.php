<?php

namespace deanar\fileProcessor\assets;

use yii\web\AssetBundle;

/**
 * jquery.fileapi asset bundle.
 */
class FileAPIAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/rubaxa/fileapi';

    /**
     * @inheritdoc
     */
    public $js = [
        'FileAPI/FileAPI.min.js',
        'FileAPI/FileAPI.exif.js',
        'jquery.fileapi.min.js'
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}