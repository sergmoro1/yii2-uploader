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
      // only if rows should be draggable & sortable
      -&gt;orderBy('created_at')
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
&lt;span id='description'&gt;
	&lt;?php echo isset($file-&gt;vars-&gt;description) ? $file-&gt;vars-&gt;description : ''; ?&gt;
&lt;/span&gt;
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

