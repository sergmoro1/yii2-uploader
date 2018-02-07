<?php

namespace sergmoro1\uploader\bundles;

use yii\web\AssetBundle;

class DraggableAsset extends AssetBundle
{
    public $sourcePath = '@vendor/sergmoro1/yii2-byone-uploader/assets';
    public $css = [
    ];
    public $js = [
        'js/draggable.js',
    ];
    public $depends = [
    ];
}
