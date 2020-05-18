<?php

namespace sergmoro1\uploader\behaviors;

use Yii;
use yii\base\Behavior;
use yii\imagine\Image;

use sergmoro1\uploader\jobs\ResizeImageJob;
use sergmoro1\uploader\jobs\DeleteTmpImageJob;

/**
 * Image transformations behavior.
 * 
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 */
class ImageTransformationBehavior extends Behavior
{
    /**
     * Resize and save image for all sizes. Using queue if it is active.
     * 
     * @param string $path to the file
     * @param string $tmp image
     * @param string $file
     * @param array  $sizes of images and catalogs to save them
     */
    public function resizeSave($path, $tmp, $file)
    {
        $ids = [];
        $queueIsActive = isset(Yii::$app->queue);
        foreach ($this->owner->sizes as $catalog => $size) {
            // resize and save thumbnail for returning it in AJAX response and
            // others too if queue is not active
            if ($catalog == 'thumb' || !$queueIsActive) {
                // $sizes > 0 then resize or simply copy
                $image = ($size['width'] > 0 && $size['height'] > 0)
                    ? Image::resize($path . $tmp, $size['width'], $size['height'])
                    : Image::open($path . $tmp);
                $image->save($path . ($size['catalog'] ? $size['catalog'] . '/' : '') . $file);
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
        // delete tmp image
        if ($ids) {
            // after all jobs done in a queue
            Yii::$app->queue->push(new DeleteTmpImageJob([
                'ids'  => $ids,
                'file' => ($path . $tmp),
            ]));
        } else {
            // just after foreach
            unlink($path . $tmp);
        }
    }
}
