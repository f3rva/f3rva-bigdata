<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/service/MemberService.php');

use F3\Service\MemberService;

$memberService = new MemberService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// process post
	$action = $_REQUEST['action'];
	
	switch ($action) {
		case 'merge': 
			$memberId = $_REQUEST['memberId'];
			$associatedMemberId= $_REQUEST['associatedMemberId'];
			
			$memberService->assignAlias($memberId, $associatedMemberId);
			
			break;
		case 'split': 
			break;
		default:
			break;
	}	
	
	// redirect to self
	$self = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	header("Location: " . $self); /* Redirect browser */
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php'; ?>
<? include __ROOT__ . '/include/nav.php';?>

<?
    $members = $memberService->getMembers();
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-3">
			<table class="table table-striped">
			    <tr>
			        <th>ID</th>
			        <th>Name</th>
			    </tr>
			<?    
			    foreach ($members as $member) {
			?>
			    <tr>
			        <td><?= $member->getMemberId() ?></td>
			        <td><?= $member->getF3Name() ?></td>
			    </tr>
			<?
			    }
			?>
			</table>
		</div>
		<div class="col-md-3">
			<form method="post" action="managePax.php">
				<input type="hidden" name="action" value="merge" />
				<div class="form-group">
					<label for="memberId">ID</label>
					<input type="text" name="memberId" class="form-control" id="memberId" placeholder="ID">
					<p class="help-block">ID of the primary member id</p>
				</div>
				<div class="form-group">
					<label for="associatedMemberId">Associated ID</label>
					<input type="text" name="associatedMemberId" class="form-control" id="associatedMemberId" placeholder="Associated ID">
					<p class="help-block">ID of member you want to assocate with the primary user above</p>
				</div>
				<button type="submit" class="btn btn-default">Create Alias</button>
			</form>
		</div>
		<div class="col-md-3">
			<form method="post" action="managePax.php">
				<input type="hidden" name="action" value="split" />
				<div class="form-group">
					<label for="memberId">ID</label>
					<input type="text" name="memberId" class="form-control" id="memberId" placeholder="ID">
					<p class="help-block">ID of the member to split</p>
				</div>
				<button type="submit" class="btn btn-default">Split</button>
			</form>
		</div>
	</div>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
</body>
</html>

