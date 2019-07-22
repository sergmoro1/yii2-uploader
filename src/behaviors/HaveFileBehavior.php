<?php

namespace sergmoro1\uploader\behaviors;

use Yii;
use yii\base\Behavior;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\base\NotSupportedException;
use sergmoro1\uploader\models\OneFile;

/**
 * HaveFileBehavior class for model that can have images or other type of files.
 * 
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 */
class HaveFileBehavior extends Behavior
{
    // Min size in pixels for cropping
    const MIN_SIZE = 100;
    // Default aspect ratio for cropping - 16/9
    const ASPECT_RATIO = 1.7778;
    // Popup window border
    const POPUP_BORDER = 35;

    /** @var string main directory where files will be saved, for ex. /files/user */
    public $file_path;
    
    // full path will be $base_path . $file_path
    private $base_path;
    // name of model's files collection
    private $files = 'files';
    // current file index
    private $current = null;
    // min image size
    private $_min;
    // aspect ratio
    private $_aspectRatio;
    
    public function init()
    {
        parent::init();
        // change base path to frontend
        $this->base_path = Url::base() ? Url::base() : Yii::$app->request->hostInfo;
        if(isset(Yii::$app->params['before_web'])) 
            $this->base_path = str_replace(Yii::$app->params['before_web'], 'frontend', $this->base_path);
    }
    
    /**
     * Set name of files variable
     */
    public function setFiles($files) { $this->files = $files; }

    /**
     * Add directory separator
     * @param string $subdir
     * @return string $subdir with final slash
     */
    private function add($subdir) { return $subdir ? $subdir . '/' : ''; }
    
    /**
     * Get full path to a file.
     * 
     * @param string $subdir subdirectory in a parent directory - files/post/38, where 38 is a $subdir and a post ID
     * @param string $catalog files/post/38/thumb or files/post/38/main, thumb, main are catalogs
     * @param boolean $webroot
     * @return string full file path
    */
    public function getFilePath($subdir, $catalog = '', $webroot = false)
    {
        if(!($app_path = Yii::getAlias('@frontend')))
            $app_path = Yii::getAlias('@app');
        $path = ($webroot
            ? $app_path . '/web'
            : $this->base_path
        ) . $this->file_path . $this->add($subdir);
        return is_dir($app_path . '/web' . $this->file_path . $this->add($subdir) . $catalog) 
            ? $path . $this->add($catalog) 
            : $path;
    }

    /**
     * If path exists return path else make it. 
     * 
     * @param $subdir - subdirectory
     * @return string | false
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
     * Get file from collection of model files by index.
     * 
     * @param integer $i - index in owner->$files
     * @return \sergmoro1\uploader\models\OneFile | null
    */
    public function getFile($i = 0)
    {
        $files = $this->files;
        $files = $this->owner->$files;
        if (isset($files[$i])) {
            $this->current = $i;
            return $files[$i];
        } else {
            $this->current = 0;
            return null;
        }
    }

    /**
     * Get files count in model's collection.
     * 
     * @return integer
    */
    public function getFileCount()
    {
        $files = $this->files;
        return count($this->owner->$files);
    }

    /**
     * Get document.
     * 
     * @param integer $i - index in owner->$files
     * @return string full file name
    */
    public function getDoc($i = 0)
    {
        if ($file = $this->getFile($i))
        {
            return $this->getFilePath($file->subdir, '') . $file->name;
        } else
            return '';
    }

    /**
     * Is type an image.
     * 
     * @param string media $type
     * @return boolean
    */
    public function isImage($type) { return substr($type, 0, 5) == 'image'; }

    /**
     * Get image by index.
     * 
     * @param string $catalog - 'main', 'thumb' and so
     * @param integer $i - index in owner->$files
     * @return string full file name
    */
    public function getImage($catalog = '', $i = 0)
    {
        if ($file = $this->getFile($i))
        {
            if ($this->isImage($file->type))
                return $this->getFilePath($file->subdir, $catalog) . $file->name;
            else
                return false;
        } else
            return false;
    }

    /**
     * Get next image.
     * 
     * @param string $catalog - 'main', 'thumb' and so
     * @return string full file name | false
    */
    public function getNextImage($catalog = '')
    {
        if ($this->current + 1 < $this->fileCount)
        {
            $this->current++;
            return $this->getImage($catalog, $this->current);
        } else {
            $this->current = 0;
            return false;
        }
    }

