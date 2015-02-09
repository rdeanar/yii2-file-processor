<?php

namespace deanar\fileProcessor\assets;

use yii\web\AssetBundle;

class UploadAsset extends AssetBundle
{
	public $sourcePath = '@deanar/fileProcessor/vendor/assets';

	public $js = [
        'Sortable/Sortable.js',
	];
	public $css = [
	];

    public $depends = [
        'yii\web\JqueryAsset',
        'deanar\fileProcessor\assets\FileAPIAsset',
        'deanar\fileProcessor\assets\BaseUploadAsset',
        'deanar\fileProcessor\assets\JCropAsset',
    ];
}