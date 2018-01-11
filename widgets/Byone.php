<?php
/**
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - MIT
 * 
 * Byone file upload plugin with jQueryFileUpload (Blueimp) and JCrop.
 */

namespace sergmoro1\uploader\widgets;

use Yii;
use yii\base\Widget;
use yii\web\View;
use yii\helpers\Url;
use yii\base\NotSupportedException;

use sergmoro1\uploader\bundles\BlueimpAsset;
use sergmoro1\uploader\bundles\EditLineAsset;
use sergmoro1\uploader\bundles\JQueryUiAsset;
use sergmoro1\uploader\bundles\DraggableAsset;
use sergmoro1\uploader\bundles\JcropAsset;

class Byone extends Widget {
	public $model;
	// relation name by default, for OneFile model, you can change it on your own
	public $files = 'files';
	// secure actions
	public $secure = true;

	// OneFile model fields
	public $modelName;
	public $parent_id;
	public $subdir;

	public $acceptFileTypes = 'image\\/[jpeg|jpg|png|gif]';
	public $minFileSize = 0.1; // min file (FS) size in Mb 
	public $maxFileSize = 2; // max FS in Mb
	public $maxFiles = 0; // max count of files that can be uploaded, 0 - any
	public $draggable = false; 
	public $errors = [];
	
	// Buttons for working with uploaded files.
	// All actions defined in OneFileController.
    public $btns = [];
	private static $defaults = [
		'choose' => [
			'class' => 'btn btn-default',
		],
		'delete' => [
			'caption' => '<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>',
			'class' => 'btn btn-danger btn-sm',
			'action' => '/uploader/one-file-secure/delete',
		],
		'edit' => [
			'caption' => '<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>',
			'class' => 'btn btn-primary btn-sm',
		],
		'save' => [
			'caption' => '<span class="glyphicon glyphicon-floppy-save" aria-hidden="true"></span>',
			'class' => 'btn btn-success btn-sm',
			'action' => '/uploader/one-file-secure/save',
		],
		'cancel' => [
			'caption' => '<span class="glyphicon glyphicon-erase" aria-hidden="true"></span>',
			'class' => 'btn btn-default btn-sm',
		],
		'crop' => [
			'action' => '/uploader/one-file-secure/crop',
		],
		'swap' => [
			'action' => '/uploader/one-file-secure/swap',
		],
	];
	public $barClass = 'progress-bar';
	public $cropAllowed = false;
	public $uploadAction = '/uploader/one-file-secure/create';
	public $appendixView = ''; 
	
	public $blueimp = [
		'name' => 'fileupload', // name of file-upload field
		'dataType' => 'json',
	];

	/**
	 * Init vars and publishes the required assets.
	 */
	public function init() {

		parent::init();
        $this->registerTranslations();
        
        if(!$this->secure)
			$this->uploadAction = '/uploader/one-file/create';
		// errors of the file uploading
		$this->errors['size'] = Yii::t('byone', 'File size can be from {minFileSize} to {maxFileSize}Mb', ['minFileSize' => $this->minFileSize, 'maxFileSize' => $this->maxFileSize]);
		$this->errors['type'] = Yii::t('byone', 'There is not right file type! Allowed types - {acceptFileTypes}.', ['acceptFileTypes' => $this->acceptFileTypes]);
		$this->errors['maxFiles'] = Yii::t('byone', 'Only {maxFiles} file(s) can be uploaded.', ['maxFiles' => $this->maxFiles]);

		// captions of the buttons and other strings that should be translated
		self::$defaults['choose']['label'] = Yii::t('byone', 'Photo');
		self::$defaults['choose']['caption'] = Yii::t('byone', 'Choose a file');
		self::$defaults['delete']['question'] = Yii::t('byone', 'Are you shure you want to delete this element?');
		self::$defaults['crop']['caption'] = Yii::t('byone', 'Upload');
		
		// set buttons by widget parameters or defaults
		foreach(self::$defaults as $name => $values) {
			foreach($values as $property => $value)
				if(!isset($this->btns[$name][$property]))
					$this->btns[$name][$property] = $property == 'action' && !$this->secure 
						? str_replace('-secure', '', self::$defaults[$name][$property])
						: self::$defaults[$name][$property];
		}
		
		// full path server side handlers
		$this->btns['delete']['action'] = Url::toRoute($this->btns['delete']['action']);
		$this->btns['save']['action'] = Url::toRoute($this->btns['save']['action']);
		$this->btns['crop']['action'] = Url::toRoute($this->btns['crop']['action']);
		$this->btns['swap']['action'] = Url::toRoute($this->btns['swap']['action']);
		
		$this->blueimp['url'] = Url::toRoute($this->uploadAction);
		$this->blueimp['minFileSize'] = $this->minFileSize;
		$this->blueimp['maxFileSize'] = $this->maxFileSize;
		$this->blueimp['maxFiles'] = $this->maxFiles;

		// if model name is not set, set it
		if(!isset($this->modelName))
			$this->modelName = get_class($this->model);
		// parent_id has to exist
		if(is_null($this->model->id))
			$this->model->id = false;
		if(is_null($this->parent_id))
			$this->parent_id = $this->model->id;
		if(is_null($this->subdir))
			$this->subdir = $this->model->id;
		// POST-type data for the handler
		$this->blueimp['formData'] = [
			'model' => $this->modelName,
			'parent_id' => $this->parent_id,
			'subdir' => $this->subdir,
			'cropAllowed' => ($this->cropAllowed ? 1 : 0),
		];
		
		// min pixels for X and Y when cropping
		$minW = $this->model->getMin();
		$aspectRatio = $this->model->getAspectRatio();
		$minH = $minW * $aspectRatio;

		// send params to JavaScript environment
		$options = [
			'btns' => $this->btns,
			'barClass' => $this->barClass,
			'cropAllowed' => $this->cropAllowed,
			'minW' => $minW,
			'minH' => $minH,
			'aspectRatio' => $aspectRatio,
			'appendixView' => $this->appendixView,
			'acceptFileTypes' => $this->acceptFileTypes,
			'errors' => $this->errors,
		];
		$this->view->registerJs(
			"editLine.options = " . json_encode($options) . ";", 
			View::POS_READY, 
			'editLineOptions'
		);
		
		// assets 
		BlueimpAsset::register($this->view);
		EditLineAsset::register($this->view);
		if($this->draggable) {
			JQueryUiAsset::register($this->view);
			DraggableAsset::register($this->view);
		}
		if($this->cropAllowed) 
			JcropAsset::register($this->view);
		// plugin
		$selector = $name = $this->blueimp['name'];
        $option = json_encode($this->blueimp);
        $script = "jQuery('#{$selector}').{$name}({$option});";
        $this->view->registerJs($script, View::POS_END, 'byone-plugin');
	}

    /**
     * Generates the required HTML and Javascript
     */
    public function run() {
		parent::run();
		return $this->render('byone', [
			'model' => $this->model,
			'files' => $this->files,
			'barClass' => $this->barClass,
			'btns' => $this->btns,
			'blueimp' => $this->blueimp,
			'cropAllowed' => $this->cropAllowed,
			'draggable' => $this->draggable,
			'appendixView' => $this->appendixView, 
		]);
    }

    /**
     * Register widget translations.
     */
    public static function registerTranslations()
    {
        if (!isset(Yii::$app->i18n->translations['byone'])) {
            Yii::$app->i18n->translations['byone'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@vendor/sergmoro1/yii2-byone-uploader/messages',
                'forceTranslation' => true,
                'fileMap' => [
                    'byone' => 'byone.php'
                ]
            ];
        }
    }
}
