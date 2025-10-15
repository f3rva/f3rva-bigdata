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

function exit_error($status, $code, $message): never {
	error_log(message: 'error: (' . $status . ') - ' . $code . ': ' . $message);
	http_response_code(response_code: $status);
	echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
	exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	init_response();
	
	$year = $_REQUEST['year'];
	$month = $_REQUEST['month'];
	$day = $_REQUEST['day'];
	$slug = $_REQUEST['slug'];
	
	// if any of these are null or empty, return with error
	if (is_null(value: $year) || $year === '' || 
			is_null(value: $month) || $month === '' || 
			is_null(value: $day) || $day === '' || 
			is_null(value: $slug) || $slug === '') {
		exit_error(status: 400, code: 1000, message: 'Invalid date or slug');
	}
	
	$workoutService = new WorkoutService();
	$workout = $workoutService->getWorkoutByDateAndSlug(year: $year, month: $month, day: $day, slug: $slug);

	if (is_null(value: $workout)) {
		exit_error(status: 404, code: 1001, message: 'Workout not found');
	}
	
	echo json_encode(value: $workout, flags: JSON_UNESCAPED_SLASHES);
}
