<?php

namespace sergmoro1\uploader\bundles;

use yii\web\AssetBundle;

class CropHandlerAsset extends AssetBundle
{
	public $sourcePath = '@vendor/sergmoro1/yii2-byone-uploader/assets';
	public $css = [
	];
	public $js = [
		'js/handler.Jcrop.js',
	];
	public $depends = [
	];
}
