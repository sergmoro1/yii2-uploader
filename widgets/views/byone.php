<?php
/**
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - GPL
 * 
 * View for upload buttons and results of uploading.
 */

use yii\helpers\Html;
use models\OneFile;
?>

<?php if(!$model->id): ?>
	<label class="control-label">
		<?= $btns['choose']['label']; ?>
	</label>
	<p><?= \Yii::t('byone', 'After saving you will be able to upload images') ?></p>
<?php else: ?>

<?php if($cropAllowed): ?>

<!-- Modal window for image and coordinates of cropping -->
<div id="imagePreview" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
	<div class="modal-dialog" style='width:70%;'>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="deleteDestroy();">X</button>
				<h4 class="modal-title"><?= \Yii::t('app', 'Select the area'); ?></h4>
			</div>
			<div class="modal-body" align='middle' style='width:100%;'>
				<!-- Place for image -->
				<img id="cropBox">
				<!--
				Variables for saving of cropping coordinates.
				http://deepliquid.com/content/Jcrop.html
				-->
				<div id="coords" class="coords">
					<input type='hidden' size='4' id='x' name='x'>
					<input type='hidden' size='4' id='y' name='y'>
					<input type='hidden' size='4' id='x2' name='x2'>
					<input type='hidden' size='4' id='y2' name='y2'>
					<input type='hidden' size='4' id='w' name='w'>
					<input type='hidden' size='4' id='h' name='h'>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal" onclick="cropCoords();">
					<?= $btns['crop']['caption']; ?>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal" onclick="deleteDestroy();">
					<?= $btns['cancel']['caption']; ?>
				</button>
			</div>
		</div> <!-- / .modal-content -->
	</div> <!-- / .modal-dialog -->
</div> <!-- / #preview -->

<?php endif; ?>

<!-- Input button -->
<div class='container'>
	<div class="row">
		<?php if(isset($btns['choose']['label'])): ?>
		<label class="control-label" for='<?= $blueimp['name']; ?>'>
			<?= $btns['choose']['label']; ?>
		</label>
		<?php endif; ?>
		<div class='controls' <?= $draggable ? 'id="draggable"' : '' ?>>
			<!-- upload field -->
			<span class="<?= $btns['choose']['class']; ?> fileinput-button" id="fileinput-button">
				
				<?= $btns['choose']['caption']; ?>
				
				<input id="<?= $blueimp['name']; ?>" name="<?= $blueimp['name']; ?>" data-url="<?= $blueimp['url']; ?>" type="file">

			</span>
		</div> <!-- / .controls -->
	</div>
</div>

<!-- The global progress bar -->
<div id='bprogress' class='progress'>
	<div class="<?= $barClass; ?>"></div>
</div>

<!-- Uploaded files -->
<div class='col-sm-12'>
	<div class='row'>
		<!-- Container for the uploaded files -->
		<div id='bfiles'>
			
			<?php if($draggable): ?>
			<span class='draggable-zone text-right'>
				<?= Yii::t('byone', 'Swap') ?>
				<a href='#' title='<?= Yii::t('byone', 'Rows can be swapped - click, hold, drag.') ?>'>
					<span class='glyphicon glyphicon-question-sign'></span>
				</a>
			</span>
			<?php endif; ?>
			
			<!-- row for error messages -->
			<div class='help-block'></div>
			
			<ul class='table' <?= $draggable ? 'id="sortable"' : '' ?>>
			<!-- table with files/images already uploaded at the time the form load -->
			<?php foreach($model->$files as $i => $file): ?>
				<li id='row-<?php echo $file->id; ?>'>

					<!-- image -->
					<span width='<?= $model->sizes['thumb']['width'] ?>px'>
						<?php echo Html::img($model->getImage('thumb', $i));?>
					</span>
					
					<?php if($appendixView)
						echo $this->render($appendixView, [
							'file' => $file,
						]); 
					?>
					
					<!-- buttons -->
					<span id='buttons'>
						<?php if($appendixView): ?>
						<a id='btn-save' class='<?php echo $btns['save']['class']; ?>' style='display:none;' title='<?= Yii::t('byone', 'save'); ?>'>
							<?php echo $btns['save']['caption']; ?>
						</a>
						<a id='btn-cancel' class='<?php echo $btns['cancel']['class']; ?>' style='display:none;' title='<?= Yii::t('byone', 'cancel'); ?>'>
							<?php echo $btns['cancel']['caption']; ?>
						</a>
						<a id='btn-edit' class='<?php echo $btns['edit']['class']; ?>' title='<?= Yii::t('byone', 'edit'); ?>'>
							<?php echo $btns['edit']['caption']; ?>
						</a>
						<?php endif; ?>

						<a id='btn-delete' class='<?php echo $btns['delete']['class']; ?>' title='<?= Yii::t('byone', 'delete'); ?>'>
							<?php echo $btns['delete']['caption']; ?>
						</a>
					</span>

				</li> <!-- / #row-id -->
			<?php endforeach;?>
			</ul>
		</div>  <!-- / bfiles -->
	</div> <!-- / .row -->
</div>

<?php endif; ?>
