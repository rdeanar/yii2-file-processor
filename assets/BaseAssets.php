<?php
namespace deanar\fileProcessor\assets;
use yii\web\AssetBundle;

class BaseAssets extends AssetBundle
{
	public $sourcePath = '@deanar/fileProcessor/assets';

	public $js = [
		'js/fp_base.js',
		'js/fp_multi_upload.js',
		'js/fp_single_upload.js',
	];
	public $css = [
		'css/fp_upload.css'
	];

    public $depends = [
    ];
}