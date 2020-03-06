<?php

namespace tests\mock;

use tests\models\OneFile;
use tests\models\Photo;

/**
 * Class for testing sergmoro1\uploader\behaviors\HaveFileBehavior
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class HaveFileBehavior extends \sergmoro1\uploader\behaviors\HaveFileBehavior {
    
    /**
     * @inheritdoc
     */
    public function getFiles()
    {
        $models = [];
        $models[] = new OneFile([
            'id' => 1,
            'model' => Photo::className(),
            'parent_id' => 1,
            'name' => '123.jpg',
            'original' =>  'image1.jpg',
            'subdir' => '',
            'type' => 'image/jpg',
            'size' => '1024',
            'defs' => '{"description": "park"}',
            'created_at' => time(), 
            'updated_at' => time(),
        ]);
        $models[] = new OneFile([
            'id' => 2,
            'model' => Photo::className(),
            'parent_id' => 1,
            'name' => '234.docx',
            'original' =>  'test.docx',
            'subdir' => '',
            'type' => 'text/plain',
            'size' => '2048',
            'defs' => '{"description": "document"}',
            'created_at' => time(), 
            'updated_at' => time(),
        ]);
        return $models;
    }
}
