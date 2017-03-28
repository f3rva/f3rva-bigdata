<?php 
define('__ROOT__', dirname(dirname(dirname(__FILE__)))); 
require(__ROOT__ . '/repo/memberRepo.php'); 
require(__ROOT__ . '/repo/workoutRepo.php'); 
?>

<?

class WorkoutService {
    private $memberRepo;
    private $workoutRepo;

    public function __construct() {
        $this->memberRepo = new MemberRepository();
        $this->workoutRepo = new WorkoutRepository();
    }

    public function addWorkout() {

    }

    public function getWorkouts() {

    }
}

?>