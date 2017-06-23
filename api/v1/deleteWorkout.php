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
	$workoutId = $_REQUEST['workoutId'];
	
	if (empty($workoutId)) {
		exit_error(400, 5400, "invalid input received: " . $workoutId);
	}
	
	return $workoutId;
}

function exit_error($status, $code, $message) {
    error_log('error: (' . $status . ') - ' . $code . ': ' . $message);
    http_response_code($status);
    echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {	
	init_response();
	$workoutId = validateInput();
	
	$workoutService = new WorkoutService();
	$workout = $workoutService->deleteWorkout($workoutId);
	
	echo json_encode(array(), JSON_FORCE_OBJECT);
}
?>