<?php

namespace sergmoro1\uploader\widgets;

use yii\web\AssetBundle;

class BlueimpAsset extends AssetBundle
{
	public $sourcePath = '@bower/jQuery-File-Upload';
	public $css = [
		'css/jquery.fileupload-ui.css',
	];
	public $js = [
		'js/vendor/jquery.ui.widget.js',
		'js/jquery.iframe-transport.js',
		'js/jquery.fileupload.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
		'sergmoro1\uploader\widgets\FileHandlerAsset',
	];
}
