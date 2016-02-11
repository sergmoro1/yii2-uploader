<a href='#en_readme_md'>readme.md in English</a>

<h1>Yii2 модуль для загрузки файлов|изображений на сервер</h1>

Информация обо всех загруженных файлах хранится в одной таблице - onefile.
Нет необходимости определять поле типа <code>file</code> в модели, которой нужны файлы.

Файлы загружаются и хранятся в каталоге <code>frontend/web/files</code>.
Для каждой модели возможен свой подкаталог: <code>frontend/web/files/user</code> или <code>frontend/web/files/post</code>.
В подкаталоге файлы раскладываются по пользователям или постам и размерам:
<code>frontend/web/files/user</code> или <code>frontend/web/files/post/5/thumb</code>
<code>frontend/web/files/user</code> или <code>frontend/web/files/post/5</code>

<h2>Например</h2>
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
    return OneFile::find(['parent_id' =&gt; 'id'])
      -&gt;where('model=:model', [':model' =&gt; 'common\models\User'])
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

Может быть загружено любое количество файлов.

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

<h1><a name='en_readme_md'>Yii2 module for files|images upload</a></h1>

Information about all uploaded files are stored in one table - onefile. 
There is no need to define a field of type <code>file</code> in the model, which need files.

The files are uploaded and stored in the directory <code>frontend/web/files</code>. 
For each model subdirectories are possible: <code>frontend/web/files/user</code> or <code>frontend/web/files/uploads</code>. 
In the subdirectory the files are arranged by users or posts and size:
<code>frontend/web/files/user</code> или <code>frontend/web/files/post/5/thumb</code>
<code>frontend/web/files/user</code> или <code>frontend/web/files/post/5</code>

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
    return OneFile::find(['parent_id' =&gt; 'id'])
      -&gt;where('model=:model', [':model' =&gt; 'common\models\User'])
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

May be uploaded any amount of files.

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
