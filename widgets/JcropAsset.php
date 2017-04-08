<?php

namespace sergmoro1\uploader\widgets;

use yii\web\AssetBundle;

class JcropAsset extends AssetBundle
{
	public $sourcePath = '@bower/jcrop';
	public $css = [
		'css/Jcrop.min.css',
	];
	public $js = [
		'js/Jcrop.min.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
		'sergmoro1\uploader\widgets\CropHandlerAsset',
	];
}
