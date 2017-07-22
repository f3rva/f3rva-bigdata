<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/include/init.php');
require_once(__ROOT__ . '/service/ReportService.php');

use F3\Service\ReportService;
?>

<!DOCTYPE html>
<html lang="en">

<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php'; ?>
<? include __ROOT__ . '/include/nav.php';?>

<?
	$reportService = new ReportService();
	$startDate = $reportService->getDefaultDateSubtractInterval($_REQUEST['startDate'], 'P1M');
	$endDate= $reportService->getDefaultDate($_REQUEST['endDate']);
	
	$aoAverages = $reportService->getAverageAttendanceByAO($startDate, $endDate);
	$chartData = $reportService->getWorkoutCountsChartData($startDate, $endDate);
?>

<div class="container-fluid">
	<div class="row">
		<div class="col col-sm-12">
			<div id="chartContainer"></div>
		</div>
	</div>
	<div class="row">
		<div class="col col-sm-3">
			<form method="get" action="ao.php">
				<div class="form-group row">
					<label class="col-md-4 col-form-label" for="startDate">Start</label>
					<div class="col-md-8">
						<input type="date" name="startDate" class="form-control" id="startDate" value="<?= $startDate ?>">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-md-4 col-form-label" for="endDate">End</label>
					<div class="col-md-8">
						<input type="date" name="endDate" class="form-control" id="endDate" value="<?= $endDate ?>">
					</div>
				</div>
				<button type="submit" class="btn btn-default">Filter</button>
			</form>
		</div>
		<div class="col col-sm-3">
			<table class="table table-striped">
				<tr>
					<th>AO</th>
					<th>Average Attendance</th>
				</tr>
			<?	
			foreach ($aoAverages as $ao) {
			?>
				<tr>
					<td><a href="/ao/detail.php?id=<?= $ao->getId() ?>"><?= $ao->getDescription() ?></a></td>
					<td><?= $ao->getValue() ?></td>
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
				hAxis: {
					title: 'Time',
					logScale: true
				},
				vAxis: {
					title: 'Popularity',
					logScale: false
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

