<?php

namespace sergmoro1\uploader;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'sergmoro1\uploader\controllers';
    public $controllerMap = ['uploader' => 'sergmoro1\uploader\controllers\OneFile'];
}
