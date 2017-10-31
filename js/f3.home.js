$(document).ready(function() {

	var moreRecords = true;
	
	// Each time the user scrolls
	$(window).scroll(function(){
        if (moreRecords && $(window).scrollTop() == $(document).height() - $(window).height()) {
    		$('#loading').show();
        	setTimeout(loadWorkouts, 500);
		}
	});

	function loadWorkouts() {

		var maxDate = getOldestDate();
		$.ajax({
			url: '/api/v1/getWorkouts.php',
			dataType: 'json',
			data: { 
				startDate: maxDate, 
			    numberOfDays: 5
			},
			success: function(json) {
				if (json.length > 0) {
					$.each(json, function(k, v) {
						// start of row
						var row = $('<tr>');
						// column 1
						$(row).append('<td>' + v.workout.workoutDate + '</td>');
						// column 2
						$(row).append('<td><a href="' + v.workout.backblastUrl + '" target="_blank">' + v.workout.title + '</a></td>');
						// column 3
						var aoRow = '<td><ul class="list-unstyled">';
						$.each(v.workout.ao, function (k, v) {
							aoRow += '<li><a href="/ao/detail.php?id=' + k + '">' + v + '</a></li>';
						});
						aoRow += '</ul></td>';
						$(row).append(aoRow);
						// column 4
						var qRow = '<td><ul class="list-unstyled">';
						$.each(v.workout.q, function (k, v) {
							qRow += '<li><a href="/member/detail.php?id=' + k + '">' + v + '</a></li>';
						});
						qRow += '</ul></td>';
						$(row).append(qRow);
						// column 5
						$(row).append('<td><a href="/workout/detail.php?id=' + v.workout.workoutId + '">' + v.workout.paxCount + '</a></td>');
						// end of row
						$(row).append('</tr>');
				
						$('#workouts').append(row);
		
					});
				}
				else {
					moreRecords = false;
				}
				$('#loading').hide();
			}
		});
	}
});

function getOldestDate() {
	var lastDate = $('#workouts tr:last td:first')[0].innerText;
	var date = new Date(lastDate.split('-')[0], lastDate.split('-')[1] - 1, lastDate.split('-')[2]);
	date.setDate(date.getDate() - 1);
	var previousDateStr = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
	return previousDateStr;
}