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
?>

<div class="container-fluid">
	<div class="row">
		<div class="col col-sm-12">
			<h1><?= $aoAverages[$aoId]->getDescription() ?></h1>
		</div>
	</div>
	<div class="row">
		<div class="col col-sm-12">
			<div id="chartContainer"></div>
		</div>
	</div>
	<div class="row">
		<div class="col col-sm-3">
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingOne">
						<h4 class="panel-title">
							<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
								Summary
							</a>
						</h4>
					</div>
					<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
						<div class="panel-body">
							<p>
								<?= $aoAverages[$aoId]->getDescription() ?> has averaged <strong><?= $aoAverages[$aoId]->getValue() ?></strong>
								PAX members per workout since the AOs inception.
							</p>
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingTwo">
						<h4 class="panel-title">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								Attendee Leaderboard
							</a>
						</h4>
					</div>
					<div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
						<div class="panel-body">
							<p>
								This is the list of top workout attendees at <?= $aoAverages[$aoId]->getDescription() ?>. 
								Climb these rankings by posting frequently.
							</p>
						</div>
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
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingThree">
						<h4 class="panel-title">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								Q Leaderboard
							</a>
						</h4>
					</div>
					<div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
						<div class="panel-body">
							<p>
									This is the list of top workout leaders at <?= $aoAverages[$aoId]->getDescription() ?>. 
									This is an elite club! Climb these rankings by leading workouts at <?= $aoAverages[$aoId]->getDescription() ?>.
							</p>
						</div>
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
		</div>
		<div class="col col-sm-9">
			<table class="table table-striped">
				<tr>
					<th>Workout Date</th>
					<th>Backblast Title</th>
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

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="/js/bootstrap.min.js"></script>
	
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

