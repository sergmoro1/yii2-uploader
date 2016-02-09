<?php

namespace sergmoro1\uploader\widgets;

use yii\web\AssetBundle;

class EditLineAsset extends AssetBundle
{
	public $sourcePath = '@vendor/sergmoro1/yii2-byone-uploader/assets';
	public $css = [
		'css/editLine.css',
	];
	public $js = [
		'js/editLine.js',
	];
	public $depends = [
		'sergmoro1\uploader\widgets\BlueimpAsset',
	];
}
