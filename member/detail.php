<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__))); 
}
require(__ROOT__ . '/service/MemberService.php');
require(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\MemberService;
use F3\Service\WorkoutService;

?>

<!DOCTYPE html>
<html lang="en">
<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php';?>
<? include __ROOT__ . '/include/nav.php';?>

<?
	$memberId = $_REQUEST['id'];
    $memberService = new MemberService();
    $member = $memberService->getMemberById($memberId);

    $workoutService = new WorkoutService();
    $qWorkouts = $workoutService->getWorkoutsByQ($memberId);
    $paxWorkouts = $workoutService->getWorkoutsByPax($memberId);
?>

<div class="container-fluid">
	<div class="row">
		<div class="col col-sm-12">
			<h1><?= $member->getF3Name() ?></h1>
		</div>
	</div>
	<div class="row">
		<div class="col col-sm-4">
			<h4>Workouts</h4>
			<table class="table table-striped">
				<tr>
					<th>Date</th>
					<th>Title</th>
					<th>AO</th>
				</tr>
			<?	
			foreach ($paxWorkouts as $workout) {
			?>
				<tr>
					<td><a href="/workout/detail.php?id=<?= $workout->getWorkoutId() ?>"><?= $workout->getWorkoutDate() ?></a></td>
					<td><a href="<?= $workout->getBackblastUrl() ?>" target="_blank"><?= $workout->getTitle() ?></a></td>
					<td>
						<ul class="list-unstyled">
						<? foreach ($workout->getAo() as $aoId => $ao) { ?>
							<li><a href="/ao/detail.php?id=<?= $aoId ?>"><?= $ao ?></a></li>
						<? } ?>
						</ul>
					</td>
				</tr>
			<?
				}
			?>
			</table>
		</div>
		<div class="col col-sm-4">
			<h4>Qs</h4>
			<table class="table table-striped">
				<tr>
					<th>Date</th>
					<th>Title</th>
					<th>AO</th>
				</tr>
			<?	
			foreach ($qWorkouts as $workout) {
			?>
				<tr>
					<td><a href="/workout/detail.php?id=<?= $workout->getWorkoutId() ?>"><?= $workout->getWorkoutDate() ?></a></td>
					<td><a href="<?= $workout->getBackblastUrl() ?>" target="_blank"><?= $workout->getTitle() ?></a></td>
					<td>
						<ul class="list-unstyled">
						<? foreach ($workout->getAo() as $aoId => $ao) { ?>
							<li><a href="/ao/detail.php?id=<?= $aoId ?>"><?= $ao ?></a></li>
						<? } ?>
						</ul>
					</td>
				</tr>
			<?
				}
			?>
			</table>
		</div>
		<div class="col col-sm-4">
			<h4>Aliases</h4>
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
