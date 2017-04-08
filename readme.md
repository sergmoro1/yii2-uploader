<a href='#en_readme_md'>readme.md in English</a>

<h1>Yii2 модуль для загрузки файлов|изображений на сервер</h1>

<h2>Demo</h2>
<a href='http://sample.vorst.ru/photo/index'>Фото по категориям</a>.
<a href='http://sample.vorst.ru/stout/index'>Галерея фотографий "До" и "После"</a>.

<h2>Преимущество</h2>
Информация обо всех загруженных файлах хранится в одной таблице - onefile.
Нет необходимости определять поле типа <code>file</code> в модели, которой нужны файлы.

Файлы загружаются и хранятся в каталоге <code>app/web/files</code> (base) или <code>frontend/web/files</code> (advanced).
Для каждой модели возможен свой подкаталог: <code>frontend/web/files/user</code> или <code>frontend/web/files/post</code>.
В подкаталоге файлы раскладываются по пользователям (или постам) и размерам:
<pre>
<code>frontend/web/files/user/2</code>
<code>frontend/web/files/user/2/thumb</code>
<code>frontend/web/files/user/2/original</code>
</pre>

Строки с загруженными файлами можно сортировать перетаскивая строки мышкой.
 
<h2>Пример</h2>
Нужно, чтобы User мог загружать фотографии. Определим в модели <code>common/models/User</code>:

<pre>
...
use sergmoro1\uploader\FilePath;
use sergmoro1\uploader\models\OneFile;
...

class User extends ActiveRecord implements IdentityInterface
{
   ...
  // Images sizes
  public $sizes = [
    // Catalog original should be define for cropping
    'original' =&gt; ['width' =&gt; 1600, 'height' =&gt; 900, 'catalog' =&gt; 'original'],
    'main' =&gt; ['width' =&gt; 400, 'height' =&gt; 400, 'catalog' =&gt; ''],
    'thumb' =&gt; ['width' =&gt; 90, 'height' =&gt; 90, 'catalog' =&gt; 'thumb'],
  ];
  // Get ref to the file, make dir and so
  public function behaviors()
  {
    return [
      'FilePath' =&gt; [
        'class' =&gt; FilePath::className(),
        'file_path' =&gt; '/files/user/',
      ]
    ];
  }
  // All files for User model
  public function getFiles()
  {
    return OneFile::find()
      -&gt;where('parent_id=:parent_id AND model=:model', [
        ':parent_id' =&gt; $this-&gt;id,
        ':model' =&gt; 'common\models\User',
       ])
      -&gt;all();
  }

  ...

</pre>

Теперь User может грузить фотографии, которые будут записываться в каталоги:
<pre>
  frontend/web/files/user/user_id
  frontend/web/files/user/user_id/original
  frontend/web/files/user/user_id/thumb
</pre>

При этом размеры будут изменены так, как определено в <code>$sizes</code>.

Для выполнения загрузки нужно определить widget в форме или в любом другом представлении. 
Например в <code>backend/views/user/_form.php</code>:

<pre>
use sergmoro1\uploader\widgets\Byone;
...

