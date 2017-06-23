<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/include/init.php');
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
	$chartData = $reportService->getAoDetailChartData($aoId, $workouts);
?>

<div class="container-fluid">
	<div class="row">
		<div class="col col-sm-12">
			<div id="chartContainer"></div>
		</div>
	</div>
	<div class="row">
		<div class="col col-sm-3">
			<table class="table table-striped">
				<tr>
					<th>AO</th>
					<th>Average Attendance</th>
				</tr>
				<tr>
					<td><?= $aoAverages[$aoId]->getDescription() ?></td>
					<td><?= $aoAverages[$aoId]->getValue() ?></td>
				</tr>
			</table>
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
			        	<? foreach ($workout->getQ() as $q) { ?>
			        		<li><?= $q ?></li>
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

