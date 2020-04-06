<?php
/**
 * @link https://github.com/sergmoro1/yii2-uploader
 * @copyright Copyright (c) 2020 Sergey Morozov
 * @license MIT
 */

namespace tests\fixtures;

use tests\models\Photo;
use sergmoro1\uploader\components\OneFileCropper;

trait OneFileCropperTrait
{
    public $width  = 320;
    public $height = 180;

    public $scaleWidth;
    public $scaleHeight;

    /**
     * @return sergmoro1\uploader\components\OneFileCropper
     */
    public function makeCropper($name)
    {
        $model = new Photo(['id' => 1, 'category' => 'Street']);

        $scale = $model->sizes['original']['width'] / $model->sizes['main']['width'];
        $this->scaleWidth = intval($this->width * $scale);
        $scale = $model->sizes['original']['height'] / $model->sizes['main']['height'];
        $this->scaleHeight = intval($this->height * $scale);
        
        return new OneFileCropper([
            'id'    => 1,
            'path'  => $model->getAbsoluteFilePath($this->subdir),
            'name'  => $name,
            'w'     => $this->width,
            'h'     => $this->height,
            'start' => [0, 0],
            'sizes' => $model->sizes,
        ]);
    }
}
