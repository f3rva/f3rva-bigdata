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

function exit_error($status, $code, $message) {
	error_log('error: (' . $status . ') - ' . $code . ': ' . $message);
	http_response_code($status);
	echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
	exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	init_response();
	
	$startDate = $_REQUEST['startDate'];
	$numDays = $_REQUEST['numberOfDays'];
	
	$workoutService = new WorkoutService();
	$workouts = $workoutService->getWorkouts($startDate, $numDays);

	echo json_encode(array_values($workouts), JSON_UNESCAPED_SLASHES);
}
?>