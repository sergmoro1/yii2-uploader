/*
 * Make file lines draggable
 */
$( function() {
	var before, after;
	function getRowsIds() {
		var rows = $('#bfiles .table li'), a = []; i = 0;
		$.each(rows, function(index, row) {
			if(id = row.id.substr(4))
				a[i++] = id;
		});
		return JSON.stringify(a);
	}
	$( "#sortable" ).sortable({
		revert: true,
		start: function( event, ui ) {
			before = getRowsIds();
		},
		update: function( event, ui ) {
			after = getRowsIds();
			if(before != after) {
				// save new order
				$.ajax({
					method: "POST",
					url: editLine.options.btns.swap.action,
					data: {ids: after},
					success: function(id, status, xhr) {}
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
