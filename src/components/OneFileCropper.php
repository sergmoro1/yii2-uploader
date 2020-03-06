<?php

namespace sergmoro1\uploader\components;

use yii\base\Component;
use yii\imagine\Image;
use sergmoro1\uploader\behaviors\ImageTransformationBehavior;

/**
 * Class for cropping image uploaded before and resize it for all defined sizes.
 * Original size should be defined.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class OneFileCropper extends Component {
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
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            ['class' => ImageTransformationBehavior::className()],
        ];
    }

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
        // create tmp file name
        $tmp = 'tmp_' . $this->name;
        // crop original and save result to tmp
        Image::crop($this->path . 'original/' . $this->name, $this->w * $scale, $this->h * $scale, $this->start)
            ->save($this->path . $tmp);
        // resize others
        $this->resizeSave($this->path, $tmp, $this->name);

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
