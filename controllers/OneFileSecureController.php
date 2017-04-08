<?php
/**
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - MIT
 * 
 * Ajax requests handler serves file uploading.
 */

namespace sergmoro1\uploader\controllers;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class OneFileSecureController extends OneFileController {

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'crop', 'delete', 'save'],
                'rules' => [
                    [
                        'actions' => ['create', 'crop', 'delete', 'save', 'swap'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
}

