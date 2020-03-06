<?php

namespace sergmoro1\uploader\components;

use Yii;
use yii\base\Component;
use yii\imagine\Image;

use sergmoro1\uploader\Module;
use sergmoro1\uploader\models\OneFile;
use sergmoro1\uploader\behaviors\ImageTransformationBehavior;

/**
 * Class for keeping just uploaded files and resize them if they are images.
 *
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class OneFileKeeper extends Component {
    /** @var string */
    public $modelClass;

    /** @var integer id in a $modelClass */
    public $parent_id;

    /** @var string $subdir in the $path */
    public $subdir;

    /** @var string $set_path to save files of the model */
    public $set_path;
    /** @var string $get_path to get files of the model */
    public $get_path;

    /** @var array $sizes of images */
    public $sizes;

    /** @var string RegExp */
    public $allowedTypesReg;
    /** @var integer */
    public $minFileSize;
    /** @var integer */
    public $maxFileSize;
    /** @var integer $limit amount of files to upload */
    public $limit;
    /** @var integer $alreadyUploaded files */
    public $alreadyUploaded;

    private $ext;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            ['class' => ImageTransformationBehavior::className()],
        ];
    }
    
    /**
     * Get new unique name of file with the same extension as $old.
     * 
     * @param string $old
     * @return string
     */
    private function getNewName($old) {
        mb_internal_encoding('UTF-8');
        
        $point = mb_strrpos($old, '.');
        $this->ext   = mb_strtolower(mb_substr($old, $point));
        
        return uniqid() . $this->ext;
    }

    /**
     * Unsuccessful uploading. 
     * 
     * @param string $message
     * @return array with error status and message
     */
    private function err($message)
    {
        return [
            'success' => false,
            'ext'     => $this->ext,
            'message' => $message,
        ];
    }
    
    /**
     * Move uploaded file from source to dest.
     * 
     * @param string $source file path and name
     * @param string $dest file path and name
     * @return boolean
     */
    public function move($source, $dest)
    {
        return move_uploaded_file($source, $dest);
    }

    /**
     * Sculpt model.
     * 
     * @param array $config
     * @return \sergmoro1\uploader\models\OneFile
     */
    public function sculpt($config)
    {
        return new OneFile($config);
    }

    /**
     * Resizing and keeping uploaded file. Save information about uploaded file.
     * 
     * @param string $file_input
     * @return array information about uploaded files | errors
     * @see \sergmoro1\uploader\models\OneFile.
     */
    public function proceed($fileinput)
    {
        if (!(isset($_FILES[$fileinput]) && $this->set_path))
            return $this->err(
                Module::t('core', 'Field {fileinput} is empty or path {path} can\'t be created.', [
                    'fileinput' => $fileinput,
                    'path'      => $this->set_path,
                ])
            );
        
        // not uploaded
        if (!($_FILES[$fileinput]['error'] == UPLOAD_ERR_OK))
            return $this->err(Module::t('core', 'File can\'t be uploaded.'));

        $name      = $_FILES[$fileinput]['name'];
        $tmp_name  = $_FILES[$fileinput]['tmp_name'];
        $file_type = $_FILES[$fileinput]['type'];
        $file_size = $_FILES[$fileinput]['size'];

        $is_image  = strtolower(substr($file_type, 0, 5)) == 'image';
        $new_name  = ($is_image ? 'i' : 'd') . $this->getNewName($name);
        
        // too many files
        if ($this->limit && $this->limit <= $this->alreadyUploaded)
            return $this->err(Module::t('core', 'Too many files uploaded. Allowed {max}.', ['max' => $this->limit]));

        // check allowed types
        if ($this->allowedTypesReg && preg_match($this->allowedTypesReg, $file_type) == 0)
            return $this->err(
                Module::t('core', 'File type {type} is not allowed.', ['type' => $file_type])
            );
        // check file size
        if (!($file_size >= $this->minFileSize && $file_size <= $this->maxFileSize))
            return $this->err(
                Module::t('core', 'File size {size} is too small or big. Min {min}, max {max} bytes allowed.', [
                    'size' => $file_size,
                    'min'  => $this->minFileSize,
                    'max'  => $this->maxFileSize,
                ])
            );

        // check min width and height of Image
        if ($is_image) {
            $image = Image::getImagine()->open($tmp_name);
            $image_size = $image->getSize();
            $width = $this->sizes['main']['width'];
            $height = $this->sizes['main']['height'];
            if ($image_size->getWidth() < $width || $image_size->getHeight() < $height)

                return $this->err(
                    Module::t('core', 'The width or height of the image, or both, is smaller than necessary [{width}, {height}]px.', [
                        'width'  => $width,
                        'height' => $height,
                    ])
                );
        }

        $tmp = 'tmp_' . $new_name;
        if ($this->move($tmp_name, $this->set_path . $tmp)) {
            if ($is_image) {
                // resize image
                $this->resizeSave($this->set_path, $tmp, $new_name);
            }
        } else {
            return $this->err(
                Module::t('core', 'Temporary file {source} can\'t be moved to file {target}.', [
                    'source' => ($tmp_name),
                    'target' => ($this->set_path . $tmp),
                ])
            );
        }

        
        // add new record to OneFile model
        $oneFile = $this->sculpt([
            'model'     => $this->modelClass,
            'parent_id' => $this->parent_id,
            'original'  => $name,
            'name'      => $new_name,
            'subdir'    => $this->subdir,
            'type'      => $file_type,
            'size'      => $file_size,
        ]);
        
        // save information about just uploaded file to the model 
        if ($oneFile->save()) {
            return [
                'success' => true, 
                'file'   => [ 
                    'id'        => $oneFile->id,
                    'path'      => $this->get_path,
                    'catalog'   => (isset($this->sizes['thumb']) ? 'thumb/' : ''),
                    'name'      => $new_name,
                    'is_image'  => $is_image,
                    'ext'       => $this->ext,
                    'width'     => (isset($this->sizes['thumb']) ? $this->sizes['thumb']['width'] : '50'),
                ]
            ];
        } else { // information about a file can't be saved
            return $this->err(
                    Module::t('core', 'Information about file {name} can\'t be saved in a model.', ['name' => ($this->set_path . $new_name)])
            );
        }
    }
}
