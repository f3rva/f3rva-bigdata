<?php
namespace F3;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__ . '/service/ReportService.php');
require_once(__ROOT__ . '/util/DateUtil.php');
require_once(__ROOT__ . '/util/Util.php');

use F3\Service\ReportService;
use F3\Util\DateUtil;
use F3\Util\Util;
?>

<!DOCTYPE html>
<html lang="en">

<? include __ROOT__ . '/include/head.php';?>

<body>
<? include __ROOT__ . '/include/analytics.php'; ?>
<? include __ROOT__ . '/include/nav.php';?>

<?
	$reportService = new ReportService();
	$startDate = DateUtil::getDefaultDateSubtractInterval($_REQUEST['startDate'] ?? NULL, 'P1M');
	$endDate = DateUtil::getDefaultDate($_REQUEST['endDate'] ?? NULL);
	$order = $_REQUEST['order'] ?? NULL;
	
	$attendance = $reportService->getAttendanceCounts($startDate, $endDate, $order);
	//$attendance = $reportService->getPAXAttendance($startDate, $endDate);
	//$qs = $reportService->getQTotals($startDate, $endDate);
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
		<div class="col col-sm-8">
			<table id="attendance" class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Name</th>
						<th>Workouts</th>
						<th># Qs</th>
						<th>Q Ratio</th>
					</tr>
				</thead>
				<tbody>
				<?	
				foreach ($attendance as $stat) {
				?>
					<tr>
						<td><a href="/member/detail.php?id=<?= $stat->getMemberId()?>"><?= $stat->getMemberName() ?></a></td>
						<td><?= $stat->getNumWorkouts() ?></td>
						<td><?= $stat->getNumQs() ?></td>
						<td><?= $stat->getQRatio() * 100 ?>%</td>
					</tr>
				<?
					}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="row">
	</div>
</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="/js/bootstrap.min.js"></script>
	<script src="/js/f3.report.attendance.js?v=<?= Util::getVersion() ?>"></script>
	<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
</body>
</html>

