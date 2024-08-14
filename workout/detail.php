<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__))); 
}
require(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\WorkoutService;

?>

<!DOCTYPE html>
<html lang="en">
<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php';?>
<? include __ROOT__ . '/include/nav.php';?>

<?
    $workoutService = new WorkoutService();
    $detail = $workoutService->getWorkout($_REQUEST['id']);
?>

<h1><?= $detail->getTitle() ?></h1>

<div style="display: flex;">
	<span>Q:</span>
 	<ul class="list-inline">
	<? foreach ($detail->getQ() as $q) { ?>
		<li><?= $q ?></li>
	<? } ?>
	</ul>
</div>
<div style="display: flex;">
	<span>AO:</span>
	<ul class="list-inline">
	<? foreach ($detail->getAo() as $ao) { ?>
		<li><?= $ao ?></li>
	<? } ?>
	</ul>
</div>

<table class="table table-striped">

<?
    foreach ($detail->getPax() as $pax) {
?>
    <tr><td><a href="/member/detail.php?id=<?= $pax->getMemberId() ?>"><?= $pax->getF3Name() ?></a></td></tr>
<?
    }
?>
</table>

<script src="/js/jquery-3.7.1/jquery-3.7.1.min.js"></script>
<script src="/js/bootstrap-5.3.3/bootstrap.bundle.min.js"></script>

</body>
</html>
