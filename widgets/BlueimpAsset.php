<?php

namespace sergmoro1\uploader\widgets;

use yii\web\AssetBundle;

class BlueimpAsset extends AssetBundle
{
	public $sourcePath = '@vendor/sergmoro1/yii2-byone-uploader/assets';
	public $css = [
		'css/jquery.fileupload-ui.css',
	];
	public $js = [
		'js/blueimp/jquery.ui.widget.js',
		'js/blueimp/jquery.iframe-transport.js',
		'js/blueimp/jquery.fileupload.js',
		'js/handler.fileupload.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
	];
}
