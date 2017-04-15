<?php
if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require(__ROOT__ . '/dao/ScraperDao.php');

use F3\Dao\ScraperDao;

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
	
	return $json;
}

function exit_error($status, $code, $message) {
	error_log('error: (' . $status . ') - ' . $code . ': ' . $message);
	http_response_code($status);
	echo '{ "errorCode": ', $code, ', "errorMessage": "', $message, '" }';
	exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	init_response();
	$data = validateInput();
	$scraper = new ScraperDao();
	
	echo json_encode($scraper->parsePost($data->post->url));
}
?>
