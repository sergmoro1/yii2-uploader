<?php

namespace sergmoro1\uploader;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'sergmoro1\uploader\controllers';
    public $controllerMap = ['uploader' => 'sergmoro1\uploader\controllers\OneFile'];
    public $sourceLanguage = 'en-US';

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
        Yii::$app->i18n->translations['sergmoro1/uploader/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => $this->sourceLanguage,
            'basePath' => '@vendor/sergmoro1/yii2-uploader/src/messages',
            'fileMap' => [
                'sergmoro1/uploader/core' => 'core.php',
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
        return Yii::t('sergmoro1/uploader/' . $category, $message, $params, $language);
    }
}
