/**
 * Lines of additioanal fields manager.
 * This is a type of rows container for file info keep in sergmoro1\uploader\models\OneFile.
 * Any file can be asossiated with description or any other fields.
 * 
 * <div id='uploads'>
 *     <ul class='table'>
 *         <li id='123'>
 *             <span class='block'>
 *                 <img>
 *                 <a id='btn-crop'>
 *                 <a id='btn-view'>
 *             </span>
 *             <span id='description'></span>
 *             ...
 *             <span id='buttons'>
 *                 <a id='btn-edit'>
 *                 <a id='btn-cancel'>
 *                 <a id='btn-save'>
 *                 <a id='btn-delete'>
 *             </span>
 *         </li>
 *     </ul>
 * </div>
 * 
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */

var editLine = editLine || {
    start: function() {},
    options: {},
    defaultField: {
        name: 'description',
        placeholder: 'description'
    },
    fields: [],
    prefix: '#uploads .table',
    id: null,
    preview: null
};

editLine.getRowId = function() {
    return this.prefix + ' #' + this.id; 
}
editLine.getRowIdBy = function(id) {
    return this.prefix + ' #' + id; 
}

/**
 * Aditional data for file upload action.
 * Standart fields - id and uploaded file short description.
 * 
 * @param integer file_id of the model sergmoro1\uploader\models\OneFile
 * @return array {"file_id": "123", "description": "bla bla"}
 */
editLine.data = function() {
    var dflt = this.defaultField.name;
    return {
        'file_id': this.id,
        'defs': '{' + '"' + dflt + '": "' + $(this.getRowId()).find('#' + dflt).find('#edit').val() + '"' + '}'
    }
}

/**
 * Each file may be associated with fields like description.
 * For combining fields in a row add them on the head or tail of a fields list.
 * 
 * @param boolean tail add field to the tail or head of the list.
 * @param object field
 */
editLine.addField = function(tail, field) {
    var that = this;
    if(tail)
        that.fields.push(field);
    else {
        for(var i = that.fields.length; i>0; i--)
            that.fields[i] = that.fields[i-1];
        that.fields[0] = field;
    }
}

/**
 * Show fields values when
 * user click save or cancel button.
 * 
 * @param integer file_id ID of the OneFile model
 * @param boolean save user click save or cancel button
 */
editLine.off = function(file_id, save) {
    var that = this;
    if(that.options.appendixView)
    {
        var row_id = this.getRowIdBy(file_id);
        var field;
        for(var i=0; i < that.fields.length; i++) {
            field = that.fields[i];
            field.save(row_id + ' #' + field.name, save);
        }
        var buttons = $(row_id).find('#buttons');
        buttons.find('#btn-save').addClass('hidden');
        buttons.find('#btn-cancel').addClass('hidden');
        buttons.find('#btn-edit').removeClass('hidden');
        buttons.find('#btn-delete').removeClass('hidden');
    }
}

/**
 * Make fields editable when
 * user upload a new file or click edit button.
 * 
 * @param integer file_id ID of the OneFile model
 */
editLine.on = function(file_id) {
    var that = this;
    if(that.options.appendixView)
    {
        var row = $(this.getRowIdBy(file_id));
        for(var i=0; i < that.fields.length; i++) {
            var field = that.fields[i];
            field.add(row.find('#' + field.name));
        }
        // focus on default field
        row.find('#' + that.defaultField.name + '#edit').focus();
        var buttons = row.find('#buttons')
        buttons.find('#btn-save').removeClass('hidden');
        buttons.find('#btn-cancel').removeClass('hidden');
        buttons.find('#btn-edit').addClass('hidden');
        buttons.find('#btn-delete').addClass('hidden');
    }
}

/**
 * Save editLine values.
 */
editLine.save = function(file_id) {
    var that = this;
    that.id = file_id;
    $.ajax({
        type:'POST',
        url: that.options.btns.save.action,
        data: that.data(),
        success: function(data){ if(data.sucess) that.off(data.file_id, true); }
    });
}

/**
 * Add new editable line.
 * 
 * @param integer file_id
 */
editLine.addFields = function() {
    var that = this;
    if(that.options.appendixView)
    {
        var rowId = this.getRowId();
        var buttons = $(rowId).find('#buttons');
        // add fields
        for(var i = 0; i < that.fields.length; i++) {
            field = that.fields[i];
            buttons.before($('<span/>').prop('id', field.name));
        }
        var row = $(rowId);
        var obj;
        // fill in defaults and make all fields editable
        for(var i = 0; i < that.fields.length; i++) {
            field = that.fields[i];
            obj = row.find('#' + field.name);
            field.default(obj);
            field.add(obj);
        }
    }
}

/**
 * Add buttons to edit line.
 * 
 * @param boolean is_image if file is an image add view and, may be, crop buttons
 */
