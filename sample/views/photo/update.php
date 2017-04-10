<?php
/* @var $this yii\web\View */
/* @var $model sample\models\Photo */

use sergmoro1\uploader\widgets\Byone;

?>

<div class="photo-update">

	<?= Byone::widget([
		'model' => $model,
		'secure' => false,
		'minFileSize' => 0.02,
		'maxFiles' => 5,
		'appendixView' => '/photo/appendix',
		'cropAllowed' => true,
		'draggable' => true,
	]) ?>

</div>
