<?php

namespace sergmoro1\uploader\bundles;

use yii\web\AssetBundle;

class JQueryUiAsset extends AssetBundle
{
	public $sourcePath = '@bower/jquery-ui';
	public $css = [
	];
	public $js = [
		'jquery-ui.min.js',
	];
	public $depends = [
	];
}
