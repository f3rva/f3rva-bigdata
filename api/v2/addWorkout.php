<?php 
if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\WorkoutService;

function init_response(): void {
    // initialize
    header(header: 'Content-Type: application/json');
}

function validateInput(): mixed {
    // parse the input into json
    $jsonStr = file_get_contents(filename: 'php://input');
    $json = json_decode(json: $jsonStr);

    if ($json == null) {
        exit_error(status: 400, code: 5400, message: "invalid input received: " . $jsonStr);
    }
    
    return $json;
}

function exit_error($status, $code, $message): never {
    error_log(message: 'error: (' . $status . ') - ' . $code . ': ' . $message);
    http_response_code(response_code: $status);
    echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	init_response();
	$data = validateInput();
	error_log(message: 'request: ' . json_encode(value: $data));
	
	$workoutService = new WorkoutService();
	$workout = $workoutService->addWorkoutWithData(data: $data);

    echo '{ "id": ' . $workout . ' }';
}
