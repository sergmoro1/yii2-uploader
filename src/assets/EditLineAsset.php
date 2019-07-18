<?php

namespace sergmoro1\uploader\assets;

use yii\web\AssetBundle;

class EditLineAsset extends AssetBundle
{
    public $sourcePath = '@vendor/sergmoro1/yii2-uploader/src/assets/src';
    public $css = [
        'css/editLine.css',
    ];
    public $js = [
        'js/editLine.js',
    ];
    public $depends = [
    ];
}
