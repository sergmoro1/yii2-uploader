<?php

namespace sergmoro1\uploader;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'sergmoro1\uploader\controllers';
    public $controllerMap = ['uploader' => 'sergmoro1\uploader\controllers\OneFile'];

    public function init()
    {
        parent::init();

        $this->registerTranslations();
    }

    /**
     * Register translate messages for module
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['sergmoro1/uploder/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'ru-RU', //$this->sourceLanguage,
            'basePath' => '@vendor/sergmoro1/yii2-uploder/src/messages',
            'fileMap' => [
                'sergmoro1/uploder/core' => 'core.php',
            ],
        ];
    }

    /**
     * Translate shortcut
     *
     * @param $category
     * @param $message
     * @param array $params
     * @param null $language
     *
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('sergmoro1/uploder/' . $category, $message, $params, $language);
    }
}
