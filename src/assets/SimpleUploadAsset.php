<?php

namespace sergmoro1\uploader\assets;

use yii\web\AssetBundle;

class SimpleUploadAsset extends AssetBundle
{
    public $sourcePath = '@npm/jquery-simple-upload';
    public $css = [
    ];
    public $js = [
        'simpleUpload.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'sergmoro1\uploader\assets\UploadHandlerAsset',
    ];
}
