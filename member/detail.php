<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__))); 
}
require(__ROOT__ . '/service/MemberService.php');

use F3\Service\MemberService;

?>

<!DOCTYPE html>
<html lang="en">
<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php';?>
<? include __ROOT__ . '/include/nav.php';?>

<?
    $memberService = new MemberService();
    $member = $memberService->getMemberById($_REQUEST['id']);
?>

<div class="container-fluid">
	<div class="row">
		<div class="col col-sm-3">
			<h1><?= $member->getF3Name() ?></h1>
			
			<table class="table table-striped">
			<?
			    foreach ($member->getAliases() as $alias) {
			?>
			    <tr><td><?= $alias ?></td></tr>
			<?
			    }
			?>
			</table>
		</div>
	</div>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
</body>
</html>
