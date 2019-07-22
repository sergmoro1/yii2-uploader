<?php

namespace sergmoro1\uploader\assets;

use yii\web\AssetBundle;

class UploadHandlerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/sergmoro1/yii2-uploader/src/assets/dist';
    public $css = [
        'css/upload.css',
    ];
    public $js = [
        'js/handler.upload.js',
    ];
    public $depends = [
    ];
}
