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

function validateInput($year, $month, $day, $page, $resultsPerPage): void {
	// validate year, month, day
	if (is_null(value: $year) || $year === '') {
		exit_error(status: 400, code: 1003, message: 'Invalid year');
	}
	if (!is_null(value: $month) && ($month < 1 || $month > 12)) {
		exit_error(status: 400, code: 1004, message: 'Invalid month');
	}
	if (!is_null(value: $day) && ($day < 1 || $day > 31)) {
		exit_error(status: 400, code: 1005, message: 'Invalid day');
	}

	// page and results per page must be positive integers
	if (!is_numeric(value: $page) || $page < 1 || (int)$page != $page) {
		exit_error(status: 400, code: 1006, message: 'Invalid page number');
	}
	if (!is_numeric(value: $resultsPerPage) || $resultsPerPage < 1 || (int)$resultsPerPage != $resultsPerPage) {
		exit_error(status: 400, code: 1007, message: 'Invalid results per page');
	}
}

function isYearSearch($year, $month, $day): bool {
	return !is_null(value: $year) && is_null(value: $month) && is_null(value: $day);
}

function isMonthSearch($year, $month, $day): bool {
	return !is_null(value: $year) && !is_null(value: $month) && is_null(value: $day);
}	

function isDaySearch($year, $month, $day): bool {
	return !is_null(value: $year) && !is_null(value: $month) && !is_null(value: $day);
}	

function exit_error($status, $code, $message): never {
	error_log(message: 'error: (' . $status . ') - ' . $code . ': ' . $message);
	http_response_code(response_code: $status);
	echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
	exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	init_response();
	
	$year = $_REQUEST['year'] ?? null;
	$month = $_REQUEST['month'] ?? null;
	$day = $_REQUEST['day'] ?? null;
	$page = $_REQUEST['page'] ?? 1;
	$resultsPerPage = $_REQUEST['results'] ?? 20;

	validateInput(year: $year, month: $month, day: $day, page: $page, resultsPerPage: $resultsPerPage);
	
	$workoutService = new WorkoutService();

	// if year is the only parameter, return all workouts for that year
	if (isYearSearch($year, $month, $day)) {
		$workouts = $workoutService->getWorkoutsByYear(year: $year, page: $page, pageSize: $resultsPerPage);
	} elseif (isMonthSearch($year, $month, $day)) {
		$workouts = $workoutService->getWorkoutsByMonth(year: $year, month: $month, page: $page, pageSize: $resultsPerPage);
	} elseif (isDaySearch($year, $month, $day)) {
		$workouts = $workoutService->getWorkoutsByDay(year: $year, month: $month, day: $day, page: $page, pageSize: $resultsPerPage);
	} else {
		exit_error(status: 400, code: 1002, message: 'Invalid parameters. Must provide year or year and month or year, month and day');
	}	

	if (is_null(value: $workouts) || empty($workouts)) {
		exit_error(status: 404, code: 1001, message: 'Workout not found');
	}

	echo json_encode(value: $workouts, flags: JSON_UNESCAPED_SLASHES);
}
