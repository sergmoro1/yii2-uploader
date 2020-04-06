<?php

namespace tests\mock\data;

use tests\models\Photo;

return [
    [
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
    ],
    [
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
    ]
];
