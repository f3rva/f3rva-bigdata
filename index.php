<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(__FILE__));
}
require_once(__ROOT__ . '/service/WorkoutService.php');
require_once(__ROOT__ . '/util/DateUtil.php');

use F3\Service\WorkoutService;
use F3\Util\DateUtil;
?>

<!DOCTYPE html>
<html lang="en">

<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php'; ?>
<? include __ROOT__ . '/include/nav.php';?>

<?
	$workoutService = new WorkoutService();
	$workouts = $workoutService->getWorkouts(NULL, 10);
?>

<table class="table table-striped">
	<thead>
		<tr>
			<th>Workout Date</th>
			<th>Backblast Title</th>
			<th>AO</th>
			<th>Q</th>
			<th># PAX</th>
		</tr>
	</thead>
	<tbody id="workouts">
	<?	
		foreach ($workouts as $workout) {
	?>
		<tr>
			<td><?= $workout->getWorkoutDate() ?></td>
			<td><a href="<?= $workout->getBackblastUrl() ?>" target="_blank"><?= $workout->getTitle() ?></a></td>
			<td>
				<ul class="list-unstyled">
				<? foreach ($workout->getAo() as $ao) { ?>
					<li><a href="/ao/detail.php?id=<?= $ao->getId() ?>"><?= $ao->getDescription() ?></a></li>
				<? } ?>
				</ul>
			</td>
			<td>
	        	<ul class="list-unstyled">
	        	<? foreach ($workout->getQ() as $q) { ?>
	        		<li><a href="/member/detail.php?id=<?= $q->getMemberId() ?>"><?= $q->getF3Name() ?></a></li>
	        	<? } ?>
	        	</ul>
			</td>
			<td><a href="/workout/detail.php?id=<?= $workout->getWorkoutId() ?>"><?= $workout->getPaxCount() ?></a></td>
		</tr>
	<?
		}
	?>
	</tbody>
</table>
<div id="loading">
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="/js/jquery-3.7.1/jquery-3.7.1.min.js"></script>
<script src="/js/bootstrap-5.3.3/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/infinite-scroll@4/dist/infinite-scroll.pkgd.min.js"></script>
<script src="/js/f3.home.js"></script>
</body>
</html>
