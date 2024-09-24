<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/model/AliasRequestStatus.php');
require_once(__ROOT__ . '/service/MemberService.php');
require_once(__ROOT__ . '/util/Util.php');

use F3\Model\AliasRequestStatus;
use F3\Service\MemberService;
use F3\Util\Util;

$memberService = new MemberService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// process post
	$action = $_REQUEST['action'];
	
	switch ($action) {
		case 'request': 
			$primaryMemberId = $_REQUEST['primaryMemberId'];
			$aliasMemberId= $_REQUEST['aliasMemberId'];
			
			$memberService->requestAlias($primaryMemberId, $aliasMemberId);
			
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
		$pendingAliases = $memberService->getAliasesByStatus(AliasRequestStatus::PENDING);
?>

<div class="container-fluid">
	<div class="row mt-2">
		<div class="col-md-3">
			<form method="post" action="alias.php">
				<input type="hidden" name="action" value="request" />
				<label for="primaryMemberId" class="help-block">ID of the primary user</>
				<div class="form-group mt-2">
					<input type="text" name="primaryMemberId" class="form-control" id="primaryMemberId" placeholder="Primary ID">
				</div>
				<label for="aliasMemberId" class="mt-2">ID that should become an alias for the primary user</label>
				<div class="form-group mt-2 mb-2">
					<input type="text" name="aliasMemberId" class="form-control" id="aliasMemberId" placeholder="Alias ID">
				</div>
				<button type="submit" class="btn btn-secondary">Request Alias</button>
			</form>
			<div class="row mt-5">
				<div class="col-md-12">
					<h6>Pending Aliases</h6>
					<table id="pending" class="table table-striped table-hover">
						<thead>
							<tr>
								<th>Primary</th>
								<th>Alias</th>
								<th>Status</th>
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
							</tr>
						<?
							}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<table id="primary" class="table table-striped table-hover">
				<thead>
					<tr>
							<th>Primary</th>
							<th></th>
					</tr>
				</thead>
				<tbody>
			<?    
					foreach ($members as $member) {
			?>
					<tr>
							<td><input type="radio" name="primary" value="<?= $member->getMemberId() ?>"/></td>
							<td><a href="/member/detail.php?id=<?= $member->getMemberId() ?>"><?= $member->getF3Name() ?></a></td>
					</tr>
			<?
					}
			?>
				</tbody>
			</table>
		</div>
		<div class="col-md-3">
			<table id="alias" class="table table-striped table-hover">
				<thead>
					<tr>
							<th>Alias</th>
							<th></th>
					</tr>
				</thead>
				<tbody>
			<?    
			    foreach ($members as $member) {
			?>
			    <tr>
							<td>
								<input type="radio" name="alias" value="<?= $member->getMemberId() ?>"
								<? if (in_array($member->getMemberId(), $pendingAliasIds)) { ?>
									disabled
								<? } ?>
								/>
							</td>
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
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.bootstrap5.js"></script>
<script src="/js/f3.self-service.alias.js?v=<?= Util::getVersion() ?>"></script>

</body>
</html>

