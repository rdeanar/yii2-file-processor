<?php
namespace deanar\fileProcessor;
use yii\web\AssetBundle;

class UploadAssets extends AssetBundle
{
	public $sourcePath = '@deanar/fileProcessor/assets';

	public $js = [
		'jquery.fileapi/FileAPI/FileAPI.min.js',
		'jquery.fileapi/FileAPI/FileAPI.exif.js',
		'jquery.fileapi/jquery.fileapi.js',
	];
	public $css = [
		'jquery.fileapi/statics/main.css',
	];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}