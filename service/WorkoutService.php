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
require_once(__ROOT__ . '/util/DateUtil.php');

use F3\Dao\ScraperDao;
use F3\Model\Member;
use F3\Model\Workout;
use F3\Repo\Database;
use F3\Repo\WorkoutRepository;
use F3\Service\MemberService;
use F3\Util\DateUtil;

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
	public function getWorkouts($endDate, $numberOfDaysBack) {
		error_log('endDate: ' . $endDate);
		if (is_null($endDate)) {
			$endDate = $this->workoutRepo->findMostRecentWorkoutDate();
		}
		
		$startDate = DateUtil::subtractInterval($endDate, 'P' . $numberOfDaysBack . 'D');
		
		$workouts = $this->workoutRepo->findAllByDateRange($startDate, $endDate);
		
		return $this->processWorkoutResults($workouts);
	}
	
	public function getWorkoutsByAo($aoId) {
		$workouts = $this->workoutRepo->findAllByAo($aoId);
		
		return $this->processWorkoutResults($workouts);
	}
	
	public function getWorkoutsByQ($qId) {
		$workouts = $this->workoutRepo->findAllByQ($qId);
		
		return $this->processWorkoutResults($workouts);
	}
	
	public function getWorkoutsByPax($paxId) {
		$workouts = $this->workoutRepo->findAllByPax($paxId);
		
		return $this->processWorkoutResults($workouts);
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
				$workoutObj = $this->addQToWorkout($workoutObj, $workout['Q_ID'], $workout['Q']);
			}
		}
				
		return $workoutObj;
	}

	public function addWorkout($data) {
		$additionalInfo = $data->additionalInfo ?? null;
		// parse the post to get the information we need
		if ($additionalInfo == null) {
			$additionalInfo = $this->scraperDao->parsePost($data->post->url);
		}
		error_log('additionalInfo: ' . json_encode($additionalInfo));
		
		$workoutId = null;
		
		// validate the workout
		if ($this->validateWorkout($additionalInfo->date)) {
			$db = Database::getInstance()->getDatabase();
			try {
				$db->beginTransaction();
				
				// insert the workout
				//error_log('adding workout: ' . $data->post->title . ' | ' . $additionalInfo->dateTime . '|' . $data->post->url);
				$workoutId = $this->workoutRepo->save(title: $data->post->title, slug: null, 
					dateArray: $additionalInfo->date, url: $data->post->url);
				
				// add the aos
				$this->saveWorkoutAos($workoutId, $additionalInfo->tags);
				
				// add the qs
				$this->saveWorkoutQs($workoutId, $additionalInfo->q);
				
				// add the pax members
				$this->saveWorkoutMembers($workoutId, $additionalInfo->pax);
				
				$db->commit();
			}
			catch (\Exception $e) {
				$db->rollBack();
				error_log($e);
				throw $e;
			}
		}
		
		return $workoutId;
	}

	public function addWorkoutWithData($data): bool|string|null {
		$title = $data->title ?? null;
		$url = $data->url ?? null;
		$slug = $data->slug ?? null;
		$body = $data->body ?? null;
		$workoutDate = $this->parseDateStringToDateArray(dateString: $data->workoutDate);
		$qArray = $this->parseNames(nameString: $data->qic);
		$paxArray = $this->parseNames(nameString: $data->pax);
		$aos = $data->aos ?? null;

		$workoutId = null;
		
		// validate the workout
		if ($this->validateWorkout(dateArray: $workoutDate)) {
			$db = Database::getInstance()->getDatabase();
			try {
				$db->beginTransaction();
				
				// insert the workout
				$workoutId = $this->workoutRepo->save(title: $title, slug: $slug, dateArray: $workoutDate, url: $url);
				
				// add the details
				$this->saveWorkoutDetails(workoutId: $workoutId, body: $body);

				// add the aos
				$aoNames = array_column(array: $aos, column_key: 'name');
				$this->saveWorkoutAos(workoutId: $workoutId, aos: $aoNames);
				
				// add the qs
				$this->saveWorkoutQs(workoutId: $workoutId, qs: $qArray);
				
				// add the pax members
				$this->saveWorkoutMembers(workoutId: $workoutId, pax: $paxArray);
				
				$db->commit();
			}
			catch (\Exception $e) {
				$db->rollBack();
				error_log(message: $e);
				throw $e;
			}
		}
		
		return $workoutId;
	}

	public function refreshWorkout($workoutId) {
		// get the workout
		$workout = $this->getWorkout($workoutId);
		
		// parse the post to get the information we need
		$additionalInfo = $this->scraperDao->parsePost($workout->getBackblastUrl());
		error_log('additionalInfo: ' . json_encode($additionalInfo));
		
		// validate the workout
		if ($this->validateWorkout($additionalInfo->date)) {
			$db = Database::getInstance()->getDatabase();
			try {
				$db->beginTransaction();
				
				// update the workout
				$this->workoutRepo->update(workoutId: $workoutId, title: $workout->getTitle(), 
					slug: null, dateArray: $additionalInfo->date, url: $workout->getBackblastUrl());
				
				// delete the previous details
				$this->workoutRepo->deleteWorkoutDetails(workoutId: $workoutId);

				// delete previous aos
				$this->workoutRepo->deleteWorkoutAos($workoutId);
				
				// add the aos
				$this->saveWorkoutAos($workoutId, $additionalInfo->tags);
				
				// delete the previous qs
				$this->workoutRepo->deleteWorkoutQs($workoutId);
				
				// add the qs
				$this->saveWorkoutQs($workoutId, $additionalInfo->q);
				
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
		}
		
		return $workoutId;
	}
	
	public function refreshWorkoutWithData($data) {
		$workoutId = $data->workoutId ?? null;
		$title = $data->title ?? null;
		$url = $data->url ?? null;
		$slug = $data->slug ?? null;
		$body = $data->body ?? null;
		$workoutDate = $this->parseDateStringToDateArray(dateString: $data->workoutDate);
		$qArray = $this->parseNames(nameString: $data->qic);
		$paxArray = $this->parseNames(nameString: $data->pax);
		$aos = $data->aos ?? null;
		
		// validate the workout
		if ($this->validateWorkout(dateArray: $workoutDate)) {
			$db = Database::getInstance()->getDatabase();
			try {
				$db->beginTransaction();
				
				// update the workout
				$this->workoutRepo->update(workoutId: $workoutId, title: $title, slug: $slug, dateArray: $workoutDate, url: $url);
				
				// delete the previous details
				$this->workoutRepo->deleteWorkoutDetails(workoutId: $workoutId);

				// add the details
				$this->saveWorkoutDetails(workoutId: $workoutId, body: $body);

				// delete previous aos
				$this->workoutRepo->deleteWorkoutAos(workoutId: $workoutId);
				
				// add the aos
				error_log(message: json_encode(value: $aos));
				$aoNames = array_column(array: $aos, column_key: 'name');
				$this->saveWorkoutAos(workoutId: $workoutId, aos: $aoNames);
				
				// delete the previous qs
				$this->workoutRepo->deleteWorkoutQs(workoutId: $workoutId);
				
				// add the qs
				$this->saveWorkoutQs(workoutId: $workoutId, qs: $qArray);
				
				// delete the previous members
				$this->workoutRepo->deleteWorkoutMembers(workoutId: $workoutId);
				
				// add the pax members
				$this->saveWorkoutMembers(workoutId: $workoutId, pax: $paxArray);
	
				$db->commit();
			}
			catch (\Exception $e) {
				$db->rollBack();
				error_log(message: $e);
				throw $e;
			}
		}
		
		return $workoutId;
	}

	public function refreshWorkouts($numDays) {
		error_log('refreshing the past ' . $numDays . ' days');
		// get all workouts in the most recent days
		$workouts = $this->getWorkouts(DateUtil::getDefaultDate(null), $numDays);
		
		$refreshed = array();
		
		// loop through all workouts that meet criteria
		foreach ($workouts as $workout) {
			// refresh the workout
			$this->refreshWorkout($workout->getWorkoutId());
			
			$refreshed[$workout->getWorkoutId()] = $workout->getTitle();
		}
		
		return $refreshed;
	}
	
	public function deleteWorkout($workoutId): mixed {
		$db = Database::getInstance()->getDatabase();
		try {
			$db->beginTransaction();
			
			// delete previous aos
			$this->workoutRepo->deleteWorkoutAos(workoutId: $workoutId);
			
			// delete the previous qs
			$this->workoutRepo->deleteWorkoutQs(workoutId: $workoutId);
			
			// delete the previous members
			$this->workoutRepo->deleteWorkoutMembers(workoutId: $workoutId);
			
			// delete the previous details
			$this->workoutRepo->deleteWorkoutDetails(workoutId: $workoutId);

			// delete the workout
			$this->workoutRepo->deleteWorkout(workoutId: $workoutId);
			
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollBack();
			error_log($e);
			throw $e;
		}
		
		return $workoutId;
	}
	
	private function processWorkoutResults($workouts) {
		$workoutsArray = array();
		
		foreach ($workouts as $workout) {
			$workoutId = $workout['WORKOUT_ID'];
			
			// if the key doesn't exist, we need to create the objects
			if (!array_key_exists($workoutId, $workoutsArray)) {
				$workoutObj = $this->createWorkoutObj($workout);
				$workoutsArray[$workoutObj->getWorkoutId()] = $workoutObj;
			}
			else {
				// we already have the workout details, just add the duplicate info
				if (!is_null($workout['AO_ID'])) {
					$existingWorkout = $workoutsArray[$workoutId];
					$existingWorkout = $this->addAoToWorkout($existingWorkout, $workout['AO_ID'], $workout['AO']);
				}
				if (!is_null($workout['Q_ID'])) {
					$existingWorkout = $workoutsArray[$workoutId];
					$existingWorkout = $this->addQToWorkout($existingWorkout, $workout['Q_ID'], $workout['Q']);
				}
			}
		}
		
		return $workoutsArray;
	}
	private function createWorkoutObj($workout) {
		$workoutObj = new Workout();
		                
		error_log('createWorkoutObj workout: ' . json_encode($workout));

		$aoArray = array();
		// only add the AO if it exists
		if (!is_null($workout['AO_ID'])) {
			$aoArray[$workout['AO_ID']] = $workout['AO'];
		}
		$workoutObj->setAo($aoArray);
		
		$qArray = array();
		// only add the Q if it exists
		if (!is_null($workout['Q_ID'])) {
			$qArray[$workout['Q_ID']] = $workout['Q'];
		}
		$workoutObj->setQ($qArray);
		
		$workoutObj->setBackblastUrl($workout['BACKBLAST_URL']);
		$workoutObj->setTitle($workout['TITLE']);
		$workoutObj->setWorkoutId($workout['WORKOUT_ID']);
		$workoutObj->setWorkoutDate($workout['WORKOUT_DATE']);

		// only set if PAX_COUNT is there
		if (array_key_exists('PAX_COUNT', $workout)) {
			$workoutObj->setPaxCount($workout['PAX_COUNT']);
		}
		
		return $workoutObj;
	}
	
	private function addAoToWorkout($workout, $aoId, $aoDescription) {
		$aoArray = $workout->getAo();
		
		if (!array_key_exists($aoId, $aoArray)) {
			$aoArray[$aoId] = $aoDescription;
			$workout->setAo($aoArray);
		}
		
		return $workout;
	}
	
	private function addQToWorkout($workout, $qId, $qName) {
		$qArray = $workout->getQ();
		if (!array_key_exists($qId, $qArray)) {
			$qArray[$qId] = $qName;
			$workout->setQ($qArray);
		}
		
		return $workout;
	}
	
	private function saveWorkoutDetails($workoutId, $body): void {
		$this->workoutRepo->saveWorkoutDetails(workoutId: $workoutId, body: $body);
	}

	private function saveWorkoutAos($workoutId, $aos): void {
		foreach ($aos as $ao) {
			$ao = $this->workoutRepo->selectOrAddAo($ao);
			$this->workoutRepo->saveWorkoutAo($workoutId, $ao->aoId);
		}
	}
	
	private function saveWorkoutMembers($workoutId, $pax): void {
		foreach ($pax as $paxMember) {
			$member = $this->memberService->getOrAddMember($paxMember);
			$this->workoutRepo->saveWorkoutMember($workoutId, $member->getMemberId());
		}
	}
	
	private function saveWorkoutQs($workoutId, $qs): void {
		foreach ($qs as $q) {
			$member = $this->memberService->getOrAddMember($q);
			$this->workoutRepo->saveWorkoutQ($workoutId, $member->getMemberId());
		}
	}
	
	private function validateWorkout($dateArray): bool {
		// check to see if this workout is in the future.  if it is then skip
		$dateStr = $dateArray['year'] . '-' . $dateArray['month'] . '-' . $dateArray['day'];
		if(strtotime(date('m/d/y', time())) < strtotime($dateStr)) {
			error_log('date is in the future');
			return false;
		}
		
		return true;
	}

	// parse names from a delimited list of names and return as an array
	private function parseNames($nameString): array {
		$trimmedNames = trim(string: $nameString ?? '');
		$split = preg_split(pattern: "/,|\band\b|&/", subject: $trimmedNames);
		// trim values and remove empty values from the array
		$paxArray = array_filter(array: array_map(callback: 'trim', array: $split));

		return $paxArray;
	}

	// parse a date in format YYYYMMDD and return as a date array
	private function parseDateStringToDateArray($dateString): array {
		$dateArray = array();

		// if the dateString is null or empty, return today's date
		if (is_null(value: $dateString) || empty($dateString)) {
			$today = getdate();
			return array('year' => $today['year'], 'month' => $today['mon'], 'day' => $today['mday']);
		}
		else {
			$year = substr(string: $dateString, offset: 0, length: 4);
			$month = substr(string: $dateString, offset: 4, length: 2);
			$day = substr(string: $dateString, offset: 6, length: 2);
			$dateArray = array('year' => $year, 'month' => $month, 'day' => $day);
		}

		return $dateArray;
	}
}
