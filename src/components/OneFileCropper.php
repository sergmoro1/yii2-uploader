<?php

namespace sergmoro1\uploader\components;

use yii\base\BaseObject;
use yii\imagine\Image;

/**
 * Class for cropping image uploaded before and resize it for all defined sizes.
 * Original size should be defined.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class OneFileCropper extends BaseObject {
    /** @var integer sergmoro1\uploader\models\OneFile model ID */
    public $id;

    /** @var string $path to file */
    public $path;

    /** @var string $name of file */
    public $name;

    /** @var integer cropping width */
    public $w;

    /** @var integer cropping height */
    public $h;

    /** @var array top, left point of cropping rectangle */
    public $start;

    /** @var array */
    public $sizes;

    /**
     * Crop original image according to width, height and start point, risize and save it.
     * 
     * @return array
     */
    public function proceed()
    {
        // calculate scale from main to original sizes
        $original = Image::getImagine()->open($this->path . 'original/' . $this->name);
        $originalSize = $original->getSize();
        $main = Image::getImagine()->open($this->path . $this->name);
        $mainSize = $main->getSize();
        $scale = $originalSize->getWidth() / $mainSize->getWidth();
        // scale cropping parameters
        $this->start[0] = $this->start[0] * $scale;
        $this->start[1] = $this->start[1] * $scale;
        // crop original
        Image::crop($this->path . 'original/' . $this->name, $this->w * $scale, $this->h * $scale, $this->start)->save();
        // resize others
        foreach ($this->sizes as $size) {
            Image::resize($this->path . 'original/' . $this->name, $size['width'], $size['height'])
                ->save($this->path . ($size['catalog'] ? $size['catalog'] . '/' : '') . $this->name);
        }

        return [
            'success' => true,
            'file'    => [
                'id'      => $this->id,
                'path'    => $this->path,
                'catalog' => 'thumb/',
                'name'    => $this->name,
            ],
        ];
    }
}
