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
use yii\helpers\Html;

class FilePath extends Behavior
{
    // Min size in pixels for cropping
    const MIN_SIZE = 270;
    
    // Full path - $base_path . $file_path
    public $base_path; // (for advanced) by default - Yii::alias('@frontend').'/web'
    public $file_path; // example - @frontend/files/user/
    
    // name of files collection
    public $files = 'files';

    // current file index
    public $current = null;
    
    public function init()
    {
        parent::init();
        // change base path to frontend
        $this->base_path = Url::base() ? Url::base() : Yii::$app->request->hostInfo;
        if(isset(\Yii::$app->params['before_web'])) 
            $this->base_path = str_replace(\Yii::$app->params['before_web'], 'frontend', $this->base_path); 
    }
    
    /**
     * Setter & getter for files
     */
    public function setFiles($files) { $this->files = $files; }
    public function getFiles() { return $this->files; }

    /**
     * Add directory separator
     */
    private function add($subdir) {    return $subdir ? $subdir . '/' : ''; }
    
    /* 
     * Get full path to file.
     * 
     * @param $subdir - subdirectory
     * It depends on a model.
     * It may be a User ID, but in this case user must be exists.
     * @return full file path
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

    /**
     * If $path exists return $path else make it. 
     * 
     * @param $subdir - subdirectory
     * @return full path if is a success or false
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

    /**
     * Get image of file. Name of image files begins with 'i', other with 'd'.
     * You have to make images for all file types that you want to upload with names $ext.png in /files/site/
     * For ex. xls: '/files/site/xls.png'
     * 
     * @param $catalog - for ex.: 'main', 'thumb' and so
     * @param $i - index in owner->$files
     * @return full file name
    */
    public function getImage($catalog = '', $i = 0)
    {
        $files = $this->files;
        $files = $this->owner->$files;
        if(isset($files[$i])) {
            $file = $files[$i];
            if($file->name && substr($file->name, 0, 1) == 'i')
                return $this->getFilePath($file->subdir, $catalog) . $file->name;
            elseif($file->original)
                return $this->base_path . '/files/' . $file->original;
            else
                return $this->base_path . '/files/site/' . 
                    substr($file->name, strrpos($file->name, '.') + 1) . '.png';
        } else
            return false;
    }

    /**
     * Add full path to a file name
     * 
     * @param file $name 
     * @param $subdir to main directory/$catalog
     * @param $catalog in a main dirictiry for a model 
     * @return full file name
    */
    public function getImageByName($name, $subdir, $catalog = '')
    {
        if(substr($name, 0, 1) == 'i')
            return $this->getFilePath($subdir, $catalog) . $name;
        else
            return $this->base_path . '/files/site/' . 
                substr($name, strrpos($name, '.') + 1) . '.png';
    }

    /**
     * Not used now
     */
    public function getLink($catalog = '', $i = 0)
    {
        $files = $this->files;
        $file = $this->owner->$files[$i];
        return $this->getFilePath($file->subdir, $catalog) . $file->name;
    }

    /**
     * Find file by original file name in a $files collection
     * 
     * @param $original file name 
     * @param $catalog in a main dirictiry for a model 
     * @return full file name
    */
    public function getImageByOriginal($original, $catalog = '')
    {
        $files = $this->files;
        foreach($this->owner->$files as $i => $file)
        {
            if(!(stripos($file->original, $original) === false)) {
                $this->current = $i;
                return $this->getImage($catalog, $i, $files);
            }
        }
        return false;
    }
    
    /**
     * Each file can has some additional attributes - description by default.
     * 
     * @param $i index in a $files collection 
     * @return file description
    */
    public function getDescription($i = 0)
    {
        $files = $this->files;
        $files = $this->owner->$files;
        if(!isset($files[$i]))
           return "";
        $file = $files[$i];
        return isset($file->vars->description) ? $file->vars->description : "";
    }

    /**
     * Each file can has some additional attributes - description by default.
     * Current file was founded by getImageByOriginal().
     * 
     * @return file description
    */
    public function getCurrentDescription()
    {
        $files = $this->files;
        if(!($this->current === null)) {
            $files = $this->owner->$files;
            $file = $files[$this->current];
            return isset($file->vars->description) ? $file->vars->description : "";
        }
        return "";
    }

    /* 
     * Delete $file and it's thumbnail if exist.
     * 
     * @param $file - file name,
     * @param $subdir - subdirectory.
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
    
   	public function prepareSlider($caption = false)
	{
        $files = $this->files;
		$items = [];
		foreach($this->owner->$files as $i => $file)
		{
			$item['content'] = '<p class="text-center">'. Html::img($this->getImage('', $i), ['style' => 'width: 100%;']) .'</p>';
			if($caption)
				$item['caption'] = $this->getDescription($i);
			$items[] = $item;
		}
		return $items;
	}
}
