$(document).ready(function() {
	$('#primary').DataTable( {
		"order": [[ 1, "asc" ]],
        "paging": false,
        "scrollY": 650,
        "searching": true,
		"columns": [
            { orderable: false },
            null
        ]
    } );

	$('#alias').DataTable( {
		"order": [[ 1, "asc" ]],
        "paging": false,
        "scrollY": 650,
        "searching": true,
		"columns": [
            { orderable: false },
            null
        ]
    } );

    // when the primary radio button is clicked, set the memberId value to the selected member id
    $('input[name="primary"]').on('click', function() {
        var selectedValue = $(this).val();
        $('#primaryMemberId').val(selectedValue);
    });
      
    // when the alias radio button is clicked, set the associatedMemberId value to the selected member id
    $('input[name="alias"]').on('click', function() {
        var selectedValue = $(this).val();
        $('#aliasMemberId').val(selectedValue);
    });
});