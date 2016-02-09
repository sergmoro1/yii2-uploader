<h1>Yii2 module for files|images upload</h1>
Информация обо всех загруженных файлах хранится в одной модели - OneFile.
Нет необходимости определять поле типа file в модели, которой нужны файлы.

Например, нужно, чтобы User мог загружать фотографии. 
Определим в модели <code>common/models/User</code>:

<pre>
...
use sergmoro1\uploader\FilePath;
use sergmoro1\uploader\models\OneFile;
...

class User extends ActiveRecord implements IdentityInterface
{
   ...
  public $sizes = [
    'original' =&gt; ['width' =&gt; 1600, 'height' =&gt; 900, 'catalog' =&gt; 'original'],
    'main' =&gt; ['width' =&gt; 400, 'height' =&gt; 400, 'catalog' =&gt; ''],
    'thumb' =&gt; ['width' =&gt; 90, 'height' =&gt; 90, 'catalog' =&gt; 'thumb'],
  ];

  public function behaviors()
  {
    return [
      'FilePath' =&gt; [
        'class' =&gt; FilePath::className(),
        'file_path' =&gt; '/files/user/',
      ]
    ];
  }

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

Для выполнения загрузки определите widget в форме или в любом другом представлении. 
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

<h2>Installation</h2>

В каталоге приложения на сервере или на localhost:

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
в уже выше упомянутом widget, нужно добавить параметр <code>appendeixView</code>:

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
Если Вам необходимо добавить поле, обратитесь к автору.
