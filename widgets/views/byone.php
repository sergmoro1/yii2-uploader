<?php
/**
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - GPL
 * 
 * View for upload buttons and results of uploading.
 */

use Yii;
use yii\helpers\Html;
use models\OneFile;
?>

<?php if(!$model->id): ?>
	<label class="control-label">
		<?= $btns['choose']['label']; ?>
	</label>
	<p><?= Yii::t('byone', 'After saving you will be able to upload images') ?></p>
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
		<div class='controls'>
			<!-- upload field -->
			<span class="<?= $btns['choose']['class']; ?> fileinput-button" id="fileinput-button">
				
				<?= $btns['choose']['caption']; ?>
				
				<input id="<?= $blueimp['name']; ?>" name="<?= $blueimp['name']; ?>" data-url="<?= $blueimp['url']; ?>" type="file">

			</span>
		</div> <!-- / .controls -->
	</div>
</div>

<!-- The global progress bar -->
<div id="bprogress" class="progress" style="display:none; margin-top:20px;">
	<div class="<?= $barClass; ?>"></div>
</div>

<!-- Uploaded files -->
<div class='container'>
	<div class='row'>
		<!-- Container for the uploaded files -->
		<div id="bfiles">
			
			<!-- row for error messages -->
			<div class='help-block'></div>

			<div class='col-sm-12'>
			<div class='row'>
			<table class='file-table'>
			<!-- table with files/images already uploaded at the time the form load -->
			<?php foreach($model->$files as $i => $file): ?>
				<tr id='row-<?php echo $file->id; ?>'>

					<!-- image -->
					<td>
						<?php echo Html::img($model->getImage('thumb', $i), [
							'align' => 'left',
							'width' => $model->sizes['thumb']['width'] . 'px',
						]);?>
					</td>
					
					<?php if($appendixView)
						echo $this->render($appendixView, [
							'file' => $file,
						]); 
					?>
					
					<!-- buttons -->
					<td id='buttons'>
						<?php if($appendixView): ?>
						<a id='btn-save' class='<?php echo $btns['save']['class']; ?>' onclick="editLine.save(<?php echo $file->id; ?>);" style='display:none;'>
							<?php echo $btns['save']['caption']; ?>
						</a>
						<a id='btn-cancel' class='<?php echo $btns['cancel']['class']; ?>' onclick="editLine.off(<?php echo $file->id; ?>, false);" style='display:none;'>
							<?php echo $btns['cancel']['caption']; ?>
						</a>
						<a id='btn-edit' class='<?php echo $btns['edit']['class']; ?>' onclick="editLine.on(<?php echo $file->id; ?>);" >
							<?php echo $btns['edit']['caption']; ?>
						</a>
						<?php endif; ?>

						<a id='btn-delete' class='<?php echo $btns['delete']['class']; ?>' onclick="editLine.delete(<?php echo $file->id; ?>);">
							<?php echo $btns['delete']['caption']; ?>
						</a>
					</td>

				</tr> <!-- / .row .file-row -->
			<?php endforeach;?>
			</table>
			</div> <!-- / .row -->
			</div> <!-- / .col -->
		</div>  <!-- / bfiles -->
	</div> <!-- / .row -->
</div>

<?php endif; ?>
