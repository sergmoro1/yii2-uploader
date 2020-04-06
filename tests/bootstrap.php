<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

//require_once(__DIR__ . '/../vendor/autoload.php');
//require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(dirname(__DIR__, 3) . '/autoload.php');
require(dirname(__DIR__, 3) . '/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', __DIR__);

$app = new \yii\console\Application([
    'id' => 'test-app',
    'basePath' => __DIR__,
    'vendorPath' => __DIR__ . '/../vendor',
    'aliases' => [
        '@absolute' => __DIR__,
        '@uploader' => '/data/files',
    ],
    'components' => [
        'i18n' => [
			'translations' => [
				'sergmoro1/uploader/*' => [
					'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@vendor/sergmoro1/yii2-uploader/src/messages',
                    'fileMap' => [
                        'sergmoro1/uploader/core' => 'core.php',
                    ],
				],
			],
		],
    ],
]);
