$(function() {
	$(".workout-container .actions .glyphicon-remove").click(function() {
		var row = $(this).closest("tr");
		var id = $(row).attr("id");
		var workoutId = id.substring(id.indexOf("-") + 1);
		console.log("remove clicked: " + workoutId);
		
		$.ajax({
			url: "/api/v1/deleteWorkout.php?workoutId=" + workoutId,
			type: "DELETE",
			success: function(result) {
				console.log("success: " + result);
				$(row).remove();
			},
			error: function(result, text, error) {
				console.log("error: " + result + ", text: " + text + ", error: " + error);
			}
		});
	});
	
	$(".workout-container .actions .glyphicon-refresh").click(function() {
		var row = $(this).closest("tr");
		var id = $(row).attr("id");
		var workoutId = id.substring(id.indexOf("-") + 1);
		console.log("refresh clicked: " + workoutId);
		$(row).fadeTo("slow", .25);
		
		$.ajax({
			url: "/api/v1/refreshWorkout.php",
			type: "PUT",
			processData: false,
			data: '{ "workoutId": ' + workoutId + ' }',
			success: function(result) {
				console.log("success: " + result);
			},
			error: function(result, text, error) {
				console.log("error: " + result + ", text: " + text + ", error: " + error);
			},
			complete: function(result) {
				$(row).fadeTo("slow", 1);
			},
		});
	});
});