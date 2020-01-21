<?php

namespace sergmoro1\uploader\behaviors;

use Yii;
use yii\imagine\Image;

use sergmoro1\uploader\jobs\ResizeImageJob;
use sergmoro1\uploader\jobs\DeleteTmpImageJob;

/**
 * Image transformations.
 * 
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 */
trait ImageTransformationTrait
{
    /**
     * Resize and save image for all sizes using queue.
     * 
     * @param string $path to the file
     * @param string $tmp image
     * @param string $file
     * @param array  $sizes of images and catalogs to save them
     */
    public function resizeSave($path, $tmp, $file)
    {
        $ids = [];
        foreach ($this->sizes as $catalog => $size) {
            if ($catalog == 'thumb') {
                // resize and save thumbnail for returning it in AJAX response
                Image::resize($path . $tmp, $size['width'], $size['height'])
                    ->save($path . $catalog . '/' . $file);
            } else {
                // resize and save others using queue
                $ids[] = Yii::$app->queue->push(new ResizeImageJob([
                    'path' => $path,
                    'tmp'  => $tmp,
                    'file' => $file,
                    'size' => $size,
                ]));
            }
        }
        // delete tmp image after all jobs done
        Yii::$app->queue->push(new DeleteTmpImageJob([
            'ids'  => $ids,
            'file' => ($path . $tmp),
        ]));
    }
}
