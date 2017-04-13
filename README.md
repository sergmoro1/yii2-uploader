<h1>Yii2 module for files|images upload</h1>

<h2>Demo</h2>
<a href='http://sample.vorst.ru/photo/index'>Photos by categories</a>. (when editing) <br>
<a href='http://sample.vorst.ru/stout/index'>Photo gallery "before" & "after"</a> (similarly).

<h2>Advantages</h2>

If the model needs files, you don't need:
<ul>
  <li>define attribute <code>file</code> in a model,</li>
  <li>to process the result of filling the form,</li>
  <li>to come up with where save files,</li>
</ul>

You can:
<ul>
  <li>you can add a description to the file,</li>
  <li>delete no needed files,</li>
  <li>define sizes to compress images,</li>
  <li>crop images,</li>
  <li>swap file rows by mouse.</li>
</ul>

<h2>How it's made?</h2>

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
      // only if rows should be draggable & sortable
      -&gt;orderBy('created_at')
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
&lt;span id='description'&gt;
	&lt;?php echo isset($file-&gt;vars-&gt;description) ? $file-&gt;vars-&gt;description : ''; ?&gt;
&lt;/span&gt;
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
