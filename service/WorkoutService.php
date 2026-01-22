<?php
namespace F3\Service;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require_once(__ROOT__ . '/dao/ScraperDao.php');
require_once(__ROOT__ . '/model/AO.php');
require_once(__ROOT__ . '/model/Member.php');
require_once(__ROOT__ . '/model/Workout.php');
require_once(__ROOT__ . '/repo/Database.php');
require_once(__ROOT__ . '/repo/WorkoutRepo.php');
require_once(__ROOT__ . '/service/MemberService.php');
require_once(__ROOT__ . '/util/DateUtil.php');

use F3\Dao\ScraperDao;
use F3\Model\AO;
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
	private const DEFAULT_PAGE = 1;
	private const DEFAULT_PAGE_SIZE = 20;

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
		
		$workouts = $this->workoutRepo->findAllByDateRange($startDate, $endDate, 200, 0);
		
		return $this->processWorkoutResults($workouts);
	}

	public function getRecentWorkouts($page = self::DEFAULT_PAGE, $pageSize = self::DEFAULT_PAGE_SIZE): array {
		$workouts = $this->workoutRepo->findAll($pageSize, $this->getOffset($page, $pageSize));
		
		return $this->processWorkoutResults($workouts);
	}

	public function getWorkoutsByYear($year, $page = self::DEFAULT_PAGE, $pageSize = self::DEFAULT_PAGE_SIZE): array {
		// use the year to call findAllByDateRange to get all the workouts for that year
		$startDate = DateUtil::getStartDateOfYear(year: $year);
		$endDate = DateUtil::getEndDateOfYear(year: $year);
		$offset = $this->getOffset(page: $page, pageSize: $pageSize);

		$workouts = $this->workoutRepo->findAllByDateRange(startDate: $startDate, endDate: $endDate, limit: $pageSize, offset: $offset);
		
		return $this->processWorkoutResults(workouts: $workouts);
	}

	public function getWorkoutsByMonth($year, $month, $page = self::DEFAULT_PAGE, $pageSize = self::DEFAULT_PAGE_SIZE): array {
		// use the year and month to call findAllByDateRange to get all the workouts for that month
		$startDate = sprintf('%04d-%02d-01',  $year, $month);
		$endDate = sprintf('%04d-%02d-%02d',  $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));
		$offset = $this->getOffset(page: $page, pageSize: $pageSize);

		$workouts = $this->workoutRepo->findAllByDateRange(startDate: $startDate, endDate: $endDate, limit: $pageSize, offset: $offset);
		
		return $this->processWorkoutResults(workouts: $workouts);
	}
	
	public function getWorkoutsByDay($year, $month, $day, $page = self::DEFAULT_PAGE, $pageSize = self::DEFAULT_PAGE_SIZE) {
		// use the year, month, and day to call findAllByDateRange to get all the workouts for that day
		$startDate = sprintf('%04d-%02d-%02d',  $year, $month, $day);
		$endDate = $startDate;
		$offset = $this->getOffset(page: $page, pageSize: $pageSize);

		$workouts = $this->workoutRepo->findAllByDateRange(startDate: $startDate, endDate: $endDate, limit: $pageSize, offset: $offset);
		
		return $this->processWorkoutResults($workouts);
	}
	
	public function getWorkoutsByAo($aoId, $page = self::DEFAULT_PAGE, $pageSize = self::DEFAULT_PAGE_SIZE) {
		$offset = $this->getOffset(page: $page, pageSize: $pageSize);
		$workouts = $this->workoutRepo->findAllByAo($aoId, $pageSize, $offset);
		
		return $this->processWorkoutResults($workouts);
	}

	public function getWorkoutsByAoName($name, $page = self::DEFAULT_PAGE, $pageSize = self::DEFAULT_PAGE_SIZE) {
		$offset = $this->getOffset(page: $page, pageSize: $pageSize);
		$workouts = $this->workoutRepo->findAllByAoDescription($name, $pageSize, $offset);
		
		return $this->processWorkoutResults($workouts);
	}

	public function getWorkoutsByAoSlug($slug, $page = self::DEFAULT_PAGE, $pageSize = self::DEFAULT_PAGE_SIZE) {
		$offset = $this->getOffset(page: $page, pageSize: $pageSize);
		$workouts = $this->workoutRepo->findAllByAoSlug($slug, $pageSize, $offset);
		
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
	
	public function getWorkout($workoutId): mixed {
		$details = $this->workoutRepo->find(id: $workoutId);
		$workoutObj = null;
		
		foreach ($details as $workout) {
			$workoutId = $workout['WORKOUT_ID'];
			if (is_null(value: $workoutObj)) {
				$workoutObj = $this->createWorkoutObj(workout: $workout);
				
				// retrieve pax
				$paxList = $this->workoutRepo->findPax(id: $workoutId);
				$paxArray = array();
				foreach ($paxList as $pax) {
					$member = new Member();
					$member->setMemberId(memberId: $pax["MEMBER_ID"]);
					$member->setF3Name(f3Name: $pax["F3_NAME"]);
					$paxArray[] = $member;
				}
				$workoutObj->setPax(pax: $paxArray);
			}
			else {
				// we already have the workout details, just add the duplicate info
				$workoutObj = $this->addAoToWorkout(workout: $workoutObj, aoId: $workout['AO_ID'], aoDescription: $workout['AO']);
				$workoutObj = $this->addQToWorkout(workout: $workoutObj, qId: $workout['Q_ID'], qName: $workout['Q']);
			}
		}

		if (!is_null(value: $workoutObj)) {
			$workoutObj->setAo(array_values(array: $workoutObj->getAo()));
			$workoutObj->setQ(array_values(array: $workoutObj->getQ()));	
		}
				
		return $workoutObj;
	}

	public function getWorkoutByDateAndSlug($year, $month, $day, $slug): mixed {
		$date = sprintf('%04d-%02d-%02d',  $year, $month, $day);
		$details = $this->workoutRepo->findByDateAndSlug(date: $date, slug: $slug);
		$workoutObj = null;
		
		foreach ($details as $workout) {
			$workoutId = $workout['WORKOUT_ID'];
			if (is_null(value: $workoutObj)) {
				$workoutObj = $this->createWorkoutObj(workout: $workout);
				
				// retrieve pax
				$paxList = $this->workoutRepo->findPax(id: $workoutId);
				$paxArray = array();
				foreach ($paxList as $pax) {
					$member = new Member();
					$member->setMemberId(memberId: $pax["MEMBER_ID"]);
					$member->setF3Name(f3Name: $pax["F3_NAME"]);
					$paxArray[] = $member;
				}
				$workoutObj->setPax(pax: $paxArray);
			}
			else {
				// we already have the workout details, just add the duplicate info
				$workoutObj = $this->addAoToWorkout(workout: $workoutObj, aoId: $workout['AO_ID'], aoDescription: $workout['AO']);
				$workoutObj = $this->addQToWorkout(workout: $workoutObj, qId: $workout['Q_ID'], qName: $workout['Q']);
			}
		}

		if (!is_null(value: $workoutObj)) {
			$workoutObj->setAo(array_values(array: $workoutObj->getAo()));
			$workoutObj->setQ(array_values(array: $workoutObj->getQ()));	
		}
				
		return $workoutObj;
	}

	/**
	 * Add a workout.  This is a legacy call which scrapes the backblast URL again.
	 * 
	 * @deprecated use addWorkoutWithData instead
	 */
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
				$workoutId = $this->workoutRepo->save(title: $data->post->title, author: null, slug: null, 
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
		$author = $data->author ?? null;
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
				$workoutId = $this->workoutRepo->save(title: $title, author: $author, slug: $slug, dateArray: $workoutDate, url: $url);
				
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

	/**
	 * Refresh a workout by its ID.  This is a legacy call which scrapes the backblast URL again.
	 * 
	 * @deprecated use refreshWorkoutWithData instead
	 */
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
				$this->workoutRepo->update(workoutId: $workoutId, title: $workout->getTitle(), author: null,
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
		$author = $data->author ?? null;
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
				$this->workoutRepo->update(workoutId: $workoutId, title: $title, author: $author, slug: $slug, dateArray: $workoutDate, url: $url);
				
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
			$workoutObj = $this->createWorkoutObj($workout);
			$workoutsArray[] = $workoutObj;
		}
		
		return $workoutsArray;
	}

	private function createWorkoutObj($workout): Workout {
		$workoutObj = new Workout();
		
		error_log(message: 'createWorkoutObj workout: ' . json_encode($workout));

		$aoArray = array();
		// AO_ID and AO description are comma delimited list, so we need to split it
		if (!is_null(value: $workout['AO_IDS'])) {
			$aoIds = explode(',', $workout['AO_IDS']);
			$aoDescriptions = explode(',', $workout['AO']);
			$aoSlugs = explode(',', $workout['AO_SLUGS']);
	
			for ($i = 0; $i < count($aoIds); $i++) {
				$aoId = trim(string: $aoIds[$i]);
				$aoDescription = trim(string: $aoDescriptions[$i]);
				$aoSlug = trim(string: $aoSlugs[$i]);
	
				// only add the AO if it exists
				if (!is_null(value: $aoId) && $aoId !== '') {
					$ao = new AO();
					$ao->setId(id: $aoId);
					$ao->setDescription(description: $aoDescription);
					$ao->setSlug(slug: $aoSlug);
					$aoArray[] = $ao;
				}
			}
		}
		$workoutObj->setAo(ao: $aoArray);
		
		$qArray = array();
		// Q_ID and Q name are comma delimited list, so we need to split it
		if (!is_null($workout['Q_IDS'])) {
			$qIds = explode(',', $workout['Q_IDS']);
			$qNames = explode(',', $workout['Q']);

			for ($i = 0; $i < count($qIds); $i++) {
				$qId = trim(string: $qIds[$i]);
				$qName = trim(string: $qNames[$i]);

				// only add the Q if it exists
				if (!is_null(value: $qId) && $qId !== '') {
					$q = new Member();
					$q->setMemberId(memberId: $qId);
					$q->setF3Name(f3Name: $qName);
					$qArray[] = $q;
				}
			}
		}
		$workoutObj->setQ(q: $qArray);
		
		$workoutObj->setBackblastUrl(backblastUrl: $workout['BACKBLAST_URL']);
		$workoutObj->setTitle(title: $workout['TITLE']);
		$workoutObj->setAuthor(author: $workout['AUTHOR']);
		$workoutObj->setSlug(slug: $workout['SLUG']);
		$workoutObj->setWorkoutId(workoutId: $workout['WORKOUT_ID']);
		$workoutObj->setWorkoutDate(workoutDate: $workout['WORKOUT_DATE']);
		if (array_key_exists(key: 'HTML_CONTENT', array: $workout)) {
			$workoutObj->setContent(content: $workout['HTML_CONTENT']);
		}
		else {
			$workoutObj->setContent(content: '');
		}

		// only set if PAX_COUNT is there
		if (array_key_exists(key: 'PAX_COUNT', array: $workout)) {
			$workoutObj->setPaxCount(paxCount: (int) $workout['PAX_COUNT']);
		}
		
		return $workoutObj;
	}
	
	private function addAoToWorkout($workout, $aoId, $aoDescription) {
		$aoArray = $workout->getAo();
		
		if (!array_key_exists($aoId, $aoArray)) {
			$ao = new AO();
			$ao->setId(id: $aoId);
			$ao->setDescription(description: $aoDescription);
			$aoArray[$aoId] = $ao;
			$workout->setAo($aoArray);
		}
		
		return $workout;
	}
	
	private function addQToWorkout($workout, $qId, $qName) {
		$qArray = $workout->getQ();
		if (!array_key_exists($qId, $qArray)) {
			$q = new Member();
			$q->setMemberId(memberId: $qId);
			$q->setF3Name(f3Name: $qName);
			$qArray[$qId] = $q;
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

	private function getOffset($page, $pageSize): int {
		return ($page - 1) * $pageSize;
	}
}
