<?php
namespace F3;

define('__ROOT__', dirname(__FILE__));
require_once(__ROOT__ . '/service/WorkoutService.php');

use F3\Service\WorkoutService;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>F3RVA</title>

    <link href="/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<?php
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
        	<? foreach ($workout->getAo() as $ao) { ?>
        		<li><?= $ao ?></li>
        	<? } ?>
        	</ul>
        </td>
        <td><?= $workout->getQ() ?></td>
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

