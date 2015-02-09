<?php

namespace deanar\fileProcessor\assets;

use yii\web\AssetBundle;

class JCropAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/jcrop/';

    public $js = [
        'js/jquery.Jcrop.min.js',
    ];
    public $css = [
        'css/jquery.Jcrop.min.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}