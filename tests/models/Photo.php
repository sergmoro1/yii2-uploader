<?php

namespace tests\models;

use Yii;
use yii\base\Model;
use tests\fixtures\HaveFileBehavior;

/**
 * Mock class for testing yii\db\ActiveRecord.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class Photo extends Model
{
	public $id;
	public $category;
	
    public function behaviors()
    {
        return [
			[
				'class' => HaveFileBehavior::className(),
				'file_path' => '/photo/',
                'sizes' => [
                    'original' => ['width' => 1200, 'height' => 900, 'catalog' => 'original'],
                    'main'     => ['width' => 800,  'height' => 600, 'catalog' => ''],
                    'thumb'    => ['width' => 120,  'height' => 90,  'catalog' => 'thumb'],
                ],
			]
		];
    }
}