  &lt;?= Byone::widget([
    'model' =&gt; $model,
    'cropAllowed' =&gt; true,
  ]) ?&gt;
</pre>

Если нужно обрезать изображение (cropAllowed = true), необходимо определить подкаталог <code>original</code>.

Может быть загружено любое количество файлов, но можно и ограничить количество задав параметр <code>maxFiles</code>.

<h2>Установка</h2>

В каталоге приложения:

<pre>
$ composer require sergmoro1/yii2-byone-uploader "dev-master"
</pre>

Запустить миграцию
<pre>
$ php yii migrate --migrationPath=@vendor/sergmoro1/yii2-byone-uploader/migrations
</pre>

Зарегистрировать модуль в приложении - <code>common/config/main.php</code>:
<pre>
  'modules' =&gt; [
    'uploader' =&gt; [
      'class' =&gt; 'sergmoro1\uploader\Module',
  ],
</pre>

Если используется шаблон <code>advanced</code>, то в параметрах соответствующей ветки
нужно определить переменную <code>before_web</code>.
Например в <code>backend\config\params.php</code>
<pre>
&lt;?php
return [
	'before_web' =&gt; 'backend',
    ...
];
</pre>

Для <code>frontend</code> или шаблона <code>basic</code> в этом нет необходимости.

<h2>Описание загружаемых файлов</h2>

Можно оставлять комментарии к файлам. Для этого в форме <code>backend/views/user/_form.php</code>,
в уже упомянутом widget, нужно добавить параметр <code>appendeixView</code>:

<pre>
...
  &lt;?= Byone::widget([
    'model' =&gt; $model,
    'appendixView' =&gt; '/user/appendix',
    'cropAllowed' =&gt; true,
  ]) ?&gt;
</pre>

И добавить представление, например <code>backend/views/user/appendix.php</code>
следующего содержания:

<pre>
&lt;td id='description'&gt;
	&lt;?php echo isset($file-&gt;vars-&gt;description) ? $file-&gt;vars-&gt;description : ''; ?&gt;
&lt;/td&gt;
</pre>

Поле <code>description</code> определено по умолчанию, но количество полей не ограничено.

<h2>Опции</h2>

<code>cropAllowed</code> (false)
Если нужно обрезать изображение, то кроме установки этого флага нужно обязательно определить
размер 'original' в переменной <code>$sizes</code> модели.

<code>draggable</code> (false)
Если необходимо менять порядок загруженных файлов, то, кроме установки данного флага, необходимо
установить сортировку по полю <code>created_at</code> при определении геттера <code>getFiles()</code>. 

<pre>
  public function getFiles()
  {
    return OneFile::find()
      -&gt;where('parent_id=:parent_id AND model=:model', [
        ':parent_id' =&gt; $this-&gt;id,
        ':model' =&gt; 'common\models\YourModel',
       ])
      -&gt;orderBy('created_at')
      -&gt;all();
  }
</pre>

<code>acceptFileTypes</code> ('image\\/[jpeg|jpg|png|gif]')

<code>minFileSize</code> (0.1Mb)

<code>maxFileSize</code> (2Mb)

<code>maxFiles</code> (0 - любое количество)

<code>secure</code> (true)
Обычно расширение требует, чтобы пользователь был авторизован, но можно отключить проверку.

<h1><a name='en_readme_md'></a>Yii2 module for files|images upload</h1>

<h2>Demo</h2>
<a href='http://sample.vorst.ru/photo/index'>Photos by categories</a>.
<a href='http://sample.vorst.ru/stout/index'>Photo gallery "before" & "after"</a>.

<h2>Advantages</h2>
Information about all uploaded files are stored in one table - onefile. 
There is no need to define a field of type <code>file</code> in the model, which need files.

The files are uploaded and stored in the directory <code>frontend/web/files</code>. 
For each model subdirectories are possible: <code>frontend/web/files/user</code> or <code>frontend/web/files/post</code>. 
In the subdirectory the files are arranged by users (or posts) and sizes:
<pre>
<code>frontend/web/files/user/2</code>
<code>frontend/web/files/user/2/thumb</code>
<code>frontend/web/files/user/2/original</code>
</pre>

Rows with uploaded files can be sorted dragging the rows with the mouse.

<h2>Example</h2>
User must can upload photos. Need to be defined in a model <code>common/models/User</code>:

<pre>
...
use sergmoro1\uploader\FilePath;
use sergmoro1\uploader\models\OneFile;
...

class User extends ActiveRecord implements IdentityInterface
{
   ...
  // Images sizes
  public $sizes = [
    // Catalog original should be define for cropping
    'original' =&gt; ['width' =&gt; 1600, 'height' =&gt; 900, 'catalog' =&gt; 'original'],
    'main' =&gt; ['width' =&gt; 400, 'height' =&gt; 400, 'catalog' =&gt; ''],
    'thumb' =&gt; ['width' =&gt; 90, 'height' =&gt; 90, 'catalog' =&gt; 'thumb'],
  ];
  // Get ref to the file, make dir and so
  public function behaviors()
  {
    return [
      'FilePath' =&gt; [
        'class' =&gt; FilePath::className(),
        'file_path' =&gt; '/files/user/',
      ]
    ];
  }
  // All files for User model
  public function getFiles()
  {
    return OneFile::find()
      -&gt;where('parent_id=:parent_id AND model=:model', [
        ':parent_id' =&gt; $this-&gt;id,
        ':model' =&gt; 'common\models\User',
       ])
      -&gt;all();
  }

  ...

</pre>

Now User can uploading and files will be in:
<pre>
  frontend/web/files/user/user_id
  frontend/web/files/user/user_id/original
  frontend/web/files/user/user_id/thumb
</pre>

Thus, the sizes will be modified as specified.

To do uploading you need to place widget in a form or any other view. 
For ex. <code>backend/views/user/_form.php</code>:

<pre>
use sergmoro1\uploader\widgets\Byone;
...

  &lt;?= Byone::widget([
    'model' =&gt; $model,
    'cropAllowed' =&gt; true,
  ]) ?&gt;
</pre>

If image should be cropped (cropAllowed = true), subdirectory <code>original</code> need to be define.

May be uploaded any amount of files but files amount can be limited by <code>maxFiles</code>.

<h2>Installation</h2>

In app directory:

<pre>
$ composer require sergmoro1/yii2-byone-uploader "dev-master"
</pre>

Run migration:
<pre>
$ php yii migrate --migrationPath=@vendor/sergmoro1/yii2-byone-uploader/migrations
</pre>

To register the module in an app - <code>common/config/main.php</code>:
<pre>
  'modules' =&gt; [
    'uploader' =&gt; [
      'class' =&gt; 'sergmoro1\uploader\Module',
  ],
</pre>

If <code>advanced</code> template is used then <code>before_web</code> param should be defined.
For example <code>backend\config\params.php</code>
<pre>
&lt;?php
return [
	'before_web' =&gt; 'backend',
    ...
];
</pre>

For <code>frontend</code> or <code>basic</code> template no needed.

<h2>Description of uploaded files</h2>

You can leave comments to the files. To do this in the form <code>backend/views/user/_form.php</code>,
in the already mentioned widget, you need to add the parameter <code>appendeixView</code>:

<pre>
...
  &lt;?= Byone::widget([
    'model' =&gt; $model,
    'appendixView' =&gt; '/user/appendix',
    'cropAllowed' =&gt; true,
  ]) ?&gt;
</pre>

And add view, for ex. <code>backend/views/user/appendix.php</code>
the following content:

<pre>
&lt;td id='description'&gt;
	&lt;?php echo isset($file-&gt;vars-&gt;description) ? $file-&gt;vars-&gt;description : ''; ?&gt;
&lt;/td&gt;
</pre>

Field <code>description</code> defined by default, but fields not limited.

<h2>Options</h2>

<code>cropAllowed</code> (false)
If image should be cropped, 'original' size must be defined in <code>$sizes</code> array of the model.

<code>draggable</code> (false)
If uploaded files should be swapped then in a getter <code>getFiles()</code> rows must be sorted by <code>created_at</code>. 

<pre>
  public function getFiles()
  {
    return OneFile::find()
      -&gt;where('parent_id=:parent_id AND model=:model', [
        ':parent_id' =&gt; $this-&gt;id,
        ':model' =&gt; 'common\models\YourModel',
       ])
      -&gt;orderBy('created_at')
      -&gt;all();
  }
</pre>

<code>acceptFileTypes</code> ('image\\/[jpeg|jpg|png|gif]')

<code>minFileSize</code> (0.1Mb)

<code>maxFileSize</code> (2Mb)

<code>maxFiles</code> (0 - any amount)

<code>secure</code> (true)
Ordinary extension require user authorization, but verification may be switched off.
