<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/service/ReportService.php');
require_once(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\ReportService;
use F3\Service\WorkoutService;
?>

<!DOCTYPE html>
<html lang="en">

<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php'; ?>
<? include __ROOT__ . '/include/nav.php';?>

<?
	$reportService = new ReportService();
	$workoutService = new WorkoutService();
	$aoId = $_REQUEST['id'];
	
	$workouts = $workoutService->getWorkoutsByAo($aoId);
	$aoAverages = $reportService->getAverageAttendanceByAO(null, null);
	$topQs = $reportService->getTopQsByAO($aoId, 10, 0);
	$topPax = $reportService->getTopPAXByAO($aoId, 10, 0);
	$chartData = $reportService->getAoDetailChartData($aoId, $workouts);
	$streakers = $reportService->getStreakingPAXMembersByAO($aoId);

	$aoDescription = $aoAverages[$aoId]->getDescription();
?>

<div class="container-fluid">
	<div class="row">
		<div class="col col-sm-12">
			<h1><?= $aoDescription ?></h1>
		</div>
	</div>
	<div class="row">
		<div class="col col-sm-12">
			<div id="chartContainer"></div>
		</div>
	</div>
	<div class="row">
		<div class="col col-sm-3">
			<div class="accordion" id="accordionExample">
				<div class="accordion-item">
					<h2 class="accordion-header" id="headingOne">
						<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
							Summary
						</button>
					</h2>
					<div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
						<div class="accordion-body">
							<?= $aoDescription ?> has averaged <strong><?= $aoAverages[$aoId]->getValue() ?></strong>
							PAX members per workout since the AOs inception.
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="headingTwo">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
							Attendee Leaderboard
						</button>
					</h2>
					<div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
						<div class="accordion-body">
							<p>
								This is the list of top workout attendees at <?= $aoDescription ?>. 
								Climb these rankings by posting frequently.
							</p>
							<table class="table table-striped">
								<tr>
									<th>PAX</th>
									<th>Attendance Count</th>
								</tr>
								<?	
									foreach ($topPax as $pax) {
								?>
								<tr>
									<td><?= $pax->getDescription() ?></td>
									<td><?= $pax->getValue() ?></td>
								</tr>
								<?
									}
								?>
							</table>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="headingThree">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
							Q Leaderboard
						</button>
					</h2>
					<div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
						<div class="accordion-body">
							<p>
									This is the list of top workout leaders at <?= $aoDescription ?>. 
									This is an elite club! Climb these rankings by leading workouts at <?= $aoDescription ?>.
							</p>
							<table class="table table-striped">
								<tr>
									<th>Qs</th>
									<th>Q Count</th>
								</tr>
								<?	
									foreach ($topQs as $q) {
								?>
								<tr>
									<td><?= $q->getDescription() ?></td>
									<td><?= $q->getValue() ?></td>
								</tr>
								<?
									}
								?>
							</table>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="headingFour">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
							Streakers
						</button>
					</h2>
					<div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
						<div class="accordion-body">
							<p>
									You have to post consistently to be a streaker!  Here are the current streakers at <?= $aoDescription ?>.
							</p>
							<table class="table table-striped">
								<tr>
									<th>PAX</th>
									<th>Streak</th>
								</tr>
								<?	
									foreach ($streakers as $streaker) {
								?>
								<tr>
									<td><?= $streaker->getDescription() ?></td>
									<td><?= $streaker->getValue() ?></td>
								</tr>
								<?
									}
								?>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col col-sm-9">
			<table class="table table-striped">
			<thead>
				<tr>
					<th>Workout Date</th>
					<th>Backblast Title</th>
					<th>Q</th>
					<th># PAX</th>
				</tr>
			</thead>
			<?	
				foreach ($workouts as $workout) {
			?>
				<tr>
					<td><?= $workout->getWorkoutDate() ?></td>
					<td><a href="<?= $workout->getBackblastUrl() ?>" target="_blank"><?= $workout->getTitle() ?></a></td>
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
		</div>
	</div>
	<div class="row">
	</div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="/js/jquery-3.7.1/jquery-3.7.1.min.js"></script>
<script src="/js/bootstrap-5.3.3/bootstrap.bundle.min.js"></script>

<script>
	google.charts.load('current', {packages: ['corechart', 'line']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Day');
		<? foreach ($chartData->getXLabels() as $label) { ?>
			data.addColumn('number', '<?= $label ?>');
		<? } ?>

		data.addRows([
			<?= $chartData->getSeriesImploded() ?>
		]);

		var options = {
			animation: {
				duration: 800,
				startup: true
			},
			hAxis: {
				title: 'Date'
			},
			vAxis: {
				title: 'Attendance'
			},
			colors: ['#a52714', '#097138'],
			interpolateNulls: true
		};

		var chart = new google.visualization.LineChart(document.getElementById('chartContainer'));
		chart.draw(data, options);
	}
</script>
</body>
</html>

