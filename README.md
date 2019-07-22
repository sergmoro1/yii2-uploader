Yii2 module for image|file upload
=================================

Multiple uploading, sorting file collection by mouse, adding description for the file, cropping.

Demo
----

In progress.

Advantages
----------

A common approach for working with uploading images or files in an application.

If the model needs images or files, it is enough

* to connect the behavior, 
* determine the subdirectory in which they will be stored, 
* define the desired image sizes and 
* the method that receives the files for the model.

For example `common\models\User.php`

```php
namespace common\models;

use sergmoro1\uploader\behaviors\HaveFileBehavior;
use sergmoro1\uploader\models\OneFile;

class User extends ActiveRecord
{
    // sizes and subdirs of uploaded images
    public $sizes = [
        'original' => ['width' => 1200, 'height' => 1200, 'catalog' => 'original'],
        'main'     => ['width' => 400,  'height' => 400, 'catalog' => ''],
        'thumb'    => ['width' => 90,   'height' => 90,  'catalog' => 'thumb'],
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'have-file' => [
                'class' => HaveFileBehavior::className(),
                'file_path' => '/files/user/',
            ],
        ]);
    }

    /**
     * @return array all files linked with the model
     */
    public function getFiles()
    {
        return OneFile::find()
            ->where('parent_id=:parent_id AND model=:model', [
                ':parent_id' => $this->id,
                ':model' => 'common\models\User',
            ])
            ->orderBy('created_at')
            ->all();
    }
}
```

Directories
-----------

Information about all uploaded files are stored in one table `onefile`. 
There is no need to define a field of type `file` in the model, which need files.

The files are uploaded and stored in the directory `frontend/web/files`. 
For each model subdirectories are possible. For example `frontend/web/files/user`.
 
In the subdirectory the files are arranged by users and sizes.

* `frontend/web/files/user/2`
* `frontend/web/files/user/2/thumb`
* `frontend/web/files/user/2/original`

Where `2` is the user ID.

Sizes in an example are `thumb`, `main`, `original`.
More sizes can be defined but those are `must have`.

Installation
------------

The preferred way to install this extension is through composer.

Either run

`composer require --prefer-dist sergmoro1/yii2-uploader`

or add

`"sergmoro1/yii2-uploader": "~2.0"`

to the require section of your composer.json.

Run migration.

`php yii migrate --migrationPath=@vendor/sergmoro1/yii2-uploader/src/migrations`

If you used a previous version `sergmoro1\yii2-byone-uploader` then run only the next migration.

`php yii migrate --migrationPath=@vendor/sergmoro1/yii2-uploader/src/migrations/v1`

Configuration
-------------

To register the module in an app `common/config/main.php`.

```php
'modules' => [
    'uploader' => [
        'class' => 'sergmoro1\uploader\Module',
    ],
```

If `advanced` template is used then `before_web` parameter should be defined. For example `backend\config\params.php`

```php
return [
    'before_web' => 'backend',
    ...
];
```

For `frontend` or `basic` template no needed.

Usage
-----

```php
$model = User::findOne(2);

// get top thumb image of the model with image description
echo Html::img($model->getImage('thumb'), ['alt' => $model->getFileDescription()) ]);

// get top image of the model from main catalog
echo Html::img($model->getImage());

// get all images of the model from original catalog with image description
$image = $model->getImage('original');
while ($image) {
    echo Html::img($image, ['title' => $model->getFileDescription()]);
    $image = $model->getNextImage('original');
}
```

To do uploading place the widget in a form or any other view, for example `backend/views/user/_form.php`.

```
use sergmoro1\uploader\widgets\Uploader;

    <?= Uploader::widget([
        'model'       => $model,
        'draggable'   => true,
        'cropAllowed' => true,
        'limit'       => 5,
    ]) ?>
```

If image should be cropped, subdirectories `original`, `main`, `thumb` must to be defined.

May be uploaded any amount of files for one model but files amount can be limited by `limit` parameter of the widget.

Description of uploaded files
-----------------------------

You can leave descriptions to the files. To do this in the form `backend/views/user/_form.php` (for example),
in the already mentioned widget, you need to add the parameter `appendeixView`.

```php
  <?= Uploader::widget([
    'model'        => $model,
    'appendixView' => '/user/appendix',
    'cropAllowed'  => true,
  ]) ?>
```

And add view `backend/views/user/appendix.php`
the following content:

```html
<span id='description'>
    <?= isset($file->vars->description) ? $file->vars->description : ''; ?>
</span>
```

Field `description` defined by default, but fields not limited.

Options
-------

**cropAllowed** (`false`)

If image should be cropped, `original`, `main`, `thumb` sizes must be defined in `$sizes` array of the model.

The cropping sizes are specified by the main directory where `catalor` parameter should be equal `''`.

If this directory is set to square, then the remaining sizes will be square after cropping.

**draggable** (`false`)

If uploaded files should be swapped then in a getter `getFiles()` rows must be sorted by `created_at`. 

```php
public function getFiles()
{
    return OneFile::find()
        ->where('parent_id=:parent_id AND model=:model', [
            ':parent_id' => $this->id,
            ':model' => 'common\models\User',
        ])
        ->orderBy('created_at')
        ->all();
}
```

**allowedTypes** ( `[]` )

To control files types on client side, `['image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png', 'image/gif', 'image/x-gif']`.
Any if empty.

**allowedTypesReg** ( `'/image\\/[jpeg|jpg|png|gif]/i'` )

Server side control. Any if empty. Preferable way to check allowed types to upload.

**appendixView** ( `` )

View file name of additional fields for uploaded files. For ex. `'/user/appendix'`. See `\sergmoro1\user\views\user\appendix.php`.

**minFileSize** ( `0` )

Minimum file size in bytes. `0` for any.

**maxFileSize** ( `0` )

Maximum file size in bytes. `0` for any.

**limit** ( `0` )

Maximum amount of files to upload for one model. `0` for any.

**secure** ( `true` )

Ordinary extension require user authorization, but verification may be switched off.
