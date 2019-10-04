<?php

namespace sergmoro1\uploader\controllers;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Ajax requests secure handler serves file uploading.
 * 
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class OneFileSecureController extends OneFileController {

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'crop', 'delete', 'update', 'swap'],
                'rules' => [
                    [
                        'actions' => ['create', 'crop', 'delete', 'update', 'swap'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                    'delete' => ['post'],
                    'update' => ['post'],
                    'swap'   => ['post'],
                ],
            ],
        ];
    }
}

