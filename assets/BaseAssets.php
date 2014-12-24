<?php
namespace deanar\fileProcessor\assets;
use yii\web\AssetBundle;

class BaseAssets extends AssetBundle
{
	public $sourcePath = '@deanar/fileProcessor/assets';

	public $js = [
		'js/fp_base.js',
		'js/fp_multi_upload.js',
	];
	public $css = [
	];

    public $depends = [
    ];
}