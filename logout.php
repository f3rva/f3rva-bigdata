<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(__FILE__));
}
require_once(__ROOT__ . '/include/init.php');
include(__ROOT__ . '/service/AuthenticationService.php');

use F3\Service\AuthenticationService;

$authService = new AuthenticationService();
$authService->logout();

header("Location: " . '/index.php');
?>

