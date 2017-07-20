<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(__FILE__));
}
require_once(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\WorkoutService;
?>

<!DOCTYPE html>
<html lang="en">

<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php'; ?>
<? include __ROOT__ . '/include/nav.php';?>

<?
	$workoutService = new WorkoutService();
	$workouts = $workoutService->getWorkouts();
?>

<table class="table table-striped">
	<tr>
		<th>Workout Date</th>
		<th>Backblast Title</th>
		<th>AO</th>
		<th>Q</th>
		<th># PAX</th>
	</tr>
<?	
	foreach ($workouts as $workout) {
?>
	<tr>
		<td><?= $workout->getWorkoutDate() ?></td>
		<td><a href="<?= $workout->getBackblastUrl() ?>" target="_blank"><?= $workout->getTitle() ?></a></td>
		<td>
			<ul class="list-unstyled">
			<? foreach ($workout->getAo() as $aoId => $ao) { ?>
				<li><a href="/ao/detail.php?id=<?= $aoId ?>"><?= $ao ?></a></li>
			<? } ?>
			</ul>
		</td>
		<td>
        	<ul class="list-unstyled">
        	<? foreach ($workout->getQ() as $qId => $q) { ?>
        		<li><a href="/member/detail.php?id=<?= $qId ?>"><?= $q ?></a></li>
        	<? } ?>
        	</ul>
		</td>
		<td><a href="/workout/detail.php?id=<?= $workout->getWorkoutId() ?>"><?= $workout->getPaxCount() ?></a></td>
	</tr>
<?
	}
?>
</table>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="/js/bootstrap.min.js"></script>
</body>
</html>

