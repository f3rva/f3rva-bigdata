$(document).ready(function() {
  const $container = $('#workouts');
  $container.infiniteScroll({
    path: function() {
			var maxDate = getOldestDate();
			var numberOfDays = 5;
      return `/api/v1/getWorkouts.php?startDate=${maxDate}&numberOfDays=${numberOfDays}`;
    },
    responseBody: 'json',
    status: '.loading',
    history: false
  });

  $container.on('load.infiniteScroll', function(event, response) {
		if (response.length > 0) {
			var allRows = '';
			$.each(response, function(key, value) {
				var row = getWorkoutRow(key, value);
				allRows += row;
			});

			let $items = $(allRows);
			$container.infiniteScroll('appendItems', $items);
		};
  });
});

function getWorkoutRow(k, v) {
	// start of row
	var row = '<tr>';
	// column 1
	row += '<td>' + v.workoutDate + '</td>';
	// column 2
	row += '<td><a href="' + v.backblastUrl + '" target="_blank">' + v.title + '</a></td>';
	// column 3
	var aoRow = '<td><ul class="list-unstyled">';
	$.each(v.ao, function (k, v) {
		aoRow += '<li><a href="/ao/detail.php?id=' + v.id + '">' + v.description + '</a></li>';
	});
	aoRow += '</ul></td>';
	row += aoRow;
	// column 4
	var qRow = '<td><ul class="list-unstyled">';
	$.each(v.q, function (k, v) {
		qRow += '<li><a href="/member/detail.php?id=' + v.memberId + '">' + v.f3Name + '</a></li>';
	});
	qRow += '</ul></td>';
	row += qRow;
	// column 5
	row += '<td><a href="/workout/detail.php?id=' + v.workoutId + '">' + v.paxCount + '</a></td>';
	// end of row
	row += '</tr>';

	return row;
}

function getOldestDate() {
	var lastDate = $('#workouts tr:last td:first')[0].innerText;
	var date = new Date(lastDate.split('-')[0], lastDate.split('-')[1] - 1, lastDate.split('-')[2]);
	date.setDate(date.getDate() - 1);
	var previousDateStr = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
	
	return previousDateStr;
}