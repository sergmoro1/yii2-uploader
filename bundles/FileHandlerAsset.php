<?php

namespace sergmoro1\uploader\bundles;

use yii\web\AssetBundle;

class FileHandlerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/sergmoro1/yii2-byone-uploader/assets';
    public $css = [
        'css/fileupload.css',
    ];
    public $js = [
        'js/handler.fileupload.js',
    ];
    public $depends = [
    ];
}
