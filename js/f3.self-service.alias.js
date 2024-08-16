$(document).ready(function() {
	$('#members').DataTable( {
		"order": [[ 1, "asc" ]],
        "paging": false,
        "scrollY": 500,
        "searching": true,
		"aoColumns": [
            { "orderSequence": [ "desc", "asc" ] },
            { "orderSequence": [ "desc", "asc" ] },
        ]
    } );
});