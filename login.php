<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(__FILE__));
}
require_once(__ROOT__ . '/include/init.php');
require_once(__ROOT__ . '/service/AuthenticationService.php');

use F3\Service\AuthenticationService;

$authService = new AuthenticationService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// process post
	$email = $_REQUEST['email'];
	$password = $_REQUEST['password'];
	
	$loggedIn = $authService->login($email, $password);
	
	if ($loggedIn) {
		header("Location: " . '/index.php');
	}
	else {
		$errorMessage = 'Invalid login attempt';
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php'; ?>
<? include __ROOT__ . '/include/nav.php';?>

<div class="container">

<?
	if (!empty($errorMessage)) {
?>
	<div class="col-md-4 col-md-offset-4 alert alert-danger" role="alert">
		<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
		<span class="sr-only">Error:</span>
		<?= $errorMessage ?>
	</div>
<?
	}
?>
	<form action="login.php" method="post" class="form-signin">
		<h2 class="form-signin-heading">Sign In</h2>
		<label for="inputEmail" class="sr-only">Email address</label>
		<input type="email" name="email" id="inputEmail" class="form-control" placeholder="email@address.com" required autofocus>
		<label for="inputPassword" class="sr-only">Password</label>
		<input type="password" name="password" id="inputPassword" class="form-control" placeholder="password" required>
		<div class="checkbox">
			<label>
				<input type="checkbox" value="remember-me"> Remember me
			</label>
		</div>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
	</form>

</div> <!-- /container -->

</body>
</html>
