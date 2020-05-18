<?php

namespace sergmoro1\uploader\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

use sergmoro1\uploader\Module;
use sergmoro1\uploader\models\OneFile;
use sergmoro1\uploader\components\OneFileKeeper;
use sergmoro1\uploader\components\OneFileCropper;

/**
 * Ajax requests handler serves file uploading.
 * 
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class OneFileController extends Controller {
    /**
     * Upload file and resize it if it is an image.
     * Add a new record to the \sergmoro1\uploader\models\OneFile.
     * 
     * POST params:
     * @param string  $model class
     * @param integer $parent_id of a model that file belongs to
     * @param string  $subdir subdirectory where file will be saved
     * @param string  $allowedTypesReg regexp to check file type
     * @param integer $minFileSize
     * @param integer $maxFileSize
     * @param integer $limit amount of uploaded files
     * @return string JSON array with results of creating
     * @throws ForbiddenHttpException
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isAjax)
        {
            $post = Yii::$app->request->post();
            $modelClass = $post['model'];
            $model = $modelClass::findOne($post['parent_id']);

            $oneFileKeeper = new OneFileKeeper([
                'get_path'        => $model->getFilePath($post['subdir']),
                'set_path'        => $model->setFilePath($post['subdir']),
                'modelClass'      => $modelClass,
                'parent_id'       => $post['parent_id'],
                'subdir'          => $post['subdir'],
                'sizes'           => $model->sizes,
                'check_wh'        => $model->check_wh,
                'allowedTypesReg' => $post['allowedTypesReg'],
                'minFileSize'     => $post['minFileSize'],
                'maxFileSize'     => $post['maxFileSize'],
                'limit'           => $post['limit'], 
                'alreadyUploaded' => $model->getFileCount(),
            ]);
            return $this->asJson($oneFileKeeper->proceed('fileinput'));
        } else
            throw new ForbiddenHttpException();
    }

    /**
     * Load file, uploaded before and crop it.
     * 
     * @param integer $id of \sergmoro1\uploader\models\OneFile model
     * @param integer $x left
     * @param integer $y top corner of image for cropping
     * @param integer $w width
     * @param integer $h height for cropping
     * @return string JSON array with results of cropping
     * @throws ForbiddenHttpException
     */
    public function actionCrop($id, $x, $y, $w, $h)
    {
        if (Yii::$app->request->isAjax)
        {
            // find file by Id
            $oneFile = OneFile::findOne($id);
            // load model for path to file and image sizes
            $modelClass = $oneFile->model;
            $model = $modelClass::findOne($oneFile->parent_id);
            
            $oneFileCropper = new OneFileCropper([
                'id'    => $id,
                'path'  => $model->getAbsoluteFilePath($oneFile->subdir),
                'name'  => $oneFile->name,
                'w'     => $w,
                'h'     => $h,
                'start' => [$x, $y],
                'sizes' => $model->sizes,
            ]);

            return $this->asJson($oneFileCropper->proceed());
        } else
            throw new ForbiddenHttpException();
    }

    /**
     * Delete file. If it an image then delete all sizes.
     * Also delete information about file from \sergmoro1\uploader\models\OneFile.
     * 
     * POST params:
     * @param integer $file_id that should be deleted
     * @return string JSON array with results of deleting
     * @throws ForbiddenHttpException
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax)
        {
            $file_id    = Yii::$app->request->post('file_id');
            $oneFile    = OneFile::findOne($file_id);
            $modelClass = $oneFile->model;
            $model      = $modelClass::findOne($oneFile->parent_id);
            // delete file and all of it sizes
            $model->deleteFile($oneFile);
            // delete information about file
            $result = [
                'success'  => $oneFile->delete(),
                'file_id' => $file_id
            ];
            return $this->asJson($result);
        } else
            throw new ForbiddenHttpException();
    }

    /**
     * Update file additional info.
     * 
     * POST params:
     * @param integer $file_id that should be updated
     * @param string $defs JSON array
     * @return string JSON array with results of updating
     * @throws ForbiddenHttpException
     * @see \sergmoro1\uploader\models\OneFile::afterFind, ::beforeSave 
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isAjax)
        {
            $post = Yii::$app->request->post();
            $file_id = $post['file_id'];
            if ($model = OneFile::findOne($file_id))
            {
                // update vars
                $model->vars = json_decode($post['defs']);
                // encode vars to defs and save it
                $result = [
                    'sucess'  => $model->save(false, ['defs']),
                    'file_id' => $file_id,
                ];
                return $this->asJson($result);
            } else
                throw new NotFoundHttpException();
        } else
            throw new ForbiddenHttpException();
    }

    /**
     * Swap rows with file information.
     * 
     * POST params:
     * @param integer $a
     * @param integer $b files ids that should be swapped with each other
     */
    public function actionSwap()
    {
        if (Yii::$app->request->isAjax)
        {
            $post = Yii::$app->request->post();
            if (($model_b = OneFile::findOne($post['b'])) &&
                ($model_a = OneFile::findOne($post['a'])))
            {
                // swap
                $temp = $model_a->created_at;
                $model_a->created_at = $model_b->created_at;
                $model_b->created_at = $temp;
                return $this->asJson([
                    'success' => $model_a->save(false, ['created_at']) && $model_b->save(false, ['created_at'])
                ]);
            } else
                throw new NotFoundHttpException();
        } else
            throw new ForbiddenHttpException();
    }
}