editLine.addButtons = function(is_image) {
    var that = this;
    var block = $(that.getRowId()).find('.block');
    var buttons = $(that.getRowId()).find('#buttons');
    // Buttons will placed if additional fields exist
    if(that.options.appendixView)
    {
        buttons
            // button save
            .append($('<a/>')
                .addClass(that.options.btns.save.class)
                .prop('id', 'btn-save')
                .html(that.options.btns.save.caption)
                .on('click', function(){
                    that.save($(this).closest('li').prop('id'));
                })
            )
            // button cancel
            .append($('<a/>')
                .addClass(that.options.btns.cancel.class)
                .prop('id', 'btn-cancel')
                .html(that.options.btns.cancel.caption)
                .on('click', function(){
                    that.off($(this).closest('li').prop('id'), false);
                })
            )
            // button edit
            .append($('<a/>')
                .addClass(that.options.btns.edit.class)
                .prop('id', 'btn-edit')
                .html(that.options.btns.edit.caption)
                .addClass('hidden')
                .on('click', function(){
                    that.on($(this).closest('li').prop('id'));
                })
            );
    }
    if (is_image) {
        var img_tool = $('<div>').addClass('img-tool');
        if (that.options.cropAllowed) {
            // cropping
            img_tool
                .append($('<a/>')
                    .prop('id', 'btn-crop')
                    .addClass(that.options.btns.crop.class)
                    .html(that.options.btns.crop.caption)
                    .on('click', function() {
                        that.crop($(this).closest('li').prop('id'));
                    })
                );
        }
        // view
        img_tool
            .append($('<a/>')
                .prop('id', 'btn-view')
                .addClass(that.options.btns.view.class)
                .html(that.options.btns.view.caption)
                .on('click', function() {
                    that.view($(this).closest('li').prop('id'));
                })
            );
        block.append(img_tool);
    }
    // button delete should be placed in any case
    buttons
        .append($('<a/>')
            .prop('id', 'btn-delete')
            .addClass(that.options.btns.delete.class)
            .html(that.options.btns.delete.caption)
            .addClass('hidden')
            .on('click', function() {
                that.delete($(this).closest('li').prop('id'));
            })
        );
    if(!that.options.appendixView)
        buttons.find('#btn-delete').removeClass('hidden');
}

/**
 * Add new editable line after image|file upload.
 * 
 * @param object file - uploaded files info
 */
editLine.add = function(li, id, is_image) {
    var that = this;
    // set internal id
    that.id = id;
    // set tag id
    li.prop('id', id);
    // add buttons area
    var buttons = $('<span/>').prop('id', 'buttons');
    li.append(buttons);
    // fill in file line with fields and action buttons
    that.addFields();
    that.addButtons(is_image);
}

/**
 * View file image in a modal window.
 * 
 * @param integer file_id the ID of the OneFile model
 * @param boolean popup variant for cropping
 */
editLine.view = function(file_id) {
    var that = this;
    // set up image
    var img = that.preview.find('img');
    img.attr('src', $(that.getRowIdBy(file_id)).find('img').data('img'));
    // activate button
    that.preview.find('.btn-crop').hide();

    that.preview.modal({backdrop: false});
}

/**
 * Crop file image in a modal window.
 * 
 * @param integer file_id the ID of the OneFile model
 * @param boolean popup variant for cropping
 */
editLine.crop = function(file_id) {
    var that = this;
    // set up image
    var img = that.preview.find('img');
    img.attr('src', $(that.getRowIdBy(file_id)).find('img').data('img'));
    // keep file_id
    var keeper = $('<span/>').addClass('keeper').prop('id', file_id);
    that.preview.find('.modal-footer').append(keeper);
    // activate button
    that.preview.find('.btn-crop').show();
    
    if (that.options.cropAllowed) {
        // set cropping parameters
        var ww = $(window).width();
        var ar = that.options.aspectRatio;
        cropBox.Jcrop({
            boxWidth: ww,
            setSelect: [0, 0, ww, ww / ar], 
            aspectRatio: ar,
            minSize: [that.options.minW, that.options.minH], 
            onChange: coordsChanged,
            onSelect: coordsChanged
        },
            function(){
                jcrop_api = this;
            }
        );
        // make popup some wider or vise versa
        that.preview.find('.modal-content').width(that.options.popUpWidth);
        // show popup
        that.preview.modal({backdrop: false});
    }
}

/**
 * Delete file by ID.
 * 
 * @param integer file_id the ID of the OneFile model
 */
editLine.delete = function(file_id) {
    var that = this;
    $.ajax({
        url: that.options.btns.delete.action,
        type: 'POST',
        data: {file_id: file_id},
        beforeSend: function() { return confirm(that.options.btns.delete.question); },
        success: function(data) { if (data.success) $(that.getRowIdBy(data.file_id)).remove(); }
    });
}

/**
 * Set buttons events.
 */
$(function() {
    function getId(obj) {
        return obj.closest('li').prop('id');
    }
    $(editLine.prefix + ' #buttons #btn-save').click(function() {
        editLine.save(getId($(this)));
    });
    $(editLine.prefix + ' #buttons #btn-cancel').click(function() {
        editLine.off(getId($(this)), false);
    });
    $(editLine.prefix + ' #buttons #btn-edit').click(function() {
        editLine.on(getId($(this)));
    });
    $(editLine.prefix + ' #buttons #btn-delete').click(function() {
        editLine.delete(getId($(this)));
    });
    $(editLine.prefix + ' .img-tool #btn-view').click(function() {
        editLine.view(getId($(this)));
    });
    $(editLine.prefix + ' .img-tool #btn-crop').click(function() {
        editLine.crop(getId($(this)));
    });
});

/**
 * Add default field.
 * In the same time this is an example how you can define new field.
 * Aditional fields can be added in editLine_start() if needed. 
 */
$(document).ready(function() {

    editLine.addField(true, {
        name: editLine.defaultField.name,
        save: function(id, save) {
            var obj = $(id);
            var edit = obj.find('#edit');
            var span = obj.find('#span');
            if(save)
                // copy textarea value to <span/> tag
                obj.text(edit.val());
            else
                // copy span value to <span/> tag
                obj.text(span.text());
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
    editLine.preview = $('#preview');
    editLine.start();
});
