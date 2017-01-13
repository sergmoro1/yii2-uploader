/* 
 * jQueryFileUpload event handler
 * 
 * @author sergmoro1@ya.ru
 * @license - MIT
 */

$(function () {
	// just after choice a file
	$('#fileupload').bind('fileuploadchange', 
	function (e, data) {
		var hp = $('#bfiles .help-block');
		// clear previous error messages
		hp.text('');
		var errors = 0;
		var minFS = $(this).fileupload('option', 'minFileSize');
		var maxFS = $(this).fileupload('option', 'maxFileSize');
		var file = data.files[0];
		// check count uploaded files
		var maxFiles = $(this).fileupload('option', 'maxFiles');
		var files_uploaded = $('#bfiles table.table tr').length;
		if(maxFiles != 0 && files_uploaded >= maxFiles){
			hp.append($('<p/>')
				.addClass('text-danger')
				.text(editLine.options.errors.maxFiles)
			); errors++;
		}
		// check file size
		if(file.size < (minFS * 1048576) || file.size > (maxFS * 1048576)){
			hp.append($('<p/>')
				.addClass('text-danger')
				.text(editLine.options.errors.size)
			); errors++;
		}
		// check file type
		var re = new RegExp(editLine.options.acceptFileTypes, 'i');
		if(!re.test(file.type)){
			hp.append($('<p/>')
				.addClass('text-danger')
				.text(editLine.options.errors.type)
			);  errors++;
		}
		if(errors){
			// show file name if errors
			hp.prepend($('<p/>').text(file.name));
			return false;
		} else {
			// show progress bar
			$('#bprogress').show();
		}
	});

	// change progress bar
	$('#fileupload').bind('fileuploadprogressall', 
	function (e, data) {
		var progress = parseInt(data.loaded / data.total * 100, 10);
		$('#bprogress .' + editLine.options.barClass).css('width', progress + '%');
	})

	// all done
	$('#fileupload').bind('fileuploaddone', 
	function (e, data) {
		editLine.add(data.result.files[0]);
	})

	// fail
	$('#fileupload').bind('fileuploadfail', 
	function (e, data) {
		alert(data.errorThrown + "\n" + data.jqXHR.responseText);
	})
})
