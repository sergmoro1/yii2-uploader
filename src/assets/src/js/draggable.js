/**
 * Make file lines draggable.
 * 
 * @author Sergey Morozov <sergmoro1@ya.ru>
 */
$( function() {
    var before, after;
    function getRowsIds() {
        var rows = $('#uploads .table li'), a = []; i = 0;
        $.each(rows, function(index, row) {
            if(id = row.id)
                a[i++] = id;
        });
        return a;
    }
    $( "#sortable" ).sortable({
        revert: true,
        start: function( event, ui ) {
            before = getRowsIds();
        },
        update: function( event, ui ) {
            after = getRowsIds(); 
            var i, swapped = false;
            for(i = 0; i < before.length; i++) {
                if(before[i] != after[i]) {
                    swapped = true;
                    break;
                }
            }
            if(swapped) {
                // save new order
                $.ajax({
                    method: "POST",
                    url: editLine.options.btns.swap.action,
                    data: {b: before[i], a: after[i]},
                    success: function(data, status, xhr) {},
                });
            }
        }
    });
    $( "#draggable" ).draggable({
        connectToSortable: "#sortable",
        helper: "clone",
        revert: "invalid"
    });
    $( "ul, li" ).disableSelection();
} );
