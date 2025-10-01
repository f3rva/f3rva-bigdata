<?php
if (!defined(constant_name: '__ROOT__')) {
	define(constant_name: '__ROOT__', value: dirname(path: dirname(path: dirname(path: __FILE__))));
}
require(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\WorkoutService;

function init_response(): void {
	// initialize
	header(header: 'Content-Type: application/json');
}

function exit_error($status, $code, $message): never {
	error_log(message: 'error: (' . $status . ') - ' . $code . ': ' . $message);
	http_response_code(response_code: $status);
	echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
	exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	init_response();
	
	$workoutId = $_REQUEST['id'];
	
	$workoutService = new WorkoutService();
	$workout = $workoutService->getWorkout(workoutId: $workoutId);

	echo json_encode(value: $workout, flags: JSON_UNESCAPED_SLASHES);
}
