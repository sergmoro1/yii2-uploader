<?php

namespace sergmoro1\uploader\widgets;

use yii\web\AssetBundle;

class JcropAsset extends AssetBundle
{
	public $sourcePath = '@vendor/sergmoro1/yii2-byone-uploader/assets';
	public $css = [
		'css/jquery.Jcrop.min.css',
	];
	public $js = [
		'js/jcrop/jquery.Jcrop.min.js',
		'js/handler.Jcrop.js',
	];
	public $depends = [
		'sergmoro1\uploader\widgets\EditLineAsset',
	];
}
