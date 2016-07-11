<?php
/**
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - MIT
 * 
 * This is the Behavior class for model that can have images or other type of files.
 * You should define behavior in ActiveRecord class.
 * 
 * public function behaviors() {
 *  return [
 *   'FilePath' => [
 *    'class' => FilePath::className(),
 *    'file_path' => '/files/user/',
 *   ],
 *  ];
 * }
 * 
 * All files will be loaded to $file_path . $subdir.
 * If files are images they can be resized & cropped.
 */
namespace sergmoro1\uploader;

use Yii;
use yii\base\Behavior;
use yii\base\NotSupportedException;
use yii\helpers\Url;

class FilePath extends Behavior
{
	// Min size in pixels for cropping
	const MIN_SIZE = 270;
	
	// Full path - $base_path . $file_path
	public $base_path; // (for advanced) by default - Yii::alias('@frontend').'/web'
	public $file_path; // example - @frontend/files/user/

	public function init()
	{
		parent::init();
		// change base path to frontend
		$this->base_path = Url::base() ? Url::base() : Yii::$app->request->hostInfo;
		if(isset(\Yii::$app->params['before_web'])) 
			$this->base_path = str_replace(\Yii::$app->params['before_web'], 'frontend', $this->base_path); 
	}

	private function add($subdir)
	{
		return $subdir ? $subdir . '/' : '';
	}
	
	/* 
	 * Get full path to file.
	 * 
	 * @var $subdir - subdirectory
	 * It depends on a model.
	 * It may be a User ID, but in this case user must be exists. 
	*/
	public function getFilePath($subdir, $catalog = '', $webroot = false)
	{
		if(!($app_path = Yii::getAlias('@frontend')))
			$app = Yii::getAlias('@app');
		$path = ($webroot
			? $app_path . '/web'
			: $this->base_path
		) . $this->file_path . $this->add($subdir);
		return is_dir($app_path . '/web' . $this->file_path . $this->add($subdir) . $catalog) 
			? $path . $this->add($catalog) 
			: $path;
	}

	/* 
	 * If $path exists return $path else make it. 
	 * 
	 * @var $subdir - subdirectory 
	 */
	public function setFilePath($subdir)
	{
		$path = $this->getFilePath($subdir, '', true);
		if(is_dir($path))
			return $path;
		elseif(mkdir($path, 0777)) {
			foreach($this->owner->sizes as $size) {
				if($size['catalog']) {
					if(mkdir($path . $size['catalog'], 0777))
						continue;
					else
						return false;
				} else
					continue;
			}
			return $path;
		} else
			return false;
	}

	/* 
	 * Get image of file. Name of image files begins with 'i', other with 'd'.
	 * You have to make images for all file types that you want to upload with names $ext.png in /files/site/
	 * For ex. xls: '/files/site/xls.png'
	 * 
	 * @var $catalog - for ex.: 'main', 'thumb' and so
	 * @var $i - index in owner->files
	*/
	public function getImage($catalog = '', $i = 0, $files = 'files')
	{
		$files = $this->owner->$files;
		$file = $files[$i];
		if($file->name && substr($file->name, 0, 1) == 'i')
			return $this->getFilePath($file->subdir, $catalog) . $file->name;
		elseif($file->original)
			return $this->base_path . '/files/' . $file->original;
		else
			return $this->base_path . '/files/site/' . 
				substr($file->name, strrpos($file->name, '.') + 1) . '.png';
	}

	public function getImageByName($name, $subdir, $catalog = '')
	{
		if(substr($name, 0, 1) == 'i')
			return $this->getFilePath($subdir, $catalog) . $name;
		else
			return $this->base_path . '/files/site/' . 
				substr($name, strrpos($name, '.') + 1) . '.png';
	}

	public function getLink($catalog = '', $i = 0)
	{
		$file = $this->owner->files[$i];
		return $this->getFilePath($file->subdir, $catalog) . $file->name;
	}

	public function getImageByOriginal($original, $catalog = '', $files = 'files')
	{
		foreach($this->owner->$files as $i => $file)
		{
			if(!(stripos($file->original, $original) === false))
				return $this->getImage($catalog, $i, $files);
		}
		return false;
	}
	
	/* 
	 * Delete $file and it's thumbnail if exist.
	 * 
	 * @var $file - file name,
	 * @var $subdir - subdirectory.
	*/
	public function deleteFile($file, $subdir)
	{
		$path = $this->getFilePath($subdir, '', true);
		foreach($this->owner->sizes as $size) {
			if(file_exists($path . $size['catalog'] . '/' . $file))
				unlink($path . $size['catalog'] . '/' . $file);
		}
	}
	
	public function getMin()
	{
		$mw = isset($this->owner->sizes['main']['width']) ? $this->owner->sizes['main']['width'] : 0;
		$mh = isset($this->owner->sizes['main']['height']) ? $this->owner->sizes['main']['height'] : 0;
		return ($mw == 0 || $mh == 0)
			? self::MIN_SIZE
			: ($mw > $mh ? $mh : $mw);
	}
	
	public function getAspectRatio()
	{
		return isset($this->owner->sizes['main']['height']) && $this->owner->sizes['main']['height'] > 0 
			? $this->owner->sizes['main']['width'] / $this->owner->sizes['main']['height']
			: 16 / 9;
	}
}
