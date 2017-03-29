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
				$workoutObj = $this->createWorkout($workout);
				$workoutsArray[$workoutObj->getWorkoutId()] = $workoutObj;
			}
			else {
				// we already have the workout details, just add the duplicate info
				if (!is_null($workout['AO_ID'])) {
					$existingWorkout = $workoutsArray[$workoutId];
					$existingWorkout = $this->addAoToWorkout($existingWorkout, $workout['AO_ID'], $workout['AO']);
				}
			}
		}
		
		return $workoutsArray;
    }
    
    public function getWorkout($workoutId) {
    	$details = $this->workoutRepo->find($workoutId);
    	$workoutObj = null;
    	
    	foreach ($details as $workout) {
    		$workoutId = $workout['WORKOUT_ID'];
    		if (is_null($workoutObj)) {
    			$workoutObj = $this->createWorkout($workout);
    			
    			// retrieve pax
    			$paxList = $this->workoutRepo->findPax($workoutId);
    			$paxArray = array();
    			foreach ($paxList as $pax) {
    				$member = new Member();
    				$member->setMemberId($pax["MEMBER_ID"]);
    				$member->setF3Name($pax["F3_NAME"]);
    				$paxArray[$member->getMemberId()] = $member;
    			}
    			$workoutObj->setPax($paxArray);
    		}
    		else {
    			// we already have the workout details, just add the duplicate info
    			$workoutObj = $this->addAoToWorkout($workoutObj, $workout['AO_ID'], $workout['AO']);
    		}
    	}
    	
    	return $workoutObj;
    }
    
    private function createWorkout($workout) {
    	$workoutObj = new Workout();
    	
    	$aoArray = array();
    	// only add the AO if it exists
    	if (!is_null($workout['AO_ID'])) {
    		$aoArray[$workout['AO_ID']] = $workout['AO'];
    	}
    	$workoutObj->setAo($aoArray);
    	
    	$workoutObj->setBackblastUrl($workout['BACKBLAST_URL']);
    	$workoutObj->setPaxCount($workout['PAX_COUNT']);
    	$workoutObj->setQ($workout['Q']);
    	$workoutObj->setTitle($workout['TITLE']);
    	$workoutObj->setWorkoutId($workout['WORKOUT_ID']);
    	$workoutObj->setWorkoutDate($workout['WORKOUT_DATE']);
    	
    	return $workoutObj;
    }
    
    private function addAoToWorkout($workout, $aoId, $aoDescription) {
    	$aoArray = $workout->getAo();
    	$aoArray[$aoId] = $aoDescription;
    	$workout->setAo($aoArray);
    	
    	return $workout;
    }
}

?>