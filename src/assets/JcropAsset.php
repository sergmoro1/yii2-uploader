<?php

namespace sergmoro1\uploader\assets;

use yii\web\AssetBundle;

class JcropAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower-asset/jcrop';
    public $css = [
        'css/Jcrop.min.css',
    ];
    public $js = [
        'js/Jcrop.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'sergmoro1\uploader\assets\CropHandlerAsset',
    ];
}
