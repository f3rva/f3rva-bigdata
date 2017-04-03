<?php
namespace F3;

define('__ROOT__', dirname(dirname(__FILE__))); 
require(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\WorkoutService;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Workout Details - F3RVA</title>

    <link href="/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<?
    $workoutService = new WorkoutService();
    $detail = $workoutService->getWorkout($_REQUEST['id']);
?>

<h1><?= $detail->getTitle() ?></h1>
<h2>Q: <?= $detail->getQ() ?></h2>
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
    <tr><td><?= $pax->getF3Name() ?></td></tr>
<?
    }
?>
</table>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
</body>
</html>
