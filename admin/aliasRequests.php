<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/auth.php');
require_once(__ROOT__ . '/model/AliasRequestStatus.php');
require_once(__ROOT__ . '/service/MemberService.php');
require_once(__ROOT__ . '/util/Util.php');

use F3\Model\AliasRequestStatus;
use F3\Service\MemberService;
use F3\Util\Util;

$memberService = new MemberService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// process post
	$action = $_POST['action'];
	
	switch ($action) {
		case 'approve': 
			$memberId = $_REQUEST['memberId'];
			$associatedMemberId = $_REQUEST['associatedMemberId'];
			
			$memberService->assignAlias($memberId, $associatedMemberId);
			
			break;
		case 'reject': 
			$memberId = $_REQUEST['memberId'];
			$associatedMemberId = $_REQUEST['associatedMemberId'];

			$memberService->rejectAlias($memberId, $associatedMemberId);

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
	$pendingAliases = $memberService->getAliasesByStatus(AliasRequestStatus::PENDING);
?>

<div class="container-fluid">
	<div class="row mt-4">
		<div class="col-md-1"></div>
		<div class="col-md-10">
			<form method="post" action="aliasRequests.php">
				<input type="hidden" id="action" name="action" value="" />
				<input type="hidden" id="memberId" name="memberId" value="" />
				<input type="hidden" id="associatedMemberId" name="associatedMemberId" value="" />

				<h5>Pending Aliases</h5>
				<table id="pending" class="table table-striped table-hover">
					<thead>
						<tr>
							<th class="col-quarters">Primary</th>
							<th class="col-quarters">Alias</th>
							<th class="col-quarters">Status</th>
							<th class="col-quarters">Action</th>
						</tr>
					</thead>
					<tbody>
					<?
						// create an array of pending alias ids
						$pendingAliasIds = [];
						foreach ($pendingAliases as $alias) {
							$pendingAliasIds[] = $alias->getAliasMember()->getMemberId();
					?>
						<tr>
							<td><a href="/member/detail.php?id=<?= $alias->getPrimaryMember()->getMemberId() ?>"><?= $alias->getPrimaryMember()->getF3Name() ?></a></td>
							<td><a href="/member/detail.php?id=<?= $alias->getAliasMember()->getMemberId() ?>"><?= $alias->getAliasMember()->getF3Name() ?></a></td>
							<td><?= $alias->getStatus()->value ?></td>
							<td>
								<input type="submit" id="approve-<?= $alias->getAliasMember()->getMemberId() ?>" class="btn btn-success" value="Approve" data-primary-id="<?= $alias->getPrimaryMember()->getMemberId() ?>" data-alias-id="<?= $alias->getAliasMember()->getMemberId() ?>" data-action="approve" />
								<input type="submit" id="reject-<?= $alias->getAliasMember()->getMemberId() ?>" class="btn btn-danger" value="Reject" data-primary-id="<?= $alias->getPrimaryMember()->getMemberId() ?>" data-alias-id="<?= $alias->getAliasMember()->getMemberId() ?>" data-action="reject" />
							</td>
						</tr>
					<?
						}
					?>
					</tbody>
				</table>
			</form>
		</div>
		<div class="col-md-1"></div>
	</div>
</div>

<script src="/js/jquery-3.7.1/jquery-3.7.1.min.js"></script>
<script src="/js/bootstrap-5.3.3/bootstrap.bundle.min.js"></script>
<script src="/js/f3.admin.aliasRequests.js?v=<?= Util::getVersion() ?>"></script>
</body>
</html>

