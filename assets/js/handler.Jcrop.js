/* 
 * jQuery jCrop event handler
 * 
 * @author sergmoro1@ya.ru
 * @license - MIT
 */

var jcrop_api;
var cropBox;

$(window).on('load', function() {
	cropBox = $("#cropBox");
}

/*
 * Delete file and no more needed field and clear cropBox properties.
 */
function deleteDestroy()
{
	var input = $('#coords #file_id'); 
	var file_id = input.val();
	$.ajax({
		url: editLine.options.btns.delete.action,
		type: 'POST',
		data: {file_id: file_id},
		success: function(respond, status, jqXHR) {
			jcrop_api.destroy(); 
			input.remove(); 
			cropBox.prop('src', ''); cropBox.attr('style', '');
		}
	});
}

/*
 * Save changed coordinates in form variables.
 * @param object c - new coordinates
 */
function showCoords(c)
{
	$('#coords #x').val(c.x);
	$('#coords #y').val(c.y);
	$('#coords #x2').val(c.x2);
	$('#coords #y2').val(c.y2);
	$('#coords #w').val(c.w);
	$('#coords #h').val(c.h);
};

/*
 * Ajax call for server image cropping.
 */
function cropCoords() {
	$.ajax({
		method: 'GET',
		url: editLine.options.btns.crop.action,
		data: {
			file_id: $('#coords #file_id').val(),
			x: $('#coords #x').val(),
			y: $('#coords #y').val(),
			w: $('#coords #w').val(),
			h: $('#coords #h').val()
		},
		success: function(respond, status, jqXHR) {
			// clearing
			jcrop_api.destroy();
			$('#coords #file_id').remove();
			cropBox.prop('src', ''); cropBox.attr('style', '');
			// JSON encode
			var data =  JSON.parse(respond);
			// add new line to image table
			var file = data.files[0];
			if(file.url) {
				editLine.addThumbnail(file);
				editLine.addFieldsAndButtons(file.id);
			}
		},
		error: function(jqXHR, status, errorThrown) {
			alert(status);
		}
	});
}
