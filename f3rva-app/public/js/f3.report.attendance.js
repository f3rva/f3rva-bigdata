$(document).ready(function() {
	$('#attendance').DataTable( {
		"order": [[ 1, "desc" ]],
        "paging": false,
        "searching": false,
		"aoColumns": [
            null,
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] }
        ]
    } );
});