<?php

namespace sergmoro1\uploader\widgets;

use Yii;
use yii\base\Widget;
use yii\web\View;
use yii\helpers\Url;
use yii\base\NotSupportedException;

use sergmoro1\uploader\assets\SimpleUploadAsset;
use sergmoro1\uploader\assets\EditLineAsset;
use sergmoro1\uploader\assets\JQueryUiAsset;
use sergmoro1\uploader\assets\DraggableAsset;
use sergmoro1\uploader\assets\JcropAsset;

/**
 * File upload widget with simpleUpload.js and JCrop.js
 * 
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
class Uploader extends Widget {
    /** @var string name of file input field */
    public $name = 'fileinput';
    /** $var yii\db\ActiveRecord model connected with uploader */
    public $model;
    /** @var string relation name by default, for OneFile model, you can change it on your own */
    public $files = 'files';
    /** @var boolean secure actions for sergmoro1\uploader\controllers\OneFileController or not */
    public $secure = true;
    /** @var boolean draggable line of uploaded files or not */
    public $draggable = false; 
    /** @var boolean cropping allowed, if uploaded file is image? or not */
    public $cropAllowed = false;
    /** @var string path to a view file with additional fields connected with uploaded file */
    public $appendixView = ''; 

    // OneFile model fields
    public $modelClass;
    public $parent_id;
    public $subdir;

    // simpleUpload.js settings
    public $limit = 0;          // max count of files that can be uploaded, 0 mean any amount
    public $maxFileSize = null; // max file size in bytes
    public $allowedTypes = []; // array of types like 'image/jpeg', used only by js plugin
    // additional settings
    public $allowedTypesReg = '/image\/[jpeg|jpg|png|gif]/i'; // regexp for checking file types, used on the server side
    public $minFileSize = null; // min file size in bytes
    
    // Buttons for working with uploaded files.
    // All actions defined in OneFileController.
    public $btns = [];
    private static $defaults = [
        'choose' => [
            'class' => 'btn btn-default',
        ],
        'delete' => [
            'caption' => '<span class="glyphicon glyphicon-trash"></span>',
            'class' => 'btn btn-danger btn-sm',
            'action' => '/uploader/one-file-secure/delete',
        ],
        'edit' => [
            'caption' => '<span class="glyphicon glyphicon-pencil"></span>',
            'class' => 'btn btn-primary btn-sm',
        ],
        'view' => [
            'caption' => '<span class="glyphicon glyphicon-eye-open"></span>',
            'class' => 'btn btn-default',
        ],
        'save' => [
            'caption' => '<span class="glyphicon glyphicon-floppy-save"></span>',
            'class' => 'btn btn-success btn-sm',
            'action' => '/uploader/one-file-secure/update',
        ],
        'cancel' => [
            'caption' => '<span class="glyphicon glyphicon-erase"></span>',
            'class' => 'btn btn-default btn-sm',
        ],
        'crop' => [
            'caption' => '<span class="glyphicon glyphicon-resize-small"></span>',
            'class' => 'btn btn-default',
            'action' => '/uploader/one-file-secure/crop',
        ],
        'swap' => [
            'action' => '/uploader/one-file-secure/swap',
        ],
    ];
    
    public $errors = [];
   
    public $uploadAction = '/uploader/one-file-secure/create';
    
    public $simpleUpload = [
        'name'   => 'fileinput',
        'expect' => 'json',
    ];

    /**
     * Init vars and publishes the required assets.
     */
    public function init() {

        parent::init();
        $this->registerTranslations();
        
        if (!$this->secure)
            $this->uploadAction = '/uploader/one-file/create';

        $this->setFileSizes();
        $this->setCaptions();
        $this->setButtons();
        $this->setModel();
        $this->setPlugin();

        // send params to JavaScript environment
        // editLine
        $editLineOptions = [
            'btns'         => $this->btns,
            'appendixView' => $this->appendixView,
            'cropAllowed'  => $this->cropAllowed,
            'minW'         => $this->model->getMin(),
            'aspectRatio'  => $this->model->getAspectRatio(),
            'minH'         => ($this->model->getMin() * $this->model->getAspectRatio()),
        ];
        $json = json_encode($editLineOptions);
        $this->view->registerJs("editLine.options = $json;", View::POS_READY);
        // simpleUpload
        $json = json_encode($this->simpleUpload);
        $this->view->registerJs("var uploadOptions = $json;", View::POS_HEAD);
        
        // assets 
        SimpleUploadAsset::register($this->view);
        EditLineAsset::register($this->view);
        if($this->draggable) {
            JQueryUiAsset::register($this->view);
            DraggableAsset::register($this->view);
        }
        if($this->cropAllowed) 
            JcropAsset::register($this->view);
    }

    /**
     * Set min and max file sizes.
     */
    public function setFileSizes()
    {
        // set min & max file sizes
        if (is_null($this->minFileSize))
            $this->minFileSize = isset(Yii::$app->params['fileSize']['min']) 
                ? Yii::$app->params['fileSize']['min'] 
                : (1024 * 10);
        if (is_null($this->maxFileSize))
            $this->maxFileSize = isset(Yii::$app->params['fileSize']['max'])
                ? Yii::$app->params['fileSize']['max']
                : (1024 * 2000);
    }

    /**
     * Set label and captions.
     */
    public function setCaptions()
    {
        // captions of the buttons and other strings that should be translated
        self::$defaults['choose']['label']    = Yii::t('core', 'Photo');
        self::$defaults['choose']['caption']  = Yii::t('core', 'Choose a file');
        self::$defaults['delete']['question'] = Yii::t('core', 'Are you shure you want to delete this element?');
    }

    /**
     * Set errors of uploading.
     */
    public function getErrors()
    {
        return [ 
            'InvalidFileTypeError' => Yii::t('core', 'That file format is not allowed'),
            'MaxFileSizeError '    => Yii::t('core', 'That file is too big'),
        ];
    }

    /**
     * Set buttons and actions.
     */
    public function setButtons()
    {
        // set buttons by widget parameters or defaults
        foreach (self::$defaults as $name => $values) {
            foreach ($values as $property => $value)
                if(!isset($this->btns[$name][$property]))
                    $this->btns[$name][$property] = $property == 'action' && !$this->secure 
                        ? str_replace('-secure', '', self::$defaults[$name][$property])
                        : self::$defaults[$name][$property];
        }
        // full path server side handlers
        $this->btns['delete']['action'] = Url::toRoute($this->btns['delete']['action']);
        $this->btns['save']['action']   = Url::toRoute($this->btns['save']['action']);
        $this->btns['crop']['action']   = Url::toRoute($this->btns['crop']['action']);
        $this->btns['swap']['action']   = Url::toRoute($this->btns['swap']['action']);
    }

        
    /**
     * Set model class and IDs by default.
     */
    public function setModel()
    {
        // set model Class
        if (!isset($this->modelClass))
            $this->modelClass = get_class($this->model);
        // set IDs
        if (is_null($this->model->id))
            $this->model->id = false;
        if (is_null($this->parent_id))
            $this->parent_id = $this->model->id;
        if (is_null($this->subdir))
            $this->subdir = $this->model->id;
    }
    
    /**
     * Set JS plugin options.
     */
    public function setPlugin()
    {
        $this->simpleUpload['name']         = $this->name;
        $this->simpleUpload['url']          = Url::toRoute($this->uploadAction);
        $this->simpleUpload['allowedTypes'] = $this->allowedTypes;
        $this->simpleUpload['maxFileSize']  = $this->maxFileSize;
        $this->simpleUpload['limit']        = $this->limit;
        $this->simpleUpload['errors']       = $this->getErrors();
        // set data for the handler
        $this->simpleUpload['data'] = [
            'model'             => $this->modelClass,
            'parent_id'         => $this->parent_id,
            'subdir'            => $this->subdir,
            'cropAllowed'       => ($this->cropAllowed ? 1 : 0),
            'allowedTypesReg'   => $this->allowedTypesReg,
            'minFileSize'       => $this->minFileSize,
            'maxFileSize'       => $this->maxFileSize,
            'limit'             => $this->limit,
        ];
    }

    /**
     * Generates the required HTML and Javascript
     */
    public function run()
    {
        parent::run();
        return $this->render('uploader', [
            'fileinput'     => $this->name,
            'model'         => $this->model,
            'files'         => $this->files,
            'btns'          => $this->btns,
            'cropAllowed'   => $this->cropAllowed,
            'draggable'     => $this->draggable,
            'appendixView'  => $this->appendixView, 
        ]);
    }

    /**
     * Register widget translations.
     */
    public static function registerTranslations()
    {
        if (!isset(Yii::$app->i18n->translations['core'])) {
            Yii::$app->i18n->translations['core'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@vendor/sergmoro1/yii2-uploader/src/messages',
                'forceTranslation' => true,
                'fileMap' => [
                    'core' => 'core.php'
                ]
            ];
        }
    }
}
