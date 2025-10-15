<?php
if (!defined(constant_name: '__ROOT__')) {
	define(constant_name: '__ROOT__', value: dirname(path: dirname(path: dirname(path: __FILE__))));
}
require(__ROOT__ . '/service/WorkoutService.php');
require(__ROOT__ . '/util/Util.php');

use F3\Service\WorkoutService;
use F3\Util\Util;

header(header: 'Access-Control-Allow-Origin: ' . Util::retrieveAccessControlAllowOriginHeader());
header(header: 'Access-Control-Allow-Methods: GET');
header(header: 'Access-Control-Allow-Headers: Content-Type, Authorization');
header(header: 'Access-Control-Allow-Credentials: true');

function init_response(): void {
	// initialize
	header(header: 'Content-Type: application/json');
}

function validateInput($page, $resultsPerPage): void {
	// page and results per page must be positive integers
	if (!is_numeric(value: $page) || $page < 1 || (int)$page != $page) {
		exit_error(status: 400, code: 1006, message: 'Invalid page number');
	}
	if (!is_numeric(value: $resultsPerPage) || $resultsPerPage < 1 || (int)$resultsPerPage != $resultsPerPage) {
		exit_error(status: 400, code: 1007, message: 'Invalid results per page');
	}
}

function exit_error($status, $code, $message): never {
	error_log(message: 'error: (' . $status . ') - ' . $code . ': ' . $message);
	http_response_code(response_code: $status);
	echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
	exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	init_response();
	
	$page = $_REQUEST['page'] ?? 1;
	$resultsPerPage = $_REQUEST['results'] ?? 20;

	validateInput(page: $page, resultsPerPage: $resultsPerPage);
	
	$workoutService = new WorkoutService();
	$workouts = $workoutService->getRecentWorkouts(page: $page, pageSize: $resultsPerPage);

	if (is_null(value: $workouts) || empty($workouts)) {
		exit_error(status: 404, code: 1001, message: 'Workout not found');
	}

	echo json_encode(value: $workouts, flags: JSON_UNESCAPED_SLASHES);
}
