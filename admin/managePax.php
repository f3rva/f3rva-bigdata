<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/auth.php');
require_once(__ROOT__ . '/service/MemberService.php');
require_once(__ROOT__ . '/util/Util.php');

use F3\Service\MemberService;
use F3\Util\Util;

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
	<div class="row mt-2">
		<div class="col-md-3">
			<form method="post" action="managePax.php">
				<input type="hidden" name="action" value="merge" />
				<label for="memberId" class="help-block">ID of the primary user</>
				<div class="form-group mt-2">
					<input type="text" name="memberId" class="form-control" id="memberId" placeholder="Primary ID">
				</div>
				<label for="associatedMemberId" class="mt-2">ID that should become an alias for the primary user</label>
				<div class="form-group mt-2 mb-2">
					<input type="text" name="associatedMemberId" class="form-control" id="associatedMemberId" placeholder="Alias ID">
				</div>
				<button type="submit" class="btn btn-secondary">Create Alias</button>
			</form>
		</div>		
		<div class="col-md-4">
			<table id="members" class="table table-striped table-hover">
				<thead>
    			    <tr>
    			        <th>ID</th>
    			        <th>Name</th>
    			    </tr>
    			</thead>
    			<tbody>
			<?    
			    foreach ($members as $member) {
			?>
			    <tr>
			        <td><?= $member->getMemberId() ?></td>
			        <td><a href="/member/detail.php?id=<?= $member->getMemberId() ?>"><?= $member->getF3Name() ?></a></td>
			    </tr>
			<?
			    }
			?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script src="/js/jquery-3.7.1/jquery-3.7.1.min.js"></script>
<script src="/js/bootstrap-5.3.3/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.bootstrap5.js"></script>
<script src="/js/f3.admin.managePax.js?v=<?= Util::getVersion() ?>"></script>
</body>
</html>

