<?php
namespace deanar\fileProcessor\assets;
use yii\web\AssetBundle;

class UploadAssets extends AssetBundle
{
	public $sourcePath = '@deanar/fileProcessor/vendor/assets';

	public $js = [
		'jquery.fileapi/FileAPI/FileAPI.min.js',
		'jquery.fileapi/FileAPI/FileAPI.exif.js',
		'jquery.fileapi/jquery.fileapi.js',

        'Sortable/Sortable.js',
	];
	public $css = [
		'jquery.fileapi/statics/main.css',
	];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}