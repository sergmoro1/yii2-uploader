<script>
var editLine = editLine || {};
editLine.start = function() {
	// Re-declare defaults
	editLine.data = function(file_id) {
		var row_id = '#row-' + file_id;
		var description = $(row_id + ' #description #edit').val();
		var when = $(row_id + ' #when #edit #when_0').prop('checked');
		return {
			'file_id': file_id,
			'defs': '{"description": "' + description + '", "when": ' + (when ? 0 : 1) + '}'
		}
	}
	editLine.addField(false, {
		name: 'when',
		save: function(id, save) {
			var edit = $(id + ' #edit #when_0');
			var span = $(id + ' #span');
			if(save)
				// copy radio value to <td/> tag
				$(id).text(edit.prop('checked') ? 'before' : 'after');
			else
				// copy span value to <td/> tag
				$(id).text(span.text());
			// remove all service fields
			edit.remove();
			span.remove();
		},
		default: function(obj) {
			obj.text('before');
		},
		add: function(obj) {
			var t = obj.text().trim();
			obj.text('');
			// make radio 
			obj.append($('<span/>')
				.prop('id', 'edit')
				.append($('<label/>')
					.addClass('radio')
					.append($('<input/>')
						.prop('id', 'when_0')
						.prop('value', '0')
						.prop('type', 'radio')
						.prop('name', 'when')
						.prop('checked', (t == 'before' ? 'checked' : ''))
					)
					.append($('<label/>')
						.prop('for', 'when_0')
						.text('before')
					)
				)
				.append($('<label/>')
					.addClass('radio')
					.append($('<input/>')
						.prop('id', 'when_1')
						.prop('value', '1')
						.prop('type', 'radio')
						.prop('name', 'when')
						.prop('checked', (t == 'after' ? 'checked' : ''))
					)
					.append($('<label/>')
						.prop('for', 'when_1')
						.text('after')
					)
				)
			);

			// hide old value
			obj.append($('<span/>')
				.prop('id', 'span')
				.text(t)
				.hide()
			);
		}
	});
}
</script>

<?php
/* @var $this yii\web\View */
/* @var $model sample\models\Stout */

use sergmoro1\uploader\widgets\Byone;

?>

<div class="stout-update">

	<?= Byone::widget([
		'model' => $model,
		'secure' => false,
		'minFileSize' => 0.02,
		'maxFiles' => 4,
		'appendixView' => '/stout/appendix',
		'cropAllowed' => true,
		'draggable' => true,
	]) ?>

</div>
