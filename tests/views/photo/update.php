<?php
/* @var $this yii\web\View */
/* @var $model tests\models\Photo */

use sergmoro1\uploader\widgets\Uploader;

$this->title = Yii::t('app', 'Update');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Photo'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->category;
?>

<div class="photo-update">

	<?= Uploader::widget([
		'model'         => $model,
		'secure'        => false,
		'appendixView'  => '/photo/appendix',
        'limit'         => 2,
	]) ?>

</div>
