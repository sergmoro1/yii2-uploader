<?php
/**
 * @link https://github.com/sergmoro1/yii2-uploader
 * @copyright Copyright (c) 2020 Sergey Morozov
 * @license MIT
 */

namespace tests\mock;

use tests\components\OneFileKeeper;
use tests\models\Photo;

trait OneFileKeeperTrait
{
    /**
     * @return tests\components\OneFileKeeper
     */
    public function makeKeeper()
    {
        $model = new Photo(['id' => 1, 'category' => 'Street']);

        return new OneFileKeeper([
            'get_path'        => $model->getFilePath($this->subdir),
            'set_path'        => $model->setFilePath($this->subdir),
            'modelClass'      => $model->className(),
            'parent_id'       => 1,
            'subdir'          => $this->subdir,
            'sizes'           => $model->sizes,
            'minFileSize'     => 10240,
            'maxFileSize'     => 1048576,
            'limit'           => 2,
            'alreadyUploaded' => 0,
        ]);
    }
}
