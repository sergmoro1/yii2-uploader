<?php

namespace sergmoro1\uploader\jobs;

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
    /* @var string $tmp file name */
    public $tmp;
    /* @var string $file name */
    public $file;
    /* @var array $size of image and catalog to save it */
    public $size;

    /**
     * Resize and save image if sizes > 0 or simply copy.
     */
    public function execute($queue)
    {
        if ($this->size['width'] > 0 && $this->size['height'] > 0)
            Image::resize($this->path . $this->tmp, $this->size['width'], $this->size['height'])
                ->save($this->path . ($this->size['catalog'] ? $this->size['catalog'] . '/' : '') . $this->file);
        else 
            copy($this->path . $this->tmp,
                $this->path . ($this->size['catalog'] ? $this->size['catalog'] . '/' : '') . $this->file);
    }
}
