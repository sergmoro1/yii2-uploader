Yii2 module for image|file upload
=================================

Multiple uploading, sorting file collection by mouse, adding description for the file, cropping.
Resize images using the queue component.

Demo
----

Photos [by categories](http://sample.vorst.ru/photo/index),
photo gallery [BEFORE | AFTER](http://sample.vorst.ru/stout/index).

Advantages
----------

A common approach for working with uploading images or files in an application.

If the model needs images or files, it is enough:

* to connect the behavior, 
* define the subdirectory in which they will be stored, 
* define the desired image sizes

For example `common\models\User.php`

```php
use sergmoro1\uploader\behaviors\HaveFileBehavior;

class User extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => HaveFileBehavior::className(),
                'file_path' => '/user/',
                'sizes' => [
                    'original' => ['width' => 1200, 'height' => 1200, 'catalog' => 'original'],
                    'main'     => ['width' => 400,  'height' => 400,  'catalog' => ''],
                    'thumb'    => ['width' => 90,   'height' => 90,   'catalog' => 'thumb'],
                ],
            ],
        ]);
    }
}
```

Directories
-----------

Information about all uploaded files are stored in one table `onefile`. 
There is no need to define a field of type `file` in the model, which need files.

The files are uploaded and stored in the directory defined as a concatenation of paths of two aliases @uploader and @absolute.
Both aleases should be defined in appropriate config file. For example in `common/config/main-local.php` 
if storage folder are the same for `backend` and `fronend`.

```php
return [
    'aliases' => [
        '@absolute' => '/home/my/site',
        '@uploader' => '/frontend/web/files',
    ],
```

But there may be different configurations.

In the folder the files are arranged by users and sizes.

* `/frontend/web/files/user/2`
* `/frontend/web/files/user/2/thumb`
* `/frontend/web/files/user/2/original`

Where `2` is the user ID or `subdir`.
`subdir` can be blank, then all files will be saved in one folder.
`subdir` can be defined when widget placed in a view. 

Sizes in an example are `thumb`, `main`, `original`.
More sizes can be defined but those are `must have`.

Installation
------------

The preferred way to install this extension is through composer.

Either run

`composer require --prefer-dist sergmoro1/yii2-uploader`

or add

`"sergmoro1/yii2-uploader": "^2.0.0"`

to the require section of your composer.json.

Run migration.

`php yii migrate --migrationPath=@vendor/sergmoro1/yii2-uploader/src/migrations`

If you used a previous version `sergmoro1\yii2-byone-uploader` then run only the next migration.

`php yii migrate --migrationPath=@vendor/sergmoro1/yii2-uploader/src/migrations/v1`

Configuration
-------------

To register the module in an app `common/config/main.php` (advanced) or in appropriate config file.

```php
    'modules' => [
        'uploader' => [
            'class' => 'sergmoro1\uploader\Module',
        ],
```

For file storage configuration in `common/config/main-local.php` or any other config file add 
lines appropriate for your app.

```php
return [
    'aliases' => [
        '@absolute' => '/home/my/site',
        '@uploader' => '/frontend/web/files',
    ],
```

If [queue](https://www.yiiframework.com/extension/yiisoft/yii2-queue/doc/guide/2.0/ru/usage) should be using for image resizing, 
then `queue` component must be defined. The name of the queue component must be exactly `queue`.

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

To do uploading place the widget in a `_form.php` or any other view.

```
    <?= \sergmoro1\uploader\widgets\Uploader::widget([
        'model'       => $model,
        'draggable'   => true,
        'cropAllowed' => true,
        'limit'       => 5,
    ]) ?>
```

If image should be cropped, subdirectories `original`, `main`, `thumb` must to be defined.

May be uploaded any amount of files for one model but files amount can be limited by `limit` parameter of the widget.

If `subdir` will be defined as an empty string then all files will be uploaded in the same folder but with subfolders
that was defined in `sizes`.

```
    <?= Uploader::widget([
        'model'       => $model,
        'subdir'      => '',
    ]) ?>
```

Description of uploaded files
-----------------------------

You can leave descriptions to the files. To do this in the `_form.php`,
in the already mentioned widget, you need to add the parameter `appendeixView`.

```php
  <?= Uploader::widget([
    'model'        => $model,
    'appendixView' => '/user/appendix',
    'cropAllowed'  => true,
  ]) ?>
```

And add to the view`/views/user/appendix.php` the following content:

```html
<span id='description'>
    <?= isset($file->vars->description) ? $file->vars->description : ''; ?>
</span>
```

Field `description` defined by default, but fields not limited.

Options
-------

**cropAllowed** (`false`)

If image should be cropped, `original`, `main`, `thumb` sizes must be defined.

The cropping sizes are specified by the main directory where `catalor` parameter should be equal `''`.

If this directory is set to square, then the remaining sizes will be square after cropping.

**draggable** (`false`)

Make `true` if uploaded files should be swapped. 

**allowedTypes** ( `[]` )

To control files types on a client side, `['image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png', 'image/gif', 'image/x-gif']`.
Any if empty.

**allowedTypesReg** ( `'/image\\/[jpeg|jpg|png|gif]/i'` )

Server side control. Any if empty. Preferable way to check allowed types to upload.

**appendixView** ( `''` )

View file name of additional fields for uploaded files. 
See [views/user/appendix.php](https://github.com/sergmoro1/yii2-user/blob/master/src/views/user/appendix.php).

**minFileSize** ( `0` )

Minimum file size in bytes. `0` for any.

**maxFileSize** ( `0` )

Maximum file size in bytes. `0` for any.

**limit** ( `0` )

Maximum amount of files to upload for one model. `0` for any.

**secure** ( `true` )

Ordinary user authorization required, but verification may be switched off.

**subdir** ( `null` )

If `null` (by defauld) `$model->id` will be used.
