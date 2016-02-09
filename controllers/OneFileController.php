<?php
/**
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - MIT
 * 
 * Ajax requests handler serves file uploading.
 */

namespace sergmoro1\uploader\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use sergmoro1\uploader\models\OneFile;
use sergmoro1\uploader\SimpleImage;

class OneFileController extends Controller {

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'crop', 'delete', 'save'],
                'rules' => [
                    [
                        'actions' => ['create', 'crop', 'delete', 'save'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

	/*
	 * Upload file, resize it if it's image by $model->sizes and save.
	 * Add new record to OneFile model.
	 * 
	 * POST params
	 * @var $model - model name
	 * @var $parent_id - record of parent model that file belongs to
	 * @var $subdir - subdirectory where file will be saved
	 * @var $cropAllowed - if file is a picture it can be cropped
	 */
	public function actionCreate()
	{
        if(Yii::$app->request->isAjax)
		{
			$modelName = Yii::$app->request->post('model');
			$parent_id = Yii::$app->request->post('parent_id');
			$subdir = Yii::$app->request->post('subdir');
			$cropAllowed = Yii::$app->request->post('cropAllowed'); 

			$model = $modelName::findOne($parent_id);

			if(
				isset($_FILES["fileupload"]) && 
				isset($model) && 
				($path = $model->setFilePath($subdir))
			)
			{
				$error = $_FILES["fileupload"]["error"];
				$fileNameExt = $_FILES["fileupload"]["name"];
				$fileSize = $_FILES["fileupload"]["size"];
				
				mb_internal_encoding("UTF-8");
				$point = mb_strrpos($fileNameExt, '.');
				$ext = mb_strtolower(mb_substr($fileNameExt, $point));
				$fileName = mb_substr($fileNameExt, 0, $point);
				$is_image = in_array($ext, ['.jpg', '.jpeg', '.gif', '.png']);
				$newFileName = ($is_image ? 'i' : 'd') . '_' . uniqid();

				if(move_uploaded_file($_FILES["fileupload"]["tmp_name"], $path . $newFileName . $ext))
				{
					if($is_image)
					{
						// resizing
						$image = new SimpleImage($path . $newFileName . $ext);
						$image->resizeSave($path, $newFileName . $ext, $cropAllowed, $model->sizes);
					} 

					// add new record to oneFile model
					$oneFile = new OneFile;
					$oneFile->model = $modelName;
					$oneFile->parent_id = $parent_id;
					$oneFile->original = mb_substr($fileName, 0, 128);
					$oneFile->name = $newFileName . $ext;
					$oneFile->subdir = $subdir;
					$oneFile->save();
					
					// return the image and it's id that is just added
					echo json_encode(['files' => [[
						"name" => $oneFile->original, 
						"url" => $model->getImageByName($newFileName . $ext, $subdir, 'original'), 
						"thumbnailUrl" => $model->getImageByName($newFileName . $ext, $subdir, 'thumb'), 
						"size" => $fileSize,
						'id' => $oneFile->id,
					]]]);
				} else
					// error 
					echo json_encode(['files' => [[
						"name" => $_FILES["fileupload"]["name"], 
						'error' => 'File: ' . $_FILES["fileupload"]["name"] . ' can\'t be loaded!',
					]]]);
			 } else
				echo 0;
        }
	}

	/*
	 * Upload file, resize it if it's image by $model->sizes and save.
	 * Add new record to Image model.
	 * 
	 * GET params
	 * @var $file_id - oneFile model Id
	 * @var $x, $y - left, top corner of image for cropping
	 * @var $w, $h - width, height for cropping
	 */
	public function actionCrop($file_id, $x, $y, $w, $h)
	{
        if(Yii::$app->request->isAjax)
		{
			// find file by Id
			$oneFile = OneFile::findOne($file_id);
			// load model for path to file and image sizes
			$modelName = $oneFile->model;
			$model = $modelName::findOne($oneFile->parent_id);
			
			$path = $model->getFilePath($oneFile->subdir, '', true);
			$file = $oneFile->name;
			// load, crop and save image
			$image = new SimpleImage($path . 'original/' . $file);
			$image->crop($w, $h, $x, $y);
			$image->resizeSave($path, $file, false, $model->sizes);

			// return the image and it's id that you just added
			echo json_encode(['files' => [[
				"name" => $oneFile->original, 
				"url" => $model->getImageByName($oneFile->name, $oneFile->subdir, ''), 
				"thumbnailUrl" => $model->getImageByName($oneFile->name, $oneFile->subdir, 'thumb'), 
				'id' => $oneFile->id,
			]]]);
        }
	}

	/*
	 * Delete file.
	 * 
	 * @var $file_id - file that should be deleted
	 */
	public function actionDelete()
	{
        if(Yii::$app->request->isAjax)
		{
			$file_id = Yii::$app->request->post('file_id');
			$oneFile = OneFile::findOne($file_id);
			$modelName = $oneFile->model;
			$model = $modelName::findOne($oneFile->parent_id);

			$model->deleteFile($oneFile->name, $oneFile->subdir);
			$oneFile->delete();
			
			echo $file_id;
        }
	}

	/*
	 * Save file additional info.
	 * 
	 * @var $file_id
	 * @var $defs - json array
	 */
	public function actionSave()
	{
        if(Yii::$app->request->isAjax)
		{
			$file_id = Yii::$app->request->post('file_id');
			$defs = Yii::$app->request->post('defs');
			// after find there are old values (see modules/OneFile::afterFind())
			$model = OneFile::findOne($file_id);
			// so, change them for new
			$model->vars = json_decode($defs);
			$model->save(false, ['defs']);
			
			echo $file_id;
        }
	}
}

