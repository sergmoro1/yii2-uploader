/**
 * SimpleUpload.js handler.
 * UploadOptions should be defined before handler.
 * 
 * @author Sergey Morozov <sergmoro1@ya.ru>
 * @see http://simpleupload.michaelcbrook.com/
 */
$(document).ready(function() {
	$('input[name="' + uploadOptions.name + '"]').change(function() {
		$(this).simpleUpload(uploadOptions.url, {

			allowedTypes: uploadOptions.allowedTypes,
			maxFileSize:  uploadOptions.maxFileSize,
            data:         uploadOptions.data,
            expect:       'json',
            limit:        uploadOptions.limit,
            
            start: function(file){
                // add new line
                this.li = $('<li/>');
                // add block
                this.block = $('<span/>').addClass('block');
                // add progressbar to a block
                this.progressBar = $('<span/>').addClass('progressBar');
                this.li.append(this.block.append(this.progressBar));
                // add line to table
                var table = $(editLine.prefix);
                table.append(this.li);
                // clear prev errors
                table.find('li.error').remove();
            },

            progress: function(progress){
                this.progressBar.width(progress + "%");
            },

            success: function(data){
                // progressbar not needed
                this.progressBar.remove();
                if (data.success) {
                    // if uploaded file is image then add img tag
                    // else fill block with extension
                    var fileBlock = data.file.is_image
                        ? $('<img/>')
                            .attr('src', data.file.path + data.file.catalog + data.file.name)
                            .data('img', data.file.path + data.file.name)
                        : $('<span/>').addClass('extension').text(data.file.ext);
                    this.block.append(fileBlock);
                    // add new line to the table
                    editLine.add(this.li, data.file.id, data.file.is_image);
                } else {
                    // error
                    // add block with file extension
                    this.block.addClass('extension').text(data.ext);
                    // and message
                    var message = $('<span/>').addClass('message').text(data.message);
                    // add line with error to the table
                    this.li.addClass('error').append(message);
                }
            },

            error: function(error){
                this.progressBar.remove();
                this.li.addClass('error');
                this.block.addClass('message').text(error.message);
            }
        });
    });
});
