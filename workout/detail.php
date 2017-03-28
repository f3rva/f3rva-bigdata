<?php
namespace F3;

define('__ROOT__', dirname(dirname(__FILE__))); 
require(__ROOT__ . '/repo/workout.php');

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
    $workoutRepo = new WorkoutRepository();
    $details = $workoutRepo->find($_REQUEST['id']);
?>

<h1><?= $details[0]['TITLE']; ?></h1>
<h2>Q: <?= $details[0]['Q']; ?></h2>

<table class="table table-striped">

<?    
    foreach ($details as $detail) {
?>
    <tr><td><? echo $detail['PAX'] ?></td></tr>
<?
    }
?>
</table>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
</body>
</html>
