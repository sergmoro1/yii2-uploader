<?php

namespace sergmoro1\uploader\components;

use yii\base\BaseObject;
use yii\imagine\Image;

/**
 * Class for resizing just uploaded file.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class ResizeImageJob extends BaseObject implements \yii\queue\JobInterface
{
    /* @var string $path to the file */
    public $path;
    /* @var string $file name */
    public $file;
    /* @var array $size of image and catalog to save it */
    public $size;
    
    /**
     * Resize and save image.
     */
    public function execute($queue)
    {
        Image::resize($this->path . $this->file, $this->size['width'], $this->size['height'])
            ->save($this->path . ($this->size['catalog'] ? $this->size['catalog'] . '/' : '') . $this->file);
    }
}
