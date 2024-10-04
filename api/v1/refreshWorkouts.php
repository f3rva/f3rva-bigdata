<?php
if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\WorkoutService;

function init_response() {
	// initialize
	header('Content-Type: application/json');
}

function validateInput() {
	// parse the input into json
	$jsonStr = file_get_contents('php://input');
	$json = json_decode($jsonStr);
	
	if ($json == null) {
		exit_error(400, 5400, "invalid input received: " . $jsonStr);
	}
	else if ($json->numDays > 10) {
		exit_error(400, 5410, "invalid number of days to refresh: " . $json->numDays);
	}
	
	return $json;
}

function exit_error($status, $code, $message) {
	error_log('error: (' . $status . ') - ' . $code . ': ' . $message);
	http_response_code($status);
	echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
	exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
	init_response();
	$data = validateInput();
	
	$workoutService = new WorkoutService();
	$workouts = $workoutService->refreshWorkouts($data->numDays);
	echo json_encode($workouts);
}
