<?php
namespace F3;
define('__ROOT__', dirname(__FILE__));
require(__ROOT__ . '/model/workout.php');
require(__ROOT__ . '/model/member.php');
?>

<?
$workout = new Model\Workout();
$workout->setTitle('asdf');

$member = new Model\Member();
$member->setF3Name('test');
?>

<?= $workout->getTitle() ?>
<br/>
<?= $member->getF3Name() ?>
