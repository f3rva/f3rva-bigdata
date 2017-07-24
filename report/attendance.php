<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
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
	
	$attendance = $reportService->getPAXAttendance($startDate, $endDate);
	$qs = $reportService->getQTotals($startDate, $endDate);
?>

<div class="container-fluid">
	<div class="row">
		<div class="col col-sm-3">
			<form method="get" action="attendance.php">
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
		<div class="col col-sm-4">
			<table class="table table-striped">
				<tr>
					<th>Name</th>
					<th># Workouts</th>
				</tr>
			<?	
			foreach ($attendance as $summary) {
			?>
				<tr>
					<td><a href="/member/detail.php?id=<?= $summary->getId() ?>"><?= $summary->getDescription() ?></a></td>
					<td><?= $summary->getValue() ?></td>
				</tr>
			<?
				}
			?>
			</table>
		</div>
		<div class="col col-sm-4">
			<table class="table table-striped">
				<tr>
					<th>Name</th>
					<th># Qs</th>
				</tr>
			<?	
			foreach ($qs as $summary) {
			?>
				<tr>
					<td><a href="/member/detail.php?id=<?= $summary->getId() ?>"><?= $summary->getDescription() ?></a></td>
					<td><?= $summary->getValue() ?></td>
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
</body>
</html>

