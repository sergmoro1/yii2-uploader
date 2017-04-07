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
use sergmoro1\uploader\models\OneFile;
use sergmoro1\uploader\SimpleImage;

class OneFileController extends Controller {
	const DELIMITER = '-';

	/*
	 * Upload file, resize it if it's image by $model->sizes and save.
	 * Add new record to OneFile model.
	 * 
	 * POST params - $p
	 * @var $model - model name
	 * @var $parent_id - record of parent model that file belongs to
	 * @var $subdir - subdirectory where file will be saved
	 * @var $cropAllowed - if file is a picture it can be cropped
	 */
	public function actionCreate()
	{
        if(Yii::$app->request->isAjax)
		{
			$p = Yii::$app->request->post();
			$modelName = $p['model'];
			$model = $modelName::findOne($p['parent_id']);

			if(
				isset($_FILES['fileupload']) && 
				isset($model) && 
				($path = $model->setFilePath($p['subdir']))
			)
			{
				$fu = $_FILES['fileupload'];
				$is_image = strtolower(substr($fu['type'], 0, 5)) == 'image';

				mb_internal_encoding('UTF-8');
				$point = mb_strrpos($fu['name'], '.');
				$ext = mb_strtolower(mb_substr($fu['name'], $point));
				$original = mb_substr($fu['name'], 0, $point);
				$newFile = ($is_image ? 'i' : 'd') . '_' . uniqid();

				if(move_uploaded_file($fu['tmp_name'], $path . $newFile))
				{
					if($is_image)
					{
						// resizing
						$image = new SimpleImage($path . $newFile);
						$image->resizeSave($path, $newFile, $p['cropAllowed'], $model->sizes);
					} 

					// add new record to oneFile model
					$oneFile = new OneFile;
					$oneFile->model = $modelName;
					$oneFile->parent_id = $p['parent_id'];
					$oneFile->original = mb_substr($original, 0, 128);
					$oneFile->name = $newFile;
					$oneFile->subdir = $p['subdir'];
					$oneFile->save();
					
					// return the image and it's id that is just added
					echo json_encode(['files' => [[
						'name' => $oneFile->original, 
						'url' => $model->getImageByName($newFile, $p['subdir'], 'original'), 
						'thumbnailUrl' => $model->getImageByName($newFile, $p['subdir'], 'thumb'), 
						'size' => $fu['size'],
						'id' => $oneFile->id,
					]]]);
				} else
					// error 
					echo json_encode(['files' => [[
						'name' => $fu['name'], 
						'error' => 'File: ' . $fu['name'] . ' can\'t be loaded!',
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
			$p = Yii::$app->request->post();
			// after find there are old values (see modules/OneFile::afterFind())
			$model = OneFile::findOne($p['file_id']);
			// so, change them for new defs (see modules/OneFile::beforeSave())
			$model->vars = json_decode($p['defs']);
			$model->save(false, ['defs']);
			
			echo $p['file_id'];
        }
	}

	/*
	 * Swap rows.
	 * 
	 * @var $ids - files ids
	 */
	public function actionSwap()
	{
        if(Yii::$app->request->isAjax)
		{
			$ids = json_decode(Yii::$app->request->post('ids'));
			$time = time(); $j = 0;
			for($i = 0; $i < count($ids); $i++) {
				$model = OneFile::findOne($ids[$i]);
				$model->created_at = $time + $i;
				if($model->save()) $j++;
			}
			echo $j;
        }
	}
}

