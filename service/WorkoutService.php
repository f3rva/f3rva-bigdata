<?php
namespace F3\Service;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require_once(__ROOT__ . '/dao/ScraperDao.php');
require_once(__ROOT__ . '/model/Member.php');
require_once(__ROOT__ . '/model/Workout.php');
require_once(__ROOT__ . '/repo/Database.php');
require_once(__ROOT__ . '/repo/WorkoutRepo.php');
require_once(__ROOT__ . '/service/MemberService.php');

use F3\Dao\ScraperDao;
use F3\Model\Member;
use F3\Model\Workout;
use F3\Repo\Database;
use F3\Repo\WorkoutRepository;
use F3\Service\MemberService;

/**
 * Service class encapsulating business logic for workouts.
 * 
 * @author bbischoff
 */
class WorkoutService {
	private $memberService;
	private $scraperDao;
	private $workoutRepo;

	public function __construct() {
		$this->memberService = new MemberService();
		$this->scraperDao = new ScraperDao();
		$this->workoutRepo = new WorkoutRepository();
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
				$workoutObj = $this->createWorkoutObj($workout);
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
				$workoutObj = $this->createWorkoutObj($workout);
				
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
	
	public function addWorkout($data) {
		// parse the post to get the information we need
		$additionalInfo = $this->scraperDao->parsePost($data->post->url);
		error_log('additionalInfo: ' . json_encode($additionalInfo));
		
		$db = Database::getInstance()->getDatabase();
		try {
			$db->beginTransaction();
			
			// find or insert the q
			$q = $this->memberService->getOrAddMember($additionalInfo->q);
			
			// insert the workout
			$workoutId = $this->workoutRepo->save($data->post->title, $additionalInfo->date, $q->getMemberId(), $data->post->url);
			
			// add the aos
			$this->saveWorkoutAos($workoutId, $additionalInfo->tags);
			
			// add the pax members
			$this->saveWorkoutMembers($workoutId, $additionalInfo->pax);
			
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollBack();
			error_log($e);
			throw $e;
		}
		
		return $workoutId;
	}
	
	public function refreshWorkout($workoutId) {
		// get the workout
		$workout = $this->getWorkout($workoutId);
		
		// parse the post to get the information we need
		$additionalInfo = $this->scraperDao->parsePost($workout->getBackblastUrl());
		error_log('additionalInfo: ' . json_encode($additionalInfo));
		
		$db = Database::getInstance()->getDatabase();
		try {
			$db->beginTransaction();
			
			// find or insert the q
			$q = $this->memberService->getOrAddMember($additionalInfo->q);
			
			// update the workout
			$this->workoutRepo->update($workoutId, $workout->getTitle(), $q->getMemberId(), $workout->getBackblastUrl());
			
			// delete previous aos
			$this->workoutRepo->deleteWorkoutAos($workoutId);
			
			// add the aos
			$this->saveWorkoutAos($workoutId, $additionalInfo->tags);
			
			// delete the previous members
			$this->workoutRepo->deleteWorkoutMembers($workoutId);
			
			// add the pax members
			$this->saveWorkoutMembers($workoutId, $additionalInfo->pax);
			
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollBack();
			error_log($e);
			throw $e;
		}
		
		return $workoutId;
	}
	
	private function createWorkoutObj($workout) {
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
	
	private function saveWorkoutAos($workoutId, $aos) {
		foreach ($aos as $ao) {
			$ao = $this->workoutRepo->selectOrAddAo($ao);
			$this->workoutRepo->saveWorkoutAo($workoutId, $ao->aoId);
		}
	}
	
	private function saveWorkoutMembers($workoutId, $pax) {
		foreach ($pax as $paxMember) {
			$member = $this->memberService->getOrAddMember($paxMember);
			$this->workoutRepo->saveWorkoutMember($workoutId, $member->getMemberId());
		}
	}
	
}

?>