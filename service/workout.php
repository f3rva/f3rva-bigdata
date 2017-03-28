<?php
namespace F3\Service;

define('__ROOT__', dirname(dirname(dirname(__FILE__)))); 
require_once(__ROOT__ . '/model/member.php');
require_once(__ROOT__ . '/model/workout.php');
require_once(__ROOT__ . '/repo/member.php');
require_once(__ROOT__ . '/repo/workout.php');

use F3\Model\Member;
use F3\Model\Workout;
use F3\Repo\MemberRepository;
use F3\Repo\WorkoutRepository;

/**
 * Service class encapsulating business logic for workouts.
 * 
 * @author bbischoff
 */
class WorkoutService {
    private $memberRepo;
    private $workoutRepo;

    public function __construct() {
        $this->memberRepo = new MemberRepository();
        $this->workoutRepo = new WorkoutRepository();
    }

    public function addWorkout() {

    }

    /**
     * Retrieves all workouts.
     * 
     * @return array of Member
     */
    public function getWorkouts() {
		$workouts = $this->workoutRepo->findAll();
		$workoutsArray = array();
		
		foreach ($workouts as $workout) {
			$workoutId = $workout['WORKOUT_ID'];
			if (is_null($workoutsArray[$workoutId])) {
				$workoutObj = new Workout();
				
				$aoArray = array();
				$aoArray[$workout['AO_ID']] = $workout['AO'];
				$workoutObj->setAo($aoArray);
				
				$workoutObj->setBackblastUrl($workout['BACKBLAST_URL']);
				$workoutObj->setPax($workout['PAX']);
				$workoutObj->setQ($workout['Q']);
				$workoutObj->setTitle($workout['TITLE']);
				$workoutObj->setWorkoutId($workoutId);
				$workoutObj->setWorkoutDate($workout['WORKOUT_DATE']);

				$workoutsArray[$workoutObj->getWorkoutId()] = $workoutObj;
			}
			else {
				// we already have the workout details, just add the duplicate info
				$existingWorkout = $workoutsArray[$workoutId];
				$aoArray = $existingWorkout->getAo();
				$aoArray[$workout['AO_ID']] = $workout['AO'];
				$existingWorkout->setAo($aoArray);
			}
		}
		
		//var_dump($workoutsArray);
		return $workoutsArray;
    }
    
    public function getWorkout($workoutId) {
    	$details = $this->workoutRepo->find($workoutId);
    }
}

?>