    /**
     * Add full path to a file name
     * 
     * @param string $subdir
     * @param string $catalog in subdir 
     * @param string file $name 
     * @return full file name
    */
    public function getFileByName($subdir, $catalog = '', $name)
    {
        return $this->getFilePath($subdir, $catalog) . $name;
    }

    /**
     * Find file by original file name in a $files collection
     * 
     * @param string $original file name 
     * @param string $catalog 
     * @return full file name
    */
    public function getImageByOriginal($catalog = '', $original)
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
     * Each file can has some additional vars, description by default.
     * 
     * @param string  var $name
     * @param integer $i index in a $files set
     * @return mixed
    */
    public function getVar($name = 'description', $default = '')
    {
        $file = $this->getFile($this->current);
        return $file && isset($file->vars->$name) ? $file->vars->$name : $default;
    }

    /**
     * @return string getVar() alias
     */
    public function getFileDescription() { return $this->getVar(); }

    /**
     * Delete file and all resized images in catalogs if exist.
     * 
     * @param string $subdir
     * @param string $file name
     */
    public function deleteFile($subdir, $file)
    {
        $path = $this->getFilePath($subdir, '', true);
        foreach($this->owner->sizes as $size) {
            if(file_exists($path . $size['catalog'] . '/' . $file))
                unlink($path . $size['catalog'] . '/' . $file);
        }
    }

    /**
     * Get minimal size for cropping rectangle.
     * 
     * @return integer
     */
    public function getMin()
    {
        if ($this->_min)
            return $this->_min;
        $ow = isset($this->owner->sizes['original']['width']) ? $this->owner->sizes['original']['width'] : 0;
        $oh = isset($this->owner->sizes['original']['height']) ? $this->owner->sizes['original']['height'] : 0;
        $mw = isset($this->owner->sizes['main']['width']) ? $this->owner->sizes['main']['width'] : 0;
        $mh = isset($this->owner->sizes['main']['height']) ? $this->owner->sizes['main']['height'] : 0;
        if ($ow > $oh) {
            $scale = ($mw > 0) ? $ow / $mw : self::MIN_SIZE;
            $this->_min = min(floor($oh / $scale), $mh);
        } else {
            $scale = ($mh > 0) ? $oh / $mh : self::MIN_SIZE;
            $this->_min = min(floor($ow / $scale), $mw);
        }
        return $this->_min;
    }
    
    /**
     * Get width for popup window using for cropping.
     * 
     * @return integer
     */
    public function getPopUpWidth()
    {
        return (isset($this->owner->sizes['main']['width']) 
            ? $this->owner->sizes['main']['width']
            : self::MIN_SIZE
        ) + self::POPUP_BORDER;
    }

    /**
     * Get image aspect ratio for cropping.
     * 
     * @return float
     */
    public function getAspectRatio()
    {
        if ($this->_aspectRatio)
            return $this->_aspectRatio;
        $this->_aspectRatio = isset($this->owner->sizes['main']['width']) && 
            isset($this->owner->sizes['main']['height']) && $this->owner->sizes['main']['height'] > 0 
            ? $this->owner->sizes['main']['width'] / $this->owner->sizes['main']['height']
            : self::ASPECT_RATIO;
        return $this->_aspectRatio;
    }

    /**
     * Get slider array.
     * 
     * $param string $wrapper tag
     * $param array $wrapperOptions HTML options for $wrapper tag
     * $param array $imageOptions HTML options for <img> tag
     * $param boolean $need_caption
     * @return array $items for slider
     * @see usage \sergmoro1\yii2-blog-tools\src\views\post\view.php
     */
    public function prepareSlider($wrapper = 'div', $wrapperOptions = [], $imgOptions = [], $need_caption = false)
    {
        $items = [];
        $image = $this->getImage();
        while ($image) {
            $item = [];
            $item['content'] = Html::tag($wrapper, Html::img($image, $imgOptions), $wrapperOptions);
            if($need_caption)
                $item['caption'] = $this->getFileDescription();
            $items[] = $item;
            $image = $this->getNextImage();
        }
        return $items;
    }
}
