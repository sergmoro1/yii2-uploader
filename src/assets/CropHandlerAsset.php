<?php

namespace sergmoro1\uploader\assets;

use yii\web\AssetBundle;

class CropHandlerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/sergmoro1/yii2-uploader/src/assets/src';
    public $css = [
    ];
    public $js = [
        'js/handler.crop.js',
    ];
    public $depends = [
    ];
}
