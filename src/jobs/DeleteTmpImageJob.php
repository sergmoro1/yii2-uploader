<?php

namespace sergmoro1\uploader\jobs;

use Yii;
use yii\base\BaseObject;

/**
 * Class for deleting temporary image file.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class DeleteTmpImageJob extends BaseObject implements \yii\queue\JobInterface
{
    /* @var array jobs $ids */
    public $ids;
    /* @var string $file full path and file name */
    public $file;

    /**
     * Delete temporary image after all jobs complete.
     */
    public function execute($queue)
    {
        $all_done = false;
        while (!$all_done) {
            $done = true;
            foreach($this->ids as $id)
                $done = $done && Yii::$app->queue->isDone($id);
            $all_done = $done;
        }
        unlink($this->file);
    }
}
