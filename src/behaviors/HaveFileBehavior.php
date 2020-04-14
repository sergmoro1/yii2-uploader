<?php

namespace sergmoro1\uploader\behaviors;

use Yii;
use yii\base\Behavior;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\FileHelper;
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

    // full path will be $base_path . $file_path
    private $base_path;
    // current file index
    private $current = null;
    // min image size
    private $_min;
    // aspect ratio
    private $_aspectRatio;

    /** 
     * @var string $absoluteAlias link name
     * @see $uploaderAlias 
     */
    public $absoluteAlias = '@absolute';
    /**
     * @var string $uploaderAlias link name
     * Real name of alias and it's value should be defined in config file, for example in common/config/main-local.php
     * 
     * ```php
     * return [
     *     'aliases' => [
     *         '@absolute' => '/home/me/www/site',
     *         '@uploader' => '/frontend/web/files',
     *     ],
     * ```
     */
    public $uploaderAlias = '@uploader';
    /**
     * @var string $file_path subdirectory where files will be saved, 
     * for example /user/
     */
    public $file_path;
    /** 
     * @var array variants of image sizes
     * where if width & height = 0, then image saved as is
     * 
     * ```php
     * [
     *   'original'  => ['width' => 2400, 'height' => 1600, 'catalog' => 'original'],
     *   'main'      => ['width' => 600,  'height' => 400,  'catalog' => ''],
     *   'thumb'     => ['width' => 120,  'height' => 80,   'catalog' => 'thumb'],
     * ]
     * ```
     * 
     * All files saved in a folder: Yii::getAlias($uploaderAlias) . $file_path . ($model->id || \sergmoro1\uploader\Uploader::subdir)
     * For example $uploaderAlias='@uploader' and @uploader defined as '/frontend/web/files', $file_path="/user/" and $subdir by default then 
     * files will be saved in frontend/web/files/user/53 and subdirectories according sizes:
     *     frontend/web/files/user/53/thumb
     *     frontend/web/files/user/53
     *     frontend/web/files/user/53/original
     * 
     * -//- $file_path="/common/" and Uploader::subdir='' then 
     * files will be saved in frontend/web/files/post and subdirectories according sizes:
     *     frontend/web/files/common/thumb
     *     frontend/web/files/common
     *     frontend/web/files/common/original
     */
    public $sizes;
    
    private $dir_to_delete;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->base_path = Yii::getAlias($this->uploaderAlias);
    }
    
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            // delete all associated files
            \yii\db\ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * Add directory separator
     * @param string $subdir
     * @return string $subdir with final slash
     */
    private function add($subdir) { return $subdir ? $subdir . '/' : ''; }
    
    /**
     * Get relative path to a file.
     * 
     * @param string $subdir subdirectory in a parent directory - files/post/38, where 38 is a $subdir and a post ID
     * @param string $catalog files/post/38/thumb or files/post/38/main where "thumb", "main" are catalogs
     * @return string full file path
    */
    public function getFilePath($subdir, $catalog = '')
    {
        return $this->base_path . $this->file_path . $this->add($subdir) . $this->add($catalog);
    }


    /**
     * Get absolute path to a file.
     * 
     * @param string $subdir subdirectory in a parent directory - files/post/38, where 38 is a $subdir
     * @return string full file path
    */

    public function getAbsoluteFilePath($subdir)
    {
        return Yii::getAlias($this->absoluteAlias) . $this->getFilePath($subdir);
    }

    /**
     * If subdirectory exists return path else make it with catalogs
     * 
     * @param $subdir - subdirectory
     * @return string | false
     */
    public function setFilePath($subdir)
    {
        $ok = false;
        $path = $this->getAbsoluteFilePath($subdir);
        if ($ok = is_dir($path) ? true : FileHelper::createDirectory($path)) {
            foreach($this->sizes as $size) {
                if ($size['catalog'] && !is_dir($path . $size['catalog'])) {
                    $ok = FileHelper::createDirectory($path . $size['catalog']);
                } else
                    continue;
            }
        }
        return $ok ? $path : false;
    }

    /**
     * @return array all files linked with the model
     */
    public function getFiles()
    {
        return OneFile::find()
            ->where('parent_id=:parent_id AND model=:model', [
                ':parent_id' => $this->owner->id,
                ':model'     => $this->owner->className(),
            ])
            ->orderBy('created_at')
            ->all();
    }

    /**
     * Get file from collection of model files by index.
     * 
     * @param integer $i - index in owner->$files
     * @return \sergmoro1\uploader\models\OneFile | null
    */
    public function getFile($i = 0)
    {
        $files = $this->getFiles();
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
        return count($this->getFiles());
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
            return $this->getFilePath($file->subdir) . $file->name;
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
     * Delete file and all resized images in catalogs if exist
     * 
     * @param sergmoro1\uploader\models\OneFile $file
     * @return boolean
     */
    public function deleteFile($file)
    {
        $success = true;
        $path = $this->getAbsoluteFilePath($file->subdir);
        if (!$this->dir_to_delete)
            $this->dir_to_delete = $path;
        foreach($this->sizes as $size) {
            $path_file = $path . $this->add($size['catalog']) . $file->name;
            if(file_exists($path_file))
                $success = FileHelper::unlink($path_file);
        }
        return $success;
    }

    /**
     * Delete all files associated with an owner model
     * 
     * @param yii\base\Event $event
     */
    public function afterDelete($event)
    {
        foreach ($this->getFiles() as $file) {
            if ($this->deleteFile($file))
                $file->delete();
        }
        FileHelper::removeDirectory($this->dir_to_delete);
        $this->dir_to_delete = null;
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
        $mw = isset($this->sizes['main']['width']) ? $this->sizes['main']['width'] : 0;
        $mh = isset($this->sizes['main']['height']) ? $this->sizes['main']['height'] : 0;
        $this->_min = min($mw, $mh); 
        return $this->_min;
    }
    
    /**
     * Get width for popup window using for cropping.
     * 
     * @return integer
     */
    public function getPopUpWidth()
    {
        return (isset($this->sizes['main']['width']) 
            ? $this->sizes['main']['width']
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
        $this->_aspectRatio = isset($this->sizes['main']['width']) && 
            isset($this->sizes['main']['height']) && $this->sizes['main']['height'] > 0 
            ? $this->sizes['main']['width'] / $this->sizes['main']['height']
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
