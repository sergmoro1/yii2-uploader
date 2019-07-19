/**
 * jQuery jCrop event handler.
 * 
 * @author Sergey Morozov <sergmoro1@ya.ru>
 * @see http://deepliquid.com/content/Jcrop.html
 */

var jcrop_api;
var cropBox;

$(document).ready(function() {
    cropBox = $("#cropBox");
});

/**
 * Save changed coordinates in form variables.
 * 
 * @param object new coordinates
 */
coordsChanged = function (c) {
    $('#coords #x').val(c.x);
    $('#coords #y').val(c.y);
    $('#coords #x2').val(c.x2);
    $('#coords #y2').val(c.y2);
    $('#coords #w').val(c.w);
    $('#coords #h').val(c.h);
};

/**
 * Server side image cropping.
 */
$(function () {
    $('#preview .btn-crop').click( function() {
        $.ajax({
            method: 'GET',
            url: editLine.options.btns.crop.action,
            data: {
                id: $('#preview .modal-footer .keeper').prop('id'),
                x: $('#coords #x').val(),
                y: $('#coords #y').val(),
                w: $('#coords #w').val(),
                h: $('#coords #h').val()
            },
            success: function(data) {
                if (data.sucess) {
                    // trying to replace thumbnail
                    var thumb = $(editLine.getRowIdBy(data.file.id)).find('img');
                    thumb
                        removeAttr('src').attr('src', data.file.path + data.file.catalog + data.file.name)
                        removeData('img').data('img', data.file.path + data.file.name);
                }
            },
            error: function(qXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            }
        });
    });
    $('#preview').on('hidden.bs.modal', function (e) {
        // delete cropping services data if it initiated before
        if (window.jcrop_api && $(this).find('.modal-footer .keeper').length > 0) {
            jcrop_api.destroy();
            $('#preview .modal-footer .keeper').remove();
        }
    });
});
