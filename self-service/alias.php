<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/model/AliasRequestStatus.php');
require_once(__ROOT__ . '/model/Response.php');
require_once(__ROOT__ . '/service/MemberService.php');
require_once(__ROOT__ . '/util/Util.php');

use F3\Model\AliasRequestStatus;
use F3\Model\Response;
use F3\Service\MemberService;
use F3\Util\Util;

$memberService = new MemberService();
$success = $_REQUEST['s'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// process post
	$action = $_REQUEST['action'];
	$return = Response::SUCCESS;

	switch ($action) {
		case 'request': 
			$primaryMemberId = $_REQUEST['primaryMemberId'];
			$aliasMemberId= $_REQUEST['aliasMemberId'];
			
			$return = $memberService->requestAlias($primaryMemberId, $aliasMemberId);
			
			break;
		default:
			break;
	}	
	
	// redirect to self
	$self = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?s=$return";
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

		$memberArray = [];
		foreach ($members as $member) {
			$memberArray[] = [
				'data' => $member->getMemberId(),
				'value' => $member->getF3Name()
			];
		}

		$pendingAliases = $memberService->getAliasesByStatus(AliasRequestStatus::PENDING);
?>

<script>
	// create a javascript array of members for autocomplete
	var members = <?= json_encode($memberArray) ?>;
</script>

<div class="container-fluid">
	<div class="row mt-2">
		<div class="col-md-2"></div>
		<div class="col-md-8">
		<? 
			$alertClass = 'alert-danger';
			$alertDisplay = 'block';
			$alertMessage = '';

			switch ($success) {
				case null:
					$alertDisplay = 'none';
					break;
				case Response::SUCCESS: 
					$alertClass = 'alert-success';
					$alertMessage = 'Alias request submitted successfully';
					break;
				case Response::DUPLICATE:
					$alertMessage = 'Alias request already exists';
					break;
				case Response::ERROR:
					$alertMessage = 'Error submitting alias request';
					break;
				default:
					$alertDisplay = 'none';
					break;
			}
		?>
			<div class="alert <?= $alertClass ?>" role="alert" style="display: <?= $alertDisplay ?>" id="aliasAlert">
				<?= $alertMessage ?>
			</div>

			<form method="post" action="alias.php" id="aliasForm">
				<input type="hidden" name="action" value="request" />
				<label for="primaryMemberName" class="help-block">Select your preferred name</label>
				<div class="form-group mt-2">
					<input type="hidden" name="primaryMemberId" id="primaryMemberId" />
					<input type="text" name="primaryMemberName" class="form-control" id="primaryMemberName" placeholder="Primary Name" />
				</div>
				<label for="aliasMemberName" class="mt-2">Select the alias you want associated with your preferred name</label>
				<div class="form-group mt-2 mb-2">
					<input type="hidden" name="aliasMemberId" id="aliasMemberId" />
					<input type="text" name="aliasMemberName" class="form-control" id="aliasMemberName" placeholder="Alias Name" />
				</div>
				<button type="submit" class="btn btn-secondary mt-2">Request Alias</button>
			</form>
			<div class="row mt-5">
				<div class="col-md-12">
					<h5>Pending Aliases</h5>
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
		<div class="col-md-2"></div>
	</div>
</div>

<script>
</script>

<script src="/js/jquery-3.7.1/jquery-3.7.1.min.js"></script>
<script src="/js/bootstrap-5.3.3/bootstrap.bundle.min.js"></script>
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.bootstrap5.js"></script>
<script src="/js/f3.self-service.alias.js?v=<?= Util::getVersion() ?>"></script>

</body>
</html>

