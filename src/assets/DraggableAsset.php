<?php

namespace sergmoro1\uploader\assets;

use yii\web\AssetBundle;

class DraggableAsset extends AssetBundle
{
    public $sourcePath = '@vendor/sergmoro1/yii2-uploader/src/assets/src';
    public $css = [
        'css/draggable.css',
    ];
    public $js = [
        'js/draggable.js',
    ];
    public $depends = [
    ];
}
