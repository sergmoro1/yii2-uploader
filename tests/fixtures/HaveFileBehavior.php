<?php
/**
 * @link https://github.com/sergmoro1/yii2-uploader
 * @copyright Copyright (c) 2020 Sergey Morozov
 * @license MIT
 */

namespace tests\fixtures;

use tests\models\OneFile;

class HaveFileBehavior extends \sergmoro1\uploader\behaviors\HaveFileBehavior {
    
    /**
     * @inheritdoc
     */
    public function getFiles()
    {
        $models = [];
        $entities = require(__DIR__ . '/data/onefile.php');
        foreach ($entities as $entity)
            $models[] = new OneFile($entity);
        return $models;
    }
}
