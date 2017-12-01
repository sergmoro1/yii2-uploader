/*
 * @author - Sergey Morozov <sergmoro1@ya.ru>
 * @license - MIT
 * 
 * Edit-line manager for OneFile model.
 * This is a type of row container for file uploading info:
 * <div id='bfiles'>
 * <ul class='table'>
 * <li id='row_id'>
 * 	<span><img></span>
 * 	<span id='description'></span>
 * 	  ...
 *  <span id='buttons'>
 * 	  <button id='btn-edit' style='display:none;'>
 * 	  <button id='btn-cancel' style='display:none;'>
 * 	  <button id='btn-save'>
 * 	  <button id='btn-delete'>
 * 	</span>
 * </li>
 * </ul>
 * </div>
 */

var editLine = editLine || {
	start: function() {}
}
editLine.options = {};
editLine.defaultField = {
	name: 'description',
	placeholder: 'description'
};
editLine.fields = [];

/*
 * Aditional data for file upload action.
 * Standart fields - id and uploaded file short description.
 * @param integer file_id - the ID of the OneFile model
 */
editLine.data = function(file_id) {
	var row_id = '#row-' + file_id;
	var dflt = this.defaultField.name;
	return {
		'file_id': file_id,
		'defs': '{' + 
			'"' + dflt + '": "' + $(row_id + ' #' + dflt + ' #edit').val() + '"' + 
		'}'
	}
}
/*
 * Each file may be associated with fields like description.
 * For combining fields in a row add them on the head or tail of a list.
 * @param boolean tail - add field to the tail or head of the list.
 * @param object field
 */
editLine.addField = function(tail, field) {
	if(tail)
		editLine.fields.push(field);
	else {
		for(var i = editLine.fields.length; i>0; i--)
			editLine.fields[i] = editLine.fields[i-1];
		editLine.fields[0] = field;
	}
}
/*
 * User click save or cancel button.
 * @param integer file_id - the ID of the OneFile model
 * @param boolean save - user click save or cancel button
 */
editLine.off = function(file_id, save) {
	if(editLine.options.appendixView)
	{
		var row_id = '#row-' + file_id;
		for(var i=0; i < editLine.fields.length; i++) {
			var field = editLine.fields[i];
			field.save(row_id + ' #' + field.name, save);
		}
		$(row_id + ' #buttons #btn-save').hide();
		$(row_id + ' #buttons #btn-cancel').hide();
		$(row_id + ' #buttons #btn-edit').show();
		$(row_id + ' #buttons #btn-delete').show();
	}
}
/*
 * User upload a new file or click edit button.
 * @param integer file_id - the ID of the OneFile model
 */
editLine.on = function(file_id) {
	if(editLine.options.appendixView)
	{
		var row_id = '#row-' + file_id;
		for(var i=0; i < editLine.fields.length; i++) {
			var field = editLine.fields[i];
			field.add($(row_id + ' #' + field.name));
		}
		// focus on default field
		$(row_id + ' #' + editLine.defaultField.name + '#edit').focus();
		$(row_id + ' #buttons #btn-save').show();
		$(row_id + ' #buttons #btn-cancel').show();
		$(row_id + ' #buttons #btn-edit').hide();
		$(row_id + ' #buttons #btn-delete').hide();
	}
}
editLine.save = function(file_id) {
	$.ajax({
		type:'POST',
		url: editLine.options.btns.save.action,
		data: editLine.data(file_id),
		success: function(file_id, status, xhr){
			editLine.off(file_id, true);
		}
	});
}
/*
 * Four buttons can be shown - edit, delete, save, cancel.
 * @param integer file_id - the ID of the OneFile model
 */
editLine.addButtons = function(file_id) {
	var btns = $('#row-' + file_id + ' #buttons');
	if(editLine.options.appendixView)
	{
		btns
			// button save
			.append($('<a/>')
				.addClass(editLine.options.btns.save.class)
				.prop('id', 'btn-save')
				.html(editLine.options.btns.save.caption)
				.on('click', function(){
					editLine.save(file_id);
				})
			)
			// button cancel
			.append($('<a/>')
				.addClass(editLine.options.btns.cancel.class)
				.prop('id', 'btn-cancel')
				.html(editLine.options.btns.cancel.caption)
				.on('click', function(){
					editLine.off(file_id, false);
				})
			)
			// button edit
			.append($('<a/>')
				.addClass(editLine.options.btns.edit.class)
				.prop('id', 'btn-edit')
				.html(editLine.options.btns.edit.caption)
				.hide()
				.on('click', function(){
					editLine.on(file_id);
				})
			);
	}
	btns
		.append($('<a/>')
			.prop('id', 'btn-delete')
			.addClass(editLine.options.btns.delete.class)
			.html(editLine.options.btns.delete.caption)
			.hide()
			.on('click', function(){
				$.ajax({
					type: 'POST',
					url: editLine.options.btns.delete.action,
					data:{file_id: file_id},
					beforeSend: function(xhr){
						return confirm(editLine.options.btns.delete.question);
					},
					success: function(file_id, status, xhr){
						$('#row-' + file_id).remove();
					}
				});
			})
		);
	if(!editLine.options.appendixView)
		$('#row-' + file_id + ' #buttons #btn-delete').show();
}
/*
 * Delete file by id
 * @param integer file_id - the ID of the OneFile model
 */
