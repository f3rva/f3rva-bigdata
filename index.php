<?php 
define('__ROOT__', dirname(__FILE__)); 
require(__ROOT__ . '/repo/workoutRepo.php'); 
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
    $workoutRepo = new WorkoutRepository();
    $workouts = $workoutRepo->findAll();
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
        <td><?= $workout['WORKOUT_DATE'] ?></td>
        <td><a href="<?= $workout['BACKBLAST_URL'] ?>" target="_blank"><?= $workout['TITLE'] ?></a></td>
        <td><?= $workout['AO'] ?></td>
        <td><?= $workout['Q'] ?></td>
        <td><a href="/workout/detail.php?id=<?= $workout['WORKOUT_ID'] ?>"><?= $workout['PAX'] ?></a></td>
    </tr>
<?
    }
?>
</table>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
</body>
</html>