editLine.delete = function(file_id) {
	$.ajax({
		url: editLine.options.btns.delete.action,
		type: 'POST',
		data: {file_id: file_id},
		beforeSend: function() {return confirm(editLine.options.btns.delete.question);},
		success: function(id, status, xhr) {$('#row-' + id).remove();}
	});
}
/*
 * Add new editable line after image|file upload.
 * @param object file - uploaded files info
 */
editLine.add = function(file){
	if(file.url) {
		// hide progress bar
		$('#bprogress').hide();
		// clean bar
		$('#bprogress .' + editLine.options.barClass).css('width', '0%');
		if(editLine.options.cropAllowed) {
			// set cropping parameters
			cropBox = $('#cropBox');
			cropBox.prop('src', file.url);
			var ww = $(window).width();
			var ar = editLine.options.aspectRatio;
			cropBox.Jcrop({
				boxWidth: ww*0.65,
				setSelect: [0, 0, ww, ww / ar], 
				aspectRatio: ar,
				minSize: [editLine.options.minW, editLine.options.minH], 
				onChange: showCoords,
				onSelect: showCoords
			},
				function(){
					jcrop_api = this;
				}
			);
			// save file_id
			$('#imagePreview #coords')
				.append($('<input/>').prop('type','hidden').prop('id','file_id').val(file.id));
			// show modal window
			$('#imagePreview').modal({backdrop: false});
		} else {
			// add new row and img to the table
			editLine.addThumbnail(file);
			// add fields and buttons
			editLine.addFieldsAndButtons(file.id);
		}
	} else if(file.error) {
		var error = $('<span/>').addClass('text-danger').text(file.error);
		$('#bfiles .new-row')
			.append('<br>')
			.append(error);
	}
}
/*
 * Add new editable line after image|file upload.
 * @param object file - uploaded files info
 */
editLine.addFieldsAndButtons = function(file_id) {
	if(editLine.options.appendixView)
	{
		var row = $('#bfiles .table #row-' + file_id + ' #buttons');
		for(var i = 0; i < editLine.fields.length; i++) {
			field = editLine.fields[i];
			row.before($('<span/>').prop('id', field.name));
		}
		var obj;
		// fill in defaults and make all fields editable
		for(var i = 0; i < editLine.fields.length; i++) {
			field = editLine.fields[i];
			obj = $('#row-' + file_id + ' #' + field.name);
			field.default(obj);
			field.add(obj);
		}
	}
	editLine.addButtons(file_id);
}
/*
 * Add new table record, image thumbnail and cell for buttons.
 * @param object file - uploaded file info
 */
editLine.addThumbnail = function(file){
	$('#bfiles .table')
		// file container
		.append($('<li/>').prop('id', 'row-' + file.id).addClass('file-row')
			// image
			.append($('<span/>')
				.append($('<img/>')
					.prop('src', file.thumbnailUrl)
					//.prop('align', 'left')
				)
			)
			// buttons cell
			.append($('<span/>')
				.prop('id', 'buttons')
			)
		);
}

/*
 * Buttons events.
 */
$( function() {
	function getRowId(obj) {
		return obj.closest('li').attr('id').substr(4);
	}
	$('#bfiles .table #buttons #btn-save').click(function() {
		editLine.save(getRowId($(this)));
	});
	$('#bfiles .table #buttons #btn-cancel').click(function() {
		editLine.off(getRowId($(this)), false);
	});
	$('#bfiles .table #buttons #btn-edit').click(function() {
		editLine.on(getRowId($(this)));
	});
	$('#bfiles .table #buttons #btn-delete').click(function() {
		editLine.delete(getRowId($(this)));
	});
} );

/*
 * Add default field.
 * In the same time this is an example how you can define new field.
 */
$(window).on('load', function() {

	editLine.addField(true, {
		name: editLine.defaultField.name,
		save: function(id, save) {
			var edit = $(id + ' #edit');
			var span = $(id + ' #span');
			if(save)
				// copy textarea value to <span/> tag
				$(id).text(edit.val());
			else
				// copy span value to <span/> tag
				$(id).text(span.text());
			// remove all service fields
			edit.remove();
			span.remove();
		},
		default: function(obj) {
			// empty by default
			obj.text('');
		},
		add: function(obj) {
			var t = obj.text().trim();
			obj.text('');
			// make textarea and fill in it
			obj.append($('<textarea/>')
				.prop('id', 'edit')
				.prop('placeholder', editLine.defaultField.placeholder)
				.prop('maxlength', '255')
				.text(t)
			);
			// hide old value
			obj.append($('<span/>')
				.prop('id', 'span')
				.text(t)
				.hide()
			);
		}
	});
	/* 
	 * You can define aditional fields in editLine_start() if needed. 
	 * Use editLine.addField() for each new field and re-declare editLine.data().
	 */
	editLine.start();
});
